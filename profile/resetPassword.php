<html>
<script>
function redirect (URL, confirmation) {
    if (confirmation) {
        alert('Your password has changed to "cadet".');
        window.location.href = URL;
    } else {
        window.location.href = URL;
    }
}
</script>

<?php
require "../databaseFunctions.php";
session_start();
establishConnection();

$id = $_GET["id"];
if ($id != $_SESSION["currentUserId"]) { // Makes sure that anyone trying to change a password is only changing their own unles they are an admin
    redirectingUnauthUsers("stockMC");
}

$hashedpass = password_hash("cadet", PASSWORD_BCRYPT);
$sql = "UPDATE `users` SET `userpass` = '$hashedpass' WHERE `id` = '$id';";
$results = $_SESSION["conn"]->query($sql);

echo "<script>redirect(\"http://" . $_SESSION["websiteLoc"] . "/profile/?id=$id\", true)</script>";

?>

</html>