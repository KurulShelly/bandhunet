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

    while (($data = fgetcsv($handle, 1000, ";")) !== FALSE) {

        $no++;

        if ($no == 1) continue; // skip header

        // ==========================
        // 🔹 AMBIL DATA DARI EXCEL
        // ==========================
        $nama           = trim($data[0] ?? '');
        $nim            = trim($data[1] ?? '');
        $tahun_masuk    = trim($data[2] ?? '');
        $tanggal_lulus  = trim($data[3] ?? '');
        $fakultas       = trim($data[4] ?? '');
        $prodi          = trim($data[5] ?? '');

        $email          = trim($data[7] ?? '');
        $no_hp          = trim($data[8] ?? '');
        $linkedin       = trim($data[9] ?? '');
        $instagram      = trim($data[10] ?? '');
        $facebook       = trim($data[11] ?? '');
        $tiktok         = trim($data[12] ?? '');
        $tempat_kerja   = trim($data[13] ?? '');
        $alamat_kerja   = trim($data[14] ?? '');
        $posisi         = trim($data[15] ?? '');
        $jenis_kerja    = trim($data[16] ?? '');
        $sosmed_kantor  = trim($data[17] ?? '');

        // skip kalau nama kosong
        if ($nama == '') continue;

        // ==========================
        // 🔹 AMBIL TAHUN LULUS
        // ==========================
        $tahun_lulus = substr($tanggal_lulus, -4);

        // ==========================
        // 🔹 ESCAPE DATA (AMAN)
        // ==========================
        $nama           = mysqli_real_escape_string($conn, $nama);
        $nim            = mysqli_real_escape_string($conn, $nim);
        $tahun_masuk    = mysqli_real_escape_string($conn, $tahun_masuk);
        $tahun_lulus    = mysqli_real_escape_string($conn, $tahun_lulus);
        $fakultas       = mysqli_real_escape_string($conn, $fakultas);
        $prodi          = mysqli_real_escape_string($conn, $prodi);

        $email          = mysqli_real_escape_string($conn, $email);
        $no_hp          = mysqli_real_escape_string($conn, $no_hp);
        $linkedin       = mysqli_real_escape_string($conn, $linkedin);
        $instagram      = mysqli_real_escape_string($conn, $instagram);
        $facebook       = mysqli_real_escape_string($conn, $facebook);
        $tiktok         = mysqli_real_escape_string($conn, $tiktok);
        $tempat_kerja   = mysqli_real_escape_string($conn, $tempat_kerja);
        $alamat_kerja   = mysqli_real_escape_string($conn, $alamat_kerja);
        $posisi         = mysqli_real_escape_string($conn, $posisi);
        $jenis_kerja    = mysqli_real_escape_string($conn, $jenis_kerja);
        $sosmed_kantor  = mysqli_real_escape_string($conn, $sosmed_kantor);

        // ==========================
        // 🔹 STATUS OTOMATIS
        // ==========================
        $status = ($email || $no_hp || $tempat_kerja || $linkedin)
            ? "Teridentifikasi"
            : "Belum Dilacak";

        // ==========================
        // 🔹 INSERT DATABASE
        // ==========================
        $query = "INSERT INTO alumni (
            nama, nim, tahun_masuk, tahun_lulus, fakultas, prodi,
            email, no_hp, linkedin, instagram, facebook, tiktok,
            tempat_kerja, alamat_kerja, posisi, status_kerja,
            sosmed_kantor, status_tracking
        ) VALUES (
            '$nama','$nim','$tahun_masuk','$tahun_lulus','$fakultas','$prodi',
            '$email','$no_hp','$linkedin','$instagram','$facebook','$tiktok',
            '$tempat_kerja','$alamat_kerja','$posisi','$jenis_kerja',
            '$sosmed_kantor','$status'
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