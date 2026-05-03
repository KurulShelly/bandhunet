<?php
/**
 * api_save_tracking.php
 * Menerima JSON dari profiling.php dan menyimpan/update ke tabel tracking.
 * Kolom sesuai hasil DESCRIBE tracking:
 *   id_tracking, id_alumni, sosmed_linkedin, sosmed_ig, sosmed_fb, sosmed_tiktok,
 *   email, no_hp, tempat_kerja, alamat_kerja, posisi, jenis_instansi (enum),
 *   sosmed_kantor, nama_pt_pddikti, prodi_pt_pddikti, status_mhs,
 *   sumber_data, updated_at, created_at, status, tanggal_track
 */

session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['id_user'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

include 'koneksi.php';

$input = json_decode(file_get_contents('php://input'), true);

if (empty($input) || empty($input['alumni_id'])) {
    echo json_encode(['success' => false, 'message' => 'Data tidak valid atau alumni_id kosong']);
    exit;
}

// ── Sanitasi input ────────────────────────────────────────────────────────
$id_alumni       = (int)$input['alumni_id'];
$sosmed_linkedin  = substr(trim($input['sosmed_linkedin']  ?? ''), 0, 255);
$sosmed_ig        = substr(trim($input['sosmed_ig']        ?? ''), 0, 255);
$sosmed_fb        = substr(trim($input['sosmed_fb']        ?? ''), 0, 255);
$sosmed_tiktok    = substr(trim($input['sosmed_tiktok']    ?? ''), 0, 255);
$email            = substr(trim($input['email']            ?? ''), 0, 100);
$no_hp            = substr(trim($input['no_hp']            ?? ''), 0, 20);
$tempat_kerja     = substr(trim($input['tempat_kerja']     ?? ''), 0, 150);
$alamat_kerja     = trim($input['alamat_kerja']            ?? '');
$posisi           = substr(trim($input['posisi']           ?? ''), 0, 100);
$sosmed_kantor    = substr(trim($input['sosmed_kantor']    ?? ''), 0, 255);
$nama_pt_pddikti  = substr(trim($input['nama_pt_pddikti']  ?? ''), 0, 255);
$prodi_pt_pddikti = substr(trim($input['prodi_pt_pddikti'] ?? ''), 0, 150);
$status_mhs       = substr(trim($input['status_mhs']       ?? ''), 0, 80);
$sumber_data      = substr(trim($input['sumber_data']      ?? ''), 0, 150);

// jenis_instansi harus salah satu nilai enum yang valid
$valid_instansi   = ['PNS', 'Swasta', 'Wirausaha', 'Lainnya'];
$jenis_instansi   = in_array($input['jenis_instansi'] ?? '', $valid_instansi)
                    ? $input['jenis_instansi']
                    : 'Lainnya';

// status bebas (varchar 50), default 'Belum Dilacak'
$status           = substr(trim($input['status'] ?? 'Belum Dilacak'), 0, 50);
if ($status === '') $status = 'Belum Dilacak';

try {
    // Cek apakah baris tracking untuk alumni ini sudah ada
    $check = mysqli_prepare($koneksi, "SELECT id_tracking FROM tracking WHERE id_alumni = ? LIMIT 1");
    mysqli_stmt_bind_param($check, 'i', $id_alumni);
    mysqli_stmt_execute($check);
    $check_res  = mysqli_stmt_get_result($check);
    $existing   = mysqli_fetch_assoc($check_res);

    if ($existing) {
        // ── UPDATE ────────────────────────────────────────────────────────
        $sql = "UPDATE tracking SET
                    sosmed_linkedin  = ?,
                    sosmed_ig        = ?,
                    sosmed_fb        = ?,
                    sosmed_tiktok    = ?,
                    email            = ?,
                    no_hp            = ?,
                    tempat_kerja     = ?,
                    alamat_kerja     = ?,
                    posisi           = ?,
                    jenis_instansi   = ?,
                    sosmed_kantor    = ?,
                    nama_pt_pddikti  = ?,
                    prodi_pt_pddikti = ?,
                    status_mhs       = ?,
                    sumber_data      = ?,
                    status           = ?,
                    updated_at       = NOW(),
                    tanggal_track    = NOW()
                WHERE id_alumni = ?";

        $stmt = mysqli_prepare($koneksi, $sql);
        mysqli_stmt_bind_param($stmt, 'ssssssssssssssssi',
            $sosmed_linkedin,
            $sosmed_ig,
            $sosmed_fb,
            $sosmed_tiktok,
            $email,
            $no_hp,
            $tempat_kerja,
            $alamat_kerja,
            $posisi,
            $jenis_instansi,
            $sosmed_kantor,
            $nama_pt_pddikti,
            $prodi_pt_pddikti,
            $status_mhs,
            $sumber_data,
            $status,
            $id_alumni
        );
        mysqli_stmt_execute($stmt);

    } else {
        // ── INSERT ────────────────────────────────────────────────────────
        $sql = "INSERT INTO tracking
                    (id_alumni, sosmed_linkedin, sosmed_ig, sosmed_fb, sosmed_tiktok,
                     email, no_hp, tempat_kerja, alamat_kerja, posisi, jenis_instansi,
                     sosmed_kantor, nama_pt_pddikti, prodi_pt_pddikti, status_mhs,
                     sumber_data, status, updated_at, tanggal_track)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())";

        $stmt = mysqli_prepare($koneksi, $sql);
        mysqli_stmt_bind_param($stmt, 'issssssssssssssss',
            $id_alumni,
            $sosmed_linkedin,
            $sosmed_ig,
            $sosmed_fb,
            $sosmed_tiktok,
            $email,
            $no_hp,
            $tempat_kerja,
            $alamat_kerja,
            $posisi,
            $jenis_instansi,
            $sosmed_kantor,
            $nama_pt_pddikti,
            $prodi_pt_pddikti,
            $status_mhs,
            $sumber_data,
            $status
        );
        mysqli_stmt_execute($stmt);
    }

    if (mysqli_stmt_affected_rows($stmt) >= 0) {
        echo json_encode(['success' => true, 'message' => 'Data tracking berhasil disimpan']);
    } else {
        throw new Exception('Tidak ada baris yang terpengaruh');
    }

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}