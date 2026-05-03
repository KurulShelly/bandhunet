/**
 * BandhuNet Scraper Service
 * Node.js + Puppeteer — berjalan di port 3100
 * 
 * Instalasi:
 *   cd scraper-service
 *   npm install
 *   node server.js
 *
 * Atau dengan PM2 agar tetap berjalan:
 *   npm install -g pm2
 *   pm2 start server.js --name bandhunet-scraper
 *   pm2 save && pm2 startup
 */

const express    = require('express');
const puppeteer  = require('puppeteer-extra');
const Stealth    = require('puppeteer-extra-plugin-stealth');
const https      = require('https');
const app        = express();

puppeteer.use(Stealth());

const PORT        = 3100;
const HEADLESS    = true;  // false untuk debug visual
const TIMEOUT     = 20000;
const DELAY_MIN   = 800;
const DELAY_MAX   = 2200;

// ── Helpers ─────────────────────────────────────────────────────────────────
const delay   = (ms) => new Promise(r => setTimeout(r, ms));
const randMs  = (min = DELAY_MIN, max = DELAY_MAX) => Math.floor(Math.random() * (max - min) + min);
const slug    = (s) => s.toLowerCase().replace(/[^a-z0-9\s]/g,'').trim().replace(/\s+/g,'+');
const logTime = () => new Date().toISOString().split('T')[1].slice(0,8);
const log     = (label, msg) => console.log(`[${logTime()}] [${label}] ${msg}`);

// ── Browser pool (singleton) ─────────────────────────────────────────────────
let browser = null;
async function getBrowser() {
    if (!browser || !browser.isConnected()) {
        browser = await puppeteer.launch({
            headless: HEADLESS,
            args: [
                '--no-sandbox',
                '--disable-setuid-sandbox',
                '--disable-dev-shm-usage',
                '--disable-blink-features=AutomationControlled',
                '--window-size=1366,768',
            ],
        });
        log('BROWSER', 'Browser instance launched');
    }
    return browser;
}

// ── Open a stealth page ──────────────────────────────────────────────────────
async function newPage() {
    const b    = await getBrowser();
    const page = await b.newPage();
    await page.setViewport({ width: 1366, height: 768 });
    await page.setExtraHTTPHeaders({ 'Accept-Language': 'id-ID,id;q=0.9,en;q=0.8' });
    return page;
}

// ── Google fetcher (no Puppeteer, use HTTPS directly) ──────────────────────
function googleSearch(query) {
    return new Promise((resolve) => {
        const url = `https://www.google.com/search?q=${encodeURIComponent(query)}&num=5&hl=id`;
        const options = {
            hostname: 'www.google.com',
            path: `/search?q=${encodeURIComponent(query)}&num=5&hl=id`,
            method: 'GET',
            headers: {
                'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120 Safari/537.36',
                'Accept': 'text/html',
                'Accept-Language': 'id-ID,id;q=0.9',
            },
        };
        let data = '';
        const req = https.request(options, (res) => {
            res.on('data', c => data += c);
            res.on('end', () => resolve(data));
        });
        req.on('error', () => resolve(''));
        req.setTimeout(10000, () => { req.destroy(); resolve(''); });
        req.end();
    });
}

// ────────────────────────────────────────────────────────────────────────────
// 1. LINKEDIN SCRAPER
// GET /linkedin?nama=X&prodi=Y
// ────────────────────────────────────────────────────────────────────────────
app.get('/linkedin', async (req, res) => {
    const { nama = '', prodi = '' } = req.query;
    log('LINKEDIN', `Searching: ${nama} | ${prodi}`);

    const result = {
        found: false, profile_url: '', headline: '', company: '',
        company_addr: '', position: '', sektor: '', email: '',
        phone: '', company_social: '',
    };

    let page;
    try {
        page = await newPage();

        // Cari via Google dulu — lebih reliable daripada langsung ke LinkedIn
        const query   = `site:linkedin.com/in "${nama}" ${prodi} Indonesia`;
        const gHtml   = await googleSearch(query);

        // Extract LinkedIn URL dari hasil Google
        const liMatch = gHtml.match(/linkedin\.com\/in\/([a-zA-Z0-9\-_%]+)/);
        if (liMatch) {
            const profileSlug = liMatch[1].split('"')[0].split('\\')[0];
            const profileUrl  = `https://www.linkedin.com/in/${profileSlug}`;
            result.profile_url = profileUrl;
            log('LINKEDIN', `Found URL: ${profileUrl}`);

            // Buka profil LinkedIn (bisa di-skip kalau perlu login)
            // Gunakan Google cache atau metode lain agar tidak perlu login
            const cacheUrl = `https://webcache.googleusercontent.com/search?q=cache:${encodeURIComponent(profileUrl)}`;
            await page.goto(cacheUrl, { waitUntil: 'domcontentloaded', timeout: TIMEOUT });
            await delay(randMs(500, 1200));

            // Extract info dari cache
            const pageText = await page.evaluate(() => document.body?.innerText || '');

            // Headline / jabatan
            const hlMatch = pageText.match(/–\s*([^\n]{5,80})/);
            if (hlMatch) result.headline = hlMatch[1].trim();

            // Company
            const coMatch = pageText.match(/(?:at|di|@)\s+([A-Z][^\n]{3,60})/i);
            if (coMatch) result.company = coMatch[1].trim();

            // Tentukan sektor
            const lower = pageText.toLowerCase();
            if (lower.includes('pns') || lower.includes('pegawai negeri') || lower.includes('asn') || lower.includes('pemerintah')) {
                result.sektor = 'PNS / ASN';
            } else if (lower.includes('wirausaha') || lower.includes('founder') || lower.includes('ceo') || lower.includes('owner') || lower.includes('entrepreneur')) {
                result.sektor = 'Wirausaha';
            } else if (result.company) {
                result.sektor = 'Swasta';
            }

            result.found = !!(result.headline || result.company);
        }

    } catch (e) {
        log('LINKEDIN', `Error: ${e.message}`);
    } finally {
        if (page) await page.close().catch(() => {});
    }

    res.json(result);
});

