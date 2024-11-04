<?php

require 'dbconnection.php';


// send_email.php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

     // Database connection
    $conn = new mysqli($servername, $username, $password, $database);
    if ($conn->connect_error) {
        echo json_encode(["message" => "Database connection failed"]);
        exit;
    }       
    
    $email = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);
    
    // Check if email exists
    $sql = "SELECT UserID FROM useraccount WHERE Email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();

        $encrypted_email = base64_encode($email);
        $reset_link = "http://localhost/Users/chuac_pvtupth/Desktop/hubgit_stuff/IE4483-project/ABCinema/reset_password.php?token=$encrypted_email";

       
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

        // Email settings
        $to = 'email2@localhost';
        $subject = 'Password Reset Request';
        $headers = "From: ABC Cinema <email1@localhost>\r\n";
        $headers .= "Reply-To: email1@localhost\r\n";
        $headers .= "Content-Type: text/html; charset=UTF-8\r\n";


       // Send the email
        if (mail($to, $subject, $email_content, $headers)) {
            echo "<script>
                alert('Password reset link has been sent to your email');
                window.location.href = 'login.php';
            </script>";
        } else {
            echo "<script>alert('Failed to send email.');</script>";
        }
    } else {
        echo "<script>alert('Invalid email address');</script>";
    }

    $stmt->close();
    $conn->close();
}
?>