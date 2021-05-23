<?php

session_start();
$websiteLoc = $_SESSION["websiteLoc"];
session_unset();
header("Location: http://" . $websiteLoc . "/login/");
die();

?>