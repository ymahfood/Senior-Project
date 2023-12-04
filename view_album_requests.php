<?php
session_start();
require_once('database.php');

$mysqli = Database::dbConnect();
$mysqli->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$query = "SELECT * FROM AlbumRequest";
$stmt = $mysqli->prepare($query);
$stmt -> execute();
$requests = $stmt->fetchAll(PDO::FETCH_ASSOC);

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

    <section class="verification-page">
    <h2>Album Requests</h2>

    <p>Make sure to do research before accepting a request.</p>
    <p>Before accepting a request, make sure you create a new artist if the artist is not already in our database.</p>
    <p>Also, add the album to the artist's page before accepting the request<p>

    <?php
    if ($requests != NULL) {
        $pendingRequestsExist = false; // Flag to check if there are pending requests
    
        foreach ($requests as $request) {
            if ($request['RequestStatus'] == 'Pending') {
                $pendingRequestsExist = true; // Set the flag to true if at least one request is pending
    
                echo "<b><p>Request:</p></b>";
                echo "<div class='verification-requests'>";
                echo "<h3>Username: <a href='user_profiles.php?profile_id={$request['UserID']}'>{$request['Username']}</a></h3>";
                echo "<p>Artist Name: {$request['ArtistName']}</p>";
                echo "<b><p>Album Name: {$request['AlbumName']}</p></b>";
                echo "<p>Genres: {$request['Genres']}</p>";
                echo "<p>Release Date: {$request['ReleaseDate']}</p>";
    
                // Add buttons for accepting and denying
                echo "<form action='process_request.php' method='post'>";
                echo "<input type='hidden' name='request_id' value='{$request['RequestID']}'>";
                echo "<input type='hidden' name='user_id' value='{$request['UserID']}'>";
                echo "<button type='submit' name='accept_request'>Accept Request</button>";
                echo "<p> </p>";
                echo "<button type='submit' name='deny_request'>Deny Request</button>";
                echo "</form>";
    
                echo "</div>";
            }
        }
    
        // Display "No requests found" if no pending requests were found
        if (!$pendingRequestsExist) {
            echo "<p>No pending requests found.</p>";
        }
    } else {
        echo "<p>No requests found.</p>";
    }
    ?>
</section>

</body>

</html>