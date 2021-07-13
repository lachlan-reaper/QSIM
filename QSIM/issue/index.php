<?php
    require "../databaseFunctions.php";
    session_start();
    redirectingUnauthUsers("issue");
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
        <div style="vertical-align:text-top;text-align:center">
            <div style="display:inline-block;width:65%">
                <h1><?php echo $_GET["action"]; ?></h1>
                <h3><?php echo getUserValue($_GET["id"],"lastName","users"); echo ", "; echo getUserValue($_GET["id"],"firstName","users");  ?></h3> <!--Displays the selected users name-->
                <table style="min-width:0;">
                    <tr>
                        <th style="width:60%">Equipment</th>
                        <th style="width:20%">#</th>
                        <th style="width:20%"></th>
                    </tr>
                    <?php 
                        $rowFormat = "<tr>
                        <td>ITEM</td>
                        <td><input class='equipNum' id='ITEM' type='number' min='0' value=0></td>
                        <td><button type='button' onClick='changeValue(\"ITEM\", 1)'>+1</button>  <button type='button' onClick='changeValue(\"ITEM\", -1)'>-1</button></td>
                        </tr>";
                        $results = retrieveAllIssuedItemsOnStock();
                        $i = $results->num_rows;
                        while($i > 0) { // Displays a row of input for every type of equipment issued.
                            $item = $results->fetch_assoc();
                            echo str_replace("ITEM", $item["item"], $rowFormat);
                            $i--;
                        }
                    ?>
                </table> <br>
                <div style="text-align:right">
                    <button type="button" onClick="issueSet('RECIssue')">Standard REC Issue</button>  
                    <button type="button" onClick="issueSet('AFXIssue')">Standard AFX Issue</button>  
                    <button type="button" onClick="issueSet('customIssue')">Custom Issue</button>
                    <button type="button" onClick="reset()">Reset</button> <br> <br>
                    <?php
                        $rowFormat = "<button type='button' onClick='process(\"ACTION\", \"ID\")' class='searchButtonResult' value='ACTION'>ACTION</button>";
                        $row = $rowFormat;
                        $row = str_replace("ACTION", $_GET["action"], $row);
                        $row = str_replace("ID", $_GET["id"], $row);
                        echo $row;
                    ?>
                </div>
            </div>
        </div> 

        <script>
            function process(action, id) {
                mods = "";
                prevPage = document.referrer;
                items = document.getElementsByClassName("equipNum");
                for (i = 0; i < items.length; i++) {
                    num = items[i].value;
                    item = items[i].id;
                    if (num == "" || num == 0 || num == "0") {
                        num = 0;
                    } else if (num < 0) {
                        alert(item + " must be greater than or equal to 0.");
                        return;
                    } else {
                        mods += "|" + item.replace(/ /g, "-") + "_" + num;
                    }
                }
                mods = mods.slice(1);
                window.location.href = "../issue/processing.php?action=" + action + "&id=" + id + "&mods=" + encodeURIComponent(mods) + "&prev=" + encodeURIComponent(prevPage);
            }
            function changeValue(item, value) {
                box = document.getElementById(item);
                if (+ box.value + value >= 0) {
                    box.value = + box.value + value;
                }
            }
            function issueSet(setName) {
                var set = [];
                const recSet = [<?php // These next few PHP scripts is to output a javascript safe array to use when someone clicks the predefined set button.
                                $row = "";
                                $results = retrieveIssuedItems("RECIssue");
                                $items = retrieveAllIssuedItemsOnStock();

                                $i = $items->num_rows;
                                $num = $results->fetch_assoc();
                                while($i > 0) { 
                                    $item = $items->fetch_assoc();
                                    $name = $item["item"];
                                    $row = $row . ", " . $num[$name];
                                    $i--;
                                }
                                $row = substr($row, 2);
                                echo $row;
                                ?>];
                const afxSet = [<?php 
                                $row = "";
                                $results = retrieveIssuedItems("AFXIssue");
                                $items = retrieveAllIssuedItemsOnStock();

                                $i = $items->num_rows;
                                $num = $results->fetch_assoc();
                                while($i > 0) { 
                                    $item = $items->fetch_assoc();
                                    $name = $item["item"];
                                    $row = $row . ", " . $num[$name];
                                    $i--;
                                }
                                $row = substr($row, 2);
                                echo $row;
                                ?>];
                const customSet = [<?php 
                                $row = "";
                                $results = retrieveIssuedItems("customIssue");
                                $items = retrieveAllIssuedItemsOnStock();

                                $i = $items->num_rows;
                                $num = $results->fetch_assoc();
                                while($i > 0) { 
                                    $item = $items->fetch_assoc();
                                    $name = $item["item"];
                                    $row = $row . ", " . $num[$name];
                                    $i--;
                                }
                                $row = substr($row, 2);
                                echo $row;
                                ?>];
                if (setName == "RECIssue") {
                    for (i = 0; i < recSet.length; i++) {
                        set[i] = recSet[i];
                    }
                } else if (setName == "AFXIssue") {
                    for (i = 0; i < afxSet.length; i++) {
                        set[i] = afxSet[i];
                    }
                } else if (setName == "customIssue") {
                    for (i = 0; i < customSet.length; i++) {
                        set[i] = customSet[i];
                    }
                } else {
                    return;
                }
                items = document.getElementsByClassName("equipNum");
                for (i = 0; i < items.length; i++) {
                    items[i].value = +items[i].value + set[i];
                }
            }
            function reset() {
                items = document.getElementsByClassName("equipNum");
                for (i = 0; i < items.length; i++) {
                    items[i].value = 0;
                }
            }
        </script>

    </maincontents>

    <footer>
        Lachlan Muir ®2021
    </footer>
</body>
</html>
