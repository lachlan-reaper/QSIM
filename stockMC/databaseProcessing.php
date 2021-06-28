<?php

require '../databaseMaintenanceFunctions.php';
session_start();
redirectingUnauthUsers("stockMC");

// !!!!!!!!!!!!!!!!!!!!!!!!!!!!! GIVE FEEDBACK !!!!!!!!!!!!!!!!!!!!!!!!!!!
$function = $_GET["function"];

if ($function == "manualModifyStock") {
    manualModifyStock();
} else if ($function == "manualAddItems") {
    manualAddItems();
} else if ($function == "manualRemoveItems") {
    manualRemoveItems();
} else if ($function == "resetLostOrDamaged") {
    resetLostOrDamaged();
} else if ($function == "refreshStockTotals") {
    refreshStockTotals();
} else if ($function == "manualAddUser") {
    $id = manualAddUser();
    header("Location: http://" . $_SESSION["websiteLoc"] . "/profile/?id=$id");
    die();
} else if ($function == "manualRemoveUser") {
    manualRemoveUser();
} else if ($function == "manualModifyUser") {
    $newId = manualModifyUser();
    header("Location: http://" . $_SESSION["websiteLoc"] . "/profile/?id=$newId");
    die();
} else if ($function == "refreshAccessLevels") {
    refreshAccessLevels();
} else if ($function == "graduateAllCadets") {
    graduateAllCadets();
} else {
    die("I SPECIFICALLY SAID DON'T TOUCH THE URL! <br><br><i>Gosh.... Kids these days....</i>"); // Easter Egg
}

header("Location: http://" . $_SESSION["websiteLoc"] . "/stockMC/");
die();


function manualModifyStock () { // NEED TO MAKE SURE IT CAN'T GO NEG!!!
    establishConnection();

    $sql = "";

    $time = date_format(date_create(), "Y/m/d H:i:s"); // REMOVE IF NOT NECESSARY IN FUTURE!!!!!!!!
    $time = formatNullAndStringToSQL($time);

    $action = $_GET["action"];
    $listOfMods = $_GET["mods"];

    $listOfMods = str_replace("-", " ", $listOfMods);
    $listOfMods = explode("|", $listOfMods);

    if ($action == "Add") {
        $i = count($listOfMods);
        while($i > 0) {
            $i--;
            $listOfMods[$i] = explode("_", $listOfMods[$i]);
            $item = formatNullAndStringToSQL($listOfMods[$i][0]);
            $value = formatNullAndStringToSQL($listOfMods[$i][1]);
            $sql = $sql . "UPDATE `stock` SET `total` = `total` + " . $value . ", `onShelf` = `onShelf` + " . $value . " WHERE `item` = " . $item . ";";
        }
    } else if ($action == "Remove") {
        $i = count($listOfMods);
        while($i > 0) {
            $i--;
            $listOfMods[$i] = explode("_", $listOfMods[$i]);
            $item = formatNullAndStringToSQL($listOfMods[$i][0]);
            $value = formatNullAndStringToSQL($listOfMods[$i][1]);
            $sql = $sql . "UPDATE `stock` SET `total` = `total` - " . $value . ", `onShelf` = `onShelf` - " . $value . " WHERE `item` = " . $item . ";";
        }
    } else {
        die("Improper function. DON'T TOUCH THE URL!");
    }

    $result = $_SESSION['conn'] -> multi_query($sql);
}

function manualAddItems () {
    establishConnection();

    $sqlStock = "";
    $sqlInventory = "";

    $listOfMods = $_GET["mods"];
    $listOfMods = str_replace("-", " ", $listOfMods);
    $listOfMods = explode("|", $listOfMods);

    $i = count($listOfMods);
    while($i > 0) {
        $i--;
        $listOfMods[$i] = explode("_", $listOfMods[$i]);
        $unfItem = $listOfMods[$i][0];
        $item = formatNullAndStringToSQL($listOfMods[$i][0]);
        $value = $listOfMods[$i][1];
        $sqlStock = $sqlStock . "INSERT INTO `stock` (`item`, `total`, `onShelf`) VALUES ($item, $value, $value);";
        $sqlInventory = $sqlInventory . "ALTER TABLE `inventory` ADD `$unfItem` int(6) NOT NULL DEFAULT 0;";
    }

    $result = $_SESSION['conn'] -> multi_query($sqlStock);
    $result = $_SESSION['conn'] -> multi_query($sqlInventory);
}

function manualRemoveItems () { // DOES NOT REMOVE THE HISTORY OF IT FROM THE RECEIPT HISTORY!!!!!
    establishConnection();

    $sqlStock = "";
    $sqlInventory = "";

    $listOfMods = $_GET["mods"];
    $listOfMods = str_replace("-", " ", $listOfMods);
    $listOfMods = explode("|", $listOfMods);

    $i = count($listOfMods);
    while($i > 0) {
        $i--;
        $listOfMods[$i] = explode("_", $listOfMods[$i]);
        $unfItem = $listOfMods[$i][0];
        $item = formatNullAndStringToSQL($listOfMods[$i][0]);
        $value = $listOfMods[$i][1];
        if ($value) {
            $sqlStock = $sqlStock . "DELETE FROM `stock` WHERE `item` = $item;";
            $sqlInventory = $sqlInventory . "ALTER TABLE `inventory` DROP `$unfItem`;";
        } else {
            die("I SPECIFICALLY SAID DON'T TOUCH THE URL! <br><br><i>Gosh.... Kids these days....</i>"); // !!!!!!!!!!!!!!!!!!!!!!!! Easter Egg !!!!!!!!!!!!!!!!!!!!!!!!!!!!
        }
    }

    $result = $_SESSION['conn'] -> multi_query($sqlStock);
    $result = $_SESSION['conn'] -> multi_query($sqlInventory);
}

