<?php
session_start();
include "koneksi.php";

if (isset($_POST['login'])) {
    $u = $_POST['username'];
    $p = $_POST['password'];

    $q = mysqli_query($conn, "SELECT * FROM users WHERE username='$u' AND password='$p'");
    $d = mysqli_fetch_assoc($q);

    if ($d) {
        $_SESSION['user'] = $d['username'];
        header("Location: dashboard.php");
    } else {
        echo "<script>alert('Login gagal');</script>";
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