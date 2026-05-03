<?php
session_start();
if (!isset($_SESSION['id_user'])) { header("Location: login.php"); exit; }
include 'koneksi.php';
include 'layout/header.php';

$sql = "SELECT a.id_alumni, a.nim, a.nama_lengkap, a.tahun_masuk, a.prodi, a.fakultas,
               a.tanggal_lulus,
               COALESCE(t.status, 'Belum Dilacak') AS status
        FROM alumni a
        LEFT JOIN tracking t ON a.id_alumni = t.id_alumni
        ORDER BY CASE WHEN COALESCE(t.status,'Belum Dilacak') = 'Found' THEN 1 ELSE 0 END ASC,
                 a.id_alumni ASC";

$res = mysqli_query($koneksi, $sql);
if (!$res) die("Query Error: " . mysqli_error($koneksi));

$rows = [];
while ($r = mysqli_fetch_assoc($res)) $rows[] = $r;
$total   = count($rows);
$done    = count(array_filter($rows, fn($r) => strtolower($r['status']) === 'found'));
$pct     = $total > 0 ? round($done / $total * 100) : 0;
$pending = array_values(array_filter($rows, fn($r) => strtolower($r['status']) !== 'found'));
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Profiling Engine — BandhuNet</title>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
:root{
  --bg:#0D0F14;--surface:#13161E;--surface2:#191D27;
  --border:rgba(255,255,255,0.07);--border2:rgba(255,255,255,0.12);
  --text:#F1F5F9;--muted:#64748B;--subtle:#1E2433;
  --indigo:#6366F1;--indigo-l:#A5B4FC;
  --emerald:#10B981;--emerald-l:#6EE7B7;
  --amber:#F59E0B;--amber-l:#FDE68A;
  --rose:#F43F5E;--rose-l:#FDA4AF;
  --violet:#8B5CF6;--violet-l:#C4B5FD;
}
body{font-family:'Plus Jakarta Sans',sans-serif;background:var(--bg);color:var(--text);min-height:100vh;display:flex}
.main-layout{display:flex;width:100%}
.page-content{flex:1;min-width:0;display:flex;flex-direction:column}
@keyframes fadeUp{from{opacity:0;transform:translateY(10px)}to{opacity:1;transform:translateY(0)}}
@keyframes pulse{0%,100%{opacity:1}50%{opacity:.4}}
@keyframes spin{to{transform:rotate(360deg)}}
@keyframes shimmer{0%{left:-100%}100%{left:200%}}

