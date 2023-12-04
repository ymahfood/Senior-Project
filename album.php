<?php
session_start();

require_once("database.php");

    $mysqli = Database::dbConnect();
    $mysqli->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);


    if ($_GET['album_id']){
        $albumID = $_GET['album_id'];
    } else {
        echo "<h2>Error: No album query parameter given.</h2>";
    }

    function getAlbumDetails($albumID, $mysqli) {
        $query = "SELECT Album.AlbumID, Artist.ArtistID, Album.AlbumName, Artist.ArtistName, Album.ReleaseDate, Album.AverageRating, Album.NumRatings, Album.AlbumStatus, AlbumGenres.GenreID, Genres.GenreName
                FROM Album
                LEFT JOIN Artist ON Album.ArtistID = Artist.ArtistID
                LEFT JOIN AlbumGenres ON Album.AlbumID = AlbumGenres.AlbumID
                LEFT JOIN Genres ON AlbumGenres.GenreID = Genres.GenreID
                WHERE Album.AlbumID = :albumID";

        $stmt = $mysqli->prepare($query);

        if (!$stmt) {
            die("Query failed: " . $mysqli->error());
        }
        $stmt->bindParam(':albumID', $albumID, PDO::PARAM_INT);
        $stmt->execute();
        
        $details = $stmt->fetch(PDO::FETCH_ASSOC);
        $genreNames = [$details['GenreName']];

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $genreNames[] = $row['GenreName'];
        }

        $genreString = implode(', ', $genreNames);
        return [
            'details' => $details,
            'genreNames' => $genreString
        ];
    }

    $reviewQuery = "SELECT Rating.RatingID, Rating.Rating, Rating.Review, Rating.UserID, Rating.AlbumID, User.Username, User.UserType
    FROM Rating 
    LEFT JOIN User ON Rating.UserID = User.UserID
    WHERE Rating.AlbumID = :albumID";

    $reviewStmt = $mysqli->prepare($reviewQuery);
    $reviewStmt->bindParam(':albumID', $albumID, PDO::PARAM_INT);
    $reviewStmt->execute();

    $reviewDetails = $reviewStmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>YourFavoriteAlbum.com - Profile</title>
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

    <section class="album-page">
        <?php
                $albumDetails = getAlbumDetails($albumID, $mysqli);

                $queryNumRatings = "SELECT COUNT(*) as count FROM Rating WHERE AlbumID = :albumID";
                $stmtNumRatings = $mysqli->prepare($queryNumRatings);
                $stmtNumRatings->bindParam(':albumID', $albumID, PDO::PARAM_INT);
                $stmtNumRatings->execute();
                $numRatings = $stmtNumRatings->fetch(PDO::FETCH_ASSOC);

                $artist = $albumDetails['details']['ArtistName'];
                $artist = str_replace(' ', '%20', $artist);

                $album = $albumDetails['details']['AlbumName'];
                $album = str_replace(' ', '%20', $album);

                if ($albumDetails) {
                    if($albumDetails['details']['AlbumStatus'] != 'Deleted') {
                        $lastfm_call = file_get_contents("https://ws.audioscrobbler.com/2.0/?method=album.getinfo&api_key=0b393a85d0b34580aa099c1623623d83&artist={$artist}&album={$album}&format=json");
                        $data = json_decode($lastfm_call, true);

                        if (isset($data['album']['image']) && is_array($data['album']['image']) && !empty($data['album']['image'])) {
                            // Get the URL of the large image (you can change 'large' to other sizes if needed)
                            $lastfmUrl = "http://www.last.fm/music/{$artist}/{$album}";
                            $largeImageURL = $data['album']['image'][3]['#text'];
                
                            // Output the image HTML
                            echo "<div class='cover-container'>";
                            echo "<a href='{$lastfmUrl}' target='_blank'>";
                            echo "<img src='$largeImageURL' alt='Album Image'>";
                            echo "</a>";
                            echo "</div>";
                        }

                        echo "<div class='tracklist-container'>";
                        if (isset($data['album']['tracks']['track']) && is_array($data['album']['tracks']['track'])) {
                            $tracks = $data['album']['tracks']['track'];
                            echo "<ol>";
                            $number = 1;
                            foreach ($tracks as $track) {
                                $trackName = $track['name'];
                                echo "Track $number: $trackName <br>";
                                $number++;
                            }
                            echo "</ol>";
                        
                        } else {
                            echo "<p>No track data available.</p>";
                        }
                        echo "</div>";

                        echo "<h2>{$albumDetails['details']['AlbumName']}</h2>";
                        echo "<hr></hr>";
                        echo "<div class='indented-section'>";
                        echo "<p><b>Artist: <a href='artist.php?artist_id={$albumDetails['details']['ArtistID']}'>{$albumDetails['details']['ArtistName']}</a></b></p>";
                        echo "<hr></hr>";
                        echo "<p>Release Date: {$albumDetails['details']['ReleaseDate']}</p>";
                        echo "<hr></hr>";
                        echo "<p>Genres: {$albumDetails['genreNames']}</p>";
                        echo "<hr></hr>";
                        echo "<p>Number of Ratings: {$albumDetails['details']['NumRatings']}</p>";
                        echo "<hr></hr>";
                        echo "<p>Average Rating: " . round($albumDetails['details']['AverageRating'], 2) . "</p>";
                        echo "<hr></hr>";
                        echo "</div>";
                        if(isset($_SESSION['username'])) {
                            echo "<form action='process_rating.php' method='POST'>";
                            echo "<label for='rating'>Rate this album:</label>";
                            echo "<select name='rating' id='rating'>";

                            $ratingQuery = "SELECT Rating, Review FROM Rating 
                            WHERE UserID = :userID AND AlbumID = :albumID";
                            $ratingStmt = $mysqli->prepare($ratingQuery);
                            $ratingStmt->bindParam(':userID', $_SESSION['user_id'], PDO::PARAM_INT);
                            $ratingStmt->bindParam(':albumID', $albumID, PDO::PARAM_INT);
                            $ratingStmt->execute();

                            $presetValue = $ratingStmt->fetch(PDO::FETCH_ASSOC);

                            for ($i = 0.5; $i <= 5; $i += 0.5) {
                                $selected = ($i == $presetValue['Rating']) ? 'selected' : '';
                                echo "<option value='{$i}' {$selected}>{$i}</option>";
                            }

                            echo "</select>";
                            echo "</select>";
                            echo "<br>";
                            echo "<br>";
                            echo "<label for='review'>Write a review:</label>";
                            echo "<br>";

                            echo "<textarea name='review' id='review' rows='4' cols='50'>{$presetValue['Review']}</textarea>";
                            echo "<input type='hidden' name='album_id' value='{$albumID}'> ";
                            echo "<input type='submit' value='Submit Rating/Review'>";
                            echo "</form>";

                            if (isset($_SESSION['user_type']) && $_SESSION['user_type'] == 'Admin') {
                                echo "<form action='delete_album.php' method='POST'>";
                                echo "<input type='hidden' name='album_id' value='{$albumID}'>";
                                echo "<input type='submit' value='Delete Album'>";
                                echo "</form>";
                            }

                            echo "<br></br>";
                            echo "<h2 class='reviews-heading'>Your Rating/Review:</h2>";
                            if ($presetValue['Rating']){
                                echo "<div class = 'album'>";
                                echo "<p>{$_SESSION['username']}</p>";
                                echo "<p>Rating: {$presetValue['Rating']}</p>";
                                if ($presetValue['Review']) {
                                    echo "<p>Review: {$presetValue['Review']}</p>";
                                }
                                echo "</div>";
                            } else {
                                echo "<p class='error'>No user rating found.</p>";
                            }
                        }
                        echo "<h2 class='reviews-heading'>Reviews:</h2>";
                        if (!empty($reviewDetails)) {
                            foreach ($reviewDetails as $reviews) {
                                if ($reviews['Review'] && $reviews['UserType'] != 'Deleted') {
                                    echo "<div class='album'>";
                                    echo "<p><a href='user_profiles.php?profile_id={$reviews['UserID']}'>{$reviews['Username']}</a></p>";
                                    echo "<p>Rating: {$reviews['Rating']}</p>";
                                    if ($reviews['Review']) {
                                        echo "<p>Review: {$reviews['Review']}</p>";
                                    }
                                    if (isset($_SESSION['user_type']) && $_SESSION['user_type'] == 'Admin') {
                                        $ratingID = $reviews['RatingID'];
                                        echo "<form action='remove_review.php' method='POST'>";
                                        echo "<input type='hidden' name='rating_id' value='{$ratingID}'>";
                                        echo "<input type='submit' value='Remove Review'>";
                                        echo "</form>";
                                    }
                                    echo "</div>";
                                }
                            }
                        } else {
                            echo "<p class='error'>No reviews found.</p>";
                        }

                        echo "<div class='lastfm-button'>";
                        echo "<a href='{$lastfmUrl}' target='_blank'><button>View on Last.fm</button></a>";
                        echo "</div>";
                    } else {
                        echo "<p>Error: Album has been removed.</p>";
                    }
                } else {
                    echo "<p>Error: Album not found.</p>";
                }
        ?>
    </section>

</body>

</html>