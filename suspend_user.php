<?php
    session_start();
    require_once('database.php');

    if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] != 'Admin') {
        header("Location: index.php");
        exit();
    }

    $profileID = isset($_POST['profile_id']) ? $_POST['profile_id'] : null;

    if ($profileID) {
        if (isset($_POST['confirm_delete'])) {
            $mysqli = Database::dbConnect();
            $mysqli->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
            $deleteQuery = "UPDATE User SET UserType = 'Deleted' WHERE UserID = :userID";
            $deleteStmt = $mysqli->prepare($deleteQuery);
            $deleteStmt->bindParam(':userID', $profileID, PDO::PARAM_INT);
    
            try {
                $deleteStmt->execute();
                header("Location: user_profiles.php?profile_id=$profileID");
                exit();
            } catch (PDOException $e) {
                echo "Error: " . $e->getMessage();
            }
        }
    } else {
        header("Location: user_profiles.php?profile_id=$profileID");
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
        <div class='delete-confirmation'>
            <h1>Suspend User Confirmation</h1>

            <p>Are you sure you want to suspend this user?</p>

            <form action="suspend_user.php" method="post">
                <input type="hidden" name="profile_id" value="<?php echo $profileID; ?>">
                <button type="submit" name="confirm_delete">Yes, delete</button>
                <?php echo "<a href=user_profiles.php?profile_id=$profileID>No, go back</a>";?>
            </form>
        </div>
    </body>

</body>

</html> 