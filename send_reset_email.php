<?php
require 'vendor/autoload.php';
require 'dbconnection.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

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

        // Send email using PHPMailer
        $mail = new PHPMailer(true);
        try {
            // Server settings
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'adamcheehean01@gmail.com'; // Your Gmail address
            $mail->Password = 'lqot wuvq xiuq xwbi'; // App password from Google
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;

            // Email settings
            $mail->setFrom('adamcheehean01@gmail.com', 'Your Name');
            $mail->addAddress($email); // Recipient email address
            $mail->Subject = 'Password Reset Request';
            $mail->isHTML(true);
            $mail->Body    = $email_content;

            // Send email
            $mail->send();
            echo "<script>
                alert('Password reset link has been sent to your email');
                window.location.href = 'login.php';
            </script>";
        } catch (Exception $e) {
            echo "<script>alert('Failed to send email. Mailer Error: {$mail->ErrorInfo}');</script>";
        }
    } else {
        echo "<script>alert('Invalid email address');</script>";
    }

    $stmt->close();
    $conn->close();
}
?>