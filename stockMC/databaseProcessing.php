<?php

require '../databaseMaintenanceFunctions.php';
session_start();
redirectingUnauthUsers("stockMC");

if (isset($_POST["function"])) {
    $function = $_POST["function"];
} else if (isset($_GET["function"])) {
    $function = $_GET["function"];
} else {
    echo '<script language="javascript">';
    echo 'alert("Input \'function\' error")';
    echo 'window.location.href = "http://' . $_SESSION["websiteLoc"] . '/stockMC/";';
    echo '</script>';
}

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
    echo '<script language="javascript">';
    echo 'alert("Input \'function\' error");';
    echo 'window.location.href = "http://' . $_SESSION["websiteLoc"] . '/stockMC/";';
    echo '</script>';
}

header("Location: http://" . $_SESSION["websiteLoc"] . "/stockMC/");
die();


function manualModifyStock () {
    // Allows for the changing of stock, adding or writing off stock
    establishConnection();

    // Collects all of the post vars into an array
    $listOfMods = [];
    $post_keys = array_keys($_POST);
    $i = count($post_keys);
    $num = 2; // The number of POST vars that are not items of issue
    while($i > 0) {
        $i--;
        $key = $post_keys[$i];
        switch ($key) {
            case "function":
                $num--;
                break;
            case "action":
                $num--;
                break;
            default:
                $amount = urldecode($_POST[$key]);
                $amount = str_replace("_", " ", $amount);

                $item = urldecode($key);
                $item = str_replace("_", " ", $item);

                $listOfMods[$i-$num] = [$item, $amount]; // '-$num' adjusts for the removal of action, id and prev.
        }
    }

    $sql = "";

    $time = date_format(date_create(), "Y/m/d H:i:s");
    $time = formatVarToSQL($time);

    $action = $_POST["action"];

    if ($action == "Add") {
        // Iterates through all of the new stock and formats a SQL query to update the table
        $i = count($listOfMods);
        while($i > 0) {
            $i--;
            $item = formatVarToSQL($listOfMods[$i][0]);
            $value = formatVarToSQL($listOfMods[$i][1]);
            $sql = $sql . "UPDATE `stock` SET `total` = `total` + " . $value . ", `onShelf` = `onShelf` + " . $value . " WHERE `item` = " . $item . ";";
        }
    } else if ($action == "Remove") {
        $error = FALSE;

        $results = retrieveStock();
        $x = $results->num_rows;
        
        // Iterates through all of the old stock and formats a SQL query to update the table
        $max = count($listOfMods);
        $i = 0;
        while($i < $max) {
            $item = $listOfMods[$i][0];
            $value = $listOfMods[$i][1];
            
            // Iterates through the stock until it finds the correct row for the item so you can find its amount on shelf later
            $row = $results->fetch_assoc();
            while($item != $row["item"]) {
                $x--;
                $row = $results->fetch_assoc();
                if ($x == 0) {
                    die("Error in handling of stock names.");
                }
            }
            
            // Checks if there is enough of an item to take out of stock
            if ($row["onShelf"] < $value) {
                echo "<script> alert('There is not enough $item on shelf. Please make sure you have provide the correct inputs.');</script>";
                $error = TRUE;
            } else {
                $item = formatVarToSQL($item);
                $value = formatVarToSQL($value);
                $sql = $sql . "UPDATE `stock` SET `total` = `total` - " . $value . ", `onShelf` = `onShelf` - " . $value . " WHERE `item` = " . $item . ";";
            }
            $i++;
        }
    } else {
        die("Improper function. DON'T TOUCH THE URL!");
    }

    $result = $_SESSION['conn'] -> multi_query($sql);

    if ($error) { // If there is an error, then the program needs to die without redirecting to display JS alerts and needs JS to then redirect to a relevant page after
        echo "<script> window.location.href = \"http://" . $_SESSION["websiteLoc"] . "/stock/\"</script>";
        die();
    }
}

function manualAddItems () {
    // Adds an item to stock to be issued to users
    establishConnection();

    $unfItem = $_POST["name"];
    $item = formatVarToSQL($unfItem);
    $value = $_POST["num"];

    $sqlStock = "INSERT INTO `stock` (`item`, `total`, `onShelf`) VALUES ($item, $value, $value);";
    $sqlInventory = "ALTER TABLE `inventory` ADD `$unfItem` int(6) NOT NULL DEFAULT 0;";

    $result = $_SESSION['conn'] -> query($sqlStock);
    $result = $_SESSION['conn'] -> query($sqlInventory);
}

