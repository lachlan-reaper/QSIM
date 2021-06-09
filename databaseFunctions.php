<?php

require 'functions.php';

function formatNullAndStringToSQL($variable) {
    // Formats the inputted string so it can be directly placed into a SQL query string without extra formatting.
    if ($variable === "" or $variable === "NULL" or $variable === null or $variable === 0) {
        return "NULL";
    }
    if (gettype($variable) === "string") {
        return "'" . $variable . "'";
    }
    return $variable;
}

function retrieveAccessLevel(string $appointment, string $platoon) : string {
    // Returns the access level for the given appointment from the defining document stored on the server.
    $myfile = fopen("../appointmentAccessRoles.aars", "r") or die("Internal server error: Unable to open file!");
    $file = fread($myfile, filesize("../appointmentAccessRoles.aars"));

    // Retrieves everything beyond the appointment, the ":" is to ensure that the appointment of 'recruit' does retrieve the correct line.
    $line = strstr($file, $appointment . ":");
    fclose($myfile);

    // Finds the starting and sentinel characters for the invidual appointment lines and returns the access level.
    $start = strpos($line, ':');
    $end = strpos($line, '|');
    $access = substr($line, $start+1, $end-$start-1);

    // If the cadet is in the QM PL they require extra priviledges in order to complete their duties.
    if ($access !== "admin" and $platoon === "qm") {
        $access = "qstore";
    }

    return $access;
}

