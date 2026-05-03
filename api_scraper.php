<?php
/**
 * api_scraper.php — BandhuNet Profiling Engine
 *
 * KENAPA DATA TIDAK MUNCUL — 3 masalah utama:
 * 1. Node.js scraper (port 3100) belum/tidak berjalan → callScraper() selalu gagal
 * 2. LinkedIn/Instagram/FB memblokir scraping langsung
 * 3. PDDIKTI API endpoint berubah format
 *
 * SOLUSI di file ini:
 * - Semua sumber menggunakan DuckDuckGo HTML search sebagai fallback utama
 * - Node.js TETAP dipakai kalau berjalan, tapi tidak wajib
 * - PDDIKTI memakai API v2 resmi
 */

session_start();
if (!isset($_SESSION['id_user'])) {
    http_response_code(403);
    die(json_encode(['error' => 'Unauthorized']));
}

header('Content-Type: application/json; charset=utf-8');
set_time_limit(60);

$action = trim($_GET['action'] ?? '');

// ── cURL helper ────────────────────────────────────────────────────────────
function curlGet(string $url, array $extra_headers = [], int $timeout = 15): array {
    if (!function_exists('curl_init')) {
        return ['body' => '', 'code' => 0, 'err' => 'curl tidak aktif'];
    }
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => $timeout,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_MAXREDIRS      => 4,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_ENCODING       => '',
        CURLOPT_HTTPHEADER     => array_merge([
            'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0.0.0 Safari/537.36',
            'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
            'Accept-Language: id-ID,id;q=0.9,en;q=0.8',
            'Accept-Encoding: gzip, deflate, br',
        ], $extra_headers),
    ]);
    $body = curl_exec($ch);
    $code = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $err  = curl_error($ch);
    curl_close($ch);
    return ['body' => (string)$body, 'code' => $code, 'err' => $err];
}

// ── Cek Node.js scraper ────────────────────────────────────────────────────
function scraperRunning(): bool {
    static $ok = null;
    if ($ok !== null) return $ok;
    $r = curlGet('http://127.0.0.1:3100/health', [], 2);
    $ok = ($r['code'] === 200);
    return $ok;
}

function callScraper(string $ep, array $params): ?array {
    if (!scraperRunning()) return null;
    $r = curlGet('http://127.0.0.1:3100/' . $ep . '?' . http_build_query($params), [], 25);
    if ($r['err'] || $r['code'] !== 200) return null;
    $d = json_decode($r['body'], true);
    return is_array($d) ? $d : null;
}

// ── DuckDuckGo HTML search fallback ───────────────────────────────────────
function duckSearch(string $query): array {
    $url = 'https://html.duckduckgo.com/html/?q=' . urlencode($query) . '&kl=id-id';
    $res = curlGet($url, ['Referer: https://duckduckgo.com/'], 12);
    if ($res['code'] !== 200 || empty($res['body'])) return [];

    $html = $res['body'];
    $results = [];

    // Ambil semua result block
    preg_match_all('/<div class="result__body">(.*?)<\/div>\s*<\/div>/si', $html, $blocks);
    foreach (($blocks[1] ?? []) as $block) {
        // URL
        preg_match('/uddg=([^"&]+)/i', $block, $um);
        $url = urldecode($um[1] ?? '');
        // Title
        preg_match('/<a[^>]+class="result__a"[^>]*>(.*?)<\/a>/si', $block, $tm);
        $title = strip_tags($tm[1] ?? '');
        // Snippet
        preg_match('/<a[^>]+class="result__snippet"[^>]*>(.*?)<\/a>/si', $block, $sm);
        $snippet = strip_tags($sm[1] ?? '');

        if ($url) {
            $results[] = ['url' => $url, 'title' => $title, 'snippet' => $snippet];
        }
    }

    // Fallback: ambil semua uddg links
    if (empty($results)) {
        preg_match_all('/uddg=([^"&\s]+)/i', $html, $allU);
        foreach (array_unique($allU[1] ?? []) as $enc) {
            $u = urldecode($enc);
            if (str_starts_with($u, 'http')) {
                $results[] = ['url' => $u, 'title' => '', 'snippet' => ''];
            }
        }
    }

    return array_slice($results, 0, 8);
}

