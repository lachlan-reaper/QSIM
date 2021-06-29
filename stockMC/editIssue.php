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

    <maincontents>
        <div style="vertical-align:text-top;text-align:center">
            <div style="display:inline-block;width:65%">
                <table style="min-width:0;">
                    <tr>
                        <th style="width:60%">Equipment</th>
                        <th style="width:20%">#</th>
                        <th style="width:20%"></th>
                    </tr>
                    <?php 
                        $rowFormat = "<tr>
                        <td>ITEM</td>
                        <td><input class='equipNum' id='ITEM' type='number' min='0' value=VALUES></td>
                        <td><button type='button' onClick='changeValue(\"ITEM\", 1)'>+1</button>  <button type='button' onClick='changeValue(\"ITEM\", -1)'>-1</button></td>
                        </tr>";
                        $id = $_GET["id"];
                        $results = retrieveAllIssuedItemsOnStock();
                        $i = $results->num_rows;
                        while($i > 0) {
                            $row = $rowFormat;
                            $item = $results->fetch_assoc();
                            $row = str_replace("ITEM", $item["item"], $row);
                            $num = getUserValue($id, $item["item"], "inventory");
                            $row = str_replace("VALUES", $num, $row);
                            echo $row;
                            $i--;
                        }
                    ?>
                </table> <br>
                <div style="text-align:right">
                    <?php
                        $rowFormat = "<button type='button' onClick='process(\"Set\", \"ID\")' class='searchButtonResult' value='Set'>Set</button>";
                        $row = $rowFormat;
                        $row = str_replace("ID", $id, $row);
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
                    if (num < 0) {
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
        </script>

    </maincontents>

    <footer>
        Lachlan Muir ®2021
    </footer>
</body>
</html>
