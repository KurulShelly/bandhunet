<?php
session_start();
include "koneksi.php";

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}


/* ==========================
   🔹 TOTAL DATA
========================== */
$total = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM alumni"));

$belum = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM alumni WHERE status_tracking='Belum Dilacak'"));
$teridentifikasi = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM alumni WHERE status_tracking='Teridentifikasi'"));
$verifikasi = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM alumni WHERE status_tracking='Perlu Verifikasi'"));
$tidak = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM alumni WHERE status_tracking='Tidak Ditemukan'"));

/* ==========================
   🔹 COVERAGE
========================== */
if ($total < 28459) $coverage = 40;
elseif ($total <= 56917) $coverage = 60;
elseif ($total <= 85376) $coverage = 80;
elseif ($total <= 106720) $coverage = 90;
else $coverage = 100;

/* ==========================
   🔹 COMPLETENESS
========================== */
$q = mysqli_query($conn,"SELECT * FROM alumni");

$total_field = 0;
$total_data = 0;

while($d = mysqli_fetch_assoc($q)){
    $isi = 0;

    if($d['email']) $isi++;
    if($d['no_hp']) $isi++;
    if($d['linkedin']) $isi++;
    if($d['tempat_kerja']) $isi++;

    $total_field += $isi;
    $total_data++;
}

$rata = $total_field / ($total_data ?: 1);

if ($rata < 2) $completeness = 50;
elseif ($rata < 3) $completeness = 70;
elseif ($rata < 4) $completeness = 85;
else $completeness = 100;

/* ==========================
   🔹 ACCURACY
========================== */
$accuracy = 80;

/* ==========================
   🔹 FINAL SCORE
========================== */
$final = ($coverage*0.4)+($accuracy*0.4)+($completeness*0.2);
?>

<link rel="stylesheet" href="css/style.css">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<div class="wrapper">

<div class="sidebar">
    <h2>Bandhunet</h2>
    <a href="dashboard.php">Dashboard</a>
    <a href="alumni.php">Data Alumni</a>
    <a href="import.php">Import</a>
    <a href="logout.php">Logout</a>
</div>

<div class="main">

<h2>Dashboard Alumni</h2>

<br>

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

<br>

<div class="card">
    <h3>📊 Data Quality</h3>
    <p>Coverage: <?= $coverage ?></p>
    <p>Accuracy: <?= $accuracy ?></p>
    <p>Completeness: <?= $completeness ?></p>
    <h2>Final Score: <?= number_format($final,2) ?></h2>
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