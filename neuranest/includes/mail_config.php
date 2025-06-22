<?php
require_once 'config.php';
require_once __DIR__ . '/../vendor/PHPMailer/src/PHPMailer.php';
require_once __DIR__ . '/../vendor/PHPMailer/src/SMTP.php';
require_once __DIR__ . '/../vendor/PHPMailer/src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

function send_email($to, $subject, $body, $is_html = true) {
    $mail = new PHPMailer(true);
    
    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host = SMTP_HOST;
        $mail->SMTPAuth = true;
        $mail->Username = SMTP_USER;
        $mail->Password = SMTP_PASS;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = SMTP_PORT;
        $mail->CharSet = 'UTF-8';
        
        // Recipients
        $mail->setFrom(SMTP_USER, SITE_NAME);
        $mail->addAddress($to);
        
        // Content
        $mail->isHTML($is_html);
        $mail->Subject = $subject;
        $mail->Body = $body;
        
        $mail->send();
        return ['success' => true, 'message' => 'Email sent successfully'];
    } catch (Exception $e) {
        return ['success' => false, 'message' => "Email could not be sent. Mailer Error: {$mail->ErrorInfo}"];
    }
}

function send_verification_email($email, $code, $type = 'registration') {
    $lang = load_language();
    
    $subjects = [
        'registration' => $lang['email_verification_subject'],
        'password_reset' => $lang['password_reset_subject'],
        'email_change' => $lang['email_change_subject']
    ];
    
    $templates = [
        'registration' => get_verification_email_template($code, $lang),
        'password_reset' => get_password_reset_email_template($code, $lang),
        'email_change' => get_email_change_template($code, $lang)
    ];
    
    $subject = $subjects[$type] ?? $subjects['registration'];
    $body = $templates[$type] ?? $templates['registration'];
    
    return send_email($email, $subject, $body);
}

function get_verification_email_template($code, $lang) {
    return "
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset='UTF-8'>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0; }
            .content { background: #f9f9f9; padding: 30px; border-radius: 0 0 10px 10px; }
            .code { background: #667eea; color: white; padding: 15px; text-align: center; font-size: 24px; font-weight: bold; border-radius: 5px; margin: 20px 0; letter-spacing: 3px; }
            .footer { text-align: center; margin-top: 20px; color: #666; font-size: 14px; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h1>NeuraNest</h1>
                <p>{$lang['welcome_to_neuranest']}</p>
            </div>
            <div class='content'>
                <h2>{$lang['email_verification']}</h2>
                <p>{$lang['verification_code_text']}</p>
                <div class='code'>$code</div>
                <p>{$lang['code_expires_text']}</p>
                <p>{$lang['ignore_if_not_you']}</p>
            </div>
            <div class='footer'>
                <p>&copy; 2025 NeuraNest. {$lang['all_rights_reserved']}</p>
            </div>
        </div>
    </body>
    </html>
    ";
}

function get_password_reset_email_template($code, $lang) {
    return "
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset='UTF-8'>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0; }
            .content { background: #f9f9f9; padding: 30px; border-radius: 0 0 10px 10px; }
            .code { background: #667eea; color: white; padding: 15px; text-align: center; font-size: 24px; font-weight: bold; border-radius: 5px; margin: 20px 0; letter-spacing: 3px; }
            .footer { text-align: center; margin-top: 20px; color: #666; font-size: 14px; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h1>NeuraNest</h1>
                <p>{$lang['password_reset']}</p>
            </div>
            <div class='content'>
                <h2>{$lang['password_reset_request']}</h2>
                <p>{$lang['password_reset_code_text']}</p>
                <div class='code'>$code</div>
                <p>{$lang['code_expires_text']}</p>
                <p>{$lang['ignore_if_not_you']}</p>
            </div>
            <div class='footer'>
                <p>&copy; 2025 NeuraNest. {$lang['all_rights_reserved']}</p>
            </div>
        </div>
    </body>
    </html>
    ";
}

function get_email_change_template($code, $lang) {
    return "
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset='UTF-8'>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0; }
            .content { background: #f9f9f9; padding: 30px; border-radius: 0 0 10px 10px; }
            .code { background: #667eea; color: white; padding: 15px; text-align: center; font-size: 24px; font-weight: bold; border-radius: 5px; margin: 20px 0; letter-spacing: 3px; }
            .footer { text-align: center; margin-top: 20px; color: #666; font-size: 14px; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h1>NeuraNest</h1>
                <p>{$lang['email_change']}</p>
            </div>
            <div class='content'>
                <h2>{$lang['email_change_request']}</h2>
                <p>{$lang['email_change_code_text']}</p>
                <div class='code'>$code</div>
                <p>{$lang['code_expires_text']}</p>
                <p>{$lang['ignore_if_not_you']}</p>
            </div>
            <div class='footer'>
                <p>&copy; 2025 NeuraNest. {$lang['all_rights_reserved']}</p>
            </div>
        </div>
    </body>
    </html>
    ";
}
?>