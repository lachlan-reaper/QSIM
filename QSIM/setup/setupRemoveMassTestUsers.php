<?php
// FOR STRESS TESTING!!!!!
require "../databaseFunctions.php";

establishConnection();

$max = 800;
$i = 0;

while ($i < $max) {
    $id = "$i";
    $unfId = $id;

    $id = formatNullAndStringToSQL($id);

    // Remove from the database
    $sqlUser = "DELETE FROM `users` WHERE `id` = $id;";
    $sqlInventory = "DELETE FROM `inventory` WHERE `id` = $id;";
    $sqlHistory = "DELETE FROM `equipmentreceipts` WHERE `id` = $id;";
    $result = $_SESSION['conn'] -> query($sqlUser);
    $result = $_SESSION['conn'] -> query($sqlInventory);
    $result = $_SESSION['conn'] -> query($sqlHistory);

    // Delete profile picture
    unlink("../photo/$unfId.jpg");
    
    $i++;
}
?>