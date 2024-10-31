<?php
require 'dbconnection.php';

session_start();

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



// Retrieve booking details
$sql = "SELECT * FROM booking 
        NATURAL JOIN shoppingcart 
        NATURAL JOIN screeningtime2 
        WHERE UserID = ". $userid ."";
$query = $conn->prepare($sql);
$query->execute();
$bookingresult = $query->get_result();

// Check if any booking details exist
$hasBookings = $bookingresult->num_rows > 0;


$stmt->close();
$query->close();
$conn->close();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>User Profile</title>
</head>
<body>
    <h2>User Profile</h2>
    <p><strong>Username:</strong> <?php echo htmlspecialchars($user['Username']); ?></p>
    <p><strong>Full Name:</strong> <?php echo htmlspecialchars($user['FullName']); ?></p>
    <p><strong>Email:</strong> <?php echo htmlspecialchars($user['Email']); ?></p>
    <p><strong>Date of Birth:</strong> <?php echo htmlspecialchars($user['DateOfBirth']); ?></p>
    <br/>
    <!-- Password field for editing -->
    <p><strong>Edit Password:</strong> 
        <input type="password" id="password" disabled>
        <input type="checkbox" onclick="togglePasswordEdit()"> Edit
    </p>
    <p><strong>Edit Email:</strong> 
        <input type="email" id="email" disabled>
        <input type="checkbox" onclick="toggleEmailEdit()"> Edit
    </p>
    <button id="savedetails" onclick="saveNewDetails()" disabled>Save Changes</button>
    <button onclick="location.href='logout.php'">Log Out</button>
    <br/>
    <!-- Booking Details Section -->
    <h2>Your Bookings</h2>

    <?php if ($hasBookings): ?>
        <table>
            <tr>
                <th>Booking ID</th>
                <th>Screening Time</th>
                <th>Item</th>
                <th>Quantity</th>
                <th>Total Price</th>
                <!-- Add more columns based on your database fields -->
            </tr>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['BookingID']); ?></td>
                    <td><?php echo htmlspecialchars($row['ScreeningTime']); ?></td>
                    <td><?php echo htmlspecialchars($row['Item']); ?></td>
                    <td><?php echo htmlspecialchars($row['Quantity']); ?></td>
                    <td><?php echo htmlspecialchars($row['TotalPrice']); ?></td>
                    <!-- Display other relevant booking details -->
                </tr>
            <?php endwhile; ?>
        </table>
    <?php else: ?>
        <p>You have no tickets currently.</p>
    <?php endif; ?>
    
    <p><a href="display_movie_info.php">Go to Display Movie Info</a></p>

    <script>

        var detailCheckPw = false;
        var detailCheckEd = false;

        function togglePasswordEdit() {
            document.getElementById("password").disabled = !document.getElementById("password").disabled;
            detailCheckPw = !detailCheckPw;
            toggleSaveBtn(detailCheckPw, detailCheckEd);
        }
        function toggleEmailEdit() {
            document.getElementById("email").disabled = !document.getElementById("email").disabled;
            detailCheckEd = !detailCheckEd;
            toggleSaveBtn(detailCheckPw, detailCheckEd);
        }

        function toggleSaveBtn(detailCheckPw, detailCheckEd){

            if (detailCheckPw || detailCheckEd){
                document.getElementById("savedetails").disabled = false;
            } else {
                document.getElementById("savedetails").disabled = true;
            }

        }

        function saveNewDetails() {
            const email = document.getElementById("email").value;
            const password = document.getElementById("password").value;
            const originalEmail = "<?php echo htmlspecialchars($user['Email']); ?>"; // Original email for comparison

            // Check if either field has been modified
            const emailChanged = email !== originalEmail;
            const passwordChanged = password.trim() !== "";

            if (!emailChanged && !passwordChanged) {
                alert("No changes made.");
                return;
            }

            const xhr = new XMLHttpRequest();
            xhr.open("POST", "update_user_details.php", true);
            xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");

            xhr.onreadystatechange = function() {
                if (xhr.readyState === 4 && xhr.status === 200) {
                    alert(xhr.responseText); // Show response from server
                }
            };

            // Prepare data to send
            const data = `email=${encodeURIComponent(emailChanged ? email : "")}&password=${encodeURIComponent(passwordChanged ? password : "")}`;
            xhr.send(data);
        }

    </script>
</body>
</html>