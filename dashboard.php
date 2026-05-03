<?php 
session_start(); 

if (!isset($_SESSION['id_user'])) {
    header("Location: login.php");
    exit;
}

include 'koneksi.php';
include 'layout/header.php'; 

$total_alumni  = mysqli_num_rows(mysqli_query($koneksi, "SELECT id_alumni FROM alumni"));
$query = "SELECT COUNT(*) as jumlah FROM tracking WHERE status = 'Found'";
$res = mysqli_query($koneksi, $query);
$accuracy      = 85;
$query_found = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM tracking WHERE status = 'Found'");
$data_found = mysqli_fetch_assoc($query_found);
$total_found = $data_found['total']; // Variabel ini yang dicari baris 356
// --------------------------

// Jika kamu punya kartu statistik lain, tambahkan juga sekalian:
$total_alumni = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) as total FROM alumni"))['total'];
$total_pending = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) as total FROM tracking WHERE status = 'Belum Dilacak'"))['total'];
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Dashboard — BandhuNet</title>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<style>
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

:root {
    --bg:       #0D0F14;
    --surface:  #13161E;
    --surface2: #191D27;
    --border:   rgba(255,255,255,0.07);
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
}

body {
    font-family: 'Plus Jakarta Sans', sans-serif;
    background: var(--bg);
    color: var(--text);
    min-height: 100vh;
    display: flex;
}

.main-layout { display: flex; width: 100%; }

.page-content {
    flex: 1;
    min-width: 0;
    display: flex;
    flex-direction: column;
}

/* ── Topbar ── */
.topbar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0 28px;
    height: 60px;
    background: var(--surface);
    border-bottom: 1px solid var(--border);
    flex-shrink: 0;
}

.topbar-left h2 {
    font-size: 15px;
    font-weight: 600;
    color: var(--text);
    letter-spacing: -0.2px;
}
.topbar-left p {
    font-size: 12px;
    color: var(--muted);
    margin-top: 1px;
}

.topbar-right { display: flex; align-items: center; gap: 12px; }

.welcome-chip {
    display: flex;
    align-items: center;
    gap: 8px;
    background: var(--subtle);
    border: 1px solid var(--border);
    border-radius: 20px;
    padding: 6px 12px 6px 6px;
    font-size: 12.5px;
    color: var(--muted);
}
.welcome-chip strong { color: var(--text); font-weight: 500; }

.avatar {
    width: 26px; height: 26px;
    border-radius: 50%;
    background: linear-gradient(135deg, var(--indigo), #818CF8);
    display: flex; align-items: center; justify-content: center;
    font-size: 11px; font-weight: 600; color: #fff;
    flex-shrink: 0;
}

.logout-chip {
    display: flex; align-items: center; gap: 6px;
    background: rgba(244,63,94,0.08);
    border: 1px solid rgba(244,63,94,0.2);
    border-radius: 8px;
    padding: 6px 12px;
    font-size: 12px; font-weight: 500;
    color: #FB7185;
    text-decoration: none;
    transition: background 0.15s;
}
.logout-chip:hover { background: rgba(244,63,94,0.15); }
.logout-chip svg { width: 13px; height: 13px; }

/* ── Body ── */
.dash-body {
    flex: 1;
    padding: 28px;
    overflow-y: auto;
}

/* ── Section header ── */
.section-header {
    margin-bottom: 20px;
}
.section-header h3 {
    font-size: 12px;
    font-weight: 600;
    letter-spacing: 1px;
    text-transform: uppercase;
    color: var(--muted);
}

/* ── Stat cards ── */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    gap: 16px;
    margin-bottom: 28px;
}

.stat-card {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: 14px;
    padding: 22px 22px 20px;
    position: relative;
    overflow: hidden;
    transition: border-color 0.2s, transform 0.2s;
    animation: fadeUp 0.4s ease both;
}
.stat-card:hover { transform: translateY(-2px); }
.stat-card:nth-child(1) { animation-delay: 0.05s; }
.stat-card:nth-child(2) { animation-delay: 0.1s; }
.stat-card:nth-child(3) { animation-delay: 0.15s; }

