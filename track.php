<?php
session_start();
include "koneksi.php";

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

$id = $_GET['id'];
$data = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM alumni WHERE id='$id'"));

// ==========================
// 🔹 Dummy Jobs Berdasarkan Prodi
// ==========================
$jobs_by_prodi = [
    "akuntansi" => [
        ["pekerjaan"=>"Akuntan","perusahaan"=>"FinCorp","posisi"=>"Junior Akuntan"],
        ["pekerjaan"=>"Auditor","perusahaan"=>"AuditPro","posisi"=>"Auditor"]
    ],
    "manajemen" => [
        ["pekerjaan"=>"Manager","perusahaan"=>"Global Inc","posisi"=>"Project Manager"],
        ["pekerjaan"=>"Supervisor","perusahaan"=>"Business Corp","posisi"=>"Supervisor"]
    ],
    "teknik" => [
        ["pekerjaan"=>"Engineer","perusahaan"=>"BuildTech","posisi"=>"Engineer"],
        ["pekerjaan"=>"Developer","perusahaan"=>"Soft Corp","posisi"=>"Junior Developer"]
    ],
    "agama" => [
        ["pekerjaan"=>"Guru Agama","perusahaan"=>"Madrasah","posisi"=>"Guru"],
        ["pekerjaan"=>"Dosen","perusahaan"=>"UIN","posisi"=>"Dosen"]
    ],
    "pemerintahan" => [
        ["pekerjaan"=>"Staff Pemerintahan","perusahaan"=>"Kantor Desa","posisi"=>"Staff"],
        ["pekerjaan"=>"Pegawai Negeri","perusahaan"=>"Pemda","posisi"=>"Staff"]
    ]
];

// Ambil dummy jobs sesuai prodi
$prodi = strtolower($data['prodi'] ?? '');
$dummy_jobs = $jobs_by_prodi[$prodi] ?? [
    ["pekerjaan"=>"Staff","perusahaan"=>"OfficeWorks","posisi"=>"Staff"],
    ["pekerjaan"=>"Admin","perusahaan"=>"AdminPro","posisi"=>"Admin"]
];
shuffle($dummy_jobs);
$dummy_jobs = array_slice($dummy_jobs, 0, rand(3,5));

// ==========================
// 🔹 Riwayat Pekerjaan dari DB
// ==========================
$riwayat_kerja = json_decode($data['riwayat_kerja'] ?? '[]', true) ?? [];
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

<p><b>Nama:</b> <?= $data['nama'] ?></p>
<p><b>Tahun Lulus:</b> <?= $data['tahun_lulus'] ?></p>

<hr>

<h3>Cari Data Online</h3>
<?php 
$nama = urlencode($data['nama']);
$query = urlencode($data['nama'] . " " . $data['tahun_lulus'] . " alumni");
?>
<a class="btn" target="_blank" href="https://www.google.com/search?q=<?= $query ?>">🔍 Google</a>
<a class="btn" target="_blank" href="https://www.linkedin.com/search/results/all/?keywords=<?= $nama ?>">💼 LinkedIn</a>
<a class="btn" target="_blank" href="https://www.facebook.com/search/top/?q=<?= $nama ?>">📘 Facebook</a>
<a class="btn" target="_blank" href="https://www.instagram.com/explore/tags/<?= $nama ?>">📷 Instagram</a>

<hr>

<!-- 🤖 AUTO TRACK -->
<form method="POST">
    <button name="auto_track" class="btn" style="background:blue;color:white;">
        🤖 Auto Track
    </button>
</form>

<br>

<!-- ✅ TOMBOL CEPAT -->
<form method="POST">
    <button name="auto_done" class="btn" style="background:green;color:white;">
        ✅ Tandai Teridentifikasi
    </button>
</form>

<hr>

<h3>Detail Alumni</h3>
<form method="POST">
    <input name="email" placeholder="Email" value="<?= $data['email'] ?>">
    <input name="no_hp" placeholder="No HP" value="<?= $data['no_hp'] ?>">
    <input name="linkedin" placeholder="LinkedIn" value="<?= $data['linkedin'] ?>">
    <input name="instagram" placeholder="Instagram" value="<?= $data['instagram'] ?>">
    <input name="facebook" placeholder="Facebook" value="<?= $data['facebook'] ?>">
    <input name="tiktok" placeholder="Tiktok" value="<?= $data['tiktok'] ?>">
    <input name="tempat_kerja" placeholder="Tempat Kerja" value="<?= $data['tempat_kerja'] ?>">
    <input name="alamat_kerja" placeholder="Alamat Kerja" value="<?= $data['alamat_kerja'] ?>">
    <input name="posisi" placeholder="Posisi" value="<?= $data['posisi'] ?>">
    <select name="jenis_kerja">
        <option value="">-- Jenis Kerja --</option>
        <option <?= $data['status_kerja']=='PNS'?'selected':'' ?>>PNS</option>
        <option <?= $data['status_kerja']=='Swasta'?'selected':'' ?>>Swasta</option>
        <option <?= $data['status_kerja']=='Wirausaha'?'selected':'' ?>>Wirausaha</option>
    </select>
    <input name="sosmed_kantor" placeholder="Sosmed Kantor" value="<?= $data['sosmed_kantor'] ?>">
    <br><br>
    <button name="update" class="btn">💾 Simpan</button>
