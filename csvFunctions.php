<?php

function commaRemoval($matches) : string{
    // Reformats csv lines into strings without actual commas that would otherwise interrupt the csv formatting process
    $line = $matches[0];
    $line = str_replace('"', '', $line);
    $line = str_replace(',', '&comm;', $line);
    return $line;
}

function csvLineToArr (string $line) : array {
    // 1. Replace all [""] with [&dbqt;]
    // 2. Preg-replace [/"(.*),(.*)"/] with [$1&comm;$2]
    // 3. Explode the string with [,]
    // 4. Iterate through the array, replacing each [&dbqt;] and [&comm;] with ["] and [,] respectively
    
    $line = str_replace('""', '&dbqt;', $line);

    // Replaces commas inside of a pair of double quotes
    $line = preg_replace_callback('|"[^"]+"|', 'commaRemoval', $line);
    
    $arr = explode(",", $line);
    $i = count($arr);
    $parameters = ['&comm;', '&dbqt;'];
    $replacements = [',', '"'];
    while ($i > 0) { // For each item in the new array, replace the substituting identifiers into their respective characters
        $i--;
        $arr[$i] = str_replace($parameters, $replacements, $arr[$i]);
    }

    $arr[count($arr)-1] = trim($arr[count($arr)-1], " \t\n\r");

    return $arr;
}

function csvFileToArr2D (string $file) : array {
    $file = explode("\n", $file);

    $i = count($file);
    while ($i > 0) {
        $i--;
        $file[$i] = csvLineToArr($file[$i]);
    }
    
    return $file;
}

function strToCsv (string $line) : string {
    // Converts a str into a csv safe format, does only one item at a time
    $line = str_replace('"', '""', $line);
    if (str_contains($line, ",")) {
        $line = '"' . $line . '"';
    } else if (substr($line, 0, 1) == "0") {
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