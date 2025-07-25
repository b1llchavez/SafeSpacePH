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
            background-color: #ffc107; 
        }
        .status-accepted {
            background-color: #28a745; 
        }
        .status-rejected {
            background-color: #dc3545; 
        }
        .status-completed {
            background-color: #007bff; 
        }
      
        .modal {
            display: none; /* Initially hidden */
            position: fixed; 
            z-index: 1000; 
            left: 0;
            top: 0;
            width: 100%; 
            height: 100%; 
            overflow: auto; 
            background-color: rgba(0,0,0,0.4);
            align-items: center;
            justify-content: center;
        }
        .modal-content {
            background-color: #fefefe;
            margin: auto;
            padding: 25px;
            border: 1px solid #888;
            width: 90%;
            max-width: 500px;
            border-radius: 10px;
            animation: transitionIn-Y-bottom 0.5s;
            position: relative;
        }
        .close-btn {
            position: absolute;
            top: 15px;
            right: 15px;
            font-size: 24px;
            font-weight: bold;
            color: #aaa;
            cursor: pointer;
            border: none;
            background: transparent;
            padding: 0;
            width: 32px;
            height: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            transition: background-color 0.2s, color 0.2s;
            text-decoration: none;
            z-index: 1;
        }

        .close-btn:hover {
            background-color: #f0e9f7;
            color: #5A2675;
        }
        .rejection-reason {
            margin-bottom: 10px;
            display: flex;
            align-items: center;
        }
        .rejection-reason input[type="radio"] {
            margin-right: 10px;
        }
        .rejection-reason label {
            flex-grow: 1;
        }
        .action-btn-container {
            display: flex;
            justify-content: center;
            gap: 10px;
        }
        .details-label {
            font-weight: bold;
            color: #555;
            margin-top: 10px;
        }
        .details-data {
            background-color: #f1f1f1;
            padding: 8px;
            border-radius: 5px;
            margin-bottom: 10px;
            white-space: pre-wrap;
            word-wrap: break-word;
        }
        /* New robust style for button icons */
        .button-icon img {
            width: 15px;
            height: auto;
            vertical-align: middle;
            margin-right: 8px;
        }
    </style>
