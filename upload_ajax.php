<!DOCTYPE html>
<html lang="id">
<head>
    <title>Bulk Upload Alumni</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/PapaParse/5.3.2/papaparse.min.js"></script>
    <style>
        body { font-family: sans-serif; background: #0D0F14; color: white; padding: 50px; }
        .progress-container { width: 100%; background: #191D27; border-radius: 10px; margin-top: 20px; display:none; }
        .progress-bar { width: 0%; height: 20px; background: #6366F1; border-radius: 10px; transition: 0.3s; }
        #status { margin-top: 10px; font-size: 14px; color: #64748B; }
    </style>
</head>
<body>
    <h2>Upload 142.000+ Data Alumni</h2>
    <input type="file" id="csvFile" accept=".csv">
    <button onclick="startUpload()" id="btnUpload" style="background:#6366F1; color:white; border:none; padding:10px 20px; cursor:pointer;">Mulai Upload</button>

    <div class="progress-container" id="pContainer">
        <div class="progress-bar" id="pBar"></div>
    </div>
    <p id="status"></p>

    <script>
    async function startUpload() {
        const fileInput = document.getElementById('csvFile');
        if (!fileInput.files.length) return alert('Pilih file CSV!');

        const file = fileInput.files[0];
        const btn = document.getElementById('btnUpload');
        const pBar = document.getElementById('pBar');
        const pContainer = document.getElementById('pContainer');
        const statusTxt = document.getElementById('status');

        btn.disabled = true;
        pContainer.style.display = 'block';

        Papa.parse(file, {
            header: true,
            skipEmptyLines: true,
            complete: async function(results) {
                const allData = results.data;
                const total = allData.length;
                const batchSize = 500; // Kirim 500 data per kloter
                
                for (let i = 0; i < total; i += batchSize) {
// Di dalam upload_ajax.php
                const chunk = allData.slice(i, i + batchSize).map(row => ({
                    nama: row['Nama Lulusan'], // Ganti jika header di excel berbeda
                    nim: row['NIM'],
                    masuk: row['Tahun Masuk'],
                    // Pastikan 'Tanggal Lulus' sama dengan header di Excel kamu
                    tanggal_lulus: row['Tanggal Lulus'], 
                    fakultas: row['Fakultas'],
                    prodi: row['Program Studi']
                }));

                    await fetch('proses_ajax.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify(chunk)
                    });

                    // Update Progress Bar
                    const percent = Math.round(((i + chunk.length) / total) * 100);
                    pBar.style.width = percent + '%';
                    statusTxt.innerText = `Memproses ${i + chunk.length} dari ${total} data...`;
                }

                statusTxt.innerText = "Selesai! Semua data berhasil diimpor.";
                alert("Upload Berhasil!");
                window.location.href = "alumni.php";
            }
        });
    }
    </script>
</body>
</html>