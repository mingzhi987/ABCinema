<?php
session_start();

//install "composer", then composer init, composer install, composer require phpmailer/phpmailer
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
$sql = "SELECT UserID, Email, FullName FROM useraccount WHERE login_token = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $login_token);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
    $user_fullName = $user["FullName"];
    $userid = $user['UserID'];
    $userEmail = $user['Email'];
} else {
    echo "Invalid login token.";
    exit;
}


// Retrieve the shopping cart linked to the user
$sql = "SELECT ShoppingCartID FROM shoppingcart WHERE UserID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $userid);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $shoppingCart = $result->fetch_assoc();
    $shoppingCartID = $shoppingCart['ShoppingCartID'];
} else {
    echo "No items in the shopping cart.";
    exit;
}

// Query to get movie screening details and seats in the shopping cart
$sql = "
    SELECT 
        st.ScreenTimeID, st.ScreenTimeDate, st.ScreenTimeCost, c.CinemaHall, m.MovieName, s.SeatNumber
    FROM 
        shoppingscreening AS ss
    JOIN 
        screeningtime2 AS st ON ss.ScreenTimeID = st.ScreenTimeID
    JOIN 
        cinema AS c ON st.SeatingLocation = c.CinemaID
    JOIN 
        movies AS m ON st.ScreeningMovie = m.MovieID
    JOIN 
        seating AS s ON ss.SeatID = s.SeatID
    WHERE 
        ss.ShoppingCartID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $shoppingCartID);
$stmt->execute();
$result = $stmt->get_result();

$cartItems = [];
while ($row = $result->fetch_assoc()) {
    $cartItems[] = $row;
}

$stmt->close();
$conn->close();

// Construct the email body
$emailBody = "Hello $user_fullName! You have successfully purchased tickets from our website, please check them out under your profile page!\n\nDetails are as follows:\n\n";

foreach ($cartItems as $item) {
    $emailBody .= "Screening Details\n";
    $emailBody .= "ScreenTimeID: " . htmlspecialchars($item['ScreenTimeID']) . "\n";
    $emailBody .= "Movie: " . htmlspecialchars($item['MovieName']) . "\n";
    $emailBody .= "Date: " . htmlspecialchars($item['ScreenTimeDate']) . "\n";
    $emailBody .= "Cost: $" . htmlspecialchars($item['ScreenTimeCost']) . "\n";
    $emailBody .= "Cinema Hall: " . htmlspecialchars($item['CinemaHall']) . "\n";
    $emailBody .= "Seat Number: " . htmlspecialchars($item['SeatNumber']) . "\n\n";
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
    $mail->Body = $emailBody;

    // Send email
    $mail->send();

    echo '<script>alert("Booking Success! Please check your email for confirmation.");</script>';
} catch (Exception $e) {
    echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
}
?>