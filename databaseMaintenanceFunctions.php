<?php

require 'databaseFunctions.php';

// Auto-update database
function refreshAllAccess () {
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

        $access = getAccessLevel($appointment, $platoon);
        $id = formatVarToSQL($id);
        $access = formatVarToSQL($access);

        $sql = $sql . "UPDATE `users` SET `access` = $access WHERE `id` = $id;";
        
        $i--;
    }

    if ($_SESSION['conn']->multi_query($sql) === TRUE) { // If the query is successful
        echo "Records updated successfully";
    } else { // Else, display the error
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

        // SUM() is a MySQL inbuilt function that performs the function in a more efficient manner
        $sql = "SELECT SUM(`$name`) AS 'sum' FROM `inventory` WHERE NOT (`id` = 'customIssue' OR `id` = 'AFXIssue' OR `id` = 'RECIssue' OR `id` = 'stdIssue')";
        $result = $_SESSION['conn'] -> query($sql);

        $sum = $result->fetch_assoc();
        $sum = $sum['sum'];

        $sql = "UPDATE `stock` SET `onLoan` = '$sum' WHERE `item` = '$name';";
        $result = $_SESSION['conn'] -> query($sql);
        
        $i--;
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

// Modifying users
function addUserArr (array $userValues) {
    // Adds a user based off of a customly keyed array
    establishConnection();
    $hasheduserpass = password_hash($userValues["userpass"], PASSWORD_BCRYPT);
    $access = getAccessLevel($userValues["appointment"], strtoupper($userValues["platoon"]));

    $firstName =                formatVarToSQL($userValues["firstName"]);
    $lastName =                 formatVarToSQL($userValues["lastName"]);
    $id =                       formatVarToSQL($userValues["id"]);
    $access =                   formatVarToSQL($access);
    $username =                 formatVarToSQL($userValues["username"]);
    $hasheduserpass =           formatVarToSQL($hasheduserpass);
    $rank =         strtoupper( formatVarToSQL($userValues["rank"]));
    $appointment =  strtoupper( formatVarToSQL($userValues["appointment"]));
    $company =      strtoupper( formatVarToSQL($userValues["company"]));
    $platoon =      strtoupper( formatVarToSQL($userValues["platoon"]));
    $section =                  formatVarToSQL($userValues["section"]);
    
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

function removeUserArr (array $userValues) {
    // Removes a user by a customly keyed array
    establishConnection();

    $id = $userValues["id"];
    $id = formatVarToSQL($id);
    $sqlUser = "DELETE FROM `users` WHERE `id` = $id;";
    $sqlInventory = "DELETE FROM `inventory` WHERE `id` = $id;";
    $sqlHistory = "DELETE FROM `equipmentreceipts` WHERE `id` = $id;";
    $result = $_SESSION['conn'] -> query($sqlUser);
    $result = $_SESSION['conn'] -> query($sqlInventory);
    $result = $_SESSION['conn'] -> query($sqlHistory);
}

function updateUserArr (array $userValues) {
    // Updates a user based on a customly keyed array
    establishConnection();
    $variables = "";
    
    // If each value is set, then it can be added to the set of variables to modify
    if (isset($userValues["firstName"])) {
        $firstName = formatVarToSQL($userValues["firstName"]);
        $variables = $variables . ", `firstName` = $firstName";
    }
    if (isset($userValues["lastName"])) {
        $lastName = formatVarToSQL($userValues["lastName"]);
        $variables = $variables . ", `lastName` = $lastName";
    }
    if (isset($userValues["id"])) {
        $id = formatVarToSQL($userValues["id"]);
        $variables = $variables . ", `id` = $id";
    }
    if (isset($userValues["appointment"]) and isset($userValues["platoon"])) {
        $access = getAccessLevel($userValues["appointment"], strtoupper($userValues["platoon"]));
        $access = formatVarToSQL($access);
        $variables = $variables . ", `access` = $access";
    }
    if (isset($userValues["username"])) {
        $username =                 formatVarToSQL($userValues["username"]);
        $variables = $variables . ", `username` = $username";
    }
    if (isset($userValues["rank"])) {
        $rank = strtoupper( formatVarToSQL($userValues["rank"]));
        $variables = $variables . ", `rank` = $rank";
    }
    if (isset($userValues["appointment"])) {
        $appointment = strtoupper( formatVarToSQL($userValues["appointment"]));
        $variables = $variables . ", `appointment` = $appointment";
    }
    if (isset($userValues["company"])) {
        $company = strtoupper( formatVarToSQL($userValues["company"]));
        $variables = $variables . ", `company` = $company";
    }
    if (isset($userValues["platoon"])) {
        $platoon = strtoupper( formatVarToSQL($userValues["platoon"]));
        $variables = $variables . ", `platoon` = $platoon";
    }
    if (isset($userValues["section"])) {
        $section = formatVarToSQL($userValues["section"]);
        $variables = $variables . ", `section` = $section";
    }
    
    if (isset($userValues["yearLevel"])) {
        if ($userValues["yearLevel"] === 0) {
            $yearLevel = '0';
        } else {
            $yearLevel = $userValues["yearLevel"];
        }
        $variables = $variables . ", `yearLevel` = $yearLevel";
    }

    // Remove unnecessary commas at the front
    $variables = substr($variables, 2);

    $sql = "UPDATE `users` SET $variables WHERE `id` = $id";
    $result = $_SESSION['conn'] -> query($sql);
}

function refreshUserAccess (string $id) {
    // Given the ID num of a user, it will check the user's access level as defined by their appointment and thusly correct any discrepancies automatically.
    establishConnection();

    // Retrieve User information
    $result = getUserValues($id, ["platoon", "appointment"], "users");

    $platoon = $result["platoon"];
    $appointment = $result["appointment"];

    $access = getAccessLevel($appointment, $platoon);
    $id =       formatVarToSQL($id);
    $access =   formatVarToSQL($access);

    // Update User information
    $sql = "UPDATE `users` SET `access` = $access WHERE `users`.`id` = $id;";
    if ($_SESSION['conn']->query($sql) === TRUE) {
        echo "Record updated successfully";
    } else {
        echo "Error: " . $sql . "<br>" . $_SESSION['conn']->error;
    }
}

?>