function getUserValue(string $id, string $uservalue, string $table) {
    // Given user ID and a value that is desired to be used, will return the respective value.
    establishConnection();

    $id = formatNullAndStringToSQL($id);
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

function getMultiUserValues(string $id, array $uservalues, string $table) { // TO BE TESTED AND IMPLEMENTED WHERE NECESSARY
    // Given user ID and values that are desired to be used, will return the respective values in the form of a MySQLi object.
    establishConnection();

    $id = formatNullAndStringToSQL($id);

    $i = count($uservalues);
    while ($i > 0) {
        $i--;
        $x = $uservalues[$i];
        $values = $values . "`$x`";
    }

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

function retrieveAccess(string $id) { // TO BE REMOVED! IS NOW REDUNDANT!!!
    // Returns the access level for the given person (identified by their id num) from the database stored on the server.
    $result = getUserValue($id, "access", "users");
    return $result;
}

function establishSessionVars() {
    // Defines the session variables
    session_start();
    $id = $_SESSION["currentUserId"]; // ID is the only one to not assign to the session var as it is what is used as the credential for the user.
    $_SESSION["currentUserFirstName"] =     getUserValue($id, "firstName", "users");
    $_SESSION["currentUserLastName"] =      getUserValue($id, "lastName", "users");
    $_SESSION["currentUserAppointment"] =   getUserValue($id, "appointment", "users");
    $_SESSION["currentUserRank"] =          getUserValue($id, "rank", "users");
}

function establishProfilePageVars(string $id) {
    // Returns the variables that the profile page desires.
    session_start();
    $firstname =    getUserValue($id, "firstName", "users");
    $lastname =     getUserValue($id, "lastName", "users");
    $appointment =  getUserValue($id, "appointment", "users");
    $rank =         getUserValue($id, "rank", "users");
    return array($firstname, $lastname, $appointment, $rank);
}

function addUser(string $firstName, string $lastName, string $id, string $username, string $userpass, string $rank, string $appointment, string $company, string $platoon, string $section, int $yearLevel) {
    // Adds a user to the database as well as encrypt the password.
    establishConnection();
    $hasheduserpass = password_hash($userpass, PASSWORD_BCRYPT);
    $access = retrieveAccessLevel($appointment, $platoon);

    $firstName =                formatNullAndStringToSQL($firstName);
    $lastName =                 formatNullAndStringToSQL($lastName);
    $id =                       formatNullAndStringToSQL($id);
    $access =                   formatNullAndStringToSQL($access);
    $username =                 formatNullAndStringToSQL($username);
    $hasheduserpass =           formatNullAndStringToSQL($hasheduserpass);
    $rank =         strtoupper( formatNullAndStringToSQL($rank));
    $appointment =  strtolower( formatNullAndStringToSQL($appointment));
    $company =      strtoupper( formatNullAndStringToSQL($company));
    $platoon =      strtoupper( formatNullAndStringToSQL($platoon));
    $section =                  formatNullAndStringToSQL($section);
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

function formatSearchFilters (string $filters) {
    // Returns an array of strings of all the search filters that were described of in the URL.
    $filters = explode("|", $filters);

    $i = count($filters);
    while ($i > 0) {
        $i--;
        $filters[$i] = explode("_", $filters[$i]);
    }

    return $filters;
}

function retrieveSearchQueryResults (string $userQuery, array $parameters) {
    // Returns a MySQLi object of the results after parsing and interpreting the user's query and parameters.
    establishConnection();
    
    // If the query is numeric, then it is an ID num.
    if (is_numeric($userQuery)) {
        $userQuery = formatNullAndStringToSQL($userQuery);
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
            (users.`firstName` LIKE '%" . $userQueryArr[-1] . "%' AND users.`lastName` LIKE '%" . implode('% %', array_slice($userQueryArr, 0, -1)) . "%') OR 
            (users.`firstName` LIKE '%" . $userQueryArr[-1] . "%' AND users.`lastName` LIKE '%" . implode('%-%', array_slice($userQueryArr, 0, -1)) . "%') OR 

            (users.`firstName` LIKE '%" . implode('% %', array_slice($userQueryArr, 1)) . "%' AND users.`lastName` LIKE '%" . $userQueryArr[0] . "%') OR 
            (users.`firstName` LIKE '%" . implode('%-%', array_slice($userQueryArr, 1)) . "%' AND users.`lastName` LIKE '%" . $userQueryArr[0] . "%') OR 
            (users.`firstName` LIKE '%" . implode('% %', array_slice($userQueryArr, 0, -1)) . "%' AND users.`lastName` LIKE '%" . $userQueryArr[-1] . "%') OR 
            (users.`firstName` LIKE '%" . implode('%-%', array_slice($userQueryArr, 0, -1)) . "%' AND users.`lastName` LIKE '%" . $userQueryArr[-1] . "%'))";
        } else if (count($userQueryArr) == 4) {
            $sql = "SELECT users.`firstName`, users.`lastName`, users.`platoon`, users.`rank`, users.`appointment`, users.`id` FROM `users` INNER JOIN `inventory` WHERE (
            (users.`firstName` LIKE '%" . implode('% %', $userQueryArr) . "%') OR
            (users.`firstName` LIKE '%" . implode('%-%', $userQueryArr) . "%') OR
            (users.`lastName`  LIKE '%" . implode('% %', $userQueryArr) . "%') OR
            (users.`lastName`  LIKE '%" . implode('%-%', $userQueryArr) . "%') OR

            (users.`firstName` LIKE '%" . $userQueryArr[0]  . "%' AND users.`lastName` LIKE '%" . implode('% %', array_slice($userQueryArr, 1)) . "%') OR 
            (users.`firstName` LIKE '%" . $userQueryArr[0]  . "%' AND users.`lastName` LIKE '%" . implode('%-%', array_slice($userQueryArr, 1)) . "%') OR 
            (users.`firstName` LIKE '%" . $userQueryArr[-1] . "%' AND users.`lastName` LIKE '%" . implode('% %', array_slice($userQueryArr, 0, -1)) . "%') OR 
            (users.`firstName` LIKE '%" . $userQueryArr[-1] . "%' AND users.`lastName` LIKE '%" . implode('%-%', array_slice($userQueryArr, 0, -1)) . "%') OR 

            (users.`firstName` LIKE '%" . implode('% %', array_slice($userQueryArr, 1)) . "%' AND users.`lastName` LIKE '%" . $userQueryArr[0] . "%') OR 
            (users.`firstName` LIKE '%" . implode('%-%', array_slice($userQueryArr, 1)) . "%' AND users.`lastName` LIKE '%" . $userQueryArr[0] . "%') OR 
            (users.`firstName` LIKE '%" . implode('% %', array_slice($userQueryArr, 0, -1)) . "%' AND users.`lastName` LIKE '%" . $userQueryArr[-1] . "%') OR 
            (users.`firstName` LIKE '%" . implode('%-%', array_slice($userQueryArr, 0, -1)) . "%' AND users.`lastName` LIKE '%" . $userQueryArr[-1] . "%') OR

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
            $i = count($parameters);
            while ($i > 0) {
                $i--;
                $parameter = $parameters[$i][0];
                $comparator = $parameters[$i][1];
                $value = formatNullAndStringToSQL($parameters[$i][2]);

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
                        $rankSql = $rankSql . " OR users.`yearLevel` IS NULL"; // Officers don't go to school i.e. no yearLevel
                    } else {
                        $rankSql = $rankSql . " OR users.`$parameter` $comparator $value";
                    }
                } else {
                    $sql = $sql . " AND inventory.`$parameter` $comparator $value";
                }
            }

            // Since the rank parameter is inclusive with other rank parameters it needs to be added last together.
            if (strlen($rankSql) > 0) {
                $rankSql = " AND (" . substr($rankSql, 4) . ")";
                $sql = $sql . $rankSql;
            }
        }

        $sql = $sql . " AND users.`id` = inventory.`id`";
        $sql = $sql . " ORDER BY users.`lastName` ASC, users.`firstName` ASC, users.`platoon` ASC";

        $result = $_SESSION['conn'] -> query($sql);

        return $result;
    }
}

function formatRowSearchResult ($row) : string {
    // Takes a MySQLi result row object as input and returns the formatted version of a row of a search result table.
    $rowFormat = "<tr> <td>LASTNAME</td> <td>FIRSTNAME</td> <td>PLATOON</td> <td>RANK</td> <td>APPOINTMENT</td>
    <td>    <a href='../issue/?action=Issue&id=ID'> <button type='button'>  Issue     </button> </a> 
            <a href='../issue/?action=Return&id=ID'><button type='button'>  Return    </button> </a> 
            <a href='../issue/?action=Lost&id=ID'>  <button type='button'>  Lost      </button> </a> 
            <a href='../profile/?id=ID'>            <button type='button'>  Profile   </button> </a> </td> </tr>";
    
    $firstname = ucfirst($row['firstName']);
    $rowFormat = str_replace('FIRSTNAME', $firstname, $rowFormat);

    $lastname = ucfirst($row['lastName']);
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
    $id = formatNullAndStringToSQL($id);
    $sql = "SELECT * FROM `inventory` WHERE `id` = $id";
    $result = $_SESSION['conn'] -> query($sql);
    return $result;
}

