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
        <input type="text" id="searchQuery" name="searchQuery" class="searchBarResult">
        <span style="text-align:center;">
            <button type="button" class="searchButtonResult" onClick="formatFilters()" value="Search">Search</button>
        </span> <br> <br> <br>

        <div style="vertical-align:text-top">
            <div style="display:inline-block;width:65%">
                <table id="tableFilters" style="min-width:0;">
                    <tr>
                        <th style="width:70%">Equipment</th>
                        <th style="width:10%" ></th>
                        <th style="width:20%">#</th>
                    </tr>
                    <?php 
                    $rowFormat = "<tr>
                    <td>ITEM</td>
                    <td style='text-align:center'><select class='equipComp' id='ITEM'>
                        <option></option>
                        <option><</option>
                        <option><=</option>
                        <option>=</option>
                        <option>>=</option>
                        <option>></option>
                        <option>!=</option>
                    </select></td>
                    <td><input class='equipNum' type='number' min='0'></td>
                    </tr>";
                    $results = retrieveAllIssuedItemsOnStock();
                    $i = $results->num_rows;
                    while($i > 0) {
                        $item = $results->fetch_assoc();
                        echo str_replace("ITEM", $item["item"], $rowFormat);
                        $i = $i - 1;
                    }
                    ?>
                </table>
            </div>
            <div style="display:inline-block;width:30%;vertical-align:top;">
                <table id="tableRank" style="min-width:0;">
                    <tr>
                        <th style="width:80%">Rank</th>
                        <th style="width:20%" ></th>
                    </tr>
                    <tr>
                        <td>Officers</td>
                        <td style="text-align:center"><input class="rankCheck" id="Officers" type="checkbox"></td>
                    </tr>
                    <tr>
                        <td>CUO</td>
                        <td style="text-align:center"><input class="rankCheck" id="CUO" type="checkbox"></td>
                    </tr>
                    <tr>
                        <td>WO1</td>
                        <td style="text-align:center"><input class="rankCheck" id="WO1" type="checkbox"></td>
                    </tr>
                    <tr>
                        <td>WO2</td>
                        <td style="text-align:center"><input class="rankCheck" id="WO2" type="checkbox"></td>
                    </tr>
                    <tr>
                        <td>SGT</td>
                        <td style="text-align:center"><input class="rankCheck" id="SGT" type="checkbox"></td>
                    </tr>
                    <tr>
                        <td>CPL</td>
                        <td style="text-align:center"><input class="rankCheck" id="CPL" type="checkbox"></td>
                    </tr>
                    <tr>
                        <td>LCPL</td>
                        <td style="text-align:center"><input class="rankCheck" id="LCPL" type="checkbox"></td>
                    </tr>
                    <tr>
                        <td>REC</td>
                        <td style="text-align:center"><input class="rankCheck" id="REC" type="checkbox"></td>
                    </tr>
                </table>
            </div>
        </div>

        <script>
        function formatFilters() {
            var filters = "";
            var i;
            var el;
            var num;

            var searchQuery = document.getElementById("searchQuery").value;
            var equipCompEls = document.getElementsByClassName("equipComp");
            var equipNumEls = document.getElementsByClassName("equipNum");
            
            for (i = 0; i < equipCompEls.length; i++) {
                el = equipCompEls[i];
                if (el.value != "") {
                    num = equipNumEls[i].value;
                    if (num == "") {
                        num = 0;
                    } else if (num < 0) {
                        alert(el.id + " must be greater than or equal to 0, or disabled as a filter.");
                        return;
                    }
                    filters += "|" + el.id.replace(/ /g, "-") + "_" + el.value + "_" +  num;
                }
            }

            var rankEls = document.getElementsByClassName("rankCheck");
            for (i = 0; i < rankEls.length; i++) {
                el = rankEls[i];
                if (el.checked == true) {
                    filters += "|rank_=_" + el.id;
                }
            }
            filters = filters.slice(1);
            window.location.href = "../searchResult/?searchQuery=" + searchQuery + "&searchFilters=" + encodeURIComponent(filters);
        }
        </script>

    </maincontents>

    <footer>
        Lachlan Muir ®2021
    </footer>
</body>
</html>