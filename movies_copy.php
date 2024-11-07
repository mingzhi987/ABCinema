<?php
session_start();
require 'dbconnection.php';

// Connect to the database
// Movies table: MovieID int(11), MovieName varchar(45), MovieGenre varchar(45), MovieLength int(11), MovieRating varchar(45), MovieDesc text

// Create a connection to the MySQL database
// $conn = new mysqli($servername, $username, $password, $database);

// // Check if the connection was successful
// if ($conn->connect_error) {
//     die("Connection failed: " . $conn->connect_error);
// }

// Set pagination variables
$moviesPerPage = 8;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $moviesPerPage;

// Retrieve selected filter and search criteria
$selectedGenre = isset($_GET['genre']) ? $_GET['genre'] : 'all';
$searchTerm = isset($_GET['search']) ? strtolower($_GET['search']) : '';

// Generate SQL query based on filter and search criteria
$whereClause = '';
if ($selectedGenre !== 'all') {
    $whereClause .= "WHERE MovieGenre = '" . $conn->real_escape_string($selectedGenre) . "'";
}
if (!empty($searchTerm)) {
    $whereClause .= ($whereClause ? " AND " : "WHERE ") . "LOWER(MovieName) LIKE '%" . $conn->real_escape_string($searchTerm) . "%'";
}

// Count the total number of movies with filter and search applied
$totalMoviesQuery = "SELECT COUNT(*) FROM movies $whereClause";
$totalMoviesResult = $conn->query($totalMoviesQuery);
$totalMoviesRow = $totalMoviesResult->fetch_row();
$totalMovies = $totalMoviesRow[0];
$totalPages = ceil($totalMovies / $moviesPerPage);

// Fetch movies data with pagination, filter, and search applied
$query = "SELECT * FROM movies $whereClause ORDER BY MovieID ASC LIMIT $offset, $moviesPerPage";
$result = $conn->query($query);

// Fetch unique genres for dropdown filter
$genresQuery = "SELECT DISTINCT MovieGenre FROM movies";
$genresResult = $conn->query($genresQuery);
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="abcmovies.css">
    <link rel="stylesheet" href="about_us.css">
    <script src="footerAdjuster.js"></script>
    <title>Movies | ABCinema</title>
</head>

