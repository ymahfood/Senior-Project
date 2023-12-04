<?php
    session_start();
    require_once('database.php');

    if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] != 'Admin') {
        header("Location: index.php");
        exit();
    }

    $albumID = isset($_POST['album_id']) ? $_POST['album_id'] : null;

    if ($albumID) {
        if (isset($_POST['confirm_delete'])) {
            $mysqli = Database::dbConnect();
            $mysqli->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
            $deleteQuery = "UPDATE Album SET AlbumStatus = 'Deleted' WHERE AlbumID = :albumID";
            $deleteStmt = $mysqli->prepare($deleteQuery);
            $deleteStmt->bindParam(':albumID', $albumID, PDO::PARAM_INT);
    
            try {
                $deleteStmt->execute();
                header("Location: album.php?album_id=$albumID");
                exit();
            } catch (PDOException $e) {
                echo "Error: " . $e->getMessage();
            }
        }
    } else {
        header("Location: album.php?album_id=$albumID");
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
                <a href="view_album_requests.php"><button>Album Requests</button></a>
                <a href="add_artist.php"><button>Add Artist</button></a>
            </div>
        </nav>
    <?php endif; ?>

    <body>

        <h1>Delete Album Confirmation</h1>

        <p>Are you sure you want to delete this album?</p>

        <form action="delete_album.php" method="post">
            <input type="hidden" name="album_id" value="<?php echo $albumID; ?>">
            <button type="submit" name="confirm_delete">Yes, delete</button>
            <?php echo "<a href=album.php?album_id=$albumID>No, go back</a>";?>
        </form>
    </body>

</body>

</html>
