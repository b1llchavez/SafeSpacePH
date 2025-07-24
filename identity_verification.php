<?php
session_start(); // Start the session at the very beginning

include 'connection.php'; // Ensure this file contains a valid database connection
include 'send_email.php'; // Include the file containing the email function

// Initialize variables to store form data and errors
$errors = [];
$formData = [];
$formSubmittedSuccessfully = false;

// Retrieve session-based personal details for pre-filling
// Assuming 'personal' array exists in $_SESSION with 'fname', 'lname', 'address', 'dob', 'email', 'tele'
$fname_session = $_SESSION['personal']['fname'] ?? '';
$lname_session = $_SESSION['personal']['lname'] ?? '';
$address_session = $_SESSION['personal']['address'] ?? '';
$dob_session = $_SESSION['personal']['dob'] ?? '';
$email_session = $_SESSION['personal']['email'] ?? ''; // Using session for email as requested for pre-fill
$tele_session = $_SESSION['personal']['tele'] ?? '';   // Using session for contact number as requested for pre-fill

// Function to sanitize input data
function sanitize_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Function to validate file upload
function validate_file($file, $allowedTypes, $maxSize = 5242880) { // 5MB
    // Check for upload errors
    if ($file['error'] === UPLOAD_ERR_NO_FILE) {
        return "No file uploaded."; // Specific error for no file
    }
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return "File upload error: " . $file['error']; // More descriptive error
    }

    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, $allowedTypes)) return "Invalid file type. Only JPG, JPEG, PNG, PDF are allowed."; // Adjusted for ID files
    if ($file['size'] > $maxSize) return "File size exceeds 5MB.";
    return null; // No error
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Collect and sanitize form data from POST
    $formData['firstName'] = sanitize_input($_POST['firstName'] ?? '');
    $formData['middleName'] = sanitize_input($_POST['middleName'] ?? '');
    $formData['lastName'] = sanitize_input($_POST['lastName'] ?? '');
    $formData['suffix'] = sanitize_input($_POST['suffix'] ?? '');
    $formData['dob'] = sanitize_input($_POST['dob'] ?? '');
    $formData['sex'] = sanitize_input($_POST['sex'] ?? '');
    $formData['civilStatus'] = sanitize_input($_POST['civilStatus'] ?? '');
    $formData['citizenship'] = sanitize_input($_POST['citizenship'] ?? '');
    $formData['birthPlace'] = sanitize_input($_POST['birthPlace'] ?? '');
    $formData['presentAddress'] = sanitize_input($_POST['presentAddress'] ?? '');
    $formData['permanentAddress'] = sanitize_input($_POST['permanentAddress'] ?? '');
    $formData['email'] = sanitize_input($_POST['email'] ?? '');
    $formData['contactNumber'] = sanitize_input($_POST['contactNumber'] ?? '');
    $formData['emergencyContactName'] = sanitize_input($_POST['emergencyContactName'] ?? '');
    $formData['emergencyContactNumber'] = sanitize_input($_POST['emergencyContactNumber'] ?? '');
    $formData['emergencyContactRelationship'] = sanitize_input($_POST['emergencyContactRelationship'] ?? '');
    $formData['idType'] = sanitize_input($_POST['idType'] ?? '');
    $formData['idNumber'] = sanitize_input($_POST['idNumber'] ?? '');
    $formData['agreeTerms'] = isset($_POST['agreeTerms']) ? 1 : 0;
    
    // Middle name handling from checkbox
    if (isset($_POST['noMiddleName']) && $_POST['noMiddleName'] === 'on') {
        $formData['middleName'] = ''; // Explicitly set to empty if checkbox is checked
    }

    // Validate required fields
    if (empty($formData['firstName'])) $errors['firstName'] = "First name is required.";
    if (empty($formData['lastName'])) $errors['lastName'] = "Last name is required.";
    if (empty($formData['dob'])) $errors['dob'] = "Date of birth is required.";
    // Validate age (example: at least 18 years old)
    if (!empty($formData['dob'])) {
        $birthDate = new DateTime($formData['dob']);
        $today = new DateTime();
        $age = $today->diff($birthDate)->y;
        if ($age < 18) {
            $errors['dob'] = "You must be at least 18 years old.";
        }
    }
    if (empty($formData['sex'])) $errors['sex'] = "Sex is required.";
    if (empty($formData['civilStatus'])) $errors['civilStatus'] = "Civil Status is required.";
    if (empty($formData['citizenship'])) $errors['citizenship'] = "Citizenship is required.";
    if (empty($formData['birthPlace'])) $errors['birthPlace'] = "Place of Birth is required.";
    if (empty($formData['presentAddress'])) $errors['presentAddress'] = "Present Address is required.";
    if (empty($formData['permanentAddress'])) $errors['permanentAddress'] = "Permanent Address is required.";
    if (empty($formData['email'])) {
        $errors['email'] = "Email is required.";
    } elseif (!filter_var($formData['email'], FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = "Invalid email format.";
    }
    if (empty($formData['contactNumber'])) $errors['contactNumber'] = "Contact number is required.";
    if (empty($formData['emergencyContactName'])) $errors['emergencyContactName'] = "Emergency contact name is required.";
    if (empty($formData['emergencyContactNumber'])) $errors['emergencyContactNumber'] = "Emergency contact number is required.";
    if (empty($formData['emergencyContactRelationship'])) $errors['emergencyContactRelationship'] = "Emergency contact relationship is required.";
    if (empty($formData['idType'])) $errors['idType'] = "ID type is required.";
    if (empty($formData['idNumber'])) $errors['idNumber'] = "ID number is required.";
    if ($formData['agreeTerms'] == 0) $errors['agreeTerms'] = "You must agree to the terms and conditions.";


    // Validate file uploads
    $allowIdTypes = ['jpg', 'jpeg', 'png', 'pdf'];
    $idPhotoFrontError = validate_file($_FILES['idPhotoFront'], $allowIdTypes);
    $idPhotoBackError = validate_file($_FILES['idPhotoBack'], $allowIdTypes);
    
    // New: Validate profile photo
    $allowProfilePhotoTypes = ['jpg', 'jpeg', 'png'];
    $profilePhotoError = validate_file($_FILES['profilePhoto'], $allowProfilePhotoTypes);


    if ($idPhotoFrontError) {
        $errors['idPhotoFront'] = $idPhotoFrontError;
    }
    if ($idPhotoBackError) {
        $errors['idPhotoBack'] = $idPhotoBackError;
    }
    // New: Add profile photo error
    if ($profilePhotoError) {
        $errors['profilePhoto'] = $profilePhotoError;
    }


    // Check if both files are selected and are the same (added server-side check)
    if (empty($errors['idPhotoFront']) && empty($errors['idPhotoBack']) &&
        isset($_FILES["idPhotoFront"]) && $_FILES["idPhotoFront"]["error"] == 0 &&
        isset($_FILES["idPhotoBack"]) && $_FILES["idPhotoBack"]["error"] == 0) {
        if ($_FILES["idPhotoFront"]["name"] === $_FILES["idPhotoBack"]["name"] &&
            $_FILES["idPhotoFront"]["size"] === $_FILES["idPhotoBack"]["size"] &&
            $_FILES["idPhotoFront"]["tmp_name"] === $_FILES["idPhotoFront"]["tmp_name"]) { // Compare temporary file paths
            $errors['idPhotoFront'] = 'Front and back ID images must be different files.';
            $errors['idPhotoBack'] = 'Front and back ID images must be different files.';
        }
    }


    // Handle file uploads
    $idPhotoFrontPath = null;
    $idPhotoBackPath = null;
    $profilePhotoPath = null; // New: Profile photo path
    
    $id_front_dir = "uploads/id_front/";
    $id_back_dir = "uploads/id_back/";
    $profile_photo_dir = "uploads/profile_photo_client/"; // New: Profile photo directory

    // Create directories if they don't exist
    if (!is_dir($id_front_dir) && !mkdir($id_front_dir, 0777, true)) {
        $errors['idPhotoFront'] = "Failed to create upload directory for front ID.";
    }
    if (!is_dir($id_back_dir) && !mkdir($id_back_dir, 0777, true)) {
        $errors['idPhotoBack'] = "Failed to create upload directory for back ID.";
    }
    // New: Create directory for profile photos
    if (!is_dir($profile_photo_dir) && !mkdir($profile_photo_dir, 0777, true)) {
        $errors['profilePhoto'] = "Failed to create upload directory for profile photo.";
    }


    if (empty($errors['idPhotoFront']) && isset($_FILES["idPhotoFront"]) && $_FILES["idPhotoFront"]["error"] == 0) {
        $fileName = basename($_FILES["idPhotoFront"]["name"]);
        $targetFilePath = $id_front_dir . uniqid('front_') . "_" . $fileName;
        if (move_uploaded_file($_FILES["idPhotoFront"]["tmp_name"], $targetFilePath)) {
            $idPhotoFrontPath = $targetFilePath;
        } else {
            $errors['idPhotoFront'] = "Error uploading front ID photo.";
        }
    }

    if (empty($errors['idPhotoBack']) && isset($_FILES["idPhotoBack"]) && $_FILES["idPhotoBack"]["error"] == 0) {
        $fileName = basename($_FILES["idPhotoBack"]["name"]);
        $targetFilePath = $id_back_dir . uniqid('back_') . "_" . $fileName;
        if (move_uploaded_file($_FILES["idPhotoBack"]["tmp_name"], $targetFilePath)) {
            $idPhotoBackPath = $targetFilePath;
        } else {
            $errors['idPhotoBack'] = "Error uploading back ID photo.";
        }
    }

    // New: Handle profile photo upload
    if (empty($errors['profilePhoto']) && isset($_FILES["profilePhoto"]) && $_FILES["profilePhoto"]["error"] == 0) {
        $fileName = basename($_FILES["profilePhoto"]["name"]);
        $targetFilePath = $profile_photo_dir . uniqid('profile_') . "_" . $fileName;
        if (move_uploaded_file($_FILES["profilePhoto"]["tmp_name"], $targetFilePath)) {
            $profilePhotoPath = $targetFilePath;
        } else {
            $errors['profilePhoto'] = "Error uploading profile photo.";
        }
    }


    // If no errors, insert data into the database
    if (empty($errors)) {
        // Check if $database is set and is a valid mysqli object
        if (isset($database) && $database instanceof mysqli) {
            // New: Added profile_photo_path to the insert query
            $stmt = $database->prepare("INSERT INTO identity_verifications (
                first_name, middle_name, last_name, suffix, dob, sex, civil_status, citizenship, birth_place,
                present_address, permanent_address, email, contact_number, emergency_contact_name,
                emergency_contact_number, emergency_contact_relationship, id_type, id_number,
                id_photo_front_path, id_photo_back_path, profile_photo_path, agree_terms
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

            if ($stmt) {
                // New: Added 's' for profile_photo_path (string)
                $stmt->bind_param("sssssssssssssssssssssi",
                    $formData['firstName'], $formData['middleName'], $formData['lastName'], $formData['suffix'], $formData['dob'],
                    $formData['sex'], $formData['civilStatus'], $formData['citizenship'], $formData['birthPlace'],
                    $formData['presentAddress'], $formData['permanentAddress'], $formData['email'], $formData['contactNumber'],
                    $formData['emergencyContactName'], $formData['emergencyContactNumber'], $formData['emergencyContactRelationship'],
                    $formData['idType'], $formData['idNumber'], $idPhotoFrontPath, $idPhotoBackPath, $profilePhotoPath, $formData['agreeTerms'] // Pass profilePhotoPath
                );

                if ($stmt->execute()) {
                    $formSubmittedSuccessfully = true;
                    // Send welcome email
                    $recipientEmail = $formData['email'];
                    $recipientName = $formData['firstName'] . ' ' . $formData['lastName'];
                    // Send verification notice email
                    sendVerificationNoticeToClient($recipientEmail, $recipientName);
                    // $formData = []; // Optionally clear form data after successful submission
                } else {
                    $errors['db'] = "Database error: " . $stmt->error;
                }
                $stmt->close();
            } else {
                $errors['db'] = "Statement preparation failed: " . $database->error;
            }
        } else {
            $errors['db'] = "Database connection not established.";
        }
    }
} else {
    // If not a POST request, pre-fill formData with session data
    $formData['firstName'] = $fname_session;
    $formData['lastName'] = $lname_session;
    $formData['permanentAddress'] = $address_session;
    $formData['dob'] = $dob_session;
    $formData['email'] = $email_session;
    $formData['contactNumber'] = $tele_session;

    // For other fields, initialize them as empty if not already set by a previous POST with errors
    $formData['middleName'] = $formData['middleName'] ?? '';
    $formData['suffix'] = $formData['suffix'] ?? '';
    $formData['sex'] = $formData['sex'] ?? '';
    $formData['civilStatus'] = $formData['civilStatus'] ?? '';
    $formData['citizenship'] = $formData['citizenship'] ?? '';
    $formData['birthPlace'] = $formData['birthPlace'] ?? '';
    $formData['presentAddress'] = $formData['presentAddress'] ?? ''; // This will be handled by JS for "same as permanent"
    $formData['emergencyContactName'] = $formData['emergencyContactName'] ?? '';
    $formData['emergencyContactNumber'] = $formData['emergencyContactNumber'] ?? '';
    $formData['emergencyContactRelationship'] = $formData['emergencyContactRelationship'] ?? '';
    $formData['idType'] = $formData['idType'] ?? '';
    $formData['idNumber'] = $formData['idNumber'] ?? '';
    $formData['agreeTerms'] = $formData['agreeTerms'] ?? 0;
}

// Close the database connection if it's open
if (isset($database) && $database) {
    $database->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Identity Verification | SafeSpace PH</title>
    <link rel="icon" type="image/png" href="https://i.ibb.co/qYYZs46/logo.png">
    <link rel="icon" type="image/png" href="https://i.ibb.co/qYYZs46L/logo.png">

    <style>
        /* General Body and Modal Styling - Matches volunteer_form.php */
        body {
            background: rgba(0, 0, 0, 0.15);
            min-height: 100vh;
            margin: 0;
            font-family: 'Inter', Arial, sans-serif;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .modal {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            width: 100vw; /* Ensure modal takes full width on small screens */
        }

        .modal-content {
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.15);
            padding: 40px 64px;
            max-width: 800px;
            width: 100%; /* Make it fluid */
            position: relative;
            animation: fadeIn 0.3s;
            box-sizing: border-box; /* Include padding in width */
        }

        .form-title {
            text-align: center;
            color: #391053;
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 0;
            margin-top: 0;
            letter-spacing: 0.5px;
        }

        .form-divider {
            width: 100%;
            height: 4px;
            background: linear-gradient(90deg, #391053 0%, #5A2675 30%, #9D72B3 65%, #C9A8F1 100%);
            border: none;
            border-radius: 2px;
            margin: 18px 0 32px 0;
        }

        .section-title {
            font-size: 1.18rem;
            color: #391053;
            font-weight: 600;
            margin: 32px 0 14px 0;
            letter-spacing: 0.2px;
            padding-bottom: 4px;
            border-bottom: 1px solid #e2d8fa;
        }

        .section-title:first-of-type {
            margin-top: 0;
        }

        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 28px 32px;
            margin-bottom: 18px;
        }

        .form-group {
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            margin-bottom: 0;
            position: relative;
            gap: 2px;
        }

        .form-group:not(:last-child) {
            margin-bottom: 18px;
        }

        .form-group.full-width {
            grid-column: 1 / 3;
        }

        label {
            font-size: 1rem;
            font-weight: 500;
            color: #391053;
            margin-bottom: 4px;
            letter-spacing: 0.03em;
        }

        input[type="text"],
        input[type="email"],
        input[type="number"],
        input[type="date"],
        textarea,
        select {
            font-size: 1rem;
            color: #2d1f44;
            background: #fcfbff;
            border: 1.5px solid #ccc;
            border-radius: 5px;
            padding: 10px 12px;
            margin-bottom: 0;
            width: 100%;
            box-sizing: border-box;
        }

        /* File input styling */
        input[type="file"] {
            font-size: 1rem;
            color: #2d1f44;
            margin-top: 4px;
            padding: 8px 0;
            border: none;
            background: none;
        }

        input[type="file"]::-webkit-file-upload-button,
        input[type="file"]::file-selector-button {
            background: #5A2675;
            color: #fff;
            border: none;
            border-radius: 5px;
            padding: 8px 22px;
            font-size: 15px;
            font-weight: 500;
            cursor: pointer;
            transition: background 0.2s;
            margin-right: 10px;
        }

        input[type="file"]:hover::-webkit-file-upload-button,
        input[type="file"]:hover::file-selector-button {
            background: #391053;
        }

        textarea {
            resize: vertical;
        }

        .checkbox-group {
            margin-bottom: 16px;
            padding: 12px 16px;
            background: #f7f4fd;
            border-radius: 5px;
            border: 1px solid #e2d8fa;
            display: flex;
            align-items: flex-start;
        }

        .checkbox-group label {
            font-size: 1rem;
            color: #391053;
            font-weight: 500;
            margin-bottom: 0;
        }

        .checkbox-group input[type="checkbox"] {
            margin-right: 10px;
            margin-top: 3px;
            accent-color: #5A2675;
            width: 18px;
            height: 18px;
            flex-shrink: 0;
        }

        /* Focus styles for inputs and selects */
        input[type="text"]:focus,
        input[type="email"]:focus,
        input[type="number"]:focus,
        input[type="date"]:focus,
        textarea:focus,
        select:focus {
            border-color: #5A2675 !important;
            box-shadow: 0 0 0 3px rgba(90, 38, 117, 0.2); /* Added subtle shadow on focus */
            outline: none;
        }

        /* Select dropdown styling */
        select {
            /* Custom arrow for select */
            background: #5A2675 url('data:image/svg+xml;utf8,<svg fill="white" height="18" viewBox="0 0 24 24" width="18" xmlns="http://www.w3.org/2000/svg"><path d="M7 10l5 5 5-5z"/></svg>') no-repeat right 16px center/18px 18px;
            color: #fff;
            border: none;
            border-radius: 5px;
            padding: 10px 38px 10px 12px; /* Adjusted padding for better text alignment */
            font-size: 15px;
            font-weight: 500;
            outline: none;
            transition: background 0.2s;
            appearance: none; /* Hide default arrow */
            -webkit-appearance: none; /* Hide default arrow for webkit browsers */
            -moz-appearance: none; /* Hide default arrow for mozilla browsers */
            cursor: pointer;
            box-shadow: none;
        }

        select:hover {
            background-color: #391053;
            color: #fff;
        }

        /* Checkbox styling */
        input[type="checkbox"] {
            accent-color: #5A2675;
            width: 18px;
            height: 18px;
            border-radius: 4px;
            border: 2px solid #5A2675;
            transition: border-color 0.2s, background 0.2s;
            cursor: pointer;
        }

        input[type="checkbox"]:checked {
            background-color: #5A2675;
            border-color: #5A2675;
        }

        /* Navigation buttons */
        .form-navigation {
            display: flex;
            justify-content: flex-end; /* Align buttons to the right */
            margin-top: 30px;
            gap: 12px;
        }

        .form-navigation button {
            background: #5A2675;
            color: #fff;
            border: none;
            border-radius: 5px;
            padding: 10px 28px;
            font-size: 16px;
            cursor: pointer;
            transition: background 0.2s, box-shadow 0.2s;
            box-shadow: 0 2px 8px rgba(157, 114, 179, 0.12);
        }

        .form-navigation button:hover {
            background: #391053;
            box-shadow: 0 4px 16px rgba(157, 114, 179, 0.18);
        }

        .form-navigation button:disabled {
            background: #ccc;
            cursor: not-allowed;
            box-shadow: none;
        }

        .form-navigation .back-btn {
            background: #b48be3; /* Lighter purple for back button */
        }

        .form-navigation .back-btn:hover {
            background: #9d72b3; /* Darker shade on hover */
        }

        /* Confirmation message styling */
        .confirmation-box {
            padding: 40px 64px;
            max-width: 800px;
            width: 100%;
            margin: 0 auto;
            text-align: center;
            animation: fadeIn 0.3s;
            box-sizing: border-box;
        }

        .confirmation-check {
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 18px;
        }

        .confirmation-check svg {
            width: 70px;
            height: 70px;
            display: block;
        }

        .confirmation-box h2 {
            color: #391053;
            font-size: 2.1rem;
            font-weight: 800;
            margin-bottom: 16px;
            margin-top: 0;
            letter-spacing: 0.5px;
            line-height: 1.2;
        }

        .confirmation-box p {
            font-size: 1.13rem;
            color: #2d1f44;
            margin: 12px 0 22px 0;
            font-weight: 500;
            line-height: 1.6;
        }

        .popup-notification {
            background: #f7f4fd;
            border-left: 5px solid #5A2675;
            padding: 18px 20px;
            margin: 26px 0;
            border-radius: 7px;
            text-align: left;
            font-size: 1.01rem;
            color: #3a2c5c;
            font-weight: 500;
            line-height: 1.7;
            box-shadow: none;
            border-radius: 7px;
        }

        .popup-notification strong {
            color: #391053;
            font-weight: 700;
        }

        .go-back-button {
            display: inline-flex; /* Use flex to align icon and text */
            align-items: center;
            background: #b48be3; /* Lighter purple for back button */
            color: #fff;
            padding: 10px 20px;
            border-radius: 7px;
            text-decoration: none;
            font-size: 1rem;
            font-weight: 600;
            margin-top: 0; /* Remove top margin as it's at the very top */
            transition: background 0.2s, box-shadow 0.2s;
            border: none;
            cursor: pointer;
            box-shadow: 0 2px 8px rgba(157, 114, 179, 0.12);
            letter-spacing: 0.2px;
        }

        .go-back-button:hover {
            background: #9d72b3; /* Darker shade on hover */
            box-shadow: 0 4px 16px rgba(157, 114, 179, 0.18);
        }

        /* Responsive adjustments */
        @media (max-width: 800px) {
            .modal-content,
            .confirmation-box {
                padding: 24px 8vw;
                max-width: 98vw;
            }

            .form-grid {
                grid-template-columns: 1fr;
                gap: 18px 0;
            }

            .form-group.full-width {
                grid-column: 1 / 2;
            }
        }

        @media (max-width: 700px) {
            .confirmation-box {
                padding: 28px 6vw 24px 6vw;
                max-width: 98vw;
            }

            .confirmation-check svg {
                width: 50px;
                height: 50px;
            }
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-30px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* Hide sections */
        .form-section {
            display: none;
            padding-bottom: 32px; /* Add vertical space at bottom of each section */
            margin-bottom: 24px;  /* Add space between sections */
        }
        .form-section.active {
            display: block;
        }

        .form-group {
            margin-bottom: 22px; /* More vertical space between fields */
        }

        .form-navigation {
            margin-top: 36px; /* More space above navigation buttons */
            margin-bottom: 8px; /* Space below navigation buttons */
        }

        .form-navigation button {
            margin-right: 10px; /* Space between buttons */
        }
        .form-navigation button:last-child {
            margin-right: 0;
        }

        /* Summary section specific styles */
        .summary-item {
            margin-bottom: 10px;
            padding: 8px 0;
            border-bottom: 1px dashed #eee;
            display: flex; /* Use flexbox for better alignment of key-value pairs */
            align-items: flex-start;
        }
        .summary-item:last-child {
            border-bottom: none;
        }
        .summary-item strong {
            color: #391053;
            display: inline-block;
            min-width: 160px; /* Align labels, slightly increased for more space */
            flex-shrink: 0; /* Prevent shrinking */
            margin-right: 10px; /* Space between label and value */
        }
        .summary-item span {
            color: #2d1f44;
            flex-grow: 1; /* Allow value to take remaining space */
            word-break: break-word; /* Ensure long text wraps */
        }
        .summary-item ul {
            list-style: none;
            padding: 0;
            margin: 0;
            display: inline-block;
            vertical-align: top;
        }
        .summary-item ul li {
            margin-bottom: 5px;
        }
        .summary-item ul li:last-child {
            margin-bottom: 0;
        }
        .summary-file-preview {
            max-width: 120px; /* Slightly larger preview */
            max-height: 120px;
            margin-top: 5px;
            display: block;
            object-fit: contain; /* Ensure image fits without distortion */
            border: 1px solid #e2d8fa; /* Softer border */
            border-radius: 5px;
            padding: 5px;
            background-color: #fcfbff; /* Light background for preview */
        }
        /* Error message styling */
        .error-message {
            color: #d9534f;
            font-size: 0.93rem;
            margin-top: 2px;
            margin-bottom: 0;
            display: none;
            position: static;
            width: 100%;
            text-align: left;
            white-space: normal;
            overflow: visible;
            text-overflow: unset;
        }
        /* Input invalid state */
        input.input-invalid, select.input-invalid, textarea.input-invalid {
            border-color: #d9534f !important; /* Red border for invalid inputs */
            box-shadow: 0 0 0 2px rgba(217, 83, 79, 0.2); /* Red shadow for invalid inputs */
        }
        /* Radio button/checkbox alignment fix */
        .radio-group-container {
            display: flex;
            gap: 20px;
            margin-top: 8px;
            flex-wrap: wrap; /* Allow wrapping on small screens */
            align-items: center; /* Vertically align items in the flex container */
        }
        .radio-group-container label {
            display: flex;
            align-items: center; /* Align checkbox/radio with text */
            font-weight: normal;
            margin-bottom: 0;
            cursor: pointer;
        }
        .radio-group-container input[type="radio"] {
            width: auto;
            margin-right: 8px;
            accent-color: #5A2675;
            flex-shrink: 0; /* Prevent shrinking */
        }

        /* New styles for read-only inputs */
        input[readonly], textarea[readonly], select[readonly] {
            background-color: #e9ecef; /* Light gray background */
            cursor: not-allowed; /* Indicate not editable */
            opacity: 0.8; /* Slightly dim it */
        }
    </style>
</head>
<body>
    <div class="modal">
        <div class="modal-content" id="registration-form-container">
            <?php if ($formSubmittedSuccessfully): ?>
                <div class="confirmation-box">
                    <div class="confirmation-check">
                        <svg viewBox="0 0 80 80" fill="none" width="70" height="70">
                            <circle cx="40" cy="40" r="38" fill="#f7f4fd" stroke="#391053" stroke-width="4"/>
                            <path d="M25 43.5L37.5 56L56 28" stroke="#391053" stroke-width="6" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </div>
                    <h2>Registration Submitted!</h2>
                    <p>
                        Thank you for registering with SafeSpace PH.<br>
                        Your identity verification is in progress.<br>
                        <span style="color:#391053;font-weight:600;">We appreciate your patience!</span>
                    </p>
                    <div class="popup-notification">
                        <strong>What happens next?</strong><br>
                        Our team will review your information and contact you within
                        <strong>
                            <span style="vertical-align:middle;display:inline-flex;align-items:center;">
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" style="margin-right:4px;vertical-align:middle;" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="10" stroke="#391053" stroke-width="2"/><path d="M12 7v5l3 3" stroke="#6a0dad" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                24 hours
                            </span>
                        </strong>
                        for verification.<br>
                        Please ensure you can access the email address and phone number you provided.<br>
                        <br>
                        <span style="color:#391053;">If you have questions, email us at <strong>safespaceph2025@gmail.com</strong>.</span>
                    </div>
                    <a href="index.html" class="go-back-button">Go back</a>
                </div>
            <?php else: ?>
                <div class="back-button-container" id="globalBackButtonContainer" style="text-align: left; margin-bottom: 20px;">
                    <button type="button" class="go-back-button" onclick="window.history.back();">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align: middle; margin-right: 5px;">
                            <line x1="19" y1="12" x2="5" y2="12"></line>
                            <polyline points="12 19 5 12 12 5"></polyline>
                        </svg>
                        Back
                    </button>
                </div>
                <h2 class="form-title" id="form-title">Identity Verification</h2>
                <div class="form-divider"></div>

                <form id="registrationForm" method="POST" action="identity_verification.php" enctype="multipart/form-data" novalidate>
                    <div class="form-section active" id="section1">
                        <h3 class="section-title">Personal Information</h3>
                        <div class="form-grid">
                            <div class="form-group">
                                <label for="firstName">First Name <span style="color: red;">*</span></label>
                                <input type="text" id="firstName" name="firstName" value="<?php echo htmlspecialchars($formData['firstName'] ?? ''); ?>" required readonly class="readonly-input">
                                <span class="error-message" id="firstName-error"></span>
                            </div>
                            <div class="form-group">
                                <label for="middleName">Middle Name</label>
                                <input type="text" id="middleName" name="middleName" value="<?php echo htmlspecialchars($formData['middleName'] ?? ''); ?>">
                                <div class="checkbox-group" style="margin-top: 10px; margin-bottom: 0;">
                                    <label>
                                        <input type="checkbox" id="noMiddleName" name="noMiddleName" <?php echo (isset($formData['middleName']) && $formData['middleName'] === '') ? 'checked' : ''; ?>>
                                        I have no legal middle name
                                    </label>
                                </div>
                                <span class="error-message" id="middleName-error"></span>
                            </div>
                            <div class="form-group">
                                <label for="lastName">Last Name <span style="color: red;">*</span></label>
                                <input type="text" id="lastName" name="lastName" value="<?php echo htmlspecialchars($formData['lastName'] ?? ''); ?>" required readonly class="readonly-input">
                                <span class="error-message" id="lastName-error"></span>
                            </div>
                            <div class="form-group">
                                <label for="suffix">Suffix</label>
                                <input type="text" id="suffix" name="suffix" value="<?php echo htmlspecialchars($formData['suffix'] ?? ''); ?>">
                            </div>
                            <div class="form-group">
                                <label for="dob">Date of Birth <span style="color: red;">*</span></label>
                                <input type="date" id="dob" name="dob" value="<?php echo htmlspecialchars($formData['dob'] ?? ''); ?>" required readonly class="readonly-input">
                                <span class="error-message" id="dob-error"></span>
                            </div>
                            <div class="form-group">
                                <label>Sex <span style="color: red;">*</span></label>
                                <div class="radio-group-container">
                                    <label><input type="radio" name="sex" value="Male" <?php echo (isset($formData['sex']) && $formData['sex'] == 'Male') ? 'checked' : ''; ?> required> Male</label>
                                    <label><input type="radio" name="sex" value="Female" <?php echo (isset($formData['sex']) && $formData['sex'] == 'Female') ? 'checked' : ''; ?>> Female</label>
                                    <label><input type="radio" name="sex" value="Prefer not to say" <?php echo (isset($formData['sex']) && $formData['sex'] == 'Prefer not to say') ? 'checked' : ''; ?>> Prefer not to say</label>
                                </div>
                                <span class="error-message" id="sex-error"></span>
                            </div>
                            <div class="form-group">
                                <label for="civilStatus">Civil Status <span style="color: red;">*</span></label>
                                <select id="civilStatus" name="civilStatus" required>
                                    <option value="">Select Civil Status</option>
                                    <option value="Single" <?php echo (isset($formData['civilStatus']) && $formData['civilStatus'] == 'Single') ? 'selected' : ''; ?>>Single</option>
                                    <option value="Married" <?php echo (isset($formData['civilStatus']) && $formData['civilStatus'] == 'Married') ? 'selected' : ''; ?>>Married</option>
                                    <option value="Divorced" <?php echo (isset($formData['civilStatus']) && $formData['civilStatus'] == 'Divorced') ? 'selected' : ''; ?>>Divorced</option>
                                    <option value="Widowed" <?php echo (isset($formData['civilStatus']) && $formData['civilStatus'] == 'Widowed') ? 'selected' : ''; ?>>Widowed</option>
                                </select>
                                <span class="error-message" id="civilStatus-error"></span>
                            </div>
                            <div class="form-group">
                                <label for="citizenship">Citizenship <span style="color: red;">*</span></label>
                                <input type="text" id="citizenship" name="citizenship" value="<?php echo htmlspecialchars($formData['citizenship'] ?? ''); ?>" required>
                                <span class="error-message" id="citizenship-error"></span>
                            </div>
                            <div class="form-group full-width">
                                <label for="birthPlace">Place of Birth <span style="color: red;">*</span></label>
                                <input type="text" id="birthPlace" name="birthPlace" value="<?php echo htmlspecialchars($formData['birthPlace'] ?? ''); ?>" required>
                                <span class="error-message" id="birthPlace-error"></span>
                            </div>
                            <div class="form-group full-width">
                                <label for="profilePhoto">Upload Profile Photo <span style="color: red;">*</span></label>
                                <input type="file" id="profilePhoto" name="profilePhoto" accept="image/png,image/jpeg" required>
                                <p style="font-size:0.85em; color:#555; margin-top:5px;">
                                    **Requirement:** Formal picture with plain background (white or blue). Accepts JPG/PNG only.
                                </p>
                                <span class="error-message" id="profilePhoto-error"></span>
                            </div>
                        </div>
                        <div class="form-navigation">
                            <button type="button" id="next1" class="next-btn">Next</button>
                        </div>
                    </div>

                    <div class="form-section" id="section2">
                        <h3 class="section-title">Contact Information</h3>
                        <div class="form-grid">
                            <div class="form-group full-width">
                                <label for="permanentAddress">Permanent Address <span style="color: red;">*</span></label>
                                <textarea id="permanentAddress" name="permanentAddress" rows="3" required readonly class="readonly-input"><?php echo htmlspecialchars($formData['permanentAddress'] ?? ''); ?></textarea>
                                <span class="error-message" id="permanentAddress-error"></span>
                            </div>
                            <div class="form-group full-width">
                                <label for="presentAddress">Present Address <span style="color: red;">*</span></label>
                                <div class="checkbox-group" style="margin-bottom: 10px; margin-top: 5px;">
                                    <label>
                                        <input type="checkbox" id="sameAsPermanentAddress">
                                        Same as permanent address
                                    </label>
                                </div>
                                <textarea id="presentAddress" name="presentAddress" rows="3" required><?php echo htmlspecialchars($formData['presentAddress'] ?? ''); ?></textarea>
                                <span class="error-message" id="presentAddress-error"></span>
                            </div>
                            <div class="form-group">
                                <label for="email">Email Address <span style="color: red;">*</span></label>
                                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($formData['email'] ?? ''); ?>" required readonly class="readonly-input">
                                <span class="error-message" id="email-error"></span>
                            </div>
                            <div class="form-group">
                                <label for="contactNumber">Contact Number <span style="color: red;">*</span></label>
                                <input type="text" id="contactNumber" name="contactNumber" value="<?php echo htmlspecialchars($formData['contactNumber'] ?? ''); ?>" required readonly class="readonly-input">
                                <span class="error-message" id="contactNumber-error"></span>
                            </div>
                        </div>

                        <h3 class="section-title">Emergency Contact Information</h3>
                        <div class="form-grid">
                            <div class="form-group">
                                <label for="emergencyContactName">Full Name <span style="color: red;">*</span></label>
                                <input type="text" id="emergencyContactName" name="emergencyContactName" value="<?php echo htmlspecialchars($formData['emergencyContactName'] ?? ''); ?>" required>
                                <span class="error-message" id="emergencyContactName-error"></span>
                            </div>
                            <div class="form-group">
                                <label for="emergencyContactNumber">Contact Number <span style="color: red;">*</span></label>
                                <input type="text" id="emergencyContactNumber" name="emergencyContactNumber" value="<?php echo htmlspecialchars($formData['emergencyContactNumber'] ?? ''); ?>" required>
                                <span class="error-message" id="emergencyContactNumber-error"></span>
                            </div>
                            <div class="form-group full-width">
                                <label for="emergencyContactRelationship">Relationship <span style="color: red;">*</span></label>
                                <input type="text" id="emergencyContactRelationship" name="emergencyContactRelationship" value="<?php echo htmlspecialchars($formData['emergencyContactRelationship'] ?? ''); ?>" required>
                                <span class="error-message" id="emergencyContactRelationship-error"></span>
                            </div>
                        </div>
                        <div class="form-navigation">
                            <button type="button" id="back2" class="back-btn">Back</button>
                            <button type="button" id="next2" class="next-btn">Next</button>
                        </div>
                    </div>

                    <div class="form-section" id="section3">
                        <h3 class="section-title">Identity Verification</h3>
                        <div class="form-grid">
                            <div class="form-group">
                                <label for="idType">Type of ID <span style="color: red;">*</span></label>
                                <select id="idType" name="idType" required onchange="document.getElementById('customIdNameGroup').style.display = this.value === 'Other Valid ID' ? 'block' : 'none';">
                                    <option value="">Select ID Type</option>
                                    <option value="Passport" <?php echo (isset($formData['idType']) && $formData['idType'] == 'Passport') ? 'selected' : ''; ?>>Passport</option>
                                    <option value="Driver License" <?php echo (isset($formData['idType']) && $formData['idType'] == "Driver's License") ? 'selected' : ''; ?>>Driver's License</option>
                                    <option value="UMID" <?php echo (isset($formData['idType']) && $formData['idType'] == 'UMID') ? 'selected' : ''; ?>>UMID</option>
                                    <option value="PhilHealth ID" <?php echo (isset($formData['idType']) && $formData['idType'] == 'PhilHealth ID') ? 'selected' : ''; ?>>PhilHealth ID</option>
                                    <option value="Postal ID" <?php echo (isset($formData['idType']) && $formData['idType'] == 'Postal ID') ? 'selected' : ''; ?>>Postal ID</option>
                                    <option value="Voter ID" <?php echo (isset($formData['idType']) && $formData['idType'] == "Voter's ID") ? 'selected' : ''; ?>>Voter's ID</option>
                                    <option value="PRC ID" <?php echo (isset($formData['idType']) && $formData['idType'] == 'PRC ID') ? 'selected' : ''; ?>>PRC ID</option>
                                    <option value="TIN ID" <?php echo (isset($formData['idType']) && $formData['idType'] == 'TIN ID') ? 'selected' : ''; ?>>TIN ID</option>
                                    <option value="Other Valid ID" <?php echo (isset($formData['idType']) && $formData['idType'] == 'Other Valid ID') ? 'selected' : ''; ?>>Other Valid ID</option>
                                </select>
                                <span class="error-message" id="idType-error"></span>
                            </div>
                            <div class="form-group">
                                <label for="idNumber">ID Number <span style="color: red;">*</span></label>
                                <input type="text" id="idNumber" name="idNumber" value="<?php echo htmlspecialchars($formData['idNumber'] ?? ''); ?>" required>
                                <span class="error-message" id="idNumber-error"></span>
                            </div>
                            <div class="form-group">
                                <label for="idPhotoFront">Upload Front of ID <span style="color: red;">*</span></label>
                                <input type="file" id="idPhotoFront" name="idPhotoFront" accept="image/*,application/pdf" required>
                                <span class="error-message" id="idPhotoFront-error"></span>
                            </div>
                            <div class="form-group">
                                <label for="idPhotoBack">Upload Back of ID <span style="color: red;">*</span></label>
                                <input type="file" id="idPhotoBack" name="idPhotoBack" accept="image/*,application/pdf" required>
                                <span class="error-message" id="idPhotoBack-error"></span>
                            </div>
                        </div>

                        <div class="checkbox-group full-width">
                            <input type="checkbox" id="agreeTerms" name="agreeTerms" <?php echo (isset($formData['agreeTerms']) && $formData['agreeTerms'] == 1) ? 'checked' : ''; ?> required>
                            <label for="agreeTerms">I agree to the <a href="#" style="color: #5A2675; text-decoration: underline;">Terms and Conditions</a> and authorize SafeSpace PH to verify my identity using the provided information.</label>
                            <span class="error-message" id="agreeTerms-error"></span>
                        </div>

                        <div class="form-navigation">
                            <button type="button" id="back3" class="back-btn">Back</button>
                            <button type="button" id="next3" class="next-btn">Next</button>
                        </div>
                    </div>

                    <div class="form-section" id="section4">
                        <h3 class="section-title">Summary of Information</h3>
                        <div class="summary-content">
                            </div>
                        <div class="form-navigation">
                            <button type="button" id="back4" class="back-btn">Back</button>
                            <button type="submit" id="submitBtn">Submit Registration</button>
                        </div>
                    </div>
                </form>
            <?php endif; ?>
        </div>
    </div>

    <script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('registrationForm');
    const sections = document.querySelectorAll('.form-section');
    const nextBtns = document.querySelectorAll('.next-btn');
    const backBtns = document.querySelectorAll('.back-btn');
    const submitBtn = document.getElementById('submitBtn');
    const formTitle = document.getElementById('form-title');
    const globalBackButtonContainer = document.getElementById('globalBackButtonContainer'); // Get the global back button container
    let currentSectionIndex = 0;

    const phpErrors = <?php echo json_encode($errors); ?>;
    const phpFormData = <?php echo json_encode($formData); ?>;
    const formSubmittedSuccessfully = <?php echo json_encode($formSubmittedSuccessfully); ?>;

    function showSection(index) {
        sections.forEach((section, i) => {
            section.classList.toggle('active', i === index);
        });
        currentSectionIndex = index;
        updateFormTitle();

        // Control visibility of the global back button
        if (globalBackButtonContainer) {
            if (currentSectionIndex === 0) { // Only show on the first section (index 0)
                globalBackButtonContainer.style.display = 'block';
            } else {
                globalBackButtonContainer.style.display = 'none';
            }
        }

        if (Object.keys(phpErrors).length > 0 && !formSubmittedSuccessfully) {
            displayPHPErrors();
        }
    }

    function updateFormTitle() {
        const titles = [
            'Identity Verification',    
            'Contact Information',
            'Proof of Identity',
            'Review & Submit'
        ];
        formTitle.textContent = titles[currentSectionIndex] || 'Identity Verification';
    }

    function displayError(inputElement, message) {
        if (!inputElement) return;
        inputElement.classList.add('input-invalid');
        const errorElement = document.getElementById(inputElement.id + '-error');
        if (errorElement) {
            errorElement.textContent = message;
            errorElement.style.display = 'block';
        }
    }

    function clearError(inputElement) {
        if (!inputElement) return;
        inputElement.classList.remove('input-invalid');
        const errorElement = document.getElementById(inputElement.id + '-error');
        if (errorElement) {
            errorElement.textContent = '';
            errorElement.style.display = 'none';
        }
    }

    function validateSection(sectionIndex) {
        let isValid = true;
        const currentSection = sections[sectionIndex];
        const requiredInputs = currentSection.querySelectorAll('input[required], select[required], textarea[required]');

        // Special: Middle name logic for section 0
        if (sectionIndex === 0) {
            const middleNameInput = document.getElementById('middleName');
            const noMiddleNameCheckbox = document.getElementById('noMiddleName');
            clearError(middleNameInput);
            if (!noMiddleNameCheckbox.checked && middleNameInput.value.trim() === '') {
                displayError(middleNameInput, 'Please enter your middle name or check the box if you have none.');
                isValid = false;
            }
            // New: Profile photo validation
            const profilePhotoInput = document.getElementById('profilePhoto');
            clearError(profilePhotoInput);
            if (profilePhotoInput && profilePhotoInput.files.length === 0) {
                displayError(profilePhotoInput, 'Profile photo is required.');
                isValid = false;
            } else if (profilePhotoInput && profilePhotoInput.files.length > 0) {
                const file = profilePhotoInput.files[0];
                const allowedTypes = ['image/jpeg', 'image/png'];
                const maxSize = 5 * 1024 * 1024; // 5MB

                if (!allowedTypes.includes(file.type)) {
                    displayError(profilePhotoInput, 'Invalid file type. Only JPG and PNG are allowed for profile photo.');
                    isValid = false;
                }
                if (file.size > maxSize) {
                    displayError(profilePhotoInput, 'Profile photo size exceeds 5MB.');
                    isValid = false;
                }
            }
        }

        // Special: Front and back ID images must be different files
        if (sectionIndex === 2) {
            const idPhotoFront = document.getElementById('idPhotoFront');
            const idPhotoBack = document.getElementById('idPhotoBack');
            clearError(idPhotoFront);
            clearError(idPhotoBack);

            // Check if both files are selected and are the same
            if (
                idPhotoFront.files.length > 0 &&
                idPhotoBack.files.length > 0 &&
                (
                    idPhotoFront.files[0].name === idPhotoBack.files[0].name &&
                    idPhotoFront.files[0].size === idPhotoBack.files[0].size &&
                    idPhotoFront.files[0].lastModified === idPhotoBack.files[0].lastModified
                )
            ) {
                displayError(idPhotoFront, 'Front and back ID images must be different files.');
                displayError(idPhotoBack, 'Front and back ID images must be different files.');
                isValid = false;
            }
        }

        // Validate all required fields in the section
        requiredInputs.forEach(input => {
            // Skip profilePhoto as it's handled in special section logic
            if (input.id === 'profilePhoto') {
                return;
            }

            // Skip readonly inputs from direct validation here, as their values are pre-filled/controlled
            if (input.readOnly) {
                return;
            }

            clearError(input);

            if (input.type === 'radio') {
                // Only validate the first radio in the group
                if (!input.checked) {
                    const group = currentSection.querySelectorAll(`input[name="${input.name}"]`);
                    const isAnyChecked = Array.from(group).some(radio => radio.checked);
                    if (!isAnyChecked) {
                        displayError(group[0], 'This field is required.');
                        isValid = false;
                    }
                }
            } else if (input.type === 'checkbox') {
                if (!input.checked) {
                    displayError(input, 'This field is required.');
                    isValid = false;
                }
            } else if (input.type === 'file') {
                if (input.files.length === 0) {
                    displayError(input, 'This field is required.');
                    isValid = false;
                }
            } else if (input.id === 'middleName') {
                // Already handled above
            } else if (input.value.trim() === '') {
                displayError(input, 'This field is required.');
                isValid = false;
            } else if (input.type === 'email' && !/^[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,}$/i.test(input.value)) {
                displayError(input, 'Please enter a valid email address.');
                isValid = false;
            }
        });

        return isValid;
    }

    function validateAllSections() {
        let allValid = true;
        for (let i = 0; i < sections.length - 1; i++) { // skip summary
            if (!validateSection(i)) {
                allValid = false;
                showSection(i);
                break;
            }
        }
        return allValid;
    }

    function populateSummary() {
        const summaryContent = document.querySelector('.summary-content');
        summaryContent.innerHTML = '';
        const data = new FormData(form);
        const summaryData = {};
        for (let [key, value] of data.entries()) {
            if (key === 'idPhotoFront' || key === 'idPhotoBack' || key === 'profilePhoto') {
                const fileInput = document.getElementById(key);
                if (fileInput && fileInput.files.length > 0) {
                    summaryData[key] = { name: fileInput.files[0].name, url: URL.createObjectURL(fileInput.files[0]) };
                } else {
                    summaryData[key] = { name: 'No file selected', url: '' };
                }
            } else if (key === 'agreeTerms') {
                summaryData[key] = 'Yes';
            } else if (key === 'noMiddleName') {
                // Skip this field from summary as it's handled by middleName directly
                continue;
            }
            else if (key === 'sameAsPermanentAddress') {
                // Skip this field from summary
                continue;
            }
            else {
                summaryData[key] = value;
            }
        }
        const displayMap = {
            firstName: 'First Name',
            middleName: 'Middle Name',
            lastName: 'Last Name',
            suffix: 'Suffix',
            dob: 'Date of Birth',
            sex: 'Sex',
            civilStatus: 'Civil Status',
            citizenship: 'Citizenship',
            birthPlace: 'Place of Birth',
            profilePhoto: 'Profile Photo', // New: Added to display map
            presentAddress: 'Present Address',
            permanentAddress: 'Permanent Address',
            email: 'Email Address',
            contactNumber: 'Contact Number',
            emergencyContactName: 'Emergency Contact Name',
            emergencyContactNumber: 'Emergency Contact Number',
            emergencyContactRelationship: 'Emergency Contact Relationship',
            idType: 'Type of ID',
            idNumber: 'ID Number',
            idPhotoFront: 'Front of ID',
            idPhotoBack: 'Back of ID',
            agreeTerms: 'Agreed to Terms'
        };
        for (const key in displayMap) {
            // Check if middleName is empty and noMiddleName is checked, then display 'N/A'
            if (key === 'middleName' && document.getElementById('noMiddleName')?.checked) {
                const item = document.createElement('div');
                item.classList.add('summary-item');
                const label = document.createElement('strong');
                label.textContent = displayMap[key] + ':';
                item.appendChild(label);
                const valueContainer = document.createElement('span');
                valueContainer.textContent = 'N/A';
                item.appendChild(valueContainer);
                summaryContent.appendChild(item);
                continue;
            }
            if (summaryData[key] !== undefined && summaryData[key] !== null && summaryData[key] !== '') {
                const item = document.createElement('div');
                item.classList.add('summary-item');
                const label = document.createElement('strong');
                label.textContent = displayMap[key] + ':';
                item.appendChild(label);

                const valueContainer = document.createElement('span');
                valueContainer.style.display = 'flex';
                valueContainer.style.flexDirection = 'column';
                valueContainer.style.alignItems = 'flex-start';

                if (key === 'idPhotoFront' || key === 'idPhotoBack' || key === 'profilePhoto') { // New: Include profilePhoto
                    const fileInfo = summaryData[key];
                    const fileNameSpan = document.createElement('span');
                    fileNameSpan.textContent = fileInfo.name;
                    valueContainer.appendChild(fileNameSpan);
                    if (fileInfo.url && fileInfo.url !== 'No file selected') {
                        const fileExtension = fileInfo.name.split('.').pop().toLowerCase();
                        if (['jpg', 'png', 'jpeg', 'gif'].includes(fileExtension)) {
                            const img = document.createElement('img');
                            img.src = fileInfo.url;
                            img.classList.add('summary-file-preview');
                            img.alt = `Preview of ${displayMap[key]}`;
                            valueContainer.appendChild(img);
                        } else if (fileExtension === 'pdf') {
                            const pdfLink = document.createElement('a');
                            pdfLink.href = fileInfo.url;
                            pdfLink.textContent = 'View PDF';
                            pdfLink.target = '_blank';
                            pdfLink.classList.add('summary-file-preview');
                            pdfLink.style.display = 'inline-block';
                            pdfLink.style.maxWidth = '120px';
                            pdfLink.style.textAlign = 'center';
                            pdfLink.style.padding = '10px';
                            pdfLink.style.border = '1px solid #e2d8fa';
                            pdfLink.style.borderRadius = '5px';
                            pdfLink.style.backgroundColor = '#fcfbff';
                            valueContainer.appendChild(pdfLink);
                        }
                    }
                } else {
                    valueContainer.textContent = summaryData[key];
                }
                item.appendChild(valueContainer);
                summaryContent.appendChild(item);
            }
        }
    }

    function displayPHPErrors() {
        for (const field in phpErrors) {
            const inputElement = document.getElementById(field);
            if (inputElement) {
                displayError(inputElement, phpErrors[field]);
                let parentSection = inputElement.closest('.form-section');
                if (parentSection) {
                    let sectionIndex = Array.from(sections).indexOf(parentSection);
                    if (sectionIndex !== -1) {
                        showSection(sectionIndex);
                    }
                }
            } else if (field === 'agreeTerms') {
                const checkbox = document.getElementById('agreeTerms');
                if (checkbox) {
                    displayError(checkbox, phpErrors[field]);
                    let parentSection = checkbox.closest('.form-section');
                    if (parentSection) {
                        let sectionIndex = Array.from(sections).indexOf(parentSection);
                        if (sectionIndex !== -1) {
                            showSection(sectionIndex);
                        }
                    }
                }
            } else if (field === 'db') {
                const modalContent = document.getElementById('registration-form-container');
                if (modalContent) {
                    const errorDiv = document.createElement('div');
                    errorDiv.className = 'popup-notification';
                    errorDiv.style.borderColor = '#d9534f';
                    errorDiv.innerHTML = `<strong>Submission Error:</strong> ${phpErrors[field]}`;
                    modalContent.prepend(errorDiv);
                }
            }
        }
    }

    // Navigation logic
    nextBtns.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            if (validateSection(currentSectionIndex)) {
                if (currentSectionIndex < sections.length - 1) {
                    currentSectionIndex++;
                    if (currentSectionIndex === sections.length - 1) {
                        populateSummary();
                    }
                    showSection(currentSectionIndex);
                }
            }
        });
    });

    backBtns.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            if (currentSectionIndex > 0) {
                currentSectionIndex--;
                showSection(currentSectionIndex);
            }
        });
    });

    // Validate all sections before submit
    if (submitBtn) {
        submitBtn.addEventListener('click', function(e) {
            if (!validateAllSections()) {
                e.preventDefault();
            } else {
                // If validation passes, the form will naturally submit
                // The PHP will then handle the database insertion and redirect to the success message
                // The JS confirmation message should only show if PHP explicitly signals success
            }
        });
    }

    // Clear errors on input
    const inputsToClearErrors = document.querySelectorAll('input, select, textarea');
    inputsToClearErrors.forEach(input => {
        if (['text', 'number', 'date', 'email'].includes(input.type) || input.tagName === 'TEXTAREA') {
            input.addEventListener('input', () => clearError(input));
        }
        if (input.type === 'file' || input.tagName === 'SELECT' || input.type === 'radio' || input.type === 'checkbox') {
            input.addEventListener('change', () => clearError(input));
        }
    });

    // Special case for radio buttons: clear error when any radio in the group is selected
    document.querySelectorAll('input[name="sex"]').forEach(radio => {
        radio.addEventListener('change', () => {
            const group = document.querySelectorAll('input[name="sex"]');
            group.forEach(r => clearError(r));
        });
    });

    // Handle middle name checkbox logic
    const middleNameInput = document.getElementById('middleName');
    const noMiddleNameCheckbox = document.getElementById('noMiddleName');

    if (middleNameInput && noMiddleNameCheckbox) {
        noMiddleNameCheckbox.addEventListener('change', function() {
            if (this.checked) {
                middleNameInput.value = '';
                middleNameInput.disabled = true;
                clearError(middleNameInput);
            } else {
                middleNameInput.disabled = false;
            }
        });

        // Set initial state based on PHP data if middleName is empty
        if (middleNameInput.value.trim() === '' && noMiddleNameCheckbox.checked) {
            middleNameInput.disabled = true;
        }
    }

    // Handle "Same as permanent address" checkbox logic
    const permanentAddressInput = document.getElementById('permanentAddress');
    const presentAddressInput = document.getElementById('presentAddress');
    const sameAsPermanentAddressCheckbox = document.getElementById('sameAsPermanentAddress');

    if (permanentAddressInput && presentAddressInput && sameAsPermanentAddressCheckbox) {
        function updatePresentAddressState() {
            if (sameAsPermanentAddressCheckbox.checked) {
                presentAddressInput.value = permanentAddressInput.value;
                presentAddressInput.readOnly = true;
                presentAddressInput.classList.add('readonly-input');
                clearError(presentAddressInput); // Clear any existing error
            } else {
                // Only clear if it was previously filled by the checkbox and was read-only
                if (presentAddressInput.value === permanentAddressInput.value && presentAddressInput.readOnly) {
                    presentAddressInput.value = '';
                }
                presentAddressInput.readOnly = false;
                presentAddressInput.classList.remove('readonly-input');
            }
        }

        sameAsPermanentAddressCheckbox.addEventListener('change', updatePresentAddressState);

        // Initial check: If presentAddress is already the same as permanentAddress on load, check the box
        // This handles cases where the form was submitted with errors and presentAddress was pre-filled
        // and also for initial load if the session data implies they are the same.
        if (permanentAddressInput.value.trim() !== '' && presentAddressInput.value.trim() === permanentAddressInput.value.trim()) {
            sameAsPermanentAddressCheckbox.checked = true;
            updatePresentAddressState(); // Apply read-only and value
        }
    }


    // Initial display
    showSection(currentSectionIndex);

    if (Object.keys(phpErrors).length > 0 && !formSubmittedSuccessfully) {
        displayPHPErrors();
    }

    const idPhotoFront = document.getElementById('idPhotoFront');
    const idPhotoBack = document.getElementById('idPhotoBack');
    function checkIdFilesDifferent() {
        clearError(idPhotoFront);
        clearError(idPhotoBack);
        if (
            idPhotoFront.files.length > 0 &&
            idPhotoBack.files.length > 0 &&
            idPhotoFront.files[0].name === idPhotoBack.files[0].name &&
            idPhotoFront.files[0].size === idPhotoBack.files[0].size &&
            idPhotoFront.files[0].lastModified === idPhotoBack.files[0].lastModified
        ) {
            displayError(idPhotoFront, 'Front and back ID images must be different files.');
            displayError(idPhotoBack, 'Front and back ID images must be different files.');
        }
    }
    if (idPhotoFront && idPhotoBack) {
        idPhotoFront.addEventListener('change', checkIdFilesDifferent);
        idPhotoBack.addEventListener('change', checkIdFilesDifferent);
    }
});
</script>
</body>
</html>
