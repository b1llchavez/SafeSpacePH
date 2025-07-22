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

    $lawyerid = $_SESSION['lawyerid']; // Lawyer ID from session
    $lawyername = $_SESSION['lawyername']; // Lawyer name from session
    $lawyeremail = $_SESSION['user']; // Lawyer email from session

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
                    <td colspan="4" style="padding-top:20px;width: 100%;">
                        <p class="heading-main12" style="margin-left: 45px;font-size:18px;color:rgb(49, 49, 49)">New Session Requests</p>
                    </td>
                </tr>
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
                                                    <a href="process_lawyer_action.php?action=accept&appoid='.$appoid.'&scheduleid='.$scheduleid.'&lawyerid='.$lawyerid.'" class="non-style-link">
                                                        <button class="btn-primary-soft btn button-icon btn-view" style="padding-left: 20px;padding-right: 20px;padding-top: 12px;padding-bottom: 12px;margin-top: 10px;">
                                                            <font class="tn-in-text">Accept</font>
                                                        </button>
                                                    </a>
                                                    &nbsp;&nbsp;&nbsp;
                                                    <a href="process_lawyer_action.php?action=reject&appoid='.$appoid.'" class="non-style-link">
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

                <tr>
                    <td colspan="4" style="padding-top:40px;width: 100%;">
                        <p class="heading-main12" style="margin-left: 45px;font-size:18px;color:rgb(49, 49, 49)">My Upcoming Sessions</p>
                    </td>
                </tr>
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
                                    Client Name
                                </th>
                                <th class="table-headin">
                                    Session Title
                                </th>
                                <th class="table-headin" style="font-size:10px">
                                    Scheduled Date & Time
                                </th>
                                <th class="table-headin">
                                    Status
                                </th>
                                <th class="table-headin">
                                    Events
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                        
                            <?php
                                // Fetch sessions assigned to this lawyer, status is 'accepted' or 'completed' and scheduled in the future
                                $sql_upcoming_sessions = "SELECT 
                                    appointment.appoid,
                                    appointment.apponum,
                                    appointment.appodate,
                                    appointment.status,
                                    schedule.scheduleid,
                                    schedule.title,
                                    schedule.scheduledate,
                                    schedule.scheduletime,
                                    client.cname
                                FROM appointment 
                                INNER JOIN schedule ON appointment.scheduleid = schedule.scheduleid
                                INNER JOIN client ON appointment.cid = client.cid
                                WHERE schedule.lawyerid = '$lawyerid' AND appointment.status IN ('accepted') AND schedule.scheduledate >= '$today'
                                ORDER BY schedule.scheduledate ASC, schedule.scheduletime ASC";

                                $result_upcoming_sessions = $database->query($sql_upcoming_sessions);

                                if($result_upcoming_sessions->num_rows == 0){
                                    echo '<tr>
                                    <td colspan="6"> <br><br><br><br>
                                    <center>
                                    <img src="../img/notfound.svg" width="25%">
                                    
                                    <br>
                                    <p class="heading-main12" style="margin-left: 45px;font-size:20px;color:rgb(49, 49, 49)">No upcoming sessions found!</p>
                                    <a class="non-style-link" href="manage-appointments.php"><button  class="login-btn btn-primary-soft btn"  style="display: flex;justify-content: center;align-items: center;margin-left:20px;">&nbsp; Refresh &nbsp;</font></button>
                                    </a>
                                    </center>
                                    <br><br><br><br>
                                    </td>
                                    </tr>';
                                    
                                } else {
                                    for ( $x=0; $x<$result_upcoming_sessions->num_rows;$x++){
                                        $row_upcoming = $result_upcoming_sessions->fetch_assoc();
                                        $appoid = $row_upcoming["appoid"];
                                        $apponum = $row_upcoming["apponum"];
                                        $title = $row_upcoming["title"];
                                        $scheduledate = $row_upcoming["scheduledate"];
                                        $scheduletime = $row_upcoming["scheduletime"];
                                        $clientname = $row_upcoming["cname"];
                                        $status = $row_upcoming["status"];

                                        echo '<tr>
                                            <td style="text-align:center;">'.$apponum.'</td>
                                            <td>'.htmlspecialchars($clientname).'</td>
                                            <td>'.substr(htmlspecialchars($title),0,15).'</td>
                                            <td style="text-align:center;">
                                                '.htmlspecialchars($scheduledate).'<br>Starts: <b>@'.substr(htmlspecialchars($scheduletime),0,5).'</b>
                                            </td>
                                            <td>
                                                <span class="status-badge status-'.strtolower($status).'">'.$status.'</span> </td>
                                            <td>
                                                <div style="display:flex;justify-content: center;">
                                                    <a href="lawyer_appointments.php?action=view&id='.$appoid.'" class="non-style-link">
                                                        <button class="btn-primary-soft btn button-icon btn-view" style="padding-left: 40px;padding-top: 12px;padding-bottom: 12px;margin-top: 10px;">
                                                            <font class="tn-in-text">View Details</font>
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
</body>
</html>