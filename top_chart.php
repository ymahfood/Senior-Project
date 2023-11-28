<?php
session_start();

require_once("database.php");

$mysqli = Database::dbConnect();
$mysqli->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Function to get top rated albums with pagination
function getTopRatedAlbums($mysqli, $start, $perPage) {
    $query = "SELECT Album.AlbumID, Artist.ArtistID, Album.AlbumName, Artist.ArtistName, Album.AverageRating, Album.NumRatings, Album.ReleaseDate, Album.AlbumStatus
            FROM Album LEFT JOIN Artist ON Album.ArtistID = Artist.ArtistID
            ORDER BY ((3 * Album.AverageRating) + (0.01 * SQRT(Album.NumRatings))) DESC
            LIMIT :start, :perPage";

    $stmt = $mysqli->prepare($query);
    $stmt->bindParam(':start', $start, PDO::PARAM_INT);
    $stmt->bindParam(':perPage', $perPage, PDO::PARAM_INT);
    $stmt->execute();
    $albums = $stmt->fetchAll(PDO::FETCH_ASSOC);

    return $albums;
}

// Determine the current page number
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;

// Set the number of results per page
$perPage = 20;

// Calculate the starting index for the SQL LIMIT clause
$start = ($page - 1) * $perPage;

// Get top rated albums for the current page
$topAlbums = getTopRatedAlbums($mysqli, $start, $perPage);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>YourFavoriteAlbum.com</title>
    <link rel="stylesheet" href="styles.css">
</head>

<body>

    <header>
        <div class="logo">
            <h1>YourFavoriteAlbum.com</h1>
        </div>
        <div class="search">
            <form action="search.php" method = "get">
                <input type="text" placeholder="Search" name="search">
                <button type="submit">Search</button>
            </form>
        </div>
    </header>

    <nav>
        <div class="nav-buttons">
        <?php
            if (isset($_SESSION['username'])) {
                echo '<a href="logout.php"><button>Logout</button></a>';
            } else {
                echo '<a href="login.php"><button>Login</button></a>';
            }
            ?>
            <a href="homepage.php"><button>Home</button></a>
            <a href="top_chart.php"><button>Charts</button></a>
            <div class="profile-button">
            <a href="profile.php"><button>Profile:
                <?php
                if (isset($_SESSION['username'])) {
                    echo $_SESSION['username'];
                    echo " ";
                    echo $_SESSION['user_id'];
                } else {
                    echo "No Profile Found";
                }
                ?>
            </button></a>
            <a href="usersettings.php"><button>Settings</button></a>
            </div>
        </div>
    </nav>

    <?php if ($_SESSION['user_type'] == 'Admin'): ?>
        <nav class="admin-nav">
            <div class="nav-buttons">
                <a href="verification_requests.php"><button>Verification Requests</button></a>
                <a href="add_artist.php"><button>Add Artist</button></a>
            </div>
        </nav>
    <?php endif; ?>

    
    <section class="featured-albums">
        <h2>Top 100 Albums of All Time</h2>

        <?php
        $number = $start + 1;
        foreach ($topAlbums as $album) {
            if ($album['AlbumStatus'] != 'Deleted') {
                echo "<div class='chart-albums'>";
                echo "<h3>{$number}. <a href='album.php?album_id={$album['AlbumID']}'>{$album['AlbumName']}</a></h3>";
                echo "<p><a href='artist.php?artist_id={$album['ArtistID']}'>{$album['ArtistName']}</a></p>";
                echo "<p>Date: {$album['ReleaseDate']}</p>";
                echo "<p>Number of Ratings: {$album['NumRatings']}</p>";
                echo "<p>Rating: " . round($album['AverageRating'], 2) . "</p>";
                echo "</div>";
            }
            $number++;
        }
        ?>

        <!-- Pagination links -->
        <div class="pagination">
            <?php
            // Calculate the total number of pages
            $totalPages = ceil(count(getTopRatedAlbums($mysqli, 0, PHP_INT_MAX)) / $perPage);

            // Display pagination links
            echo "<div class='pagination'>";
            for ($i = 1; $i <= min(5, $totalPages); $i++) {
                echo "<a href='top_chart.php?page=$i'>$i</a>";
            }
            echo "</div>";
            ?>
        </div>

    </section>

</body>

</html>