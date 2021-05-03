<?php

function establishConnection() {
    session_start();
    $host = $_SERVER['HTTP_HOST'];
    $_SESSION["websiteLoc"] = $host . "/QSIM";

    $servername = "localhost"; // The server name containing the database
    $username = "lmuir2021";
    $password = "riddles";
    $databaseName = "userlist";

    // Create connection
    $_SESSION['conn'] = new mysqli($servername, $username, $password, $databaseName);

    // Check connection
    if ($_SESSION['conn']->connect_error) {
        die("Connection failed: " . $_SESSION['conn']->connect_error);
    }
}

function validUser(string $pagename, $accessLevel) : bool {
    $myfile = fopen("../pageAccessLevels.pal", "r") or die("Internal server error: Unable to open file!");
    $file = fread($myfile, filesize("../pageAccessLevels.pal"));
    $line = strstr($file, $pagename);
    fclose($myfile);

    $start = strpos($line, ':');
    $end = strpos($line, '|');
    $allowedAccessLevels = substr($line, $start+1, $end-$start-1);

    if (str_contains($allowedAccessLevels, "all")) {
        return TRUE;
    } else if (str_contains($allowedAccessLevels, $accessLevel)) {
        return TRUE;
    }

    return FALSE;
}

function redirectingUnauthUsers(string $pagename) {
    establishConnection();
    if ($_SESSION["currentUserId"] === 0) {
        header("Location: http://" . $_SESSION["websiteLoc"] . "/login/");
        die();
    }
    if (! validUser($pagename, $_SESSION["currentUserAccess"])) {
        header("Location: http://" . $_SESSION["websiteLoc"] . "/home/");
        die();
    }
}

// Potentially useless?????
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

?>