// ── 1. PDDIKTI ─────────────────────────────────────────────────────────────
function searchPddikti(string $nim, string $nama): array {
    $keyword = trim($nim ?: $nama);
    if (empty($keyword)) return ['found' => false];

    // Coba beberapa endpoint
    $endpoints = [
        "https://api2.pddikti.kemdikbud.go.id/search/mhs/" . urlencode($keyword),
        "https://api-frontend.kemdikbud.go.id/hit/"         . urlencode($keyword) . "/mahasiswa",
    ];
    // Kalau NIM dicoba dulu, fallback ke nama
    if ($nim && $nama && $nim !== $nama) {
        $endpoints[] = "https://api2.pddikti.kemdikbud.go.id/search/mhs/" . urlencode($nama);
        $endpoints[] = "https://api-frontend.kemdikbud.go.id/hit/"         . urlencode($nama) . "/mahasiswa";
    }

    foreach ($endpoints as $url) {
        $res = curlGet($url, [
            'Accept: application/json',
            'Origin: https://pddikti.kemdikbud.go.id',
            'Referer: https://pddikti.kemdikbud.go.id/',
        ], 10);

        if ($res['code'] !== 200 || empty($res['body'])) { usleep(200000); continue; }

        $json = json_decode($res['body'], true);
        if (!is_array($json) || empty($json)) { continue; }

        // Normalisasi berbagai format
        $list = $json['mahasiswa']      ?? $json['data_mahasiswa'] ?? null;
        if (!$list && isset($json[0]))  $list = $json;
        if (!$list && isset($json['text'])) $list = [$json];
        if (empty($list))               continue;

        $f = $list[0];

        // Format pipe-separated
        if (isset($f['text'])) {
            $p = array_map('trim', explode('|', $f['text']));
            return ['found'=>true,'nama'=>$p[0]??'','nim'=>$p[1]??$nim,'nama_pt'=>$p[2]??'','prodi'=>$p[3]??'','status'=>$p[4]??''];
        }

        return [
            'found'   => true,
            'nama'    => $f['nama']       ?? $f['nm_pd']   ?? '',
            'nim'     => $f['nim']        ?? $f['nipd']    ?? $nim,
            'nama_pt' => $f['nama_pt']    ?? $f['nm_lemb'] ?? $f['nama_perguruan_tinggi'] ?? '',
            'prodi'   => $f['nama_prodi'] ?? $f['nm_prg']  ?? $f['prodi'] ?? '',
            'status'  => $f['jenjang']    ?? $f['sts_mhs'] ?? $f['status'] ?? '',
        ];
    }

    return ['found' => false];
}

// ── 2. LINKEDIN ─────────────────────────────────────────────────────────────
function searchLinkedin(string $nama, string $prodi): array {
    // Node.js dulu
    $s = callScraper('linkedin', ['nama'=>$nama,'prodi'=>$prodi]);
    if ($s && !empty($s['found'])) return $s;

    $parts   = explode(' ', $nama);
    $keyword = implode(' ', array_slice($parts, 0, 3));
    $results = duckSearch("site:linkedin.com/in \"$keyword\" Indonesia");

    foreach ($results as $r) {
        if (!str_contains($r['url'], 'linkedin.com/in/')) continue;
        preg_match('#linkedin\.com/in/([a-zA-Z0-9%\-_.]+)#i', $r['url'], $m);
        if (empty($m[1])) continue;

        $slug    = rawurldecode(explode('?',$m[1])[0]);
        $profUrl = 'https://www.linkedin.com/in/' . $slug;
        $text    = $r['title'] . ' ' . $r['snippet'];
        $textL   = strtolower($text);

        // Perusahaan
        preg_match('/(?:at|di|@)\s+([A-Z][^\s|·\-]{2,40})/u', $text, $cm);
        $company = trim($cm[1] ?? '');

        // Jabatan: ambil sebelum " - " atau " at " atau " | "
        preg_match('/^([^|\-·]{5,60})(?:\s*[-|·]|\s+at\s|\s+di\s)/ui', $r['snippet'], $pm);
        $position = trim($pm[1] ?? '');

        // Sektor
        if (preg_match('/\b(pns|asn|pemerintah|kementerian|pemda|dinas)\b/i', $text)) $sektor = 'PNS';
        elseif (preg_match('/\b(wirausaha|founder|owner|wiraswasta|entrepreneur)\b/i', $text)) $sektor = 'Wirausaha';
        elseif ($company) $sektor = 'Swasta';
        else              $sektor = 'Lainnya';

        return [
            'found'          => true,
            'profile_url'    => $profUrl,
            'headline'       => substr($text, 0, 120),
            'company'        => $company,
            'position'       => $position,
            'sektor'         => $sektor,
            'email'          => '',
            'phone'          => '',
            'company_addr'   => '',
            'company_social' => '',
        ];
    }
    return ['found' => false];
}

