<?php
session_start();

require_once("database.php");

$mysqli = Database::dbConnect();
$mysqli->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$session_user_id = $_SESSION['user_id'];

if ($_GET['artist_id']){
    $artistID = $_GET['artist_id'];
} else {
    echo "<h2>Error: No album query parameter given.</h2>";
}

function getArtistDetails($artistID, $mysqli) {

    $query = "SELECT Artist.ArtistID, Album.AlbumID, Artist.ArtistName, Album.AlbumName, Album.AverageRating, Album.AlbumStatus, Artist.ArtistStatus
            FROM Artist
            LEFT JOIN Album ON Artist.ArtistID = Album.ArtistID
            WHERE Artist.ArtistID = :artistID";

    $stmt = $mysqli->prepare($query);
    if (!$stmt) {
        die("Query failed: " . $mysqli->error());
    }
    $stmt->bindParam(':artistID', $artistID, PDO::PARAM_INT);
    $stmt->execute();
    $details = $stmt->fetchAll(PDO::FETCH_ASSOC);
    return $details;
}

$userQuery = "SELECT UserArtist.UserID, User.Username FROM UserArtist LEFT JOIN User ON UserArtist.UserID = User.UserID WHERE ArtistID = :artistID";
$userStmt = $mysqli->prepare($userQuery);
$userStmt->bindParam(':artistID', $artistID, PDO::PARAM_INT);
$userStmt->execute();
$userDetails = $userStmt -> fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>YourFavoriteAlbum.com - Artist Page</title>
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

    <section class="artist-page">
        <?php
        $artistDetails = getArtistDetails($artistID, $mysqli);
        if($artistDetails[0]['ArtistStatus'] != 'Deleted'){
            $typeQuery = "SELECT ArtistID FROM UserArtist WHERE UserID = :userID";
            $typeStmt = $mysqli->prepare($typeQuery);
            $typeStmt->bindParam(':userID', $session_user_id, PDO::PARAM_INT);
            $typeStmt->execute();
            $idMatch = $typeStmt->fetch(PDO::FETCH_ASSOC);            

            echo "<h2>{$artistDetails[0]['ArtistName']}</h2>";
            if (isset($userDetails[0])) {
                echo "<p>This artist has a profile on our website, check them out here: <a href='user_profiles.php?profile_id={$userDetails[0]['UserID']}'>{$userDetails[0]['Username']}</a></p>";
            }
            if (isset($_SESSION['user_type']) && $_SESSION['user_type'] == 'Admin') {
                echo "<form action='delete_artist.php' method='POST'>";
                echo "<input type='hidden' name='artist_id' value='{$artistID}'>";
                echo "<input type='submit' value='Delete Artist'>";
                echo "</form>";
            }
            echo "<h3>Albums:</h3>";

            if ($artistDetails) {
                foreach ($artistDetails as $album) {
                    if ($album['AlbumStatus'] != 'Deleted' && isset($album['AlbumName'])){
                        echo "<div class='album'>";
                        echo "<h3><a href='album.php?album_id={$album['AlbumID']}'>{$album['AlbumName']}</a></h3>";
                        echo "<p>Average Rating: ". round($album['AverageRating'], 2) . "</p>";
                        echo "</div>";
                    }
                }

                if (!isset($artistDetails[0]['AlbumName'])) {
                    echo "<p>No albums found for this artist.<p>";
                }
            } else {
                echo "<p>Artist not found.<p>";
            }
            if (isset($_SESSION['user_type']) && $_SESSION['user_type'] == 'Artist' && $idMatch['ArtistID'] == $artistID || $_SESSION['user_type'] == 'Admin') {
                echo "<h3>Add Album:</h3>";
                echo "<form action='add_album.php' method='POST'>";
                echo "<input type='hidden' name='artist_id' value='{$artistID}'>";
            
                echo "<label for='album_name'>Album Name:</label>";
                echo "<input type='text' name='album_name' required><br>";
            
                echo "<label for='release_date'>Release Date:</label>";
                echo "<input type='date' name='release_date' required><br>";

                echo "<label for='genres'>Genres (Separate With Commas):</label>";
                echo "<input type='text' name='genres' required><br>";
            
                echo "<button type='submit'>Add Album</button>";
                echo "</form>";
            }
        } else {
            echo "<p>Error: Artist has been removed.</p>";
        }
        ?>
    </section>

</body>

</html>