@keyframes fadeUp {
    from { opacity: 0; transform: translateY(12px); }
    to   { opacity: 1; transform: translateY(0); }
}

.stat-card::before {
    content: '';
    position: absolute;
    top: 0; left: 0; right: 0;
    height: 2px;
    border-radius: 14px 14px 0 0;
}
.stat-card.indigo::before { background: var(--indigo); }
.stat-card.emerald::before { background: var(--emerald); }
.stat-card.amber::before { background: var(--amber); }

.stat-card.indigo:hover  { border-color: rgba(99,102,241,0.35); }
.stat-card.emerald:hover { border-color: rgba(16,185,129,0.35); }
.stat-card.amber:hover   { border-color: rgba(245,158,11,0.35); }

.stat-icon {
    width: 38px; height: 38px;
    border-radius: 10px;
    display: flex; align-items: center; justify-content: center;
    margin-bottom: 14px;
}
.stat-icon.indigo  { background: rgba(99,102,241,0.12); color: var(--indigo-l); }
.stat-icon.emerald { background: rgba(16,185,129,0.12); color: var(--emerald-l); }
.stat-icon.amber   { background: rgba(245,158,11,0.12);  color: var(--amber-l); }
.stat-icon svg { width: 18px; height: 18px; }

.stat-label {
    font-size: 11px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.8px;
    color: var(--muted);
    margin-bottom: 6px;
}

.stat-value {
    font-size: 32px;
    font-weight: 700;
    letter-spacing: -1px;
    line-height: 1;
    color: var(--text);
}

.stat-badge {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    font-size: 11px;
    font-weight: 500;
    padding: 3px 8px;
    border-radius: 20px;
    margin-top: 10px;
}
.stat-badge.indigo  { background: rgba(99,102,241,0.12);  color: var(--indigo-l); }
.stat-badge.emerald { background: rgba(16,185,129,0.12);  color: var(--emerald-l); }
.stat-badge.amber   { background: rgba(245,158,11,0.12);   color: var(--amber-l); }

/* ── Chart card ── */
.chart-card {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: 14px;
    overflow: hidden;
    animation: fadeUp 0.4s ease 0.2s both;
}

.chart-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 20px 22px 16px;
    border-bottom: 1px solid var(--border);
}

.chart-header h4 {
    font-size: 14px;
    font-weight: 600;
    color: var(--text);
    letter-spacing: -0.2px;
}

.chart-header p {
    font-size: 12px;
    color: var(--muted);
    margin-top: 2px;
}

.chart-legend {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 12px;
    color: var(--muted);
}

.legend-dot {
    width: 8px; height: 8px;
    border-radius: 50%;
    background: var(--indigo);
    flex-shrink: 0;
}

.chart-body {
    padding: 20px 22px 22px;
    height: 280px;
    position: relative;
}