// ── 3. INSTAGRAM ─────────────────────────────────────────────────────────────
function searchInstagram(string $nama): array {
    $s = callScraper('instagram', ['nama'=>$nama]);
    if ($s && !empty($s['found'])) return $s;

    $parts   = explode(' ', $nama);
    $keyword = implode(' ', array_slice($parts, 0, 2));
    $skip    = ['p','explore','reels','stories','accounts','reel','tv','about','help'];
    $results = duckSearch("site:instagram.com \"$keyword\"");

    foreach ($results as $r) {
        if (!str_contains($r['url'], 'instagram.com/')) continue;
        preg_match('#instagram\.com/([a-zA-Z0-9_.]{3,30})/?#', $r['url'], $m);
        if (empty($m[1]) || in_array(strtolower($m[1]), $skip)) continue;
        if (!str_contains(strtolower($r['snippet'].$r['title']), strtolower($parts[0]))) continue;

        return ['found'=>true,'username'=>$m[1],'profile_url'=>'https://instagram.com/'.$m[1],'bio'=>strip_tags($r['snippet'])];
    }
    return ['found' => false];
}

// ── 4. FACEBOOK ──────────────────────────────────────────────────────────────
function searchFacebook(string $nama): array {
    $s = callScraper('facebook', ['nama'=>$nama]);
    if ($s && !empty($s['found'])) return $s;

    $parts   = explode(' ', $nama);
    $keyword = implode(' ', array_slice($parts, 0, 2));
    $skip    = ['groups','pages','events','photo','photos','video','watch','marketplace','gaming','login','help','about','hashtag'];
    $results = duckSearch("site:facebook.com \"$keyword\"");

    foreach ($results as $r) {
        if (!str_contains($r['url'], 'facebook.com/')) continue;
        preg_match('#facebook\.com/([a-zA-Z0-9.\-]{4,60})/?#', $r['url'], $m);
        if (empty($m[1]) || in_array(strtolower($m[1]), $skip)) continue;
        if (!str_contains(strtolower($r['snippet'].$r['title']), strtolower($parts[0]))) continue;

        return ['found'=>true,'profile_url'=>'https://facebook.com/'.$m[1],'bio'=>strip_tags($r['snippet'])];
    }
    return ['found' => false];
}

// ── 5. TIKTOK ────────────────────────────────────────────────────────────────
function searchTiktok(string $nama): array {
    $s = callScraper('tiktok', ['nama'=>$nama]);
    if ($s && !empty($s['found'])) return $s;

    $parts   = explode(' ', $nama);
    $keyword = implode(' ', array_slice($parts, 0, 2));
    $results = duckSearch("site:tiktok.com \"$keyword\"");

    foreach ($results as $r) {
        preg_match('#tiktok\.com/@([a-zA-Z0-9_.]{3,30})#', $r['url'], $m);
        if (empty($m[1])) continue;
        if (!str_contains(strtolower($r['snippet'].$r['title']), strtolower($parts[0]))) continue;

        return ['found'=>true,'username'=>$m[1],'profile_url'=>'https://tiktok.com/@'.$m[1]];
    }
    return ['found' => false];
}

