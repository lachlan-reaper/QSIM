<?php

require 'csvFunctions.php';

// Unit Structure
function getContacts () : array {
    // Returns an array of the the appointments and contacts for Q Store from the contacts.csv file indexed by their appointment
    $myfile = fopen("../contacts.csv", "r") or die("Internal server error: Unable to open file!");
    $file = fread($myfile, filesize("../contacts.csv"));
    $lines = csvFileToArr2D($file);
    $contacts = [];

    // Iterates through all of the lines of contacts in the file
    $i = count($lines);
    while ($i > 0) {
        $i--;
        $contacts[$lines[$i][0]] = $lines[$i][1];
    }

    fclose($myfile);
    return $contacts;
}

function getCompanies () : array {
    // Returns a one dimesional array of all companies
    $myfile = fopen("../unitStructure/COY-PL.csv", "r") or die("Internal server error: Unable to open file!");
    $file = fread($myfile, filesize("../unitStructure/COY-PL.csv"));
    $lines = csvFileToArr2D($file);
    $companies = [];

    $i = count($lines);
    while ($i > 0) {
        $i--;
        $companies[$i] = $lines[$i][0];
    }
    
    fclose($myfile);
    return $companies;
}

function getCompanyStructure () : array {
    // Returns a two-dimensional array indexed to each of the coys
    $myfile = fopen("../unitStructure/COY-PL.csv", "r") or die("Internal server error: Unable to open file!");
    $file = fread($myfile, filesize("../unitStructure/COY-PL.csv"));
    $lines = csvFileToArr2D($file);
    $structure = [];

    $i = count($lines);
    while ($i > 0) {
        $i--;
        $coy = $lines[$i][0];
        $pls = $lines[$i];
        array_splice($pls, 0, 1);
        $structure[$coy] = $pls;
    }
    
    fclose($myfile);
    return $structure;
}

function getPlatoons (string $company=NULL) : array {
    // Returns a one dimensional array of all the plattons, limited to just the company if the argument is provided
    $myfile = fopen("../unitStructure/COY-PL.csv", "r") or die("Internal server error: Unable to open file!");
    $file = fread($myfile, filesize("../unitStructure/COY-PL.csv"));
    $lines = csvFileToArr2D($file);
    $platoons = [];
    
    $coys = count($lines);
    $i = 0;
    $num = 0;
    if ($company === NULL) {
        while ($coys > $i) {
            $pls = count($lines[$i]);
            $x = 1; // First in the line is the coy not a platoon
            while ($pls > $x) {
                $platoons[$num] = $lines[$i][$x];
                $num++;
                $x++;
            }
            $i++;
        }
    } else if (gettype($company) == "string") {
        while ($coys > $i) {
            if (!($lines[$i][0] == $company)) {
                $i++;
                continue;
            }

            $pls = count($lines[$i]);
            $x = 1; // First in the line is the coy not a platoon
            while ($pls > $x) {
                $platoons[$num] = $lines[$i][$x];
                $num++;
                $x++;
            }
            $i++;
        }
    } else {
        die("Error! The provided company argument was not a string.");
    }
    
    fclose($myfile);
    return $platoons;
}

function getAppointments (bool $giveAccess=true) : array {
    $myfile = fopen("../appointmentAccessRoles.csv", "r") or die("Internal server error: Unable to open file!");
    $file = fread($myfile, filesize("../appointmentAccessRoles.csv"));
    $lines = csvFileToArr2D($file);

    if ($giveAccess) {
        $appts = $lines;
    } else {
        $max = count($lines);
        $i = 0;
        while ($max > $i) {
            $appts[$i] = $lines[$i][0];
            $i++;
        }
    }

    fclose($myfile);
    return $appts;
}

function getPlatoonStructure (string $platoon=NULL) : array {
    // Returns a two-dimensional array if no argument is provided, returns a one-dimensional array if an argument is given.
    $myfile = fopen("../unitStructure/PLsStructure.csv", "r") or die("Internal server error: Unable to open file!");
    $file = fread($myfile, filesize("../unitStructure/PLsStructure.csv"));

    if ($platoon === NULL) {
        $lines = csvFileToArr2D($file);
        $max = count($lines);
        $i = 0;
        while ($max > $i) {
            $pl = $lines[$i][0];
            $struct = $lines[$i];
            array_splice($struct, 0, 1);
            $structure[$pl] = $struct;
            $i++;
        }
    } else if (gettype($platoon) == "string") {
        $structure = csvFileToArr2D($file);
        $i = count($structure);
        while ($i > 0) {
            $i--;
            if ($structure[$i][0] == $platoon) {
                $structure = $structure[$i];
                array_splice($structure, 0, 1);
                break;
            }
        }
    } else {
        die("Error! The provided platoon argument was not a string.");
    }

    fclose($myfile);
    return $structure;
}

function getRanks () : array {
    $myfile = fopen("../unitStructure/ranks.csv", "r") or die("Internal server error: Unable to open file!");
    $file = fread($myfile, filesize("../unitStructure/ranks.csv"));

    $lines = csvFileToArr2D($file);
    fclose($myfile);

    $ranks = $lines[0];
    return $ranks;
}

function getYears () : array {
    $myfile = fopen("../unitStructure/years.csv", "r") or die("Internal server error: Unable to open file!");
    $file = fread($myfile, filesize("../unitStructure/years.csv"));

    $lines = csvFileToArr2D($file);
    fclose($myfile);

    $years = $lines[0];
    return $years;
}

?>