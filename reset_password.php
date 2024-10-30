<?php
// reset_password.php
session_start(); // Start a session to store error messages if needed

if (isset($_GET['token'])) {
    $token = $_GET['token'];
    $email = base64_decode($token); // Decode the token to get the email

    // Validate the email address (check if it is a valid email format)
    if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
        // Here, you would typically check if the email exists in your database
        // For example:
        // $userExists = checkUserExists($email); // Implement this function based on your database logic

        // Example condition: assume $userExists is true if the user exists
        $userExists = true; // Replace this with your actual check

        if ($userExists) {
            // Display the password reset form
            ?>
            <!DOCTYPE html>
            <html lang="en">
            <head>
                <meta charset="UTF-8">
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
                <title>Reset Password</title>
            </head>
            <body>
                <h2>Reset Password</h2>
                <form action="update_password.php" method="POST">
                    <input type="hidden" name="email" value="<?php echo htmlspecialchars($email); ?>">
                    <label for="new_password">New Password:</label>
                    <input type="password" name="new_password" id="new_password" required>
                    <label for="confirm_password">Confirm Password:</label>
                    <input type="password" name="confirm_password" id="confirm_password" required>
                    <button type="submit">Update Password</button>
                </form>
            </body>
            </html>
            <?php
        } else {
            // User does not exist
            echo "<p>Invalid reset link. Please request a new password reset.</p>";
        }
    } else {
        // Invalid email format
        echo "<p>Invalid token. Please request a new password reset.</p>";
    }
} else {
    // Token not provided
    echo "<p>No reset token provided.</p>";
}
?>