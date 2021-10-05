<?php

function getCsvFile ($fileHandle) : array {
    $file = [];
    $line = fgetcsv($fileHandle);
    while ($line !== false) {
        $file[] = $line;
        $line = fgetcsv($fileHandle);
    }
    return $file;
}

function csvFileToArr2D (string $file) : array {
    $file = explode("\n", $file);

    $i = count($file);
    while ($i > 0) {
        $i--;
        $file[$i] = str_getcsv($file[$i]);
    }
    
    return $file;
}

function strToCsv ($line) : string {
    // Converts a var into a csv safe format, does only one item at a time
    if ($line === NULL) {
        return "";
    }

    $line = str_replace('"', '""', $line);
    if (str_contains($line, ",") or str_contains($line, "\n") or substr($line, 0, 1) == "0" or substr($line, 0, 1) == " " or substr($line, -1, 1) == " ") {
        $line = '"' . $line . '"';
    }
    
    return $line;
}

function arrToCsvLine (array $arr) : string {
    // Converts a one-dimensional numerically indexed array into a single .csv formatted line
    $csvLine = "";
    
    $num = count($arr);
    $i = 0;
    while ($num > $i) {
        $csvStr = strToCsv($arr[$i]);
        $csvLine = $csvLine . "," . $csvStr;
        $i++;
    }

    $csvLine = substr($csvLine, 1); // Remove initial comma
    $csvLine = $csvLine . "\n";

    return $csvLine;
}

function arr2DToCsvFile (array $arr) : string {
    // Converts a two-dimensional numerically indexed array into a single .csv formatted line
    $csvFile = "";
    
    $num = count($arr);
    $i = 0;
    while ($num > $i) {
        $csvStr = arrToCsvLine($arr[$i]);
        $csvFile = $csvFile . $csvStr;
        $i++;
    }

    $len = strlen($csvFile);
    $csvFile = substr($csvFile, 0, $len-1);

    return $csvFile;
}

?>