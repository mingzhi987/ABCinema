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
$seatStatus = 1;
if (isset($_POST['screening_date'])) {

    if (empty($_POST['screening_date'])) {
        $seatStatus = 0;
    }

    $screening_date = intval($_POST['screening_date']);
    $sql = "SELECT SeatID, SeatNumber FROM seating 
            WHERE CinemaNumber IN (SELECT CinemaID FROM cinema WHERE MovieAllocated = ?) 
            AND SeatID NOT IN (SELECT SeatID FROM booking WHERE Showtime = ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $movieID, $screening_date);
    $stmt->execute();
    $seatingResult = $stmt->get_result();
    
    if ($seatStatus == 1) {
        while ($row = $seatingResult->fetch_assoc()) {
            $seats[] = $row;
        }
    } else{
        $seats = [];
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
    <link rel="stylesheet" href="about_us.css">
    <script src="footerAdjuster.js"></script>
    <!-- <link rel="stylesheet" href="all_styles.css"> -->
</head>
<body>
    <!-- Nav bar -->
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
            <?php if (isset($_SESSION['token_id'])): ?>
                <a href="profile.php"><img src="images/icons/profile.svg" alt="Profile" /></a>
            <?php else: ?>
                <a href="login.php"><img src="images/icons/profile.svg" alt="Login" /></a>
            <?php endif; ?>
        </div>
    </div>
    <div class="body-content" style="display: flex;
    width: 70%;
    flex-direction: column;
    margin: 0 auto; margin-bottom: 50px;">
        <h1 style="margin-top: 30px; margin-bottom: 30px; text-align: center;">Booking Page</h1>
        <div class="movie-detail" style="background-color: #fff;">
            <table>
                <tr>
                    <td>
                    <img src="<?php echo htmlspecialchars($movie['MoviePoster']); ?>" alt="<?php echo htmlspecialchars($movie['MovieName']); ?>" style="width: 30vh;">
                    </td>
                    <td>
                        <h1><?php echo htmlspecialchars($movie['MovieName']); ?></h1>
                        <p><strong>Genre:</strong> <?php echo htmlspecialchars($movie['MovieGenre']); ?></p>
                        <p><strong>Length:</strong> <?php echo htmlspecialchars($movie['MovieLength']); ?> minutes</p>
                        <p><strong>Synopsis:</strong> <?php echo htmlspecialchars($movie['MovieDesc']); ?></p>
                        <p><strong>Rating:</strong> <?php echo htmlspecialchars($movie['MovieRating']); ?> / 10</p>
                    </td>
                </tr>
            </table>
            <div style="margin-bottom: 30px;
    margin-left: 20px;
    display: flex;
    flex-direction: column;
    align-items: center;">
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
                <h2 id="seatheader" style="margin-top: 50px;">Select Seats</h2>
                
                <input type="hidden" name="screening_date" value="<?php echo htmlspecialchars($screening_date); ?>">
                <div id="seats-container">
                    <?php include 'display_test.php'; ?>
                </div>
                
            <?php endif; ?>
            <?php else: ?>
                <p>Please log in or sign up to purchase tickets!</p>
                <a href="login.php"><button>Log In / Sign Up</button></a>
            <?php endif; ?>
            </div>
        </div>
    </div>

   <!-- Footer -->
   <footer id="footer">
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
</body>
</html>