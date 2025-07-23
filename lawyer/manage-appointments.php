<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/animations.css">  
    <link rel="stylesheet" href="../css/main.css">  
    <link rel="stylesheet" href="../css/admin.css"> <link rel="icon" type="image/png" href="https://i.ibb.co/qYYZs46L/logo.png">

    <title>Lawyer Dashboard | SafeSpace PH</title>
    <style>
        .popup{
            animation: transitionIn-Y-bottom 0.5s;
        }
        .sub-table{
            animation: transitionIn-Y-bottom 0.5s;
        }
        /* This ensures the main content area will scroll if its content is taller than the screen. */
        .dash-body{
            height: 100vh;
            overflow-y: auto;
        }
        .status-badge {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 5px;
            font-weight: bold;
            color: #fff;
        }
        .status-pending {
            background-color: #ffc107; /* Orange */
        }
        .status-accepted {
            background-color: #28a745; /* Green */
        }
        .status-rejected {
            background-color: #dc3545; /* Red */
        }
        .status-completed {
            background-color: #007bff; /* Blue */
        }
        /* Modal Styles */
        .modal {
            display: none; 
            position: fixed; 
            z-index: 1000; 
            left: 0;
            top: 0;
            width: 100%; 
            height: 100%; 
            overflow: auto; 
            background-color: rgba(0,0,0,0.4);
        }
        .modal-content {
            background-color: #fefefe;
            margin: 10% auto;
            padding: 25px;
            border: 1px solid #888;
            width: 90%;
            max-width: 500px;
            border-radius: 10px;
            animation: transitionIn-Y-bottom 0.5s;
        }
        .close-btn {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
        }
        .close-btn:hover,
        .close-btn:focus {
            color: black;
            text-decoration: none;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <?php
    session_start();

    // Authentication: Check if the user is logged in and is a 'lawyer'
    if(isset($_SESSION["user"])){
        if(($_SESSION["user"])=="" or $_SESSION['usertype']!='l'){
            header("location: ../login.php");
            exit();
        }
    }else{
        header("location: ../login.php");
        exit();
    }
    
    // Import database connection
    include("../connection.php");
    // Include the email functions file
    require_once('../send_email.php');

    // Handle appointment acceptance or rejection actions
    if (isset($_GET['action']) && isset($_GET['appoid'])) {
        $appoid = $_GET['appoid'];
        $lawyerid = $_SESSION['lawyerid']; // Get lawyer ID from session

        // Handle the 'accept' action
        if ($_GET['action'] == 'accept' && isset($_GET['scheduleid'])) {
            $scheduleid = $_GET['scheduleid'];

            // Use a database transaction to ensure data integrity
            $database->begin_transaction();
            try {
                // Step 1: Update the appointment status to 'accepted'
                $stmt1 = $database->prepare("UPDATE appointment SET status = 'accepted' WHERE appoid = ?");
                $stmt1->bind_param("i", $appoid);
                $stmt1->execute();
                $stmt1->close();

                // Step 2: Assign the current lawyer to the schedule
                $stmt2 = $database->prepare("UPDATE schedule SET lawyerid = ? WHERE scheduleid = ?");
                $stmt2->bind_param("ii", $lawyerid, $scheduleid);
                $stmt2->execute();
                $stmt2->close();

                // Step 3: Fetch all necessary details for the email notification
                $sql_details = "SELECT 
                                    c.cname, c.cemail,
                                    s.title, s.scheduledate, s.scheduletime,
                                    a.description AS case_description,
                                    l.lawyername, l.meeting_link, l.meeting_platform
                                FROM appointment a
                                JOIN client c ON a.cid = c.cid
                                JOIN schedule s ON a.scheduleid = s.scheduleid
                                JOIN lawyer l ON s.lawyerid = l.lawyerid
                                WHERE a.appoid = ?";
                
                $stmt_details = $database->prepare($sql_details);
                $stmt_details->bind_param("i", $appoid);
                $stmt_details->execute();
                $result_details = $stmt_details->get_result();
                $details = $result_details->fetch_assoc();
                $stmt_details->close();

                if ($details) {
                    // Step 4: Prepare the details array and send the confirmation email
                    $appointmentDetails = [
                        'lawyerName' => $details['lawyername'],
                        'appointmentDate' => date("F j, Y", strtotime($details['scheduledate'])),
                        'appointmentTime' => date("g:i A", strtotime($details['scheduletime'])),
                        'meetingType' => $details['meeting_platform'],
                        'meetingLink' => $details['meeting_link'],
                        'caseTitle' => $details['title'],
                        'caseDescription' => $details['case_description']
                    ];

                    // Call the function to send the email to the user
                    // This function will now throw an error if the email fails,
                    // which will be caught by the catch block below.
                    sendAppointmentAcceptedNoticeToUser($details['cemail'], $details['cname'], $appointmentDetails);
                }

                // If all steps are successful, commit the transaction
                $database->commit();

                // Redirect to clean the URL and show a success message
                header("Location: manage-appointments.php?action=accept_success");
                exit();

            } catch (Exception $e) {
                // If any step fails (including email sending), roll back all database changes
                $database->rollback();
                error_log("Failed to accept appointment: " . $e->getMessage());
                header("Location: manage-appointments.php?action=accept_error");
                exit();
            }
        } elseif ($_GET['action'] == 'reject') {
            // Handle the 'reject' action
            $stmt = $database->prepare("UPDATE appointment SET status = 'rejected' WHERE appoid = ?");
            $stmt->bind_param("i", $appoid);
            $stmt->execute();
            $stmt->close();
            
            // Redirect to clean the URL
            header("Location: manage-appointments.php?action=reject_success");
            exit();
        }
    }

    $lawyerid = $_SESSION['lawyerid']; // Lawyer ID from session
    $lawyername = $_SESSION['lawyername']; // Lawyer name from session
    $lawyeremail = $_SESSION['user']; // Lawyer email from session

    // Handle Meeting Link Form Submission
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_link'])) {
        $new_link = $_POST['meeting_link'];
        $new_platform = $_POST['meeting_platform'];

        // Prepare and execute the update statement to prevent SQL injection
        $stmt = $database->prepare("UPDATE lawyer SET meeting_link = ?, meeting_platform = ? WHERE lawyerid = ?");
        $stmt->bind_param("ssi", $new_link, $new_platform, $lawyerid);
        $stmt->execute();
        $stmt->close();

        // Redirect to avoid form resubmission on refresh
        header("Location: manage-appointments.php?action=link_updated");
        exit();
    }

    // Fetch lawyer's meeting link details
    $link_query = $database->prepare("SELECT meeting_link, meeting_platform FROM lawyer WHERE lawyerid = ?");
    $link_query->bind_param("i", $lawyerid);
    $link_query->execute();
    $link_result = $link_query->get_result();
    $lawyer_link_data = $link_result->fetch_assoc();
    $meeting_link = $lawyer_link_data['meeting_link'] ?? '';
    $meeting_platform = $lawyer_link_data['meeting_platform'] ?? '';
    $link_query->close();


    date_default_timezone_set('Asia/Manila'); // Set timezone to Manila
    $today = date('Y-m-d');
    ?>
    <div class="container">
        <div class="menu">
            <table class="menu-container" border="0">
                <tr>
                    <td style="padding:10px" colspan="2">
                        <table border="0" class="profile-container">
                            <tr>
                                <td width="30%" style="padding-left:20px" >
                                    <img src="../img/user.png" alt="" width="100%" style="border-radius:50%">
                                </td>
                                <td style="padding:0px;margin:0px;">
                                    <p class="profile-title"><?php echo htmlspecialchars($lawyername); ?></p>
                                    <p class="profile-subtitle"><?php echo htmlspecialchars($lawyeremail); ?></p>
                                </td>
                            </tr>
                            <tr>
                                <td colspan="2">
                                    <a href="../logout.php" ><input type="button" value="Log out" class="logout-btn btn-primary-soft btn"></a>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
                 <tr class="menu-row" >
                    <td class="menu-btn menu-icon-dashbord" >
                        <a href="index.php" class="non-style-link-menu"><div><p class="menu-text">Dashboard</p></a></div></a>
                    </td>
                </tr>
                 <tr class="menu-row">
                    <td class="menu-btn menu-icon-session menu-active menu-icon-session-active">
                        <a href="manage-appointments.php" class="non-style-link-menu non-style-link-menu-active"><div><p class="menu-text menu-text-active">Share a Safe Space</p></a></div>
                    </td>
                </tr>
                <tr class="menu-row">
                    <td class="menu-btn menu-icon-appoinment">
                        <a href="lawyer_appointments.php" class="non-style-link-menu"><div><p class="menu-text">My Appointments</p></a></div>
                    </td>
                </tr>
                <tr class="menu-row">
                    <td class="menu-btn menu-icon-schedule">
                        <a href="appointment.php" class="non-style-link-menu"><div><p class="menu-text">Manage Appointments</p></a></div>
                    </td>
                </tr>
               <tr class="menu-row" >
                    <td class="menu-btn menu-icon-client">
                        <a href="client.php" class="non-style-link-menu"><div><p class="menu-text"> My Clients</p></a></div>
                    </td>
                </tr>
                <tr class="menu-row" >
                    <td class="menu-btn menu-icon-settings">
                        <a href="settings.php" class="non-style-link-menu"><div><p class="menu-text">Settings</p></a></div>
                    </td>
                </tr>
                </table>
        </div>
        <div class="dash-body">
            <table border="0" width="100%" style=" border-spacing: 0;margin:0;padding:0;margin-top:25px; ">
                <tr>
                    <td width="13%">
                        <a href="manage-appointments.php" ><button class="login-btn btn-primary-soft btn btn-icon-back" style="padding-top:11px;padding-bottom:11px;margin-left:20px;width:125px"><font class="tn-in-text">Refresh</font></button></a>
                    </td>
                    <td>
                        <p style="font-size: 23px;padding-left:12px;font-weight: 600;">Share a Safe Space</p>
                    </td>
                    <td width="15%">
                        <p style="font-size: 14px;color: rgb(119, 119, 119);padding: 0;margin: 0;text-align: right;">
                            Today's Date
                        </p>
                        <p class="heading-sub12" style="padding: 0;margin: 0;">
                            <?php echo $today; ?>
                        </p>
                    </td>
                    <td width="10%">
                        <button class="btn-label" style="display: flex;justify-content: center;align-items: center;"><img src="../img/calendar.svg" width="100%"></button>
                    </td>
                </tr>
                
                <tr>
                    <td colspan="4" style="padding-top: 10px; width: 100%;">
                        <div style="display: flex; justify-content: space-between; align-items: center; padding: 0 40px 0 45px;">
                            <p class="heading-main12" style="font-size:18px; color:rgb(49, 49, 49); margin: 0;">New Session Requests</p>
                            <div style="display: flex; gap: 10px;">
                                <button id="showMyLinkBtn" class="login-btn btn" style="background-color: #5A2675; color: white; padding: 10px 20px; font-size: 14px; width: auto; border-radius: 5px; border: 1px solid #5A2675; cursor: pointer;">My Meeting Link</button>
                                <button id="meetingLinkBtn" class="login-btn btn-primary-soft btn" style="width: auto; padding: 10px 20px; font-size: 14px; margin: 0;">
                                    <?php echo !empty($meeting_link) ? 'Edit Meeting Link' : 'Add Meeting Link'; ?>
                                </button>
                            </div>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td colspan="4">
                        <center>
                        <?php
                            // Display feedback messages based on the action performed
                            if(isset($_GET['action'])){
                                if($_GET['action']=='accept_success'){
                                    echo "<div style='padding: 10px; margin: 10px 0; border-radius: 5px; background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb;'>Appointment accepted successfully and the user has been notified via email.</div>";
                                } elseif ($_GET['action']=='accept_error'){
                                    echo "<div style='padding: 10px; margin: 10px 0; border-radius: 5px; background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb;'>There was an error accepting the appointment. The user was not notified and the appointment was not confirmed. Please try again.</div>";
                                } elseif ($_GET['action']=='reject_success'){
                                    echo "<div style='padding: 10px; margin: 10px 0; border-radius: 5px; background-color: #fff3cd; color: #856404; border: 1px solid #ffeeba;'>Appointment has been successfully rejected.</div>";
                                }
                            }
                        ?>
                        </center>
                    </td>
                </tr>
                <tr>
                    <td colspan="4" style="padding-bottom: 30px;">
                       <center>
                        <div class="abc scroll">
                        <table width="93%" class="sub-table scrolldown" border="0">
                        <thead>
                        <tr>
                                <th class="table-headin">
                                    Appointment Number
                                </th>
                                <th class="table-headin">
                                    Client Name
                                </th>
                                <th class="table-headin">
                                    Session Title
                                </th>
                                <th class="table-headin">
                                    Preferred Date & Time
                                </th>
                                <th class="table-headin">
                                    Requested On
                                </th>
                                <th class="table-headin">
                                    Actions
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                        
                            <?php
                                // Fetch new session requests (lawyerid is NULL and status is 'pending')
                                $sql_new_requests = "SELECT 
                                    appointment.appoid,
                                    appointment.apponum,
                                    appointment.appodate,
                                    appointment.description AS case_description,
                                    schedule.scheduleid,
                                    schedule.title,
                                    schedule.scheduledate,
                                    schedule.scheduletime,
                                    client.cname,
                                    client.cemail,
                                    client.ctel
                                FROM appointment 
                                INNER JOIN schedule ON appointment.scheduleid = schedule.scheduleid
                                INNER JOIN client ON appointment.cid = client.cid
                                WHERE schedule.lawyerid IS NULL AND appointment.status = 'pending' 
                                ORDER BY schedule.scheduledate ASC, schedule.scheduletime ASC";

                                $result_new_requests = $database->query($sql_new_requests);

                                if($result_new_requests->num_rows == 0){
                                    echo '<tr>
                                    <td colspan="6"> <br><br><br><br>
                                    <center>
                                    <img src="../img/notfound.svg" width="25%">
                                    
                                    <br>
                                    <p class="heading-main12" style="margin-left: 45px;font-size:20px;color:rgb(49, 49, 49)">No new session requests found!</p>
                                    </center>
                                    <br><br><br><br>
                                    </td>
                                    </tr>';
                                    
                                } else {
                                    for ( $x=0; $x<$result_new_requests->num_rows;$x++){
                                        $row_request = $result_new_requests->fetch_assoc();
                                        $appoid = $row_request["appoid"];
                                        $apponum = $row_request["apponum"];
                                        $appodate = $row_request["appodate"];
                                        $case_description = $row_request["case_description"];
                                        $scheduleid = $row_request["scheduleid"];
                                        $title = $row_request["title"];
                                        $scheduledate = $row_request["scheduledate"];
                                        $scheduletime = $row_request["scheduletime"];
                                        $clientname = $row_request["cname"];
                                        $clientemail = $row_request["cemail"];
                                        $clienttel = $row_request["ctel"];

                                        echo '<tr>
                                            <td style="text-align:center;">'.$apponum.'</td>
                                            <td>'.htmlspecialchars($clientname).'</td>
                                            <td>'.htmlspecialchars($title).'</td>
                                            <td style="text-align:center;">'.htmlspecialchars($scheduledate).'<br>at '.substr(htmlspecialchars($scheduletime),0,5).'</td>
                                            <td style="text-align:center;">'.htmlspecialchars($appodate).'</td>
                                            <td>
                                                <div style="display:flex;justify-content: center;">
                                                    <a href="manage-appointments.php?action=accept&appoid='.$appoid.'&scheduleid='.$scheduleid.'" class="non-style-link">
                                                        <button class="btn-primary-soft btn button-icon btn-view" style="padding-left: 20px;padding-right: 20px;padding-top: 12px;padding-bottom: 12px;margin-top: 10px;">
                                                            <font class="tn-in-text">Accept</font>
                                                        </button>
                                                    </a>
                                                    &nbsp;&nbsp;&nbsp;
                                                    <a href="manage-appointments.php?action=reject&appoid='.$appoid.'" class="non-style-link" onclick="return confirm(\'Are you sure you want to reject this appointment?\');">
                                                        <button class="btn-primary-soft btn button-icon btn-delete" style="padding-left: 20px;padding-right: 20px;padding-top: 12px;padding-bottom: 12px;margin-top: 10px;">
                                                            <font class="tn-in-text">Reject</font>
                                                        </button>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>';
                                    }
                                }
                            ?>
                        </tbody>
                        </table>
                        </div>
                        </center>
                   </td> 
                </tr>
            </table>
        </div>
    </div>

    <div id="viewLinkModal" class="modal">
        <div class="modal-content">
            <span class="close-btn">&times;</span>
            <h2 style="margin-bottom: 20px;">My Meeting Link</h2>
            <?php if (!empty($meeting_link) && !empty($meeting_platform)): ?>
                <div style="margin-bottom: 15px;">
                    <label class="form-label">Platform:</label>
                    <input type="text" value="<?php echo htmlspecialchars($meeting_platform); ?>" class="input-text" readonly style="background-color: #f1f1f1;">
                </div>
                <div style="margin-bottom: 15px;">
                    <label class="form-label">Link or Address:</label>
                    <input type="text" value="<?php echo htmlspecialchars($meeting_link); ?>" class="input-text" readonly style="background-color: #f1f1f1;">
                </div>
                <p style="font-size: 12px; color: #666;">To change this, use the 'Edit Meeting Link' button.</p>
            <?php else: ?>
                <p class="heading-main12" style="font-size:16px; color:rgb(49, 49, 49); text-align: center;">You have not set a meeting link yet.</p>
                <p style="text-align: center; font-size: 14px; color: #666;">Please use the "Add Meeting Link" button to set one.</p>
            <?php endif; ?>
        </div>
    </div>

    <div id="meetingLinkModal" class="modal">
        <div class="modal-content">
            <span class="close-btn">&times;</span>
            <h2 style="margin-bottom: 20px;"><?php echo !empty($meeting_link) ? 'Edit Meeting Link' : 'Add Meeting Link'; ?></h2>
            <form action="manage-appointments.php" method="POST">
                <div style="margin-bottom: 15px;">
                    <label for="meeting_platform" class="form-label">Platform:</label>
                  <select name="meeting_platform" id="meeting_platform" class="input-text" required>
                        <option value="" disabled selected>Select a Platform</option>
                        <option value="Google Meet" <?php if($meeting_platform == 'Google Meet') echo 'selected'; ?>>Google Meet</option>
                        <option value="Zoom" <?php if($meeting_platform == 'Zoom') echo 'selected'; ?>>Zoom</option>
                        <option value="Microsoft Teams" <?php if($meeting_platform == 'Microsoft Teams') echo 'selected'; ?>>Microsoft Teams</option>
                        <option value="Facebook Messenger" <?php if($meeting_platform == 'Facebook Messenger') echo 'selected'; ?>>Facebook Messenger</option>
                        <option value="Skype" <?php if($meeting_platform == 'Skype') echo 'selected'; ?>>Skype</option>
                        <option value="WhatsApp" <?php if($meeting_platform == 'WhatsApp') echo 'selected'; ?>>WhatsApp</option>
                        <option value="Viber" <?php if($meeting_platform == 'Viber') echo 'selected'; ?>>Viber</option>
                        <option value="SafeSpace PH Office" <?php if($meeting_platform == 'SafeSpace PH Office') echo 'selected'; ?>>SafeSpace PH Office (In-Person)</option>
                    </select>
                </div>
                <div style="margin-bottom: 15px;">
                    <label for="meeting_link" class="form-label">Link or Address:</label>
                    <input type="text" name="meeting_link" id="meeting_link" class="input-text" placeholder="e.g., https://meet.google.com/abc-def-ghi or Office Address" value="<?php echo htmlspecialchars($meeting_link); ?>" required>
                </div>
                <p style="font-size: 12px; color: #666; margin-top: 5px;">Saving this will notify every client you currently have an appointment with.</p>
                <div style="text-align: right; margin-top: 25px;">
                     <button type="submit" name="save_link" class="login-btn btn-primary btn">Save Link</button>
                </div>
            </form>
        </div>
    </div>

