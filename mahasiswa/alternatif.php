<?php
session_start();
include '../config.php';

// Cek role mahasiswa
if (!isset($_SESSION['username']) || $_SESSION['role'] != 'mahasiswa') {
    header("Location: ../kriteria.php");
    exit;
}

$q = mysqli_query($koneksi, "SELECT * FROM alternatif ORDER BY id_alternatif ASC");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Data Alternatif Mahasiswa</title>
    <style>
        body { font-family: Arial; background: #f4f4f4; padding: 20px; }
        .navbar {
            background: #2980b9;
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
        h2 { color: #2980b9; }
        table { width: 100%; border-collapse: collapse; background: white; }
        th, td { border: 1px solid #ccc; padding: 10px; text-align: center; }
    </style>
</head>
<body>

<div class="navbar">
    <div><strong>Mahasiswa Panel Beasiswa</strong></div>
    <div>
        <a href="kriteria.php">Data Kriteria</a>
        <a href="alternatif.php">Data Alternatif</a>
        <a href="hasil.php">Hasil Rekomendasi</a>
        <a href="../logout.php">Logout</a>
    </div>
</div>

<h2>Data Alternatif</h2>

<table>
    <tr>
        <th>No</th>
        <th>Nama Mahasiswa</th>
        <th>IPK</th>
        <th>Penghasilan Orang Tua</th>
        <th>Jumlah Tanggungan</th>
        <th>Prestasi</th>
        <th>Keaktifan</th>
    </tr>
    <?php
    $no = 1;
    while ($row = mysqli_fetch_assoc($q)) {
        echo "<tr>
            <td>$no</td>
            <td>".htmlspecialchars($row['nama_alternatif'])."</td>
            <td>{$row['c1']}</td>
            <td>".number_format($row['c2'],0,",",".")."</td>
            <td>{$row['c3']}</td>
            <td>{$row['c4']}</td>
            <td>{$row['c5']}</td>
        </tr>";
        $no++;
    }
    ?>
</table>

</body>
</html>
