<?php
require "../databaseMaintenanceFunctions.php";

$folderPath = "../saveState";
$file = "saveState.zip";

// Create temporary folder
mkdir($folderPath);

// Create Save State files

// Zip the folder
$zip = new ZipArchive;
if ($zip->open($file, ZipArchive::OVERWRITE) === TRUE) {
    if ($handle = opendir($folderPath)) {
        // Add all files inside the directory
        while (false !== ($entry = readdir($handle))) {
            if ($entry != "." && $entry != ".." && !is_dir($folderPath . '/' . $entry)) {
                $zip->addFile($folderPath . '/' . $entry);
            }
        }
        closedir($handle);
    }
    $zip->close();
}

// Download the File
header('Content-Description: File Transfer');
header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename="'.basename($file).'"');
header('Expires: 0');
header('Cache-Control: must-revalidate');
header('Pragma: public');
header('Content-Length: ' . filesize($file));
readfile($file);

// Delete the folder and everything in it
$files = glob($folderPath . '/*'); 
foreach ($files as $file) {
    if (is_file($file)) {    
        unlink($file); 
    }
}
rmdir($folderPath);

exit;
?>