<?php
session_start();
include 'koneksi.php';

function formatTanggalIndo($dateStr) {
    if (empty($dateStr)) return null;
    $bulanIndo = [
        'Januari' => '01', 'Februari' => '02', 'Maret' => '03', 'April' => '04',
        'Mei' => '05', 'Juni' => '06', 'Juli' => '07', 'Agustus' => '08',
        'September' => '09', 'Oktober' => '10', 'November' => '11', 'Desember' => '12'
    ];
    $dateStr = preg_replace('/\s+/', ' ', trim($dateStr));
    $pecah = explode(' ', $dateStr);
    if (count($pecah) == 3) {
        $tgl = str_pad($pecah[0], 2, '0', STR_PAD_LEFT);
        $bln = $bulanIndo[$pecah[1]] ?? '01';
        $thn = $pecah[2];
        return "$thn-$bln-$tgl";
    }
    $timestamp = strtotime($dateStr);
    return $timestamp ? date('Y-m-d', $timestamp) : null;
}

if (isset($_POST['upload'])) {
    $file = $_FILES['file_alumni']['tmp_name'];
    if (!$file) {
        echo "<script>alert('Pilih file terlebih dahulu!'); window.location='upload_excel.php';</script>";
        exit;
    }

    $handle = fopen($file, "r");
    // Deteksi separator (koma atau titik koma)
    $firstLine = fgets($handle);
    $separator = (strpos($firstLine, ';') !== false) ? ';' : ',';
    rewind($handle);
    
    $first_row = true;
    $success_count = 0;

    mysqli_begin_transaction($koneksi);

    try {
        while (($data = fgetcsv($handle, 1000, $separator)) !== FALSE) {
            // 1. Lewati Header (Baris 1)
            if ($first_row) { $first_row = false; continue; } 

            // 2. Validasi kolom minimal (Nama dan NIM)
            if (empty($data[0]) || empty($data[1])) continue;

            $nama  = mysqli_real_escape_string($koneksi, trim($data[0]));
            $nim   = mysqli_real_escape_string($koneksi, trim($data[1]));
            $masuk = !empty($data[2]) ? (int)$data[2] : "NULL";
            
            $tgl_lulus_raw = $data[3] ?? '';
            $tgl_lulus_fix = formatTanggalIndo($tgl_lulus_raw);
            $lulus = $tgl_lulus_fix ? "'$tgl_lulus_fix'" : "NULL";

            $fak   = mysqli_real_escape_string($koneksi, $data[4] ?? '');
            $prodi = mysqli_real_escape_string($koneksi, $data[5] ?? '');

            // 3. Insert atau Update jika NIM sudah ada (Upsert)
            $query_alumni = "INSERT INTO alumni (nama_lengkap, nim, tahun_masuk, tanggal_lulus, fakultas, prodi) 
                             VALUES ('$nama', '$nim', $masuk, $lulus, '$fak', '$prodi')
                             ON DUPLICATE KEY UPDATE 
                             nama_lengkap = VALUES(nama_lengkap),
                             tahun_masuk = VALUES(tahun_masuk),
                             tanggal_lulus = VALUES(tanggal_lulus),
                             fakultas = VALUES(fakultas),
                             prodi = VALUES(prodi)";
            
            if (!mysqli_query($koneksi, $query_alumni)) {
                throw new Exception("Gagal simpan/update alumni NIM $nim: " . mysqli_error($koneksi));
            }

            // 4. Ambil ID Alumni untuk tabel tracking
            $res_id = mysqli_query($koneksi, "SELECT id_alumni FROM alumni WHERE nim = '$nim'");
            $row_alumni = mysqli_fetch_assoc($res_id);
            $id_alumni = $row_alumni['id_alumni'];
            
            // 5. Tambah ke tracking jika belum ada barisnya
            $query_tracking = "INSERT IGNORE INTO tracking (id_alumni, status) VALUES ('$id_alumni', 'Belum Dilacak')";
            mysqli_query($koneksi, $query_tracking);
            
            $success_count++;
        }
        
        mysqli_commit($koneksi);
        fclose($handle);
        echo "<script>alert('Impor Selesai! $success_count data alumni telah diproses.'); window.location='alumni.php';</script>";

    } catch (Exception $e) {
        mysqli_rollback($koneksi);
        die("Terjadi Kesalahan: " . $e->getMessage());
    }
}
?>