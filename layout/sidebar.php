<?php 
$current_page = basename($_SERVER['PHP_SELF']);

$nav_groups = [
    'core' => [
        ['href' => 'dashboard.php',  'icon' => 'grid',     'label' => 'Dashboard'],
        ['href' => 'penilaian.php',  'icon' => 'star',     'label' => 'Skor Penilaian'],
    ],
    'Manajemen Alumni' => [
        ['href' => 'alumni.php',       'icon' => 'users',        'label' => 'List Alumni'],
        ['href' => 'upload_excel.php', 'icon' => 'upload-cloud', 'label' => 'Upload Data'],
        ['href' => 'upload_ajax.php',  'icon' => 'upload-cloud', 'label' => 'Upload Data Besar'],
    ],
    'Mesin Pelacakan' => [
        ['href' => 'profiling.php', 'icon' => 'zap',        'label' => 'Jalankan Profiling', 'accent' => true],
        ['href' => 'tracking.php',  'icon' => 'radio',      'label' => 'Hasil Tracking'],
        ['href' => 'verifikasi.php','icon' => 'check-circle','label' => 'Verifikasi Data'],
    ],
    'Laporan' => [
        ['href' => 'laporan/coverage.php', 'icon' => 'bar-chart-2', 'label' => 'Laporan Coverage'],
    ],
];

function feather_icon(string $name): string {
    $icons = [
        'grid'        => '<path d="M3 3h7v7H3zM14 3h7v7h-7zM14 14h7v7h-7zM3 14h7v7H3z"/>',
        'star'        => '<polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>',
        'building'    => '<rect x="4" y="2" width="16" height="20" rx="1"/><path d="M9 22V12h6v10"/><path d="M8 7h.01M12 7h.01M16 7h.01M8 11h.01M12 11h.01M16 11h.01"/>',
        'award'       => '<circle cx="12" cy="8" r="6"/><path d="M15.477 12.89L17 22l-5-3-5 3 1.523-9.11"/>',
        'users'       => '<path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>',
        'upload-cloud'=> '<polyline points="16 16 12 12 8 16"/><line x1="12" y1="12" x2="12" y2="21"/><path d="M20.39 18.39A5 5 0 0 0 18 9h-1.26A8 8 0 1 0 3 16.3"/>',
        'zap'         => '<polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/>',
        'radio'       => '<circle cx="12" cy="12" r="2"/><path d="M16.24 7.76a6 6 0 0 1 0 8.49m-8.48-.01a6 6 0 0 1 0-8.49m11.31-2.82a10 10 0 0 1 0 14.14m-14.14 0a10 10 0 0 1 0-14.14"/>',
        'check-circle'=> '<path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/>',
        'bar-chart-2' => '<line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/>',
        'log-out'     => '<path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/>',
    ];
    $paths = $icons[$name] ?? '<circle cx="12" cy="12" r="3"/>';
    return '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">' . $paths . '</svg>';
}
?>

<style>
@import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600&display=swap');

.bnet-sidebar {
    width: 260px;
    min-height: 100vh;
    background: #0D0F14;
    display: flex;
    flex-direction: column;
    flex-shrink: 0;
    font-family: 'Plus Jakarta Sans', sans-serif;
    border-right: 1px solid rgba(255,255,255,0.06);
    position: relative;
    overflow: hidden;
}

.bnet-sidebar::before {
    content: '';
    position: absolute;
    top: -80px;
    left: -80px;
    width: 260px;
    height: 260px;
    background: radial-gradient(circle, rgba(99,102,241,0.15) 0%, transparent 70%);
    pointer-events: none;
}

.bnet-sidebar::after {
    content: '';
    position: absolute;
    bottom: 60px;
    right: -60px;
    width: 200px;
    height: 200px;
    background: radial-gradient(circle, rgba(16,185,129,0.08) 0%, transparent 70%);
    pointer-events: none;
}

.sidebar-brand {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 24px 20px 20px;
    text-decoration: none;
    border-bottom: 1px solid rgba(255,255,255,0.06);
    margin-bottom: 8px;
}

