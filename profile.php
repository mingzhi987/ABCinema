<?php
// Start the session
session_start();

require 'dbconnection.php';



if (!isset($_SESSION['token_id'])) {
    header("Location: login.php");
    exit; // Redirect to login if not logged in
}

$conn = new mysqli($servername, $username, $password, $database);
if ($conn->connect_error) {
    echo json_encode(["success" => false, "message" => "Database connection failed"]);
    exit;
}

/// Retrieve user details using the login token
$login_token = $_SESSION['token_id'];
$sql = "SELECT * FROM useraccount WHERE login_token = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $login_token);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
    $userid = $user['UserID'];
    // echo json_encode(["success" => true, "user" => $user]);
} else {
    // echo json_encode(["success" => false, "message" => "User not found"]);
    header("Location: login.php");
    exit;
}


// Update user details if form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_profile'])) {
    $newEmail = $_POST['email'];
    $newPassword = $_POST['password'];

    $updateQuery = "UPDATE useraccount SET Email='$newEmail', Password='$newPassword' WHERE UserID = '$userID'";
    if ($conn->query($updateQuery) === TRUE) {
        echo "<script>alert('Profile updated successfully!'); window.location.href = 'profile_zm.php';</script>";
    } else {
        echo "Error updating profile: " . $conn->error;
    }
}

// Handle refund booking
if (isset($_POST['refund_booking']) && isset($_POST['booking_id'])) {
    $bookingID = intval($_POST['booking_id']);

    // Delete the booking
    $sql = "DELETE FROM booking WHERE BookingID = ? AND UserID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $bookingID, $userid);
    if ($stmt->execute()) {
        echo "<script>alert('Booking refunded successfully!'); window.location.href='profile.php'</script>";
    } else {
        echo "<script>alert('Error refunding booking: " . $stmt->error . "');</script>";
    }

    $stmt->close();
}


