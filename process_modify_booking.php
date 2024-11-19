<?php
session_start();
require 'dbconnection.php';

// Check if the user is logged in
if (!isset($_SESSION['token_id'])) {
    echo "<script>alert('You must log in to modify your booking.'); window.location.href='login.php'</script>";
    exit;
}

// Retrieve user details using the login token
$login_token = $_SESSION['token_id'];
$sql = "SELECT UserID FROM useraccount WHERE login_token = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $login_token);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
    $userid = $user['UserID'];
} else {
    echo "<script>alert('Invalid login token.'); window.location.href='login.php'</script>";
    exit;
}

// Handle modify booking
if (isset($_POST['booking_id']) && isset($_POST['new_showtime']) && isset($_POST['new_seat'])) {
    $bookingID = intval($_POST['booking_id']);
    $newShowtime = intval($_POST['new_showtime']);
    $newSeat = intval($_POST['new_seat']);

    // Check if the new showtime and seat are available
    $sql = "SELECT * FROM booking WHERE Showtime = ? AND SeatID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $newShowtime, $newSeat);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        echo "<script>alert('The selected showtime and seat are already taken.'); window.location.href='profile.php'</script>";
    } else {
        // Update the booking with the new showtime and seat
        $sql = "UPDATE booking SET Showtime = ?, SeatID = ? WHERE BookingID = ? AND UserID = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iiii", $newShowtime, $newSeat, $bookingID, $userid);
        if ($stmt->execute()) {
            echo "<script>alert('Booking modified successfully!'); window.location.href='profile.php'</script>";
        } else {
            echo "<script>alert('Error modifying booking: " . $stmt->error . "');</script>";
        }
    }

    $stmt->close();
}

$conn->close();
?>