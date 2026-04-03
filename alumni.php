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

// =======================
// 📄 PAGINATION 10000 DATA
// =======================
$limit = 10000;
$page = $_GET['page'] ?? 1;
$page = (int)$page;
if ($page < 1) $page = 1;

$start = ($page - 1) * $limit;

// =======================
// 🔎 QUERY FILTER
// =======================
$where = "WHERE nama LIKE '%$cari%'";
if ($status != '') {
    $where .= " AND status_tracking='$status'";
}

// =======================
// 📊 TOTAL DATA
// =======================
$total_query = mysqli_query($conn, "SELECT COUNT(*) as total FROM alumni $where");
$total = mysqli_fetch_assoc($total_query)['total'];
$total_pages = ceil($total / $limit);

// =======================
// 📥 AMBIL DATA
// =======================
$data = mysqli_query($conn, "SELECT * FROM alumni $where ORDER BY tahun_lulus DESC LIMIT $start, $limit");
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
<h2>Data Alumni</h2>

<!-- 🔍 SEARCH -->
<form method="GET" class="search-box">
    <input type="text" name="cari" placeholder="Cari nama..." value="<?= htmlspecialchars($cari) ?>">

    <select name="status">
        <option value="">Semua Status</option>
        <option <?= $status=='Belum Dilacak'?'selected':'' ?>>Belum Dilacak</option>
        <option <?= $status=='Teridentifikasi'?'selected':'' ?>>Teridentifikasi</option>
        <option <?= $status=='Perlu Verifikasi'?'selected':'' ?>>Perlu Verifikasi</option>
        <option <?= $status=='Tidak Ditemukan'?'selected':'' ?>>Tidak Ditemukan</option>
    </select>

    <button>Cari</button>
</form>

<br>

<a href="tambah.php" class="btn">+ Tambah</a>
<a href="import.php" class="btn">Import</a>

<br><br>

<table>
<tr>
    <th>Nama</th>
    <th>Tahun</th>
    <th>Email</th>
    <th>Pekerjaan</th>
    <th>Status Tracking</th>
    <th>Aksi</th>
</tr>

<?php while($d = mysqli_fetch_assoc($data)) { 

$warna = "gray";
if ($d['status_tracking'] == "Belum Dilacak") $warna = "red";
if ($d['status_tracking'] == "Teridentifikasi") $warna = "green";
if ($d['status_tracking'] == "Perlu Verifikasi") $warna = "orange";
if ($d['status_tracking'] == "Tidak Ditemukan") $warna = "black";

?>

<tr>
<td><?= htmlspecialchars($d['nama']) ?></td>
<td><?= $d['tahun_lulus'] ?></td>
<td><?= $d['email'] ?: '-' ?></td>
<td><?= $d['tempat_kerja'] ?: '-' ?></td>

<td style="color:<?= $warna ?>; font-weight:bold;">
<?= $d['status_tracking'] ?>
</td>

<td>
<button class="btn" onclick='showDetail(<?= json_encode($d) ?>)'>Detail</button>
<a class="btn" href="track.php?id=<?= $d['id'] ?>">Track</a>
<a class="btn" href="edit.php?id=<?= $d['id'] ?>">Edit</a>
<a class="btn" href="hapus.php?id=<?= $d['id'] ?>" onclick="return confirm('Hapus data?')">Hapus</a>
</td>
</tr>

<?php } ?>

</table>

<!-- 📄 PAGINATION RANGE -->
<div style="margin-top:20px;">

<?php for($i=1; $i <= $total_pages; $i++) { 

$start_data = ($i-1)*$limit + 1;
$end_data = min($i*$limit, $total);

?>

<a class="btn <?= $page==$i?'active':'' ?>"
   href="?page=<?= $i ?>&cari=<?= urlencode($cari) ?>&status=<?= urlencode($status) ?>">
   
   <?= $start_data ?> - <?= $end_data ?>

</a>

<?php } ?>

</div>

</div>
</div>
</div>

<!-- MODAL DETAIL -->
<div id="modalDetail" class="modal">
<div class="modal-content">
<span class="close" onclick="closeModal()">&times;</span>
<h2>Detail Alumni</h2>
<div id="detailContent"></div>
</div>
</div>

<script>
function showDetail(data) {
    let html = `
        <p><b>Nama:</b> ${data.nama}</p>
        <p><b>Tahun Lulus:</b> ${data.tahun_lulus}</p>
        <p><b>Email:</b> ${data.email || '-'}</p>
        <p><b>No HP:</b> ${data.no_hp || '-'}</p>

        <hr>

        <p><b>LinkedIn:</b> <a href="${data.linkedin}" target="_blank">${data.linkedin || '-'}</a></p>
        <p><b>Instagram:</b> <a href="${data.instagram}" target="_blank">${data.instagram || '-'}</a></p>
        <p><b>Facebook:</b> <a href="${data.facebook}" target="_blank">${data.facebook || '-'}</a></p>
        <p><b>Tiktok:</b> <a href="${data.tiktok}" target="_blank">${data.tiktok || '-'}</a></p>

        <hr>

        <p><b>Tempat Kerja:</b> ${data.tempat_kerja || '-'}</p>
        <p><b>Alamat Kerja:</b> ${data.alamat_kerja || '-'}</p>
        <p><b>Posisi:</b> ${data.posisi || '-'}</p>
        <p><b>Jenis Kerja:</b> ${data.status_kerja || '-'}</p>
        <p><b>Sosmed Kantor:</b> ${data.sosmed_kantor || '-'}</p>
    `;

    document.getElementById("detailContent").innerHTML = html;
    document.getElementById("modalDetail").style.display = "block";
}

function closeModal() {
    document.getElementById("modalDetail").style.display = "none";
}
</script>

<style>
.modal {
    display:none;
    position:fixed;
    z-index:999;
    padding-top:50px;
    left:0; top:0;
    width:100%;
    height:100%;
    overflow:auto;
    background-color:rgba(0,0,0,0.5);
}
.modal-content {
    background:#fff;
    margin:auto;
    padding:20px;
    border-radius:8px;
    width:80%;
    max-width:600px;
    position:relative;
}
.close {
    position:absolute;
    top:10px;
    right:15px;
    font-size:25px;
    cursor:pointer;
}
</style>