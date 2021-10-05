<?php
require '../databaseFunctions.php';
session_start();

establishConnection();

$usernameinput = $_POST['user'];
$userpassinput = $_POST['pass'];

$userID = userIdentification($usernameinput, $userpassinput);

if (!($userID === NULL)) {
    $arr = getUserValues($userID, ["access"], "users");
    $access = $arr["access"];

    $_SESSION["currentUserAccess"] = $access;
    $_SESSION["currentUserId"] = $userID;

    establishSessionVars();
    header("Location: http://" . $_SESSION["websiteLoc"] . "/home/");
} else {
    $_SESSION['currentUserId'] = "Invalid"; 
    $_SESSION["currentUserAccess"] = "recruit"; // Set to the most restrictive access just in case someone finds a work around

    header("Location: http://" . $_SESSION["websiteLoc"] . "/login/");
}

?>