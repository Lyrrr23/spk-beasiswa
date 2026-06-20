<?php
session_start();
include '../config.php';

if (!isset($_SESSION['username']) || $_SESSION['role'] != 'mahasiswa') {
    header("Location: ../login.php");
    exit;
}

// Ambil alternatif & kriteria
$alternatif = getAlternatif();
$kriteria = getKriteria();
$total_bobot = array_sum(array_column($kriteria, 'bobot'));

// Hitung SMART
$smart = [];
foreach ($alternatif as $alt) {
    $skor = 0;
    foreach ($kriteria as $i => $k) {
        $c = $i + 1;
        $skor += ($k['bobot'] / $total_bobot) * $alt["c$c"];
    }
    $smart[] = ['nama' => $alt['nama_alternatif'], 'skor' => $skor];
}
usort($smart, fn($a, $b) => $b['skor'] <=> $a['skor']);

// Ambil terbaik SMART
$rekom_smart = $smart[0];
?>

<!DOCTYPE html>
<html>
<head>
    <title>Hasil Rekomendasi Mahasiswa</title>
    <style>
        body { font-family: Arial; padding: 20px; background: #f4f4f4; }
        .navbar { background: #2980b9; padding: 12px 20px; color: white; margin-bottom: 20px; }
        .navbar a { color: white; margin-left: 15px; text-decoration: none; font-weight: bold; }
        .box { background: white; padding: 20px; border-radius: 10px; }
        h2 { margin-top: 0; color: #2e7d32; }
        p { font-size: 18px; }
    </style>
</head>
<body>

<div class="navbar">
    <strong>Mahasiswa Panel Beasiswa</strong>
    <div>
        <a href="alternatif.php">Data Alternatif</a>
        <a href="hasil.php">Hasil Rekomendasi</a>
        <a href="../logout.php">Logout</a>
    </div>
</div>

<div class="box">
    <h2>Hasil Rekomendasi (Metode SMART)</h2>
    <p><strong><?= htmlspecialchars($rekom_smart['nama']) ?></strong> direkomendasikan dengan skor SMART: <strong><?= number_format($rekom_smart['skor'],4) ?></strong></p>
    
</div>

</body>
</html>
