<?php

require 'functions.php';

// NEW
function formatVarToSQL ($variable) : string { // Was "formatVarToSQL"
    // Formats the inputted string so it can be directly placed into a SQL query string without extra formatting.
    if ($variable === "" or $variable === "NULL" or $variable === null) {
        return "NULL";
    }
    if (gettype($variable) === "string") {
        return "'" . $variable . "'";
    } else if (substr($variable, 0, 1) == "0") {
        return "'" . $variable . "'";
    }
    return $variable;
}

function getUserValues (string $id, array $uservalues, string $table) : array { // Was "getMultiUserValues"
    // Given user ID and values that are desired to be used, will return the respective values in the form of an array.
    establishConnection();

    $id = formatVarToSQL($id);

    // Formats and adds the values to a SQL query
    $i = count($uservalues);
    $values = "";
    while ($i > 0) {
        $i--;
        $x = $uservalues[$i];
        $values = $values . ", `$x`";
    }
    $values = substr($values, 2);

    $sql = "SELECT $values FROM `$table` WHERE `id` LIKE $id";
    $result = $_SESSION['conn'] -> query($sql);

    if ($result->num_rows > 1) {
        echo '<script language="javascript">';
        echo 'alert("Duplicate user ID error. ID: ' . $id . '")';
        echo '</script>';
        return NULL;
    } 
    if ($result->num_rows == 1) {
        $row = $result->fetch_assoc();
        return $row;
    } else {
        echo '<script language="javascript">';
        echo 'alert("No user by the id of: ' . $id . '")';
        echo '</script>';
        return NULL;
    }
}

