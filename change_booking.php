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
    $sql = "
        SELECT 
            b.BookingID, b.PaymentDate, b.MovieName, b.Showtime, b.Price, 
            st.ScreenTimeDate, c.CinemaHall, s.SeatNumber 
        FROM 
            booking AS b
        JOIN 
            screeningtime2 AS st ON b.Showtime = st.ScreenTimeID
        JOIN 
            cinema AS c ON b.CinemaID = c.CinemaID
        JOIN 
            seating AS s ON b.SeatID = s.SeatID
        WHERE 
            b.BookingID = ? AND b.UserID = ?";
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
            AND SeatID NOT IN (SELECT SeatID FROM booking WHERE Showtime = ?)";
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
    <link rel="stylesheet" href="abcmovies.css">
    <link rel="stylesheet" href="about_us.css">
    <link rel="stylesheet" href="change_booking.css">
</head>
<body>
    <div class="header">
        <div>
            <a href="movies.php">
                <img class=logo src="images/logo/logo.png">
            </a>
            <div class="header-left">
                <a href="movies.php" alt="Movies">Movies</a>
                <a href="about_us.html" alt="About us">About us</a>
            </div>
        </div>
        <div class="header-right">
            <a href="checkout.php"><img src="images/icons/basket.svg" alt="Checkout" /></a>
            <a href="profile.php"><img src="images/icons/profile.svg" alt="Profile" /></a>
        </div>
    </div>
    <div class="body-content">
        <div class="current-book">
            <h1>Current Booking Details</h1>
            <table>
                <tr>
                    <th>Showtime</th>
                    <td><?php echo htmlspecialchars($booking['ScreenTimeDate']); ?></td>
                </tr>
                <tr>
                    <th>Cinema Hall</th>
                    <td><?php echo htmlspecialchars($booking['CinemaHall']); ?></td>
                </tr>
                <tr>
                    <th>Seat Number</th>
                    <td><?php echo htmlspecialchars($booking['SeatNumber']); ?></td>
                </tr>
            </table>
        </div>

        <div class="change-book">
            <h1>Modify Booking</h1>
            <form method="post" action="">
                <input type="hidden" name="booking_id" value="<?php echo htmlspecialchars($bookingID); ?>">
                <label for="new_showtime"><b>New Showtime:</b></label>
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
            <div style="margin-top: 50px;">
                <input type="hidden" name="booking_id" value="<?php echo htmlspecialchars($bookingID); ?>">
                <input type="hidden" name="new_showtime" value="<?php echo htmlspecialchars($new_showtime); ?>">
    
                <?php include 'display_seat_modify.php'; ?>
                
            </div>
            <?php endif; ?>
        </div>
    </div>

    
</body>

<footer>
        <div class="footer-container">
            <div class="row">
                <div class="column-1"><img class="logo" src="images/logo/logo.png">
                    <div class="footer-summary">Welcome to ABCinema, a modern cinema delivering immersive experiences with top-notch visuals, sound, and cosy seating. Discover blockbusters, indie films, and local gems—all designed to captivate and inspire.
                    </div>
                </div>

                <div class="column-2">
                    <h2>Links</h2>
                        <p class="footer-links"><a href="http://demo.amytheme.com/movie/demo/elementor-single-cinema/" aria-current="page">Home</a></p>
                        <p class="footer-links"><a href="http://demo.amytheme.com/movie/demo/elementor-single-cinema/coming-soon/">Movies</a></p>
                        <p class="footer-links"><a href="http://demo.amytheme.com/movie/demo/elementor-single-cinema/top-rated/">About us</a></p>
                </div>

                <div class="column-2">
                    <h2>Contact Us</h2>
                    234 Film Street #01-01 <br> Singapore 690234
                    <p>contact@abcinema.com.sg
                    <p>+65 6349 8203
                </div>

                <div class="column-2">
                    <h2>Follow Us</h2>
                    <img id="footer-icons" src="images/icons/twitter-x.svg" alt="X (Twitter)" />
                    <img id="footer-icons" src="images/icons/facebook.svg" alt="Facebook" />
                    <img id="footer-icons" src="images/icons/instagram.svg" alt="Instagram" />
                </div>
            </div>
        </div>
        </div>
    </footer>
</html>