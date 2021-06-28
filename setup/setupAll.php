<?php
    date_default_timezone_set('Australia/Sydney');
    $servername = "localhost"; // The server name containing the database
    $username = "root";

    $databaseName = "QSIMDB";
    
    // Create connection
    $conn = new mysqli($servername, $username);
    
    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    // NEED TO ADD THE lmuir2021 USER WITH riddles PASSWORD!!!!

    $sql = "CREATE DATABASE `$databaseName`;";
    
    $result = $conn->query($sql);
    if ($result == TRUE) {
        echo "Good";
    } else {
        echo "Bad";
        die();
    }
    echo "<br>";

    $sql = "CREATE USER lmuir2021 IDENTIFIED BY 'riddles';
    GRANT SELECT, INSERT, UPDATE, DELETE, CREATE, ALTER, INDEX ON `$databaseName`.* TO lmuir2021;";
    
    $result = $conn->multi_query($sql);
    if ($result == TRUE) {
        echo "Good";
    } else {
        echo "Bad";
        die($sql);
    }
    echo "<br>";

    require 'setupDatabase.php';
    require 'setupStockTable.php';
?>