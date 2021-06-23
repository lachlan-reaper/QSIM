<?php 
    require "../databaseFunctions.php";
    session_start();
    establishConnection();
    if (! isset($_GET["id"])) {
        redirectingUnauthUsers("profile");
        $id = $_SESSION["currentUserId"];
    } else {
        $id = $_GET['id'];
        if ($id == $_SESSION["currentUserId"]) {
            redirectingUnauthUsers("profile");
		} else {
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
        <?php
            if (validUser("stockMC", $_SESSION["currentUserAccess"])) {
                echo "<span style='float:right'><button type='button' onClick='redirect(\"../stockMC/changeUser.php?function=manualModifyUser&id=$id\", false)'>Edit User</button></span>";
            }
        ?>
        <table class="profilePage">
            <tr>
                <td rowspan=2 style="height:500px;width:55%">
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
                <td style="height:300px;width:45%">
                    <profilePageBox>
                        <b>Identification</b> <br>
                        <?php 
                            $rowFormat = "<img src='../photo/IDNUM.jpg' style='object-fit:cover;float:left;margin:10px' height='250px' width='250px'>";
                            $rowFormat = str_replace("IDNUM", $id, $rowFormat);
                            echo $rowFormat;

                            $rowFormat = "<br> <b>LASTNAME</b><br> <b>FIRSTNAME</b><br> RANK<br> APPOINTMENT";
                            $rowFormat = str_replace("LASTNAME",    $firstname, $rowFormat);
                            $rowFormat = str_replace("FIRSTNAME",   $lastname, $rowFormat);
                            $rowFormat = str_replace("RANK",        $rank, $rowFormat);
                            $rowFormat = str_replace("APPOINTMENT", strtoupper($appointment), $rowFormat);
                            echo $rowFormat
                        ?>
                    </profilePageBox>
                </td>
            </tr>
            <tr>
                <td style="height:150px">
                    <profilePageBox>
                        <b><i>If you have any issues or concerns please contact the QM at:</i></b> <br>
                        jpriv2021@waverley.nsw.edu.au <br>
                        <b><i>Or the RQMS at:</i></b> <br>
                        breid2022@waverley.nsw.edu.au
                    </profilePageBox>
                </td>
            </tr>
            <tr>
                <td colspan=1>
                    <profilePageBox>
                        <b>History of Issuing</b> <br> <br>
                        <table style="border-width:0px;min-width:0px">
                            <?php 
                                $history = "";

                                $rowFormatIssue =   "<tr>                       <td>TIMESTAMP</td>                      <td>Issued:</td>   <td>NUMx</td> <td>ITEM</td> </tr>";
                                $rowFormatReturn =  "<tr style='color:orange'>  <td style='color:black'>TIMESTAMP</td>  <td>Returned:</td> <td>NUMx</td> <td>ITEM</td> </tr>";
                                $rowFormatLost =    "<tr style='color:red'>     <td style='color:black'>TIMESTAMP</td>  <td>Lost:</td>     <td>NUMx</td> <td>ITEM</td> </tr>";
                                $rowFormatIssueShort =  "<tr>                      <td></td><td></td>   <td>NUMx</td> <td>ITEM</td> </tr>";
                                $rowFormatReturnShort = "<tr style='color:orange'> <td></td><td></td>   <td>NUMx</td> <td>ITEM</td> </tr>";
                                $rowFormatLostShort =   "<tr style='color:red'>    <td></td><td></td>   <td>NUMx</td> <td>ITEM</td> </tr>";
                                $results = retrieveIssueHistory($id);

                                if (isset($_GET["maxRows"])) {
                                    $max_rows = $_GET['maxRows'];
	                            } else {
                                    $max_rows = 20;
                                }

                                $lastDOI = "";
                                $lastMode = 2; // -1 for Lost/Damaged, 0 for Returned, 1 for Issued, 2 for Nothing Prior
                                $num_rows = 0;
                                $i = $results->num_rows;
                                while($i > 0) {
                                    if ($num_rows >= $max_rows) {
                                        $history = $history."<tr><td colspan=4 style='text-align:center'><button type='button' onClick='redirect(\"URL\", false)'>Show More Rows</button></td></tr>";
                                        $history = str_replace("URL", "//" . $_SESSION["websiteLoc"] . "/profile/?id=$id&maxRows=" . ($max_rows+10), $history);
                                        break;
									}
                                    $row = "";

                                    $receipt = $results->fetch_assoc();
                                    $num = $receipt["changeInNum"];

                                    if ($receipt["time"] == $lastDOI) {
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
                                    } else {
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
                </td>
            </tr>
        </table>

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