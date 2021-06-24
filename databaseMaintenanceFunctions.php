<?php

require '../databaseFunctions.php';

// refreshUserAccess("3718363097");
// refreshAllAccess();
// refreshStockTotal();
// refreshStockOnLoan();
// refreshStockTable();
// completeRefresh();

function refreshUserAccess(string $id) {
    // Given the ID num of a user, it will check the user's access level as defined by their appointment and thusly correct any discrepancies automatically.
    establishConnection();

    $id = formatNullAndStringToSQL($id);
    $sql = "SELECT `platoon`, `appointment` FROM `users` WHERE `id` LIKE $id";
    $result = $_SESSION['conn'] -> query($sql);

    if ($result->num_rows > 1) {
        echo '<script language="javascript">';
        echo 'alert("Duplicate user error")';
        echo '</script>';
        return;
    } 
    if ($result->num_rows == 1) {
        $row = $result->fetch_assoc();
        $platoon = $row["platoon"];
        $appointment = $row["appointment"];
    } else {
        echo '<script language="javascript">';
        echo 'alert("No user by the id of: ' . $id . '")';
        echo '</script>';
        return;
    }
    $access = retrieveAccessLevel($appointment, $platoon);
    
    $id =       formatNullAndStringToSQL($id);
    $access =   formatNullAndStringToSQL($access);

    $sql = "UPDATE `users` SET `access` = $access WHERE `users`.`id` = $id;";
    
    if ($_SESSION['conn']->query($sql) === TRUE) {
        echo "Record updated successfully";
    } else {
        echo "Error: " . $sql . "<br>" . $_SESSION['conn']->error;
    }
}

function commaRemoval($matches) {
    $line = $matches[0];
    $line = str_replace('"', '', $line);
    $line = str_replace(',', '&comm;', $line);
    return $line;
}

function csvLineToArr(string $line) {
    // 1. Replace all [""] with [&dbqt;]
    // 2. Preg-replace [/"(.*),(.*)"/] with [$1&comm;$2]
    // 3. Explode the string with [,]
    // 4. Iterate through the array, replacing each [&dbqt;] and [&comm;]
    
    $line = str_replace('""', '&dbqt;', $line);
    $line = preg_replace_callback('|"[^"]+"|', 'commaRemoval', $line);
    $arr = explode(",", $line);
    $i = count($arr);
    $parameters = array('&comm;', '&dbqt;');
    $replacements = array(',', '"');
    while ($i > 0) {
        $i--;
        $arr[$i] = str_replace($parameters, $replacements, $arr[$i]);
    }
    $arr[count($arr)-1] = trim($arr[count($arr)-1]);
    return $arr;
}

function addUserArr(array $userValues) {
    establishConnection();
    $hasheduserpass = password_hash($userValues["userpass"], PASSWORD_BCRYPT);
    $access = retrieveAccessLevel($userValues["appointment"], strtoupper($userValues["platoon"]));

    $firstName =                formatNullAndStringToSQL($userValues["firstName"]);
    $lastName =                 formatNullAndStringToSQL($userValues["lastName"]);
    $id =                       formatNullAndStringToSQL($userValues["id"]);
    $access =                   formatNullAndStringToSQL($access);
    $username =                 formatNullAndStringToSQL($userValues["username"]);
    $hasheduserpass =           formatNullAndStringToSQL($hasheduserpass);
    $rank =         strtoupper( formatNullAndStringToSQL($userValues["rank"]));
    $appointment =  strtolower( formatNullAndStringToSQL($userValues["appointment"]));
    $company =      strtoupper( formatNullAndStringToSQL($userValues["company"]));
    $platoon =      strtoupper( formatNullAndStringToSQL($userValues["platoon"]));
    $section =                  formatNullAndStringToSQL($userValues["section"]);
    if ($userValues["yearLevel"] === 0) {
        $yearLevel = '0';
    } else {
        $yearLevel = $userValues["yearLevel"];
    }

    // Creates a user record
    $sql = "INSERT INTO `users` (`firstName`, `lastName`, `id`, `access`, `username`, `userpass`, `rank`, `appointment`, `yearLevel`, `company`, `platoon`, `section`) VALUES ($firstName, $lastName, $id, $access, $username, $hasheduserpass, $rank, $appointment, $yearLevel, $company, $platoon, $section)";
    if ($_SESSION['conn']->query($sql) === TRUE) {
        echo "New user record created successfully <br>";
    } else {
        echo "Error: " . $sql . "<br>" . $_SESSION['conn']->error . "<br>";
        return;
    }

    // Creates a respective inventory record, connected via the ID num
    $sql = "INSERT INTO `inventory` (`id`) VALUES ($id);";
    if ($_SESSION['conn']->query($sql) === TRUE) {
        echo "New user inventory created successfully <br>";
    } else {
        echo "Error: " . $sql . "<br>" . $_SESSION['conn']->error . "<br>";

        // In case of the creation of a record inventory fails, it automatically destroys the user's record in order to allow for the reuse of this function after a failure.
        $sql = "DELETE FROM `users` WHERE id = $id;";
        if ($_SESSION['conn']->query($sql) === TRUE) {
            echo "User record successfully removed because of previous error <br>";
        } else {
            echo "Error: " . $sql . "<br>" . $_SESSION['conn']->error . "<br>";
        }
    }
}

