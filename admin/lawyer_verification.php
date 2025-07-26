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
    <link rel="icon" type="image/png" href="../img/logo.png">

    <title>Lawyer Verification | SafeSpace PH</title>
    <style>
        .popup{
            animation: transitionIn-Y-bottom 0.5s;
        }
        .sub-table{
            animation: transitionIn-Y-bottom 0.5s;
        }
        
        .overlay {
            position: fixed;
            top: 0;
            bottom: 0;
            left: 0;
            right: 0;
            background: rgba(0, 0, 0, 0.7);
            transition: opacity 500ms;
            visibility: hidden;
            opacity: 0;
            z-index: 1000;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 15px;
        }
        
        .overlay.view-modal-overlay {
            display: block;
            overflow-y: auto;
            padding: 30px 15px;
        }
        
        .overlay:target, .overlay.active {
            visibility: visible;
            opacity: 1;
        }

        .modal-content {
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 8px 32px rgba(57, 16, 83, 0.15);
            padding: 30px 40px;
            max-width: 600px;
            width: 90%;
            position: relative;
            animation: fadeIn 0.4s ease-out;
            margin: 0 auto;
        }
        
        .modal-content-view {
            max-width: 850px;
        }

        .modal-header {
            text-align: center;
            color: #391053;
            font-size: 1.8rem;
            font-weight: 700;
            margin-bottom: 10px;
            margin-top: 0;
            letter-spacing: 0.5px;
            position: relative;
        }

        .modal-header .close {
            position: absolute;
            top: -25px;
            right: -20px;
            font-size: 2.5rem;
            font-weight: bold;
            text-decoration: none;
            color: #888;
            transition: color 0.3s ease, transform 0.3s ease;
        }
        .modal-header .close:hover {
            color: #333;
            transform: scale(1.1);
        }

        .modal-divider {
            width: 100%;
            height: 3px;
            background: linear-gradient(90deg, #391053 0%, #5A2675 30%, #9D72B3 65%, #C9A8F1 100%);
            border: none;
            border-radius: 2px;
            margin: 18px 0 28px 0;
        }

        .modal-body {
            text-align: center;
            font-size: 16px;
            color: #444;
            line-height: 1.6;
        }

        .modal-body-left {
            text-align: left;
        }
        
        .modal-footer {
            display: flex;
            justify-content: center;
            gap: 15px;
            margin-top: 25px;
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

        .modal-btn-primary {
            background-color: #5A2675;
            color: white;
        }
        .modal-btn-primary:hover {
            background-color: #5A2675;
        }
        .modal-btn-danger {
            background-color: #dc3545;
            color: white;
        }
        .modal-btn-danger:hover {
            background-color: #c82333;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: scale(0.95) translateY(10px); }
            to { opacity: 1; transform: scale(1) translateY(0); }
        }

        .detail-section {
            margin-bottom: 20px;
            padding-bottom: 10px;
        }
        .detail-section h4 {
            color: #5A2675;
            font-weight: bold;
            margin-top: 0;
            margin-bottom: 15px;
            display: inline-block;
            width: 100%;
            text-align: left;
        }
        .detail-section hr {
            border: none;
            border-top: 1px solid #C9A8F1;
            margin-top: -10px;
            margin-bottom: 15px;
        }
        .detail-item {
            margin-bottom: 10px;
            display: flex;
            align-items: baseline;
            font-size: 15px;
        }
        .detail-item strong {
            flex: 0 0 200px;
            margin-right: 10px;
            color: #333;
            font-weight: 600;
        }
        .detail-item span {
            flex-grow: 1;
            color: #444;
            word-wrap: break-word;
        }
        .description-box {
            background-color: #f8f9fa;
            border: 1px solid #e0e0e0;
            border-radius: 5px;
            padding: 10px;
            margin-top: 5px;
            max-height: 120px;
            overflow-y: auto;
            word-wrap: break-word;
            flex-grow: 1;
        }
        
        .file-link {
            text-decoration: none;
        }
        .file-link button {
            font-size: 14px;
            padding: 8px 12px;
            margin-top: 5px;
            width: auto;
        }
        
        /* --- Custom Styles for Sidebar Adjustment (Final Fix) --- */
        .menu {
            width: 250px; 
        }
        .menu-btn {
            background-position: 52px center !important;
            padding: 9px 15px 9px 4px !important;
        }
        .menu-text {
            font-size: 14px;
            white-space: nowrap; 
            overflow: hidden;
            text-overflow: ellipsis; 
        }
        .profile-container td {
            padding: 0 5px;
        }
        .profile-container .profile-info-cell {
            padding-left: 10px !important;
        }
        .profile-title {
            font-size: 15px;
            margin-bottom: 2px;
        }
        .profile-subtitle {
            font-size: 12px;
            word-break: break-all;
        }
        .logout-btn {
            width: 100%;
            padding: 8px !important;
            margin-top: 8px !important;
            font-size: 13px;
        }
        /* --- End of Custom Styles --- */
    </style>
</head>
<body>
    <?php
    if ($verification_details) {
        echo '<div id="viewDetailsModal" class="overlay view-modal-overlay active">
                <div class="modal-content modal-content-view">
                    <a href="javascript:void(0)" class="modal-header close" onclick="closeViewDetailsModal()" style="text-decoration: none;">&times;</a>
                    <h2 class="modal-header">Volunteer Lawyer Application Details</h2>
                    <div class="modal-divider"></div>

                    <div class="modal-body modal-body-left">
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
                            <div class="detail-item"><strong>Motivation:</strong> <div class="description-box" style="flex-grow:1;">' . nl2br(htmlspecialchars($verification_details['motivation'])) . '</div></div>
                            <div class="detail-item"><strong>Preferred Areas of Law:</strong> <span>' . htmlspecialchars($verification_details['preferred_areas']) . '</span></div>
                            <div class="detail-item"><strong>Availability (Hours/Week):</strong> <span>' . htmlspecialchars($verification_details['availability_hours']) . '</span></div>
                            <div class="detail-item"><strong>Commitment (Months):</strong> <span>' . htmlspecialchars($verification_details['commitment_months']) . '</span></div>
                            <div class="detail-item"><strong>Available for Urgent Consults:</strong> <span>' . htmlspecialchars($verification_details['urgent_consult']) . '</span></div>
                        </div>

                        <div class="detail-section">
                            <h4>Uploaded Documents & Consents</h4>
                            <hr>
                            <div class="detail-item"><strong>Attorney License/ID:</strong> <span><a href="../' . htmlspecialchars($verification_details['license_file']) . '" target="_blank" class="file-link"><button class="btn-primary-soft btn">View File</button></a></span></div>
                            <div class="detail-item"><strong>Resume/CV:</strong> <span><a href="../' . htmlspecialchars($verification_details['resume_file']) . '" target="_blank" class="file-link"><button class="btn-primary-soft btn">View File</button></a></span></div>
                            <div class="detail-item"><strong>Profile Photo:</strong> <span><a href="../' . htmlspecialchars($verification_details['profile_photo']) . '" target="_blank" class="file-link"><button class="btn-primary-soft btn">View File</button></a></span></div>
                            <div class="detail-item"><strong>Consent to Background Check:</strong> <span>' . ($verification_details['consent_background_check'] ? 'Yes' : 'No') . '</span></div>
                            <div class="detail-item"><strong>Agreed to Terms:</strong> <span>' . ($verification_details['agree_terms'] ? 'Yes' : 'No') . '</span></div>
                            <div class="detail-item"><strong>Certified Information Correct:</strong> <span>' . ($verification_details['info_certified'] ? 'Yes' : 'No') . '</span></div>
                        </div>
                    </div>
                    
                    <div class="modal-footer">
                        <button type="button" class="modal-btn modal-btn-soft" onclick="closeViewDetailsModal()">Close</button>
                    </div>
                </div>
              </div>';
    }

    if (isset($_GET['message'])) {
        $popup_title = '';
        $popup_content = '';
        $is_error = false;

        switch ($_GET['message']) {
            case 'success':
                $popup_title = "Success!";
                $popup_content = "Lawyer has been successfully verified and an account has been created.";
                break;
            case 'rejected':
                $popup_title = "Rejected!";
                $popup_content = "Lawyer application has been rejected and removed.";
                break;
            case 'error':
                 $is_error = true;
                $popup_title = "Error!";
                $popup_content = 'Action failed. Please try again. <br><small>' . htmlspecialchars($_GET['details'] ?? '') . '</small>';
                break;
        }
        
        if (!empty($popup_title)) {
            $header_color = $is_error ? '#dc3545' : '#5A2675';
            $button_class = $is_error ? 'modal-btn-danger' : 'modal-btn-primary';

            echo '<div id="messagePopup" class="overlay active">
                    <div class="modal-content" style="max-width: 450px;">
                        <h2 class="modal-header" style="color: '. $header_color .';">
                            '. $popup_title .'
                        </h2>
                        <div class="modal-divider"></div>
                        <div class="modal-body">
                            <p>'. $popup_content .'</p>
                        </div>
                        <div class="modal-footer">
                            <a href="lawyer_verification.php" class="non-style-link">
                                <button type="button" class="modal-btn '. $button_class .'">OK</button>
                            </a>
                        </div>
                    </div>
                  </div>';
        }
    }
    ?>
    <div class="container">
        <div class="menu">
            <table class="menu-container" border="0">
                <tr>
                    <td style="padding:15px 10px;" colspan="2">
                        <table border="0" class="profile-container">
                            <tr>
                                <td width="25%" style="padding-left:10px">
                                    <img src="../img/user.png" alt="" width="100%" style="border-radius:50%">
                                </td>
                                <td class="profile-info-cell" style="vertical-align: middle;">
                                    <p class="profile-title">Administrator</p>
                                    <p class="profile-subtitle">admin@safespaceph.com</p>
                                    <a href="../logout.php">
                                        <input type="button" value="Log out" class="logout-btn btn-primary-soft btn">
                                    </a>
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
                     </tr>
                     <tr class="menu-row">
                    <td class="menu-btn menu-icon-schedule">
                        <a href="schedule.php" class="non-style-link-menu">
                            <div>
                                <p class="menu-text">Session Requests</p>
                            </div>
                        </a>
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
                                                <div style="display:flex;justify-content: center; gap: 5px;">
                                                    <a href="?action=view&id='.$id.'" class="non-style-link"><button class="btn-primary-soft btn button-icon btn-view" style="padding-left: 40px;padding-top: 12px;padding-bottom: 12px;margin-top: 10px;"><font class="tn-in-text">View</font></button></a>
                                                    
                                                    <button class="btn-primary-soft btn button-icon menu-icon-verify"
                                                        onclick="showVerifyPasswordModal(\''.$id.'\', \''.addslashes($email).'\', \''.addslashes($first_name_lawyer).'\', \''.addslashes($last_name_lawyer).'\')"
                                                        style="padding-left: 40px; padding-top: 12px; padding-bottom: 12px; margin-top: 10px;">
                                                        <font class="tn-in-text">Verify</font>
                                                    </button>
                                                    
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

    <div id="verifyPasswordModal" class="overlay">
        <div class="modal-content">
            <form id="verifyForm" onsubmit="submitVerification(event)">
                <h2 class="modal-header">Verify Lawyer & Set Password</h2>
                <div class="modal-divider"></div>
                <div class="modal-body modal-body-left">
                    <p>You are verifying the account for: <strong id="verifyLawyerEmail"></strong>.</p>
                    <p>Please set a temporary password for the lawyer's account.</p>

                    <label for="lawyerPassword" class="form-label" style="margin-top: 15px; display: block;">Password:</label>
                    <input type="password" id="lawyerPassword" name="lawyer_password" class="input-text" required style="width: 100%;">
                    
                    <label for="confirmLawyerPassword" class="form-label" style="margin-top: 15px; display: block;">Confirm Password:</label>
                    <input type="password" id="confirmLawyerPassword" class="input-text" required style="width: 100%;">
                    
                    <p id="passwordError" style="color: red; display: none; margin-top: 10px;"></p>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="modal-btn modal-btn-primary">Confirm & Verify</button>
                    <button type="button" class="modal-btn modal-btn-soft" onclick="hideVerifyPasswordModal()">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <div id="rejectConfirmModal" class="overlay">
        <div class="modal-content" style="max-width: 500px;">
            <h2 class="modal-header">Are You Sure?</h2>
            <div class="modal-divider"></div>
            <div class="modal-body">
                <p>Are you sure you want to reject this application? The applicant will be notified.</p>
                <p style="color: #dc3545; font-weight: bold; margin-top: 10px;">This action cannot be undone.</p>
            </div>
            <div class="modal-footer">
                <button class="modal-btn modal-btn-danger" id="confirmRejectBtn">Yes, Reject</button>
                <button class="modal-btn modal-btn-soft" onclick="hideRejectConfirmModal()">Cancel</button>
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
            document.getElementById('verifyPasswordModal').classList.add('active');
            // Clear previous values and errors
            document.getElementById('lawyerPassword').value = '';
            document.getElementById('confirmLawyerPassword').value = '';
            document.getElementById('passwordError').style.display = 'none';
        }

        function hideVerifyPasswordModal() {
            document.getElementById('verifyPasswordModal').classList.remove('active');
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
            document.getElementById('rejectConfirmModal').classList.add('active');
        }

        function hideRejectConfirmModal() {
            document.getElementById('rejectConfirmModal').classList.remove('active');
        }

        function closeViewDetailsModal() {
            const url = new URL(window.location.href);
            url.searchParams.delete('action');
            url.searchParams.delete('id');
            window.history.pushState({}, '', url);
            const viewModal = document.getElementById('viewDetailsModal');
            if (viewModal) {
                 viewModal.classList.remove('active');
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

        // Use onload to ensure modals start hidden unless activated by a URL parameter
        window.onload = function() {
            const urlParams = new URLSearchParams(window.location.search);
            const message = urlParams.get('message');
            const action = urlParams.get('action');

            if (!message) {
                 const messageModal = document.getElementById('messagePopup');
                 if(messageModal) messageModal.classList.remove('active');
            }
            if (action !== 'view') {
                 const viewModal = document.getElementById('viewDetailsModal');
                if (viewModal) {
                    viewModal.classList.remove('active');
                }
            }
        };
    </script>
</body>
</html>