<?php

session_start(); 


include("../connection.php");
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
    $client_email = $_POST['client_email'];

    $first_name = $_POST['first_name'] ?? '';
    $last_name = $_POST['last_name'] ?? '';


    $database->begin_transaction();

    try {
        if ($_POST['action'] == 'verify_user') {

            $stmt_check_webuser = $database->prepare("SELECT email FROM webuser WHERE email = ?");
            if ($stmt_check_webuser) {
                $stmt_check_webuser->bind_param("s", $client_email);
                $stmt_check_webuser->execute();
                $result_check_webuser = $stmt_check_webuser->get_result();
                $stmt_check_webuser->close();

                if ($result_check_webuser->num_rows > 0) {

                    $stmt_update_webuser = $database->prepare("UPDATE webuser SET usertype = 'c' WHERE email = ?");
                    if ($stmt_update_webuser) {
                        $stmt_update_webuser->bind_param("s", $client_email);
                        $stmt_update_webuser->execute();
                        $stmt_update_webuser->close();
                    } else {
                        throw new Exception("Failed to prepare webuser update statement: " . $database->error);
                    }
                } else {


                    $stmt_fetch_details = $database->prepare("SELECT * FROM identity_verifications WHERE id = ?");
                    if ($stmt_fetch_details) {
                        $stmt_fetch_details->bind_param("i", $verification_id);
                        $stmt_fetch_details->execute();
                        $result_details = $stmt_fetch_details->get_result();
                        $details = $result_details->fetch_assoc();
                        $stmt_fetch_details->close();

                        if ($details) {
                            $stmt_insert_webuser = $database->prepare("INSERT INTO webuser (email, usertype) VALUES (?, 'c')");
                            if ($stmt_insert_webuser) {
                                $stmt_insert_webuser->bind_param("s", $client_email);
                                $stmt_insert_webuser->execute();
                                $stmt_insert_webuser->close();
                            } else {
                                throw new Exception("Failed to prepare webuser insert statement: " . $database->error);
                            }
                        } else {
                            throw new Exception("Identity verification details not found for ID: " . $verification_id);
                        }
                    } else {
                        throw new Exception("Failed to prepare fetch details statement: " . $database->error);
                    }
                }
            } else {
                throw new Exception("Failed to prepare webuser check statement: " . $database->error);
            }


            $stmt_identity = $database->prepare("UPDATE identity_verifications SET is_verified = TRUE WHERE id = ?");
            if ($stmt_identity) {
                $stmt_identity->bind_param("i", $verification_id);
                $stmt_identity->execute();
                $stmt_identity->close();
            } else {
                throw new Exception("Failed to prepare identity_verifications update statement: " . $database->error);
            }


            sendVerificationApprovedNoticeToClient($client_email, $first_name . ' ' . $last_name);


            $database->commit();

            header("Location: client_verification.php?message=success");
            exit(); 

        } else if ($_POST['action'] == 'reject_user') {
            

            $stmt_get_name = $database->prepare("SELECT first_name, last_name FROM identity_verifications WHERE id = ?");
            $client_full_name = "Valued Client"; 
            if ($stmt_get_name) {
                $stmt_get_name->bind_param("i", $verification_id);
                $stmt_get_name->execute();
                $result_name = $stmt_get_name->get_result();
                if ($name_row = $result_name->fetch_assoc()) {
                    $client_full_name = $name_row['first_name'] . ' ' . $name_row['last_name'];
                }
                $stmt_get_name->close();
            }


            sendVerificationRejectedNoticeToClient($client_email, $client_full_name);


            $stmt_delete_verification = $database->prepare("DELETE FROM identity_verifications WHERE id = ?");
            if ($stmt_delete_verification) {
                $stmt_delete_verification->bind_param("i", $verification_id);
                $stmt_delete_verification->execute();
                $stmt_delete_verification->close();
            } else {
                throw new Exception("Failed to prepare delete verification statement: " . $database->error);
            }


            $database->commit();
            header("Location: client_verification.php?message=rejected");
            exit(); 
        }

    } catch (Exception $e) {

        $database->rollback();
        error_log("Action failed: " . $e->getMessage()); 
        header("Location: client_verification.php?message=error&details=" . urlencode($e->getMessage()));
        exit(); 
    }
}


