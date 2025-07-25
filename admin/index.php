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
        .dashbord-tables {
            animation: transitionIn-Y-over 0.5s;
        }
        .filter-container {
            animation: transitionIn-Y-bottom 0.5s;
        }
        .sub-table {
            animation: transitionIn-Y-bottom 0.5s;
        }
         
        .dash-body {
            overflow-y: auto;
        }
    </style>
</head>

<body>
    <?php
    session_start();
    if (isset($_SESSION["user"])) {
        if (($_SESSION["user"]) == "" or $_SESSION['usertype'] != 'a') {
            header("location: ../login.php");
        }
    } else {
        header("location: ../login.php");
    }
    include("../connection.php");
    ?>
    <div class="container">
        <div class="menu">
            <table class="menu-container" border="0">
                <tr>
                    <td style="padding:10px" colspan="2">
                        <table border="0" class="profile-container">
                            <tr>
                                <td width="30%" style="padding-left:20px">
                                    <img src="../img/user.png" alt="" width="100%" style="border-radius:50%">
                                </td>
                                <td style="padding:0px;margin:0px;">
                                    <p class="profile-title">Administrator</p>
                                    <p class="profile-subtitle">admin@safespaceph.com</p>
                                </td>
                            </tr>
                            <tr>
                                <td colspan="2">
                                    <a href="../logout.php">
                                        <input type="button" value="Log out" class="logout-btn btn-primary-soft btn">
                                    </a>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
                <tr class="menu-row">
                    <td class="menu-btn menu-icon-dashbord menu-active menu-icon-dashbord-active">
                        <a href="index.php" class="non-style-link-menu non-style-link-menu-active">
                            <div>
                                <p class="menu-text menu-text-active">Dashboard</p>
                            </div>
                        </a>
                    </td>
                </tr>
                <tr class="menu-row">
                    <td class="menu-btn menu-icon-report">
                        <a href="admin_reports.php" class="non-style-link-menu">
                            <div>
                                <p class="menu-text">Violation Reports</p>
                            </div>
                        </a>
                    </td>
                </tr>
                <tr class="menu-row">
                    <td class="menu-btn menu-icon-appoinment">
                        <a href="appointment.php" class="non-style-link-menu">
                            <div>
                                <p class="menu-text">Appointments</p>
                            </div>
                        </a>
                    </td>
                </tr>
                <tr class="menu-row">
                    <td class="menu-btn menu-icon-client-verification">
                        <a href="client_verification.php" class="non-style-link-menu">
                            <div>
                                <p class="menu-text">Client Verification</p>
                            </div>
                        </a>
                    </td>
                </tr>
                <tr class="menu-row">
                    <td class="menu-btn menu-icon-lawyer-verification">
                        <a href="lawyer_verification.php" class="non-style-link-menu"><div><p class="menu-text">Lawyer Verification</p></div></a>
                    </td>
                </tr>
                <tr class="menu-row">
                    <td class="menu-btn menu-icon-lawyers">
                        <a href="lawyers.php" class="non-style-link-menu">
                            <div>
                                <p class="menu-text">All Lawyers</p>
                            </div>
                        </a>
                    </td>
                </tr>
                <tr class="menu-row">
                    <td class="menu-btn menu-icon-client">
                        <a href="client.php" class="non-style-link-menu">
                            <div>
                                <p class="menu-text">All Clients</p>
                            </div>
                        </a>
                    </td>
                </tr>
            </table>
        </div>
        <div class="dash-body" style="margin-top: 15px">
            <table border="0" width="100%" style="border-spacing: 0;margin:0;padding:0;">
                <tr>
                    <td colspan="2" class="nav-bar">
                        <form action="lawyers.php" method="post" class="header-search">
                            <input type="search" name="search" class="input-text header-searchbar"
                                placeholder="Search Lawyer name or Email" list="lawyers">&nbsp;&nbsp;
                            <?php
                            echo '<datalist id="lawyers">';
                            $list11 = $database->query("SELECT lawyername, lawyeremail FROM lawyer;");
                            for ($y = 0; $y < $list11->num_rows; $y++) {
                                $row00 = $list11->fetch_assoc();
                                $l = $row00["lawyername"];
                                $c = $row00["lawyeremail"];
                                echo "<option value='$l'><br/>";
                                echo "<option value='$c'><br/>";
                            }
                            echo '</datalist>';
                            ?>
                            <input type="Submit" value="Search" class="login-btn btn-primary-soft btn"
                                style="padding-left: 25px;padding-right: 25px;padding-top: 10px;padding-bottom: 10px;">
                        </form>
                    </td>
                    <td width="15%">
                        <p style="font-size: 14px;color: rgb(119, 119, 119);padding: 0;margin: 0;text-align: right;">
                            Today's Date
                        </p>
                        <p class="heading-sub12" style="padding: 0;margin: 0;">
                            <?php
                            date_default_timezone_set('Asia/Kolkata');
                            $today = date('Y-m-d');
                            echo $today;
                            $clientrow = $database->query("SELECT * FROM client;");
                            $lawyerrow = $database->query("SELECT * FROM lawyer;");
                            $appointmentrow = $database->query("SELECT * FROM appointment WHERE appodate >= '$today';");
                            $schedulerow = $database->query("SELECT * FROM schedule WHERE scheduledate = '$today';");
                            ?>
                        </p>
                    </td>
                    <td width="10%">
                        <button class="btn-label" style="display: flex;justify-content: center;align-items: center;">
                            <img src="../img/calendar.svg" width="100%">
                        </button>
                    </td>
                </tr>
                <tr>
                    <td colspan="4">
                        <center>
                            <table class="filter-container" style="border: none;" border="0">
                                <tr>
                                    <td colspan="4">
                                        <p style="font-size: 20px;font-weight:600;padding-left: 12px;">Status</p>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="width: 25%;">
                                        <div class="dashboard-items" style="padding:20px;margin:auto;width:95%;display: flex;justify-content: space-between;align-items: center;">
                                            <div>
                                                <div class="h1-dashboard">
                                                    <?php echo $lawyerrow->num_rows ?>
                                                </div><br>
                                                <div class="h3-dashboard">
                                                    Lawyers
                                                </div>
                                            </div>
                                            <div class="btn-icon-back dashboard-icons"
                                                style="background-image: url('../img/icons/lawyers-hover.svg');"></div>
                                        </div>
                                    </td>
                                    <td style="width: 25%;">
                                        <div class="dashboard-items" style="padding:20px;margin:auto;width:95%;display: flex;justify-content: space-between;align-items: center;">
                                            <div>
                                                <div class="h1-dashboard">
                                                    <?php echo $clientrow->num_rows ?>
                                                </div><br>
                                                <div class="h3-dashboard">
                                                    Clients
                                                </div>
                                            </div>
                                            <div class="btn-icon-back dashboard-icons"
                                                style="background-image: url('../img/icons/clients-hover.svg');"></div>
                                        </div>
                                    </td>
                                    <td style="width: 25%;">
                                        <div class="dashboard-items" style="padding:20px;margin:auto;width:95%;display: flex;justify-content: space-between;align-items: center;">
                                            <div>
                                                <div class="h1-dashboard">
                                                    <?php echo $appointmentrow->num_rows ?>
                                                </div><br>
                                                <div class="h3-dashboard">
                                                    New Booking
                                                </div>
                                            </div>
                                            <div class="btn-icon-back dashboard-icons"
                                                style="margin-left: 0px; background-image: url('../img/icons/newbookings-hover.svg')">
                                            </div>
                                        </div>
                                    </td>
                                    <td style="width: 25%;">
                                       <div class="dashboard-items" style="padding:20px;margin:auto;width:95%;display: flex;justify-content: space-between;align-items: center;">
                                            <div>
                                                <div class="h1-dashboard">
                                                    <?php echo $schedulerow->num_rows ?>
                                                </div><br>
                                                <div class="h3-dashboard">
                                                    Sessions
                                                </div>
                                            </div>
                                            <div class="btn-icon-back dashboard-icons"
                                                style="background-image: url('../img/icons/sessions-hover.svg');"></div>
                                        </div>
                                    </td>
                                </tr>
                            </table>
                        </center>
                    </td>
                </tr>
                <tr>
                    <td colspan="4">
                        <table width="100%" border="0" class="dashbord-tables">
                            <tr>
                                <td>
                                    <p style="padding:10px;padding-left:48px;padding-bottom:0;font-size:23px;font-weight:700;color:var(--primarycolor);">
                                        Upcoming Appointments until Next <?php echo date("l", strtotime("+1 week")); ?>
                                    </p>
                                    <p style="padding-bottom:19px;padding-left:50px;font-size:15px;font-weight:500;color:#212529e3;line-height: 20px;">
                                        Here's Quick access to Upcoming Appointments until 7 days<br>
                                        More details available in @Appointment section.
                                    </p>
                                </td>
                                <td>
                                    <p style="text-align:right;padding:10px;padding-right:48px;padding-bottom:0;font-size:23px;font-weight:700;color:var(--primarycolor);">
                                        Upcoming Sessions until Next <?php echo date("l", strtotime("+1 week")); ?>
                                    </p>
                                    <p style="padding-bottom:19px;text-align:right;padding-right:50px;font-size:15px;font-weight:500;color:#212529e3;line-height: 20px;">
                                        Here's Quick access to Upcoming Sessions that Scheduled until 7 days<br>
                                        Add,Remove and Many features available in @Schedule section.
                                    </p>
                                </td>
                            </tr>
                            <tr>
                                <td width="50%">
                                    <center>
                                        <div class="abc scroll" style="height: 200px;">
                                            <table width="85%" class="sub-table scrolldown" border="0">
                                                <thead>
                                                    <tr>
                                                        <th class="table-headin" style="font-size: 12px;">
                                                            Appointment number
                                                        </th>
                                                        <th class="table-headin">
                                                            Client name
                                                        </th>
                                                        <th class="table-headin">
                                                            Lawyer
                                                        </th>
                                                        <th class="table-headin">
                                                            Session
                                                        </th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php
                                                    $nextweek = date("Y-m-d", strtotime("+1 week"));
                                                    $sqlmain = "SELECT appointment.appoid, schedule.scheduleid, schedule.title, lawyer.lawyername, client.cname, schedule.scheduledate, schedule.scheduletime, appointment.apponum, appointment.appodate FROM schedule INNER JOIN appointment ON schedule.scheduleid = appointment.scheduleid INNER JOIN client ON client.cid = appointment.cid INNER JOIN lawyer ON schedule.lawyerid = lawyer.lawyerid WHERE schedule.scheduledate >= '$today'  AND schedule.scheduledate <= '$nextweek' ORDER BY schedule.scheduledate DESC";
                                                    $result = $database->query($sqlmain);
                                                    if ($result->num_rows == 0) {
                                                        echo '<tr>
                                                            <td colspan="3">
                                                                <br><br><br><br>
                                                                <center>
                                                                    <img src="../img/notfound.svg" width="25%">
                                                                    <br>
                                                                    <p class="heading-main12" style="margin-left: 45px;font-size:20px;color:rgb(49, 49, 49)">We  couldnt find anything related to your keywords !</p>
                                                                    <a class="non-style-link" href="appointment.php"><button  class="login-btn btn-primary-soft btn"  style="display: flex;justify-content: center;align-items: center;margin-left:20px;">&nbsp; Show all Appointments &nbsp;</button></a>
                                                                </center>
                                                                <br><br><br><br>
                                                            </td>
                                                        </tr>';
                                                    } else {
                                                        for ($x = 0; $x < $result->num_rows; $x++) {
                                                            $row = $result->fetch_assoc();
                                                            $appoid = $row["appoid"];
                                                            $scheduleid = $row["scheduleid"];
                                                            $title = $row["title"];
                                                            $lawyername = $row["lawyername"];
                                                            $scheduledate = $row["scheduledate"];
                                                            $scheduletime = $row["scheduletime"];
                                                            $cname = $row["cname"];
                                                            $apponum = $row["apponum"];
                                                            $appodate = $row["appodate"];
                                                            echo '<tr>
                                                                <td style="text-align:center;font-size:23px;font-weight:500; color: var(--btnnicetext);padding:20px;">' . $apponum . '</td>
                                                                <td style="font-weight:600;"> &nbsp;' . substr($cname, 0, 25) . '</td>
                                                                <td style="font-weight:600;"> &nbsp;' . substr($lawyername, 0, 25) . '</td>
                                                                <td>' . substr($title, 0, 15) . '</td>
                                                            </tr>';
                                                        }
                                                    }
                                                    ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </center>
                                </td>
                                <td width="50%" style="padding: 0;">
                                    <center>
                                        <div class="abc scroll" style="height: 200px;padding: 0;margin: 0;">
                                            <table width="85%" class="sub-table scrolldown" border="0">
                                                <thead>
                                                    <tr>
                                                        <th class="table-headin">
                                                            Session Title
                                                        </th>
                                                        <th class="table-headin">
                                                            Lawyer
                                                        </th>
                                                        <th class="table-headin">
                                                            Sheduled Date & Time
                                                        </th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php
                                                    $nextweek = date("Y-m-d", strtotime("+1 week"));
                                                    $sqlmain = "SELECT schedule.scheduleid, schedule.title, lawyer.lawyername, schedule.scheduledate, schedule.scheduletime, schedule.nop FROM schedule INNER JOIN lawyer ON schedule.lawyerid = lawyer.lawyerid  WHERE schedule.scheduledate >= '$today' AND schedule.scheduledate <= '$nextweek' ORDER BY schedule.scheduledate DESC";
                                                    $result = $database->query($sqlmain);
                                                    if ($result->num_rows == 0) {
                                                        echo '<tr>
                                                            <td colspan="4">
                                                                <br><br><br><br>
                                                                <center>
                                                                    <img src="../img/notfound.svg" width="25%">
                                                                    <br>
                                                                    <p class="heading-main12" style="margin-left: 45px;font-size:20px;color:rgb(49, 49, 49)">We  couldnt find anything related to your keywords !</p>
                                                                    <a class="non-style-link" href="schedule.php"><button  class="login-btn btn-primary-soft btn"  style="display: flex;justify-content: center;align-items: center;margin-left:20px;">&nbsp; Show all Sessions &nbsp;</button></a>
                                                                </center>
                                                                <br><br><br><br>
                                                            </td>
                                                        </tr>';
                                                    } else {
                                                        for ($x = 0; $x < $result->num_rows; $x++) {
                                                            $row = $result->fetch_assoc();
                                                            $scheduleid = $row["scheduleid"];
                                                            $title = $row["title"];
                                                            $lawyername = $row["lawyername"];
                                                            $scheduledate = $row["scheduledate"];
                                                            $scheduletime = $row["scheduletime"];
                                                            $nop = $row["nop"];
                                                            echo '<tr>
                                                                <td style="padding:20px;"> &nbsp;' . substr($title, 0, 30) . '</td>
                                                                <td>' . substr($lawyername, 0, 20) . '</td>
                                                                <td style="text-align:center;">' . substr($scheduledate, 0, 10) . ' ' . substr($scheduletime, 0, 5) . '</td>
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
                                <td>
                                    <center>
                                        <a href="appointment.php" class="non-style-link">
                                            <button class="btn-primary btn" style="width:85%">Show all Appointments</button>
                                        </a>
                                    </center>
                                </td>
                                <td>
                                    <center>
                                        <a href="schedule.php" class="non-style-link">
                                            <button class="btn-primary btn" style="width:85%">Show all Sessions</button>
                                        </a>
                                    </center>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
        </div>
    </div>
</body>
</html>
