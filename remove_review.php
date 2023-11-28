<?php
session_start();

require_once('database.php');

$mysqli = Database::dbConnect();
$mysqli->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

if (isset($_SESSION['user_type']) && $_SESSION['user_type'] == 'Admin') {
    if (isset($_POST['rating_id'])) {
        $ratingID = $_POST['rating_id'];

        $stmt = $mysqli->prepare("UPDATE Rating SET Review = NULL WHERE RatingID = :ratingID");
        $stmt->bindParam(':ratingID', $ratingID, PDO::PARAM_INT);
        $stmt->execute();

        echo "Review removed successfully. {$ratingID}";
        
    } else {
        echo "Rating ID not provided.";
    }
} else {
    echo "You do not have permission to remove reviews.";
}
?>