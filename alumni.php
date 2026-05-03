<?php 
session_start();
if (!isset($_SESSION['id_user'])) { header("Location: login.php"); exit; }
include 'koneksi.php';
include 'layout/header.php';

// ─── LOGIKA PAGINATION & FILTER SERVER-SIDE ───────────────────────────────
$limit  = 50;
$page   = isset($_GET['halaman']) ? max(1, (int)$_GET['halaman']) : 1;
$offset = ($page - 1) * $limit;

// Ambil nilai filter dari GET
$search          = isset($_GET['q'])        ? trim($_GET['q'])        : '';
$filterStatus    = isset($_GET['status'])   ? trim($_GET['status'])   : '';
$filterYear      = isset($_GET['tahun'])    ? trim($_GET['tahun'])    : '';
$filterFakultas  = isset($_GET['fakultas']) ? trim($_GET['fakultas']) : '';

// Bangun klausa WHERE secara dinamis (prepared statement)
$where_parts = [];
$bind_types  = '';
$bind_values = [];

if ($search !== '') {
    $where_parts[] = "(alumni.nim LIKE ? OR alumni.nama_lengkap LIKE ?)";
    $bind_types   .= 'ss';
    $like           = '%' . $search . '%';
    $bind_values[]  = $like;
    $bind_values[]  = $like;
}
if ($filterStatus !== '') {
    $where_parts[] = "alumni.status_awal = ?";
    $bind_types   .= 's';
    $bind_values[]  = $filterStatus;
}
if ($filterFakultas !== '') {
    $where_parts[] = "alumni.fakultas = ?";
    $bind_types   .= 's';
    $bind_values[]  = $filterFakultas;
}
if ($filterYear !== '') {
    $where_parts[] = "YEAR(alumni.tanggal_lulus) = ?";
    $bind_types   .= 'i';
    $bind_values[]  = (int)$filterYear;
}

$where_sql = count($where_parts) ? 'WHERE ' . implode(' AND ', $where_parts) : '';

// ── Hitung total data (untuk pagination) ──────────────────────────────────
$count_sql  = "SELECT COUNT(*) AS jml 
               FROM alumni 
               LEFT JOIN tracking ON alumni.id_alumni = tracking.id_alumni 
               $where_sql";
$count_stmt = mysqli_prepare($koneksi, $count_sql);
if ($bind_types && $bind_values) {
    mysqli_stmt_bind_param($count_stmt, $bind_types, ...$bind_values);
}
mysqli_stmt_execute($count_stmt);
$count_res  = mysqli_stmt_get_result($count_stmt);
$total_data = (int)mysqli_fetch_assoc($count_res)['jml'];
$total_pages = max(1, (int)ceil($total_data / $limit));
if ($page > $total_pages) $page = $total_pages;
$offset = ($page - 1) * $limit;

// ── Query utama dengan LIMIT & OFFSET ─────────────────────────────────────
$main_sql  = "SELECT alumni.id_alumni,
                     alumni.nim,
                     alumni.nama_lengkap,
                     alumni.tahun_masuk,
                     alumni.tanggal_lulus,
                     alumni.fakultas,
                     alumni.prodi,
                     alumni.status_awal,
                     YEAR(alumni.tanggal_lulus) AS thn_lulus_display,
                     tracking.tempat_kerja
              FROM alumni
              LEFT JOIN tracking ON alumni.id_alumni = tracking.id_alumni
              $where_sql
              ORDER BY alumni.tanggal_lulus DESC, alumni.id_alumni DESC
              LIMIT ? OFFSET ?";
$main_stmt = mysqli_prepare($koneksi, $main_sql);
$all_types  = $bind_types . 'ii';
$all_values = array_merge($bind_values, [$limit, $offset]);
mysqli_stmt_bind_param($main_stmt, $all_types, ...$all_values);
mysqli_stmt_execute($main_stmt);
$res  = mysqli_stmt_get_result($main_stmt);
if (!$res) { die("Query Error: " . mysqli_error($koneksi)); }
$rows = [];
while ($row = mysqli_fetch_assoc($res)) $rows[] = $row;

