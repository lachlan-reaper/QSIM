<?php 
    require "../databaseFunctions.php";
    session_start();
    redirectingUnauthUsers("stockMC");
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

    <script>
        document.getElementById("stockTab").className = "activetab";
    </script>

    <maincontents>
        <h1>History of Issuing</h1>
        <div style="float:right;">
            <select onChange="refreshNewFilter()" id="filter">
                <option value="all">All</option>
                <option value="issue">Issue</option>
                <option value="return">Return</option>
                <option value="lost">Lost</option>
            </select>
        </div> <br> <br>
        <table style="min-width:0px" class="stockHistory">
            <tr>
                <th>Time</th>
                <th>Type</th>
                <th>Server</th>
                <th>Recipient</th>
                <th>#</th>
                <th>Item</th>
            </tr>
            <?php 
                if (isset($_GET["filter"])) {
                    $filter = $_GET["filter"];
                } else {
                    $filter = "all";
                }

                $history = "<tbody>";

                $rowFormatIssue =   "<tr>                       <td rowspan=NEXTROWS>TIMESTAMP</td>                      <td rowspan=NEXTROWS>Issued:</td>    <td rowspan=NEXTROWS>By: SERVER</td> <td rowspan=NEXTROWS>To: RECEIVER</td> <td>NUMx</td> <td>ITEM</td> </tr>";
                $rowFormatReturn =  "<tr style='color:darkgoldenrod'>  <td style='color:black' rowspan=NEXTROWS>TIMESTAMP</td>  <td rowspan=NEXTROWS>Returned:</td>  <td rowspan=NEXTROWS>By: SERVER</td> <td rowspan=NEXTROWS>To: RECEIVER</td> <td>NUMx</td> <td>ITEM</td> </tr>";
                $rowFormatLost =    "<tr style='color:red'>     <td style='color:black' rowspan=NEXTROWS>TIMESTAMP</td>  <td rowspan=NEXTROWS>Lost:</td>      <td rowspan=NEXTROWS>By: SERVER</td> <td rowspan=NEXTROWS>To: RECEIVER</td> <td>NUMx</td> <td>ITEM</td> </tr>";
                $rowFormatIssueShort =  "<tr>                      <td>NUMx</td> <td>ITEM</td> </tr>";
                $rowFormatReturnShort = "<tr style='color:darkgoldenrod'> <td>NUMx</td> <td>ITEM</td> </tr>";
                $rowFormatLostShort =   "<tr style='color:red'>    <td>NUMx</td> <td>ITEM</td> </tr>";

                $results = retrieveStockHistory();

                $lastDOI = "";
                $lastServer = "";
                $lastReceiver = "";
                $lastMode = 2; // -1 for Lost/Damaged, 0 for Returned, 1 for Issued, 2 for Nothing Prior
                $numOfConsecRows = 0;
                $i = $results->num_rows;
                while($i > 0) { // Display a row for every new input in the history log
                    $row = "";
                    $numOfConsecRows++;

                    $receipt = $results->fetch_assoc();
                    $num = $receipt["changeInNum"];
                    
                    // If the current receipt was issued at the same time as the last one by the same person then that information can be skipped.
                    if ($receipt["time"] == $lastDOI and $lastReceiver == $receipt["id"] and $lastServer == $receipt["serverId"]) { 
                        $receipt["time"] = "";

                        // Checks if the last row was of the same function type as the current one
                        if ($receipt["lostOrDamaged"] == 1 and $lastMode == -1) {
                            if ($filter == "all" or $filter == "lost") {
                                $row = $rowFormatLostShort;
                                $num = $num * -1;
                            } else {
                                $i--;
                                $numOfConsecRows = 0;
                                continue;
                            }
                        } else if ($num > 0 and $lastMode == 1) {
                            if ($filter == "all" or $filter == "issue") {
                                $row = $rowFormatIssueShort;
                            } else {
                                $i--;
                                $numOfConsecRows = 0;
                                continue;
                            }
                        } else if ($num < 0 and $lastMode == 0) {
                            if ($filter == "all" or $filter == "return") {
                                $row = $rowFormatReturnShort;
                                $num = $num * -1;
                            } else {
                                $i--;
                                $numOfConsecRows = 0;
                                continue;
                            }
                        } else {
                            // Replaces NEXTROWS to instruct rowspan the date, server and recipient boxes should stretch
                            $history = str_replace("NEXTROWS", $numOfConsecRows, $history);
                            $numOfConsecRows = 0;
                            if ($lastDOI != "") {
                                $row = "<tr><td></td></tr>" . $row;
                            }
                            $lastDOI = $receipt["time"];
                            $lastReceiver = $receipt["id"];  
                            $lastServer = $receipt["serverId"];
                            
                            // Checks what function type the current row is
                            if ($receipt["lostOrDamaged"] == 1) {
                                $lastMode = -1;
                                if ($filter == "all" or $filter == "lost") {
                                    $row = $rowFormatLost;
                                    $num = $num * -1;
                                } else {
                                    $i--;
                                    continue;
                                }
                            } else if ($num > 0) {
                                $lastMode = 1;
                                if ($filter == "all" or $filter == "issue") {
                                    $row = $rowFormatIssue;
                                } else {
                                    $i--;
                                    continue;
                                }
                            } else if ($num < 0) {
                                $lastMode = 0;
                                if ($filter == "all" or $filter == "return") {
                                    $row = $rowFormatReturn;
                                    $num = $num * -1;
                                } else {
                                    $i--;
                                    continue;
                                }
                            } else {
                                echo "Error! Receipt Num = " . $receipt["receiptNum"];
                                continue;
                            }

                            // Adds a tbody for alternate colouring
                            $history = $history . "</tbody><tbody>";
                        }
                    } else { // Else nothing is in common
                        // Replaces NEXTROWS to instruct rowspan the date, server and recipient boxes should stretch
                        $history = str_replace("NEXTROWS", $numOfConsecRows, $history);
                        $numOfConsecRows = 0;
                        if ($lastDOI != "") {
                            $row = "<tr><td></td></tr>" . $row;
                        }
                        $lastDOI = $receipt["time"];
                        $lastReceiver = $receipt["id"];  
                        $lastServer = $receipt["serverId"];
                        
                        // Checks what function type the current row is
                        if ($receipt["lostOrDamaged"] == 1) {
                            $lastMode = -1;
                            if ($filter == "all" or $filter == "lost") {
                                $row = $rowFormatLost;
                                $num = $num * -1;
                            } else {
                                $i--;
                                continue;
                            }
                        } else if ($num > 0) {
                            $lastMode = 1;
                            if ($filter == "all" or $filter == "issue") {
                                $row = $rowFormatIssue;
                            } else {
                                $i--;
                                continue;
                            }
                        } else if ($num < 0) {
                            $lastMode = 0;
                            if ($filter == "all" or $filter == "return") {
                                $row = $rowFormatReturn;
                                $num = $num * -1;
                            } else {
                                $i--;
                                continue;
                            }
                        } else {
                            echo "Error! Receipt Num = " . $receipt["receiptNum"];
                            continue;
                        }

                        // Adds a tbody for alternate colouring
                        $history = $history . "</tbody><tbody>";
                    }

                    // Get the information of the one who served the equipment
                    $server = getUserValues($receipt["serverId"], ['firstName', 'lastName'], 'users');
                    $server = $server["lastName"] . ", " . $server["firstName"];
                    
                    // Get the information of the one who received the equipment, used same variable name to save memory
                    $receiver = getUserValues($receipt["id"], ['firstName', 'lastName'], 'users');
                    $receiver = $receiver["lastName"] . ", " . $receiver["firstName"];
                    
                    $row = str_replace("TIMESTAMP", $receipt["time"], $row);
                    $row = str_replace("NUM", $num, $row);
                    $row = str_replace("ITEM", $receipt["item"], $row);
                    $row = str_replace("SERVER", $server, $row);
                    $row = str_replace("RECEIVER", $receiver, $row);
                    
                    $history = $history . $row;
                    $i--;
                }
                $history = str_replace("NEXTROWS", $numOfConsecRows+1, $history);
                $history = $history . "</tbody>";
                echo $history;
            ?>
        </table>
        <script>
            currFilter = "<?php echo $filter?>";
            select = document.getElementById("filter");
            if (currFilter == "all") {
                select.selectedIndex = 0;
            } else if (currFilter == "issue") {
                select.selectedIndex = 1;
            } else if (currFilter == "return") {
                select.selectedIndex = 2;
            } else if (currFilter == "lost") {
                select.selectedIndex = 3;
            }

            function refreshNewFilter() {
                newFilter = document.getElementById("filter").value;
                window.location.href = "http://<?php echo $_SESSION["websiteLoc"] ?>/stockMC/history.php?filter=" + newFilter;
            }

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