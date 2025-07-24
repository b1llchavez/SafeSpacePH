<?php

session_start(); 

if(isset($_SESSION["user"])){
    if(($_SESSION["user"])=="" or $_SESSION['usertype']!='a'){
        header("location: ../login.php");
        exit(); 
    }
}else{
    header("location: ../login.php");
    exit(); 
}


include("../connection.php");
require_once '../send_email.php'; 


if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] == 'delete_client') {
    $client_id = $_POST['client_id'];
    $client_email = $_POST['client_email'];


    $database->begin_transaction();

    try {

        $stmt_client = $database->prepare("DELETE FROM client WHERE cid = ?");
        if ($stmt_client) {
            $stmt_client->bind_param("i", $client_id);
            $stmt_client->execute();
            $stmt_client->close();
        } else {
            throw new Exception("Failed to prepare client delete statement: " . $database->error);
        }


        $stmt_webuser = $database->prepare("DELETE FROM webuser WHERE email = ?");
        if ($stmt_webuser) {
            $stmt_webuser->bind_param("s", $client_email);
            $stmt_webuser->execute();
            $stmt_webuser->close();
        } else {
            throw new Exception("Failed to prepare webuser delete statement: " . $database->error);
        }


        $database->commit();

        header("Location: client.php?message=deleted_success");
        exit(); 

    } catch (Exception $e) {

        $database->rollback();
        error_log("Client deletion failed: " . $e->getMessage()); 
        header("Location: client.php?message=deleted_error&details=" . urlencode($e->getMessage()));
        exit(); 
    }
}


if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] == 'unverify_client') {
    $client_id = $_POST['client_id'];
    $client_email = $_POST['client_email'];

    $database->begin_transaction();

    try {

        $stmt_check_usertype = $database->prepare("SELECT usertype FROM webuser WHERE email = ?");
        if ($stmt_check_usertype) {
            $stmt_check_usertype->bind_param("s", $client_email);
            $stmt_check_usertype->execute();
            $result_check_usertype = $stmt_check_usertype->get_result();
            $user_data = $result_check_usertype->fetch_assoc();
            $stmt_check_usertype->close();


            if ($user_data && $user_data['usertype'] == 'u') {
                throw new Exception("unverified_already");
            }
        } else {
            throw new Exception("Failed to prepare usertype check statement: " . $database->error);
        }


        $stmt_get_name = $database->prepare("SELECT cname FROM client WHERE cemail = ?");
        $client_name = 'Client'; 
        if ($stmt_get_name) {
            $stmt_get_name->bind_param("s", $client_email);
            $stmt_get_name->execute();
            $result_name = $stmt_get_name->get_result();
            if ($client_data = $result_name->fetch_assoc()) {
                $client_name = $client_data['cname'];
            }
            $stmt_get_name->close();
        }


        $stmt_webuser_update = $database->prepare("UPDATE webuser SET usertype = 'u' WHERE email = ?");
        if ($stmt_webuser_update) {
            $stmt_webuser_update->bind_param("s", $client_email);
            $stmt_webuser_update->execute();
            $stmt_webuser_update->close();
        } else {
            throw new Exception("Failed to prepare webuser update statement: " . $database->error);
        }


        $stmt_identity_update = $database->prepare("UPDATE identity_verifications SET is_verified = 0 WHERE email = ?");
        if ($stmt_identity_update) {
            $stmt_identity_update->bind_param("s", $client_email);
            $stmt_identity_update->execute();
            $stmt_identity_update->close();
        } else {
            throw new Exception("Failed to prepare identity_verifications update statement: " . $database->error);
        }

        $database->commit();


        try {
            sendUnverificationNoticeEmail($client_email, $client_name);
        } catch (Exception $e) {

            error_log("Failed to send unverification email to {$client_email}: " . $e->getMessage());
        }

        header("Location: client.php?message=unverified_success");
        exit();

    } catch (Exception $e) {
        $database->rollback();
        error_log("Client unverification failed: " . $e->getMessage());
        if ($e->getMessage() == "unverified_already") {
            header("Location: client.php?message=unverified_already");
        } else {
            header("Location: client.php?message=unverified_error&details=" . urlencode($e->getMessage()));
        }
        exit();
    }
}