// ── Ambil daftar tahun lulus unik untuk dropdown filter ───────────────────
$year_res  = mysqli_query($koneksi, "SELECT DISTINCT YEAR(tanggal_lulus) AS thn FROM alumni WHERE tanggal_lulus IS NOT NULL ORDER BY thn DESC");
$year_list = [];
while ($yr = mysqli_fetch_assoc($year_res)) $year_list[] = $yr['thn'];

// ── Ambil daftar fakultas unik untuk dropdown filter ─────────────────────
$fak_res  = mysqli_query($koneksi, "SELECT DISTINCT fakultas FROM alumni WHERE fakultas IS NOT NULL AND fakultas != '' ORDER BY fakultas ASC");
$fak_list = [];
while ($fk = mysqli_fetch_assoc($fak_res)) $fak_list[] = $fk['fakultas'];

// Helper: bangun URL pagination dengan mempertahankan filter aktif
function pageUrl($p) {
    $params = $_GET;
    $params['halaman'] = $p;
    return '?' . http_build_query($params);
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Daftar Alumni — BandhuNet</title>
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
}

body {
    font-family: 'Plus Jakarta Sans', sans-serif;
    background: var(--bg);
    color: var(--text);
    min-height: 100vh;
    display: flex;
}

.main-layout { display: flex; width: 100%; }
.page-content { flex: 1; min-width: 0; display: flex; flex-direction: column; }

