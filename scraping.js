const puppeteer = require('puppeteer');
const mysql = require('mysql2/promise');

(async () => {
  try {
    // 🔗 Koneksi ke database
    const db = await mysql.createConnection({
      host: 'localhost',
      user: 'root',
      password: '',
      database: 'db_bandhunet'
    });

    console.log("✅ Database terhubung");

    // 🤖 Jalankan browser
    const browser = await puppeteer.launch({
      headless: false // ubah ke true kalau mau tanpa tampilan
    });

    const page = await browser.newPage();

    // 📥 Ambil data yang belum ada linkedin
    const [rows] = await db.execute(`
      SELECT id, nama FROM alumni 
      WHERE linkedin IS NULL OR linkedin = ''
    `);

    console.log(`🔍 Data ditemukan: ${rows.length}`);

    // 🔄 Loop data
    for (let d of rows) {
      const nama = d.nama;
      console.log("Cari:", nama);

      // Cari di Google
      await page.goto(`https://www.google.com/search?q=${encodeURIComponent(nama + " linkedin")}`, {
        waitUntil: 'domcontentloaded'
      });

      // Ambil link pertama
      const link = await page.evaluate(() => {
        const a = document.querySelector('a');
        return a ? a.href : '';
      });

      console.log("➡️ Link:", link);

      // 💾 Update ke database
      await db.execute(`
        UPDATE alumni SET linkedin=? WHERE id=?
      `, [link, d.id]);

      // Delay biar tidak diblok Google
      await new Promise(r => setTimeout(r, 3000));
    }

    await browser.close();
    await db.end();

    console.log("✅ Selesai semua");

  } catch (err) {
    console.error("❌ Error:", err);
  }
})();