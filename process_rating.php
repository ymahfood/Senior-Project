<?php
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    require_once("database.php");

    $user_id = $_SESSION['user_id'];
    $albumID = $_POST['album_id'];
    $rating = $_POST['rating'];
    $review = $_POST['review'];

    $mysqli = Database::dbConnect();
    $mysqli->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $queryCheck = "SELECT COUNT(*) AS count FROM Rating WHERE AlbumID = :albumID AND UserID = :userID";
    $stmtCheck = $mysqli->prepare($queryCheck);
    $stmtCheck->bindParam(':albumID', $albumID, PDO::PARAM_INT);
    $stmtCheck->bindParam(':userID', $user_id, PDO::PARAM_INT);
    $stmtCheck->execute();
    $resultCheck = $stmtCheck->fetch(PDO::FETCH_ASSOC);

    if ($resultCheck['count'] > 0) {
        $queryUserRatingUpdate = "UPDATE Rating SET Rating = :rating, Review = :review WHERE AlbumID = :albumID AND UserID = :userID";
        $stmtUserRatingUpdate = $mysqli->prepare($queryUserRatingUpdate);
        $stmtUserRatingUpdate->bindParam(':rating', $rating, PDO::PARAM_INT);
        $stmtUserRatingUpdate->bindParam(':albumID', $albumID, PDO::PARAM_INT);
        $stmtUserRatingUpdate->bindParam(':userID', $user_id, PDO::PARAM_INT);
        $stmtUserRatingUpdate->bindParam(':review', $review, PDO::PARAM_STR);
        if ($stmtUserRatingUpdate->execute()) {
            $query2 = "SELECT Rating FROM Rating WHERE AlbumID = :albumID";
            $stmt2 = $mysqli->prepare($query2);
            $stmt2->bindParam(':albumID', $albumID, PDO::PARAM_INT);
            $stmt2->execute();
            $ratings = $stmt2->fetchAll(PDO::FETCH_ASSOC);

            $totalRatings = count($ratings);
            $sumRatings = array_sum(array_column($ratings, 'Rating'));
            $averageRating = $totalRatings > 0 ? $sumRatings / $totalRatings : 0;

            $query3 = "UPDATE Album SET AverageRating = :averageRating WHERE AlbumID = :albumID";
            $stmt3 = $mysqli->prepare($query3);
            $stmt3->bindParam(':averageRating', $averageRating, PDO::PARAM_STR);
            $stmt3->bindParam(':albumID', $albumID, PDO::PARAM_INT);
            $stmt3->execute();
            header("Location: album.php?album_id=$albumID");
            exit();
        } else {
            echo "Error updating rating: " . $stmtInsert->error;
        }
    } else {
        $queryInsert = "INSERT INTO Rating (Rating, AlbumID, UserID, Review) VALUES (:rating, :albumID, :userID, :review)";
        $stmtInsert = $mysqli->prepare($queryInsert);
        $stmtInsert->bindParam(':rating', $rating, PDO::PARAM_STR);
        $stmtInsert->bindParam(':albumID', $albumID, PDO::PARAM_INT);
        $stmtInsert->bindParam(':userID', $user_id, PDO::PARAM_INT);
        $stmtInsert->bindParam(':review', $review, PDO::PARAM_STR);

        if ($stmtInsert->execute()) {
            $query2 = "SELECT Rating FROM Rating WHERE AlbumID = :albumID";
            $stmt2 = $mysqli->prepare($query2);
            $stmt2->bindParam(':albumID', $albumID, PDO::PARAM_INT);
            $stmt2->execute();
            $ratings = $stmt2->fetchAll(PDO::FETCH_ASSOC);

            $totalRatings = count($ratings);
            $sumRatings = array_sum(array_column($ratings, 'Rating'));
            $averageRating = $totalRatings > 0 ? $sumRatings / $totalRatings : 0;

            $query3 = "UPDATE Album SET AverageRating = :averageRating WHERE AlbumID = :albumID";
            $stmt3 = $mysqli->prepare($query3);
            $stmt3->bindParam(':averageRating', $averageRating, PDO::PARAM_STR);
            $stmt3->bindParam(':albumID', $albumID, PDO::PARAM_INT);
            $stmt3->execute();
            header("Location: album.php?album_id=$albumID");
            exit();
        } else {
            echo "Error updating rating: " . $stmtInsert->error;
        }
    }
} else {
    header("Location: homepage.php");
    exit();
}
?>