<?php
session_start();

require_once("database.php");

    $mysqli = Database::dbConnect();
	$mysqli->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $genreQuery = "SELECT GenreID, GenreName FROM Genres LIMIT 10";
        
    $genreStmt = $mysqli->prepare($genreQuery);
    $genreStmt -> execute();
    $genres = $genreStmt->fetchAll(PDO::FETCH_ASSOC);
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
                <a href="view_album_requests.php"><button>Album Requests</button></a>
                <a href="add_artist.php"><button>Add Artist</button></a>
            </div>
        </nav>
    <?php endif; ?>
    <section class="featured-albums">
        <h2>Featured Albums</h2>

        <?php
        foreach ($genres as $genre) {
            $gid = $genre['GenreID'];

            $query = "SELECT Album.AlbumID, Artist.ArtistID, Album.AlbumName, Artist.ArtistName, Album.AverageRating, Album.ReleaseDate, Album.AlbumStatus, AlbumGenres.GenreID
                    FROM Album 
                    LEFT JOIN AlbumGenres ON Album.AlbumID = AlbumGenres.AlbumID
                    LEFT JOIN Artist ON Album.ArtistID = Artist.ArtistID
                    WHERE AlbumGenres.GenreID = :genreID
                    ORDER BY Album.AverageRating DESC
                    LIMIT 1";

            $stmt = $mysqli->prepare($query);
            $stmt->bindParam(':genreID', $gid, PDO::PARAM_INT);
            $stmt->execute();
            $album = $stmt->fetch(PDO::FETCH_ASSOC);

            $artistName = $album['ArtistName'];
            $albumName = $album['AlbumName'];

            echo "<b><p>{$genre['GenreName']}:</p></b>";
            if ($album['AlbumStatus'] != 'Deleted') {
                echo "<div class='homepage-album'>";
                echo "<h3><a href='album.php?album_id={$album['AlbumID']}'>{$album['AlbumName']}</a></h3>";
                echo "<p><a href='artist.php?artist_id={$album['ArtistID']}'>$artistName</a></p>";
                echo "<p>Date: {$album['ReleaseDate']}</p>";
                echo "<p>Rating: " . round($album['AverageRating'], 2) . "</p>";
                echo "</div>";
            }
        }
        ?>


    </section>

</body>

</html>