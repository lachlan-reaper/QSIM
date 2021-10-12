<?php
// FOR STRESS TESTING!!!!!
require "../databaseFunctions.php";

establishConnection();

$max = 800;
$i = 0;

while ($i < $max) {
    $id = "$i";
    $unfId = $id;

    $id = formatVarToSQL($id);

    // Remove from the database
    $sql = "DELETE FROM `users` WHERE `id` = $id; DELETE FROM `inventory` WHERE `id` = $id; DELETE FROM `equipmentreceipts` WHERE `id` = $id;";
    $result = $_SESSION['conn'] -> multi_query($sql);
    
    $i++;
}
?>