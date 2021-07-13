<?php

session_start();
$websiteLoc = $_SESSION["websiteLoc"];
session_unset(); // Resets all seesion variable so it becomes like the user never logged in
header("Location: http://" . $websiteLoc . "/login/");
die();

?>