<?php

$servername = "localhost"; // The server name containing the database
$username = "lmuir2021";
$password = "riddles";
$databaseName = "QSIMDB";

// Create connection
$conn = new mysqli($servername, $username, $password, $databaseName);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Add all of the initial default items
$sql = "INSERT INTO `stock` (`item`) VALUES 
    ('DPCU Pants'),
    ('DPCU Shirt'),
    ('Black Belt'),
    ('Belt Brass'),
    ('DPCU Field Hat'),
    ('Khaki Fur Felt'),
    ('Cadet Jumper'),
    ('Cadet Boots'),
    ('ANF Patch'),
    ('AAC Patch'),
    ('2nd BDE Patch'),
    ('Hutchie'),
    ('Poncho'),
    ('Duffle Bag'),
    ('Kidney Cup'),
    ('Water Bottle');";

$result = $conn->query($sql);
if ($result == TRUE) {
    echo "Good";
} else {
    echo "Bad";
}

?>