// ── 6. GOOGLE (info karir) ────────────────────────────────────────────────────
function searchGoogle(string $nama, string $prodi, string $lulus): array {
    $s = callScraper('google', ['nama'=>$nama,'prodi'=>$prodi,'lulus'=>$lulus]);
    if ($s && !empty($s['found'])) return $s;

    $parts   = explode(' ', $nama);
    $keyword = '"' . implode(' ', array_slice($parts, 0, 3)) . '"';
    $q       = "$keyword alumni" . ($prodi ? " $prodi" : '') . ($lulus ? " $lulus" : '');
    $results = duckSearch($q);

    $email = $phone = $company = $position = $sektor = $cosocial = $snippet_out = '';

    foreach ($results as $r) {
        $text  = strip_tags($r['title'] . ' ' . $r['snippet']);
        $textL = strtolower($text);

        if (!$email)   { preg_match('/[\w.+\-]+@[\w\-]+\.[a-z]{2,}/i', $text, $em); if (!empty($em[0])) $email = $em[0]; }
        if (!$phone)   { preg_match('/(?:\+62|08)[0-9]{8,12}/', $text, $pm); if (!empty($pm[0])) $phone = $pm[0]; }
        if (!$company) { preg_match('/(?:bekerja di|works? at|di perusahaan|Manager di)\s+([A-Z][^\n.,|]{3,50})/ui', $text, $cm); if (!empty($cm[1])) $company = trim($cm[1]); }
        if (!$position){ preg_match('/(?:sebagai|as a?|jabatan|posisi)\s+([^\n.,|]{5,60})/ui', $text, $posm); if (!empty($posm[1])) $position = trim($posm[1]); }
        if (!$sektor) {
            if (preg_match('/\b(pns|asn|pegawai negeri|kementerian|pemda|dinas)\b/i', $text))        $sektor = 'PNS';
            elseif (preg_match('/\b(wirausaha|wiraswasta|founder|owner|entrepreneur)\b/i', $text))   $sektor = 'Wirausaha';
            elseif ($company) $sektor = 'Swasta';
        }
        if (!$cosocial) { preg_match('#(?:linkedin|instagram|facebook)\.com/(?:company|in|pg)/([a-zA-Z0-9\-_]+)#i', $r['url'], $csm); if (!empty($csm[0])) $cosocial = 'https://'.$csm[0]; }
        if (!$snippet_out && str_contains($textL, strtolower($parts[0]))) $snippet_out = substr($text, 0, 120);
    }

    $found = !empty($email) || !empty($company) || !empty($position) || !empty($snippet_out);
    return [
        'found'          => $found,
        'snippet'        => $snippet_out,
        'email'          => $email,
        'phone'          => $phone,
        'company'        => $company,
        'company_addr'   => '',
        'position'       => $position,
        'sektor'         => $sektor ?: ($company ? 'Swasta' : 'Lainnya'),
        'company_social' => $cosocial,
    ];
}

// ── ROUTER ─────────────────────────────────────────────────────────────────
switch ($action) {
    case 'pddikti':
        echo json_encode(searchPddikti(trim($_GET['nim']??''), trim($_GET['nama']??'')));
        break;
    case 'linkedin':
        echo json_encode(searchLinkedin(trim($_GET['nama']??''), trim($_GET['prodi']??'')));
        break;
    case 'instagram':
        echo json_encode(searchInstagram(trim($_GET['nama']??'')));
        break;
    case 'facebook':
        echo json_encode(searchFacebook(trim($_GET['nama']??'')));
        break;
    case 'tiktok':
        echo json_encode(searchTiktok(trim($_GET['nama']??'')));
        break;
    case 'google':
        echo json_encode(searchGoogle(trim($_GET['nama']??''), trim($_GET['prodi']??''), trim($_GET['lulus']??'')));
        break;

    // ── Test endpoint: buka di browser ─────────────────────────────────────
    // api_scraper.php?action=test&nim=12345&nama=Budi+Santoso
    case 'test':
        $nim  = trim($_GET['nim']  ?? '');
        $nama = trim($_GET['nama'] ?? '');
        echo json_encode([
            'node_scraper_running' => scraperRunning(),
            'curl_enabled'         => function_exists('curl_init'),
            'pddikti_result'       => searchPddikti($nim, $nama),
            'google_result'        => searchGoogle($nama, '', ''),
            'linkedin_result'      => searchLinkedin($nama, ''),
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        break;

    default:
        http_response_code(400);
        echo json_encode(['error' => 'Action tidak dikenal']);
}