function manualRemoveItems () {
    // Removes an item from stock
    establishConnection();

    // Collects all of the post vars into an array
    $listOfMods = [];
    $post_keys = array_keys($_POST);
    $i = count($post_keys);
    $num = 1; // The number of POST vars that are not items of issue
    while($i > 0) {
        $i--;
        $key = $post_keys[$i];
        switch ($key) {
            case "function":
                $num--;
                break;
            default:
                $amount = urldecode($_POST[$key]);
                $amount = str_replace("_", " ", $amount);

                $item = urldecode($key);
                $item = str_replace("_", " ", $item);

                $listOfMods[$i-$num] = [$item, $amount]; // '-$num' adjusts for the removal of action, id and prev.
        }
    }

    $sqlStock = "";
    $sqlInventory = "";

    // Iterates through each of the old items of stock and formats a SQL query to modify the tables
    $i = count($listOfMods);
    while($i > 0) {
        $i--;
        $unfItem = $listOfMods[$i][0];
        $item = formatVarToSQL($listOfMods[$i][0]);
        $value = $listOfMods[$i][1];
        if ($value) {
            $sqlStock = $sqlStock . "DELETE FROM `stock` WHERE `item` = $item;";
            $sqlInventory = $sqlInventory . "ALTER TABLE `inventory` DROP `$unfItem`;";
        } else {
            die("I SPECIFICALLY SAID DON'T TOUCH THE URL! <br><br><i>Gosh.... Kids these days....</i>"); // !!!! Easter Egg !!!!
        }
    }

    $result = $_SESSION['conn'] -> multi_query($sqlStock);
    $result = $_SESSION['conn'] -> multi_query($sqlInventory);
}

function resetLostOrDamaged () {
    // Resets the Lost/Damaged numbers on the stock table
    establishConnection();

    $sql = "UPDATE `stock` SET `lostOrDamaged` = 0;";
    $result = $_SESSION['conn'] -> query($sql);
}

function refreshStockTotals () {
    // Refreshes the totals on the Stock Table
    refreshStockOnLoan();
    refreshStockTotal();
}

function manualAddUser () {
    // Adds a user from the HTML form using the method POST
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

    // Adds the profile picture
    $picture = $_FILES["picture"]["tmp_name"];
    move_uploaded_file($picture, "../photo/$id.jpg");

    return $id;
}

function manualRemoveUser () {
    // Removes a user from the HTML form using the method POST
    establishConnection();

    // Removes the profile picture
    $id = $_GET["id"];
    $filename = "../photo/$id.jpg";
    if (file_exists($filename)) {
        unlink($filename);
    }

    $id = formatVarToSQL($id);
    $sqlUser = "DELETE FROM `users` WHERE `id` = $id;";
    $sqlInventory = "DELETE FROM `inventory` WHERE `id` = $id;";
    $sqlHistory = "DELETE FROM `equipmentreceipts` WHERE `id` = $id;";
    $result = $_SESSION['conn'] -> query($sqlUser);
    $result = $_SESSION['conn'] -> query($sqlInventory);
    $result = $_SESSION['conn'] -> query($sqlHistory);

    // Ensures stock table numbers are accurate
    refreshStockTable();
}

function manualModifyUser () {
    // Updates a user from the HTML form using the method POST
    establishConnection();

    $oldId = $_GET["id"];
    $newId = $_POST["id"];

    // Updates the profile picture to the new ID or updates the profile picture or both
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

    $oldId = formatVarToSQL($oldId);
    $newId = formatVarToSQL($newId);
    $firstName = formatVarToSQL($_POST["firstName"]);
    $lastName = formatVarToSQL($_POST["lastName"]);
    $username = formatVarToSQL($_POST["username"]);
    $rank = strtoupper(formatVarToSQL($_POST["rank"]));
    $appointment = strtoupper(formatVarToSQL($_POST["appointment"]));
    $company = strtoupper(formatVarToSQL($_POST["company"]));
    $platoon = strtoupper(formatVarToSQL($_POST["platoon"]));
    $section = formatVarToSQL($_POST["section"]);
    $yearLevel = formatVarToSQL($_POST["yearLevel"]);
    
    // Creates a SQL query to update the user information
    $sqlUser = "UPDATE `users` SET `firstName` = $firstName, `lastName` = $lastName, `username` = $username, `rank` = $rank, `appointment` = $appointment, `company` = $company, `platoon` = $platoon, `section` = $section, `yearLevel` = $yearLevel, `id` = $newId WHERE `id` = $oldId;";
    $result = $_SESSION['conn'] -> query($sqlUser);

    // Updates the other tables to the new ID if the user changed their ID
    if (! ($newId == $oldId)) {
        $sqlInventory = "UPDATE `inventory` SET `id` = $newId WHERE `id` = $oldId;";
        $sqlHistory = "UPDATE `equipmentreceipts` SET `id` = $newId WHERE `id` = $oldId; 
        UPDATE `equipmentreceipts` SET `serverId` = $newId WHERE `serverId` = $oldId;";

        $result = $_SESSION['conn'] -> query($sqlInventory);
        $result = $_SESSION['conn'] -> multi_query($sqlHistory);
    }

    refreshUserAccess($newId);
    establishSessionVars(); // If the user edits themselves then the server may need to update the variables.
    return $_POST["id"];
}

function refreshAccessLevels () {
    // Refreshes all user's access levels
    refreshAllAccess();
}

function graduateAllCadets () {
    // Increments all user's year levels by 1 as long as they are not an officer, i.e. yearLevel=0
    establishConnection();

    // Increments the year levels
    $sql = "UPDATE `users` SET `yearLevel` = `yearLevel` + 1 WHERE `yearLevel` > 0;";
    $result = $_SESSION['conn'] -> query($sql);

    // Selects everyone who should no longer be in school or thus the unit
    $sql = "SELECT `id`, `firstName`, `lastName` FROM `users` WHERE `yearLevel` > 12;";
    $result = $_SESSION['conn'] -> query($sql);

    $i = $result->num_rows;
    if ($i > 0) { // Displays a notification message about those who should no longer be in the unit
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