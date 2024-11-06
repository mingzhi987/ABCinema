<?php
session_start();
require 'dbconnection.php';

$conn = new mysqli($servername, $username, $password, $database);
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $movieID = $_POST['MovieID'];
    $movieName = $_POST['MovieName'];
    $movieGenre = $_POST['MovieGenre'];
    $movieLength = $_POST['MovieLength'];
    $movieRating = $_POST['MovieRating'];
    $movieDesc = $_POST['MovieDesc'];
    $cinemaID = $_POST['CinemaID'];
    $cinemaHall = $_POST['CinemaHall'];


    // Print all inputs for debugging
    echo "<pre>";
    echo "MovieID: $movieID\n";
    echo "MovieName: $movieName\n";
    echo "MovieGenre: $movieGenre\n";
    echo "MovieLength: $movieLength\n";
    echo "MovieRating: $movieRating\n";
    echo "MovieDesc: $movieDesc\n";
    echo "CinemaID: $cinemaID\n";
    echo "CinemaHall: $cinemaHall\n";
    echo "</pre>";

    echo "<script>console.log('checking empty fields');</script>";
    if (empty($movieName) || empty($movieGenre) || empty($movieLength) || empty($movieRating) || empty($movieDesc) || empty($cinemaID) || empty($cinemaHall)) {
        echo "<script>alert('All fields are required. Please fill in all fields.');</script>";
        exit();
    }

    echo "<script>console.log('checking existing cinema');</script>";
    // Check if the cinema exists
    $stmt = $conn->prepare("SELECT * FROM cinema WHERE CinemaID = ?");
    $stmt->bind_param("i", $cinemaID);
    if (!$stmt->execute()) {
        echo "<script>alert('Error checking cinema existence: " . $stmt->error . "'); history.back();</script>";
        exit();
    }
    $cinemaResult = $stmt->get_result();

    echo "<script>console.log('checking for cinema results');</script>";
    if ($cinemaResult->num_rows == 0) {


        echo "<script>console.log('if cinema does not exist');</script>";
        // Create a new cinema
        $stmt = $conn->prepare("INSERT INTO cinema (MovieAllocated, CinemaHall) VALUES (?, ?)");
        $stmt->bind_param("ii", $movieID, $cinemaHall);
        if (!$stmt->execute()) {
            echo "<script>alert('Error inserting new cinema: " . $stmt->error . "'); history.back();</script>";
            exit();
        }

        $cinemaID = $conn->insert_id; // Get the newly inserted CinemaID

        // Debugging line to check the cinemaID after insertion
        echo "<script>console.log('New Cinema ID: " . $cinemaID . "');</script>";

        // Add 10 seats to the seating table
        for ($j = 1; $j <= 10; $j++) {
            $seatNumber = $j;
            $stmt = $conn->prepare("INSERT INTO seating (CinemaNumber, SeatNumber) VALUES (?, ?)");
            $stmt->bind_param("ii", $cinemaID, $seatNumber);
            if (!$stmt->execute()) {
                echo "<script>alert('Error inserting seats: " . $stmt->error . "'); history.back();</script>";
                exit();
            }
        }
    } else {
        // Check if the cinema is allocated to another movie
        $cinema = $cinemaResult->fetch_assoc();
        if ($cinema['MovieAllocated'] != $movieID) {
            echo "<script>alert('The cinema is already allocated to another movie.');</script>";
            exit();
        }
    }

    echo "<script>console.log('updating movie details');</script>";
    // Update the movie details
    $stmt = $conn->prepare("
        UPDATE movies 
        SET MovieName = ?, MovieGenre = ?, MovieLength = ?, MovieRating = ?, MovieDesc = ? 
        WHERE MovieID = ?
    ");
    $stmt->bind_param("ssissi", $movieName, $movieGenre, $movieLength, $movieRating, $movieDesc, $movieID);
    if (!$stmt->execute()) {
        echo "<script>alert('Error updating movie details: " . $stmt->error . "'); history.back();</script>";
        exit();
    }

    // Update the cinema details if necessary
    $stmt = $conn->prepare("UPDATE cinema SET CinemaHall = ? WHERE CinemaID = ?");
    $stmt->bind_param("ii", $cinemaHall, $cinemaID);
    if (!$stmt->execute()) {
        echo "<script>alert('Error updating cinema details: " . $stmt->error . "'); history.back();</script>";
        exit();
    }

    echo "<script>alert('Movie updated successfully!');</script>";
}
?>