// Handle gifting booking
if (isset($_POST['gift_booking']) && isset($_POST['recipient_email']) && isset($_POST['booking_id'])) {
    $recipientEmail = $_POST['recipient_email'];
    $bookingID = $_POST['booking_id'];

    // Check if recipient email exists
    $recipientQuery = "SELECT UserID FROM useraccount WHERE Email = '$recipientEmail'";
    $recipientResult = $conn->query($recipientQuery);

    if ($recipientResult->num_rows > 0) {
        $recipient = $recipientResult->fetch_assoc();
        $recipientUserID = $recipient['UserID'];

        // Update the booking with the new UserID
        $giftQuery = "UPDATE booking SET UserID = '$recipientUserID' WHERE BookingID = '$bookingID'";
        if ($conn->query($giftQuery) === TRUE) {
            echo "<script>alert('Booking gifted successfully!'); window.location.href='profile.php'</script>";
        } else {
            echo "<script>alert('Error updating booking: " . $stmt->error . "');</script>";
        }
    } else {
        echo "<script>alert('Invalid recipient email.');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Profile Page</title>
    <link rel="stylesheet" href="abcmovies.css">
    <link rel="stylesheet" href="about_us.css">
    <script>
        function promptEmail(bookingID) {
            var recipientEmail = prompt("Please enter the recipient's email:");
            if (recipientEmail != null && recipientEmail != "") {
                var form = document.createElement("form");
                form.method = "POST";
                form.action = "";

                var bookingInput = document.createElement("input");
                bookingInput.type = "hidden";
                bookingInput.name = "booking_id";
                bookingInput.value = bookingID;
                form.appendChild(bookingInput);

                var emailInput = document.createElement("input");
                emailInput.type = "hidden";
                emailInput.name = "recipient_email";
                emailInput.value = recipientEmail;
                form.appendChild(emailInput);

                var giftInput = document.createElement("input");
                giftInput.type = "hidden";
                giftInput.name = "gift_booking";
                giftInput.value = "1";
                form.appendChild(giftInput);

                document.body.appendChild(form);
                form.submit();
            }
        }
    </script>
    <script src="footerAdjuster.js"></script>
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
            <a href="profile.php"><img src="images/icons/profile.svg" alt="Profile" /></a>
        </div>
    </div>

    <div class="container" style="display: flex;
    justify-content: center;
    width: 100%;">
        <div class="profile-details" style="width: 70%">
            <h1>Profile Details</h1>
            <form method="POST" action="" style="margin: 5px;">
                <input type="hidden" name="userid" value="<?php echo $user['UserID']; ?>">
                <label>Username: </label>
                <input type="text" name="username" value="<?php echo $user['Username']; ?>" disabled>
    
                <label>Email: </label>
                <input type="email" name="email" value="<?php echo $user['Email']; ?>">
    
                <label>Full Name: </label>
                <input type="text" value="<?php echo $user['FullName']; ?>" disabled>
    
                <label>Password: </label>
                <input type="password" name="password" value="<?php echo $user['Password']; ?>">
    
                <label>Date of Birth: </label>
                <input type="date" value="<?php echo $user['DateOfBirth']; ?>" disabled>
    
                <div style="display: flex; gap:20px;">
                    <button id="submitUpdates" type="submit" name="update_profile">Update Profile</button>
                    <button type="button" onclick="location.href='logout.php'">Log Out</button>
                </div>
            </form>
        </div>
    </div>

    <div class="super-nonsense" style="display: flex; justify-content: center; margin-top: 50px; margin-bottom: 40px;">
        <div class="container" style="display: flex;
        justify-content: center;
        width: 70%; flex-direction: column;">
            <h1>Tickets</h1>
            <table border="1">
                <tr>
                    <th>Payment Made</th>
                    <th>Movie</th>
                    <th>Showtime</th>
                    <th>Cinema Hall</th>
                    <th>Seat Number</th>
                    <th>Actions</th>
                </tr>
                <?php
                // Retrieve bookings / tickets for the user
                $bookingQuery = "
                SELECT 
                    b.BookingID, b.PaymentDate, b.UserID, b.ShoppingCartID, b.MovieName, 
                    st.ScreenTimeDate AS Showtime, c.CinemaHall, s.SeatNumber 
                FROM 
                    booking AS b
                JOIN 
                    screeningtime2 AS st ON b.Showtime = st.ScreenTimeID
                JOIN 
                    cinema AS c ON b.CinemaID = c.CinemaID
                JOIN 
                    seating AS s ON b.SeatID = s.SeatID
                WHERE 
                    b.UserID = ?";
                $stmt = $conn->prepare($bookingQuery);
                $stmt->bind_param("i", $userid);
                $stmt->execute();
                $bookingResult = $stmt->get_result();
    
                if ($bookingResult->num_rows > 0) {
                    while ($booking = $bookingResult->fetch_assoc()) {
                        $showtime = new DateTime($booking['Showtime']);
                        $now = new DateTime();
                        $interval = $now->diff($showtime);
                        $daysUntilShowtime = $interval->format('%a');
    
    
                        echo "<tr>";
                        echo "<td>" . $booking['PaymentDate'] . "</td>";
                        echo "<td>" . $booking['MovieName'] . "</td>";
                        echo "<td>" . $booking['Showtime'] . "</td>";
                        echo "<td>" . $booking['CinemaHall'] . "</td>";
                        echo "<td>" . $booking['SeatNumber'] . "</td>";
                        echo "<td class='actions-container'>";
                        if ($daysUntilShowtime > 2) {
                            echo "<button type='button' class='btn btn-gift' onclick='promptEmail(" . $booking['BookingID'] . ")'>Gift</button> ";

                            echo "<form method='POST' action='change_booking.php' style='display:flex;'>";
                            echo "<input type='hidden' name='booking_id' value='" . htmlspecialchars($booking['BookingID']) . "'>";
                            echo "<button type='submit' name='modify_booking' class='btn btn-modify'>Modify</button>";
                            echo "</form>";

                            echo "<form method='POST' action='' style='display:flex;'>";
                            echo "<input type='hidden' name='booking_id' value='" . htmlspecialchars($booking['BookingID']) . "'>";
                            echo "<button type='submit' name='refund_booking' class='btn btn-refund'>Refund</button>";
                            echo "</form>";
                        } else {
                            echo "Modification/Refund not allowed within 2 days of showtime.";
                        }
                        echo "</td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='9'>No bookings found.</td></tr>";
                }
                ?>
            </table>
        </div>
    </div>
</body>

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

</html>

<?php
// Close the database connection
$conn->close();
?>