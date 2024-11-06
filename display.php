<?php
// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "abcinema_db"; // Replace with your actual database name

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Retrieve all cinema numbers for the dropdown
$cinemaNumbers = [];
$cinemaResult = $conn->query("SELECT DISTINCT CinemaNumber FROM seating");
if ($cinemaResult->num_rows > 0) {
    while ($row = $cinemaResult->fetch_assoc()) {
        $cinemaNumbers[] = $row['CinemaNumber'];
    }
}

// Get selected cinema number from dropdown or default to the first option
$selectedCinemaNumber = isset($_GET['cinemaNumber']) ? $_GET['cinemaNumber'] : (isset($cinemaNumbers[0]) ? $cinemaNumbers[0] : null);

// Handle seat selection submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['selectedSeats'])) {
    $selectedSeats = $_POST['selectedSeats'];
    $userID = 1; // Replace with actual user ID
    $shoppingCartID = 1; // Replace with actual shopping cart ID
    $movieName = "Sample Movie"; // Replace with actual movie name
    $showtime = "2024-11-06 18:00:00"; // Replace with actual showtime
    $cinemaID = $selectedCinemaNumber;
    $paymentDate = date("Y-m-d H:i:s"); // Current date and time

    // Insert each selected seat as a new booking
    foreach ($selectedSeats as $seatID) {
        $sql = "INSERT INTO booking (PaymentDate, UserID, ShoppingCartID, MovieName, Showtime, CinemaID, SeatID)
                VALUES ('$paymentDate', $userID, $shoppingCartID, '$movieName', '$showtime', $cinemaID, $seatID)";
        $conn->query($sql);
    }
}

// Initialize seats array for seat arrangement display
$seats = [];
if ($selectedCinemaNumber !== null) {
    // Query to get seat information and check if occupied by joining with booking table
    $sql = "SELECT s.SeatID, s.SeatNumber, 
                   CASE WHEN b.SeatID IS NOT NULL THEN 1 ELSE 0 END AS occupied
            FROM seating s
            LEFT JOIN booking b ON s.SeatID = b.SeatID
            WHERE s.CinemaNumber = $selectedCinemaNumber";
    
    $result = $conn->query($sql);

    // Populate the seats array with seat IDs, numbers, and occupancy status
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $seats[$row['SeatNumber']] = [
                'SeatID' => $row['SeatID'],
                'occupied' => $row['occupied']
            ];
        }
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="seat_display.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Seating Arrangement</title>
    <script>
        function toggleSelection(seatElement, seatID) {
            if (seatElement.classList.contains('occupied')) return;

            seatElement.classList.toggle('selected');
            const seatInput = document.getElementById('seat_' + seatID);
            seatInput.checked = seatElement.classList.contains('selected');
        }
    </script>
</head>
<body>

<h2>Select Cinema and View Seating Arrangement</h2>
<!-- Dropdown to select cinema number -->
<form method="GET" action="seating_display.php">
    <label for="cinemaNumber">Cinema Number:</label>
    <select name="cinemaNumber" id="cinemaNumber" onchange="this.form.submit()">
        <?php
        foreach ($cinemaNumbers as $cinemaNumber) {
            $selected = ($cinemaNumber == $selectedCinemaNumber) ? 'selected' : '';
            echo "<option value='$cinemaNumber' $selected>Cinema $cinemaNumber</option>";
        }
        ?>
    </select>
</form>

<h3>Seating Arrangement for Cinema <?php echo htmlspecialchars($selectedCinemaNumber); ?></h3>
<form method="POST" action="seating_display.php?cinemaNumber=<?php echo htmlspecialchars($selectedCinemaNumber); ?>">   
<div id="cinema-container">
<div id="screen"><p>Screen</p></div>
</div>
<div id="seating">
        <!-- Row A (Seats 1-5) -->
        <div>
            <div class="row-label">A</div>
            <?php
            for ($i = 1; $i <= 5; $i++) {
                $seatInfo = isset($seats[$i]) ? $seats[$i] : ['SeatID' => null, 'occupied' => 0];
                $seatID = $seatInfo['SeatID'];
                $class = $seatInfo['occupied'] ? 'occupied' : 'not-occupied';
                echo "<div class='seat $class' onclick='toggleSelection(this, $seatID)'>$i</div>";
                if ($seatID) {
                    echo "<input type='checkbox' name='selectedSeats[]' value='$seatID' id='seat_$seatID' style='display: none;'>";
                }
            }
            ?>
        </div>

        <!-- Row B (Seats 6-10) -->
        <div>
            <div class="row-label">B</div>
            <?php
            for ($i = 6; $i <= 10; $i++) {
                $seatInfo = isset($seats[$i]) ? $seats[$i] : ['SeatID' => null, 'occupied' => 0];
                $seatID = $seatInfo['SeatID'];
                $class = $seatInfo['occupied'] ? 'occupied' : 'not-occupied';
                echo "<div class='seat $class' onclick='toggleSelection(this, $seatID)'>$i</div>";
                if ($seatID) {
                    echo "<input type='checkbox' name='selectedSeats[]' value='$seatID' id='seat_$seatID' style='display: none;'>";
                }
            }
            ?>
        </div>
    </div>
    <button type="submit">Submit</button>
</form>
</body>
</html>
