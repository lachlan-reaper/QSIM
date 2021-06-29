<?php

require '../databaseMaintenanceFunctions.php';
session_start();
redirectingUnauthUsers("stockMC");

$function = $_GET["function"];

if ($function == "fileAddUsers") {
    fileAddUsers();
} else if ($function == "fileRemoveUsers") {
    fileRemoveUsers();
} else if ($function == "fileUpdateUsers") {
    fileUpdateUsers();
} if ($function == "fileIssueStock") {
    fileIssueStock();
} else if ($function == "fileReturnStock") {
    fileReturnStock();
} else if ($function == "fileLostStock") {
    fileLostStock();
} else {
    die("I SPECIFICALLY SAID DON'T TOUCH THE URL! <br><br><i>Gosh.... Kids these days....</i>"); // Easter Egg
}

header("Location: http://" . $_SESSION["websiteLoc"] . "/stockMC/");

function arr2dSort ($a, $b) {
    $arr = array($a[0], $b[0]);
    $arrS = $arr;
    sort($arrS);
    if ($a[0] == $b[0]) {
        return 0;
    } else if ($arr === $arrS) {
        return -1;
    } else {
        return 1;
    }
}

function fileAddUsers () {
    $userInfo = $_FILES["userInfo"]["tmp_name"];
    move_uploaded_file($userInfo, "tmp.csv");
    
    $userParameterArray = array();
    $userParameterArrayIndexes = array();
    $file = fopen("tmp.csv", "r");
    $firstLine = fgets($file);
    $firstLine = csvLineToArr($firstLine);

    $results = retrieveAllUserColumns();
    $i = $results->num_rows;
    while ($i > 0) {
        $i--;
        $item = $results->fetch_assoc();
        $name = $item["Field"];
        $DBname = $name;
        
        // Converting behind the scenes variables to more relevant common words.
        if ($name == "userpass") {
            $name = "Password";
        } else if ($name == "access") {
            continue;
        } else {
            $name = preg_replace("/[A-Z]/", " $0", $name); // Adds a space in front of every capital letter, since variables in the DB are camel case.
            $name = ucfirst($name);
        }
        $userParameterArray[] = $DBname;
        $userParameterArrayIndexes[$DBname] = array_search($name, $firstLine);
    }
    

    $numOfLines = count(file("tmp.csv")) - 1; // -1 is to not include the first line
    $lineNum = 0;
    while ($lineNum < $numOfLines) {
        $line = fgets($file);
        $line = csvLineToArr($line);

        $i = 0;
        $max = count($userParameterArray);
        while ($i < $max) {
            $name = $userParameterArray[$i];
            $index = $userParameterArrayIndexes[$name];
            $value = $line[$index];
            $userValue[$name] = $value;
            $i++;
        }

        addUserArr($userValue);

        $lineNum++;
    }

    unlink("tmp.csv");

    $i = 0;
    $num = count($_FILES["userPhotos"]["name"]);
    while ($i < $num) {
        $userPhotoName = $_FILES["userPhotos"]["name"][$i];
        $userPhoto = $_FILES["userPhotos"]["tmp_name"][$i];
        move_uploaded_file($userPhoto, "../photo/$userPhotoName");
        $i++;
    }
}

function fileRemoveUsers () {
    $userInfo = $_FILES["userInfo"]["tmp_name"];
    move_uploaded_file($userInfo, "tmp.csv");
    
    $userParameterArray = array();
    $userParameterArrayIndexes = array();
    $file = fopen("tmp.csv", "r");
    $firstLine = fgets($file);
    $firstLine = csvLineToArr($firstLine);
    
    $userParameterArray[] = "id";
    $userParameterArrayIndexes["id"] = array_search("Id", $firstLine);
    $userParameterArray[] = "firstName";
    $userParameterArrayIndexes["firstName"] = array_search("First Name", $firstLine);
    $userParameterArray[] = "lastName";
    $userParameterArrayIndexes["lastName"] = array_search("Last Name", $firstLine);

    $numOfLines = count(file("tmp.csv")) - 1; // -1 is to not include the first line
    $lineNum = 0;
    while ($lineNum < $numOfLines) {
        $line = fgets($file);
        $line = csvLineToArr($line);

        $i = 0;
        $max = count($userParameterArray);
        while ($i < $max) {
            $name = $userParameterArray[$i];
            $index = $userParameterArrayIndexes[$name];
            $value = $line[$index];
            $userValue[$name] = $value;
            $i++;
        }

        removeUserArr($userValue);

        $id = $userValue["id"];
        $filename = "../photo/$id.jpg";
        if (file_exists($filename)) {
            unlink($filename);
        }

        $lineNum++;
    }
    unlink("tmp.csv");
}

