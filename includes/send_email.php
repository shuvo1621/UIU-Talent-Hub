<?php
/**
 * Email Sending Function for UIU TalentHub
 * Sends OTP verification emails using PHPMailer
 */

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/email_config.php';

/**
 * Send OTP email to user
 * @param string $to_email Recipient email address
 * @param string $to_name Recipient name
 * @param string $otp_code 6-digit OTP code
 * @return bool True on success, false on failure
 */
function sendOTPEmail($to_email, $to_name, $otp_code)
{
    $mail = new PHPMailer(true);

    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host = SMTP_HOST;
        $mail->SMTPAuth = true;
        $mail->Username = SMTP_USERNAME;
        $mail->Password = SMTP_PASSWORD;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = SMTP_PORT;

        // Recipients
        $mail->setFrom(SMTP_FROM_EMAIL, SMTP_FROM_NAME);
        $mail->addAddress($to_email, $to_name);

        // Content
        $mail->isHTML(true);
        $mail->Subject = 'Your UIU TalentHub Verification Code';
        $mail->Body = getOTPEmailTemplate($to_name, $otp_code);
        $mail->AltBody = "Hello $to_name,\n\nYour UIU TalentHub verification code is: $otp_code\n\nThis code will expire in " . OTP_EXPIRY_MINUTES . " minutes.\n\nIf you didn't request this code, please ignore this email.";

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Email sending failed: {$mail->ErrorInfo}");
        return false;
    }
}

/**
 * Generate HTML email template for OTP
 */
function getOTPEmailTemplate($name, $otp_code)
{
    return <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body { font-family: 'Segoe UI', Arial, sans-serif; background: #f4f4f4; margin: 0; padding: 0; }
        .container { max-width: 600px; margin: 40px auto; background: white; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 20px rgba(0,0,0,0.1); }
        .header { background: linear-gradient(135deg, #ff5e00 0%, #ff8533 100%); padding: 40px 30px; text-align: center; }
        .header h1 { color: white; margin: 0; font-size: 28px; font-weight: 800; }
        .header p { color: rgba(255,255,255,0.9); margin: 8px 0 0 0; font-size: 14px; }
        .content { padding: 40px 30px; }
        .greeting { font-size: 18px; color: #333; margin-bottom: 20px; }
        .message { color: #666; line-height: 1.6; margin-bottom: 30px; }
        .otp-box { background: #f8f9fa; border: 2px dashed #ff5e00; border-radius: 12px; padding: 30px; text-align: center; margin: 30px 0; }
        .otp-label { color: #666; font-size: 14px; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 10px; }
        .otp-code { font-size: 42px; font-weight: 800; color: #ff5e00; letter-spacing: 8px; font-family: 'Courier New', monospace; }
        .expiry { color: #999; font-size: 13px; margin-top: 15px; }
        .warning { background: #fff3cd; border-left: 4px solid #ffc107; padding: 15px; margin: 20px 0; border-radius: 4px; }
        .warning p { margin: 0; color: #856404; font-size: 14px; }
        .footer { background: #f8f9fa; padding: 20px 30px; text-align: center; color: #999; font-size: 12px; }
        .footer a { color: #ff5e00; text-decoration: none; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>UIU TalentHub</h1>
            <p>Showcase Your Genius</p>
        </div>
        <div class="content">
            <div class="greeting">Hello {$name}! 👋</div>
            <div class="message">
                Thank you for joining UIU TalentHub! To complete your registration and verify your email address, please use the verification code below:
            </div>
            
            <div class="otp-box">
                <div class="otp-label">Your Verification Code</div>
                <div class="otp-code">{$otp_code}</div>
                <div class="expiry">⏱️ Expires in 10 minutes</div>
            </div>
            
            <div class="message">
                Enter this code on the verification page to activate your account and start showcasing your talent!
            </div>
            
            <div class="warning">
                <p><strong>⚠️ Security Notice:</strong> If you didn't create an account with UIU TalentHub, please ignore this email. Never share this code with anyone.</p>
            </div>
        </div>
        <div class="footer">
            <p>This is an automated message from UIU TalentHub</p>
            <p>Need help? Contact us at <a href="mailto:noobusingyt@gmail.com">noobusingyt@gmail.com</a></p>
        </div>
    </div>
</body>
</html>
HTML;
}
