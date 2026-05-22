<?php
$server = "localhost";
$username = "root";
$password = "";
$db_name = "toko_online";

date_default_timezone_set("Asia/Jakarta");

$conn = mysqli_connect($server, $username, $password, $db_name);

if (!$conn) {
    die("Connection failed: ". mysqli_connect_error());
}
?>