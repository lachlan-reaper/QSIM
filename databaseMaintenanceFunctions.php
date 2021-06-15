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
