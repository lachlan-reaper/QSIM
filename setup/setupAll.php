<?php
    $servername = "localhost"; // The server name containing the database
    $username = "admin";
    $password = "secret";

    $databaseName = "QSIMDB";
    
    // Create connection
    $conn = new mysqli($servername, $username, $password);
    
    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    // NEED TO ADD THE lmuir2021 USER WITH riddles PASSWORD!!!!

    $sql = "CREATE DATABASE '$databaseName';";
    
    $result = $conn->query($sql);
    if ($result == TRUE) {
        echo "Good";
    } else {
        echo "Bad";
    }

    require 'setupDatabase.php';
    require 'setupStockTable.php';
?>