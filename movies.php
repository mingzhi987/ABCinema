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

// Set the number of movies per page
$movies_per_page = 8;

// Determine the current page
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $movies_per_page;

// Capture filter and search inputs
$selected_genre = isset($_GET['genre']) ? $_GET['genre'] : 'All';
$search_query = isset($_GET['search']) ? strtolower(trim($_GET['search'])) : '';

// Generate SQL condition for genre and search
$where_conditions = [];
if ($selected_genre !== 'All') {
    $where_conditions[] = "MovieGenre = '" . $conn->real_escape_string($selected_genre) . "'";
}
if (!empty($search_query)) {
    $where_conditions[] = "MovieName LIKE '%" . $conn->real_escape_string($search_query) . "%'";
}
$where_sql = !empty($where_conditions) ? 'WHERE ' . implode(' AND ', $where_conditions) : '';

// Count the total number of movies with filters
$total_query = "SELECT COUNT(*) AS total FROM movies $where_sql";
$total_result = $conn->query($total_query);
$total_row = $total_result->fetch_assoc();
$total_movies = $total_row['total'];

// Fetch movies data with filters and pagination
$query = "SELECT MovieID, MovieName, MovieGenre, MovieLength, MovieRating, MovieDesc, MoviePoster 
          FROM movies
          ORDER BY MovieID ASC 
          LIMIT $movies_per_page OFFSET $offset";
$result = $conn->query($query);

// Fetch unique genres for the filter dropdown
$genre_query = "SELECT DISTINCT MovieGenre FROM movies";
$genre_result = $conn->query($genre_query);
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
            <a href="#about" alt="About">About us</a>
        </div>
        <div class="menu-icons">
            <a href="#"><img src="images/icons/basket.svg" alt="Checkout" /></a>
            <a href="#"><img src="images/icons/profile.svg" alt="Profile" /></a>
        </div>
    </div>

    <!-- Top container -->
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
</div>

    <!-- Movies -->
    <div class="movies-container">
        <div class="movies-row">
            <hr class="dotted" />
            <h1 class="movies-heading"> Now Showing </h1>
            <?php echo $total_movies ?>
            <hr class="dotted" />
            <!-- Filter and Search Form -->
            <div class="filter-container">
                <form method="GET" action="">
                    <label for="genre">Filter by Genre:</label>
                    <select name="genre" id="genre">
                        <option value="All">All</option>
                        <?php while ($genre_row = $genre_result->fetch_assoc()): ?>
                            <option value="<?php echo htmlspecialchars($genre_row['MovieGenre']); ?>"
                                <?php echo $selected_genre === $genre_row['MovieGenre'] ? 'selected' : ''; ?>>
                                <?php echo ucwords(htmlspecialchars($genre_row['MovieGenre']), '\',.- '); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>

                    <label for="search">Search by Name:</label>
                    <input type="text" name="search" id="search" value="<?php echo htmlspecialchars($search_query); ?>">

                    <button type="submit">Apply</button>
                </form>
            </div>
        </div>

        <!-- Display movies -->
        <div class="movies-row">

            <?php while ($row = $result->fetch_assoc()): ?>
                <div class="movies-column">
                    <a class="movies">
                        <div class="movies-card">
                            <img id="poster" src="images/movie_poster/vertical/<?php echo htmlspecialchars($row['MoviePoster']); ?>" alt="<?php echo ucwords(htmlspecialchars($row['MovieName']), '\',.- '); ?>">
                            <ul class="movie-details">
                                <h2><?php echo ucwords(htmlspecialchars($row['MovieName']), '\',.- '); ?></h2>
                                <p><strong>Genre:</strong> <?php echo ucwords(htmlspecialchars($row['MovieGenre']), '\',.- '); ?></p>
                                <p><strong>Length:</strong> <?php echo htmlspecialchars($row['MovieLength']); ?> mins</p>
                                <p><strong>Rating:</strong> <?php echo htmlspecialchars($row['MovieRating']); ?>/10</p>
                                <p><strong>Description:</strong> <?php echo htmlspecialchars($row['MovieDesc']); ?></p>
                                <!-- Tooltip for showing screening times -->
                                <div class="tooltip"></div>
                        </div>
                </div>
            <?php endwhile; ?>
        </div>

    <div class="pagination">
    <?php
    // Calculate total pages
    $total_pages = ceil($total_movies / $movies_per_page);

    for ($i = 1; $i <= $total_pages; $i++) {
        $url_params = http_build_query([
            'page' => $i,
            'genre' => $selected_genre,
            'search' => $search_query,
        ]);
        if ($i == $page) {
            echo "<a class='active' href='?$url_params'>$i</a>";
        } else {
            echo "<a href='?$url_params'>$i</a>";
        }
    }
    ?>
    </div>
    </div>
</div>
</body>

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

</html>

<script>
    let slideIndex = 1;
    showSlides(slideIndex);

    // Next/previous controls
    function plusSlides(n) {
        showSlides(slideIndex += n);
    }

    // Thumbnail image controls
    function currentSlide(n) {
        showSlides(slideIndex = n);
    }

    function showSlides(n) {
        let i;
        let slides = document.getElementsByClassName("mySlides");
        let dots = document.getElementsByClassName("dot");
        if (n > slides.length) {
            slideIndex = 1
        }
        if (n < 1) {
            slideIndex = slides.length
        }
        for (i = 0; i < slides.length; i++) {
            slides[i].style.display = "none";
        }
        for (i = 0; i < dots.length; i++) {
            dots[i].className = dots[i].className.replace(" active", "");
        }
        slides[slideIndex - 1].style.display = "block";
        dots[slideIndex - 1].className += " active";
        setTimeout(showSlides(slideIndex), 2000);
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

    // Add event listener to movie elements
    document.querySelectorAll('.movie').forEach(movie => {
        movie.addEventListener('click', function() {
            const movieID = this.dataset.movieId;
            const tooltip = this.querySelector('.tooltip');

            // Toggle tooltip visibility
            tooltip.classList.toggle('active');

            // Fetch screening times if not already loaded
            if (!tooltip.innerHTML) {
                fetch(`fetch_screenings.php?movie_id=${movieID}`)
                    .then(response => response.json())
                    .then(data => {
                        let html = '';
                        data.forEach(screening => {
                            html += `
                            <div class="time-tab" data-date="${screening.date}">
                                ${screening.date}
                            </div>
                            <div class="time-blocks">
                                ${screening.times.map(time => `
                                    <div class="time-block" data-cost="${time.cost}" data-location="${time.location}">
                                        ${time.time}
                                    </div>
                                `).join('')}
                            </div>
                        `;
                        });
                        html += '<a class="buy-now">Buy now</a>';
                        tooltip.innerHTML = html;

                        // Add event listener to each time block
                        tooltip.querySelectorAll('.time-block').forEach(block => {
                            block.addEventListener('click', function() {
                                alert(`Cost: ${this.dataset.cost} | Location: ${this.dataset.location}`);
                            });
                        });

                        // Add event listener to "Buy now" button
                        tooltip.querySelector('.buy-now').addEventListener('click', function() {
                            fetch(`add_to_cart.php?movie_id=${movieID}`)
                                .then(response => response.text())
                                .then(response => {
                                    alert(response);
                                });
                        });
                    });
            }
        });
    });
</script>

<?php
// Close the database connection
$conn->close();
?>