<?php 
    require "../functions.php";
    redirectingUnauthUsers("search");
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
        document.getElementById("searchTab").className = "activetab";
    </script>

    <maincontents style="display: flex;">
        <span style="width: 100%; align-self:center;" >
            <div>
                <div style="float:right"><a href="../advancedSearch/">Advanced Search</a></div> <br>
            </div> <br>
            <form action="../searchResult/" method="get">
                <input type="text" id="searchQuery" name="searchQuery" class="searchBarMain"> <br> <br>
                <input type="hidden" id="searchFilters" name="searchFilters" value="">
                <div style="text-align:center;">
                    <input type="submit" class="searchButtonMain" value="Search"></input>
                </div>
            </form>
        </span>
    </maincontents>

    <footer>
        Lachlan Muir ®2021
    </footer>
</body>
</html>