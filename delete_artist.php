<?php
    session_start();
    require_once('database.php');

    if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] != 'Admin') {
        header("Location: index.php");
        exit();
    }

    $artistID = isset($_POST['artist_id']) ? $_POST['artist_id'] : null;

    if ($artistID) {
        if (isset($_POST['confirm_delete'])) {
            $mysqli = Database::dbConnect();
            $mysqli->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
            $deleteQuery = "UPDATE Artist SET ArtistStatus = 'Deleted' WHERE ArtistID = :artistID";
            $deleteStmt = $mysqli->prepare($deleteQuery);
            $deleteStmt->bindParam(':artistID', $artistID, PDO::PARAM_INT);
    
            try {
                $deleteStmt->execute();
                header("Location: artist.php?artist_id=$artistID");
                exit();
            } catch (PDOException $e) {
                echo "Error: " . $e->getMessage();
            }
        }
    } else {
        header("Location: artist.php?artist_id=$artistID");
        exit();
    }
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
            </div>
        </nav>
    <?php endif; ?>

    <body>

        <h1>Delete Artist Confirmation</h1>

        <p>Are you sure you want to delete this artist?</p>

        <form action="delete_artist.php" method="post">
            <input type="hidden" name="artist_id" value="<?php echo $artistID; ?>">
            <button type="submit" name="confirm_delete">Yes, delete</button>
            <?php echo "<a href=artist.php?artist_id=$artistID>No, go back</a>";?>
        </form>
    </body>

</body>

</html>