<?php
    session_start();

    if (isset($_SESSION["user"])) {
        if (($_SESSION["user"]) == "" or $_SESSION['usertype'] != 'l') {
            header("location: ../login.php");
            exit(); 
        } else {
            $useremail = $_SESSION["user"];
        }
    } else {
        header("location: ../login.php");
        exit();
    }

    include("../connection.php");
    $userrow = $database->query("select * from lawyer where lawyeremail='$useremail'");
    $userfetch = $userrow->fetch_assoc();
    $userid = $userfetch["lawyerid"];
    $username = $userfetch["lawyername"];
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

    <title>Settings | SafeSpace PH</title>
    <style>
        .dashbord-tables {
            animation: transitionIn-Y-over 0.5s;
        }
        .filter-container {
            animation: transitionIn-X 0.5s;
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
            padding: 25px 40px;
            max-width: 650px;
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
            letter-spacing: 0.5px;
        }
        .modal-divider {
            width: 100%;
            height: 3px;
            background: linear-gradient(90deg, #391053 0%, #5A2675 30%, #9D72B3 65%, #C9A8F1 100%);
            border: none;
            border-radius: 2px;
            margin: 15px 0 20px 0;
        }
        .modal-body {
            text-align: left;
        }
        .modal-footer {
            display: flex;
            justify-content: flex-end;
            gap: 12px;
            margin-top: 30px;
        }
        .modal-btn {
            border: none;
            border-radius: 7px;
            padding: 12px 28px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease-in-out;
            text-decoration: none;
            display: inline-block;
            text-align: center;
            font-family: inherit;
        }
        .modal-btn-primary {
            background: #5A2675;
            color: #fff;
        }
        .modal-btn-primary:hover {
            background: #391053;
            box-shadow: 0 4px 15px rgba(90, 38, 117, 0.3);
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
            box-shadow: 0 4px 15px rgba(220, 53, 69, 0.3);
        }
        .detail-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 15px 30px;
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
            word-wrap: break-word;
        }
        .detail-full {
            grid-column: 1 / -1;
        }
        .form-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 18px;
        }
        @media (min-width: 576px) {
            .form-grid {
                grid-template-columns: repeat(2, 1fr);
                gap: 16px 25px; 
            }
            .form-group.full-width {
                grid-column: 1 / -1;
            }
        }
        .form-group {
            display: flex;
            flex-direction: column;
        }
        .form-label {
            font-size: 15px;
            font-weight: 600;
            color: #452a5a;
            margin-bottom: 6px;
            text-align: left;
        }
        .input-text, .box {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #dcdcdc;
            border-radius: 8px;
            font-size: 15px;
            transition: border-color 0.2s, box-shadow 0.2s;
            background-color: #fdfdff;
        }
        .box {
            height: 43px;
        }
        .input-text:focus, .box:focus {
            outline: none;
            border-color: #5A2675;
            box-shadow: 0 0 0 3px rgba(90, 38, 117, 0.15);
        }
        .input-text[readonly] {
            background-color: #f3f4f6;
            color: #555;
            cursor: not-allowed; 
            border-color: #e5e7eb;
        }
        .input-text[readonly]:focus {
            box-shadow: none; 
            border-color: #e5e7eb;
        }
        .close-btn { 
             position: absolute; 
             top: 18px; 
             right: 18px; 
             font-size: 24px; 
             font-weight: bold; 
             color: #aaa; 
             cursor: pointer; 
             border: none; 
             background: transparent; 
             line-height: 1; 
             padding: 0; 
             width: 32px; 
             height: 32px; 
             border-radius: 50%; 
             display: flex; 
             align-items: center; 
             justify-content: center; 
             transition: background-color 0.2s, color 0.2s; 
             text-decoration: none;
         } 
         .close-btn:hover { 
             background-color: #f0e9f7; 
             color: #5A2675; 
         }
        @keyframes fadeIn {
            from { opacity: 0; transform: scale(0.95); }
            to { opacity: 1; transform: scale(1); }
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
                                <td width="30%" style="padding-left:20px">
                                    <img src="../img/user.png" alt="" width="100%" style="border-radius:50%">
                                </td>
                                <td style="padding:0px;margin:0px;">
                                    <p class="profile-title"><?php echo substr($username, 0, 13) ?>..</p>
                                    <p class="profile-subtitle"><?php echo substr($useremail, 0, 22) ?></p>
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
                        <a href="index.php" class="non-style-link-menu "><div><p class="menu-text">Dashboard</p></a></div>
                    </td>
                </tr>
                <tr class="menu-row">
                    <td class="menu-btn menu-icon-session">
                        <a href="manage-appointments.php" class="non-style-link-menu"><div><p class="menu-text">Share a Safe Space</p></a></div>
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
                <tr class="menu-row">
                    <td class="menu-btn menu-icon-settings  menu-active menu-icon-settings-active">
                        <a href="settings.php" class="non-style-link-menu non-style-link-menu-active"><div><p class="menu-text menu-text-active">Settings</p></a></div>
                    </td>
                </tr>
            </table>
        </div>
        <div class="dash-body" style="margin-top: 15px">
            <table border="0" width="100%" style=" border-spacing: 0;margin:0;padding:0;">
                <tr>
                    <td>
                        <p style="margin-left: 67px; font-size: 23px;font-weight: 600;">Settings</p>
                    </td>
                    <td width="15%">
                        <p style="font-size: 14px;color: rgb(119, 119, 119);padding: 0;margin: 0;text-align: right;">
                            Today's Date
                        </p>
                        <p class="heading-sub12" style="padding: 0;margin: 0;">
                            <?php
                            date_default_timezone_set('Asia/Manila');
                            $today = date('Y-m-d');
                            echo $today;
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
                            <table class="filter-container" style="border: none;" border="0">
                                <tr><td colspan="4"><p style="font-size: 20px">&nbsp;</p></td></tr>
                                <tr>
                                    <td style="width: 25%;">
                                        <a href="?action=edit&id=<?php echo $userid ?>&error=0" class="non-style-link">
                                            <div class="dashboard-items setting-tabs" style="padding:20px; margin:auto; width:95%; display: flex">
                                                <div class="btn-icon-settings dashboard-icons-setting" style="background-image: url('../img/icons/account-settings-icon.svg'); background-size: 30px 30px; display: flex; align-items: center; justify-content: center;"></div>
                                                <div>
                                                    <div class="h1-dashboard">Account Settings &nbsp;</div><br>
                                                    <div class="h3-dashboard" style="font-size: 15px;">Edit your Account Details & Change Password</div>
                                                </div>
                                            </div>
                                        </a>
                                    </td>
                                </tr>
                                <tr><td colspan="4"><p style="font-size: 5px">&nbsp;</p></td></tr>
                                <tr>
                                    <td style="width: 25%;">
                                        <a href="?action=view&id=<?php echo $userid ?>" class="non-style-link">
                                            <div class="dashboard-items setting-tabs" style="padding:20px;margin:auto;width:95%;display: flex;">
                                                <div class="btn-icon-settings dashboard-icons-setting" style="background-image: url('../img/icons/view-icon.svg');"></div>
                                                <div>
                                                    <div class="h1-dashboard">View Account Details</div><br>
                                                    <div class="h3-dashboard" style="font-size: 15px;">View Personal information About Your Account</div>
                                                </div>
                                            </div>
                                        </a>
                                    </td>
                                </tr>
                                <tr><td colspan="4"><p style="font-size: 5px">&nbsp;</p></td></tr>
                                <tr>
                                    <td style="width: 25%;">
                                        <a href="?action=drop&id=<?php echo $userid . '&name=' . urlencode($username) ?>" class="non-style-link">
                                            <div class="dashboard-items setting-tabs" style="padding:20px;margin:auto;width:95%;display: flex;">
                                                <div class="btn-icon-settings dashboard-icons-setting" style="background-image: url('../img/icons/delete-icon.svg');"></div>
                                                <div>
                                                    <div class="h1-dashboard" style="color: #ff5050;">Delete Account</div><br>
                                                    <div class="h3-dashboard" style="font-size: 15px;">Will Permanently Remove your Account</div>
                                                </div>
                                            </div>
                                        </a>
                                    </td>
                                </tr>
                            </table>
                        </center>
                    </td>
                </tr>
            </table>
        </div>
    </div>
    <?php
    if (isset($_GET['action']) && isset($_GET['id'])) {
        $id = $_GET["id"];
        $action = $_GET["action"];
        if ($action == 'drop') {
            $nameget = $_GET["name"];
            echo '
            <div id="popup1" class="overlay active">
                <div class="modal-content">
                    <h2 class="modal-header">Are you sure?</h2>
                    <div class="modal-divider"></div>
                    <div class="modal-body" style="text-align: center; font-size: 16px; color: #555;">
                        You are about to permanently delete your account for<br><strong>' . htmlspecialchars(substr($nameget, 0, 40)) . '</strong>.
                        <br><br>This action cannot be undone.
                    </div>
                    <div class="modal-footer" style="justify-content: center;">
                        <a href="settings.php" class="modal-btn modal-btn-soft">Cancel</a>
                        <a href="delete-lawyer.php?id=' . $id . '" class="modal-btn modal-btn-danger">Delete</a>
                    </div>
                </div>
            </div>';
        } elseif ($action == 'view') {
            $sqlmain = "select * from lawyer where lawyerid='$id'";
            $result = $database->query($sqlmain);
            $row = $result->fetch_assoc();
            $name = $row["lawyername"];
            $email = $row["lawyeremail"];
            $rollid = $row['lawyerrollid'];
            $tele = $row['lawyertel'];
            $spe = $row["specialties"];
            $platform = $row["meeting_platform"];
            $link = $row["meeting_link"];

            $spcil_res = $database->query("select sname from specialties where id='$spe'");
            $spcil_array = $spcil_res->fetch_assoc();
            $spcil_name = $spcil_array["sname"];
            
            echo '
            <div id="popup1" class="overlay active">
                <div class="modal-content">
                    <h2 class="modal-header">Account Details</h2>
                    <div class="modal-divider"></div>
                    <div class="modal-body">
                        <div class="detail-grid">
                            <div class="detail-item"><strong>Full Name:</strong> <span>' . htmlspecialchars($name) . '</span></div>
                            <div class="detail-item"><strong>Email Address:</strong> <span>' . htmlspecialchars($email) . '</span></div>
                            <div class="detail-item"><strong>Telephone:</strong> <span>' . htmlspecialchars($tele) . '</span></div>
                            <div class="detail-item"><strong>Lawyer Roll ID No.:</strong> <span>' . htmlspecialchars($rollid) . '</span></div>
                            <div class="detail-item"><strong>Meeting Platform:</strong> <span>' . htmlspecialchars($platform ?: 'Not Set') . '</span></div>
                            <div class="detail-item"><strong>Specialties:</strong> <span>' . htmlspecialchars($spcil_name) . '</span></div>
                            <div class="detail-full"><strong>Link or Address:</strong> <span>' . htmlspecialchars($link ?: 'Not Set') . '</span></div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <a href="settings.php" class="modal-btn modal-btn-soft">Close</a>
                    </div>
                </div>
            </div>';
        } elseif ($action == 'edit') {
            $sqlmain = "select * from lawyer where lawyerid='$id'";
            $result = $database->query($sqlmain);
            $row = $result->fetch_assoc();
            $name = $row["lawyername"];
            $email = $row["lawyeremail"];
            $rollid = $row['lawyerrollid'];
            $tele = $row['lawyertel'];
            $spe = $row["specialties"];
            $platform = $row["meeting_platform"];
            $link = $row["meeting_link"];

            $spcil_res = $database->query("select sname from specialties where id='$spe'");
            $spcil_array = $spcil_res->fetch_assoc();
            $spcil_name = isset($spcil_array["sname"]) ? $spcil_array["sname"] : "Not Set";

            $error_1 = $_GET["error"];
            $errorlist = array(
                '1' => '<label class="form-label" style="color:rgb(255, 62, 62);text-align:center;">An account with this email address already exists.</label>',
                '2' => '<label class="form-label" style="color:rgb(255, 62, 62);text-align:center;">Password confirmation does not match.</label>',
                '3' => '<label class="form-label" style="color:rgb(255, 62, 62);text-align:center;">An unexpected error occurred.</label>',
                '4' => "",
                '0' => '',
            );

            if ($error_1 != '4') {
                echo '
                <div id="popup1" class="overlay active">
                    <div class="modal-content">
                        <a href="settings.php" class="close-btn">&times;</a>
                        <h2 class="modal-header">Edit Account Details</h2>
                        <div class="modal-divider"></div>
                        <div class="modal-body">
                            <form action="edit-lawyers.php" method="POST">
                                <div style="text-align:center; margin-bottom: 15px;">' . $errorlist[$error_1] . '</div>
                                <input type="hidden" value="' . $id . '" name="id00">
                                <input type="hidden" name="oldemail" value="' . $email . '" >

                                <div class="form-grid">
                                    <div class="form-group full-width">
                                        <label for="name" class="form-label">Full Name:</label>
                                        <input type="text" name="name" class="input-text" placeholder="Lawyer Name" value="' . htmlspecialchars($name) . '" readonly>
                                    </div>
                                    <div class="form-group">
                                        <label for="email" class="form-label">Email Address:</label>
                                        <input type="email" name="email" class="input-text" placeholder="Email Address" value="' . htmlspecialchars($email) . '" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="nic" class="form-label">Lawyer Roll ID No.:</label>
                                        <input type="text" name="nic" class="input-text" placeholder="National ID Card No." value="' . htmlspecialchars($rollid) . '" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="Tele" class="form-label">Telephone:</label>
                                        <input type="tel" name="Tele" class="input-text" placeholder="Telephone Number" value="' . htmlspecialchars($tele) . '" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="spec" class="form-label">Specialties:</label>
                                        <select name="spec" id="spec" class="box" required>';
                                            $list11 = $database->query("select * from specialties order by sname asc;");
                                            for ($y = 0; $y < $list11->num_rows; $y++) {
                                                $row00 = $list11->fetch_assoc();
                                                $sn = $row00["sname"];
                                                $id00 = $row00["id"];
                                                $selected = ($spe == $id00) ? "selected" : "";
                                                echo "<option value='" . $id00 . "' ".$selected.">".htmlspecialchars($sn)."</option>";
                                            }
                echo '                  </select>
                                    </div>
                                    <div class="form-group">
                                        <label for="platform" class="form-label">Meeting Platform:</label>
                                        <input type="text" name="platform" class="input-text" placeholder="e.g., Google Meet, Zoom" value="' . htmlspecialchars($platform) . '" readonly>
                                    </div>
                                     <div class="form-group">
                                        <label for="link" class="form-label">Link or Address:</label>
                                        <input type="url" name="link" class="input-text" placeholder="https://meet.google.com/abc-defg-hij" value="' . htmlspecialchars($link) . '" readonly>
                                    </div>
                                    <div class="form-group">
                                        <label for="password" class="form-label">New Password (optional):</label>
                                        <input type="password" name="password" class="input-text" placeholder="Leave blank to keep current password">
                                    </div>
                                    <div class="form-group">
                                        <label for="cpassword" class="form-label">Confirm New Password:</label>
                                        <input type="password" name="cpassword" class="input-text" placeholder="Confirm new password">
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <input type="reset" value="Reset" class="modal-btn modal-btn-soft" >
                                    <input type="submit" value="Save Changes" class="modal-btn modal-btn-primary">
                                </div>
                            </form>
                        </div>
                    </div>
                </div>';
            } else {
                echo '
                <div id="popup1" class="overlay active">
                    <div class="modal-content" style="text-align:center;">
                        <h2 class="modal-header">Success!</h2>
                        <div class="modal-divider"></div>
                        <div class="modal-body">
                            Your account details have been updated successfully.
                            <br><br>
                            <p style="font-size: 14px; color: #555;">If you changed your email, please log out and sign back in with the new address.</p>
                        </div>
                        <div class="modal-footer" style="justify-content:center;">
                            <a href="settings.php" class="modal-btn modal-btn-primary">OK</a>
                            <a href="../logout.php" class="modal-btn modal-btn-soft">Log Out</a>
                        </div>
                    </div>
                </div>';
            }
        }
    }
    ?>
</body>
</html>