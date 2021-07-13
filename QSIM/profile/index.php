<?php 
    require "../databaseFunctions.php";
    session_start();
    establishConnection();

    // If the id isnt set, then default to the current user's ID and their profile
    if (! isset($_GET["id"])) {
        redirectingUnauthUsers("profile");
        $id = $_SESSION["currentUserId"];
    } else { // Else, if the id is the current user's then it is just viewing a profile
        $id = $_GET['id'];
        if ($id == $_SESSION["currentUserId"]) {
            redirectingUnauthUsers("profile");
		} else { // Else, then the person trying to view the profile needs to have been given access to view other's profiles
            redirectingUnauthUsers("profile?id");
        }
	}

    $vars = establishProfilePageVars($id);
    $firstname = $vars[0];
    $lastname = $vars[1];
    $appointment = $vars[2];
    $rank = $vars[3];
?>

<html lang="en-us">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../styles.css">
    <link rel="icon" href="../images/logo.svg" sizes="any" type="image/svg+xml">
    <title>QSIM</title>
</head>

<body>
    <?php 
        displayHeader();
    ?>

    <maincontents>
        <span style='float:right'>
            <?php
                // If a user doesn't have access to a page, then the link to the page is not shown
                if (validUser("issue", $_SESSION["currentUserAccess"])) {
                    echo "
                    <button type='button' onClick='redirect(\"../issue/?action=Issue&id=$id\", false)'>  Issue     </button> 
                    <button type='button' onClick='redirect(\"../issue/?action=Return&id=$id\", false)'> Return    </button>
                    <button type='button' onClick='redirect(\"../issue/?action=Lost&id=$id\", false)'>   Lost      </button>
                    ";
                }
                if (validUser("stockMC", $_SESSION["currentUserAccess"])) {
                    echo "
                    <button type='button' onClick='redirect(\"../stockMC/changeUser.php?function=manualModifyUser&id=$id\", false)'>Edit User</button>
                    ";
                }
            ?>
        </span>
        <table class="profilePage">
            <tr>
                <td style="height:300px;width:40%">
                    <profilePageBox>
                        <b>Identification</b> <br>
                        <?php
                            // Display profile pic
                            $rowFormat = "<img src='../photo/IDNUM.jpg' style='object-fit:cover;float:left;margin:10px' height='250px' width='250px'>";
                            $rowFormat = str_replace("IDNUM", $id, $rowFormat);
                            echo $rowFormat;

                            // Display profile information
                            $rowFormat = "<br> <b>LASTNAME</b><br> <b>FIRSTNAME</b><br> RANK<br> APPOINTMENT<br><br><br><br><br><br>";
                            $rowFormat = str_replace("LASTNAME",    $firstname, $rowFormat);
                            $rowFormat = str_replace("FIRSTNAME",   $lastname, $rowFormat);
                            $rowFormat = str_replace("RANK",        $rank, $rowFormat);
                            $rowFormat = str_replace("APPOINTMENT", strtoupper($appointment), $rowFormat);
                            echo $rowFormat;

                            if (validUser("stockMC", $_SESSION["currentUserAccess"])) { // Only people with access to the mastercontrols, i.e. Admins
                                echo "<button type='button' onClick='return redirect(\"resetPassword.php?id=$id\", true)'>Reset Password</button><br><br>";
                            }
                            if ($id == $_SESSION["currentUserId"]) { // If this is the current user than they can reset or change the passowrd, they decide
                                echo "<button type='button' onClick='return redirect(\"resetPassword.php?id=$id\", true)'>Reset Password</button><br><br>";
                                echo "<button type='button' onClick='return redirect(\"retrievePassword.php?id=$id\", false)'>Change Password</button>";
                            }
                        ?>
                    </profilePageBox>
                </td>
                <td rowspan=2 style="height:500px;width:30%">
                    <profilePageBox>
                        <b>Current Issue</b>
                        <table style="border-width:0px;min-width:0px;max-width:90%;margin-left:20px;width:auto;">
                            <?php
                                $rowFormat = "<tr> <td>NUMx</td> <td>ITEM</td> </tr>";
                                $results = retrieveIssuedItems($id);
                                $items = retrieveAllIssuedItemsOnStock();

                                $i = $items->num_rows;
                                $num = $results->fetch_assoc();
                                while($i > 0) { // Displays the count of each item issued of every item on issue
                                    $item = $items->fetch_assoc();
                                    $name = $item["item"];
                                    $row = $rowFormat;
                                    $row = str_replace("NUM", $num[$name], $row);
                                    $row = str_replace("ITEM", $name, $row);
                                    echo $row;
                                    $i--;
                                }
                            ?>
                        </table>
                    </profilePageBox>
                </td>
                <td rowspan=2 style="height:500px;width:30%">
                    <profilePageBox>
                        <b>Current Concerns</b>
                        <table style="border-width:0px;min-width:0px;max-width:90%;margin-left:20px;width:auto;">
                            <?php
                                $rowFormatMissing = "<tr style='color:red'> <td>Missing:</td> <td>NUMx</td> <td>ITEM</td> </tr>";
                                $rowFormatExcess = "<tr> <td>Excess Of:</td><td>NUMx</td> <td>ITEM</td> </tr>";

                                $resultsExp = retrieveIssuedItems("stdIssue"); // The expected issue has a constant id
                                $resultsId = retrieveIssuedItems($id);
                                $items = retrieveAllIssuedItemsOnStock();

                                $i = $items->num_rows;
                                $numExp = $resultsExp->fetch_assoc();
                                $numId = $resultsId->fetch_assoc();
                                while($i > 0) { // Displays the count of each item issued of every item on issue
                                    $item = $items->fetch_assoc();
                                    $name = $item["item"];
                                    $expected = $numExp[$name];
                                    $current = $numId[$name];
                                    if ($current < $expected) { // If there is not enough items issued
                                        $row = $rowFormatMissing;
                                        $row = str_replace("NUM", $expected-$current, $row);
                                        $row = str_replace("ITEM", $name, $row);
                                    } else if ($current >= ($expected * 2) and $current > ($expected + 1)) { // If the item is in excess
                                        $row = $rowFormatExcess;
                                        $row = str_replace("NUM", $current-($expected*2), $row);
                                        $row = str_replace("ITEM", $name, $row);
                                    } else { // Else no need to display anything
                                        $row = "";
                                    }
                                    echo $row;
                                    $i--;
                                }
                            ?>
                        </table>
                    </profilePageBox>
                </td>
            </tr>
            <tr>
                <td style="height:150px">
                    <profilePageBox>
                        <?php
                            $rowsFormat = "<b><i>If you have any questions or queries please contact the APPT1 at:</i></b> <br>
                            EMAIL1 <br>
                            <b><i>Or the APPT2 at:</i></b> <br>
                            EMAIL2";
                            $contacts = getContacts();

                            // Format the RQMS's contact details
                            $rowsFormat = str_replace("APPT1", strtoupper($contacts[0][0]), $rowsFormat);
                            $rowsFormat = str_replace("EMAIL1", $contacts[0][1], $rowsFormat);
                            
                            // Format the QM's contact details
                            $rowsFormat = str_replace("APPT2", strtoupper($contacts[1][0]), $rowsFormat);
                            $rowsFormat = str_replace("EMAIL2", $contacts[1][1], $rowsFormat);

                            echo $rowsFormat;
                        ?>
                    </profilePageBox>
                </td>
            </tr>
        </table>
        <div style="text-align:center;">
            <profilePageBox style="width:60%;height:auto;">
                <b>History of Issuing</b> <br> <br>
                <table style="border-width:0px;min-width:0px">
                    <?php 
                        $history = "";

                        $rowFormatIssue =   "<tr>                       <td>TIMESTAMP</td>                      <td>Issued:</td>   <td>NUMx</td> <td>ITEM</td> </tr>";
                        $rowFormatReturn =  "<tr style='color:darkgoldenrod'>  <td style='color:black'>TIMESTAMP</td>  <td>Returned:</td> <td>NUMx</td> <td>ITEM</td> </tr>";
                        $rowFormatLost =    "<tr style='color:red'>     <td style='color:black'>TIMESTAMP</td>  <td>Lost:</td>     <td>NUMx</td> <td>ITEM</td> </tr>";
                        $rowFormatIssueShort =  "<tr>                      <td></td><td></td>   <td>NUMx</td> <td>ITEM</td> </tr>";
                        $rowFormatReturnShort = "<tr style='color:darkgoldenrod'> <td></td><td></td>   <td>NUMx</td> <td>ITEM</td> </tr>";
                        $rowFormatLostShort =   "<tr style='color:red'>    <td></td><td></td>   <td>NUMx</td> <td>ITEM</td> </tr>";

                        $results = retrieveIssueHistory($id);

                        if (isset($_GET["maxRows"])) {
                            $max_rows = $_GET['maxRows'];
                        } else {
                            $max_rows = 25;
                        }

                        $lastDOI = "";
                        $lastMode = 2; // -1 for Lost/Damaged, 0 for Returned, 1 for Issued, 2 for Nothing Prior
                        $num_rows = 0;
                        $i = $results->num_rows;
                        while($i > 0) { // For each log into the history
                            if ($num_rows >= $max_rows) {
                                $history = $history."<tr><td colspan=4 style='text-align:center'><button type='button' onClick='redirect(\"URL\", false)'>Show More Rows</button></td></tr>";
                                $history = str_replace("URL", "//" . $_SESSION["websiteLoc"] . "/profile/?id=$id&maxRows=" . ($max_rows+25), $history);
                                break;
                            }
                            $row = "";

                            $receipt = $results->fetch_assoc();
                            $num = $receipt["changeInNum"];

                            if ($receipt["time"] == $lastDOI) { // If the last log was processed at the same time
                                $receipt["time"] = "";

                                if ($receipt["lostOrDamaged"] == 1 and $lastMode == -1) {
                                    $row = $rowFormatLostShort;
                                    $num = $num * -1;
                                } else if ($num > 0 and $lastMode == 1) {
                                    $row = $rowFormatIssueShort;
                                } else if ($num < 0 and $lastMode == 0) {
                                    $row = $rowFormatReturnShort;
                                    $num = $num * -1;
                                } else {
                                    $row = "<tr><td></td></tr>" . $row;
                                    if ($receipt["lostOrDamaged"] == 1) {
                                        $row = $rowFormatLost;
                                        $lastMode = -1;
                                        $num = $num * -1;
                                    } else if ($num > 0) {
                                        $row = $rowFormatIssue;
                                        $lastMode = 1;
                                    } else if ($num < 0) {
                                        $row = $rowFormatReturn;
                                        $lastMode = 0;
                                        $num = $num * -1;
                                    } else {
                                        echo "Error! Receipt Num = " . $receipt["receiptNum"];
                                        continue;
                                    }
                                }
                            } else { // Else reset the counter of logs that are in the same row
                                $numOfConsecRows = 0;
                                if (! $lastDOI == "") {
                                    $row = "<tr><td></td></tr>" . $row;
                                }
                                $lastDOI = $receipt["time"];

                                if ($receipt["lostOrDamaged"] == 1) {
                                    $row = $rowFormatLost;
                                    $lastMode = -1;
                                    $num = $num * -1;
                                } else if ($num > 0) {
                                    $row = $rowFormatIssue;
                                    $lastMode = 1;
                                } else if ($num < 0) {
                                    $row = $rowFormatReturn;
                                    $lastMode = 0;
                                    $num = $num * -1;
                                } else {
                                    echo "Error! Receipt Num = " . $receipt["receiptNum"];
                                    continue;
                                }
                            }
                            
                            $row = str_replace("TIMESTAMP", $receipt["time"], $row);
                            $row = str_replace("NUM", $num, $row);
                            $row = str_replace("ITEM", $receipt["item"], $row);
                            
                            $history = $history . $row;
                            $i--;
                            $num_rows++;
                        }
                        echo $history;
                    ?>
                </table>
            </profilePageBox>
        </div>

        <script>
            function redirect (URL, confirmation) {
                if (confirmation) {
                    if (confirm('Do you really want to submit the form?')) {
                        window.location.href = URL;
                    }
                } else {
                    window.location.href = URL;
                }
            }
        </script>
    </maincontents>

    <footer>
        Lachlan Muir ®2021
    </footer>
</body>
</html>