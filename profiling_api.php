<?php
include 'koneksi.php';
header('Content-Type: application/json');

// 1. Ambil 1 data yang belum diproses
$sql = "SELECT alumni.id_alumni, alumni.nama_lengkap, alumni.nim 
        FROM alumni 
        JOIN tracking ON alumni.id_alumni = tracking.id_alumni 
        WHERE tracking.status = 'Belum Dilacak' 
        LIMIT 1";

$res = mysqli_query($koneksi, $sql);
$alumni = mysqli_fetch_assoc($res);

if (!$alumni) {
    echo json_encode(['status' => 'done']);
    exit;
}

$id = $alumni['id_alumni'];
$nama = $alumni['nama_lengkap'];
$nim = $alumni['nim'];

/** * SIMULASI PROFILING (Logika Pencarian)
 * Di sini Anda bisa memasukkan integrasi API PDDIKTI atau Scraping Google
 * Untuk sementara, kita buat simulasi "Found" secara acak
 */
$status_pilihan = ['Found', 'Not Found'];
$hasil_status = $status_pilihan[array_rand($status_pilihan)];
$tempat_kerja = ($hasil_status == 'Found') ? "Perusahaan " . chr(rand(65,90)) : "";

// 2. Update hasil tracking ke database
$update = "UPDATE tracking SET 
           status = '$hasil_status', 
           tempat_kerja = '$tempat_kerja', 
           tanggal_track = NOW() 
           WHERE id_alumni = '$id'";
mysqli_query($koneksi, $update);

// 3. Hitung persentase untuk progress bar
$total_all = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) as jml FROM tracking"))['jml'];
$total_done = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) as jml FROM tracking WHERE status != 'Belum Dilacak'"))['jml'];
$persen = round(($total_done / $total_all) * 100);

echo json_encode([
    'status' => 'process',
    'nama_alumni' => $nama,
    'nim' => $nim,
    'persentase' => $persen
]);