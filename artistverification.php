<?php
session_start();
require_once('database.php');

$mysqli = Database::dbConnect();
$mysqli->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userID = $_SESSION['user_id'];
    $username = $_SESSION['username'];
    $artistID = $_POST['artistID'];
    $twitterHandle = $_POST['twitter'];
    $managementEmail = $_POST['managementEmail'];

    $artistQuery = "SELECT ArtistName FROM Artist WHERE ArtistID = :artistID";
    $artistStatement = $mysqli->prepare($artistQuery);

    $artistStatement->bindParam(':artistID', $artistID, PDO::PARAM_INT);
    $artistStatement->execute();
    $artistName = $artistStatement->fetch(PDO::FETCH_ASSOC);
    $artistName = $artistName['ArtistName'];

    $verificationQuery = "INSERT INTO ArtistVerificationRequest (UserID, ArtistID, TwitterHandle, ManagementEmail, Username, ArtistName) 
                          VALUES (:userID, :artistID, :twitterHandle, :managementEmail, :username, :artistName)";
    $verificationStatement = $mysqli->prepare($verificationQuery);
    
    $verificationStatement->bindParam(':userID', $userID, PDO::PARAM_INT);
    $verificationStatement->bindParam(':artistID', $artistID, PDO::PARAM_INT);
    $verificationStatement->bindParam(':twitterHandle', $twitterHandle, PDO::PARAM_STR);
    $verificationStatement->bindParam(':managementEmail', $managementEmail, PDO::PARAM_STR);
    $verificationStatement->bindParam(':artistName', $artistName, PDO::PARAM_STR);
    $verificationStatement->bindParam(':username', $username, PDO::PARAM_STR);
    
    if ($verificationStatement->execute()) {
        header("Location: homepage.php");
        exit();
    } else {
        echo "Error submitting artist verification request.";
    }

}
?>