<?php

session_start(); // THIS MUST BE THE VERY FIRST THING IN THE FILE

if(isset($_SESSION["user"])){
    if(($_SESSION["user"])=="" or $_SESSION['usertype']!='a'){
        header("location: ../login.php");
        exit(); // Always exit after a header redirect
    }
}else{
    header("location: ../login.php");
    exit(); // Always exit after a header redirect
}

// Import database connection
include("../connection.php");

// Handle delete action when form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] == 'delete_client') {
    $client_id = $_POST['client_id'];
    $client_email = $_POST['client_email'];

    // Start a database transaction for atomicity (all or nothing)
    $database->begin_transaction();

    try {
        // 1. Delete from client table
        $stmt_client = $database->prepare("DELETE FROM client WHERE cid = ?");
        if ($stmt_client) {
            $stmt_client->bind_param("i", $client_id);
            $stmt_client->execute();
            $stmt_client->close();
        } else {
            throw new Exception("Failed to prepare client delete statement: " . $database->error);
        }

        // 2. Delete from webuser table
        $stmt_webuser = $database->prepare("DELETE FROM webuser WHERE email = ?");
        if ($stmt_webuser) {
            $stmt_webuser->bind_param("s", $client_email);
            $stmt_webuser->execute();
            $stmt_webuser->close();
        } else {
            throw new Exception("Failed to prepare webuser delete statement: " . $database->error);
        }

        // Commit the transaction if both deletions are successful
        $database->commit();
        // Redirect to prevent form resubmission and display success message
        header("Location: client.php?message=deleted_success");
        exit(); // Always exit after a header redirect

    } catch (Exception $e) {
        // Rollback the transaction if any error occurs
        $database->rollback();
        error_log("Client deletion failed: " . $e->getMessage()); // Log error for debugging
        header("Location: client.php?message=deleted_error&details=" . urlencode($e->getMessage()));
        exit(); // Always exit after a header redirect
    }
}

