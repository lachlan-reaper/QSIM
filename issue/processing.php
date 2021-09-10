<?php 
require "../databaseFunctions.php";
session_start();
redirectingUnauthUsers("issue");

$mods = [];
$post_keys = array_keys($_POST);
$i = count($post_keys);
$num = 3; // The number of POST vars that are not items of issue
while($i > 0) {
    $i--;
    $key = $post_keys[$i];
    switch ($key) {
        case "action":
            $action = $_POST["action"];
            $num--;
            break;
        case "id":
            $id = $_POST["id"];
            $num--;
            break;
        case "prev":
            $prevPage = urldecode($_POST["prev"]);
            $num--;
            break;
        default:
            $amount = urldecode($_POST[$key]);
            $amount = str_replace("_", " ", $amount);

            $item = urldecode($key);
            $item = str_replace("_", " ", $item);

            $mods[$i-$num] = [$item, $amount]; // '-$num' adjusts for the removal of action, id and prev.
    }
}


$num = count($mods);
$i = 0;

if ($action == "Issue") {
    $stock = retrieveStock();
    
    while($num > $i) { // Iterates through each item to be affected and makes sure the proprosed action is valid
        $item = $stock->fetch_assoc();
        $currentStock = $item["onShelf"];
        
        if ($mods[$i][1] > $currentStock) {
            echo '<script language="javascript">';
            echo 'alert("The whole order has been cancelled as there was not enough of ' . $item["item"] . ' in stock.");';
            echo 'window.location.href="../stock/";';
            echo '</script>';
            die();
        }
        $i++;
    }
    
    issueEquipment($id, $mods);
} else if ($action == "Return") {
    while($num > $i) { // Iterates through each item to be affected and makes sure the proprosed action is valid
        $currentIssued = getUserValues($id, [$mods[$i][0]], "inventory");
        if ($mods[$i][1] > $currentIssued) {
            echo '<script language="javascript">';
            echo 'alert("The whole order has been cancelled as there was not enough of ' . $mods[$i][0] . ' that had been issued to the person of id: ' . $id . '");';
            echo 'window.location.href="../profile/?id=' . $id . '";';
            echo '</script>';
            die();
        }
        $i++;
    }

    returnEquipment($id, $mods);
} else if ($action == "Lost") {
    while($num > $i) { // Iterates through each item to be affected and makes sure the proprosed action is valid
        $currentIssued = getUserValues($id, [$mods[$i][0]], "inventory");
        if ($mods[$i][1] > $currentIssued) {
            echo '<script language="javascript">';
            echo 'alert("The whole order has been cancelled as there was not enough of ' . $mods[$i][0] . ' that had been issued to the person of id: ' . $id . '");';
            echo 'window.location.href="../profile/?id=' . $id . '";';
            echo '</script>';
            die();
        }
        $i++;
    }

    declareLostOrDamaged($id, $mods);
} else if ($action == "Set") { // Iterates through each item to be affected
    setIssue($id, $mods);
} else {
    die("Improper function. DON'T TOUCH THE URL! Kids these days....");
}

header("Location: " . $prevPage);
die();

?>
