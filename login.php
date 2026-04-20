<?php
session_start();
include "koneksi.php";

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Kalau sudah login → langsung ke dashboard
if (isset($_SESSION['user'])) {
    header("Location: dashboard.php");
    exit;
}

if (isset($_POST['login'])) {

    // 🔹 Ambil input
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);

    // 🔹 Query user
    $query = mysqli_query($conn, "SELECT * FROM users WHERE username='$username' AND password='$password'");

    if (!$query) {
        die("Query Error: " . mysqli_error($conn));
    }

    $data = mysqli_fetch_assoc($query);

    if ($data) {

        // 🔹 Set session
        $_SESSION['user'] = $data['username'];

        // 🔹 Hindari session hijack
        session_regenerate_id(true);

        // 🔹 Redirect
        header("Location: dashboard.php");
        exit;

    } else {
        echo "<script>alert('Username atau Password salah!');</script>";
    }
}
?>

<link rel="stylesheet" href="css/style.css">

<div class="login-box">
    <h2>Login Bandhunet</h2>

    <form method="POST">
        <input name="username" placeholder="Username" required>
        <input type="password" name="password" placeholder="Password" required>

        <button name="login">Login</button>
    </form>
</div>