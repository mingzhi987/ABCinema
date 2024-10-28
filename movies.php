<?php

// Connect to the database
// Movies table: MovieID int(11), MovieName varchar(45), MovieGenre varchar(45), MovieLength int(11), MovieRating varchar(45), MovieDesc text
// Database credentials
$servername = "localhost";  // Replace with your MySQL server name
$username = "root";         // Replace with your MySQL username
$password = "";             // Replace with your MySQL password
$dbname = "abcinema";  // Replace with your database name

// Create a connection to the MySQL database
$conn = new mysqli($servername, $username, $password, $dbname);

// Check if the connection was successful
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch the movies data from the movies table
$movies_query = $conn->query("SELECT * FROM movies");

while ($row = $movies_query->fetch_assoc()) {
    $total_movies = $conn->query("SELECT COUNT * FROM movies");
    echo $total_movies;

    // Calculate totals for 
    $total_java_quantity += $row['java_quantity'];
    $total_java_sales += ($row['java_sub_price'] * $row['java_quantity']);

    // Calculate totals for single shots
    $total_single_shot_quantity += $row['cafe_single_quantity'] + $row['cappuccino_single_quantity'];
    $total_single_shot_sales += ($row['cafe_single_sub_price'] * $row['cafe_single_quantity']) + ($row['cappuccino_single_sub_price'] * $row['cappuccino_single_quantity']);

    // Calculate totals for double shots
    $total_double_shot_quantity += $row['cafe_double_quantity'] + $row['cappuccino_double_quantity'];
    $total_double_shot_sales += ($row['cafe_double_sub_price'] * $row['cafe_double_quantity']) + ($row['cappuccino_double_sub_price'] * $row['cappuccino_double_quantity']);
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="cs2_styles.css">
    <title>Sales Reports By Categories | JavaJam Coffee House</title>
</head>

<body>
    <div id="wrapper">
        <header>
            <h1>JavaJam Coffee House</h1>
        </header>
        <div class="content">
            <h2>Sales Report - Categories</h2>
            <table border="1">
                <tr>
                    <th>Categories</th>
                    <th>Total Quantity Sold</th>
                    <th>Total Sales ($)</th>
                </tr>
                <tr>
                    <td>Java</td>
                    <td><?php echo $total_java_quantity; ?></td>
                    <td><?php echo number_format($total_java_sales, 2); ?></td>
                </tr>
                <tr>
                    <td>Single Shot (Cafe and Cappuccino)</td>
                    <td><?php echo $total_single_shot_quantity; ?></td>
                    <td><?php echo number_format($total_single_shot_sales, 2); ?></td>
                </tr>
                <tr>
                    <td>Double Shot (Cafe and Cappuccino)</td>
                    <td><?php echo $total_double_shot_quantity; ?></td>
                    <td><?php echo number_format($total_double_shot_sales, 2); ?></td>
                </tr>
            </table>
            <br>
            <a href="salesreport.php"><button>Go back to Report Selection</button></a>
</body>

<footer>
    <small><i>Copyright &copy; 2024 JavaJam Coffee House</i></small>
    <br><small><a href="mailto:zhiming@lin.com"><i>zhiming@lin.com</i></a></small>
</footer>

</html>