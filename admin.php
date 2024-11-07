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

    // Query to get movie details along with cinema ID and hall
    $moviesQuery = "
    SELECT m.MovieID, m.MovieName, m.MovieGenre, m.MovieLength, m.MovieRating, m.MovieDesc, m.MoviePoster, c.CinemaID, c.CinemaHall
    FROM movies m
    LEFT JOIN cinema c ON m.MovieID = c.MovieAllocated
    ";
    $moviesResult = $conn->query($moviesQuery);


    // Create or Update Screening Time
    if (isset($_POST['saveScreeningTime'])) {
        $ScreenTimeID = $_POST['ScreenTimeID'];
        $ScreenTimeDate = $_POST['ScreenTimeDate'];
        $ScreenTimeCost = $_POST['ScreenTimeCost'];
        $ScreeningMovie = $_POST['ScreeningMovie'];

        // Retrieve the CinemaID based on the selected ScreeningMovie
        $query = "SELECT CinemaID FROM cinema WHERE MovieAllocated = '$ScreeningMovie' LIMIT 1";
        $result = mysqli_query($conn, $query);
        $row = mysqli_fetch_assoc($result);
        $SeatingLocation = $row['CinemaID'];

        if ($ScreenTimeID) {
            // Update existing screening time
            $query = "UPDATE screeningtime2 SET ScreenTimeDate='$ScreenTimeDate', ScreenTimeCost='$ScreenTimeCost', SeatingLocation='$SeatingLocation', ScreeningMovie='$ScreeningMovie' WHERE ScreenTimeID='$ScreenTimeID'";
        } else {
            // Insert new screening time
            $query = "INSERT INTO screeningtime2 (ScreenTimeDate, ScreenTimeCost, SeatingLocation, ScreeningMovie) VALUES ('$ScreenTimeDate', '$ScreenTimeCost', '$SeatingLocation', '$ScreeningMovie')";
        }
        mysqli_query($conn, $query);
        header('Location: admin.php');
    }

    // Delete Screening Time
    if (isset($_GET['deleteScreenTimeID'])) {
        $ScreenTimeID = $_GET['deleteScreenTimeID'];
        $query = "DELETE FROM screeningtime2 WHERE ScreenTimeID='$ScreenTimeID'";
        mysqli_query($conn, $query);
        header('Location: admin.php');
    }

    // Fetch Screening Times
    $screeningTimes = mysqli_query($conn, "SELECT * FROM screeningtime2");

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
                <label for="function">Choose an item to edit:</label>
                <select name="function" id="function-id" onchange="toggleTables()">
                    <option value="movies" selected>Movies</option>
                    <option value="screeningtime2">Screening Time</option>
                </select>
            </select>
            <div id="movieTable">
                <h1>Movies</h1>
                <button type="button" onclick="addNewRow()">Add New Movie Row</button>
                <table border="1" class="movies-table">
                    <tr>
                    <th>MovieID</th>
                    <th>Movie Name</th>
                    <th>Genre</th>
                    <th>Movie Length</th>
                    <th>Rating</th>
                    <th>Description</th>
                    <th>Cinema ID</th>
                    <th>Cinema Hall</th>
                    <th></th>
                    </tr>
                    <?php while ($row = $moviesResult->fetch_assoc()): ?>
                        <tr>
                        <form id="movieForm" method="POST" action="update_movie.php">
                            <td><input type="text" name="MovieID" value="<?php echo $row['MovieID']; ?>" disabled></td>
                            <td><input type="text" name="MovieName" value="<?php echo $row['MovieName']; ?>" disabled></td>
                            <td><input type="text" name="MovieGenre" value="<?php echo $row['MovieGenre']; ?>" disabled></td>
                            <td><input type="text" name="MovieLength" value="<?php echo $row['MovieLength']; ?>" disabled></td>
                            <td><input type="text" name="MovieRating" value="<?php echo $row['MovieRating']; ?>" disabled></td>
                            <td><textarea name="MovieDesc" disabled><?php echo $row['MovieDesc']; ?></textarea></td>
                            <td><input type="text" name="CinemaID" value="<?php echo $row['CinemaID']; ?>" disabled></td>
                            <td><input type="text" name="CinemaHall" value="<?php echo $row['CinemaHall']; ?>" disabled></td>
                            <td>
                                <button type="button" onclick="enableEditing(this.closest('tr'))">Modify</button>
                                <button type="button" onclick="disableEditing(this.closest('tr'))">Update</button>
                                <button type="button" onclick="deleteMovie(<?php echo $row['MovieID']; ?>)">Delete</button>
                            </td> 
                        </form>
                        </tr>
                    <?php endwhile; ?>
                </table>
            </div>
            
            <div id="screeningTable">
                <h1>Screening Time</h1>
                <button type="button" onclick="showAddEditScreeningTimeForm()">Add New Screening Time</button>
                <div id="addEditScreeningTimeForm" style="display:none; margin: auto;">
                    <h1 id="formTitle">Add/Edit Screening Time</h1>
                    <form id="screeningTimeForm" method="post" action="admin.php">
                        <input type="hidden" name="ScreenTimeID" id="ScreenTimeID">
                        <label for="ScreenTimeDate">Date:</label>
                        <input type="datetime-local" name="ScreenTimeDate" id="ScreenTimeDate" required>
                        <label for="ScreenTimeCost">Cost:</label>
                        <input type="number" step="0.01" name="ScreenTimeCost" id="ScreenTimeCost" required>
                        <label for="ScreeningMovie">Movie:</label>
                        <select name="ScreeningMovie" id="ScreeningMovie" required>
                            <?php
                            // Fetch movies from the database
                            $movies = mysqli_query($conn, "SELECT MovieID, MovieName FROM movies");
                            while ($movie = mysqli_fetch_assoc($movies)) {
                                echo "<option value='{$movie['MovieID']}'>{$movie['MovieName']}</option>";
                            }
                            ?>
                        </select>
                        <button type="submit" name="saveScreeningTime">Save</button>
                        <button type="button" onclick="hideAddEditScreeningTimeForm()">Cancel</button>
                    </form>
                </div>
                <table border="1" class="screening-table">
                    <tr>
                        <th>ScreenTimeID</th>
                        <th>ScreenTimeDate</th>
                        <th>ScreenTimeCost</th>
                        <th>SeatingLocation</th>
                        <th>ScreeningMovie</th>
                        <th>Actions</th>
                    </tr>
                    <?php while ($screeningTime = mysqli_fetch_assoc($screeningTimes)) { ?>
                    <tr>
                        <td><?php echo $screeningTime['ScreenTimeID']; ?></td>
                        <td><?php echo $screeningTime['ScreenTimeDate']; ?></td>
                        <td><?php echo $screeningTime['ScreenTimeCost']; ?></td>
                        <td><?php echo $screeningTime['SeatingLocation']; ?></td>
                        <td><?php echo $screeningTime['ScreeningMovie']; ?></td>
                        <td>
                            <button type="button" onclick='showAddEditScreeningTimeForm(<?php echo json_encode($screeningTime); ?>)'>Edit</button>
                            <button type="button" onclick="location.href='admin.php?deleteScreenTimeID=<?php echo $screeningTime['ScreenTimeID']; ?>'">Delete</button>
                        </td>
                    </tr>
                    <?php } ?>
                </table>
            </div>
        </div>
       
    </div>
    <div id="addEditScreeningTimeForm" style="display:none;">
    <h1 id="formTitle">Add/Edit Screening Time</h1>
    <form id="screeningTimeForm" method="post" action="admin.php">
        <input type="hidden" name="ScreenTimeID" id="ScreenTimeID">
        <label for="ScreenTimeDate">Date:</label>
        <input type="datetime-local" name="ScreenTimeDate" id="ScreenTimeDate" required>
        <label for="ScreenTimeCost">Cost:</label>
        <input type="number" step="0.01" name="ScreenTimeCost" id="ScreenTimeCost" required>
        <label for="ScreeningMovie">Movie:</label>
        <select name="ScreeningMovie" id="ScreeningMovie" required>
            <?php
            // Fetch movies from the database
            $movies = mysqli_query($conn, "SELECT MovieID, MovieName FROM movies");
            while ($movie = mysqli_fetch_assoc($movies)) {
                echo "<option value='{$movie['MovieID']}'>{$movie['MovieName']}</option>";
            }
            ?>
        </select>
        <button type="submit" name="saveScreeningTime">Save</button>
        <button type="button" onclick="hideAddEditScreeningTimeForm()">Cancel</button>
    </form>
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
    </footer>