/* ── Footer ── */
.dash-footer {
    padding: 16px 28px;
    border-top: 1px solid var(--border);
    font-size: 11.5px;
    color: var(--muted);
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.status-dot {
    display: inline-block;
    width: 7px; height: 7px;
    background: var(--emerald);
    border-radius: 50%;
    margin-right: 6px;
    box-shadow: 0 0 6px rgba(16,185,129,0.6);
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.4; }
}
</style>
</head>
<body>
<div class="main-layout">

    <?php include 'layout/sidebar.php'; ?>

    <div class="page-content">

        <!-- Topbar -->
        <header class="topbar">
            <div class="topbar-left">
                <h2>Dashboard</h2>
                <p>Ringkasan sistem pelacakan alumni</p>
            </div>
            <div class="topbar-right">
                <div class="welcome-chip">
                    <div class="avatar"><?= strtoupper(substr($_SESSION['nama'], 0, 1)) ?></div>
                    <span>Halo, <strong><?= htmlspecialchars($_SESSION['nama']) ?></strong></span>
                </div>
                <a href="logout.php" class="logout-chip">
                    <svg fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" viewBox="0 0 24 24"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
                    Keluar
                </a>
            </div>
        </header>

        <!-- Main Content -->
        <div class="dash-body">

            <div class="section-header">
                <h3>Ringkasan Utama</h3>
            </div>

            <!-- Stat Cards -->
            <div class="stats-grid">

                <div class="stat-card indigo">
                    <div class="stat-icon indigo">
                        <svg fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                    </div>
                    <div class="stat-label">Total Alumni Terdaftar</div>
                    <div class="stat-value"><?= $total_alumni ?></div>
                    <div class="stat-badge indigo">Orang</div>
                </div>

                <div class="stat-card emerald">
                    <div class="stat-icon emerald">
                        <svg fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><circle cx="12" cy="12" r="2"/><path d="M16.24 7.76a6 6 0 0 1 0 8.49m-8.48-.01a6 6 0 0 1 0-8.49m11.31-2.82a10 10 0 0 1 0 14.14m-14.14 0a10 10 0 0 1 0-14.14"/></svg>
                    </div>
                    <div class="stat-label">Berhasil Dilacak</div>
                    <div class="stat-value"><?= $total_found ?></div>
                    <div class="stat-badge emerald">Found</div>
                </div>

                <div class="stat-card amber">
                    <div class="stat-icon amber">
                        <svg fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
                    </div>
                    <div class="stat-label">Skor Akurasi Data</div>
                    <div class="stat-value"><?= $accuracy ?>%</div>
                    <div class="stat-badge amber">Reliable</div>
                </div>

            </div>

            <!-- Chart -->
            <div class="chart-card">
                <div class="chart-header">
                    <div>
                        <h4>Progress Pelacakan Bulanan</h4>
                        <p>Alumni ditemukan per bulan — 2026</p>
                    </div>
                    <div class="chart-legend">
                        <span class="legend-dot"></span>
                        Alumni Ditemukan
                    </div>
                </div>
                <div class="chart-body">
                    <canvas id="trackingChart"></canvas>
                </div>
            </div>

        </div>

        <!-- Footer -->
        <footer class="dash-footer">
            <span><span class="status-dot"></span>Sistem berjalan normal</span>
            <span>&copy; 2026 BandhuNet &mdash; Alumni Tracking &amp; Social Profiling</span>
        </footer>

    </div><!-- /page-content -->
</div>

<script>
const ctx = document.getElementById('trackingChart').getContext('2d');

const grad = ctx.createLinearGradient(0, 0, 0, 240);
grad.addColorStop(0, 'rgba(99,102,241,0.25)');
grad.addColorStop(1, 'rgba(99,102,241,0)');

new Chart(ctx, {
    type: 'line',
    data: {
        labels: ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun'],
        datasets: [{
            label: 'Alumni Ditemukan',
            data: [5, 12, 19, 25, 40, 55],
            fill: true,
            backgroundColor: grad,
            borderColor: '#6366F1',
            borderWidth: 2.5,
            tension: 0.45,
            pointBackgroundColor: '#6366F1',
            pointBorderColor: '#0D0F14',
            pointBorderWidth: 2,
            pointRadius: 5,
            pointHoverRadius: 7,
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: { display: false },
            tooltip: {
                backgroundColor: '#1E2433',
                borderColor: 'rgba(255,255,255,0.08)',
                borderWidth: 1,
                titleColor: '#94A3B8',
                bodyColor: '#F1F5F9',
                padding: 10,
                cornerRadius: 8,
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                grid: { color: 'rgba(255,255,255,0.04)' },
                ticks: { color: '#64748B', font: { size: 11 } },
                border: { dash: [4, 4], color: 'transparent' }
            },
            x: {
                grid: { display: false },
                ticks: { color: '#64748B', font: { size: 11 } },
                border: { color: 'rgba(255,255,255,0.06)' }
            }
        }
    }
});
</script>

<?php include 'layout/footer.php'; ?>
</body>
</html>