</head>
<body>
    <?php
    session_start();

    if(isset($_SESSION["user"])){
        if(($_SESSION["user"])=="" or $_SESSION['usertype']!='l'){
            header("location: ../login.php");
            exit();
        }
    }else{
        header("location: ../login.php");
        exit();
    }
    
    include("../connection.php");
    require_once('../send_email.php');

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] == 'reject') {
        if (isset($_POST['appoid']) && !empty($_POST['appoid']) && isset($_POST['rejection_reason'])) {
            $appoid = $_POST['appoid'];
            $reason = $_POST['rejection_reason'];
            if ($reason === 'Other') {
                if (isset($_POST['other_reason_text']) && !empty(trim($_POST['other_reason_text']))) {
                    $reason = 'Other: ' . htmlspecialchars(trim($_POST['other_reason_text']));
                } else {
                    header("Location: manage-appointments.php?action=reject_error&reason=other_empty");
                    exit();
                }
            }
            $description = isset($_POST['rejection_description']) ? htmlspecialchars(trim($_POST['rejection_description'])) : '';
    
        
    
            $stmt = $database->prepare("UPDATE appointment SET status = 'rejected' WHERE appoid = ?");
            $stmt->bind_param("i", $appoid);
            $stmt->execute();
            $stmt->close();
            
            header("Location: manage-appointments.php?action=reject_success");
            exit();
        } else {
             header("Location: manage-appointments.php?action=reject_error&reason=missing_data");
             exit();
        }
    }

    if (isset($_GET['action']) && isset($_GET['appoid'])) {
        $appoid = $_GET['appoid'];
        $lawyerid = $_SESSION['lawyerid']; 

        if ($_GET['action'] == 'accept' && isset($_GET['scheduleid'])) {
            $scheduleid = $_GET['scheduleid'];

            $database->begin_transaction();
            try {
                $stmt1 = $database->prepare("UPDATE appointment SET status = 'accepted' WHERE appoid = ?");
                $stmt1->bind_param("i", $appoid);
                $stmt1->execute();
                $stmt1->close();

                $stmt2 = $database->prepare("UPDATE schedule SET lawyerid = ? WHERE scheduleid = ?");
                $stmt2->bind_param("ii", $lawyerid, $scheduleid);
                $stmt2->execute();
                $stmt2->close();

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
                    $appointmentDetails = [
                        'lawyerName' => $details['lawyername'],
                        'appointmentDate' => date("F j, Y", strtotime($details['scheduledate'])),
                        'appointmentTime' => date("g:i A", strtotime($details['scheduletime'])),
                        'meetingType' => $details['meeting_platform'],
                        'meetingLink' => $details['meeting_link'],
                        'caseTitle' => $details['title'],
                        'caseDescription' => $details['case_description']
                    ];

                    sendAppointmentAcceptedNoticeToUser($details['cemail'], $details['cname'], $appointmentDetails);
                }

                $database->commit();

                header("Location: manage-appointments.php?action=accept_success");
                exit();

            } catch (Exception $e) {
                $database->rollback();
                error_log("Failed to accept appointment: " . $e->getMessage());
                header("Location: manage-appointments.php?action=accept_error");
                exit();
            }
        } 
       
    }

    $lawyerid = $_SESSION['lawyerid']; 
    $lawyername = $_SESSION['lawyername']; 
    $lawyeremail = $_SESSION['user']; 
    
    $link_query = $database->prepare("SELECT meeting_link, meeting_platform FROM lawyer WHERE lawyerid = ?");
    $link_query->bind_param("i", $lawyerid);
    $link_query->execute();
    $link_result = $link_query->get_result();
    $lawyer_link_data = $link_result->fetch_assoc();
    $meeting_link = $lawyer_link_data['meeting_link'] ?? '';
    $meeting_platform = $lawyer_link_data['meeting_platform'] ?? '';
    $link_query->close();

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_link'])) {
        $new_link = $_POST['meeting_link'];
        $new_platform = $_POST['meeting_platform'];
        $old_meeting_link = $meeting_link; 

        $database->begin_transaction();
        try {
            $sql_clients = $database->prepare("
                SELECT
                    c.cname, c.cemail,
                    s.title, s.scheduledate, s.scheduletime
                FROM appointment a
                JOIN client c ON a.cid = c.cid
                JOIN schedule s ON a.scheduleid = s.scheduleid
                WHERE s.lawyerid = ?
                  AND a.status = 'accepted'
                  AND s.scheduledate >= ?
            ");
            $today_for_query = date('Y-m-d');
            $sql_clients->bind_param("is", $lawyerid, $today_for_query);
            $sql_clients->execute();
            $clients_result = $sql_clients->get_result();
            $clients_to_notify = [];
            while ($row = $clients_result->fetch_assoc()) {
                $clients_to_notify[] = $row;
            }
            $sql_clients->close();

            $stmt_update = $database->prepare("UPDATE lawyer SET meeting_link = ?, meeting_platform = ? WHERE lawyerid = ?");
            $stmt_update->bind_param("ssi", $new_link, $new_platform, $lawyerid);
            $stmt_update->execute();
            $stmt_update->close();

            foreach ($clients_to_notify as $client) {
                $appointmentDetails = [
                    'lawyerName'      => $lawyername,
                    'appointmentDate' => date("F j, Y", strtotime($client['scheduledate'])),
                    'appointmentTime' => date("g:i A", strtotime($client['scheduletime'])),
                    'oldMeetingLink'  => $old_meeting_link,
                    'newMeetingLink'  => $new_link,
                    'caseTitle'       => $client['title']
                ];

                sendMeetingLinkUpdateNoticeToUser($client['cemail'], $client['cname'], $appointmentDetails);
            }

            $database->commit();

            header("Location: manage-appointments.php?action=link_updated");
            exit();

        } catch (Exception $e) {
            $database->rollback();
            error_log("Failed to update meeting link and notify clients: " . $e->getMessage());

            header("Location: manage-appointments.php?action=link_update_error");
            exit();
        }
    }


    date_default_timezone_set('Asia/Manila'); 
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
                
                    <td>
                        <p style="margin-left: 45px; font-size: 23px;font-weight: 600;">Share a Safe Space</p>
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
                            if(isset($_GET['action'])){
                                if($_GET['action']=='accept_success'){
                                    echo "<div style='padding: 10px; margin: 10px 0; border-radius: 5px; background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb;'>Appointment accepted successfully and the user has been notified via email.</div>";
                                } elseif ($_GET['action']=='accept_error'){
                                    echo "<div style='padding: 10px; margin: 10px 0; border-radius: 5px; background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb;'>There was an error accepting the appointment. The user was not notified and the appointment was not confirmed. Please try again.</div>";
                                } elseif ($_GET['action']=='reject_success'){
                                    echo "<div style='padding: 10px; margin: 10px 0; border-radius: 5px; background-color: #fff3cd; color: #856404; border: 1px solid #ffeeba;'>Appointment has been successfully rejected.</div>";
                                } elseif ($_GET['action'] == 'link_updated') {
                                    echo "<div style='padding: 10px; margin: 10px 0; border-radius: 5px; background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb;'>Meeting link updated successfully. All clients with upcoming appointments have been notified of the change.</div>";
                                } elseif ($_GET['action'] == 'link_update_error') {
                                    echo "<div style='padding: 10px; margin: 10px 0; border-radius: 5px; background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb;'>There was an error updating the meeting link. No changes were saved, and no notifications were sent. Please try again.</div>";
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
                                <th class="table-headin" style="width: 30%;">
                                    Actions
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                        
                            <?php
                                $sql_new_requests = "SELECT 
                                    appointment.appoid,
                                    appointment.apponum,
                                    appointment.appodate,
                                    appointment.description AS case_description,
                                    schedule.scheduleid,
                                    schedule.title,
                                    schedule.scheduledate,
                                    schedule.scheduletime,
                                    client.cname
                                FROM appointment 
                                INNER JOIN schedule ON appointment.scheduleid = schedule.scheduleid
                                INNER JOIN client ON appointment.cid = client.cid
                                WHERE schedule.lawyerid IS NULL AND appointment.status = 'pending' 
                                ORDER BY schedule.scheduledate ASC, schedule.scheduletime ASC";

                                $result_new_requests = $database->query($sql_new_requests);

                                if($result_new_requests->num_rows == 0){
                                    echo '<tr>
                                    <td colspan="5"> <br><br><br><br>
                                    <center>
                                    <img src="../img/notfound.svg" width="25%">
                                    
                                    <br>
                                    <p class="heading-main12" style="margin-left: 45px;font-size:20px;color:rgb(49, 49, 49)">No new session requests found!</p>
                                    </center>
                                    <br><br><br><br>
                                    </td>
                                    </tr>';
                                    
                                } else {
                                    while ($row_request = $result_new_requests->fetch_assoc()){
                                        $appoid = $row_request["appoid"];
                                        $appodate = $row_request["appodate"];
                                        $scheduleid = $row_request["scheduleid"];
                                        $title = $row_request["title"];
                                        $scheduledate = $row_request["scheduledate"];
                                        $scheduletime = $row_request["scheduletime"];
                                        $clientname = $row_request["cname"];
                                        $case_description = $row_request["case_description"];
                                        
                                        $view_icon_path = '../img/icons/view.svg';
                                        $view_icon_white_path = '../img/icons/view-white.svg';
                                        $accept_icon_path = '../img/icons/verify.svg';
                                        $accept_icon_white_path = '../img/icons/verify-white.svg';
                                        $reject_icon_path = '../img/icons/reject.svg';
                                        $reject_icon_white_path = '../img/icons/reject-white.svg';


                                        echo '<tr>
                                            <td>'.htmlspecialchars($clientname).'</td>
                                            <td>'.htmlspecialchars($title).'</td>
                                            <td style="text-align:center;">'.date("M d, Y", strtotime($scheduledate)).'<br>'.date("g:i A", strtotime($scheduletime)).'</td>
                                            <td style="text-align:center;">'.date("M d, Y", strtotime($appodate)).'</td>
                                            <td>
                                                <div class="action-btn-container">
                                                    <button class="btn-primary-soft btn button-icon btn-view view-details-btn"
                                                        onmouseover="this.querySelector(\'img\').src=\''.$view_icon_white_path.'\'"
                                                        onmouseout="this.querySelector(\'img\').src=\''.$view_icon_path.'\'"
                                                        data-clientname="'.htmlspecialchars($clientname).'"
                                                        data-title="'.htmlspecialchars($title).'"
                                                        data-date="'.date("F j, Y", strtotime($scheduledate)).'"
                                                        data-time="'.date("g:i A", strtotime($scheduletime)).'"
                                                        data-description="'.htmlspecialchars($case_description).'">
                                                       View Details
                                                    </button>
                                               <a href="manage-appointments.php?action=accept&appoid='.$appoid.'&scheduleid='.$scheduleid.'" class="non-style-link accept-btn">
    <button class="btn-primary-soft btn button-icon"
        onmouseover="this.querySelector(\'img\').src=\''.$accept_icon_white_path.'\'"
        onmouseout="this.querySelector(\'img\').src=\''.$accept_icon_path.'\'">
        <img src="'.$accept_icon_path.'" alt="Accept"> Accept
    </button>
</a>
                                                    <a href="manage-appointments.php?action=reject&appoid='.$appoid.'" class="non-style-link reject-btn">
                                                        <button type="button" class="btn-primary-soft btn button-icon btn-delete"
                                                            onmouseover="this.querySelector(\'img\').src=\''.$reject_icon_white_path.'\'"
                                                            onmouseout="this.querySelector(\'img\').src=\''.$reject_icon_path.'\'">
                                                           Reject
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

    <!-- View Details Modal -->
    <div id="viewDetailsModal" class="modal">
        <div class="modal-content">
            <span class="close-btn">&times;</span>
            <h2 style="margin-bottom: 20px;">Session Request Details</h2>
            <div>
                <p class="details-label">Client Name:</p>
                <p id="detailClientName" class="details-data"></p>

                <p class="details-label">Session Title:</p>
                <p id="detailSessionTitle" class="details-data"></p>

                <p class="details-label">Preferred Date & Time:</p>
                <p id="detailDateTime" class="details-data"></p>

                <p class="details-label">Case Description:</p>
                <p id="detailDescription" class="details-data" style="max-height: 150px; overflow-y: auto;"></p>
            </div>
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

    <div id="rejectionModal" class="modal">
        <div class="modal-content">
            <span class="close-btn">&times;</span>
            <h2 style="margin-bottom: 15px;">Reason for Rejection</h2>
            <form id="rejectionForm" action="manage-appointments.php" method="POST" novalidate>
                <input type="hidden" name="action" value="reject">
                <input type="hidden" id="rejectAppoId" name="appoid" value="">

                <div id="rejectionError" style="color: #721c24; background-color: #f8d7da; border: 1px solid #f5c6cb; padding: 10px; border-radius: 5px; margin-bottom: 15px; display: none;"></div>
                
                <p style="margin-bottom: 15px;">Why do you want to reject this appointment?</p>

                <div class="rejection-reason">
                    <input type="radio" id="reason_time" name="rejection_reason" value="Change of time" required>
                    <label for="reason_time">Change of time</label>
                </div>
                <div class="rejection-reason">
                    <input type="radio" id="reason_emergency" name="rejection_reason" value="Personal emergency">
                    <label for="reason_emergency">Personal emergency</label>
                </div>
                <div class="rejection-reason">
                    <input type="radio" id="reason_details" name="rejection_reason" value="Incomplete details">
                    <label for="reason_details">Incomplete details</label>
                </div>
                <div class="rejection-reason">
                    <input type="radio" id="reason_other" name="rejection_reason" value="Other">
                    <label for="reason_other">Other</label>
                </div>
                <input type="text" id="other_reason_text" name="other_reason_text" class="input-text" placeholder="Please specify" style="display: none; margin-top: 5px; width: 100%;">

                <div style="margin-top: 20px;">
                    <label for="rejection_description" class="form-label">Optional Description:</label>
                    <textarea name="rejection_description" id="rejection_description" class="input-text" rows="3" placeholder="Provide more details (optional)"></textarea>
                </div>

                <div style="text-align: right; margin-top: 25px;">
                     <button type="button" id="cancelRejectionBtn" class="btn-primary-soft btn">Cancel</button>
                     <button type="submit" name="confirm_rejection" class="login-btn btn-primary btn">Confirm Rejection</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Acceptance Confirmation Modal -->
    <div id="acceptanceModal" class="modal">
        <div class="modal-content">
            <span class="close-btn">&times;</span>
            <h2 style="margin-bottom: 15px;">Confirm Acceptance</h2>
            <p style="margin-bottom: 25px;">Are you sure you want to accept this appointment? The client will be notified via email.</p>
            <div style="text-align: right; display: flex; justify-content: flex-end; gap: 10px;">
                 <button type="button" id="cancelAcceptanceBtn" class="btn-primary-soft btn">Cancel</button>
                 <a id="confirmAcceptanceLink" href="#" class="non-style-link">
                    <button type="button" class="login-btn btn-primary btn">Confirm Acceptance</button>
                 </a>
            </div>
        </div>
    </div>

<script>
    // Get all modals
    var meetingModal = document.getElementById("meetingLinkModal");
    var viewModal = document.getElementById("viewLinkModal");
    var rejectionModal = document.getElementById("rejectionModal");
    var acceptanceModal = document.getElementById("acceptanceModal");
    var viewDetailsModal = document.getElementById("viewDetailsModal");

    // Get buttons that open modals
    var openMeetingBtn = document.getElementById("meetingLinkBtn");
    var showLinkBtn = document.getElementById("showMyLinkBtn");
    var rejectBtns = document.getElementsByClassName("reject-btn");
    var acceptBtns = document.getElementsByClassName("accept-btn");
    var viewDetailsBtns = document.getElementsByClassName("view-details-btn");

    var closeBtns = document.getElementsByClassName("close-btn");

    // Open modal functions
    openMeetingBtn.onclick = function() {
        meetingModal.style.display = "flex";
    }
    showLinkBtn.onclick = function() {
        viewModal.style.display = "flex";
    }

    // Handler for View Details buttons
    for (let i = 0; i < viewDetailsBtns.length; i++) {
        viewDetailsBtns[i].onclick = function(event) {
            event.preventDefault();
            const button = this;
            document.getElementById('detailClientName').innerText = button.getAttribute('data-clientname');
            document.getElementById('detailSessionTitle').innerText = button.getAttribute('data-title');
            document.getElementById('detailDateTime').innerText = button.getAttribute('data-date') + ' at ' + button.getAttribute('data-time');
            document.getElementById('detailDescription').innerText = button.getAttribute('data-description');
            viewDetailsModal.style.display = "flex";
        }
    }

    // Handler for Accept buttons
    for (let i = 0; i < acceptBtns.length; i++) {
        acceptBtns[i].onclick = function(event) {
            event.preventDefault();
            const href = this.getAttribute('href');
            document.getElementById('confirmAcceptanceLink').setAttribute('href', href);
            acceptanceModal.style.display = "flex";
        }
    }
    
    // Handler for Reject buttons
    for (let i = 0; i < rejectBtns.length; i++) {
        rejectBtns[i].onclick = function(event) {
            event.preventDefault(); 
            const href = this.getAttribute('href');
            const urlParams = new URLSearchParams(href.substring(href.indexOf('?')));
            const appoId = urlParams.get('appoid');
            document.getElementById('rejectAppoId').value = appoId;
            rejectionModal.style.display = "flex";
        }
    }

    // Handler for all close buttons in modals
    for (let i = 0; i < closeBtns.length; i++) {
        closeBtns[i].onclick = function() {
            this.closest('.modal').style.display = "none";
        }
    }

    // Handler for cancel buttons
    document.getElementById('cancelRejectionBtn').onclick = function() {
        rejectionModal.style.display = "none";
    }
    document.getElementById('cancelAcceptanceBtn').onclick = function() {
        acceptanceModal.style.display = "none";
    }

    // Close modal if clicked outside
    window.onclick = function(event) {
        if (event.target.classList.contains('modal')) {
            event.target.style.display = "none";
        }
    }

    // Rejection form logic
    const rejectionForm = document.getElementById('rejectionForm');
    const reasonRadios = rejectionForm.querySelectorAll('input[name="rejection_reason"]');
    const otherReasonText = document.getElementById('other_reason_text');
    const rejectionError = document.getElementById('rejectionError');

    reasonRadios.forEach(radio => {
        radio.addEventListener('change', function() {
            if (this.id === 'reason_other' && this.checked) {
                otherReasonText.style.display = 'block';
                otherReasonText.setAttribute('required', 'required');
            } else {
                otherReasonText.style.display = 'none';
                otherReasonText.removeAttribute('required');
            }
        });
    });

    rejectionForm.addEventListener('submit', function(event) {
        const otherRadio = document.getElementById('reason_other');
        const selectedReason = rejectionForm.querySelector('input[name="rejection_reason"]:checked');
        rejectionError.style.display = 'none'; 

        if (!selectedReason) {
            rejectionError.textContent = 'Please select a reason for rejection.';
            rejectionError.style.display = 'block';
            event.preventDefault(); 
            return;
        }

        if (otherRadio.checked && otherReasonText.value.trim() === '') {
            rejectionError.textContent = 'Please specify the reason for selecting "Other".';
            rejectionError.style.display = 'block';
            event.preventDefault();
        }
    });

    const observer = new MutationObserver(function(mutations) {
        mutations.forEach(function(mutation) {
            if (mutation.attributeName === "style" && rejectionModal.style.display === 'none') {
                 rejectionForm.reset();
                 otherReasonText.style.display = 'none';
                 otherReasonText.removeAttribute('required');
                 rejectionError.style.display = 'none';
            }
        });
    });
    observer.observe(rejectionModal, { attributes: true });


    const platformSelect = document.getElementById('meeting_platform');
    const linkInput = document.getElementById('meeting_link');
    const officeAddress = 'SafeSpace PH Office, P. Paredes St., Sampaloc, Manila 1015';

    function handlePlatformChange() {
        if (platformSelect.value === 'SafeSpace PH Office') {
            linkInput.value = officeAddress;
            linkInput.readOnly = true;
        } else {
            linkInput.readOnly = false;
            if (linkInput.value === officeAddress) {
                linkInput.value = '';
            }
        }
    }

    platformSelect.addEventListener('change', handlePlatformChange);

    if (platformSelect.value === 'SafeSpace PH Office') {
        linkInput.readOnly = true;
    }
</script>

</body>
</ht