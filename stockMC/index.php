<?php 
    require "../functions.php";
    redirectingUnauthUsers("stock");
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
        document.getElementById("stockTab").className = "activetab";
    </script>

    <maincontents>
        <!-- MUST CHANGE ACTION!!!! -->
        <form action="../stock/">
            <input type="text">
            <input type="submit" id="submit" value="Submit">
        </form>
    </maincontents>

    <footer>
        Lachlan Muir ®2021
    </footer>
</body>

</html>