function fileUpdateUsers () { // MAKE IT ACCEPT NAMES INSTEAD OF ID ONLY !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
    $userInfo = $_FILES["userInfo"]["tmp_name"];
    move_uploaded_file($userInfo, "tmp.csv");
    
    $userParameterArray = array();
    $userParameterArrayIndexes = array();
    $file = fopen("tmp.csv", "r");
    $firstLine = fgets($file);
    $firstLine = csvLineToArr($firstLine);

    $results = retrieveAllUserColumns();
    $i = $results->num_rows;
    while ($i > 0) {
        $i--;
        $item = $results->fetch_assoc();
        $name = $item["Field"];
        $DBname = $name;
        
        // Converting behind the scenes variables to more relevant common words.
        if ($name == "userpass") {
            $name = "password";
        } else if ($name == "access") {
            continue;
        } else {
            $name = preg_replace("/[A-Z]/", " $0", $name); // Adds a space in front of every capital letter, since variables in the DB are camel case.
        }

        $result = array_search(strtolower($name), array_map('strtolower', $firstLine));
        if (! ($result === FALSE)) { // It returns fales if not found, thus wont do it if so. NEED TO CHECK !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
            $userParameterArray[] = $DBname;
            $userParameterArrayIndexes[$DBname] = $result;
        }
    }

    $numOfLines = count(file("tmp.csv")) - 1; // -1 is to not include the first line
    $lineNum = 0;
    while ($lineNum < $numOfLines) {
        $line = fgets($file);
        $line = csvLineToArr($line);

        $i = 0;
        $max = count($userParameterArray);
        while ($i < $max) {
            $name = $userParameterArray[$i];
            $index = $userParameterArrayIndexes[$name];
            $value = $line[$index];
            $userValue[$name] = $value;
            $i++;
        }

        updateUserArr($userValue);

        $lineNum++;
    }

    unlink("tmp.csv");

    $i = 0;
    $num = count($_FILES["userPhotos"]["name"]);
    while ($i < $num) {
        $userPhotoName = $_FILES["userPhotos"]["name"][$i];
        $userPhoto = $_FILES["userPhotos"]["tmp_name"][$i];
        move_uploaded_file($userPhoto, "../photo/$userPhotoName");
        $i++;
    }
}

function fileIssueStock () {
    $userInfo = $_FILES["userInfo"]["tmp_name"];
    move_uploaded_file($userInfo, "tmp.csv");

    $mfile = fopen("tmp.csv", "r");
    $line = fgets($mfile);
    $cols = csvLineToArr($line);

    // Convert CSV into SQL DB
    $numOfLines = count(file("tmp.csv")) - 1;
    while ($numOfLines > 0) {
        $line = fgets($mfile);
        $line = csvLineToArr($line);

        $i = 0;
        $num = count($line);
        $listOfIssues = [];
        while ($i < $num) {
            if (strtolower($cols[$i]) == "id") {
                $id = $line[$i];
            } else if ($line[$i] == "0") {
                $i++;
                continue;
            } else {
                $val = (int)$line[$i];
                $listOfIssues[] = array($cols[$i], $val);
            }
            $i++;
        }
        usort($listOfIssues, "arr2dSort");
        issueEquipment($id, $listOfIssues);
        $numOfLines--;
    }
    unlink("tmp.csv");
}

function fileReturnStock () {
    $userInfo = $_FILES["userInfo"]["tmp_name"];
    move_uploaded_file($userInfo, "tmp.csv");

    $mfile = fopen("tmp.csv", "r");
    $line = fgets($mfile);
    $cols = csvLineToArr($line);

    // Convert CSV into SQL DB
    $numOfLines = count(file("tmp.csv")) - 1;
    while ($numOfLines > 0) {
        $line = fgets($mfile);
        $line = csvLineToArr($line);

        $i = 0;
        $num = count($line);
        $listOfReturns = [];
        while ($i < $num) {
            if (strtolower($cols[$i]) == "id") {
                $id = $line[$i];
            } else if ($line[$i] == "0") {
                $i++;
                continue;
            } else {
                $val = (int)$line[$i];
                $listOfReturns[] = array($cols[$i], $val);
            }
            $i++;
        }
        usort($listOfReturns, "arr2dSort");
        returnEquipment($id, $listOfReturns);
        $numOfLines--;
    }
    unlink("tmp.csv");
}

function fileLostStock () {
    $userInfo = $_FILES["userInfo"]["tmp_name"];
    move_uploaded_file($userInfo, "tmp.csv");

    $mfile = fopen("tmp.csv", "r");
    $line = fgets($mfile);
    $cols = csvLineToArr($line);

    // Convert CSV into SQL DB
    $numOfLines = count(file("tmp.csv")) - 1;
    while ($numOfLines > 0) {
        $line = fgets($mfile);
        $line = csvLineToArr($line);

        $i = 0;
        $num = count($line);
        $listOfLost = [];
        while ($i < $num) {
            if (strtolower($cols[$i]) == "id") {
                $id = $line[$i];
            } else if ($line[$i] == "0") {
                $i++;
                continue;
            } else {
                $val = (int)$line[$i];
                $listOfLost[] = array($cols[$i], $val);
            }
            $i++;
        }
        usort($listOfLost, "arr2dSort");
        declareLostOrDamaged($id, $listOfLost);
        $numOfLines--;
    }
    unlink("tmp.csv");
}

?>