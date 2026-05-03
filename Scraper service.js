/**
 * scraper-service.js
 * Node.js HTTP service — berjalan di port 3100
 * Digunakan oleh api_scraper.php sebagai backend scraping
 *
 * Cara menjalankan:
 *   npm install express puppeteer-extra puppeteer-extra-plugin-stealth
 *   node scraper-service.js
 *
 * Butuh: Node.js >= 18, Chrome/Chromium terinstall
 */

const express    = require('express');
const puppeteer  = require('puppeteer-extra');
const Stealth    = require('puppeteer-extra-plugin-stealth');

puppeteer.use(Stealth());

const app  = express();
const PORT = 3100;

// ─── Helper: buka browser & halaman baru ─────────────────────────────────────
async function newPage() {
    const browser = await puppeteer.launch({
        headless: 'new',
        args: [
            '--no-sandbox',
            '--disable-setuid-sandbox',
            '--disable-dev-shm-usage',
            '--disable-blink-features=AutomationControlled',
        ],
    });
    const page = await browser.newPage();
    await page.setViewport({ width: 1280, height: 800 });
    await page.setUserAgent(
        'Mozilla/5.0 (Windows NT 10.0; Win64; x64) ' +
        'AppleWebKit/537.36 (KHTML, like Gecko) ' +
        'Chrome/124.0.0.0 Safari/537.36'
    );
    return { browser, page };
}

// ─── Helper: tutup browser dengan aman ───────────────────────────────────────
async function closeBrowser(browser) {
    try { await browser.close(); } catch (_) {}
}

// ─── Helper: bersihkan nama untuk query pencarian ─────────────────────────────
function cleanName(nama) {
    return nama.trim().replace(/\s+/g, ' ');
}

// ─────────────────────────────────────────────────────────────────────────────
// ENDPOINT: /linkedin
// Query params: nama, prodi
// ─────────────────────────────────────────────────────────────────────────────
app.get('/linkedin', async (req, res) => {
    const nama  = cleanName(req.query.nama  || '');
    const prodi = cleanName(req.query.prodi || '');

    if (!nama) return res.json({ found: false, _error: 'Nama kosong' });

    let browser;
    try {
        const query = encodeURIComponent(`${nama} ${prodi} site:linkedin.com/in`);
        const { browser: b, page } = await newPage();
        browser = b;

        // Cari via Google karena LinkedIn blokir langsung
        await page.goto(`https://www.google.com/search?q=${query}`, {
            waitUntil: 'domcontentloaded', timeout: 20000
        });

        // Ambil hasil pertama dari Google yang mengarah ke linkedin.com/in
        const result = await page.evaluate(() => {
            const links = Array.from(document.querySelectorAll('a[href*="linkedin.com/in"]'));
            if (!links.length) return null;
            const a    = links[0];
            const url  = a.href;
            // Coba ambil judul dan snippet dari hasil Google
            const card = a.closest('div[data-hveid]') || a.closest('.g');
            const h3   = card ? card.querySelector('h3') : null;
            const span = card ? card.querySelector('.VwiC3b') : null;
            return {
                profile_url: url,
                headline:    h3   ? h3.innerText   : '',
                snippet:     span ? span.innerText  : '',
            };
        });

        if (!result) return res.json({ found: false });

        // Parse headline untuk ambil posisi & perusahaan
        // Format umum LinkedIn: "Nama — Posisi di Perusahaan"
        const headline = result.headline || '';
        const snippet  = result.snippet  || '';

        // Coba ekstrak perusahaan dari snippet
        const companyMatch = snippet.match(/(?:at|di|@)\s+([A-Z][^\n,·|]+)/i);
        const positionMatch = snippet.match(/^([^·\n|]+)/);

        res.json({
            found:       true,
            profile_url: result.profile_url,
            headline:    headline,
            position:    positionMatch  ? positionMatch[1].trim()  : '',
            company:     companyMatch   ? companyMatch[1].trim()   : '',
            email:       '',   // LinkedIn tidak expose email publik
            phone:       '',
            sektor:      '',
            company_addr:   '',
            company_social: '',
        });

    } catch (err) {
        res.json({ found: false, _error: err.message });
    } finally {
        if (browser) await closeBrowser(browser);
    }
});

