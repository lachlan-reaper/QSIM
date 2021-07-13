<?php
// FOR STRESS TESTING!!!!!
require "../databaseFunctions.php";

establishConnection();

$max = 800;
$i = 0;
while ($i < $max) {
    //      First Name      Last Name           ID Num          Username        Password    Rank    Appointment     COY     PL      SECT    Year Level
    addUser("Test$i",       "Test$i",           "$i",           "cadet$i",      "stupid",   "REC",  "recruit",      "A",    "1",    "1",    8);
    $i++;
}
?>