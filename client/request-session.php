<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/animations.css">  
    <link rel="stylesheet" href="../css/main.css">  
    <link rel="stylesheet" href="../css/admin.css"> <!-- You might want to create a client-specific CSS like client.css -->
    <link rel="icon" type="image/png" href="https://i.ibb.co/qYYZs46L/logo.png">
    <title>Request New Session | SafeSpace PH</title>
    <style>
        .popup{
            animation: transitionIn-Y-bottom 0.5s;
        }
        .sub-table{
            animation: transitionIn-Y-bottom 0.5s;
        }
        /* Additional styling for date/time input if needed */
        input[type="date"], input[type="time"] {
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            width: 100%; /* Adjust as necessary */
            box-sizing: border-box; /* Include padding and border in the element's total width and height */
        }
    </style>
</head>
<body>
    <?php
    session_start();

    // Authentication: Changed to check for 'c' (client) usertype
    if(!isset($_SESSION["user"]) || $_SESSION['usertype']!='c'){
        header("location: ../login.php");
        exit();
    }
    
    include("../connection.php");

    $clientid = $_SESSION['cid']; // Client ID from session
    $clientname = $_SESSION['cname']; // Client name from session, assuming you add it in login.php
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
                    <td class="menu-btn menu-icon-schedule menu-active menu-icon-schedule-active">
                        <a href="request-session.php" class="non-style-link-menu non-style-link-menu-active"><div><p class="menu-text menu-text-active">Find a Safe Space</p></div></a>
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
        <div class="dash-body">
            <table border="0" width="100%" style=" border-spacing: 0;margin:0;padding:0;margin-top:25px; ">
                <tr>
                    <td width="13%">
                        <a href="client_dashboard.php" ><button class="login-btn btn-primary-soft btn btn-icon-back" style="padding-top:11px;padding-bottom:11px;margin-left:20px;width:125px"><font class="tn-in-text">Back</font></button></a>
                    </td>
                    <td>
                        <p style="font-size: 23px;padding-left:12px;font-weight: 600;">Request New Session</p>
                    </td>
                    <td width="15%">
                        <p style="font-size: 14px;color: rgb(119, 119, 119);padding: 0;margin: 0;text-align: right;">
                            Today's Date
                        </p>
                        <p class="heading-sub12" style="padding: 0;margin: 0;">
                            <?php 
                            date_default_timezone_set('Asia/Manila');
                            echo date('Y-m-d');
                            ?>
                        </p>
                    </td>
                    <td width="10%">
                        <button class="btn-label" style="display: flex;justify-content: center;align-items: center;"><img src="../img/calendar.svg" width="100%"></button>
                    </td>
                </tr>
                <tr>
                    <td colspan="4">
                        <center>
                            <div class="abc">
                                <table width="80%" class="sub-table scrolldown add-lawyer-form-container" border="0">
                                    <tr>
                                        <td>
                                            <p style="padding: 0;margin: 0;text-align: left;font-size: 25px;font-weight: 500;">Submit a new session request.</p><br>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="label-td" colspan="2">
                                            <form action="process_session_request.php" method="POST" class="add-new-form">
                                                <!-- REMOVED: Lawyer Selection Dropdown -->
                                                <!--
                                                <label for="lawyerid" class="form-label">Select Lawyer: </label>
                                                <select name="lawyerid" id="lawyerid" class="box" required>
                                                    <option value="" disabled selected hidden>Choose Lawyer Name from the list</option><br/>
                                                    <?php
                                                        // Fetch lawyers from the database
                                                        // $list11 = $database->query("SELECT lawyerid, lawyername FROM lawyer ORDER BY lawyername ASC;");
                                                        // for ($y=0;$y<$list11->num_rows;$y++){
                                                        //     $row00=$list11->fetch_assoc();
                                                        //     $lawyer_name=$row00["lawyername"];
                                                        //     $lawyer_id=$row00["lawyerid"];
                                                        //     echo "<option value='".htmlspecialchars($lawyer_id)."'>".htmlspecialchars($lawyer_name)."</option><br/>";
                                                        // };
                                                    ?>
                                                </select><br><br>
                                                -->
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="label-td" colspan="2">
                                                <label for="title" class="form-label">Session Title (e.g., Legal Consultation): </label>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="label-td" colspan="2">
                                                <input type="text" name="title" class="input-text" placeholder="e.g., Family Law Consultation, Contract Review" required><br>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="label-td" colspan="2">
                                                <label for="description" class="form-label">Case Description / Session Purpose: </label>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="label-td" colspan="2">
                                                <textarea name="description" class="input-text" placeholder="Briefly describe your case or what you need assistance with..." rows="5" required></textarea><br>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="label-td" colspan="2">
                                                <label for="preferred_date" class="form-label">Preferred Session Date: </label>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="label-td" colspan="2">
                                                <input type="date" name="preferred_date" class="input-text" min="<?php echo date('Y-m-d'); ?>" required><br>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="label-td" colspan="2">
                                                <label for="preferred_time" class="form-label">Preferred Session Time: </label>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="label-td" colspan="2">
                                                <input type="time" name="preferred_time" class="input-text" required><br>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td colspan="2">
                                                <input type="hidden" name="cid" value="<?php echo htmlspecialchars($clientid); ?>">
                                                <input type="reset" value="Reset" class="login-btn btn-primary-soft btn" >&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                                <input type="submit" value="Submit Request" class="login-btn btn-primary btn" name="submit_request">
                                            </td>
                                        </tr>
                                    </form>
                                </table>
                            </div>
                        </center>
                        <br><br>
                    </td>
                </tr>
            </table>
        </div>
    </div>
</body>
</html>