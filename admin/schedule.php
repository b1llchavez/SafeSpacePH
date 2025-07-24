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

    <title>Schedule | SafeSpace PH</title>
    <style>
        .popup {
            animation: transitionIn-Y-bottom 0.5s;
        }

        .sub-table {
            animation: transitionIn-Y-bottom 0.5s;
        }

         
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
            padding: 30px 40px;
            max-width: 800px;
            width: 95%;
            position: relative;
            animation: fadeIn 0.3s;
            max-height: 90vh;  
            overflow-y: auto;  
        }
        .modal-header {
            text-align: center;
            color: #391053;
            font-size: 1.8rem;
            font-weight: 700;
            margin-bottom: 10px;
            margin-top: 0;
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
            margin-top: 25px;
        }
        .modal-body {
            text-align: left;
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
        .modal-btn-soft:hover { background: #e2d8fa; }
        .modal-btn-primary { background-color: #5A2675; color: #fff; }
        .modal-btn-primary:hover { background-color: #391053; }
        .modal-btn-danger { background-color: #dc3545; color: #fff; }
        .modal-btn-danger:hover { background-color: #c82333; }

        @keyframes fadeIn {
            from { opacity: 0; transform: scale(0.95); }
            to { opacity: 1; transform: scale(1); }
        }
        .detail-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px 30px;
        }
        .detail-item strong, .detail-full strong {
            color: #391053;
            display: block;
            margin-bottom: 4px;
            font-weight: 600;
        }
        .detail-item span, .detail-full span { color: #555; }
        .detail-full { grid-column: 1 / -1; }
         
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
                                    <a href="../logout.php"><input type="button" value="Log out"
                                            class="logout-btn btn-primary-soft btn"></a>
                                </td>
                            </tr>
                        </table>
                    </td>

                </tr>
                <tr class="menu-row">
                    <td class="menu-btn menu-icon-dashbord">
                        <a href="index.php" class="non-style-link-menu">
                            <div>
                                <p class="menu-text">Dashboard</p>
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
                    <td class="menu-btn menu-icon-schedule menu-active menu-icon-schedule-active">
                        <a href="schedule.php" class="non-style-link-menu non-style-link-menu-active">
                            <div>
                                <p class="menu-text menu-text-active">Schedules</p>
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

    <div class="dash-body" style="overflow-y: auto; overflow-x: hidden;">
        <table border="0" width="100%" style=" border-spacing: 0;margin:0;padding:0;margin-top:25px; ">
            <tr>
                <td>
                    <p style="margin-left: 45px; font-size: 23px;font-weight: 600;">Schedule Manager</p>

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

                        $list110 = $database->query("select  * from  schedule;");

                        ?>
                    </p>
                </td>
                <td width="10%">
                    <button class="btn-label" style="display: flex;justify-content: center;align-items: center;"><img
                            src="../img/calendar.svg" width="100%"></button>
                </td>
            </tr>

            <tr>
                <td colspan="4">
                    <div style="display: flex;margin-top: 40px;">
                        <div class="heading-main12"
                            style="margin-left: 45px;font-size:20px;color:rgb(49, 49, 49);margin-top: 5px;">Schedule a
                            Session</div>
                        <a href="?action=add-session&id=none&error=0" class="non-style-link"><button
                                class="login-btn btn-primary btn button-icon"
                                style="margin-left:25px;background-image: url('../img/icons/add.svg');">Add a Session
                                </font></button>
                        </a>
                    </div>
                </td>
            </tr>

            <tr>
                <td colspan="4" style="padding-top:10px;width: 100%;">

                    <p class="heading-main12" style="margin-left: 45px;font-size:18px;color:rgb(49, 49, 49)">All
                        Sessions (<?php echo $list110->num_rows; ?>)</p>
                </td>

            </tr>

            <tr>
                <td colspan="4" style="padding-top:0px;width: 100%;">
                    <center>
                        <form action="" method="post">
                        <table class="filter-container" border="0">
                            <tr>
                                <td width="10%"></td>
                                <td width="5%" style="text-align: center;">Date:</td>
                                <td width="30%">
                                    <input type="date" name="sheduledate" id="date" class="input-text filter-container-items" style="margin: 0;width: 95%;">
                                </td>
                                <td width="5%" style="text-align: center;">Lawyer:</td>
                                <td width="30%">
                                    <select name="lawyerid" id="" class="box filter-container-items" style="width:90% ;height: 37px;margin: 0;">
                                        <option value="" disabled selected hidden>Choose Lawyer Name from the list</option><br />
                                        <?php
                                        $list11 = $database->query("select * from lawyer order by lawyername asc;");
                                        for ($y = 0; $y < $list11->num_rows; $y++) {
                                            $row00 = $list11->fetch_assoc();
                                            $sn = $row00["lawyername"];
                                            $id00 = $row00["lawyerid"];
                                            echo "<option value=" . $id00 . ">$sn</option><br/>";
                                        }
                                        ?>
                                    </select>
                                </td>
                                <td width="12%">
                                    <input type="submit" name="filter" value=" Filter" class=" btn-primary-soft btn button-icon btn-filter" style="padding: 15px; margin :0;width:100%">
                                </td>
                            </tr>
                        </table>
                        </form>
                    </center>
                </td>
            </tr>

            <?php
            if ($_POST) {

                $sqlpt1 = "";
                if (!empty($_POST["sheduledate"])) {
                    $sheduledate = $_POST["sheduledate"];
                    $sqlpt1 = " schedule.scheduledate='$sheduledate' ";
                }


                $sqlpt2 = "";
                if (!empty($_POST["lawyerid"])) {
                    $lawyerid = $_POST["lawyerid"];
                    $sqlpt2 = " lawyer.lawyerid=$lawyerid ";
                }


                $sqlmain = "select schedule.scheduleid,schedule.title,lawyer.lawyername,schedule.scheduledate,schedule.scheduletime,schedule.nop from schedule inner join lawyer on schedule.lawyerid=lawyer.lawyerid ";
                $sqllist = array($sqlpt1, $sqlpt2);
                $sqlkeywords = array(" where ", " and ");
                $key2 = 0;
                foreach ($sqllist as $key) {

                    if (!empty($key)) {
                        $sqlmain .= $sqlkeywords[$key2] . $key;
                        $key2++;
                    }
                }
            } else {
                $sqlmain = "select schedule.scheduleid,schedule.title,lawyer.lawyername,schedule.scheduledate,schedule.scheduletime,schedule.nop from schedule inner join lawyer on schedule.lawyerid=lawyer.lawyerid  order by schedule.scheduledate desc";

            }
            ?>

            <tr>
                <td colspan="4">
                    <center>
                        <div class="abc scroll">
                            <table width="95%" class="sub-table scrolldown" border="0">
                                <thead>
                                <tr>
                                    <th class="table-headin">
                                        Session Title
                                    </th>
                                    <th class="table-headin">
                                        Lawyer
                                    </th>
                                    <th class="table-headin">
                                        Scheduled Date & Time
                                    </th>
                                    <th class="table-headin">
                                        Max num that can be booked
                                    </th>
                                    <th class="table-headin">
                                        Events
                                    </th>
                                </tr>
                                </thead>
                                <tbody>

                                <?php
                                $result = $database->query($sqlmain);
                                if ($result->num_rows == 0) {
                                    echo '<tr>
                                    <td colspan="5">
                                    <br><br><br><br>
                                    <center>
                                    <img src="../img/notfound.svg" width="25%">
                                    
                                    <br>
                                    <p class="heading-main12" style="margin-left: 45px;font-size:20px;color:rgb(49, 49, 49)">We couldnt find anything related to your keywords !</p>
                                    <a class="non-style-link" href="schedule.php"><button  class="login-btn btn-primary-soft btn"  style="display: flex;justify-content: center;align-items: center;margin-left:20px;">&nbsp; Show all Sessions &nbsp;</font></button>
                                    </a>
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
                                        <td> &nbsp;' .
                                            substr($title, 0, 30)
                                            . '</td>
                                        <td>
                                        ' . substr($lawyername, 0, 20) . '
                                        </td>
                                        <td style="text-align:center;">
                                            ' . substr($scheduledate, 0, 10) . ' ' . substr($scheduletime, 0, 5) . '
                                        </td>
                                        <td style="text-align:center;">
                                            ' . $nop . '
                                        </td>

                                        <td>
                                        <div style="display:flex;justify-content: center;">
                                        
                                        <a href="?action=view&id=' . $scheduleid . '" class="non-style-link"><button  class="btn-primary-soft btn button-icon btn-view"  style="padding-left: 40px;padding-top: 12px;padding-bottom: 12px;margin-top: 10px;"><font class="tn-in-text">View</font></button></a>
                                       &nbsp;&nbsp;&nbsp;
                                       <a href="?action=drop&id=' . $scheduleid . '&name=' . urlencode($title) . '" class="non-style-link"><button  class="btn-primary-soft btn button-icon btn-delete"  style="padding-left: 40px;padding-top: 12px;padding-bottom: 12px;margin-top: 10px;"><font class="tn-in-text">Remove</font></button></a>
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

    if ($_GET) {
        $id = $_GET["id"];
        $action = $_GET["action"];
        
        $overlay_class = 'overlay active'; // Common class to show modal

        if ($action == 'add-session') {
            echo '
            <div id="addModal" class="'.$overlay_class.'">
                <div class="modal-content" style="max-width: 600px;">
                    <h2 class="modal-header">Add New Session</h2>
                    <div class="modal-divider"></div>
                    <form action="add-session.php" method="POST" class="add-new-form">
                        <div class="modal-body">
                            <label for="title" class="form-label">Session Title : </label>
                            <input type="text" name="title" class="input-text" placeholder="Name of this Session" required><br><br>
                            <label for="lawyerid" class="form-label">Select Lawyer: </label>
                            <select name="lawyerid" id="" class="box" style="width: 100%;" required>
                                <option value="" disabled selected hidden>Choose Lawyer Name from the list</option><br/>';
                                $list11 = $database->query("select  * from  lawyer order by lawyername asc;");
                                for ($y = 0; $y < $list11->num_rows; $y++) {
                                    $row00 = $list11->fetch_assoc();
                                    $sn = $row00["lawyername"];
                                    $id00 = $row00["lawyerid"];
                                    echo "<option value=" . $id00 . ">$sn</option><br/>";
                                }
            echo '              </select><br><br>
                            <label for="nop" class="form-label">Number of Clients/Appointment Numbers : </label>
                            <input type="number" name="nop" class="input-text" min="1"  placeholder="The final appointment number for this session depends on this number" required><br><br>
                            <label for="date" class="form-label">Session Date: </label>
                            <input type="date" name="date" class="input-text" min="' . date('Y-m-d') . '" required><br><br>
                            <label for="time" class="form-label">Schedule Time: </label>
                            <input type="time" name="time" class="input-text" placeholder="Time" required><br>
                        </div>
                        <div class="modal-footer">
                            <a href="schedule.php" class="non-style-link"><button type="button" class="modal-btn modal-btn-soft">Close</button></a>
                            <input type="reset" value="Reset" class="modal-btn modal-btn-soft" >
                            <input type="submit" value="Place this Session" class="modal-btn modal-btn-primary" name="shedulesubmit">
                        </div>
                    </form>
                </div>
            </div>';
        } elseif ($action == 'session-added') {
            $titleget = $_GET["title"];
            echo '
            <div id="successModal" class="'.$overlay_class.'">
                <div class="modal-content" style="max-width: 500px;">
                    <h2 class="modal-header">Session Placed</h2>
                    <div class="modal-divider"></div>
                    <div class="modal-body" style="text-align: center;">
                        <p>\'' . substr($titleget, 0, 40) . '\' was scheduled successfully.</p>
                    </div>
                    <div class="modal-footer" style="justify-content: center;">
                        <a href="schedule.php" class="non-style-link"><button class="modal-btn modal-btn-primary">OK</button></a>
                    </div>
                </div>
            </div>';
        } elseif ($action == 'drop') {
            $nameget = $_GET["name"];
            echo '
            <div id="deleteModal" class="'.$overlay_class.'">
                <div class="modal-content" style="max-width: 500px;">
                     <h2 class="modal-header">Are you sure?</h2>
                     <div class="modal-divider"></div>
                     <div class="modal-body" style="text-align: center;">
                        <p>You want to delete this record<br><strong>(' . substr(urldecode($nameget), 0, 40) . ')</strong>.</p>
                        <p style="font-size: 13px; color: #dc3545; margin-top: 15px;">This action cannot be undone.</p>
                     </div>
                     <div class="modal-footer">
                        <a href="schedule.php" class="non-style-link"><button type="button" class="modal-btn modal-btn-soft">No</button></a>
                        <a href="delete-session.php?id=' . $id . '" class="non-style-link"><button type="button" class="modal-btn modal-btn-danger">Yes</button></a>
                     </div>
                </div>
            </div>';
        } elseif ($action == 'view') {
            $sqlmain = "select schedule.scheduleid,schedule.title,lawyer.lawyername,schedule.scheduledate,schedule.scheduletime,schedule.nop from schedule inner join lawyer on schedule.lawyerid=lawyer.lawyerid  where  schedule.scheduleid=$id";
            $result = $database->query($sqlmain);
            $row = $result->fetch_assoc();
            $lawyername = $row["lawyername"];
            $title = $row["title"];
            $scheduledate = $row["scheduledate"];
            $scheduletime = $row["scheduletime"];
            $nop = $row['nop'];

            $sqlmain12 = "SELECT * FROM appointment INNER JOIN client ON client.cid = appointment.cid WHERE appointment.scheduleid = $id;";
            $result12 = $database->query($sqlmain12);
            echo '
            <div id="viewModal" class="'.$overlay_class.'">
                <div class="modal-content">
                    <h2 class="modal-header">View Details</h2>
                    <div class="modal-divider"></div>
                    <div class="modal-body">
                        <div class="detail-grid">
                            <div class="detail-full"><strong>Session Title:</strong> <span>' . $title . '</span></div>
                            <div class="detail-item"><strong>Lawyer of this session:</strong> <span>' . $lawyername . '</span></div>
                            <div class="detail-item"><strong>Max. Number of Clients:</strong> <span>' . $nop . '</span></div>
                            <div class="detail-item"><strong>Scheduled Date:</strong> <span>' . $scheduledate . '</span></div>
                            <div class="detail-item"><strong>Scheduled Time:</strong> <span>' . $scheduletime . '</span></div>
                        </div>
                        <hr class="modal-divider" style="margin: 20px 0;">
                        <strong>Clients that Already Registered for this session:</strong> (' . $result12->num_rows . "/" . $nop . ')
                        <div class="abc scroll" style="max-height: 250px; margin-top: 10px;">
                            <table width="100%" class="sub-table scrolldown" border="0">
                                <thead>
                                    <tr>   
                                        <th class="table-headin">Client ID</th>
                                        <th class="table-headin">Client Name</th>
                                        <th class="table-headin">Appointment Number</th>
                                        <th class="table-headin">Client Telephone</th>
                                    </tr>
                                </thead>
                                <tbody>';
            if ($result12->num_rows == 0) {
                echo '<tr><td colspan="4" style="text-align:center; padding: 20px;">
                        <img src="../img/notfound.svg" width="100px"><br>No clients found for this session.
                      </td></tr>';
            } else {
                while($row_client = $result12->fetch_assoc()) {
                    echo '<tr style="text-align:center;">
                            <td>' . substr($row_client["cid"], 0, 15) . '</td>
                            <td style="font-weight:600;">' . substr($row_client["cname"], 0, 25) . '</td>
                            <td style="font-size:23px;font-weight:500; color: var(--btnnicetext);">' . $row_client["apponum"] . '</td>
                            <td>' . substr($row_client["ctel"], 0, 25) . '</td>
                          </tr>';
                }
            }
            echo '          </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <a href="schedule.php" class="non-style-link"><button type="button" class="modal-btn modal-btn-soft">Close</button></a>
                    </div>
                </div>
            </div>';
        }
    }

    ?>
    </div>

</body>

</html>