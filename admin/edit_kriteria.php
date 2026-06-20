<?php
session_start();
include '../config.php';

if (!isset($_SESSION['username']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit;
}

$id = intval($_GET['id']); // pakai id_kriteria
$q = mysqli_query($koneksi, "SELECT * FROM kriteria WHERE id_kriteria=$id");
$data = mysqli_fetch_assoc($q);

if (isset($_POST['update'])) {
    $nama = mysqli_real_escape_string($koneksi, $_POST['nama_kriteria']);
    $bobot = floatval($_POST['bobot']);
    $kepentingan = mysqli_real_escape_string($koneksi, $_POST['kepentingan']);
    $kategori = mysqli_real_escape_string($koneksi, $_POST['kategori']);

    mysqli_query($koneksi, "UPDATE kriteria SET 
        nama_kriteria='$nama',
        bobot='$bobot',
        kepentingan='$kepentingan',
        kategori='$kategori'
        WHERE id_kriteria=$id");

    header("Location: kriteria.php");
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Kriteria</title>
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
            background: #3498db;
            color: white;
            border: none;
            font-weight: bold;
            cursor: pointer;
        }
        button:hover { background: #2980b9; }
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
    <h2>Edit Data Kriteria</h2>
    <form method="POST">
        <input type="text" name="nama_kriteria" value="<?= htmlspecialchars($data['nama_kriteria']) ?>" required>
        <input type="number" step="0.01" name="bobot" value="<?= htmlspecialchars($data['bobot']) ?>" required>

        <select name="kepentingan" required>
            <option value="Tidak Penting" <?= $data['kepentingan'] == 'Tidak Penting' ? 'selected' : '' ?>>Tidak Penting</option>
            <option value="Cukup Penting" <?= $data['kepentingan'] == 'Cukup Penting' ? 'selected' : '' ?>>Cukup Penting</option>
            <option value="Penting" <?= $data['kepentingan'] == 'Penting' ? 'selected' : '' ?>>Penting</option>
            <option value="Sangat Penting" <?= $data['kepentingan'] == 'Sangat Penting' ? 'selected' : '' ?>>Sangat Penting</option>
        </select>

        <select name="kategori" required>
            <option value="benefit" <?= $data['kategori'] == 'benefit' ? 'selected' : '' ?>>Benefit</option>
            <option value="cost" <?= $data['kategori'] == 'cost' ? 'selected' : '' ?>>Cost</option>
        </select>

        <button type="submit" name="update">Update</button>
    </form>
    <a href="kriteria.php">← Kembali ke Data Kriteria</a>
</div>

</body>
</html>
