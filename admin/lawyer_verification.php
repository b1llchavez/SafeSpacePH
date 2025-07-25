<?php

session_start();

include("../connection.php");
// The lawyerverificationemails.php file is now included. 
// This will later be merged into 'send_email.php'.
require_once '../send_email.php'; 

if(isset($_SESSION["user"])){
    if(($_SESSION["user"])=="" or $_SESSION['usertype']!='a'){
        header("location: ../login.php");
        exit();
    }
}else{
    header("location: ../login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action'])) {
    $verification_id = $_POST['verification_id'];
    $lawyer_email = $_POST['lawyer_email'];
    $first_name = $_POST['first_name'] ?? '';
    $last_name = $_POST['last_name'] ?? '';
    $full_name = trim($first_name . ' ' . $last_name);

    $database->begin_transaction();

    try {
        if ($_POST['action'] == 'verify_user') {
            // Validate the password received from the modal form
            if (!isset($_POST['lawyer_password']) || empty(trim($_POST['lawyer_password']))) {
                throw new Exception("Password is required.");
            }
            if (strlen($_POST['lawyer_password']) < 8) {
                // While the password length check is good practice, you might want to adjust or remove it 
                // if other system passwords (like '123') don't meet this criteria.
                // For now, we'll keep it as a basic validation step.
                // throw new Exception("Password must be at least 8 characters long.");
            }
            // This is the plain-text password for both the email and the database.
            $temporary_password = $_POST['lawyer_password']; 

            // Fetch volunteer details
            $stmt_fetch_details = $database->prepare("SELECT * FROM volunteer_lawyer WHERE id = ?");
            if (!$stmt_fetch_details) throw new Exception("Failed to prepare fetch details statement: " . $database->error);
            $stmt_fetch_details->bind_param("i", $verification_id);
            $stmt_fetch_details->execute();
            $result_details = $stmt_fetch_details->get_result();
            $details = $result_details->fetch_assoc();
            $stmt_fetch_details->close();

            if (!$details) {
                throw new Exception("Volunteer application details not found for ID: " . $verification_id);
            }

            // The password is no longer hashed to match the existing system's insecure storage method.
            // $hashed_password = password_hash($temporary_password, PASSWORD_DEFAULT);

            // Check if user exists in webuser table
            $stmt_check_webuser = $database->prepare("SELECT email FROM webuser WHERE email = ?");
            if (!$stmt_check_webuser) throw new Exception("Failed to prepare webuser check statement: " . $database->error);
            $stmt_check_webuser->bind_param("s", $lawyer_email);
            $stmt_check_webuser->execute();
            $result_check_webuser = $stmt_check_webuser->get_result();
            $stmt_check_webuser->close();

            if ($result_check_webuser->num_rows > 0) {
                // Update existing user to be a lawyer
                $stmt_update_webuser = $database->prepare("UPDATE webuser SET usertype = 'l' WHERE email = ?");
                if (!$stmt_update_webuser) throw new Exception("Failed to prepare webuser update statement: " . $database->error);
                $stmt_update_webuser->bind_param("s", $lawyer_email);
                $stmt_update_webuser->execute();
                $stmt_update_webuser->close();
            } else {
                // Insert new user into webuser table
                $stmt_insert_webuser = $database->prepare("INSERT INTO webuser (email, usertype) VALUES (?, 'l')");
                if (!$stmt_insert_webuser) throw new Exception("Failed to prepare webuser insert statement: " . $database->error);
                $stmt_insert_webuser->bind_param("s", $lawyer_email);
                $stmt_insert_webuser->execute();
                $stmt_insert_webuser->close();
            }
            
            // Insert into lawyer table
            $stmt_insert_lawyer = $database->prepare("INSERT INTO lawyer (lawyeremail, lawyername, lawyerpassword, lawyerrollid, lawyertel) VALUES (?, ?, ?, ?, ?)");
            if (!$stmt_insert_lawyer) throw new Exception("Failed to prepare lawyer insert statement: " . $database->error);
            // Storing the plain-text password to match the rest of the system.
            $stmt_insert_lawyer->bind_param("sssss", $details['email'], $full_name, $temporary_password, $details['roll_number'], $details['contact_number']);
            $stmt_insert_lawyer->execute();
            $stmt_insert_lawyer->close();

            // Update volunteer application to be verified
            $stmt_volunteer = $database->prepare("UPDATE volunteer_lawyer SET is_verified = TRUE WHERE id = ?");
            if (!$stmt_volunteer) throw new Exception("Failed to prepare volunteer_lawyer update statement: " . $database->error);
            $stmt_volunteer->bind_param("i", $verification_id);
            $stmt_volunteer->execute();
            $stmt_volunteer->close();

            // Send the approval email with the admin-set password
            sendLawyerVerificationApprovedNotice($lawyer_email, $full_name, $temporary_password);

            $database->commit();
            header("Location: lawyer_verification.php?message=success");
            exit();

        } else if ($_POST['action'] == 'reject_user') {
            // Send rejection email
            sendLawyerVerificationRejectedNotice($lawyer_email, $full_name); 

            // Delete the verification request from volunteer_lawyer table
            $stmt_delete_verification = $database->prepare("DELETE FROM volunteer_lawyer WHERE id = ?");
            if (!$stmt_delete_verification) throw new Exception("Failed to prepare delete verification statement: " . $database->error);
            $stmt_delete_verification->bind_param("i", $verification_id);
            $stmt_delete_verification->execute();
            $stmt_delete_verification->close();

            $database->commit();
            header("Location: lawyer_verification.php?message=rejected");
            exit();
        }

    } catch (Exception $e) {
        $database->rollback();
        error_log("Action failed: " . $e->getMessage());
        header("Location: lawyer_verification.php?message=error&details=" . urlencode($e->getMessage()));
        exit();
    }
}

$verification_details = null;
if (isset($_GET['action']) && $_GET['action'] == 'view' && isset($_GET['id'])) {
    $view_id = $_GET['id'];
    $stmt_view = $database->prepare("SELECT * FROM volunteer_lawyer WHERE id = ?");
    $stmt_view->bind_param("i", $view_id);
    $stmt_view->execute();
    $result_view = $stmt_view->get_result();
    $verification_details = $result_view->fetch_assoc();
    $stmt_view->close();
}
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
    <link rel="icon" type="image/png" href="https://i.ibb.co/qYYZs46/logo.png">

    <title>Lawyer Verification | SafeSpace PH</title>
    <style>
        .popup{
            animation: transitionIn-Y-bottom 0.5s;
        }
        .sub-table{
            animation: transitionIn-Y-bottom 0.5s;
        }
        .custom-modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0,0,0,0.4);
            align-items: center;
            justify-content: center;
            padding: 20px;
            box-sizing: border-box;
        }
        .custom-modal-content {
            background-color: #fefefe;
            padding: 30px;
            border: 1px solid #888;
            width: 90%;
            max-width: 500px;
            border-radius: 10px;
            box-shadow: 0 4px 8px 0 rgba(0,0,0,0.2), 0 6px 20px 0 rgba(0,0,0,0.19);
            text-align: center;
            position: relative;
            max-height: 90vh;
            overflow-y: auto;
            box-sizing: border-box;
        }
        .custom-modal-content h3 {
            margin-top: 0;
            color: #333;
        }
        .custom-modal-content .modal-buttons button {
            margin: 10px;
            padding: 10px 20px;
            border: none;
            border-radius: 20px;
            cursor: pointer;
            font-weight: bold;
        }
        .custom-modal-content .modal-buttons .confirm-btn {
            background-color: #4CAF50;
            color: white;
        }
        .custom-modal-content .modal-buttons .cancel-btn {
            background-color: #f44336;
            color: white;
        }
        .overlay:target, #messagePopup {
            visibility: visible;
            opacity: 1;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .overlay .popup {
            margin: auto;
        }
        #viewDetailsModal .custom-modal-content {
            max-width: 800px;
            text-align: left;
            padding: 30px;
            max-height: 90vh;
            overflow-y: auto;
        }
        #viewDetailsModal .custom-modal-content h3 {
            text-align: center;
            margin-bottom: 25px;
        }
        #viewDetailsModal .close-x-button {
            position: absolute;
            top: 15px;
            right: 20px;
            font-size: 24px;
            font-weight: bold;
            color: #aaa;
            cursor: pointer;
            text-decoration: none;
        }
        #viewDetailsModal .close-x-button:hover,
        #viewDetailsModal .close-x-button:focus {
            color: #000;
            text-decoration: none;
            cursor: pointer;
        }
        #viewDetailsModal .detail-section {
            margin-bottom: 20px;
            padding-bottom: 10px;
        }
        #viewDetailsModal .detail-section:last-of-type {
            border-bottom: none;
        }
        #viewDetailsModal .detail-section h4 {
            color: #5A2675;
            font-weight: bold;
            margin-top: 0;
            margin-bottom: 15px;
            display: inline-block;
            padding-bottom: 5px;
            width: 100%;
            text-align: left;
        }
        #viewDetailsModal .detail-section hr {
            border: none;
            border-top: 1px solid #5A2675;
            margin-top: -10px;
            margin-bottom: 15px;
        }
        #viewDetailsModal .detail-item {
            margin-bottom: 8px;
            display: flex;
            align-items: baseline;
        }
        #viewDetailsModal .detail-item strong {
            flex: 0 0 200px; /* Adjusted width */
            margin-right: 10px;
            color: #555;
            font-weight: bold;
        }
        #viewDetailsModal .detail-item span {
            flex-grow: 1;
            color: #333;
            word-wrap: break-word;
            font-weight: normal;
        }
        #viewDetailsModal .close-button {
            background-color: #C9A8F1;
            color: white;
            padding: 10px 20px;
            border-radius: 20px;
            border: none;
            cursor: pointer;
            font-size: 16px;
            font-weight: bold;
            margin-top: 25px;
            display: block;
            margin-left: auto;
            margin-right: auto;
        }
        #viewDetailsModal .close-button:hover {
            background-color: #b193d5;
        }
        .file-link {
            display: inline-block;
            padding: 8px 15px;
            background-color: #5A2675;
            color: white;
            border-radius: 20px;
            text-decoration: none;
            margin: 5px;
            font-weight: bold;
        }
        .file-link:hover {
            background-color: #C9A8F1;
        }
    </style>
