<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include("connection.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $last_name = $_POST["last_name"] ?? '';
    $first_name = $_POST["first_name"] ?? '';
    $email = $_POST["email"] ?? '';
    $contact_number = $_POST["contact_number"] ?? '';
    $home_address = $_POST["home_address"] ?? '';
    $years_experience = $_POST["years_experience"] ?? '';
    $roll_number = $_POST["roll_number"] ?? '';
    $motivation = $_POST["motivation"] ?? '';
    $availability_hours = $_POST["availability_hours"] ?? '';
    $urgent_consult = $_POST["urgent_consult"] ?? '';
    $commitment_months = $_POST["commitment_months"] ?? '';
    $preferred_areas = isset($_POST["preferred_areas"]) ? implode(",", $_POST["preferred_areas"]) : '';
    $bar_region = $_POST["bar_region"] ?? '';
    $affiliation = $_POST["affiliation"] ?? '';
    $reference = $_POST["reference"] ?? '';

    $info_certified = isset($_POST["info_certified"]) ? 1 : 0;
    $consent_check = isset($_POST["consent_check"]) ? 1 : 0;
    $agree_terms = isset($_POST["agree_terms"]) ? 1 : 0;

    // Set upload directories
    $license_dir = "uploads/license/";
    $photo_dir = "uploads/profile_photo/";
    $resume_dir = "uploads/resume/";

    // Ensure directories exist
    if (!is_dir($license_dir)) mkdir($license_dir, 0777, true);
    if (!is_dir($photo_dir)) mkdir($photo_dir, 0777, true);
    if (!is_dir($resume_dir)) mkdir($resume_dir, 0777, true);

    $license_path = "";
    $photo_path = "";
    $resume_path = "";

    $allowed_exts = ["pdf", "jpg", "jpeg", "png"];
    $allowed_mimes = ["application/pdf", "image/jpeg", "image/png"];

    // Handle license upload (Image or PDF)
    if (isset($_FILES["license_file"]) && $_FILES["license_file"]["error"] === 0) {
        $license_tmp = $_FILES["license_file"]["tmp_name"];
        $license_ext = strtolower(pathinfo($_FILES["license_file"]["name"], PATHINFO_EXTENSION));
        $license_mime = mime_content_type($license_tmp);

        if (in_array($license_ext, $allowed_exts) && in_array($license_mime, $allowed_mimes)) {
            $license_filename = uniqid("license_") . "." . $license_ext;
            $license_path = $license_dir . $license_filename;
            move_uploaded_file($license_tmp, $license_path);
        } else {
            echo "<script>alert('Only PDF, JPG, JPEG, or PNG files are allowed for the license.'); history.back();</script>";
            exit;
        }
    }

    // Handle profile photo (Image or PDF)
    if (isset($_FILES["profile_photo"]) && $_FILES["profile_photo"]["error"] === 0) {
        $photo_tmp = $_FILES["profile_photo"]["tmp_name"];
        $photo_ext = strtolower(pathinfo($_FILES["profile_photo"]["name"], PATHINFO_EXTENSION));
        $photo_mime = mime_content_type($photo_tmp);

        if (in_array($photo_ext, $allowed_exts) && in_array($photo_mime, $allowed_mimes)) {
            $photo_filename = uniqid("photo_") . "." . $photo_ext;
            $photo_path = $photo_dir . $photo_filename;
            move_uploaded_file($photo_tmp, $photo_path);
        } else {
            echo "<script>alert('Only PDF, JPG, JPEG, or PNG files are allowed for profile photo.'); history.back();</script>";
            exit;
        }
    }

    // Resume upload (optional)
    $allowed_resume_exts = ["pdf", "doc", "docx"];
    $allowed_resume_mimes = ["application/pdf", "application/msword", "application/vnd.openxmlformats-officedocument.wordprocessingml.document"];
    if (isset($_FILES["resume_file"]) && $_FILES["resume_file"]["error"] === 0) {
        $resume_tmp = $_FILES["resume_file"]["tmp_name"];
        $resume_ext = strtolower(pathinfo($_FILES["resume_file"]["name"], PATHINFO_EXTENSION));
        $resume_mime = mime_content_type($resume_tmp);
        if (in_array($resume_ext, $allowed_resume_exts) && in_array($resume_mime, $allowed_resume_mimes)) {
            $resume_filename = uniqid("resume_") . "." . $resume_ext;
            $resume_path = $resume_dir . $resume_filename;
            move_uploaded_file($resume_tmp, $resume_path);
        } else {
            echo "<script>alert('Only PDF, DOC, or DOCX files are allowed for resume.'); history.back();</script>";
            exit;
        }
    } else {
        echo "<script>alert('Resume is required and must be a PDF, DOC, or DOCX file.'); history.back();</script>";
        exit;
    }

    // Handle preferred areas and "Others" text
    $preferred_areas_arr = $_POST["preferred_areas"] ?? [];
    $preferred_areas_other_text = trim($_POST["preferred_areas_other_text"] ?? '');

    if (in_array("Others", $preferred_areas_arr) && $preferred_areas_other_text !== '') {
        // Replace "Others" with the actual text
        foreach ($preferred_areas_arr as $k => $v) {
            if ($v === "Others") {
                $preferred_areas_arr[$k] = "Others: " . $preferred_areas_other_text;
            }
        }
    }
    $preferred_areas = implode(",", $preferred_areas_arr);

    // Prepare insert query (add new fields)
    $sql = "INSERT INTO volunteer_lawyer 
        (last_name, first_name, email, contact_number, home_address, years_experience, roll_number, license_file, profile_photo, motivation, consent_background_check, agree_terms, info_certified,
        availability_hours, urgent_consult, commitment_months, preferred_areas, bar_region, resume_file, affiliation, reference_contact) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = $database->prepare($sql);

    if ($stmt) {
    // Change this line (around line 120):
$stmt->bind_param(
    "sssssissssiiiisssssss",  // Removed spaces and corrected count
    $last_name,             // s
    $first_name,            // s
    $email,                 // s
    $contact_number,        // s
    $home_address,          // s
    $years_experience,      // i
    $roll_number,           // s
    $license_path,          // s
    $photo_path,            // s
    $motivation,            // s
    $consent_check,         // i
    $agree_terms,           // i
    $info_certified,        // i
    $availability_hours,    // i
    $urgent_consult,        // s
    $commitment_months,     // i
    $preferred_areas,       // s
    $bar_region,            // s
    $resume_path,           // s
    $affiliation,           // s
    $reference              // s
);
        if ($stmt->execute()) {
            // Show styled confirmation popup and stop further output
            ?>
            <!DOCTYPE html>
            <html lang="en">
            <head>
              <meta charset="UTF-8">
              <title>Volunteer Registration | SafeSpace PH</title>
                  <link rel="icon" type="image/png" href="https://i.ibb.co/qYYZs46L/logo.png">

              <style>
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
  width: 100vw;
}

.confirmation-box {
  background: #fff;
  border-radius: 16px;
  box-shadow: 0 8px 32px rgba(57, 16, 83, 0.15), 0 2px 8px rgba(90, 38, 117, 0.12);
  padding: 54px 64px 38px 64px;
  max-width: 685px;
  width: 98%;
  margin: 40px auto;
  text-align: center;
  animation: fadeIn 0.3s;
  position: relative;
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
  margin: 26px 0 22px 0;
  border-radius: 7px;
  text-align: left;
  font-size: 1.01rem;
  color: #3a2c5c;
  font-weight: 500;
  line-height: 1.7;
}

.popup-notification strong {
  color: #391053;
  font-weight: 700;
}

.go-back-button {
  display: inline-block;
  background: #5A2675;
  color: #fff;
  padding: 14px 38px;
  border-radius: 7px;
  text-decoration: none;
  font-size: 1.08rem;
  font-weight: 600;
  margin-top: 22px;
  transition: background 0.2s;
  border: none;
  cursor: pointer;
  box-shadow: 0 2px 8px rgba(157, 114, 179, 0.12);
  letter-spacing: 0.2px;
}

.go-back-button:hover {
  background: #391053;
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

              </style>
            </head>
            <body>
              <div class="modal">
                <div class="confirmation-box">
                  <div class="confirmation-check">
                    <!-- Big purple check icon SVG -->
                    <svg viewBox="0 0 80 80" fill="none">
                      <circle cx="40" cy="40" r="38" fill="#f7f4fd" stroke="#391053" stroke-width="4"/>
                      <path d="M25 43.5L37.5 56L56 28" stroke="#391053" stroke-width="6" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                  </div>
                  <h2>Form Submitted Successfully!</h2>
                  <p>
                    Thank you for applying as a <strong>Volunteer Lawyer</strong> with SafeSpace PH.<br>
                    Your application has been received.<br>
                    <span style="color:#391053;font-weight:600;">We appreciate your willingness to help!</span>
                  </p>
                  <div class="popup-notification">
  <strong>What happens next?</strong><br>
  Our team will review your application and contact you within 
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
              </div>
            </body>
            </html>
            <?php
            exit;
        } else {
            echo "Execution failed: " . $stmt->error;
        }
    } else {
        echo "Statement preparation failed: " . $database->error;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Volunteer Lawyer Application | SafeSpace PH</title>
    <link rel="stylesheet" href="css/main.css">
    <link rel="stylesheet" href="css/admin.css">
        <link rel="icon" type="image/png" href="https://i.ibb.co/qYYZs46L/logo.png">

    <style>
        body {
    background: rgba(0, 0, 0, 0.15);
    min-height: 100vh;
    margin: 0;
    font-family: 'Inter', Arial, sans-serif;
}

.modal {
    display: flex;
    align-items: center;
    justify-content: center;
    min-height: 100vh;
}

.modal-content {
    background: #fff;
    border-radius: 10px;
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.15);
    padding: 40px 64px;
    max-width: 800px;
    width: 100%;
    position: relative;
    animation: fadeIn 0.3s;
}

.close {
    position: absolute;
    top: 18px;
    right: 22px;
    font-size: 22px;
    color: #888;
    cursor: pointer;
    border: none;
    background: none;
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
    margin-bottom: 0;
}

.form-group.full-width {
    grid-column: 1 / 3;
}

label {
    display: block;
    margin-bottom: 6px;
    font-weight: 500;
}

input[type="text"],
input[type="email"],
input[type="number"],
textarea {
    width: 100%;
    padding: 8px 10px;
    border: 1px solid #ccc;
    border-radius: 5px;
    font-size: 15px;
}

input[type="file"] {
    margin-top: 4px;
    padding: 8px 0;
    border: none;
    background: none;
    font-size: 15px;
    color: #3a2c5c;
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
    display: flex;
    align-items: flex-start;
    font-weight: 500;
    font-size: 14px;
    color: #3a2c5c;
    line-height: 1.6;
    cursor: pointer;
}

.checkbox-group input[type="checkbox"] {
    margin-right: 10px;
    margin-top: 3px;
    accent-color: #5A2675;
    width: 18px;
    height: 18px;
    flex-shrink: 0;
}

button[type="submit"] {
    background: #5A2675;
    color: #fff;
    border: none;
    border-radius: 5px;
    padding: 10px 28px;
    font-size: 16px;
    cursor: pointer;
    transition: background 0.2s;
}

button[type="submit"]:hover {
    background: #391053;
    border: 2px solid #9D72B3;
    color: #fff;
    box-shadow: 0 4px 16px rgba(157, 114, 179, 0.18);
}

input[type="text"]:focus,
input[type="email"]:focus,
input[type="number"]:focus,
input[type="file"]:focus,
textarea:focus,
select:focus {
    border-color: #5A2675 !important;
    box-shadow: none;
    outline: none;
    transition: border-color 0.2s;
}

button[type="submit"]:focus,
button[type="submit"]:active {
    border: 2px solid #5A2675;
    background: #391053;
    color: #fff;
    outline: none;
    box-shadow: 0 0 0 2px rgba(157, 114, 179, 0.18);
    transition: border-color 0.2s, background 0.2s, box-shadow 0.2s;
}

button[type="submit"]:hover,
button[type="submit"]:focus,
button[type="submit"]:active {
    background: #5A2675;
    border: none;
    color: #fff;
    box-shadow: none;
    outline: none;
    transition: none;
}

select,
select:focus {
    background: #5A2675 url('data:image/svg+xml;utf8,<svg fill="white" height="18" viewBox="0 0 24 24" width="18" xmlns="http://www.w3.org/2000/svg"><path d="M7 10l5 5 5-5z"/></svg>') no-repeat right 16px center/18px 18px;
    color: #fff;
    border: none;
    border-radius: 5px;
    padding: 10px 38px 10px 22px;
    font-size: 15px;
    font-weight: 500;
    outline: none;
    transition: background 0.2s;
    appearance: none;
    -webkit-appearance: none;
    -moz-appearance: none;
    cursor: pointer;
    box-shadow: none;
}

select:hover {
    background-color: #391053;
    color: #fff;
}

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

.preferred-areas-list {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 12px 32px;
    margin-top: 6px;
    margin-bottom: 4px;
}

.preferred-areas-list label {
    font-size: 15px;
    font-weight: 500;
    color: #3a2c5c;
    display: flex;
    align-items: center;
    gap: 6px;
    margin-bottom: 0;
}

@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(-30px);
    }

    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@media (max-width: 800px) {
    .modal-content,
    .popup {
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

@media (max-width: 600px) {
    .preferred-areas-list {
        grid-template-columns: 1fr;
    }
}

    </style>
</head>
<body>
<div class="modal">
    <div class="modal-content">
        <button class="close" onclick="window.location.href='index.html';" title="Close">&times;</button>
        <h2 class="form-title">Volunteer Lawyer Application</h2>
        <div class="form-divider"></div>
        <form method="POST" enctype="multipart/form-data">
            <!-- Personal Information Section -->
            <h3 class="section-title">Personal Information</h3>
            <div class="form-grid">
                <div class="form-group">
                    <label for="first_name">First Name</label>
                    <input type="text" name="first_name" id="first_name" required>
                </div>
                <div class="form-group">
                    <label for="middle_name">Middle Name</label>
                    <input type="text" name="middle_name" id="middle_name">
                </div>
                <div class="form-group">
                    <label for="last_name">Last Name</label>
                    <input type="text" name="last_name" id="last_name" required>
                </div>
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" name="email" id="email" required>
                </div>
                <div class="form-group">
                    <label for="contact_number">Contact Number</label>
                    <input type="text" name="contact_number" id="contact_number" required>
                </div>
                <div class="form-group">
                    <label for="home_address">Home Address</label>
                    <input type="text" name="home_address" id="home_address" required>
                </div>
            </div>
            <!-- Professional Information Section -->
            <h3 class="section-title">Professional Information</h3>
            <div class="form-grid">
                <div class="form-group">
                    <label for="years_experience">Years of Experience</label>
                    <input type="number" name="years_experience" id="years_experience" min="0" required>
                </div>
                <div class="form-group">
                    <label for="roll_number">Roll of Attorneys No.</label>
                    <input type="text" name="roll_number" id="roll_number" required>
                </div>
                <div class="form-group">
                    <label for="bar_region">Bar Membership Region / IBP Chapter</label>
                    <input type="text" name="bar_region" id="bar_region" required>
                </div>
                <div class="form-group">
                    <label for="affiliation">Affiliation (Optional)</label>
                    <input type="text" name="affiliation" id="affiliation">
                </div>
                <div class="form-group">
                    <label for="reference">Reference (Optional)</label>
                    <input type="text" name="reference" id="reference">
                </div>
            </div>
            <!-- Commitment & Availability Section -->
            <h3 class="section-title">Commitment & Availability</h3>
            <div class="form-grid">
                <div class="form-group">
                    <label for="availability_hours">Hours per week you can commit</label>
                    <input type="number" name="availability_hours" id="availability_hours" min="1" required>
                </div>
                <div class="form-group">
                    <label>Available for urgent consultations?</label>
                    <select name="urgent_consult" id="urgent_consult" required>
                        <option value="">Select</option>
                        <option value="Yes">Yes</option>
                        <option value="No">No</option>
                        <option value="Maybe">Maybe</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="commitment_months">Months willing to volunteer</label>
                    <input type="number" name="commitment_months" id="commitment_months" min="1" required>
                </div>
            </div>
            <!-- Legal Work Preferences Section -->
            <h3 class="section-title">Preferred Areas of Legal Work</h3>
         <div class="form-group full-width">
    <label style="color:#555;display:block;margin-bottom:10px; font-family: 'Inter', Arial, sans-serif; font-size:13px;">
        (Select all that apply)
    </label>
    <div class="preferred-areas-list">
                    <label><input type="checkbox" name="preferred_areas[]" value="Family Law"> Family Law</label>
                    <label><input type="checkbox" name="preferred_areas[]" value="Gender-Based Violence"> Gender-Based Violence</label>
                    <label><input type="checkbox" name="preferred_areas[]" value="Workplace Harassment"> Workplace Harassment</label>
                    <label><input type="checkbox" name="preferred_areas[]" value="LGBTQ+ Rights"> LGBTQ+ Rights</label>
                    <label><input type="checkbox" name="preferred_areas[]" value="Sexual Harassment (RA 11313)"> Sexual Harassment (RA 11313)</label>
                    <label><input type="checkbox" name="preferred_areas[]" value="Domestic Violence (RA 9262)"> Domestic Violence (RA 9262)</label>
                    <label><input type="checkbox" name="preferred_areas[]" value="Cybercrime / Online Harassment"> Cybercrime / Online Harassment</label>
                    <label><input type="checkbox" name="preferred_areas[]" value="Child Protection"> Child Protection</label>
                    <label><input type="checkbox" name="preferred_areas[]" value="Human Trafficking / Exploitation"> Human Trafficking / Exploitation</label>
                    <label><input type="checkbox" name="preferred_areas[]" value="Legal Aid for Marginalized Groups"> Legal Aid for Marginalized Groups</label>
                    <label><input type="checkbox" name="preferred_areas[]" value="Mental Health & Legal Safeguards"> Mental Health & Legal Safeguards</label>
                    <label><input type="checkbox" name="preferred_areas[]" value="Community Legal Education"> Community Legal Education</label>
                    <label style="display:flex;align-items:center;">
                        <input type="checkbox" id="preferred_areas_others" name="preferred_areas[]" value="Others" style="margin-right:8px;">
                        Others
                        <input type="text" id="preferred_areas_others_input" name="preferred_areas_other_text" placeholder="Please specify" style="display:none; margin-left:12px; flex:1; min-width:140px; padding:6px 10px; border-radius:5px; border:1px solid #ccc; font-size:14px;">
                    </label>
                </div>
            </div>
            <!-- Supporting Documents Section -->
            <h3 class="section-title">Supporting Documents</h3>
            <div class="form-grid">
                <div class="form-group full-width">
                    <label for="license_file">IBP Identification Card (Image or PDF)</label>
                    <input type="file" name="license_file" id="license_file" accept="application/pdf,image/png,image/jpeg" required>
                </div>
                <div class="form-group full-width">
                    <label for="profile_photo">Profile Photo (Image or PDF)</label>
                    <input type="file" name="profile_photo" id="profile_photo" accept="application/pdf,image/png,image/jpeg" required>
                    <small style="color:#555;display:block;margin-top:4px; font-family: 'Inter', Arial, sans-serif;">
                        Please upload a 2x2 formal photo, preferably with a plain background.
                    </small>
                </div>
                <div class="form-group full-width">
                    <label for="resume_file">Resume (PDF/DOC/DOCX)</label>
                    <input type="file" name="resume_file" id="resume_file" accept="application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document" required>
                </div>
            </div>
            <!-- Motivation Section -->
            <h3 class="section-title">Reason for Volunteering</h3>
            <div class="form-group full-width">
                <label for="motivation">What Inspires You to Join SafeSpace PH?</label>
                <textarea 
                    name="motivation" 
                    id="motivation" 
                    rows="4" 
                    required 
                    placeholder="Share your motivation here..."
                    style="font-size:13px; color:#555; padding-left:10px; padding-right:10px; font-family: 'Inter', Arial, sans-serif;"></textarea>
                <small style="color:#555;display:block;margin-top:4px; font-family: 'Inter', Arial, sans-serif;">
We’d love to know what drives you to offer your time and expertise. Please share your reasons for volunteering, any relevant experience, or what you hope to contribute to the cause.
                </small>
            </div>
            <div class="form-divider"></div>
            <!-- Agreements Section -->
            <div class="checkbox-group">
                <label><input type="checkbox" name="info_certified" required> By checking this box, I certify that all the information I have provided is true, accurate, and complete to the best of my knowledge. I understand that providing false or misleading information may affect the assistance I receive through SafeSpace PH.</label>
            </div>
            <div class="checkbox-group">
                <label><input type="checkbox" name="consent_check" required>  I voluntarily consent to a background check for verification purposes. I understand that this may involve the review of publicly available professional records to confirm my identity and qualifications.</label>
            </div>
            <div class="checkbox-group">
                <label>
                    <input type="checkbox" name="agree_terms" required>
                    I agree to the    
                    <span style="margin-left:4px;">
                    <a href="#terms-popup" class="non-style-link" style="color:#1b5fa7;text-decoration:underline;cursor:pointer;">Terms and Conditions</a>
                    </span>.
                </label>
            </div>
            <div style="text-align: right;">
    <button type="button" class="back-btn" style="margin-top:24px; margin-right:12px; background:#b48be3;" onclick="window.location.href='index.html';">Back</button>
    <button type="submit" style="margin-top:24px;">Submit Application</button>
</div>  
        </form>
    </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const othersCheckbox = document.getElementById('preferred_areas_others');
    const othersInput = document.getElementById('preferred_areas_others_input');
    if (othersCheckbox) {
        othersCheckbox.addEventListener('change', function() {
            othersInput.style.display = this.checked ? 'inline-block' : 'none';
            if (!this.checked) othersInput.value = '';
        });
    }

});
</script>
<div id="terms-popup" class="overlay">
  <div class="popup" style="max-height: 80vh; overflow-y: auto;">
    <h2 style="color: #5A2675; font-size: 28px; font-weight: 700;">Terms and Conditions</h2>
    <a class="close" href="#" style="position:absolute;top:18px;right:22px;font-size:22px;color:#888;text-decoration:none;">&times;</a>
    <div class="content" style="padding-top: 10px; line-height: 1.6; font-size: 14.5px; color: #2c2040;">
      <p><strong>Effective Date:</strong> July 12, 2025</p>
      <h3 style="color: #5A2675;">1. Overview of the Platform</h3>
      <p>SafeSpace PH is a web application providing free legal assistance and education related to the Safe Spaces Act (RA 11313) in the Philippines. It connects victims of gender-based harassment with volunteer lawyers and offers resources for legal awareness.</p>
      
      <h3 style="color: #5A2675;">2. Eligibility</h3>
      <ul>
        <li>Users must be 18 years or older or supervised by a guardian.</li>
        <li>The platform is to be used only for lawful purposes under the Act.</li>
        <li>Legal services are pro bono, and outcomes are not guaranteed.</li>
      </ul>

      <h3 style="color: #5A2675;">3. Use of Services</h3>
      <p><strong>For Users:</strong> Submit honest, complete info. We connect you to volunteer lawyers privately and securely.</p>
      <p><strong>For Lawyers:</strong> Only licensed lawyers may join. You must provide pro bono consultations, and SafeSpace PH may verify credentials.</p>

      <h3 style="color: #5A2675;">4. No Attorney-Client Relationship</h3>
      <p>Using SafeSpace PH does not automatically create an attorney-client relationship. This occurs only through mutual agreement between user and lawyer.</p>

      <h3 style="color: #5A2675;">5. No Guarantees or Warranties</h3>
      <p>We do not guarantee availability of services, outcomes of consultations, or the accuracy of content. Platform changes may occur without prior notice.</p>

      <h3 style="color: #5A2675;">6. User Conduct</h3>
      <ul>
        <li>No fraudulent or malicious activity allowed.</li>
        <li>No impersonation or misrepresentation.</li>
        <li>Users must respect privacy and platform guidelines.</li>
      </ul>

      <h3 style="color: #5A2675;">7. Data Privacy</h3>
      <p>Your data is protected under RA 10173 (Data Privacy Act of 2012). Please refer to our Privacy Policy for full details.</p>

      <h3 style="color: #5A2675;">8. Intellectual Property</h3>
      <p>All website content is owned by SafeSpace PH and may not be reproduced or distributed without permission.</p>

      <h3 style="color: #5A2675;">9. Third-Party Links</h3>
      <p>We are not responsible for content or privacy practices of third-party links provided on the site.</p>

      <h3 style="color: #5A2675;">10. Limitation of Liability</h3>
      <p>SafeSpace PH is not liable for any damage, data loss, or legal consequences from use of the site or consultations made.</p>

      <h3 style="color: #5A2675;">11. Changes to the Terms</h3>
      <p>We may revise these Terms at any time. Continued use of the site implies agreement with any changes made.</p>

      <h3 style="color: #5A2675;">12. Governing Law</h3>
      <p>These Terms are governed by the laws of the Republic of the Philippines.</p>

      <h3 style="color: #5A2675;">13. Contact</h3>
      <p>📧 safespaceph2025@gmail.com<br>📍 SafeSpace PH Office, P. Paredes St., Sampaloc, Manila 1015</p>

      <h3 style="color: #5A2675;">14. Acceptance</h3>
      <p>By using this platform, you confirm that you have read and agree to these Terms and Conditions.</p>

      <div style="text-align:center; margin-top: 28px;">
        <a href="#" class="back-btn">Back</a>
      </div>
    </div>
  </div>
</div>

<style>
.overlay {
  position: fixed;
  top: 0; left: 0; right: 0; bottom: 0;
  background: rgba(0,0,0,0.25);
  z-index: 9999;
  display: none;
}
.overlay:target {
  display: block;
}
.popup {
  background: #fff;
  border-radius: 18px;
  box-shadow: 0 8px 32px rgba(0,0,0,0.15);
  padding: 48px 64px 36px 64px;
  max-width: 720px;
  width: 98%;
  margin: 60px auto;
  position: relative;
  animation: fadeIn 0.3s;
}
.back-btn {
  display: inline-block;
  background: #5A2675;
  color: #fff !important;
  border: none;
  border-radius: 6px;
  padding: 10px 32px;
  font-size: 16px;
  font-weight: 500;
  text-decoration: none;
  margin-top: 10px;
  cursor: pointer;
  box-shadow: 0 2px 8px rgba(90,38,117,0.2);
}
.back-btn:hover {
  background: #391053;
  color: #fff;
}
@keyframes fadeIn {
  from { opacity: 0; transform: translateY(-20px); }
  to { opacity: 1; transform: translateY(0); }
}
</style>