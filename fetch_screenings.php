<?php

// Connect to the database
// Movies table: MovieID int(11), MovieName varchar(45), MovieGenre varchar(45), MovieLength int(11), MovieRating varchar(45), MovieDesc text
// Database credentials
$servername = "localhost";  // Replace with your MySQL server name
$username = "root";         // Replace with your MySQL username
$password = "";             // Replace with your MySQL password
$dbname = "abcinema";  // Replace with your database name

// Database connection
$conn = new mysqli($servername, $username, $password, $dbname);

$movie_id = isset($_GET['movie_id']) ? (int)$_GET['movie_id'] : 0;

// Query to fetch screening times for the selected movie
$query = "SELECT st.ScreenTimeID, st.ScreenTimeDate, st.ScreenTimeCost, st.SeatingLocation, s.BookingState
          FROM screeningtime2 AS st
          LEFT JOIN seating AS s ON st.ScreenTimeID = s.CinemaNumber
          WHERE st.ScreeningMovie = $movie_id
          ORDER BY st.ScreenTimeDate ASC";
$result = $conn->query($query);

$data = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $date = date('Y-m-d', strtotime($row['ScreenTimeDate']));
        $time = date('H:i', strtotime($row['ScreenTimeDate']));
        $data[$date]['date'] = $date;
        $data[$date]['times'][] = [
            'time' => $time,
            'cost' => $row['ScreenTimeCost'],
            'location' => $row['SeatingLocation'],
            'screentime_id' => $row['ScreenTimeID'],
            'booking_state' => $row['BookingState']
        ];
    }
}

// Return JSON data
echo json_encode(array_values($data));
$conn->close();
