<?php
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    require_once("database.php");

    $album_name = $_POST["album_name"];
    $artist_name = $_POST["artist_name"];
    $genres = $_POST["genres"];
    $release_date = $_POST["date"];

    // You can add additional validation if needed

    $user_id = $_SESSION["user_id"]; // Assuming the user is logged in
    $username = $_SESSION['username'];

    $mysqli = Database::dbConnect();
    $mysqli->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $query = "INSERT INTO AlbumRequest (UserID, AlbumName, Genres, ReleaseDate, ArtistName, Username) 
            VALUES (:user_id, :album_name, :genres, :release_date, :artist_name, :username)";

    $stmt = $mysqli->prepare($query);
    $stmt->bindParam(":user_id", $user_id, PDO::PARAM_INT);
    $stmt->bindParam(":album_name", $album_name, PDO::PARAM_STR);
    $stmt->bindParam(":genres", $genres, PDO::PARAM_STR);
    $stmt->bindParam(":release_date", $release_date, PDO::PARAM_STR);
    $stmt->bindParam(":artist_name", $artist_name, PDO::PARAM_STR);
    $stmt->bindParam(":username", $username, PDO::PARAM_STR);

    if ($stmt->execute()) {
        header("Location: usersettings.php?request=1");
        exit();
    } else {
        header("Location: usersettings.php?request=0");
        exit();
    }
}
?>