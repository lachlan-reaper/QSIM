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

        $id = $_GET["id"];
    ?>

    <maincontents>
        <div style="vertical-align:text-top;text-align:center">
            <div style="display:inline-block;width:65%">
                <form action="../issue/processing.php" id='stockForm' method='POST' onSubmit="return confirmation();">
                    <input type='hidden' name='action' value='Set'>
                    <input type='hidden' name='id' value='<?php echo $id; ?>'>
                    <input type='hidden' name='prev' id='prev' value=''>
                    <table style="min-width:0;">
                        <tr>
                            <th style="width:60%">Equipment</th>
                            <th style="width:20%">#</th>
                            <th style="width:20%"></th>
                        </tr>
                        <?php 
                            $rowFormat = "<tr>
                            <td>ITEM</td>
                            <td><input class='equipNum' id='ITEM' name='ITEM' type='number' min='0' value=VALUES></td>
                            <td><button type='button' onClick='changeValue(\"ITEM\", 1)'>+1</button>  <button type='button' onClick='changeValue(\"ITEM\", -1)'>-1</button></td>
                            </tr>";
                            
                            $results = retrieveAllIssuedItemsOnStock();
                            $i = $results->num_rows;
                            while($i > 0) { // Display a formatted row for each item that can be possibly issued
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
                        <input type='submit' class='searchButtonResult' value='Set'>
                    </div>
                </form>
            </div>
        </div> 

        <script>
            el = document.getElementById("prev");
            el.value = document.referrer;
            
            function changeValue(item, value) {
                box = document.getElementById(item);
                if (+ box.value + value >= 0) {
                    box.value = + box.value + value;
                }
            }
            function confirmation () {
                if (confirm('Do you really want to submit the form?')) {
                    return true;
                }
                return false;
            }
        </script>

    </maincontents>

    <footer>
        Lachlan Muir ®2021
    </footer>
</body>
</html>
