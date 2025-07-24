<?php
require __DIR__ . '/vendor/autoload.php'; // Composer autoloader
require __DIR__ . '/phpmailer/PHPMailer.php';
require __DIR__ . '/phpmailer/SMTP.php';
require __DIR__ . '/phpmailer/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Load .env variables
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

function sendConfirmationEmail($recipientEmail, $recipientName) {
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = $_ENV['EMAIL_USER'];
        $mail->Password   = $_ENV['EMAIL_PASS'];
        $mail->SMTPSecure = 'tls';
        $mail->Port       = 587;

        $mail->setFrom($_ENV['EMAIL_USER'], 'SafeSpace PH');
        $mail->addAddress($recipientEmail, $recipientName);

        $mail->isHTML(true);
        $mail->Subject = 'Welcome to SafeSpacePH!';
        $mail->addEmbeddedImage(__DIR__ . '/img/logo.png', 'logoimg');
        $mail->addEmbeddedImage(__DIR__ . '/img/logo.png', 'logoimg_footer');
        $mail->Body    = "
        <!DOCTYPE html>
        <html lang='en'>
        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <title>Welcome to SafeSpace PH - Your Partner for Justice and Awareness!</title>
            <style>
                body { font-family: 'Inter', sans-serif; margin: 0; padding: 0; background-color: #f4f4f4; -webkit-text-size-adjust: 100%; -ms-text-size-adjust: 100%; width: 100% !important; }
                table { border-collapse: collapse; mso-table-lspace: 0pt; mso-table-rspace: 0pt; }
                td { padding: 0; }
                img { -ms-interpolation-mode: bicubic; border: 0; height: auto; line-height: 100%; outline: none; text-decoration: none; }
                a { text-decoration: none; color: #8a2be2; }
                .email-container { max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1); }
                .header { background-color: #391053; padding: 20px 30px; text-align: center; color: white; }
                .header-logo { height: 70px; width: auto; vertical-align: middle; margin-right: 15px; }
                .header-title { font-size: 28px; font-weight: 1000; color: #ffffff; white-space: nowrap; text-shadow: 2px 2px 10px rgba(57, 16, 83, 0.3); vertical-align: middle; }
                .content { padding: 30px; color: #333333; line-height: 1.6; font-size: 16px; }
                .content h2 { font-size: 22px; color: #5d00a0; margin-top: 25px; margin-bottom: 15px; }
                .content ul { list-style: none; padding: 0; margin: 0; }
                .content ul li { margin-bottom: 10px; padding-left: 25px; position: relative; }
                .content ul li:before { content: '•'; color: #8a2be2; font-size: 20px; position: absolute; left: 0; top: -2px; }
                .button-container { text-align: center; margin-top: 30px; margin-bottom: 20px; }
                .button { display: inline-block; background-color: #8a2be2; color: white; padding: 12px 25px; border-radius: 25px; font-size: 17px; font-weight: bold; text-decoration: none; transition: background-color 0.3s ease; }
                .button:hover { background-color: #6a0dad; }
                .footer { background-color: #391053; color: white; padding: 20px 30px; text-align: center; font-size: 14px; border-top: 1px solid #5d00a0; }
                .footer-branding { display: flex; justify-content: center; align-items: center; gap: 10px; margin-bottom: 15px; }
                .footer-logo { height: 50px; width: auto; }
                .footer-title { font-size: 24px; font-weight: 1000; color: #ffffff; white-space: nowrap; text-shadow: 2px 2px 10px rgba(57, 16, 83, 0.3); }
                .footer-links-container { margin-bottom: 10px; }
                .footer a { color: #e0caff; text-decoration: none; margin: 0 8px; }
                .footer a:hover { text-decoration: underline; }
            </style>
        </head>
        <body>
            <center>
                <table role='presentation' cellspacing='0' cellpadding='0' border='0' width='100%' style='background-color: #f4f4f4;'>
                    <tr>
                        <td align='center' style='padding: 20px 0;'>
                            <table role='presentation' cellspacing='0' cellpadding='0' border='0' width='600' class='email-container'>
                                <tr>
                                    <td class='header'>
                                        <table role='presentation' cellspacing='0' cellpadding='0' border='0' width='100%'>
                                            <tr>
                                                <td style='text-align: center;'>
                                                    <img src=\"cid:logoimg\" alt=\"SafeSpace PH Logo\" style=\"height: 70px; width: auto; vertical-align: middle; margin-right: 15px;\">
                                                    <span class='header-title'>SafeSpace PH</span>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                                <tr>
                                    <td class='content'>
                                        <p>Dear {$recipientName},</p>
                                        <p>Welcome to SafeSpace PH! We are thrilled to have you join our community dedicated to promoting justice, awareness, and accessible legal assistance in the Philippines.</p>
                                        <p>At SafeSpace PH, our mission is to bridge the gap between victims of gender-based sexual harassment and the justice system, particularly concerning the Safe Spaces Act (Republic Act No. 11313). We understand that navigating legal processes can be daunting, and access to legal aid is often limited. That's why we're here to help.</p>
                                        <h2>What can you do on SafeSpace PH?</h2>
                                        <ul>
                                            <li><strong>Access Pro Bono Legal Services:</strong> If you are a victim of gender-based sexual harassment or other violations covered by the Safe Spaces Act, you can connect with volunteer lawyers who are ready to provide free consultations and educate you about your rights.</li>
                                            <li><strong>Expand Your Knowledge:</strong> Our platform is a reliable source of information about the Safe Spaces Act, helping you understand your rights and how to properly handle violations.</li>
                                            <li><strong>Join a Network of Support:</strong> Whether you're a victim, a legal professional willing to volunteer, or simply someone seeking information, SafeSpace PH connects you with a community committed to upholding human dignity.</li>
                                        </ul>
                                        <p>We believe that increased awareness and easy access to legal assistance are crucial steps towards achieving justice and equality. Your journey towards empowerment and healing begins here.</p>
                                        <div class='button-container'>
                                            <a href='https://safespaceph.com' class='button'>Get Started</a>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td class='footer'>
                                        <div class='footer-branding'>
                                            <img src=\"cid:logoimg_footer\"alt=\"SafeSpace PH Logo\" style=\"height: 50px; width: auto; vertical-align: middle; margin-right: 15px;\">
                                            <span class='footer-title'>SafeSpace PH</span>
                                        </div>
                                        <div class='footer-links-container'>
                                            <a href='https://safespaceph.com/about' class='footer-link'>About Us</a>
                                            <a href='https://safespaceph.com/services' class='footer-link'>Our Services</a>
                                            <a href='https://safespaceph.com/contact' class='footer-link'>Contact Us</a>
                                        </div>
                                        <p>&copy; 2023 SafeSpace PH. All rights reserved.</p>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
            </center>
        </body>
        </html>
        ";

        $mail->send();
        // Optionally, you can return true or a success message here
    } catch (Exception $e) {
        error_log("Message could not be sent. Mailer Error: {$mail->ErrorInfo}");
        echo "<pre>Mailer Error: {$mail->ErrorInfo}</pre>"; // Show error for debugging
    }

}

function sendVerificationNoticeToClient($recipientEmail, $recipientName) {
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = $_ENV['EMAIL_USER'];
        $mail->Password = $_ENV['EMAIL_PASS'];
        $mail->SMTPSecure = 'tls';
        $mail->Port = 587;

        $mail->setFrom($_ENV['EMAIL_USER'], 'SafeSpace PH');
        $mail->addAddress($recipientEmail, $recipientName);

        $mail->isHTML(true);
        $mail->Subject = 'Verification in Progress - SafeSpace PH Legal Services';
        $mail->addEmbeddedImage(__DIR__ . '/img/logo.png', 'logoimg');
        $mail->addEmbeddedImage(__DIR__ . '/img/logo.png', 'logoimg_footer');

        $recipientName = htmlspecialchars($recipientName); // Prevent injection
        $mail->Body = "
        <!DOCTYPE html>
<html lang='en'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Identity Verification - SafeSpace PH</title>
    <style>
        body { font-family: 'Inter', sans-serif; margin: 0; padding: 0; background-color: #f4f4f4; width: 100% !important; }
        table { border-collapse: collapse; }
        td { padding: 0; }
        img { border: 0; height: auto; outline: none; }
        a { text-decoration: none; color: #8a2be2; }
        .email-container { max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 8px; box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1); }
        .header { background-color: #391053; padding: 20px 30px; text-align: center; color: white; }
        .header-title { font-size: 28px; font-weight: 1000; color: #ffffff; }
        .content { padding: 30px; color: #333; line-height: 1.6; font-size: 16px; }
        .content h2 { font-size: 22px; color: #5d00a0; margin-top: 25px; }
        .content ul { list-style: none; padding: 0; }
        .content ul li { margin-bottom: 10px; padding-left: 25px; position: relative; }
        .content ul li:before { content: '•'; color: #8a2be2; position: absolute; left: 0; }
        .button-container { text-align: center; margin-top: 30px; }
        .button { background-color: #8a2be2; color: white; padding: 12px 25px; border-radius: 25px; font-size: 17px; font-weight: bold; }
        .footer { background-color: #391053; color: white; padding: 20px 30px; text-align: center; font-size: 14px; border-top: 1px solid #5d00a0; }
        .footer a { color: #e0caff; margin: 0 8px; }
    </style>
</head>
<body>
    <center>
        <table role='presentation' width='100%' style='background-color: #f4f4f4;'>
            <tr>
                <td align='center' style='padding: 20px 0;'>
                    <table width='600' class='email-container'>
                        <tr>
                            <td class='header'>
                                <img src='cid:logoimg' alt='SafeSpace PH Logo' style='height: 70px; vertical-align: middle;'>
                                <div class='header-title'>SafeSpace PH</div>
                            </td>
                        </tr>
                        <tr>
                            <td class='content'>
                                <p>Dear {$recipientName},</p>
                                <p>Thank you for submitting your request for legal services through SafeSpace PH. We’ve received your verification request and our team is currently reviewing the information you provided.</p>
                                <p>This step helps us ensure a safe, reliable environment for clients and legal professionals alike. We aim to respond within 24–48 hours depending on the volume of requests.</p>
                                <h2>What to expect next:</h2>
                                <ul>
                                    <li>Your submitted documents and details will be reviewed by our verification team.</li>
                                    <li>We may contact you if additional information is needed.</li>
                                    <li>You’ll be notified via email once your legal service access has been confirmed.</li>
                                </ul>
                                <p>You can monitor your verification status at any time via your SafeSpace PH account.</p>
                                <div class='button-container'>
                                    <a href='https://safespaceph.com/my-account' class='button'>View My Account</a>
                                </div>
                                <p>We appreciate your trust in SafeSpace PH. Our lawyers are ready to help you with your legal needs once your verification is approved.</p>
                                <p>Warm regards,<br>SafeSpace PH Legal Team</p>
                            </td>
                        </tr>
                        <tr>
                            <td class='footer'>
                                <img src='cid:logoimg_footer' alt='SafeSpace PH Logo' style='height: 50px;'>
                                <div style='font-size: 24px; font-weight: 1000;'>SafeSpace PH</div>
                                <div>
                                    <a href='https://safespaceph.com/about'>About Us</a>
                                    <a href='https://safespaceph.com/services'>Services</a>
                                    <a href='https://safespaceph.com/contact'>Contact</a>
                                </div>
                                <p>&copy; 2023 SafeSpace PH. All rights reserved.</p>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </center>
</body>
</html>";

        $mail->send();
    } catch (Exception $e) {
        error_log("Verification email error: {$mail->ErrorInfo}");
    }
}

/**
 * Sends an email to the client to notify them that their appointment request is pending.
 *
 * @param string $recipientEmail The client's email address.
 * @param string $recipientName The client's full name.
 * @param string $appointmentDate The preferred date for the appointment.
 * @param string $appointmentTime The preferred time for the appointment.
 * @param string $caseTitle The title or subject of the legal concern.
 * @param string $caseDescription The detailed description of the case.
 * @param string $lawyerName The name of the lawyer (defaults to 'To be Assigned').
 * @param string $meetingType The type of meeting (defaults to 'Online Consultation').
 */
function sendAppointmentPendingEmail($recipientEmail, $recipientName, $appointmentDate, $appointmentTime, $caseTitle, $caseDescription, $lawyerName = 'To be Assigned', $meetingType = 'Online Consultation') {
    // Add logging
    error_log("Attempting to send appointment confirmation email to: $recipientEmail");
    
    $mail = new PHPMailer(true);
    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = $_ENV['EMAIL_USER'];
        $mail->Password   = $_ENV['EMAIL_PASS'];
        $mail->SMTPSecure = 'tls';
        $mail->Port       = 587;

        // Recipients
        $mail->setFrom($_ENV['EMAIL_USER'], 'SafeSpace PH');
        $mail->addAddress($recipientEmail, $recipientName);

        // Content
        $mail->isHTML(true);
        $mail->Subject = 'SafeSpace PH: Your Appointment Request is Pending';
        $mail->addEmbeddedImage(__DIR__ . '/img/logo.png', 'logoimg');
        $mail->addEmbeddedImage(__DIR__ . '/img/logo.png', 'logoimg_footer');

        // Sanitize dynamic data before inserting into the email body
        $recipientName_safe = htmlspecialchars($recipientName, ENT_QUOTES, 'UTF-8');
        $lawyerName_safe = htmlspecialchars($lawyerName, ENT_QUOTES, 'UTF-8');
        $appointmentDate_safe = htmlspecialchars(date("F j, Y", strtotime($appointmentDate)), ENT_QUOTES, 'UTF-8');
        $appointmentTime_safe = htmlspecialchars(date("g:i A", strtotime($appointmentTime)), ENT_QUOTES, 'UTF-8');
        $meetingType_safe = htmlspecialchars($meetingType, ENT_QUOTES, 'UTF-8');
        $caseTitle_safe = htmlspecialchars($caseTitle, ENT_QUOTES, 'UTF-8');
        $caseDescription_safe = nl2br(htmlspecialchars($caseDescription, ENT_QUOTES, 'UTF-8'));

        // HTML Email Body
        $mail->Body = "
        <!DOCTYPE html>
        <html lang='en'>
        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <title>SafeSpace PH: Your Appointment Request is Pending</title>
            <style>
                body { font-family: Arial, sans-serif; margin: 0; padding: 0; background-color: #f4f4f4; }
                .email-container { max-width: 600px; margin: 20px auto; background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1); }
                .header { background-color: #391053; color: white; padding: 20px 30px; text-align: center; }
                .header-title { font-size: 28px; font-weight: bold; vertical-align: middle;}
                .content { padding: 30px; color: #333333; line-height: 1.6; font-size: 16px; }
                .content h2 { font-size: 20px; color: #5d00a0; margin-top: 25px; margin-bottom: 15px; border-bottom: 2px solid #eee; padding-bottom: 5px;}
                .details-list { list-style: none; padding: 0; margin: 0; background-color: #f9f9f9; border-left: 4px solid #8a2be2; padding: 15px; border-radius: 5px; }
                .details-list li { margin-bottom: 10px; }
                .details-list li strong { color: #555; }
                .button-container { text-align: center; margin-top: 30px; margin-bottom: 20px; }
                .button { display: inline-block; background-color: #8a2be2; color: white !important; padding: 12px 25px; border-radius: 25px; font-size: 17px; font-weight: bold; text-decoration: none; }
                .footer { background-color: #391053; color: white; padding: 20px 30px; text-align: center; font-size: 14px; border-top: 1px solid #5d00a0; }
                .footer a { color: #e0caff; text-decoration: none; margin: 0 8px;}
            </style>
        </head>
        <body>
            <center>
            <table role='presentation' cellspacing='0' cellpadding='0' border='0' width='600' class='email-container'>
               <tr>
                    <td class='header'>
                        <img src='cid:logoimg' alt='SafeSpace PH Logo' style='height: 70px; vertical-align: middle; margin-right: 15px;'>
                        <span class='header-title'>SafeSpace PH</span>
                    </td>
                </tr>
                <tr>
                  <td class='content'>
                    <p>Dear {$recipientName_safe},</p>
                    <p>Thank you for requesting an appointment with <strong>SafeSpace PH</strong>! We've successfully received your booking request.</p>
                    <p>Your appointment is currently <strong>pending approval</strong>. Our team will review your request and connect you with an available lawyer. We appreciate your patience as we work to find the best match for your needs.</p>
                    
                    <h2>Your Requested Appointment Details:</h2>
                    <ul class='details-list'>
                        <li><strong>Lawyer:</strong> {$lawyerName_safe}</li>
                        <li><strong>Preferred Date:</strong> {$appointmentDate_safe}</li>
                        <li><strong>Preferred Time:</strong> {$appointmentTime_safe} (PHT)</li>
                        <li><strong>Meeting Type:</strong> {$meetingType_safe}</li>
                        <li><strong>Case/Concern:</strong> {$caseTitle_safe}</li>
                        <li><strong>Description:</strong><br>{$caseDescription_safe}</li>
                    </ul>

                    <p>You will receive another email from us once your appointment has been confirmed and a lawyer has been assigned, or if we need to propose alternative time slots. Please keep an eye on your inbox.</p>
                    
                    <div class='button-container'>
                        <a href='https://safespaceph.com/my-account' class='button'>Check My Appointments</a>
                    </div>

                    <p>Thank you for choosing SafeSpace PH. We are committed to providing you with the support you need.</p>
                    <p>Sincerely,<br>The SafeSpace PH Team</p>
                  </td>
                </tr>
                <tr>
                    <td class='footer'>
                       <img src='cid:logoimg_footer' alt='SafeSpace PH Logo' style='height: 50px; margin-bottom: 15px;'>
                        <div style='font-size: 24px; font-weight: 1000;'>SafeSpace PH</div>
                        <div>
                           <a href='https://safespaceph.com/about'>About Us</a> | <a href='https://safespaceph.com/contact'>Contact Us</a>
                        </div>
                        <p>&copy; ".date('Y')." SafeSpace PH. All rights reserved.</p>
                    </td>
                </tr>
            </table>
            </center>
        </body>
        </html>";

        $mail->send();
        error_log("Appointment confirmation email sent successfully to: $recipientEmail");
        return true;
    } catch (Exception $e) {
        error_log("Failed to send appointment confirmation email to {$recipientEmail}: {$mail->ErrorInfo}");
        throw $e; // Re-throw to be handled by caller
    }
}

function sendAppointmentCanceledEmail($recipientEmail, $recipientName, $appointmentDate, $appointmentTime, $caseTitle, $caseDescription, $lawyerName = 'Not Assigned', $meetingType = 'Online Consultation') {
    error_log("Attempting to send appointment cancellation email to: $recipientEmail");

    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = $_ENV['EMAIL_USER'];
        $mail->Password   = $_ENV['EMAIL_PASS'];
        $mail->SMTPSecure = 'tls';
        $mail->Port       = 587;

        $mail->setFrom($_ENV['EMAIL_USER'], 'SafeSpace PH');
        $mail->addAddress($recipientEmail, $recipientName);

        $mail->isHTML(true);
        $mail->Subject = 'SafeSpace PH: Appointment Request Canceled';
        $mail->addEmbeddedImage(__DIR__ . '/img/logo.png', 'logoimg');
        $mail->addEmbeddedImage(__DIR__ . '/img/logo.png', 'logoimg_footer');

        // Sanitize
        $recipientName_safe = htmlspecialchars($recipientName, ENT_QUOTES, 'UTF-8');
        $lawyerName_safe = htmlspecialchars($lawyerName, ENT_QUOTES, 'UTF-8');
        $appointmentDate_safe = htmlspecialchars(date("F j, Y", strtotime($appointmentDate)), ENT_QUOTES, 'UTF-8');
        $appointmentTime_safe = htmlspecialchars(date("g:i A", strtotime($appointmentTime)), ENT_QUOTES, 'UTF-8');
        $meetingType_safe = htmlspecialchars($meetingType, ENT_QUOTES, 'UTF-8');
        $caseTitle_safe = htmlspecialchars($caseTitle, ENT_QUOTES, 'UTF-8');
        $caseDescription_safe = nl2br(htmlspecialchars($caseDescription, ENT_QUOTES, 'UTF-8'));

        $mail->Body = "
        <!DOCTYPE html>
        <html lang='en'>
        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <title>SafeSpace PH: Appointment Request Canceled</title>
            <style>
                body { font-family: Arial, sans-serif; margin: 0; padding: 0; background-color: #f4f4f4; }
                .email-container { max-width: 600px; margin: 20px auto; background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1); }
                .header { background-color: #391053; color: white; padding: 20px 30px; text-align: center; }
                .header-title { font-size: 28px; font-weight: bold; vertical-align: middle;}
                .content { padding: 30px; color: #333333; line-height: 1.6; font-size: 16px; }
                .content h2 { font-size: 20px; color: #5d00a0; margin-top: 25px; margin-bottom: 15px; border-bottom: 2px solid #eee; padding-bottom: 5px;}
                .details-list { list-style: none; padding: 0; margin: 0; background-color: #f9f9f9; border-left: 4px solid #8a2be2; padding: 15px; border-radius: 5px; }
                .details-list li { margin-bottom: 10px; }
                .details-list li strong { color: #555; }
                .button-container { text-align: center; margin-top: 30px; margin-bottom: 20px; }
                .button { display: inline-block; background-color: #8a2be2; color: white !important; padding: 12px 25px; border-radius: 25px; font-size: 17px; font-weight: bold; text-decoration: none; }
                .footer { background-color: #391053; color: white; padding: 20px 30px; text-align: center; font-size: 14px; border-top: 1px solid #5d00a0; }
                .footer a { color: #e0caff; text-decoration: none; margin: 0 8px;}
            </style>
        </head>
        <body>
            <center>
            <table role='presentation' cellspacing='0' cellpadding='0' border='0' width='600' class='email-container'>
               <tr>
                    <td class='header'>
                        <img src='cid:logoimg' alt='SafeSpace PH Logo' style='height: 70px; vertical-align: middle; margin-right: 15px;'>
                        <span class='header-title'>SafeSpace PH</span>
                    </td>
                </tr>
                <tr>
                  <td class='content'>
                    <p>Dear {$recipientName_safe},</p>
                    <p>We regret to inform you that your appointment request with <strong>SafeSpace PH</strong> has been <strong>canceled</strong>.</p>
                    
                    <h2>Canceled Appointment Details:</h2>
                    <ul class='details-list'>
                        <li><strong>Lawyer:</strong> {$lawyerName_safe}</li>
                        <li><strong>Preferred Date:</strong> {$appointmentDate_safe}</li>
                        <li><strong>Preferred Time:</strong> {$appointmentTime_safe} (PHT)</li>
                        <li><strong>Meeting Type:</strong> {$meetingType_safe}</li>
                        <li><strong>Case/Concern:</strong> {$caseTitle_safe}</li>
                        <li><strong>Description:</strong><br>{$caseDescription_safe}</li>
                    </ul>

<p>This appointment request has been canceled — either manually by you or due to other circumstances. This may have been due to scheduling conflicts, incomplete information, or other factors. If you believe this was a mistake or you would like to reschedule, we encourage you to request another appointment at your convenience.</p>                    
                    <div class='button-container'>
                        <a href='https://safespaceph.com/my-account' class='button'>Book Another Appointment</a>
                    </div>

                    <p>We appreciate your understanding, and we remain committed to helping you find the support you need.</p>
                    <p>Sincerely,<br>The SafeSpace PH Team</p>
                  </td>
                </tr>
                <tr>
                    <td class='footer'>
                       <img src='cid:logoimg_footer' alt='SafeSpace PH Logo' style='height: 50px; margin-bottom: 15px;'>
                        <div style='font-size: 24px; font-weight: 1000;'>SafeSpace PH</div>
                        <div>
                           <a href='https://safespaceph.com/about'>About Us</a> | <a href='https://safespaceph.com/contact'>Contact Us</a>
                        </div>
                        <p>&copy; " . date('Y') . " SafeSpace PH. All rights reserved.</p>
                    </td>
                </tr>
            </table>
            </center>
        </body>
        </html>";

        $mail->send();
        error_log("Appointment cancellation email sent successfully to: $recipientEmail");
        return true;
    } catch (Exception $e) {
        error_log("Failed to send appointment cancellation email to {$recipientEmail}: {$mail->ErrorInfo}");
        throw $e;
    }
}

/**
 * Sends a detailed appointment cancellation email to the user, including the reason.
 *
 * @param string $recipientEmail The client's email address.
 * @param string $recipientName The client's full name.
 * @param array $appointmentDetails Associative array with appointment info.
 * @param string $cancellationReason The reason for cancellation.
 * @param string $cancellationExplanation Additional details from the lawyer.
 */
function sendDetailedAppointmentCanceledEmail($recipientEmail, $recipientName, $appointmentDetails, $cancellationReason, $cancellationExplanation) {
    error_log("Attempting to send detailed appointment cancellation email to: $recipientEmail");
    
    $mail = new PHPMailer(true);
    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = $_ENV['EMAIL_USER'];
        $mail->Password   = $_ENV['EMAIL_PASS'];
        $mail->SMTPSecure = 'tls';
        $mail->Port       = 587;

        // Recipients
        $mail->setFrom($_ENV['EMAIL_USER'], 'SafeSpace PH');
        $mail->addAddress($recipientEmail, $recipientName);

        // Content
        $mail->isHTML(true);
        $mail->Subject = 'Important: Your SafeSpace PH Appointment Has Been Canceled';
        $mail->addEmbeddedImage(__DIR__ . '/img/logo.png', 'logoimg');
        $mail->addEmbeddedImage(__DIR__ . '/img/logo.png', 'logoimg_footer');

        // Sanitize data
        $recipientName_safe = htmlspecialchars($recipientName, ENT_QUOTES, 'UTF-8');
        $lawyerName_safe = htmlspecialchars($appointmentDetails['lawyerName'] ?? 'The assigned lawyer', ENT_QUOTES, 'UTF-8');
        $appointmentDate_safe = htmlspecialchars(date("F j, Y", strtotime($appointmentDetails['appointmentDate'])), ENT_QUOTES, 'UTF-8');
        $appointmentTime_safe = htmlspecialchars(date("g:i A", strtotime($appointmentDetails['appointmentTime'])), ENT_QUOTES, 'UTF-8');
        $caseTitle_safe = htmlspecialchars($appointmentDetails['caseTitle'], ENT_QUOTES, 'UTF-8');
        $cancellationReason_safe = htmlspecialchars($cancellationReason, ENT_QUOTES, 'UTF-8');
        $cancellationExplanation_safe = !empty($cancellationExplanation) ? nl2br(htmlspecialchars($cancellationExplanation, ENT_QUOTES, 'UTF-8')) : 'No additional details were provided.';

        $mail->Body = "
        <!DOCTYPE html>
        <html lang='en'>
        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <title>SafeSpace PH: Appointment Canceled</title>
            <style>
                body { font-family: 'Inter', sans-serif; margin: 0; padding: 0; background-color: #f4f4f4; -webkit-text-size-adjust: 100%; -ms-text-size-adjust: 100%; width: 100% !important; }
                table { border-collapse: collapse; mso-table-lspace: 0pt; mso-table-rspace: 0pt; }
                td { padding: 0; }
                img { -ms-interpolation-mode: bicubic; border: 0; height: auto; line-height: 100%; outline: none; text-decoration: none; }
                a { text-decoration: none; color: #8a2be2; }
                .email-container { max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1); }
                .header { background-color: #391053; padding: 20px 30px; text-align: center; color: white; }
                .header-logo { height: 70px; width: auto; vertical-align: middle; margin-right: 15px; }
                .header-title { font-size: 28px; font-weight: 1000; color: #ffffff; white-space: nowrap; text-shadow: 2px 2px 10px rgba(57, 16, 83, 0.3); vertical-align: middle; }
                .content { padding: 30px; color: #333333; line-height: 1.6; font-size: 16px; }
                .content h2 { font-size: 22px; color: #5d00a0; margin-top: 25px; margin-bottom: 15px; }
                .details-list { list-style: none; padding: 15px; margin: 20px 0; background-color: #f9f9f9; border-left: 4px solid #aaa; border-radius: 5px; }
                .details-list li { margin-bottom: 10px; }
                .details-list li strong { color: #555; }
                .reason-box { list-style: none; padding: 15px; margin: 20px 0; background-color: #fff0f0; border-left: 4px solid #d9534f; border-radius: 5px; color: #721c24; }
                .reason-box li { margin-bottom: 10px; }
                .reason-box li strong { color: #d9534f; }
                .button-container { text-align: center; margin-top: 30px; margin-bottom: 20px; }
                .button { display: inline-block; background-color: #8a2be2; color: white !important; padding: 12px 25px; border-radius: 25px; font-size: 17px; font-weight: bold; text-decoration: none; transition: background-color 0.3s ease; }
                .button:hover { background-color: #6a0dad; }
                .footer { background-color: #391053; color: white; padding: 20px 30px; text-align: center; font-size: 14px; border-top: 1px solid #5d00a0; }
                .footer-branding { display: flex; justify-content: center; align-items: center; gap: 10px; margin-bottom: 15px; }
                .footer-logo { height: 50px; width: auto; }
                .footer-title { font-size: 24px; font-weight: 1000; color: #ffffff; white-space: nowrap; text-shadow: 2px 2px 10px rgba(57, 16, 83, 0.3); }
                .footer-links-container { margin-bottom: 10px; }
                .footer a { color: #e0caff; text-decoration: none; margin: 0 8px; }
                .footer a:hover { text-decoration: underline; }
            </style>
        </head>
        <body>
            <center>
                <table role='presentation' cellspacing='0' cellpadding='0' border='0' width='100%' style='background-color: #f4f4f4;'>
                    <tr>
                        <td align='center' style='padding: 20px 0;'>
                            <table role='presentation' cellspacing='0' cellpadding='0' border='0' width='600' class='email-container'>
                                <tr>
                                    <td class='header'>
                                        <img src='cid:logoimg' alt='SafeSpace PH Logo' style='height: 70px; vertical-align: middle; margin-right: 15px;'>
                                        <span class='header-title'>SafeSpace PH</span>
                                    </td>
                                </tr>
                                <tr>
                                    <td class='content'>
                                        <p>Dear {$recipientName_safe},</p>
                                        <p>We are writing to inform you that your upcoming appointment with <strong>{$lawyerName_safe}</strong> has been <strong>canceled</strong>.</p>
                                        
                                        <h2>Reason for Cancellation:</h2>
                                        <ul class='reason-box'>
                                            <li><strong>Reason:</strong> {$cancellationReason_safe}</li>
                                            <li><strong>Note from the Lawyer:</strong><br>{$cancellationExplanation_safe}</li>
                                        </ul>

                                        <h2>Canceled Appointment Details:</h2>
                                        <ul class='details-list'>
                                            <li><strong>Lawyer:</strong> {$lawyerName_safe}</li>
                                            <li><strong>Date:</strong> {$appointmentDate_safe}</li>
                                            <li><strong>Time:</strong> {$appointmentTime_safe} (PHT)</li>
                                            <li><strong>Case/Concern:</strong> {$caseTitle_safe}</li>
                                        </ul>

                                        <p>We understand this may be inconvenient and apologize for any disruption to your schedule. If you would like to reschedule, you can book a new appointment through your account.</p>
                                        
                                        <div class='button-container'>
                                            <a href='https://safespaceph.com/my-account' class='button'>Book Another Appointment</a>
                                        </div>

                                        <p>We appreciate your understanding and remain committed to helping you find the support you need.</p>
                                        <p>Sincerely,<br>The SafeSpace PH Team</p>
                                    </td>
                                </tr>
                                <tr>
                                    <td class='footer'>
                                        <img src='cid:logoimg_footer' alt='SafeSpace PH Logo' style='height: 50px; margin-bottom: 15px;'>
                                        <div style='font-size: 24px; font-weight: 1000;'>SafeSpace PH</div>
                                        <div>
                                           <a href='https://safespaceph.com/about'>About Us</a> | <a href='https://safespaceph.com/contact'>Contact Us</a>
                                        </div>
                                        <p>&copy; " . date('Y') . " SafeSpace PH. All rights reserved.</p>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
            </center>
        </body>
        </html>";

        $mail->send();
        error_log("Detailed appointment cancellation email sent successfully to: $recipientEmail");
        return true;
    } catch (Exception $e) {
        error_log("Failed to send detailed appointment cancellation email to {$recipientEmail}: {$mail->ErrorInfo}");
        throw $e;
    }
}


function sendAppointmentAcceptedNoticeToUser($recipientEmail, $recipientName, $appointmentDetails) {
    $mail = new PHPMailer(true);
    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = $_ENV['EMAIL_USER'];
        $mail->Password = $_ENV['EMAIL_PASS'];
        $mail->SMTPSecure = 'tls';
        $mail->Port = 587;

        // Recipients
        $mail->setFrom($_ENV['EMAIL_USER'], 'SafeSpace PH');
        $mail->addAddress($recipientEmail, $recipientName);

        // Content
        $mail->isHTML(true);
        $mail->Subject = 'Your SafeSpace PH Appointment is Confirmed!';
        // [FIXED] Use __DIR__ to ensure the correct path to images
        $mail->addEmbeddedImage(__DIR__ . '/img/logo.png', 'logoimg');
        $mail->addEmbeddedImage(__DIR__ . '/img/logo.png', 'logoimg_footer');

        // Sanitize and extract details from the $appointmentDetails array
        $lawyerName = htmlspecialchars($appointmentDetails['lawyerName'] ?? 'N/A', ENT_QUOTES, 'UTF-8');
        $appointmentDate = htmlspecialchars($appointmentDetails['appointmentDate'] ?? 'N/A', ENT_QUOTES, 'UTF-8');
        $appointmentTime = htmlspecialchars($appointmentDetails['appointmentTime'] ?? 'N/A', ENT_QUOTES, 'UTF-8');
        $meetingType = htmlspecialchars($appointmentDetails['meetingType'] ?? 'N/A', ENT_QUOTES, 'UTF-8');
        $meetingLink = htmlspecialchars($appointmentDetails['meetingLink'] ?? '#', ENT_QUOTES, 'UTF-8');
        $caseTitle = htmlspecialchars($appointmentDetails['caseTitle'] ?? 'N/A', ENT_QUOTES, 'UTF-8');
        $caseDescription = nl2br(htmlspecialchars($appointmentDetails['caseDescription'] ?? 'N/A', ENT_QUOTES, 'UTF-8'));

        $mail->Body = "
        <!DOCTYPE html>
<html lang='en'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>SafeSpace PH: Your Appointment is Confirmed!</title>
    <style>
        body { font-family: 'Inter', sans-serif; margin: 0; padding: 0; background-color: #f4f4f4; -webkit-text-size-adjust: 100%; -ms-text-size-adjust: 100%; width: 100% !important; }
        table { border-collapse: collapse; mso-table-lspace: 0pt; mso-table-rspace: 0pt; }
        td { padding: 0; }
        img { -ms-interpolation-mode: bicubic; border: 0; height: auto; line-height: 100%; outline: none; text-decoration: none; }
        a { text-decoration: none; color: #8a2be2; }
        .email-container { max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1); }
        .header { background-color: #391053; padding: 20px 30px; text-align: center; color: white; }
        .header-logo { height: 70px; width: auto; vertical-align: middle; margin-right: 15px; }
        .header-title { font-size: 28px; font-weight: 1000; color: #ffffff; white-space: nowrap; text-shadow: 2px 2px 10px rgba(57, 16, 83, 0.3); vertical-align: middle; }
        .content { padding: 30px; color: #333333; line-height: 1.6; font-size: 16px; }
        .content h2 { font-size: 22px; color: #5d00a0; margin-top: 25px; margin-bottom: 15px; }
        .details-list { list-style: none; padding: 15px; margin: 20px 0; background-color: #f9f9f9; border-left: 4px solid #8a2be2; border-radius: 5px; }
        .details-list li { margin-bottom: 10px; }
        .details-list li strong { color: #555; }
        .button-container { text-align: center; margin-top: 30px; margin-bottom: 20px; }
        .button { display: inline-block; background-color: #8a2be2; color: white !important; padding: 12px 25px; border-radius: 25px; font-size: 17px; font-weight: bold; text-decoration: none; transition: background-color 0.3s ease; }
        .button:hover { background-color: #6a0dad; }
        .footer { background-color: #391053; color: white; padding: 20px 30px; text-align: center; font-size: 14px; border-top: 1px solid #5d00a0; }
        .footer-branding { display: flex; justify-content: center; align-items: center; gap: 10px; margin-bottom: 15px; }
        .footer-logo { height: 50px; width: auto; }
        .footer-title { font-size: 24px; font-weight: 1000; color: #ffffff; white-space: nowrap; text-shadow: 2px 2px 10px rgba(57, 16, 83, 0.3); }
        .footer-links-container { margin-bottom: 10px; }
        .footer a { color: #e0caff; text-decoration: none; margin: 0 8px; }
        .footer a:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <center>
        <table role='presentation' cellspacing='0' cellpadding='0' border='0' width='100%' style='background-color: #f4f4f4;'>
            <tr>
                <td align='center' style='padding: 20px 0;'>
                    <table role='presentation' cellspacing='0' cellpadding='0' border='0' width='600' class='email-container'>
                        <tr>
                            <td class='header'>
                                <table role='presentation' cellspacing='0' cellpadding='0' border='0' width='100%'>
                                    <tr>
                                        <td style='text-align: center;'>
                                            <img src=\"cid:logoimg\" alt=\"SafeSpace PH Logo\" style=\"height: 70px; width: auto; vertical-align: middle; margin-right: 15px;\">
                                            <span class='header-title'>SafeSpace PH</span>
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                        <tr>
                            <td class='content'>
                                <p>Dear {$recipientName},</p>
                                <p>Great news! Your <strong>appointment with a lawyer</strong> at SafeSpace PH has been successfully <strong>accepted</strong>.</p>
                                <p>We're pleased to inform you that <strong>Attorney {$lawyerName}</strong> has accepted your request and is ready to assist you.</p>
                                <h2>Your Confirmed Appointment Details:</h2>
                                <ul class='details-list'>
                                    <li><strong>Lawyer:</strong> Attorney {$lawyerName}</li>
                                    <li><strong>Date:</strong> {$appointmentDate}</li>
                                    <li><strong>Time:</strong> {$appointmentTime} (PHT)</li>
                                    <li><strong>Meeting Type:</strong> {$meetingType}</li>
                                    <li><strong>Meeting Link:</strong> <a href='{$meetingLink}' style='color: #8a2be2; word-break: break-all;'>{$meetingLink}</a></li>
                                    <li><strong>Case Title/Concern:</strong> {$caseTitle}</li>
                                    <li><strong>Case Description:</strong><br>{$caseDescription}</li>
                                </ul>
                                <p>Please make sure to click the <strong>meeting link</strong> at the scheduled time to join your consultation. If you have any documents or information relevant to your case, please prepare them beforehand.</p>
                                <p>We encourage you to review the <strong>Safe Spaces Act (RA 11313)</strong> beforehand to better understand your rights. You can find more information on our website.</p>
                                <div class='button-container'>
                                    <a href='https://safespaceph.com/my-appointments' class='button'>View My Appointments</a>
                                </div>
                                <p>If you need to reschedule or cancel your appointment, please do so at least 24 hours in advance through your SafeSpace PH account or by contacting our support team.</p>
                                <p>We look forward to assisting you.</p>
                                <p>Sincerely,<br>The SafeSpace PH Team</p>
                            </td>
                        </tr>
                        <tr>
                            <td class='footer'>
                                <div class='footer-branding'>
                                   <img src=\"cid:logoimg_footer\"alt=\"SafeSpace PH Logo\" style=\"height: 50px; width: auto; vertical-align: middle; margin-right: 15px;\">
                                    <span class='footer-title'>SafeSpace PH</span>
                                </div>
                                <div class='footer-links-container'>
                                    <a href='https://safespaceph.com/about' class='footer-link'>About Us</a>
                                    <a href='https://safespaceph.com/services' class='footer-link'>Our Services</a>
                                    <a href='https://safespaceph.com/contact' class='footer-link'>Contact Us</a>
                                </div>
                                <p>&copy; ".date('Y')." SafeSpace PH. All rights reserved.</p>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </tr>
        </table>
    </center>
</body>
</html>";
        $mail->send();
        error_log("Appointment accepted email sent successfully to: {$recipientEmail}");
        return true;
    } catch (Exception $e) {
        error_log("Appointment accepted email error for {$recipientEmail}: {$mail->ErrorInfo}");
        // [FIXED] Re-throw the exception so the calling script can handle the failure.
        throw $e;
    }
}

function sendVerificationApprovedNoticeToClient($recipientEmail, $recipientName) {
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = $_ENV['EMAIL_USER'];
        $mail->Password = $_ENV['EMAIL_PASS'];
        $mail->SMTPSecure = 'tls';
        $mail->Port = 587;

        $mail->setFrom($_ENV['EMAIL_USER'], 'SafeSpace PH');
        $mail->addAddress($recipientEmail, $recipientName);

        $mail->isHTML(true);
        $mail->Subject = 'Congratulations! Your SafeSpace PH Verification is Approved';
        $mail->addEmbeddedImage(__DIR__ . '/img/logo.png', 'logoimg');
        $mail->addEmbeddedImage(__DIR__ . '/img/logo.png', 'logoimg_footer');

        $mail->Body = "
        <!DOCTYPE html>
<html lang='en'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>SafeSpace PH: Verification Application Approved</title>
    <style>
        body { font-family: 'Inter', sans-serif; margin: 0; padding: 0; background-color: #f4f4f4; -webkit-text-size-adjust: 100%; -ms-text-size-adjust: 100%; width: 100% !important; }
        table { border-collapse: collapse; mso-table-lspace: 0pt; mso-table-rspace: 0pt; }
        td { padding: 0; }
        img { -ms-interpolation-mode: bicubic; border: 0; height: auto; line-height: 100%; outline: none; text-decoration: none; }
        a { text-decoration: none; color: #8a2be2; }
        .email-container { max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1); }
        .header { background-color: #391053; padding: 20px 30px; text-align: center; color: white; }
        .header-logo { height: 70px; width: auto; vertical-align: middle; margin-right: 15px; }
        .header-title { font-size: 28px; font-weight: 1000; color: #ffffff; white-space: nowrap; text-shadow: 2px 2px 10px rgba(57, 16, 83, 0.3); vertical-align: middle; }
        .content { padding: 30px; color: #333333; line-height: 1.6; font-size: 16px; }
        .content h2 { font-size: 22px; color: #5d00a0; margin-top: 25px; margin-bottom: 15px; }
        .content ul { list-style: none; padding: 0; margin: 0; }
        .content ul li { margin-bottom: 10px; padding-left: 25px; position: relative; }
        .content ul li:before { content: '✔'; color: #28a745; font-size: 20px; position: absolute; left: 0; top: -2px; }
        .button-container { text-align: center; margin-top: 30px; margin-bottom: 20px; }
        .button { display: inline-block; background-color: #8a2be2; color: #ffffff !important; padding: 12px 25px; border-radius: 25px; font-size: 17px; font-weight: bold; text-decoration: none; transition: background-color 0.3s ease; }
        .button:hover { background-color: #6a0dad; }
        .footer { background-color: #391053; color: white; padding: 20px 30px; text-align: center; font-size: 14px; border-top: 1px solid #5d00a0; }
        .footer-branding { display: flex; justify-content: center; align-items: center; gap: 10px; margin-bottom: 15px; }
        .footer-logo { height: 50px; width: auto; }
        .footer-title { font-size: 24px; font-weight: 1000; color: #ffffff; white-space: nowrap; text-shadow: 2px 2px 10px rgba(57, 16, 83, 0.3); }
        .footer-links-container { margin-bottom: 10px; }
        .footer a { color: #e0caff; text-decoration: none; margin: 0 8px; }
        .footer a:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <center>
        <table role='presentation' cellspacing='0' cellpadding='0' border='0' width='100%' style='background-color: #f4f4f4;'>
            <tr>
                <td align='center' style='padding: 20px 0;'>
                    <table role='presentation' cellspacing='0' cellpadding='0' border='0' width='600' class='email-container'>
                        <tr>
                            <td class='header'>
                                <table role='presentation' cellspacing='0' cellpadding='0' border='0' width='100%'>
                                    <tr>
                                        <td style='text-align: center;'>
                                            <img src=\"cid:logoimg\" alt=\"SafeSpace PH Logo\" style=\"height: 70px; width: auto; vertical-align: middle; margin-right: 15px;\">
                                            <span class='header-title'>SafeSpace PH</span>
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                        <tr>
                            <td class='content'>
                                <p>Dear {$recipientName},</p>
                                <p>Great news! Your identity verification with SafeSpace PH has been successfully reviewed and <strong>approved</strong>.</p>
                                <p>You are now a verified client and have full access to our platform's features, including the ability to book pro bono appointments with our volunteer lawyers.</p>
                                <h2>What you can do next:</h2>
                                <ul>
                                    <li>Explore our network of legal professionals.</li>
                                    <li>Book a consultation for your legal concerns.</li>
                                    <li>Access resources about the Safe Spaces Act.</li>
                                </ul>
                                <p>We are here to support you on your journey to justice and empowerment.</p>
                                <div class='button-container'>
                                    <a href='https://safespaceph.com/appointment' class='button'>Book an Appointment</a>
                                </div>
                                <p>Thank you for your trust in us.</p>
                                <p>Sincerely,<br>The SafeSpace PH Team</p>
                            </td>
                        </tr>
                        <tr>
                            <td class='footer'>
                                <div class='footer-branding'>
                                   <img src=\"cid:logoimg_footer\"alt=\"SafeSpace PH Logo\" style=\"height: 50px; width: auto; vertical-align: middle; margin-right: 15px;\">
                                    <span class='footer-title'>SafeSpace PH</span>
                                </div>
                                <div class='footer-links-container'>
                                    <a href='https://safespaceph.com/about' class='footer-link'>About Us</a>
                                    <a href='https://safespaceph.com/services' class='footer-link'>Our Services</a>
                                    <a href='https://safespaceph.com/contact' class='footer-link'>Contact Us</a>
                                </div>
                                <p>&copy; ".date('Y')." SafeSpace PH. All rights reserved.</p>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </center>
</body>
</html>";
        $mail->send();
    } catch (Exception $e) {
        error_log("Verification approval email error for {$recipientEmail}: {$mail->ErrorInfo}");
    }
}


function sendVerificationRejectedNoticeToClient($recipientEmail, $recipientName, $reasonDetails = '') {
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = $_ENV['EMAIL_USER'];
        $mail->Password = $_ENV['EMAIL_PASS'];
        $mail->SMTPSecure = 'tls';
        $mail->Port = 587;

        $mail->setFrom($_ENV['EMAIL_USER'], 'SafeSpace PH');
        $mail->addAddress($recipientEmail, $recipientName);

        $mail->isHTML(true);
        $mail->Subject = 'Important: Your SafeSpace PH Verification Application Update';
        $mail->addEmbeddedImage(__DIR__ . '/img/logo.png', 'logoimg');
        $mail->addEmbeddedImage(__DIR__ . '/img/logo.png', 'logoimg_footer');

        $recipientName_safe = htmlspecialchars($recipientName, ENT_QUOTES, 'UTF-8');
        $rejectionReasonHtml = '';
        if (!empty($reasonDetails)) {
            // Sanitize the custom reason and format it within a styled list item
            $reasonDetails_safe = htmlspecialchars($reasonDetails, ENT_QUOTES, 'UTF-8');
            $rejectionReasonHtml = "<p>Our team noted the following specific reason for this decision:</p><ul class='details-list'><li>{$reasonDetails_safe}</li></ul>";
        }

        $mail->Body = "
        <!DOCTYPE html>
        <html lang='en'>
        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <title>SafeSpace PH: Verification Application Update</title>
            <style>
                body { font-family: 'Inter', sans-serif; margin: 0; padding: 0; background-color: #f4f4f4; -webkit-text-size-adjust: 100%; -ms-text-size-adjust: 100%; width: 100% !important; }
                table { border-collapse: collapse; mso-table-lspace: 0pt; mso-table-rspace: 0pt; }
                td { padding: 0; }
                img { -ms-interpolation-mode: bicubic; border: 0; height: auto; line-height: 100%; outline: none; text-decoration: none; }
                a { text-decoration: none; color: #8a2be2; }
                .email-container { max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1); }
                .header { background-color: #391053; padding: 20px 30px; text-align: center; color: white; }
                .header-logo { height: 70px; width: auto; vertical-align: middle; margin-right: 15px; }
                .header-title { font-size: 28px; font-weight: 1000; color: #ffffff; white-space: nowrap; text-shadow: 2px 2px 10px rgba(57, 16, 83, 0.3); vertical-align: middle; }
                .content { padding: 30px; color: #333333; line-height: 1.6; font-size: 16px; }
                .content h2 { font-size: 22px; color: #5d00a0; margin-top: 25px; margin-bottom: 15px; }
                .content ul { list-style: none; padding: 0; margin: 0; }
                .content ul:not(.details-list) li { margin-bottom: 10px; padding-left: 25px; position: relative; }
                .content ul:not(.details-list) li:before { content: '•'; color: #8a2be2; font-size: 20px; position: absolute; left: 0; top: -2px; }
                .details-list { list-style: none; padding: 15px; margin: 20px 0; background-color: #fff0f0; border-left: 4px solid #d9534f; border-radius: 5px; color: #721c24; }
                .details-list li { margin-bottom: 0; }
                .button-container { text-align: center; margin-top: 30px; margin-bottom: 20px; }
                .button { display: inline-block; background-color: #8a2be2; color: #ffffff !important; padding: 12px 25px; border-radius: 25px; font-size: 17px; font-weight: bold; text-decoration: none; transition: background-color 0.3s ease; }
                .button:hover { background-color: #6a0dad; }
                .footer { background-color: #391053; color: white; padding: 20px 30px; text-align: center; font-size: 14px; border-top: 1px solid #5d00a0; }
                .footer-branding { display: flex; justify-content: center; align-items: center; gap: 10px; margin-bottom: 15px; }
                .footer-logo { height: 50px; width: auto; }
                .footer-title { font-size: 24px; font-weight: 1000; color: #ffffff; white-space: nowrap; text-shadow: 2px 2px 10px rgba(57, 16, 83, 0.3); }
                .footer-links-container { margin-bottom: 10px; }
                .footer a { color: #e0caff; text-decoration: none; margin: 0 8px; }
                .footer a:hover { text-decoration: underline; }
            </style>
        </head>
        <body>
            <center>
                <table role='presentation' cellspacing='0' cellpadding='0' border='0' width='100%' style='background-color: #f4f4f4;'>
                    <tr>
                        <td align='center' style='padding: 20px 0;'>
                            <table role='presentation' cellspacing='0' cellpadding='0' border='0' width='600' class='email-container'>
                                <tr>
                                    <td class='header'>
                                        <table role='presentation' cellspacing='0' cellpadding='0' border='0' width='100%'>
                                            <tr>
                                                <td style='text-align: center;'>
                                                    <img src=\"cid:logoimg\" alt=\"SafeSpace PH Logo\" style=\"height: 70px; width: auto; vertical-align: middle; margin-right: 15px;\">
                                                    <span class='header-title'>SafeSpace PH</span>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                                <tr>
                                    <td class='content'>
                                        <p>Dear {$recipientName_safe},</p>
                                        <p>We are writing to inform you that after a careful review, your <strong>identity verification application</strong> with SafeSpace PH could not be approved at this time.</p>
                                        <p>Ensuring the security and integrity of our platform for all users is our highest priority, and this decision was made in accordance with our verification standards.</p>
                                        
                                        {$rejectionReasonHtml}

                                        <h2>Common Reasons for Rejection</h2>
                                        <p>This may have been due to one or more of the following common factors:</p>
                                        <ul>
                                            <li><strong>Document Clarity:</strong> Submitted documents or images were blurry, expired, or otherwise illegible.</li>
                                            <li><strong>Information Mismatch:</strong> The name or other details provided did not fully match the submitted documents.</li>
                                            <li><strong>Incomplete Submission:</strong> One or more required documents or pieces of information were missing.</li>
                                        </ul>
                                        <p>We understand this may be disappointing. If you believe there has been a mistake or if you have new documents to provide, you are welcome to submit a new application.</p>
                                        <div class='button-container'>
                                            <a href='https://safespaceph.com/my-account' class='button'>Submit a New Application</a>
                                        </div>
                                        <p>If you have any questions, please don't hesitate to contact our support team for further assistance.</p>
                                        <p>Thank you for your understanding.</p>
                                        <p>Sincerely,<br>The SafeSpace PH Team</p>
                                    </td>
                                </tr>
                                <tr>
                                    <td class='footer'>
                                        <div class='footer-branding'>
                                           <img src=\"cid:logoimg_footer\" alt=\"SafeSpace PH Logo\" style=\"height: 50px; width: auto; vertical-align: middle; margin-right: 15px;\">
                                            <span class='footer-title'>SafeSpace PH</span>
                                        </div>
                                        <div class='footer-links-container'>
                                            <a href='https://safespaceph.com/about' class='footer-link'>About Us</a>
                                            <a href='https://safespaceph.com/services' class='footer-link'>Our Services</a>
                                            <a href='https://safespaceph.com/contact' class='footer-link'>Contact Us</a>
                                        </div>
                                        <p>&copy; " . date('Y') . " SafeSpace PH. All rights reserved.</p>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
            </center>
        </body>
        </html>";
        $mail->send();
        error_log("Verification rejection email sent successfully to: {$recipientEmail}");
    } catch (Exception $e) {
        error_log("Verification rejection email error for {$recipientEmail}: {$mail->ErrorInfo}");
        // Re-throw the exception to allow the caller to handle the failure gracefully
        throw $e;
    }
}

/**
 * Sends an email to the client to notify them that their verification has been revoked.
 *
 * @param string $recipientEmail The client's email address.
 * @param string $recipientName The client's full name.
 */
function sendUnverificationNoticeEmail($recipientEmail, $recipientName) {
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = $_ENV['EMAIL_USER'];
        $mail->Password = $_ENV['EMAIL_PASS'];
        $mail->SMTPSecure = 'tls';
        $mail->Port = 587;

        $mail->setFrom($_ENV['EMAIL_USER'], 'SafeSpace PH');
        $mail->addAddress($recipientEmail, $recipientName);

        $mail->isHTML(true);
        $mail->Subject = 'Important: Your SafeSpace PH Verification Has Been Revoked';
        $mail->addEmbeddedImage(__DIR__ . '/img/logo.png', 'logoimg');
        $mail->addEmbeddedImage(__DIR__ . '/img/logo.png', 'logoimg_footer');

        $recipientName_safe = htmlspecialchars($recipientName, ENT_QUOTES, 'UTF-8');

        $mail->Body = "
        <!DOCTYPE html>
        <html lang='en'>
        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <title>SafeSpace PH: Verification Status Update</title>
            <style>
                body { font-family: 'Inter', sans-serif; margin: 0; padding: 0; background-color: #f4f4f4; -webkit-text-size-adjust: 100%; -ms-text-size-adjust: 100%; width: 100% !important; }
                table { border-collapse: collapse; mso-table-lspace: 0pt; mso-table-rspace: 0pt; }
                td { padding: 0; }
                img { -ms-interpolation-mode: bicubic; border: 0; height: auto; line-height: 100%; outline: none; text-decoration: none; }
                a { text-decoration: none; color: #8a2be2; }
                .email-container { max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1); }
                .header { background-color: #391053; padding: 20px 30px; text-align: center; color: white; }
                .header-logo { height: 70px; width: auto; vertical-align: middle; margin-right: 15px; }
                .header-title { font-size: 28px; font-weight: 1000; color: #ffffff; white-space: nowrap; text-shadow: 2px 2px 10px rgba(57, 16, 83, 0.3); vertical-align: middle; }
                .content { padding: 30px; color: #333333; line-height: 1.6; font-size: 16px; }
                .content h2 { font-size: 22px; color: #d9534f; margin-top: 25px; margin-bottom: 15px; }
                .button-container { text-align: center; margin-top: 30px; margin-bottom: 20px; }
                .button { display: inline-block; background-color: #8a2be2; color: #ffffff !important; padding: 12px 25px; border-radius: 25px; font-size: 17px; font-weight: bold; text-decoration: none; transition: background-color 0.3s ease; }
                .button:hover { background-color: #6a0dad; }
                .footer { background-color: #391053; color: white; padding: 20px 30px; text-align: center; font-size: 14px; border-top: 1px solid #5d00a0; }
                .footer-branding { display: flex; justify-content: center; align-items: center; gap: 10px; margin-bottom: 15px; }
                .footer-logo { height: 50px; width: auto; }
                .footer-title { font-size: 24px; font-weight: 1000; color: #ffffff; white-space: nowrap; text-shadow: 2px 2px 10px rgba(57, 16, 83, 0.3); }
                .footer-links-container { margin-bottom: 10px; }
                .footer a { color: #e0caff; text-decoration: none; margin: 0 8px; }
                .footer a:hover { text-decoration: underline; }
            </style>
        </head>
        <body>
            <center>
                <table role='presentation' cellspacing='0' cellpadding='0' border='0' width='100%' style='background-color: #f4f4f4;'>
                    <tr>
                        <td align='center' style='padding: 20px 0;'>
                            <table role='presentation' cellspacing='0' cellpadding='0' border='0' width='600' class='email-container'>
                                <tr>
                                    <td class='header'>
                                        <table role='presentation' cellspacing='0' cellpadding='0' border='0' width='100%'>
                                            <tr>
                                                <td style='text-align: center;'>
                                                    <img src=\"cid:logoimg\" alt=\"SafeSpace PH Logo\" style=\"height: 70px; width: auto; vertical-align: middle; margin-right: 15px;\">
                                                    <span class='header-title'>SafeSpace PH</span>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                                <tr>
                                    <td class='content'>
                                        <h2>Verification Status Revoked</h2>
                                        <p>Dear {$recipientName_safe},</p>
                                        <p>We are writing to inform you that your verification status on the SafeSpace PH platform has been <strong>revoked</strong> by an administrator.</p>
                                        <p>This means your account is no longer considered verified. As a result, you will lose access to features that require identity verification, such as booking appointments with legal professionals, until your account is re-verified.</p>
                                        <p>If you believe this was done in error or have questions regarding this action, please contact our support team immediately for clarification.</p>
                                        <div class='button-container'>
                                            <a href='https://safespaceph.com/contact' class='button'>Contact Support</a>
                                        </div>
                                        <p>Thank you for your understanding.</p>
                                        <p>Sincerely,<br>The SafeSpace PH Team</p>
                                    </td>
                                </tr>
                                <tr>
                                    <td class='footer'>
                                        <div class='footer-branding'>
                                           <img src=\"cid:logoimg_footer\" alt=\"SafeSpace PH Logo\" style=\"height: 50px; width: auto; vertical-align: middle; margin-right: 15px;\">
                                            <span class='footer-title'>SafeSpace PH</span>
                                        </div>
                                        <div class='footer-links-container'>
                                            <a href='https://safespaceph.com/about' class='footer-link'>About Us</a>
                                            <a href='https://safespaceph.com/services' class='footer-link'>Our Services</a>
                                            <a href='https://safespaceph.com/contact' class='footer-link'>Contact Us</a>
                                        </div>
                                        <p>&copy; " . date('Y') . " SafeSpace PH. All rights reserved.</p>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
            </center>
        </body>
        </html>";
        $mail->send();
        error_log("Unverification notice sent successfully to: {$recipientEmail}");
    } catch (Exception $e) {
        error_log("Unverification notice email error for {$recipientEmail}: {$mail->ErrorInfo}");
        // Re-throw the exception to allow the caller to handle the failure gracefully
        throw $e;
    }
}

function sendMeetingLinkUpdateNoticeToUser($recipientEmail, $recipientName, $appointmentDetails) {
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = $_ENV['EMAIL_USER'];
        $mail->Password = $_ENV['EMAIL_PASS'];
        $mail->SMTPSecure = 'tls';
        $mail->Port = 587;

        $mail->setFrom($_ENV['EMAIL_USER'], 'SafeSpace PH');
        $mail->addAddress($recipientEmail, $recipientName);

        $mail->isHTML(true);
        $mail->Subject = 'Important: Your SafeSpace PH Meeting Link Has Been Updated';
        $mail->addEmbeddedImage(__DIR__ . '/img/logo.png', 'logoimg');
        $mail->addEmbeddedImage(__DIR__ . '/img/logo.png', 'logoimg_footer');

        // Extracting and sanitizing details from the $appointmentDetails array
        $recipientName_safe = htmlspecialchars($recipientName, ENT_QUOTES, 'UTF-8');
        $lawyerName_safe = htmlspecialchars($appointmentDetails['lawyerName'] ?? 'N/A', ENT_QUOTES, 'UTF-8');
        $appointmentDate_safe = htmlspecialchars($appointmentDetails['appointmentDate'] ?? 'N/A', ENT_QUOTES, 'UTF-8');
        $appointmentTime_safe = htmlspecialchars($appointmentDetails['appointmentTime'] ?? 'N/A', ENT_QUOTES, 'UTF-8');
        $oldMeetingLink_safe = htmlspecialchars($appointmentDetails['oldMeetingLink'] ?? 'N/A', ENT_QUOTES, 'UTF-8');
        $newMeetingLink_safe = htmlspecialchars($appointmentDetails['newMeetingLink'] ?? '#', ENT_QUOTES, 'UTF-8');
        $caseTitle_safe = htmlspecialchars($appointmentDetails['caseTitle'] ?? 'N/A', ENT_QUOTES, 'UTF-8');

        $mail->Body = "
        <!DOCTYPE html>
<html lang='en'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>SafeSpace PH: Meeting Link Updated</title>
    <style>
        body { font-family: 'Inter', sans-serif; margin: 0; padding: 0; background-color: #f4f4f4; -webkit-text-size-adjust: 100%; -ms-text-size-adjust: 100%; width: 100% !important; }
        table { border-collapse: collapse; mso-table-lspace: 0pt; mso-table-rspace: 0pt; }
        td { padding: 0; }
        img { -ms-interpolation-mode: bicubic; border: 0; height: auto; line-height: 100%; outline: none; text-decoration: none; }
        a { text-decoration: none; color: #8a2be2; }
        .email-container { max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1); }
        .header { background-color: #391053; padding: 20px 30px; text-align: center; color: white; }
        .header-logo { height: 70px; width: auto; vertical-align: middle; margin-right: 15px; }
        .header-title { font-size: 28px; font-weight: 1000; color: #ffffff; white-space: nowrap; text-shadow: 2px 2px 10px rgba(57, 16, 83, 0.3); vertical-align: middle; }
        .content { padding: 30px; color: #333333; line-height: 1.6; font-size: 16px; }
        .content h2 { font-size: 22px; color: #5d00a0; margin-top: 25px; margin-bottom: 15px; }
        .details-list { list-style: none; padding: 15px; margin: 20px 0; background-color: #f9f9f9; border-left: 4px solid #8a2be2; border-radius: 5px; }
        .details-list li { margin-bottom: 10px; }
        .details-list li strong { color: #555; }
        .button-container { text-align: center; margin-top: 30px; margin-bottom: 20px; }
        .button { display: inline-block; background-color: #8a2be2; color: white !important; padding: 12px 25px; border-radius: 25px; font-size: 17px; font-weight: bold; text-decoration: none; transition: background-color 0.3s ease; }
        .button:hover { background-color: #6a0dad; }
        .footer { background-color: #391053; color: white; padding: 20px 30px; text-align: center; font-size: 14px; border-top: 1px solid #5d00a0; }
        .footer-branding { display: flex; justify-content: center; align-items: center; gap: 10px; margin-bottom: 15px; }
        .footer-logo { height: 50px; width: auto; }
        .footer-title { font-size: 24px; font-weight: 1000; color: #ffffff; white-space: nowrap; text-shadow: 2px 2px 10px rgba(57, 16, 83, 0.3); }
        .footer-links-container { margin-bottom: 10px; }
        .footer a { color: #e0caff; text-decoration: none; margin: 0 8px; }
        .footer a:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <center>
        <table role='presentation' cellspacing='0' cellpadding='0' border='0' width='100%' style='background-color: #f4f4f4;'>
            <tr>
                <td align='center' style='padding: 20px 0;'>
                    <table role='presentation' cellspacing='0' cellpadding='0' border='0' width='600' class='email-container'>
                        <tr>
                            <td class='header'>
                                <table role='presentation' cellspacing='0' cellpadding='0' border='0' width='100%'>
                                    <tr>
                                        <td style='text-align: center;'>
                                            <img src=\"cid:logoimg\" alt=\"SafeSpace PH Logo\" style=\"height: 70px; width: auto; vertical-align: middle; margin-right: 15px;\">
                                            <span class='header-title'>SafeSpace PH</span>
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                        <tr>
                            <td class='content'>
                                <p>Dear {$recipientName_safe},</p>
                                <p>This is an important notification regarding your upcoming appointment with <strong>Attorney {$lawyerName_safe}</strong>.</p>
                                <p>The meeting link for your consultation has been updated by the lawyer. Please use the <strong>new link</strong> provided below to join your meeting at the scheduled time.</p>
                                
                                <h2>Appointment Details Update:</h2>
                                <ul class='details-list'>
                                    <li><strong>Lawyer:</strong> Attorney {$lawyerName_safe}</li>
                                    <li><strong>Date:</strong> {$appointmentDate_safe}</li>
                                    <li><strong>Time:</strong> {$appointmentTime_safe} (PHT)</li>
                                    <li><strong>Case Title:</strong> {$caseTitle_safe}</li>
                                    <li style='color: #777;'><strong>Old Meeting Link:</strong> <span style='text-decoration: line-through;'>{$oldMeetingLink_safe}</span> (No longer valid)</li>
                                    <li><strong>New Meeting Link:</strong> <a href='{$newMeetingLink_safe}' style='color: #8a2be2; font-weight: bold; word-break: break-all;'>{$newMeetingLink_safe}</a></li>
                                </ul>
                                
                                <p>Please ensure you use this new link to avoid any issues joining your consultation. We recommend updating any calendar invites you may have created.</p>
                                
                                <div class='button-container'>
                                    <a href='https://safespaceph.com/my-appointments' class='button'>View My Appointments</a>
                                </div>
                                
                                <p>We apologize for any inconvenience this may cause and look forward to assisting you.</p>
                                <p>Sincerely,<br>The SafeSpace PH Team</p>
                            </td>
                        </tr>
                        <tr>
                            <td class='footer'>
                                <div class='footer-branding'>
                                   <img src=\"cid:logoimg_footer\"alt=\"SafeSpace PH Logo\" style=\"height: 50px; width: auto; vertical-align: middle; margin-right: 15px;\">
                                    <span class='footer-title'>SafeSpace PH</span>
                                </div>
                                <div class='footer-links-container'>
                                    <a href='https://safespaceph.com/about' class='footer-link'>About Us</a>
                                    <a href='https://safespaceph.com/services' class='footer-link'>Our Services</a>
                                    <a href='https://safespaceph.com/contact' class='footer-link'>Contact Us</a>
                                </div>
                                <p>&copy; " . date('Y') . " SafeSpace PH. All rights reserved.</p>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </tr>
        </table>
    </center>
</body>
</html>";
        $mail->send();
        error_log("Meeting link update email sent successfully to: {$recipientEmail}");
        return true;
    } catch (Exception $e) {
        error_log("Meeting link update email error for {$recipientEmail}: {$mail->ErrorInfo}");
        // Re-throw the exception to allow the calling script to handle the failure (e.g., rollback a transaction)
        throw $e;
    }
}

function sendGoodbyeEmailOnAccountDeletion($recipientEmail, $recipientName) {
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = $_ENV['EMAIL_USER'];
        $mail->Password = $_ENV['EMAIL_PASS'];
        $mail->SMTPSecure = 'tls';
        $mail->Port = 587;

        $mail->setFrom($_ENV['EMAIL_USER'], 'SafeSpace PH');
        $mail->addAddress($recipientEmail, $recipientName);

        $mail->isHTML(true);
        $mail->Subject = 'Your SafeSpace PH Account is Scheduled for Deletion';
        $mail->addEmbeddedImage(__DIR__ . '/img/logo.png', 'logoimg');
        $mail->addEmbeddedImage(__DIR__ . '/img/logo.png', 'logoimg_footer');

        $mail->Body = "
        <!DOCTYPE html>
        <html lang='en'>
        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <title>SafeSpace PH: Account Deletion Request</title>
            <style>
                 /* Styles remain the same as the previously provided email template */
                body { font-family: 'Inter', sans-serif; margin: 0; padding: 0; background-color: #f4f4f4; -webkit-text-size-adjust: 100%; -ms-text-size-adjust: 100%; width: 100% !important; }
                table { border-collapse: collapse; mso-table-lspace: 0pt; mso-table-rspace: 0pt; }
                td { padding: 0; }
                img { -ms-interpolation-mode: bicubic; border: 0; height: auto; line-height: 100%; outline: none; text-decoration: none; }
                a { text-decoration: none; color: #8a2be2; }
                .email-container { max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1); }
                .header { background-color: #391053; padding: 20px 30px; text-align: center; color: white; }
                .header-logo { height: 70px; width: auto; vertical-align: middle; margin-right: 15px; }
                .header-title { font-size: 28px; font-weight: 1000; color: #ffffff; white-space: nowrap; text-shadow: 2px 2px 10px rgba(57, 16, 83, 0.3); vertical-align: middle; }
                .content { padding: 30px; color: #333333; line-height: 1.6; font-size: 16px; }
                .content p { margin: 0 0 15px; }
                .footer { background-color: #391053; color: white; padding: 20px 30px; text-align: center; font-size: 14px; border-top: 1px solid #5d00a0; }
                .footer-branding { display: flex; justify-content: center; align-items: center; gap: 10px; margin-bottom: 15px; }
                .footer-logo { height: 50px; width: auto; }
                .footer-title { font-size: 24px; font-weight: 1000; color: #ffffff; white-space: nowrap; text-shadow: 2px 2px 10px rgba(57, 16, 83, 0.3); }
                .footer-links-container { margin-bottom: 10px; }
                .footer a { color: #e0caff; text-decoration: none; margin: 0 8px; }
                .footer a:hover { text-decoration: underline; }
            </style>
        </head>
        <body>
            <center>
                <table role='presentation' cellspacing='0' cellpadding='0' border='0' width='100%' style='background-color: #f4f4f4;'>
                    <tr>
                        <td align='center' style='padding: 20px 0;'>
                            <table role='presentation' cellspacing='0' cellpadding='0' border='0' width='600' class='email-container'>
                                <tr>
                                    <td class='header'>
                                       <img src='cid:logoimg' alt='SafeSpace PH Logo' style='height: 70px; width: auto; vertical-align: middle; margin-right: 15px;'>
                                       <span class='header-title'>SafeSpace PH</span>
                                    </td>
                                </tr>
                                <tr>
                                    <td class='content'>
                                        <p>Dear {$recipientName},</p>
                                        <p>We've received your request to delete your SafeSpace PH account. We are sorry to see you go.</p>
                                        <p>Your account has been deactivated and is now scheduled for permanent deletion in <strong>48 hours</strong>. During this time, your profile will not be visible on the platform.</p>
                                        <p>If you've made a mistake or wish to restore your account, please <strong>contact the SafeSpace PH support team</strong> by replying to this email within the next 48 hours.</p>
                                        <p>If we do not hear from you, your account and all associated data will be permanently deleted. This action cannot be undone.</p>
                                        <p>Thank you for being a part of our community.</p>
                                        <p>Sincerely,<br>The SafeSpace PH Team</p>
                                    </td>
                                </tr>
                                <tr>
                                    <td class='footer'>
                                        <div class='footer-branding'>
                                           <img src='cid:logoimg_footer' alt='SafeSpace PH Logo' style='height: 50px; width: auto; vertical-align: middle; margin-right: 15px;'>
                                            <span class='footer-title'>SafeSpace PH</span>
                                        </div>
                                        <div class='footer-links-container'>
                                            <a href='https://safespaceph.com/about' class='footer-link'>About Us</a>
                                            <a href='https://safespaceph.com/services' class='footer-link'>Our Services</a>
                                            <a href='https://safespaceph.com/contact' class='footer-link'>Contact Us</a>
                                        </div>
                                        <p>&copy; ".date('Y')." SafeSpace PH. All rights reserved.</p>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
            </center>
        </body>
        </html>";
        $mail->send();
    } catch (Exception $e) {
        error_log("Account deletion goodbye email error for {$recipientEmail}: {$mail->ErrorInfo}");
    }
}
?>