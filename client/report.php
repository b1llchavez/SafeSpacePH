<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/animations.css">
    <link rel="stylesheet" href="../css/main.css">
    <link rel="stylesheet" href="../css/admin.css">
    <link rel="stylesheet" href="../css/style.css">
    <link rel="icon" type="image/png" href="https://i.ibb.co/qYYZs46L/logo.png">

    <title>Client | SafeSpace PH</title>
    <style>
        .popup {
            animation: transitionIn-Y-bottom 0.5s;
        }

        .sub-table {
            animation: transitionIn-Y-bottom 0.5s;
        }
    </style>
</head>

<body>
    <?php

    //learn from w3schools.com
    
    session_start();

    if (isset($_SESSION["user"])) {
        if (($_SESSION["user"]) == "" or $_SESSION['usertype'] != 'c') {
            header("location: ../login.php");
        } else {
            $useremail = $_SESSION["user"];
        }

    } else {
        header("location: ../login.php");
    }


    //import database
    include("../connection.php");
    $userrow = $database->query("select * from client where cemail='$useremail'");
    $userfetch = $userrow->fetch_assoc();
    $userid = $userfetch["cid"];
    $username = $userfetch["cname"];

    //echo $userid;
    //echo $username;
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
                    <td class="menu-btn menu-icon-home">
                        <a href="index.php" class="non-style-link-menu">
                            <div>
                                <p class="menu-text">Home</p>
                        </a>
        </div></a>
        </td>
        </tr>
        <tr class="menu-row">
            <td class="menu-btn menu-icon-report menu-active menu-icon-report-active">
                <a href="report.php" class="non-style-link-menu non-style-link-menu-active">
                    <div>
                        <p class="menu-text menu-text-active">Report Violation</p>
                </a>
    </div>
    </td>
    </tr>
    <tr class="menu-row">
        <td class="menu-btn menu-icon-lawyers">
            <a href="lawyers.php" class="non-style-link-menu">
                <div>
                    <p class="menu-text">All Lawyers</p>
            </a></div>
        </td>
    </tr>
    <tr class="menu-row">
        <td class="menu-btn menu-icon-session">
            <a href="schedule.php" class="non-style-link-menu">
                <div>
                    <p class="menu-text">Scheduled Sessions</p>
                </div>
            </a>
        </td>
    </tr>
    <tr class="menu-row">
        <td class="menu-btn menu-icon-appoinment">
            <a href="appointment.php" class="non-style-link-menu">
                <div>
                    <p class="menu-text">My Bookings</p>
            </a></div>
        </td>
    </tr>
    <tr class="menu-row">
        <td class="menu-btn menu-icon-settings">
            <a href="settings.php" class="non-style-link-menu">
                <div>
                    <p class="menu-text">Settings</p>
            </a></div>
        </td>
    </tr>

    </table>
    </div>
    <?php

    $selecttype = "My";
    $current = "My clients Only";
    if ($_POST) {

        if (isset($_POST["search"])) {
            $keyword = $_POST["search12"];

            $sqlmain = "select * from client where cemail='$keyword' or cname='$keyword' or cname like '$keyword%' or cname like '%$keyword' or cname like '%$keyword%' ";
            $selecttype = "my";
        }

        if (isset($_POST["filter"])) {
            if ($_POST["showonly"] == 'all') {
                $sqlmain = "select * from client";
                $selecttype = "All";
                $current = "All clients";
            } else {
                $sqlmain = "select * from appointment inner join client on client.cid=appointment.cid inner join schedule on schedule.scheduleid=appointment.scheduleid where schedule.lawyerid=$userid;";
                $selecttype = "My";
                $current = "My clients Only";
            }
        }
    } else {
        $sqlmain = "select * from appointment inner join client on client.cid=appointment.cid inner join schedule on schedule.scheduleid=appointment.scheduleid where schedule.lawyerid=$userid;";
        $selecttype = "My";
    }



    ?>
    <div class="dash-body">
            <div class="top-bar-with-header">
                <div class="form-header-section">
                    <h1>Report Violation Form</h1>
                    <p class="subtitle">WE CAN HELP TO PROTECT YOU.</p>
                </div>
                <div class="user-info">
                    <span class="user-avatar">BM</span>
                    <span class="username">Bill</span>
                    <span class="material-icons expand-icon">expand_more</span>
                </div>
            </div>
            <div class="report-form-section">
                <div class="form-content">
                    <div class="instructions-section">
                        <p class="intro-text">
                            We are here to listen, document, and act.<br>
                            If you have experienced or witnessed a violation under the Safe Spaces Act — whether in a public area, online, school, or workplace — you can report it through this confidential form.<br>
                        </p>
                        <h3>INSTRUCTIONS</h3>
                        <ol>
                            <li>Provide clear and honest details about the incident.</li>
                            <li>You may choose to report anonymously, but giving your
                                contact information will help us provide support and
                                connect you with legal help if needed.</li>
                            <li>All information submitted is kept strictly confidential and
                                used only to assist you or take
                                appropriate action.</li>
                        </ol>

                        <div class="input-group">
                            <label for="your-name">Your Name</label>
                            <input type="text" id="your-name" placeholder="Optional">
                        </div>
                        <div class="input-group">
                            <label for="phone">Phone</label>
                            <input type="text" id="phone" placeholder="Optional">
                        </div>
                        <div class="input-group">
                            <label for="email">Email</label>
                            <input type="email" id="email" placeholder="Optional">
                        </div>
                    </div>

                    <div class="form-fields-section">
                        <div class="form-group">
                            <label>Type of Violation</label>
                            <div class="button-group">
                                <button class="type-button active">Public Harassment</button>
                                <button class="type-button">Online Harassment</button>
                                <button class="type-button">Workplace/School Harassment</button>
                                <button class="type-button">Others</button>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="description">Description of the Incident</label>
                            <textarea id="description" placeholder="Type your description here..."></textarea>
                            <p class="hint-text">This should be a comprehensive description.</p>
                        </div>  

                        <div class="form-group">
                            <label>Do you wish to request legal consultation?</label>
                            <div class="button-group">
                                <button class="yes-no-button">Yes</button>
                                <button class="yes-no-button active">No</button>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Do you have any supporting files?</label>
                            <input type="file" id="fileInput" multiple style="display: none">
                            <button class="upload-button" onclick="document.getElementById('fileInput').click()">Upload File</button>
                            <div id="fileList" class="file-list"></div>
                        </div>

                        <div class="input-group">
                            <label for="additional-notes">Additional Notes or Questions</label>
                            <textarea id="additional-notes" placeholder="Type your notes here..." class="description-box"></textarea>
                            <p class="hint-text">Optional</p>
                        </div>

                        <div class="checkbox-group">
                            <input type="checkbox" id="consent">
                            <label for="consent">By submitting this form, you agree that the information
                                you've shared will be used to assess your case and, if
                                requested, connect you with pro bono legal assistance.</label>
                        </div>

                        <button type="submit" class="submit-report-button" id="submitReportBtn">Submit Report</button>
                    </div>
                </div>
            </div>
    </div>
    </div>
    <?php
    if ($_GET) {

        $id = $_GET["id"];
        $action = $_GET["action"];
        $sqlmain = "select * from client where cid='$id'";
        $result = $database->query($sqlmain);
        $row = $result->fetch_assoc();
        $name = $row["cname"];
        $email = $row["cemail"];
        $nic = $row["cnic"];
        $dob = $row["cdob"];
        $tele = $row["ctel"];
        $address = $row["caddress"];
        echo '
            <div id="popup1" class="overlay">
                    <div class="popup">
                    <center>
                        <a class="close" href="client.php">&times;</a>
                        <div class="content">

                        </div>
                        <div style="display: flex;justify-content: center;">
                        <table width="80%" class="sub-table scrolldown add-lawyer-form-container" border="0">
                        
                            <tr>
                                <td>
                                    <p style="padding: 0;margin: 0;text-align: left;font-size: 25px;font-weight: 500;">View Details.</p><br><br>
                                </td>
                            </tr>
                            <tr>
                                
                                <td class="label-td" colspan="2">
                                    <label for="name" class="form-label">client ID: </label>
                                </td>
                            </tr>
                            <tr>
                                <td class="label-td" colspan="2">
                                    P-' . $id . '<br><br>
                                </td>
                                
                            </tr>
                            
                            <tr>
                                
                                <td class="label-td" colspan="2">
                                    <label for="name" class="form-label">Name: </label>
                                </td>
                            </tr>
                            <tr>
                                <td class="label-td" colspan="2">
                                    ' . $name . '<br><br>
                                </td>
                                
                            </tr>
                            <tr>
                                <td class="label-td" colspan="2">
                                    <label for="Email" class="form-label">Email: </label>
                                </td>
                            </tr>
                            <tr>
                                <td class="label-td" colspan="2">
                                ' . $email . '<br><br>
                                </td>
                            </tr>
                            <tr>
                                <td class="label-td" colspan="2">
                                    <label for="nic" class="form-label">NIC: </label>
                                </td>
                            </tr>
                            <tr>
                                <td class="label-td" colspan="2">
                                ' . $nic . '<br><br>
                                </td>
                            </tr>
                            <tr>
                                <td class="label-td" colspan="2">
                                    <label for="Tele" class="form-label">Telephone: </label>
                                </td>
                            </tr>
                            <tr>
                                <td class="label-td" colspan="2">
                                ' . $tele . '<br><br>
                                </td>
                            </tr>
                            <tr>
                                <td class="label-td" colspan="2">
                                    <label for="spec" class="form-label">Address: </label>
                                    
                                </td>
                            </tr>
                            <tr>
                            <td class="label-td" colspan="2">
                            ' . $address . '<br><br>
                            </td>
                            </tr>
                            <tr>
                                
                                <td class="label-td" colspan="2">
                                    <label for="name" class="form-label">Date of Birth: </label>
                                </td>
                            </tr>
                            <tr>
                                <td class="label-td" colspan="2">
                                    ' . $dob . '<br><br>
                                </td>
                                
                            </tr>
                            <tr>
                                <td colspan="2">
                                    <a href="client.php"><input type="button" value="OK" class="login-btn btn-primary-soft btn" ></a>
                                
                                    
                                </td>
                
                            </tr>
                           

                        </table>
                        </div>
                    </center>
                    <br><br>
            </div>
            </div>
            ';

    }
    ;

    ?>
    </div>

</body>

</html>