// Search Result
function retrieveSearchQueryResults (string $userQuery, array $parameters) {
    // Returns a MySQLi object of the results after parsing and interpreting the user's query and parameters.
    establishConnection();
    
    // If the query is numeric, then it is an ID num.
    if (is_numeric($userQuery)) {
        $userQuery = formatVarToSQL($userQuery);
        $sql = "SELECT `firstName`, `lastName`, `platoon`, `rank`, `appointment`, `id` FROM `users` WHERE `id` LIKE $userQuery";
        $result = $_SESSION['conn'] -> query($sql);
        return $result;
    } else {
        // 'preg_replace' is used to ensure there is no SQL injections or hacking. This is done by removing all unnesscesssary symbols that could possibly be used.
        $userQuery = preg_replace('/[_+!?=<>≤≥@#$%^&*(){}|~\/]/', '', $userQuery);
        $userQuery = str_replace('-', ' ', $userQuery);
        $userQuery = str_replace('.', ' ', $userQuery);
        $userQuery = trim($userQuery);
        $userQueryArr = explode(" ", $userQuery);

        // This ginormous collection of statements is used to try all possible permutations of the input as either part of the last name or first name, as seperated by ' ', '-' or '.'.
        if (count($userQueryArr) == 1) {
            $sql = "SELECT users.`firstName`, users.`lastName`, users.`platoon`, users.`rank`, users.`appointment`, users.`id` FROM `users` INNER JOIN `inventory` WHERE (
            users.`firstName` LIKE '%" . $userQueryArr[0] . "%' OR users.`lastName` LIKE '%" . $userQueryArr[0] . "%')";
        } else if (count($userQueryArr) == 2) {
            $sql = "SELECT users.`firstName`, users.`lastName`, users.`platoon`, users.`rank`, users.`appointment`, users.`id` FROM `users` INNER JOIN `inventory` WHERE (
            (users.`firstName` LIKE '%" . implode('% %', $userQueryArr) . "%') OR
            (users.`firstName` LIKE '%" . implode('%-%', $userQueryArr) . "%') OR
            (users.`lastName`  LIKE '%" . implode('% %', $userQueryArr) . "%') OR
            (users.`lastName`  LIKE '%" . implode('%-%', $userQueryArr) . "%') OR

            (users.`firstName` LIKE '%" . $userQueryArr[0] . "%' AND users.`lastName` LIKE '%" . $userQueryArr[1] . "%') OR 
            (users.`firstName` LIKE '%" . $userQueryArr[1] . "%' AND users.`lastName` LIKE '%" . $userQueryArr[0] . "%'))";
        } else if (count($userQueryArr) == 3) {
            $sql = "SELECT users.`firstName`, users.`lastName`, users.`platoon`, users.`rank`, users.`appointment`, users.`id` FROM `users` INNER JOIN `inventory` WHERE (
            (users.`firstName` LIKE '%" . implode('% %', $userQueryArr) . "%') OR
            (users.`firstName` LIKE '%" . implode('%-%', $userQueryArr) . "%') OR
            (users.`lastName`  LIKE '%" . implode('% %', $userQueryArr) . "%') OR
            (users.`lastName`  LIKE '%" . implode('%-%', $userQueryArr) . "%') OR

            (users.`firstName` LIKE '%" . $userQueryArr[0]  . "%' AND users.`lastName` LIKE '%" . implode('% %', array_slice($userQueryArr, 1)) . "%') OR 
            (users.`firstName` LIKE '%" . $userQueryArr[0]  . "%' AND users.`lastName` LIKE '%" . implode('%-%', array_slice($userQueryArr, 1)) . "%') OR 
            (users.`firstName` LIKE '%" . end($userQueryArr) . "%' AND users.`lastName` LIKE '%" . implode('% %', array_slice($userQueryArr, 0, -1)) . "%') OR 
            (users.`firstName` LIKE '%" . end($userQueryArr) . "%' AND users.`lastName` LIKE '%" . implode('%-%', array_slice($userQueryArr, 0, -1)) . "%') OR 

            (users.`firstName` LIKE '%" . implode('% %', array_slice($userQueryArr, 1)) . "%' AND users.`lastName` LIKE '%" . $userQueryArr[0] . "%') OR 
            (users.`firstName` LIKE '%" . implode('%-%', array_slice($userQueryArr, 1)) . "%' AND users.`lastName` LIKE '%" . $userQueryArr[0] . "%') OR 
            (users.`firstName` LIKE '%" . implode('% %', array_slice($userQueryArr, 0, -1)) . "%' AND users.`lastName` LIKE '%" . end($userQueryArr) . "%') OR 
            (users.`firstName` LIKE '%" . implode('%-%', array_slice($userQueryArr, 0, -1)) . "%' AND users.`lastName` LIKE '%" . end($userQueryArr) . "%'))";
        } else if (count($userQueryArr) == 4) {
            $sql = "SELECT users.`firstName`, users.`lastName`, users.`platoon`, users.`rank`, users.`appointment`, users.`id` FROM `users` INNER JOIN `inventory` WHERE (
            (users.`firstName` LIKE '%" . implode('% %', $userQueryArr) . "%') OR
            (users.`firstName` LIKE '%" . implode('%-%', $userQueryArr) . "%') OR
            (users.`lastName`  LIKE '%" . implode('% %', $userQueryArr) . "%') OR
            (users.`lastName`  LIKE '%" . implode('%-%', $userQueryArr) . "%') OR

            (users.`firstName` LIKE '%" . $userQueryArr[0]  . "%' AND users.`lastName` LIKE '%" . implode('% %', array_slice($userQueryArr, 1)) . "%') OR 
            (users.`firstName` LIKE '%" . $userQueryArr[0]  . "%' AND users.`lastName` LIKE '%" . implode('%-%', array_slice($userQueryArr, 1)) . "%') OR 
            (users.`firstName` LIKE '%" . end($userQueryArr) . "%' AND users.`lastName` LIKE '%" . implode('% %', array_slice($userQueryArr, 0, -1)) . "%') OR 
            (users.`firstName` LIKE '%" . end($userQueryArr) . "%' AND users.`lastName` LIKE '%" . implode('%-%', array_slice($userQueryArr, 0, -1)) . "%') OR 

            (users.`firstName` LIKE '%" . implode('% %', array_slice($userQueryArr, 1)) . "%' AND users.`lastName` LIKE '%" . $userQueryArr[0] . "%') OR 
            (users.`firstName` LIKE '%" . implode('%-%', array_slice($userQueryArr, 1)) . "%' AND users.`lastName` LIKE '%" . $userQueryArr[0] . "%') OR 
            (users.`firstName` LIKE '%" . implode('% %', array_slice($userQueryArr, 0, -1)) . "%' AND users.`lastName` LIKE '%" . end($userQueryArr) . "%') OR 
            (users.`firstName` LIKE '%" . implode('%-%', array_slice($userQueryArr, 0, -1)) . "%' AND users.`lastName` LIKE '%" . end($userQueryArr) . "%') OR

            (users.`firstName` LIKE '%" . implode('% %', array_slice($userQueryArr, 0, 2)) . "%' AND users.`lastName` LIKE '%" . implode('% %', array_slice($userQueryArr, 2)) . "%') OR 
            (users.`firstName` LIKE '%" . implode('% %', array_slice($userQueryArr, 0, 2)) . "%' AND users.`lastName` LIKE '%" . implode('%-%', array_slice($userQueryArr, 2)) . "%') OR 
            (users.`firstName` LIKE '%" . implode('%-%', array_slice($userQueryArr, 0, 2)) . "%' AND users.`lastName` LIKE '%" . implode('% %', array_slice($userQueryArr, 2)) . "%') OR 
            (users.`firstName` LIKE '%" . implode('%-%', array_slice($userQueryArr, 0, 2)) . "%' AND users.`lastName` LIKE '%" . implode('%-%', array_slice($userQueryArr, 2)) . "%') OR 

            (users.`firstName` LIKE '%" . implode('% %', array_slice($userQueryArr, 2)) . "%' AND users.`lastName` LIKE '%" . implode('% %', array_slice($userQueryArr, 0, 2)) . "%') OR 
            (users.`firstName` LIKE '%" . implode('% %', array_slice($userQueryArr, 2)) . "%' AND users.`lastName` LIKE '%" . implode('%-%', array_slice($userQueryArr, 0, 2)) . "%') OR 
            (users.`firstName` LIKE '%" . implode('%-%', array_slice($userQueryArr, 2)) . "%' AND users.`lastName` LIKE '%" . implode('% %', array_slice($userQueryArr, 0, 2)) . "%') OR 
            (users.`firstName` LIKE '%" . implode('%-%', array_slice($userQueryArr, 2)) . "%' AND users.`lastName` LIKE '%" . implode('%-%', array_slice($userQueryArr, 0, 2)) . "%'))";
        } else {
            // Since very few people will ever require more than 5 words, there is little need to further define the possibilities. Especially since they can search for a person with their ID num.
            echo '<script language="javascript">';
            echo 'alert("The program is not built for this! Please have mercy! Please reduce the amount of words in the search bar.")';
            echo '</script>';
        }

        // Each item of $parameters is an array in the format of [(item / 'rank'), comparator, value]
        if ($parameters[0][0] != "") {
            $rankSql = "";
            $yearSql = "";
            $coySql = "";
            $plSql = "";

            $i = count($parameters);
            while ($i > 0) {
                $i--;
                $parameter = $parameters[$i][0];
                $comparator = $parameters[$i][1];
                $value = formatVarToSQL($parameters[$i][2]);

                if ($comparator == "!=") {
                    if ($value == "NULL") {
                        $comparator = "IS NOT";
                    } else {
                        $comparator = "<>";
                    }
                } else if ($value == "NULL") {
                    $comparator = "IS";
                }

                if ($parameter == "rank") {
                    if ($value == "'Officers'") {
                        $rankSql = $rankSql . " OR users.`yearLevel` = 0"; // Officers don't go to school i.e. no yearLevel
                    } else {
                        $rankSql = $rankSql . " OR users.`rank` $comparator $value";
                    }
                } else if ($parameter == "year") {
                    if ($value == "'Officers'") {
                        $yearSql = $yearSql . " OR users.`yearLevel` = 0"; // Officers don't go to school i.e. no yearLevel
                    } else if ($value == "'13'") {
                        $yearSql = $yearSql . " OR users.`yearLevel` > 12";
                    }else {
                        $yearSql = $yearSql . " OR users.`yearLevel` $comparator $value";
                    }
                } else if ($parameter == "company") {
                    $coySql = $coySql . " OR users.`company` $comparator $value";
                } else if ($parameter == "platoon") {
                    $plSql = $plSql . " OR users.`platoon` $comparator $value";
                } else {
                    $sql = $sql . " AND inventory.`$parameter` $comparator $value";
                }
            }

            // Since the rank parameter is inclusive with other rank parameters it needs to be added last together.
            if (strlen($rankSql) > 0) {
                $rankSql = " AND (" . substr($rankSql, 4) . ")";
                $sql = $sql . $rankSql;
            } else if (strlen($yearSql) > 0) {
                $yearSql = " AND (" . substr($yearSql, 4) . ")";
                $sql = $sql . $yearSql;
            } else if (strlen($coySql) > 0) {
                $coySql = " AND (" . substr($coySql, 4) . ")";
                $sql = $sql . $coySql;
            } else if (strlen($plSql) > 0) {
                $plSql = " AND (" . substr($plSql, 4) . ")";
                $sql = $sql . $plSql;
            }
        }

        // Allows the query to search through two different tables but not double up on results after joining the table together
        $sql = $sql . " AND users.`id` = inventory.`id`";
        $sql = $sql . " ORDER BY users.`lastName` ASC, users.`firstName` ASC, users.`platoon` ASC";

        $result = $_SESSION['conn'] -> query($sql);

        return $result;
    }
}

