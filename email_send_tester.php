<?php
session_start();

require 'vendor/autoload.php';
require 'dbconnection.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$mail = new PHPMailer(true);


if (!isset($_SESSION['token_id'])) {
    header("Location: movies.php");
    exit; // Redirect to login if not logged in
}


// Create a connection to the MySQL database
$conn = new mysqli($servername, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Retrieve user details using the login token
$login_token = $_SESSION['token_id'];
$sql = "SELECT UserID, Email FROM useraccount WHERE login_token = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $login_token);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
    $userid = $user['UserID'];
    $userEmail = $user['Email'];
} else {
    echo "Invalid login token.";
    exit;
}

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
    $mail->setFrom('adamcheehean01@gmail.com', 'ABC Cinemas');
    $mail->addAddress($userEmail, 'Recipient Name');
    $mail->Subject = 'Booking tickets successful!';
    $mail->Body    = 'Hello! You have successfully purchased tickets from our website, please check them out under your profile page!';

    // Send email
    $mail->send();

    echo 'Email sent successfully!';
} catch (Exception $e) {
    echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
}
?>