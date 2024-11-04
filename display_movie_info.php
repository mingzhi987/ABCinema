<?php
session_start();
require 'dbconnection.php';

// MovieID to be displayed
if (isset($_GET['movieID'])){
    $movieID = intval($_GET['movieID']);
} else {
    die('MovieID not specified.');
}

// Create connection
$conn = new mysqli($servername, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Retrieve movie information
$sql = "SELECT * FROM movies WHERE MovieID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $movieID);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $movie = $result->fetch_assoc();
} else {
    die("Movie not found.");
}

$isLoggedIn = isset(($_SESSION["token_id"]));


if ($isLoggedIn) {
    // Retrieve available screening dates
    $sql = "SELECT ScreenTimeID, ScreenTimeDate FROM screeningtime2 WHERE ScreeningMovie = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $movieID);
    $stmt->execute();
    $screeningResult = $stmt->get_result();

}

// Retrieve available seats if screening date is selected
$seats = [];
if (isset($_POST['screening_date'])) {
    $screening_date = intval($_POST['screening_date']);
    $sql = "SELECT SeatID, SeatNumber FROM seating 
            WHERE CinemaNumber IN (SELECT CinemaID FROM cinema WHERE MovieAllocated = ?) 
            AND SeatID NOT IN (SELECT SeatID FROM booking WHERE Showtime = ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $movieID, $screening_date);
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
    
    <title><?php echo htmlspecialchars($movie['MovieName']); ?></title>
    <link rel="stylesheet" href="abcmovies.css">
    <link rel="stylesheet" href="all_styles.css">
</head>
<body>
    <!-- Nav bar -->
    <div class="header">
        <a href="movies.php">
            <img class=logo src="images/logo/logo.png" href="#">
        </a>
        <div class="header-left">
            <a href="#contact" alt="Contact">Contact</a>
            <a href="#about" alt="About">About us</a>
        </div>
        <div class="menu-icons">
            <a href="checkout.php"><img src="images/icons/basket.svg" alt="Checkout" /></a>
            <?php if (isset($_SESSION['token_id'])): ?>
                <a href="profile.php"><img src="images/icons/profile.svg" alt="Profile" /></a>
            <?php else: ?>
                <a href="login.php"><img src="images/icons/profile.svg" alt="Login" /></a>
            <?php endif; ?>
        </div>
    </div>
    <div class="body-content">
        <h1 style="margin-top: 30px; margin-bottom: 30px;">Booking Page</h1>
        <table>
            <tr>
                <td>
                <img src="<?php echo htmlspecialchars($movie['MoviePoster']); ?>" alt="<?php echo htmlspecialchars($movie['MovieName']); ?>" style="width: 300px;">
                </td>
                <td>
                    <h1><?php echo htmlspecialchars($movie['MovieName']); ?></h1>
                    <p><strong>Genre:</strong> <?php echo htmlspecialchars($movie['MovieGenre']); ?></p>
                    <p><strong>Length:</strong> <?php echo htmlspecialchars($movie['MovieLength']); ?> minutes</p>
                    <p><strong>Synopsis:</strong> <?php echo htmlspecialchars($movie['MovieDesc']); ?></p>
                    <p><strong>Rating:</strong> <?php echo htmlspecialchars($movie['MovieRating']); ?></p>

                    <?php if ($isLoggedIn): ?>
                    <h2>Select Screening Date</h2>
                    <form method="post" action="">
                        <label for="screening_date">Screening Date:</label>
                        <select name="screening_date" id="screening_date" required onchange="this.form.submit()">
                            <option value="">Select a date</option>
                            <?php while ($screening = $screeningResult->fetch_assoc()): ?>
                                <option value="<?php echo htmlspecialchars($screening['ScreenTimeID']); ?>" <?php echo (isset($screening_date) && $screening_date == $screening['ScreenTimeID']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($screening['ScreenTimeDate']); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </form>

                    <?php if (!empty($seats)): ?>
                    <h2>Select Seats</h2>
                    <form method="post" action="add_movie_to_cart.php">
                        <input type="hidden" name="screening_date" value="<?php echo htmlspecialchars($screening_date); ?>">
                        <div id="seats-container">
                            <?php foreach ($seats as $seat): ?>
                                <input type="checkbox" name="seats[]" value="<?php echo htmlspecialchars($seat['SeatID']); ?>" id="seat_<?php echo htmlspecialchars($seat['SeatID']); ?>">
                                <label for="seat_<?php echo htmlspecialchars($seat['SeatID']); ?>"><?php echo htmlspecialchars($seat['SeatNumber']); ?></label><br>
                            <?php endforeach; ?>
                        </div>
                        <button type="submit">Book Now</button>
                    </form>
                    <?php endif; ?>
                    <?php else: ?>
                        <p>Please log in or sign up to purchase tickets!</p>
                        <a href="login.php"><button>Log In / Sign Up</button></a>
                    <?php endif; ?>
                </td>
            </tr>
        </table>
    </div>

   <!-- Footer -->
<footer>
    <div class="footer-container">
        <div class="row">
            <div class="column-1"><img class="logo" src="images/logo/logo.png">
                <div class="footer-summary">Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat</div>
            </div>

            <div class="column-2">
                <h4>Links</h4>
                <ul>
                    <li class="footer-links"><a href="http://demo.amytheme.com/movie/demo/elementor-single-cinema/" aria-current="page">Home</a></li>
                    <li class="footer-links"><a href="http://demo.amytheme.com/movie/demo/elementor-single-cinema/coming-soon/">Coming Soon</a></li>
                    <li class="footer-links"><a href="http://demo.amytheme.com/movie/demo/elementor-single-cinema/top-rated/">Top rated</a></li>
                </ul>
            </div>

            <div class="column-2">
                <h4>Contact Us</h4>
                123 Raffles Place #14-01 <br> Singapore 348023
                <p>support@abcinema.com.sg
                <p>+65 63498203
            </div>

            <div class="column-2">
                <h4>Follow Us</h4>
                <img src="images/icons/twitter-x.svg" alt="X (Twitter)" />
                <img src="images/icons/facebook.svg" alt="Facebook" />
                <img src="images/icons/instagram.svg" alt="Instagram" />
            </div>
        </div>
    </div>
    </div>
</footer>
</body>
</html>