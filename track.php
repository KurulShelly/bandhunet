<?php
session_start();
include "koneksi.php";

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

$id = $_GET['id'] ?? 0;
$id = (int)$id;

$data = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM alumni WHERE id='$id'"));

if (!$data) {
    echo "Data tidak ditemukan!";
    exit;
}

$riwayat_kerja = json_decode($data['riwayat_kerja'] ?? '[]', true);

/* ==========================
   💾 UPDATE SEMUA (STATUS + DATA)
========================== */
if (isset($_POST['update_all'])) {

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
    $status = mysqli_real_escape_string($conn, $_POST['status_tracking']);

    mysqli_query($conn, "UPDATE alumni SET
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
    WHERE id='$id'");

    header("Location: track.php?id=$id");
    exit;
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

<h2>Tracking Alumni</h2>

<a href="alumni.php" class="btn">⬅ Kembali</a>
<a href="dashboard.php" class="btn" style="background:gray;">🏠 Dashboard</a>

<br><br>

<p><b>Nama:</b> <?= htmlspecialchars($data['nama']) ?></p>
<p><b>Tahun Lulus:</b> <?= htmlspecialchars($data['tahun_lulus']) ?></p>

<hr>

<!-- 🔍 LINK -->
<h3>Cari Online</h3>
<?php 
$nama = urlencode($data['nama']);
$query = urlencode($data['nama'] . " alumni");
?>
<a class="btn" target="_blank" href="https://www.google.com/search?q=<?= $query ?>">🔍 Google</a>
<a class="btn" target="_blank" href="https://www.linkedin.com/search/results/all/?keywords=<?= $nama ?>">💼 LinkedIn</a>
<a class="btn" target="_blank" href="https://www.facebook.com/search/top/?q=<?= $nama ?>">📘 Facebook</a>
<a class="btn" target="_blank" href="https://www.instagram.com/explore/tags/<?= $nama ?>">📷 Instagram</a>
<a class="btn" target="_blank" href="https://www.tiktok.com/search?q=<?= $nama ?>">🎵 TikTok</a>

<hr>

<!-- 🔥 1 FORM SAJA -->
<form method="POST">

    <label>Status Tracking</label>
    <select name="status_tracking">
        <option value="Belum Dilacak" <?= $data['status_tracking']=='Belum Dilacak'?'selected':'' ?>>Belum Dilacak</option>
        <option value="Teridentifikasi" <?= $data['status_tracking']=='Teridentifikasi'?'selected':'' ?>>Teridentifikasi</option>
        <option value="Perlu Verifikasi" <?= $data['status_tracking']=='Perlu Verifikasi'?'selected':'' ?>>Perlu Verifikasi</option>
        <option value="Tidak Ditemukan" <?= $data['status_tracking']=='Tidak Ditemukan'?'selected':'' ?>>Tidak Ditemukan</option>
    </select>

    <hr>

    <h3>Detail Alumni</h3>

    <input name="email" placeholder="Email" value="<?= htmlspecialchars($data['email']) ?>">
    <input name="no_hp" placeholder="No HP" value="<?= htmlspecialchars($data['no_hp']) ?>">
    <input name="linkedin" placeholder="LinkedIn" value="<?= htmlspecialchars($data['linkedin']) ?>">
    <input name="instagram" placeholder="Instagram" value="<?= htmlspecialchars($data['instagram']) ?>">
    <input name="facebook" placeholder="Facebook" value="<?= htmlspecialchars($data['facebook']) ?>">
    <input name="tiktok" placeholder="TikTok" value="<?= htmlspecialchars($data['tiktok']) ?>">

    <input name="tempat_kerja" placeholder="Tempat Kerja" value="<?= htmlspecialchars($data['tempat_kerja']) ?>">
    <input name="alamat_kerja" placeholder="Alamat Kerja" value="<?= htmlspecialchars($data['alamat_kerja']) ?>">
    <input name="posisi" placeholder="Posisi" value="<?= htmlspecialchars($data['posisi']) ?>">

    <select name="jenis_kerja">
        <option value="">-- Jenis Kerja --</option>
        <option value="PNS" <?= $data['status_kerja']=='PNS'?'selected':'' ?>>PNS</option>
        <option value="Swasta" <?= $data['status_kerja']=='Swasta'?'selected':'' ?>>Swasta</option>
        <option value="Wirausaha" <?= $data['status_kerja']=='Wirausaha'?'selected':'' ?>>Wirausaha</option>
    </select>

    <input name="sosmed_kantor" placeholder="Sosmed Kantor" value="<?= htmlspecialchars($data['sosmed_kantor']) ?>">

    <br><br>
    <button name="update_all" class="btn" style="background:green;">
        💾 Simpan Semua
    </button>

</form>

<hr>

<!-- 📊 RIWAYAT -->
<h3>Riwayat Pekerjaan</h3>
<table>
<tr>
    <th>Tahun</th>
    <th>Pekerjaan</th>
    <th>Perusahaan</th>
    <th>Posisi</th>
</tr>

<?php
if ($riwayat_kerja) {
    foreach($riwayat_kerja as $row) {
        echo "<tr>
            <td>{$row['tahun']}</td>
            <td>{$row['pekerjaan']}</td>
            <td>{$row['perusahaan']}</td>
            <td>{$row['posisi']}</td>
        </tr>";
    }
}
?>
</table>

</div>
</div>
</div>