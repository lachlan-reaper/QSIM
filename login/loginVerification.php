<?php
// require === import AND execute
// require vs include (require produces fatal error, include produces warning error)
require '../databaseFunctions.php';
session_start();
// Since the script userListFunctions.php requires functions.php itself, it creates an error when you re-require functions.php


establishConnection();

$usernameinput = $_POST['user'];
$userpassinput = $_POST['pass'];

$sql = "SELECT `userpass`, `id`, `access` FROM `users` WHERE `username` = '$usernameinput'";
$result = $_SESSION['conn'] -> query($sql);

if ($result->num_rows > 1) {
    echo '<script language="javascript">';
    echo 'alert("Duplicate user error")';
    echo '</script>';

    header("Location: http://" . $_SESSION["websiteLoc"] . "/login/");
    die(); // This stops the code and ensures that the user is taken to the proper destination without moving on with the script
} 
if ($result->num_rows == 1) {
    $row = $result->fetch_assoc();
    if (password_verify($userpassinput, $row["userpass"])) {
        $_SESSION["currentUserId"] = $row["id"];
        $_SESSION["currentUserAccess"] = $row["access"];
        establishSessionVars();
        header("Location: http://" . $_SESSION["websiteLoc"] . "/home/");
        die();
    }
}

// If the code reaches this point then it means that the user has not been redirected as 
// they have not provided the correct username or password
// The userId of 0 will be reserved for notifying the system of invalid input with NULL for not yet attempted
$_SESSION['currentUserId'] = 0; 
$_SESSION["currentUserAccess"] = "recruit";
header("Location: http://" . $_SESSION["websiteLoc"] . "/login/");

?>