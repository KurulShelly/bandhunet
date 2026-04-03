<?php
session_start();
include "koneksi.php";

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

if (isset($_POST['simpan'])) {
    mysqli_query($conn, "INSERT INTO alumni (
        nama,tahun_lulus,linkedin,instagram,facebook,tiktok,
        email,no_hp,tempat_kerja,alamat_kerja,posisi,status_kerja,sosmed_kantor,status_tracking
    ) VALUES (
        '$_POST[nama]','$_POST[tahun]','$_POST[linkedin]','$_POST[instagram]',
        '$_POST[facebook]','$_POST[tiktok]','$_POST[email]','$_POST[no_hp]',
        '$_POST[tempat_kerja]','$_POST[alamat_kerja]','$_POST[posisi]',
        '$_POST[status_kerja]','$_POST[sosmed_kantor]','$_POST[status_tracking]'
    )");

    header("Location: alumni.php");
}
?>

<link rel="stylesheet" href="css/style.css">

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

        <div class="card">
            <h2>Tambah Alumni</h2>

            <form method="POST">

                <input name="nama" placeholder="Nama" required>
                <input name="tahun" placeholder="Tahun Lulus">

                <h4>Sosial Media</h4>
                <input name="linkedin" placeholder="LinkedIn">
                <input name="instagram" placeholder="Instagram">
                <input name="facebook" placeholder="Facebook">
                <input name="tiktok" placeholder="TikTok">

                <h4>Kontak</h4>
                <input name="email" placeholder="Email">
                <input name="no_hp" placeholder="No HP">

                <h4>Pekerjaan</h4>
                <input name="tempat_kerja" placeholder="Tempat Kerja">
                <textarea name="alamat_kerja" placeholder="Alamat Kerja"></textarea>
                <input name="posisi" placeholder="Posisi">

                <select name="status_kerja">
                    <option>PNS</option>
                    <option>Swasta</option>
                    <option>Wirausaha</option>
                </select>

                <input name="sosmed_kantor" placeholder="Sosial Media Kantor">

                <h4>Status Tracking</h4>
                <select name="status_tracking">
                    <option>Belum Dilacak</option>
                    <option>Teridentifikasi</option>
                    <option>Perlu Verifikasi</option>
                    <option>Tidak Ditemukan</option>
                </select>

                <br><br>
                <button name="simpan">Simpan</button>

            </form>
        </div>

    </div>

</div>