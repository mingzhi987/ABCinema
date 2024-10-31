<?php
require 'dbconnection.php';

if (isset($_POST['screening_date']) && isset($_POST['movie_id'])) {
    $screening_date = $_POST['screening_date'];
    $movieID = $_POST['movie_id'];

    // Create connection
    $conn = new mysqli($servername, $username, $password, $database);

    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Retrieve available seats
    $sql = "SELECT SeatID, SeatNumber FROM seating 
            WHERE CinemaNumber IN (SELECT CinemaID FROM cinema WHERE MovieAllocated = ?) 
            AND SeatID NOT IN (SELECT SeatID FROM shoppingscreening WHERE ScreenTimeID = ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $movieID, $screening_date);
    $stmt->execute();
    $result = $stmt->get_result();

    $seats = [];
    while ($row = $result->fetch_assoc()) {
        $seats[] = $row;
    }

    $stmt->close();
    $conn->close();

    echo json_encode($seats);
}
?>