<!DOCTYPE html>
<html lang="en-us">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../stylesLogin.css">
    <link rel="icon" href="../images/logo.svg" sizes="any" type="image/svg+xml">
    <title>QSIM</title>
</head>

<body>
    <div class="loginBGImage"></div>
    <loginbox>
        <img src="../images/Cadet crest.png" width="100%"> <br> 
        <div style="padding-top: 15px; text-align: center;">
            <?php
                require "../databaseFunctions.php";
                session_start();
                if(empty($_SESSION) or ! isset($_SESSION["currentUserId"])){
                    // Pass
                } else if ($_SESSION["currentUserId"] === "Invalid") {
                    echo "<div id='invalidLogin'>Invalid Username or Password</div>";
                }
            ?>
            <span style="float:right;font-size:medium;"><a href="" onClick="displayContact()">Forgot Password?</a></span>
            <form action="loginVerification.php" method="post">
                <input type="text" id="username" name="user" placeholder="Username" required> <br>
                <input type="password" id="password" name="pass" placeholder="Password" required> <br>
                <input type="submit" id="submit" value="Submit">
            </form>
        </div>
    </loginbox>
    <script>
        function displayContact () {
            alert("Please contact the QM at <?php echo getContacts()["QM"]; ?> and ask for your password to be reset.");
        }
    </script>
</body>

</html>