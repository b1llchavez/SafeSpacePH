<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

/**
 * Sends an email to a lawyer notifying them that their verification has been approved.
 * Includes their temporary login credentials.
 *
 * @param string $recipientEmail The lawyer's email address.
 * @param string $recipientName The lawyer's full name.
 * @param string $temporaryPassword The generated temporary password for the lawyer.
 * @return void
 */
function sendLawyerVerificationApprovedNotice($recipientEmail, $recipientName, $temporaryPassword) {
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
        $mail->Subject = 'Congratulations! Your SafeSpace PH Lawyer Account is Ready';
        $mail->addEmbeddedImage(__DIR__ . '/img/logo.png', 'logoimg');
        $mail->addEmbeddedImage(__DIR__ . '/img/logo.png', 'logoimg_footer');

        $recipientName_safe = htmlspecialchars($recipientName, ENT_QUOTES, 'UTF-8');
        $temporaryPassword_safe = htmlspecialchars($temporaryPassword, ENT_QUOTES, 'UTF-8');

        $mail->Body = "
        <!DOCTYPE html>
        <html lang='en'>
        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <title>SafeSpace PH: Lawyer Verification Approved</title>
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
                .credentials-box { background-color: #f7f4fd; border: 1px solid #e2d8fa; border-radius: 8px; padding: 20px; margin: 25px 0; text-align: left; }
                .credentials-box p { margin: 8px 0; font-size: 16px; }
                .credentials-box strong { color: #391053; font-weight: 600; }
                .credentials-box span { font-family: 'Courier New', Courier, monospace; background: #e9e0f9; padding: 3px 8px; border-radius: 4px; font-weight: bold; }
                .security-note { font-size: 14px; color: #d9534f; margin-top: 15px; text-align: center; }
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
                                        <img src='cid:logoimg' alt='SafeSpace PH Logo' style='height: 70px; width: auto; vertical-align: middle; margin-right: 15px;'>
                                        <span class='header-title'>SafeSpace PH</span>
                                    </td>
                                </tr>
                                <tr>
                                    <td class='content'>
                                        <p>Dear Atty. {$recipientName_safe},</p>
                                        <p>We are delighted to inform you that your application as a volunteer lawyer on SafeSpace PH has been <strong>approved</strong>. An account has been created for you. Welcome to our network of legal professionals dedicated to making a difference!</p>
                                        
                                        <h2>Your Login Credentials</h2>
                                        <div class='credentials-box'>
                                            <p><strong>Login Email:</strong> <span>{$recipientEmail}</span></p>
                                            <p><strong>Temporary Password:</strong> <span>{$temporaryPassword_safe}</span></p>
                                            <p class='security-note'>For your security, please change this password immediately after your first login.</p>
                                        </div>
                                        
                                        <p>Your profile is now active, and you can begin receiving appointment requests. You can manage your appointments and availability through your dashboard.</p>
                                        
                                        <div class='button-container'>
                                            <a href='https://safespaceph.com/login.php' class='button'>Login to My Dashboard</a>
                                        </div>
                                        <p>Thank you for joining our cause.</p>
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
        error_log("Lawyer verification approval email sent successfully to: {$recipientEmail}");
    } catch (Exception $e) {
        error_log("Lawyer verification approval email error for {$recipientEmail}: {$mail->ErrorInfo}");
        throw $e;
    }
}


/**
 * Sends an email to a lawyer notifying them that their verification has been rejected.
 *
 * @param string $recipientEmail The lawyer's email address.
 * @param string $recipientName The lawyer's full name.
 * @param string $reasonDetails A specific reason for the rejection (optional).
 * @return void
 */
function sendLawyerVerificationRejectedNotice($recipientEmail, $recipientName, $reasonDetails = '') {
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
        $mail->Subject = 'Important: Your SafeSpace PH Lawyer Verification Update';
        $mail->addEmbeddedImage(__DIR__ . '/img/logo.png', 'logoimg');
        $mail->addEmbeddedImage(__DIR__ . '/img/logo.png', 'logoimg_footer');

        $recipientName_safe = htmlspecialchars($recipientName, ENT_QUOTES, 'UTF-8');
        $rejectionReasonHtml = '';
        if (!empty($reasonDetails)) {
            $reasonDetails_safe = nl2br(htmlspecialchars($reasonDetails, ENT_QUOTES, 'UTF-8'));
            $rejectionReasonHtml = "<p>Our team noted the following specific reason for this decision:</p><ul class='details-list'><li>{$reasonDetails_safe}</li></ul>";
        }

        $mail->Body = "
        <!DOCTYPE html>
        <html lang='en'>
        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <title>SafeSpace PH: Lawyer Verification Update</title>
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
                .content ul:not(.details-list) { list-style: none; padding: 0; margin: 0; }
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
                                        <img src='cid:logoimg' alt='SafeSpace PH Logo' style='height: 70px; width: auto; vertical-align: middle; margin-right: 15px;'>
                                        <span class='header-title'>SafeSpace PH</span>
                                    </td>
                                </tr>
                                <tr>
                                    <td class='content'>
                                        <p>Dear Atty. {$recipientName_safe},</p>
                                        <p>We are writing to inform you that after a careful review, your <strong>lawyer verification application</strong> with SafeSpace PH could not be approved at this time.</p>
                                        <p>To maintain the highest standards of trust and safety on our platform, all volunteer lawyers must meet our verification criteria.</p>
                                        
                                        {$rejectionReasonHtml}

                                        <h2>Common Reasons for Rejection</h2>
                                        <p>While we cannot provide specific details for privacy reasons, applications are commonly rejected for one or more of the following:</p>
                                        <ul>
                                            <li><strong>Document Clarity:</strong> Submitted ID or legal documents were blurry, expired, or illegible.</li>
                                            <li><strong>Information Mismatch:</strong> The name or IBP number provided did not match the submitted documents.</li>
                                            <li><strong>Incomplete Application:</strong> Required fields in the application form were not filled out.</li>
                                        </ul>
                                        <p>We understand this may be disappointing. If you believe there has been an error or you have updated information, you are welcome to submit a new application in the future.</p>
                                        
                                        <p>If you have questions, please contact our support team for assistance.</p>
                                        <p>Thank you for your understanding and interest in supporting our cause.</p>
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
        error_log("Lawyer verification rejection email sent successfully to: {$recipientEmail}");
    } catch (Exception $e) {
        error_log("Lawyer verification rejection email error for {$recipientEmail}: {$mail->ErrorInfo}");
        throw $e;
    }
}

?>