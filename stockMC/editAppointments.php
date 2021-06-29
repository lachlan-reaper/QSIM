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
                <table style="min-width:0;">
                    <tr>
                        <th style="width:60%">Appointment</th>
                        <th style="width:40%">Access</th>
                    </tr>
                    <?php 
                        $rowFormat = "<tr>
                        <td><input class='appointment' type='text' value='APPT'></td>
                        <td><select class='access'>
                            <option value='admin'>Admin</option>
                            <option value='qstore'>Q Store</option>
                            <option value='rank'>Rank</option>
                            <option value='recruit'>Recruit</option>
                        </select></td>
                        </tr>";
                        $myfile = fopen("../appointmentAccessRoles.aars", "r") or die("Internal server error: Unable to open file!");
                        $file = fread($myfile, filesize("../appointmentAccessRoles.aars"));
                        $lines = explode("|", $file);
                        
                        $i = 0;
                        $max = count($lines);
                        while ($i < $max) {
                            $row = $rowFormat;
                            $lines[$i] = trim($lines[$i]);

                            if ($lines[$i] == "") {
                                $i++;
                                continue;
                            }

                            $lines[$i] = explode(":", $lines[$i]);
                            $appt = $lines[$i][0];
                            $access = $lines[$i][1];
                            $row = str_replace("APPT", $appt, $row);
                            $row = str_replace("value='$access'", "value='$access' selected", $row);
                            echo $row;
                            $i++;
                        }
                    ?>
                </table> <br>
                <div style="text-align:right">
                    <button type='button' class='searchButtonResult' onClick='process()' value='Set'>Set</button>
                </div>
            </div>
        </div> 
        <script>
        function process() {
            var file = "";
            var el;
            var appointmentEls = document.getElementsByClassName("appointment");
            var accessEls = document.getElementsByClassName("access");
            
            for (i = 0; i < appointmentEls.length; i++) {
                file += "|" + appointmentEls[i].value + ":" + accessEls[i].value;
            }

            file = file.slice(1);
            window.location.href = "setInfo.php?func=appointments&file=" + file.replace(/ /g, "_");
        }
        </script>
    </maincontents>

    <footer>
        Lachlan Muir ®2021
    </footer>
</body>
</html>
