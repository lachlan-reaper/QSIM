<?php
    require "../functions.php";
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
                <form action="databaseProcessing.php" id='stockItem' method='POST' onSubmit="return confirmation();">
                    <input type='hidden' name='function' value='manualAddItems'>
                    <table style="min-width:0;">
                        <tr>
                            <th style="width:50%">Equipment Name</th>
                            <th style="width:30%">Initial Stock on Shelf</th>
                            <th style="width:20%"></th>
                        </tr>
                        <tr>
                            <td><input class='equipName' form='stockItem' id='item' name='name' type='text'></td>
                            <td><input class='equipNum' form='stockItem' id='num' name='num' type='number' min='0' value=0></td>
                            <td><button type='button' onClick='changeValue("num", 1)'>+1</button>  <button type='button' onClick='changeValue("num", -1)'>-1</button></td>
                        </tr>
                    </table> <br>
                    <div style="text-align:right">
                        <input type='submit' form='stockItem' class='searchButtonResult' value='Add Items'>
                    </div>
                </form>
            </div>
        </div> 

        <script>
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
