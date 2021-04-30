<?php

function establishConnection() {
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

?>