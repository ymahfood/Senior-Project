<?php
session_start();
require_once("database.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $requestID = $_POST['request_id'];
    $userID = $_POST['user_id'];

    if (isset($_POST['accept_request'])) {
        $newStatus = 'Accepted';
    } elseif (isset($_POST['deny_request'])) {
        $newStatus = 'Denied';
    } else {
        echo "Invalid action.";
        exit();
    }

    try {
        $mysqli = Database::dbConnect();
        $mysqli->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $success = updateAlbumRequestStatus($mysqli, $requestID, $newStatus);

        if ($success) {
            // Successfully processed the request
            header("Location: view_album_requests.php"); // Redirect to the verification requests page
            exit();
        } else {
            // Failed to process the request
            echo "Error: Unable to update the request status.";
        }
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    } finally {
        $mysqli = null; // Close the database connection
    }
} else {
    // Handle cases where the request method is not POST
    echo "Invalid request method.";
}

function updateAlbumRequestStatus($mysqli, $requestID, $newStatus) {
    try {
        $query = "UPDATE AlbumRequest SET RequestStatus = :newStatus WHERE RequestID = :requestID";
        $stmt = $mysqli->prepare($query);
        $stmt->bindParam(':newStatus', $newStatus);
        $stmt->bindParam(':requestID', $requestID, PDO::PARAM_INT);
        
        return $stmt->execute();
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
        return false;
    }
}
?>