<?php
require '../functions.php';
establishConnection();
$function = $_GET["func"];

if ($function == "appointments") {
    $file = $_GET["file"];

    // Formats the URL safe file string and formats it into the desired result
    $file = str_replace("_" , " ", $file);
    $file = str_replace("|" , "|\n", $file);

    $mfile = fopen("../appointmentAccessRoles.aars", "w");
    fwrite($mfile, $file);
    fclose($mfile);
} else if ($function == "contacts") {
    $file = $_GET["contacts"];

    // Formats the URL safe file string and formats it into the desired result
    $file = str_replace("_" , " ", $file);
    $file = str_replace("|" , "|\n", $file);

    $mfile = fopen("../contacts.txt", "w");
    fwrite($mfile, $file);
    fclose($mfile);
}

header("Location: http://" . $_SESSION["websiteLoc"] . "/stockMC/");
?>