function retrieveIssueHistory(string $id) {
    // Retrieves a MySQLi object of the history of item issuements and returns for a certain user.
    establishConnection();
    $id = formatNullAndStringToSQL($id);
    $sql = "SELECT * FROM `equipmentReceipts` WHERE `id` = $id";
    $result = $_SESSION['conn'] -> query($sql);
    return $result;
}

function issueEquipment (string $id, $listOfIssues) { // CHECK IF THE REDOING OF THE WHILE LOOP FIXES THE PROBLEM!!!
    // Issues a list of items to a user and only allows the issues if every item has enough items on shelf on record. It also makes a record of this transaction.
    establishConnection();

    $id = formatNullAndStringToSQL($id);
    $time = date_format(date_create(), "Y/m/d H:i:s");
    $time = formatNullAndStringToSQL($time);
    $stock = retrieveStock();
    
    $max = count($listOfIssues);
    $i = 0;
    while ($max > $i) {
        // Goes through the list of items on stock until it selects the one that is currently the point of issue.
        $item = $stock->fetch_assoc();
        while (! ($item["item"] == $listOfIssues[$i][0])) {
            $item = $stock->fetch_assoc();
        }

        // If there are not enough items on shelf, it alerts the user of the problem and cancels the issue.
        if ($listOfIssues[$i][1] > $item["onShelf"]) {
            echo '<script language="javascript">';
            echo 'alert("Not enough of ' . $listOfIssues[$i][0] . ' has been counted on the shelf.");';
            echo 'window.location.href="../stock/";';
            echo '</script>';
            die();
        }
        
        $item = formatNullAndStringToSQL($listOfIssues[$i][0]);
        $value = formatNullAndStringToSQL($listOfIssues[$i][1]);
        $formattedMods = $formattedMods . ", `" . $listOfIssues[$i][0] . "` = `" . $listOfIssues[$i][0] . "` + " . $value;
        $formattedReceipt = $formattedReceipt . ", ($id, " . $item . ", " . $value . ", " . $time . ", '" . $_SESSION["currentUserId"] . "')";
        $sqlStock = $sqlStock . "UPDATE `stock` SET `onShelf` = `onShelf` - " . $value . ", `onLoan` = `onLoan` + " . $value . " WHERE `item` = " . $item . ";";

        $i++;
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

function returnEquipment (string $id, $listOfReturns) {
    // Returns a list of items from a user and only allows the returns if every item has enough on issue to the user. It also makes a record of this transaction.
    establishConnection();

    $id = formatNullAndStringToSQL($id);
    $time = date_format(date_create(), "Y/m/d H:i:s");
    $time = formatNullAndStringToSQL($time);

    $i = count($listOfReturns);
    while ($i > 0) {
        $i--;
        $item = formatNullAndStringToSQL($listOfReturns[$i][0]);
        $value = formatNullAndStringToSQL($listOfReturns[$i][1]);
        $formattedMods = $formattedMods . ", `" . $listOfReturns[$i][0] . "` = `" . $listOfReturns[$i][0] . "` - " . $value;
        $formattedReceipt = $formattedReceipt . ", ($id, " . $item . ", '-" . $listOfReturns[$i][1] . "', " . $time . ", '" . $_SESSION["currentUserId"] . "')";
        $sqlStock = $sqlStock . "UPDATE `stock` SET `onShelf` = `onShelf` + " . $value . ", `onLoan` = `onLoan` - " . $value . " WHERE `item` = " . $item . ";";
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

function declareLostOrDamaged (string $id, $listOfLost) {
    // Declares a list of items as lost or damaged from the user and makes a record of this transaction.
    establishConnection();

    $id = formatNullAndStringToSQL($id);
    $time = date_format(date_create(), "Y/m/d H:i:s");
    $time = formatNullAndStringToSQL($time);

    $i = count($listOfLost);
    while ($i > 0) {
        $i--;
        $item = formatNullAndStringToSQL($listOfLost[$i][0]);
        $value = formatNullAndStringToSQL($listOfLost[$i][1]);
        $formattedMods = $formattedMods . ", `" . $listOfLost[$i][0] . "` = `" . $listOfLost[$i][0] . "` - " . $value;
        $formattedReceipt = $formattedReceipt . ", ($id, " . $item . ", '-" . $listOfLost[$i][1] . "', " . $time . ", 1, '" . $_SESSION["currentUserId"] . "')";
        $sqlStock = $sqlStock . "UPDATE `stock` SET `lostOrDamaged` = `lostOrDamaged` + " . $value . ", `onLoan` = `onLoan` - " . $value . " WHERE `item` = " . $item . ";";
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

?>
