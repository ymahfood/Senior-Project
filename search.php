<?php
session_start();
require_once("database.php");

$mysqli = Database::dbConnect();
$mysqli->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

if (isset($_GET['search'])) {
    $searchKeyword = '%' . $_GET['search'] . '%';

    $query = 'SELECT Album.AlbumID, Album.AlbumName, Album.ArtistID, Artist.ArtistName, Album.ReleaseDate, Album.AverageRating 
            FROM Album LEFT JOIN Artist 
            ON Album.ArtistID = Artist.ArtistID 
            WHERE AlbumName LIKE :search';
    
    $stmt = $mysqli->prepare($query);
    $stmt->bindParam(":search", $searchKeyword, PDO::PARAM_STR);
    $stmt->execute();
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $query2 = 'SELECT ArtistID, ArtistName FROM Artist WHERE ArtistName LIKE :search';
    $stmt2 = $mysqli->prepare($query2);
    $stmt2->bindParam(":search", $searchKeyword, PDO::PARAM_STR);
    $stmt2->execute();
    $artists = $stmt2->fetchAll(PDO::FETCH_ASSOC);
}



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
            </div>
        </nav>
    <?php endif; ?>

    <section class="featured-albums">
        <h2>Search Results:</h2>
        <b><p>Albums:</p></b>
        <?php
        if (count($results) > 0) {
            foreach ($results as $r) {
                echo "<div class='search-results'>";
                echo "<h3><a href='album.php?album_id={$r['AlbumID']}'>{$r['AlbumName']}</a></h3>";
                echo "<p><a href='artist.php?artist_id={$r['ArtistID']}'>{$r['ArtistName']}</a></p>";
                echo "<p>Date: {$r['ReleaseDate']}</p>";
                echo "<p>Rating: {$r['AverageRating']}</p>";
                echo "</div>";
            }
        } else {
            echo "No albums found.";
        }
        ?>
        <br></br>
        <b><p>Artists:</p></b>
        <?php
        if (count($artists) > 0) {
            foreach ($artists as $a) {
                echo "<div class ='search-results'>";
                echo "<h3><a href='artist.php?artist_id={$a['ArtistID']}'>{$a['ArtistName']}</a></h3>";
                echo "</div>";
            }
        } else {
            echo "No artists found.";
        }
        ?>
    </section>

</body>

</html>