function formatSearchFilters (string $filters) : array {
    // Returns an array of strings of all the search filters that were described of in the URL.
    $filters = explode("|", $filters);

    $i = count($filters);
    while ($i > 0) {
        $i--;
        $filters[$i] = explode("_", $filters[$i]);
    }

    return $filters;
}

function formatRowSearchResult (array $row) : string {
    // Takes a MySQLi result row object as input and returns the formatted version of a row of a search result table.
    $rowFormat = "<tr> <td>LASTNAME</td> <td>FIRSTNAME</td> <td>PLATOON</td> <td>RANK</td> <td>APPOINTMENT</td>
    <td>    <button type='button' onClick='redirect(\"../issue/?action=Issue&id=ID\", false)'>  Issue     </button> 
            <button type='button' onClick='redirect(\"../issue/?action=Return&id=ID\", false)'> Return    </button>
            <button type='button' onClick='redirect(\"../issue/?action=Lost&id=ID\", false)'>   Lost      </button>
            <button type='button' onClick='redirect(\"../profile/?id=ID\", false)'>             Profile   </button> </td> </tr>";
    
    $firstname = $row['firstName'];
    $rowFormat = str_replace('FIRSTNAME', $firstname, $rowFormat);

    $lastname = $row['lastName'];
    $rowFormat = str_replace('LASTNAME', $lastname, $rowFormat);

    $appointment = strtoupper($row['appointment']);
    $rowFormat = str_replace('APPOINTMENT', $appointment, $rowFormat);

    $rank = strtoupper($row['rank']);
    $rowFormat = str_replace('RANK', $rank, $rowFormat);

    $platoon = strtoupper($row['platoon']);
    $rowFormat = str_replace('PLATOON', $platoon, $rowFormat);

    $id = $row['id'];
    $rowFormat = str_replace('ID', $id, $rowFormat);

    return $rowFormat;
}

