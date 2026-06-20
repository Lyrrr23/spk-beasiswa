<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
include 'config.php';

if (!isset($_SESSION['username']) || !isset($_SESSION['role'])) {
    header("Location: login.php");
    exit;
}

$nama = htmlspecialchars($_SESSION['username'], ENT_QUOTES, 'UTF-8');
$role = htmlspecialchars($_SESSION['role'], ENT_QUOTES, 'UTF-8');
?>

<!DOCTYPE html>
<html>
<head>
    <title>Dashboard Seleksi Beasiswa</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f4f6f9;
            padding: 40px;
            text-align: center;
        }
        .card {
            display: inline-block;
            background: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
            width: 400px;
        }
        h2 { color: #2980b9; }
        a.logout, .btn {
            display: inline-block;
            text-decoration: none;
            border-radius: 6px;
            font-weight: bold;
            color: white;
            padding: 12px 24px;
            margin-top: 25px;
        }
        a.logout {
            background: #e74c3c;
        }
        a.logout:hover { background: #c0392b; }
        .btn {
            background: #27ae60;
            font-size: 16px;
        }
        .btn:hover { background: #2ecc71; }
        .info {
            font-size: 14px;
            color: #555;
            margin-top: 15px;
        }
    </style>
</head>
<body>

<div class="card">
    <h2>Halo, Selamat Datang <strong><?= ucfirst($nama) ?></strong>!</h2>
    <p>Anda login sebagai <strong><?= $role ?></strong></p>

    <?php if ($role === 'admin'): ?>
        <a class="btn" href="admin/kriteria.php">Mulai</a>
        <p class="info">Anda dapat mengelola kriteria, alternatif, dan melihat hasil analisa.</p>
    <?php else: ?>
        <a class="btn" href="mahasiswa/hasil.php">Lihat Hasil</a>
        <p class="info">Anda hanya dapat melihat hasil perangkingan dari sistem.</p>
    <?php endif; ?>

    <a href="logout.php" class="logout">Logout</a>
</div>

</body>
</html>
