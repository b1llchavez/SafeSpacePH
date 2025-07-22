<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/animations.css">  
    <link rel="stylesheet" href="../css/main.css">  
    <link rel="stylesheet" href="../css/admin.css"> 
    <link rel="icon" type="image/png" href="https://i.ibb.co/qYYZs46L/logo.png">

    <title>My Appointments | SafeSpace PH</title>
    <style>
        .popup{
            animation: transitionIn-Y-bottom 0.5s;
            max-height: 85vh; /* Set a max-height for the popup */
            overflow-y: auto; /* Allow vertical scrolling within the popup */
        }
        .sub-table{
            animation: transitionIn-Y-bottom 0.5s;
        }
        /* This allows the main content area to scroll if content overflows */
        .dash-body{
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
                    <td class="menu-btn menu-icon-session">
                        <a href="manage-appointments.php" class="non-style-link-menu"><div><p class="menu-text">Share a Safe Space</p></a></div>
                    </td>
                </tr>
                <tr class="menu-row">
                    <td class="menu-btn menu-icon-appoinment menu-active menu-icon-appoinment-active">
                        <a href="lawyer_appointments.php" class="non-style-link-menu non-style-link-menu-active"><div><p class="menu-text menu-text-active">My Appointments</p></a></div>
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
                        <a href="manage-appointments.php" ><button class="login-btn btn-primary-soft btn btn-icon-back" style="padding-top:11px;padding-bottom:11px;margin-left:20px;width:125px"><font class="tn-in-text">Back</font></button></a>
                    </td>
                    <td>
                        <p style="font-size: 23px;padding-left:12px;font-weight: 600;">My Appointments</p>
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
                    <td colspan="4" style="padding-top:10px;width: 100%;">
                        <p class="heading-main12" style="margin-left: 45px;font-size:18px;color:rgb(49, 49, 49)">All My Appointments</p>
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
                                    Appointment Date
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
                                // Fetch all appointments associated with the logged-in lawyer, regardless of status
                                $sql_appointments = "SELECT
                                    appointment.appoid,
                                    appointment.apponum,
                                    appointment.appodate,
                                    appointment.status,
                                    schedule.scheduleid,
                                    schedule.title,
                                    schedule.scheduledate,
                                    schedule.scheduletime,
                                    client.cname,
                                    client.cemail,
                                    client.ctel,
                                    client.caddress,
                                    client.cdob
                                FROM appointment 
                                INNER JOIN schedule ON appointment.scheduleid = schedule.scheduleid
                                INNER JOIN client ON appointment.cid = client.cid
                                WHERE schedule.lawyerid = '$lawyerid'
                                ORDER BY schedule.scheduledate DESC, schedule.scheduletime DESC";

                                $result_appointments = $database->query($sql_appointments);

                                if($result_appointments->num_rows == 0){
                                    echo '<tr>
                                    <td colspan="7"> <br><br><br><br>
                                    <center>
                                    <img src="../img/notfound.svg" width="25%">
                                    
                                    <br>
                                    <p class="heading-main12" style="margin-left: 45px;font-size:20px;color:rgb(49, 49, 49)">No appointments found!</p>
                                    <a class="non-style-link" href="manage-appointments.php"><button  class="login-btn btn-primary-soft btn"  style="display: flex;justify-content: center;align-items: center;margin-left:20px;">&nbsp; Back to Dashboard &nbsp;</font></button>
                                    </a>
                                    </center>
                                    <br><br><br><br>
                                    </td>
                                    </tr>';
                                    
                                } else {
                                    for ( $x=0; $x<$result_appointments->num_rows;$x++){
                                        $row_appointment = $result_appointments->fetch_assoc();
                                        $appoid = $row_appointment["appoid"];
                                        $apponum = $row_appointment["apponum"];
                                        $appodate_made = $row_appointment["appodate"];
                                        $status = $row_appointment["status"];
                                        $title = $row_appointment["title"];
                                        $scheduledate = $row_appointment["scheduledate"];
                                        $scheduletime = $row_appointment["scheduletime"];
                                        $clientname = $row_appointment["cname"];

                                        echo '<tr>
                                            <td style="text-align:center;">'.$apponum.'</td>
                                            <td>'.htmlspecialchars($clientname).'</td>
                                            <td>'.substr(htmlspecialchars($title),0,15).'</td>
                                            <td style="text-align:center;">
                                                '.htmlspecialchars($scheduledate).'<br>at '.substr(htmlspecialchars($scheduletime),0,5).'
                                            </td>
                                            <td style="text-align:center;">'.htmlspecialchars($appodate_made).'</td>
                                            <td>
                                                <span class="status-badge status-'.strtolower($status).'">'.$status.'</span> 
                                            </td>
                                            <td>
                                                <div style="display:flex;justify-content: center;">
                                                    <a href="?action=view&id='.$appoid.'" class="non-style-link">
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

    <?php
    // View details popup
    if($_GET){
        $id=$_GET["id"];
        $action=$_GET["action"];
        if($action=='view'){
            $sql_view_details = "SELECT 
                appointment.appoid,
                appointment.apponum,
                appointment.appodate,
                appointment.status,
                appointment.description AS case_description,
                schedule.scheduleid,
                schedule.title,
                schedule.scheduledate,
                schedule.scheduletime,
                client.cname,
                client.cemail,
                client.ctel,
                client.caddress,
                client.cdob
            FROM appointment 
            INNER JOIN schedule ON appointment.scheduleid = schedule.scheduleid
            INNER JOIN client ON appointment.cid = client.cid
            WHERE appointment.appoid = '$id' AND schedule.lawyerid = '$lawyerid'"; // Ensure lawyer can only view their own appointments

            $result_view_details = $database->query($sql_view_details);

            if($result_view_details->num_rows == 0){
                echo '<div id="popup1" class="overlay">
                    <div class="popup">
                        <center>
                            <h2>Error!</h2>
                            <a class="close" href="lawyer_appointments.php">&times;</a>
                            <div class="content">
                                Appointment details not found or you do not have permission to view this appointment.
                            </div>
                            <div style="display: flex;justify-content: center;">
                                <a href="lawyer_appointments.php" class="non-style-link"><button  class="btn-primary btn"  style="display: flex;justify-content: center;align-items: center;margin:10px;padding:10px;"><font class="tn-in-text">&nbsp;&nbsp;OK&nbsp;&nbsp;</font></button></a>
                            </div>
                        </center>
                    </div>
                </div>';
            } else {
                $row_details = $result_view_details->fetch_assoc();
                $appoid_details = $row_details["appoid"];
                $apponum_details = $row_details["apponum"];
                $appodate_made_details = $row_details["appodate"];
                $status_details = $row_details["status"];
                $title_details = $row_details["title"];
                $scheduledate_details = $row_details["scheduledate"];
                $scheduletime_details = $row_details["scheduletime"];
                $case_description_details = $row_details["case_description"];
                $clientname_details = $row_details["cname"];
                $clientemail_details = $row_details["cemail"];
                $clienttel_details = $row_details["ctel"];
                $clientaddress_details = $row_details["caddress"];
                $clientdob_details = $row_details["cdob"];


                echo '
                <div id="popup1" class="overlay">
                        <div class="popup">
                        <center>
                            <a class="close" href="lawyer_appointments.php">&times;</a>
                            <div class="content">
                                <br>
                                <img src="../img/lawyers/lawyer_icon.png" alt="" width="50%" style="border-radius: 50%;">
                                <br><br>
                                
                                <p style="padding: 0;margin: 0;text-align: left;font-size: 25px;font-weight: 500;">Appointment Details</p>
                                <br><br>
                                <table width="80%" class="sub-table scrolldown add-doc-form-container" border="0">
                                
                                    <tr>
                                        <td>
                                            <p style="padding: 0;margin: 0;text-align: left;font-size: 14px;font-weight: 600;">Appointment Number: </p>
                                        </td>
                                        <td>
                                            <p style="padding: 0;margin: 0;text-align: left;font-size: 14px;font-weight: 600;">'.$apponum_details.'</p>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="label-td" colspan="2">
                                            <label for="name" class="form-label">Client Name: </label>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="label-td" colspan="2">
                                            '.htmlspecialchars($clientname_details).'<br><br>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="label-td" colspan="2">
                                            <label for="title" class="form-label">Session Title: </label>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="label-td" colspan="2">
                                            '.htmlspecialchars($title_details).'<br><br>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="label-td" colspan="2">
                                            <label for="scheduledate" class="form-label">Scheduled Date: </label>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="label-td" colspan="2">
                                            '.htmlspecialchars($scheduledate_details).'<br><br>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="label-td" colspan="2">
                                            <label for="scheduletime" class="form-label">Scheduled Time: </label>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="label-td" colspan="2">
                                            '.substr(htmlspecialchars($scheduletime_details),0,5).'<br><br>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="label-td" colspan="2">
                                            <label for="appodate_made" class="form-label">Appointment Made On: </label>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="label-td" colspan="2">
                                            '.htmlspecialchars($appodate_made_details).'<br><br>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="label-td" colspan="2">
                                            <label for="status" class="form-label">Status: </label>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="label-td" colspan="2">
                                            <span class="status-badge status-'.strtolower($status_details).'">'.$status_details.'</span><br><br>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="label-td" colspan="2">
                                            <label for="case_description" class="form-label">Case Description: </label>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="label-td" colspan="2">
                                            '.nl2br(htmlspecialchars($case_description_details)).'<br><br>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="label-td" colspan="2">
                                            <label for="client_contact" class="form-label">Client Contact: </label>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="label-td" colspan="2">
                                            Email: '.htmlspecialchars($clientemail_details).'<br>
                                            Phone: '.htmlspecialchars($clienttel_details).'<br><br>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td colspan="2">
                                            <a href="lawyer_appointments.php"><input type="button" value="OK" class="login-btn btn-primary-soft btn" ></a>
                                        </td>
                                    </tr>
                                </table>
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