.brand-mark {
    width: 36px;
    height: 36px;
    border-radius: 10px;
    background: linear-gradient(135deg, #6366F1, #818CF8);
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    box-shadow: 0 4px 12px rgba(99,102,241,0.4);
}

.brand-mark svg {
    width: 18px; height: 18px;
    fill: none; stroke: #fff;
    stroke-width: 2.5;
    stroke-linecap: round; stroke-linejoin: round;
}

.brand-text {
    line-height: 1;
}
.brand-text strong {
    display: block;
    font-size: 15px;
    font-weight: 600;
    color: #F9FAFB;
    letter-spacing: -0.3px;
}
.brand-text span {
    font-size: 10px;
    color: #6B7280;
    letter-spacing: 1px;
    text-transform: uppercase;
    font-weight: 400;
}

.sidebar-nav {
    flex: 1;
    padding: 4px 12px;
    overflow-y: auto;
    scrollbar-width: none;
}
.sidebar-nav::-webkit-scrollbar { display: none; }

.nav-section-label {
    font-size: 9.5px;
    font-weight: 600;
    letter-spacing: 1.2px;
    text-transform: uppercase;
    color: #4B5563;
    padding: 16px 8px 6px;
}

.nav-item-link {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 9px 10px;
    border-radius: 8px;
    text-decoration: none;
    font-size: 13.5px;
    font-weight: 400;
    color: #9CA3AF;
    margin-bottom: 2px;
    transition: background 0.15s, color 0.15s;
    position: relative;
}

.nav-item-link svg { flex-shrink: 0; opacity: 0.7; transition: opacity 0.15s; }
.nav-item-link:hover { background: rgba(255,255,255,0.05); color: #E5E7EB; }
.nav-item-link:hover svg { opacity: 1; }

.nav-item-link.active {
    background: rgba(99,102,241,0.15);
    color: #A5B4FC;
    font-weight: 500;
}
.nav-item-link.active svg { opacity: 1; }
.nav-item-link.active::before {
    content: '';
    position: absolute;
    left: 0; top: 25%; bottom: 25%;
    width: 3px;
    border-radius: 0 3px 3px 0;
    background: #6366F1;
}

.nav-item-link.accent {
    color: #FCD34D;
}
.nav-item-link.accent svg { opacity: 1; }
.nav-item-link.accent:hover { background: rgba(252,211,77,0.1); }
.nav-item-link.accent.active {
    background: rgba(252,211,77,0.15);
    color: #FDE68A;
}
.nav-item-link.accent.active::before { background: #F59E0B; }

.sidebar-footer {
    padding: 12px;
    border-top: 1px solid rgba(255,255,255,0.06);
}

.logout-btn {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 9px 10px;
    border-radius: 8px;
    text-decoration: none;
    font-size: 13px;
    font-weight: 500;
    color: #6B7280;
    transition: background 0.15s, color 0.15s;
}
.logout-btn:hover { background: rgba(239,68,68,0.1); color: #FCA5A5; }
.logout-btn svg { opacity: 0.7; flex-shrink: 0; transition: opacity 0.15s; }
.logout-btn:hover svg { opacity: 1; }
</style>

<aside class="bnet-sidebar">
    <a href="dashboard.php" class="sidebar-brand">
        <div class="brand-mark">
            <svg viewBox="0 0 24 24"><path d="M12 2L2 7l10 5 10-5-10-5z"/><path d="M2 17l10 5 10-5"/><path d="M2 12l10 5 10-5"/></svg>
        </div>
        <div class="brand-text">
            <strong>BandhuNet</strong>
            <span>Alumni System</span>
        </div>
    </a>

    <nav class="sidebar-nav">
        <?php foreach ($nav_groups as $group_name => $items): ?>
            <?php if ($group_name !== 'core'): ?>
                <div class="nav-section-label"><?= $group_name ?></div>
            <?php endif; ?>
            <?php foreach ($items as $item): 
                $is_active = ($current_page == basename($item['href']));
                $classes = 'nav-item-link';
                if ($is_active) $classes .= ' active';
                if (!empty($item['accent'])) $classes .= ' accent';
            ?>
            <a href="<?= $item['href'] ?>" class="<?= $classes ?>">
                <?= feather_icon($item['icon']) ?>
                <?= $item['label'] ?>
            </a>
            <?php endforeach; ?>
        <?php endforeach; ?>
    </nav>

    <div class="sidebar-footer">
        <a href="logout.php" class="logout-btn">
            <?= feather_icon('log-out') ?>
            Keluar Sistem
        </a>
    </div>
</aside>