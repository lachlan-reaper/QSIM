<?php 
    require "../databaseFunctions.php";
    session_start();
    redirectingUnauthUsers("stock");
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
        <?php
            if (validUser("stockMC", $_SESSION["currentUserAccess"])) { // If this user is allowed access, then the link will be displayed.
                echo "<div style='display:block;width:100%'><a href='../stockMC/'>Master Controls</a> <span style='float:right'><a href='../stockMC/history.php'>Issue History</a></span></div> <br>";
            }
        ?>
        <table id="tableStock">
            <tr>
                <th style="width:40%">Equipment</th>
                <th style="width:15%">Total</th>
                <th style="width:15%">On Shelf</th>
                <th style="width:15%">On Loan</th>
                <th style="width:15%">Lost/Damaged</th>
            </tr>
            <?php 
                $rowFormat = "<tr>
                    <td>ITEM</td>
                    <td>TOTAL</td>
                    <td>SHELF</td>
                    <td>LOAN</td>
                    <td>LOST</td>
                </tr>";
                $results = retrieveStock();
                $i = $results->num_rows;
                while($i > 0) { // Display a row for each item currently in stock
                    $item = $results->fetch_assoc();
                    $row = $rowFormat;
                    $row = str_replace("ITEM", $item["item"], $row);
                    $row = str_replace("TOTAL", $item["total"], $row);
                    $row = str_replace("SHELF", $item["onShelf"], $row);
                    $row = str_replace("LOAN", $item["onLoan"], $row);
                    $row = str_replace("LOST", $item["lostOrDamaged"], $row);
                    echo $row;
                    $i--;
                }
            ?>
        </table>
    </maincontents>

    <footer>
        Lachlan Muir ®2021
    </footer>
</body>

</html>