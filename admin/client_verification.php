<?php

session_start(); // THIS MUST BE THE VERY FIRST THING IN THE FILE

// Include database connection
include("../connection.php");

// Check if user is logged in and is an administrator
if(isset($_SESSION["user"])){
    if(($_SESSION["user"])=="" or $_SESSION['usertype']!='a'){
        header("location: ../login.php");
        exit(); // Always exit after a header redirect
    }
}else{
    header("location: ../login.php");
    exit(); // Always exit after a header redirect
}

// Handle verification action when form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action'])) {
    $verification_id = $_POST['verification_id'];
    $client_email = $_POST['client_email'];
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];

    // Start a database transaction for atomicity (all or nothing)
    $database->begin_transaction();

    try {
        if ($_POST['action'] == 'verify_user') {
            // 1. Check if a user with the same First Name, Last Name, and Email already exists in webuser table
            $stmt_check_webuser = $database->prepare("SELECT email FROM webuser WHERE email = ?");
            if ($stmt_check_webuser) {
                $stmt_check_webuser->bind_param("s", $client_email);
                $stmt_check_webuser->execute();
                $result_check_webuser = $stmt_check_webuser->get_result();
                $stmt_check_webuser->close();

                if ($result_check_webuser->num_rows > 0) {
                    // User exists, update usertype to 'c'
                    $stmt_update_webuser = $database->prepare("UPDATE webuser SET usertype = 'c' WHERE email = ?");
                    if ($stmt_update_webuser) {
                        $stmt_update_webuser->bind_param("s", $client_email);
                        $stmt_update_webuser->execute();
                        $stmt_update_webuser->close();
                    } else {
                        throw new Exception("Failed to prepare webuser update statement: " . $database->error);
                    }
                } else {
                    // User does not exist, insert new user with usertype 'c'
                    // Fetch other necessary details from identity_verifications to create a new webuser entry
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

            // 2. Update is_verified status in identity_verifications table to TRUE
            $stmt_identity = $database->prepare("UPDATE identity_verifications SET is_verified = TRUE WHERE id = ?");
            if ($stmt_identity) {
                $stmt_identity->bind_param("i", $verification_id);
                $stmt_identity->execute();
                $stmt_identity->close();
            } else {
                throw new Exception("Failed to prepare identity_verifications update statement: " . $database->error);
            }

            // Commit the transaction if both updates are successful
            $database->commit();
            // Redirect to prevent form resubmission and display success message
            header("Location: client_verification.php?message=success");
            exit(); // Always exit after a header redirect

        } else if ($_POST['action'] == 'reject_user') {
            // Handle rejection: Delete the entry from identity_verifications table
            $stmt_delete_verification = $database->prepare("DELETE FROM identity_verifications WHERE id = ?");
            if ($stmt_delete_verification) {
                $stmt_delete_verification->bind_param("i", $verification_id);
                $stmt_delete_verification->execute();
                $stmt_delete_verification->close();
            } else {
                throw new Exception("Failed to prepare delete verification statement: " . $database->error);
            }

            // Commit the transaction
            $database->commit();
            header("Location: client_verification.php?message=rejected");
            exit(); // Always exit after a header redirect
        }

    } catch (Exception $e) {
        // Rollback the transaction if any error occurs
        $database->rollback();
        error_log("Action failed: " . $e->getMessage()); // Log error for debugging
        header("Location: client_verification.php?message=error&details=" . urlencode($e->getMessage()));
        exit(); // Always exit after a header redirect
    }
}

// Handle view action to fetch and display verification details
$verification_details = null; // Initialize to null
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
    <link rel="icon" type="image/png" href="https://i.ibb.co/qYYZs46/logo.png">


    <title>Client Verification | SafeSpace PH</title>
    <style>
        /* Styles for popups and sub-tables, consistent with existing admin pages */
       .popup {
            padding: 20px;
            background: #fff;
            border-radius: 5px;
            width: 50%;
            position: relative;
            /* transition: all 5s ease-in-out; - This is a very long transition, consider reducing if not intentional */
            /* Added for scrollability and responsiveness */
            max-height: 90vh; /* Max height of the popup */
            overflow-y: auto; /* Enable vertical scrolling */
            box-sizing: border-box; /* Include padding in width/height */
        }
        .sub-table{
            animation: transitionIn-Y-bottom 0.5s;
        }
        /* Custom Modal Styles for verification confirmation */
        .custom-modal {
            display: none; /* Hidden by default */
            position: fixed; /* Stay in place */
            z-index: 1000; /* Sit on top */
            left: 0;
            top: 0;
            width: 100%; /* Full width */
            height: 100%; /* Full height */
            overflow: auto; /* Enable scroll if needed */
            background-color: rgba(0,0,0,0.4); /* Black w/ opacity */
            /* Ensure flexbox for centering when displayed */
            align-items: center; /* Center vertically */
            justify-content: center; /* Center horizontally */
            padding: 20px; /* Overall modal padding for responsiveness */
            box-sizing: border-box; /* Include padding in width/height */
        }

        .custom-modal-content {
            background-color: #fefefe;
            /* Removed margin for flexbox centering */
            padding: 30px;
            border: 1px solid #888;
            width: 90%;
            max-width: 500px;
            border-radius: 10px;
            box-shadow: 0 4px 8px 0 rgba(0,0,0,0.2), 0 6px 20px 0 rgba(0,0,0,0.19);
            text-align: center;
            position: relative;
            /* Added for scrollability and responsiveness */
            max-height: 90vh; /* Max height of the modal content */
            overflow-y: auto; /* Enable vertical scrolling for content */
            box-sizing: border-box; /* Include padding in width/height */
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
            background-color: #4CAF50; /* Green */
            color: white;
        }
        .custom-modal-content .modal-buttons .cancel-btn {
            background-color: #f44336; /* Red */
            color: white;
        }
        /* Styles for file viewer links, matching button aesthetics */
        .file-link {
            display: inline-block;
            padding: 8px 15px;
            background-color: #007bff; /* Blue, similar to primary buttons */
            color: white;
            border-radius: 20px; /* Rounded corners */
            text-decoration: none;
            margin: 5px;
            font-weight: bold;
        }
        .file-link:hover {
            background-color: #0056b3; /* Darker blue on hover */
        }

        /* Styles for the View Details Modal - Refined */
        #viewDetailsModal .custom-modal-content {
            max-width: 800px; /* Wider modal */
            text-align: left; /* Align content to left */
            padding: 30px; /* Ensure consistent padding */
            max-height: 90vh; /* Ensure it doesn't exceed viewport height */
            overflow-y: auto; /* Enable scrolling for content within this modal */
        }
        #viewDetailsModal .custom-modal-content h3 {
            text-align: center; /* Center the modal title */
            margin-bottom: 25px; /* More space below title */
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
            margin-bottom: 20px; /* Spacing between sections */
            padding-bottom: 10px;
            /* border-bottom: 1px solid #eee; Removed default border-bottom */
        }
        #viewDetailsModal .detail-section:last-of-type {
            border-bottom: none; /* No border for the last section */
        }
        /* For the general popup used for messages, ensure it's also centered */
        .overlay:target, #messagePopup { /* Added #messagePopup to this selector */
            visibility: visible;
            opacity: 1;
            display: flex; /* Ensure flexbox for centering */
            align-items: center; /* Center vertically */
            justify-content: center; /* Center horizontally */
        }

        .overlay .popup { /* Targeting popup within overlay to remove margin */
            margin: auto; /* Allow flexbox to center it */
        }
        #viewDetailsModal .detail-section h4 {
            color: #5A2675; /* Section heading color */
            font-weight: bold; /* Semi-bold or bold */
            margin-top: 0;
            margin-bottom: 15px;
            /* border-bottom: 2px solid #007bff; Removed default underline */
            display: inline-block; /* Only underline the text */
            padding-bottom: 5px;
            width: 100%; /* Ensure heading takes full width for hr */
            text-align: left; /* Align heading text to left */
        }
        #viewDetailsModal .detail-section hr { /* Divider line for sections */
            border: none;
            border-top: 1px solid #5A2675; /* Thin and elegant line with specified color */
            margin-top: -10px; /* Move divider closer to heading */
            margin-bottom: 15px;
        }
        #viewDetailsModal .detail-item {
            margin-bottom: 8px; /* Spacing between items */
            display: flex; /* Use flexbox for label-value alignment */
            align-items: baseline; /* Align text baselines */
        }
        /* Ensure the labels are bold and values are normal font weight */
        #viewDetailsModal .detail-item strong { /* Targeting the strong tag for labels */
            flex: 0 0 180px; /* Fixed width for labels, adjust as needed */
            margin-right: 10px; /* Space between label and value */
            color: #555;
            font-weight: bold; /* Keep labels bold */
        }
        #viewDetailsModal .detail-item span { /* Targeting the span tag for values */
            flex-grow: 1; /* Allow value to take remaining space */
            color: #333;
            word-wrap: break-word; /* Ensure long text wraps */
            font-weight: normal; /* Set values to normal font weight */
        }
        #viewDetailsModal .close-button {
            background-color: #C9A8F1; /* Lavender-like shade */
            color: white;
            padding: 10px 20px; /* Match existing button padding */
            border-radius: 20px; /* Match existing button border-radius */
            border: none; /* Ensure no default border */
            cursor: pointer;
            font-size: 16px;
            font-weight: bold;
            margin-top: 25px; /* More space from content */
            display: block; /* Make it a block element to center with auto margins */
            margin-left: auto;
            margin-right: auto;
        }
        #viewDetailsModal .close-button:hover {
            background-color: #b193d5; /* Slightly darker lavender on hover */
        }
    </style>
