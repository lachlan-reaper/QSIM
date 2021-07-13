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
            <div style="display:inline-block;width:40%">
                <table style="min-width:0;">
                    <tr>
                        <th style="width:40%">Appointment</th>
                        <th style="width:60%">Contact</th>
                    </tr>
                    <?php 
                        $rowFormat = "<tr>
                        <td>APPT</td>
                        <td><input class='contacts' id='APPT' type='text' value=VALUES style='width:100%'></td>
                        </tr>";
                        $contacts = getContacts();

                        // Create and display the RQMS's contact details
                        $row1 = $rowFormat;
                        $row1 = str_replace("APPT", $contacts[0][0], $row1);
                        $row1 = str_replace("VALUES", $contacts[0][1], $row1);
                        echo $row1;
                        
                        // Create and display the QM's contact details
                        $row2 = $rowFormat;
                        $row2 = str_replace("APPT", $contacts[1][0], $row2);
                        $row2 = str_replace("VALUES", $contacts[1][1], $row2);
                        echo $row2;
                    ?>
                </table> <br>
                <div style="text-align:right">
                    <button type='button' onClick='process()' class='searchButtonResult' value='Set'>Set</button>
                </div>
            </div>
        </div> 

        <script>
            function process() {
                contacts = "";
                prevPage = document.referrer;
                items = document.getElementsByClassName("contacts");
                
                for (i = 0; i < items.length; i++) {
                    appt = items[i].id;
                    contact = items[i].value;
                    contacts += "|" + appt + ":" + contact;
                }
                contacts = contacts.slice(1);
                window.location.href = "../stockMC/setInfo.php?func=contacts&contacts=" + contacts;
            }
        </script>

    </maincontents>

    <footer>
        Lachlan Muir ®2021
    </footer>
</body>
</html>
