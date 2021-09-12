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
                <form action="databaseProcessing.php" id='stockForm' method='POST' onSubmit="return confirmation();">
                    <input type='hidden' name='function' value='manualRemoveItems'>
                    <table style="min-width:0;">
                        <tr>
                            <th style="width:90%">Equipment</th>
                            <th style="width:10%"></th>
                        </tr>
                        <?php 
                            $rowFormat = "<tr>
                            <td>ITEM</td>
                            <td><input class='equipBox' id='ITEM' name='ITEM' type='checkbox'></td>
                            </tr>";
                            $results = retrieveAllIssuedItemsOnStock();
                            $i = $results->num_rows;
                            while($i > 0) { // Displays a row to affect for each currently issued item
                                $item = $results->fetch_assoc();
                                echo str_replace("ITEM", $item["item"], $rowFormat);
                                $i--;
                            }
                        ?>
                    </table> <br>
                    <div style="text-align:right">
                        <input type='submit' class='searchButtonResult' value='Remove Items'>
                    </div>
                </form>
            </div>
        </div> 

        <script>
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
