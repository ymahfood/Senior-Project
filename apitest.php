<?php
$lastfmuser = "soupyys";
$artists_overall = file_get_contents("https://ws.audioscrobbler.com/2.0/?method=user.gettopartists&user={$lastfmuser}&period=7day&api_key=0b393a85d0b34580aa099c1623623d83&format=json");
$artists_overall = json_decode($artists_overall);
$overall_data = $artists_overall->topartists->artist;
$overall_data = array_slice($overall_data, 0, 10);
foreach ($overall_data as $artist) {
    $name = $artist->name;
    echo "Name: $name\n";
}


$usernameQuery = "SELECT Username FROM User WHERE UserID = :userID";
        $usernameStmt = $mysqli->prepare($usernameQuery);
        $usernameStmt->bindParam(":userID", $profile_id, PDO::PARAM_INT);
        $usernameStmt->execute();

        $username = $usernameStmt->fetch(PDO::FETCH_ASSOC);
?>