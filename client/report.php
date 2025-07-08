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

    <title>Reports | SafeSpace PH</title>
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
    <?php
// ...session and database setup...

// Handle form submission
$successMsg = '';
$errorMsg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_report'])) {
    // Sanitize inputs
    $your_name = trim($_POST['your_name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $violation_type = trim($_POST['violation_type'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $legal_consult = trim($_POST['legal_consult'] ?? '');
    $additional_notes = trim($_POST['additional_notes'] ?? '');
    $consent = isset($_POST['consent']) ? 1 : 0;

    // Basic validation
    if (!$description || !$consent) {
        $errorMsg = "Please provide a description and agree to the consent.";
    } else {
        // Handle file uploads
        $uploaded_files = [];
        if (!empty($_FILES['supporting_files']['name'][0])) {
            $upload_dir = "../uploads/";
            if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
            foreach ($_FILES['supporting_files']['name'] as $idx => $name) {
                $tmp_name = $_FILES['supporting_files']['tmp_name'][$idx];
                $target_file = $upload_dir . basename($name);
                if (move_uploaded_file($tmp_name, $target_file)) {
                    $uploaded_files[] = $target_file;
                }
            }
        }
        // Save to database (example, adjust as needed)
        $stmt = $database->prepare("INSERT INTO violation_reports (cid, your_name, phone, email, violation_type, description, legal_consult, additional_notes, consent, files) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $files_str = implode(',', $uploaded_files);
        $stmt->bind_param('isssssssis', $userid, $your_name, $phone, $email, $violation_type, $description, $legal_consult, $additional_notes, $consent, $files_str);
        if ($stmt->execute()) {
            $successMsg = "Your report has been submitted. Thank you!";
        } else {
            $errorMsg = "There was an error submitting your report. Please try again.";
        }
        $stmt->close();
    }
}
?>

<div class="dash-body">
    <div class="top-bar-with-header">
        <div class="form-header-section">
            <h1>Report Violation Form</h1>
            <p class="subtitle">WE CAN HELP TO PROTECT YOU.</p>
        </div>
        <div class="user-info">
            <span class="user-avatar"><?php echo strtoupper(substr($username,0,2)); ?></span>
            <span class="username"><?php echo htmlspecialchars($username); ?></span>
            <span class="material-icons expand-icon">expand_more</span>
        </div>
    </div>
    <div class="report-form-section">
        <div class="form-content">
            <?php if ($successMsg): ?>
                <div class="success-message" style="color:green; margin-bottom:16px;"><?php echo $successMsg; ?></div>
            <?php elseif ($errorMsg): ?>
                <div class="error-message" style="color:red; margin-bottom:16px;"><?php echo $errorMsg; ?></div>
            <?php endif; ?>
            <form method="post" enctype="multipart/form-data" id="violationReportForm" autocomplete="off">
                <section class="instructions-section">
                    <p class="intro-text">
                        We are here to listen, document, and act.<br>
                        If you have experienced or witnessed a violation under the Safe Spaces Act — whether in a public area, online, school, or workplace — you can report it through this confidential form.<br>
                    </p>
                    <h3>Instructions</h3>
                    <ol>
                        <li>Provide clear and honest details about the incident.</li>
                        <li>You may choose to report anonymously, but giving your contact information will help us provide support and connect you with legal help if needed.</li>
                        <li>All information submitted is kept strictly confidential and used only to assist you or take appropriate action.</li>
                    </ol>
                </section>
                <section class="personal-info-section">
                    <h3>Contact Information <span style="font-weight:400; color:#888; font-size:0.95em;">(Optional)</span></h3>
                    <div class="input-group">
                        <label for="your-name">Your Name</label>
                        <input type="text" id="your-name" name="your_name" placeholder="Optional" value="<?php echo htmlspecialchars($_POST['your_name'] ?? ''); ?>">
                    </div>
                    <div class="input-group">
                        <label for="phone">Phone</label>
                        <input type="text" id="phone" name="phone" placeholder="Optional" value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>">
                    </div>
                    <div class="input-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" placeholder="Optional" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
                    </div>
                </section>
                <section class="form-fields-section">
                    <h3>Incident Details</h3>
                    <div class="form-group">
                        <label>Type of Violation</label>
                        <div class="button-group" id="violationTypeGroup">
                            <?php
                            $types = [
                                "Public Harassment",
                                "Online Harassment",
                                "Workplace/School Harassment",
                                "Others"
                            ];
                            $selectedType = $_POST['violation_type'] ?? "Public Harassment";
                            foreach ($types as $type) {
                                $active = ($selectedType === $type) ? 'active' : '';
                                echo "<button class=\"type-button $active\" type=\"button\" data-value=\"$type\">$type</button>";
                            }
                            ?>
                            <input type="hidden" name="violation_type" id="violation_type" value="<?php echo htmlspecialchars($selectedType); ?>">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="description">Description of the Incident <span style="color:red">*</span></label>
                        <textarea id="description" name="description" required placeholder="Type your description here..."><?php echo htmlspecialchars($_POST['description'] ?? ''); ?></textarea>
                        <p class="hint-text">This should be a comprehensive description.</p>
                    </div>
                    <div class="form-group">
                        <label>Do you wish to request legal consultation?</label>
                        <div class="button-group" id="legalConsultGroup">
                            <?php
                            $yesNo = ["Yes", "No"];
                            $selectedConsult = $_POST['legal_consult'] ?? "No";
                            foreach ($yesNo as $yn) {
                                $active = ($selectedConsult === $yn) ? 'active' : '';
                                echo "<button class=\"yes-no-button $active\" type=\"button\" data-value=\"$yn\">$yn</button>";
                            }
                            ?>
                            <input type="hidden" name="legal_consult" id="legal_consult" value="<?php echo htmlspecialchars($selectedConsult); ?>">
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Do you have any supporting files?</label>
                        <input type="file" id="fileInput" name="supporting_files[]" multiple style="display: none">
                        <button class="upload-button" type="button" onclick="document.getElementById('fileInput').click()">Upload File</button>
                        <div id="fileList" class="file-list"></div>
                    </div>
                    <div class="input-group">
                        <label for="additional-notes">Additional Notes or Questions</label>
                        <textarea id="additional-notes" name="additional_notes" placeholder="Type your notes here..." class="description-box"><?php echo htmlspecialchars($_POST['additional_notes'] ?? ''); ?></textarea>
                        <p class="hint-text">Optional</p>
                    </div>
                    <div class="checkbox-group">
                        <input type="checkbox" id="consent" name="consent" <?php if(isset($_POST['consent'])) echo 'checked'; ?>>
                        <label for="consent">By submitting this form, you agree that the information you've shared will be used to assess your case and, if requested, connect you with pro bono legal assistance. <span style="color:red">*</span></label>
                    </div>
                    <button type="submit" class="submit-report-button" id="submitReportBtn" name="submit_report">Submit Report</button>
                </section>
            </form>
        </div>
    </div>
</div>

<style>
/* --- Integrated CSS from draft.html and your styles --- */
.report-form-section {padding:30px;}
.form-content {background:#fff;border-radius:12px;box-shadow:0 2px 8px rgba(0,0,0,0.06);padding:32px;max-width:600px;margin:auto;}
.instructions-section .intro-text {font-size:1.1em;color:#391053;margin-bottom:12px;}
.instructions-section h3 {margin-top:18px;font-size:1.1em;color:#391053;}
.instructions-section ol {margin-left:18px;margin-bottom:18px;}
.instructions-section ol li {margin-bottom:6px;font-size:0.98em;}
.personal-info-section h3 {margin-bottom:10px;}
.input-group {margin-bottom:16px;}
.input-group label {display:block;font-weight:500;margin-bottom:6px;color:#391053;}
.input-group input[type="text"], .input-group input[type="email"] {
    width:100%;padding:10px 12px;border:1px solid #ccc;border-radius:6px;background:#f5f5f5;
    font-size:1em;transition:border 0.2s;
}
.input-group input[type="text"]:focus, .input-group input[type="email"]:focus {border:1.5px solid #2F1435;}
.form-fields-section h3 {margin-top:24px;margin-bottom:12px;}
.form-group {margin-bottom:18px;}
.form-group label {font-weight:500;color:#391053;}
.button-group {display:flex;gap:10px;margin-top:8px;}
.type-button, .yes-no-button {
    padding:8px 18px;border:none;border-radius:6px;background:#f5f5f5;color:#2F1435;
    font-weight:500;cursor:pointer;transition:background 0.2s,color 0.2s;
}
.type-button.active, .yes-no-button.active, .type-button:hover, .yes-no-button:hover {
    background:#2F1435;color:#fff;
}
.form-group textarea, .input-group textarea {
    width:100%;min-height:80px;padding:10px 12px;border:1px solid #ccc;border-radius:6px;
    background:#f5f5f5;font-size:1em;resize:vertical;transition:border 0.2s;
}
.form-group textarea:focus, .input-group textarea:focus {border:1.5px solid #2F1435;}
.hint-text {font-size:0.92em;color:#888;margin-top:4px;}
.upload-button {
    padding:8px 18px;border:none;border-radius:6px;background:#2F1435;color:#fff;
    font-weight:500;cursor:pointer;transition:background 0.2s;
    margin-top:8px;
}
.upload-button:hover {background:#5A2675;}
.file-list {margin-top:10px;}
.file-item {display:flex;align-items:center;gap:8px;background:#f3eaff;padding:6px 12px;border-radius:5px;margin-bottom:5px;}
.remove-file {color:#ff5252;cursor:pointer;font-weight:bold;font-size:1.2em;}
.checkbox-group {display:flex;align-items:flex-start;gap:10px;margin-bottom:18px;}
.checkbox-group input[type="checkbox"] {margin-top:3px;}
.checkbox-group label {font-size:0.98em;color:#391053;}
.submit-report-button {
    width:100%;padding:12px 0;border:none;border-radius:6px;background:#2F1435;color:#fff;
    font-size:1.1em;font-weight:600;cursor:pointer;transition:background 0.2s;
    margin-top:10px;
}
.submit-report-button:hover {background:#5A2675;}
.success-message, .error-message {font-size:1em;padding:8px 0;}
@media (max-width:900px) {.main-content{margin-left:200px;}}
@media (max-width:600px) {.main-content{margin-left:0;}.form-content{padding:16px;}}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Violation type button group
    const typeButtons = document.querySelectorAll('.type-button');
    const violationTypeInput = document.getElementById('violation_type');
    typeButtons.forEach(button => {
        button.addEventListener('click', function() {
            typeButtons.forEach(btn => btn.classList.remove('active'));
            this.classList.add('active');
            violationTypeInput.value = this.getAttribute('data-value');
        });
    });

    // Legal consult button group
    const yesNoButtons = document.querySelectorAll('.yes-no-button');
    const legalConsultInput = document.getElementById('legal_consult');
    yesNoButtons.forEach(button => {
        button.addEventListener('click', function() {
            yesNoButtons.forEach(btn => btn.classList.remove('active'));
            this.classList.add('active');
            legalConsultInput.value = this.getAttribute('data-value');
        });
    });

    // File upload preview
    const fileInput = document.getElementById('fileInput');
    const fileList = document.getElementById('fileList');
    if(fileInput && fileList) {
        fileInput.addEventListener('change', function(e) {
            fileList.innerHTML = '';
            const files = Array.from(e.target.files);
            files.forEach((file, idx) => {
                const fileItem = document.createElement('div');
                fileItem.className = 'file-item';
                const fileName = document.createElement('span');
                fileName.textContent = file.name;
                const removeBtn = document.createElement('span');
                removeBtn.textContent = '×';
                removeBtn.className = 'remove-file';
                removeBtn.onclick = function() {
                    // Remove file from input (not possible directly, so just hide from UI)
                    fileItem.remove();
                };
                fileItem.appendChild(fileName);
                fileItem.appendChild(removeBtn);
                fileList.appendChild(fileItem);
            });
        });
    }
});
</script>

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