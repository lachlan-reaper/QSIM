<?php
date_default_timezone_set('Australia/Sydney');

require 'fileFunctions.php';

// NEW
function establishConnection () {
    // Establishes a connection with the server and sets up any necessary $SESSION variables
    $host = $_SERVER['HTTP_HOST'];
    $_SESSION["websiteLoc"] = $host . "/QSIM";

    $servername = "localhost"; // The server name containing the database
    $username = "lmuir2021";
    $password = "riddles";
    $databaseName = "QSIMDB";

    // Create connection
    $_SESSION['conn'] = new mysqli($servername, $username, $password, $databaseName);

    // Check connection
    if ($_SESSION['conn']->connect_error) {
        die("Connection failed: " . $_SESSION['conn']->connect_error);
    }
}

function userIdentification (string $username, string $password) {
    $sql = "SELECT `userpass`, `id`, `access` FROM `users` WHERE `username` = '$username'";
    $result = $_SESSION['conn'] -> query($sql);

    $num = $result->num_rows;
    $idResult = NULL;
    
    if ($num >= 1) {
        while ($num > 0) {
            $row = $result->fetch_assoc();

            if (password_verify($password, $row["userpass"])) {
                if ($idResult != NULL) {
                    die("Error: Duplicate Username and Password.");
                }
                $idResult = $row["id"];
            }

            $num--;
        }
    }

    return $idResult;
}

// File page access
function validUser (string $pagename, string $accessLevel) : bool {
    // Given the page name and user's access level, will return a boolean value signalling whether the user has access to this page.
    $myfile = fopen("../pageAccessLevels.csv", "r") or die("Internal server error: Unable to open file!");
    $file = fread($myfile, filesize("../pageAccessLevels.csv"));
    $lines = csvFileToArr2D($file);
    fclose($myfile);

    $max = count($lines);
    $i = 0;
    while (!($lines[$i][0] == $pagename)) {
        $i++;
        if ($i >= $max) {
            return FALSE;
        }
    }

    if ($lines[$i][1] == "all") {
        // If the access restriction has been defined as "all" then anyone is allowed to access this page.
        return TRUE;
    } else {
        $num = count($lines[$i]);
        $x = 1;
        while ($num > $x) {
            if ($lines[$i][$x] == $accessLevel) {
                return TRUE;
            }
            $x++;
        }
        return FALSE;
    }
}

function redirectingUnauthUsers (string $pagename) {
    // Given the page name, it will check if the current user has access to the webpage.
    establishConnection();
    if ($_SESSION["currentUserId"] === 0 or $_SESSION["currentUserId"] === NULL) {
        // If the user has not logged in.
        header("Location: http://" . $_SESSION["websiteLoc"] . "/login/");
        die();
    }
    if (! validUser($pagename, $_SESSION["currentUserAccess"])) {
        // If the user does not have access to the current page they are trying to access, this will redirect them to the home page.
        header("Location: http://" . $_SESSION["websiteLoc"] . "/home/");
        die();
    }
}

// Profile
function getProfilePicture (string $id) : string {
    $folder = "../photo/";
    
    if (file_exists($folder . $id . ".jpg")) {
        $filename = $folder . $id . ".jpg";
    } else if (file_exists($folder . $id . ".jpeg")) {
        $filename = $folder . $id . ".jpeg";
    } else if (file_exists($folder . $id . ".png")) {
        $filename = $folder . $id . ".png";
    } else if (file_exists($folder . $id . ".img")) {
        $filename = $folder . $id . ".img";
    } else {
        $filename = "../images/defaultAvatar.png";
    }

    return $filename;
}

function formatProfileBox (string $strHTMLFile) : string {
    // Given the HTML format of the Profile Box in theupper left of the screen, it will output the HTML lines of the box as customised to the user.
    $firstname = ucfirst($_SESSION["currentUserFirstName"]);
    $strHTMLFile = str_replace('FIRSTNAME', $firstname, $strHTMLFile);

    $lastname = strtoupper($_SESSION["currentUserLastName"]);
    $strHTMLFile = str_replace('LASTNAME', $lastname, $strHTMLFile);

    $appointment = strtoupper($_SESSION["currentUserAppointment"]);
    $strHTMLFile = str_replace('APPOINTMENT', $appointment, $strHTMLFile);

    $rank = strtoupper($_SESSION["currentUserRank"]);
    $strHTMLFile = str_replace('RANK', $rank, $strHTMLFile);

    $fileName = getProfilePicture($_SESSION["currentUserId"]);
    $strHTMLFile = str_replace('FILE', $fileName, $strHTMLFile);

    return $strHTMLFile;
}

function formatNavbarToUserAccess (string $strHTMLFile, string $accessLevel) : string {
    // Given the HTML format of the navbar, it will display tabs in the navbar if the user can access the specific page.
    if (! validUser("search", $accessLevel)) {
        $strHTMLFile = preg_replace('#<li id="searchTabList">(.*?)</li>#', '', $strHTMLFile);
    }
    if (! validUser("stock", $accessLevel)) {
        $strHTMLFile = preg_replace('#<li id="stockTabList">(.*?)</li>#', '', $strHTMLFile);
    }
    return $strHTMLFile;
}

function displayHeader () {
    // This reads the HTML file that contains the format for the entire Header and displays it.

    // This could be optimised to save performance at the expense of hand writing the code or saving it in a session variable 
    // however this allows for easy customisation and performance is not too much of an issue anyways.
    $header = fopen("../headerFormat.html", "r") or die("Unable to open file!");
    $file = fread($header,filesize("../headerFormat.html"));
    $file = formatProfileBox($file);
    $file = formatNavbarToUserAccess($file, $_SESSION["currentUserAccess"]);
    echo $file;
    fclose($header);
}

?>