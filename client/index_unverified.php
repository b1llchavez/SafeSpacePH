<?php
session_start(); // session_start() must be called before any output

// Set the timezone to Asia/Manila for Quezon City, Philippines
date_default_timezone_set('Asia/Manila');

// Check if the user is logged in and is of type 'u' (unverified client)
if(isset($_SESSION["user"])){
    if(($_SESSION["user"])=="" or $_SESSION['usertype']!='u'){
        // If user is not logged in or is not an unverified client, redirect to login page
        header("location: ../login.php");
        exit(); // Always exit after a header redirect
    }else{
        $useremail=$_SESSION["user"]; // Set useremail if session is valid
    }
}else{
    // If session is not set, redirect to login page
    header("location: ../login.php");
    exit(); // Always exit after a header redirect
}

// Include database connection
include("../connection.php");

// Retrieve client ID and name from session
// Ensure these session variables are set during login for 'u' type users
$clientid = isset($_SESSION['cid']) ? $_SESSION['cid'] : null; 
$clientname = isset($_SESSION['cname']) ? $_SESSION['cname'] : 'Guest';

// Fetch user details from the database
$userrow = $database->query("select * from client where cemail='$useremail'");
$userfetch=$userrow->fetch_assoc();
$userid= $userfetch["cid"];
$username=$userfetch["cname"];
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
    <title>Dashboard | SafeSpace PH</title>
    <style>
        .dashbord-tables{
            animation: transitionIn-Y-over 0.5s;
        }
        .filter-container{
            animation: transitionIn-Y-bottom  0.5s;
        }
        .sub-table,.anime{
            animation: transitionIn-Y-bottom 0.5s;
        }
        /* Styles for the verification modal */
        .modal-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5); /* Semi-transparent background */
            z-index: 1000; /* Ensure it's on top */
            justify-content: center;
            align-items: center;
        }

        .modal-content {
            background-color: white;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.2);
            width: 80%;
            max-width: 500px;
            text-align: center;
        }

        .modal-header {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 10px;
        }

        .modal-body {
            font-size: 16px;
            margin-bottom: 20px;
        }

        .modal-button {
            background-color: #007bff;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
        }

        /* Verification Modal Styles */
        #verify-modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(57, 16, 83, 0.6);
            z-index: 1000;
            justify-content: center;
            align-items: center;
            backdrop-filter: blur(4px);
        }

        .verify-content {
            background-color: white;
            padding: 35px;
            border-radius: 12px;
            width: 90%;
            max-width: 520px;
            text-align: center;
            animation: modalFadeIn 0.3s ease-out;
        }

        .verify-header {
            font-size: 24px;
            font-weight: 600;
            margin-bottom: 20px;
            color: rgb(49, 49, 49);
        }

        .verify-body {
            font-size: 16px;
            margin-bottom: 25px;
            color: rgb(88, 88, 88);
        }

        .verify-button {
            background: linear-gradient(90deg, #391053 0%, #5A2675 100%);
            padding: 14px 38px;
            font-size: 16px;
            font-weight: 600;
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            box-shadow: 0 4px 12px rgba(57, 16, 83, 0.2);
            transition: all 0.3s ease;
        }

        .verify-button:hover {
            background: linear-gradient(90deg, #5A2675 0%, #391053 100%) !important;
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(57, 16, 83, 0.3) !important;
        }

        @keyframes modalFadeIn {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        /* Styles for the logout button, adapted to match verify button's dimensions */
        .logout-modal-btn {
            padding: 14px 38px; /* Same padding as verify-button */
            font-size: 16px; /* Same font size as verify-button */
            font-weight: 600; /* Same font weight as verify-button */
            color: #391053; /* Dark text for contrast */
            background-color: #f0f0f0; /* Light grey background */
            border: 1px solid #ccc; /* Light border */
            border-radius: 8px; /* Same border-radius as verify-button */
            cursor: pointer;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1); /* Subtle shadow */
            transition: all 0.3s ease;
            margin-top: 15px; /* Space between verify and logout buttons */
        }

        .logout-modal-btn:hover {
            background-color: #e0e0e0; /* Darker grey on hover */
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.15);
        }
    </style>
</head>
<body>
    <!-- Enhanced Verification Modal -->
    <div id="verify-modal">
        <div class="verify-content" style="box-shadow: 0 8px 32px rgba(57, 16, 83, 0.15); border: 1px solid #e9d5ff;">
            <img src="../img/logo.png" alt="SafeSpacePH Logo" style="width:70px; margin-bottom:20px; border-radius:50%; box-shadow:0 2px 8px rgba(57, 16, 83, 0.12);">
            <div class="verify-header" style="color:#391053; font-size:28px; font-weight:800; letter-spacing:0.5px;">
                Help Us Keep SafeSpace PH Secure
            </div>
            <div class="verify-body" style="font-size:17px; color:#444; margin-bottom:30px; line-height:1.6;">
                To protect our community and ensure a safe environment for everyone, please verify your account by uploading a valid government-issued ID (such as PhilSys, Passport, or Driver's License).<br>
                <span style="display:block; margin-top:12px; color:#5A2675; font-weight:500;">
                    Your information is kept private and secure. Verification helps us keep SafeSpace PH a trusted place for all.
                </span>
            </div>
            <button onclick="location.href='../identity_verification.php'" class="verify-button">
                Verify Now
            </button>
            <!-- Logout button added here -->
            <button type="button" onclick="window.location.href='../logout.php';" class="logout-modal-btn">
                Log out
            </button>
        </div>
    </div>

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
                    <td class="menu-btn menu-icon-home menu-active menu-icon-home-active" >
                        <a href="index.php" class="non-style-link-menu non-style-link-menu-active"><div><p class="menu-text menu-text-active">Home</p></a></div></a>
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
                    <td class="menu-btn menu-icon-session">
                        <a href="client-appointment.php" class="non-style-link-menu"><div><p class="menu-text">My Appointments</p></a></div>
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
        <div class="dash-body" style="margin-top: 15px">
            <table border="0" width="100%" style=" border-spacing: 0;margin:0;padding:0;" >
                <tr>
                    <td colspan="1" class="nav-bar" >
                        <p style="font-size: 23px;padding-left:12px;font-weight: 600;margin-left:20px;">Home</p>
                    </td>
                    <td width="25%"></td>
                    <td width="15%">
                        <p style="font-size: 14px;color: rgb(119, 119, 119);padding: 0;margin: 0;text-align: right;">
                            Today's Date
                        </p>
                        <p class="heading-sub12" style="padding: 0;margin: 0;">
                            <?php 
                                $today = date('Y-m-d');
                                echo $today;

                                $clientrow = $database->query("select  * from  client;");
                                $lawyerrow = $database->query("select  * from  lawyer;");
                                $appointmentrow = $database->query("select  * from  appointment where appodate>='$today';");
                                $schedulerow = $database->query("select  * from  schedule where scheduledate='$today';");
                            ?>
                        </p>
                    </td>
                    <td width="10%">
                        <button  class="btn-label"  style="display: flex;justify-content: center;align-items: center;"><img src="../img/calendar.svg" width="100%"></button>
                    </td>
                </tr>
                <tr>
                    <td colspan="4" >
                        <center>
                            <table class="filter-container lawyer-header client-header" style="border: none;width:95%" border="0" >
                                <tr>
                                    <td>
                                        <h3>Welcome!</h3>
                                        <h1><?php echo $username  ?>.</h1>
                                        <p>Haven't any idea about lawyers? no problem let's jumping to 
                                            <a href="lawyers.php" class="non-style-link"><b>"All Lawyers"</b></a> section or 
                                            <a href="schedule.php" class="non-style-link"><b>"Sessions"</b> </a><br>
                                            Track your past and future appointments history.<br>Also find out the expected arrival time of your lawyer or medical consultant.<br><br>
                                        </p>
                                        <h3>Channel a Lawyer Here</h3>
                                        <form action="schedule.php" method="post" style="display: flex">
                                            <input type="search" name="search" class="input-text " placeholder="Search Lawyer and We will Find The Session Available" list="lawyers" style="width:45%;">&nbsp;&nbsp;
                                            <?php
                                                echo '<datalist id="lawyers">';
                                                $list11 = $database->query("select  lawyername,lawyeremail from  lawyer;");
                                                for ($y=0;$y<$list11->num_rows;$y++){
                                                    $row00=$list11->fetch_assoc();
                                                    $d=$row00["lawyername"];
                                                    echo "<option value='$d'><br/>";
                                                };
                                                echo ' </datalist>';
                                            ?>
                                            <input type="Submit" value="Search" class="login-btn btn-primary btn" style="padding-left: 25px;padding-right: 25px;padding-top: 10px;padding-bottom: 10px;">
                                            <br><br>
                                        </form>
                                    </td>
                                </tr>
                            </table>
                        </center>
                    </td>
                </tr>
                <tr>
                    <td colspan="4">
                        <table border="0" width="100%">
                            <tr>
                                <td width="50%">
                                    <center>
                                        <table class="filter-container" style="border: none;" border="0">
                                            <tr>
                                                <td colspan="4">
                                                    <p style="font-size: 20px;font-weight:600;padding-left: 12px;">Status</p>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td style="width: 25%;">
                                                    <div  class="dashboard-items"  style="padding:20px;margin:auto;width:95%;display: flex">
                                                        <div>
                                                            <div class="h1-dashboard">
                                                                <?php    echo $lawyerrow->num_rows  ?>
                                                            </div><br>
                                                            <div class="h3-dashboard">
                                                                All Lawyers &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                                            </div>
                                                        </div>
                                                        <div class="btn-icon-back dashboard-icons" style="background-image: url('../img/icons/lawyers-hover.svg');"></div>
                                                    </div>
                                                </td>
                                                <td style="width: 25%;">
                                                    <div  class="dashboard-items"  style="padding:20px;margin:auto;width:95%;display: flex;">
                                                        <div>
                                                            <div class="h1-dashboard">
                                                                <?php    echo $clientrow->num_rows  ?>
                                                            </div><br>
                                                            <div class="h3-dashboard">
                                                                All Clients &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                                            </div>
                                                        </div>
                                                        <div class="btn-icon-back dashboard-icons" style="background-image: url('../img/icons/clients-hover.svg');"></div>
                                                    </div>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td style="width: 25%;">
                                                    <div  class="dashboard-items"  style="padding:20px;margin:auto;width:95%;display: flex; ">
                                                        <div>
                                                            <div class="h1-dashboard" >
                                                                <?php    echo $appointmentrow ->num_rows  ?>
                                                            </div><br>
                                                            <div class="h3-dashboard" >
                                                                NewBooking &nbsp;&nbsp;
                                                            </div>
                                                        </div>
                                                        <div class="btn-icon-back dashboard-icons" style="margin-left: 0px;background-image: url('../img/icons/book-hover.svg');"></div>
                                                    </div>
                                                </td>
                                                <td style="width: 25%;">
                                                    <div  class="dashboard-items"  style="padding:20px;margin:auto;width:95%;display: flex;padding-top:21px;padding-bottom:21px;">
                                                        <div>
                                                            <div class="h1-dashboard">
                                                                <?php    echo $schedulerow ->num_rows  ?>
                                                            </div><br>
                                                            <div class="h3-dashboard" style="font-size: 15px">
                                                                Today Sessions
                                                            </div>
                                                        </div>
                                                        <div class="btn-icon-back dashboard-icons" style="background-image: url('../img/icons/session-iceblue.svg');"></div>
                                                    </div>
                                                </td>
                                            </tr>
                                        </table>
                                    </center>
                                </td>
                                <td>
                                    <p style="font-size: 20px;font-weight:600;padding-left: 40px;" class="anime">Your Upcoming Booking</p>
                                    <center>
                                        <div class="abc scroll" style="height: 250px;padding: 0;margin: 0;">
                                            <table width="85%" class="sub-table scrolldown" border="0" >
                                                <thead>
                                                    <tr>
                                                        <th class="table-headin">Appoint. Number</th>
                                                        <th class="table-headin">Session Title</th>
                                                        <th class="table-headin">Lawyer</th>
                                                        <th class="table-headin">Sheduled Date & Time</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php
                                                        $nextweek=date("Y-m-d",strtotime("+1 week"));
                                                        $sqlmain= "select * from schedule inner join appointment on schedule.scheduleid=appointment.scheduleid inner join client on client.cid=appointment.cid inner join lawyer on schedule.lawyerid=lawyer.lawyerid  where  client.cid=$userid  and schedule.scheduledate>='$today' order by schedule.scheduledate asc";
                                                        $result= $database->query($sqlmain);
                                                        if($result->num_rows==0){
                                                            echo '<tr>
                                                                    <td colspan="4">
                                                                        <br><br><br><br>
                                                                        <center>
                                                                            <img src="../img/notfound.svg" width="25%">
                                                                            <br>
                                                                            <p class="heading-main12" style="margin-left: 45px;font-size:20px;color:rgb(49, 49, 49)">Nothing to show here!</p>
                                                                            <a class="non-style-link" href="schedule.php"><button  class="login-btn btn-primary-soft btn"  style="display: flex;justify-content: center;align-items: center;margin-left:20px;">&nbsp; Channel a Lawyer &nbsp;</font></button>
                                                                            </a>
                                                                        </center>
                                                                        <br><br><br><br>
                                                                    </td>
                                                                </tr>';
                                                        } else {
                                                            for ( $x=0; $x<$result->num_rows;$x++){
                                                                $row=$result->fetch_assoc();
                                                                $scheduleid=$row["scheduleid"];
                                                                $title=$row["title"];
                                                                $apponum=$row["apponum"];
                                                                $lawyername=$row["lawyername"];
                                                                $scheduledate=$row["scheduledate"];
                                                                $scheduletime=$row["scheduletime"];
                                                                echo '<tr>
                                                                        <td style="padding:30px;font-size:25px;font-weight:700;"> &nbsp;'.$apponum.'</td>
                                                                        <td style="padding:20px;"> &nbsp;'.$title.'</td>
                                                                        <td style="padding:20px;"> &nbsp;'.$lawyername.'</td>
                                                                        <td style="padding:20px;"> &nbsp;'.$scheduledate.' '.$scheduletime.'</td>
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
                    </td>
                </tr>
            </table>
        </div>
    </div>

    <!-- Add this script at the bottom of the body -->
    <script>
        // Simulate verification status (replace with actual logic later)
        const isVerified = false;

        // Show verification modal if user is not verified
        window.onload = function() {
            if (!isVerified) {
                document.getElementById('verify-modal').style.display = 'flex';
            }
        };
    </script>
</body>
</html>
