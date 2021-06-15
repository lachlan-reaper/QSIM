<?php
    require "../databaseFunctions.php";
    session_start();
    redirectingUnauthUsers("stockMC");
    // !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!! ADD MULTIPLE ROWS OF INPUT !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
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
                        <th style="width:50%">Equipment Name</th>
                        <th style="width:30%">Initial Stock on Shelf</th>
                        <th style="width:20%"></th>
                    </tr>
                    <tr>
                        <td><input class='equipName' id='item' type='text'></td>
                        <td><input class='equipNum' id='num' type='number' min='0' value=0></td>
                        <td><button type='button' onClick='changeValue("num", 1)'>+1</button>  <button type='button' onClick='changeValue("num", -1)'>-1</button></td>
                    </tr>
                </table> <br>
                <div style="text-align:right">
                    <button type='button' onClick='process()' class='searchButtonResult' value='Add Items'>Add Items</button>
                </div>
            </div>
        </div> 

        <script>
            function process() {
                mods = "";
                items = document.getElementsByClassName("equipName");
                nums = document.getElementsByClassName("equipNum");
                for (i = 0; i < items.length; i++) {
                    item = items[i].value;
                    num = nums[i].value;
                    if (num < 0) {
                        alert(item + " must be greater than or equal to 0.");
                        return;
                    } else {
                        mods += "|" + item.replace(/ /g, "-") + "_" + num;
                    }
                }
                mods = mods.slice(1);
                redirect("databaseProcessing.php?function=manualAddItems&mods=" + encodeURIComponent(mods), true);
            }

            function changeValue(item, value) {
                box = document.getElementById(item);
                if (+ box.value + value >= 0) {
                    box.value = + box.value + value;
                }
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
