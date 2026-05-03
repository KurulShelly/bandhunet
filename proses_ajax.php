<?php
include 'koneksi.php';

// Ambil data JSON dari AJAX
$input = json_decode(file_get_contents('php://input'), true);

if (!empty($input)) {
    mysqli_begin_transaction($koneksi);
    try {
        foreach ($input as $row) {
            $nama  = mysqli_real_escape_string($koneksi, $row['nama']);
            $nim   = mysqli_real_escape_string($koneksi, $row['nim']);
            $masuk = !empty($row['masuk']) ? (int)$row['masuk'] : "NULL";
            
            // Mengambil teks "2 Juli 2000" apa adanya
            // Kita gunakan null coalescing (?? '') agar tidak error jika kolom kosong
            $lulus = mysqli_real_escape_string($koneksi, $row['tanggal_lulus'] ?? '');
            
            $fak   = mysqli_real_escape_string($koneksi, $row['fakultas']);
            $prodi = mysqli_real_escape_string($koneksi, $row['prodi']);
            
            // Query Insert & Update
            // Kolom tanggal_lulus sekarang akan terisi teks seperti di Excel
            $q_alumni = "INSERT INTO alumni (nama_lengkap, nim, tahun_masuk, tanggal_lulus, fakultas, prodi) 
                         VALUES ('$nama', '$nim', $masuk, '$lulus', '$fak', '$prodi')
                         ON DUPLICATE KEY UPDATE 
                            nama_lengkap = VALUES(nama_lengkap),
                            tahun_masuk = VALUES(tahun_masuk),
                            tanggal_lulus = VALUES(tanggal_lulus),
                            fakultas = VALUES(fakultas),
                            prodi = VALUES(prodi)";
            
            mysqli_query($koneksi, $q_alumni);

            // Logika untuk tabel tracking
            $id_alumni = mysqli_insert_id($koneksi) ?: 0;
            if ($id_alumni == 0) {
                $res = mysqli_query($koneksi, "SELECT id_alumni FROM alumni WHERE nim='$nim'");
                $d = mysqli_fetch_assoc($res);
                $id_alumni = $d['id_alumni'];
            }
            mysqli_query($koneksi, "INSERT IGNORE INTO tracking (id_alumni, status) VALUES ('$id_alumni', 'Belum Dilacak')");
        }
        
        mysqli_commit($koneksi);
        echo json_encode(['status' => 'success']);
    } catch (Exception $e) {
        mysqli_rollback($koneksi);
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
}