<?php
session_start();

if (!isset($_SESSION['token_id'])) {
    header("Location: login.php");
    exit; // Redirect to login if not logged in
}

$token_id = $_SESSION['token_id'];

// Split the token_id to get userid and username
list($userid, $username) = explode(":", $token_id);



// Database credentials
$servername = "localhost";  // or your server's IP address
$username = "root"; // replace with your MySQL username
$password = ""; // replace with your MySQL password
$database = "abcinema_db"; // replace with your database name

$conn = new mysqli($servername, $username, $password, $database);
if ($conn->connect_error) {
    echo json_encode(["success" => false, "message" => "Database connection failed"]);
    exit;
}

// Retrieve user details
$sql = "SELECT Username, FullName, Email, DateOfBirth, Password FROM useraccount WHERE UserID = ". $userid ."";
$stmt = $conn->prepare($sql);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
    // echo json_encode(["success" => true, "user" => $user]);
} else {
    // echo json_encode(["success" => false, "message" => "User not found"]);
}

$stmt->close();
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