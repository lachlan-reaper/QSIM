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

    <maincontents>
        <div style="vertical-align:text-top;text-align:center">
        <?php
            $function = $_GET["function"]; 
            if (! ($function == "manualAddUser" or $function == "manualModifyUser")) {
                die("Invalid URL defined function.");
            }
            if (isset($_GET["id"])) {
                $id = "&id=" . $_GET["id"];
            } else {
                $id = "";
            }
            echo "<form action='databaseProcessing.php?function=$function$id' method='POST' enctype='multipart/form-data' onSubmit='return confirmForm()'>";
        ?>
                <div style="display:inline-block;width:50%">
                    <table style="min-width:0;">
                        <tr>
                            <th style="width:70%">Variable</th>
                            <th style="width:30%">Value</th>
                        </tr>
                        <?php 
                            if ($function == "manualAddUser") {
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
                                        $name = preg_replace("/[A-Z]/", " $0", $name); // Adds a space in front of every capital letter, since variables in the DB are camel case.
                                        $name = ucfirst($name);
                                    }

                                    if (substr($item["Type"], 0, 3) == "int") { // Ignores the rest of the var since that holds the varying size of the variable.
                                        $row = $rowFormatNumInput;
                                    } else {
                                        $row = $rowFormatTextInput;
                                    }

                                    $row = str_replace("ITEM", $item["Field"], $row);
                                    $row = str_replace("NAME", $name, $row);
                                    echo $row;
                                }
                            } else if ($function == "manualModifyUser") {
                                $rowFormatTextInput = "<tr>
                                <td>NAME</td>
                                <td><input class='textInputs' id='ITEM' name='ITEM' type='text' autocomplete='off' value='VALUE' placeholder='VALUE' required></td>
                                </tr>";
                                $rowFormatNumInput = "<tr>
                                <td>NAME</td>
                                <td><input class='numInputs' id='ITEM' name='ITEM' type='number' autocomplete='off' min='0' value=VALUE required></td>
                                </tr>";

                                $results = retrieveAllUserColumns();
                                $i = $results->num_rows;
                                while ($i > 0) {
                                    $i--;
                                    $item = $results->fetch_assoc();
                                    $name = $item["Field"];

                                    $id = $_GET['id'];
                                    $value = getUserValue($id, $name, "users");
                                    
                                    // Converting behind the scenes variables to more relevant common words and skipping some hidden variables.
                                    if ($name == "access" or $name == "userpass") {
                                        continue;
                                    } else {
                                        $name = preg_replace("/[A-Z]/", " $0", $name); // Adds a space in front of every capital letter, since variables in the DB are camel case.
                                        $name = ucfirst($name);
                                    }

                                    if (substr($item["Type"], 0, 3) == "int") { // Ignores the rest of the var since that holds the varying size of the variable.
                                        $row = $rowFormatNumInput;
                                    } else {
                                        $row = $rowFormatTextInput;
                                    }
                                    
                                    $row = str_replace("ITEM", $item["Field"], $row);
                                    $row = str_replace("NAME", $name, $row);
                                    $row = str_replace("VALUE", $value, $row);
                                    echo $row;
                                }
                            } else {
                                echo "ERROR: URL defined function is invalid.";
                            }
                        ?>
                        <tr>
                            <td>Profile Picture</td>
                            <td><input class='profilePic' id='picture' name='picture' type='file' <?php if ($function == "manualAddUser") { echo "required"; } ?>></td>
                        </tr>
                    </table>
                    <?php if ($function == "manualModifyUser") { echo "<div style='text-align:left'>Please note that you can leave the picture file empty here, this will leave the current photo as is.</div>"; } ?> <br>
                    <div style="text-align:right">
                        <?php
                            if ($function == "manualAddUser") {
                                $word = "Add User";
                            } else if ($function == "manualModifyUser") {
                                $word = "Modify User";
                            } else {
                                echo "ERROR: URL defined function is invalid.";
                            }
                            echo "<button type='submit' class='searchButtonResult' value='$word'>$word</button>";
                        ?>
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