<script>
    // Get the modals
    var meetingModal = document.getElementById("meetingLinkModal");
    var viewModal = document.getElementById("viewLinkModal");

    // Get the buttons that open the modals
    var openMeetingBtn = document.getElementById("meetingLinkBtn");
    var showLinkBtn = document.getElementById("showMyLinkBtn");

    // Get all <span> elements that close the modals
    var closeBtns = document.getElementsByClassName("close-btn");

    // When the user clicks the button, open the corresponding modal
    openMeetingBtn.onclick = function() {
        meetingModal.style.display = "block";
    }
    showLinkBtn.onclick = function() {
        viewModal.style.display = "block";
    }

    // When the user clicks on <span> (x) in any modal, close it
    for (let i = 0; i < closeBtns.length; i++) {
        closeBtns[i].onclick = function() {
            closeBtns[i].parentElement.parentElement.style.display = "none";
        }
    }

    // When the user clicks anywhere outside of a modal, close it
    window.onclick = function(event) {
        if (event.target == meetingModal) {
            meetingModal.style.display = "none";
        }
        if (event.target == viewModal) {
            viewModal.style.display = "none";
        }
    }

    // --- START: Added functionality for the meeting link form ---

    // Get the platform dropdown and the link input field
    const platformSelect = document.getElementById('meeting_platform');
    const linkInput = document.getElementById('meeting_link');
    const officeAddress = 'SafeSpace PH Office, P. Paredes St., Sampaloc, Manila 1015';

    // This function updates the link input based on the selected platform
    function handlePlatformChange() {
        if (platformSelect.value === 'SafeSpace PH Office') {
            // If "SafeSpace PH Office" is selected, populate the address and make it read-only
            linkInput.value = officeAddress;
            linkInput.readOnly = true;
        } else {
            // For any other platform, make the input editable
            linkInput.readOnly = false;
            // If the input currently holds the office address, clear it for the user
            if (linkInput.value === officeAddress) {
                linkInput.value = '';
            }
        }
    }

    // Add an event listener to detect changes in the platform dropdown
    platformSelect.addEventListener('change', handlePlatformChange);

    // Also, check the initial state when the page loads.
    // This handles the case where "SafeSpace PH Office" is already saved and the form is opened for editing.
    // The script is at the end of the body, so the elements are guaranteed to be available.
    if (platformSelect.value === 'SafeSpace PH Office') {
        // The value is already populated by PHP, so we just need to ensure the field is read-only.
        linkInput.readOnly = true;
    }
    
    // --- END: Added functionality ---
</script>

</body>
</html>
