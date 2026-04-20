<?php
session_start();
include "koneksi.php";

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

// =======================
// 🔍 FILTER
// =======================
$cari = $_GET['cari'] ?? '';
$status = $_GET['status'] ?? '';

$where = "WHERE nama LIKE '%$cari%'";
if ($status != '') {
    $where .= " AND status_tracking='$status'";
}

// =======================
// 📥 HEADER EXCEL
// =======================
header("Content-type: application/vnd-ms-excel");
header("Content-Disposition: attachment; filename=Data_Alumni_FULL_".date('Y-m-d').".xls");

// =======================
// 🏷️ JUDUL
// =======================
echo "<h3>Data Alumni Lengkap</h3>";

echo "<table border='1'>
<tr>
    <th>No</th>
    <th>Nama</th>
    <th>NIM</th>
    <th>Tahun Masuk</th>
    <th>Tahun Lulus</th>
    <th>Fakultas</th>
    <th>Prodi</th>

    <th>Email</th>
    <th>No HP</th>

    <th>LinkedIn</th>
    <th>Instagram</th>
    <th>Facebook</th>
    <th>TikTok</th>

    <th>Tempat Kerja</th>
    <th>Alamat Kerja</th>
    <th>Posisi</th>
    <th>Status Kerja</th>

    <th>Sosmed Kantor</th>

    <th>Riwayat Pekerjaan (Terbaru)</th>
    <th>Perusahaan</th>
    <th>Posisi (Riwayat)</th>

    <th>Status Tracking</th>
</tr>";

$no = 1;

// =======================
// 📊 AMBIL DATA
// =======================
$data = mysqli_query($conn, "SELECT * FROM alumni $where ORDER BY tahun_lulus DESC");

while($d = mysqli_fetch_assoc($data)) {

    // =======================
    // 🔥 PARSE JSON RIWAYAT
    // =======================
    $riwayat = json_decode($d['riwayat_kerja'] ?? '[]', true);

    $pekerjaan_json = '-';
    $perusahaan_json = '-';
    $posisi_json = '-';

    if (!empty($riwayat)) {
        $pekerjaan_json = $riwayat[0]['pekerjaan'] ?? '-';
        $perusahaan_json = $riwayat[0]['perusahaan'] ?? '-';
        $posisi_json = $riwayat[0]['posisi'] ?? '-';
    }

    echo "<tr>
        <td>".$no++."</td>
        <td>".$d['nama']."</td>
        <td>".$d['nim']."</td>
        <td>".$d['tahun_masuk']."</td>
        <td>".$d['tahun_lulus']."</td>
        <td>".$d['fakultas']."</td>
        <td>".$d['prodi']."</td>

        <td>".$d['email']."</td>
        <td>".$d['no_hp']."</td>

        <td>".$d['linkedin']."</td>
        <td>".$d['instagram']."</td>
        <td>".$d['facebook']."</td>
        <td>".$d['tiktok']."</td>

        <td>".$d['tempat_kerja']."</td>
        <td>".$d['alamat_kerja']."</td>
        <td>".$d['posisi']."</td>
        <td>".$d['status_kerja']."</td>

        <td>".$d['sosmed_kantor']."</td>

        <td>".$pekerjaan_json."</td>
        <td>".$perusahaan_json."</td>
        <td>".$posisi_json."</td>

        <td>".$d['status_tracking']."</td>
    </tr>";
}

echo "</table>";
?>