$client_details = null; 
if (isset($_GET['action']) && $_GET['action'] == 'view' && isset($_GET['id'])) {
    $view_id = $_GET['id'];

    $stmt_view = $database->prepare("
        SELECT
            c.*,
            w.email, w.usertype,
            iv.first_name, iv.middle_name, iv.last_name, iv.suffix, iv.dob, iv.sex, iv.civil_status, iv.citizenship, iv.birth_place,
            iv.contact_number, iv.present_address, iv.permanent_address,
            iv.emergency_contact_name, iv.emergency_contact_number, iv.emergency_contact_relationship,
            iv.id_type, iv.id_number, iv.id_photo_front_path, iv.id_photo_back_path, iv.profile_photo_path, iv.agree_terms
        FROM client c
        INNER JOIN webuser w ON c.cemail = w.email
        LEFT JOIN identity_verifications iv ON c.cemail = iv.email
        WHERE c.cid = ?
    ");
    $stmt_view->bind_param("i", $view_id);
    $stmt_view->execute();
    $result_view = $stmt_view->get_result();
    $client_details = $result_view->fetch_assoc();
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
    <link rel="icon" type="image/png" href="https://i.ibb.co/qYYZs46L/logo.png">


    <title>Client | SafeSpace PH</title>
    <style>
        .dash-body {
            overflow-y: auto;
        }
        .btn-unverify {
            background-repeat: no-repeat;
            background-position: left 5px center;
        }
        .btn-unverify:hover {
            background-repeat: no-repeat;
            background-position: left 5px center;
        }
        .popup{
            animation: transitionIn-Y-bottom 0.5s;
        }
        .sub-table{
            animation: transitionIn-Y-bottom 0.5s;
        }
        .status-btn {
            padding: 8px 15px;
            border-radius: 20px;
            color: #fff !important;
            font-weight: bold;
            display: inline-block;
            text-align: center;
        }
        .status-verified {
            background-color: #4CAF50;
        }
        .status-unverified {
            background-color: #f44336;
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
        
        .overlay {
            position: fixed;
            top: 0;
            bottom: 0;
            left: 0;
            right: 0;
            background: rgba(0, 0, 0, 0.7);
            transition: opacity 500ms;
            display: none;  
            z-index: 1000;
            align-items: center;
            justify-content: center;
            padding: 15px;
        }
        .overlay.view-modal-overlay {
            padding: 30px 15px;
            align-items: flex-start;
            overflow-y: auto;  
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
            text-align: left;
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
            top: -22px;
            right: -30px;
            font-size: 24px;
            font-weight: bold;
            text-decoration: none;
            color: #aaa;
            border: none;
            background: transparent;
            padding: 0;
            width: 32px;
            height: 30px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: background-color 0.2s, color 0.2s;
             z-index: 1;
        }
        .modal-header .close:hover {
            background-color: #f0e9f7;
            color: #5A2675;
            transform: none;
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
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
            text-align: center;
        }
        .modal-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(0,0,0,0.15);
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
        .modal-btn-secondary {
            background: #f0f0f0;
            color: #555;
            border: 1px solid #ddd;
        }
        .modal-btn-secondary:hover {
            background: #e0e0e0;
            border-color: #ccc;
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
    </style>
</head>
<body>
    <?php
    if ($client_details) {
        echo '<div id="viewDetailsModal" class="overlay view-modal-overlay" style="display:flex;">
                <div class="modal-content modal-content-view">
                    <h2 class="modal-header">
                        Client Details
                        <a href="javascript:void(0)" onclick="closeViewDetailsModal()" class="close">&times;</a>
                    </h2>
                    <div class="modal-divider"></div>
                    <div class="modal-body modal-body-left">
                        <div class="detail-section">
                            <h4>Personal Information</h4>
                            <hr>
                            <div class="detail-item"><strong>Client ID:</strong> <span>C-' . htmlspecialchars($client_details['cid']) . '</span></div>
                            <div class="detail-item"><strong>Full Name:</strong> <span>' . htmlspecialchars($client_details['cname']) . '</span></div>
                            <div class="detail-item"><strong>First Name:</strong> <span>' . htmlspecialchars($client_details['first_name'] ?? 'N/A') . '</span></div>
                            <div class="detail-item"><strong>Middle Name:</strong> <span>' . htmlspecialchars($client_details['middle_name'] ?? 'N/A') . '</span></div>
                            <div class="detail-item"><strong>Last Name:</strong> <span>' . htmlspecialchars($client_details['last_name'] ?? 'N/A') . '</span></div>
                            <div class="detail-item"><strong>Suffix:</strong> <span>' . htmlspecialchars($client_details['suffix'] ?? 'N/A') . '</span></div>
                            <div class="detail-item"><strong>Sex:</strong> <span>' . htmlspecialchars($client_details['sex'] ?? 'N/A') . '</span></div>
                            <div class="detail-item"><strong>Civil Status:</strong> <span>' . htmlspecialchars($client_details['civil_status'] ?? 'N/A') . '</span></div>
                            <div class="detail-item"><strong>Citizenship:</strong> <span>' . htmlspecialchars($client_details['citizenship'] ?? 'N/A') . '</span></div>
                            <div class="detail-item"><strong>Birth Place:</strong> <span>' . htmlspecialchars($client_details['birth_place'] ?? 'N/A') . '</span></div>
                            <div class="detail-item"><strong>Verified:</strong> <span><span class="status-btn ' . (($client_details['usertype'] == 'c') ? 'status-verified' : 'status-unverified') . '">' . (($client_details['usertype'] == 'c') ? 'Yes' : 'No') . '</span></span></div>
                        </div>

                        <div class="detail-section">
                            <h4>Contact Information</h4>
                            <hr>
                            <div class="detail-item"><strong>Email:</strong> <span>' . htmlspecialchars($client_details['cemail']) . '</span></div>
                            <div class="detail-item"><strong>Phone Number:</strong> <span>' . htmlspecialchars($client_details['ctel'] ?? 'N/A', ENT_QUOTES, 'UTF-8') . '</span></div>
                        </div>

                        <div class="detail-section">
                            <h4>Address Information</h4>
                            <hr>
                            <div class="detail-item"><strong>Address:</strong> <span>' . htmlspecialchars($client_details['caddress'] ?? 'N/A', ENT_QUOTES, 'UTF-8') . '</span></div>
                            <div class="detail-item"><strong>Present Address:</strong> <span>' . htmlspecialchars($client_details['present_address'] ?? 'N/A') . '</span></div>
                            <div class="detail-item"><strong>Permanent Address:</strong> <span>' . htmlspecialchars($client_details['permanent_address'] ?? 'N/A') . '</span></div>
                        </div>

                        <div class="detail-section">
                            <h4>Emergency Contact</h4>
                            <hr>
                            <div class="detail-item"><strong>Name:</strong> <span>' . htmlspecialchars($client_details['emergency_contact_name'] ?? 'N/A') . '</span></div>
                            <div class="detail-item"><strong>Number:</strong> <span>' . htmlspecialchars($client_details['emergency_contact_number'] ?? 'N/A') . '</span></div>
                            <div class="detail-item"><strong>Relationship:</strong> <span>' . htmlspecialchars($client_details['emergency_contact_relationship'] ?? 'N/A') . '</span></div>
                        </div>

                        <div class="detail-section">
                            <h4>ID & Photos</h4>
                            <hr>
                            <div class="detail-item"><strong>ID Type:</strong> <span>' . htmlspecialchars($client_details['id_type'] ?? 'N/A') . '</span></div>
                            <div class="detail-item"><strong>ID Number:</strong> <span>' . htmlspecialchars($client_details['id_number'] ?? 'N/A') . '</span></div>
                            <div class="detail-item"><strong>Front of ID Photo:</strong> <span>' . (isset($client_details['id_photo_front_path']) && $client_details['id_photo_front_path'] ? '<a href="../' . htmlspecialchars($client_details['id_photo_front_path']) . '" target="_blank" class="file-link">View File</a>' : 'N/A') . '</span></div>
                            <div class="detail-item"><strong>Back of ID Photo:</strong> <span>' . (isset($client_details['id_photo_back_path']) && $client_details['id_photo_back_path'] ? '<a href="../' . htmlspecialchars($client_details['id_photo_back_path']) . '" target="_blank" class="file-link">View File</a>' : 'N/A') . '</span></div>
                            <div class="detail-item"><strong>Profile Photo:</strong> <span>' . (isset($client_details['profile_photo_path']) && $client_details['profile_photo_path'] ? '<a href="../' . htmlspecialchars($client_details['profile_photo_path']) . '" target="_blank" class="file-link">View File</a>' : 'N/A') . '</span></div>
                        </div>

                        <div class="detail-section" style="border-bottom: none; margin-bottom: 0;">
                            <div class="detail-item"><strong>Agreed Terms:</strong> <span>' . (isset($client_details['agree_terms']) ? ($client_details['agree_terms'] ? 'Yes' : 'No') : 'N/A') . '</span></div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button class="modal-btn modal-btn-secondary" onclick="closeViewDetailsModal()">Close</button>
                    </div>
                </div>
            </div>';
    } else if (isset($_GET['action']) && $_GET['action'] == 'view' && !isset($_GET['id'])) {
        echo '<div id="viewDetailsModal" class="overlay" style="display:flex;">
                <div class="modal-content" style="max-width: 450px;">
                     <h2 class="modal-header" style="color: #dc3545;">Error</h2>
                     <div class="modal-divider"></div>
                     <div class="modal-body">
                        <p>Client ID not provided.</p>
                     </div>
                     <div class="modal-footer">
                        <button class="modal-btn modal-btn-secondary" onclick="closeViewDetailsModal()">Close</button>
                     </div>
                </div>
              </div>';
    }

    if (isset($_GET['message'])) {
        $action_result = $_GET['message'];
        $popup_title = '';
        $popup_content = '';
        $is_error = false;
        $header_color = '#5A2675';

        switch ($action_result) {
            case 'deleted_success':
                $popup_title = "Deleted Successfully!";
                $popup_content = "The client has been permanently deleted.";
                break;
            case 'unverified_success':
                $popup_title = "Verification Revoked!";
                $popup_content = "Client has been successfully unverified.";
                break;
            case 'deleted_error':
                $is_error = true;
                $popup_title = "Deletion Error!";
                $popup_content = "Client deletion failed. Please try again.";
                if (isset($_GET['details'])) {
                    $popup_content .= '<br><small style="color:#555;">Details: ' . htmlspecialchars($_GET['details']) . '</small>';
                }
                break;
            case 'unverified_error':
                $is_error = true;
                $popup_title = "Unverification Error!";
                $popup_content = "Client unverification failed. Please try again.";
                if (isset($_GET['details'])) {
                    $popup_content .= '<br><small style="color:#555;">Details: ' . htmlspecialchars($_GET['details']) . '</small>';
                }
                break;
            case 'unverified_already':
                $is_error = true;
                $popup_title = "Warning";
                $popup_content = 'This user is already unverified or was never verified.';
                break;
        }
        
        if ($is_error) {
            $header_color = '#dc3545';
        }

        if (!empty($popup_title)) {
            echo '
            <div id="messagePopup" class="overlay" style="display: flex;">
                <div class="modal-content" style="max-width: 450px;">
                    <h2 class="modal-header" style="color: '. $header_color .';">
                        '. $popup_title .'
                    </h2>
                    <div class="modal-divider"></div>
                    <div class="modal-body">
                        <p>'. $popup_content .'</p>
                    </div>
                    <div class="modal-footer">
                        <a href="client.php" class="non-style-link">
                            <button type="button" class="modal-btn modal-btn-primary">OK</button>
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
                    <td class="menu-btn menu-icon-schedule">
                        <a href="schedule.php" class="non-style-link-menu">
                            <div>
                                <p class="menu-text">Schedules</p>
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
                    <td class="menu-btn menu-icon-client menu-active menu-icon-client-active">
                        <a href="client.php" class="non-style-link-menu non-style-link-menu-active">
                            <div>
                                <p class="menu-text menu-text-active">All Clients</p>
                            </div>
                        </a>
                    </td>
                </tr>
            </table>
        </div>
        <div class="dash-body">
            <table border="0" width="100%" style=" border-spacing: 0;margin:0;padding:0;margin-top:25px; ">
                <tr >
                    <td>
                        <form action="" method="post" class="header-search">
                            <input type="search" name="search" class="input-text header-searchbar" placeholder="Search Client name or Email" list="client">&nbsp;&nbsp;
                            <?php
                                echo '<datalist id="client">';
                                $list11 = $database->query("select  cname,cemail from client;");

                                for ($y=0;$y<$list11->num_rows;$y++){
                                    $row00=$list11->fetch_assoc();
                                    $l=$row00["cname"];
                                    $c=$row00["cemail"];
                                    echo "<option value='$l'><br/>";
                                    echo "<option value='$c'><br/>";
                                };

                            echo ' </datalist>';
                            ?>
                            <input type="Submit" value="Search" class="login-btn btn-primary btn" style="padding-left: 25px;padding-right: 25px;padding-top: 10px;padding-bottom: 10px;">
                        </form>
                    </td>
                    <td width="15%">
                        <p style="font-size: 14px;color: rgb(119, 119, 119);padding: 0;margin: 0;text-align: right;">
                            Today's Date
                        </p>
                        <p class="heading-sub12" style="padding: 0;margin: 0;">
                            <?php
                        date_default_timezone_set('Asia/Manila');
                        $date = date('Y-m-d');
                        echo $date;
                        ?>
                        </p>
                    </td>
                    <td width="10%">
                        <button  class="btn-label"  style="display: flex;justify-content: center;align-items: center;"><img src="../img/calendar.svg" width="100%"></button>
                    </td>
                </tr>
                <tr>
                    <td colspan="4" style="padding-top:10px;">
                        <p class="heading-main12" style="margin-left: 45px;font-size:18px;color:rgb(49, 49, 49)">All Clients (<?php echo $list11->num_rows; ?>)</p>
                    </td>
                </tr>
                <?php
                    if($_POST){
                        $keyword=$_POST["search"];
                       $sqlmain= "select * from client inner join webuser on client.cemail=webuser.email where client.cemail LIKE '%$keyword%' or cname LIKE '%$keyword%'";
                    }else{
                        $sqlmain= "select * from client inner join webuser on client.cemail=webuser.email order by cid desc";
                    }
                ?>
                <tr>
                   <td colspan="4">
                       <center>
                        <div class="abc scroll">
                        <table width="93%" class="sub-table scrolldown"  style="border-spacing:0;">
                        <thead>
                        <tr>
                                <th class="table-headin">Name</th>
                                <th class="table-headin">Phone Number</th>
                                <th class="table-headin">Email</th>
                                <th class="table-headin">Date of Birth</th>
                                <th class="table-headin">Verified</th>
                                <th class="table-headin">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                                $result= $database->query($sqlmain);
                                if($result->num_rows==0){
                                    echo '<tr>
                                    <td colspan="6">
                                    <br><br><br><br>
                                    <center>
                                    <img src="../img/notfound.svg" width="25%">
                                    <br>
                                    <p class="heading-main12" style="margin-left: 45px;font-size:20px;color:rgb(49, 49, 49)">We couldnt find anything related to your keywords !</p>
                                    <a class="non-style-link" href="client.php"><button  class="login-btn btn-primary-soft btn"  style="display: flex;justify-content: center;align-items: center;margin-left:20px;">&nbsp; Show all Clients &nbsp;</font></button>
                                    </a>
                                    </center>
                                    <br><br><br><br>
                                    </td>
                                    </tr>';
                                }
                                else{
                                for ( $x=0; $x<$result->num_rows;$x++){
                                    $row=$result->fetch_assoc();
                                    $cid=$row["cid"];
                                    $name=$row["cname"];
                                    $email=$row["cemail"];
                                    $dob=$row["cdob"];
                                    $tel=$row["ctel"];
                                    $usertype=$row["usertype"];

                                    $verified_text = ($usertype == 'c') ? 'Yes' : 'No';
                                    $verified_class = ($usertype == 'c') ? 'status-verified' : 'status-unverified';

                                    echo '<tr>
                                            <td> &nbsp;'. substr($name ?? '',0,35) .'</td>
                                            <td>'.substr($tel ?? '',0,10).'</td>
                                            <td>'.substr($email ?? '',0,20).'</td>
                                            <td>'.substr($dob ?? '',0,10).'</td>
                                            <td><span class="status-btn ' . $verified_class . '">' . $verified_text . '</span></td>
                                            <td>
                                                <div style="display:flex;justify-content: center;">
                                                    <a href="?action=view&id='.$cid.'" class="btn-primary-soft btn button-icon btn-view" style="padding-left: 40px; padding-top: 12px; padding-bottom: 12px; margin-top: 10px;">
                                                        <font class="tn-in-text">View</font>
                                                    </a>
                                                    &nbsp;&nbsp;&nbsp;
                                                    <button class="btn-primary-soft btn button-icon btn-unverify"
                                                        onclick="event.stopPropagation(); showUnverifyConfirmModal(\''.$cid.'\', \''.$email.'\')"
                                                        style="padding-left: 40px; padding-top: 12px; padding-bottom: 12px; margin-top: 10px;">
                                                        <font class="tn-in-text">Unverify</font>
                                                    </button>
                                                    &nbsp;&nbsp;&nbsp;
                                                    <button class="btn-primary-soft btn button-icon btn-delete"
                                                        onclick="event.stopPropagation(); showDeleteConfirmModal(\''.$cid.'\', \''.$email.'\')"
                                                        style="padding-left: 40px; padding-top: 12px; padding-bottom: 12px; margin-top: 10px;">
                                                        <font class="tn-in-text">Delete</font>
                                                    </button>
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

    <div id="deleteConfirmModal" class="overlay">
        <div class="modal-content" style="max-width: 500px;">
            <h2 class="modal-header">Confirm Deletion</h2>
            <div class="modal-divider"></div>
            <div class="modal-body">
                <p>Are you sure you want to delete this client?</p>
                <p style="color: #dc3545; font-weight: bold; margin-top: 10px;">This action cannot be undone.</p>
            </div>
            <div class="modal-footer">
                <button class="modal-btn modal-btn-danger" id="confirmDeleteBtn">Yes, Delete</button>
                <button type="button" class="modal-btn modal-btn-secondary" onclick="hideDeleteConfirmModal()">Cancel</button>
            </div>
        </div>
    </div>

    <div id="unverifyConfirmModal" class="overlay">
        <div class="modal-content" style="max-width: 500px;">
            <h2 class="modal-header">Confirm Unverification</h2>
            <div class="modal-divider"></div>
            <div class="modal-body">
                <p>Are you sure you want to unverify this client?</p>
            </div>
            <div class="modal-footer">
                <button class="modal-btn modal-btn-primary" id="confirmUnverifyBtn">Yes, Unverify</button>
                <button type="button" class="modal-btn modal-btn-secondary" onclick="hideUnverifyConfirmModal()">Cancel</button>
            </div>
        </div>
    </div>

    <script>
        let currentClientId = null;
        let currentClientEmail = null;

        function showDeleteConfirmModal(clientId, clientEmail) {
            currentClientId = clientId;
            currentClientEmail = clientEmail;
            document.getElementById('deleteConfirmModal').style.display = 'flex';
        }

        function hideDeleteConfirmModal() {
            document.getElementById('deleteConfirmModal').style.display = 'none';
            currentClientId = null;
            currentClientEmail = null;
        }

        function showUnverifyConfirmModal(clientId, clientEmail) {
            currentClientId = clientId;
            currentClientEmail = clientEmail;
            document.getElementById('unverifyConfirmModal').style.display = 'flex';
        }

        function hideUnverifyConfirmModal() {
            document.getElementById('unverifyConfirmModal').style.display = 'none';
            currentClientId = null;
            currentClientEmail = null;
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

        document.getElementById('confirmDeleteBtn').addEventListener('click', function() {
            if (currentClientId && currentClientEmail) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = 'client.php';

                const actionInput = document.createElement('input');
                actionInput.type = 'hidden';
                actionInput.name = 'action';
                actionInput.value = 'delete_client';
                form.appendChild(actionInput);

                const idInput = document.createElement('input');
                idInput.type = 'hidden';
                idInput.name = 'client_id';
                idInput.value = currentClientId;
                form.appendChild(idInput);

                const emailInput = document.createElement('input');
                emailInput.type = 'hidden';
                emailInput.name = 'client_email';
                emailInput.value = currentClientEmail;
                form.appendChild(emailInput);

                document.body.appendChild(form);
                form.submit();
            }
            hideDeleteConfirmModal();
        });

        document.getElementById('confirmUnverifyBtn').addEventListener('click', function() {
            if (currentClientId && currentClientEmail) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = 'client.php';

                const actionInput = document.createElement('input');
                actionInput.type = 'hidden';
                actionInput.name = 'action';
                actionInput.value = 'unverify_client';
                form.appendChild(actionInput);

                const idInput = document.createElement('input');
                idInput.type = 'hidden';
                idInput.name = 'client_id';
                idInput.value = currentClientId;
                form.appendChild(idInput);

                const emailInput = document.createElement('input');
                emailInput.type = 'hidden';
                emailInput.name = 'client_email';
                emailInput.value = currentClientEmail;
                form.appendChild(emailInput);

                document.body.appendChild(form);
                form.submit();
            }
            hideUnverifyConfirmModal();
        });

        window.onload = function() {
            if (!window.location.search.includes('action=view') && !window.location.search.includes('message=')) {
                const viewModal = document.getElementById('viewDetailsModal');
                if (viewModal) {
                    viewModal.style.display = 'none';
                }
            }
        };
    </script>
</body>
</html>