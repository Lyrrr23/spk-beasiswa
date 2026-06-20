<?php
session_start();
include '../config.php';

if (!isset($_SESSION['username']) || $_SESSION['role'] != 'mahasiswa') {
    header("Location: ../login.php");
    exit;
}

// Ambil data alternatif dan kriteria
$alternatif = mysqli_query($koneksi, "SELECT * FROM alternatif ORDER BY id_alternatif ASC");
$kriteria = mysqli_query($koneksi, "SELECT * FROM kriteria ORDER BY id_kriteria ASC");

$alt = []; $krit = [];
while ($a = mysqli_fetch_assoc($alternatif)) $alt[] = $a;
while ($k = mysqli_fetch_assoc($kriteria)) $krit[] = $k;

if (empty($alt) || empty($krit)) {
    die("<h2>Data alternatif atau kriteria kosong. Tidak bisa melakukan rekomendasi.</h2><a href='alternatif.php'>← Kembali</a>");
}

// Konversi data alternatif ke matriks
$matriks = [];
foreach ($alt as $a) {
    $matriks[] = [$a['c1'], $a['c2'], $a['c3'], $a['c4'], $a['c5']];
}

// Hitung min dan max masing-masing kriteria
$min = []; $max = [];
for ($j = 0; $j < count($krit); $j++) {
    $col = array_column($matriks, $j);
    $max[$j] = max($col);
    $min[$j] = min($col);
}

// SMART: normalisasi min-max dengan kategori
$normal_smart = [];
foreach ($matriks as $i => $row) {
    foreach ($row as $j => $val) {
        $range = $max[$j] - $min[$j];
        if ($range == 0) {
            $normal_smart[$i][$j] = 0;
        } else {
            $normal_smart[$i][$j] = $krit[$j]['kategori'] == 'benefit'
                ? ($val - $min[$j]) / $range
                : ($max[$j] - $val) / $range;
        }
    }
}

// Hitung skor SMART
$total_bobot = array_sum(array_column($krit, 'bobot'));
$bobot_smart = array_map(fn($k) => $k['bobot'] / $total_bobot, $krit);

$smart = [];
foreach ($normal_smart as $i => $row) {
    $skor = 0;
    foreach ($row as $j => $val) $skor += $val * $bobot_smart[$j];
    $smart[] = ['nama' => $alt[$i]['nama_alternatif'], 'skor' => $skor];
}
usort($smart, fn($a, $b) => $b['skor'] <=> $a['skor']);
$rekom_smart = $smart[0];

// SAW: normalisasi benefit/cost
$normal_saw = [];
foreach ($matriks as $i => $row) {
    foreach ($row as $j => $val) {
        $normal_saw[$i][$j] = $krit[$j]['kategori'] == 'benefit'
            ? $val / $max[$j]
            : $min[$j] / $val;
    }
}

// Hitung skor SAW
$saw = [];
foreach ($normal_saw as $i => $row) {
    $skor = 0;
    foreach ($row as $j => $val) $skor += $val * $bobot_smart[$j];
    $saw[] = ['nama' => $alt[$i]['nama_alternatif'], 'skor' => $skor];
}
usort($saw, fn($a, $b) => $b['skor'] <=> $a['skor']);
$rekom_saw = $saw[0];
?>
<!DOCTYPE html>
<html>
<head>
    <title>Hasil Rekomendasi Mahasiswa</title>
    <style>
        body {font-family: Arial; background: #f4f4f4; padding: 40px;}
        .header{text-align:center;color:#333;margin-bottom:40px;}
        .header h1{font-size:2.5em;}
        .results-grid{display:grid;grid-template-columns:1fr 1fr;gap:30px;}
        .box{background:white;padding:30px;border-radius:15px;box-shadow:0 10px 30px rgba(0,0,0,0.2);transition:transform 0.3s;}
        .box:hover{transform:translateY(-5px);}
        .smart{border-left:8px solid #43a047;}
        .saw{border-left:8px solid #fbc02d;}
        .box h2{margin-top:0;}
        .winner{font-size:1.5em;font-weight:bold;color:#1a237e;margin-bottom:10px;}
        .score{font-size:1.2em;padding:10px 15px;border-radius:8px;display:inline-block;margin-top:10px;}
        .smart-score{background:#c8e6c9;color:#1b5e20;}
        .saw-score{background:#fff9c4;color:#795548;}
        @media(max-width:768px){.results-grid{grid-template-columns:1fr;}}
    </style>
</head>
<body>

<div class="header">
    <h1>🎓 Hasil Rekomendasi Penerima Beasiswa</h1>
</div>

<div class="results-grid">
    <div class="box smart">
        <h2>📊 Rekomendasi SMART</h2>
        <div class="winner"><?= htmlspecialchars($rekom_smart['nama']) ?></div>
        <p>Direkomendasikan dengan metode SMART</p>
        <div class="score smart-score">Skor: <?= number_format($rekom_smart['skor'],4) ?></div>
    </div>

    <div class="box saw">
        <h2>📈 Rekomendasi SAW</h2>
        <div class="winner"><?= htmlspecialchars($rekom_saw['nama']) ?></div>
        <p>Direkomendasikan dengan metode SAW</p>
        <div class="score saw-score">Skor: <?= number_format($rekom_saw['skor'],4) ?></div>
    </div>
</div>

<a href="alternatif.php" class="back" style="display:inline-block;margin-top:20px;text-decoration:none;color:white;background:#2980b9;padding:10px 20px;border-radius:6px;">← Kembali ke Data Alternatif</a>

</body>
</html>
