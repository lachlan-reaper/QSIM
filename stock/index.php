<?php 
    require "../databaseFunctions.php";
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
        <a href="../stockMC/">Master Controls</a> <br> <br>
        <table id="tableStock">
            <tr>
                <th style="width:40%">Equipment</th>
                <th style="width:10%">Total</th>
                <th style="width:10%">On Shelf</th>
                <th style="width:10%">On Loan</th>
                <th style="width:10%">Lost/Damaged</th>
                <th style="width:20%"></th>
            </tr>
            <?php 
            $rowFormat = "<tr>
                <td>ITEM</td>
                <td>TOTAL</td>
                <td>SHELF</td>
                <td>LOAN</td>
                <td>LOST</td>
                <td><button type='button'>Add Stock</button> <button type='button'>Remove Stock</button></td>
            </tr>";
            $results = retrieveStock();
            $i = $results->num_rows;
            while($i > 0) {
                $item = $results->fetch_assoc();
                $row = $rowFormat;
                $row = str_replace("ITEM", $item["item"], $row);
                $row = str_replace("TOTAL", $item["total"], $row);
                $row = str_replace("SHELF", $item["onShelf"], $row);
                $row = str_replace("LOAN", $item["onLoan"], $row);
                $row = str_replace("LOST", $item["lostOrDamaged"], $row);
                echo $row;
                $i = $i - 1;
            }
            ?>
        </table>
    </maincontents>

    <footer>
        Lachlan Muir ®2021
    </footer>
</body>

</html>