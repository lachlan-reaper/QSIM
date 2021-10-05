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

        // Makes sure that the searchQuery parameter is set for the rest of the function. This stops warning messages from popping up and confusing the user.
        if (isset($_GET["searchQuery"])) {
            $userQuery = $_GET["searchQuery"];
        } else {
            $userQuery = "";
        }
    ?>

    <script>
        document.getElementById("stockTab").className = "activetab";
    </script>

    <maincontents>

        <form action="selectUser.php" method="GET">
            <input type="hidden" id="function" name="function" value="<?php echo $_GET["function"]; ?>">
            <input type="text" id="searchQuery" name="searchQuery" class="searchBarResult" placeholder="Name or ID Number" value="<?php echo $userQuery; ?>">
            <span style="text-align:center;">
                <input type="submit" class="searchButtonResult" value="Search"></input>
            </span>
        </form> <br> <br> <br>

        <table id="tableSearch" style="width: 85%; margin-left: 7.5%;">
            <tr>
                <th style="width:45%" colspan="2">Name</th>
                <th style="width:5%">PL</th>
                <th style="width:10%">Rank</th>
                <th style="width:10%">APPT</th>
                <th style="width:30%"></th>
            </tr>

            <?php
                $searchFilters = formatSearchFilters(""); // To use a prefined function, this is necessary
                $results = getSearchQueryResults($userQuery, $searchFilters);

                $i = $results->num_rows;
                if ($i === 0) {
                    echo "<tr><td colspan=6 style='text-align:center;color:red;'>NO USERS FOUND</td></tr>";
                } else {
                    $rowFormat = "<tr> <td>LASTNAME</td> <td>FIRSTNAME</td> <td>PLATOON</td> <td>RANK</td> <td>APPOINTMENT</td>
                    <td> <a href='SCRIPT.php?function=FUNCTION&id=ID' CLICK> <button type='button'> WORD </button> </a> </td> </tr>";

                    $function = $_GET['function'];
                    $rowFormat = str_replace('FUNCTION', $function, $rowFormat);

                    if ($function == "manualModifyUser") {
                        $word = "Modify";
                        $scriptDestination = "changeUser";
                        $click = "";
                    } else if ($function == "manualRemoveUser") {
                        $word = "DELETE";
                        $scriptDestination = "databaseProcessing";
                        $click = "onClick='return confirmForm();'";
                    } else { // Makes sure that if an error occurs that no unintentional side effects will occur
                        $word = "DO NOT CLICK! ERROR!";
                        $scriptDestination = "../home/";
                        $click = "";
                    }

                    $rowFormat = str_replace('WORD', $word, $rowFormat);
                    $rowFormat = str_replace('SCRIPT', $scriptDestination, $rowFormat);
                    $rowFormat = str_replace('CLICK', $click, $rowFormat);

                    while($i > 0) { // Displays a new row for each searched account with formatted information
                        $row = $results->fetch_assoc();
                        $edittedRow = $rowFormat;
                        
                        $firstname = $row['firstName'];
                        $edittedRow = str_replace('FIRSTNAME', $firstname, $edittedRow);

                        $lastname = $row['lastName'];
                        $edittedRow = str_replace('LASTNAME', $lastname, $edittedRow);

                        $appointment = strtoupper($row['appointment']);
                        $edittedRow = str_replace('APPOINTMENT', $appointment, $edittedRow);

                        $rank = strtoupper($row['rank']);
                        $edittedRow = str_replace('RANK', $rank, $edittedRow);

                        $platoon = strtoupper($row['platoon']);
                        $edittedRow = str_replace('PLATOON', $platoon, $edittedRow);

                        $id = $row['id'];
                        $edittedRow = str_replace('ID', $id, $edittedRow);

                        echo $edittedRow;
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