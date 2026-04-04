* **Nama** : Shelly Kurnia Ulisyah
* **NIM** : 202210370311136
* **Kelas** : Rekayasa Kebutuhan A

---

## 📌 Deskripsi Aplikasi

**Bandhunet** adalah aplikasi berbasis web yang digunakan untuk **menyimpan, mengelola, dan melacak data alumni** secara terstruktur.

Aplikasi ini dikembangkan untuk membantu:

* Pengelolaan data alumni secara digital
* Mempermudah pencarian data alumni
* Melakukan pelacakan status alumni
* Mengelompokkan data berdasarkan kondisi tertentu

---

## 🚀 Fitur Utama

* 📄 Menampilkan data alumni
* ➕ Menambahkan data alumni
* 🔍 Pencarian data alumni
* 🏷️ Filter berdasarkan status tracking
* ✏️ Edit data alumni
* ❌ Hapus data alumni
* 📥 Import data alumni (Excel/CSV)
* 📊 Tracking status alumni
* 📌 Status Tracking:

  * **Teridentifikasi**
  * **Perlu Verifikasi**
  * **Tidak Ditemukan**
  * **Belum Dilacak**

---

## 🛠️ Teknologi yang Digunakan

* **Frontend** : HTML, CSS, JavaScript
* **Backend** : PHP Native
* **Database** : MySQL
* **Server** : InfinityFree / Localhost (Laragon/XAMPP)

---

## ⚙️ Cara Menjalankan Aplikasi

### 1. Clone Repository

```bash
git clone https://github.com/username/bandhunet.git
```

### 2. Pindahkan ke Folder Server

* Jika menggunakan XAMPP → `htdocs/`
* Jika menggunakan Laragon → `www/`

### 3. Import Database

* Buka **phpMyAdmin**
* Buat database baru (misal: `bandhunet`)
* Import file `.sql` yang tersedia

### 4. Konfigurasi Koneksi

Edit file `koneksi.php`:

```php
$conn = mysqli_connect("localhost", "root", "", "bandhunet");
```

### 5. Jalankan Aplikasi

Buka browser:

```
http://localhost/bandhunet
```

---

## 📂 Struktur Folder

```
bandhunet/
│── css/
│── js/
│── koneksi.php
│── dashboard.php
│── alumni.php
│── tambah.php
│── edit.php
│── hapus.php
│── import.php
│── track.php
│── login.php
│── logout.php
```

---

## 🧪 Hasil Pengujian Aplikasi

Pengujian dilakukan menggunakan metode **Black Box Testing** untuk memastikan sistem berjalan sesuai dengan kebutuhan.

### 🎯 Aspek Kualitas yang Diuji

* Fungsionalitas
* Keandalan (Reliability)
* Kemudahan penggunaan (Usability)
* Efisiensi
* Validitas data

---

### 📊 Tabel Hasil Pengujian

| No | Fitur yang Diuji   | Skenario Pengujian          | Hasil yang Diharapkan | Hasil Aktual                                           | Status            |
| -- | ------------------ | --------------------------- | --------------------- | ------------------------------------------------------ | ----------------- |
| 1  | Tampil Data Alumni | Membuka halaman data alumni | Data tampil di tabel  | Data tampil (masih terdapat data dummy dan data valid) | ⚠️ Perlu Validasi |
| 2  | Pencarian Data     | Input nama                  | Data terfilter        | Berhasil                                               | ✅ Berhasil        |
| 3  | Filter Status      | Pilih status                | Data sesuai filter    | Berhasil                                               | ✅ Berhasil        |
| 4  | Tambah Data        | Input data baru             | Data tersimpan        | Berhasil                                               | ✅ Berhasil        |
| 5  | Tombol Aksi        | Klik detail/edit/hapus      | Berfungsi normal      | Berhasil                                               | ✅ Berhasil        |
| 6  | Tracking Alumni    | Input tracking              | Data valid            | Belum sepenuhnya valid                                 | ❌ Belum Optimal   |
| 7  | Status Tracking    | Penandaan status            | Sesuai kondisi        | Berhasil                                               | ✅ Berhasil        |

---

## 🧾 Pernyataan Hasil Pengujian

Berdasarkan pengujian yang telah dilakukan:

* Aplikasi sudah berjalan dengan baik pada fitur utama (CRUD data alumni).
* Fitur **pencarian dan filter** bekerja optimal.
* Masih terdapat **data dummy**, sehingga perlu validasi lebih lanjut.
* Fitur **tracking alumni belum sepenuhnya optimal**.
* Solusi sementara menggunakan **status tracking** untuk menandai kondisi data.

## 📎 Lisensi

Project ini dibuat untuk keperluan **tugas mata kuliah Rekayasa Kebutuhan**.
