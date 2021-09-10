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

    <?php
        if (isset($_GET["action"]) && $_GET["action"] != "") {
            $action = $_GET["action"];
        } else {
            $action = "DON'T TOUCH THE URL!!! Gosh kids these days are not respecting their elders...";
        }
        if (isset($_GET["id"]) && $_GET["id"] != "") {
            $id = $_GET["id"];
        } else {
            $id = "DON'T TOUCH THE URL!!! Gosh kids these days are not respecting their elders...";
        }

        $firstname = getUserValues($id,["firstName"],"users")["firstName"];
        $lastname = getUserValues($id,["lastName"],"users")["lastName"];
    ?>

    <maincontents>
        <div style="vertical-align:text-top;text-align:center">
            <div style="display:inline-block;width:65%">
                <h1><?php echo $action; ?></h1>
                <h3><?php echo $lastname . ", " . $firstname; ?></h3>

                <form id="transaction" action="processing.php" method="POST" onSubmit="return checkValues()">
                    <input form="transaction" type="hidden" id="id" name="id" value="<?php echo $id; ?>">
                    <input form="transaction" type="hidden" id="action" name="action" value="<?php echo $action; ?>">
                    <input form="transaction" type="hidden" id="prev" name="prev" value="">
                    <table style="min-width:0;">
                        <tr>
                            <th style="width:60%">Equipment</th>
                            <th style="width:20%">#</th>
                            <th style="width:20%"></th>
                        </tr>
                        <?php 
                            $rowFormat = "<tr>
                            <td>ITEM</td>
                            <td><input form='transaction' class='equipNum' id='ITEM' name='ITEM' type='number' min='0' value=0></td>
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
                        <input type='submit' form='transaction' class='searchButtonResult' value="<?php echo $action; ?>">
                    </div>
                </form>
            </div>
        </div> 

        <script>
            prevPage = document.referrer;
            item = document.getElementById("prev");
            item.value = prevPage;

            function checkValues() {
                items = document.getElementsByClassName("equipNum");
                for (i = 0; i < items.length; i++) {
                    num = items[i].value;
                    item = items[i].id;
                    if (num == "" || num == 0 || num == "0") {
                        num = 0;
                    } else if (num < 0) {
                        alert(item + " must be greater than or equal to 0.");
                        return false;
                    }
                }
                return true;
            }

            function changeValue(item, value) {
                box = document.getElementById(item);
                if (+ box.value + value >= 0) {
                    box.value = + box.value + value;
                }
            }

            function issueSet(setName) {
                var set = [];

                const recSet = <?php echo getPredefSetsJSArr("RECIssue"); ?>;
                const afxSet = <?php echo getPredefSetsJSArr("AFXIssue"); ?>;
                const customSet = <?php echo getPredefSetsJSArr("customIssue"); ?>;

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
                    alert("Set name is undefined: '" + setName + "'");
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
