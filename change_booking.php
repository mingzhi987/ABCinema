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

// Retrieve booking details
if (isset($_POST['booking_id'])) {
    $bookingID = intval($_POST['booking_id']);
    $sql = "SELECT * FROM booking WHERE BookingID = ? AND UserID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $bookingID, $userid);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $booking = $result->fetch_assoc();
    } else {
        echo "<script>alert('Booking not found.'); window.location.href='profile.php'</script>";
        exit;
    }
} else {
    echo "<script>alert('No booking ID provided.'); window.location.href='profile.php'</script>";
    exit;
}

// Retrieve available screening times for the movie
$sql = "SELECT ScreenTimeID, ScreenTimeDate FROM screeningtime2 WHERE ScreeningMovie = (SELECT MovieID FROM movies WHERE MovieName = ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $booking['MovieName']);
$stmt->execute();
$screeningResult = $stmt->get_result();

// Retrieve available seats if screening date is selected
$seats = [];
if (isset($_POST['new_showtime'])) {
    $new_showtime = intval($_POST['new_showtime']);
    $sql = "SELECT SeatID, SeatNumber FROM seating 
            WHERE CinemaNumber IN (SELECT CinemaID FROM cinema WHERE MovieAllocated = (SELECT MovieID FROM movies WHERE MovieName = ?)) 
            AND SeatID NOT IN (SELECT SeatID FROM booking WHERE ScreenTimeID = ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $booking['MovieName'], $new_showtime);
    $stmt->execute();
    $seatingResult = $stmt->get_result();
    while ($row = $seatingResult->fetch_assoc()) {
        $seats[] = $row;
    }
}

$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modify Booking</title>
    <link rel="stylesheet" href="all_styles.css">
</head>
<body>
    <div class="header-content">
        <h1>Modify Booking</h1>
    </div>
    <div class="body-content">
        <h2>Current Booking Details</h2>
        <p><strong>Showtime:</strong> <?php echo htmlspecialchars($booking['Showtime']); ?></p>
        <p><strong>Cinema Hall:</strong> <?php echo htmlspecialchars($booking['CinemaHall']); ?></p>
        <p><strong>Seat Number:</strong> <?php echo htmlspecialchars($booking['SeatNo']); ?></p>

        <h2>Modify Booking</h2>
        <form method="post" action="">
            <input type="hidden" name="booking_id" value="<?php echo htmlspecialchars($bookingID); ?>">
            <label for="new_showtime">New Showtime:</label>
            <select name="new_showtime" id="new_showtime" required onchange="this.form.submit()">
                <option value="">Select a new showtime</option>
                <?php while ($screening = $screeningResult->fetch_assoc()): ?>
                    <option value="<?php echo htmlspecialchars($screening['ScreenTimeID']); ?>" <?php echo (isset($new_showtime) && $new_showtime == $screening['ScreenTimeID']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($screening['ScreenTimeDate']); ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </form>

        <?php if (!empty($seats)): ?>
        <form method="post" action="process_modify_booking.php">
            <input type="hidden" name="booking_id" value="<?php echo htmlspecialchars($bookingID); ?>">
            <input type="hidden" name="new_showtime" value="<?php echo htmlspecialchars($new_showtime); ?>">
            <label for="new_seat">New Seat:</label>
            <select name="new_seat" id="new_seat" required>
                <?php foreach ($seats as $seat): ?>
                    <option value="<?php echo htmlspecialchars($seat['SeatID']); ?>"><?php echo htmlspecialchars($seat['SeatNumber']); ?></option>
                <?php endforeach; ?>
            </select>
            <button type="submit" name="modify_booking">Modify Booking</button>
        </form>
        <?php endif; ?>
    </div>
</body>
</html>