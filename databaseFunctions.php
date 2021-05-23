<?php

require 'functions.php';
// addUser("Julie-ann", "de Kantzow", 1230984, "jdk", "unit", "MAJ", "commander", "RHQ", "RHQ", "", 0);
// addUser("Lachlan", "Muir", 987654321, "lmuir2021", "riddles", "CUO", "logso", "RHQ", "RHQ", "", 12);
// addUser("Hayden", "Wild", 3333005358, "hwild2021", "puzzle", "SGT", "sgt mentor", "SPT", "PNR", "1", 12);
// addUser("Darius", "Hall", 91387911, "dhall2023", "hoi4", "CPL", "sect comd", "SPT", "qm", "2", 10);
// addUser("John", "Smith", 123456789, "cadet2025", "stupid", "REC", "recruit", "A", "1", "1", 8);
// addUser("Finn", "Harley Whitney", 3872694318, "fharl2021", "friday", "LCPL", "sect 2ic", "C", "9", "7", 8);
// addUser("Isaac", "Coombes", 1834443423, "isaac", "coombes", "WO2", "rqms", "SPT", "QM", "1", 11);
// addUser("James", "Spargo", 3718363097, "teach", "god", "REC", "recruit", "B", "5", "4", 8);
// addUser("Jåcque", "Roué", 987123, "french", "man", "WO1", "rsm", "RHQ", "RHQ", "", 12);
// refreshAccess(91387911);

function retrieveAccessLevel(string $appointment, string $platoon) : string {
    $myfile = fopen("appointmentAccessRoles.aars", "r") or die("Internal server error: Unable to open file!");
    $file = fread($myfile, filesize("appointmentAccessRoles.aars"));
    $line = strstr($file, $appointment);
    fclose($myfile);

    $start = strpos($line, ':');
    $end = strpos($line, '|');
    $access = substr($line, $start+1, $end-$start-1);

    if ($access !== "admin" and $platoon === "qm") {
        $access = "qstore";
    }

    return $access;
}

function formatNullAndStringToSQL($variable) {
    if ($variable === "" or $variable === "NULL" or $variable === null or $variable === 0) {
        return "NULL";
    }
    if (gettype($variable) === "string") {
        return "'" . $variable . "'";
    }
    return $variable;
}

function retrieveAccess(int $id) {
    establishConnection();

    $sql = "SELECT `access` FROM `users` WHERE `id` LIKE $id";
    $result = $_SESSION['conn'] -> query($sql);

    if ($result->num_rows > 1) {
        echo '<script language="javascript">';
        echo 'alert("Duplicate user error")';
        echo '</script>';
        return;
    } 
    if ($result->num_rows == 1) {
        $row = $result->fetch_assoc();
        return $row["access"];
    } else {
        echo '<script language="javascript">';
        echo 'alert("No user by the id of: ' . $id . '")';
        echo '</script>';
        return;
    }
}

function refreshAccess(int $id) {
    establishConnection();

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
    $id = formatNullAndStringToSQL($id);
    $access = formatNullAndStringToSQL($access);

    $sql = "UPDATE `users` SET `access` = $access WHERE `users`.`id` = $id;";
    
    if ($_SESSION['conn']->query($sql) === TRUE) {
        echo "Record updated successfully";
    } else {
        echo "Error: " . $sql . "<br>" . $_SESSION['conn']->error;
    }
}

function getUserValue(int $id, string $uservalue, string $table) {
    establishConnection();

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

function establishSessionVars() {
    session_start();
    $id = $_SESSION["currentUserId"];
    $_SESSION["currentUserFirstname"] = getUserValue($id, "firstname", "users");
    $_SESSION["currentUserLastname"] = getUserValue($id, "lastname", "users");
    $_SESSION["currentUserAppointment"] = getUserValue($id, "appointment", "users");
    $_SESSION["currentUserRank"] = getUserValue($id, "rank", "users");
}

function addUser(string $firstName, string $lastName, int $id, string $username, string $userpass, string $rank, string $appointment, string $company, string $platoon, string $section, int $yearLevel) {
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
    $yearLevel =                formatNullAndStringToSQL($yearLevel);

    $sql = "INSERT INTO `users` (`firstName`, `lastName`, `id`, `access`, `username`, `userpass`, `rank`, `appointment`, `yearLevel`, `company`, `platoon`, `section`) VALUES ($firstName, $lastName, $id, $access, $username, $hasheduserpass, $rank, $appointment, $yearLevel, $company, $platoon, $section)";
    if ($_SESSION['conn']->query($sql) === TRUE) {
        echo "New user record created successfully";
    } else {
        echo "Error: " . $sql . "<br>" . $_SESSION['conn']->error;
        return;
    }

    $sql = "INSERT INTO `inventory` (`id`) VALUES ($id);";
    if ($_SESSION['conn']->query($sql) === TRUE) {
        echo "New user inventory created successfully";
    } else {
        echo "Error: " . $sql . "<br>" . $_SESSION['conn']->error;

        $sql = "DELETE FROM `users` WHERE id = $id;";
        if ($_SESSION['conn']->query($sql) === TRUE) {
            echo "User record successfully removed because of previous error";
        } else {
            echo "Error: " . $sql . "<br>" . $_SESSION['conn']->error;
        }
    }
}

function formatSearchFilters (string $filters) {
    $filters = explode("|", $filters);

    $i = count($filters);
    while ($i > 0) {
        $i--;
        $filters[$i] = explode("_", $filters[$i]);
    }

    return $filters;
}

function retrieveSearchQueryResults (string $userQuery, array $parameters) {
    establishConnection();
    
    if (is_numeric($userQuery)) {
        $sql = "SELECT `firstName`, `lastName`, `platoon`, `rank`, `appointment` FROM `users` WHERE `id` LIKE $userQuery";
        $result = $_SESSION['conn'] -> query($sql);
        return $result;
    } else {
        $userQuery = preg_replace('/[_+!?=<>≤≥@#$%^&*(){}|~\/]/', '', $userQuery);
        $userQuery = str_replace('-', ' ', $userQuery);
        $userQuery = str_replace('.', ' ', $userQuery);
        $userQuery = trim($userQuery);
        $userQueryArr = explode(" ", $userQuery);

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
            echo '<script language="javascript">';
            echo 'alert("The program is not built for this! Please have mercy! Please reduce the amount of words in the search bar.")';
            echo '</script>';
        }

        if ($parameters[0][0] != "") {
            $rankSql = "";
            $i = count($parameters);
            while ($i > 0) {
                $i--;
                $parameter = $parameters[$i][0];
                $comparator = $parameters[$i][1];
                $value = formatNullAndStringToSQL($parameters[$i][2]);

                if ($comparator == "!=") {
                    $comparator = "<>";
                    if ($value == "NULL") {
                        $comparator = "IS NOT";
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
    $rowFormat = "<tr><td>LASTNAME</td><td>FIRSTNAME</td><td>PLATOON</td><td>RANK</td><td>APPOINTMENT</td>
    <td><a href='../profile/?id=ID'><button type='button'>Issue</button></a></td></tr>";
    
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

function modifyEquipmentIssue ($id, $modifications) {
    establishConnection();
    $i = count($modifications);
    while ($i > 0) {
        $i--;
        $formattedMods = $formattedMods . ", " . $modifications[$i][0] . "=" . $modifications[$i][1];
    }
    $formattedMods = substr($formattedMods, 2);
    $sql = "UPDATE inventory SET $formattedMods WHERE `id` = $id";
    $result = $_SESSION['conn'] -> query($sql);
}

?>