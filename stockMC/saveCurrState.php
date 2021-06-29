<?php
require "../databaseMaintenanceFunctions.php";
establishConnection();

// Saving the state of the database
function convertDBToCsvFile ($dbname) {
    $mfile = fopen("$dbname.csv", "w+");
    $cols = [];
    $row = "";

    $sql = "SHOW COLUMNS FROM `$dbname`;";
    $results = $_SESSION["conn"]->query($sql);
    $i = $results->num_rows;
    while ($i > 0) {
        $item = $results->fetch_assoc();
        $name = $item["Field"];
        $cols[] = $name;
        $row = $row . "," . strToCsv($name);
        $i--;
    }
    $row = substr($row, 1) . "\n";
    fwrite($mfile, $row);
    $size = count($cols);

    $sql = "SELECT * FROM `$dbname`;";
    $results = $_SESSION["conn"]->query($sql);
    $i = $results->num_rows;
    while($i > 0) {
        $user = $results->fetch_assoc();

        $row = "";
        $num = 0;
        while ($num < $size) {
            $name = $user[$cols[$num]];
            $row = $row . "," . strToCsv($name);
            $num++;
        }
        
        $row = substr($row, 1) . "\n";
        fwrite($mfile, $row);
        $i--;
    }
    fclose($mfile);
}

convertDBToCsvFile('users');
convertDBToCsvFile('inventory');
convertDBToCsvFile('equipmentReceipts');
convertDBToCsvFile('stock');

$zipFile = "saveState.zip";

// Create a ZIP file and add all of the documents
$zip = new ZipArchive;
$zip->open($zipFile, ZipArchive::CREATE);
$zip->addFile("users.csv");
$zip->addFile("inventory.csv");
$zip->addFile("stock.csv");
$zip->addFile("equipmentReceipts.csv");
$zip->addFile("../pageAccessLevels.pal");
$zip->addFile("../appointmentAccessRoles.aars");
$zip->addFile("../contacts.txt");

// Add all photos
$zip->addEmptyDir('photo');
$handle = opendir("../photo/");
while (false !== ($entry = readdir($handle))) {
    if ($entry != "." && $entry != ".." && !is_dir('../photo/' . $entry)) {
        $zip->addFile('../photo/' . $entry, 'photo/' . $entry);
    }
}
closedir($handle);

// Save
$zip->close();

// Download the File
header('Content-Description: File Transfer');
header('Content-Type: application/zip');
header('Content-Disposition: attachment; filename="'.basename($zipFile).'"');
header('Expires: 0');
header('Cache-Control: must-revalidate');
header('Pragma: public');
header('Content-Length: ' . filesize($zipFile));
flush();
readfile($zipFile);

// Delete the ZIP file and temp files
unlink($zipFile);
unlink('users.csv');
unlink('inventory.csv');
unlink('stock.csv');
unlink('equipmentReceipts.csv');

exit;
?>