<?php
session_start();

require_once("database.php");
$username = $_SESSION['username'];
$user_id = $_SESSION['user_id'];
$mysqli = Database::dbConnect();
$mysqli->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$query = "SELECT Rating.Rating, Rating.Review, Rating.AlbumID, Album.AlbumName, Artist.ArtistName, Artist.ArtistID, Album.AlbumStatus
FROM Rating 
LEFT JOIN Album ON Rating.AlbumID = Album.AlbumID
LEFT JOIN Artist ON Album.ArtistID = Artist.ArtistID
WHERE Rating.UserID = :userID";
$stmt = $mysqli->prepare($query);
$stmt->bindParam(":userID", $user_id, PDO::PARAM_INT);
$stmt->execute();
$userRatings = $stmt->fetchAll(PDO::FETCH_ASSOC);

$settingsQuery = "SELECT Biography, FavoriteArtists, FavoriteGenres, FavoritePlaylist, LastFmUsername FROM UserSettings WHERE UserID = :userID";
$settingsStmt = $mysqli->prepare($settingsQuery);
$settingsStmt->bindParam(":userID", $user_id, PDO::PARAM_INT);
$settingsStmt->execute();
$userDetails = $settingsStmt->fetch(PDO::FETCH_ASSOC);

$userQuery = "SELECT UserType FROM User WHERE UserID = :userID";
$userStmt = $mysqli->prepare($userQuery);
$userStmt->bindParam(":userID", $user_id, PDO::PARAM_INT);
$userStmt->execute();
$userType = $userStmt->fetch(PDO::FETCH_ASSOC);
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

    <section class="profile-page">
        <?php
        

        if (isset($_SESSION['user_id'])) {
            echo "<h><b>Profile: $username</b></h>";
            if ($userType['UserType'] == 'Artist'){
                $artistQuery = "SELECT Artist.ArtistName, UserArtist.ArtistID FROM Artist LEFT JOIN UserArtist ON Artist.ArtistID = UserArtist.ArtistID WHERE UserArtist.UserID = :userID";
                $artistStmt = $mysqli->prepare($artistQuery);
                $artistStmt->bindParam(':userID', $user_id, PDO::PARAM_INT);
                $artistStmt->execute();
                $details = $artistStmt->fetchAll(PDO::FETCH_ASSOC);

                if (!empty($details)) {
                    $artistName = $details[0]['ArtistName'];
                    $artistID = $details[0]['ArtistID'];
                    echo "<p>This is a verified artist account, check out their artist page here: <a href='artist.php?artist_id={$artistID}'>{$artistName}</a></p>";
                } else {
                    echo "<p>This is a verified artist account, but no artist details found.</p>";
                }
            }
            echo "<hr></hr>";
            echo "<p>Biography: {$userDetails['Biography']}</p>";
            echo "<hr></hr>";
            echo "<p>Favorite Artists: {$userDetails['FavoriteArtists']}</p>";
            echo "<hr></hr>";
            echo "<p>Favorite Genres: {$userDetails['FavoriteGenres']}</p>";
            echo "<hr></hr>";
            if ($userDetails['FavoritePlaylist']) {
                echo "<p>Favorite Playlist: </p>";
                $playlist = $userDetails['FavoritePlaylist'];
                $embed = str_replace("/open.spotify.com/", "/open.spotify.com/embed/", $playlist);
                $embed = strstr($embed, '?', true);

                $embedLink = '<iframe style="border-radius:12px" src=" '.$embed.'?utm_source=generator" width="40%" height="152" frameBorder="0" allowfullscreen="" allow="autoplay; clipboard-write; encrypted-media; fullscreen; picture-in-picture" loading="lazy"></iframe>';
                echo "{$embedLink}";
            }
            if ($userDetails['LastFmUsername']) {
                echo "<h2>Last.fm Top Artists:</h2>";
                echo "<form action='' method='get'>";
                echo "<label for='selectedTimeframe'>Select Timeframe:</label>";
                echo "<select name='selectedTimeframe' id='selectedTimeframe'>";
                $timeframe = array('overall', '7day', '1month', '3month', '6month', '12month');
                foreach($timeframe as $time) {
                    $timeselect = ($_GET['selectedTimeframe'] == $time) ? 'selected' : '';
                    echo "<option value='$time' $timeselect>$time</option>";
                }
                echo "</select>";
                echo "<button type='submit'>Filter</button>";
                echo "</form>";

                $lastfmuser = $userDetails['LastFmUsername'];

                $time = isset($_GET['selectedTimeframe']) ? $_GET['selectedTimeframe'] : 'overall';

                $lastfm_call = file_get_contents("https://ws.audioscrobbler.com/2.0/?method=user.gettopartists&user={$lastfmuser}&period={$time}&api_key=0b393a85d0b34580aa099c1623623d83&format=json");
                $lastfm_data = json_decode($lastfm_call);
                $lastfm_clean = $lastfm_data->topartists->artist;
                $lastfm_clean = array_slice($lastfm_clean, 0, 5);

                echo "<div class='lastfm-artists-container'>";
                foreach ($lastfm_clean as $data) {
                    $name = $data->name;
                    $plays = $data->playcount;
                    echo "<div class='lastfm-artists' data-plays='$plays'>";
                    echo "<p class='artist-name'>$name</p>";
                    echo "<div class='additional-info'>$plays plays</div>";
                    echo "</div>";
                }
                echo "</div>";
            }
            echo "<h2> User Ratings: </h2>";
            echo "<form action='' method='get'>";
            echo "<label for='selectedRating'>Select Rating:</label>";
            echo "<select name='selectedRating' id='selectedRating'>";

            $options = array('All', '5.0', '4.0', '3.0', '2.0', '1.0');
            foreach ($options as $option) {
                $selected = ($_GET['selectedRating'] == $option) ? 'selected' : '';
                echo "<option value='$option' $selected>$option</option>";
            }

            echo "</select>";
            echo "<button type='submit'>Filter</button>";
            echo "</form>";

            if (isset($_GET['selectedRating'])) {
                $selectedRating = ($_GET['selectedRating'] != 'All') ? floatval($_GET['selectedRating']) : 'All';
            } else {
                $selectedRating = 'All';
            }
            
            foreach ($userRatings as $ratings) {
                if ($ratings['AlbumStatus'] != 'Deleted' && ($selectedRating == 'All' || $ratings['Rating'] == $selectedRating)) {
                    echo "<div class='ratings'>";
                    echo "<p>Rating: {$ratings['Rating']}</p>";
                    echo "<h3><a href='album.php?album_id={$ratings['AlbumID']}'>{$ratings['AlbumName']}</a> by <a href='artist.php?artist_id={$ratings['ArtistID']}'>{$ratings['ArtistName']}</a></h3>";
                    if ($ratings['Review']) {
                        echo "<p>Review: {$ratings['Review']}</p>";
                    }
                    echo "</div>";
                }
            }
        } else {
            echo "<h>No user found. Please log in.</h>";
        }
        ?>
        
    </section>

</body>

</html>