/* ── Topbar ── */
.topbar {
    display: flex; align-items: center; justify-content: space-between;
    padding: 0 28px; height: 60px;
    background: var(--surface); border-bottom: 1px solid var(--border);
    flex-shrink: 0;
}
.topbar-left h2 { font-size: 15px; font-weight: 600; letter-spacing: -0.2px; }
.topbar-left p  { font-size: 12px; color: var(--muted); margin-top: 1px; }
.topbar-right   { display: flex; align-items: center; gap: 12px; }
.welcome-chip {
    display: flex; align-items: center; gap: 8px;
    background: var(--subtle); border: 1px solid var(--border);
    border-radius: 20px; padding: 6px 12px 6px 6px;
    font-size: 12.5px; color: var(--muted);
}
.welcome-chip strong { color: var(--text); font-weight: 500; }
.avatar {
    width: 26px; height: 26px; border-radius: 50%;
    background: linear-gradient(135deg, var(--indigo), #818CF8);
    display: flex; align-items: center; justify-content: center;
    font-size: 11px; font-weight: 600; color: #fff;
}
.logout-chip {
    display: flex; align-items: center; gap: 6px;
    background: rgba(244,63,94,0.08); border: 1px solid rgba(244,63,94,0.2);
    border-radius: 8px; padding: 6px 12px;
    font-size: 12px; font-weight: 500; color: #FB7185;
    text-decoration: none; transition: background 0.15s;
}
.logout-chip:hover { background: rgba(244,63,94,0.15); }
.logout-chip svg { width: 13px; height: 13px; }

/* ── Body ── */
.dash-body { flex: 1; padding: 28px; overflow-y: auto; }

@keyframes fadeUp {
    from { opacity: 0; transform: translateY(10px); }
    to   { opacity: 1; transform: translateY(0); }
}

/* ── Page header row ── */
.page-header-row {
    display: flex; align-items: flex-end; justify-content: space-between;
    margin-bottom: 20px; gap: 16px; flex-wrap: wrap;
    animation: fadeUp 0.35s ease both;
}
.page-header-row h3 { font-size: 20px; font-weight: 700; letter-spacing: -0.4px; }
.page-header-row p  { font-size: 13px; color: var(--muted); margin-top: 3px; }

.btn-add {
    display: inline-flex; align-items: center; gap: 7px;
    background: var(--indigo); color: #fff; border: none;
    border-radius: 9px; padding: 9px 16px;
    font-family: 'Plus Jakarta Sans', sans-serif;
    font-size: 13px; font-weight: 600; cursor: pointer;
    text-decoration: none; white-space: nowrap;
    transition: background 0.15s, transform 0.15s;
}
.btn-add:hover  { background: #5254CC; }
.btn-add:active { transform: scale(0.97); }
.btn-add svg { width: 15px; height: 15px; }

/* ── Toolbar ── */
.toolbar {
    display: flex; align-items: center; gap: 10px;
    margin-bottom: 16px; flex-wrap: wrap;
    animation: fadeUp 0.35s ease 0.05s both;
}
.toolbar form { display: flex; align-items: center; gap: 10px; flex-wrap: wrap; width: 100%; }

.search-wrap {
    position: relative; flex: 1; min-width: 200px; max-width: 340px;
}
.search-wrap svg {
    position: absolute; left: 12px; top: 50%; transform: translateY(-50%);
    width: 15px; height: 15px;
    stroke: var(--muted); fill: none; pointer-events: none;
    stroke-width: 2; stroke-linecap: round; stroke-linejoin: round;
}
.search-wrap input {
    width: 100%; height: 36px;
    background: var(--surface); border: 1px solid var(--border2);
    border-radius: 8px; padding: 0 12px 0 36px;
    font-family: 'Plus Jakarta Sans', sans-serif;
    font-size: 13px; color: var(--text); outline: none;
    transition: border-color 0.15s;
}
.search-wrap input::placeholder { color: var(--muted); }
.search-wrap input:focus { border-color: var(--indigo); }

.filter-select {
    height: 36px;
    background: var(--surface); border: 1px solid var(--border2);
    border-radius: 8px; padding: 0 32px 0 12px;
    font-family: 'Plus Jakarta Sans', sans-serif;
    font-size: 13px; color: var(--text); outline: none; cursor: pointer;
    appearance: none;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%2364748B' stroke-width='2' stroke-linecap='round'%3E%3Cpolyline points='6 9 12 15 18 9'/%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: right 10px center;
    transition: border-color 0.15s;
}
.filter-select:focus { border-color: var(--indigo); }
.filter-select option { background: #1E2433; }

.btn-search {
    height: 36px; padding: 0 16px;
    background: var(--indigo); border: none; border-radius: 8px;
    font-family: 'Plus Jakarta Sans', sans-serif;
    font-size: 13px; font-weight: 600; color: #fff;
    cursor: pointer; transition: background 0.15s;
    white-space: nowrap;
}
.btn-search:hover { background: #5254CC; }

.btn-reset {
    height: 36px; padding: 0 14px;
    background: transparent; border: 1px solid var(--border2); border-radius: 8px;
    font-family: 'Plus Jakarta Sans', sans-serif;
    font-size: 13px; color: var(--muted);
    cursor: pointer; transition: border-color 0.15s, color 0.15s;
    text-decoration: none; display: inline-flex; align-items: center;
    white-space: nowrap;
}
.btn-reset:hover { border-color: var(--rose); color: var(--rose-l); }

.count-chip {
    margin-left: auto;
    font-size: 12px; color: var(--muted);
    background: var(--subtle); border: 1px solid var(--border);
    border-radius: 20px; padding: 4px 12px;
    white-space: nowrap;
}
.count-chip strong { color: var(--text); font-weight: 600; }

/* ── Table card ── */
.table-card {
    background: var(--surface); border: 1px solid var(--border);
    border-radius: 14px; overflow: hidden;
    animation: fadeUp 0.35s ease 0.1s both;
}

.data-table { width: 100%; border-collapse: collapse; }

.data-table thead tr { border-bottom: 1px solid var(--border); }
.data-table thead th {
    padding: 12px 16px;
    font-size: 11px; font-weight: 600;
    text-transform: uppercase; letter-spacing: 0.8px;
    color: var(--muted); text-align: left; white-space: nowrap;
    background: rgba(255,255,255,0.02);
}
.data-table thead th:first-child { padding-left: 20px; }
.data-table thead th:last-child  { padding-right: 20px; text-align: right; }

.data-table tbody tr {
    border-bottom: 1px solid var(--border);
    transition: background 0.12s;
}
.data-table tbody tr:last-child { border-bottom: none; }
.data-table tbody tr:hover { background: rgba(255,255,255,0.03); }

.data-table tbody td {
    padding: 13px 16px;
    font-size: 13.5px; color: var(--text);
    vertical-align: middle;
}
.data-table tbody td:first-child { padding-left: 20px; }
.data-table tbody td:last-child  { padding-right: 20px; text-align: right; }

/* NIM cell */
.nim-cell { font-family: 'Courier New', monospace; font-size: 12.5px; color: var(--muted); letter-spacing: 0.5px; }

/* Name cell */
.name-cell { display: flex; align-items: center; gap: 10px; }
.name-avatar {
    width: 30px; height: 30px; border-radius: 8px; flex-shrink: 0;
    display: flex; align-items: center; justify-content: center;
    font-size: 12px; font-weight: 600;
}
.name-text { font-weight: 500; font-size: 13.5px; }

/* Year badge */
.year-badge {
    display: inline-flex; align-items: center; gap: 4px;
    font-size: 12px; font-weight: 500;
    padding: 3px 10px; border-radius: 20px;
    background: rgba(255,255,255,0.05); border: 1px solid var(--border2);
    color: var(--muted);
}

/* Status badges */
.status-badge {
    display: inline-flex; align-items: center; gap: 5px;
    font-size: 11.5px; font-weight: 500;
    padding: 4px 10px; border-radius: 20px; border: 1px solid;
    white-space: nowrap;
}
.status-badge .dot { width: 6px; height: 6px; border-radius: 50%; flex-shrink: 0; }
.status-found      { background: rgba(16,185,129,0.1);  border-color: rgba(16,185,129,0.25); color: var(--emerald-l); }
.status-found .dot      { background: var(--emerald); box-shadow: 0 0 5px rgba(16,185,129,0.6); }
.status-not-found  { background: rgba(244,63,94,0.08);  border-color: rgba(244,63,94,0.22);  color: var(--rose-l); }
.status-not-found .dot  { background: var(--rose); }
.status-pending    { background: rgba(245,158,11,0.08); border-color: rgba(245,158,11,0.22); color: var(--amber-l); }
.status-pending .dot    { background: var(--amber); }
.status-default    { background: rgba(255,255,255,0.04); border-color: var(--border2); color: var(--muted); }
.status-default .dot    { background: var(--muted); }

/* Detail button */
.btn-detail {
    display: inline-flex; align-items: center; gap: 6px;
    background: rgba(99,102,241,0.1); border: 1px solid rgba(99,102,241,0.25);
    border-radius: 7px; padding: 6px 12px;
    font-family: 'Plus Jakarta Sans', sans-serif;
    font-size: 12px; font-weight: 500; color: var(--indigo-l);
    text-decoration: none; transition: background 0.15s, border-color 0.15s;
    white-space: nowrap;
}
.btn-detail:hover { background: rgba(99,102,241,0.18); border-color: rgba(99,102,241,0.4); }
.btn-detail svg { width: 13px; height: 13px; }

/* Empty state */
.empty-state { text-align: center; padding: 64px 24px; }
.empty-icon {
    width: 52px; height: 52px; border-radius: 14px;
    background: rgba(255,255,255,0.04); border: 1px solid var(--border);
    display: flex; align-items: center; justify-content: center;
    margin: 0 auto 16px; color: var(--muted);
}
.empty-icon svg { width: 22px; height: 22px; }
.empty-state h4 { font-size: 15px; font-weight: 600; margin-bottom: 6px; }
.empty-state p  { font-size: 13px; color: var(--muted); }

/* ── Pagination ── */
.pagination-wrap {
    display: flex; align-items: center; justify-content: space-between;
    padding: 16px 20px;
    border-top: 1px solid var(--border);
    flex-wrap: wrap; gap: 12px;
}
.pagination-info { font-size: 12.5px; color: var(--muted); }
.pagination-info strong { color: var(--text); }
.pagination-nav { display: flex; align-items: center; gap: 6px; flex-wrap: wrap; }

.pg-btn {
    display: inline-flex; align-items: center; justify-content: center;
    min-width: 32px; height: 32px; padding: 0 10px;
    background: var(--surface2); border: 1px solid var(--border2);
    border-radius: 7px; font-family: 'Plus Jakarta Sans', sans-serif;
    font-size: 12.5px; font-weight: 500; color: var(--muted);
    text-decoration: none; transition: all 0.15s; white-space: nowrap;
}
.pg-btn:hover   { border-color: var(--indigo); color: var(--indigo-l); background: rgba(99,102,241,0.08); }
.pg-btn.active  { background: var(--indigo); border-color: var(--indigo); color: #fff; font-weight: 600; pointer-events: none; }
.pg-btn.disabled{ opacity: 0.35; pointer-events: none; }
.pg-ellipsis { font-size: 13px; color: var(--muted); padding: 0 4px; }

/* ── Filter active banner ── */
.filter-banner {
    display: flex; align-items: center; gap: 8px;
    background: rgba(99,102,241,0.07); border: 1px solid rgba(99,102,241,0.2);
    border-radius: 9px; padding: 9px 14px; margin-bottom: 12px;
    font-size: 12.5px; color: var(--indigo-l);
    animation: fadeUp 0.3s ease both;
}
.filter-banner svg { width: 14px; height: 14px; flex-shrink: 0; }
.filter-banner a {
    margin-left: auto; font-size: 12px; font-weight: 500;
    color: var(--rose-l); text-decoration: none;
}
.filter-banner a:hover { text-decoration: underline; }

/* Footer */
.dash-footer {
    padding: 16px 28px; border-top: 1px solid var(--border);
    font-size: 11.5px; color: var(--muted);
    display: flex; align-items: center; justify-content: space-between;
}
.status-dot {
    display: inline-block; width: 7px; height: 7px;
    background: var(--emerald); border-radius: 50%; margin-right: 6px;
    box-shadow: 0 0 6px rgba(16,185,129,0.6);
    animation: pulse 2s infinite;
}
@keyframes pulse { 0%,100%{opacity:1} 50%{opacity:0.4} }
</style>
</head>
<body>
<div class="main-layout">

    <?php include 'layout/sidebar.php'; ?>

    <div class="page-content">

        <!-- Topbar -->
        <header class="topbar">
            <div class="topbar-left">
                <h2>Daftar Alumni</h2>
                <p>Manajemen data dan status tracking alumni</p>
            </div>
            <div class="topbar-right">
                <div class="welcome-chip">
                    <div class="avatar"><?= strtoupper(substr($_SESSION['nama'], 0, 1)) ?></div>
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

            <!-- Page heading -->
            <div class="page-header-row">
                <div>
                    <h3>Alumni Terdaftar</h3>
                    <p>Total <strong><?= number_format($total_data) ?></strong> alumni dalam sistem</p>
                </div>
                <a href="upload_excel.php" class="btn-add">
                    <svg fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                        <line x1="12" y1="5" x2="12" y2="19"/>
                        <line x1="5" y1="12" x2="19" y2="12"/>
                    </svg>
                    Import Alumni
                </a>
            </div>

            <!-- ── Filter banner (tampil kalau ada filter aktif) ── -->
            <?php if ($search !== '' || $filterStatus !== '' || $filterYear !== '' || $filterFakultas !== ''): ?>
            <div class="filter-banner">
                <svg fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                    <polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/>
                </svg>
                <span>
                    Filter aktif:
                    <?php if ($search !== ''): ?> <strong>Pencarian "<?= htmlspecialchars($search) ?>"</strong><?php endif; ?>
                    <?php if ($filterStatus !== ''): ?> &bull; <strong>Status: <?= htmlspecialchars($filterStatus) ?></strong><?php endif; ?>
                    <?php if ($filterYear !== ''): ?> &bull; <strong>Lulus: <?= htmlspecialchars($filterYear) ?></strong><?php endif; ?>
                    <?php if ($filterFakultas !== ''): ?> &bull; <strong>Fakultas: <?= htmlspecialchars($filterFakultas) ?></strong><?php endif; ?>
                    &mdash; Ditemukan <strong><?= number_format($total_data) ?></strong> data
                </span>
                <a href="alumni.php">✕ Reset</a>
            </div>
            <?php endif; ?>

            <!-- ── Toolbar (form GET) ── -->
            <div class="toolbar">
                <form method="GET" action="alumni.php">
                    <div class="search-wrap">
                        <svg viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                        <input type="text" name="q" id="searchInput"
                               placeholder="Cari NIM, nama, atau prodi..."
                               value="<?= htmlspecialchars($search) ?>">
                    </div>

                    <select class="filter-select" name="status">
                        <option value="">Semua Status</option>
                        <?php foreach (['Bekerja','Wirausaha','Melanjutkan Studi','Belum Bekerja','Tidak Diketahui'] as $s): ?>
                        <option value="<?= $s ?>" <?= $filterStatus === $s ? 'selected' : '' ?>><?= $s ?></option>
                        <?php endforeach; ?>
                    </select>

                    <select class="filter-select" name="tahun">
                        <option value="">Semua Tahun Lulus</option>
                        <?php foreach ($year_list as $y): ?>
                        <option value="<?= $y ?>" <?= $filterYear == $y ? 'selected' : '' ?>><?= $y ?></option>
                        <?php endforeach; ?>
                    </select>

                    <select class="filter-select" name="fakultas">
                        <option value="">Semua Fakultas</option>
                        <?php foreach ($fak_list as $fk): ?>
                        <option value="<?= htmlspecialchars($fk) ?>" <?= $filterFakultas === $fk ? 'selected' : '' ?>><?= htmlspecialchars($fk) ?></option>
                        <?php endforeach; ?>
                    </select>

                    <button type="submit" class="btn-search">
                        <svg style="width:13px;height:13px;vertical-align:-2px;margin-right:5px" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                        Cari
                    </button>

                    <?php if ($search !== '' || $filterStatus !== '' || $filterYear !== '' || $filterFakultas !== ''): ?>
                    <a href="alumni.php" class="btn-reset">Reset</a>
                    <?php endif; ?>

                    <div class="count-chip">
                        Halaman <strong><?= $page ?></strong> / <strong><?= $total_pages ?></strong>
                        &nbsp;&middot;&nbsp;
                        <?= number_format(($offset + 1)) ?>–<?= number_format(min($offset + $limit, $total_data)) ?> dari <strong><?= number_format($total_data) ?></strong>
                    </div>
                </form>
            </div>

            <!-- ── Table ── -->
            <?php if ($total_data > 0 && count($rows) > 0): ?>
            <div class="table-card">
                <table class="data-table" id="alumniTable">
                    <thead>
                        <tr>
                            <th>NIM</th>
                            <th>Nama Alumni</th>
                            <th>Tahun Masuk</th>
                            <th>Tanggal Lulus</th>
                            <th>Fakultas / Prodi</th>
                            <th>Status Awal</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $avatar_colors = [
                            ['rgba(99,102,241,0.15)',  '#A5B4FC'],
                            ['rgba(16,185,129,0.15)',  '#6EE7B7'],
                            ['rgba(245,158,11,0.15)',  '#FDE68A'],
                            ['rgba(244,63,94,0.12)',   '#FDA4AF'],
                            ['rgba(139,92,246,0.15)',  '#C4B5FD'],
                        ];
                        foreach ($rows as $i => $row):
                            // Status awal dari kolom alumni.status_awal
                            $status    = trim($row['status_awal'] ?? '');
                            $status_lc = strtolower($status);
                            if      (str_contains($status_lc, 'bekerja') && !str_contains($status_lc, 'belum')) $sc = 'status-found';
                            elseif  (str_contains($status_lc, 'wirausaha'))    $sc = 'status-found';
                            elseif  (str_contains($status_lc, 'melanjutkan'))  $sc = 'status-pending';
                            elseif  (str_contains($status_lc, 'belum'))        $sc = 'status-not-found';
                            elseif  (str_contains($status_lc, 'tidak'))        $sc = 'status-not-found';
                            else                                                $sc = 'status-default';
                            $label    = $status ?: '—';
                            $color    = $avatar_colors[$i % count($avatar_colors)];
                            $initials = strtoupper(substr($row['nama_lengkap'], 0, 1));
                            // Format tanggal lulus
                            $tgl_lulus = $row['tanggal_lulus'] ?? '';
                            $tgl_fmt = '—';
                            if (!empty($tgl_lulus) && $tgl_lulus !== '0000-00-00' && $tgl_lulus !== '0000-00-00 00:00:00') {
                                $ts = strtotime($tgl_lulus);
                                if ($ts && $ts > 0) {
                                    $bulan_id = ['','Jan','Feb','Mar','Apr','Mei','Jun','Jul','Ags','Sep','Okt','Nov','Des'];
                                    $tgl_fmt = date('d', $ts) . ' ' . $bulan_id[(int)date('n', $ts)] . ' ' . date('Y', $ts);
                                } else {
                                    // Coba parse manual kalau strtotime gagal (misal format d/m/Y)
                                    $tgl_fmt = htmlspecialchars($tgl_lulus);
                                }
                            }
                        ?>
                        <tr>
                            <td class="nim-cell"><?= htmlspecialchars($row['nim']) ?></td>
                            <td>
                                <div class="name-cell">
                                    <div class="name-avatar" style="background:<?= $color[0] ?>;color:<?= $color[1] ?>"><?= $initials ?></div>
                                    <span class="name-text"><?= htmlspecialchars($row['nama_lengkap']) ?></span>
                                </div>
                            </td>
                            <td>
                                <span class="year-badge"><?= htmlspecialchars($row['tahun_masuk'] ?? '—') ?></span>
                            </td>
                            <td style="font-size:12.5px;color:var(--muted)">
                                <?= $tgl_fmt ?>
                                <?php if ($tgl_fmt === '—' && !empty($row['tanggal_lulus'])): ?>
                                <div style="font-size:10px;color:var(--amber);margin-top:2px">[<?= htmlspecialchars($row['tanggal_lulus']) ?>]</div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div style="font-size:13px;font-weight:500;line-height:1.3"><?= htmlspecialchars($row['fakultas'] ?? '—') ?></div>
                                <div style="font-size:11.5px;color:var(--muted);margin-top:2px"><?= htmlspecialchars($row['prodi'] ?? '—') ?></div>
                            </td>
                            <td>
                                <span class="status-badge <?= $sc ?>">
                                    <span class="dot"></span>
                                    <?= htmlspecialchars($label) ?>
                                </span>
                            </td>
                            <td>
                                <a href="alumni_detail.php?id=<?= $row['id_alumni'] ?>" class="btn-detail">
                                    <svg fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                                        <circle cx="11" cy="11" r="8"/>
                                        <line x1="21" y1="21" x2="16.65" y2="16.65"/>
                                    </svg>
                                    Detail
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <!-- ── Pagination ── -->
                <?php if ($total_pages > 1): ?>
                <div class="pagination-wrap">
                    <div class="pagination-info">
                        Menampilkan <strong><?= number_format($offset + 1) ?>–<?= number_format(min($offset + $limit, $total_data)) ?></strong>
                        dari <strong><?= number_format($total_data) ?></strong> alumni
                    </div>

                    <nav class="pagination-nav" aria-label="Navigasi halaman">
                        <!-- Tombol Pertama & Sebelumnya -->
                        <a href="<?= pageUrl(1) ?>" class="pg-btn <?= $page <= 1 ? 'disabled' : '' ?>" title="Halaman pertama">«</a>
                        <a href="<?= pageUrl($page - 1) ?>" class="pg-btn <?= $page <= 1 ? 'disabled' : '' ?>" title="Sebelumnya">‹</a>

                        <?php
                        // Tampilkan nomor halaman: selalu tampil 1, 2 halaman di sekitar current, dan halaman terakhir
                        $show = [];
                        $show[] = 1;
                        for ($p = max(2, $page - 2); $p <= min($total_pages - 1, $page + 2); $p++) $show[] = $p;
                        if ($total_pages > 1) $show[] = $total_pages;
                        $show = array_unique($show);
                        sort($show);
                        $prev_p = null;
                        foreach ($show as $p_num):
                            if ($prev_p !== null && $p_num - $prev_p > 1):
                        ?>
                            <span class="pg-ellipsis">…</span>
                        <?php
                            endif;
                        ?>
                        <a href="<?= pageUrl($p_num) ?>"
                           class="pg-btn <?= $p_num === $page ? 'active' : '' ?>"><?= $p_num ?></a>
                        <?php
                            $prev_p = $p_num;
                        endforeach;
                        ?>

                        <!-- Tombol Berikutnya & Terakhir -->
                        <a href="<?= pageUrl($page + 1) ?>" class="pg-btn <?= $page >= $total_pages ? 'disabled' : '' ?>" title="Berikutnya">›</a>
                        <a href="<?= pageUrl($total_pages) ?>" class="pg-btn <?= $page >= $total_pages ? 'disabled' : '' ?>" title="Halaman terakhir">»</a>
                    </nav>
                </div>
                <?php endif; ?>

            </div>

            <?php elseif ($total_data === 0 && ($search !== '' || $filterStatus !== '' || $filterYear !== '' || $filterFakultas !== '')): ?>
            <!-- Tidak ada hasil pencarian -->
            <div class="table-card">
                <div class="empty-state">
                    <div class="empty-icon">
                        <svg fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                            <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
                        </svg>
                    </div>
                    <h4>Tidak ditemukan hasil</h4>
                    <p>Tidak ada alumni yang cocok dengan filter yang kamu terapkan.</p><br>
                    <a href="alumni.php" class="btn-add" style="display:inline-flex">Reset Filter</a>
                </div>
            </div>

            <?php else: ?>
            <!-- Benar-benar tidak ada data sama sekali -->
            <div class="table-card">
                <div class="empty-state">
                    <div class="empty-icon">
                        <svg fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                            <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                            <circle cx="9" cy="7" r="4"/>
                            <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
                            <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                        </svg>
                    </div>
                    <h4>Belum ada data alumni</h4>
                    <p>Mulai dengan mengimpor data melalui file Excel atau CSV.</p>
                </div>
            </div>
            <?php endif; ?>

        </div>

        <footer class="dash-footer">
            <span><span class="status-dot"></span>Sistem berjalan normal</span>
            <span>&copy; 2026 BandhuNet &mdash; Alumni Tracking &amp; Social Profiling</span>
        </footer>

    </div>
</div>

<?php include 'layout/footer.php'; ?>
</body>
</html>