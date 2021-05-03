<?php

require 'functions.php'; // Possibly unnecessary for once implemented fully???

// addUser("John", "Smith", 123456789, "cadet2025", "stupid", "REC", "recruit", "A", "1", "1", 8);
// addUser("Hayden", "Wild", 41902712, "hwild2021", "puzzle", "SGT", "sgt mentor", "SPT", "PNR", "1", 12);
// addUser("Darius", "Hall", 91387911, "dhall2023", "hoi4", "CPL", "sect comd", "SPT", "qm", "2", 10);
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

function addUser(string $firstName, string $lastName, int $id, string $username, string $userpass, string $rank, string $appointment, string $company, string $platoon, string $section, int $yearLevel) {
    establishConnection();
    $hasheduserpass = password_hash($userpass, PASSWORD_BCRYPT);
    $access = retrieveAccessLevel($appointment, $platoon);

    $firstName = formatNullAndStringToSQL($firstName);
    $lastName = formatNullAndStringToSQL($lastName);
    $id = formatNullAndStringToSQL($id);
    $access = formatNullAndStringToSQL($access);
    $username = formatNullAndStringToSQL($username);
    $hasheduserpass = formatNullAndStringToSQL($hasheduserpass);
    $rank = formatNullAndStringToSQL($rank);
    $appointment = formatNullAndStringToSQL($appointment);
    $company = formatNullAndStringToSQL($company);
    $platoon = formatNullAndStringToSQL($platoon);
    $section = formatNullAndStringToSQL($section);
    $yearLevel = formatNullAndStringToSQL($yearLevel);

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
?>