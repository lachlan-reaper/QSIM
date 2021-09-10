<?php 
    require "../databaseFunctions.php";
    session_start();
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

    <maincontents>
        
        Filters: 

        <?php 
        // For use in other PHP parts as well
        if (isset($_GET["searchQuery"])) {
            $userQuery = $_GET["searchQuery"];
        } else {
            $userQuery = "";
        }
        
        if (isset($_GET["searchFilters"])) {
            $searchFilters = $_GET["searchFilters"];
        } else {
            $searchFilters = "";
        }

        if ($searchFilters != "") {
            $filterBoxFormat = '<span class="filterBox">ITEM COMPARATOR VALUE  <button type="button" onClick="removeFilter(\'ITEM_COMPARATOR_VALUE\')" ><b>X</b></button></span> ';
        
            $searchFilterBoxes = urldecode($searchFilters);
            $searchFilterBoxes = str_replace("-", " ", $searchFilterBoxes);
        
            $items = formatSearchFilters($searchFilterBoxes);
            
            $num = count($items);
            $i = 0;
            while ($i < $num) { // Formats and displays the filter boxes
                $row = $filterBoxFormat;
                $id = str_replace("-", " ", $items[$i][0]);
                $row = str_replace("ITEM", $id, $row);
                $row = str_replace("COMPARATOR", $items[$i][1], $row);
                $row = str_replace("VALUE", $items[$i][2], $row);
                echo $row;
                $i++;
            }
        }
        ?>

        <br> <br> 

        <form>
            <input type="text" id="searchQuery" name="searchQuery" class="searchBarResult" placeholder="Name or ID Number">
            <input type="hidden" id="searchFilters" name="searchFilters" value="">
            <span style="text-align:center;">
                <input type="submit" class="searchButtonResult" onClick="addSearchFilters()" value="Search"></input>
            </span>
        </form> <br> <br> <br>
        
        <a href="downloadProcessing.php?searchQuery=<?php echo $searchQuery; ?>&searchFilters=<?php echo $searchFilters; ?>">Export List</a> 
        <span style="float:right"><a href="../advancedSearch/">Advanced Search</a></span> <br> <br>

        <table id="tableSearch" style="width: 85%; margin-left: 7.5%;">
            <tr>
                <th style="width:45%" colspan="2">Name</th>
                <th style="width:5%">PL</th>
                <th style="width:10%">Rank</th>
                <th style="width:10%">APPT</th>
                <th style="width:30%"></th>
            </tr>

            <?php
                $searchFilters = urldecode($searchFilters);
                $searchFilters = str_replace("-", " ", $searchFilters);

                $searchFilters = formatSearchFilters($searchFilters);
                $results = retrieveSearchQueryResults($userQuery, $searchFilters);

                $i = $results->num_rows;
                if ($i <= 0) {
                    echo "<tr><td colspan=6 style='text-align:center;color:red;'>NO USERS FOUND</td></tr>";
                } else {
                    while($i > 0) { // Displays all of the searched for users
                        $row = $results->fetch_assoc();
                        echo formatRowSearchResult($row);
                        $i--;
                    }
                }

            ?>

        </table>
        <script>
            bar = document.getElementById("searchQuery");
            URL = window.location.href;

            pos1 = URL.indexOf("searchQuery=");
            pos2 = URL.indexOf("&searchFilters=");

            query = URL.slice(pos1+12, pos2);
            query = decodeURIComponent(query);
            query = query.replace(/\+/g, " ");

            if (isNaN(query.trim())) {
                bar.value = query.trim();
            }

            function removeFilter(filter) {
                URL = window.location.href;
                filter = filter.replace(/ /g, "-");
                filter = encodeURIComponent(filter);
                URL = URL.replace(filter, "");
                filter = filter.replace(/!/g, "%21");
                URL = URL.replace(filter, "");
                encodedSymbol = encodeURIComponent("|");
                URL = URL.replace(encodedSymbol+encodedSymbol, encodedSymbol);
                URL = URL.replace("=" + encodedSymbol, "=");
                if (URL.slice(-3) == encodedSymbol) {
                    URL = URL.slice(0, -3)
                }
                window.location.href = URL;
            }
            function addSearchFilters() { 
                input = document.getElementById("searchFilters");
                URL = window.location.href;
                pos = URL.indexOf("searchFilters=");
                filters = URL.slice(pos+14);
                input.value = decodeURIComponent(filters);
            }
            function redirect (URL, confirmation) {
                if (confirmation) {
                    if (confirm('Do you really want to submit the form?')) {
                        window.location.href = URL;
                    }
                } else {
                    window.location.href = URL;
                }
            }
        </script>
    </maincontents>

    <footer>
        Lachlan Muir ®2021
    </footer>
</body>
</html>