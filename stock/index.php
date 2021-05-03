<?php 
    require "../functions.php";
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
        $header = fopen("../headerFormat.php", "r") or die("Unable to open file!");
        echo fread($header,filesize("../headerFormat.php"));
        fclose($header);
    ?>

    <script>
        document.getElementById("stockTab").className = "activetab";
    </script>

    <maincontents>
        <a href="stockMC.html">Master Controls</a> <br> <br>
        <table id="tableStock">
            <tr>
                <th style="width:40%">Equipment</th>
                <th style="width:10%">Total</th>
                <th style="width:10%">On Shelf</th>
                <th style="width:10%">On Loan</th>
                <th style="width:10%">Lost/Damaged</th>
                <th style="width:20%"></th>
            </tr>
            <tr>
                <td>DPCU Shirt</td>
                <td>1000</td>
                <td>900</td>
                <td>100</td>
                <td>10</td>
                <td><button type="button">Add Stock</button> <button type="button">Remove Stock</button></td>
            </tr>
            <tr>
                <td>DPCU Pants</td>
                <td>1000</td>
                <td>900</td>
                <td>100</td>
                <td>10</td>
                <td><button type="button">Add Stock</button> <button type="button">Remove Stock</button></td>
            </tr>
            <tr>
                <td>DPCU Boots</td>
                <td>1000</td>
                <td>900</td>
                <td>100</td>
                <td>10</td>
                <td><button type="button">Add Stock</button> <button type="button">Remove Stock</button></td>
            </tr>
            <tr>
                <td>DPCU Hats</td>
                <td>1000</td>
                <td>900</td>
                <td>100</td>
                <td>10</td>
                <td><button type="button">Add Stock</button> <button type="button">Remove Stock</button></td>
            </tr>
        </table>
    </maincontents>

    <footer>
        Lachlan Muir ®2021
    </footer>
</body>

</html>