<?php
    session_start();
    require_once('database.php');

    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit();
    }

    $mysqli = Database::dbConnect();
    $mysqli->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $userId = $_SESSION['user_id'];
    $query = "SELECT * FROM UserSettings WHERE UserID = :userId";
    $statement = $mysqli->prepare($query);
    $statement->bindParam(':userId', $userId, PDO::PARAM_INT);
    $statement->execute();
    $userSettings = $statement->fetch(PDO::FETCH_ASSOC);

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $biography = htmlspecialchars($_POST['biography']);
        $favoriteArtists = htmlspecialchars($_POST['favoriteArtists']);
        $favoriteGenres = htmlspecialchars($_POST['favoriteGenres']);
        $favoritePlaylist = htmlspecialchars($_POST['favoritePlaylist']);
        $lastFmUsername = htmlspecialchars($_POST['lastFmUsername']);

        $checkQuery = "SELECT COUNT(*) FROM UserSettings WHERE UserID = :userId";
        $checkStatement = $mysqli->prepare($checkQuery);
        $checkStatement->bindParam(':userId', $userId, PDO::PARAM_INT);
        $checkStatement->execute();

        $existingUserCount = $checkStatement->fetchColumn();

        if ($existingUserCount > 0) {
            $updateQuery = "UPDATE UserSettings 
                            SET Biography = :biography, 
                                FavoriteArtists = :favoriteArtists, 
                                FavoriteGenres = :favoriteGenres, 
                                FavoritePlaylist = :favoritePlaylist, 
                                LastFmUsername = :lastFmUsername
                            WHERE UserID = :userId";

            $updateStatement = $mysqli->prepare($updateQuery);
            $updateStatement->bindParam(':biography', $biography, PDO::PARAM_STR);
            $updateStatement->bindParam(':favoriteArtists', $favoriteArtists, PDO::PARAM_STR);
            $updateStatement->bindParam(':favoriteGenres', $favoriteGenres, PDO::PARAM_STR);
            $updateStatement->bindParam(':favoritePlaylist', $favoritePlaylist, PDO::PARAM_STR);
            $updateStatement->bindParam(':lastFmUsername', $lastFmUsername, PDO::PARAM_STR);
            $updateStatement->bindParam(':userId', $userId, PDO::PARAM_INT);

            if ($updateStatement->execute()) {
                header("Location: profile.php");
                exit();
            } else {
                $error = "Error updating settings.";
            }
        } else {
            $insertQuery = "INSERT INTO UserSettings (UserID, Biography, FavoriteArtists, FavoriteGenres, FavoritePlaylist, LastFmUsername) 
                            VALUES (:userId, :biography, :favoriteArtists, :favoriteGenres, :favoritePlaylist, :lastFmUsername)";
            $insertStatement = $mysqli->prepare($insertQuery);
            $insertStatement->bindParam(':biography', $biography, PDO::PARAM_STR);
            $insertStatement->bindParam(':favoriteArtists', $favoriteArtists, PDO::PARAM_STR);
            $insertStatement->bindParam(':favoriteGenres', $favoriteGenres, PDO::PARAM_STR);
            $insertStatement->bindParam(':favoritePlaylist', $favoritePlaylist, PDO::PARAM_STR);
            $insertStatement->bindParam(':lastFmUsername', $lastFmUsername, PDO::PARAM_STR);
            $insertStatement->bindParam(':userId', $userId, PDO::PARAM_INT);

            if ($insertStatement->execute()) {
                header("Location: profile.php");
                exit();
            } else {
                $error = "Error updating settings.";
            }
        }
    }
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Settings - YourFavoriteAlbum.com</title>
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

    <main>
    <div class="user-settings">
        <h2>User Settings</h2>
        <form action="usersettings.php" method="post">
            <div class="form-group">
                <label for="biography">Biography:</label>
                <textarea name="biography"><?php echo $userSettings['Biography']; ?></textarea>
            </div>

            <div class="form-group">
                <label for="favoriteArtists">Favorite Artists:</label>
                <input type="text" name="favoriteArtists" value="<?php echo $userSettings['FavoriteArtists']; ?>">
            </div>

            <div class="form-group">
                <label for="favoriteGenres">Favorite Genres:</label>
                <input type="text" name="favoriteGenres" value="<?php echo $userSettings['FavoriteGenres']; ?>">
            </div>

            <?php $playlist = $userSettings['FavoritePlaylist']; ?>

            <div class="form-group">
                <label for="favoritePlaylist">Favorite Playlist (Spotify Playlist Link):</label>
                <input type="text" name="favoritePlaylist" value="<?php echo $playlist; ?>">
            </div>

            <div class="form-group">
                <label for="lastFmUsername">Last.fm Username:</label>
                <input type="text" name="lastFmUsername" value="<?php echo $userSettings['LastFmUsername']; ?>">
            </div>

            <button type="submit">Save Changes</button>
        </form>

        <?php
        if (isset($error)) {
            echo '<p class="error">' . $error . '</p>';
        }
        ?>
        <form action="artistverification.php" method="post">
            <h2>Request Artist Verification</h2>
            <p>Submit this form to request artist verification.</p>

            <div class="form-group">
                <label for="artistID">Select Artist:</label>
                <select name="artistID" required>
                    <?php
                    $artistQuery = "SELECT ArtistID, ArtistName FROM Artist";
                    $artistStatement = $mysqli->prepare($artistQuery);
                    $artistStatement->execute();
                    $artists = $artistStatement->fetchAll(PDO::FETCH_ASSOC);
                    
                    foreach ($artists as $artist) {
                        echo "<option value=\"{$artist['ArtistID']}\">{$artist['ArtistName']}</option>";
                    }
                    ?>
                </select>
            </div>

            <div class="form-group">
                <label for="twitter">Twitter Handle:</label>
                <input type="text" name="twitter" value="">
            </div>

            <div class="form-group">
                <label for="managementEmail">Management Email:</label>
                <input type="text" name="managementEmail" value="">
            </div>

            <button type="submit">Submit Request</button>
        </form>
    </div>
</main>

</body>

</html>