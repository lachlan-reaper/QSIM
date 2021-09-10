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
            <div style="display:inline-block;width:45%">
                <form action="setInfo.php" id="apptAccess" method="POST">
                    <input type="hidden" form="apptAccess" name="func" value="appointments">
                    <table style="min-width:0;" id="accessTable">
                        <tr>
                            <th style="width:60%">Appointment</th>
                            <th style="width:40%">Access</th>
                        </tr>
                        <?php 
                            if (isset($_GET["extra"])) {
                                $extraNum = $_GET["extra"];
                            } else {
                                $extraNum = 0;
                            }
                            $rowFormat = "<tr name='row'>
                            <td><input class='appointment' form='apptAccess' id='appt' name='APPTS' type='text' value='APPTS' placeholder='Appointment'></td>
                            <td><select class='access' form='apptAccess' id='access' name='APPTSAccess'>
                                <option value='admin'>Admin</option>
                                <option value='qstore'>Q Store</option>
                                <option value='rank'>Rank</option>
                                <option value='recruit'>Recruit</option>
                            </select></td>
                            </tr>";
                            
                            $lines = getAppointments();
                            
                            $i = 0;
                            $max = count($lines);
                            while ($i < $max) { // For each appointment, display a new row
                                $row = $rowFormat;

                                $appt = $lines[$i][0];
                                $access = $lines[$i][1];
                                $row = str_replace("APPTS", $appt, $row);
                                $row = str_replace("value='$access'", "value='$access' selected", $row);
                                echo $row;
                                $i++;
                            }
                            $i = 0;
                            while ($i < $extraNum) { // For each extra appointment, display a new row
                                $row = $rowFormat;
                                $row = str_replace("APPTS", "", $row);
                                $row = str_replace("value='recruit'", "value='recruit' selected", $row);
                                echo $row;
                                $i++;
                            }
                        ?>
                        <tr id="extraRow">
                            <td>
                                <button type="button" id="extra" name="extra" onClick="addRow(1)">Add Rows</button>
                            </td>
                            <td>

                            </td>
                        </tr>
                    </table> <br>
                    <div style="text-align:right">
                        <input type='submit' class='searchButtonResult' value='Set'>
                    </div>
                </form>
            </div>
        </div> 
    </maincontents>

    <script>
        function addRow(num) {
            table = document.getElementById("accessTable");
            row = document.getElementsByName("row")[0];
            clone = row.cloneNode(true);

            btnRow = document.getElementById("extraRow");
            btnRow.remove();

            el1 = clone.cells[0];
            el1.firstElementChild.name = num;
            el1.firstElementChild.value = num;
            el2 = clone.cells[1];
            el2.firstElementChild.name = num + "Access";
            el2.firstElementChild.value = "admin";
            
            table.appendChild(clone);
            table.appendChild(btnRow);

            btn = document.getElementById("extra");
            btn.setAttribute("onClick", "addRow(" + (num + 1) + ")");
        }
    </script>

    <footer>
        Lachlan Muir ®2021
    </footer>
</body>
</html>
