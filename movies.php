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

// Check if this is an AJAX request
if (isset($_GET['ajax']) && $_GET['ajax'] === '1') {
    // Fetch the filtered data for AJAX requests

    // Pagination and filter variables
    $moviesPerPage = 8;
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $offset = ($page - 1) * $moviesPerPage;

    // Set search and filter variables
    $genreFilter = isset($_GET['genre']) && $_GET['genre'] !== "all" ? $_GET['genre'] : "";
    $search = isset($_GET['search']) ? strtolower(trim($_GET['search'])) : "";

    // SQL query with filters
    $query = "SELECT * FROM movies WHERE 1=1";

    if ($genreFilter) {
        $query .= " AND MovieGenre = '" . $conn->real_escape_string($genreFilter) . "'";
    }

    if ($search) {
        $query .= " AND LOWER(MovieName) LIKE '%" . $conn->real_escape_string($search) . "%'";
    }

    $query .= " ORDER BY MovieID ASC LIMIT $offset, $moviesPerPage";
    $result = $conn->query($query);

    // Fetch movies
    $movies = [];
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $movies[] = $row;
        }
    }

    // Fetch total count for pagination
    $countQuery = "SELECT COUNT(*) AS total FROM movies WHERE 1=1";
    if ($genreFilter) {
        $countQuery .= " AND MovieGenre = '" . $conn->real_escape_string($genreFilter) . "'";
    }
    if ($search) {
        $countQuery .= " AND LOWER(MovieName) LIKE '%" . $conn->real_escape_string($search) . "%'";
    }
    $countResult = $conn->query($countQuery);
    $totalMovies = $countResult->fetch_assoc()['total'];

    // Send JSON response
    echo json_encode([
        "movies" => $movies,
        "totalMovies" => $totalMovies,
    ]);
    exit();
}

// Fetch genres for dropdown
$genresResult = $conn->query("SELECT DISTINCT MovieGenre FROM movies ORDER BY MovieGenre ASC");
$genres = [];
if ($genresResult->num_rows > 0) {
    while ($genreRow = $genresResult->fetch_assoc()) {
        $genres[] = $genreRow['MovieGenre'];
    }
}
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="abcmovies.css">
    <title>Movies | ABCinema</title>
</head>

