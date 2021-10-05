<?php
    require "../databaseMaintenanceFunctions.php";

    establishConnection();
    $mfile = fopen("searchResult.csv", "w+");

    if (isset($_GET["searchQuery"])) {
        $userQuery = $_GET["searchQuery"];
    } else {
        $userQuery = "";
    }
    
    if (isset($_GET["searchFilters"])) {
        $searchFilters = $_GET["searchFilters"];
        $searchFilters = urldecode($searchFilters);
        $searchFilters = str_replace("-", " ", $searchFilters);
    } else {
        $searchFilters = "";
    }

    // Retrieves an array of the searched for users
    $searchFilters = formatSearchFilters($searchFilters);
    $results = getSearchQueryResults($userQuery, $searchFilters);

    // Writes a formatted row for each searched user into the file
    $row = "id,firstName,lastName,platoon,rank,appointment\n";
    fwrite($mfile, $row);
    $i = $results->num_rows;
    while($i > 0) {
        $user = $results->fetch_assoc();
        $id = strToCsv($user["id"]);
        $fname = strToCsv($user["firstName"]);
        $lname = strToCsv($user["lastName"]);
        $pl = strToCsv($user["platoon"]);
        $rank = strToCsv($user["rank"]);
        $appt = strToCsv($user["appointment"]);

        $row = "$id,$fname,$lname,$pl,$rank,$appt\n";
        fwrite($mfile, $row);
        $i--;
    }

    fclose($mfile);

    // Downloads the File
    $file = "searchResult.csv";
    header('Content-Description: File Transfer');
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="'.basename($file).'"');
    header('Expires: 0');
    header('Cache-Control: must-revalidate');
    header('Pragma: public');
    header('Content-Length: ' . filesize($file));
    readfile($file);

    unlink($file);
    exit;
?>