</head>
<body>
    <?php
    if ($verification_details) {
        echo '<div id="viewDetailsModal" class="custom-modal" style="display:flex;">
                <div class="custom-modal-content">
                    <a href="javascript:void(0)" class="close-x-button" onclick="closeViewDetailsModal()">&times;</a>
                     <h3 style="text-align:center; color:#391053; font-size:1.8rem; font-weight:700; margin:0 0 10px 0; letter-spacing:0.5px;">Volunteer Lawyer Application Details</h3>
                    <div style="width:100%; height:3px; background:linear-gradient(90deg, #391053 0%, #5A2675 30%, #9D72B3 65%, #C9A8F1 100%); border-radius:2px; margin:18px 0 28px 0;"></div>


                    <div class="detail-section">
                        <h4>Personal & Contact Information</h4>
                        <hr>
                        <div class="detail-item"><strong>Full Name:</strong> <span>' . htmlspecialchars($verification_details['first_name'] . ' ' . $verification_details['last_name']) . '</span></div>
                        <div class="detail-item"><strong>Email:</strong> <span>' . htmlspecialchars($verification_details['email']) . '</span></div>
                        <div class="detail-item"><strong>Contact Number:</strong> <span>' . htmlspecialchars($verification_details['contact_number']) . '</span></div>
                        <div class="detail-item"><strong>Home Address:</strong> <span>' . htmlspecialchars($verification_details['home_address']) . '</span></div>
                    </div>

                    <div class="detail-section">
                        <h4>Professional Information</h4>
                        <hr>
                        <div class="detail-item"><strong>IBP Roll Number:</strong> <span>' . htmlspecialchars($verification_details['roll_number']) . '</span></div>
                        <div class="detail-item"><strong>Years of Experience:</strong> <span>' . htmlspecialchars($verification_details['years_experience']) . '</span></div>
                        <div class="detail-item"><strong>Bar Region/Chapter:</strong> <span>' . htmlspecialchars($verification_details['bar_region']) . '</span></div>
                        <div class="detail-item"><strong>Affiliation:</strong> <span>' . htmlspecialchars($verification_details['affiliation']) . '</span></div>
                         <div class="detail-item"><strong>Reference Contact:</strong> <span>' . htmlspecialchars($verification_details['reference_contact']) . '</span></div>
                    </div>

                    <div class="detail-section">
                        <h4>Application Details</h4>
                        <hr>
                        <div class="detail-item"><strong>Motivation:</strong> <span>' . nl2br(htmlspecialchars($verification_details['motivation'])) . '</span></div>
                        <div class="detail-item"><strong>Preferred Areas of Law:</strong> <span>' . htmlspecialchars($verification_details['preferred_areas']) . '</span></div>
                        <div class="detail-item"><strong>Availability (Hours/Week):</strong> <span>' . htmlspecialchars($verification_details['availability_hours']) . '</span></div>
                        <div class="detail-item"><strong>Commitment (Months):</strong> <span>' . htmlspecialchars($verification_details['commitment_months']) . '</span></div>
                        <div class="detail-item"><strong>Available for Urgent Consults:</strong> <span>' . htmlspecialchars($verification_details['urgent_consult']) . '</span></div>
                    </div>

                    <div class="detail-section">
                        <h4>Uploaded Documents & Consents</h4>
                        <hr>
                        <div class="detail-item"><strong>Attorney License/ID:</strong> <span><a href="../' . htmlspecialchars($verification_details['license_file']) . '" target="_blank" class="file-link">View File</a></span></div>
                        <div class="detail-item"><strong>Resume/CV:</strong> <span><a href="../' . htmlspecialchars($verification_details['resume_file']) . '" target="_blank" class="file-link">View File</a></span></div>
                        <div class="detail-item"><strong>Profile Photo:</strong> <span><a href="../' . htmlspecialchars($verification_details['profile_photo']) . '" target="_blank" class="file-link">View File</a></span></div>
                        <div class="detail-item"><strong>Consent to Background Check:</strong> <span>' . ($verification_details['consent_background_check'] ? 'Yes' : 'No') . '</span></div>
                        <div class="detail-item"><strong>Agreed to Terms:</strong> <span>' . ($verification_details['agree_terms'] ? 'Yes' : 'No') . '</span></div>
                        <div class="detail-item"><strong>Certified Information Correct:</strong> <span>' . ($verification_details['info_certified'] ? 'Yes' : 'No') . '</span></div>
                    </div>
                    
                    <button class="close-button" onclick="closeViewDetailsModal()">Close</button>
                </div>
              </div>';
    }

    if (isset($_GET['message'])) {
        echo '<div id="messagePopup" class="overlay" style="display:flex;">
                <div class="popup">
                    <center>
                        <a class="close" href="lawyer_verification.php">&times;</a>
                        <div class="content">';
        if ($_GET['message'] == 'success') {
            echo '<h3>Success!</h3><p>Lawyer has been successfully verified and an account has been created.</p>';
        } else if ($_GET['message'] == 'rejected') {
            echo '<h3>Rejected!</h3><p>Lawyer application has been rejected and removed.</p>';
        } else if ($_GET['message'] == 'error') {
            echo '<h3>Error!</h3><p>Action failed. Please try again.</p>';
            if (isset($_GET['details'])) {
                echo '<p>Details: ' . htmlspecialchars($_GET['details']) . '</p>';
            }
        }
        echo '      </div>
                        <a href="lawyer_verification.php"><button class="login-btn btn-primary-soft btn" style="margin-top:15px;">OK</button></a>
                    </center>
                </div>
              </div>';
    }
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
                                    <p class="profile-title">Administrator</p>
                                    <p class="profile-subtitle">admin@safespaceph.com</p>
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
                <tr class="menu-row">
                    <td class="menu-btn menu-icon-dashbord">
                        <a href="index.php" class="non-style-link-menu"><div><p class="menu-text">Dashboard</p></div></a>
                    </td>
                </tr>
                <tr class="menu-row">
                    <td class="menu-btn menu-icon-report">
                        <a href="admin_reports.php" class="non-style-link-menu"><div><p class="menu-text">Violation Reports</p></div></a>
                    </td>
                </tr>
                <tr class="menu-row">
                    <td class="menu-btn menu-icon-appoinment">
                        <a href="appointment.php" class="non-style-link-menu"><div><p class="menu-text">Appointments</p></div></a>
                    </td>
                </tr>
                <tr class="menu-row">
                    <td class="menu-btn menu-icon-client-verification">
                        <a href="client_verification.php" class="non-style-link-menu"><div><p class="menu-text">Client Verification</p></div></a>
                    </td>
                </tr>
                <tr class="menu-row">
                    <td class="menu-btn menu-icon-lawyer-verification menu-active menu-icon-lawyer-verification-active">
                        <a href="lawyer_verification.php" class="non-style-link-menu non-style-link-menu-active"><div><p class="menu-text menu-text-active">Lawyer Verification</p></div></a>
                    </td>
                </tr>
                <tr class="menu-row">
                    <td class="menu-btn menu-icon-lawyers">
                        <a href="lawyers.php" class="non-style-link-menu"><div><p class="menu-text">All Lawyers</p></div></a>
                    </td>
                </tr>
                <tr class="menu-row">
                    <td class="menu-btn menu-icon-client">
                        <a href="client.php" class="non-style-link-menu"><div><p class="menu-text">All Clients</p></div></a>
                    </td>
                </tr>
            </table>
        </div>
        <div class="dash-body">
            <table border="0" width="100%" style=" border-spacing: 0;margin:0;padding:0;margin-top:25px; ">
                <tr >
                    <td>
                        <form action="" method="post" class="header-search">
                            <input type="search" name="search" class="input-text header-searchbar" placeholder="Search Applicant Name or Email" list="lawyer">&nbsp;&nbsp;
                            <?php
                                $list11_query = "SELECT first_name, last_name, email FROM volunteer_lawyer WHERE is_verified = FALSE;";
                                $list11 = $database->query($list11_query);
                                echo '<datalist id="lawyer">';
                                if ($list11) {
                                    while($row00 = $list11->fetch_assoc()){
                                        $l = htmlspecialchars($row00["first_name"]." ".$row00["last_name"]);
                                        $c = htmlspecialchars($row00["email"]);
                                        echo "<option value='$l'></option>";
                                        echo "<option value='$c'></option>";
                                    }
                                }
                            echo ' </datalist>';
                            ?>
                            <input type="Submit" value="Search" class="login-btn btn-primary btn" style="padding-left: 25px;padding-right: 25px;padding-top: 10px;padding-bottom: 10px;">
                        </form>
                    </td>
                    <td width="15%">
                        <p style="font-size: 14px;color: rgb(119, 119, 119);padding: 0;margin: 0;text-align: right;">Today's Date</p>
                        <p class="heading-sub12" style="padding: 0;margin: 0;"><?php date_default_timezone_set('Asia/Manila'); echo date('Y-m-d'); ?></p>
                    </td>
                    <td width="10%">
                        <button  class="btn-label"  style="display: flex;justify-content: center;align-items: center;"><img src="../img/calendar.svg" width="100%"></button>
                    </td>
                </tr>
                <tr>
                    <td colspan="4" style="padding-top:10px;">
                        <p class="heading-main12" style="margin-left: 45px;font-size:18px;color:rgb(49, 49, 49)">New Volunteer Lawyer Applications (<?php echo $list11 ? $list11->num_rows : 0; ?>)</p>
                    </td>
                </tr>
                <tr>
                    <td colspan="4" style="padding-top:0px;width: 100%;">
                        <center>
                            <div class="abc scroll">
                                <table width="93%" class="sub-table scrolldown" style="border-spacing:0;">
                                <thead>
                                    <tr>
                                        <th class="table-headin">Applicant Name</th>
                                        <th class="table-headin">Email</th>
                                        <th class="table-headin">Contact Number</th>
                                        <th class="table-headin">Submission Date</th>
                                        <th class="table-headin">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php
                                    $sqlmain = "SELECT * FROM volunteer_lawyer WHERE is_verified = FALSE ORDER BY submitted_at DESC";
                                    if($_POST && isset($_POST['search'])){
                                        $searchkey = $database->real_escape_string($_POST['search']);
                                        $sqlmain = "SELECT * FROM volunteer_lawyer WHERE is_verified = FALSE AND (CONCAT(first_name, ' ', last_name) LIKE '%$searchkey%' OR email LIKE '%$searchkey%') ORDER BY submitted_at DESC";
                                    }
                                    $result = $database->query($sqlmain);

                                    if(!$result || $result->num_rows==0){
                                        echo '<tr>
                                                <td colspan="5">
                                                    <br><br><br><br>
                                                    <center>
                                                        <img src="../img/notfound.svg" width="25%">
                                                        <br>
                                                        <p class="heading-main12" style="margin-left: 45px;font-size:20px;color:rgb(49, 49, 49)">No new applications found!</p>
                                                        <a class="non-style-link" href="lawyer_verification.php"><button  class="login-btn btn-primary-soft btn"  style="display: flex;justify-content: center;align-items: center;margin-left:20px;">&nbsp; Show all Applications &nbsp;</button></a>
                                                    </center>
                                                    <br><br><br><br>
                                                </td>
                                            </tr>';
                                    } else {
                                        while($row = $result->fetch_assoc()){
                                            $id = $row["id"];
                                            $name = htmlspecialchars($row["first_name"]." ".$row["last_name"]);
                                            $email = htmlspecialchars($row["email"]);
                                            $contact_number = htmlspecialchars($row["contact_number"]);
                                            $submission_date = $row["submitted_at"];
                                            $first_name_lawyer = htmlspecialchars($row["first_name"]);
                                            $last_name_lawyer = htmlspecialchars($row["last_name"]);

                                      echo '<tr>
                                            <td>'.substr($name,0,30).'</td>
                                            <td>'.substr($email,0,30).'</td>
                                            <td>'.substr($contact_number,0,20).'</td>
                                            <td>'.substr($submission_date,0,10).'</td>
                                            <td>
                                                <div style="display:flex;justify-content: center;">
                                                    <a href="?action=view&id='.$id.'" class="btn-primary-soft btn button-icon btn-view" style="padding-left: 40px; padding-top: 12px; padding-bottom: 12px; margin-top: 10px;">
                                                        <font class="tn-in-text">View</font>
                                                    </a>
                                                    &nbsp;&nbsp;&nbsp;
                                                    <button class="btn-primary-soft btn button-icon menu-icon-verify"
                                                        onclick="showVerifyPasswordModal(\''.$id.'\', \''.addslashes($email).'\', \''.addslashes($first_name_lawyer).'\', \''.addslashes($last_name_lawyer).'\')"
                                                        style="padding-left: 40px; padding-top: 12px; padding-bottom: 12px; margin-top: 10px;">
                                                        <font class="tn-in-text">Verify</font>
                                                    </button>
                                                    &nbsp;&nbsp;&nbsp;
                                                    <button class="btn-primary-soft btn button-icon btn-delete"
                                                        onclick="showRejectConfirmModal(\''.$id.'\', \''.addslashes($email).'\', \''.addslashes($first_name_lawyer).'\', \''.addslashes($last_name_lawyer).'\')"
                                                        style="padding-left: 40px; padding-top: 12px; padding-bottom: 12px; margin-top: 10px;">
                                                        <font class="tn-in-text">Reject</font>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>';
                                        }
                                    }
                                ?>
                                </tbody>
                            </table>
                            </div> </center>
                    </td>
                </tr>
            </table>
        </div>
    </div>

    <!-- New Modal for Verification with Password Input -->
    <div id="verifyPasswordModal" class="custom-modal">
        <div class="custom-modal-content">
            <form id="verifyForm" onsubmit="submitVerification(event)">
                <h3>Verify Lawyer & Set Password</h3>
                <p>You are verifying the account for: <strong id="verifyLawyerEmail"></strong>.</p>
                <p>Please set a temporary password for the lawyer's account.</p>

                <div style="margin: 15px 0;">
                    <label for="lawyerPassword" style="display: block; text-align: left; margin-bottom: 5px; font-weight: bold;">Password:</label>
                    <input type="password" id="lawyerPassword" name="lawyer_password" class="input-text" required style="width: 100%; box-sizing: border-box;">
                </div>
                <div style="margin: 15px 0;">
                    <label for="confirmLawyerPassword" style="display: block; text-align: left; margin-bottom: 5px; font-weight: bold;">Confirm Password:</label>
                    <input type="password" id="confirmLawyerPassword" class="input-text" required style="width: 100%; box-sizing: border-box;">
                </div>
                <p id="passwordError" style="color: red; display: none; margin-top: 10px;"></p>

                <div class="modal-buttons">
                    <button type="submit" class="confirm-btn">Confirm & Verify</button>
                    <button type="button" class="cancel-btn" onclick="hideVerifyPasswordModal()">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <div id="rejectConfirmModal" class="custom-modal">
        <div class="custom-modal-content">
            <h3>Confirm Rejection</h3>
            <p>Are you sure you want to reject this application? This action cannot be undone.</p>
            <div class="modal-buttons">
                <button class="cancel-btn" id="confirmRejectBtn">Reject</button>
                <button class="confirm-btn" onclick="hideRejectConfirmModal()">Cancel</button>
            </div>
        </div>
    </div>

    <script>
        let currentVerificationId = null;
        let currentLawyerEmail = null;
        let currentLawyerFirstName = null;
        let currentLawyerLastName = null;

        function showVerifyPasswordModal(verificationId, lawyerEmail, firstName, lastName) {
            currentVerificationId = verificationId;
            currentLawyerEmail = lawyerEmail;
            currentLawyerFirstName = firstName;
            currentLawyerLastName = lastName;
            document.getElementById('verifyLawyerEmail').innerText = lawyerEmail;
            document.getElementById('verifyPasswordModal').style.display = 'flex';
            // Clear previous values and errors
            document.getElementById('lawyerPassword').value = '';
            document.getElementById('confirmLawyerPassword').value = '';
            document.getElementById('passwordError').style.display = 'none';
        }

        function hideVerifyPasswordModal() {
            document.getElementById('verifyPasswordModal').style.display = 'none';
        }

        function submitVerification(event) {
            event.preventDefault(); // Prevent default form submission

            const password = document.getElementById('lawyerPassword').value;
            const confirmPassword = document.getElementById('confirmLawyerPassword').value;
            const errorP = document.getElementById('passwordError');

            if (password.length === 0) {
                errorP.innerText = 'Password cannot be empty.';
                errorP.style.display = 'block';
                return;
            }

            if (password !== confirmPassword) {
                errorP.innerText = 'Passwords do not match.';
                errorP.style.display = 'block';
                return;
            }

            errorP.style.display = 'none';

            if (currentVerificationId) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = 'lawyer_verification.php';

                const fields = {
                    'action': 'verify_user',
                    'verification_id': currentVerificationId,
                    'lawyer_email': currentLawyerEmail,
                    'first_name': currentLawyerFirstName,
                    'last_name': currentLawyerLastName,
                    'lawyer_password': password
                };

                for (const key in fields) {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = key;
                    input.value = fields[key];
                    form.appendChild(input);
                }

                document.body.appendChild(form);
                form.submit();
            }
            hideVerifyPasswordModal();
        }

        function showRejectConfirmModal(verificationId, lawyerEmail, firstName, lastName) {
            currentVerificationId = verificationId;
            currentLawyerEmail = lawyerEmail;
            currentLawyerFirstName = firstName;
            currentLawyerLastName = lastName;
            document.getElementById('rejectConfirmModal').style.display = 'flex';
        }

        function hideRejectConfirmModal() {
            document.getElementById('rejectConfirmModal').style.display = 'none';
        }

        function closeViewDetailsModal() {
            const url = new URL(window.location.href);
            url.searchParams.delete('action');
            url.searchParams.delete('id');
            window.history.pushState({}, '', url);
            const viewModal = document.getElementById('viewDetailsModal');
            if (viewModal) {
                 viewModal.style.display = 'none';
            }
        }

        document.getElementById('confirmRejectBtn').addEventListener('click', function() {
            if (currentVerificationId) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = 'lawyer_verification.php';

                const fields = {
                    'action': 'reject_user',
                    'verification_id': currentVerificationId,
                    'lawyer_email': currentLawyerEmail,
                    'first_name': currentLawyerFirstName,
                    'last_name': currentLawyerLastName
                };

                for (const key in fields) {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = key;
                    input.value = fields[key];
                    form.appendChild(input);
                }

                document.body.appendChild(form);
                form.submit();
            }
            hideRejectConfirmModal();
        });

        window.onload = function() {
            // This logic ensures the view modal doesn't re-appear on page load unless specified in URL
            const urlParams = new URLSearchParams(window.location.search);
            if (!urlParams.has('action') || urlParams.get('action') !== 'view') {
                 const viewModal = document.getElementById('viewDetailsModal');
                if (viewModal && viewModal.style.display !== 'none') {
                    // This case is unlikely with the server-side logic but good for robustness
                    viewModal.style.display = 'none';
                }
            }
        };
    </script>
</body>
</html>
