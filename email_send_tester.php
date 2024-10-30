<?php
require 'vendor/autoload.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$mail = new PHPMailer(true);

try {

     // Server settings
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'adamcheehean01@gmail.com'; // Your Gmail address
    $mail->Password = 'lqot wuvq xiuq xwbi';    // App password from Google
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = 587;

    // Email settings
    $mail->setFrom('adamcheehean01@gmail.com', 'Your Name');
    $mail->addAddress('chua1020@e.ntu.edu.sg', 'Recipient Name');
    $mail->Subject = 'Test Email from PHP via Gmail';
    $mail->Body    = 'Hello! This is a test email sent using Gmail SMTP server.';

    // Send email
    $mail->send();

    echo 'Email sent successfully!';
} catch (Exception $e) {
    echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
}
?>