// Issuement
function getPredefSetsJSArr (string $setname) : string {
    $row = "";
    $results = retrieveIssuedItems($setname);
    $items = retrieveAllIssuedItemsOnStock();

    $i = $items->num_rows;
    $num = $results->fetch_assoc();
    while($i > 0) { 
        $item = $items->fetch_assoc();
        $name = $item["item"];
        $row = $row . ", " . $num[$name];
        $i--;
    }
    $row = substr($row, 2);
    return "[" . $row . "]";
}

function issueEquipment (string $id, array $listOfIssues) {
    // Issues a list of items to a user and only allows the issues if every item has enough items on shelf on record. It also makes a record of this transaction.
    establishConnection();

    $formattedMods =  "";
    $formattedReceipt = "";
    $sqlStock = "";

    $id = formatVarToSQL($id);
    $time = date_format(date_create(), "Y/m/d H:i:s");
    $time = formatVarToSQL($time);

    // Creates the different SQL queries
    $i = count($listOfIssues);
    while ($i > 0) {
        $i--;
        $item = formatVarToSQL($listOfIssues[$i][0]);
        $value = formatVarToSQL($listOfIssues[$i][1]);
        if ($listOfIssues[$i][1] > 0) { // Do nothing if the modification is a value of 0
            $formattedMods = $formattedMods . ", `" . $listOfIssues[$i][0] . "` = `" . $listOfIssues[$i][0] . "` + " . $value;
            $formattedReceipt = $formattedReceipt . ", ($id, " . $item . ", " . $value . ", " . $time . ", '" . $_SESSION["currentUserId"] . "')";
            $sqlStock = $sqlStock . "UPDATE `stock` SET `onShelf` = `onShelf` - " . $value . ", `onLoan` = `onLoan` + " . $value . " WHERE `item` = " . $item . ";";
        }
    }

    // Removes the initial characters used to conjoin multiple statements, the first use of conjoing characters is unnecessary as it has nothing to join to.
    $formattedMods = substr($formattedMods, 2);
    $formattedReceipt = substr($formattedReceipt, 2);

    $sql = "UPDATE inventory SET " . $formattedMods . " WHERE `id` = $id";
    $result = $_SESSION['conn'] -> query($sql);

    $sql = "INSERT INTO equipmentReceipts (`id`, `item`, `changeInNum`, `time`, `serverId`) VALUES " . $formattedReceipt;
    $result = $_SESSION['conn'] -> query($sql);

    $result = $_SESSION['conn'] -> multi_query($sqlStock);
}

