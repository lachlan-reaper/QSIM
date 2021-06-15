<?php
    require "../databaseFunctions.php";
    session_start();
    redirectingUnauthUsers("stockMC");
    // !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!! MUST ACTUALLY MAKE WORK !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
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
            <form action="databaseProcessing.php?function=manualAddUser" method="POST" enctype="multipart/form-data" onSubmit="return confirmForm()">
                <div style="display:inline-block;width:50%">
                    <table style="min-width:0;">
                        <tr>
                            <th style="width:70%">Variable</th>
                            <th style="width:30%">Value</th>
                        </tr>
                        <?php 
                            $rowFormatTextInput = "<tr>
                            <td>NAME</td>
                            <td><input class='textInputs' id='ITEM' name='ITEM' type='text' autocomplete='off' required></td>
                            </tr>";
                            $rowFormatNumInput = "<tr>
                            <td>NAME</td>
                            <td><input class='numInputs' id='ITEM' name='ITEM' type='number' autocomplete='off' min='0' value=0 required></td>
                            </tr>";
                            $results = retrieveAllUserColumns();
                            $i = $results->num_rows;
                            while ($i > 0) {
                                $i--;
                                $item = $results->fetch_assoc();
                                $name = $item["Field"];
                                
                                // Converting behind the scenes variables to more relevant common words.
                                if ($name == "userpass") {
                                    $name = "Password";
                                } else if ($name == "access") {
                                    continue;
                                } else {
                                    $name = preg_replace("/[A-Z]/", " $0", $name);
                                    $name = ucfirst($name);
                                }

                                if (substr($item["Type"], 0, 3) == "int") {
                                    $row = $rowFormatNumInput;
                                } else {
                                    $row = $rowFormatTextInput;
                                }
                                $row = str_replace("ITEM", $item["Field"], $row);
                                $row = str_replace("NAME", $name, $row);
                                echo $row;
                            }
                        ?>
                        <tr>
                            <td>Profile Picture</td>
                            <td><input class='profilePic' id='picture' name='picture' type='file' required></td>
                        </tr>
                    </table> <br>
                    <div style="text-align:right">
                        <button type='submit' class='searchButtonResult' value='Add User'>Add User</button>
                    </div>
                </div>
            </form>
        </div> 
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
