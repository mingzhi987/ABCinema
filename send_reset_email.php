<?php
// send_email.php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);
    
    // Assuming you have a way to verify the email exists in your database
    // Here you might want to check if the email exists in your users table.

    // TODO: change this to htdocs to that u no need to use my dang directory
    $reset_link = "http://localhost/Users/chuac_pvtupth/Desktop/hubgit_stuff/IE4483-project/ABCinema/reset_password.php?token=" . base64_encode($email);

    // Create email content
    $email_content = "
    <html>
    <head>
        <title>Password Reset</title>
    </head>
    <body>
        <h2>Password Reset Request</h2>
        <p>Hi,</p>
        <p>You requested a password reset. Click the link below to reset your password:</p>
        <p><a href='$reset_link'>Reset Password</a></p>
        <p>If you didn't request this, please ignore this email.</p>
        <p>Thank you!</p>
    </body>
    </html>";

    // Output the email as an HTML page
    header('Content-Type: text/html; charset=UTF-8');
    echo $email_content;
}
?>