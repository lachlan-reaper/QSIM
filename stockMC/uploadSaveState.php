<?php
require "../databaseMaintenanceFunctions.php";
establishConnection();

function convertCsvToDBSave($dbname) {
    $mfile = fopen("saveState/$dbname.csv", "r");
    
    // Wipe table
    $sql = "DROP TABLE `$dbname`;";
    $results = $_SESSION["conn"]->query($sql);
    if (! $results) {
        echo "bad!<br>";
        echo $_SESSION["conn"]->error;
        echo $sql;
    }
    
    $line = fgets($mfile);
    $cols = csvLineToArr($line);
    // Format cols to SQL
    $colsStr = [];
    $colInfo = [];
    $keySql = "";
    $max = count($cols);
    $i = 0;
    while ($max > $i) {
        $col = csvLineToArr($cols[$i]);
        $cols[$i] = $col;
        $name = $col[0];
        $type = $col[1];
        
        $null = $col[2];
        if ($null == "NO") {
            $null = "NOT NULL";
        } else {
            $null = "";
        }
        
        $default = $col[3];
        if ($default == "") {
            $default = "";
        } else {
            $default = "DEFAULT $default";
        }
        
        $key = $col[4];
        if ($key == "") {
            $key = "";
        } else {
            $keySql = ", PRIMARY KEY (`$name`)";
        }
        
        $colsStr[$i] = "`" . $name . "`";
        $colInfo[$i] = "`$name` $type $null $default";
        $i++;
    }
    $colsStr = implode(", ", $colsStr);
    $colInfo = implode(", ", $colInfo);
    
    $sql = "CREATE TABLE `$dbname` ($colInfo$keySql)";
    $results = $_SESSION["conn"]->query($sql);
    
    // Convert CSV into SQL DB
    $numOfLines = count(file("saveState/$dbname.csv")) - 1;
    while ($numOfLines > 0) {
        $line = fgets($mfile);
        $lineSql = csvLineToArr($line);
        $i = count($lineSql);
        while ($i > 0) { // For each item in the line array, formats the items into a SQL query
            $i--;
            if (is_numeric($lineSql[$i])) {
                if ($cols[$i][0] == "id" or $cols[$i][0] == "serverId" or $cols[$i][0] == "platoon" or $cols[$i][0] == "section" or $cols[$i][0] == "username" or $cols[$i][0] == "company") {
                    $lineSql[$i] = formatVarToSQL($lineSql[$i]);
                }
            } else {
                $lineSql[$i] = formatVarToSQL($lineSql[$i]);
            }
        }
        $lineSql = implode(", ", $lineSql); // Recombines the array back into a SQL safe string

        $sql = "INSERT INTO `$dbname` ($colsStr) VALUES ($lineSql);";
        $results = $_SESSION["conn"]->query($sql);

        if (! $results) { // If the query didn't work
            echo "SQL Query failed due to these reasons:<br>";
            echo $_SESSION["conn"]->error . "<br>";
            echo "The SQL in issue: " . $sql . "<br>";
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
$res =  $zip->open($zipFile);
if ($res === TRUE) {
    $zip->extractTo($zipFolder . '/');
    $zip->close();
} else {
    die("Error: " . $res);
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
unlink("../pageAccessLevels.csv");
unlink("../appointmentAccessRoles.csv");
unlink("../contacts.csv");
copy($zipFolder . '/pageAccessLevels.csv', "../pageAccessLevels.csv");
copy($zipFolder . '/appointmentAccessRoles.csv', "../appointmentAccessRoles.csv");
copy($zipFolder . '/contacts.csv', "../contacts.csv");

// Upload state of DBs
convertCsvToDBSave('users');
convertCsvToDBSave('stock');
convertCsvToDBSave('inventory');
convertCsvToDBSave('equipmentReceipts');

// Delete photos in the zip folder
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
unlink($zipFolder . '/pageAccessLevels.csv');
unlink($zipFolder . '/appointmentAccessRoles.csv');
unlink($zipFolder . '/contacts.csv');
rmdir($zipFolder);

header("Location: http://" . $_SESSION["websiteLoc"] . "/stockMC/");
exit;
?>