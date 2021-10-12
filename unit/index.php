<?php 
    require "../databaseFunctions.php";
    session_start();
    redirectingUnauthUsers("unit");
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
        document.getElementById("unitTab").className = "activetab";
    </script>

    <maincontents>
        <table class="profilePage">
            <tr style="vertical-align:top;">
                <td colspan=4 style="text-align:center;">
                    <profilePageBox style="min-width:0;width:auto;text-align:left;">
                        <?php
                            echo formatCoyStructure('RHQ', false);
                        ?>
                    </profilePageBox>
                </td>
            </tr>
            <tr style="vertical-align:top;">
                <td style="width:25%;">
                    <profilePageBox>
                        <?php
                            echo formatCoyStructure('A');
                        ?>
                    </profilePageBox>
                </td>
                <td style="width:25%;">
                    <profilePageBox>
                        <?php
                            echo formatCoyStructure('B');
                        ?>
                    </profilePageBox>
                </td>
                <td style="width:25%;">
                    <profilePageBox>
                        <?php
                            echo formatCoyStructure('C');
                        ?>
                    </profilePageBox>
                </td>
                <td style="width:25%;">
                    <profilePageBox>
                        <?php
                            echo formatCoyStructure('D');
                        ?>
                    </profilePageBox>
                </td>
            </tr>
            <tr style="vertical-align:top;">
                <td colspan=4 style="text-align:center;">
                    <profilePageBox style="min-width:0;width:auto;text-align:left;">
                        <?php
                            echo formatCoyStructure('SPT', false);
                        ?>
                    </profilePageBox>
                </td>
            </tr>
        </table>
    </maincontents>

    <footer>
        Lachlan Muir ®2021
    </footer>
</body>

</html>