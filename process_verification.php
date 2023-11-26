<?php
session_start();
require_once('database.php');

$mysqli = Database::dbConnect();
$mysqli->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

function updateVerificationStatus($mysqli, $requestID, $status, $userID, $artistID)
{
    $query = "UPDATE ArtistVerificationRequest SET VerificationStatus = :verification WHERE RequestID = :requestID";
    $stmt = $mysqli->prepare($query);
    $stmt->bindParam(':verification', $status, PDO::PARAM_STR);
    $stmt->bindParam(':requestID', $requestID, PDO::PARAM_INT);
    $stmt->execute();

    if ($status === 'Accepted') {
        // If the request is accepted, update UserArtist table and User table
        $userArtistQuery = "INSERT INTO UserArtist (UserID, ArtistID) VALUES (:userID, :artistID)";
        $userArtistStmt = $mysqli->prepare($userArtistQuery);
        $userArtistStmt->bindParam(':userID', $userID, PDO::PARAM_INT);
        $userArtistStmt->bindParam(':artistID', $artistID, PDO::PARAM_INT);
        $userArtistStmt->execute();

        // Update UserType to 'Artist'
        $updateUserTypeQuery = "UPDATE User SET UserType = 'Artist' WHERE UserID = :userID";
        $updateUserTypeStmt = $mysqli->prepare($updateUserTypeQuery);
        $updateUserTypeStmt->bindParam(':userID', $userID, PDO::PARAM_INT);
        $updateUserTypeStmt->execute();
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['accept_request']) || isset($_POST['deny_request'])) {
        $requestID = $_POST['request_id'];
        $userID = $_POST['user_id'];
        $artistID = $_POST['artist_id'];
        $status = isset($_POST['accept_request']) ? 'Accepted' : 'Denied';

        updateVerificationStatus($mysqli, $requestID, $status, $userID, $artistID);

        header("Location: verification_requests.php");
        exit();
    }
}

header("Location: verification_requests.php");
exit();
?>