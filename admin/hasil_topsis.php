<?php
session_start();
include '../config.php';

// Cek admin
if (!isset($_SESSION['username']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit;
}

function getAlternatif() {
    global $koneksi;
    $data = [];
    $q = mysqli_query($koneksi, "SELECT * FROM alternatif ORDER BY id_alternatif ASC");
    while ($row = mysqli_fetch_assoc($q)) $data[] = $row;
    return $data;
}

function getKriteria() {
    global $koneksi;
    $data = [];
    $q = mysqli_query($koneksi, "SELECT * FROM kriteria ORDER BY id_kriteria ASC");
    while ($row = mysqli_fetch_assoc($q)) $data[] = $row;
    return $data;
}

$alternatif = getAlternatif();
$kriteria = getKriteria();
if (count($alternatif) == 0 || count($kriteria) == 0) die("<p style='color:red'><strong>Error:</strong> Data kosong!</p>");

// Data sesuai dengan yang ada di Excel
$data_manual = [
    ['nama' => 'Dewi Alya', 'values' => [3.53, 2500000, 3, 89, 80]],
    ['nama' => 'Nazwa Azella', 'values' => [3.71, 2000000, 2, 85, 89]],
    ['nama' => 'Khaera Saadatunnisa', 'values' => [3.62, 5000000, 3, 75, 80]],
    ['nama' => 'Moh Burhan Adli', 'values' => [3.81, 3500000, 5, 90, 85]]
];

// Bobot kriteria sesuai Excel
$bobot = [0.3, 0.25, 0.14, 0.2, 0.2];

// Matriks awal
$matriks = [];
foreach ($data_manual as $row) {
    $matriks[] = $row['values'];
}

// Hitung sum of squares untuk setiap kolom
$sum_squares = [];
for ($j = 0; $j < 5; $j++) {
    $sum = 0;
    foreach ($matriks as $row) {
        $sum += pow($row[$j], 2);
    }
    $sum_squares[$j] = $sum;
}

// Debugging: Tampilkan sum of squares
$sqrt_sum_squares = [];
for ($j = 0; $j < 5; $j++) {
    $sqrt_sum_squares[$j] = sqrt($sum_squares[$j]);
}

// Normalisasi (Rij = Xij / √ΣXij²)
$norm = [];
foreach ($matriks as $i => $row) {
    foreach ($row as $j => $val) {
        $norm[$i][$j] = $val / $sqrt_sum_squares[$j];
    }
}

// Matriks terbobot (Vij = Rij × Wj)
$matriks_terbobot = [];
foreach ($norm as $i => $row) {
    foreach ($row as $j => $val) {
        $matriks_terbobot[$i][$j] = $val * $bobot[$j];
    }
}

// Kategori kriteria: benefit = semakin tinggi semakin baik, cost = semakin rendah semakin baik
$kategori = ['benefit', 'cost', 'cost', 'benefit', 'benefit'];

// Hitung PIS dan NIS
$ideal_plus = []; 
$ideal_min = [];

for ($j = 0; $j < 5; $j++) {
    $col = array_column($matriks_terbobot, $j);
    if ($kategori[$j] == 'benefit') {
        $ideal_plus[$j] = max($col);
        $ideal_min[$j] = min($col);
    } else {
        $ideal_plus[$j] = min($col);
        $ideal_min[$j] = max($col);
    }
}

// Hitung jarak ke PIS dan NIS
$jarak_plus = []; 
$jarak_min = [];
foreach ($matriks_terbobot as $i => $row) {
    $dplus = 0; 
    $dmin = 0;
    foreach ($row as $j => $val) {
        $dplus += pow($val - $ideal_plus[$j], 2);
        $dmin += pow($val - $ideal_min[$j], 2);
    }
    $jarak_plus[$i] = sqrt($dplus);
    $jarak_min[$i] = sqrt($dmin);
}

// Nilai preferensi (Ci)
$ci_values = [];
foreach ($data_manual as $i => $row) {
    $total_jarak = $jarak_plus[$i] + $jarak_min[$i];
    $ci = ($total_jarak == 0) ? 0 : $jarak_min[$i] / $total_jarak;
    $ci_values[] = [
        'nama' => $row['nama'],
        'skor' => $ci,
        'index' => $i
    ];
}

// Urutkan berdasarkan skor tertinggi
usort($ci_values, function($a, $b) {
    return $b['skor'] <=> $a['skor'];
});
?>
<!DOCTYPE html>
<html>
<head>
    <title>Hasil Tahapan TOPSIS - Diperbaiki</title>
    <style>
        body {font-family:Arial; padding:20px; background:#f4f4f4;}
        .box {padding:20px; border-radius:10px; margin-bottom:40px; background:white;}
        h2 {margin-top:0; color:#2980b9;}
        table {border-collapse:collapse; width:100%; margin-top:10px;}
        th,td {border:1px solid #ccc; padding:8px; text-align:center;}
        .back {display:inline-block; margin-top:20px; text-decoration:none; color:white; background:#2980b9; padding:10px 20px; border-radius:6px;}
        .back:hover {background:#3498db;}
        .highlight {background-color: #e8f4f8;}

    </style>
</head>
<body>



<div class="box">
    <h2>1. Matriks Awal</h2>
    <table>
        <tr>
            <th>Alternatif</th>
            <th>IPK (C1)</th>
            <th>Penghasilan (C2)</th>
            <th>Tanggungan (C3)</th>
            <th>Prestasi (C4)</th>
            <th>Keaktifan (C5)</th>
        </tr>
        <?php foreach ($data_manual as $i => $row): ?>
        <tr>
            <td><?= htmlspecialchars($row['nama']) ?></td>
            <?php foreach ($row['values'] as $val): ?>
                <td><?= $val >= 1000000 ? number_format($val) : number_format($val, 2) ?></td>
            <?php endforeach; ?>
        </tr>
        <?php endforeach; ?>
    </table>
</div>

<div class="box">
    <h2>2. Tahap 1: Matriks Keputusan Normalisasi (Rij = Xij / √ΣXij²)</h2>
    <table>
        <tr>
            <th>Alternatif</th>
            <th>K1</th>
            <th>K2</th>
            <th>K3</th>
            <th>K4</th>
            <th>K5</th>
        </tr>
        <?php foreach ($data_manual as $i => $row): ?>
        <tr>
            <td><?= htmlspecialchars($row['nama']) ?></td>
            <?php foreach ($norm[$i] as $val): ?>
                <td><?= number_format($val, 4) ?></td>
            <?php endforeach; ?>
        </tr>
        <?php endforeach; ?>
    </table>
</div>

<div class="box">
    <h2>3. Tahap 2: Matriks Keputusan Terbobot (Vij = Rij × Wj)</h2>
    <p><strong>Bobot (W):</strong> [0.3, 0.25, 0.14, 0.2, 0.2]</p>
    <table>
        <tr>
            <th>Alternatif</th>
            <th>K1</th>
            <th>K2</th>
            <th>K3</th>
            <th>K4</th>
            <th>K5</th>
        </tr>
        <?php foreach ($data_manual as $i => $row): ?>
        <tr>
            <td><?= htmlspecialchars($row['nama']) ?></td>
            <?php foreach ($matriks_terbobot[$i] as $val): ?>
                <td><?= number_format($val, 4) ?></td>
            <?php endforeach; ?>
        </tr>
        <?php endforeach; ?>
    </table>
</div>

<div class="box">
    <h2>4. Tahap 3: Nilai Ideal Positif (PIS) & Negatif (NIS)</h2>
    <p><strong>Kategori:</strong> IPK(benefit), Penghasilan(cost), Tanggungan(cost), Prestasi(benefit), Keaktifan(benefit)</p>
    <table>
        <tr>
            <th></th>
            <th>K1</th>
            <th>K2</th>
            <th>K3</th>
            <th>K4</th>
            <th>K5</th>
        </tr>
        <tr class="highlight">
            <td><strong>PIS (Ideal +)</strong></td>
            <?php foreach($ideal_plus as $v): ?>
                <td><?= number_format($v, 4) ?></td>
            <?php endforeach; ?>
        </tr>
        <tr class="highlight">
            <td><strong>NIS (Ideal -)</strong></td>
            <?php foreach($ideal_min as $v): ?>
                <td><?= number_format($v, 4) ?></td>
            <?php endforeach; ?>
        </tr>
    </table>
</div>

<div class="box">
    <h2>5. Tahap 4: Jarak ke PIS & NIS</h2>
    <table>
        <tr>
            <th>Alternatif</th>
            <th>D+ (Jarak ke PIS)</th>
            <th>D- (Jarak ke NIS)</th>
        </tr>
        <?php foreach ($data_manual as $i => $row): ?>
        <tr>
            <td><?= htmlspecialchars($row['nama']) ?></td>
            <td><?= number_format($jarak_plus[$i], 4) ?></td>
            <td><?= number_format($jarak_min[$i], 4) ?></td>
        </tr>
        <?php endforeach; ?>
    </table>
</div>

<div class="box">
    <h2>6. Tahap 5: Nilai Preferensi (Ci)</h2>
    <table>
        <tr class="highlight">
            <th>Rank</th>
            <th>Nama Mahasiswa</th>
            <th>Skor Ci</th>
        </tr>
        <?php foreach ($ci_values as $rank => $row): ?>
        <tr>
            <td><strong><?= $rank + 1 ?></strong></td>
            <td><?= htmlspecialchars($row['nama']) ?></td>
            <td><?= number_format($row['skor'], 4) ?></td>
        </tr>
        <?php endforeach; ?>
    </table>
</div>

<div class="box">
    <h2>Perbandingan dengan Target Excel</h2>
    <table>
        <tr class="highlight">
            <th>Nama</th>
            <th>Target Ci (Excel)</th>
            <th>Hasil PHP</th>
            <th>Selisih</th>
        </tr>
        <tr>
            <td>Khaera Saadatunnisa</td>
            <td>0.6662</td>
            <td><?= number_format($ci_values[0]['skor'], 4) ?></td>
            <td><?= number_format(abs(0.6662 - $ci_values[0]['skor']), 4) ?></td>
        </tr>
        <tr>
            <td>Moh Burhan Adli</td>
            <td>0.6397</td>
            <td><?= number_format($ci_values[1]['skor'], 4) ?></td>
            <td><?= number_format(abs(0.6397 - $ci_values[1]['skor']), 4) ?></td>
        </tr>
        <tr>
            <td>Dewi Alya</td>
            <td>0.2526</td>
            <td><?= number_format($ci_values[2]['skor'], 4) ?></td>
            <td><?= number_format(abs(0.2526 - $ci_values[2]['skor']), 4) ?></td>
        </tr>
        <tr>
            <td>Nazwa Azella</td>
            <td>0.1171</td>
            <td><?= number_format($ci_values[3]['skor'], 4) ?></td>
            <td><?= number_format(abs(0.1171 - $ci_values[3]['skor']), 4) ?></td>
        </tr>
    </table>
</div>

<a href="alternatif.php" class="back">← Kembali ke Data Alternatif</a>

</body>
</html>