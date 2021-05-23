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

function validUser(string $pagename, string $accessLevel) : bool {
    $myfile = fopen("../pageAccessLevels.pal", "r") or die("Internal server error: Unable to open file! JAck");
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

function formatProfileBox(string $strHTMLFile) : string {
    session_start();
    $firstname = ucfirst($_SESSION["currentUserFirstname"]);
    $strHTMLFile = str_replace('FIRSTNAME', $firstname, $strHTMLFile);

    $lastname = strtoupper($_SESSION["currentUserLastname"]);
    $strHTMLFile = str_replace('LASTNAME', $lastname, $strHTMLFile);

    $appointment = strtoupper($_SESSION["currentUserAppointment"]);
    $strHTMLFile = str_replace('APPOINTMENT', $appointment, $strHTMLFile);

    $rank = strtoupper($_SESSION["currentUserRank"]);
    $strHTMLFile = str_replace('RANK', $rank, $strHTMLFile);

    return $strHTMLFile;
}

function formatNavbarToUserAccess (string $strHTMLFile, string $accessLevel) : string {
    if (! validUser("search", $accessLevel)) {
        $strHTMLFile = preg_replace('#<li id="searchTabList">(.*?)</li>#', '', $strHTMLFile);
    }
    if (! validUser("stock", $accessLevel)) {
        $strHTMLFile = preg_replace('#<li id="stockTabList">(.*?)</li>#', '', $strHTMLFile);
    }
    return $strHTMLFile;
}

function displayHeader() {
    // This could be optimised to save performance at the expense of hand writing the code or saving it in a session variable 
    // however this allows for easy customisation and performance is not too much of an issue anyways.
    $header = fopen("../headerFormat.html", "r") or die("Unable to open file!");
    $file = fread($header,filesize("../headerFormat.html"));
    $file = formatProfileBox($file);
    $file = formatNavbarToUserAccess($file, $_SESSION["currentUserAccess"]);
    echo $file;
    fclose($header);
}

function redirectingUnauthUsers(string $pagename) {
    establishConnection();
    if ($_SESSION["currentUserId"] === 0 or $_SESSION["currentUserId"] === NULL) {
        header("Location: http://" . $_SESSION["websiteLoc"] . "/login/");
        die();
    }
    if (! validUser($pagename, $_SESSION["currentUserAccess"])) {
        header("Location: http://" . $_SESSION["websiteLoc"] . "/home/");
        die();
    }
}

?>