// ─────────────────────────────────────────────────────────────────────────────
// ENDPOINT: /instagram
// Query params: nama
// ─────────────────────────────────────────────────────────────────────────────
app.get('/instagram', async (req, res) => {
    const nama = cleanName(req.query.nama || '');
    if (!nama) return res.json({ found: false, _error: 'Nama kosong' });

    let browser;
    try {
        const query = encodeURIComponent(`${nama} site:instagram.com`);
        const { browser: b, page } = await newPage();
        browser = b;

        await page.goto(`https://www.google.com/search?q=${query}`, {
            waitUntil: 'domcontentloaded', timeout: 20000
        });

        const result = await page.evaluate(() => {
            const links = Array.from(document.querySelectorAll('a[href*="instagram.com/"]'));
            // Filter hanya profil (bukan post/reel/explore)
            const profileLinks = links.filter(a =>
                /instagram\.com\/[a-zA-Z0-9._]+\/?$/.test(a.href) &&
                !a.href.includes('/p/') &&
                !a.href.includes('/reel/') &&
                !a.href.includes('/explore/')
            );
            if (!profileLinks.length) return null;
            const a   = profileLinks[0];
            const url = a.href.split('?')[0];
            // Ambil username dari URL
            const match = url.match(/instagram\.com\/([a-zA-Z0-9._]+)/);
            return {
                profile_url: url,
                username:    match ? match[1] : '',
            };
        });

        if (!result || !result.username) return res.json({ found: false });

        res.json({
            found:       true,
            profile_url: result.profile_url,
            username:    result.username,
        });

    } catch (err) {
        res.json({ found: false, _error: err.message });
    } finally {
        if (browser) await closeBrowser(browser);
    }
});

// ─────────────────────────────────────────────────────────────────────────────
// ENDPOINT: /facebook
// Query params: nama
// ─────────────────────────────────────────────────────────────────────────────
app.get('/facebook', async (req, res) => {
    const nama = cleanName(req.query.nama || '');
    if (!nama) return res.json({ found: false, _error: 'Nama kosong' });

    let browser;
    try {
        const query = encodeURIComponent(`${nama} site:facebook.com`);
        const { browser: b, page } = await newPage();
        browser = b;

        await page.goto(`https://www.google.com/search?q=${query}`, {
            waitUntil: 'domcontentloaded', timeout: 20000
        });

        const result = await page.evaluate(() => {
            const links = Array.from(document.querySelectorAll('a[href*="facebook.com/"]'));
            const profileLinks = links.filter(a =>
                /facebook\.com\/(?!sharer|share|dialog|pages\/category)[a-zA-Z0-9.]+\/?/.test(a.href)
            );
            if (!profileLinks.length) return null;
            const url = profileLinks[0].href.split('?')[0];
            return { profile_url: url };
        });

        if (!result) return res.json({ found: false });

        res.json({
            found:       true,
            profile_url: result.profile_url,
        });

    } catch (err) {
        res.json({ found: false, _error: err.message });
    } finally {
        if (browser) await closeBrowser(browser);
    }
});

// ─────────────────────────────────────────────────────────────────────────────
// ENDPOINT: /tiktok
// Query params: nama
// ─────────────────────────────────────────────────────────────────────────────
app.get('/tiktok', async (req, res) => {
    const nama = cleanName(req.query.nama || '');
    if (!nama) return res.json({ found: false, _error: 'Nama kosong' });

    let browser;
    try {
        const query = encodeURIComponent(`${nama} site:tiktok.com/@`);
        const { browser: b, page } = await newPage();
        browser = b;

        await page.goto(`https://www.google.com/search?q=${query}`, {
            waitUntil: 'domcontentloaded', timeout: 20000
        });

        const result = await page.evaluate(() => {
            const links = Array.from(document.querySelectorAll('a[href*="tiktok.com/@"]'));
            if (!links.length) return null;
            const url   = links[0].href.split('?')[0];
            const match = url.match(/tiktok\.com\/@([a-zA-Z0-9._]+)/);
            return {
                profile_url: url,
                username:    match ? match[1] : '',
            };
        });

        if (!result || !result.username) return res.json({ found: false });

        res.json({
            found:       true,
            profile_url: result.profile_url,
            username:    result.username,
        });

    } catch (err) {
        res.json({ found: false, _error: err.message });
    } finally {
        if (browser) await closeBrowser(browser);
    }
});

