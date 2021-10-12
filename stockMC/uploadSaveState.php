<?php
require '../databaseMaintenanceFunctions.php';
establishConnection();

function convertCsvToDBSave($dbname) {
    $mfile = fopen('saveState/' . $dbname . '.csv', 'r');
    
    // Wipe table
    $sql = 'DROP TABLE `' . $dbname . '`;';
    $results = $_SESSION['conn']->query($sql);
    if (! $results) {
        echo 'bad!<br>';
        echo $_SESSION['conn']->error;
        echo $sql;
    }
    
    $cols = fgetcsv($mfile);
    // Format cols to SQL
    $colsStr = [];
    $colInfo = [];
    $keySql = '';
    $max = count($cols);
    $i = 0;
    while ($max > $i) {
        $col = str_getcsv($cols[$i]);
        $cols[$i] = $col;
        $name = $col[0];
        $type = $col[1];
        
        $null = $col[2];
        if ($null === 'NO') {
            $null = 'NOT NULL';
        } else {
            $null = '';
        }
        
        $default = $col[3];
        if ($default !== '') {
            $default = 'DEFAULT ' . $default;
        }
        
        $key = $col[4];
        if ($key !== '') {
            $keySql = ', PRIMARY KEY (`' . $name . '`)';
        }
        
        $extra = $col[5];
        
        $colsStr[$i] = '`' . $name . '`';
        $colInfo[$i] = '`' . $name . '` ' . $type . ' ' . $null . ' ' . $default . ' ' . $extra;

        $i++;
    }

    $colsStr = implode(', ', $colsStr);
    $colInfo = implode(', ', $colInfo);
    
    $sql = 'CREATE TABLE `' . $dbname . '` (' . $colInfo . $keySql . ')';
    $results = $_SESSION['conn']->query($sql);
    
    
    // Convert CSV into SQL DB
    $numOfLines = count(file('saveState/' . $dbname . '.csv')) - 1;
    $lineSql = '';
    $chunkCount = 0;
    while ($numOfLines > 0) {
        $lineArr = fgetcsv($mfile);

        if (!$lineArr) {
            break;
        }

        if ($chunkCount > 50) { // Used to optimize server performance
            $lineSql = substr($lineSql, 0, -2);

            $sql = 'INSERT INTO `' . $dbname . '` (' . $colsStr . ') VALUES ' . $lineSql . ';';
            $results = $_SESSION['conn']->query($sql);

            $lineSql = '';
            $chunkCount = 0;

            if (!$results) { // If the query didn't work
                echo 'SQL Query failed due to these reasons:<br>';
                echo $_SESSION['conn']->error . '<br>';
                echo 'The SQL in issue: ' . $sql . '<br>';
            }
        }

        $i = count($lineArr);
        while ($i > 0) { // For each item in the line array, formats the items into a SQL query
            $i--;
            if (is_numeric($lineArr[$i])) {
                $col = $cols[$i][0];

                if ($col === 'id' or $col === 'serverId' or $col === 'platoon' or $col === 'section' or $col === 'username' or $col === 'company') {
                    $lineArr[$i] = formatVarToSQL($lineArr[$i]);
                }
            } else {
                $lineArr[$i] = formatVarToSQL($lineArr[$i]);
            }
        }

        $lineArr = implode(', ', $lineArr); // Recombines the array back into a SQL safe string
        $lineSql = $lineSql . '(' . $lineArr . '), ';
        
        $numOfLines--;
        $chunkCount++;        
    }
    
    $lineSql = substr($lineSql, 0, -2);
    $sql = 'INSERT INTO `' . $dbname . '` (' . $colsStr . ') VALUES ' . $lineSql . ';';
    $results = $_SESSION['conn']->query($sql);

    if (!$results) { // If the query didn't work
        echo 'SQL Query failed due to these reasons:<br>';
        echo $_SESSION['conn']->error . '<br>';
        echo 'The SQL in issue: ' . $sql . '<br>';
    }
}

$zipFile = 'saveState.zip';
$zipFolder = 'saveState';

// Save ZIP file
$zipTMPFile = $_FILES['saveState']['tmp_name'];
move_uploaded_file($zipTMPFile, $zipFile);

// Unzip file
mkdir($zipFolder);
$zip = new ZipArchive;
$res =  $zip->open($zipFile);
if ($res === true) {
    $zip->extractTo($zipFolder . '/');
    $zip->close();
} else {
    die('Error: ' . $res);
}

// Save photos
$handle = opendir($zipFolder . '/photo/');
while (false !== ($entry = readdir($handle))) {
    if ($entry !== '.' and $entry !== '..' and !is_dir('../photo/' . $entry)) {
        copy($zipFolder . '/photo/' . $entry, '../photo/' . $entry);
    }
}
closedir($handle);

// Save unitStructure
$handle = opendir($zipFolder . '/unitStructure/');
while (false !== ($entry = readdir($handle))) {
    if ($entry !== '.' and $entry !== '..' and !is_dir('../unitStructure/' . $entry)) {
        copy($zipFolder . '/unitStructure/' . $entry, '../unitStructure/' . $entry);
    }
}
closedir($handle);

// Overwrite server files
unlink('../pageAccessLevels.csv');
unlink('../appointmentAccessRoles.csv');
unlink('../contacts.csv');
copy($zipFolder . '/pageAccessLevels.csv', '../pageAccessLevels.csv');
copy($zipFolder . '/appointmentAccessRoles.csv', '../appointmentAccessRoles.csv');
copy($zipFolder . '/contacts.csv', '../contacts.csv');

// Upload state of DBs
convertCsvToDBSave('users');
convertCsvToDBSave('stock');
convertCsvToDBSave('inventory');
convertCsvToDBSave('equipmentReceipts');


// Delete photos in the zip folder
$handle = opendir($zipFolder . '/photo/');
while (false !== ($entry = readdir($handle))) {
    if ($entry !== '.' and $entry !== '..' and !is_dir($zipFolder . '/photo/' . $entry)) {
        unlink($zipFolder . '/photo/' . $entry);
    }
}
closedir($handle);
rmdir($zipFolder . '/photo');

$handle = opendir($zipFolder . '/unitStructure/');
while (false !== ($entry = readdir($handle))) {
    if ($entry !== '.' and $entry !== '..' and !is_dir($zipFolder . '/unitStructure/' . $entry)) {
        unlink($zipFolder . '/unitStructure/' . $entry);
    }
}
closedir($handle);
rmdir($zipFolder . '/unitStructure');

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

header('Location: http://' . $_SESSION['websiteLoc'] . '/stockMC/');
exit;
?>