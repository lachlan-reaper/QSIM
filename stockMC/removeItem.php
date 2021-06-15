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
            <div style="display:inline-block;width:30%">
                <table style="min-width:0;">
                    <tr>
                        <th style="width:90%">Equipment</th>
                        <th style="width:10%"></th>
                    </tr>
                    <?php 
                        $rowFormat = "<tr>
                        <td>ITEM</td>
                        <td><input class='equipBox' id='ITEM' type='checkbox'></td>
                        </tr>";
                        $results = retrieveAllIssuedItemsOnStock();
                        $i = $results->num_rows;
                        while($i > 0) {
                            $item = $results->fetch_assoc();
                            echo str_replace("ITEM", $item["item"], $rowFormat);
                            $i--;
                        }
                    ?>
                </table> <br>
                <div style="text-align:right">
                    <button type='button' onClick='process()' class='searchButtonResult' value='Remove Items'>Remove Items</button>
                </div>
            </div>
        </div> 

        <script>
            function process() {
                mods = "";
                items = document.getElementsByClassName("equipBox");
                for (i = 0; i < items.length; i++) {
                    value = items[i].checked;
                    if (value) {
                        item = items[i].id;
                        mods += "|" + item.replace(/ /g, "-") + "_" + value;
                    }
                }
                mods = mods.slice(1);
                redirect("databaseProcessing.php?function=manualRemoveItems&mods=" + encodeURIComponent(mods), true);
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
