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

        if ($id == $_SESSION["currentUserId"]) {
            echo "<script> document.getElementById('profileTab').className = 'activetab'; </script>";
		} else {
            echo "<script> document.getElementById('searchTab').className = 'activetab'; </script>";
		}
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
                            $rowFormat = "<img src='FILE' style='object-fit:cover;float:left;margin:10px' height='250px' width='250px'>";
                            $fileName = getProfilePicture($id);
                            $rowFormat = str_replace("FILE", $fileName, $rowFormat);
                            echo $rowFormat;

                            // Display profile information
                            $rowFormat = "<br> <b>LASTNAME</b><br> <b>FIRSTNAME</b><br> RANK<br> APPOINTMENT<br><br>COY: COMP<br>PL: PLAT<br>Section: SECT<br><br>";
                            $rowFormat = "<br> <b>LASTNAME</b><br> <b>FIRSTNAME</b><br> RANK<br> APPOINTMENT<br><br><table class='identification'><tr><td>Company:</td> <td>COMP</td></tr><tr><td>Platoon:</td> <td>PLAT</td></tr><tr><td>Section:</td> <td>SECT</td></tr></table><br>";
                            $rowFormat = str_replace("LASTNAME",    $firstname, $rowFormat);
                            $rowFormat = str_replace("FIRSTNAME",   $lastname, $rowFormat);
                            $rowFormat = str_replace("RANK",        $rank, $rowFormat);
                            $rowFormat = str_replace("APPOINTMENT", strtoupper($appointment), $rowFormat);

                            $arr = getUserValues($id, ["company", "platoon", "section"], "users");
                            $company = $arr["company"];
                            $platoon = $arr["platoon"];
                            $section = $arr["section"];

                            $rowFormat = str_replace("COMP", $company, $rowFormat);
                            $rowFormat = str_replace("PLAT", $platoon, $rowFormat);
                            $rowFormat = str_replace("SECT", $section, $rowFormat);
                            echo $rowFormat;

                            if ($id == $_SESSION["currentUserId"]) { // If this is the current user than they can reset or change the passowrd, they decide
                                echo "<button type='button' onClick='return redirect(\"resetPassword.php?id=$id\", true)'>Reset Password</button><br><br>";
                                echo "<button type='button' onClick='return redirect(\"retrievePassword.php?id=$id\", false)'>Change Password</button>";
                            } else if (validUser("stockMC", $_SESSION["currentUserAccess"])) { // Only people with access to the mastercontrols, i.e. Admins
                                echo "<button type='button' onClick='return redirect(\"resetPassword.php?id=$id\", true)'>Reset Password</button><br><br>";
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
                                $results = getIssuedItems($id);
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
                                $helpIcon = "<span class='dropdown'>
                                    <img src='../images/questionMark.png' height='20px' width='20px'>
                                    <div class='dropdown_content_overlay'>
                                        TEXT
                                    </div>
                                </span>";

                                $helpTextMissing = "Please check that you do not have this equipment on you or at home. If this is the case you may want to visit the Quartermaster's Clothing Store (QCS) behind the library at lunch or on the designated opening afternoon.";
                                $helpTextExcess = "Please check to see if you have this equipment on you or at home. If you do not have the equipment please immediately contact someone from Q Store. If you do have the excess equipment please consider returning the excess.";

                                $rowFormatMissing = "<tr style='color:red'> <td>Missing:</td> <td>NUMx</td> <td>ITEM</td> <td>HELP</td> </tr>";
                                $rowFormatExcess = "<tr> <td>Excess Of:</td><td>NUMx</td> <td>ITEM</td> <td>HELP</td> </tr>";

                                $resultsExp = getIssuedItems("stdIssue"); // The expected issue has a constant id
                                $resultsId = getIssuedItems($id);
                                $items = retrieveAllIssuedItemsOnStock();

                                $i = $items->num_rows;
                                $numExp = $resultsExp->fetch_assoc();
                                $numId = $resultsId->fetch_assoc();
                                while($i > 0) { // Displays the count of each item issued of every item on issue
                                    $item = $items->fetch_assoc();
                                    $name = $item["item"];
                                    $expected = $numExp[$name];
                                    $current = $numId[$name];

                                    $help = $helpIcon;

                                    if ($current < $expected) { // If there is not enough items issued
                                        $row = $rowFormatMissing;
                                        $row = str_replace("NUM", $expected-$current, $row);
                                        $help = str_replace("TEXT", $helpTextMissing, $help);
                                    } else if ($current > ($expected * 2) and $current > ($expected + 1)) { // If the item is in excess
                                        $row = $rowFormatExcess;
                                        $row = str_replace("NUM", $current-($expected*2), $row);
                                        $help = str_replace("TEXT", $helpTextExcess, $help);
                                    } else { // Else no need to display anything
                                        $row = "";
                                    }

                                    $row = str_replace("ITEM", $name, $row);
                                    $row = str_replace("HELP", $help, $row);

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
                            $rowsFormat = "<b><i>If you have any questions or queries please contact the RQMS at:</i></b> <br>
                            EMAIL1 <br>
                            <b><i>Or the QM at:</i></b> <br>
                            EMAIL2";
                            $contacts = getContacts();
                            $rowsFormat = str_replace("EMAIL1", $contacts["RQMS"], $rowsFormat);
                            $rowsFormat = str_replace("EMAIL2", $contacts["QM"], $rowsFormat);

                            echo $rowsFormat;
                        ?>
                    </profilePageBox>
                </td>
            </tr>
        </table>
        <div style="text-align:center;">
            <profilePageBox style="width:60%;height:auto;text-align:justify;">
                <div style="text-align:center;">
                    <b>Notes</b>
                </div>
                <span style="float:right;margin-top:-19px;">
                    <button id="editNotes" onClick="editNotes();">Edit Notes</button>
                </span>
                <br>
                <span id="notesDisp">
                    <?php
                        $arr = getUserValues($id, ["notes"], "users");
                        $notes = $arr["notes"];
                        echo str_replace("\n", "<br>", $notes);
                    ?>
                </span>
                <form id="notesForm" style="text-align:right;display:none;" method="POST" action="changeNotes.php">
                    <input type="hidden" name="id" value="<?php echo $id; ?>">
                    <textarea id="notesInp" name="notes" form="notesForm" style="width:100%;min-height:100px;resize:vertical;"><?php echo $notes; ?></textarea> <br> <br>
                    <input id="notesSub" type="submit">
                </form>
            </profilePageBox>
        </div>
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

                        $results = getIssueHistory($id);

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
            function editNotes () {
                editBtn = document.getElementById("editNotes");
                disp = document.getElementById("notesDisp");
                form = document.getElementById("notesForm");
                sub = document.getElementById("notesSub");

                editBtn.style.display = "none";
                disp.style.display = "none";
                form.style.display = "block";
            }
        </script>
    </maincontents>

    <footer>
        Lachlan Muir ®2021
    </footer>
</body>
</html>