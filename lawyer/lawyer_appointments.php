<?php
// Ensure all files are included correctly at the top.
session_start();

// Import database connection and email functions
include("../connection.php");

// Authentication: Check if the user is logged in and is a 'lawyer'
if(isset($_SESSION["user"])){
    if(($_SESSION["user"])=="" or $_SESSION['usertype']!='l'){
        header("location: ../login.php");
        exit();
    } else {
        $useremail = $_SESSION["user"];
    }
}else{
    header("location: ../login.php");
    exit();
}

// Get Lawyer Info from session
$lawyerid = $_SESSION['lawyerid'];
$lawyername = $_SESSION['lawyername'];

date_default_timezone_set('Asia/Manila');
$today = date('Y-m-d');
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
    <link rel="icon" type="image/png" href="https://i.ibb.co/qYYZs46L/logo.png">

    <title>My Appointments | SafeSpace PH</title>
    <style>
        .sub-table{
            animation: transitionIn-Y-bottom 0.5s;
        }
        .dash-body{
            overflow-y: auto;
        }
        .status-badge {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 5px;
            font-weight: bold;
            color: #fff;
            font-size: 12px;
        }
         .modal-body .status-badge {
            color: #fff !important; /* Using !important to ensure it overrides any other styles */
        }
        .status-pending { background-color: #ffc107; }
        .status-accepted { background-color: #28a745; }
        .status-rejected { background-color: #dc3545; }
        .status-completed { background-color: #007bff; }
        .status-cancelled { background-color: #6c757d; }

        /* Consistent Modal Styling */
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
        .overlay.active {
            visibility: visible;
            opacity: 1;
        }
        .modal-content {
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 8px 32px rgba(57, 16, 83, 0.15);
            padding: 30px 50px; /* Adjusted vertical padding */
            max-width: 650px;
            width: 95%;
            position: relative;
            animation: fadeIn 0.3s;
            max-height: 90vh; /* Responsive height */
            overflow-y: auto; /* Scrollbar appears only when needed */
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
        .modal-footer {
            display: flex;
            justify-content: flex-end;
            gap: 12px;
            margin-top: 25px; /* Adjusted top margin */
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
        
        /* Aesthetic Improvements */
   
        
        @keyframes fadeIn {
            from { opacity: 0; transform: scale(0.95); }
            to { opacity: 1; transform: scale(1); }
        }
        /* Styles for details view */
        .detail-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px 30px; /* Adjusted row gap */
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
                                    <p class="profile-title"><?php echo htmlspecialchars($lawyername); ?></p>
                                    <p class="profile-subtitle"><?php echo htmlspecialchars($useremail); ?></p>
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
                    <td>
                        <p style="margin-left: 45px; font-size: 23px;font-weight: 600;">My Appointments</p>
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
                                <th class="table-headin">Appt. No</th>
                                <th class="table-headin">Client Name</th>
                                <th class="table-headin">Session Title</th>
                                <th class="table-headin">Scheduled Date & Time</th>
                                <th class="table-headin">Booked On</th>
                                <th class="table-headin">Status</th>
                                <th class="table-headin">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                        
                            <?php
                                $sql_appointments = "SELECT
                                    appointment.appoid,
                                    appointment.apponum,
                                    appointment.appodate,
                                    appointment.status,
                                    schedule.title,
                                    schedule.scheduledate,
                                    schedule.scheduletime,
                                    client.cname
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
                                    </center>
                                    <br><br><br><br>
                                    </td>
                                    </tr>';
                                } else {
                                    while($row_appointment = $result_appointments->fetch_assoc()){
                                        $appoid = $row_appointment["appoid"];
                                        $status = $row_appointment["status"];

                                        echo '<tr>
                                            <td style="text-align:center; font-weight:600; color: #391053;">'.$row_appointment["apponum"].'</td>
                                            <td>'.htmlspecialchars($row_appointment["cname"]).'</td>
                                            <td>'.substr(htmlspecialchars($row_appointment["title"]),0,25).'</td>
                                            <td style="text-align:center;">
                                                '.date("M d, Y", strtotime($row_appointment["scheduledate"])).'<br>'.date("h:i A", strtotime($row_appointment["scheduletime"])).'
                                            </td>
                                            <td style="text-align:center;">'.date("M d, Y", strtotime($row_appointment["appodate"])).'</td>
                                            <td style="text-align:center;">
                                                <span class="status-badge status-'.strtolower($status).'">'.ucfirst($status).'</span> 
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
    // View details modal logic
    if(isset($_GET['action']) && $_GET['action'] == 'view' && isset($_GET['id'])){
        $id = $_GET["id"];
        $sql_view_details = "SELECT 
            appointment.*,
            schedule.title, schedule.scheduledate, schedule.scheduletime,
            client.cname, client.cemail, client.ctel, client.caddress, client.cdob
        FROM appointment 
        INNER JOIN schedule ON appointment.scheduleid = schedule.scheduleid
        INNER JOIN client ON appointment.cid = client.cid
        WHERE appointment.appoid = '$id' AND schedule.lawyerid = '$lawyerid'";

        $result_view_details = $database->query($sql_view_details);

        if($result_view_details->num_rows > 0){
            $row = $result_view_details->fetch_assoc();
            echo '
            <div id="viewModal" class="overlay active">
                <div class="modal-content">
                    <h2 class="modal-header">Appointment Details</h2>
                    <div class="modal-divider"></div>
                    <div class="modal-body">
                        <div class="detail-grid">
                            <div class="detail-item"><strong>Appointment No:</strong> <span>'.htmlspecialchars($row["apponum"]).'</span></div>
                            <div class="detail-item"><strong>Status:</strong> <span><span class="status-badge status-'.strtolower($row["status"]).'">'.ucfirst($row["status"]).'</span></span></div>
                            <div class="detail-item"><strong>Client Name:</strong> <span>'.htmlspecialchars($row["cname"]).'</span></div>
                            <div class="detail-item"><strong>Client Email:</strong> <span>'.htmlspecialchars($row["cemail"]).'</span></div>
                            <div class="detail-item"><strong>Client Phone:</strong> <span>'.htmlspecialchars($row["ctel"]).'</span></div>
                            <div class="detail-item"><strong>Client DOB:</strong> <span>'.date("F j, Y", strtotime($row["cdob"])).'</span></div>
                            <div class="detail-full"><strong>Client Address:</strong> <span>'.htmlspecialchars($row["caddress"]).'</span></div>
                            <div class="detail-divider" style="grid-column: 1 / -1; height: 1px; background-color: #e0e0e0; margin: 10px 0;"></div>
                            <div class="detail-item"><strong>Session Title:</strong> <span>'.htmlspecialchars($row["title"]).'</span></div>
                            <div class="detail-item"><strong>Appointment Booked:</strong> <span>'.date("F j, Y", strtotime($row["appodate"])).'</span></div>
                            <div class="detail-item"><strong>Scheduled Date:</strong> <span>'.date("l, F j, Y", strtotime($row["scheduledate"])).'</span></div>
                            <div class="detail-item"><strong>Scheduled Time:</strong> <span>'.date("h:i A", strtotime($row["scheduletime"])).'</span></div>
                            <div class="detail-full"><strong>Case Description:</strong>
                                <div style="background-color: #f8f9fa; border: 1px solid #e0e0e0; border-radius: 5px; padding: 10px; margin-top: 5px;">
                                    '.nl2br(htmlspecialchars($row["description"])).'
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <a href="lawyer_appointments.php" class="non-style-link"><button type="button" class="modal-btn modal-btn-soft">Close</button></a>
                    </div>
                </div>
            </div>';
        }
    }
    ?>

    <script>
        const viewModal = document.getElementById('viewModal');

        // Close modal if clicked outside of the content area
        window.onclick = function(event) {
            if (event.target == viewModal) {
                // Redirect to close the view modal by removing GET params
                window.location.href = 'lawyer_appointments.php';
            }
        }
    </script>
</body>
</html>
