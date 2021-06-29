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

$sql1 = "CREATE TABLE users (
    id varchar(12) NOT NULL,
    firstName varchar(32) NOT NULL,
    lastName varchar(32) NOT NULL,
    username varchar(32) NOT NULL,
    userpass varchar(60) NOT NULL,
    access varchar(12) NOT NULL,
    rank varchar(6) NOT NULL,
    appointment varchar(20) NOT NULL,
    yearLevel int(12) NOT NULL,
    company varchar(10) NOT NULL,
    platoon varchar(10) NOT NULL,
    section varchar(10) NOT NULL,
    PRIMARY KEY (id)
);";
$sql2 = "CREATE TABLE `inventory` (
    `id` varchar(12) NOT NULL,
    `DPCU Pants` int(6) NOT NULL DEFAULT 0,
    `DPCU Shirt` int(6) NOT NULL DEFAULT 0,
    `Black Belt` int(6) NOT NULL DEFAULT 0,
    `Belt Brass` int(6) NOT NULL DEFAULT 0,
    `DPCU Field Hat` int(6) NOT NULL DEFAULT 0,
    `Khaki Fur Felt` int(6) NOT NULL DEFAULT 0,
    `Cadet Jumper` int(6) NOT NULL DEFAULT 0,
    `Cadet Boots` int(6) NOT NULL DEFAULT 0,
    `ANF Patch` int(6) NOT NULL DEFAULT 0,
    `AAC Patch` int(6) NOT NULL DEFAULT 0,
    `2nd BDE Patch` int(6) NOT NULL DEFAULT 0,
    `Hutchie` int(6) NOT NULL DEFAULT 0,
    `Poncho` int(6) NOT NULL DEFAULT 0,
    `Duffle Bag` int(6) NOT NULL DEFAULT 0,
    `Kidney Cup` int(6) NOT NULL DEFAULT 0,
    `Water Bottle` int(6) NOT NULL DEFAULT 0,
    PRIMARY KEY (`id`)
);";
$sql3 = "CREATE TABLE stock (
    item varchar(32) NOT NULL,
    total int(12) NOT NULL DEFAULT 0,
    onShelf int(12) NOT NULL DEFAULT 0,
    onLoan int(12) NOT NULL DEFAULT 0,
    lostOrDamaged int(12) NOT NULL DEFAULT 0,
    PRIMARY KEY (item)
);";
$sql4 = "CREATE TABLE equipmentReceipts (
    receiptNum int(12) NOT NULL AUTO_INCREMENT,
    id varchar(12) NOT NULL,
    serverId varchar(12) NOT NULL,
    item varchar(32) NOT NULL,
    changeInNum int(6) NOT NULL,
    lostOrDamaged int(1) NOT NULL DEFAULT 0,
    time datetime NOT NULL,
    PRIMARY KEY (receiptNum)
);";

$result = $conn->query($sql1);
if ($result == TRUE) {
    echo "Good - 1";
} else {
    echo "Bad  - 1";
}
echo "<br>";
$result = $conn->query($sql2);
if ($result == TRUE) {
    echo "Good - 2";
} else {
    echo "Bad  - 2";
}
echo "<br>";
$result = $conn->query($sql3);
if ($result == TRUE) {
    echo "Good - 3";
} else {
    echo "Bad  - 3";
}
echo "<br>";
$result = $conn->query($sql4);
if ($result == TRUE) {
    echo "Good - 4";
} else {
    echo "Bad  - 4";
}
echo "<br>";

// This allows for the initial access via the website and not needing developer access in order to populate the server
require '../databaseFunctions.php';
establishConnection();
addUser("Admin", "Admin", "4242424242", "Admin", "Admin", "MAJ", "commander", "RHQ", "RHQ", "", 0);

$sql = "INSERT INTO `inventory` (`id`) VALUES ('stdIssue');INSERT INTO `inventory` (`id`) VALUES ('RECIssue');INSERT INTO `inventory` (`id`) VALUES ('AFXIssue');INSERT INTO `inventory` (`id`) VALUES ('customIssue');";
if ($_SESSION['conn']->multi_query($sql) === TRUE) {
    echo "Standard issue records created successfully <br>";
} else {
    echo "Error: " . $sql . "<br>" . $_SESSION['conn']->error . "<br>";
}

?>