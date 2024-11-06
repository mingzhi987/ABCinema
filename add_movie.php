<?php
session_start();
include 'dbconnection.php'; // Include your database connection file

// Create a connection to the MySQL database
$conn = new mysqli($servername, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $movieName = $_POST['MovieName'];
    $movieGenre = $_POST['MovieGenre'];
    $movieLength = $_POST['MovieLength'];
    $movieRating = $_POST['MovieRating'];
    $movieDesc = $_POST['MovieDesc'];
    $cinemaID = $_POST['CinemaID'];
    $cinemaHall = $_POST['CinemaHall'];

    // Validate inputs
    if (empty($movieName) || empty($movieGenre) || empty($movieLength) || empty($movieRating) || empty($movieDesc) || empty($cinemaID) || empty($cinemaHall)) {
        echo "<script>alert('All fields are required. Please fill in all fields.'); window.history.back();</script>";
        exit();
    }

    // Insert the new movie
    $stmt = $conn->prepare("INSERT INTO movies (MovieName, MovieGenre, MovieLength, MovieRating, MovieDesc) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("ssiss", $movieName, $movieGenre, $movieLength, $movieRating, $movieDesc);
    $stmt->execute();
    $newMovieID = $stmt->insert_id;

    // Insert the new cinema
    $stmt = $conn->prepare("INSERT INTO cinema (MovieAllocated, CinemaHall) VALUES (?, ?, ?)");
    $stmt->bind_param("ii", $newMovieID, $cinemaHall);
    $stmt->execute();

    // Add 10 seats to the seating table
    for ($j = 1; $j <= 10; $j++) {
        $seatNumber = $j;
        $stmt = $conn->prepare("INSERT INTO seating (CinemaNumber, SeatNumber) VALUES (?, ?)");
        $stmt->bind_param("ii", $cinemaID, $seatNumber);
        $stmt->execute();
    }

    echo "<script>alert('Movie added successfully!'); window.history.back();</script>";
}
?>