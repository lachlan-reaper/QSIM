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
                <form action="setInfo.php" id="plStruct" method="POST">
                    <input type="hidden" form="plStruct" name="func" value="plStruct">
                    <table style="min-width:0;">
                        <tr>
                            <th style="width:20%">Platoon</th>
                            <th style="width:80%">Structure</th>
                        </tr>
                        <?php 
                            $appts = getAppointments(false);

                            $options = "";
                            $max = count($appts);
                            $i = 0;
                            while ($max > $i) {
                                $appt = $appts[$i];
                                $options = $options . "<option value='$appt'>$appt</option>";
                                $i++;
                            }

                            $selectFormat = "<select class='struct' form='plStruct' name='PLATOON'><option value='None'>None</option>" . $options . "</select>";

                            $platoons = getPlatoons();
                            $structure = getPlatoonStructure();
                            $max = count($platoons);
                            $i = 0;
                            while ($max > $i) {
                                $struct = $structure[$platoons[$i]];
                                $selects = "";

                                $select = str_replace("PLATOON", $platoons[$i] . "-h", $selectFormat);
                                $select = str_replace("form='plStruct'", "hidden", $select);
                                $selects = $selects . $select;

                                $num = count($struct);
                                $x = 0;
                                while ($num > $x) {
                                    $appt = $struct[$x];
                                    $select = str_replace("='$appt'", "='$appt' selected", $selectFormat);
                                    $select = str_replace("PLATOON", $platoons[$i] . "-" . $x, $select);
                                    $selects = $selects . $select;
                                    $x++;
                                }

                                $row = "<tr><td>PLATOON</td><td id='$i'>" . $selects . "<button type='button' id='$i-btn' onClick='addInput($i, $x, \"PLATOON\")'>Add more Appts</button></td></tr>";
                                $row = str_replace("PLATOON", $platoons[$i], $row);
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
