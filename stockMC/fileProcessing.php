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
    // Sorts an array alphabetically by the first item of each of the arrays
    $arr = array($a[0], $b[0]);
    $arrS = $arr;
    sort($arrS);
    if ($a[0] === $b[0]) {
        return 0;
    } else if ($arr === $arrS) {
        return -1;
    } else {
        return 1;
    }
}

function fileAddUsers () {
    // Adds users based on the file sent through HTML form method POST
    $userInfo = $_FILES["userInfo"]["tmp_name"];
    move_uploaded_file($userInfo, "tmp.csv");
    
    $userParameterArray = array();
    $userParameterArrayIndexes = array();

    $file = fopen("tmp.csv", "r");
    $firstLine = fgets($file);
    $firstLine = csvLineToArr($firstLine);

    // Creates an array that will act as key for where each of the variables could be found, i.e. if the first column stores a User's platoon then this array when given the 
    // key of "platoon" will provide the value of 1, pointing to the first index of the array
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

        if (! $userParameterArrayIndexes[$DBname] = array_search($name, $firstLine)) {
            die("The provided file does not contain all of the required columns or a column may be misspelt.");
        }
    }
    

    // Iterates throgh each of the user entries
    $numOfLines = count(file("tmp.csv")) - 1; // -1 is to not include the first line
    $lineNum = 0;
    while ($lineNum < $numOfLines) {
        $line = fgets($file);
        $line = csvLineToArr($line);

        // Iterates through each item and assigns in a new array the value of the item to the key of the corresponding column
        // I.e. the value of the user's password is assigned to the key of 'userpass'
        $i = 0;
        $max = count($userParameterArray);
        while ($i < $max) {
            $name = $userParameterArray[$i];
            $index = $userParameterArrayIndexes[$name];
            $userValue[$name] = $line[$index];
            $i++;
        }

        addUserArr($userValue);

        $lineNum++;
    }

    unlink("tmp.csv");

    // Adds all of the profile pictures
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
    // Removes users based on the file sent through HTML form method POST
    $userInfo = $_FILES["userInfo"]["tmp_name"];
    move_uploaded_file($userInfo, "tmp.csv");
    
    $userParameterArray = array();
    $userParameterArrayIndexes = array();

    $file = fopen("tmp.csv", "r");
    $firstLine = fgets($file);
    $firstLine = csvLineToArr($firstLine);
    
    // Creates an array that will act as key for where each of the variables could be found, i.e. if the first column stores a User's first name then this array when given the 
    // key of "firstName" will provide the value of 1, pointing to the first index of the array
    $userParameterArray[] = "id";
    $userParameterArrayIndexes["id"] = array_search("Id", $firstLine);
    $userParameterArray[] = "firstName";
    $userParameterArrayIndexes["firstName"] = array_search("First Name", $firstLine);
    $userParameterArray[] = "lastName";
    $userParameterArrayIndexes["lastName"] = array_search("Last Name", $firstLine);

    // Iterates through each of the user entries
    $numOfLines = count(file("tmp.csv")) - 1; // -1 is to not include the first line
    $lineNum = 0;
    while ($lineNum < $numOfLines) {
        $line = fgets($file);
        $line = csvLineToArr($line);

        // Iterates through each item and assigns in a new array the value of the item to the key of the corresponding column
        // I.e. the value of the user's password is assigned to the key of 'userpass'
        $i = 0;
        $max = count($userParameterArray);
        while ($i < $max) {
            $name = $userParameterArray[$i];
            $index = $userParameterArrayIndexes[$name];
            $userValue[$name] = $line[$index];
            $i++;
        }

        removeUserArr($userValue);

        $id = $userValue["id"];

        // Removes the profile pic
        $filename = "../photo/$id.jpg";
        if (file_exists($filename)) {
            unlink($filename);
        }

        $lineNum++;
    }
    unlink("tmp.csv");

    // Ensures stock table numbers are accurate
    refreshStockTable();
}

function fileUpdateUsers () { 
    // Updates users based on the file sent through HTML form method POST
    $userInfo = $_FILES["userInfo"]["tmp_name"];
    move_uploaded_file($userInfo, "tmp.csv");
    
    $userParameterArray = array();
    $userParameterArrayIndexes = array();

    $file = fopen("tmp.csv", "r");
    $firstLine = fgets($file);
    $firstLine = csvLineToArr($firstLine);

    // Creates an array that will act as key for where each of the variables could be found, i.e. if the first column stores a User's platoon then this array when given the 
    // key of "platoon" will provide the value of 1, pointing to the first index of the array
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
        if (! ($result === FALSE)) { // It returns false if not found, thus wont do it if so.
            $userParameterArray[] = $DBname;
            $userParameterArrayIndexes[$DBname] = $result;
        }
    }

    // Iterates throgh each of the user entries
    $numOfLines = count(file("tmp.csv")) - 1; // -1 is to not include the first line
    $lineNum = 0;
    while ($lineNum < $numOfLines) {
        $line = fgets($file);
        $line = csvLineToArr($line);

        // Iterates through each item and assigns in a new array the value of the item to the key of the corresponding column
        // I.e. the value of the user's password is assigned to the key of 'userpass'
        $i = 0;
        $max = count($userParameterArray);
        while ($i < $max) {
            $name = $userParameterArray[$i];
            $index = $userParameterArrayIndexes[$name];
            $userValue[$name] = $line[$index];
            $i++;
        }

        updateUserArr($userValue);

        $lineNum++;
    }

    unlink("tmp.csv");

    // Updates if necessary all of the profile pictures
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
    // Issues stock en masse based on the file sent through HTML form method POST
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

        // Formats each item in the line into an array
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
    // Returns stock en masse based on the file sent through HTML form method POST
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

        // Formats each item in the line into an array
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
    // Declares stock en masse as lost or damaged based on the file sent through HTML form method POST
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

        // Formats each item in the line into an array
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