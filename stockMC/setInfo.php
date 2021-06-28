<?php
require '../functions.php';
establishConnection();
$function = $_GET["func"];

if ($function == "appointments") {
    $file = $_GET["file"];
    $file = str_replace("_" , " ", $file);
    $file = str_replace("|" , "|\n", $file);
    $mfile = fopen("../appointmentAccessRoles.aars", "w");
    fwrite($mfile, $file);
    fclose($mfile);
} else if ($function == "contacts") {
    $file = $_GET["contacts"];
    $file = str_replace("_" , " ", $file);
    $file = str_replace("|" , "|\n", $file);
    $mfile = fopen("../contacts.txt", "w");
    fwrite($mfile, $file);
    fclose($mfile);
}

header("Location: http://" . $_SESSION["websiteLoc"] . "/stockMC/");
?>