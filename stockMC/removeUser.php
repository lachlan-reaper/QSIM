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

    <script>
        document.getElementById("searchTab").className = "activetab";
    </script>

    <maincontents>

        <form action="removeUser.php" method="GET">
            <input type="text" id="searchQuery" name="searchQuery" class="searchBarResult">
            <span style="text-align:center;">
                <input type="submit" class="searchButtonResult" value="Search"></input>
            </span>
        </form> <br> <br> <br>

        <script>
        bar = document.getElementById("searchQuery");
        URL = window.location.href;
        pos1 = URL.indexOf("searchQuery=");
        if (pos1 == -1) {
            query = "";
        } else {
            query = URL.slice(pos1+12);
            query = decodeURIComponent(query);
            query = query.replace(/\+/g, " ");
            if (isNaN(query.trim())) {
                bar.value = query.trim();
            }
        }
        </script>

        <table id="tableSearch" style="width: 85%; margin-left: 7.5%;">
            <tr>
                <th style="width:45%" colspan="2">Name</th>
                <th style="width:5%">PL</th>
                <th style="width:10%">Rank</th>
                <th style="width:10%">APPT</th>
                <th style="width:30%"></th>
            </tr>

            <?php
                if (!isset($_GET["searchQuery"])) {
                    $userQuery = "";
                } else {
                    $userQuery = $_GET["searchQuery"];
                }

                if ($userQuery == NULL) {
                    $userQuery = "";
                }

                $searchFilters = formatSearchFilters(""); // To use a prefined function, this is necessary
                $results = retrieveSearchQueryResults($userQuery, $searchFilters);

                $i = $results->num_rows;
                if ($i === 0) {
                    echo "<tr><td colspan=6 style='text-align:center;color:red;'>NO USERS FOUND</td></tr>";
                } else {
                    while($i > 0) {
                        $row = $results->fetch_assoc();
                        $rowFormat = "<tr> <td>LASTNAME</td> <td>FIRSTNAME</td> <td>PLATOON</td> <td>RANK</td> <td>APPOINTMENT</td>
                        <td> <a href='databaseProcessing.php?function=manualRemoveUser&id=ID' onClick='return confirmForm();'> <button type='button'> DELETE </button> </a> </td> </tr>";
                        
                        $firstname = ucfirst($row['firstName']);
                        $rowFormat = str_replace('FIRSTNAME', $firstname, $rowFormat);

                        $lastname = ucfirst($row['lastName']);
                        $rowFormat = str_replace('LASTNAME', $lastname, $rowFormat);

                        $appointment = strtoupper($row['appointment']);
                        $rowFormat = str_replace('APPOINTMENT', $appointment, $rowFormat);

                        $rank = strtoupper($row['rank']);
                        $rowFormat = str_replace('RANK', $rank, $rowFormat);

                        $platoon = strtoupper($row['platoon']);
                        $rowFormat = str_replace('PLATOON', $platoon, $rowFormat);

                        $id = $row['id'];
                        $rowFormat = str_replace('ID', $id, $rowFormat);

                        echo $rowFormat;
                        $i--;
                    }
                }

            ?>

        </table>
    </maincontents>

    <script>

        function confirmForm () {
            return confirm('Do you really want to submit the form?'); 
        }

    </script>

    <footer>
        Lachlan Muir ®2021
    </footer>
</body>
</html>