function returnEquipment (string $id, array $listOfReturns) {
    // Returns a list of items from a user and only allows the returns if every item has enough on issue to the user. It also makes a record of this transaction.
    establishConnection();

    $formattedMods =  "";
    $formattedReceipt = "";
    $sqlStock = "";

    $id = formatVarToSQL($id);
    $time = date_format(date_create(), "Y/m/d H:i:s");
    $time = formatVarToSQL($time);

    // Creates the different SQL queries
    $i = count($listOfReturns);
    while ($i > 0) {
        $i--;
        $item = formatVarToSQL($listOfReturns[$i][0]);
        $value = formatVarToSQL($listOfReturns[$i][1]);
        if ($listOfReturns[$i][1] > 0) { // Do nothing if the modification is a value of 0
            $formattedMods = $formattedMods . ", `" . $listOfReturns[$i][0] . "` = `" . $listOfReturns[$i][0] . "` - " . $value;
            $formattedReceipt = $formattedReceipt . ", ($id, " . $item . ", '-" . $listOfReturns[$i][1] . "', " . $time . ", '" . $_SESSION["currentUserId"] . "')";
            $sqlStock = $sqlStock . "UPDATE `stock` SET `onShelf` = `onShelf` + " . $value . ", `onLoan` = `onLoan` - " . $value . " WHERE `item` = " . $item . ";";
        }
    }

    // Removes the initial characters used to conjoin multiple statements, the first use of conjoing characters is unnecessary as it has nothing to join to.
    $formattedMods = substr($formattedMods, 2);
    $formattedReceipt = substr($formattedReceipt, 2);

    $sql = "UPDATE inventory SET " . $formattedMods . " WHERE `id` = $id";
    $result = $_SESSION['conn'] -> query($sql);

    $sql = "INSERT INTO equipmentReceipts (`id`, `item`, `changeInNum`, `time`, `serverId`) VALUES " . $formattedReceipt;
    $result = $_SESSION['conn'] -> query($sql);

    $result = $_SESSION['conn'] -> multi_query($sqlStock);
}

function declareLostOrDamaged (string $id, array $listOfLost) {
    // Declares a list of items as lost or damaged from the user and makes a record of this transaction.
    establishConnection();

    $formattedMods =  "";
    $formattedReceipt = "";
    $sqlStock = "";

    $id = formatVarToSQL($id);
    $time = date_format(date_create(), "Y/m/d H:i:s");
    $time = formatVarToSQL($time);

    // Creates the different SQL queries
    $i = count($listOfLost);
    while ($i > 0) {
        $i--;
        $item = formatVarToSQL($listOfLost[$i][0]);
        $value = formatVarToSQL($listOfLost[$i][1]);
        if ($listOfLost[$i][1] > 0) { // Do nothing if the modification is a value of 0
            $formattedMods = $formattedMods . ", `" . $listOfLost[$i][0] . "` = `" . $listOfLost[$i][0] . "` - " . $value;
            $formattedReceipt = $formattedReceipt . ", ($id, " . $item . ", '-" . $listOfLost[$i][1] . "', " . $time . ", 1, '" . $_SESSION["currentUserId"] . "')";
            $sqlStock = $sqlStock . "UPDATE `stock` SET `lostOrDamaged` = `lostOrDamaged` + " . $value . ", `onLoan` = `onLoan` - " . $value . ", `total` = `total` - " . $value . " WHERE `item` = " . $item . ";";
        }
    }

    // Removes the initial characters used to conjoin multiple statements, the first use of conjoing characters is unnecessary as it has nothing to join to.
    $formattedMods = substr($formattedMods, 2);
    $formattedReceipt = substr($formattedReceipt, 2);

    $sql = "UPDATE inventory SET " . $formattedMods . " WHERE `id` = $id";
    $result = $_SESSION['conn'] -> query($sql);

    $sql = "INSERT INTO equipmentReceipts (`id`, `item`, `changeInNum`, `time`, `lostOrDamaged`, `serverId`) VALUES " . $formattedReceipt;
    $result = $_SESSION['conn'] -> query($sql);

    $result = $_SESSION['conn'] -> multi_query($sqlStock);
}