// Handle unverify action when form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] == 'unverify_client') {
    $client_id = $_POST['client_id'];
    $client_email = $_POST['client_email'];

    $database->begin_transaction();

    try {
        // First, check if the user is already unverified (usertype 'u')
        $stmt_check_usertype = $database->prepare("SELECT usertype FROM webuser WHERE email = ?");
        if ($stmt_check_usertype) {
            $stmt_check_usertype->bind_param("s", $client_email);
            $stmt_check_usertype->execute();
            $result_check_usertype = $stmt_check_usertype->get_result();
            $user_data = $result_check_usertype->fetch_assoc();
            $stmt_check_usertype->close();

            // If user_data exists and usertype is 'u', throw a specific exception
            if ($user_data && $user_data['usertype'] == 'u') {
                throw new Exception("unverified_already");
            }
        } else {
            throw new Exception("Failed to prepare usertype check statement: " . $database->error);
        }

        // 1. Update usertype in webuser table to 'u' (unverified)
        $stmt_webuser_update = $database->prepare("UPDATE webuser SET usertype = 'u' WHERE email = ?");
        if ($stmt_webuser_update) {
            $stmt_webuser_update->bind_param("s", $client_email);
            $stmt_webuser_update->execute();
            $stmt_webuser_update->close();
        } else {
            throw new Exception("Failed to prepare webuser update statement: " . $database->error);
        }

        // 2. Update is_verified to 0 in identity_verifications table
        $stmt_identity_update = $database->prepare("UPDATE identity_verifications SET is_verified = 0 WHERE email = ?");
        if ($stmt_identity_update) {
            $stmt_identity_update->bind_param("s", $client_email);
            $stmt_identity_update->execute();
            $stmt_identity_update->close();
        } else {
            throw new Exception("Failed to prepare identity_verifications update statement: " . $database->error);
        }

        $database->commit();
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


// Handle view action to fetch and display client details
$client_details = null; // Initialize to null
if (isset($_GET['action']) && $_GET['action'] == 'view' && isset($_GET['id'])) {
    $view_id = $_GET['id'];
    // Fetch all necessary details by joining client, webuser, and identity_verifications tables
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
        /* This style block fixes the scrolling issue by ensuring the main content area can scroll vertically. */
        .dash-body {
            overflow-y: auto;
        }
    </style>
    <style>
        /* START: Added styles for unverify button icon */
        .btn-unverify {
            background-repeat: no-repeat;
            background-position: left 5px center;
        }
        /* END: Added styles for unverify button icon */
        .popup{
            animation: transitionIn-Y-bottom 0.5s;
        }
        .sub-table{
            animation: transitionIn-Y-bottom 0.5s;
        }
        .status-btn {
            padding: 8px 15px;
            border-radius: 20px; /* Adjust as needed for desired roundness */
            color: white;
            font-weight: bold;
            display: inline-block;
            text-align: center;
        }
        .status-verified {
            background-color: #4CAF50; /* Green */
        }
        .status-unverified {
            background-color: #f44336; /* Red */
        }
        /* Custom Modal Styles for client details and delete confirmation */
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
            padding: 30px;
            border: 1px solid #888;
            width: 90%;
            max-width: 500px;
            border-radius: 10px;
            box-shadow: 0 4px 8px 0 rgba(0,0,0,0.2), 0 6px 20px 0 rgba(0,0,0,0.19);
            text-align: center;
            position: relative;
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

        /* Styles for file viewer links, matching button aesthetics */
        .file-link {
            display: inline-block;
            padding: 8px 15px;
            background-color: #5A2675; /* Blue, similar to primary buttons */
            color: white;
            border-radius: 20px; /* Rounded corners */
            text-decoration: none;
            margin: 5px;
            font-weight: bold;
        }
        .file-link:hover {
            background-color: #C9A8F1; /* Darker blue on hover */
        }
    </style>
</head>
<body>
    <?php
    // The PHP code that handles displaying the view details modal needs to be here,
    // as it outputs HTML directly.
    if ($client_details) {
        // Changed display:block to display:flex for proper centering
        echo '<div id="viewDetailsModal" class="custom-modal" style="display:flex;">
                <div class="custom-modal-content">
                    <a href="javascript:void(0)" class="close-x-button" onclick="closeViewDetailsModal()">&times;</a>
                    <h3>Client Details</h3>

                    <div class="detail-section">
                        <h4>Personal Information</h4>
                        <hr>
                        <div class="detail-item"><strong>Client ID:</strong> <span>C-' . htmlspecialchars($client_details['cid']) . '</span></div>
                        <div class="detail-item"><strong>Full Name:</strong> <span>' . htmlspecialchars($client_details['cname']) . '</span></div>
                        <div class="detail-item"><strong>First Name:</strong> <span>' . htmlspecialchars($client_details['first_name'] ?? '') . '</span></div>
                        <div class="detail-item"><strong>Middle Name:</strong> <span>' . htmlspecialchars($client_details['middle_name'] ?? '') . '</span></div>
                        <div class="detail-item"><strong>Last Name:</strong> <span>' . htmlspecialchars($client_details['last_name'] ?? '') . '</span></div>
                        <div class="detail-item"><strong>Suffix:</strong> <span>' . htmlspecialchars($client_details['suffix'] ?? '') . '</span></div>
                        <div class="detail-item"><strong>Sex:</strong> <span>' . htmlspecialchars($client_details['sex'] ?? '') . '</span></div>
                        <div class="detail-item"><strong>Civil Status:</strong> <span>' . htmlspecialchars($client_details['civil_status'] ?? '') . '</span></div>
                        <div class="detail-item"><strong>Citizenship:</strong> <span>' . htmlspecialchars($client_details['citizenship'] ?? '') . '</span></div>
                        <div class="detail-item"><strong>Birth Place:</strong> <span>' . htmlspecialchars($client_details['birth_place'] ?? '') . '</span></div>
                        <div class="detail-item"><strong>Verified:</strong> <span><span class="status-btn ' . (($client_details['usertype'] == 'c') ? 'status-verified' : 'status-unverified') . '">' . (($client_details['usertype'] == 'c') ? 'Yes' : 'No') . '</span></span></div>
                    </div>

                    <div class="detail-section">
                        <h4>Contact Information</h4>
                        <hr>
                        <div class="detail-item"><strong>Email:</strong> <span>' . htmlspecialchars($client_details['cemail']) . '</span></div>
                        <div class="detail-item"><strong>Phone Number:</strong> <span>' . htmlspecialchars($client_details['ctel'] ?? '', ENT_QUOTES, 'UTF-8') . '</span></div>
                    </div>

                    <div class="detail-section">
                        <h4>Address Information</h4>
                        <hr>
                        <div class="detail-item"><strong>Address:</strong> <span>' . htmlspecialchars($client_details['caddress'] ?? '', ENT_QUOTES, 'UTF-8') . '</span></div>
                        <div class="detail-item"><strong>Present Address:</strong> <span>' . htmlspecialchars($client_details['present_address'] ?? '') . '</span></div>
                        <div class="detail-item"><strong>Permanent Address:</strong> <span>' . htmlspecialchars($client_details['permanent_address'] ?? '') . '</span></div>
                    </div>

                    <div class="detail-section">
                        <h4>Emergency Contact</h4>
                        <hr>
                        <div class="detail-item"><strong>Name:</strong> <span>' . htmlspecialchars($client_details['emergency_contact_name'] ?? '') . '</span></div>
                        <div class="detail-item"><strong>Number:</strong> <span>' . htmlspecialchars($client_details['emergency_contact_number'] ?? '') . '</span></div>
                        <div class="detail-item"><strong>Relationship:</strong> <span>' . htmlspecialchars($client_details['emergency_contact_relationship'] ?? '') . '</span></div>
                    </div>

                    <div class="detail-section">
                        <h4>ID & Photos</h4>
                        <hr>
                        <div class="detail-item"><strong>ID Type:</strong> <span>' . htmlspecialchars($client_details['id_type'] ?? '') . '</span></div>
                        <div class="detail-item"><strong>ID Number:</strong> <span>' . htmlspecialchars($client_details['id_number'] ?? '') . '</span></div>
                        <div class="detail-item"><strong>Front of ID Photo:</strong> <span>' . (isset($client_details['id_photo_front_path']) && $client_details['id_photo_front_path'] ? '<a href="../' . htmlspecialchars($client_details['id_photo_front_path']) . '" target="_blank" class="file-link">View File</a>' : 'N/A') . '</span></div>
                        <div class="detail-item"><strong>Back of ID Photo:</strong> <span>' . (isset($client_details['id_photo_back_path']) && $client_details['id_photo_back_path'] ? '<a href="../' . htmlspecialchars($client_details['id_photo_back_path']) . '" target="_blank" class="file-link">View File</a>' : 'N/A') . '</span></div>
                        <div class="detail-item"><strong>Profile Photo:</strong> <span>' . (isset($client_details['profile_photo_path']) && $client_details['profile_photo_path'] ? '<a href="../' . htmlspecialchars($client_details['profile_photo_path']) . '" target="_blank" class="file-link">View File</a>' : 'N/A') . '</span></div>
                    </div>

                    <div class="detail-section" style="border-bottom: none; margin-bottom: 0;">
                        <div class="detail-item"><strong>Agreed Terms:</strong> <span>' . (isset($client_details['agree_terms']) ? ($client_details['agree_terms'] ? 'Yes' : 'No') : 'N/A') . '</span></div>
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
                    <p>Client ID not provided.</p>
                    <button class="close-button" onclick="closeViewDetailsModal()">Close</button>
                </div>
              </div>';
    }


    // Display success or error messages in a popup after a delete or unverify attempt
    if (isset($_GET['message'])) {
        // The overlay class already handles display:flex when targeted or shown
        echo '<div id="messagePopup" class="overlay" style="display:flex;">
                <div class="popup">
                    <center>
                        <a class="close" href="client.php">&times;</a>
                        <div class="content">';
        if ($_GET['message'] == 'deleted_success') {
            echo '<h3>Success!</h3><p>Client has been successfully deleted.</p>';
        } else if ($_GET['message'] == 'deleted_error') {
            echo '<h3>Error!</h3><p>Client deletion failed. Please try again.</p>';
            if (isset($_GET['details'])) {
                echo '<p>Details: ' . htmlspecialchars($_GET['details']) . '</p>'; // Display error details if available
            }
        } else if ($_GET['message'] == 'unverified_success') {
            echo '<h3>Success!</h3><p>Client has been successfully unverified.</p>';
        } else if ($_GET['message'] == 'unverified_error') {
            echo '<h3>Error!</h3><p>Client unverification failed. Please try again.</p>';
            if (isset($_GET['details'])) {
                echo '<p>Details: ' . htmlspecialchars($_GET['details']) . '</p>';
            }
        } else if ($_GET['message'] == 'unverified_already') {
            echo '<h3>Warning!</h3><p>This user is not yet verified. Please verify first.</p>';
        }
        echo '      </div>
                        <a href="client.php"><button class="login-btn btn-primary-soft btn" style="margin-top:15px;">OK</button></a>
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
                        <p class="heading-main12" style="margin-left: 45px;font-size:18px;color:rgb(49, 49, 49)">All Clients (<?php echo $list11->num_rows; ?>)</p>
                    </td>

                </tr>
                <?php
                    if($_POST){
                        $keyword=$_POST["search"];

                       $sqlmain= "select * from client inner join webuser on client.cemail=webuser.email where client.cemail='$keyword' or cname='$keyword' or cname like '$keyword%' or cname like '%$keyword' or cname like '%$keyword%' ";
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
                                <th class="table-headin">
                                    Name
                                </th>
                                <th class="table-headin">
                                    Phone Number
                                </th>
                                <th class="table-headin">
                                    Email
                                </th>
                                <th class="table-headin">
                                    Date of Birth
                                </th>
                                <th class="table-headin">
                                    Verified
                                </th>
                                <th class="table-headin">
                                    Actions
                                </th>
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
                                                        onmouseover="this.classList.add(\'btn-unverify-hover\')"
                                                        onmouseout="this.classList.remove(\'btn-unverify-hover\')"
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

    <div id="deleteConfirmModal" class="custom-modal">
        <div class="custom-modal-content">
            <h3 id="deleteModalTitle">Confirm Deletion</h3>
            <p>Are you sure you want to delete this client?</p>
            <div class="modal-buttons">
                <button class="confirm-btn" id="confirmDeleteBtn">Confirm</button>
                <button class="cancel-btn" onclick="hideDeleteConfirmModal()">Cancel</button>
            </div>
        </div>
    </div>

    <div id="unverifyConfirmModal" class="custom-modal">
        <div class="custom-modal-content">
            <h3 id="unverifyModalTitle">Confirm Unverification</h3>
            <p>Are you sure you want to unverify this client?</p>
            <div class="modal-buttons">
                <button class="confirm-btn" id="confirmUnverifyBtn">Confirm</button>
                <button class="cancel-btn" onclick="hideUnverifyConfirmModal()">Cancel</button>
            </div>
        </div>
    </div>


    <script>
        let currentClientId = null;
        let currentClientEmail = null;

        function showDeleteConfirmModal(clientId, clientEmail) {
            currentClientId = clientId;
            currentClientEmail = clientEmail;
            document.getElementById('deleteConfirmModal').style.display = 'flex'; // Changed to 'flex' for centering
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
            window.history.pushState({}, '', url); // Update URL without reloading
            document.getElementById('viewDetailsModal').style.display = 'none';
        }


        document.getElementById('confirmDeleteBtn').addEventListener('click', function() {
            if (currentClientId && currentClientEmail) {
                // Create a form dynamically to submit POST request
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = 'client.php'; // Submit to this page for processing

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

                document.body.appendChild(form); // Append form to body
                form.submit(); // Submit the form
            }
            hideDeleteConfirmModal(); // Hide modal after submission
        });

        document.getElementById('confirmUnverifyBtn').addEventListener('click', function() {
            if (currentClientId && currentClientEmail) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = 'client.php';

                const actionInput = document.createElement('input');
                actionInput.type = 'hidden';
                actionInput.name = 'action';
                actionInput.value = 'unverify_client'; // New action
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
                document.getElementById('deleteConfirmModal').style.display = 'none';
                document.getElementById('unverifyConfirmModal').style.display = 'none'; // Hide unverify modal too
            }
        };
    </script>

</body>
</html>
