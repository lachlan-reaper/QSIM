<?php
require '../databaseFunctions.php';

establishConnection();
$function = $_POST["func"];

if ($function == "appointments") {
    $apptAccess = [];
    $post_keys = array_keys($_POST);
    $max = count($post_keys);
    $num = 0; // Counts number of appts
    $i = 0; // Counts iterations of the loop
    while($max > $i) {
        $key = $post_keys[$i];
        
        if ($key == "func") {
            $i++;
            continue;
        } else if (substr($key, strlen($key)-6) == "Access") {
            $i++;
            continue;
        }
        
        $appt = strtoupper(urldecode($_POST[$key]));        
        $access = strtolower(urldecode($_POST[$key . "Access"]));
        
        $apptAccess[$num] = [$appt, $access];
        $i++;
        $num++;
    }

    $mfile = fopen("../appointmentAccessRoles.csv", "w");
    
    $file = arr2DToCsvFile($apptAccess);
    fwrite($mfile, $file);

    fclose($mfile);
} else if ($function == "contacts") {
    $contacts = [];
    $post_keys = array_keys($_POST);
    $max = count($post_keys);
    $i = 0;
    $num = 0;
    while($max > $i) {
        $key = $post_keys[$i];
        
        if ($key == "func") {
            $i++;
            continue;
        }
        
        $appt = urldecode($key);
        $appt = str_replace("_" , " ", $appt);

        $contact = urldecode($_POST[$key]);
        $contact = str_replace("_" , " ", $contact);
        
        $contacts[$num] = [$appt, $contact];
        $i++;
        $num++;
    }

    $mfile = fopen("../contacts.csv", "w");
    
    $file = arr2DToCsvFile($contacts);
    fwrite($mfile, $file);

    fclose($mfile);
} else if ($function == "plStruct") {
    $structure = [];
    $platoons = getPlatoons();
    $max = count($platoons);
    $i = 0;
    while ($max > $i) {
        $platoon = $platoons[$i];

        $num = 0;
        $skip = 0;
        $plStruct = [];

        $plStruct[0] = $platoon;
        while (true) {
            $key = $platoon . "-" . $num;
            $key = str_replace(" ", "_", $key);
            if (isset($_POST[$key])) {
                if ($_POST[$key] == "None") {
                    $skip++;
                } else {
                    $plStruct[$num + 1 - $skip] = $_POST[$key];
                }
            } else {
                break;
            }

            $num++;
        }
        $structure[$i] = $plStruct;
        $i++;
    }

    $file = arr2DToCsvFile($structure);
    
    $mfile = fopen("../unitStructure/PLsStructure.csv", "w");
    
    fwrite($mfile, $file);

    fclose($mfile);
} else if ($function == "coyStruct") {
    $coys = [];
    $post_keys = array_keys($_POST);
    $max = count($post_keys);
    $i = 0;
    $num = 0;
    $coy = "";
    while($max > $i) {
        $key = $post_keys[$i];
        
        if ($key == "func") {
            $i++;
            continue;
        } else if (substr($key, strlen($key)-2) == "-h") {
            $i++;
            continue;
        } else if (substr($key, strlen($key)-2) == "-0") {
            $coy = substr($key, 0, strlen($key)-2);
        }
        
        $arr = [$coy];
        $x = 0;
        $skip = 0;
        while (true) {
            $key = $coy . "-" . $x;
            if (isset($_POST[$key])) {
                if ($_POST[$key] == "") {
                    $skip++;
                } else {
                    $arr[$x+1-$skip] = strtoupper($_POST[$key]);
                }
                $x++;
                $i++;
            } else {
                break;
            }
        }
        
        $coys[$num] = $arr;
        $i++;
        $num++;
    }

    $mfile = fopen("../unitStructure/COY-PL.csv", "w");
    
    $file = arr2DToCsvFile($coys);
    fwrite($mfile, $file);

    fclose($mfile);
}

header("Location: http://" . $_SESSION["websiteLoc"] . "/stockMC/");
?>