function setIssue (string $id, array $listOfItems) {
    // Redefines the set of equipment issued to a user. Does not create a record of transaction.
    establishConnection();

    $formattedMods =  "";

    $id = formatVarToSQL($id);
    $time = date_format(date_create(), "Y/m/d H:i:s");
    $time = formatVarToSQL($time);

    // Creates the different SQL queries
    $i = count($listOfItems);
    while ($i > 0) {
        $i--;
        $value = formatVarToSQL($listOfItems[$i][1]);
        $formattedMods = $formattedMods . ", `" . $listOfItems[$i][0] . "` = " . $value;
    }

    // Removes the initial characters used to conjoin multiple statements, the first use of conjoing characters is unnecessary as it has nothing to join to.
    $formattedMods = substr($formattedMods, 2);

    $sql = "UPDATE inventory SET " . $formattedMods . " WHERE `id` = $id";
    $result = $_SESSION['conn'] -> query($sql);

}


// OLD
function retrieveAccessLevel(string $appointment, string $platoon) : string {
    // Returns the access level for the given appointment from the defining document stored on the server.
    $myfile = fopen("../appointmentAccessRoles.csv", "r") or die("Internal server error: Unable to open file!");
    $file = fread($myfile, filesize("../appointmentAccessRoles.csv"));

    // Retrieves everything beyond the appointment, the ":" is to ensure that the appointment of 'recruit' does retrieve the correct line.
    $line = strstr($file, $appointment . ":");
    fclose($myfile);

    // Finds the starting and sentinel characters for the invidual appointment lines and returns the access level.
    $start = strpos($line, ':');
    if (! $end = strpos($line, '|')) {
        $end = strlen($line);
    }
    $access = substr($line, $start+1, $end-$start-1);

    // If the cadet is in the QM PL they require extra priviledges in order to complete their duties.
    if ($access !== "admin" and $platoon === "QM") {
        $access = "qstore";
    }

    return $access;
}

function getUserValue(string $id, string $uservalue, string $table) {
    // Given user ID and a value that is desired to be used, will return the respective value.
    establishConnection();

    $id = formatVarToSQL($id);
    $sql = "SELECT `$uservalue` FROM `$table` WHERE `id` LIKE $id";
    
    $result = $_SESSION['conn'] -> query($sql);
    
    if ($result->num_rows > 1) {
        echo '<script language="javascript">';
        echo 'alert("Duplicate user error")';
        echo '</script>';
        return NULL;
    } 
    if ($result->num_rows == 1) {
        $row = $result->fetch_assoc();
        return $row[$uservalue];
    } else {
        echo '<script language="javascript">';
        echo 'alert("No user by the id of: ' . $id . '")';
        echo '</script>';
        return NULL;
    }
}

function getMultiUserValues(string $id, array $uservalues, string $table) { // Was "getMultiUserValues"
    // Given user ID and values that are desired to be used, will return the respective values in the form of an array.
    establishConnection();

    $id = formatVarToSQL($id);

    // Formats and adds the values to a SQL query
    $i = count($uservalues);
    $values = "";
    while ($i > 0) {
        $i--;
        $x = $uservalues[$i];
        $values = $values . ", `$x`";
    }
    $values = substr($values, 2);
    $sql = "SELECT $values FROM `$table` WHERE `id` LIKE $id";
    
    $result = $_SESSION['conn'] -> query($sql);

    if ($result->num_rows > 1) {
        echo '<script language="javascript">';
        echo 'alert("Duplicate user error")';
        echo '</script>';
        return NULL;
    } 
    if ($result->num_rows == 1) {
        $row = $result->fetch_assoc();
        return $row;
    } else {
        echo '<script language="javascript">';
        echo 'alert("No user by the id of: ' . $id . '")';
        echo '</script>';
        return NULL;
    }
}