<script>
    // Get all text inputs and textareas in the form

    const inputs = document.querySelectorAll("#myForm input[type='text'], #myForm textarea");

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
    function enableEditing(row) {
        const wtf = row.querySelectorAll("#movieTable input[type='text'], #movieTable textarea");
        wtf.forEach(wtf => wtf.removeAttribute("disabled"));
    }

    // Function to make inputs readonly
    function disableEditing(row) {
        
        if (confirm('Do you want to save these modifications?')) {
             // Submit the closest form
            const form = row.querySelector("form");
            if (form) {
                form.submit();
            } else {
                console.error("Form not found for the row.");
            }
            const inputs = row.querySelectorAll("#movieTable input[type='text'], #movieTable textarea");
            inputs.forEach(input => input.disabled = true);
        }
    }

     // Function to show the add movie table
     function addNewRow() {
        var addMovieTable = document.getElementById('addMovieTable');
        addMovieTable.style.display = 'flex';
    }

    // Function to submit the new movie form
    function submitNewMovieForm() {
        var form = document.getElementById('addMovieForm');
        form.submit();
    }

    // Function to hide the add movie table
    function hideAddMovieTable() {
        var addMovieTable = document.getElementById('addMovieTable');
        addMovieTable.style.display = 'none';
    }

    function showAddEditScreeningTimeForm(screeningTime = null) {
        document.getElementById('addEditScreeningTimeForm').style.display = 'block';
        if (screeningTime) {
            document.getElementById('formTitle').innerText = 'Edit Screening Time';
            document.getElementById('ScreenTimeID').value = screeningTime.ScreenTimeID;
            document.getElementById('ScreenTimeDate').value = screeningTime.ScreenTimeDate;
            document.getElementById('ScreenTimeCost').value = screeningTime.ScreenTimeCost;
            document.getElementById('SeatingLocation').value = screeningTime.SeatingLocation;
            document.getElementById('ScreeningMovie').value = screeningTime.ScreeningMovie;
        } else {
            document.getElementById('formTitle').innerText = 'Add Screening Time';
            document.getElementById('screeningTimeForm').reset();
            document.getElementById('SeatingLocation').value = '';
        }
    }

    function hideAddEditScreeningTimeForm() {
        document.getElementById('addEditScreeningTimeForm').style.display = 'none';
    }

    // Set inputs to readonly by default
    toggleTables();
    //disableEditing();

</script>

</html>