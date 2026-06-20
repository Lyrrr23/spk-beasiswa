<?php
session_start();
include '../config.php';

if (!isset($_SESSION['username']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit;
}

if (isset($_GET['hapus'])) {
    $id = intval($_GET['hapus']);
    // Hapus data hasil terkait
    mysqli_query($koneksi, "DELETE FROM hasil WHERE id_alternatif=$id");
    // Hapus data alternatif
    mysqli_query($koneksi, "DELETE FROM alternatif WHERE id_alternatif=$id");

    // Hitung ulang otomatis setelah hapus
    include 'proses_hitung_smart.php';
    include 'proses_hitung_saw.php';

    header("Location: alternatif.php");
    exit;
}


?>


<!DOCTYPE html>
<html>
<head>
    <title>Data Alternatif</title>
    <style>
        body {
            font-family: Arial;
            background: #f2f2f2;
            padding: 20px;
        }
        .navbar {
            background: #3498db;
            padding: 12px 20px;
            color: white;
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        .navbar a {
            color: white;
            text-decoration: none;
            margin-left: 15px;
            font-weight: bold;
        }
        .nav-links { display: flex; gap: 10px; }
        table {
            width: 100%;
            border-collapse: collapse;
            background: white;
        }
        th, td {
            padding: 10px;
            border: 1px solid #ccc;
            text-align: center;
        }
        h2 { margin-bottom: 10px; }
        .btn {
            padding: 6px 12px;
            border-radius: 4px;
            text-decoration: none;
            font-weight: bold;
        }
        .tambah { background: #2ecc71; color: white; }
        .edit { background: #3498db; color: white; }
        .hapus { background: #e74c3c; color: white; }
    </style>
</head>
<body>

<!-- Navbar -->
<div class="navbar">
    <div><strong>Admin Panel Beasiswa</strong></div>
        <div class="nav-links">
        <a href="../dashboard.php">Dashboard</a>
        <a href="kriteria.php">Kriteria</a>
        <a href="alternatif.php">Alternatif</a>
        <a href="proses_hitung_smart.php">Hasil SMART</a> <!-- ubah ke file tampilan hasil -->
        <a href="proses_hitung_saw.php">Hasil SAW</a>   <!-- ubah ke file tampilan hasil -->   
        <a href="rekomendasi.php">Rekomendasi</a>
        <a href="../logout.php">Logout</a>
</div>

</div>

<h2>Data Alternatif</h2>

<a href="tambah_alternatif.php" class="btn tambah">+ Tambah Alternatif</a>
<br><br>

<table>
    <tr>
        <th>No</th>
        <th>Nama Mahasiswa</th>
        <th>IPK</th>
        <th>Penghasilan Orang Tua</th>
        <th>Jumlah Tanggungan</th>
        <th>Prestasi</th>
        <th>Keaktifan</th>
        <th>Aksi</th>
    </tr>
    <?php
    $no = 1;
    $q = mysqli_query($koneksi, "SELECT * FROM alternatif ORDER BY id_alternatif ASC");
    while ($row = mysqli_fetch_assoc($q)) {
        echo "<tr>
            <td>$no</td>
            <td>".htmlspecialchars($row['nama_alternatif'])."</td>
            <td>{$row['c1']}</td>
            <td>{$row['c2']}</td>
            <td>{$row['c3']}</td>
            <td>{$row['c4']}</td>
            <td>{$row['c5']}</td>
            <td>
                <a href='edit_alternatif.php?id={$row['id_alternatif']}' class='btn edit'>Edit</a>
                <a href='alternatif.php?hapus={$row['id_alternatif']}' class='btn hapus' onclick='return confirm(\"Yakin hapus data ini?\")'>Hapus</a>
            </td>
        </tr>";
        $no++;
    }
    ?>
</table>

</body>
</html>