function resetLostOrDamaged () {
    establishConnection();

    $sql = "UPDATE `stock` SET `lostOrDamaged` = 0;";
    $result = $_SESSION['conn'] -> query($sql);
}

function refreshStockTotals () {
    refreshStockOnLoan();
    refreshStockTotal();
}

function manualAddUser () { // NEED TO MAKE IT CHECK IF APPOINTMENT IS VALID!!!!
    $firstName = $_POST["firstName"];
    $lastName = $_POST["lastName"];
    $id = $_POST["id"];
    $username = $_POST["username"];
    $userpass = $_POST["userpass"];
    $rank = $_POST["rank"];
    $appointment = $_POST["appointment"];
    $company = $_POST["company"];
    $platoon = $_POST["platoon"];
    $section = $_POST["section"];
    $yearLevel = $_POST["yearLevel"];
    addUser($firstName, $lastName, $id, $username, $userpass, $rank, $appointment, $company, $platoon, $section, $yearLevel);

    $picture = $_FILES["picture"]["tmp_name"];
    move_uploaded_file($picture, "../photo/$id.jpg");

    return $id;
}

function manualRemoveUser () {
    establishConnection();

    $id = $_GET["id"];
    $filename = "../photo/$id.jpg";
    if (file_exists($filename)) {
        unlink($filename);
    }

    $id = formatNullAndStringToSQL($id);
    $sqlUser = "DELETE FROM `users` WHERE `id` = $id;";
    $sqlInventory = "DELETE FROM `inventory` WHERE `id` = $id;";
    $sqlHistory = "DELETE FROM `equipmentreceipts` WHERE `id` = $id;";
    $result = $_SESSION['conn'] -> query($sqlUser);
    $result = $_SESSION['conn'] -> query($sqlInventory);
    $result = $_SESSION['conn'] -> query($sqlHistory);

    
}

function manualModifyUser () { // NEED TO MAKE IT CHECK IF APPOINTMENT IS VALID!!!! AND CHANGE ACCESS LEVEL!!!!!!!!
    establishConnection();

    $oldId = $_GET["id"];
    $newId = $_POST["id"];

    if (is_uploaded_file($_FILES["picture"]["tmp_name"])) {
        $filename = "../photo/$oldId.jpg";
        if (file_exists($filename)) {
            unlink($filename);
        }

        $picture = $_FILES["picture"]["tmp_name"];
        move_uploaded_file($picture, "../photo/$newId.jpg");
    } else if (! ($newId == $oldId)) {
        $filename = "../photo/$oldId.jpg";
        if (file_exists($filename)) {
            rename($filename, "../photo/$newId.jpg");
        }
    }

    $oldId = formatNullAndStringToSQL($oldId);
    $newId = formatNullAndStringToSQL($newId);
    $firstName = formatNullAndStringToSQL($_POST["firstName"]);
    $lastName = formatNullAndStringToSQL($_POST["lastName"]);
    $username = formatNullAndStringToSQL($_POST["username"]);
    $rank = formatNullAndStringToSQL($_POST["rank"]);
    $appointment = formatNullAndStringToSQL($_POST["appointment"]);
    $company = formatNullAndStringToSQL($_POST["company"]);
    $platoon = formatNullAndStringToSQL($_POST["platoon"]);
    $section = formatNullAndStringToSQL($_POST["section"]);
    $yearLevel = formatNullAndStringToSQL($_POST["yearLevel"]);
    
    $sqlUser = "UPDATE `users` SET `firstName` = $firstName, `lastName` = $lastName, `username` = $username, `rank` = $rank, `appointment` = $appointment, `company` = $company, `platoon` = $platoon, `section` = $section, `yearLevel` = $yearLevel, `id` = $newId WHERE `id` = $oldId;";
    $result = $_SESSION['conn'] -> query($sqlUser);

    if (! ($newId == $oldId)) {
        $sqlInventory = "UPDATE `inventory` SET `id` = $newId WHERE `id` = $oldId;";
        $sqlHistory = "UPDATE `equipmentreceipts` SET `id` = $newId WHERE `id` = $oldId; 
        UPDATE `equipmentreceipts` SET `serverId` = $newId WHERE `serverId` = $oldId;";

        $result = $_SESSION['conn'] -> query($sqlInventory);
        $result = $_SESSION['conn'] -> multi_query($sqlHistory);
    }

    establishSessionVars(); // If the user edits themselves then the server may need to update the variables.
    return $_POST["id"];
}

function refreshAccessLevels () {
    refreshAllAccess();
}

function graduateAllCadets () {
    establishConnection();

    $sql = "UPDATE `users` SET `yearLevel` = `yearLevel` + 1 WHERE `yearLevel` > 0;";
    $result = $_SESSION['conn'] -> query($sql);

    $sql = "SELECT `id`, `firstName`, `lastName` FROM `users` WHERE `yearLevel` > 12;";
    $result = $_SESSION['conn'] -> query($sql);

    $i = $result->num_rows;
    if ($i > 0) {
        echo "These cadets have graduated to year 13 or above. Please remove them or modify them to be an officer (year level = 0).<br>";
        while($i > 0) {
            $row = $result->fetch_assoc();
            echo $row["id"] . " - " . $row["firstName"] . ", " . $row["lastName"] . "<br>";
            $i--;
        }
    } else {
        echo "All cadets have successfully graduated to their next year level.<br>";
    }
    echo "<br><a href='../stock/'>Continue to Stock</a>";
    die();
}


?>