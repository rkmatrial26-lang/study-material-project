<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';

function send_password_reset_email($recipient_email, $reset_link) {
    $mail = new PHPMailer(true);

    try {
        //Server settings
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'rkmatrial26@gmail.com'; // <-- PASTE YOUR GMAIL ADDRESS
        $mail->Password   = 'rhds vfhi zkkm ujpe';      // <-- PASTE YOUR APP PASSWORD
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port       = 465;

        //Recipients
        $mail->setFrom('YOUR_GMAIL_ADDRESS_HERE@gmail.com', 'ClassmateApp');
        $mail->addAddress($recipient_email);

        //Content
        $mail->isHTML(true);
        $mail->Subject = 'Password Reset Request for ClassmateApp';
        $mail->Body    = "
            <html>
            <body style='font-family: Arial, sans-serif; color: #333;'>
                <h2>Password Reset Request</h2>
                <p>You requested a password reset for your ClassmateApp account.</p>
                <p>Please click the link below to set a new password. This link will expire in 1 hour.</p>
                <p><a href='{$reset_link}' style='background-color: #007bff; color: white; padding: 10px 15px; text-decoration: none; border-radius: 5px;'>Reset Your Password</a></p>
                <p>If you did not request this, please ignore this email.</p>
            </body>
            </html>
        ";
        $mail->AltBody = 'To reset your password, please visit the following link: ' . $reset_link;

        $mail->send();
        return true;
    } catch (Exception $e) {
        // You can uncomment the line below for debugging if needed
        // $_SESSION['error_message'] = "Mailer Error: {$mail->ErrorInfo}";
        return false;
    }
}
?>