<?php 
    require "../databaseFunctions.php";
    session_start();
    establishConnection();
?>

<!DOCTYPE html>
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
        <div style="padding-top:15px;text-align:center;width:100%;">
            <?php
                // Checks if the user has already tried to do his but provided the wrong password
                if(isset($_GET["error"])) {
                    if ($_GET["error"] == "true") {
                        echo "<div style='color:red;'><b>Sorry but the provided passwords were not valid or were not the same.</b></div><br>";
                    }
                }
            ?>
            <form action="changePassword.php?id=<?php echo $_GET["id"]?>" method="POST" onSubmit="return check()">
                <table style="min-width:0;width:20%;margin-left:auto;margin-right:auto;">
                    <tr>
                        <th>
                            Change Password:
                        </th>
                    </tr>
                    <tr>
                        <td>
                            <input type="password" id="oldpass" name="oldpass" placeholder="Old Password" required> <br>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <input type="password" id="oldpass1" name="oldpass1" placeholder="Confirm Old Password" required> <br>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <input type="password" id="newpass" name="newpass" placeholder="New Password" required> <br>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <input type="password" id="newpass1" name="newpass1" placeholder="Confirm New Password" required> <br>
                        </td>
                    </tr>
                </table>
                <br>
                <input type="submit" id="submit" class="searchButtonMain" value="Change" style="height:auto;width:auto;padding:7px;">
            </form>
        </div>

        <script>
            function check() {
                el1 = document.getElementById("oldpass");
                el2 = document.getElementById("oldpass1");
                el3 = document.getElementById("newpass");
                el4 = document.getElementById("newpass1");
                if (el1.value == el2.value && el3.value == el4.value) {
                    return true;
                } else {
                    window.location.href = "http://<?php echo $_SESSION["websiteLoc"] ?>/profile/changePassword.php?id=<?php echo $_GET["id"]?>&error=true";
                    return false;
                }
            }
        </script>
    </maincontents>

    <footer>
        Lachlan Muir ®2021
    </footer>
</body>

</html>