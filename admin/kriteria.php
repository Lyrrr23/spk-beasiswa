<?php
session_start();
include '../config.php';

if (!isset($_SESSION['username']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit;
}

// Hapus kriteria
if (isset($_GET['hapus'])) {
    $id = intval($_GET['hapus']);
    mysqli_query($koneksi, "DELETE FROM kriteria WHERE id_kriteria = $id");
    header("Location: kriteria.php");
    exit;
}

// Update kriteria
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_id'])) {
    $id = intval($_POST['update_id']);
    $nama = mysqli_real_escape_string($koneksi, $_POST['nama_kriteria']);
    $bobot = floatval($_POST['bobot']);
    $kategori = $_POST['kategori'];
    $kepentingan = $_POST['kepentingan'];

    mysqli_query($koneksi, "UPDATE kriteria SET 
    nama_kriteria='$nama',
    bobot='$bobot',
    kategori='$kategori',
    kepentingan='$kepentingan'
WHERE id_kriteria=$id");

// Jalankan ulang perhitungan setelah bobot diubah
include 'proses_hitung_saw.php';
include 'proses_hitung_smart.php';

// Set notifikasi ke session
$_SESSION['pesan'] = "Bobot berhasil diubah. Skor SMART & SAW telah dihitung ulang.";

// Redirect balik ke kriteria.php
header("Location: kriteria.php");
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
    <div><strong>Admin Panel Beasiswa</strong></div>
    <div class="nav-links">
        <a href="../dashboard.php">Dashboard</a>
        <a href="kriteria.php">Kriteria</a>
        <a href="alternatif.php">Alternatif</a>
        <a href="proses_hitung_smart.php">Hasil SMART</a> <!-- ubah ke file tampilan hasil -->
        <a href="proses_hitung_saw.php">Hasil SAW</a>     <!-- ubah ke file tampilan hasil -->
        <a href="rekomendasi.php">Rekomendasi</a>
        <a href="../logout.php">Logout</a>
    </div>
</div>

<h2>Data Kriteria</h2>
<?php if (!empty($_SESSION['pesan'])): ?>
    <div style="padding:10px;background:#dff0d8;color:#3c763d;border-radius:6px;margin-bottom:15px;">
        <?= htmlspecialchars($_SESSION['pesan']); unset($_SESSION['pesan']); ?>
    </div>
<?php endif; ?>


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
            <td>
                
                <a href='kriteria.php?hapus={$row['id_kriteria']}' class='btn hapus' onclick='return confirm(\"Yakin hapus data ini?\")'>Hapus</a>
            </td>
        </tr>";
        $no++;
    }
    ?>
</table>

</body>
</html>
