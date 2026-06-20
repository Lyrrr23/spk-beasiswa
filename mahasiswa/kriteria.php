<?php
session_start();
include '../config.php';

if (!isset($_SESSION['username']) || $_SESSION['role'] != 'mahasiswa') {
    header("Location: ../kriteria.php");
    exit;
}

?>

<!DOCTYPE html>
<html>
<head>
    <title>Data Kriteria</title>
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
    <div><strong>Mahasiswa Panel Beasiswa</strong></div>
    <div>
        <a href="kriteria.php">Data Kriteria</a>
        <a href="alternatif.php">Data Alternatif</a>
        <a href="hasil.php">Hasil Rekomendasi</a>
        <a href="../logout.php">Logout</a>
    </div>
</div>

<h2>Data Kriteria</h2>

<a href="tambah_kriteria.php" class="btn tambah">+ Tambah Kriteria</a>
<br><br>

<table>
    <tr>
        <th>No</th>
        <th>Nama Kriteria</th>
        <th>Bobot</th>
        <th>Kepentingan</th>
        <th>Kategori</th>
        <th>Aksi</th>
    </tr>
    <?php
    $no = 1;
    $q = mysqli_query($koneksi, "SELECT * FROM kriteria ORDER BY id_kriteria ASC");
    while ($row = mysqli_fetch_assoc($q)) {
        echo "<tr>
            <td>$no</td>
            <td>".htmlspecialchars($row['nama_kriteria'])."</td>
            <td>{$row['bobot']}</td>
            <td>{$row['kepentingan']}</td>
            <td>{$row['kategori']}</td>
        </tr>";
        $no++;
    }
    ?>
</table>

</body>
</html>
