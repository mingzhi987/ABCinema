<?php
    session_start();
    require 'dbconnection.php';

    if (!isset($_SESSION['token_id'])) {
        echo "<script>alert('You are not allowed to access admin! token_id: ". $_SESSION['token_id']  ."'); window.location.href='login.php'</script>";
    }

    // Create a connection to the MySQL database
    $conn = new mysqli($servername, $username, $password, $database);

    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Retrieve the admin status using the token_id
    $token_id = $_SESSION['token_id'];
    $stmt = $conn->prepare("SELECT admin FROM useraccount WHERE login_token = ?");
    $stmt->bind_param("s", $token_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $user = $result->fetch_assoc();
        if ($user['admin'] != 1) {
            echo "<script>alert('You are not allowed to access admin!'); window.location.href='login.php'</script>";
            exit();
        }
    } else {
        echo "<script>alert('Invalid session!'); window.location.href='login.php'</script>";
        exit();
    }

    // Query to get the total number of movies
    $totalMoviesQuery = "SELECT COUNT(*) AS totalMovies FROM movies";
    $totalMoviesResult = $conn->query($totalMoviesQuery);
    $totalMovies = $totalMoviesResult->fetch_assoc()['totalMovies'];

    // Query to get the total sales
    $totalSalesQuery = "SELECT SUM(Price) AS totalSales FROM booking";
    $totalSalesResult = $conn->query($totalSalesQuery);
    $totalSales = $totalSalesResult->fetch_assoc()['totalSales'];

    // Query to get the total seats sold
    $totalSeatsSoldQuery = "SELECT COUNT(SeatID) AS totalSeatsSold FROM booking";
    $totalSeatsSoldResult = $conn->query($totalSeatsSoldQuery);
    $totalSeatsSold = $totalSeatsSoldResult->fetch_assoc()['totalSeatsSold'];

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="images/logo/logo.ico" type="image/x-icon">
    <link rel="stylesheet" href="abcmovies.css">
    <link rel="stylesheet" href="admin.css">
    <link rel="stylesheet" href="about_us.css">
    <script src="footerAdjuster.js"></script>
    <title>Admin Page | ABCinema</title>
</head>

<body>
    <!-- Nav bar -->
    <div class="header">
        <a href="movies.php">
            <img class=logo src="images/logo/logo.png">
        </a>
        <div class="header-left" id="admin">
            <h1>Admin Page</h1>
        </div>
        <div class="header-right">
        </div>
    </div>

    <div>
        <div class="dashboard">
            <h1>Dashboard</h1>
            <div class="dashboard-container">
                <div class="box">
                    <h3>No of movies:</h3>
                    <p><?php echo $totalMovies; ?></p>
                </div>
                <div class="box">
                    <h3>Total Sales: </h3>
                    <p>$<?php echo number_format($totalSales, 2) ?></p>
                </div>
                <div class="box">
                    <h3>Seats sold:</h3>
                    <p><?php echo $totalSeatsSold ?></p>
                </div>
            </div>
        </div>
        <div class="admin-container">
            <form id="myForm">
                <label for="function">Choose an item to edit:</label>
                <select name="function" id="function-id" onchange="toggleTables()">
                    <option value="movies" selected>Movies</option>
                    <option value="screeningtime2">Screening Time</option>
                </select>
            </form>
            </select>
            <div id="movieTable">
                <h1>Movies</h1>
                <table border="1" class="movies-table">
                    <tr>
                    <th>MovieID</th>
                    <th>MovieName</th>
                    <th>MovieGenre</th>
                    <th>MovieLength</th>
                    <th>MovieRating</th>
                    <th>MovieDesc</th>
                    <th></th>
                    </tr>
                    <tr>
                    <td><input type="text" value="1" readonly></td>
                    <td><input type="text" value="Avatar" readonly></td>
                    <td><input type="text" value="Sci-Fi" readonly></td>
                    <td><input type="text" value="120" readonly></td>
                    <td><input type="text" value="9" readonly></td>
                    <td><textarea readonly> In the forest, far into the future</textarea></td>
                    <td>
                        <button type="button" onclick="alert('Item Deleted')">Delete</button>
                    </td>
                    </tr>
                </table>
            </div>
            <div id="screeningTable">
                <h1>Screening Time</h1>
                <table border="1" class="screening-table">
                    <tr>
                    <th>ScreenTimeID</th>
                    <th>ScreenTimeDate</th>
                    <th>ScreenTimeCost</th>
                    <th>SeatingLocation</th>
                    <th>ScreeningMovie</th>
                    <th>Delete Item</th>
                    </tr>
                    <tr>
                    <td>1</td>
                    <td>23/12/2024 15:00</td>
                    <td>15.00</td>
                    <td>2</td>
                    <td>Avatar</td>
                    <td>
                        <button type="button" onclick="alert('Item Deleted')">Delete</button>
                    </td>
                    </tr>
                </table>
            </div>
            <button type="button" id="modifyButton">Modify</button>
            <button type="button" id="updateButton" onclick="alert('Changes Updated')">Update</button>
        </div>
       
    </div>
</body>
    <main style="flex-grow: 1;">
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
                    123 Raffles Place #14-01 <br> Singapore 348123
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
    </main>
    </footer>
</footer>

<script>
    // Get all text inputs and textareas in the form
    const inputs = document.querySelectorAll("#myForm input[type='text'], #myForm textarea");
    const modifyButton = document.getElementById("modifyButton");
    const updateButton = document.getElementById("updateButton");

    function toggleTables() {
        var selectedValue = document.getElementById('function-id').value;
        console.log(selectedValue);
        var moviesTable = document.getElementById('movieTable');
        var screeningTable = document.getElementById('screeningTable');

        if (selectedValue === 'movies') {
            moviesTable.style.display = 'flex';
            screeningTable.style.display = 'none';
        } else if (selectedValue === 'screeningtime2') {
            moviesTable.style.display = 'none';
            screeningTable.style.display = 'flex';
        }
    }

    // Function to make inputs editable
    function enableEditing() {
        inputs.forEach(input => input.readOnly = false);
    }

    // Function to make inputs readonly
    function disableEditing() {
        inputs.forEach(input => input.readOnly = true);
    }

    // Event listeners for buttons
    modifyButton.addEventListener("click", enableEditing);
    updateButton.addEventListener("click", disableEditing);

    // Set inputs to readonly by default
    toggleTables();
    disableEditing();

</script>

</html>