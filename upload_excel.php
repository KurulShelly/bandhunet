<?php 
session_start();
if (!isset($_SESSION['id_user'])) { header("Location: login.php"); exit; }
include 'koneksi.php';
include 'layout/header.php'; 
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Import Data Alumni — BandhuNet</title>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<style>
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

:root {
    --bg:       #0D0F14;
    --surface:  #13161E;
    --border:   rgba(255,255,255,0.07);
    --border2:  rgba(255,255,255,0.12);
    --text:     #F1F5F9;
    --muted:    #64748B;
    --subtle:   #1E2433;
    --indigo:   #6366F1;
    --indigo-l: #A5B4FC;
    --emerald:  #10B981;
    --emerald-l:#6EE7B7;
    --amber-l:  #FDE68A;
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

/* Topbar */
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

/* Body */
.dash-body {
    flex: 1; padding: 28px; overflow-y: auto;
    display: flex; flex-direction: column; align-items: center;
}

@keyframes fadeUp {
    from { opacity: 0; transform: translateY(10px); }
    to   { opacity: 1; transform: translateY(0); }
}

.page-header {
    width: 100%; max-width: 680px; margin-bottom: 24px;
    animation: fadeUp 0.35s ease both;
}
.page-header h3 { font-size: 20px; font-weight: 700; letter-spacing: -0.4px; }
.page-header p  { font-size: 13px; color: var(--muted); margin-top: 4px; }

/* Card */
.upload-card {
    width: 100%; max-width: 680px;
    background: var(--surface); border: 1px solid var(--border);
    border-radius: 16px; overflow: hidden;
    animation: fadeUp 0.35s ease 0.05s both;
}
.card-top-bar {
    height: 3px;
    background: linear-gradient(90deg, var(--indigo), #818CF8, var(--emerald));
}
.upload-card-body { padding: 28px; }

/* Info box */
.info-box {
    display: flex; gap: 12px;
    background: rgba(245,158,11,0.08); border: 1px solid rgba(245,158,11,0.2);
    border-radius: 10px; padding: 14px 16px; margin-bottom: 28px;
}
.info-box-icon {
    width: 32px; height: 32px; flex-shrink: 0; border-radius: 8px;
    background: rgba(245,158,11,0.12);
    display: flex; align-items: center; justify-content: center;
    color: var(--amber-l);
}
.info-box-icon svg { width: 16px; height: 16px; }
.info-box-text p { font-size: 12.5px; color: var(--amber-l); line-height: 1.6; }
.info-box-text strong { font-weight: 600; color: #FEF3C7; }

/* Field label */
.field-label {
    display: flex; align-items: center; gap: 6px;
    font-size: 12px; font-weight: 600;
    text-transform: uppercase; letter-spacing: 0.7px;
    color: var(--muted); margin-bottom: 10px;
}

/* Dropzone */
.dropzone {
    border: 1.5px dashed rgba(255,255,255,0.12); border-radius: 12px;
    padding: 36px 24px;
    display: flex; flex-direction: column; align-items: center; gap: 10px;
    cursor: pointer; position: relative;
    background: rgba(255,255,255,0.02);
    transition: border-color 0.2s, background 0.2s;
}
.dropzone:hover, .dropzone.drag-over {
    border-color: var(--indigo); background: rgba(99,102,241,0.06);
}
.dropzone input[type="file"] {
    position: absolute; inset: 0; opacity: 0; cursor: pointer;
    width: 100%; height: 100%;
}
.dz-icon {
    width: 48px; height: 48px; border-radius: 12px;
    background: rgba(99,102,241,0.1);
    display: flex; align-items: center; justify-content: center;
    color: var(--indigo-l); transition: background 0.2s;
}
.dropzone:hover .dz-icon { background: rgba(99,102,241,0.2); }
.dz-icon svg { width: 22px; height: 22px; }
.dz-label { font-size: 13.5px; font-weight: 500; text-align: center; }
.dz-label span { color: var(--indigo-l); }
.dz-sub { font-size: 11.5px; color: var(--muted); text-align: center; }

/* File preview */
.file-preview {
    display: none; align-items: center; gap: 12px;
    background: rgba(16,185,129,0.06); border: 1px solid rgba(16,185,129,0.2);
    border-radius: 10px; padding: 12px 14px; margin-top: 12px;
}
.file-preview.show { display: flex; }
.file-icon {
    width: 36px; height: 36px; flex-shrink: 0; border-radius: 8px;
    background: rgba(16,185,129,0.12);
    display: flex; align-items: center; justify-content: center;
    color: var(--emerald-l);
}
.file-icon svg { width: 16px; height: 16px; }
.file-info { flex: 1; min-width: 0; }
.file-name { font-size: 13px; font-weight: 500; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.file-size { font-size: 11px; color: var(--muted); margin-top: 2px; }
.file-remove {
    background: none; border: none; cursor: pointer;
    color: var(--muted); padding: 4px; border-radius: 6px;
    display: flex; align-items: center;
    transition: color 0.15s, background 0.15s;
}
.file-remove:hover { color: #FB7185; background: rgba(244,63,94,0.1); }
.file-remove svg { width: 15px; height: 15px; }

/* Format badges */
.format-row { display: flex; align-items: center; gap: 8px; margin-top: 16px; flex-wrap: wrap; }
.format-label { font-size: 11.5px; color: var(--muted); }
.format-badge {
    display: inline-flex; align-items: center; gap: 5px;
    font-size: 11px; font-weight: 500;
    padding: 4px 10px; border-radius: 20px; border: 1px solid;
}
.format-badge.csv  { background: rgba(16,185,129,0.08); border-color: rgba(16,185,129,0.25); color: var(--emerald-l); }
.format-badge.xlsx { background: rgba(99,102,241,0.08); border-color: rgba(99,102,241,0.25); color: var(--indigo-l); }
.format-badge.xls  { background: rgba(99,102,241,0.06); border-color: rgba(99,102,241,0.18); color: #C7D2FE; }
.format-badge svg  { width: 11px; height: 11px; }

/* Divider */
.section-divider { border: none; border-top: 1px solid var(--border); margin: 24px 0; }

/* Actions */
.action-row { display: flex; align-items: center; gap: 10px; flex-wrap: wrap; }

.btn-upload {
    display: inline-flex; align-items: center; gap: 8px;
    background: var(--indigo); color: #fff; border: none;
    border-radius: 9px; padding: 10px 20px;
    font-family: 'Plus Jakarta Sans', sans-serif;
    font-size: 13.5px; font-weight: 600; cursor: pointer;
    transition: background 0.15s, transform 0.15s;
}
.btn-upload:hover   { background: #5254CC; }
.btn-upload:active  { transform: scale(0.97); }
.btn-upload:disabled { opacity: 0.45; cursor: not-allowed; }
.btn-upload svg { width: 15px; height: 15px; }

.btn-cancel {
    display: inline-flex; align-items: center; gap: 8px;
    background: transparent; color: var(--muted);
    border: 1px solid var(--border2); border-radius: 9px; padding: 10px 18px;
    font-family: 'Plus Jakarta Sans', sans-serif;
    font-size: 13.5px; font-weight: 500; text-decoration: none;
    transition: background 0.15s, color 0.15s;
}
.btn-cancel:hover { background: var(--subtle); color: var(--text); }
.btn-cancel svg { width: 14px; height: 14px; }

.size-note {
    font-size: 12px; color: var(--muted);
    margin-left: auto;
    display: flex; align-items: center; gap: 5px;
}
.size-note svg { width: 13px; height: 13px; }

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

        <header class="topbar">
            <div class="topbar-left">
                <h2>Import Data Alumni</h2>
                <p>Unggah file Excel atau CSV ke database</p>
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

            <div class="page-header">
                <h3>Upload Data Alumni</h3>
                <p>Impor daftar alumni dari file spreadsheet secara massal</p>
            </div>

            <div class="upload-card">
                <div class="card-top-bar"></div>
                <div class="upload-card-body">

                    <div class="info-box">
                        <div class="info-box-icon">
                            <svg fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                                <circle cx="12" cy="12" r="10"/>
                                <line x1="12" y1="8" x2="12" y2="12"/>
                                <line x1="12" y1="16" x2="12.01" y2="16"/>
                            </svg>
                        </div>
                        <div class="info-box-text">
                            <p>
                                <strong>Gunakan format .csv</strong> untuk proses yang lebih cepat tanpa library tambahan.
                                Pastikan header kolom sesuai template sebelum mengunggah.
                            </p>
                        </div>
                    </div>

                    <form action="import_excel.php" method="POST" enctype="multipart/form-data" id="uploadForm">

                        <div class="field-label">
                            <svg fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24" width="13" height="13">
                                <path d="M13 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V9z"/>
                                <polyline points="13 2 13 9 20 9"/>
                            </svg>
                            Pilih File
                        </div>

                        <div class="dropzone" id="dropzone">
                            <input type="file" name="file_alumni" id="fileInput" required accept=".csv,.xlsx,.xls">
                            <div class="dz-icon">
                                <svg fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                                    <polyline points="16 16 12 12 8 16"/>
                                    <line x1="12" y1="12" x2="12" y2="21"/>
                                    <path d="M20.39 18.39A5 5 0 0 0 18 9h-1.26A8 8 0 1 0 3 16.3"/>
                                </svg>
                            </div>
                            <p class="dz-label">Seret file ke sini atau <span>klik untuk memilih</span></p>
                            <p class="dz-sub">Mendukung .csv, .xlsx, dan .xls</p>
                        </div>

                        <div class="file-preview" id="filePreview">
                            <div class="file-icon">
                                <svg fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                                    <path d="M13 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V9z"/>
                                    <polyline points="13 2 13 9 20 9"/>
                                </svg>
                            </div>
                            <div class="file-info">
                                <div class="file-name" id="fileName">—</div>
                                <div class="file-size" id="fileSize">—</div>
                            </div>
                            <button type="button" class="file-remove" id="fileRemove">
                                <svg fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" viewBox="0 0 24 24">
                                    <line x1="18" y1="6" x2="6" y2="18"/>
                                    <line x1="6" y1="6" x2="18" y2="18"/>
                                </svg>
                            </button>
                        </div>

                        <div class="format-row">
                            <span class="format-label">Format diterima:</span>
                            <span class="format-badge csv">
                                <svg fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
                                .csv
                            </span>
                            <span class="format-badge xlsx">
                                <svg fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
                                .xlsx
                            </span>
                            <span class="format-badge xls">
                                <svg fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
                                .xls
                            </span>
                        </div>

                        <hr class="section-divider">

                        <div class="action-row">
                            <button type="submit" name="upload" class="btn-upload" id="submitBtn" disabled>
                                <svg fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                                    <polyline points="16 16 12 12 8 16"/>
                                    <line x1="12" y1="12" x2="12" y2="21"/>
                                    <path d="M20.39 18.39A5 5 0 0 0 18 9h-1.26A8 8 0 1 0 3 16.3"/>
                                </svg>
                                Unggah &amp; Simpan ke Database
                            </button>
                            <a href="alumni.php" class="btn-cancel">
                                <svg fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" viewBox="0 0 24 24">
                                    <line x1="19" y1="12" x2="5" y2="12"/>
                                    <polyline points="12 19 5 12 12 5"/>
                                </svg>
                                Batal
                            </a>
                            <span class="size-note">
                                <svg fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" viewBox="0 0 24 24">
                                    <circle cx="12" cy="12" r="10"/>
                                    <line x1="12" y1="8" x2="12" y2="12"/>
                                    <line x1="12" y1="16" x2="12.01" y2="16"/>
                                </svg>
                                Maks. 2 MB
                            </span>
                        </div>

                    </form>
                </div>
            </div>

        </div>

        <footer class="dash-footer">
            <span><span class="status-dot"></span>Sistem berjalan normal</span>
            <span>&copy; 2026 BandhuNet &mdash; Alumni Tracking &amp; Social Profiling</span>
        </footer>

    </div>
</div>

<script>
const fileInput  = document.getElementById('fileInput');
const dropzone   = document.getElementById('dropzone');
const preview    = document.getElementById('filePreview');
const fileName   = document.getElementById('fileName');
const fileSize   = document.getElementById('fileSize');
const fileRemove = document.getElementById('fileRemove');
const submitBtn  = document.getElementById('submitBtn');

function formatBytes(b) {
    if (b < 1024) return b + ' B';
    if (b < 1048576) return (b / 1024).toFixed(1) + ' KB';
    return (b / 1048576).toFixed(2) + ' MB';
}

function showFile(file) {
    fileName.textContent = file.name;
    fileSize.textContent = formatBytes(file.size);
    preview.classList.add('show');
    submitBtn.disabled = false;
}

function clearFile() {
    fileInput.value = '';
    preview.classList.remove('show');
    submitBtn.disabled = true;
}

fileInput.addEventListener('change', () => {
    if (fileInput.files.length) showFile(fileInput.files[0]);
});

fileRemove.addEventListener('click', clearFile);

dropzone.addEventListener('dragover',  e => { e.preventDefault(); dropzone.classList.add('drag-over'); });
dropzone.addEventListener('dragleave', () => dropzone.classList.remove('drag-over'));
dropzone.addEventListener('drop', e => {
    e.preventDefault();
    dropzone.classList.remove('drag-over');
    const file = e.dataTransfer.files[0];
    if (file) {
        const dt = new DataTransfer();
        dt.items.add(file);
        fileInput.files = dt.files;
        showFile(file);
    }
});
</script>

<?php include 'layout/footer.php'; ?>
</body>
</html>