function removeUserArr(array $userValues) { // MAKE IT ACCEPT NAMES INSTEAD OF ID
    establishConnection();

    $id = $userValues["id"];
    $id = formatNullAndStringToSQL($id);
    $sqlUser = "DELETE FROM `users` WHERE `id` = $id;";
    $sqlInventory = "DELETE FROM `inventory` WHERE `id` = $id;";
    $sqlHistory = "DELETE FROM `equipmentreceipts` WHERE `id` = $id;";
    $result = $_SESSION['conn'] -> query($sqlUser);
    $result = $_SESSION['conn'] -> query($sqlInventory);
    $result = $_SESSION['conn'] -> query($sqlHistory);
}

function updateUserArr(array $userValues) { // MAKE IT NOT NEED ALL VARIABLES!!!!!!!!!!!!!!!!!!!!!
    establishConnection();
    $access = retrieveAccessLevel($userValues["appointment"], strtoupper($userValues["platoon"]));

    $firstName =                formatNullAndStringToSQL($userValues["firstName"]);
    $lastName =                 formatNullAndStringToSQL($userValues["lastName"]);
    $id =                       formatNullAndStringToSQL($userValues["id"]);
    $access =                   formatNullAndStringToSQL($access);
    $username =                 formatNullAndStringToSQL($userValues["username"]);
    $rank =         strtoupper( formatNullAndStringToSQL($userValues["rank"]));
    $appointment =  strtolower( formatNullAndStringToSQL($userValues["appointment"]));
    $company =      strtoupper( formatNullAndStringToSQL($userValues["company"]));
    $platoon =      strtoupper( formatNullAndStringToSQL($userValues["platoon"]));
    $section =                  formatNullAndStringToSQL($userValues["section"]);
    if ($userValues["yearLevel"] === 0) {
        $yearLevel = '0';
    } else {
        $yearLevel = $userValues["yearLevel"];
    }

    $sql = "UPDATE `users` SET `firstName` = $firstName, `lastName` = $lastName, `access` = $access, `username` = $username, `rank` = $rank, `appointment` = $appointment, `yearLevel` = $yearLevel, `company` = $company, `platoon` = $platoon, `section` = $section WHERE `id` = $id";
    $result = $_SESSION['conn'] -> query($sql);
}

function refreshAllAccess() {
    // Iterates through the entire list of users and checking with their designated appointment and platoon, will redefine their access level as such.
    establishConnection();

    $sql = "SELECT `id`, `platoon`, `appointment` FROM `users`";
    $result = $_SESSION['conn'] -> query($sql);

    $sql = "";
    $i = $result->num_rows;
    while ($i > 0) {
        $row = $result->fetch_assoc();
        $platoon = $row["platoon"];
        $appointment = $row["appointment"];
        $id = $row["id"];

        $access = retrieveAccessLevel($appointment, $platoon);
        $id = formatNullAndStringToSQL($id);
        $access = formatNullAndStringToSQL($access);

        $sql = $sql . "UPDATE `users` SET `access` = $access WHERE `id` = $id;";
        
        $i--;
    }
    if ($_SESSION['conn']->multi_query($sql) === TRUE) {
        echo "Records updated successfully";
    } else {
        echo "Error: " . $sql . "<br>" . $_SESSION['conn']->error;
    }
}

function refreshStockTotal () {
    // Recaculates the total stock from the value of stock on shelf and on loan and redefines the total as such.
    establishConnection();

    $sql = "UPDATE `stock` SET `total` = `onShelf` + `onLoan`;";
    $result = $_SESSION['conn'] -> query($sql);
}

function refreshStockOnLoan () {
    // Sums up the total amount stock on record on loan.
    establishConnection();
    $items = retrieveAllIssuedItemsOnStock();

    $i = $items->num_rows;
    while($i > 0) {
        $item = $items->fetch_assoc();
        $name = $item['item'];

        $sql = "SELECT SUM(`$name`) AS 'sum' FROM `inventory`";
        $result = $_SESSION['conn'] -> query($sql);
        $sum = $result->fetch_assoc();
        $sum = $sum['sum'];

        $sql = "UPDATE `stock` SET `onLoan` = '$sum' WHERE `item` = '$name';";
        $result = $_SESSION['conn'] -> query($sql);
        
        $i = $i - 1;
    }
}

function refreshStockTable () {
    // Recaculates the total amount on loan and then the total amount in the unit.
    refreshStockOnLoan();
    refreshStockTotal();
}

function completeRefresh () {
    // Recalculates all possible variables.
    refreshStockTable();
    refreshAllAccess();
}
?>