<body>
    <!-- Nav bar -->
    <div class="header">
        <div>
            <a href="movies.php">
                <img class=logo src="images/logo/logo.png">
            </a>
            <div class="header-left">
                <a class="active" href="movies.php" alt="Movies">Movies</a>
                <a href="about_us.html" alt="About us">About us</a>
            </div>
        </div>
        <div class="header-right">
            <a href="checkout.php"><img src="images/icons/basket.svg" alt="Checkout" /></a>
            <?php if (isset($_SESSION['token_id'])): ?>
                <a href="profile.php"><img src="images/icons/profile.svg" alt="Profile" /></a>
            <?php else: ?>
                <a href="login.php"><img src="images/icons/profile.svg" alt="Login" /></a>
            <?php endif; ?>
        </div>
    </div>

    <!-- Top container -->
    <div class="content-wrapper">
        <div class="top-promotion">
    
            <!-- Slideshow container -->
            <div class="slideshow-container">
    
                <!-- Full-width images with number and caption text -->
                <div class="mySlides fade">
                    <div class="numbertext">1 / 3</div>
                    <img src="https://i0.wp.com/theroughcutpod.com/wp-content/uploads/2023/01/Avatar_Twitter.jpeg?fit=1200%2C628&quality=89&ssl=1">
                    <div class="text"><a href="http://localhost:8000/abcinema/display_movie_info.php?movieID=12">Avatar</a></div>
                </div>
    
                <div class="mySlides fade">
                    <div class="numbertext">2 / 3</div>
                    <img src="https://dx35vtwkllhj9.cloudfront.net/paramountpictures/smile/images/regions/us/header.jpg">
                    <div class="text"><a href="http://localhost:8000/abcinema/display_movie_info.php?movieID=7">Smile</a></div>
                </div>
    
                <div class="mySlides fade">
                    <div class="numbertext">3 / 3</div>
                    <img src="https://thesun.my/binrepository/inside-out-2-pixar_3662291_20231123094127.jpg">
                    <div class="text"><a href="http://localhost:8000/abcinema/display_movie_info.php?movieID=3">Inside Out 2</a></div>
                </div>
    
                <!-- Next and previous buttons -->
                <a class="prev" onclick="plusSlides(-1)">&#10094;</a>
                <a class="next" onclick="plusSlides(1)">&#10095;</a>
            </div>
            <br>
    
            <!-- The dots/circles -->
            <div style="text-align:center">
                <span class="dot" onclick="currentSlide(1)"></span>
                <span class="dot" onclick="currentSlide(2)"></span>
                <span class="dot" onclick="currentSlide(3)"></span>
            </div>
        </div>
    
        <!-- Movies -->
        <div class="movies-container">
            <div class="movies-row">
                <hr class="dotted" />
                <h1 class="movies-heading"> NOW SHOWING </h1>
                <hr class="dotted" />
                <div class="filters">
                    <!-- Filter and Search Bar -->
                    <div class="filter-bar">
                        <form id="filterForm" action="" method="get">
                            <!-- Genre Filter Dropdown -->
                            <label for="genre">Filter by Genre:</label>
                            <select name="genre" id="genre" onchange="document.getElementById('filterForm').submit()">
                                <option value="all" <?php if ($selectedGenre === 'all') echo 'selected'; ?>>All</option>
                                <?php while ($genreRow = $genresResult->fetch_assoc()): ?>
                                    <option value="<?php echo htmlspecialchars($genreRow['MovieGenre']); ?>" <?php if ($selectedGenre === $genreRow['MovieGenre']) echo 'selected'; ?>>
                                        <?php echo htmlspecialchars($genreRow['MovieGenre']); ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>

                            <!-- Search Box -->
                            <label for="search">Search by Name:</label>
                            <input type="text" name="search" id="search" value="<?php echo htmlspecialchars($searchTerm); ?>">
                            <button type="submit">Search</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    <div class="nonsense" style="display: flex;
    justify-content: center;
    flex-direction: column;
    align-items: center;">
    <div class="movielisting" style="display: flex;
    justify-content: center;
    flex-direction: row;
    flex-wrap: wrap;
    max-width: 70%;">
    <!-- Movie Display Section -->
    <?php if ($result->num_rows > 0): ?>
        <?php while ($row = $result->fetch_assoc()): ?>
            <div class="movies-column">
            <div class="movies">
            <div class="movies-card" onclick="goToMovie(<?php echo $row['MovieID']; ?>)">
                <h2><?php echo htmlspecialchars($row['MovieName']); ?></h2>
                <img src="<?php echo htmlspecialchars($row['MoviePoster']); ?>" alt="<?php echo htmlspecialchars($row['MovieName']); ?>">
                <p><strong>Genre:</strong> <?php echo htmlspecialchars($row['MovieGenre']); ?></p>
                <p><strong>Length:</strong> <?php echo htmlspecialchars($row['MovieLength']); ?> mins</p>
                <p><strong>Rating:</strong> <?php echo htmlspecialchars($row['MovieRating']); ?>/10</p>
                <p class="read-more">
                <?php
                    $words = explode(' ', $row['MovieDesc']);
                    echo implode(' ', array_slice($words, 0, 5)) . (count($words) > 10 ? '...' : '');
                ?>
                </p>
                <a href="display_movie_info.php?movieID= <?php echo htmlspecialchars($row['MovieID']); ?>">Book Movie</a>
            </div>
        </div>
        </div>
        <?php endwhile; ?>
    <?php else: ?>
        <p>No movies found.</p>
    <?php endif; ?>
    </div>
    </div>

    <!-- Pagination -->
    <div class="pagination">
        <form action="" method="get">
        <!-- Retain the filter and search criteria in pagination -->
        <input type="hidden" name="genre" value="<?php echo htmlspecialchars($selectedGenre); ?>">
        <input type="hidden" name="search" value="<?php echo htmlspecialchars($searchTerm); ?>">

        <!-- Page Number Buttons -->
        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <button type="submit" name="page" value="<?php echo $i; ?>"<?php if ($i === $page) echo ' style="font-weight: bold;"'; ?>>
                    <?php echo $i; ?>
                </button>
            <?php endfor; ?>
        </form>
    </div>
</body>

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
                234 Film Street #01-01 <br> Singapore 690234
                <p>contact@abcinema.com.sg
                <p>+65 6349 8203
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
</html>

<script>
    let slideIndex = 1;
    showSlides(slideIndex);

    // Next/previous controls
    function plusSlides(n) {
        showSlides(slideIndex += n);
    }

    setInterval(function() {
        plusSlides(1);
    }, 5000);

    function showSlides(n) {
        let i;
        let slides = document.getElementsByClassName("mySlides");
        if (n > slides.length) {slideIndex = 1}
        if (n < 1) {slideIndex = slides.length}
        for (i = 0; i < slides.length; i++) {
            slides[i].style.display = "none";
        }
        slides[slideIndex-1].style.display = "block";
    }

    /* When the user clicks on the button,
toggle between hiding and showing the dropdown content */
function sortbyFunction() {
        document.getElementById("myDropdown").classList.toggle("show");
    }

    function filterFunction() {
        var input, filter, ul, li, a, i;
        input = document.getElementById("myInput");
        filter = input.value.toUpperCase();
        div = document.getElementById("myDropdown");
        a = div.getElementsByTagName("a");
        for (i = 0; i < a.length; i++) {
            txtValue = a[i].textContent || a[i].innerText;
            if (txtValue.toUpperCase().indexOf(filter) > -1) {
                a[i].style.display = "";
            } else {
                a[i].style.display = "none";
            }
        }
    }
    function goToMovie(movieID) {
            window.location.href = 'display_movie_info.php?movieID=' + movieID;
        }
</script>

<?php
// Close the database connection
$conn->close();
?>