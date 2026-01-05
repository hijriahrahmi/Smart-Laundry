<?php
$host = "localhost";
$user = "root";
$pass = "";
$db   = "db_smartlaundry";

$conn = mysqli_connect($host, $user, $pass, $db);
if (!$conn) {
    die("Koneksi gagal: " . mysqli_connect_error());
}

function esc($data) {
    return htmlspecialchars(trim($data), ENT_QUOTES);
}
?>