.topbar{display:flex;align-items:center;justify-content:space-between;padding:0 28px;height:60px;background:var(--surface);border-bottom:1px solid var(--border);flex-shrink:0}
.topbar-left h2{font-size:15px;font-weight:600}
.topbar-left p{font-size:12px;color:var(--muted);margin-top:1px}
.topbar-right{display:flex;align-items:center;gap:12px}
.welcome-chip{display:flex;align-items:center;gap:8px;background:var(--subtle);border:1px solid var(--border);border-radius:20px;padding:6px 12px 6px 6px;font-size:12.5px;color:var(--muted)}
.welcome-chip strong{color:var(--text);font-weight:500}
.avatar{width:26px;height:26px;border-radius:50%;background:linear-gradient(135deg,var(--indigo),#818CF8);display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:600;color:#fff}
.logout-chip{display:flex;align-items:center;gap:6px;background:rgba(244,63,94,.08);border:1px solid rgba(244,63,94,.2);border-radius:8px;padding:6px 12px;font-size:12px;font-weight:500;color:#FB7185;text-decoration:none}
.logout-chip:hover{background:rgba(244,63,94,.15)}
.logout-chip svg{width:13px;height:13px}

.dash-body{flex:1;padding:28px;overflow-y:auto}

/* Stats */
.stats-strip{display:grid;grid-template-columns:repeat(auto-fit,minmax(150px,1fr));gap:14px;margin-bottom:24px;animation:fadeUp .35s ease both}
.stat-card{background:var(--surface);border:1px solid var(--border);border-radius:12px;padding:16px 18px}
.stat-card .lbl{font-size:10.5px;font-weight:600;text-transform:uppercase;letter-spacing:.8px;color:var(--muted);margin-bottom:8px}
.stat-card .val{font-size:28px;font-weight:700;letter-spacing:-1px;line-height:1}
.stat-card .sub{font-size:11px;color:var(--muted);margin-top:5px}
.stat-card.indigo .val{color:var(--indigo-l)}
.stat-card.emerald .val{color:var(--emerald-l)}
.stat-card.amber .val{color:var(--amber-l)}
.stat-card.violet .val{color:var(--violet-l)}

/* Progress */
.progress-wrap{background:var(--surface);border:1px solid var(--border);border-radius:12px;padding:18px 20px;margin-bottom:20px;animation:fadeUp .35s ease .05s both}
.progress-label{display:flex;justify-content:space-between;font-size:12.5px;color:var(--muted);margin-bottom:10px}
.progress-label strong{color:var(--text)}
.progress-track{height:10px;background:rgba(255,255,255,.06);border-radius:99px;overflow:hidden;position:relative}
.progress-fill{height:100%;border-radius:99px;background:linear-gradient(90deg,var(--indigo),var(--emerald));transition:width .4s ease}
.progress-fill.animating::after{content:'';position:absolute;top:0;height:100%;width:40%;background:linear-gradient(90deg,transparent,rgba(255,255,255,.2),transparent);animation:shimmer 1.5s infinite}

/* Control */
.control-panel{background:var(--surface);border:1px solid var(--border);border-radius:14px;padding:20px;margin-bottom:20px;animation:fadeUp .35s ease .08s both}
.control-row{display:flex;align-items:center;gap:14px;flex-wrap:wrap}
.control-left h4{font-size:14px;font-weight:600;margin-bottom:4px}
.control-left p{font-size:12.5px;color:var(--muted)}
.btn-start-all{display:inline-flex;align-items:center;gap:8px;background:linear-gradient(135deg,var(--indigo),var(--violet));border:none;border-radius:10px;padding:11px 22px;font-family:'Plus Jakarta Sans',sans-serif;font-size:14px;font-weight:700;color:#fff;cursor:pointer;box-shadow:0 4px 16px rgba(99,102,241,.3);transition:opacity .2s,transform .15s;white-space:nowrap}
.btn-start-all:hover{opacity:.9}
.btn-start-all:active{transform:scale(.97)}
.btn-start-all:disabled{opacity:.4;cursor:not-allowed;transform:none}
.btn-start-all svg{width:16px;height:16px}
.btn-pause{display:none;align-items:center;gap:7px;background:rgba(245,158,11,.1);border:1px solid rgba(245,158,11,.3);border-radius:10px;padding:11px 18px;font-family:'Plus Jakarta Sans',sans-serif;font-size:13px;font-weight:600;color:var(--amber-l);cursor:pointer;transition:background .15s}
.btn-pause:hover{background:rgba(245,158,11,.18)}
.delay-select{height:38px;background:var(--surface2);border:1px solid var(--border2);border-radius:8px;padding:0 12px;color:var(--text);font-family:'Plus Jakarta Sans',sans-serif;font-size:13px;outline:none;cursor:pointer}

/* Current banner */
.current-banner{display:none;margin-bottom:16px;background:rgba(99,102,241,.07);border:1px solid rgba(99,102,241,.2);border-radius:10px;padding:12px 16px;animation:fadeUp .25s ease both}
.current-inner{display:flex;align-items:center;gap:12px}
.spin-dot{width:10px;height:10px;border-radius:50%;background:var(--indigo);box-shadow:0 0 8px var(--indigo);animation:pulse 1s infinite;flex-shrink:0}
.current-name{font-size:13.5px;font-weight:600}
.current-meta{font-size:12px;color:var(--muted);margin-top:2px}
.current-step{margin-left:auto;font-size:12px;color:var(--indigo-l);font-weight:500;white-space:nowrap}

/* Two col */
.two-col{display:grid;grid-template-columns:1fr 360px;gap:20px;animation:fadeUp .35s ease .12s both}
@media(max-width:960px){.two-col{grid-template-columns:1fr}}

/* Panel */
.panel{background:var(--surface);border:1px solid var(--border);border-radius:14px;overflow:hidden}
.panel-header{display:flex;align-items:center;gap:10px;padding:14px 20px;border-bottom:1px solid var(--border)}
.panel-icon{width:32px;height:32px;border-radius:8px;display:flex;align-items:center;justify-content:center;flex-shrink:0}
.panel-icon svg{width:15px;height:15px}
.panel-icon.indigo{background:rgba(99,102,241,.12);color:var(--indigo-l)}
.panel-icon.emerald{background:rgba(16,185,129,.12);color:var(--emerald-l)}
.panel-icon.violet{background:rgba(139,92,246,.12);color:var(--violet-l)}
.panel-icon.amber{background:rgba(245,158,11,.12);color:var(--amber-l)}
.panel-header h4{font-size:14px;font-weight:600}
.panel-header p{font-size:12px;color:var(--muted);margin-top:1px}

.table-search{padding:10px 16px;border-bottom:1px solid var(--border);position:relative}
.table-search input{width:100%;height:34px;background:var(--surface2);border:1px solid var(--border2);border-radius:8px;padding:0 12px 0 32px;font-family:'Plus Jakarta Sans',sans-serif;font-size:13px;color:var(--text);outline:none}
.table-search input:focus{border-color:var(--indigo)}
.table-search svg{position:absolute;left:26px;top:50%;transform:translateY(-50%);width:14px;height:14px;stroke:var(--muted);fill:none;pointer-events:none}

.data-table{width:100%;border-collapse:collapse}
.data-table thead tr{border-bottom:1px solid var(--border)}
.data-table thead th{padding:10px 14px;font-size:10.5px;font-weight:600;text-transform:uppercase;letter-spacing:.7px;color:var(--muted);text-align:left;background:rgba(255,255,255,.02);white-space:nowrap}
.data-table thead th:first-child{padding-left:18px}
.data-table thead th:last-child{padding-right:18px;text-align:right}
.data-table tbody tr{border-bottom:1px solid var(--border);transition:background .12s}
.data-table tbody tr:last-child{border-bottom:none}
.data-table tbody tr:hover{background:rgba(255,255,255,.025)}
.data-table tbody tr.active-row{background:rgba(99,102,241,.07)}
.data-table tbody td{padding:11px 14px;font-size:13px;vertical-align:middle}
.data-table tbody td:first-child{padding-left:18px}
.data-table tbody td:last-child{padding-right:18px;text-align:right}
.nim-cell{font-family:'Courier New',monospace;font-size:11.5px;color:var(--muted)}
.name-cell{display:flex;align-items:center;gap:9px}
.na{width:28px;height:28px;border-radius:7px;display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:600;flex-shrink:0}
.table-scroll{max-height:520px;overflow-y:auto;scrollbar-width:thin;scrollbar-color:var(--subtle) transparent}

.sbadge{display:inline-flex;align-items:center;gap:5px;font-size:11px;font-weight:500;padding:3px 9px;border-radius:20px;border:1px solid;white-space:nowrap}
.sbadge .dot{width:6px;height:6px;border-radius:50%;flex-shrink:0}
.sbadge.found{background:rgba(16,185,129,.1);border-color:rgba(16,185,129,.25);color:var(--emerald-l)}
.sbadge.found .dot{background:var(--emerald);box-shadow:0 0 5px rgba(16,185,129,.6);animation:pulse 2s infinite}
.sbadge.partial{background:rgba(245,158,11,.08);border-color:rgba(245,158,11,.22);color:var(--amber-l)}
.sbadge.partial .dot{background:var(--amber)}
.sbadge.notfound{background:rgba(244,63,94,.07);border-color:rgba(244,63,94,.2);color:var(--rose-l)}
.sbadge.notfound .dot{background:var(--rose)}
.sbadge.scanning{background:rgba(99,102,241,.1);border-color:rgba(99,102,241,.3);color:var(--indigo-l)}
.sbadge.scanning .dot{background:var(--indigo);animation:pulse .8s infinite}

.right-col{display:flex;flex-direction:column;gap:16px}

.source-grid{display:grid;grid-template-columns:1fr 1fr;gap:7px}
.source-item{display:flex;align-items:center;gap:7px;padding:7px 10px;border:1px solid var(--border2);border-radius:8px;cursor:pointer;transition:all .15s}
.source-item:hover{border-color:var(--indigo);background:rgba(99,102,241,.05)}
.source-item input{width:13px;height:13px;accent-color:var(--indigo);cursor:pointer}
.source-item .src-icon{width:20px;height:20px;border-radius:5px;display:flex;align-items:center;justify-content:center;font-size:9px;font-weight:700;flex-shrink:0}
.source-item .src-label{font-size:12px;font-weight:500}

.log-wrap{font-family:'Courier New',monospace;font-size:11.5px;background:#080A0E;padding:14px 16px;height:280px;overflow-y:auto;color:#94A3B8;line-height:1.8;scrollbar-width:thin;scrollbar-color:var(--subtle) transparent}
.log-line.info{color:#4B5563}
.log-line.success{color:var(--emerald-l)}
.log-line.warning{color:var(--amber-l)}
.log-line.error{color:var(--rose-l)}
.log-line.bold{color:var(--indigo-l);font-weight:bold}
.log-time{color:#1F2937;margin-right:6px;font-size:10.5px}

.result-preview{padding:14px 16px;display:none}
.rp-field{display:flex;gap:8px;padding:6px 0;border-bottom:1px solid var(--border);font-size:12.5px}
.rp-field:last-of-type{border-bottom:none}
.rp-label{color:var(--muted);min-width:100px;flex-shrink:0;font-size:11.5px}
.rp-value{color:var(--text);font-weight:500;word-break:break-all}
.rp-value.empty{color:var(--muted);font-style:italic;font-weight:400}
.rp-value a{color:var(--indigo-l);text-decoration:none}
.btn-view-detail{display:inline-flex;align-items:center;gap:6px;margin-top:10px;background:rgba(16,185,129,.1);border:1px solid rgba(16,185,129,.25);border-radius:8px;padding:7px 14px;font-family:'Plus Jakarta Sans',sans-serif;font-size:12.5px;font-weight:600;color:var(--emerald-l);text-decoration:none;transition:background .15s}
.btn-view-detail:hover{background:rgba(16,185,129,.18)}

.done-banner{display:none;padding:14px 20px;background:rgba(16,185,129,.08);border-top:1px solid rgba(16,185,129,.2);font-size:13px;color:var(--emerald-l)}
.done-banner strong{font-weight:700}

.spinner{width:14px;height:14px;border:2px solid rgba(255,255,255,.2);border-top-color:#fff;border-radius:50%;animation:spin .6s linear infinite}

.dash-footer{padding:16px 28px;border-top:1px solid var(--border);font-size:11.5px;color:var(--muted);display:flex;align-items:center;justify-content:space-between}
.status-dot{display:inline-block;width:7px;height:7px;background:var(--emerald);border-radius:50%;margin-right:6px;box-shadow:0 0 6px rgba(16,185,129,.6);animation:pulse 2s infinite}
</style>
</head>
<body>
<div class="main-layout">
<?php include 'layout/sidebar.php'; ?>
<div class="page-content">

<header class="topbar">
  <div class="topbar-left">
    <h2>Profiling Engine</h2>
    <p>Pelacakan otomatis data sosial &amp; profesional alumni</p>
  </div>
  <div class="topbar-right">
    <div class="welcome-chip">
      <div class="avatar"><?= strtoupper(substr($_SESSION['nama'],0,1)) ?></div>
      <span>Halo, <strong><?= htmlspecialchars($_SESSION['nama']) ?></strong></span>
    </div>
    <a href="logout.php" class="logout-chip">
      <svg fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" viewBox="0 0 24 24">
        <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/>
      </svg>
      Keluar
    </a>
  </div>
</header>

<div class="dash-body">

<div class="stats-strip">
  <div class="stat-card indigo"><div class="lbl">Total Alumni</div><div class="val" id="statTotal"><?= number_format($total) ?></div><div class="sub">Terdaftar</div></div>
  <div class="stat-card emerald"><div class="lbl">Ditemukan</div><div class="val" id="statFound"><?= number_format($done) ?></div><div class="sub">Status Found</div></div>
  <div class="stat-card amber"><div class="lbl">Belum Dilacak</div><div class="val" id="statPending"><?= number_format(count($pending)) ?></div><div class="sub">Antrian</div></div>
  <div class="stat-card violet"><div class="lbl">Coverage</div><div class="val" id="statPct"><?= $pct ?>%</div><div class="sub">Terlacak</div></div>
</div>

<div class="progress-wrap">
  <div class="progress-label">
    <strong>Progress Pelacakan</strong>
    <span id="progressText"><?= number_format($done) ?> / <?= number_format($total) ?> ditemukan</span>
  </div>
  <div class="progress-track"><div class="progress-fill" id="progressFill" style="width:<?= $pct ?>%"></div></div>
</div>

<div class="control-panel">
  <div class="control-row">
    <div class="control-left" style="flex:1;min-width:200px">
      <h4>🚀 Profiling Otomatis</h4>
      <p id="ctrlDesc"><?= count($pending) ?> alumni menunggu &mdash; hasil <strong>langsung tersimpan otomatis</strong> ke database tanpa klik tambahan.</p>
    </div>
    <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap">
      <select class="delay-select" id="delaySelect">
        <option value="800">Cepat (0.8s)</option>
        <option value="1500" selected>Normal (1.5s)</option>
        <option value="3000">Lambat (3s)</option>
        <option value="5000">Aman (5s)</option>
      </select>
      <button class="btn-start-all" id="btnStart" <?= count($pending)===0?'disabled':'' ?>>
        <svg fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><polygon points="5 3 19 12 5 21 5 3"/></svg>
        Start Profiling Semua
      </button>
      <button class="btn-pause" id="btnPause">
        <svg fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><rect x="6" y="4" width="4" height="16"/><rect x="14" y="4" width="4" height="16"/></svg>
        Pause
      </button>
    </div>
  </div>
</div>

<div class="current-banner" id="curBanner">
  <div class="current-inner">
    <div class="spin-dot"></div>
    <div><div class="current-name" id="curName">—</div><div class="current-meta" id="curMeta">—</div></div>
    <div class="current-step" id="curStep">—</div>
  </div>
</div>

<div class="two-col">
  <!-- LEFT table -->
  <div class="panel">
    <div class="panel-header">
      <div class="panel-icon indigo">
        <svg fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
      </div>
      <div><h4>Daftar Alumni</h4><p>Belum dilacak ditampilkan lebih dulu · klik Detail untuk lihat hasil</p></div>
    </div>
    <div class="table-search">
      <svg stroke-width="2" stroke-linecap="round" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
      <input type="text" id="tSearch" placeholder="Cari nama atau NIM...">
    </div>
    <div class="table-scroll">
      <table class="data-table">
        <thead><tr><th>NIM</th><th>Nama Alumni</th><th>Prodi</th><th>Status</th><th>Aksi</th></tr></thead>
        <tbody id="tBody">
        <?php
        $colors=[['rgba(99,102,241,.15)','#A5B4FC'],['rgba(16,185,129,.15)','#6EE7B7'],['rgba(245,158,11,.15)','#FDE68A'],['rgba(244,63,94,.12)','#FDA4AF'],['rgba(139,92,246,.15)','#C4B5FD']];
        foreach($rows as $i=>$r):
          $st=strtolower(trim($r['status']??''));
          $sc=$st==='found'?'found':($st==='partial'?'partial':'notfound');
          $sl=$st==='found'?'Found':($st==='partial'?'Partial':'Belum Dilacak');
          $c=$colors[$i%5]; $ini=strtoupper(substr($r['nama_lengkap'],0,1));
        ?>
        <tr id="row-<?=$r['id_alumni']?>" data-nim="<?=strtolower($r['nim'])?>" data-nama="<?=strtolower($r['nama_lengkap'])?>">
          <td class="nim-cell"><?=htmlspecialchars($r['nim'])?></td>
          <td><div class="name-cell"><div class="na" style="background:<?=$c[0]?>;color:<?=$c[1]?>"><?=$ini?></div><div><div style="font-weight:500;font-size:13px;line-height:1.3"><?=htmlspecialchars($r['nama_lengkap'])?></div><div style="font-size:11px;color:var(--muted)"><?=htmlspecialchars($r['tanggal_lulus']??'—')?></div></div></div></td>
          <td style="font-size:12px;color:var(--muted);max-width:120px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap"><?=htmlspecialchars($r['prodi']??'—')?></td>
          <td><span class="sbadge <?=$sc?>" id="badge-<?=$r['id_alumni']?>"><span class="dot"></span><?=$sl?></span></td>
          <td><a href="alumni_detail.php?id=<?=$r['id_alumni']?>" style="font-size:12px;color:var(--indigo-l);text-decoration:none;font-weight:500">Detail →</a></td>
        </tr>
        <?php endforeach;?>
        </tbody>
      </table>
    </div>
    <div class="done-banner" id="doneBanner">
      ✅ Selesai! <strong id="doneStats">—</strong>
      <a href="dashboard.php" style="margin-left:12px;color:var(--emerald-l);font-weight:600">Lihat Dashboard →</a>
    </div>
  </div>

  <!-- RIGHT -->
  <div class="right-col">
    <!-- Sources -->
    <div class="panel">
      <div class="panel-header">
        <div class="panel-icon violet">
          <svg fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg>
        </div>
        <div><h4>Sumber Data</h4><p>Aktifkan sumber yang dipakai</p></div>
      </div>
      <div style="padding:14px 16px">
        <div class="source-grid">
          <label class="source-item"><input type="checkbox" id="src-pddikti" checked><div class="src-icon" style="background:rgba(16,185,129,.12);color:var(--emerald-l)">PDI</div><span class="src-label">PDDIKTI</span></label>
          <label class="source-item"><input type="checkbox" id="src-linkedin" checked><div class="src-icon" style="background:rgba(10,102,194,.15);color:#7EC8E3">in</div><span class="src-label">LinkedIn</span></label>
          <label class="source-item"><input type="checkbox" id="src-instagram" checked><div class="src-icon" style="background:rgba(225,48,108,.12);color:#F9A8D4">IG</div><span class="src-label">Instagram</span></label>
          <label class="source-item"><input type="checkbox" id="src-facebook"><div class="src-icon" style="background:rgba(66,103,178,.12);color:#93C5FD">fb</div><span class="src-label">Facebook</span></label>
          <label class="source-item"><input type="checkbox" id="src-tiktok"><div class="src-icon" style="background:rgba(255,255,255,.06);color:var(--text)">TK</div><span class="src-label">TikTok</span></label>
          <label class="source-item"><input type="checkbox" id="src-google" checked><div class="src-icon" style="background:rgba(234,67,53,.12);color:#FCA5A5">G</div><span class="src-label">Google</span></label>
        </div>
      </div>
    </div>

    <!-- Log -->
    <div class="panel">
      <div class="panel-header">
        <div class="panel-icon emerald">
          <svg fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><polyline points="4 17 10 11 4 5"/><line x1="12" y1="19" x2="20" y2="19"/></svg>
        </div>
        <div><h4>Log Real-Time</h4><p>Auto-save ke database</p></div>
      </div>
      <div class="log-wrap" id="logBox">
        <div class="log-line info"><span class="log-time">--:--:--</span>Siap. Klik "Start Profiling Semua" untuk mulai.</div>
      </div>
    </div>

    <!-- Last result -->
    <div class="panel" id="lastPanel" style="display:none">
      <div class="panel-header">
        <div class="panel-icon amber">
          <svg fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
        </div>
        <div><h4>Terakhir Ditemukan</h4><p id="lastPanelName">—</p></div>
      </div>
      <div class="result-preview" id="lastPreview"></div>
    </div>
  </div>
</div>

</div><!-- dash-body -->
<footer class="dash-footer">
  <span><span class="status-dot"></span>Sistem berjalan normal</span>
  <span>&copy; 2026 BandhuNet &mdash; Alumni Tracking &amp; Social Profiling</span>
</footer>
</div></div>

<script>
const PENDING = <?= json_encode($pending, JSON_UNESCAPED_UNICODE) ?>;

let isRunning=false, isPaused=false, stopFlag=false;
let sessionFound=0, totalFound=<?= $done ?>, totalAlumni=<?= $total ?>;

const logBox    = document.getElementById('logBox');
const btnStart  = document.getElementById('btnStart');
const btnPause  = document.getElementById('btnPause');
const pFill     = document.getElementById('progressFill');
const pText     = document.getElementById('progressText');
const curBanner = document.getElementById('curBanner');
const curName   = document.getElementById('curName');
const curMeta   = document.getElementById('curMeta');
const curStep   = document.getElementById('curStep');
const doneBanner= document.getElementById('doneBanner');
const doneStats = document.getElementById('doneStats');
const lastPanel = document.getElementById('lastPanel');
const lastPanelName = document.getElementById('lastPanelName');
const lastPreview   = document.getElementById('lastPreview');

// Search
document.getElementById('tSearch').addEventListener('input', e => {
  const q = e.target.value.toLowerCase();
  document.querySelectorAll('#tBody tr').forEach(tr => {
    tr.style.display = !q || tr.dataset.nim?.includes(q) || tr.dataset.nama?.includes(q) ? '' : 'none';
  });
});

function ts() {
  const d=new Date();
  return [d.getHours(),d.getMinutes(),d.getSeconds()].map(n=>String(n).padStart(2,'0')).join(':');
}
function log(msg, type='info') {
  const el=document.createElement('div');
  el.className=`log-line ${type}`;
  el.innerHTML=`<span class="log-time">${ts()}</span>${msg}`;
  logBox.appendChild(el);
  if(logBox.children.length>400) logBox.removeChild(logBox.firstChild);
  logBox.scrollTop=logBox.scrollHeight;
}
function delay(ms){ return new Promise(r=>setTimeout(r,ms)); }

function setBadge(id, status) {
  const b=document.getElementById(`badge-${id}`);
  if(!b) return;
  const map={Found:['found','Found'],Partial:['partial','Partial'],scanning:['scanning','Scanning...']};
  const [cls,lbl]=map[status]||['notfound','Belum Dilacak'];
  b.className=`sbadge ${cls}`;
  b.innerHTML=`<span class="dot"></span>${lbl}`;
}

function updateStats() {
  const pct=totalAlumni>0?Math.round(totalFound/totalAlumni*100):0;
  document.getElementById('statFound').textContent=totalFound.toLocaleString('id-ID');
  document.getElementById('statPending').textContent=(totalAlumni-totalFound).toLocaleString('id-ID');
  document.getElementById('statPct').textContent=pct+'%';
  pFill.style.width=pct+'%';
  pText.textContent=`${totalFound.toLocaleString('id-ID')} / ${totalAlumni.toLocaleString('id-ID')} ditemukan`;
}

function getSrc() {
  return {
    pddikti:  document.getElementById('src-pddikti').checked,
    linkedin: document.getElementById('src-linkedin').checked,
    instagram:document.getElementById('src-instagram').checked,
    facebook: document.getElementById('src-facebook').checked,
    tiktok:   document.getElementById('src-tiktok').checked,
    google:   document.getElementById('src-google').checked,
  };
}

async function doFetch(url) {
  const ctrl=new AbortController();
  const t=setTimeout(()=>ctrl.abort(),25000);
  try {
    const r=await fetch(url,{signal:ctrl.signal});
    clearTimeout(t);
    return await r.json();
  } catch(e) { clearTimeout(t); return {found:false,_err:e.message}; }
}

async function profileOne(a) {
  const {id_alumni:id, nim, nama_lengkap:nama, prodi='', fakultas='', tanggal_lulus:lulus=''} = a;

  document.getElementById(`row-${id}`)?.classList.add('active-row');
  setBadge(id,'scanning');
  curBanner.style.display='';
  curName.textContent=nama;
  curMeta.textContent=`${nim} · ${prodi||'—'}`;

  const src=getSrc();
  let pddikti={},linkedin={},ig={},fb={},tk={},google={};

  if(src.pddikti){
    curStep.textContent='PDDIKTI...';
    pddikti=await doFetch(`api_scraper.php?action=pddikti&nim=${encodeURIComponent(nim)}&nama=${encodeURIComponent(nama)}`);
    log(`  [PDI] ${pddikti.found?'✓ '+pddikti.nama_pt:'—'}`, pddikti.found?'success':'info');
  }
  if(src.linkedin){
    curStep.textContent='LinkedIn...';
    linkedin=await doFetch(`api_scraper.php?action=linkedin&nama=${encodeURIComponent(nama)}&prodi=${encodeURIComponent(prodi)}`);
    log(`  [LI] ${linkedin.found?'✓ '+linkedin.headline:'—'}`, linkedin.found?'success':'info');
  }
  if(src.instagram){
    curStep.textContent='Instagram...';
    ig=await doFetch(`api_scraper.php?action=instagram&nama=${encodeURIComponent(nama)}`);
    log(`  [IG] ${ig.found?'✓ @'+ig.username:'—'}`, ig.found?'success':'info');
  }
  if(src.facebook){
    curStep.textContent='Facebook...';
    fb=await doFetch(`api_scraper.php?action=facebook&nama=${encodeURIComponent(nama)}`);
    log(`  [FB] ${fb.found?'✓ ditemukan':'—'}`, fb.found?'success':'info');
  }
  if(src.tiktok){
    curStep.textContent='TikTok...';
    tk=await doFetch(`api_scraper.php?action=tiktok&nama=${encodeURIComponent(nama)}`);
    log(`  [TK] ${tk.found?'✓ @'+tk.username:'—'}`, tk.found?'success':'info');
  }
  if(src.google){
    curStep.textContent='Google...';
    google=await doFetch(`api_scraper.php?action=google&nama=${encodeURIComponent(nama)}&prodi=${encodeURIComponent(prodi)}&lulus=${encodeURIComponent(lulus)}`);
    log(`  [G] ${google.found?'✓ '+google.snippet:'—'}`, google.found?'success':'info');
  }

  const hits=[];
  if(pddikti.found)  hits.push('PDDIKTI');
  if(linkedin.found) hits.push('LinkedIn');
  if(ig.found)       hits.push('Instagram');
  if(fb.found)       hits.push('Facebook');
  if(tk.found)       hits.push('TikTok');
  if(google.found)   hits.push('Google');

  const hasProf = linkedin.company||google.company||linkedin.email||google.email||pddikti.nama_pt;
  const status  = hits.length>=2&&hasProf ? 'Found' : hits.length>=1 ? 'Partial' : 'Not Found';

  const payload = {
    alumni_id:        id,
    sosmed_linkedin:  linkedin.profile_url  || '',
    sosmed_ig:        ig.profile_url        || (ig.username  ? `https://instagram.com/${ig.username}`  : ''),
    sosmed_fb:        fb.profile_url        || '',
    sosmed_tiktok:    tk.profile_url        || (tk.username  ? `https://tiktok.com/@${tk.username}`    : ''),
    email:            linkedin.email        || google.email   || '',
    no_hp:            linkedin.phone        || google.phone   || '',
    tempat_kerja:     linkedin.company      || google.company || '',
    alamat_kerja:     linkedin.company_addr || google.company_addr || '',
    posisi:           linkedin.position     || google.position || '',
    jenis_instansi:   ['PNS','Swasta','Wirausaha','Lainnya'].includes(linkedin.sektor||google.sektor||'') ? (linkedin.sektor||google.sektor) : 'Lainnya',
    sosmed_kantor:    linkedin.company_social||google.company_social||'',
    nama_pt_pddikti:  pddikti.nama_pt  || '',
    prodi_pt_pddikti: pddikti.prodi    || '',
    status_mhs:       pddikti.status   || '',
    sumber_data:      hits.join(', '),
    status:           status,
  };

  // Auto-save
  curStep.textContent='Menyimpan...';
  try {
    const sr=await fetch('api_save_tracking.php',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify(payload)});
    const sd=await sr.json();
    if(sd.success) log(`  💾 Saved → <strong>${status}</strong>`, status==='Found'?'success':'warning');
    else           log(`  ⚠ Save gagal: ${sd.message}`, 'error');
  } catch(e){ log(`  ✗ Save error: ${e.message}`, 'error'); }

  setBadge(id, status);
  document.getElementById(`row-${id}`)?.classList.remove('active-row');

  if(status==='Found'){ totalFound++; sessionFound++; updateStats(); }

  // Tampilkan preview kalau ada data
  if(status!=='Not Found') {
    lastPanel.style.display='';
    lastPanelName.textContent=nama;
    lastPreview.style.display='block';
    lastPreview.innerHTML=[
      ['Status',     status],
      ['Sumber',     hits.join(', ')||'—'],
      ['Tempat Kerja',payload.tempat_kerja||null],
      ['Posisi',     payload.posisi||null],
      ['Email',      payload.email||null],
      ['LinkedIn',   payload.sosmed_linkedin?`<a href="${payload.sosmed_linkedin}" target="_blank">Lihat →</a>`:null],
      ['PDDIKTI',    payload.nama_pt_pddikti||null],
    ].map(([l,v])=>`<div class="rp-field"><span class="rp-label">${l}</span><span class="rp-value ${!v?'empty':''}">${v||'—'}</span></div>`).join('')
    +`<a href="alumni_detail.php?id=${id}" class="btn-view-detail" target="_blank">Lihat Detail Lengkap →</a>`;
  }

  return status;
}

btnStart.addEventListener('click', async () => {
  if(isRunning) return;
  const queue=[...PENDING];
  if(!queue.length){ log('Semua alumni sudah dilacak!','success'); return; }

  isRunning=true; stopFlag=false; isPaused=false; sessionFound=0;
  btnStart.disabled=true;
  btnStart.innerHTML='<div class="spinner"></div> Berjalan...';
  btnPause.style.display='inline-flex';
  pFill.classList.add('animating');
  doneBanner.style.display='none';

  log(`═══ START: ${queue.length} alumni dalam antrian ═══`,'bold');
  const ms=parseInt(document.getElementById('delaySelect').value);

  for(let i=0;i<queue.length;i++){
    while(isPaused&&!stopFlag) await delay(500);
    if(stopFlag) break;
    log(`── [${i+1}/${queue.length}] ${queue[i].nama_lengkap}`,'bold');
    try{ await profileOne(queue[i]); } catch(e){ log(`✗ ${e.message}`,'error'); }
    if(i<queue.length-1&&!stopFlag) await delay(ms);
  }

  isRunning=false; stopFlag=false; isPaused=false;
  pFill.classList.remove('animating');
  curBanner.style.display='none';
  btnPause.style.display='none';
  btnStart.disabled=false;
  btnStart.innerHTML='<svg fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><polygon points="5 3 19 12 5 21 5 3"/></svg>Start Profiling Semua';
  doneStats.textContent=`${sessionFound} Found dari ${queue.length} yang diproses`;
  doneBanner.style.display='block';
  log(`═══ SELESAI: ${sessionFound} Found / ${queue.length} diproses ═══`,'bold');
});

btnPause.addEventListener('click', () => {
  if(!isRunning) return;
  isPaused=!isPaused;
  if(isPaused){
    btnPause.innerHTML='<svg fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24" width="15" height="15"><polygon points="5 3 19 12 5 21 5 3"/></svg>Resume';
    log('⏸ Dijeda...','warning');
  } else {
    btnPause.innerHTML='<svg fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24" width="15" height="15"><rect x="6" y="4" width="4" height="16"/><rect x="14" y="4" width="4" height="16"/></svg>Pause';
    log('▶ Dilanjutkan...','info');
  }
});
</script>

<?php include 'layout/footer.php'; ?>
</body>
</html>