<?php
include "koneksi.php";

error_reporting(E_ALL);
ini_set('display_errors', 1);

set_time_limit(0);
ini_set('memory_limit', '512M');

if (isset($_POST['import'])) {

    $file = $_FILES['file']['tmp_name'];

    if (!$file) {
        die("File tidak ditemukan!");
    }

    $handle = fopen($file, "r");

    $no = 0;

    while (($data = fgetcsv($handle, 1000, ";")) !== FALSE) { // 🔥 ganti delimiter jadi ;

        $no++;

        if ($no == 1) continue; // skip header

        // ambil data
        $nama = trim($data[0] ?? '');
        $tanggal = trim($data[3] ?? '');

        // skip kalau kosong
        if ($nama == '') continue;

        // 🔥 ambil tahun saja (AMAN)
        $tahun_lulus = substr($tanggal, -4);

        // escape
        $nama = mysqli_real_escape_string($conn, $nama);
        $tahun_lulus = mysqli_real_escape_string($conn, $tahun_lulus);

        $query = "INSERT INTO alumni (
            nama,
            tahun_lulus,
            status_tracking
        ) VALUES (
            '$nama',
            '$tahun_lulus',
            'Belum Dilacak'
        )";

        $result = mysqli_query($conn, $query);

        if (!$result) {
            die("ERROR BARIS $no: " . mysqli_error($conn));
        }
    }

    fclose($handle);

    echo "<script>
        alert('Import berhasil!');
        window.location='alumni.php';
    </script>";
}
?>