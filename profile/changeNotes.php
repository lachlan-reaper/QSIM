<?php

require '../databaseFunctions.php';

establishConnection();

$id = $_POST["id"];
$notes = $_POST["notes"];

$idSql = formatVarToSQL($id);

$notes = formatVarToSQL($notes);

$sql = "UPDATE `users` SET `notes` = $notes WHERE `id` = $idSql;";
$result = $_SESSION['conn'] -> query($sql);

header("Location: http://" . $_SESSION["websiteLoc"] . "/profile/?id=" . $id);

?>