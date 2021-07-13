<?php 
require "../databaseFunctions.php";
session_start();
redirectingUnauthUsers("issue");

$action = $_GET["action"];
$id = $_GET["id"];
$mods = $_GET["mods"];
$prevPage = $_GET["prev"];

// Reformat the URL safe variables into their intended meaning
$mods = str_replace("-", " ", $mods);
$mods = explode("|", $mods);
$i = count($mods);

if ($action == "Issue") {
    while($i > 0) { // Iterates through each item to be affected
        $i--;
        $mods[$i] = explode("_", $mods[$i]);
    }
    issueEquipment($id, $mods);
} else if ($action == "Return") {
    while($i > 0) { // Iterates through each item to be affected and makes sure the proprosed action is valid
        $i--;
        $mods[$i] = explode("_", $mods[$i]);
        $currentIssued = getUserValue($id, $mods[$i][0], "inventory");
        if ($mods[$i][1] > $currentIssued) {
            echo '<script language="javascript">';
            echo 'alert("Not enough of ' . $mods[$i][0] . ' has been has been issued to the person of id: ' . $id . '");';
            echo 'window.location.href="../profile/?id=' . $id . '";';
            echo '</script>';
            die();
        }
    }
    returnEquipment($id, $mods);
} else if ($action == "Lost") {
    while($i > 0) { // Iterates through each item to be affected and makes sure the proprosed action is valid
        $i--;
        $mods[$i] = explode("_", $mods[$i]);
        $currentIssued = getUserValue($id, $mods[$i][0], "inventory");
        if ($mods[$i][1] > $currentIssued) {
            echo '<script language="javascript">';
            echo 'alert("Not enough of ' . $mods[$i][0] . ' has been has been issued to the person of id: ' . $id . '");';
            echo 'window.location.href="../profile/?id=' . $id . '";';
            echo '</script>';
            die();
        }
    }
    declareLostOrDamaged($id, $mods);
} else if ($action == "Set") { // Iterates through each item to be affected
    while($i > 0) {
        $i--;
        $mods[$i] = explode("_", $mods[$i]);
        $currentIssued = getUserValue($id, $mods[$i][0], "inventory");
    }
    setIssue($id, $mods);
} else {
    die("Improper function. DON'T TOUCH THE URL!");
}

header("Location: " . $prevPage);
die();

?>
