<?php

    session_start();

    // Check if user is logged in and is a client
    if(!isset($_SESSION["user"]) || $_SESSION["user"] == "" || $_SESSION['usertype'] != 'c'){
        header("location: ../login.php");
        exit(); // Always exit after a header redirect
    }

    // Include database connection
    include("../connection.php");

    // Retrieve clientid and clientname from session
    $clientid = $_SESSION['cid'];
    $clientname = $_SESSION['cname'];

    // Fetch logged-in user's details
    $useremail = $_SESSION["user"];
    $userrow = $database->query("SELECT * FROM client WHERE cemail='$useremail'");
    $userfetch = $userrow->fetch_assoc();
    $userid = $userfetch["cid"]; // Redundant if $_SESSION['cid'] is reliable, but kept for consistency
    $username = $userfetch["cname"]; // Redundant if $_SESSION['cname'] is reliable, but kept for consistency

    // Initialize form variables to retain values on re-display
    $reporter_name = isset($_POST['reporter_name']) ? htmlspecialchars($_POST['reporter_name']) : $username;
    $reporter_phone = isset($_POST['reporter_phone']) ? htmlspecialchars($_POST['reporter_phone']) : '';
    $reporter_email = isset($_POST['reporter_email']) ? htmlspecialchars($_POST['reporter_email']) : $useremail;
    $violation_type = isset($_POST['violation_type']) ? htmlspecialchars($_POST['violation_type']) : '';
    $incident_date = isset($_POST['incident_date']) ? htmlspecialchars($_POST['incident_date']) : '';
    $incident_time = isset($_POST['incident_time']) ? htmlspecialchars($_POST['incident_time']) : '';
    $incident_location = isset($_POST['incident_location']) ? htmlspecialchars($_POST['incident_location']) : '';
    $incident_description = isset($_POST['incident_description']) ? htmlspecialchars($_POST['incident_description']) : ''; // Renamed from description for clarity
    $legal_consultation = isset($_POST['legal_consultation']) ? htmlspecialchars($_POST['legal_consultation']) : 'No'; // Default to No
    $supplementary_notes = isset($_POST['supplementary_notes']) ? htmlspecialchars($_POST['supplementary_notes']) : '';
    $victim_name = isset($_POST['victim_name']) ? htmlspecialchars($_POST['victim_name']) : '';
    $victim_contact = isset($_POST['victim_contact']) ? htmlspecialchars($_POST['victim_contact']) : '';
    $perpetrator_name = isset($_POST['perpetrator_name']) ? htmlspecialchars($_POST['perpetrator_name']) : '';
    $consent_checked = isset($_POST['consent']) ? 'checked' : '';

    $message = ''; // For displaying success/error messages

    // Handle form submission
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $errors = [];

        // Server-side validation
        if (empty($_POST['incident_description'])) {
            $errors[] = "Description of the Incident is required.";
        }
        if (!isset($_POST['consent']) || $_POST['consent'] != 'on') {
            $errors[] = "You must agree to the consent statement.";
        }
        if (empty($_POST['violation_type'])) {
            $errors[] = "Type of Violation is required.";
        }
        if (empty($_POST['incident_date'])) {
            $errors[] = "Date of Incident is required.";
        }
        if (empty($_POST['incident_time'])) {
            $errors[] = "Time of Incident is required.";
        }
        if (empty($_POST['incident_location'])) {
            $errors[] = "Location of Incident is required.";
        }
        if (empty($_POST['perpetrator_name'])) {
            $errors[] = "Perpetrator Information is required.";
        }


        // Collect other form data and escape them for SQL insertion
        $client_id_submit = mysqli_real_escape_string($database, $clientid); // Use clientid from session
        $reporter_name_submit = mysqli_real_escape_string($database, $_POST['reporter_name']);
        $reporter_phone_submit = mysqli_real_escape_string($database, $_POST['reporter_phone']);
        $reporter_email_submit = mysqli_real_escape_string($database, $_POST['reporter_email']);
        $violation_type_submit = mysqli_real_escape_string($database, $_POST['violation_type']);
        $incident_date_submit = mysqli_real_escape_string($database, $_POST['incident_date']);
        $incident_time_submit = mysqli_real_escape_string($database, $_POST['incident_time']);
        $incident_location_submit = mysqli_real_escape_string($database, $_POST['incident_location']);
        $description_submit = mysqli_real_escape_string($database, $_POST['incident_description']); // Use the new variable name
        $victim_name_submit = mysqli_real_escape_string($database, $_POST['victim_name']);
        $victim_contact_submit = mysqli_real_escape_string($database, $_POST['victim_contact']);
        $perpetrator_name_submit = mysqli_real_escape_string($database, $_POST['perpetrator_name']);
        $legal_consultation_submit = isset($_POST['legal_consultation']) ? $database->real_escape_string($_POST['legal_consultation']) : 'No';
        $supplementary_notes_submit = $database->real_escape_string($_POST['supplementary_notes']);


        // File upload handling for single file (evidence_file)
        $evidence_file_name = ''; // This will store the unique file name saved on server

        if (!empty($_FILES['supporting_files']['name'][0])) {
            $upload_dir = '../uploads/reports/'; // Ensure this directory exists relative to your script
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0777, true); // Create directory if it doesn't exist
            }

            $name = $_FILES['supporting_files']['name'][0];
            $tmp_name = $_FILES['supporting_files']['tmp_name'][0];
            $error = $_FILES['supporting_files']['error'][0];

            if ($error == UPLOAD_ERR_OK) {
                $file_extension = pathinfo($name, PATHINFO_EXTENSION);
                $new_unique_file_name = uniqid('evidence_') . '.' . $file_extension;
                $destination_path = $upload_dir . $new_unique_file_name; // Corrected path concatenation

                if (move_uploaded_file($tmp_name, $destination_path)) {
                    $evidence_file_name = $database->real_escape_string($new_unique_file_name); // Store the unique generated name
                } else {
                    $errors[] = "Failed to upload file: " . htmlspecialchars($name);
                }
            } elseif ($error != UPLOAD_ERR_NO_FILE) {
                 $errors[] = "File upload error for " . htmlspecialchars($name) . ": Code " . $error;
            }
        }

        // If no errors, insert into database
        if (empty($errors)) {
            // Updated INSERT query for the 'reports' table, including client_id and other columns
            $insert_query = "INSERT INTO reports (
                client_id, reporter_name, reporter_phone, reporter_email, violation_type,
                incident_date, incident_time, incident_location, description,
                victim_name, victim_contact, perpetrator_name, evidence_file,
                submission_date, status, admin_notes
            ) VALUES (
                '$client_id_submit', '$reporter_name_submit', '$reporter_phone_submit', '$reporter_email_submit', '$violation_type_submit',
                '$incident_date_submit', '$incident_time_submit', '$incident_location_submit', '$description_submit',
                '$victim_name_submit', '$victim_contact_submit', '$perpetrator_name_submit', '$evidence_file_name',
                NOW(), 'pending', '$supplementary_notes_submit'
            )";

            if ($database->query($insert_query)) {
                $message = '<div id="popup1" class="overlay">
                                <div class="popup">
                                <center>
                                <br><br><br><br>
                                    <h2>Report Submitted Successfully!</h2>
                                    <a class="close" href="report.php">&times;</a>
                                    <div class="content">
                                        Your violation report has been received.
                                    </div>
                                    <div style="display: flex;justify-content: center;">
                                    <a href="report.php" class="non-style-link"><button class="btn-primary btn" style="display: flex;justify-content: center;align-items: center;margin:10px;padding:10px;"><font class="tn-in-text">&nbsp;&nbsp;OK&nbsp;&nbsp;</font></button></a>
                                    </div>
                                    <br><br>
                                </center>
                                </div>
                            </div>';
                // Clear form fields after successful submission (except pre-filled user data)
                $reporter_name = $username;
                $reporter_phone = '';
                $reporter_email = $useremail;
                $violation_type = '';
                $incident_date = '';
                $incident_time = '';
                $incident_location = '';
                $incident_description = '';
                $legal_consultation = 'No';
                $supplementary_notes = '';
                $victim_name = '';
                $victim_contact = '';
                $perpetrator_name = '';
                $consent_checked = '';

            } else {
                $message = '<div id="popup1" class="overlay">
                                <div class="popup">
                                <center>
                                <br><br><br><br>
                                    <h2>Submission Failed!</h2>
                                    <a class="close" href="report.php">&times;</a>
                                    <div class="content">
                                        There was an error submitting your report: ' . $database->error . '
                                    </div>
                                    <div style="display: flex;justify-content: center;">
                                    <a href="report.php" class="non-style-link"><button class="btn-primary btn" style="display: flex;justify-content: center;align-items: center;margin:10px;padding:10px;"><font class="tn-in-text">&nbsp;&nbsp;OK&nbsp;&nbsp;</font></button></a>
                                    </div>
                                    <br><br>
                                </center>
                                </div>
                            </div>';
            }
        } else {
            // Display validation errors
            $error_html = implode('<br>', $errors);
            $message = '<div id="popup1" class="overlay">
                            <div class="popup">
                            <center>
                            <br><br><br><br>
                                <h2>Submission Failed!</h2>
                                <a class="close" href="report.php">&times;</a>
                                <div class="content" style="color:rgb(255, 62, 62);">
                                    ' . $error_html . '
                                </div>
                                <div style="display: flex;justify-content: center;">
                                <a href="report.php" class="non-style-link"><button class="btn-primary btn" style="display: flex;justify-content: center;align-items: center;margin:10px;padding:10px;"><font class="tn-in-text">&nbsp;&nbsp;OK&nbsp;&nbsp;</font></button></a>
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
    <title>Report Violation | SafeSpace PH</title>
    <style>
        .popup{
            animation: transitionIn-Y-bottom 0.5s;
        }
        .sub-table{
            animation: transitionIn-Y-bottom 0.5s;
        }
        .form-label {
            font-weight: 500;
            color: #161c2d;
            font-size: 16px;
            margin-bottom: 5px;
            display: block;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .btn-group {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 10px;
        }
        .btn-group .btn {
            flex-grow: 1;
            padding: 10px 15px;
            border-radius: 5px;
            border: 1px solid var(--primarycolor);
            background-color: #f0e4ff; /* soft primary */
            color: var(--primarycolor);
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 14px;
        }
        .btn-group .btn.active {
            background-color: var(--primarycolor);
            color: #fff;
            box-shadow: 0 3px 5px 0 rgba(115, 1, 121, 0.374);
        }
        .btn-group .btn:hover:not(.active) {
            background-color: #e0d0ff;
        }
        .file-upload-area {
            border: 2px dashed #ccc;
            padding: 20px;
            text-align: center;
            cursor: pointer;
            border-radius: 8px;
            margin-top: 10px;
            transition: border-color 0.3s ease;
        }
        .file-upload-area:hover {
            border-color: var(--primarycolor);
        }
        .file-list {
            margin-top: 15px;
            list-style: none;
            padding: 0;
        }
        .file-list li {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 8px 10px;
            background-color: #f9f9f9;
            border: 1px solid #eee;
            border-radius: 5px;
            margin-bottom: 5px;
        }
        .file-list li .file-name {
            font-size: 14px;
            color: #333;
        }
        .file-list li .remove-file {
            background: none;
            border: none;
            color: #ff0000;
            cursor: pointer;
            font-weight: bold;
            font-size: 18px;
            line-height: 1;
            padding: 0 5px;
        }
        textarea.input-text {
            min-height: 120px;
            resize: vertical;
            padding: 10px;
        }
        .consent-checkbox-group {
            display: flex;
            align-items: flex-start;
            margin-top: 20px;
        }
        .consent-checkbox-group input[type="checkbox"] {
            margin-top: 5px;
            margin-right: 10px;
            min-width: 18px; /* Ensure checkbox is visible */
            min-height: 18px;
        }
        .consent-checkbox-group label {
            font-size: 14px;
            color: #555;
            line-height: 1.5;
            cursor: pointer;
        }

        .abc.scroll {
            padding: 20px;
            max-width: 800px; /* Set a max-width */
            margin: 0 auto; /* Center the container */
            width: 90%; /* Use percentage width for responsiveness */
        }

        .add-new-form {
            width: 100%;
            max-width: 500px; /* Set max-width for the form */
            margin: 0 auto; /* Center the form */
        }

        /* Adjust container padding for better spacing */
        .dash-body {
            padding: 20px;
        }
    </style>
</head>
<body>
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
                                    <p class="profile-title"><?php echo htmlspecialchars($clientname); ?></p>
                                    <p class="profile-subtitle"><?php echo $_SESSION['user']; ?></p>
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
                    <td class="menu-btn menu-icon-home" >
                        <a href="index.php" class="non-style-link-menu"><div><p class="menu-text">Home</p></a></div></a>
                    </td>
                </tr>
                <tr class="menu-row" >
                    <td class="menu-btn menu-icon-report menu-active menu-icon-report-active">
                        <a href="report.php" class="non-style-link-menu non-style-link-menu-active"><div><p class="menu-text menu-text-active">Report Violation</p></a></div>
                    </td>
                </tr>
                 <tr class="menu-row">
                    <td class="menu-btn menu-icon-schedule">
                        <a href="request-session.php" class="non-style-link-menu"><div><p class="menu-text">Find a Safe Space</p></div></a>
                    </td>
                </tr>
                 <tr class="menu-row">
                    <td class="menu-btn menu-icon-session">
                        <a href="client-appointment.php" class="non-style-link-menu"><div><p class="menu-text">My Appointments</p></a></div>
                    </td>
                </tr>
                <tr class="menu-row">
                    <td class="menu-btn menu-icon-lawyers">
                        <a href="lawyers.php" class="non-style-link-menu"><div><p class="menu-text">All Lawyers</p></a></div>
                    </td>
                </tr>
                <tr class="menu-row" >
                    <td class="menu-btn menu-icon-appoinment">
                        <a href="appointment.php" class="non-style-link-menu"><div><p class="menu-text">My Bookings</p></a></div>
                    </td>
                </tr>
                <tr class="menu-row" >
                    <td class="menu-btn menu-icon-settings">
                        <a href="settings.php" class="non-style-link-menu"><div><p class="menu-text">Settings</p></a></div>
                    </td>
                </tr>
            </table>
        </div>
        <div class="dash-body">
            <table border="0" width="100%" style=" border-spacing: 0;margin:0;padding:0;margin-top:25px; ">
                <tr>
                    <td width="13%">
                        <a href="index.php" ><button  class="login-btn btn-primary-soft btn btn-icon-back"  style="padding-top:11px;padding-bottom:11px;margin-left:20px;width:125px"><font class="tn-in-text">Back</font></button></a>
                    </td>
                    <td>
                        <p class="heading-main12" style="margin-left: 10px;font-size:18px;color:rgb(49, 49, 49)">Report a Violation</p>
                    </td>
                    <td width="15%">
                        <p style="font-size: 14px;color: rgb(119, 119, 119);padding: 0;margin: 0;text-align: right;">
                            Today's Date
                        </p>
                        <p class="heading-sub12" style="padding: 0;margin: 0;">
                            <?php
                                date_default_timezone_set('Asia/Manila'); // Set timezone to Philippines
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
                    <td colspan="4">
                        <center>
                            <div class="abc scroll" style="padding: 20px; max-width: 100%;">
                                <form action="" method="POST" enctype="multipart/form-data" class="add-new-form">
                                    <div class="form-group">
                                        <label for="reporter_name" class="form-label">Your Name (Optional):</label>
                                        <input type="text" name="reporter_name" class="input-text" placeholder="Enter your name" value="<?php echo $reporter_name; ?>">
                                    </div>
                                    <div class="form-group">
                                        <label for="reporter_phone" class="form-label">Your Phone Number (Optional):</label>
                                        <input type="tel" name="reporter_phone" class="input-text" placeholder="Enter your phone number" value="<?php echo $reporter_phone; ?>">
                                    </div>
                                    <div class="form-group">
                                        <label for="reporter_email" class="form-label">Your Email (Optional):</label>
                                        <input type="email" name="reporter_email" class="input-text" placeholder="Enter your email" value="<?php echo $reporter_email; ?>">
                                    </div>

                                    <div class="form-group">
                                        <label class="form-label">Type of Violation:</label>
                                        <div class="btn-group" id="violation-type-buttons">
                                            <button type="button" class="btn btn-primary-soft" data-value="Public Harassment">Public Harassment</button>
                                            <button type="button" class="btn btn-primary-soft" data-value="Online Harassment">Online Harassment</button>
                                            <button type="button" class="btn btn-primary-soft" data-value="Workplace/School Harassment">Workplace/School Harassment</button>
                                            <button type="button" class="btn btn-primary-soft" data-value="Gender-Based Sexual Harassment">Gender-Based Sexual Harassment</button>
                                            <button type="button" class="btn btn-primary-soft" data-value="Other">Other</button>
                                        </div>
                                        <input type="hidden" name="violation_type" id="violation_type" value="<?php echo $violation_type; ?>">
                                    </div>

                                    <div class="form-group">
                                        <label for="incident_date" class="form-label">Date of Incident:</label>
                                        <input type="date" name="incident_date" class="input-text" value="<?php echo $incident_date; ?>" required>
                                    </div>

                                    <div class="form-group">
                                        <label for="incident_time" class="form-label">Time of Incident:</label>
                                        <input type="time" name="incident_time" class="input-text" value="<?php echo $incident_time; ?>" required>
                                    </div>

                                    <div class="form-group">
                                        <label for="incident_location" class="form-label">Location of Incident:</label>
                                        <textarea name="incident_location" class="input-text" placeholder="Exact address, landmark, or online platform" required><?php echo $incident_location; ?></textarea>
                                    </div>

                                    <div class="form-group">
                                        <label for="incident_description" class="form-label">Description of the Incident (Required):</label>
                                        <textarea name="incident_description" class="input-text" placeholder="Provide a detailed description of the incident..."><?php echo $incident_description; ?></textarea>
                                    </div>

                                    <h3>Victim Information (if different from Reporter)</h3>
                                    <div class="form-group">
                                        <label for="victim_name" class="form-label">Victim's Full Name (Optional):</label>
                                        <input type="text" name="victim_name" class="input-text" placeholder="Victim's Name" value="<?php echo $victim_name; ?>">
                                    </div>

                                    <div class="form-group">
                                        <label for="victim_contact" class="form-label">Victim's Contact (Email/Phone - Optional):</label>
                                        <input type="text" name="victim_contact" class="input-text" placeholder="Victim's Email or Phone" value="<?php echo $victim_contact; ?>">
                                    </div>

                                    <h3>Perpetrator Information</h3>
                                    <div class="form-group">
                                        <label for="perpetrator_name" class="form-label">Perpetrator's Name or Description:</label>
                                        <input type="text" name="perpetrator_name" class="input-text" placeholder="Name, description, or 'Unknown'" value="<?php echo $perpetrator_name; ?>" required>
                                    </div>

                                    <div class="form-group">
                                        <label class="form-label">Request Legal Consultation?</label>
                                        <div class="btn-group" id="legal-consultation-buttons">
                                            <button type="button" class="btn btn-primary-soft" data-value="Yes">Yes</button>
                                            <button type="button" class="btn btn-primary-soft" data-value="No">No</button>
                                        </div>
                                        <input type="hidden" name="legal_consultation" id="legal_consultation" value="<?php echo $legal_consultation; ?>">
                                    </div>

                                    <div class="form-group">
                                        <label for="supporting_files" class="form-label">Upload Supporting Documentation (Optional - Only the first file will be saved):</label>
                                        <div class="file-upload-area" id="file-upload-area">
                                            Drag & Drop Files Here or Click to Browse
                                            <input type="file" name="supporting_files[]" id="supporting_files" multiple style="display: none;">
                                        </div>
                                        <ul class="file-list" id="file-list">
                                            </ul>
                                    </div>

                                    <div class="form-group">
                                        <label for="supplementary_notes" class="form-label">Admin Notes (Used as Supplementary Notes from Client Side):</label>
                                        <textarea name="supplementary_notes" class="input-text" placeholder="Add any additional notes or questions..."><?php echo $supplementary_notes; ?></textarea>
                                    </div>

                                    <div class="form-group consent-checkbox-group">
                                        <input type="checkbox" name="consent" id="consent" <?php echo $consent_checked; ?>>
                                        <label for="consent">I consent to SafeSpace PH using my shared information for case assessment and potential connection with pro bono legal assistance.</label>
                                    </div>

                                    <div style="display: flex; justify-content: center; gap: 20px; margin-top: 30px;">
                                        <input type="reset" value="Reset Form" class="login-btn btn-primary-soft btn">
                                        <input type="submit" value="Submit Report" class="login-btn btn-primary btn">
                                    </div>
                                </form>
                            </div>
                        </center>
                    </td>
                </tr>
            </table>
        </div>
    </div>

    <?php echo $message; // Display submission feedback ?>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Function to handle button group selection
            function setupButtonGroup(groupId, hiddenInputId) {
                const buttons = document.querySelectorAll(`#${groupId} .btn`);
                const hiddenInput = document.getElementById(hiddenInputId);

                // Set initial active state if a value is present in the hidden input
                if (hiddenInput.value) {
                    buttons.forEach(button => {
                        if (button.dataset.value === hiddenInput.value) {
                            button.classList.add('active');
                        }
                    });
                }

                buttons.forEach(button => {
                    button.addEventListener('click', function() {
                        // Remove active class from all buttons in the group
                        buttons.forEach(btn => btn.classList.remove('active'));
                        // Add active class to the clicked button
                        this.classList.add('active');
                        // Update the hidden input value
                        hiddenInput.value = this.dataset.value;
                    });
                });
            }

            // Setup for Violation Type buttons
            setupButtonGroup('violation-type-buttons', 'violation_type');

            // Setup for Legal Consultation buttons
            setupButtonGroup('legal-consultation-buttons', 'legal_consultation');


            // File Upload Mechanism
            const fileUploadArea = document.getElementById('file-upload-area');
            const fileInput = document.getElementById('supporting_files');
            const fileList = document.getElementById('file-list');

            let selectedFiles = new DataTransfer(); // Use DataTransfer to manage files dynamically

            // Trigger file input click when upload area is clicked
            fileUploadArea.addEventListener('click', () => {
                fileInput.click();
            });

            // Handle file selection
            fileInput.addEventListener('change', (event) => {
                // Clear existing files if only one is allowed by the DB schema, or handle multiple
                // For this schema, we'll only take the first file.
                selectedFiles = new DataTransfer(); // Reset DataTransfer for single file
                if (event.target.files.length > 0) {
                    selectedFiles.items.add(event.target.files[0]); // Add only the first file
                }
                updateFileList();
            });

            // Handle drag and drop
            fileUploadArea.addEventListener('dragover', (event) => {
                event.preventDefault();
                fileUploadArea.style.borderColor = 'var(--primarycolor)';
            });

            fileUploadArea.addEventListener('dragleave', (event) => {
                event.preventDefault();
                fileUploadArea.style.borderColor = '#ccc';
            });

            fileUploadArea.addEventListener('drop', (event) => {
                event.preventDefault();
                fileUploadArea.style.borderColor = '#ccc';

                // For this schema, we'll only take the first file from drop
                selectedFiles = new DataTransfer(); // Reset DataTransfer for single file
                if (event.dataTransfer.files.length > 0) {
                    selectedFiles.items.add(event.dataTransfer.files[0]); // Add only the first file
                }
                updateFileList();
            });

            // Function to update the displayed file list
            function updateFileList() {
                fileList.innerHTML = ''; // Clear current list

                if (selectedFiles.items.length === 0) {
                    fileList.style.display = 'none';
                    return;
                } else {
                    fileList.style.display = 'block';
                }

                // Display only the first file if multiple were selected/dropped (due to DB schema limitation)
                if (selectedFiles.items.length > 0) {
                    const file = selectedFiles.items[0];
                    const listItem = document.createElement('li');
                    listItem.innerHTML = `
                        <span class="file-name">${file.name}</span>
                        <button type="button" class="remove-file" data-index="0">&times;</button>
                    `;
                    fileList.appendChild(listItem);
                }

                // Re-assign the DataTransfer object's files to the file input
                // This is crucial for the form submission to include all selected files
                fileInput.files = selectedFiles.files;

                // Add event listeners for remove buttons
                document.querySelectorAll('.remove-file').forEach(button => {
                    button.addEventListener('click', function() {
                        const index = parseInt(this.dataset.index); // Will always be 0 for single file
                        removeFile(index);
                    });
                });
            }

            // Function to remove a file from the list
            function removeFile(indexToRemove) {
                // For single file, just clear the DataTransfer object
                selectedFiles = new DataTransfer();
                updateFileList();
            }

            // Initial call to update file list (in case of pre-filled form or existing files)
            updateFileList();
        });
    </script>
</body>
</html>