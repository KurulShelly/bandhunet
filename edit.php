<?php
session_start();
include "koneksi.php";

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

$id = $_GET['id'] ?? 0;
$id = (int)$id;

// Ambil data alumni
$d = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM alumni WHERE id=$id"));

if (!$d) {
    echo "Data tidak ditemukan.";
    exit;
}

// ==========================
// 💾 UPDATE DATA
// ==========================
if (isset($_POST['update'])) {

    $nama = mysqli_real_escape_string($conn, $_POST['nama']);
    $tahun_lulus = mysqli_real_escape_string($conn, $_POST['tahun_lulus']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $no_hp = mysqli_real_escape_string($conn, $_POST['no_hp']);
    $linkedin = mysqli_real_escape_string($conn, $_POST['linkedin']);
    $instagram = mysqli_real_escape_string($conn, $_POST['instagram']);
    $facebook = mysqli_real_escape_string($conn, $_POST['facebook']);
    $tiktok = mysqli_real_escape_string($conn, $_POST['tiktok']);
    $tempat_kerja = mysqli_real_escape_string($conn, $_POST['tempat_kerja']);
    $alamat_kerja = mysqli_real_escape_string($conn, $_POST['alamat_kerja']);
    $posisi = mysqli_real_escape_string($conn, $_POST['posisi']);
    $jenis_kerja = mysqli_real_escape_string($conn, $_POST['jenis_kerja']);
    $sosmed_kantor = mysqli_real_escape_string($conn, $_POST['sosmed_kantor']);

    $status = ($email || $no_hp || $tempat_kerja || $linkedin) ? "Teridentifikasi" : "Belum Dilacak";

    mysqli_query($conn, "UPDATE alumni SET
        nama='$nama',
        tahun_lulus='$tahun_lulus',
        email='$email',
        no_hp='$no_hp',
        linkedin='$linkedin',
        instagram='$instagram',
        facebook='$facebook',
        tiktok='$tiktok',
        tempat_kerja='$tempat_kerja',
        alamat_kerja='$alamat_kerja',
        posisi='$posisi',
        status_kerja='$jenis_kerja',
        sosmed_kantor='$sosmed_kantor',
        status_tracking='$status'
        WHERE id=$id");

    header("Location: track.php?id=$id");
    exit;
}
?>

<link rel="stylesheet" href="css/style.css">

<div class="wrapper">
<div class="main">
<div class="card">

<h2>Edit Alumni</h2>

<form method="POST">
    <label>Nama</label>
    <input name="nama" value="<?= htmlspecialchars($d['nama']) ?>" required>

    <label>Tahun Lulus</label>
    <input name="tahun_lulus" value="<?= htmlspecialchars($d['tahun_lulus']) ?>">

    <label>Email</label>
    <input name="email" value="<?= htmlspecialchars($d['email']) ?>">

    <label>No HP</label>
    <input name="no_hp" value="<?= htmlspecialchars($d['no_hp']) ?>">

    <hr>
    <label>LinkedIn</label>
    <input name="linkedin" value="<?= htmlspecialchars($d['linkedin']) ?>">

    <label>Instagram</label>
    <input name="instagram" value="<?= htmlspecialchars($d['instagram']) ?>">

    <label>Facebook</label>
    <input name="facebook" value="<?= htmlspecialchars($d['facebook']) ?>">

    <label>TikTok</label>
    <input name="tiktok" value="<?= htmlspecialchars($d['tiktok']) ?>">

    <hr>
    <label>Tempat Kerja</label>
    <input name="tempat_kerja" value="<?= htmlspecialchars($d['tempat_kerja']) ?>">

    <label>Alamat Kerja</label>
    <input name="alamat_kerja" value="<?= htmlspecialchars($d['alamat_kerja']) ?>">

    <label>Posisi</label>
    <input name="posisi" value="<?= htmlspecialchars($d['posisi']) ?>">

    <label>Jenis Kerja</label>
    <select name="jenis_kerja">
        <option value="">-- Pilih --</option>
        <option value="PNS" <?= $d['status_kerja']=='PNS'?'selected':'' ?>>PNS</option>
        <option value="Swasta" <?= $d['status_kerja']=='Swasta'?'selected':'' ?>>Swasta</option>
        <option value="Wirausaha" <?= $d['status_kerja']=='Wirausaha'?'selected':'' ?>>Wirausaha</option>
    </select>

    <label>Sosmed Kantor</label>
    <input name="sosmed_kantor" value="<?= htmlspecialchars($d['sosmed_kantor']) ?>">

    <br><br>
    <button name="update" class="btn">💾 Simpan</button>
    <a href="track.php?id=<?= $id ?>" class="btn" style="background:gray;">⬅ Kembali</a>
</form>

</div>
</div>
</div>