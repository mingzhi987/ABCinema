<?php
session_start();

require 'dbconnection.php';

if (!isset($_SESSION['token_id'])) {
    header("Location: movies.php");
    exit; // Redirect to login if not logged in
}


echo "you're in email_send_tester.php";

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

// Retrieve the new booking IDs from the session
$newBookingIDs = $_SESSION['newBookingIDs'];
$placeholders = implode(',', array_fill(0, count($newBookingIDs), '?'));

// Query to get movie screening details and seats in the shopping cart
$sql = "
    SELECT 
        b.BookingID, b.PaymentDate, b.MovieName, b.Showtime, b.Price, 
        c.CinemaHall, s.SeatNumber 
    FROM 
        booking AS b
    JOIN 
        cinema AS c ON b.CinemaID = c.CinemaID
    JOIN 
        seating AS s ON b.SeatID = s.SeatID
    WHERE 
        b.BookingID IN ($placeholders)
    ORDER BY 
        b.PaymentDate DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param(str_repeat('i', count($newBookingIDs)), ...$newBookingIDs);
$stmt->execute();
$result = $stmt->get_result();

$bookings = [];
while ($row = $result->fetch_assoc()) {
    $bookings[] = $row;
}

$stmt->close();
$conn->close();

$logoPath = 'images/logo/logo.png';
$logoData = base64_encode(file_get_contents($logoPath));
$logoSrc = 'data:image/png;base64,' . $logoData;

// Construct the email body
$emailBody = "
<html>
<head>
    <title>Booking Confirmation</title>
</head>
<body>
    <h2>Hello $user_fullName!</h2>
    <p>You have successfully purchased tickets from our website. Please check them out under your profile page!</p>
    <h3>Details are as follows:</h3>
    <table border='1'>
        <tr>
            <th>BookingID</th>
            <th>Payment Date</th>
            <th>Movie</th>
            <th>Showtime</th>
            <th>Price</th>
            <th>Cinema Hall</th>
            <th>Seat Number</th>
        </tr>";

// Loop through cart items to populate table rows
foreach ($bookings as $booking) {
    $emailBody .= "<tr>";
    $emailBody .= "<td>" . htmlspecialchars($booking['BookingID']) . "</td>";
    $emailBody .= "<td>" . htmlspecialchars($booking['PaymentDate']) . "</td>";
    $emailBody .= "<td>" . htmlspecialchars($booking['MovieName']) . "</td>";
    $emailBody .= "<td>" . htmlspecialchars($booking['Showtime']) . "</td>";
    $emailBody .= "<td>$" . htmlspecialchars($booking['Price']) . "</td>";
    $emailBody .= "<td>" . htmlspecialchars($booking['CinemaHall']) . "</td>";
    $emailBody .= "<td>" . htmlspecialchars($booking['SeatNumber']) . "</td>";
    $emailBody .= "</tr>";
}

// Close the HTML table
$emailBody .= "</table>";


$emailBody .= "<br><p>Thank you for choosing ABCinemas!</p>
<img src='$logoSrc' alt='Movie Image' width='200' height='150'>
</body>
</html>";

// Email settings
$to = 'email2@localhost';
$subject = 'Booking tickets successful!';
$headers = "From: ABCinemas <email1@localhost>\r\n";
$headers .= "Reply-To: email1@localhost\r\n";
$headers .= "Content-Type: text/html; charset=UTF-8\r\n";

// Send the email
if (mail($to, $subject, $emailBody, $headers)) {
    echo "<script>alert('Booking Success! Please check your email for confirmation.'); window.location.href='movies.php';</script>";
} else {
    echo "<script>alert('Something went wrong when sending email, please try again!'); window.location.href='checkout.php';</script>";
}
