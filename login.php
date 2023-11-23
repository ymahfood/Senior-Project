<?php
session_start();

require_once("database.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST["username"];
    $password = $_POST["password"];

    $mysqli = Database::dbConnect();
    $mysqli->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $query = "SELECT UserID, Username, PasswordHash, UserType FROM User WHERE Username = :username";
    $stmt = $mysqli->prepare($query);
    $stmt->bindParam(':username', $username);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['PasswordHash'])) {
        $_SESSION['user_id'] = $user['UserID'];
        $_SESSION['username'] = $user['Username'];
        $_SESSION['user_type'] = $user['UserType'];
        header("Location: homepage.php");
        exit;
    } else {
        $error_message = "Invalid username or password. Please try again.";
    }
}
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
            <a href="login.php"><button>Login</button></a>
            <a href="homepage.php"><button>Home</button></a>
            <a href="top_chart.php"><button>Charts</button></a>
            <a href = "profile.php"><button>Profile: 
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
        </div>
    </nav>

    <main>
        <div class="login-form">
            <h2>Login</h2>
            <?php if (isset($error_message)) {
                echo "<p>$error_message</p>";
            } elseif (isset($_SESSION['login_message'])){
                echo "<p>{$_SESSION['login_message']}";
            } ?>
            <form method="post">
                <div>
                    <label for="username">Username:</label>
                    <input type="text" id="username" name="username" required>
                </div>

                <div>
                    <label for="password">Password:</label>
                    <input type="password" id="password" name="password" required>
                </div>
                <button type="submit">Submit</button>
            </form>
            <div><p>Don't have an account?</p></div>
            <a href = "signup.php"><button type = "signup">Sign Up</button></a>
        </div>
    </main>

</body>

</html>