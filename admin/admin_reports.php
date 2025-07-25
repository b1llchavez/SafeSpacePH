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

    $reports_query = "SELECT * FROM reports ORDER BY uploaded_at DESC";
    $report_result = $database->query($reports_query);

    $action = $_GET['action'] ?? null;
    $id = $_GET['id'] ?? null;

    $popup_message = "";


    if (!empty($action) && !empty($id)) {

        $id_safe = mysqli_real_escape_string($database, $id);

        if($action=='drop'){
            $nameget = $_GET["name"] ?? 'this record';
            $popup_message = '
            <div id="popup1" class="overlay">
                <div class="modal-content" style="max-width: 500px;">
                    <h2 class="modal-header">
                        Are You Sure?
                    </h2>
                    <div class="modal-divider"></div>
                    <div class="modal-body">
                        <p>You are about to permanently delete the report by<br> <strong>' . htmlspecialchars($nameget) . '</strong>.</p>
                        <p style="color: #dc3545; font-weight: bold; margin-top: 10px;">This action cannot be undone.</p>
                    </div>
                    <div class="modal-footer">
                        <form action="admin_reports.php" method="POST" style="margin:0;">
                            <input type="hidden" name="id" value="' . htmlspecialchars($id) . '">
                            <input type="hidden" name="action" value="confirm_drop">
                            <button type="submit" class="modal-btn modal-btn-danger">Yes, Delete</button>
                        </form>
                        <a href="admin_reports.php" class="non-style-link">
<button type="button" class="modal-btn modal-btn-soft">Cancel</button>                        </a>
                    </div>
                </div>
            </div>';
        } elseif ($action == 'view') {
            $sqlmain = "SELECT * FROM reports WHERE id='$id_safe'";
            $result = $database->query($sqlmain);
            if ($result->num_rows > 0) {
                $report_details = $result->fetch_assoc();
                

                function extract_detail($pattern, $description) {
                    if (preg_match($pattern, $description, $matches)) {
                        return trim($matches[1]);
                    }
                    return ''; // Return empty string if not found
                }


                $report_id_details = htmlspecialchars($report_details['id'] ?? '');
                $client_id_details = htmlspecialchars($report_details['client_id'] ?? 'N/A');
                $submission_date_details = htmlspecialchars($report_details['uploaded_at'] ?? '');

                $status_details = htmlspecialchars($report_details['report_status'] ?? 'pending');
                $violation_type_details = htmlspecialchars(str_replace("Violation Report: ", "", $report_details['title'] ?? 'N/A'));
                

                $reporter_name_details = htmlspecialchars($report_details['reporter_name'] ?? 'N/A');
                $reporter_phone_details = htmlspecialchars($report_details['reporter_phone'] ?? 'N/A');
                $reporter_email_details = htmlspecialchars($report_details['reporter_email'] ?? 'N/A');


                $full_description = $report_details['description'] ?? '';
                $incident_date_details = htmlspecialchars(extract_detail('/Date of Incident: (.*?)\n/s', $full_description));
                $incident_time_details = htmlspecialchars(extract_detail('/Time of Incident: (.*?)\n/s', $full_description));
                $incident_location_details = nl2br(htmlspecialchars(extract_detail('/Location of Incident: (.*?)\n/s', $full_description)));
                $perpetrator_name_details = htmlspecialchars(extract_detail('/Perpetrator Information: (.*?)\n/s', $full_description));
                $victim_name_details = htmlspecialchars(extract_detail("/Victim's Name: (.*?)\n/s", $full_description));
                $victim_contact_details = htmlspecialchars(extract_detail("/Victim's Contact: (.*?)\n/s", $full_description));
                
                $description_parts = explode("---Reporter's Detailed Description---", $full_description);
                $description_details = isset($description_parts[1]) ? nl2br(htmlspecialchars(trim($description_parts[1]))) : 'N/A';


                $legal_consultation_details = htmlspecialchars($report_details['legal_consultation_requested'] ?? 'N/A');
                $supplementary_notes_details = nl2br(htmlspecialchars($report_details['supplementary_notes'] ?? ''));
                $evidence_file_details = htmlspecialchars($report_details['file_name'] ?? '');

                $evidence_link = "";
                if (!empty($evidence_file_details)) {
                    $file_path = htmlspecialchars($report_details['file_path']);
                    $server_path = realpath(__DIR__ . '/..') . '/' . substr($file_path, 3);
                    if (file_exists($server_path)) {
                        $evidence_link = '<a href="' . $file_path . '" target="_blank" class="non-style-link"><button class="btn-primary-soft btn" style="font-size: 14px; padding: 8px 12px; margin-top: 5px;">View Evidence</button></a>';
                    } else {
                        $evidence_link = '<span style="color:red;">File not found</span>';
                    }
                } else {
                    $evidence_link = 'No file uploaded';
                }
            
                $popup_message = '
                <div id="popup1" class="overlay view-modal-overlay">
                    <div class="modal-content modal-content-view">
                        <h2 class="modal-header">
                            Report Details
                        </h2>
                        <div class="modal-divider"></div>
                        <div class="modal-body modal-body-left">
                            
                            <div class="detail-section">
                                <h4>Report Overview</h4><hr>
                                <div class="detail-item"><strong>Report ID:</strong> <span>'. $report_id_details . '</span></div>
                                <div class="detail-item"><strong>Client ID:</strong> <span>'. $client_id_details . '</span></div>
                                <div class="detail-item"><strong>Submission Date:</strong> <span>'. date("F j, Y, g:i a", strtotime($submission_date_details)) . '</span></div>
                                <div class="detail-item"><strong>Status:</strong> <span><span class="status-badge status-'. strtolower($status_details) .'">'. ucfirst($status_details) .'</span></span></div>
                            </div>
                
                            <div class="detail-section">
                                <h4>Reporter Information</h4><hr>
                                <div class="detail-item"><strong>Name:</strong> <span>'. ($reporter_name_details ?: "N/A") . '</span></div>
                                <div class="detail-item"><strong>Phone:</strong> <span>'. ($reporter_phone_details ?: "N/A") . '</span></div>
                                <div class="detail-item"><strong>Email:</strong> <span>'. ($reporter_email_details ?: "N/A") . '</span></div>
                            </div>
                
                            <div class="detail-section">
                                <h4>Incident Details</h4><hr>
                                <div class="detail-item"><strong>Violation Type:</strong> <span>'. ($violation_type_details ?: "N/A") . '</span></div>
                                <div class="detail-item"><strong>Date of Incident:</strong> <span>'. (!empty($incident_date_details) ? date("F j, Y", strtotime($incident_date_details)) : 'N/A') . '</span></div>
                                <div class="detail-item"><strong>Time of Incident:</strong> <span>'. (!empty($incident_time_details) ? date("h:i A", strtotime($incident_time_details)) : 'N/A') . '</span></div>
                                <div class="detail-item"><strong>Location of Incident:</strong> <div style="flex-grow:1;">'. ($incident_location_details ?: "N/A") . '</div></div>
                                <div class="detail-item"><strong>Reporter\'s Description:</strong> <div class="description-box" style="flex-grow:1;">'. $description_details . '</div></div>
                            </div>

                            <div class="detail-section">
                                <h4>Parties Involved</h4><hr>
                                <div class="detail-item"><strong>Victim\'s Name:</strong> <span>'. ($victim_name_details ?: "N/A") . '</span></div>
                                <div class="detail-item"><strong>Victim\'s Contact:</strong> <span>'. ($victim_contact_details ?: "N/A") . '</span></div>
                                <div class="detail-item"><strong>Perpetrator Information:</strong> <span>'. ($perpetrator_name_details ?: "N/A") . '</span></div>
                            </div>
                            
                            <div class="detail-section">
                                <h4>Client Requests & Notes</h4><hr>
                                <div class="detail-item"><strong>Legal Consultation Requested:</strong> <span>' . ($legal_consultation_details ?: "N/A") . '</span></div>
                                <div class="detail-item"><strong>Client\'s Supplementary Notes:</strong> <div class="description-box" style="flex-grow:1;">'. ($supplementary_notes_details ?: 'N/A') . '</div></div>
                            </div>
                
                            <div class="detail-section">
                                <h4>Evidence</h4><hr>
                                <div class="detail-item"><strong>Evidence File:</strong> <span>'. $evidence_link . '</span></div>
                            </div>
                
                        </div>
                        <div class="modal-footer">
                             <a href="admin_reports.php" class="non-style-link">
                                <button type="button" class="modal-btn modal-btn-soft">Close</button>
                            </a>
                        </div>
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
                <div class="modal-content">
                    <h2 class="modal-header">
                        Reject Report
                    </h2>
                    <div class="modal-divider"></div>
                    <form action="admin_reports.php" method="POST">
                        <div class="modal-body modal-body-left">
                            <p>Are you sure you want to reject Report ID <strong>' . $report_id_details . '</strong> by <strong>' . $reporter_name_details . '</strong>?</p>
                            <input type="hidden" name="id" value="' . $id . '">
                            <input type="hidden" name="action" value="confirm_reject">
                            <label for="admin_notes" class="form-label" style="margin-top: 15px; display: block;">Admin Notes (Reason for rejection):</label>
                            <textarea name="admin_notes" class="input-text" style="width: 100%; min-height: 80px;" placeholder="Provide a reason for rejecting this report...">' . $admin_notes_current . '</textarea>
                        </div>
                        <div class="modal-footer">
                             <button type="submit" class="modal-btn modal-btn-primary">Yes, Reject</button>
                            <a href="admin_reports.php" class="non-style-link">
<button type="button" class="modal-btn modal-btn-soft">Cancel</button>                            </a>
                        </div>
                    </form>
                </div>
            </div>';
        } elseif ($action == 'submit') {
            $sqlmain = "SELECT * FROM reports WHERE id='$id_safe'";
            $result = $database->query($sqlmain);
            $report_details = $result->fetch_assoc();
            $report_id_details = htmlspecialchars($report_details['id'] ?? '');
            $reporter_name_details = htmlspecialchars($report_details['reporter_name'] ?? '');
            $admin_notes_current = htmlspecialchars($report_details['admin_notes'] ?? '');

            $popup_message = '
            <div id="popup1" class="overlay">
                <div class="modal-content">
                    <h2 class="modal-header">
                        Submit Report to Authorities
                    </h2>
                    <div class="modal-divider"></div>
                    <form action="admin_reports.php" method="POST">
                        <div class="modal-body modal-body-left">
                            <p>Confirm submission of Report ID <strong>' . $report_id_details . '</strong> as "Submitted to Authorities".</p>
                            <input type="hidden" name="id" value="' . $id . '">
                            <input type="hidden" name="action" value="confirm_submit">
                            <label for="admin_notes" class="form-label" style="margin-top: 15px; display: block;">Admin Notes (Optional):</label>
                            <textarea name="admin_notes" class="input-text" style="width: 100%; min-height: 80px;" placeholder="Add any relevant notes for submission...">' . $admin_notes_current . '</textarea>
                        </div>
                        <div class="modal-footer">
                            <button type="submit" class="modal-btn modal-btn-primary">Yes, Submit</button>
                            <a href="admin_reports.php" class="non-style-link">
<button type="button" class="modal-btn modal-btn-soft">Cancel</button>                            </a>
                        </div>
                    </form>
                </div>
            </div>';
        }
    }





    if($_POST){
        $id = $_POST['id'] ?? '';
        $action_type = $_POST['action'] ?? '';
        $admin_notes_raw = $_POST['admin_notes'] ?? '';

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

            $update_sql = "UPDATE reports SET report_status = 'rejected', admin_notes = '$admin_notes' WHERE id = '$id_safe'";
            if ($database->query($update_sql)) {
                header("location: admin_reports.php?action=rejected");
                exit();
            } else {
                header("location: admin_reports.php?action=error&message=".urlencode("Failed to reject report. " . $database->error));
                exit();
            }
        } elseif ($action_type == 'confirm_submit'){

            $update_sql = "UPDATE reports SET report_status = 'submitted', admin_notes = '$admin_notes' WHERE id = '$id_safe'";
            if ($database->query($update_sql)) {
                header("location: admin_reports.php?action=submitted");
                exit();
            } else {
                header("location: admin_reports.php?action=error&message=".urlencode("Failed to submit report. " . $database->error));
                exit();
            }
        }
    }


    if(isset($_GET['action'])){
        $action_result = $_GET['action'];
        $popup_title = '';
        $popup_content = '';
        $is_error = false;

        switch ($action_result) {
            case 'deleted':
                $popup_title = "Deleted Successfully!";
                $popup_content = "The report has been deleted.";
                break;
            case 'rejected':
                $popup_title = "Report Rejected!";
                $popup_content = 'The report has been marked as "rejected".';
                break;
            case 'submitted':
                $popup_title = "Report Submitted!";
                $popup_content = 'The report has been marked as "submitted to authorities".';
                break;
            case 'error':
                $is_error = true;
                $popup_title = "Error!";
                $popup_content = htmlspecialchars($_GET['message'] ?? 'An unknown error occurred.');
                break;
        }

        if (!empty($popup_title)) {
            $header_color = $is_error ? '#dc3545' : '#28a745';
            $button_class = $is_error ? 'modal-btn-secondary' : 'modal-btn-primary';


            $popup_message = '
            <div id="popup1" class="overlay">
                <div class="modal-content" style="max-width: 450px;">
                    <h2 class="modal-header" style="color: '. $header_color .';">
                        '. $popup_title .'
                    </h2>
                    <div class="modal-divider"></div>
                    <div class="modal-body">
                        <p>'. $popup_content .'</p>
                    </div>
                    <div class="modal-footer">
                        <a href="admin_reports.php" class="non-style-link">
                            <button type="button" class="modal-btn '. $button_class .'">OK</button>
                        </a>
                    </div>
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
    <link rel="icon" type="image/png" href="../img/logo.png">
    <title>Violation Reports | SafeSpace PH Admin</title>
    <style>
        .sub-table{
            animation: transitionIn-Y-bottom 0.5s;
        }
         
        .sub-table th, .sub-table td {
            padding: 16px 12px;  
            text-align: left;
            vertical-align: middle;
        }
        .sub-table th:last-child, .sub-table td:last-child {
            text-align: center;  
        }
        .sub-table tbody td {
            border-bottom: 1px solid #f0f0f0;  
        }
        .sub-table tbody tr:last-child td {
            border-bottom: none;  
        }

        .status-badge {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 5px;
            font-weight: bold;
            color: #fff;
            font-size: 12px;
        }

           .modal-body .status-badge {
            color: #fff !important;  
        }
        .status-pending { background-color: #ffc107; }
        .status-rejected { background-color: #dc3545; }
        .status-submitted { background-color: #28a745; }
        
        .dash-body {
            overflow-y: auto;
        }

         
        .overlay {
            position: fixed;
            top: 0;
            bottom: 0;
            left: 0;
            right: 0;
            background: rgba(0, 0, 0, 0.7);
            transition: opacity 500ms;
            visibility: visible;
            opacity: 1;
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
                    <td class="menu-btn menu-icon-client-verification">
                        <a href="client_verification.php" class="non-style-link-menu">
                            <div>
                                <p class="menu-text">Client Verification</p>
                            </div>
                        </a>
                    </td>
                </tr>
                <tr class="menu-row">
                    <td class="menu-btn menu-icon-lawyer-verification">
                        <a href="lawyer_verification.php" class="non-style-link-menu"><div><p class="menu-text">Lawyer Verification</p></div></a>
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
                    <td>
                        <p class="heading-main12" style="margin-left: 45px;font-size: 23px;font-weight: 600;">Violation Reports</p>
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
                                <table class="sub-table scrolldown" style="width: 93%; border-collapse: collapse;">
                                <thead>
                                    <tr>
                                        <th class="table-headin">Report ID</th>
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
                                                <td colspan="6">
                                                <br><br><br><br>
                                                <center>
                                                <img src="../img/notfound.svg" width="25%">
                                                
                                                <br>
                                                <p class="heading-main12" style="margin-left: 45px;font-size:20px;color:rgb(49, 49, 49)">No reports found!</p>
                                                <a class="non-style-link" href="admin_reports.php"><button  class="login-btn btn-primary-soft btn"  style="display: flex;justify-content: center;align-items: center;margin-left:20px;"><i class="fa-solid fa-arrows-rotate"></i>&nbsp; Show all Reports</button>
                                                </a>
                                                </center>
                                                <br><br>
                                                </td>
                                            </tr>';
                                    } else {
                                        while($reportidrow = $report_result->fetch_assoc()){
                                            $id = $reportidrow["id"];
                                            $reporter_name = htmlspecialchars($reportidrow["reporter_name"] ?? 'N/A');
                                            $violation_type = htmlspecialchars($reportidrow["title"] ?? 'N/A');

                                            $status = htmlspecialchars($reportidrow["report_status"] ?? 'pending');
                                            $submission_date = htmlspecialchars($reportidrow["uploaded_at"] ?? '');

                                            $status_class = 'status-' . strtolower($status);

                                            echo '<tr>
                                                    <td>'. $id .'</td>
                                                    <td>'. $reporter_name .'</td>
                                                    <td>'. str_replace("Violation Report: ", "", $violation_type) .'</td>
                                                    <td style="text-align: center;"><span class="status-badge '. $status_class .'">'. ucfirst($status) .'</span></td>
                                                    <td>'. date("M d, Y", strtotime($submission_date)) .'</td>
                                                    <td>
                                                        <div style="display:flex;justify-content: center; gap: 5px;">
                                                        <a href="?action=view&id='. $id .'" class="non-style-link"><button class="btn-primary-soft btn button-icon btn-view" style="padding-left: 40px;padding-top: 12px;padding-bottom: 12px;margin-top: 10px;"><font class="tn-in-text">View</font></button></a>
                                                        <a href="?action=submit&id='. $id .'" class="non-style-link"><button class="btn-primary-soft btn button-icon menu-icon-verify" style="padding-left: 40px;padding-top: 12px;padding-bottom: 12px;margin-top: 10px;"><font class="tn-in-text">Submit</font></button></a>
                                                        <a href="?action=reject&id='. $id .'" class="non-style-link"><button class="btn-primary-soft btn button-icon btn-unverify" style="padding-left: 40px;padding-top: 12px;padding-bottom: 12px;margin-top: 10px;"><font class="tn-in-text">Reject</font></button></a>
                                                        <a href="?action=drop&id='. $id .'&name='. urlencode($reporter_name) .'" class="non-style-link"><button class="btn-primary-soft btn button-icon btn-delete" style="padding-left: 40px;padding-top: 12px;padding-bottom: 12px;margin-top: 10px;"><font class="tn-in-text">Delete</font></button></a>
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