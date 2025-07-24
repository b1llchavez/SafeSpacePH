<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/animations.css">  
    <link rel="stylesheet" href="../css/main.css">  
    <link rel="stylesheet" href="../css/admin.css"> <link rel="icon" type="image/png" href="https://i.ibb.co/qYYZs46L/logo.png">

    <title>My Appointments | SafeSpace PH</title>
    <style>
        /* This rule allows the main content area to scroll if its content is too tall for the screen. */
        .dash-body{
            overflow-y: auto;
        }
        .popup{
            animation: transitionIn-Y-bottom 0.5s;
        }
        .sub-table{
            animation: transitionIn-Y-bottom 0.5s;
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
        .status-unassigned {
            background-color: #6c757d; /* Grey */
        }
    </style>
</head>
<body>
    <?php

    session_start();

    // Authentication: Changed to check for 'c' (client) usertype
    if(isset($_SESSION["user"])){
        if(($_SESSION["user"])=="" or $_SESSION['usertype']!='c'){
            header("location: ../login.php");
            exit(); // Added exit to prevent further execution
        }
    }else{
        header("location: ../login.php");
        exit(); // Added exit to prevent further execution
    }
    
    // Import database connection and email functions
    include("../connection.php");
    require_once '../send_email.php';

    // --- FIX START ---
    // Retrieve clientid and clientname from session
    $clientid = $_SESSION['cid']; 
    $clientname = $_SESSION['cname']; // Retrieve clientname from session
    // --- FIX END ---
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
                                    <p class="profile-title"><?php echo htmlspecialchars($clientname); ?></p>
                                    <p class="profile-subtitle"><?php echo $_SESSION['user']; ?></p>
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
                    <td class="menu-btn menu-icon-home" >
                        <a href="index.php" class="non-style-link-menu"><div><p class="menu-text">Home</p></a></div></a>
                    </td>
                </tr>
                <tr class="menu-row" >
                    <td class="menu-btn menu-icon-report">
                        <a href="report.php" class="non-style-link-menu"><div><p class="menu-text">Report Violation</p></a></div>
                    </td>
                </tr>
                 <tr class="menu-row">
                    <td class="menu-btn menu-icon-schedule">
                        <a href="request-session.php" class="non-style-link-menu"><div><p class="menu-text">Find a Safe Space</p></div></a>
                    </td>
                </tr>
                 <tr class="menu-row">
                    <td class="menu-btn menu-icon-session menu-active menu-icon-session-active">
                        <a href="client-appointment.php" class="non-style-link-menu non-style-link-menu-active"><div><p class="menu-text menu-text-active">My Appointments</p></div></a>
                    </td>
                </tr>
                <tr class="menu-row">
                    <td class="menu-btn menu-icon-lawyers">
                        <a href="lawyers.php" class="non-style-link-menu"><div><p class="menu-text">All Lawyers</p></a></div>
                    </td>
                </tr>
                <tr class="menu-row" >
                    <td class="menu-btn menu-icon-appoinment">
                        <a href="appointment.php" class="non-style-link-menu"><div><p class="menu-text">My Bookings</p></a></div>
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
                <!-- ======================== HEADER ROW UPDATED ======================== -->
                <tr>
                    <td width="50%">
                        <p style="margin-left: 45px; font-size: 23px;font-weight: 600">My Appointments</p>
                    </td>
                    <td colspan="2" style="text-align: right; padding-right: 45px; vertical-align: middle;">
                        <div style="display: inline-flex; align-items: center; gap: 15px;">
                            <div>
                                <p style="font-size: 14px;color: rgb(119, 119, 119);padding: 0;margin: 0;text-align: right;">
                                    Today's Date
                                </p>
                                <p class="heading-sub12" style="padding: 0;margin: 0;text-align: right;">
                                    <?php 
                                    date_default_timezone_set('Asia/Manila');
                                    $today = date('Y-m-d');
                                    echo $today;
                                    // Fetch total appointments for the logged-in client
                                    $list110 = $database->query("select * from appointment where cid = '$clientid';");
                                    ?>
                                </p>
                            </div>
                        <button class="btn-label" style="display: flex;justify-content: center;align-items: center;"><img src="../img/calendar.svg" width="100%"></button>
                        </div>
                    </td>
                </tr>
                <!-- ======================== END OF UPDATED HEADER ROW ======================== -->
               
                <!-- ======================== FILTER SECTION ======================== -->
                <tr>
                    <td width="50%" style="padding-top:10px;">
                        <p class="heading-main12" style="margin-left: 45px;font-size:18px;color:rgb(49, 49, 49)">All My Appointments (<?php echo $list110->num_rows; ?>)</p>
                    </td>
                    <td colspan="2" style="padding-top:10px; text-align: right; padding-right: 45px;">
                        <form action="" method="post" style="display: inline-flex; gap: 10px; align-items: center;">
                            
                            <!-- Date Filter -->
                            <input type="date" name="sheduledate" id="date" class="input-text" style="width: auto; padding: 8px 10px;" value="<?php echo isset($_POST['sheduledate']) ? htmlspecialchars($_POST['sheduledate']) : '' ?>">
                            
                            <!-- Lawyer Filter -->
                            <select name="lawyerid" id="lawyerid" class="input-text" style="width: auto; padding: 8px 10px; height: 37px; margin: 0;">
                                <option value="" disabled <?php if (!isset($_POST['lawyerid']) || $_POST['lawyerid'] == '') echo 'selected'; ?>>Choose a Lawyer</option>
                                <option value="NULL" <?php if (isset($_POST['lawyerid']) && $_POST['lawyerid'] == 'NULL') echo 'selected'; ?>>Unassigned</option>
                                <?php
                                    $list11 = $database->query("select * from lawyer order by lawyername asc;");
                                    while ($row00 = $list11->fetch_assoc()) {
                                        $sn = $row00["lawyername"];
                                        $id00 = $row00["lawyerid"];
                                        $selected = (isset($_POST['lawyerid']) && $_POST['lawyerid'] == $id00) ? 'selected' : '';
                                        echo "<option value='".$id00."' ".$selected.">".htmlspecialchars($sn)."</option>";
                                    }
                                ?>
                            </select>

                            <!-- Filter Button -->
                            <button type="submit" name="filter" class="btn-primary-soft btn" style="display: inline-flex; align-items: center; gap: 6px; padding: 10px 15px; font-weight: 600;">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"></polygon></svg>
                                Filter
                            </button>

                            <!-- Reset Button -->
                            <a href="client-appointment.php" class="non-style-link btn-primary-soft btn" style="display: inline-flex; align-items: center; gap: 6px; padding: 10px 15px; font-weight: 600;">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="23 4 23 10 17 10"></polyline><path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10"></path></svg>
                                Reset
                            </a>
                        </form>
                    </td>
                </tr>
                <!-- ======================== END OF FILTER SECTION ======================== -->
                
                <?php
                    $sqlmain_filter_parts = [];
                    $sqlmain_filter_parts[] = "client.cid = '$clientid'"; // Always filter by client ID

                    if($_POST){
                        if(!empty($_POST["sheduledate"])){
                            $sheduledate=$_POST["sheduledate"];
                            $sqlmain_filter_parts[] = "schedule.scheduledate='$sheduledate'";
                        }

                        if(isset($_POST["lawyerid"]) && $_POST["lawyerid"] !== ''){
                            $lawyerid_filter = mysqli_real_escape_string($database, $_POST["lawyerid"]);
                            if ($lawyerid_filter === 'NULL') {
                                $sqlmain_filter_parts[] = "schedule.lawyerid IS NULL";
                            } else {
                                $sqlmain_filter_parts[] = "schedule.lawyerid = '$lawyerid_filter'";
                            }
                        }
                    }

                    $sql_where_clause = '';
                    if (!empty($sqlmain_filter_parts)) {
                        $sql_where_clause = ' WHERE ' . implode(' AND ', $sqlmain_filter_parts);
                    }

                    // Main query to fetch appointments for the logged-in client
                    $sqlmain= "SELECT 
                                appointment.appoid,
                                schedule.scheduleid,
                                schedule.title,
                                COALESCE(lawyer.lawyername, 'Unassigned') AS lawyername, -- Display 'Unassigned' if lawyerid is NULL
                                client.cname,
                                schedule.scheduledate,
                                schedule.scheduletime,
                                appointment.apponum,
                                appointment.appodate,
                                appointment.status -- Added status column
                            FROM appointment 
                            INNER JOIN schedule ON schedule.scheduleid=appointment.scheduleid 
                            INNER JOIN client ON client.cid=appointment.cid 
                            LEFT JOIN lawyer ON schedule.lawyerid=lawyer.lawyerid " . // Use LEFT JOIN to include unassigned sessions
                            $sql_where_clause . " ORDER BY schedule.scheduledate DESC";
                ?>
                  
                <tr>
                   <td colspan="4">
                       <center>
                        <div class="abc scroll">
                        <table width="93%" class="sub-table scrolldown" border="0">
                        <thead>
                        <tr>
                                <th class="table-headin">
                                    Appointment Number
                                </th>
                                <th class="table-headin">
                                    Lawyer
                                </th>
                                <th class="table-headin">
                                    Session Title
                                </th>
                                <th class="table-headin" style="font-size:10px">
                                    Session Date & Time
                                </th>
                                <th class="table-headin">
                                    Appointment Date
                                </th>
                                <th class="table-headin">
                                    Status </th>
                                <th class="table-headin">
                                    Events
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                        
                            <?php
                                $result= $database->query($sqlmain);

                                if($result->num_rows==0){
                                    echo '<tr>
                                    <td colspan="7"> <br><br><br><br>
                                    <center>
                                    <img src="../img/notfound.svg" width="25%">
                                    
                                    <br>
                                    <p class="heading-main12" style="margin-left: 45px;font-size:20px;color:rgb(49, 49, 49)">We couldn\'t find anything related to your keywords!</p>
                                    <a class="non-style-link" href="client-appointment.php"><button  class="login-btn btn-primary-soft btn"  style="display: flex;justify-content: center;align-items: center;margin-left:20px;">&nbsp; Show all Appointments &nbsp;</font></button>
                                    </a>
                                    </center>
                                    <br><br><br><br>
                                    </td>
                                    </tr>';
                                    
                                }
                                else{
                                for ( $x=0; $x<$result->num_rows;$x++){
                                    $row=$result->fetch_assoc();
                                    $appoid=$row["appoid"];
                                    $scheduleid=$row["scheduleid"];
                                    $title=$row["title"];
                                    $lawyername=$row["lawyername"]; // This will now be 'Unassigned' or the lawyer's name
                                    $scheduledate=$row["scheduledate"];
                                    $scheduletime=$row["scheduletime"];
                                    $cname=$row["cname"]; // Still available for details if needed
                                    $apponum=$row["apponum"];
                                    $appodate=$row["appodate"];
                                    $status=$row["status"]; // Get status

                                    echo '<tr >
                                        <td style="text-align:center;font-size:23px;font-weight:500; color: var(--btnnicetext);">
                                        '.$apponum.'
                                        </td>
                                        <td>
                                        '.substr($lawyername,0,25).'
                                        </td>
                                        <td>
                                        '.substr($title,0,15).'
                                        </td>
                                        <td style="text-align:center;font-size:12px;">
                                            '.substr($scheduledate,0,10).' <br>'.substr($scheduletime,0,5).'
                                        </td>
                                        <td style="text-align:center;">
                                            '.$appodate.'
                                        </td>
                                        <td>
                                            <span class="status-badge status-'.strtolower($status).'">'.$status.'</span> </td>
                                        <td>
                                        <div style="display:flex;justify-content: center;">';
                                        
                                        // Conditional actions based on status
                                        if ($status == 'pending') {
                                            echo '<a href="?action=cancel&id='.$appoid.'&session='.$title.'&apponum='.$apponum.'" class="non-style-link">
                                                    <button class="btn-primary-soft btn button-icon btn-delete" style="padding-left: 40px;padding-top: 12px;padding-bottom: 12px;margin-top: 10px;">
                                                        <font class="tn-in-text">Cancel Request</font>
                                                    </button>
                                                </a>';
                                        } else if ($status == 'accepted') {
                                            echo '<a href="?action=view_details&id='.$appoid.'" class="non-style-link">
                                                    <button class="btn-primary-soft btn button-icon btn-view" style="padding-left: 40px;padding-top: 12px;padding-bottom: 12px;margin-top: 10px;">
                                                        <font class="tn-in-text">View Details</font>
                                                    </button>
                                                </a>';
                                        } else {
                                            echo '<button class="btn-primary-soft btn" disabled style="padding: 12px; margin-top: 10px;">No Action</button>';
                                        }
                                        
                                        echo '</div>
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
    <?php
    // Popup Actions: Adapted for client-side cancellation and view
    if($_GET){
        $id=$_GET["id"];
        $action=$_GET["action"];
        
        if($action=='cancel'){ // Renamed from 'drop' for clarity
            $session=$_GET["session"];
            $apponum=$_GET["apponum"];
            echo '
            <div id="popup1" class="overlay">
                    <div class="popup">
                    <center>
                        <h2>Are you sure you want to cancel?</h2>
                        <a class="close" href="client-appointment.php">&times;</a>
                        <div class="content">
                            You are about to cancel your appointment:<br><br>
                            Appointment number &nbsp; : <b>'.substr($apponum,0,40).'</b><br>
                            Session Title: &nbsp;<b>'.substr($session,0,40).'</b><br><br>
                            This action cannot be undone.
                        </div>
                        <div style="display: flex;justify-content: center;">
                        <a href="client-appointment.php?action=confirm_cancel&id='.$id.'" class="non-style-link"><button  class="btn-primary btn"  style="display: flex;justify-content: center;align-items: center;margin:10px;padding:10px;"<font class="tn-in-text">&nbsp;Yes, Cancel&nbsp;</font></button></a>&nbsp;&nbsp;&nbsp;
                        <a href="client-appointment.php" class="non-style-link"><button  class="btn-primary btn"  style="display: flex;justify-content: center;align-items: center;margin:10px;padding:10px;"><font class="tn-in-text">&nbsp;&nbsp;No&nbsp;&nbsp;</font></button></a>

                        </div>
                    </center>
            </div>
            </div>
            '; 
        } elseif ($action == 'confirm_cancel') {
            // Fetch appointment details to send in the cancellation email
            $sql_fetch = "SELECT 
                            c.cemail, 
                            c.cname, 
                            s.scheduledate, 
                            s.scheduletime, 
                            s.title, 
                            a.description, 
                            COALESCE(l.lawyername, 'Not Assigned') AS lawyername
                          FROM appointment AS a
                          JOIN client AS c ON a.cid = c.cid
                          JOIN schedule AS s ON a.scheduleid = s.scheduleid
                          LEFT JOIN lawyer AS l ON s.lawyerid = l.lawyerid
                          WHERE a.appoid = '$id' AND a.cid = '$clientid'";
            
            $result_fetch = $database->query($sql_fetch);

            if ($result_fetch->num_rows > 0) {
                $details = $result_fetch->fetch_assoc();

                // Send the cancellation email
                try {
                    sendAppointmentCanceledEmail(
                        $details['cemail'],
                        $details['cname'],
                        $details['scheduledate'],
                        $details['scheduletime'],
                        $details['title'],
                        $details['description'],
                        $details['lawyername']
                    );

                    // Delete the appointment from the database
                    $database->query("DELETE FROM appointment WHERE appoid='$id'");
                    
                    // Redirect to the same page to refresh the list and remove URL parameters
                    echo "<script>alert('Your appointment has been canceled successfully.'); window.location.href='client-appointment.php';</script>";
                    exit();

                } catch (Exception $e) {
                    // Handle email sending failure
                    error_log("Email sending failed for cancellation of appoid $id: " . $e->getMessage());
                    echo "<script>alert('Could not send cancellation email, but the appointment will be canceled. Please contact support if you have questions.');</script>";
                    
                    // Still proceed to cancel the appointment
                    $database->query("DELETE FROM appointment WHERE appoid='$id'");
                    echo "<script>window.location.href='client-appointment.php';</script>";
                    exit();
                }

            } else {
                // Handle error: appointment not found or doesn't belong to the user
                echo "<script>alert('Error: Appointment not found or you do not have permission to cancel it.'); window.location.href='client-appointment.php';</script>";
                exit();
            }

        } elseif($action=='view_details'){ // For viewing appointment details for accepted/completed
            // You'll need to fetch appointment, lawyer, and schedule details
            $sqlmain_view = "SELECT 
                                appointment.appoid,
                                schedule.scheduleid,
                                schedule.title,
                                COALESCE(lawyer.lawyername, 'Unassigned') AS lawyername, -- Display 'Unassigned' if lawyerid is NULL
                                lawyer.lawyeremail,
                                lawyer.lawyertel,
                                specialties.sname AS specialty_name,
                                schedule.scheduledate,
                                schedule.scheduletime,
                                appointment.apponum,
                                appointment.appodate,
                                appointment.status,
                                appointment.description 
                            FROM appointment
                            INNER JOIN schedule ON appointment.scheduleid = schedule.scheduleid
                            LEFT JOIN lawyer ON schedule.lawyerid = lawyer.lawyerid -- Use LEFT JOIN
                            LEFT JOIN specialties ON lawyer.specialties = specialties.id
                            WHERE appointment.appoid='$id' AND appointment.cid = '$clientid'"; 
            
            $result_view = $database->query($sqlmain_view);
            if ($result_view->num_rows > 0) {
                $row_view = $result_view->fetch_assoc();
                $session_title = $row_view["title"];
                $lawyer_name = $row_view["lawyername"];
                $lawyer_email = $row_view["lawyeremail"];
                $lawyer_tel = $row_view["lawyertel"];
                $specialty_name = $row_view["specialty_name"];
                $session_date = $row_view["scheduledate"];
                $session_time = $row_view["scheduletime"];
                $appointment_number = $row_view["apponum"];
                $appointment_date = $row_view["appodate"];
                $current_status = $row_view["status"];
                $case_description = $row_view["description"]; 

                echo '
                <div id="popup1" class="overlay">
                    <div class="popup">
                        <center>
                            <h2>Appointment Details</h2>
                            <a class="close" href="client-appointment.php">&times;</a>
                            <div class="content">
                                <table width="90%" class="sub-table scrolldown add-lawyer-form-container" border="0">
                                    <tr>
                                        <td class="label-td" colspan="2">
                                            <p style="padding: 0;margin: 0;text-align: left;font-size: 25px;font-weight: 500;">Details for Appointment #'.$appointment_number.'</p><br><br>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="label-td" colspan="2">
                                            <label class="form-label">Session Title: </label>
                                            <p>'.htmlspecialchars($session_title).'</p><br>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="label-td" colspan="2">
                                            <label class="form-label">Lawyer: </label>
                                            <p>'.htmlspecialchars($lawyer_name).'</p><br>
                                        </td>
                                    </tr>';
                                    // Only show lawyer contact details if a lawyer is assigned
                                    if ($lawyer_name !== 'Unassigned') {
                                        echo '<tr>
                                            <td class="label-td" colspan="2">
                                                <label class="form-label">Lawyer Email: </label>
                                                <p>'.htmlspecialchars($lawyer_email).'</p><br>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="label-td" colspan="2">
                                                <label class="form-label">Lawyer Phone: </label>
                                                <p>'.htmlspecialchars($lawyer_tel).'</p><br>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="label-td" colspan="2">
                                                <label class="form-label">Lawyer Specialty: </label>
                                                <p>'.htmlspecialchars($specialty_name).'</p><br>
                                            </td>
                                        </tr>';
                                    }
                                    echo '<tr>
                                        <td class="label-td" colspan="2">
                                            <label class="form-label">Scheduled Date & Time: </label>
                                            <p>'.htmlspecialchars($session_date).' at '.substr(htmlspecialchars($session_time),0,5).'</p><br>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="label-td" colspan="2">
                                            <label class="form-label">Appointment Date (Requested): </label>
                                            <p>'.htmlspecialchars($appointment_date).'</p><br>
                                        </td>
                                    </tr>
                                     <tr>
                                        <td class="label-td" colspan="2">
                                            <label class="form-label">Status: </label>
                                            <p><span class="status-badge status-'.strtolower($current_status).'">'.htmlspecialchars($current_status).'</span></p><br>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="label-td" colspan="2">
                                            <label class="form-label">Case Description: </label>
                                            <p>'.(empty($case_description) ? 'No description provided.' : htmlspecialchars($case_description)).'</p><br>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td colspan="2">
                                            <a href="client-appointment.php"><input type="button" value="OK" class="login-btn btn-primary-soft btn" ></a>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                        </center>
                        <br><br>
                    </div>
                </div>';
            } else {
                echo '
                <div id="popup1" class="overlay">
                    <div class="popup">
                        <center>
                            <h2>Error!</h2>
                            <a class="close" href="client-appointment.php">&times;</a>
                            <div class="content">
                                Appointment details not found or you do not have permission to view this appointment.
                            </div>
                            <div style="display: flex;justify-content: center;">
                                <a href="client-appointment.php" class="non-style-link"><button  class="btn-primary btn"  style="display: flex;justify-content: center;align-items: center;margin:10px;padding:10px;"><font class="tn-in-text">&nbsp;&nbsp;OK&nbsp;&nbsp;</font></button></a>
                            </div>
                        </center>
                    </div>
                </div>';
            }
        }
    }
    ?>
</body>
</html>
