<?php 
    require "../functions.php";
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
        $header = fopen("../headerFormat.php", "r") or die("Unable to open file!");
        echo fread($header,filesize("../headerFormat.php"));
        fclose($header);
    ?>

    <script>
        document.getElementById("homeTab").className = "activetab";
    </script>

    <maincontents>
        helloll<br>
        helloll<br>
        helloll<br>
        helloll<br>
        helloll<br>
        helloll<br>
        helloll<br>
        helloll<br>
        helloll<br>
        helloll<br>
        helloll<br>
        helloll<br>
        helloll<br>
        helloll<br>
        helloll<br>
        helloll<br>
        helloll<br>
        helloll<br>
        helloll<br>
        helloll<br>
        helloll<br>
        helloll<br>
        helloll<br>
        helloll<br>
        helloll<br>
        helloll<br>
        helloll<br>
        helloll<br>
        helloll<br>
        helloll<br>
        helloll<br>
        helloll<br>
        helloll<br>
        helloll<br>
        helloll<br>
        helloll<br>
        helloll<br>
        helloll<br>
        helloll<br>
        helloll<br>
        helloll<br>
        helloll<br>
        helloll<br>
        helloll<br>
        helloll<br>
        helloll<br>
        helloll<br>
        helloll<br>
        helloll<br>
    </maincontents>

    <footer>
        Lachlan Muir ®2021
    </footer>
</body>

</html>