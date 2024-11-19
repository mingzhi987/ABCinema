<?php
session_start(); // Start the session to retrieve cart data

require 'dbconnection.php';

if (!isset($_SESSION['token_id'])) {
    echo "<script>alert('You must log in to view shopping cart & checkout!'); window.location.href='login.php'</script>";
}


// Create a connection to the MySQL database
$conn = new mysqli($servername, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
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
    echo "Invalid login token.";
    exit;
}

// Retrieve the shopping cart linked to the user
$sql = "SELECT ShoppingCartID, TotalPrice FROM shoppingcart WHERE UserID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $userid);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $shoppingCart = $result->fetch_assoc();
    $shoppingCartID = $shoppingCart['ShoppingCartID'];
    $retrieved_totalPrice = $shoppingCart['TotalPrice'];
} else {
    echo "<script>alert('No items in the shopping cart.'); window.history.back();</script>";
    exit;
}

// Query to get movie screening details and seats in the shopping cart
$sql = "
    SELECT 
        st.ScreenTimeID, st.ScreenTimeDate, st.ScreenTimeCost, c.CinemaID, c.CinemaHall, m.MovieName, s.SeatID, s.SeatNumber, m.MoviePoster
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
$totalPrice = 0.00;
while ($row = $result->fetch_assoc()) {
    $cartItems[] = $row;
    $totalPrice += $row['ScreenTimeCost'];
}


// Group items by movie
$groupedItems = [];
foreach ($cartItems as $item) {
    $movieName = $item['MovieName'];
    if (!isset($groupedItems[$movieName])) {
        $groupedItems[$movieName] = [
            'MoviePoster' => $item['MoviePoster'],
            'CinemaHall' => $item['CinemaHall'],
            'Screenings' => [],
            'TotalPrice' => 0.00
        ];
    }

    // Ensure Seats is an array
    $seats = is_array($item['SeatNumber']) ? $item['SeatNumber'] : explode(',', $item['SeatNumber']);


    $groupedItems[$movieName]['Screenings'][] = [
        'ScreeningTime' => $item['ScreenTimeDate'],
        'Seats' => $seats,
        'Price' => $item['ScreenTimeCost']
    ];
    // Add to the total price for this movie
    $groupedItems[$movieName]['TotalPrice'] += $item['ScreenTimeCost'];
}

// // Query to get items in the shopping cart
// if ($shoppingCartID) {
//     $query = "
//         SELECT st.ScreenTimeID, st.ScreenTimeDate, st.ScreenTimeCost, st.SeatingLocation, st.ScreeningMovie 
//         FROM screeningtime2 AS st
//         JOIN shoppingcart AS sc ON sc.ShoppingCartID = $shoppingCartID
//         WHERE st.ScreenTimeID = sc.ShoppingCartID";

//     $result = $conn->query($query);
// }

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="abcmovies.css">
    <link rel="stylesheet" href="cart.css">
    <link rel="icon" href="images/logo/logo.ico" type="image/x-icon">
    <link rel="stylesheet" href="about_us.css">
    <script src="footerAdjuster.js"></script>
    <title>Checkout | ABCinema</title>
    <script>
        function checkoutAlert() {
            if (confirm("Confirm checkout?")) {
                document.getElementById('checkoutForm').submit();
            }
        }
    </script>
</head>

<body style="height: fit-content;">
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
            <a class="active" href="checkout.php"><img src="images/icons/basket.svg" alt="Checkout" /></a>
            <a href="profile.php"><img src="images/icons/profile.svg" alt="Profile" /></a>
        </div>
    </div>
    <main style="margin-top: 50px;
    margin-bottom: 50px;">
        <!-- ShoppingCart -->
        <div class="container">
            <h1>Shopping Cart</h1>
            <!-- <?php if (count($cartItems) > 0): ?> -->
            <table>
                <tr>
                    <th id="small-col">Item</th>
                    <th>Information</th>
                    <th>No. of tickets</th>
                    <th>Total Cost</th>
                </tr>
                <?php $index = 1; ?>
                <?php foreach ($groupedItems as $movieName => $movieData): ?>
                    <tr>
                        <td><?php echo $index++; ?></td>
                        <td>
                            <div class="container-ticket">
                                <img src="<?php echo htmlspecialchars($movieData['MoviePoster']); ?>" alt="avatar">
                                <div class="info">
                                    <h2><?php echo htmlspecialchars($movieName); ?></h2>
                                    <p>Cinema Number: <?php echo htmlspecialchars($movieData['CinemaHall']); ?></p>
                                </div>
                            </div>
                        </td>
                        <td class="no_of_tickets">
                            <?php foreach ($movieData['Screenings'] as $screening): ?>
                                <?php echo count(value: $screening['Seats']); ?>
                                <br> <?php echo htmlspecialchars($screening['ScreeningTime']); ?>
                                <br> Seat <?php echo htmlspecialchars(implode(', ', $screening['Seats'])); ?>
                                <br><br>
                            <?php endforeach; ?>
                        </td>
                        <td>
                            $<?php echo number_format($movieData['TotalPrice'], 2); ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </table>

            <div class="total-price">
                <strong>Total Price:</strong> $<?php echo number_format($totalPrice, 2); ?>
                <br>
                <form id="checkoutForm" method="post" action="insert_bookings.php">
                    <input type="hidden" name="cartItems" value='<?php echo json_encode($cartItems); ?>'>
                    <button type="button" onclick="checkoutAlert()">Proceed to Checkout</button>
                </form>
            </div>

        <?php else: ?>
            <p>Your cart is empty.</p>
        <?php endif; ?>
        </div>
    </main>

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
                    123 Film Street #01-01 <br> Singapore 348023
                    <p>support@abcinema.com.sg
                    <p>+65 63498203
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