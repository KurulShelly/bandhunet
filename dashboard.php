<?php
session_start();
include "koneksi.php";

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
}

/* TOTAL DATA */
$total = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM alumni"));

/* STATUS (anggap pakai field status_tracking ya) */
$belum = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM alumni WHERE status_tracking='Belum Dilacak'"));
$teridentifikasi = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM alumni WHERE status_tracking='Teridentifikasi'"));
$verifikasi = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM alumni WHERE status_tracking='Perlu Verifikasi'"));
$tidak = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM alumni WHERE status_tracking='Tidak Ditemukan'"));
?>

<link rel="stylesheet" href="css/style.css">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<div class="wrapper">

<!-- SIDEBAR -->
<div class="sidebar">
    <h2>Bandhunet</h2>
    <a href="dashboard.php">Dashboard</a>
    <a href="alumni.php">Data Alumni</a>
    <a href="import.php">Import</a>
    <a href="logout.php">Logout</a>
</div>

<!-- MAIN -->
<div class="main">

<h2>Dashboard Alumni</h2>

<div class="stats-grid">
    <div class="stat-card bg1">Total Alumni <br><?= $total ?></div>
    <div class="stat-card bg2">Belum Dilacak <br><?= $belum ?></div>
    <div class="stat-card bg3">Teridentifikasi <br><?= $teridentifikasi ?></div>
    <div class="stat-card bg4">Perlu Verifikasi <br><?= $verifikasi ?></div>
    <div class="stat-card bg5">Tidak Ditemukan <br><?= $tidak ?></div>
</div>

<br>

<div class="card">
    <h3>Grafik Status Alumni</h3>
    <canvas id="myChart"></canvas>
</div>

</div>
</div>

<script>
const ctx = document.getElementById('myChart');

new Chart(ctx, {
    type: 'bar',
    data: {
        labels: ['Belum Dilacak', 'Teridentifikasi', 'Verifikasi', 'Tidak Ditemukan'],
        datasets: [{
            label: 'Jumlah Alumni',
            data: [<?= $belum ?>, <?= $teridentifikasi ?>, <?= $verifikasi ?>, <?= $tidak ?>],
            borderWidth: 1
        }]
    }
});
</script>