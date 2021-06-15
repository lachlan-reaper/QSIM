<?php

require '../databaseMaintenanceFunctions.php';
session_start();
redirectingUnauthUsers("stockMC");

$function = $_GET["function"];

if ($function == "fileModifyStock") {
    fileModifyStock();
} else if ($function == "fileAddUsers") {
    fileAddUsers();
} else if ($function == "fileRemoveUsers") {
    fileRemoveUsers();
} else if ($function == "fileUpdateUsers") {
    fileUpdateUsers();
} else {
    die("I SPECIFICALLY SAID DON'T TOUCH THE URL! <br><br><i>Gosh.... Kids these days....</i>"); // Easter Egg
}

header("Location: http://" . $_SESSION["websiteLoc"] . "/stock/");

function fileModifyStock () {
    die();
}

function fileAddUsers () {
    die();
}

function fileRemoveUsers () {
    die();
}

function fileUpdateUsers () {
    die();
}

?>