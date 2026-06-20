<?php
session_start();
include '../config.php';

// Cek role admin
if (!isset($_SESSION['username']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit;
}

// Simpan data alternatif baru
if (isset($_POST['simpan'])) {
    $nama = mysqli_real_escape_string($koneksi, $_POST['nama_alternatif']);
    $c1 = floatval($_POST['c1']);
    $c2 = floatval($_POST['c2']);
    $c3 = intval($_POST['c3']);
    $c4 = floatval($_POST['c4']);
    $c5 = floatval($_POST['c5']);

    mysqli_query($koneksi, "INSERT INTO alternatif (nama_alternatif, c1, c2, c3, c4, c5) VALUES (
        '$nama','$c1','$c2','$c3','$c4','$c5'
    )");

    // Hitung ulang setelah tambah data
    // Penting: pastikan file proses_hitung_smart.php & proses_hitung_saw.php
    // TIDAK ADA echo atau spasi di luar <?php ... 
    include 'proses_hitung_smart.php';
    include 'proses_hitung_saw.php';

    // Redirect setelah proses selesai, harus DIPASTIKAN tidak ada output sebelum ini
    header("Location: alternatif.php");
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Tambah Alternatif</title>
    <style>
        body {
            font-family: Arial;
            background: #f2f2f2;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .box {
            background: white;
            padding: 25px;
            border-radius: 10px;
            width: 400px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        h2 {
            text-align: center;
            color: #2980b9;
        }
        input, button {
            width: 100%;
            padding: 10px;
            margin-bottom: 12px;
            border-radius: 6px;
            border: 1px solid #ccc;
        }
        button {
            background: #2ecc71;
            color: white;
            border: none;
            font-weight: bold;
            cursor: pointer;
        }
        button:hover { background: #27ae60; }
        a {
            display: block;
            text-align: center;
            margin-top: 10px;
            color: #3498db;
            text-decoration: none;
        }
    </style>
</head>
<body>

<div class="box">
    <h2>Tambah Data Alternatif</h2>
    <form method="POST">
        <input type="text" name="nama_alternatif" placeholder="Nama Mahasiswa" required>
        <input type="number" step="0.01" name="c1" placeholder="IPK" required>
        <input type="number" step="0.01" name="c2" placeholder="Penghasilan Orang Tua" required>
        <input type="number" step="1" name="c3" placeholder="Jumlah Tanggungan" required>
        <input type="number" step="0.01" name="c4" placeholder="Prestasi (skor)" required>
        <input type="number" step="0.01" name="c5" placeholder="Keaktifan (skor)" required>
        <button type="submit" name="simpan">Simpan</button>
    </form>
    <a href="alternatif.php">← Kembali ke Data Alternatif</a>
</div>

</body>
</html>
