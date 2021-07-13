<?php 
    require "../databaseFunctions.php";
    session_start();
    redirectingUnauthUsers("home");
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
        $id = $_SESSION['currentUserId']; // As this page is completely formatted around these variables, they are established at the start so all php sections have access to them
        $vars = establishProfilePageVars($id);
        $firstname = $vars[0];
        $lastname = $vars[1];
        $appointment = $vars[2];
        $rank = $vars[3];
    ?>

    <script>
        document.getElementById("homeTab").className = "activetab";
    </script>

    <maincontents>
        <table class="profilePage">
            <tr>
                <td style="height:300px;">
                    <profilePageBox>
                        <b>Current Concerns</b>
                        <table style="border-width:0px;min-width:0px;max-width:90%;margin-left:20px;width:auto;">
                            <?php
                                $rowFormatMissing = "<tr style='color:red'> <td>Missing:</td> <td>NUMx</td> <td>ITEM</td> </tr>";
                                $rowFormatExcess = "<tr> <td>Excess Of:</td><td>NUMx</td> <td>ITEM</td> </tr>";
                                $resultsExp = retrieveIssuedItems("stdIssue");
                                $resultsId = retrieveIssuedItems($id);
                                $items = retrieveAllIssuedItemsOnStock();

                                $i = $items->num_rows;
                                $numExp = $resultsExp->fetch_assoc(); // Retrieves the entire array of the expected set
                                $numId = $resultsId->fetch_assoc(); // Retrieves the entire array of the currently issued set
                                while($i > 0) {
                                    $item = $items->fetch_assoc(); // Retrieves the name of each item
                                    $name = $item["item"];
                                    $expected = $numExp[$name];
                                    $current = $numId[$name];
                                    if ($current < $expected) {
                                        $row = $rowFormatMissing;
                                        $row = str_replace("NUM", $expected-$current, $row);
                                        $row = str_replace("ITEM", $name, $row);
                                    } else if ($current >= ($expected * 2) and $current > ($expected + 1)) { // Calculates if the issued amount is too much compared to what the person is expected to have
                                        $row = $rowFormatExcess;
                                        $row = str_replace("NUM", $current-($expected*2), $row);
                                        $row = str_replace("ITEM", $name, $row);
                                    } else {
                                        $row = "";
                                    }
                                    echo $row;
                                    $i--;
                                }
                            ?>
                        </table>
                    </profilePageBox>
                </td>
                <td style="height:300px;">
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
        </table>
    </maincontents>

    <footer>
        Lachlan Muir ®2021
    </footer>
</body>

</html>