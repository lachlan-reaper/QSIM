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
            <div style="display:inline-block;width:80%">
                <form action="setInfo.php" id="coyStruct" method="POST">
                    <input type="hidden" form="coyStruct" name="func" value="coyStruct">
                    <table style="min-width:0;">
                        <tr>
                            <th style="width:20%">Company</th>
                            <th style="width:80%">Platoon</th>
                        </tr>
                        <?php
                            $rowFormat = "<input form='coyStruct' id='platoon' name='PLT' type='text' value='PLTS' placeholder='Platoon'>";

                            $companies = getCompanies();
                            $structure = getCompanyStructure();
                            $max = count($companies);
                            $i = 0;
                            while ($max > $i) {
                                $struct = $structure[$companies[$i]];
                                $selects = "";

                                $select = str_replace("PLTS", "", $rowFormat);
                                $select = str_replace("PLT", $companies[$i] . "-h", $select);
                                $select = str_replace("form='coyStruct'", "hidden", $select);
                                $selects = $selects . $select;

                                $num = count($struct);
                                $x = 0;
                                while ($num > $x) {
                                    $appt = $struct[$x];
                                    $select = str_replace("='$appt'", "='$appt' selected", $rowFormat);
                                    $select = str_replace("PLTS", $struct[$x], $select);
                                    $select = str_replace("PLT", $companies[$i] . "-" . $x, $select);
                                    $selects = $selects . $select;
                                    $x++;
                                }

                                $row = "<tr><td>PLT</td><td id='$i'>" . $selects . "<button type='button' id='$i-btn' onClick='addInput($i, $x, \"PLT\")'>Add more PLs</button></td></tr>";
                                $row = str_replace("PLT", $companies[$i], $row);
                                echo $row;
                                $i++;
                            }
                        ?>
                    </table> <br>
                    <div style="text-align:right">
                        <input type='submit' class='searchButtonResult' value='Set'>
                    </div>
                </form>
            </div>
        </div> 
    </maincontents>
    <script>
        function addInput(elementID, num, platoon) {
            tblEl = document.getElementById(elementID);
            inpEl = document.getElementsByName(platoon + "-h")[0].cloneNode(true);
            btn = document.getElementById(elementID + "-btn");
            btn.remove();

            inpEl.setAttribute("name", platoon + "-" + num);
            inpEl.setAttribute("value", "");
            inpEl.hidden = false;
            btn.setAttribute("onClick", "addInput(" + elementID + ", " + (num + 1) + ", '" + platoon + "')");

            tblEl.appendChild(inpEl);
            tblEl.appendChild(btn);
        }
    </script>

    <footer>
        Lachlan Muir ®2021
    </footer>
</body>
</html>
