<?php
// Database connection
require 'dbconnection.php';
// Create connection
$conn = new mysqli($servername, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}


$seats = [];
if (isset($_POST['screening_date'])) {
    $screening_date = intval($_POST['screening_date']);

    // Get movieID from screeningtime2
    $sql = "SELECT ScreeningMovie FROM screeningtime2 WHERE ScreenTimeID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $screening_date);
    $stmt->execute();
    $stmt->bind_result($movieID);
    $stmt->fetch();
    $stmt->close();

    // Get CinemaNumber from cinema
    $sql = "SELECT CinemaID FROM cinema WHERE MovieAllocated = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $movieID);
    $stmt->execute();
    $stmt->bind_result($selectedCinemaNumber);
    $stmt->fetch();
    $stmt->close();

    // Fetch seats and their occupied status
    $sql = "SELECT s.SeatID, s.SeatNumber, 
                   CASE WHEN b.SeatID IS NOT NULL THEN 1 ELSE 0 END AS occupied
            FROM seating s
            LEFT JOIN booking b ON s.SeatID = b.SeatID AND b.Showtime = ?
            WHERE s.CinemaNumber = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $screening_date, $selectedCinemaNumber);
    $stmt->execute();
    $seatingResult = $stmt->get_result();
    while ($row = $seatingResult->fetch_assoc()) {
        $seats[] = $row;
    }
}

// Close connection
$conn->close();

// // Retrieve all cinema numbers for the dropdown
// $cinemaNumbers = [];
// $cinemaResult = $conn->query("SELECT DISTINCT CinemaNumber FROM seating");
// if ($cinemaResult->num_rows > 0) {
//     while ($row = $cinemaResult->fetch_assoc()) {
//         $cinemaNumbers[] = $row['CinemaNumber'];
//     }
// }

// // Get selected cinema number from dropdown or default to the first option
// $selectedCinemaNumber = isset($_GET['cinemaNumber']) ? $_GET['cinemaNumber'] : (isset($cinemaNumbers[0]) ? $cinemaNumbers[0] : null);

// // Handle seat selection submission
// if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['selectedSeats'])) {
//     $selectedSeats = $_POST['selectedSeats'];
//     $userID = 1; // Replace with actual user ID
//     $shoppingCartID = 1; // Replace with actual shopping cart ID
//     $movieName = "Sample Movie"; // Replace with actual movie name
//     $showtime = "2024-11-06 18:00:00"; // Replace with actual showtime
//     $cinemaID = $selectedCinemaNumber;
//     $paymentDate = date("Y-m-d H:i:s"); // Current date and time

//     // Insert each selected seat as a new booking
//     foreach ($selectedSeats as $seatID) {
//         $sql = "INSERT INTO booking (PaymentDate, UserID, ShoppingCartID, MovieName, Showtime, CinemaID, SeatID)
//                 VALUES ('$paymentDate', $userID, $shoppingCartID, '$movieName', '$showtime', $cinemaID, $seatID)";
//         $conn->query($sql);
//     }
// }

// // Initialize seats array for seat arrangement display
// $seats = [];
// if ($selectedCinemaNumber !== null) {
//     // Query to get seat information and check if occupied by joining with booking table
//     $sql = "SELECT s.SeatID, s.SeatNumber, 
//                    CASE WHEN b.SeatID IS NOT NULL THEN 1 ELSE 0 END AS occupied
//             FROM seating s
//             LEFT JOIN booking b ON s.SeatID = b.SeatID
//             WHERE s.CinemaNumber = $selectedCinemaNumber";
    
//     $result = $conn->query($sql);

//     // Populate the seats array with seat IDs, numbers, and occupancy status
//     if ($result->num_rows > 0) {
//         while ($row = $result->fetch_assoc()) {
//             $seats[$row['SeatNumber']] = [
//                 'SeatID' => $row['SeatID'],
//                 'occupied' => $row['occupied']
//             ];
//         }
//     }
// }

// $conn->close();
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
<style>
    .seat { /* Add your seat styling here */ }
    .occupied { background-color: red; }
    .not-occupied { background-color: green; }
    .non-existent { background-color: grey; }
    .selected { border: 2px solid blue; }
</style>

<body>

<h3>Seating Arrangement for Cinema <?php echo htmlspecialchars($selectedCinemaNumber); ?></h3>
<form method="POST" action="seating_display.php?cinemaNumber=<?php echo htmlspecialchars($selectedCinemaNumber); ?>">   
<div id="cinema-container">
</div>
<div id="seating">
        <div id="cinema-container">
            <div id="screen"><p>Screen</p></div>
        </div>
        <div id="seating">
            <?php
            // Assuming 5 seats per row for simplicity
            $seatsPerRow = 5;
            $rows = ['A', 'B']; // Define rows

            // Sort seats by SeatNumber
            usort($seats, function($a, $b) {
                return $a['SeatNumber'] - $b['SeatNumber'];
            });

            foreach ($rows as $rowIndex => $rowLabel):
                echo "<div class='row'><div class='row-label'>$rowLabel</div>";
                for ($i = 1; $i <= $seatsPerRow; $i++):
                    $seatNumber = $rowIndex * $seatsPerRow + $i;
                    $seat = isset($seats[$seatNumber - 1]) ? $seats[$seatNumber - 1] : null;
                    $class = $seat ? ($seat['occupied'] ? 'occupied' : 'not-occupied') : 'non-existent';
                    $seatID = $seat ? $seat['SeatID'] : '';
            ?>
                    <div class="seat <?php echo $class; ?>" onclick="toggleSelection(this, '<?php echo $seatID; ?>')">
                        <?php echo $seatNumber; ?>
                    </div>
                    <?php if ($seat): ?>
                        <input type="checkbox" name="seats[]" value="<?php echo htmlspecialchars($seatID); ?>" id="seat_<?php echo htmlspecialchars($seatID); ?>" style="display: none;">
                    <?php endif; ?>
                <?php endfor; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    <button type="submit">Submit</button>
</form>
</body>
</html>