function establishSessionVars() {
    // Defines the session variables
    $id = $_SESSION["currentUserId"]; // ID is the only one to not assign to the session var as it is what is used as the credential for the user.
    $_SESSION["currentUserFirstName"] =     getUserValue($id, "firstName", "users");
    $_SESSION["currentUserLastName"] =      getUserValue($id, "lastName", "users");
    $_SESSION["currentUserAppointment"] =   getUserValue($id, "appointment", "users");
    $_SESSION["currentUserRank"] =          getUserValue($id, "rank", "users");
}

function establishProfilePageVars(string $id) { // CAN BE OPTIMISED AND MADE REDUNDANT
    // Returns the variables that the profile page desires.
    $values = ["firstName", "lastName", "appointment", "rank"];
    $result = getMultiUserValues($id, $values, "users");
    $firstname =    $result["firstName"];
    $lastname =     $result["lastName"];
    $appointment =  $result["appointment"];
    $rank =         $result["rank"];
    return [$firstname, $lastname, $appointment, $rank];
}

function addUser(string $firstName, string $lastName, string $id, string $username, string $userpass, string $rank, string $appointment, string $company, string $platoon, string $section, int $yearLevel) {
    // Adds a user to the database as well as encrypt the password.
    establishConnection();
    $hasheduserpass = password_hash($userpass, PASSWORD_BCRYPT);
    $access = retrieveAccessLevel($appointment, strtoupper($platoon));

    $firstName =                formatVarToSQL($firstName);
    $lastName =                 formatVarToSQL($lastName);
    $id =                       formatVarToSQL($id);
    $access =                   formatVarToSQL($access);
    $username =                 formatVarToSQL($username);
    $hasheduserpass =           formatVarToSQL($hasheduserpass);
    $rank =         strtoupper( formatVarToSQL($rank));
    $appointment =  strtolower( formatVarToSQL($appointment));
    $company =      strtoupper( formatVarToSQL($company));
    $platoon =      strtoupper( formatVarToSQL($platoon));
    $section =                  formatVarToSQL($section);
    if ($yearLevel === 0) {
        $yearLevel = '0';
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

function retrieveAllUserColumns () {
    // Returns an array of all of the columns used to define a user
    establishConnection();
    $sql = "SHOW COLUMNS FROM `users`;";
    $result = $_SESSION['conn']->query($sql);
    return $result;
}

function retrieveAllIssuedItemsOnStock() {
    // Returns a MySQLi object of all of the different items that are on stock in QCS
    establishConnection();
    $sql = "SELECT `item` FROM `stock`";
    $result = $_SESSION['conn'] -> query($sql);
    return $result;
}

function retrieveStock() {
    // Returns a MySQLi object of all of the different items that are on stock in QCS and their numbers on shelf, on loan, total and lost or damaged.
    establishConnection();
    $sql = "SELECT * FROM `stock`";
    $result = $_SESSION['conn'] -> query($sql);
    return $result;
}

function retrieveIssuedItems(string $id) {
    // Retrieves a MySQLi object of all of the items on issue to a certain user.
    establishConnection();
    $id = formatVarToSQL($id);
    $sql = "SELECT * FROM `inventory` WHERE `id` = $id";
    $result = $_SESSION['conn'] -> query($sql);
    return $result;
}

function retrieveIssueHistory(string $id) {
    // Retrieves a MySQLi object of the history of item issuements and returns for a certain user.
    establishConnection();
    $id = formatVarToSQL($id);
    $sql = "SELECT * FROM `equipmentReceipts` WHERE `id` = $id ORDER BY `receiptNum` DESC";
    $result = $_SESSION['conn'] -> query($sql);
    return $result;
}

function retrieveStockHistory() {
    // Retrieves a MySQLi object of the history of all item issuements and returns it.
    establishConnection();
    $sql = "SELECT * FROM `equipmentReceipts` ORDER BY `receiptNum` DESC";
    $result = $_SESSION['conn'] -> query($sql);
    return $result;
}

?>