<body>
    <!-- Nav bar -->
    <div class="header">
        <a href="#default">
            <img class=logo src="images/logo/logo.png" href="#">
        </a>
        <div class="header-left">
            <a href="#contact" alt="Contact">Contact</a>
            <a href="about_us.html" alt="About">About us</a>
        </div>
        <div class="menu-icons">
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
                    <img src="images/movie_poster/horizontal/img1.jpg">
                    <div class="text">Jumanji</div>
                </div>
    
                <div class="mySlides fade">
                    <div class="numbertext">2 / 3</div>
                    <img src="images/movie_poster/horizontal/img2.jpg">
                    <div class="text">Smile 2</div>
                </div>
    
                <div class="mySlides fade">
                    <div class="numbertext">3 / 3</div>
                    <img src="images/movie_poster/horizontal/img3.jpg">
                    <div class="text">Star Wars</div>
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
            <select id="genreFilter" onchange="loadMovies()">
                <option value="all">All Genres</option>
                <?php foreach ($genres as $genre): ?>
                    <option value="<?php echo ucwords(htmlspecialchars($genre)," "); ?>"><?php echo ucwords(htmlspecialchars($genre),"- "); ?></option>
                <?php endforeach; ?>
            </select>
            <input type="text" id="searchInput" placeholder="Search by Movie Name">
            <button onclick="applySearch()">Search</button>
        </div>
    
        <div id="movieList"></div>
    
        <div class="pagination" id="pagination"></div>
    
        <script>
            // Load initial movie list
            document.addEventListener("DOMContentLoaded", function() {
                loadMovies();
            });
    
            // Function to load movies with AJAX
            function loadMovies(page = 1) {
                const genre = document.getElementById("genreFilter").value;
                const search = document.getElementById("searchInput").value.trim().toLowerCase();
                
                const xhr = new XMLHttpRequest();
                xhr.open("GET", `movies.php?ajax=1&page=${page}&genre=${genre}&search=${encodeURIComponent(search)}`, true);
                xhr.onload = function() {
                    if (xhr.status === 200) {
                        const data = JSON.parse(xhr.responseText);
                        displayMovies(data.movies);
                        displayPagination(page, data.totalMovies);
                    }
                };
                xhr.send();
            }
    
            // Function to display movies in the movieList div
            function displayMovies(movies) {
                const movieList = document.getElementById("movieList");
                movieList.innerHTML = "";
                
                if (movies.length > 0) {
                    movies.forEach(movie => {
                        movieList.innerHTML += `
                        <div class="movies-column">
                        <div class="movies">
                            <div class="movies-card">
                                <h2>${movie.MovieName.charAt(0).toUpperCase()+ movie.MovieName.slice(1)}</h2>
                                <img width="100" height="150" id="poster" src="images/movie_poster/vertical/`+ movie.MoviePoster+` " alt="`+movie.MovieName+`">
                                <p><strong>Genre:</strong> ${movie.MovieGenre.charAt(0).toUpperCase()+ movie.MovieGenre.slice(1)}</p>
                                <p><strong>Length:</strong> ${movie.MovieLength} mins</p>
                                <p><strong>Rating:</strong> ${movie.MovieRating}/10</p>
                                <a href="display_movie_info.php?movieID= ${movie.MovieID}); ?>">Book Movie</a>
                            </div>
                            </div>
                        </div>
                        `;
                    });
                } else {
                    movieList.innerHTML = "<p>No movies found.</p>";
                }
            }
    
            // Function to display pagination
            function displayPagination(currentPage, totalMovies) {
                const pagination = document.getElementById("pagination");
                pagination.innerHTML = "";
    
                const totalPages = Math.ceil(totalMovies / 10);
                if (totalPages > 1) {
                    for (let i = 1; i <= totalPages; i++) {
                        pagination.innerHTML += `
                            <button onclick="loadMovies(${i})" ${i === currentPage ? 'style="font-weight:bold;"' : ''}>
                                ${i}
                            </button>
                        `;
                    }
                }
            }
    
            // Function to apply search
            function applySearch() {
                loadMovies();
            }
        </script>
        </div>
        </div>
    </div>

    <!-- Footer -->
<footer>
    <div class="footer-container">
        <div class="row">
            <div class="column-1"><img class="logo" src="images/logo/logo.png">
                <div class="footer-summary">Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat</div>
            </div>

            <div class="column-2">
                <h4>Links</h4>
                <ul>
                    <li class="footer-links"><a href="http://demo.amytheme.com/movie/demo/elementor-single-cinema/" aria-current="page">Home</a></li>
                    <li class="footer-links"><a href="http://demo.amytheme.com/movie/demo/elementor-single-cinema/coming-soon/">Coming Soon</a></li>
                    <li class="footer-links"><a href="http://demo.amytheme.com/movie/demo/elementor-single-cinema/top-rated/">Top rated</a></li>
                </ul>
            </div>

            <div class="column-2">
                <h4>Contact Us</h4>
                123 Raffles Place #14-01 <br> Singapore 348023
                <p>support@abcinema.com.sg
                <p>+65 63498203
            </div>

            <div class="column-2">
                <h4>Follow Us</h4>
                <img src="images/icons/twitter-x.svg" alt="X (Twitter)" />
                <img src="images/icons/facebook.svg" alt="Facebook" />
                <img src="images/icons/instagram.svg" alt="Instagram" />
            </div>
        </div>
    </div>
    </div>
</footer>

</body>


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

</script>

<?php
// Close the database connection
$conn->close();
?>