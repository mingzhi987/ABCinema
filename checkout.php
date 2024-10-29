<?php
session_start(); // Start the session to retrieve cart data

// Connect to the database
// Movies table: MovieID int(11), MovieName varchar(45), MovieGenre varchar(45), MovieLength int(11), MovieRating varchar(45), MovieDesc text
// Database credentials
$servername = "localhost";  // Replace with your MySQL server name
$username = "root";         // Replace with your MySQL username
$password = "";             // Replace with your MySQL password
$dbname = "abcinema";  // Replace with your database name

// Create a connection to the MySQL database
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Retrieve the ShoppingCartID from the session
$shoppingCartID = isset($_SESSION['ShoppingCartID']) ? $_SESSION['ShoppingCartID'] : null;

// Query to get items in the shopping cart
if ($shoppingCartID) {
    $query = "
        SELECT st.ScreenTimeID, st.ScreenTimeDate, st.ScreenTimeCost, st.SeatingLocation, st.ScreeningMovie 
        FROM screeningtime2 AS st
        JOIN shoppingcart AS sc ON sc.ShoppingCartID = $shoppingCartID
        WHERE st.ScreenTimeID = sc.ShoppingCartID";

    $result = $conn->query($query);
}

// Calculate the total price
$totalPrice = 0.00;
$cartItems = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $cartItems[] = $row;
        $totalPrice += $row['ScreenTimeCost'];
    }
}

// Update total price in the shopping cart table
$conn->query("UPDATE shoppingcart SET TotalPrice = $totalPrice WHERE ShoppingCartID = '$shoppingCartID'");

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="abcmovies.css">
    <link rel="stylesheet" href="cart.css">
    <title>Checkout | ABCinema</title>
</head>

<body>
    <!-- Nav bar -->
    <div class="header">
        <a href="#default">
            <img class=logo src="images/logo/logo.png" href="#">
        </a>
        <div class="header-left">
            <a href="#contact" alt="Contact">Contact</a>
            <a href="#about" alt="About">About us</a>
        </div>
        <div class="menu-icons">
            <a href="#"><img src="images/icons/basket.svg" alt="Checkout" /></a>
            <a href="#"><img src="images/icons/profile.svg" alt="Profile" /></a>
        </div>
    </div>

    <!-- ShoppingCart -->
    <div class="container">
        <h1>Shopping Cart</h1>

        <?php if (count($items) > 0): ?>
            <table>
                <tr>
                    <th>ScreenTime ID</th>
                    <th>ScreenTime Date</th>
                    <th>ScreenTime Cost</th>
                    <th>Seating Location</th>
                    <th>Screening Movie</th>
                </tr>
                <?php foreach ($items as $item): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($item['ScreenTimeID']); ?></td>
                        <td><?php echo htmlspecialchars(date('Y-m-d H:i', strtotime($item['ScreenTimeDate']))); ?></td>
                        <td><?php echo htmlspecialchars(number_format($item['ScreenTimeCost'], 2)); ?></td>
                        <td><?php echo htmlspecialchars($item['SeatingLocation']); ?></td>
                        <td><?php echo htmlspecialchars($item['ScreeningMovie']); ?></td>
                    </tr>
                <?php endforeach; ?>
            </table>

            <div class="total-price">
                <strong>Total Price:</strong> $<?php echo number_format($totalPrice, 2); ?>
            </div>
            <button class="checkout-btn" onclick="checkoutAlert()">Checkout</button>
        <?php else: ?>
            <p>Your cart is empty.</p>
        <?php endif; ?>

        <a href="sign_up.php" class="join-btn">Join us as a member</a>
    </div>

    <div id="myAlert">
        <h2>Booking Confirmed!</h2>
        <p>Your booking has been confirmed and sent to your email.</p>
        <button onclick="closeAlert()">Close</button>
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

</html>

<script>
    function checkout() {

        function checkoutAlert() {
            document
                .getElementById("myAlert")
                .style
                .display = "block";
            window.location.href = 'email_confirmation.php';
        }

        function closeAlert() {
            document
                .getElementById("myAlert")
                .style
                .display = "none";
        }
    }
</script>

<?php
$conn->close();
?>