<?php
require 'vendor/autoload.php'; // Composer autoloader
require 'phpmailer/PHPMailer.php';
require 'phpmailer/SMTP.php';
require 'phpmailer/Exception.php';

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
        $mail->addEmbeddedImage('img/logo.png', 'logoimg');
        $mail->addEmbeddedImage('img/logo.png', 'logoimg_footer');
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
        $mail->addEmbeddedImage('img/logo.png', 'logoimg');
        $mail->addEmbeddedImage('img/logo.png', 'logoimg_footer');
        
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

