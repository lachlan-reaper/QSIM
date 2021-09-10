<?php

require '../databaseFunctions.php';
// NEW


// OLD
function refreshUserAccess(string $id) {
    // Given the ID num of a user, it will check the user's access level as defined by their appointment and thusly correct any discrepancies automatically.
    establishConnection();

    // Retrieve User information
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

    // Update User information
    $sql = "UPDATE `users` SET `access` = $access WHERE `users`.`id` = $id;";
    if ($_SESSION['conn']->query($sql) === TRUE) {
        echo "Record updated successfully";
    } else {
        echo "Error: " . $sql . "<br>" . $_SESSION['conn']->error;
    }
}

function addUserArr(array $userValues) {
    // Adds a user based off of a customly keyed array
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

function removeUserArr(array $userValues) {
    // Removes a user by a customly keyed array
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

function updateUserArr(array $userValues) {
    // Updates a user based on a customly keyed array
    establishConnection();
    $variables = "";
    
    // If each value is set, then it can be added to the set of variables to modify
    if (isset($userValues["firstName"])) {
        $firstName = formatNullAndStringToSQL($userValues["firstName"]);
        $variables = $variables . ", `firstName` = $firstName";
    }
    if (isset($userValues["lastName"])) {
        $lastName = formatNullAndStringToSQL($userValues["lastName"]);
        $variables = $variables . ", `lastName` = $lastName";
    }
    if (isset($userValues["id"])) {
        $id = formatNullAndStringToSQL($userValues["id"]);
        $variables = $variables . ", `id` = $id";
    }
    if (isset($userValues["appointment"]) and isset($userValues["platoon"])) {
        $access = retrieveAccessLevel($userValues["appointment"], strtoupper($userValues["platoon"]));
        $access = formatNullAndStringToSQL($access);
        $variables = $variables . ", `access` = $access";
    }
    if (isset($userValues["username"])) {
        $username =                 formatNullAndStringToSQL($userValues["username"]);
        $variables = $variables . ", `username` = $username";
    }
    if (isset($userValues["rank"])) {
        $rank = strtoupper( formatNullAndStringToSQL($userValues["rank"]));
        $variables = $variables . ", `rank` = $rank";
    }
    if (isset($userValues["appointment"])) {
        $appointment = strtolower( formatNullAndStringToSQL($userValues["appointment"]));
        $variables = $variables . ", `appointment` = $appointment";
    }
    if (isset($userValues["company"])) {
        $company = strtoupper( formatNullAndStringToSQL($userValues["company"]));
        $variables = $variables . ", `company` = $company";
    }
    if (isset($userValues["platoon"])) {
        $platoon = strtoupper( formatNullAndStringToSQL($userValues["platoon"]));
        $variables = $variables . ", `platoon` = $platoon";
    }
    if (isset($userValues["section"])) {
        $section = formatNullAndStringToSQL($userValues["section"]);
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
?>
