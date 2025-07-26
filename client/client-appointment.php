<?php
    session_start();

    include("../connection.php");
    require_once '../send_email.php';

    // Must be at the top before any HTML output
    if(isset($_SESSION["user"])){
        if(($_SESSION["user"])=="" or $_SESSION['usertype']!='c'){
            header("location: ../login.php");
            exit();
        }
    }else{
        header("location: ../login.php");
        exit();
    }
    
    $clientid = $_SESSION['cid']; 
    $clientname = $_SESSION['cname'];

    // Handle actions that modify data or redirect BEFORE any HTML is output
    if(isset($_GET['action']) && $_GET['action'] == 'confirm_cancel' && isset($_GET['id'])){
        $id = $_GET['id'];
        
        $sql_fetch = "SELECT 
                        a.scheduleid,
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
            $scheduleid_to_delete = $details['scheduleid'];

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
            } catch (Exception $e) {
                error_log("Email sending failed for cancellation of appoid $id: " . $e->getMessage());
            }

            $database->query("DELETE FROM appointment WHERE appoid='$id'");
            $database->query("DELETE FROM schedule WHERE scheduleid='$scheduleid_to_delete'");
            
            header("location: client-appointment.php?action=cancel_success");
            exit();
        } else {
            header("location: client-appointment.php?action=cancel_error");
            exit();
        }
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/animations.css">  
    <link rel="stylesheet" href="../css/main.css">  
    <link rel="stylesheet" href="../css/admin.css"> 
    <link rel="icon" type="image/png" href="../img/logo.png">

    <title>My Appointments | SafeSpace PH</title>
    <style>
         
        .dash-body{
            overflow-y: auto;
        }
        .sub-table{
            animation: transitionIn-Y-bottom 0.5s;
        }
        .status-badge {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 5px;
            font-weight: bold;
  color: #fff !important;
              text-transform: capitalize;
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
        .status-unassigned {
            background-color: #6c757d;  
        }
        
        /* New Modal Styles */
        .overlay {
            position: fixed;
            top: 0;
            bottom: 0;
            left: 0;
            right: 0;
            background: rgba(0, 0, 0, 0.6);
            transition: opacity 500ms;
            visibility: hidden;
            opacity: 0;
            z-index: 1000;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .overlay.show {
            visibility: visible;
            opacity: 1;
        }
        .modal-content {
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 8px 32px rgba(57, 16, 83, 0.15);
            padding: 30px 40px;
            max-width: 650px;
            width: 95%;
            position: relative;
            animation: fadeIn 0.3s;
            max-height: 90vh; 
            overflow-y: auto;
            text-align: center;
        }
        .modal-header {
            text-align: center;
            color: #391053;
            font-size: 1.8rem;
            font-weight: 700;
            margin-bottom: 10px;
            margin-top: 0;
            letter-spacing: 0.5px;
        }
        .modal-divider {
            width: 100%;
            height: 3px;
            background: linear-gradient(90deg, #391053 0%, #5A2675 30%, #9D72B3 65%, #C9A8F1 100%);
            border: none;
            border-radius: 2px;
            margin: 18px 0 28px 0;
        }
        .modal-body {
            text-align: left;
            font-size: 16px;
            color: #333;
        }
        .modal-body .content{
            text-align: center;
            font-size: 1rem;
            line-height: 1.6;
        }
        .modal-footer {
            display: flex;
            justify-content: center;
            gap: 12px;
            margin-top: 25px;
        }
        .modal-btn {
            border: none;
            border-radius: 7px;
            padding: 12px 28px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.2s, box-shadow 0.2s;
        }
        .modal-btn-soft {
            background: #f0e9f7;
            color: #5A2675;
        }
        .modal-btn-soft:hover {
            background: #e2d8fa;
        }
        .modal-btn-danger {
            background-color: #dc3545;
            color: white;
        }
        .modal-btn-danger:hover {
            background-color: #c82333;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: scale(0.95); }
            to { opacity: 1; transform: scale(1); }
        }
        .detail-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px 30px; 
            text-align: left;
        }
        .detail-item {
            font-size: 15px;
        }
        .detail-item strong {
            color: #391053;
            display: block;
            margin-bottom: 4px;
            font-weight: 600;
        }
        .detail-item span {
            color: #555;
        }
        .detail-full {
            grid-column: 1 / -1;
        }

        /* --- STYLES FOR SELECT WITH ICON --- */
        .select-container {
            position: relative;
            display: inline-block;
            vertical-align: middle; /* Aligns dropdown with other form items */
        }
        .select-icon {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            pointer-events: none; /* Lets you click through the icon */
            color: #495057;
            display: flex;
            align-items: center;
        }
        .select-with-icon.input-text {
            -webkit-appearance: none;
            -moz-appearance: none;
            appearance: none;
            width: 180px;
            padding: 8px 30px 8px 10px; /* Adds space on the right for the icon */
            height: 40px;
            margin: 0;
            cursor: pointer;
        }
    </style>
</head>
<body>
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
                    <td class="menu-btn menu-icon-settings">
                        <a href="settings.php" class="non-style-link-menu"><div><p class="menu-text">Settings</p></a></div>
                    </td>
                </tr>
            </table>
        </div>
        <div class="dash-body">
            <?php
            // Notification for various actions
            if(isset($_GET['action'])){
                $action = $_GET['action'];
                $modal_title = '';
                $modal_message = '';
                $modal_header_color = '';

                if($action == 'session-requested' && isset($_GET['title'])){
                    $title = htmlspecialchars(urldecode($_GET['title']));
                    $modal_title = 'Success!';
                    $modal_header_color = '#5A2675';
                    $modal_message = 'Your session request for "<b>'.$title.'</b>" has been submitted successfully.<br>You will be notified via email once a lawyer accepts your request.';
                } elseif ($action == 'error' && isset($_GET['message'])) {
                    $message = htmlspecialchars(urldecode($_GET['message']));
                    $modal_title = 'Error!';
                    $modal_header_color = '#dc3545';
                    $modal_message = $message;
                } elseif ($action == 'cancel_success') {
                    $modal_title = 'Canceled!';
                    $modal_header_color = '#5A2675';
                    $modal_message = 'Your appointment has been canceled successfully.';
                } elseif ($action == 'cancel_error') {
                    $modal_title = 'Error!';
                    $modal_header_color = '#dc3545';
                    $modal_message = 'Could not cancel the appointment. It might not exist or you may not have permission.';
                }

                if (!empty($modal_message)) {
                    echo '
                    <div id="popup1" class="overlay show">
                        <div class="modal-content">
                            <h2 class="modal-header" style="color: '.$modal_header_color.';">'.$modal_title.'</h2>
                            <div class="modal-divider"></div>
                            <div class="modal-body">
                                <div class="content">
                                    '.$modal_message.'
                                </div>
                            </div>
                            <div class="modal-footer">
                                <a href="client-appointment.php" class="non-style-link"><button class="modal-btn modal-btn-soft">OK</button></a>
                            </div>
                        </div>
                    </div>';
                }
            }
            ?>
            <table border="0" width="100%" style=" border-spacing: 0;margin:0;padding:0;margin-top:25px; ">
                
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

                                    $list110 = $database->query("select * from appointment where cid = '$clientid';");
                                    ?>
                                </p>
                            </div>
                        <button class="btn-label" style="display: flex;justify-content: center;align-items: center;"><img src="../img/calendar.svg" width="100%"></button>
                        </div>
                    </td>
                </tr>
                
               
                
                <tr>
                    <td width="50%" style="padding-top:10px;">
                        <p class="heading-main12" style="margin-left: 45px;font-size:18px;color:rgb(49, 49, 49)">All My Appointments (<?php echo $list110->num_rows; ?>)</p>
                    </td>
                    <td colspan="2" style="padding-top:10px; text-align: right; padding-right: 45px;">
                        <form action="" method="post" style="display: inline-flex; gap: 10px; align-items: center;">
                            
                            
                            <input type="date" name="sheduledate" id="date" class="input-text" style="width: auto; padding: 8px 10px;" value="<?php echo isset($_POST['sheduledate']) ? htmlspecialchars($_POST['sheduledate']) : '' ?>">
                            
                            <div class="select-container">
                                <select name="lawyerid" id="lawyerid" class="input-text select-with-icon">
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
                                <div class="select-icon">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"/></svg>
                                </div>
                            </div>
                            
                            <button type="submit" name="filter" class="btn-primary-soft btn" style="display: inline-flex; align-items: center; gap: 6px; padding: 10px 15px; font-weight: 600;">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"></polygon></svg>
                                Filter
                            </button>

                            
                            <a href="client-appointment.php" class="non-style-link btn-primary-soft btn" style="display: inline-flex; align-items: center; gap: 6px; padding: 10px 15px; font-weight: 600;">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="23 4 23 10 17 10"></polyline><path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10"></path></svg>
                                Reset
                            </a>
                        </form>
                    </td>
                </tr>
                
                
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
                                    Action
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
                                        '.substr(htmlspecialchars($lawyername),0,25).'
                                        </td>
                                        <td>
                                        '.substr(htmlspecialchars($title),0,15).'
                                        </td>
                                        <td style="text-align:center;font-size:12px;">
                                            '.substr($scheduledate,0,10).' <br>'.substr($scheduletime,0,5).'
                                        </td>
                                        <td style="text-align:center;">
                                            '.$appodate.'
                                        </td>
                                        <td>
                                            <span class="status-badge status-'.strtolower($status).'">'.htmlspecialchars($status).'</span> </td>
                                        <td>
                                        <div style="display:flex;justify-content: center;">';
                                        

                                        if ($status == 'pending') {
                                            echo '<a href="?action=cancel&id='.$appoid.'&session='.urlencode($title).'&apponum='.$apponum.'" class="non-style-link">
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
                                            // For rejected, completed, or other statuses
                                            echo '<a href="?action=view_details&id='.$appoid.'" class="non-style-link">
                                                    <button class="btn-primary-soft btn button-icon btn-view" style="padding-left: 40px;padding-top: 12px;padding-bottom: 12px;margin-top: 10px;">
                                                        <font class="tn-in-text">View Details</font>
                                                    </button>
                                                </a>';
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

    if(isset($_GET['action'])){
        $action=$_GET["action"];
        // These actions are now handled by the notification system at the top of the dash-body
        if ($action != 'session-requested' && $action != 'error' && $action != 'cancel_success' && $action != 'cancel_error') {
            $id=$_GET["id"];
            if($action=='cancel'){ 
                $session=urldecode($_GET["session"]);
                $apponum=$_GET["apponum"];
                echo '
                <div id="popup1" class="overlay show">
                    <div class="modal-content">
                        <h2 class="modal-header">Are you sure?</h2>
                        <div class="modal-divider"></div>
                        <div class="modal-body">
                           <div class="content">
                                You are about to cancel your appointment request for:<br><br>
                                <strong>'.htmlspecialchars($session).'</strong><br>
                                (Appointment #'.htmlspecialchars($apponum).')
                                <br><br>This action cannot be undone.
                           </div>
                        </div>
                        <div class="modal-footer">
                            <a href="client-appointment.php" class="non-style-link"><button class="modal-btn modal-btn-soft">No, Keep It</button></a>
                            <a href="client-appointment.php?action=confirm_cancel&id='.$id.'" class="non-style-link"><button class="modal-btn modal-btn-danger">Yes, Cancel</button></a>
                        </div>
                    </div>
                </div>
                '; 
            } elseif($action=='view_details'){ 

                $sqlmain_view = "SELECT 
                                    appointment.appoid,
                                    schedule.scheduleid,
                                    schedule.title,
                                    COALESCE(lawyer.lawyername, 'Unassigned') AS lawyername,
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
                                LEFT JOIN lawyer ON schedule.lawyerid = lawyer.lawyerid
                                LEFT JOIN specialties ON lawyer.specialties = specialties.id
                                WHERE appointment.appoid='$id' AND appointment.cid = '$clientid'"; 
                
                $result_view = $database->query($sqlmain_view);
                if ($result_view->num_rows > 0) {
                    $row_view = $result_view->fetch_assoc();
                    
                    echo '
                    <div id="popup1" class="overlay show">
                        <div class="modal-content">
                            <h2 class="modal-header">Appointment Details</h2>
                            <div class="modal-divider"></div>
                            <div class="modal-body">
                                <div class="detail-grid">
                                    <div class="detail-item"><strong>Appointment No:</strong> <span>'.htmlspecialchars($row_view["apponum"]).'</span></div>
                                    <div class="detail-item"><strong>Status:</strong> <span><span class="status-badge status-'.strtolower($row_view["status"]).'">'.ucfirst($row_view["status"]).'</span></span></div>
                                    <div class="detail-item"><strong>Session Title:</strong> <span>'.htmlspecialchars($row_view["title"]).'</span></div>
                                    <div class="detail-item"><strong>Appointment Booked:</strong> <span>'.date("F j, Y", strtotime($row_view["appodate"])).'</span></div>
                                    <div class="detail-item"><strong>Scheduled Date:</strong> <span>'.date("l, F j, Y", strtotime($row_view["scheduledate"])).'</span></div>
                                    <div class="detail-item"><strong>Scheduled Time:</strong> <span>'.date("h:i A", strtotime($row_view["scheduletime"])).'</span></div>
                                    <div class="detail-full" style="height: 1px; background-color: #e0e0e0; margin: 10px 0;"></div>
                                    <div class="detail-item"><strong>Lawyer:</strong> <span>'.htmlspecialchars($row_view["lawyername"]).'</span></div>';
                                    
                                    if ($row_view["lawyername"] !== 'Unassigned') {
                                        echo '<div class="detail-item"><strong>Lawyer Specialty:</strong> <span>'.htmlspecialchars($row_view["specialty_name"]).'</span></div>';
                                        echo '<div class="detail-item"><strong>Lawyer Email:</strong> <span>'.htmlspecialchars($row_view["lawyeremail"]).'</span></div>';
                                        echo '<div class="detail-item"><strong>Lawyer Phone:</strong> <span>'.htmlspecialchars($row_view["lawyertel"]).'</span></div>';
                                    }

                                    echo '<div class="detail-full"><strong>Case Description:</strong>
                                        <div style="background-color: #f8f9fa; border: 1px solid #e0e0e0; border-radius: 5px; padding: 10px; margin-top: 5px;">
                                            '.nl2br(htmlspecialchars($row_view["description"])).'
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <a href="client-appointment.php" class="non-style-link"><button class="modal-btn modal-btn-soft">Close</button></a>
                            </div>
                        </div>
                    </div>';
                } else {
                     echo '
                    <div id="popup1" class="overlay show">
                        <div class="modal-content">
                            <h2 class="modal-header" style="color: #dc3545;">Error!</h2>
                            <div class="modal-divider"></div>
                            <div class="modal-body">
                                <div class="content">
                                    Appointment details not found or you do not have permission to view this.
                                </div>
                            </div>
                            <div class="modal-footer">
                                <a href="client-appointment.php" class="non-style-link"><button class="modal-btn modal-btn-soft">OK</button></a>
                            </div>
                        </div>
                    </div>';
                }
            }
        }
    }
    ?>
</body>
</html>