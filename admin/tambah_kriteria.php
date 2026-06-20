<?php
session_start();
include '../config.php';

// Cek role admin
if (!isset($_SESSION['username']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit;
}

// Proses simpan kriteria
if (isset($_POST['simpan'])) {
    $nama = mysqli_real_escape_string($koneksi, $_POST['nama_kriteria']);
    $bobot = floatval($_POST['bobot']);
    $kepentingan = mysqli_real_escape_string($koneksi, $_POST['kepentingan']);
    $kategori = mysqli_real_escape_string($koneksi, $_POST['kategori']);

    $query = "INSERT INTO kriteria (nama_kriteria, bobot, kepentingan, kategori) VALUES 
        ('$nama', '$bobot', '$kepentingan', '$kategori')";
    if (mysqli_query($koneksi, $query)) {
        header("Location: kriteria.php");
        exit;
    } else {
        die("Error saat simpan data: " . mysqli_error($koneksi));
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Tambah Kriteria</title>
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
        input, select, button {
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
    <h2>Tambah Kriteria</h2>
    <form method="POST">
        <input type="text" name="nama_kriteria" placeholder="Contoh: IPK / Penghasilan Orang Tua" required>
        <input type="number" step="0.01" name="bobot" placeholder="Bobot (misal 0.25)" required>

        <select name="kepentingan" required>
            <option value="">-- Pilih Kepentingan --</option>
            <option value="Tidak Penting">Tidak Penting</option>
            <option value="Cukup Penting">Cukup Penting</option>
            <option value="Penting">Penting</option>
            <option value="Sangat Penting">Sangat Penting</option>
        </select>

        <select name="kategori" required>
            <option value="">-- Pilih Kategori --</option>
            <option value="benefit">Benefit</option>
            <option value="cost">Cost</option>
        </select>

        <button type="submit" name="simpan">Simpan</button>
    </form>
    <a href="kriteria.php">← Kembali ke Data Kriteria</a>
</div>

</body>
</html>