// ────────────────────────────────────────────────────────────────────────────
// 2. INSTAGRAM SCRAPER
// GET /instagram?nama=X
// ────────────────────────────────────────────────────────────────────────────
app.get('/instagram', async (req, res) => {
    const { nama = '' } = req.query;
    log('INSTAGRAM', `Searching: ${nama}`);

    const result = { found: false, username: '', profile_url: '', bio: '' };

    let page;
    try {
        page = await newPage();

        // Google → site:instagram.com
        const gHtml = await googleSearch(`site:instagram.com "${nama}" Indonesia`);
        const igMatch = gHtml.match(/instagram\.com\/([a-zA-Z0-9_\.]{3,30})\/?[^a-zA-Z0-9_\.]/);
        if (igMatch) {
            const username = igMatch[1];
            if (!['p', 'explore', 'reels', 'stories', 'accounts'].includes(username)) {
                result.username    = username;
                result.profile_url = `https://instagram.com/${username}`;
                result.found       = true;
                log('INSTAGRAM', `Found: @${username}`);
            }
        }

        // Kalau belum ketemu, coba scrape langsung
        if (!result.found) {
            const igQuery = slug(nama.split(' ').slice(0,2).join(' '));
            await page.goto(`https://www.instagram.com/web/search/topsearch/?context=user&query=${igQuery}`, {
                waitUntil: 'networkidle2', timeout: TIMEOUT,
            });
            await delay(randMs());

            const raw  = await page.evaluate(() => document.body?.innerText || '');
            const json = (() => { try { return JSON.parse(raw); } catch { return null; } })();

            if (json?.users?.length) {
                const match = json.users.find(u =>
                    u.user?.full_name?.toLowerCase().includes(nama.toLowerCase().split(' ')[0])
                );
                if (match) {
                    result.username    = match.user.username;
                    result.profile_url = `https://instagram.com/${match.user.username}`;
                    result.bio         = match.user.biography || '';
                    result.found       = true;
                    log('INSTAGRAM', `Found via API: @${result.username}`);
                }
            }
        }

    } catch (e) {
        log('INSTAGRAM', `Error: ${e.message}`);
    } finally {
        if (page) await page.close().catch(() => {});
    }

    res.json(result);
});

// ────────────────────────────────────────────────────────────────────────────
// 3. FACEBOOK SCRAPER
// GET /facebook?nama=X
// ────────────────────────────────────────────────────────────────────────────
app.get('/facebook', async (req, res) => {
    const { nama = '' } = req.query;
    log('FACEBOOK', `Searching: ${nama}`);

    const result = { found: false, profile_url: '', bio: '' };

    try {
        const gHtml   = await googleSearch(`site:facebook.com "${nama}" Indonesia`);
        const fbMatch = gHtml.match(/facebook\.com\/([a-zA-Z0-9\.\-]{5,50})\/?[^a-zA-Z0-9\.\-]/);
        if (fbMatch) {
            const handle = fbMatch[1];
            if (!['groups', 'pages', 'events', 'photo', 'video', 'watch', 'marketplace', 'gaming'].includes(handle)) {
                result.profile_url = `https://facebook.com/${handle}`;
                result.found       = true;
                log('FACEBOOK', `Found: facebook.com/${handle}`);
            }
        }
    } catch (e) {
        log('FACEBOOK', `Error: ${e.message}`);
    }

    res.json(result);
});

