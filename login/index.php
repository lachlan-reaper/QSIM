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
            <!-- MUST CHANGE ACTION!!!! -->
            <form action="../loginVerification.php" method="post">
                <input type="text" id="username" name="user" placeholder="Username" required> <br>
                <input type="password" id="password" name="pass" placeholder="Password" required> <br>
                <input type="submit" id="submit" value="Submit">
            </form>
        </div>
    </loginbox>
</body>