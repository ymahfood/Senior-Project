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
        $query = "SELECT Album.AlbumID, Artist.ArtistID, Album.AlbumName, Artist.ArtistName, Album.ReleaseDate, Album.GenreID, Genres.GenreName, Album.AverageRating, Album.AlbumStatus
                FROM Album
                LEFT JOIN Artist ON Album.ArtistID = Artist.ArtistID
                LEFT JOIN Genres ON Album.GenreID = Genres.GenreID
                WHERE Album.AlbumID = :albumID";

        $stmt = $mysqli->prepare($query);

        if (!$stmt) {
            die("Query failed: " . $mysqli->error());
        }
        $stmt->bindParam(':albumID', $albumID, PDO::PARAM_INT);
        $stmt->execute();
        
        $details = $stmt->fetch(PDO::FETCH_ASSOC);
        return $details;
    }

    $reviewQuery = "SELECT Rating.Rating, Rating.Review, Rating.UserID, Rating.AlbumID, User.Username
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

    <section class="album-page">
        <?php
                $albumDetails = getAlbumDetails($albumID, $mysqli);

                $queryNumRatings = "SELECT COUNT(*) as count FROM Rating WHERE AlbumID = :albumID";
                $stmtNumRatings = $mysqli->prepare($queryNumRatings);
                $stmtNumRatings->bindParam(':albumID', $albumID, PDO::PARAM_INT);
                $stmtNumRatings->execute();
                $numRatings = $stmtNumRatings->fetch(PDO::FETCH_ASSOC);

                if ($albumDetails) {
                    if($albumDetails['AlbumStatus'] != 'Deleted') {
                        echo "<h2>{$albumDetails['AlbumName']}</h2>";
                        echo "<div class='indented-section'>";
                        echo "<p>Artist: <a href='artist.php?artist_id={$albumDetails['ArtistID']}'>{$albumDetails['ArtistName']}</a></p>";
                        echo "<p>Release Date: {$albumDetails['ReleaseDate']}</p>";
                        echo "<p>Genres: {$albumDetails['GenreName']}</p>";
                        echo "<p>Number of Ratings: {$numRatings['count']}</p>";
                        echo "<p>Average Rating: {$albumDetails['AverageRating']}</p>";
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

                            for ($i = 1; $i <= 5; $i++) {
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
                            echo "<h2>Your Rating/Review:</h2>";
                            if ($presetValue['Rating']){
                                echo "<div class = 'album'>";
                                echo "<p>{$_SESSION['username']}</p>";
                                echo "<p>Rating: {$presetValue['Rating']}</p>";
                                if ($presetValue['Review']) {
                                    echo "<p>Review: {$presetValue['Review']}</p>";
                                }
                                echo "</div>";
                            } else {
                                echo "<p>No user rating found.</p>";
                            }
                        }
                        
                        echo "<h2>Reviews:</h2>";
                        foreach($reviewDetails as $reviews){
                            if ($reviews['Review']) {
                                echo "<div class = 'album'>";
                                echo "<p><a href='user_profiles.php?profile_id={$reviews['UserID']}'>{$reviews['Username']}</a></p>";
                                echo "<p>Rating: {$reviews['Rating']}</p>";
                                if($reviews['Review']) {
                                    echo "<p>Review: {$reviews['Review']}</p>";
                                }
                                echo "</div>";
                            }
                        }
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