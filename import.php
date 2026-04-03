<?php
session_start();
include "koneksi.php";

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}
?>

<link rel="stylesheet" href="css/style.css">

<div class="wrapper">

    <!-- SIDEBAR -->
    <div class="sidebar">
        <h2>Bandhunet</h2>
        <a href="dashboard.php">Dashboard</a>
        <a href="alumni.php">Data Alumni</a>
        <a href="import.php">Import</a>
        <a href="logout.php">Logout</a>
    </div>

    <!-- MAIN -->
    <div class="main">

        <div class="card">
            <h2>Import Data Alumni (CSV)</h2>

            <!-- FORM LANGSUNG KE PROSES IMPORT -->
            <form method="POST" enctype="multipart/form-data" action="proses_import.php">
                <input type="file" name="file" required>
                <button name="import">Import Data</button>
            </form>
        </div>

        <?php
        // PREVIEW (hanya tampil, tidak dikirim)
        if (isset($_FILES['file'])) {
            $file = $_FILES['file']['tmp_name'];

            if (($handle = fopen($file, "r")) !== FALSE) {

                echo "<div class='card'>";
                echo "<h3>Preview (10 Data Pertama)</h3>";
                echo "<table>";

                echo "<tr>
                        <th>Nama</th>
                        <th>NIM</th>
                        <th>Tahun Masuk</th>
                        <th>Tanggal Lulus</th>
                        <th>Fakultas</th>
                        <th>Prodi</th>
                        <th>Status</th>
                      </tr>";

                $no = 0;

                while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                    $no++;

                    if ($no == 1) continue; // skip header
                    if ($no > 10) break;    // hanya 10 data

                    echo "<tr>
                            <td>" . ($data[0] ?? '-') . "</td>
                            <td>" . ($data[1] ?? '-') . "</td>
                            <td>" . ($data[2] ?? '-') . "</td>
                            <td>" . ($data[3] ?? '-') . "</td>
                            <td>" . ($data[4] ?? '-') . "</td>
                            <td>" . ($data[5] ?? '-') . "</td>
                            <td style='color:red;'>Belum Dilacak</td>
                          </tr>";
                }

                echo "</table>";
                echo "</div>";

                fclose($handle);
            }
        }
        ?>

    </div>

</div>