</head>
<body>
    <?php
    // The PHP code that handles displaying the view details modal needs to be here,
    // as it outputs HTML directly.
    if ($verification_details) {
        // Changed display:block to display:flex for proper centering
        echo '<div id="viewDetailsModal" class="custom-modal" style="display:flex;">
                <div class="custom-modal-content">
                    <a href="javascript:void(0)" class="close-x-button" onclick="closeViewDetailsModal()">&times;</a>
                    <h3>Verification Request Details</h3>

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
        // Only show error if action=view is present but id is missing
        // Changed display:block to display:flex for proper centering
        echo '<div id="viewDetailsModal" class="custom-modal" style="display:flex;">
                <div class="custom-modal-content">
                    <a href="javascript:void(0)" class="close-x-button" onclick="closeViewDetailsModal()">&times;</a>
                    <h3>Error</h3>
                    <p>Verification ID not provided.</p>
                    <button class="close-button" onclick="closeViewDetailsModal()">Close</button>
                </div>
              </div>';
    }


    // Display success or error messages in a popup after a verification attempt
    if (isset($_GET['message'])) {
        // The overlay class already handles display:flex when targeted or shown
        echo '<div id="messagePopup" class="overlay" style="display:flex;">
                <div class="popup">
                    <center>
                        <a class="close" href="client_verification.php">&times;</a>
                        <div class="content">';
        if ($_GET['message'] == 'success') {
            echo '<h3>Success!</h3><p>Client has been successfully verified.</p>';
        } else if ($_GET['message'] == 'rejected') {
            echo '<h3>Rejected!</h3><p>Client verification request has been rejected and removed.</p>';
        } else if ($_GET['message'] == 'error') {
            echo '<h3>Error!</h3><p>Action failed. Please try again.</p>';
            if (isset($_GET['details'])) {
                echo '<p>Details: ' . htmlspecialchars($_GET['details']) . '</p>'; // Display error details if available
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
                <tr class="menu-row" >
                    <td class="menu-btn menu-icon-dashbord" >
                        <a href="index.php" class="non-style-link-menu"><div><p class="menu-text">Dashboard</p></a></div></a>
                    </td>
                </tr>
                <tr class="menu-row">
                    <td class="menu-btn menu-icon-lawyers ">
                            <a href="lawyers.php" class="non-style-link-menu"><div><p class="menu-text">All Lawyers</p></a></div>
                    </td>
                </tr>
                <tr class="menu-row" >
                    <td class="menu-btn menu-icon-schedule">
                        <a href="schedule.php" class="non-style-link-menu"><div><p class="menu-text">Schedules</p></div></a>
                    </td>
                </tr>
                <tr class="menu-row">
                    <td class="menu-btn menu-icon-appoinment">
                        <a href="appointment.php" class="non-style-link-menu"><div><p class="menu-text">Appointments</p></a></div>
                    </td>
                </tr>
                   <tr class="menu-row">
                    <td class="menu-btn menu-icon-client">
                        <a href="client.php" class="non-style-link-menu"><div><p class="menu-text">All Clients</p></a></div>
                    </td>
                </tr>
                <tr class="menu-row" >
                    <td class="menu-btn menu-icon-client-verification  menu-active menu-icon-client-verification-active">
                        <a href="client_verification.php" class="non-style-link-menu  non-style-link-menu-active"><div><p class="menu-text menu-text-active">Client Verification</p></a></div>
                    </td>
                </tr>

            </table>
        </div>
        <div class="dash-body">
            <table border="0" width="100%" style=" border-spacing: 0;margin:0;padding:0;margin-top:25px; ">
                <tr >
                    <td width="13%">

                    <a href="client.php" ><button  class="login-btn btn-primary-soft btn btn-icon-back"  style="padding-top:11px;padding-bottom:11px;margin-left:20px;width:125px"><font class="tn-in-text">Back</font></button></a>
                        
                    </td>
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
                        date_default_timezone_set('Asia/Kolkata');

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
                        <p class="heading-main12" style="margin-left: 45px;font-size:18px;color:rgb(49, 49, 49)">New Client Verification Requests</p>
                    </td>
                </tr>
                <tr>
                    <td colspan="4" style="padding-top:0px;width: 100%;">
                        <center>
                            <!-- Added div with class "abc scroll" and adjusted table attributes for consistency -->
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

                                    if($_POST){
                                        $searchkey = $_POST['search'];
                                        $sqlmain = "SELECT * FROM identity_verifications WHERE is_verified = FALSE AND (first_name LIKE '%$searchkey%' OR last_name LIKE '%$searchkey%' OR email LIKE '%$searchkey%') ORDER BY submission_date DESC";
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
                                                        <a class="label-link" href="client_verification.php">Refresh Page</a>
                                                    </center>
                                                    <br><br><br><br>
                                                </td>
                                            </tr>';
                                    } else {
                                        for ( $x=0; $x<$result->num_rows;$x++){
                                            $row=$result->fetch_assoc();
                                            $id=$row["id"];
                                            $name=$row["first_name"]." ".$row["last_name"];
                                            $email=$row["email"];
                                            $contact_number=$row["contact_number"];
                                            $submission_date=$row["submission_date"];
                                            $first_name_client = $row["first_name"]; // Added for new logic
                                            $last_name_client = $row["last_name"];   // Added for new logic
                                            
                                      echo '<tr>
    <td>'.substr($name,0,30).'</td>
    <td>'.substr($email,0,30).'</td>
    <td>'.substr($contact_number,0,20).'</td>
    <td>'.substr($submission_date,0,10).'</td>
    <td>
        <div style="display:flex;justify-content: center;">
            <a href="?action=view&id='.$row['id'].'" class="non-style-link btn-primary-soft btn button-icon btn-view" style="padding-left: 40px; padding-top: 12px; padding-bottom: 12px; margin-top: 10px;">
                <font class="tn-in-text">View</font>
            </a>
            &nbsp;&nbsp;&nbsp;
            <button class="non-style-link btn-primary-soft btn button-icon menu-icon-verify"
                onclick="event.stopPropagation(); showConfirmModal(\''.$row['id'].'\', \''.$row['email'].'\', \''.$first_name_client.'\', \''.$last_name_client.'\')"
                style="padding-left: 40px; padding-top: 12px; padding-bottom: 12px; margin-top: 10px;">
                <font class="tn-in-text">Verify</font>
            </button>
            &nbsp;&nbsp;&nbsp;
            <button class="non-style-link btn-primary-soft btn button-icon menu-icon-delete"
                onclick="event.stopPropagation(); showRejectConfirmModal(\''.$row['id'].'\', \''.$row['email'].'\')"
                style="padding-left: 40px; padding-top: 12px; padding-bottom: 12px; margin-top: 10px; background-color: #f44336; color: white;">
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
                            </div> <!-- Closing tag for abc scroll -->
                        </center>
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
        let currentClientFirstName = null; // Added for new logic
        let currentClientLastName = null;  // Added for new logic

        function showConfirmModal(verificationId, clientEmail, firstName, lastName) {
            currentVerificationId = verificationId;
            currentClientEmail = clientEmail;
            currentClientFirstName = firstName; // Store first name
            currentClientLastName = lastName;   // Store last name
            document.getElementById('confirmModal').style.display = 'flex'; // Changed to 'flex' for centering
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
            window.history.pushState({}, '', url); // Update URL without reloading
            document.getElementById('viewDetailsModal').style.display = 'none';
        }


        document.getElementById('confirmVerificationBtn').addEventListener('click', function() {
            if (currentVerificationId && currentClientEmail && currentClientFirstName && currentClientLastName) {
                // Create a form dynamically to submit POST request
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = 'client_verification.php'; // Submit to this page for processing

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

                document.body.appendChild(form); // Append form to body
                form.submit(); // Submit the form
            }
            hideConfirmModal(); // Hide modal after submission
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

        // Close the message popup if it's shown on page load
        window.onload = function() {
            const messagePopup = document.getElementById('messagePopup');
            if (messagePopup) {
                // You can add a timeout here if you want the message to disappear automatically
                // setTimeout(() => { messagePopup.style.display = 'none'; }, 3000); 
            }
            // Ensure modals are hidden on page load unless specifically triggered by URL parameters
            if (!window.location.search.includes('action=view') && !window.location.search.includes('message=')) {
                document.getElementById('viewDetailsModal').style.display = 'none';
                document.getElementById('confirmModal').style.display = 'none';
                document.getElementById('rejectConfirmModal').style.display = 'none';
            }
        };
    </script>

</body>
</html>
