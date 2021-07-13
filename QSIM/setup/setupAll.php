<?php
    date_default_timezone_set('Australia/Sydney');
    $servername = "localhost"; // The server name containing the database
    $username = "root"; // The usernme of root connecting from localhost is by default an admin level account

    $databaseName = "QSIMDB";
    
    // Create connection
    $conn = new mysqli($servername, $username);
    
    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $sql = "CREATE DATABASE `$databaseName`;";
    
    $result = $conn->query($sql);
    if ($result == TRUE) {
        echo "Good";
    } else {
        echo "Bad";
        die();
    }
    echo "<br>";

    // Creates the user that all of the remote connections require
    $sql = "CREATE USER lmuir2021 IDENTIFIED BY 'riddles';
    GRANT SELECT, INSERT, UPDATE, DELETE, CREATE, ALTER, INDEX, DROP ON `$databaseName`.* TO lmuir2021;";
    
    $result = $conn->multi_query($sql);
    if ($result == TRUE) {
        echo "Good";
    } else {
        echo "Bad";
        die($sql);
    }
    echo "<br>";

    // Imports and executes each of these scripts. Effectively replaces these lines with the entirety of their respective files.
    require 'setupDatabase.php';
    require 'setupStockTable.php';
?>