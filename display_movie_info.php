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
    <script>

        //call seats when date selected
        function fetchSeats(screeningDate) {
            var movieID = <?php echo $movieID; ?>;
            var xhr = new XMLHttpRequest();
            xhr.open("POST", "fetch_seats.php", true);
            xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
            xhr.onreadystatechange = function () {
                if (xhr.readyState === 4 && xhr.status === 200) {
                    var seats = JSON.parse(xhr.responseText);

                    //display seats by getting seats-container
                    var seatsContainer = document.getElementById("seats-container");
                    seatsContainer.innerHTML = "";

                    seats.forEach(function (seat) {
                        var checkbox = document.createElement("input");
                        checkbox.type = "checkbox";
                        checkbox.name = "seats[]";
                        checkbox.value = seat.SeatID;
                        checkbox.id = "seat_" + seat.SeatID;

                        var label = document.createElement("label");
                        label.htmlFor = "seat_" + seat.SeatID;
                        label.textContent = seat.SeatNumber;

                        seatsContainer.appendChild(checkbox);
                        seatsContainer.appendChild(label);
                        seatsContainer.appendChild(document.createElement("br"));
                    });
                }
            };

            //send screening date and movie id to fetch_seats.php
            xhr.send("screening_date=" + screeningDate + "&movie_id=" + movieID);
        }
    </script>
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
                    <form method="post" action="add_movie_to_cart.php">
                        <label for="screening_date">Screening Date:</label>
                        <select name="screening_date" id="screening_date" required onchange="fetchSeats(this.value)">
                            <option value="">Select a date</option>
                            <?php while ($screening = $screeningResult->fetch_assoc()): ?>
                                <option value="<?php echo htmlspecialchars($screening['ScreenTimeID']); ?>">
                                    <?php echo htmlspecialchars($screening['ScreenTimeDate']); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>

                        <h2>Select Seats</h2>
                        <div id="seats-container"></div>

                        <button type="submit">Book Now</button>
                    </form>
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