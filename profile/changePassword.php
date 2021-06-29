<html>
<script>
function redirect (URL, confirmation) {
    if (confirmation) {
        alert('Your password has been changed.');
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
    header("Location: http://" . $_SESSION["websiteLoc"] . "/profile?id=$id");
    die("This is not your account!!!");
} // Else that person can freely change their own password

$userpass = getUserValue($id, "userpass", "users");
$oldpass = $_POST["oldpass"];
if(! password_verify($oldpass, $userpass)) {
    header("Location: http://" . $_SESSION["websiteLoc"] . "/profile/retrievePassword.php?id=$id&error=true");
    die("Wrong password!!!");
}

$newpass = $_POST["newpass"];
$hashedpass = password_hash($newpass, PASSWORD_BCRYPT);
$sql = "UPDATE `users` SET `userpass` = '$hashedpass' WHERE `id` = '$id';";
$results = $_SESSION["conn"]->query($sql);

echo "<script>redirect(\"http://" . $_SESSION["websiteLoc"] . "/profile/?id=$id\", true)</script>";

?>

</html>