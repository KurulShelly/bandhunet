<?php
session_start();

// Fungsi untuk mengecek apakah user sudah login
function cek_login() {
    if (!isset($_SESSION['id_user'])) {
        header("Location: login.php");
        exit;
    }
}
?>