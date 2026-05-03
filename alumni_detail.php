<?php
session_start();
if (!isset($_SESSION['id_user'])) { header("Location: login.php"); exit; }
include 'koneksi.php';
include 'layout/header.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$id) { header("Location: alumni.php"); exit; }

// ── Query alumni + tracking ─────────────────────────────────────────────
$stmt = mysqli_prepare($koneksi,
    "SELECT a.*,
            t.id_tracking,
            t.sosmed_linkedin,
            t.sosmed_ig,
            t.sosmed_fb,
            t.sosmed_tiktok,
            t.email,
            t.no_hp,
            t.tempat_kerja,
            t.alamat_kerja,
            t.posisi,
            t.jenis_instansi,
            t.sosmed_kantor,
            t.nama_pt_pddikti,
            t.prodi_pt_pddikti,
            t.status_mhs,
            t.sumber_data,
            t.status         AS status_tracking,
            t.updated_at     AS tracking_updated
     FROM alumni a
     LEFT JOIN tracking t ON a.id_alumni = t.id_alumni
     WHERE a.id_alumni = ?");
mysqli_stmt_bind_param($stmt, 'i', $id);
mysqli_stmt_execute($stmt);
$res  = mysqli_stmt_get_result($stmt);
$data = mysqli_fetch_assoc($res);
if (!$data) { header("Location: alumni.php"); exit; }

// ── Helper ─────────────────────────────────────────────────────────────────
function val(mixed $v, string $fallback = '—'): string {
    return (!empty(trim((string)$v))) ? htmlspecialchars($v) : $fallback;
}
function fmtDate(?string $d): string {
    if (!$d || $d === '0000-00-00') return '—';
    $ts = strtotime($d);
    if (!$ts) return htmlspecialchars($d);
    $bln = ['','Jan','Feb','Mar','Apr','Mei','Jun','Jul','Ags','Sep','Okt','Nov','Des'];
    return date('d', $ts) . ' ' . $bln[(int)date('n', $ts)] . ' ' . date('Y', $ts);
}

// Status tracking
$st    = strtolower(trim($data['status_tracking'] ?? ''));
$stCls = match(true) {
    $st === 'found'        => 'found',
    $st === 'partial'      => 'partial',
    default                => 'notfound',
};
$stLabel = match($stCls) {
    'found'    => 'Found',
    'partial'  => 'Partial',
    default    => 'Belum Dilacak',
};

// Jenis instansi label
$jiLabel = match($data['jenis_instansi'] ?? '') {
    'PNS'       => ['PNS / ASN',  'indigo'],
    'Swasta'    => ['Swasta',     'violet'],
    'Wirausaha' => ['Wirausaha',  'amber'],
    default     => ['Lainnya',    'muted'],
};

// Sumber data chips
$sumber = array_filter(array_map('trim', explode(',', $data['sumber_data'] ?? '')));

$hasTracking = !empty($data['id_tracking']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title><?= val($data['nama_lengkap']) ?> — BandhuNet</title>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<style>
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
:root {
    --bg:       #0D0F14;
    --surface:  #13161E;
    --surface2: #191D27;
    --border:   rgba(255,255,255,0.07);
    --border2:  rgba(255,255,255,0.12);
    --text:     #F1F5F9;
    --muted:    #64748B;
    --subtle:   #1E2433;
    --indigo:   #6366F1;
    --indigo-l: #A5B4FC;
    --emerald:  #10B981;
    --emerald-l:#6EE7B7;
    --amber:    #F59E0B;
    --amber-l:  #FDE68A;
    --rose:     #F43F5E;
    --rose-l:   #FDA4AF;
    --violet:   #8B5CF6;
    --violet-l: #C4B5FD;
}
body { font-family:'Plus Jakarta Sans',sans-serif; background:var(--bg); color:var(--text); min-height:100vh; display:flex; }
.main-layout { display:flex; width:100%; }
.page-content { flex:1; min-width:0; display:flex; flex-direction:column; }

/* Topbar */
.topbar { display:flex; align-items:center; justify-content:space-between; padding:0 28px; height:60px; background:var(--surface); border-bottom:1px solid var(--border); flex-shrink:0; }
.topbar-left h2 { font-size:15px; font-weight:600; letter-spacing:-0.2px; }
.topbar-left p  { font-size:12px; color:var(--muted); margin-top:1px; }
.topbar-right   { display:flex; align-items:center; gap:12px; }
.welcome-chip { display:flex; align-items:center; gap:8px; background:var(--subtle); border:1px solid var(--border); border-radius:20px; padding:6px 12px 6px 6px; font-size:12.5px; color:var(--muted); }
.welcome-chip strong { color:var(--text); font-weight:500; }
.avatar-sm { width:26px; height:26px; border-radius:50%; background:linear-gradient(135deg,var(--indigo),#818CF8); display:flex; align-items:center; justify-content:center; font-size:11px; font-weight:600; color:#fff; }
.logout-chip { display:flex; align-items:center; gap:6px; background:rgba(244,63,94,.08); border:1px solid rgba(244,63,94,.2); border-radius:8px; padding:6px 12px; font-size:12px; font-weight:500; color:#FB7185; text-decoration:none; transition:background .15s; }
.logout-chip:hover { background:rgba(244,63,94,.15); }
.logout-chip svg { width:13px; height:13px; }

/* Body */
.dash-body { flex:1; padding:28px; overflow-y:auto; }
@keyframes fadeUp { from{opacity:0;transform:translateY(10px)} to{opacity:1;transform:translateY(0)} }
@keyframes pulse  { 0%,100%{opacity:1} 50%{opacity:.4} }

/* Breadcrumb */
.breadcrumb { display:flex; align-items:center; gap:8px; font-size:12.5px; color:var(--muted); margin-bottom:20px; animation:fadeUp .3s ease both; }
.breadcrumb a { color:var(--muted); text-decoration:none; transition:color .15s; }
.breadcrumb a:hover { color:var(--text); }
.breadcrumb svg { width:13px; height:13px; }

/* Hero card */
.hero-card {
    background:var(--surface);
    border:1px solid var(--border);
    border-radius:16px;
    overflow:hidden;
    margin-bottom:20px;
    animation:fadeUp .35s ease .05s both;
}
.hero-banner {
    height:90px;
    background:linear-gradient(135deg,rgba(99,102,241,.25),rgba(139,92,246,.2),rgba(16,185,129,.15));
    border-bottom:1px solid var(--border);
    position:relative;
}
.hero-banner::after {
    content:'';
    position:absolute; inset:0;
    background:repeating-linear-gradient(45deg,transparent,transparent 20px,rgba(255,255,255,.015) 20px,rgba(255,255,255,.015) 40px);
}
.hero-body { padding:0 24px 24px; }
.avatar-wrap {
    width:72px; height:72px;
    border-radius:18px;
    background:linear-gradient(135deg,var(--indigo),var(--violet));
    display:flex; align-items:center; justify-content:center;
    font-size:28px; font-weight:700; color:#fff;
    margin-top:-36px; margin-bottom:14px;
    border:3px solid var(--bg);
    position:relative; z-index:1;
    box-shadow:0 8px 24px rgba(99,102,241,.35);
}
.hero-name { font-size:22px; font-weight:700; letter-spacing:-.4px; margin-bottom:6px; }
.hero-meta { display:flex; align-items:center; gap:10px; flex-wrap:wrap; }
.meta-chip {
    display:inline-flex; align-items:center; gap:5px;
    font-size:12px; font-weight:500;
    padding:4px 10px; border-radius:20px;
    background:rgba(255,255,255,.05); border:1px solid var(--border2);
    color:var(--muted);
}
.meta-chip svg { width:12px; height:12px; }

/* Status badge */
.sbadge { display:inline-flex; align-items:center; gap:5px; font-size:12px; font-weight:600; padding:5px 12px; border-radius:20px; border:1px solid; }
.sbadge .dot { width:7px; height:7px; border-radius:50%; flex-shrink:0; }
.sbadge.found    { background:rgba(16,185,129,.1); border-color:rgba(16,185,129,.25); color:var(--emerald-l); }
.sbadge.found .dot { background:var(--emerald); box-shadow:0 0 6px rgba(16,185,129,.6); animation:pulse 2s infinite; }
.sbadge.partial  { background:rgba(245,158,11,.08); border-color:rgba(245,158,11,.22); color:var(--amber-l); }
.sbadge.partial .dot { background:var(--amber); }
.sbadge.notfound { background:rgba(244,63,94,.08); border-color:rgba(244,63,94,.22); color:var(--rose-l); }
.sbadge.notfound .dot { background:var(--rose); }

/* Action buttons */
.hero-actions { display:flex; align-items:center; gap:10px; margin-top:16px; flex-wrap:wrap; }
.btn-action {
    display:inline-flex; align-items:center; gap:7px;
    padding:8px 16px; border-radius:9px;
    font-family:'Plus Jakarta Sans',sans-serif; font-size:13px; font-weight:600;
    text-decoration:none; cursor:pointer; transition:all .15s; border:none;
}
.btn-primary { background:var(--indigo); color:#fff; }
.btn-primary:hover { background:#5254CC; }
.btn-ghost   { background:transparent; color:var(--muted); border:1px solid var(--border2); }
.btn-ghost:hover { background:var(--subtle); color:var(--text); }
.btn-action svg { width:14px; height:14px; }

/* Grid layout */
.detail-grid { display:grid; grid-template-columns:1fr 1fr; gap:20px; animation:fadeUp .35s ease .1s both; }
@media(max-width:800px){ .detail-grid { grid-template-columns:1fr; } }
.detail-grid.full { grid-template-columns:1fr; }

/* Section card */
.section-card { background:var(--surface); border:1px solid var(--border); border-radius:14px; overflow:hidden; }
.section-card.span2 { grid-column: 1 / -1; }
.section-header { display:flex; align-items:center; gap:10px; padding:15px 20px; border-bottom:1px solid var(--border); }
.section-header h4 { font-size:13.5px; font-weight:600; }
.section-icon { width:30px; height:30px; border-radius:8px; display:flex; align-items:center; justify-content:center; flex-shrink:0; }
.section-icon svg { width:14px; height:14px; }
.section-icon.indigo  { background:rgba(99,102,241,.12); color:var(--indigo-l); }
.section-icon.emerald { background:rgba(16,185,129,.12); color:var(--emerald-l); }
.section-icon.amber   { background:rgba(245,158,11,.12);  color:var(--amber-l); }
.section-icon.violet  { background:rgba(139,92,246,.12); color:var(--violet-l); }
.section-icon.rose    { background:rgba(244,63,94,.10);  color:var(--rose-l); }

/* Field list */
.field-list { padding:4px 0; }
.field-row { display:flex; align-items:flex-start; gap:12px; padding:11px 20px; border-bottom:1px solid var(--border); }
.field-row:last-child { border-bottom:none; }
.field-icon { width:30px; height:30px; border-radius:8px; display:flex; align-items:center; justify-content:center; flex-shrink:0; margin-top:1px; }
.field-icon svg { width:14px; height:14px; }
.field-label { font-size:11px; font-weight:600; text-transform:uppercase; letter-spacing:.7px; color:var(--muted); margin-bottom:3px; }
.field-value { font-size:13.5px; font-weight:500; color:var(--text); word-break:break-word; }
.field-value.empty { color:var(--muted); font-style:italic; font-weight:400; font-size:13px; }
.field-value a { color:var(--indigo-l); text-decoration:none; }
.field-value a:hover { text-decoration:underline; }

/* Social link chips */
.social-row { display:flex; flex-wrap:wrap; gap:8px; margin-top:2px; }
.social-link {
    display:inline-flex; align-items:center; gap:6px;
    padding:6px 14px; border-radius:20px; border:1px solid;
    font-size:12.5px; font-weight:500; text-decoration:none;
    transition:opacity .15s, transform .1s;
}
.social-link:hover { opacity:.8; transform:translateY(-1px); }
.sl-li  { background:rgba(10,102,194,.12); border-color:rgba(10,102,194,.3);  color:#7EC8E3; }
.sl-ig  { background:rgba(225,48,108,.1);  border-color:rgba(225,48,108,.25); color:#F9A8D4; }
.sl-fb  { background:rgba(66,103,178,.1);  border-color:rgba(66,103,178,.25); color:#93C5FD; }
.sl-tk  { background:rgba(255,255,255,.05);border-color:rgba(255,255,255,.12);color:var(--text); }
.sl-web { background:rgba(99,102,241,.1);  border-color:rgba(99,102,241,.25); color:var(--indigo-l); }

/* Sumber chips */
.sumber-row { display:flex; flex-wrap:wrap; gap:6px; padding:14px 20px; }
.sumber-chip { display:inline-flex; align-items:center; gap:5px; font-size:11.5px; font-weight:500; padding:4px 10px; border-radius:20px; background:rgba(99,102,241,.08); border:1px solid rgba(99,102,241,.2); color:var(--indigo-l); }
.sumber-chip svg { width:11px; height:11px; }

/* Instansi badge */
.ji-badge { display:inline-flex; align-items:center; gap:6px; font-size:13px; font-weight:600; padding:5px 14px; border-radius:20px; }
.ji-indigo  { background:rgba(99,102,241,.1); border:1px solid rgba(99,102,241,.25); color:var(--indigo-l); }
.ji-violet  { background:rgba(139,92,246,.1); border:1px solid rgba(139,92,246,.25); color:var(--violet-l); }
.ji-amber   { background:rgba(245,158,11,.1); border:1px solid rgba(245,158,11,.25); color:var(--amber-l); }
.ji-muted   { background:rgba(255,255,255,.05); border:1px solid var(--border2); color:var(--muted); }

/* Empty tracking state */
.empty-tracking {
    text-align:center; padding:48px 24px;
}
.empty-tracking .et-icon {
    width:56px; height:56px; border-radius:16px;
    background:rgba(255,255,255,.04); border:1px solid var(--border);
    display:flex; align-items:center; justify-content:center;
    margin:0 auto 14px; color:var(--muted);
}
.empty-tracking .et-icon svg { width:24px; height:24px; }
.empty-tracking h4 { font-size:15px; font-weight:600; margin-bottom:6px; }
.empty-tracking p  { font-size:13px; color:var(--muted); margin-bottom:18px; }

/* Footer */
.dash-footer { padding:16px 28px; border-top:1px solid var(--border); font-size:11.5px; color:var(--muted); display:flex; align-items:center; justify-content:space-between; }
.status-dot { display:inline-block; width:7px; height:7px; background:var(--emerald); border-radius:50%; margin-right:6px; box-shadow:0 0 6px rgba(16,185,129,.6); animation:pulse 2s infinite; }
</style>
</head>
<body>
<div class="main-layout">
<?php include 'layout/sidebar.php'; ?>

<div class="page-content">

<!-- Topbar -->
<header class="topbar">
    <div class="topbar-left">
        <h2>Detail Alumni</h2>
        <p><?= val($data['nama_lengkap']) ?></p>
    </div>
    <div class="topbar-right">
        <div class="welcome-chip">
            <div class="avatar-sm"><?= strtoupper(substr($_SESSION['nama'],0,1)) ?></div>
            <span>Halo, <strong><?= htmlspecialchars($_SESSION['nama']) ?></strong></span>
        </div>
        <a href="logout.php" class="logout-chip">
            <svg fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" viewBox="0 0 24 24">
                <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
                <polyline points="16 17 21 12 16 7"/>
                <line x1="21" y1="12" x2="9" y2="12"/>
            </svg>
            Keluar
        </a>
    </div>
</header>

<div class="dash-body">

    <!-- Breadcrumb -->
    <div class="breadcrumb">
        <a href="dashboard.php">Dashboard</a>
        <svg fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" viewBox="0 0 24 24"><polyline points="9 18 15 12 9 6"/></svg>
        <a href="alumni.php">Daftar Alumni</a>
        <svg fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" viewBox="0 0 24 24"><polyline points="9 18 15 12 9 6"/></svg>
        <span><?= val($data['nama_lengkap']) ?></span>
    </div>

    <!-- Hero card -->
    <div class="hero-card">
        <div class="hero-banner"></div>
        <div class="hero-body">
            <div class="avatar-wrap"><?= strtoupper(substr($data['nama_lengkap'],0,1)) ?></div>
            <div class="hero-name"><?= val($data['nama_lengkap']) ?></div>
            <div class="hero-meta">
                <?php if(!empty($data['nim'])): ?>
                <span class="meta-chip">
                    <svg fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" viewBox="0 0 24 24"><rect x="2" y="5" width="20" height="14" rx="2"/><line x1="2" y1="10" x2="22" y2="10"/></svg>
                    NIM: <?= val($data['nim']) ?>
                </span>
                <?php endif; ?>
                <?php if(!empty($data['prodi'])): ?>
                <span class="meta-chip">
                    <svg fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" viewBox="0 0 24 24"><path d="M22 10v6M2 10l10-5 10 5-10 5z"/><path d="M6 12v5c3 3 9 3 12 0v-5"/></svg>
                    <?= val($data['prodi']) ?>
                </span>
                <?php endif; ?>
                <?php if(!empty($data['fakultas'])): ?>
                <span class="meta-chip">
                    <svg fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" viewBox="0 0 24 24"><rect x="4" y="2" width="16" height="20" rx="1"/><path d="M9 22V12h6v10"/></svg>
                    <?= val($data['fakultas']) ?>
                </span>
                <?php endif; ?>
                <?php if(!empty($data['tanggal_lulus'])): ?>
                <span class="meta-chip">
                    <svg fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                    Lulus: <?= fmtDate($data['tanggal_lulus']) ?>
                </span>
                <?php endif; ?>
                <!-- Status tracking -->
                <span class="sbadge <?= $stCls ?>">
                    <span class="dot"></span>
                    <?= $stLabel ?>
                </span>
            </div>

            <div class="hero-actions">
                <a href="profiling.php" class="btn-action btn-primary">
                    <svg fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" viewBox="0 0 24 24"><polygon points="5 3 19 12 5 21 5 3"/></svg>
                    Jalankan Profiling
                </a>
                <a href="alumni.php" class="btn-action btn-ghost">
                    <svg fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" viewBox="0 0 24 24"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
                    Kembali ke Daftar
                </a>
                <?php if($hasTracking && !empty($data['tracking_updated'])): ?>
                <span style="font-size:11.5px;color:var(--muted);margin-left:auto">
                    Terakhir diperbarui: <?= fmtDate($data['tracking_updated']) ?>
                </span>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- ── Detail grid ─────────────────────────────────────────────────── -->
    <div class="detail-grid">

        <!-- Kolom kiri: Data Akademik -->
        <div class="section-card">
            <div class="section-header">
                <div class="section-icon indigo">
                    <svg fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M22 10v6M2 10l10-5 10 5-10 5z"/><path d="M6 12v5c3 3 9 3 12 0v-5"/></svg>
                </div>
                <h4>Data Akademik</h4>
            </div>
            <div class="field-list">
                <?php
                $akademik = [
                    ['NIM',           $data['nim']           ?? null, 'hash'],
                    ['Nama Lengkap',  $data['nama_lengkap']  ?? null, 'user'],
                    ['Program Studi', $data['prodi']         ?? null, 'award'],
                    ['Fakultas',      $data['fakultas']      ?? null, 'building'],
                    ['Tahun Masuk',   $data['tahun_masuk']   ?? null, 'log-in'],
                    ['Tanggal Lulus', fmtDate($data['tanggal_lulus'] ?? null), 'calendar'],
                    ['Status Awal',   $data['status_awal']   ?? null, 'info'],
                ];
                $icons = [
                    'hash'     => '<line x1="4" y1="9" x2="20" y2="9"/><line x1="4" y1="15" x2="20" y2="15"/><line x1="10" y1="3" x2="8" y2="21"/><line x1="16" y1="3" x2="14" y2="21"/>',
                    'user'     => '<path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>',
                    'award'    => '<circle cx="12" cy="8" r="6"/><path d="M15.477 12.89L17 22l-5-3-5 3 1.523-9.11"/>',
                    'building' => '<rect x="4" y="2" width="16" height="20" rx="1"/><path d="M9 22V12h6v10"/>',
                    'log-in'   => '<path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/><polyline points="10 17 15 12 10 7"/><line x1="15" y1="12" x2="3" y2="12"/>',
                    'calendar' => '<rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/>',
                    'info'     => '<circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>',
                ];
                foreach ($akademik as [$label, $value, $icon]):
                    $isEmpty = (!$value || $value === '—');
                ?>
                <div class="field-row">
                    <div class="field-icon" style="background:rgba(99,102,241,.08);color:var(--indigo-l)">
                        <svg fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><?= $icons[$icon] ?? '' ?></svg>
                    </div>
                    <div>
                        <div class="field-label"><?= $label ?></div>
                        <div class="field-value <?= $isEmpty ? 'empty' : '' ?>"><?= $isEmpty ? 'Tidak tersedia' : htmlspecialchars((string)$value) ?></div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Kolom kanan: Data Kontak & Sosial -->
        <div class="section-card">
            <div class="section-header">
                <div class="section-icon emerald">
                    <svg fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M22 16.92v3a2 2 0 0 1-2.18 2A19.79 19.79 0 0 1 11.69 19a19.5 19.5 0 0 1-6-6A19.79 19.79 0 0 1 3.09 4.18 2 2 0 0 1 5.06 2h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L9.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                </div>
                <h4>Kontak &amp; Sosial Media</h4>
            </div>

            <?php if ($hasTracking): ?>
            <div class="field-list">

                <!-- Email -->
                <div class="field-row">
                    <div class="field-icon" style="background:rgba(16,185,129,.08);color:var(--emerald-l)">
                        <svg fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
                    </div>
                    <div>
                        <div class="field-label">Email</div>
                        <?php if(!empty($data['email'])): ?>
                        <div class="field-value"><a href="mailto:<?= val($data['email']) ?>"><?= val($data['email']) ?></a></div>
                        <?php else: ?><div class="field-value empty">Tidak ditemukan</div><?php endif; ?>
                    </div>
                </div>

                <!-- No HP -->
                <div class="field-row">
                    <div class="field-icon" style="background:rgba(16,185,129,.08);color:var(--emerald-l)">
                        <svg fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6A19.79 19.79 0 0 1 3.09 4.18 2 2 0 0 1 5.06 2h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L9.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                    </div>
                    <div>
                        <div class="field-label">No. HP / WhatsApp</div>
                        <?php if(!empty($data['no_hp'])): ?>
                        <div class="field-value">
                            <?= val($data['no_hp']) ?>
                            <a href="https://wa.me/<?= preg_replace('/[^0-9]/','',$data['no_hp']) ?>" target="_blank" style="margin-left:8px;font-size:11.5px;background:rgba(37,211,102,.12);border:1px solid rgba(37,211,102,.25);color:#86EFAC;padding:2px 8px;border-radius:20px;text-decoration:none;">WA</a>
                        </div>
                        <?php else: ?><div class="field-value empty">Tidak ditemukan</div><?php endif; ?>
                    </div>
                </div>

                <!-- Sosial Media -->
                <div class="field-row">
                    <div class="field-icon" style="background:rgba(16,185,129,.08);color:var(--emerald-l)">
                        <svg fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15 15 0 0 1 4 10 15 15 0 0 1-4 10 15 15 0 0 1-4-10 15 15 0 0 1 4-10z"/></svg>
                    </div>
                    <div style="flex:1">
                        <div class="field-label">Sosial Media</div>
                        <div class="social-row" style="margin-top:6px">
                            <?php if(!empty($data['sosmed_linkedin'])): ?>
                            <a href="<?= val($data['sosmed_linkedin']) ?>" target="_blank" class="social-link sl-li">
                                <svg width="13" height="13" fill="currentColor" viewBox="0 0 24 24"><path d="M16 8a6 6 0 0 1 6 6v7h-4v-7a2 2 0 0 0-2-2 2 2 0 0 0-2 2v7h-4v-7a6 6 0 0 1 6-6z"/><rect x="2" y="9" width="4" height="12"/><circle cx="4" cy="4" r="2"/></svg>
                                LinkedIn
                            </a>
                            <?php endif; ?>
                            <?php if(!empty($data['sosmed_ig'])): ?>
                            <a href="<?= val($data['sosmed_ig']) ?>" target="_blank" class="social-link sl-ig">
                                <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="2" y="2" width="20" height="20" rx="5" ry="5"/><path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"/><line x1="17.5" y1="6.5" x2="17.51" y2="6.5"/></svg>
                                Instagram
                            </a>
                            <?php endif; ?>
                            <?php if(!empty($data['sosmed_fb'])): ?>
                            <a href="<?= val($data['sosmed_fb']) ?>" target="_blank" class="social-link sl-fb">
                                <svg width="13" height="13" fill="currentColor" viewBox="0 0 24 24"><path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"/></svg>
                                Facebook
                            </a>
                            <?php endif; ?>
                            <?php if(!empty($data['sosmed_tiktok'])): ?>
                            <a href="<?= val($data['sosmed_tiktok']) ?>" target="_blank" class="social-link sl-tk">
                                <svg width="13" height="13" fill="currentColor" viewBox="0 0 24 24"><path d="M19.59 6.69a4.83 4.83 0 0 1-3.77-4.25V2h-3.45v13.67a2.89 2.89 0 0 1-2.88 2.5 2.89 2.89 0 0 1-2.89-2.89 2.89 2.89 0 0 1 2.89-2.89c.28 0 .54.04.79.1V9.01a6.33 6.33 0 0 0-.79-.05 6.34 6.34 0 0 0-6.34 6.34 6.34 6.34 0 0 0 6.34 6.34 6.34 6.34 0 0 0 6.33-6.34V8.69a8.18 8.18 0 0 0 4.78 1.52V6.73a4.85 4.85 0 0 1-1.01-.04z"/></svg>
                                TikTok
                            </a>
                            <?php endif; ?>
                            <?php if(empty($data['sosmed_linkedin']) && empty($data['sosmed_ig']) && empty($data['sosmed_fb']) && empty($data['sosmed_tiktok'])): ?>
                            <span class="field-value empty">Belum ditemukan</span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

            </div>
            <?php else: ?>
            <div style="padding:24px 20px;color:var(--muted);font-size:13px;font-style:italic">
                Data kontak belum tersedia. Jalankan profiling terlebih dahulu.
            </div>
            <?php endif; ?>
        </div>

        <!-- Data Karir / Pekerjaan -->
        <div class="section-card <?= !$hasTracking ? 'span2' : '' ?>">
            <div class="section-header">
                <div class="section-icon amber">
                    <svg fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg>
                </div>
                <h4>Karir &amp; Pekerjaan</h4>
            </div>

            <?php if ($hasTracking): ?>
            <div class="field-list">

                <!-- Jenis Instansi -->
                <div class="field-row">
                    <div class="field-icon" style="background:rgba(245,158,11,.08);color:var(--amber-l)">
                        <svg fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><polygon points="12 2 2 7 12 12 22 7 12 2"/><polyline points="2 17 12 22 22 17"/><polyline points="2 12 12 17 22 12"/></svg>
                    </div>
                    <div>
                        <div class="field-label">Sektor / Jenis Instansi</div>
                        <?php if(!empty($data['jenis_instansi']) && $data['jenis_instansi'] !== 'Lainnya'): ?>
                        <div class="field-value">
                            <span class="ji-badge ji-<?= $jiLabel[1] ?>">
                                <?= htmlspecialchars($data['jenis_instansi']) ?>
                            </span>
                        </div>
                        <?php else: ?><div class="field-value empty">Tidak diketahui</div><?php endif; ?>
                    </div>
                </div>

                <!-- Tempat Kerja -->
                <div class="field-row">
                    <div class="field-icon" style="background:rgba(245,158,11,.08);color:var(--amber-l)">
                        <svg fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg>
                    </div>
                    <div>
                        <div class="field-label">Tempat Kerja</div>
                        <div class="field-value <?= empty($data['tempat_kerja']) ? 'empty' : '' ?>"><?= !empty($data['tempat_kerja']) ? val($data['tempat_kerja']) : 'Tidak ditemukan' ?></div>
                    </div>
                </div>

                <!-- Posisi -->
                <div class="field-row">
                    <div class="field-icon" style="background:rgba(245,158,11,.08);color:var(--amber-l)">
                        <svg fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><circle cx="12" cy="8" r="6"/><path d="M15.477 12.89L17 22l-5-3-5 3 1.523-9.11"/></svg>
                    </div>
                    <div>
                        <div class="field-label">Posisi / Jabatan</div>
                        <div class="field-value <?= empty($data['posisi']) ? 'empty' : '' ?>"><?= !empty($data['posisi']) ? val($data['posisi']) : 'Tidak ditemukan' ?></div>
                    </div>
                </div>

                <!-- Alamat Kerja -->
                <div class="field-row">
                    <div class="field-icon" style="background:rgba(245,158,11,.08);color:var(--amber-l)">
                        <svg fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                    </div>
                    <div>
                        <div class="field-label">Alamat Kerja</div>
                        <div class="field-value <?= empty($data['alamat_kerja']) ? 'empty' : '' ?>"><?= !empty($data['alamat_kerja']) ? val($data['alamat_kerja']) : 'Tidak ditemukan' ?></div>
                    </div>
                </div>

                <!-- Sosmed Kantor -->
                <?php if(!empty($data['sosmed_kantor'])): ?>
                <div class="field-row">
                    <div class="field-icon" style="background:rgba(245,158,11,.08);color:var(--amber-l)">
                        <svg fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"/><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"/></svg>
                    </div>
                    <div>
                        <div class="field-label">Sosmed Kantor</div>
                        <div class="field-value"><a href="<?= val($data['sosmed_kantor']) ?>" target="_blank" class="social-link sl-web" style="display:inline-flex">🔗 <?= val($data['sosmed_kantor']) ?></a></div>
                    </div>
                </div>
                <?php endif; ?>

            </div>
            <?php else: ?>
            <div class="empty-tracking">
                <div class="et-icon">
                    <svg fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg>
                </div>
                <h4>Data karir belum tersedia</h4>
                <p>Jalankan profiling untuk melacak data pekerjaan alumni ini.</p>
                <a href="profiling.php" class="btn-action btn-primary" style="display:inline-flex;margin:0 auto">
                    <svg fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" viewBox="0 0 24 24" width="14" height="14"><polygon points="5 3 19 12 5 21 5 3"/></svg>
                    Jalankan Profiling
                </a>
            </div>
            <?php endif; ?>
        </div>

        <!-- Data PDDIKTI (kalau ada) -->
        <?php if($hasTracking && (!empty($data['nama_pt_pddikti']) || !empty($data['prodi_pt_pddikti']))): ?>
        <div class="section-card">
            <div class="section-header">
                <div class="section-icon violet">
                    <svg fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M22 10v6M2 10l10-5 10 5-10 5z"/><path d="M6 12v5c3 3 9 3 12 0v-5"/></svg>
                </div>
                <h4>Verifikasi PDDIKTI</h4>
            </div>
            <div class="field-list">
                <div class="field-row">
                    <div class="field-icon" style="background:rgba(139,92,246,.08);color:var(--violet-l)">
                        <svg fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" viewBox="0 0 24 24"><rect x="4" y="2" width="16" height="20" rx="1"/></svg>
                    </div>
                    <div>
                        <div class="field-label">Perguruan Tinggi</div>
                        <div class="field-value <?= empty($data['nama_pt_pddikti']) ? 'empty' : '' ?>"><?= !empty($data['nama_pt_pddikti']) ? val($data['nama_pt_pddikti']) : '—' ?></div>
                    </div>
                </div>
                <div class="field-row">
                    <div class="field-icon" style="background:rgba(139,92,246,.08);color:var(--violet-l)">
                        <svg fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" viewBox="0 0 24 24"><circle cx="12" cy="8" r="6"/></svg>
                    </div>
                    <div>
                        <div class="field-label">Prodi (PDDIKTI)</div>
                        <div class="field-value <?= empty($data['prodi_pt_pddikti']) ? 'empty' : '' ?>"><?= !empty($data['prodi_pt_pddikti']) ? val($data['prodi_pt_pddikti']) : '—' ?></div>
                    </div>
                </div>
                <div class="field-row">
                    <div class="field-icon" style="background:rgba(139,92,246,.08);color:var(--violet-l)">
                        <svg fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" viewBox="0 0 24 24"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                    </div>
                    <div>
                        <div class="field-label">Status Mahasiswa</div>
                        <div class="field-value <?= empty($data['status_mhs']) ? 'empty' : '' ?>"><?= !empty($data['status_mhs']) ? val($data['status_mhs']) : '—' ?></div>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Sumber Data -->
        <?php if($hasTracking && !empty($sumber)): ?>
        <div class="section-card">
            <div class="section-header">
                <div class="section-icon emerald">
                    <svg fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15 15 0 0 1 4 10 15 15 0 0 1-4 10 15 15 0 0 1-4-10 15 15 0 0 1 4-10z"/></svg>
                </div>
                <h4>Sumber Data Ditemukan</h4>
            </div>
            <div class="sumber-row">
                <?php foreach($sumber as $src): ?>
                <span class="sumber-chip">
                    <svg fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" viewBox="0 0 24 24" width="11" height="11"><polyline points="20 6 9 17 4 12"/></svg>
                    <?= htmlspecialchars($src) ?>
                </span>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

    </div><!-- /detail-grid -->

</div><!-- /dash-body -->

<footer class="dash-footer">
    <span><span class="status-dot"></span>Sistem berjalan normal</span>
    <span>&copy; 2026 BandhuNet &mdash; Alumni Tracking &amp; Social Profiling</span>
</footer>

</div>
</div>
<?php include 'layout/footer.php'; ?>
</body>
</html>