<?php
require "../databaseMaintenanceFunctions.php";
establishConnection();

function convertCsvToDBSave($dbname) {
    $mfile = fopen("saveState/$dbname.csv", "r");
    $line = fgets($mfile);
    $cols = csvLineToArr($line);
    
    // Wipe table
    $sql = "TRUNCATE TABLE `$dbname`;";
    $results = $_SESSION["conn"]->query($sql);
    if (! $results) {
        echo "bad!<br>";
        echo $_SESSION["conn"]->error;
        echo $sql;
    }

    // Format cols to SQL
    $colsStr = $cols;
    $i = count($colsStr);
    while ($i > 0) {
        $i--;
        $colsStr[$i] = "`" . $colsStr[$i] . "`";
    }
    $colsStr = implode(", ", $colsStr);

    // Convert CSV into SQL DB
    $numOfLines = count(file("saveState/$dbname.csv")) - 1;
    while ($numOfLines > 0) {
        $line = fgets($mfile);
        $lineSql = csvLineToArr($line);
        $i = count($lineSql);
        while ($i > 0) {
            $i--;
            if (is_numeric($lineSql[$i])) {
                if ($cols[$i] == "id" or $cols[$i] == "serverId" or $cols[$i] == "platoon" or $cols[$i] == "section" or $cols[$i] == "username" or $cols[$i] == "company") {
                    $lineSql[$i] = formatNullAndStringToSQL($lineSql[$i]);
                }
            } else {
                $lineSql[$i] = formatNullAndStringToSQL($lineSql[$i]);
            }
        }
        $lineSql = implode(", ", $lineSql);
        $sql = "INSERT INTO `$dbname` ($colsStr) VALUES ($lineSql);";
        $results = $_SESSION["conn"]->query($sql);
        if (! $results) {
            echo "bad!<br>";
            echo $_SESSION["conn"]->error;
            echo $sql;
        }
        $numOfLines--;
    }
}

$zipFile = "saveState.zip";
$zipFolder = "saveState";

// Save ZIP file
$zipTMPFile = $_FILES["saveState"]["tmp_name"];
move_uploaded_file($zipTMPFile, $zipFile);

// Unzip file
mkdir($zipFolder);
$zip = new ZipArchive;
if ($zip->open($zipFile) === TRUE) {
    $zip->extractTo($zipFolder . '/');
    $zip->close();
}

// Save photos
$handle = opendir($zipFolder . "/photo/");
while (false !== ($entry = readdir($handle))) {
    if ($entry != "." && $entry != ".." && !is_dir('../photo/' . $entry)) {
        copy($zipFolder . '/photo/' . $entry, '../photo/' . $entry);
    }
}
closedir($handle);

// Overwrite server files
unlink("../pageAccessLevels.pal");
unlink("../appointmentAccessRoles.aars");
unlink("../contacts.txt");
copy($zipFolder . '/pageAccessLevels.pal', "../pageAccessLevels.pal");
copy($zipFolder . '/appointmentAccessRoles.aars', "../appointmentAccessRoles.aars");
copy($zipFolder . '/contacts.txt', "../contacts.txt");

// Upload state of DBs
convertCsvToDBSave('users');
convertCsvToDBSave('stock');
convertCsvToDBSave('inventory');
convertCsvToDBSave('equipmentReceipts');

// Delete photos
$handle = opendir($zipFolder . "/photo/");
while (false !== ($entry = readdir($handle))) {
    if ($entry != "." && $entry != ".." && !is_dir($zipFolder . "/photo/" . $entry)) {
        unlink($zipFolder . "/photo/" . $entry);
    }
}
closedir($handle);
rmdir($zipFolder . "/photo");

// Delete the ZIP file and temp files
unlink($zipFile);
unlink($zipFolder . '/users.csv');
unlink($zipFolder . '/inventory.csv');
unlink($zipFolder . '/stock.csv');
unlink($zipFolder . '/equipmentReceipts.csv');
unlink($zipFolder . '/pageAccessLevels.pal');
unlink($zipFolder . '/appointmentAccessRoles.aars');
unlink($zipFolder . '/contacts.txt');
rmdir($zipFolder);

header("Location: http://" . $_SESSION["websiteLoc"] . "/stockMC/");
exit;
?>