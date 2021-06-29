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
if ($id != $_SESSION["currentUserId"]) {
    redirectingUnauthUsers("issue");
} // Else that person can freely change their own password

$hashedpass = password_hash("cadet", PASSWORD_BCRYPT);
$sql = "UPDATE `users` SET `userpass` = '$hashedpass' WHERE `id` = '$id';";
$results = $_SESSION["conn"]->query($sql);

echo "<script>redirect(\"http://" . $_SESSION["websiteLoc"] . "/profile/?id=$id\", true)</script>";

?>

</html>