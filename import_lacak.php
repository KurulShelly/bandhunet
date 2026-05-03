<?php
/**
 * import_lacak.php
 * ─────────────────────────────────────────────────────────────────
 * Mengimpor lacak.csv → cocokkan ke tabel alumni → isi tabel tracking
 * Strategi pencocokan (berurutan):
 *   1. NIM exact match
 *   2. NIM tanpa leading zero
 *   3. Nama lengkap (case-insensitive, strip gelar)
 * Semua data detail (email, HP, sosmed, karir) masuk ke tabel tracking
 * sehingga langsung muncul di alumni_detail.php
 * ─────────────────────────────────────────────────────────────────
 * HAPUS file ini setelah import selesai!
 */

session_start();
if (!isset($_SESSION['id_user'])) { header("Location: login.php"); exit; }
include 'koneksi.php';

set_time_limit(600);
ini_set('memory_limit', '256M');

$csvPath = __DIR__ . '/lacak.csv';
if (!file_exists($csvPath)) {
    die('<div style="font-family:sans-serif;padding:40px;background:#0D0F14;color:#FDA4AF;min-height:100vh">
    ❌ File <strong>lacak.csv</strong> tidak ditemukan.<br>
    Pastikan lacak.csv diletakkan di folder yang sama dengan file ini:<br>
    <code>' . htmlspecialchars(dirname($csvPath)) . '</code></div>');
}

// ── Helper: bersihkan nama dari gelar akademik ────────────────────────────
function stripGelar(string $nama): string {
    $gelar = ['S.Kom.','S.Kom','S.T.','S.T','S.IP.','S.IP','S.Pd.','S.Pd',
              'S.E.','S.E','S.H.','S.H','S.Sos.','S.Sos','M.T.','M.T',
              'M.Kom.','M.Kom','Dr.','Dr','Prof.','Prof','Drs.','Drs','ST.',
              'ST','S.Pd.I','S.Pd.I.'];
    $nama = trim($nama);
    foreach ($gelar as $g) {
        $nama = str_ireplace($g, '', $nama);
    }
    return preg_replace('/\s+/', ' ', trim($nama));
}

// ── Helper: normalisasi NIM (hapus leading zero, spasi) ──────────────────
function normNim(string $nim): string {
    return ltrim(trim(preg_replace('/\s+/', '', $nim)), '0');
}

// ── Baca seluruh alumni dari DB ke array untuk matching cepat ────────────
$allAlumni = [];
$resAll = mysqli_query($koneksi, "SELECT id_alumni, nim, nama_lengkap FROM alumni");
while ($row = mysqli_fetch_assoc($resAll)) {
    $allAlumni[] = [
        'id'        => (int)$row['id_alumni'],
        'nim'       => trim($row['nim']),
        'nim_norm'  => normNim($row['nim']),
        'nama'      => strtolower(stripGelar($row['nama_lengkap'])),
        'nama_raw'  => $row['nama_lengkap'],
    ];
}

// Index by NIM untuk lookup O(1)
$byNim     = [];
$byNimNorm = [];
$byNama    = [];
foreach ($allAlumni as $a) {
    $byNim[$a['nim']]          = $a;
    $byNimNorm[$a['nim_norm']] = $a;
    $byNama[$a['nama']]        = $a;
}

// ── Fungsi pencocokan ─────────────────────────────────────────────────────
function findAlumni(string $nim, string $nama, array $byNim, array $byNimNorm, array $byNama): ?array {
    $nimClean  = trim($nim);
    $nimNorm   = normNim($nim);
    $namaClean = strtolower(stripGelar($nama));

    // 1. NIM exact
    if (isset($byNim[$nimClean])) return $byNim[$nimClean];
    // 2. NIM tanpa leading zero
    if ($nimNorm && isset($byNimNorm[$nimNorm])) return $byNimNorm[$nimNorm];
    // 3. Nama exact (strip gelar)
    if (isset($byNama[$namaClean])) return $byNama[$namaClean];
    // 4. Nama partial (contains)
    foreach ($byNama as $key => $a) {
        if (str_contains($key, $namaClean) || str_contains($namaClean, $key)) return $a;
    }
    return null;
}

// ── Baca dan parse CSV ────────────────────────────────────────────────────
$lines = file($csvPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
$lines[0] = preg_replace('/^\xEF\xBB\xBF/', '', $lines[0]); // hapus BOM
array_shift($lines); // hapus header

function parseCsvRow(string $line): ?array {
    $cols = explode(';', $line);
    if (count($cols) < 18) return null;

    // Deteksi format: kolom pertama angka = ada prefix nomor urut
    $o = is_numeric(trim($cols[0])) ? 1 : 0;

    $nim = trim($cols[$o + 1] ?? '');
    if ($nim === '') return null;

    $kategori = trim($cols[$o + 8] ?? '');
    $jenis    = match(strtolower($kategori)) {
        'pns', 'asn'          => 'PNS',
        'bumn', 'bumd'        => 'Swasta',
        'swasta'              => 'Swasta',
        'wirausaha'           => 'Wirausaha',
        default               => 'Lainnya',
    };

    return [
        'nama'           => trim($cols[$o + 0] ?? ''),
        'nim'            => $nim,
        'tahun_masuk'    => trim($cols[$o + 2] ?? ''),
        'tanggal_lulus'  => trim($cols[$o + 3] ?? ''),
        'fakultas'       => trim($cols[$o + 4] ?? ''),
        'prodi'          => trim($cols[$o + 5] ?? ''),
        'email'          => trim($cols[$o + 6] ?? ''),
        'no_hp'          => trim($cols[$o + 7] ?? ''),
        'kategori'       => $kategori,
        'jenis_instansi' => $jenis,
        'posisi'         => trim($cols[$o + 9]  ?? ''),
        'tempat_kerja'   => trim($cols[$o + 10] ?? ''),
        'alamat_kerja'   => trim($cols[$o + 11] ?? ''),
        'sosmed_kantor'  => trim($cols[$o + 12] ?? ''),
        'sosmed_linkedin'=> trim($cols[$o + 13] ?? ''),
        'sosmed_ig'      => trim($cols[$o + 14] ?? ''),
        'sosmed_fb'      => trim($cols[$o + 15] ?? ''),
        'sosmed_tiktok'  => trim($cols[$o + 16] ?? ''),
        'score'          => (int)trim($cols[$o + 17] ?? '0'),
        'status_raw'     => strtolower(trim($cols[$o + 18] ?? '')),
    ];
}

// ── Proses import ─────────────────────────────────────────────────────────
$results   = []; // log per baris
$cntMatch  = 0;
$cntNew    = 0;
$cntSkip   = 0;
$cntErr    = 0;

mysqli_begin_transaction($koneksi);

try {
foreach ($lines as $lineNo => $line) {
    $line = trim($line);
    if ($line === '') continue;

    $d = parseCsvRow($line);
    if (!$d) { $cntSkip++; continue; }

    // Cari alumni di DB
    $found = findAlumni($d['nim'], $d['nama'], $byNim, $byNimNorm, $byNama);

    if ($found) {
        $id_alumni  = $found['id'];
        $matchType  = ($found['nim'] === $d['nim']) ? 'NIM' : 'Nama';
        $cntMatch++;
    } else {
        // Tidak ditemukan → INSERT alumni baru
        $stmt = mysqli_prepare($koneksi,
            "INSERT INTO alumni (nama_lengkap, nim, tahun_masuk, tanggal_lulus, fakultas, prodi, status_awal)
             VALUES (?, ?, ?, ?, ?, ?, ?)");
        $sa = $d['kategori'] ?: '';
        mysqli_stmt_bind_param($stmt, 'sssssss',
            $d['nama'], $d['nim'], $d['tahun_masuk'],
            $d['tanggal_lulus'], $d['fakultas'], $d['prodi'], $sa);
        mysqli_stmt_execute($stmt);
        $id_alumni = (int)mysqli_insert_id($koneksi);

        // Tambahkan ke index supaya baris duplikat di CSV juga ketemu
        $newEntry = ['id'=>$id_alumni,'nim'=>$d['nim'],'nim_norm'=>normNim($d['nim']),'nama'=>strtolower(stripGelar($d['nama'])),'nama_raw'=>$d['nama']];
        $byNim[$d['nim']]                    = $newEntry;
        $byNimNorm[normNim($d['nim'])]       = $newEntry;
        $byNama[strtolower(stripGelar($d['nama']))] = $newEntry;

        $matchType = 'Baru';
        $cntNew++;
    }

    // ── Update alumni (lengkapi data yang kosong) ─────────────────────
    $uStmt = mysqli_prepare($koneksi,
        "UPDATE alumni SET
            tanggal_lulus = IF(tanggal_lulus IS NULL OR tanggal_lulus='', ?, tanggal_lulus),
            fakultas      = IF(fakultas IS NULL OR fakultas='', ?, fakultas),
            prodi         = IF(prodi IS NULL OR prodi='', ?, prodi)
         WHERE id_alumni = ?");
    mysqli_stmt_bind_param($uStmt, 'sssi',
        $d['tanggal_lulus'], $d['fakultas'], $d['prodi'], $id_alumni);
    mysqli_stmt_execute($uStmt);

    // ── Upsert tracking ───────────────────────────────────────────────
    $status_track = 'Found'; // semua di CSV ini sudah terlacak
    $sumber       = 'Manual Import';

    $chk     = mysqli_query($koneksi, "SELECT id_tracking FROM tracking WHERE id_alumni=$id_alumni LIMIT 1");
    $hasTrack = mysqli_fetch_assoc($chk);

    if ($hasTrack) {
        $stmt = mysqli_prepare($koneksi,
            "UPDATE tracking SET
                email            = ?,
                no_hp            = ?,
                posisi           = ?,
                tempat_kerja     = ?,
                alamat_kerja     = ?,
                jenis_instansi   = ?,
                sosmed_kantor    = ?,
                sosmed_linkedin  = ?,
                sosmed_ig        = ?,
                sosmed_fb        = ?,
                sosmed_tiktok    = ?,
                sumber_data      = ?,
                status           = ?,
                updated_at       = NOW(),
                tanggal_track    = NOW()
             WHERE id_alumni = ?");
        mysqli_stmt_bind_param($stmt, 'sssssssssssssi',
            $d['email'], $d['no_hp'], $d['posisi'],
            $d['tempat_kerja'], $d['alamat_kerja'],
            $d['jenis_instansi'], $d['sosmed_kantor'],
            $d['sosmed_linkedin'], $d['sosmed_ig'],
            $d['sosmed_fb'], $d['sosmed_tiktok'],
            $sumber, $status_track, $id_alumni);
    } else {
        $stmt = mysqli_prepare($koneksi,
            "INSERT INTO tracking
                (id_alumni, email, no_hp, posisi, tempat_kerja, alamat_kerja,
                 jenis_instansi, sosmed_kantor, sosmed_linkedin, sosmed_ig,
                 sosmed_fb, sosmed_tiktok, sumber_data, status, updated_at, tanggal_track)
             VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,NOW(),NOW())");
        mysqli_stmt_bind_param($stmt, 'isssssssssssss',
            $id_alumni,
            $d['email'], $d['no_hp'], $d['posisi'],
            $d['tempat_kerja'], $d['alamat_kerja'],
            $d['jenis_instansi'], $d['sosmed_kantor'],
            $d['sosmed_linkedin'], $d['sosmed_ig'],
            $d['sosmed_fb'], $d['sosmed_tiktok'],
            $sumber, $status_track);
    }

    if (!mysqli_stmt_execute($stmt)) {
        $cntErr++;
        $results[] = ['type'=>'err','nama'=>$d['nama'],'nim'=>$d['nim'],'msg'=>mysqli_stmt_error($stmt)];
        continue;
    }

    $results[] = [
        'type'  => $matchType,
        'nama'  => $d['nama'],
        'nim'   => $d['nim'],
        'kerja' => $d['tempat_kerja'],
        'posisi'=> $d['posisi'],
        'id'    => $id_alumni,
    ];
}

mysqli_commit($koneksi);

} catch (Exception $e) {
    mysqli_rollback($koneksi);
    die('<div style="font-family:sans-serif;padding:40px;background:#0D0F14;color:#FDA4AF">❌ Error: '.htmlspecialchars($e->getMessage()).'</div>');
}

$total = $cntMatch + $cntNew;
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Import Selesai — BandhuNet</title>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:'Plus Jakarta Sans',sans-serif;background:#0D0F14;color:#F1F5F9;min-height:100vh;padding:32px}
.wrap{max-width:900px;margin:0 auto}
h1{font-size:22px;font-weight:700;margin-bottom:4px}
.sub{font-size:13px;color:#64748B;margin-bottom:24px}
.stats{display:grid;grid-template-columns:repeat(auto-fit,minmax(130px,1fr));gap:12px;margin-bottom:24px}
.stat{background:#13161E;border:1px solid rgba(255,255,255,.07);border-radius:10px;padding:16px;text-align:center}
.stat .v{font-size:30px;font-weight:700;letter-spacing:-1px}
.stat .l{font-size:11px;color:#64748B;text-transform:uppercase;letter-spacing:.7px;margin-top:4px}
.v-green{color:#6EE7B7}.v-blue{color:#A5B4FC}.v-amber{color:#FDE68A}.v-red{color:#FDA4AF}.v-muted{color:#64748B}
.warn{background:rgba(245,158,11,.08);border:1px solid rgba(245,158,11,.2);border-radius:10px;padding:12px 16px;font-size:12.5px;color:#FDE68A;margin-bottom:20px;line-height:1.7}
.btn-row{display:flex;gap:10px;margin-bottom:28px;flex-wrap:wrap}
.btn{display:inline-flex;align-items:center;gap:6px;padding:9px 18px;border-radius:9px;font-family:inherit;font-size:13px;font-weight:600;cursor:pointer;text-decoration:none;border:none;transition:opacity .15s}
.btn-primary{background:#6366F1;color:#fff}.btn-primary:hover{opacity:.85}
.btn-emerald{background:rgba(16,185,129,.15);border:1px solid rgba(16,185,129,.3);color:#6EE7B7}.btn-emerald:hover{background:rgba(16,185,129,.25)}
.btn-ghost{background:transparent;border:1px solid rgba(255,255,255,.12);color:#64748B}.btn-ghost:hover{background:#1E2433;color:#F1F5F9}
/* Table */
.tbl-wrap{background:#13161E;border:1px solid rgba(255,255,255,.07);border-radius:12px;overflow:hidden}
.tbl-header{padding:14px 18px;border-bottom:1px solid rgba(255,255,255,.07);display:flex;align-items:center;justify-content:space-between}
.tbl-header h3{font-size:13.5px;font-weight:600}
.tbl-header span{font-size:12px;color:#64748B}
.search-bar{padding:10px 16px;border-bottom:1px solid rgba(255,255,255,.07);position:relative}
.search-bar input{width:100%;height:34px;background:#191D27;border:1px solid rgba(255,255,255,.12);border-radius:8px;padding:0 12px 0 32px;font-family:inherit;font-size:13px;color:#F1F5F9;outline:none}
.search-bar svg{position:absolute;left:26px;top:50%;transform:translateY(-50%);width:14px;height:14px;stroke:#64748B;fill:none;pointer-events:none}
.tbl-scroll{max-height:480px;overflow-y:auto;scrollbar-width:thin;scrollbar-color:#1E2433 transparent}
table{width:100%;border-collapse:collapse}
thead tr{border-bottom:1px solid rgba(255,255,255,.07)}
thead th{padding:10px 14px;font-size:10.5px;font-weight:600;text-transform:uppercase;letter-spacing:.7px;color:#64748B;text-align:left;background:rgba(255,255,255,.02);white-space:nowrap}
thead th:first-child{padding-left:18px}
thead th:last-child{padding-right:18px}
tbody tr{border-bottom:1px solid rgba(255,255,255,.05);transition:background .1s}
tbody tr:last-child{border-bottom:none}
tbody tr:hover{background:rgba(255,255,255,.02)}
tbody td{padding:10px 14px;font-size:12.5px;vertical-align:middle}
tbody td:first-child{padding-left:18px}
tbody td:last-child{padding-right:18px}
.badge{display:inline-flex;align-items:center;gap:4px;font-size:10.5px;font-weight:600;padding:2px 8px;border-radius:20px;border:1px solid;white-space:nowrap}
.badge-nim{background:rgba(99,102,241,.1);border-color:rgba(99,102,241,.25);color:#A5B4FC}
.badge-nama{background:rgba(245,158,11,.08);border-color:rgba(245,158,11,.2);color:#FDE68A}
.badge-new{background:rgba(16,185,129,.08);border-color:rgba(16,185,129,.2);color:#6EE7B7}
.badge-err{background:rgba(244,63,94,.08);border-color:rgba(244,63,94,.2);color:#FDA4AF}
.nim-cell{font-family:'Courier New',monospace;font-size:11.5px;color:#64748B}
a.detail-link{color:#A5B4FC;text-decoration:none;font-weight:500;font-size:12px}
a.detail-link:hover{text-decoration:underline}
</style>
</head>
<body>
<div class="wrap">

<h1>✅ Import Selesai</h1>
<p class="sub">Data <strong>lacak.csv</strong> berhasil dicocokkan dan dimasukkan ke database</p>

<div class="stats">
    <div class="stat"><div class="v v-green"><?= number_format($total) ?></div><div class="l">Total Diproses</div></div>
    <div class="stat"><div class="v v-blue"><?= number_format($cntMatch) ?></div><div class="l">Cocok di DB</div></div>
    <div class="stat"><div class="v v-amber"><?= number_format($cntNew) ?></div><div class="l">Alumni Baru</div></div>
    <div class="stat"><div class="v v-muted"><?= number_format($cntSkip) ?></div><div class="l">Dilewati</div></div>
    <div class="stat"><div class="v v-red"><?= number_format($cntErr) ?></div><div class="l">Error</div></div>
</div>

<div class="warn">
    ⚠ <strong>Penting:</strong> Hapus file <code>import_lacak.php</code> dan <code>lacak.csv</code> dari server setelah selesai memeriksa hasil ini.
</div>

<div class="btn-row">
    <a href="alumni.php" class="btn btn-primary">Daftar Alumni</a>
    <a href="profiling.php" class="btn btn-emerald">Profiling Engine</a>
    <a href="dashboard.php" class="btn btn-ghost">Dashboard</a>
</div>

<!-- Tabel hasil -->
<div class="tbl-wrap">
    <div class="tbl-header">
        <h3>Log Hasil Import (<?= count($results) ?> baris)</h3>
        <span>Klik Detail untuk lihat di alumni_detail</span>
    </div>
    <div class="search-bar">
        <svg stroke-width="2" stroke-linecap="round" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
        <input type="text" id="srch" placeholder="Cari nama atau NIM...">
    </div>
    <div class="tbl-scroll">
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Nama</th>
                    <th>NIM</th>
                    <th>Tempat Kerja</th>
                    <th>Posisi</th>
                    <th>Cocok via</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody id="tbody">
            <?php foreach ($results as $i => $r): ?>
            <?php if ($r['type'] === 'err'): ?>
            <tr data-nama="<?= strtolower($r['nama']) ?>" data-nim="<?= strtolower($r['nim']) ?>">
                <td style="color:#64748B"><?= $i+1 ?></td>
                <td><?= htmlspecialchars($r['nama']) ?></td>
                <td class="nim-cell"><?= htmlspecialchars($r['nim']) ?></td>
                <td colspan="3" style="color:#FDA4AF;font-size:12px">❌ <?= htmlspecialchars($r['msg']) ?></td>
                <td>—</td>
            </tr>
            <?php else: ?>
            <tr data-nama="<?= strtolower($r['nama']) ?>" data-nim="<?= strtolower($r['nim']) ?>">
                <td style="color:#64748B;font-size:12px"><?= $i+1 ?></td>
                <td style="font-weight:500"><?= htmlspecialchars($r['nama']) ?></td>
                <td class="nim-cell"><?= htmlspecialchars($r['nim']) ?></td>
                <td style="color:#94A3B8;font-size:12px"><?= htmlspecialchars($r['kerja']) ?></td>
                <td style="color:#94A3B8;font-size:12px"><?= htmlspecialchars($r['posisi']) ?></td>
                <td>
                    <?php if ($r['type'] === 'NIM'): ?>
                    <span class="badge badge-nim">NIM</span>
                    <?php elseif ($r['type'] === 'Nama'): ?>
                    <span class="badge badge-nama">Nama</span>
                    <?php else: ?>
                    <span class="badge badge-new">+ Baru</span>
                    <?php endif; ?>
                </td>
                <td><a href="alumni_detail.php?id=<?= $r['id'] ?>" class="detail-link" target="_blank">Detail →</a></td>
            </tr>
            <?php endif; ?>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

</div>
<script>
document.getElementById('srch').addEventListener('input', function() {
    const q = this.value.toLowerCase();
    document.querySelectorAll('#tbody tr').forEach(tr => {
        const nama = tr.dataset.nama || '';
        const nim  = tr.dataset.nim  || '';
        tr.style.display = !q || nama.includes(q) || nim.includes(q) ? '' : 'none';
    });
});
</script>
</body>
</html>