$verification_details = null; 
if (isset($_GET['action']) && $_GET['action'] == 'view' && isset($_GET['id'])) {
    $view_id = $_GET['id'];
    $stmt_view = $database->prepare("SELECT * FROM identity_verifications WHERE id = ?");
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


    <title>Client Verification | SafeSpace PH</title>
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
            flex: 0 0 180px;  
            margin-right: 10px;  
            color: #000;
            font-weight: bold;  
        }
        #viewDetailsModal .detail-item span {  
            flex-grow: 1;  
            color: #333;
            word-wrap: break-word;  
            font-weight: normal;  
        }
        #viewDetailsModal .close-button {
            border: none;
    border-radius: 7px;
    padding: 12px 28px;
    font-size: 16px;
    color: #5A2675;
    font-weight: 600;
    cursor: pointer;
    transition: background 0.2s, box-shadow 0.2s;
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
                    <h3 style="text-align: center; color: #391053; font-size: 1.8rem; font-weight: 700; margin-bottom: 10px; margin-top: 0; letter-spacing: 0.5px; position: relative;">Verification Request Details</h3>
<div style="width: 100%; height: 3px; background: linear-gradient(90deg, #391053 0%, #5A2675 30%, #9D72B3 65%, #C9A8F1 100%); border: none; border-radius: 2px; margin: 18px 0 28px 0;"></div>

                    <div class="detail-section">
                        <h4>Personal Information</h4>
                        <hr>
                        <div class="detail-item"><strong>First Name:</strong> <span>' . htmlspecialchars($verification_details['first_name']) . '</span></div>
                        <div class="detail-item"><strong>Middle Name:</strong> <span>' . htmlspecialchars($verification_details['middle_name']) . '</span></div>
                        <div class="detail-item"><strong>Last Name:</strong> <span>' . htmlspecialchars($verification_details['last_name']) . '</span></div>
                        <div class="detail-item"><strong>Suffix:</strong> <span>' . htmlspecialchars($verification_details['suffix']) . '</span></div>
                        <div class="detail-item"><strong>Date of Birth:</strong> <span>' . htmlspecialchars($verification_details['dob']) . '</span></div>
                        <div class="detail-item"><strong>Sex:</strong> <span>' . htmlspecialchars($verification_details['sex']) . '</span></div>
                        <div class="detail-item"><strong>Civil Status:</strong> <span>' . htmlspecialchars($verification_details['civil_status']) . '</span></div>
                        <div class="detail-item"><strong>Citizenship:</strong> <span>' . htmlspecialchars($verification_details['citizenship']) . '</span></div>
                        <div class="detail-item"><strong>Birth Place:</strong> <span>' . htmlspecialchars($verification_details['birth_place']) . '</span></div>
                    </div>

                    <div class="detail-section">
                        <h4>Contact Information</h4>
                        <hr>
                        <div class="detail-item"><strong>Email:</strong> <span>' . htmlspecialchars($verification_details['email']) . '</span></div>
                        <div class="detail-item"><strong>Contact Number:</strong> <span>' . htmlspecialchars($verification_details['contact_number']) . '</span></div>
                    </div>

                    <div class="detail-section">
                        <h4>Address Information</h4>
                        <hr>
                        <div class="detail-item"><strong>Present Address:</strong> <span>' . htmlspecialchars($verification_details['present_address']) . '</span></div>
                        <div class="detail-item"><strong>Permanent Address:</strong> <span>' . htmlspecialchars($verification_details['permanent_address']) . '</span></div>
                    </div>

                    <div class="detail-section">
                        <h4>Emergency Contact</h4>
                        <hr>
                        <div class="detail-item"><strong>Name:</strong> <span>' . htmlspecialchars($verification_details['emergency_contact_name']) . '</span></div>
                        <div class="detail-item"><strong>Number:</strong> <span>' . htmlspecialchars($verification_details['emergency_contact_number']) . '</span></div>
                        <div class="detail-item"><strong>Relationship:</strong> <span>' . htmlspecialchars($verification_details['emergency_contact_relationship']) . '</span></div>
                    </div>

                    <div class="detail-section">
                        <h4>ID & Photos</h4>
                        <hr>
                        <div class="detail-item"><strong>ID Type:</strong> <span>' . htmlspecialchars($verification_details['id_type']) . '</span></div>
                        <div class="detail-item"><strong>ID Number:</strong> <span>' . htmlspecialchars($verification_details['id_number']) . '</span></div>
                        <div class="detail-item"><strong>ID Photo Front:</strong> <span><a href="../' . htmlspecialchars($verification_details['id_photo_front_path']) . '" target="_blank" class="file-link">View File</a></span></div>
                        <div class="detail-item"><strong>ID Photo Back:</strong> <span><a href="../' . htmlspecialchars($verification_details['id_photo_back_path']) . '" target="_blank" class="file-link">View File</a></span></div>
                        <div class="detail-item"><strong>Profile Photo:</strong> <span><a href="../' . htmlspecialchars($verification_details['profile_photo_path']) . '" target="_blank" class="file-link">View File</a></span></div>
                    </div>

                    <div class="detail-section" style="border-bottom: none; margin-bottom: 0;">
                        <div class="detail-item"><strong>Agreed Terms:</strong> <span>' . ($verification_details['agree_terms'] ? 'Yes' : 'No') . '</span></div>
                    </div>
                    
                    <button class="close-button" onclick="closeViewDetailsModal()">Close</button>
                </div>
              </div>';
    } else if (isset($_GET['action']) && $_GET['action'] == 'view' && !isset($_GET['id'])) {


        echo '<div id="viewDetailsModal" class="custom-modal" style="display:flex;">
                <div class="custom-modal-content">
                    <a href="javascript:void(0)" class="close-x-button" onclick="closeViewDetailsModal()">&times;</a>
                    <h3>Error</h3>
                    <p>Verification ID not provided.</p>
                    <button class="close-button" onclick="closeViewDetailsModal()">Close</button>
                </div>
              </div>';
    }



    if (isset($_GET['message'])) {

        echo '<div id="messagePopup" class="overlay" style="display:flex;">
                <div class="popup">
                    <center>
                        <div class="content">';
        if ($_GET['message'] == 'success') {
            echo '<h3>Success!</h3><p>Client has been successfully verified.</p>';
        } else if ($_GET['message'] == 'rejected') {
            echo '<h3>Rejected!</h3><p>Client verification request has been rejected and removed.</p>';
        } else if ($_GET['message'] == 'error') {
            echo '<h3>Error!</h3><p>Action failed. Please try again.</p>';
            if (isset($_GET['details'])) {
                echo '<p>Details: ' . htmlspecialchars($_GET['details']) . '</p>'; 
            }
        }
        echo '      </div>
                        <a href="client_verification.php"><button class="login-btn btn-primary-soft btn" style="margin-top:15px;">OK</button></a>
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
                        <a href="appointment.php" class="non-style-link-menu">
                            <div>
                                <p class="menu-text">Appointments</p>
                            </div>
                        </a>
                    </td>
                </tr>
                <tr class="menu-row">
                    <td class="menu-btn menu-icon-client-verification menu-active menu-icon-client-verification-active">
                        <a href="client_verification.php" class="non-style-link-menu non-style-link-menu-active">
                            <div>
                                <p class="menu-text menu-text-active">Client Verification</p>
                            </div>
                        </a>
                    </td>
                </tr>
              <tr class="menu-row">
                    <td class="menu-btn menu-icon-lawyer-verification">
                        <a href="lawyer_verification.php" class="non-style-link-menu">
                            <div>
                                <p class="menu-text menu-text">Lawyer Verification</p>
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
        <div class="dash-body">
            <table border="0" width="100%" style=" border-spacing: 0;margin:0;padding:0;margin-top:25px; ">
                <tr >
                
                    <td>
                        <form action="" method="post" class="header-search">

                            <input type="search" name="search" class="input-text header-searchbar" placeholder="Search Client name or Email" list="client">&nbsp;&nbsp;

                            <?php
                                echo '<datalist id="client">';
                                $list11 = $database->query("select first_name, last_name, email from identity_verifications where is_verified = FALSE;");

                                for ($y=0;$y<$list11->num_rows;$y++){
                                    $row00=$list11->fetch_assoc();
                                    $l=$row00["first_name"]." ".$row00["last_name"];
                                    $c=$row00["email"];
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
                        <p class="heading-main12" style="margin-left: 45px;font-size:18px;color:rgb(49, 49, 49)">New Client Verification Requests (<?php echo $list11->num_rows; ?>)</p>
                    </td>
                </tr>
                <tr>
                    <td colspan="4" style="padding-top:0px;width: 100%;">
                        <center>
                            <div class="abc scroll">
                                <table width="93%" class="sub-table scrolldown" style="border-spacing:0;">
                                <thead>
                                    <tr>
                                        <th class="table-headin">Client Name</th>
                                        <th class="table-headin">Email</th>
                                        <th class="table-headin">Contact Number</th>
                                        <th class="table-headin">Submission Date</th>
                                        <th class="table-headin">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php
                                    $sqlmain = "SELECT * FROM identity_verifications WHERE is_verified = FALSE ORDER BY submission_date DESC";

                                    if($_POST && isset($_POST['search'])){
                                        $searchkey = $_POST['search'];
                                        $sqlmain = "SELECT * FROM identity_verifications WHERE is_verified = FALSE AND (CONCAT(first_name, ' ', last_name) LIKE '%$searchkey%' OR email LIKE '%$searchkey%') ORDER BY submission_date DESC";
                                    }
                                    
                                    $result = $database->query($sqlmain);

                                    if($result->num_rows==0){
                                        echo '<tr>
                                                <td colspan="5">
                                                    <br><br><br><br>
                                                    <center>
                                                        <img src="../img/notfound.svg" width="25%">
                                                        <br>
                                                        <p class="heading-main12" style="margin-left: 45px;font-size:20px;color:rgb(49, 49, 49)">No new verification requests found!</p>
                                                        <a class="non-style-link" href="client_verification.php"><button  class="login-btn btn-primary-soft btn"  style="display: flex;justify-content: center;align-items: center;margin-left:20px;">&nbsp; Show all Requests &nbsp;</font></button></a>
                                                    </center>
                                                    <br><br><br><br>
                                                </td>
                                            </tr>';
                                    } else {
                                        while($row = $result->fetch_assoc()){
                                            $id=$row["id"];
                                            $name=$row["first_name"]." ".$row["last_name"];
                                            $email=$row["email"];
                                            $contact_number=$row["contact_number"];
                                            $submission_date=$row["submission_date"];
                                            $first_name_client = $row["first_name"];
                                            $last_name_client = $row["last_name"];
                                            
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
                                                        onclick="event.stopPropagation(); showConfirmModal(\''.$id.'\', \''.$email.'\', \''.$first_name_client.'\', \''.$last_name_client.'\')"
                                                        style="padding-left: 40px; padding-top: 12px; padding-bottom: 12px; margin-top: 10px;">
                                                        <font class="tn-in-text">Verify</font>
                                                    </button>
                                                    &nbsp;&nbsp;&nbsp;
                                                    <button class="btn-primary-soft btn button-icon btn-delete"
                                                        onclick="event.stopPropagation(); showRejectConfirmModal(\''.$id.'\', \''.$email.'\')"
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

    <div id="confirmModal" class="custom-modal">
        <div class="custom-modal-content">
            <h3 id="confirmModalTitle">Confirm Verification</h3>
            <p>Are you sure you want to verify this client?</p>
            <div class="modal-buttons">
                <button class="confirm-btn" id="confirmVerificationBtn">Confirm</button>
                <button class="cancel-btn" onclick="hideConfirmModal()">Cancel</button>
            </div>
        </div>
    </div>

    <div id="rejectConfirmModal" class="custom-modal">
        <div class="custom-modal-content">
            <h3 id="rejectConfirmModalTitle">Confirm Rejection</h3>
            <p>Are you sure you want to reject this client's verification request? This action cannot be undone.</p>
            <div class="modal-buttons">
                <button class="cancel-btn" id="confirmRejectBtn">Reject</button>
                <button class="confirm-btn" onclick="hideRejectConfirmModal()">Cancel</button>
            </div>
        </div>
    </div>


    <script>
        let currentVerificationId = null;
        let currentClientEmail = null;
        let currentClientFirstName = null; 
        let currentClientLastName = null;  

        function showConfirmModal(verificationId, clientEmail, firstName, lastName) {
            currentVerificationId = verificationId;
            currentClientEmail = clientEmail;
            currentClientFirstName = firstName; 
            currentClientLastName = lastName;   
            document.getElementById('confirmModal').style.display = 'flex'; 
        }

        function hideConfirmModal() {
            document.getElementById('confirmModal').style.display = 'none';
            currentVerificationId = null;
            currentClientEmail = null;
            currentClientFirstName = null;
            currentClientLastName = null;
        }

        function showRejectConfirmModal(verificationId, clientEmail) {
            currentVerificationId = verificationId;
            currentClientEmail = clientEmail;
            document.getElementById('rejectConfirmModal').style.display = 'flex';
        }

        function hideRejectConfirmModal() {
            document.getElementById('rejectConfirmModal').style.display = 'none';
            currentVerificationId = null;
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


        document.getElementById('confirmVerificationBtn').addEventListener('click', function() {
            if (currentVerificationId && currentClientEmail && currentClientFirstName && currentClientLastName) {

                const form = document.createElement('form');
                form.method = 'POST';
                form.action = 'client_verification.php'; 

                const actionInput = document.createElement('input');
                actionInput.type = 'hidden';
                actionInput.name = 'action';
                actionInput.value = 'verify_user';
                form.appendChild(actionInput);

                const idInput = document.createElement('input');
                idInput.type = 'hidden';
                idInput.name = 'verification_id';
                idInput.value = currentVerificationId;
                form.appendChild(idInput);

                const emailInput = document.createElement('input');
                emailInput.type = 'hidden';
                emailInput.name = 'client_email';
                emailInput.value = currentClientEmail;
                form.appendChild(emailInput);

                const firstNameInput = document.createElement('input');
                firstNameInput.type = 'hidden';
                firstNameInput.name = 'first_name';
                firstNameInput.value = currentClientFirstName;
                form.appendChild(firstNameInput);

                const lastNameInput = document.createElement('input');
                lastNameInput.type = 'hidden';
                lastNameInput.name = 'last_name';
                lastNameInput.value = currentClientLastName;
                form.appendChild(lastNameInput);

                document.body.appendChild(form); 
                form.submit(); 
            }
            hideConfirmModal(); 
        });

        document.getElementById('confirmRejectBtn').addEventListener('click', function() {
            if (currentVerificationId && currentClientEmail) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = 'client_verification.php';

                const actionInput = document.createElement('input');
                actionInput.type = 'hidden';
                actionInput.name = 'action';
                actionInput.value = 'reject_user';
                form.appendChild(actionInput);

                const idInput = document.createElement('input');
                idInput.type = 'hidden';
                idInput.name = 'verification_id';
                idInput.value = currentVerificationId;
                form.appendChild(idInput);

                const emailInput = document.createElement('input');
                emailInput.type = 'hidden';
                emailInput.name = 'client_email';
                emailInput.value = currentClientEmail;
                form.appendChild(emailInput);

                document.body.appendChild(form);
                form.submit();
            }
            hideRejectConfirmModal();
        });


        window.onload = function() {
            const messagePopup = document.getElementById('messagePopup');
            if (messagePopup) {


            }

            if (!window.location.search.includes('action=view')) {
                 const viewModal = document.getElementById('viewDetailsModal');
                if (viewModal) {
                    viewModal.style.display = 'none';
                }
                document.getElementById('confirmModal').style.display = 'none';
                document.getElementById('rejectConfirmModal').style.display = 'none';
            }
        };
    </script>

</body>
</html>