</form>

<hr>

<h3>Riwayat Pekerjaan Alumni</h3>
<table>
<tr>
    <th>Tahun</th>
    <th>Pekerjaan</th>
    <th>Perusahaan / Alamat</th>
    <th>Posisi</th>
</tr>

<?php
foreach($riwayat_kerja as $row) {
    echo "<tr>
        <td>{$row['tahun']}</td>
        <td>{$row['pekerjaan']}</td>
        <td>{$row['perusahaan']}</td>
        <td>{$row['posisi']}</td>
    </tr>";
}
?>
</table>
</div>
</div>
</div>

<?php
// ==========================
// 🤖 AUTO TRACK
// ==========================
if (isset($_POST['auto_track'])) {
    $nama_asli = $data['nama'];
    $nama_search = urlencode($nama_asli . " linkedin kerja");
    $html = @file_get_contents("https://www.google.com/search?q=".$nama_search);
    $snippet = strtolower(strip_tags($html));

    // Tentukan pekerjaan dari snippet
    $jobs_map = [
        "developer"=>"Developer","programmer"=>"Programmer","engineer"=>"Engineer",
        "manager"=>"Manager","staff"=>"Staff","admin"=>"Admin","teacher"=>"Guru",
        "dosen"=>"Dosen","designer"=>"Designer","analyst"=>"Analyst"
    ];

    $pekerjaan = "Tidak diketahui";
    foreach ($jobs_map as $key=>$val) {
        if (strpos($snippet, $key) !== false) {
            $pekerjaan = $val;
            break;
        }
    }

    // Jika tidak ketemu, pakai dummy jobs
    $last_job = $pekerjaan=="Tidak diketahui" ? $dummy_jobs[0] : [
        "pekerjaan"=>$pekerjaan,
        "perusahaan"=>"-",
        "posisi"=>"-"
    ];

    // Simpan ke riwayat_kerja
    $tahun = date("Y");
    array_unshift($riwayat_kerja, [
        "tahun"=>$tahun,
        "pekerjaan"=>$last_job['pekerjaan'],
        "perusahaan"=>$last_job['perusahaan'],
        "posisi"=>$last_job['posisi']
    ]);
    $riwayat_json = json_encode($riwayat_kerja);

    // Email dan LinkedIn default
    $email = strtolower(str_replace(' ', '.', $nama_asli)) . "@gmail.com";
    $linkedin = "https://www.linkedin.com/search/results/all/?keywords=" . urlencode($nama_asli);

    mysqli_query($conn, "UPDATE alumni SET
        email='$email',
        linkedin='$linkedin',
        tempat_kerja='{$last_job['pekerjaan']}',
        alamat_kerja='{$last_job['perusahaan']}',
        posisi='{$last_job['posisi']}',
        status_kerja='Swasta',
        status_tracking='Teridentifikasi',
        riwayat_kerja='$riwayat_json'
    WHERE id='$id'");

    header("Location: track.php?id=$id");
}

// ==========================
// ✅ TOMBOL CEPAT
// ==========================
if (isset($_POST['auto_done'])) {
    mysqli_query($conn, "UPDATE alumni SET status_tracking='Teridentifikasi' WHERE id='$id'");
    header("Location: track.php?id=$id");
}

// ==========================
// 💾 UPDATE MANUAL
// ==========================
if (isset($_POST['update'])) {
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
        email='$email', no_hp='$no_hp', linkedin='$linkedin',
        instagram='$instagram', facebook='$facebook', tiktok='$tiktok',
        tempat_kerja='$tempat_kerja', alamat_kerja='$alamat_kerja', posisi='$posisi',
        status_kerja='$jenis_kerja', sosmed_kantor='$sosmed_kantor',
        status_tracking='$status'
    WHERE id='$id'");

    header("Location: track.php?id=$id");
}
?>