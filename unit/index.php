<?php 
    require "../databaseFunctions.php";
    session_start();
    redirectingUnauthUsers("home");
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
        document.getElementById("homeTab").className = "activetab";
    </script>

    <maincontents>
        <table class="profilePage">
            <tr style="vertical-align:top;">
                <td style="width:50%">
                    <profilePageBox>
                        hi
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