// ─────────────────────────────────────────────────────────────────────────────
// ENDPOINT: /google
// Query params: nama, prodi, lulus
// Cari info karir/kontak dari hasil pencarian Google umum
// ─────────────────────────────────────────────────────────────────────────────
app.get('/google', async (req, res) => {
    const nama  = cleanName(req.query.nama  || '');
    const prodi = cleanName(req.query.prodi || '');
    const lulus = cleanName(req.query.lulus || '');

    if (!nama) return res.json({ found: false, _error: 'Nama kosong' });

    let browser;
    try {
        // Query lebih spesifik dengan konteks alumni
        const queryStr = [nama, prodi, lulus].filter(Boolean).join(' ');
        const query    = encodeURIComponent(queryStr);
        const { browser: b, page } = await newPage();
        browser = b;

        await page.goto(`https://www.google.com/search?q=${query}`, {
            waitUntil: 'domcontentloaded', timeout: 20000
        });

        const data = await page.evaluate(() => {
            const results = [];
            document.querySelectorAll('.g').forEach(el => {
                const titleEl   = el.querySelector('h3');
                const snippetEl = el.querySelector('.VwiC3b');
                const linkEl    = el.querySelector('a[href^="http"]');
                if (titleEl && snippetEl) {
                    results.push({
                        title:   titleEl.innerText,
                        snippet: snippetEl.innerText,
                        url:     linkEl ? linkEl.href : '',
                    });
                }
            });
            return results.slice(0, 5);
        });

        if (!data.length) return res.json({ found: false });

        // Gabungkan semua teks hasil untuk diekstrak
        const allText = data.map(d => `${d.title} ${d.snippet}`).join(' ');

        // Ekstrak email
        const emailMatch = allText.match(/[a-zA-Z0-9._%+\-]+@[a-zA-Z0-9.\-]+\.[a-zA-Z]{2,}/);

        // Ekstrak nomor HP Indonesia
        const phoneMatch = allText.match(/(?:\+62|62|0)[8][1-9][0-9]{7,10}/);

        // Coba deteksi nama perusahaan dari snippet
        const companyMatch = allText.match(
            /(?:bekerja di|works at|employed at|CEO|CTO|Manager|Engineer|Direktur|Kepala|Staff)\s+(?:at\s+)?([A-Z][a-zA-Z\s&,.]+?)(?:\s*[·|,\n]|$)/
        );

        // Deteksi posisi/jabatan
        const positionMatch = allText.match(
            /\b(CEO|CTO|CFO|Direktur|Manager|Engineer|Developer|Programmer|Konsultan|Dosen|Guru|PNS|ASN|Wirausaha|Pengusaha|Staff|Analis|Koordinator)[a-zA-Z\s]*/i
        );

        res.json({
            found:        true,
            email:        emailMatch    ? emailMatch[0]       : '',
            phone:        phoneMatch    ? phoneMatch[0]       : '',
            company:      companyMatch  ? companyMatch[1].trim() : '',
            position:     positionMatch ? positionMatch[0].trim() : '',
            sektor:       '',
            company_addr: '',
            company_social: '',
            snippet:      data[0]?.snippet || '',
            sources:      data.map(d => d.url),
        });

    } catch (err) {
        res.json({ found: false, _error: err.message });
    } finally {
        if (browser) await closeBrowser(browser);
    }
});

// ─────────────────────────────────────────────────────────────────────────────
// Health check
// ─────────────────────────────────────────────────────────────────────────────
app.get('/health', (req, res) => {
    res.json({ status: 'ok', port: PORT, time: new Date().toISOString() });
});

// ─────────────────────────────────────────────────────────────────────────────
// Start server
// ─────────────────────────────────────────────────────────────────────────────
app.listen(PORT, '127.0.0.1', () => {
    console.log(`✅ Scraper service berjalan di http://127.0.0.1:${PORT}`);
    console.log(`   Endpoint tersedia:`);
    console.log(`   GET /health`);
    console.log(`   GET /linkedin?nama=...&prodi=...`);
    console.log(`   GET /instagram?nama=...`);
    console.log(`   GET /facebook?nama=...`);
    console.log(`   GET /tiktok?nama=...`);
    console.log(`   GET /google?nama=...&prodi=...&lulus=...`);
});