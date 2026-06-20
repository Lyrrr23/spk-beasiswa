<?php
session_start();
include '../config.php';

if (!isset($_SESSION['username']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit;
}

$id = intval($_GET['id']); // pakai id_alternatif
$q = mysqli_query($koneksi, "SELECT * FROM alternatif WHERE id_alternatif=$id");
$data = mysqli_fetch_assoc($q);

if (isset($_POST['update'])) {
    $nama = mysqli_real_escape_string($koneksi, $_POST['nama_alternatif']);
    $c1 = floatval($_POST['c1']);
    $c2 = floatval($_POST['c2']);
    $c3 = intval($_POST['c3']);
    $c4 = floatval($_POST['c4']);
    $c5 = floatval($_POST['c5']);

    mysqli_query($koneksi, "UPDATE alternatif SET 
        nama_alternatif='$nama',
        c1='$c1',
        c2='$c2',
        c3='$c3',
        c4='$c4',
        c5='$c5'
        WHERE id_alternatif=$id");

    header("Location: alternatif.php");
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Alternatif</title>
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
        h2 { text-align: center; color: #2980b9; }
        input, button {
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
    <h2>Edit Data Alternatif</h2>
    <form method="POST">
        <input type="text" name="nama_alternatif" value="<?= htmlspecialchars($data['nama_alternatif']) ?>" required>
        <input type="number" step="0.01" name="c1" value="<?= htmlspecialchars($data['c1']) ?>" required placeholder="IPK">
        <input type="number" step="0.01" name="c2" value="<?= htmlspecialchars($data['c2']) ?>" required placeholder="Penghasilan Orang Tua">
        <input type="number" step="1" name="c3" value="<?= htmlspecialchars($data['c3']) ?>" required placeholder="Jumlah Tanggungan">
        <input type="number" step="0.01" name="c4" value="<?= htmlspecialchars($data['c4']) ?>" required placeholder="Prestasi">
        <input type="number" step="0.01" name="c5" value="<?= htmlspecialchars($data['c5']) ?>" required placeholder="Keaktifan">
        <button type="submit" name="update">Update</button>
    </form>
    <a href="alternatif.php">← Kembali ke Data Alternatif</a>
</div>

</body>
</html>
