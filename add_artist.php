<?php
session_start();
require_once('database.php');

$mysqli = Database::dbConnect();
$mysqli->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate and sanitize the input
    $artistName = filter_input(INPUT_POST, 'artist_name', FILTER_SANITIZE_STRING);

    // Validate whether artist name is not empty
    if (empty($artistName)) {
        $error = "Please enter the artist name.";
    } else {
        // Insert the artist into the database
        try {
            $stmt = $mysqli->prepare("INSERT INTO Artist (ArtistName) VALUES (:artistName)");
            $stmt->bindParam(':artistName', $artistName, PDO::PARAM_STR);
            $stmt->execute();

            $artistId = $mysqli->lastInsertId();

            header("Location: artist.php?artist_id={$artistId}");
            exit();
        } catch (PDOException $e) {
            $error = "Error: " . $e->getMessage();
        }
    }
}

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
                <a href="add_artist.php"><button>Add Artist</button></a>
            </div>
        </nav>
    <?php endif; ?>
    <div class = 'search-results'>
        <form method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>">
            <label for="artist_name">Artist Name:</label>
            <input type="text" id="artist_name" name="artist_name" value="<?php echo htmlspecialchars($artistName); ?>" required>
            <button type="submit">Add Artist</button>
        </form>
    </div>
</body>
</html>