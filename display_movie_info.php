<?php
session_start();
require 'dbconnection.php';

// MovieID to be displayed
$movieID = 1;

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

$isLoggedIn = isset($_SESSION['token_id']);

echo "wtf: " . $isLoggedIn;

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
    <h1><?php echo htmlspecialchars($movie['MovieName']); ?></h1>
    <p>Genre: <?php echo htmlspecialchars($movie['MovieGenre']); ?></p>
    <p>Length: <?php echo htmlspecialchars($movie['MovieLength']); ?> minutes</p>
    <p>Synopsis: <?php echo htmlspecialchars($movie['MovieDesc']) ?></p>
    <p>Rating: <?php echo htmlspecialchars($movie['MovieRating']); ?></p>
    <img src="<?php echo htmlspecialchars($movie['MovieImg']); ?>" alt="<?php echo htmlspecialchars($movie['MovieName']); ?>">

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
</body>
</html>