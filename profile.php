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

// Handle modify booking
if (isset($_POST['modify_booking']) && isset($_POST['booking_id']) && isset($_POST['new_showtime']) && isset($_POST['new_seat'])) {
    $bookingID = intval($_POST['booking_id']);
    $newShowtime = $_POST['new_showtime'];
    $newSeat = intval($_POST['new_seat']);

    // Check if the new showtime and seat are available
    $sql = "SELECT * FROM booking WHERE Showtime = ? AND SeatNo = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $newShowtime, $newSeat);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        echo "<script>alert('The selected showtime and seat are already taken.'); window.location.href='profile.php'</script>";
    } else {
        // Update the booking with the new showtime and seat
        $sql = "UPDATE booking SET Showtime = ?, SeatNo = ? WHERE BookingID = ? AND UserID = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("siii", $newShowtime, $newSeat, $bookingID, $userid);
        if ($stmt->execute()) {
            echo "<script>alert('Booking modified successfully!'); window.location.href='profile.php'</script>";
        } else {
            echo "<script>alert('Error modifying booking: " . $stmt->error . "');</script>";
        }
    }

    $stmt->close();
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
            <a href="#"><img src="images/icons/basket.svg" alt="Checkout" /></a>
            <a href="#"><img src="images/icons/profile.svg" alt="Profile" /></a>
        </div>
    </div>

    <div class="container">
        <h1>Profile Details</h1>
        <form method="POST" action="">
            <label>UserID: </label>
            <input type="text" value="<?php echo $user['UserID']; ?>" disabled><br>

            <label>Username: </label>
            <input type="text" name="username" value="<?php echo $user['Username']; ?>" disabled><br>

            <label>Email: </label>
            <input type="email" name="email" value="<?php echo $user['Email']; ?>"><br>

            <label>Full Name: </label>
            <input type="text" value="<?php echo $user['FullName']; ?>" disabled><br>

            <label>Password: </label>
            <input type="password" name="password" value="<?php echo $user['Password']; ?>"><br>

            <label>Date of Birth: </label>
            <input type="date" value="<?php echo $user['DateOfBirth']; ?>" disabled><br>

            <button type="submit" name="update_profile">Update Profile</button>
        </form>
        <button onclick="location.href='logout.php'">Log Out</button>
    </div>

    <div class="container">
        <h1>Tickets</h1>
        <table border="1">
            <tr>
                <th>BookingID</th>
                <th>PaymentDate</th>
                <th>UserID</th>
                <th>ShoppingCartID</th>
                <th>MovieName</th>
                <th>Showtime</th>
                <th>Cinema Hall</th>
                <th>Seat Number</th>
                <th>Gift</th>
                <th>Modify/Refund</th>
            </tr>
            <?php
            // Retrieve bookings / tickets for the user
            $bookingQuery = "SELECT * FROM booking WHERE UserID = '$userid'";
            $bookingResult = $conn->query($bookingQuery);

            if ($bookingResult->num_rows > 0) {
                while ($booking = $bookingResult->fetch_assoc()) {
                    $showtime = new DateTime($booking['Showtime']);
                    $now = new DateTime();
                    $interval = $now->diff($showtime);
                    $daysUntilShowtime = $interval->format('%a');


                    echo "<tr>";
                    echo "<td>" . $booking['BookingID'] . "</td>";
                    echo "<td>" . $booking['PaymentDate'] . "</td>";
                    echo "<td>" . $booking['UserID'] . "</td>";
                    echo "<td>" . $booking['ShoppingCartID'] . "</td>";
                    echo "<td>" . $booking['MovieName'] . "</td>";
                    echo "<td>" . $booking['Showtime'] . "</td>";
                    echo "<td>" . $booking['CinemaHall'] . "</td>";
                    echo "<td>" . $booking['SeatNo'] . "</td>";
                    echo "<td>";
                    echo "<form method='POST' action='' style='display:inline-block;'>";
                    echo "<input type='hidden' name='booking_id' value='" . $booking['BookingID'] . "'>";
                    echo "<input type='text' name='recipient_email' placeholder='Recipient email'>";
                    echo "<button type='submit' name='gift_booking'>Gift</button>";
                    echo "</form>";
                    echo "</td>";
                    echo "<td>";
                    if ($daysUntilShowtime > 2) {
                        echo "<form method='POST' action='' style='display:inline-block;'>";
                        echo "<input type='hidden' name='booking_id' value='" . htmlspecialchars($booking['BookingID']) . "'>";
                        echo "<button type='submit' name='refund_booking'>Refund</button>";
                        echo "</form> <br/>";
                        echo "<form method='POST' action='change_booking.php' style='display:inline-block;'>";
                        echo "<input type='hidden' name='booking_id' value='" . htmlspecialchars($booking['BookingID']) . "'>";
                        echo "<button type='submit' name='modify_booking'>Modify</button>";
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
</body>

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

<?php
// Close the database connection
$conn->close();
?>