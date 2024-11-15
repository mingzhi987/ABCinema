<?php
$to = "email2@localhost";
$subject = "Test Email from email1 to email2";
$message = "This is a test email sent from email1 to email2 using Mercury Mail.";
$headers = "From: email1@localhost";

if(mail($to, $subject, $message, $headers)) {
    echo "Email successfully sent to $to...";
} else {
    echo "Failed to send email.";
}
?>