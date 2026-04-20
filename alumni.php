<?php
session_start();
include "koneksi.php";

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

/* ==========================
   🤖 AUTO LENGKAPI
========================== */
if (isset($_POST['auto_all'])) {

    $q = mysqli_query($conn, "SELECT * FROM alumni");

    while ($d = mysqli_fetch_assoc($q)) {

        /* ==========================
           🔥 FIX NAMA & USERNAME
        ========================== */
        $nama_asli = trim($d['nama']);

        $nama_clean = strtolower($nama_asli);

        // hapus angka & karakter aneh
        $nama_clean = preg_replace('/[^a-z ]/', '', $nama_clean);

        // rapikan spasi
        $nama_clean = preg_replace('/\s+/', ' ', $nama_clean);

        if (!$nama_clean) $nama_clean = "user";

        // username dari nama
        $username = str_replace(' ', '', $nama_clean);

        /* ==========================
           🔥 AUTO GENERATE
        ========================== */
        $email_auto = str_replace(' ', '.', $nama_clean) . "@gmail.com";

        $linkedin_auto = "https://linkedin.com/search/results/all/?keywords=" . urlencode($d['nama']);
        $instagram_auto = "https://instagram.com/" . $username;
        $facebook_auto  = "https://facebook.com/" . $username;
        $tiktok_auto    = "https://www.tiktok.com/@" . $username;

        /* ==========================
           🔥 LOGIC
        ========================== */
        $email_final = (!$d['email'] || strpos($d['email'], '@gmail.com')) ? $email_auto : $d['email'];

        $linkedin_final = $d['linkedin'] ?: $linkedin_auto;
        $instagram_final = $d['instagram'] ?: $instagram_auto;
        $facebook_final = $d['facebook'] ?: $facebook_auto;
        $tiktok_final = $d['tiktok'] ?: $tiktok_auto;

        /* ==========================
           🔥 UPDATE + STATUS
        ========================== */
        mysqli_query($conn, "UPDATE alumni SET
            email='$email_final',
            linkedin='$linkedin_final',
            instagram='$instagram_final',
            facebook='$facebook_final',
            tiktok='$tiktok_final',
            status_tracking='Perlu Verifikasi'
        WHERE id='{$d['id']}'");
    }

    echo "<script>alert('Auto isi selesai!');location='alumni.php';</script>";
}

/* ==========================
   🔍 FILTER + PAGINATION
========================== */
$cari = $_GET['cari'] ?? '';
$status = $_GET['status'] ?? '';

$limit = 10000;
$page = max((int)($_GET['page'] ?? 1), 1);
$start = ($page - 1) * $limit;

$where = "WHERE nama LIKE '%$cari%'";
if ($status != '') $where .= " AND status_tracking='$status'";

$total = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM alumni $where"))['total'];
$total_pages = ceil($total / $limit);

$data = mysqli_query($conn, "SELECT * FROM alumni $where ORDER BY tahun_lulus DESC LIMIT $start,$limit");
?>

<link rel="stylesheet" href="css/style.css">

<div class="wrapper">

<div class="sidebar">
    <h2>Bandhunet</h2>
    <a href="dashboard.php">Dashboard</a>
    <a href="alumni.php">Data Alumni</a>
    <a href="import.php">Import</a>
    <a href="logout.php">Logout</a>
</div>

<div class="main">
<div class="card">

<h2>Data Alumni</h2>

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

<div class="btn-group">
    <a href="tambah.php" class="btn ui-btn">+ Tambah</a>
    <a href="import.php" class="btn ui-btn">Import</a>

    <a href="export_excel.php?cari=<?= urlencode($cari) ?>&status=<?= urlencode($status) ?>"
       class="btn ui-btn btn-download">
       ⬇ Download Excel
    </a>

    <form method="POST" class="inline-form">
        <button name="auto_all" class="btn ui-btn btn-auto">
            🤖 Auto Lengkapi Semua Data
        </button>
    </form>
</div>

<br><br>

<table>
<tr>
<th>Nama</th>
<th>NIM</th>
<th>Tahun Masuk</th>
<th>Tahun Lulus</th>
<th>Fakultas</th>
<th>Prodi</th>
<th>Status</th>
<th>Akurasi</th>
<th>Aksi</th>
</tr>

<?php while($d = mysqli_fetch_assoc($data)) {

$warna="gray";
if ($d['status_tracking']=="Belum Dilacak") $warna="red";
if ($d['status_tracking']=="Teridentifikasi") $warna="green";
if ($d['status_tracking']=="Perlu Verifikasi") $warna="orange";
if ($d['status_tracking']=="Tidak Ditemukan") $warna="black";

/* 🔥 AKURASI REAL */
$total_field = 10;
$isi = 0;

if($d['email']) $isi++;
if($d['no_hp']) $isi++;
if($d['linkedin']) $isi++;
if($d['instagram']) $isi++;
if($d['facebook']) $isi++;
if($d['tiktok']) $isi++;
if($d['tempat_kerja']) $isi++;
if($d['alamat_kerja']) $isi++;
if($d['posisi']) $isi++;
if($d['status_kerja']) $isi++;

$akurasi = round(($isi / $total_field) * 100);

$warnaAkurasi="red";
if ($akurasi >= 80) $warnaAkurasi="green";
elseif ($akurasi >= 60) $warnaAkurasi="orange";
?>

<tr>
<td><?= htmlspecialchars($d['nama']) ?></td>
<td><?= $d['nim'] ?: '-' ?></td>
<td><?= $d['tahun_masuk'] ?: '-' ?></td>
<td><?= $d['tahun_lulus'] ?: '-' ?></td>
<td><?= $d['fakultas'] ?: '-' ?></td>
<td><?= $d['prodi'] ?: '-' ?></td>

<td style="color:<?= $warna ?>; font-weight:bold;">
<?= $d['status_tracking'] ?>
</td>

<td style="color:<?= $warnaAkurasi ?>; font-weight:bold;">
<?= $akurasi ?>%
</td>

<td>
<div class="aksi-btn">

<button class="btn detail-btn"
data-alumni='<?= htmlspecialchars(json_encode($d), ENT_QUOTES, "UTF-8") ?>'>
Detail</button>

<a class="btn" href="track.php?id=<?= $d['id'] ?>">Track</a>
<a class="btn" href="edit.php?id=<?= $d['id'] ?>">Edit</a>
<a class="btn" href="hapus.php?id=<?= $d['id'] ?>" onclick="return confirm('Hapus data?')">Hapus</a>

</div>
</td>
</tr>

<?php } ?>
</table>

</div>
</div>
</div>

<!-- MODAL -->
<div id="modalDetail" class="modal">
<div class="modal-content">
<span class="close">&times;</span>
<h2>Detail Alumni</h2>
<div id="detailContent"></div>
</div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function(){

    const modal = document.getElementById("modalDetail");
    const closeBtn = document.querySelector(".close");

    document.querySelectorAll('.detail-btn').forEach(btn=>{
        btn.addEventListener('click',function(){

            let data = JSON.parse(this.dataset.alumni);

            let html = `
            <p><b>Nama:</b> ${data.nama ?? '-'}</p>
            <p><b>NIM:</b> ${data.nim ?? '-'}</p>
            <p><b>Tahun Masuk:</b> ${data.tahun_masuk ?? '-'}</p>
            <p><b>Tahun Lulus:</b> ${data.tahun_lulus ?? '-'}</p>
            <p><b>Fakultas:</b> ${data.fakultas ?? '-'}</p>
            <p><b>Prodi:</b> ${data.prodi ?? '-'}</p>

            <hr>

            <p><b>Email:</b> ${data.email ?? '-'}</p>
            <p><b>No HP:</b> ${data.no_hp ?? '-'}</p>

            <hr>

            <p><b>LinkedIn:</b> ${data.linkedin ?? '-'}</p>
            <p><b>Instagram:</b> ${data.instagram ?? '-'}</p>
            <p><b>Facebook:</b> ${data.facebook ?? '-'}</p>
            <p><b>TikTok:</b> ${data.tiktok ?? '-'}</p>

            <hr>

            <p><b>Tempat Kerja:</b> ${data.tempat_kerja ?? '-'}</p>
            <p><b>Alamat Kerja:</b> ${data.alamat_kerja ?? '-'}</p>
            <p><b>Posisi:</b> ${data.posisi ?? '-'}</p>
            <p><b>Status Kerja:</b> ${data.status_kerja ?? '-'}</p>
            <p><b>Sosmed Kantor:</b> ${data.sosmed_kantor ?? '-'}</p>
            `;

            document.getElementById("detailContent").innerHTML = html;
            modal.style.display = "block";
        });
    });

    closeBtn.onclick = ()=> modal.style.display="none";
    window.onclick = (e)=>{ if(e.target==modal) modal.style.display="none"; }

});
</script>

<style>
.btn-group{display:flex;gap:10px;flex-wrap:wrap;align-items:center;}
.inline-form{margin:0;}
.ui-btn{height:42px;padding:0 18px;display:flex;align-items:center;border-radius:8px;}
.btn-download{background:green;color:white;}
.btn-auto{background:purple;color:white;}

.aksi-btn{display:flex;flex-wrap:wrap;gap:6px;}
.aksi-btn .btn{padding:6px 10px;font-size:12px;border-radius:6px;}

.modal{display:none;position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.5);}
.modal-content{background:#fff;margin:50px auto;padding:20px;width:80%;max-width:500px;border-radius:10px;}
.close{float:right;font-size:25px;cursor:pointer;}
</style>