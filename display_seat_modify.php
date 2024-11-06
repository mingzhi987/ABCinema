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
if (isset($_POST['new_showtime'])) {
    $booking_id = intval($_POST['booking_id']);
    $screening_date = intval($_POST['new_showtime']);

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
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="seat_display.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Seating Arrangement</title>
    <script>
        let selectedSeatID = null;

        function toggleSelection(seatElement, seatID, seatNumber) {
            if (seatElement.classList.contains('occupied')) return;

            // Deselect previously selected seat
            if (selectedSeatID && selectedSeatID !== seatID) {
                const previousSelectedSeat = document.querySelector('.seat.selected');
                if (previousSelectedSeat) {
                    previousSelectedSeat.classList.remove('selected');
                    const previousSeatInput = document.getElementById('seat_' + selectedSeatID);
                    if (previousSeatInput) {
                        previousSeatInput.checked = false;
                    }
                }
            }

            // Toggle current selection
            seatElement.classList.toggle('selected');
            const seatInput = document.getElementById('seat_' + seatID);
            seatInput.checked = seatElement.classList.contains('selected');

            // Update selected seat ID
            selectedSeatID = seatElement.classList.contains('selected') ? seatID : null;

            // Update hidden fields with selected seat ID and number
            document.getElementById('selectedSeatID').value = selectedSeatID;
            //document.getElementById('selectedSeatNumber').value = seatElement.classList.contains('selected') ? seatNumber : '';

            // Display alert with selected seat ID and number
            alert('Selected Seat ID: ' + selectedSeatID + '\nSelected Seat Number: ' + seatNumber);
        }

        function toggleBookNowButton() {
            const bookNowButton = document.querySelector('button[type="submit"]');
            
            // If no seats are selected, disable the button
            if (selectedSeatID.size === 0) {
                bookNowButton.disabled = true;
                bookNowButton.title = "Please select at least one seat.";
            } else {
                bookNowButton.disabled = false;
                bookNowButton.title = "Click to proceed with booking.";
            }
        }

        // Initial check on page load in case there are pre-selected seats
        window.onload = function() {
            toggleBookNowButton();
        };
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
<form method="POST" action="process_modify_booking.php">   
<div id="seating" style="max-width: 100%;
    display: flex;
    flex-direction: column;
    justify-content: center;
    text-align: left;
    align-items: center;">
        <div id="cinema-container">
            <div id="screen" style="width: 320px;"><p>Screen</p></div>
        </div>
        <div id="seating1">
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
                    <div class="seat <?php echo $class; ?>" onclick="toggleSelection(this, <?php echo $seatID; ?>, <?php echo $seatNumber?>)">
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
    <!-- Hidden fields to store selected seat IDs and screening date -->
    <input type="hidden" name="new_seat" id="selectedSeatID" value="">
    <input type="hidden" name="booking_id" value="<?php echo htmlspecialchars($booking_id); ?>">
    <input type="hidden" name="new_showtime" value="<?php echo htmlspecialchars($screening_date); ?>">

    <button type="submit">Modify Booking</button>
</form>
</body>
</html>
