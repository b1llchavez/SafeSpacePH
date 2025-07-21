<?php

    session_start();

    if(!isset($_SESSION["user"]) || $_SESSION["user"] == "" || $_SESSION['usertype'] != 'a'){
        header("location: ../login.php");
        exit();
    }

    include("../connection.php");

    $useremail = $_SESSION["user"];
    $userrow = $database->query("SELECT * FROM admin WHERE aemail='$useremail'");
    $userfetch = $userrow->fetch_assoc();

    date_default_timezone_set('Asia/Manila');
    $date = date('Y-m-d');
    $_SESSION["date"] = $date;

    $reports_query = "SELECT * FROM reports";
    $report_result = $database->query($reports_query);

    $action = $_GET['action'] ?? null;
    $id = $_GET['id'] ?? null;

    $popup_message = "";

    // Handle GET requests for actions like view, delete, edit notes
    if (!empty($action) && !empty($id)) {
        // Sanitize ID for all GET actions to prevent SQL Injection
        $id_safe = mysqli_real_escape_string($database, $id);

        if($action=='drop'){
            $nameget = $_GET["name"] ?? 'this record';
            $popup_message = '
            <div id="popup1" class="overlay">
                    <div class="popup">
                    <center>
                        <h2>Are you sure?</h2>
                        <a class="close" href="admin_reports.php">&times;</a>
                        <div class="content">
                            You want to delete this Record?<br>(' . htmlspecialchars($nameget) . ').
                            
                        </div>
                        <div style="display: flex;justify-content: center;">
                        <form action="admin_reports.php" method="POST">
                            <input type="hidden" name="id" value="' . htmlspecialchars($id) . '">
                            <input type="hidden" name="action" value="confirm_drop">
                            <input type="submit" value="Yes" class="btn-primary btn" style="margin:10px;padding:10px;width:150px;">
                        </form>
                            <a href="admin_reports.php" class="non-style-link"><button class="btn-primary btn" style="margin:10px;padding:10px;width:150px;">No</button></a>
                        </div>
                    </center>
            </div>
            </div>
            ';
        } elseif ($action == 'view') {
            $sqlmain = "SELECT * FROM reports WHERE id='$id_safe'";
            $result = $database->query($sqlmain);
            if ($result->num_rows > 0) {
                $report_details = $result->fetch_assoc();
                $report_id_details = htmlspecialchars($report_details['id'] ?? '');
                $client_id_details = htmlspecialchars($report_details['client_id'] ?? '');
                $reporter_name_details = htmlspecialchars($report_details['reporter_name'] ?? '');
                $reporter_phone_details = htmlspecialchars($report_details['reporter_phone'] ?? '');
                $reporter_email_details = htmlspecialchars($report_details['reporter_email'] ?? '');
                $violation_type_details = htmlspecialchars($report_details['violation_type'] ?? '');
                $incident_date_details = htmlspecialchars($report_details['incident_date'] ?? '');
                $incident_time_details = htmlspecialchars($report_details['incident_time'] ?? '');
                $incident_location_details = nl2br(htmlspecialchars($report_details['incident_location'] ?? ''));
                $description_details = nl2br(htmlspecialchars($report_details['description'] ?? ''));
                $victim_name_details = htmlspecialchars($report_details['victim_name'] ?? '');
                $victim_contact_details = htmlspecialchars($report_details['victim_contact'] ?? '');
                $perpetrator_name_details = htmlspecialchars($report_details['perpetrator_name'] ?? '');
                $evidence_file_details = htmlspecialchars($report_details['evidence_file'] ?? '');
                $status_details = htmlspecialchars($report_details['status'] ?? '');
                $admin_notes_details = nl2br(htmlspecialchars($report_details['admin_notes'] ?? ''));
                $submission_date_details = htmlspecialchars($report_details['submission_date'] ?? '');

                $evidence_link = "";
                if (!empty($evidence_file_details)) {
                    $file_path = '../uploads/reports/' . $evidence_file_details;
                    if (file_exists($file_path)) {
                        $evidence_link = '<a href="' . $file_path . '" target="_blank" class="non-style-link"><button class="btn-primary-soft btn" style="font-size: 14px; padding: 8px 12px; margin-top: 5px;">View Evidence</button></a>';
                    } else {
                        $evidence_link = '<span style="color:red;">File not found</span>';
                    }
                } else {
                    $evidence_link = 'No file uploaded';
                }
            
                $popup_message = '
                <div id="popup1" class="overlay">
                        <div class="popup" style="width: 80%; max-width: 800px;"> <center>
                            <h2>Report Details</h2>
                            <a class="close" href="admin_reports.php">&times;</a>
                            <div class="content" style="max-height: 70vh; overflow-y: auto; padding: 20px;"> <table style="width: 100%; text-align: left; margin: 0 auto;">
                                    <tr>
                                        <td class="label-td" style="width: 50%;">
                                            <label for="report_id" class="form-label">Report ID: </label>
                                            <p>'. $report_id_details . '</p><br>
                                        </td>
                                        <td class="label-td" style="width: 50%;">
                                            <label for="client_id" class="form-label">Client ID: </label>
                                            <p>'. $client_id_details . '</p><br>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="label-td" colspan="2">
                                            <label for="reporter_info" class="form-label">Reporter Information: </label>
                                            <p>Name: '. $reporter_name_details . '</p>
                                            <p>Phone: '. $reporter_phone_details . '</p>
                                            <p>Email: '. $reporter_email_details . '</p><br>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="label-td" colspan="2">
                                            <label for="incident_details" class="form-label">Incident Details: </label>
                                            <p>Violation Type: '. $violation_type_details . '</p>
                                            <p>Date: '. $incident_date_details . '</p>
                                            <p>Time: '. $incident_time_details . '</p>
                                            <p>Location: '. $incident_location_details . '</p>
                                            <p>Description: '. $description_details . '</p><br>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="label-td" colspan="2">
                                            <label for="victim_info" class="form-label">Victim Information: </label>
                                            <p>Name: '. $victim_name_details . '</p>
                                            <p>Contact: '. $victim_contact_details . '</p><br>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="label-td" colspan="2">
                                            <label for="perpetrator_info" class="form-label">Perpetrator Information: </label>
                                            <p>Name/Description: '. $perpetrator_name_details . '</p><br>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="label-td" colspan="2">
                                            <label for="evidence" class="form-label">Evidence File: </label>
                                            <p>'. $evidence_link . '</p><br>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="label-td" colspan="2">
                                            <label for="status" class="form-label">Status: </label>
                                            <p>'. $status_details . '</p><br>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="label-td" colspan="2">
                                            <label for="admin_notes" class="form-label">Admin Notes: </label>
                                            <p>'. $admin_notes_details . '</p><br>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="label-td" colspan="2">
                                            <label for="submission_date" class="form-label">Submission Date: </label>
                                            <p>'. $submission_date_details . '</p><br>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td colspan="2">
                                            <a href="admin_reports.php"><input type="button" value="OK" class="login-btn btn-primary-soft btn" ></a>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                        </center>
                    </div>
                </div>';
            }
        } elseif ($action == 'reject') {
            $sqlmain = "SELECT * FROM reports WHERE id='$id_safe'";
            $result = $database->query($sqlmain);
            $report_details = $result->fetch_assoc();
            $report_id_details = htmlspecialchars($report_details['id'] ?? '');
            $reporter_name_details = htmlspecialchars($report_details['reporter_name'] ?? '');
            $admin_notes_current = htmlspecialchars($report_details['admin_notes'] ?? '');

            $popup_message = '
            <div id="popup1" class="overlay">
                <div class="popup">
                    <center>
                        <h2>Reject Report</h2>
                        <a class="close" href="admin_reports.php">&times;</a>
                        <div class="content">
                            Are you sure you want to reject Report ID: ' . $report_id_details . ' by ' . $reporter_name_details . '?
                            <form action="admin_reports.php" method="POST">
                                <input type="hidden" name="id" value="' . $id . '">
                                <input type="hidden" name="action" value="confirm_reject">
                                <label for="admin_notes" class="form-label">Admin Notes (Optional):</label>
                                <textarea name="admin_notes" class="input-text" placeholder="Add notes for rejection...">' . $admin_notes_current . '</textarea>
                                <div style="display: flex;justify-content: center;margin-top:20px;">
                                    <input type="submit" value="Yes, Reject" class="btn-primary btn" style="margin:10px;padding:10px;width:150px;background-color:#dc3545;">
                                    <a href="admin_reports.php" class="non-style-link"><button class="btn-primary btn" style="margin:10px;padding:10px;width:150px;">Cancel</button></a>
                                </div>
                            </form>
                        </div>
                    </center>
                </div>
            </div>';
        } elseif ($action == 'submit') { // Renamed from 'accept' to 'submit' to match status
            $sqlmain = "SELECT * FROM reports WHERE id='$id_safe'";
            $result = $database->query($sqlmain);
            $report_details = $result->fetch_assoc();
            $report_id_details = htmlspecialchars($report_details['id'] ?? '');
            $reporter_name_details = htmlspecialchars($report_details['reporter_name'] ?? '');
            $admin_notes_current = htmlspecialchars($report_details['admin_notes'] ?? '');

            $popup_message = '
            <div id="popup1" class="overlay">
                <div class="popup">
                    <center>
                        <h2>Submit Report to Authorities</h2>
                        <a class="close" href="admin_reports.php">&times;</a>
                        <div class="content">
                            Are you sure you want to mark Report ID: ' . $report_id_details . ' by ' . $reporter_name_details . ' as "Submitted to Authorities"?
                            <form action="admin_reports.php" method="POST">
                                <input type="hidden" name="id" value="' . $id . '">
                                <input type="hidden" name="action" value="confirm_submit">
                                <label for="admin_notes" class="form-label">Admin Notes (Optional):</label>
                                <textarea name="admin_notes" class="input-text" placeholder="Add notes for submission...">' . $admin_notes_current . '</textarea>
                                <div style="display: flex;justify-content: center;margin-top:20px;">
                                    <input type="submit" value="Yes, Submit" class="btn-primary btn" style="margin:10px;padding:10px;width:150px;background-color:#28a745;">
                                    <a href="admin_reports.php" class="non-style-link"><button class="btn-primary btn" style="margin:10px;padding:10px;width:150px;">Cancel</button></a>
                                </div>
                            </form>
                        </div>
                    </center>
                </div>
            </div>';
        }
    }

    // Handle POST requests for confirming actions
    if($_POST){
        $id = $_POST['id'] ?? '';
        $action_type = $_POST['action'] ?? '';
        $admin_notes_raw = $_POST['admin_notes'] ?? '';

        // Escape variables for security
        $id_safe = mysqli_real_escape_string($database, $id);
        $admin_notes = mysqli_real_escape_string($database, $admin_notes_raw);


        if($action_type == 'confirm_drop'){
            $sql_delete_report = "DELETE FROM reports WHERE id = '$id_safe'";
            if ($database->query($sql_delete_report)) {
                header("location: admin_reports.php?action=deleted");
                exit();
            } else {
                header("location: admin_reports.php?action=error&message=".urlencode("Failed to delete report. " . $database->error));
                exit();
            }
        } elseif($action_type == 'confirm_reject'){
            $update_sql = "UPDATE reports SET status = 'rejected', admin_notes = '$admin_notes' WHERE id = '$id_safe'";
            if ($database->query($update_sql)) {
                header("location: admin_reports.php?action=rejected");
                exit();
            } else {
                header("location: admin_reports.php?action=error&message=".urlencode("Failed to reject report. " . $database->error));
                exit();
            }
        } elseif ($action_type == 'confirm_submit'){ // Renamed from 'confirm_accept' to 'confirm_submit'
            $update_sql = "UPDATE reports SET status = 'submitted', admin_notes = '$admin_notes' WHERE id = '$id_safe'";
            if ($database->query($update_sql)) {
                header("location: admin_reports.php?action=submitted");
                exit();
            } else {
                header("location: admin_reports.php?action=error&message=".urlencode("Failed to submit report. " . $database->error));
                exit();
            }
        }
    }

    // Display success/error messages after actions
    if(isset($_GET['action'])){
        $action_result = $_GET['action'];
        if($action_result == 'deleted'){
            $popup_message = '<div id="popup1" class="overlay">
                <div class="popup">
                <center>
                    <h2>Deleted Successfully!</h2>
                    <a class="close" href="admin_reports.php">&times;</a>
                    <div class="content">
                        The report has been deleted.
                    </div>
                    <div style="display: flex;justify-content: center;">
                    <a href="admin_reports.php" class="non-style-link"><button class="btn-primary btn" style="display: flex;justify-content: center;align-items: center;margin:10px;padding:10px;">OK</button></a>
                    </div>
                    <br><br>
                </center>
                </div>
            </div>';
        } elseif($action_result == 'rejected'){
            $popup_message = '<div id="popup1" class="overlay">
                <div class="popup">
                <center>
                    <h2>Report Rejected!</h2>
                    <a class="close" href="admin_reports.php">&times;</a>
                    <div class="content">
                        The report has been marked as "rejected".
                    </div>
                    <div style="display: flex;justify-content: center;">
                    <a href="admin_reports.php" class="non-style-link"><button class="btn-primary btn" style="display: flex;justify-content: center;align-items: center;margin:10px;padding:10px;">OK</button></a>
                    </div>
                    <br><br>
                </center>
                </div>
            </div>';
        } elseif($action_result == 'submitted'){
            $popup_message = '<div id="popup1" class="overlay">
                <div class="popup">
                <center>
                    <h2>Report Submitted!</h2>
                    <a class="close" href="admin_reports.php">&times;</a>
                    <div class="content">
                        The report has been marked as "submitted to authorities".
                    </div>
                    <div style="display: flex;justify-content: center;">
                    <a href="admin_reports.php" class="non-style-link"><button class="btn-primary btn" style="display: flex;justify-content: center;align-items: center;margin:10px;padding:10px;">OK</button></a>
                    </div>
                    <br><br>
                </center>
                </div>
            </div>';
        } elseif($action_result == 'error'){
            $error_message = htmlspecialchars($_GET['message'] ?? 'An unknown error occurred.');
            $popup_message = '<div id="popup1" class="overlay">
                <div class="popup">
                <center>
                    <h2>Error!</h2>
                    <a class="close" href="admin_reports.php">&times;</a>
                    <div class="content" style="color:rgb(255, 62, 62);">
                        '.$error_message.'
                    </div>
                    <div style="display: flex;justify-content: center;">
                    <a href="admin_reports.php" class="non-style-link"><button class="btn-primary btn" style="display: flex;justify-content: center;align-items: center;margin:10px;padding:10px;">OK</button></a>
                    </div>
                    <br><br>
                </center>
                </div>
            </div>';
        }
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
    <title>Violation Reports | SafeSpace PH Admin</title>
    <style>
        .popup{
            animation: transitionIn-Y-bottom 0.5s;
            max-height: 90vh; /* Set a maximum height for the popup */
            overflow-y: auto; /* Enable vertical scrolling */
            padding: 20px; /* Add padding to prevent content from touching edges */
        }
        .sub-table{
            animation: transitionIn-Y-bottom 0.5s;
        }
        .status-badge {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 5px;
            font-weight: bold;
            color: #fff;
        }
        .status-pending { background-color: #ffc107; /* Orange */ }
        .status-rejected { background-color: #dc3545; /* Red */ }
        .status-submitted { background-color: #28a745; /* Green */ } /* For reports submitted to authorities */
        
        /* Ensure content inside popup table fits */
        .popup .content table {
            width: 100%;
            /* max-width: 700px; */ /* Added this to help with overall table width in popup */
            margin: 0 auto;
        }
        .popup .content table td {
            padding: 8px 0;
            vertical-align: top;
        }
        .popup .content table .label-td {
            font-weight: normal; /* Changed to normal for better readability */
            color: #161c2d;
        }
        .popup .content table p {
            margin: 0;
            padding: 0;
            word-wrap: break-word; /* Ensure long text breaks */
        }
        .dash-body {
            overflow-y: auto;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="menu">
            <table class="menu-container">
                <tr>
                    <td style="padding:10px" colspan="2">
                        <table class="profile-container">
                            <tr>
                                <td style="width:30%; padding-left:20px">
                                    <img src="../img/user.png" alt="" style="width:100%; border-radius:50%">
                                </td>
                                <td style="padding:0px;margin:0px;">
                                    <p class="profile-title">Administrator</p>
                                    <p class="profile-subtitle">admin@safespaceph.com</p>
                                </td>
                            </tr>
                            <tr>
                                <td colspan="2">
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
                        <a href="index.php" class="non-style-link-menu">
                            <div>
                                <p class="menu-text">Dashboard</p>
                            </div>
                        </a>
                    </td>
                </tr>
                <tr class="menu-row">
                    <td class="menu-btn menu-icon-report menu-active menu-icon-report-active">
                        <a href="admin_reports.php" class="non-style-link-menu non-style-link-menu-active">
                            <div>
                                <p class="menu-text menu-text-active">Violation Reports</p>
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
            <table style="width:100%; border-spacing: 0;margin:0;padding:0;margin-top:25px;">
                <tr>
                    <td width="13%">
                        <a href="index.php" ><button  class="login-btn btn-primary-soft btn btn-icon-back"  style="padding-top:11px;padding-bottom:11px;margin-left:20px;width:125px"><span class="tn-in-text">Back</span></button></a>
                    </td>
                    <td>
                        <p class="heading-main12" style="margin-left: 10px;font-size:18px;color:rgb(49, 49, 49)">Violation Reports</p>
                    </td>
                    <td width="15%">
                        <p style="font-size: 14px;color: rgb(119, 119, 119);padding: 0;margin: 0;text-align: right;">
                            Today's Date
                        </p>
                        <p class="heading-sub12" style="padding: 0;margin: 0;">
                            <?php echo $date; ?>
                        </p>
                    </td>
                    <td width="10%">
                        <button  class="btn-label"  style="display: flex;justify-content: center;align-items: center;"><img src="../img/calendar.svg" style="width:100%"></button>
                    </td>
                </tr>
                <tr>
                    <td colspan="4" style="padding-top:10px;">
                        <p class="heading-main12" style="margin-left: 45px;font-size:18px;color:rgb(49, 49, 49)">All Reports (<?php echo $report_result->num_rows; ?>)</p>
                    </td>
                </tr>
                <tr>
                    <td colspan="4">
                        <center>
                            <div class="abc scroll">
                                <table class="sub-table scrolldown" style="width: 93%;">
                                <thead>
                                    <tr>
                                        <th class="table-headin">Report ID</th>
                                        <th class="table-headin">Client ID</th>
                                        <th class="table-headin">Reporter Name</th>
                                        <th class="table-headin">Violation Type</th>
                                        <th class="table-headin">Status</th>
                                        <th class="table-headin">Submission Date</th>
                                        <th class="table-headin">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php
                                    if($report_result->num_rows == 0){
                                        echo '<tr>
                                                <td colspan="7">
                                                <br><br><br><br>
                                                <center>
                                                <img src="../img/notfound.svg" width="25%">
                                                
                                                <br>
                                                <p class="heading-main12" style="margin-left: 45px;font-size:20px;color:rgb(49, 49, 49)">We couldnt find anything related to your keywords !</p>
                                                <a class="non-style-link" href="admin_reports.php"><button  class="login-btn btn-primary-soft btn"  style="display: flex;justify-content: center;align-items: center;margin-left:20px;"><i class="fa-solid fa-arrows-rotate"></i>&nbsp; Show all Reports</button>
                                                </a>
                                                </center>
                                                <br><br>
                                                </td>
                                            </tr>';
                                    } else {
                                        while($reportidrow = $report_result->fetch_assoc()){
                                            $id = $reportidrow["id"];
                                            $client_id = $reportidrow["client_id"] ?? ''; // Get client ID, provide default
                                            $reporter_name = htmlspecialchars($reportidrow["reporter_name"] ?? '');
                                            $violation_type = htmlspecialchars($reportidrow["violation_type"] ?? '');
                                            $status = htmlspecialchars($reportidrow["status"] ?? '');
                                            $submission_date = htmlspecialchars($reportidrow["submission_date"] ?? '');

                                            $status_class = '';
                                            switch($status) {
                                                case 'pending':
                                                    $status_class = 'status-pending';
                                                    break;
                                                case 'rejected':
                                                    $status_class = 'status-rejected';
                                                    break;
                                                case 'submitted':
                                                    $status_class = 'status-submitted';
                                                    break;
                                                default:
                                                    $status_class = '';
                                            }

                                            echo '<tr>
                                                    <td>'. $id .'</td>
                                                    <td>'. $client_id .'</td>
                                                    <td>'. $reporter_name .'</td>
                                                    <td>'. $violation_type .'</td>
                                                    <td><span class="status-badge '. $status_class .'">'. ucfirst($status) .'</span></td>
                                                    <td>'. $submission_date .'</td>
                                                    <td>
                                                        <div style="display:flex;justify-content: center;">
                                                        <a href="?action=view&id='. $id .'" class="non-style-link"><button class="btn-primary-soft btn" style="padding: 5px 10px; margin-right: 5px;"><i class="fa-solid fa-eye"></i> View</button></a>
                                                        <a href="?action=submit&id='. $id .'" class="non-style-link"><button class="btn-primary-soft btn" style="padding: 5px 10px; margin-right: 5px;"><i class="fa-solid fa-check"></i> Submit</button></a>
                                                        <a href="?action=reject&id='. $id .'" class="non-style-link"><button class="btn-primary-soft btn" style="padding: 5px 10px; margin-right: 5px;"><i class="fa-solid fa-xmark"></i> Reject</button></a>
                                                        <a href="?action=drop&id='. $id .'&name='. urlencode($reporter_name) .'" class="non-style-link"><button class="btn-primary-soft btn" style="padding: 5px 10px;"><i class="fa-solid fa-trash"></i> Delete</button></a>
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
    <?php echo $popup_message; ?>
    <script src="https://kit.fontawesome.com/a0b4d4508a.js" crossorigin="anonymous"></script>
</body>
</html>