// ────────────────────────────────────────────────────────────────────────────
// 4. TIKTOK SCRAPER
// GET /tiktok?nama=X
// ────────────────────────────────────────────────────────────────────────────
app.get('/tiktok', async (req, res) => {
    const { nama = '' } = req.query;
    log('TIKTOK', `Searching: ${nama}`);

    const result = { found: false, username: '', profile_url: '' };

    try {
        const gHtml   = await googleSearch(`site:tiktok.com "@" "${nama}"`);
        const tkMatch = gHtml.match(/tiktok\.com\/@([a-zA-Z0-9_\.]{3,30})/);
        if (tkMatch) {
            result.username    = tkMatch[1];
            result.profile_url = `https://tiktok.com/@${result.username}`;
            result.found       = true;
            log('TIKTOK', `Found: @${result.username}`);
        }
    } catch (e) {
        log('TIKTOK', `Error: ${e.message}`);
    }

    res.json(result);
});

// ────────────────────────────────────────────────────────────────────────────
// 5. GOOGLE SEARCH — info karir, email, kantor
// GET /google?nama=X&prodi=Y&lulus=Z
// ────────────────────────────────────────────────────────────────────────────
app.get('/google', async (req, res) => {
    const { nama = '', prodi = '', lulus = '' } = req.query;
    log('GOOGLE', `Searching: ${nama} | ${prodi} | ${lulus}`);

    const result = {
        found: false, snippet: '',
        email: '', phone: '', company: '', company_addr: '',
        position: '', sektor: '', company_social: '',
    };

    let page;
    try {
        page = await newPage();

        // Query karir / pekerjaan
        const q1 = `"${nama}" alumni ${prodi} ${lulus} kerja`;
        await page.goto(`https://www.google.com/search?q=${encodeURIComponent(q1)}&hl=id`, {
            waitUntil: 'domcontentloaded', timeout: TIMEOUT,
        });
        await delay(randMs(600, 1400));

        const bodyText = await page.evaluate(() => document.body?.innerText || '');

        // Email
        const emailMatch = bodyText.match(/[\w.+-]+@[\w-]+\.[a-z]{2,}/i);
        if (emailMatch) result.email = emailMatch[0];

        // Phone — format Indonesia
        const phoneMatch = bodyText.match(/(?:\+62|08)[0-9]{8,12}/);
        if (phoneMatch) result.phone = phoneMatch[0];

        // Company dari snippet
        const coMatch = bodyText.match(/(?:bekerja di|works at|at)\s+([A-Z][^\n.]{3,60})/i);
        if (coMatch) result.company = coMatch[1].trim();

        // Position
        const posMatch = bodyText.match(/(?:sebagai|as|jabatan|posisi)\s+([^\n.]{5,60})/i);
        if (posMatch) result.position = posMatch[1].trim();

        // Snippet (ringkasan)
        const snippetLines = bodyText.split('\n').filter(l => l.includes(nama.split(' ')[0]));
        if (snippetLines.length) result.snippet = snippetLines[0].slice(0, 120);

        // Sektor
        const lower = bodyText.toLowerCase();
        if (lower.includes('pns') || lower.includes('asn') || lower.includes('pegawai negeri') || lower.includes('pemda') || lower.includes('kementerian')) {
            result.sektor = 'PNS / ASN';
        } else if (lower.includes('wirausaha') || lower.includes('usaha') || lower.includes('founder') || lower.includes('owner') || lower.includes('wiraswasta')) {
            result.sektor = 'Wirausaha';
        } else if (result.company) {
            result.sektor = 'Swasta';
        }

        // Social kantor
        const coSocial = bodyText.match(/(?:linkedin|instagram|facebook)\.com\/(?:company|in|pg)\/([a-zA-Z0-9\-_.]+)/i);
        if (coSocial) result.company_social = coSocial[0];

        result.found = !!(result.email || result.company || result.position || result.snippet);

    } catch (e) {
        log('GOOGLE', `Error: ${e.message}`);
    } finally {
        if (page) await page.close().catch(() => {});
    }

    res.json(result);
});

// ── Health check ─────────────────────────────────────────────────────────────
app.get('/health', (_, res) => res.json({ status: 'ok', time: new Date().toISOString() }));

// ── Shutdown gracefully ───────────────────────────────────────────────────────
process.on('SIGINT',  async () => { if (browser) await browser.close(); process.exit(0); });
process.on('SIGTERM', async () => { if (browser) await browser.close(); process.exit(0); });

// ── Start ────────────────────────────────────────────────────────────────────
app.listen(PORT, '127.0.0.1', () => {
    console.log(`\n✅ BandhuNet Scraper Service berjalan di http://127.0.0.1:${PORT}`);
    console.log(`   Endpoints: /linkedin /instagram /facebook /tiktok /google /health\n`);
});
