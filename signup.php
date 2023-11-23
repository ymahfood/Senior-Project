<?php
session_start();
require_once("database.php");

$mysqli = Database::dbConnect();
$mysqli->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST["username"];
    $email = $_POST["email"];
    $password = password_hash($_POST["password"], PASSWORD_BCRYPT);

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = "Invalid email address. Please enter a valid email.";
    } else {
        $query = "SELECT Username FROM User WHERE Username = :username";
        $query2 = "SELECT Email FROM User WHERE Email = :email";

        $stmt = $mysqli->prepare($query);
        $stmt->bindParam(':username', $username);
        $stmt->execute();

        $stmt2 = $mysqli->prepare($query2);
        $stmt2->bindParam(':email', $email);
        $stmt2->execute();

        if ($stmt->rowCount() > 0 || $stmt2->rowCount() > 0) {
            if ($stmt->rowCount() > 0 && $stmt2->rowCount() > 0) {
                $error_message = "Username and Email already in use. {$username} {$email}";
            } elseif ($stmt->rowCount() > 0) {
                $error_message = "Username already taken. Please try again. {$username} {$email}";
            } elseif ($stmt2->rowCount() > 0) {
                $error_message = "Email already in use. Try another.";
            }
        } else {
            $add_username_query = "INSERT INTO User (Username, PasswordHash, Email, UserType) VALUES (:username, :pass, :email, 'Normal')";
            $stmt_add = $mysqli->prepare($add_username_query);
            $stmt_add->bindParam(':username', $username);
            $stmt_add->bindParam(':pass', $password);
            $stmt_add->bindParam(':email', $email);
            $stmt_add->execute();

            $_SESSION['login_message'] = "Please log in with your new credentials.";
            header("Location: login.php");
            exit();
        }
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
        <div class = "signup-form">
            <h2>Sign Up</h2>
            <?php if (isset($error_message)) {
                echo "<p>$error_message</p>";
            } ?>

            <form method="post">
                <div>
                    <label for="username">Username:</label>
                    <input type="text" id="username" name="username" required>
                </div>

                <div>
                    <label for="email">Email:</label>
                    <input type="email" id="email" name="email" required>
                </div>

                <div>
                    <label for="password">Password:</label>
                    <input type="password" id="password" name="password" required>
                </div>
                <button type="submit">Submit</button>
            </form>
        </div>
    </main>

</body>

</html>