<?php
session_start();
require_once('database.php');

$mysqli = Database::dbConnect();
$mysqli->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

function getGenreID($genreName, $mysqli)
{
    // Check if the genre already exists
    $checkQuery = "SELECT GenreID FROM Genres WHERE GenreName = :genreName";
    $checkStatement = $mysqli->prepare($checkQuery);
    $checkStatement->bindParam(':genreName', $genreName, PDO::PARAM_STR);
    $checkStatement->execute();
    $existingGenre = $checkStatement->fetch(PDO::FETCH_ASSOC);

    if ($existingGenre) {
        // Genre already exists, return the existing GenreID
        return $existingGenre['GenreID'];
    } else {
        // Genre doesn't exist, add a new genre
        $insertQuery = "INSERT INTO Genres (GenreName) VALUES (:genreName)";
        $insertStatement = $mysqli->prepare($insertQuery);
        $insertStatement->bindParam(':genreName', $genreName, PDO::PARAM_STR);
        $insertStatement->execute();

        // Return the new GenreID
        return $mysqli->lastInsertId();
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $artistID = $_POST['artist_id'];
    $albumName = htmlspecialchars($_POST['album_name']);
    $releaseDate = date('Y-m-d', strtotime($_POST['release_date'])); // Format as YYYY-MM-DD
    $genres = explode(',', $_POST['genres']);

    // Insert the album into the Album table
    $albumQuery = "INSERT INTO Album (AlbumName, ReleaseDate, ArtistID) VALUES (:albumName, :releaseDate, :artistID)";
    $albumStatement = $mysqli->prepare($albumQuery);
    $albumStatement->bindParam(':albumName', $albumName, PDO::PARAM_STR);
    $albumStatement->bindParam(':releaseDate', $releaseDate, PDO::PARAM_STR);
    $albumStatement->bindParam(':artistID', $artistID, PDO::PARAM_INT);
    $albumStatement->execute();

    // Get the AlbumID of the newly added album
    $albumID = $mysqli->lastInsertId();

    // Insert genres into AlbumGenres bridge table
    foreach ($genres as $genre) {
        $genre = trim($genre); // Remove leading/trailing whitespaces
        if (!empty($genre)) {
            $genreID = getGenreID($genre, $mysqli);
    
            $genreQuery = "INSERT INTO AlbumGenres (AlbumID, GenreID) VALUES (:albumID, :genreID)";
            $genreStatement = $mysqli->prepare($genreQuery);
    
            $genreStatement->bindParam(':albumID', $albumID, PDO::PARAM_INT);
            $genreStatement->bindParam(':genreID', $genreID, PDO::PARAM_INT);
            $genreStatement->execute();
        }
    }

    header("Location: artist.php?artist_id=$artistID");
    exit();
} else {
    header("Location: homepage.php"); // Redirect if accessed directly without POST request
    exit();
}
?>