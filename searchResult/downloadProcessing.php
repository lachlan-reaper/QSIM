<?php
    require "../databaseMaintenanceFunctions.php";

    establishConnection();
    $mfile = fopen("searchResult.csv", "w+");

    $userQuery = $_GET["searchQuery"];
    $searchFilters = str_replace("-", " ", urldecode($_GET["searchFilters"]));

    if ($userQuery == NULL) {
        $userQuery = "";
    }
    if ($searchFilters == NULL) {
        $searchFilters = "";
    }

    $searchFilters = formatSearchFilters($searchFilters);
    $results = retrieveSearchQueryResults($userQuery, $searchFilters);

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