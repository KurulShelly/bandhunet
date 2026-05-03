<?php
$host     = "localhost";
$user     = "root";
$password = ""; // Laragon biasanya kosong
$db       = "bandhunet";

$koneksi = mysqli_connect($host, $user, $password, $db);

if (!$koneksi) {
    die("Koneksi gagal: " . mysqli_connect_error());
}
?>