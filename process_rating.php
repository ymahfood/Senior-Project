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

    $queryCheck = "SELECT Album.NumRatings, Album.AverageRating, Rating.UserID, Rating.AlbumID FROM Album 
    LEFT JOIN Rating ON Album.AlbumID = Rating.AlbumID
    WHERE Album.AlbumID = :albumID AND  Rating.UserID = :userID";
    $stmtCheck = $mysqli->prepare($queryCheck);
    $stmtCheck->bindParam(':albumID', $albumID, PDO::PARAM_INT);
    $stmtCheck->bindParam(':userID', $user_id, PDO::PARAM_INT);
    $stmtCheck->execute();
    $resultCheck = $stmtCheck->fetch(PDO::FETCH_ASSOC);

    if ($resultCheck['NumRatings'] > 0 && $resultCheck['UserID'] == $user_id) {
        // Fetch the existing rating for the user
        $queryGetExistingRating = "SELECT Rating FROM Rating WHERE AlbumID = :albumID AND UserID = :userID";
        $stmtGetExistingRating = $mysqli->prepare($queryGetExistingRating);
        $stmtGetExistingRating->bindParam(':albumID', $albumID, PDO::PARAM_INT);
        $stmtGetExistingRating->bindParam(':userID', $user_id, PDO::PARAM_INT);
        $stmtGetExistingRating->execute();
        $existingRating = $stmtGetExistingRating->fetch(PDO::FETCH_ASSOC);

        $queryUserRatingUpdate = "UPDATE Rating SET Rating = :rating, Review = :review WHERE AlbumID = :albumID AND UserID = :userID";
        $stmtUserRatingUpdate = $mysqli->prepare($queryUserRatingUpdate);
        $stmtUserRatingUpdate->bindParam(':rating', $rating, PDO::PARAM_STR);
        $stmtUserRatingUpdate->bindParam(':albumID', $albumID, PDO::PARAM_INT);
        $stmtUserRatingUpdate->bindParam(':userID', $user_id, PDO::PARAM_INT);
        $stmtUserRatingUpdate->bindParam(':review', $review, PDO::PARAM_STR);

        if ($stmtUserRatingUpdate->execute()) {
            // Calculate the updated average rating
            $avg = $resultCheck['AverageRating'];
            $queryUpdateAverageRating = "UPDATE Album SET AverageRating = (AverageRating * :totalRatings - :existingRating + :rating) / :totalRatings WHERE AlbumID = :albumID";
            $stmtUpdateAverageRating = $mysqli->prepare($queryUpdateAverageRating);
            $stmtUpdateAverageRating->bindParam(':rating', $rating, PDO::PARAM_STR);
            $stmtUpdateAverageRating->bindParam(':existingRating', $existingRating['Rating'], PDO::PARAM_STR);
            $stmtUpdateAverageRating->bindParam(':totalRatings', $resultCheck['NumRatings'], PDO::PARAM_INT);
            $stmtUpdateAverageRating->bindParam(':albumID', $albumID, PDO::PARAM_INT);
            $stmtUpdateAverageRating->execute();

            header("Location: album.php?album_id=$albumID");
            exit();
        } else {
            echo "Error updating rating: " . $stmtUserRatingUpdate->error;
        }
    } else {
        $queryInsert = "INSERT INTO Rating (Rating, AlbumID, UserID, Review) VALUES (:rating, :albumID, :userID, :review)";
        $stmtInsert = $mysqli->prepare($queryInsert);
        $stmtInsert->bindParam(':rating', $rating, PDO::PARAM_STR);
        $stmtInsert->bindParam(':albumID', $albumID, PDO::PARAM_INT);
        $stmtInsert->bindParam(':userID', $user_id, PDO::PARAM_INT);
        $stmtInsert->bindParam(':review', $review, PDO::PARAM_STR);

        if ($stmtInsert->execute()) {
            $query2 = "SELECT NumRatings, AverageRating FROM Album WHERE AlbumID = :albumID";
            $stmt2 = $mysqli->prepare($query2);
            $stmt2->bindParam(':albumID', $albumID, PDO::PARAM_INT);
            $stmt2->execute();
            $ratings = $stmt2->fetch(PDO::FETCH_ASSOC);

            $totalRatings = $ratings['NumRatings'] + 1;

            // Fetch the most recent rating
            $queryRecentRating = "SELECT Rating FROM Rating WHERE AlbumID = :albumID ORDER BY RatingID DESC LIMIT 1";
            $stmtRecentRating = $mysqli->prepare($queryRecentRating);
            $stmtRecentRating->bindParam(':albumID', $albumID, PDO::PARAM_INT);
            $stmtRecentRating->execute();
            $mostRecentRating = $stmtRecentRating->fetch(PDO::FETCH_ASSOC)['Rating'];

            // Calculate the updated average rating by incorporating the most recent rating
            $aggregateScore = ($ratings['NumRatings'] * $ratings['AverageRating']) + $mostRecentRating;
            $averageRating = $aggregateScore / $totalRatings;

            $query3 = "UPDATE Album SET NumRatings = :numRatings, AverageRating = :averageRating WHERE AlbumID = :albumID";
            $stmt3 = $mysqli->prepare($query3);
            $stmt3->bindParam(':numRatings', $totalRatings, PDO::PARAM_INT);
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