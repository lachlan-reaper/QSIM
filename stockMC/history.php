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
                $history = "<tbody>";

                $rowFormatIssue =   "<tr>                       <td rowspan=NEXTROWS>TIMESTAMP</td>                      <td rowspan=NEXTROWS>Issued:</td>    <td rowspan=NEXTROWS>By: SERVER</td> <td rowspan=NEXTROWS>To: RECEIVER</td> <td>NUMx</td> <td>ITEM</td> </tr>";
                $rowFormatReturn =  "<tr style='color:orange'>  <td style='color:black' rowspan=NEXTROWS>TIMESTAMP</td>  <td rowspan=NEXTROWS>Returned:</td>  <td rowspan=NEXTROWS>By: SERVER</td> <td rowspan=NEXTROWS>To: RECEIVER</td> <td>NUMx</td> <td>ITEM</td> </tr>";
                $rowFormatLost =    "<tr style='color:red'>     <td style='color:black' rowspan=NEXTROWS>TIMESTAMP</td>  <td rowspan=NEXTROWS>Lost:</td>      <td rowspan=NEXTROWS>By: SERVER</td> <td rowspan=NEXTROWS>To: RECEIVER</td> <td>NUMx</td> <td>ITEM</td> </tr>";
                $rowFormatIssueShort =  "<tr>                      <td>NUMx</td> <td>ITEM</td> </tr>";
                $rowFormatReturnShort = "<tr style='color:orange'> <td>NUMx</td> <td>ITEM</td> </tr>";
                $rowFormatLostShort =   "<tr style='color:red'>    <td>NUMx</td> <td>ITEM</td> </tr>";

                $results = retrieveStockHistory();

                if (isset($_GET["maxRows"])) {
                    $max_rows = $_GET['maxRows'];
                } else {
                    $max_rows = 20;
                }

                $lastDOI = "";
                $lastServer = "";
                $lastReceiver = "";
                $lastMode = 2; // -1 for Lost/Damaged, 0 for Returned, 1 for Issued, 2 for Nothing Prior
                $numOfConsecRows = 0;
                $num_rows = 0;
                $i = $results->num_rows;
                while($i > 0) {
                    $row = "";
                    $numOfConsecRows++;

                    $receipt = $results->fetch_assoc();
                    $num = $receipt["changeInNum"];

                    if ($receipt["time"] == $lastDOI and $lastReceiver == $receipt["id"] and $lastServer == $receipt["serverId"]) {
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
                            $history = str_replace("NEXTROWS", $numOfConsecRows, $history);
                            $history = $history . "</tbody><tbody>";
                            $numOfConsecRows = 0;
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
                        $history = str_replace("NEXTROWS", $numOfConsecRows, $history);
                        $history = $history . "</tbody><tbody>";
                        $numOfConsecRows = 0;
                        if (! $lastDOI == "") {
                            $row = "<tr><td></td></tr>" . $row;
                        }
                        $lastDOI = $receipt["time"];
                        $lastReceiver = $receipt["id"];  
                        $lastServer = $receipt["serverId"];

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

                    $server = getMultiUserValues($receipt["serverId"], array('firstName', 'lastName'), 'users');
                    $server = $server["lastName"] . ", " . $server["firstName"];
                    $receiver = getMultiUserValues($receipt["id"], array('firstName', 'lastName'), 'users');
                    $receiver = $receiver["lastName"] . ", " . $receiver["firstName"];
                    
                    $row = str_replace("TIMESTAMP", $receipt["time"], $row);
                    $row = str_replace("NUM", $num, $row);
                    $row = str_replace("ITEM", $receipt["item"], $row);
                    $row = str_replace("SERVER", $server, $row);
                    $row = str_replace("RECEIVER", $receiver, $row);
                    
                    $history = $history . $row;
                    $i--;
                    $num_rows++;
                }
                $history = str_replace("NEXTROWS", $numOfConsecRows+1, $history);
                $history = $history . "</tbody>";
                echo $history;
            ?>
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