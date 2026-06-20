<?php
session_start();
include '../config.php';

if (!isset($_SESSION['username'])) {
    header("Location: ../login.php");
    exit;
}

function getAlternatif() {
    global $koneksi;
    $data = [];
    $q = mysqli_query($koneksi, "SELECT * FROM alternatif ORDER BY id_alternatif ASC");
    while ($row = mysqli_fetch_assoc($q)) {
        $data[] = $row;
    }
    return $data;
}

function getKriteria() {
    global $koneksi;
    $data = [];
    $q = mysqli_query($koneksi, "SELECT * FROM kriteria ORDER BY id_kriteria ASC");
    while ($row = mysqli_fetch_assoc($q)) {
        $data[] = $row;
    }
    return $data;
}

// Ambil data
$alternatif = getAlternatif();
$kriteria = getKriteria();

// Validasi data
if (count($alternatif) == 0) {
    die("<p style='color:red'><strong>Error:</strong> Tidak ada data alternatif. Silakan input data alternatif terlebih dahulu.</p>");
}
if (count($kriteria) == 0) {
    die("<p style='color:red'><strong>Error:</strong> Tidak ada data kriteria. Silakan input data kriteria terlebih dahulu.</p>");
}

// Hitung total bobot
$total_bobot = array_sum(array_column($kriteria, 'bobot'));

// ----------------- SMART -----------------
$smart = [];
foreach ($alternatif as $alt) {
    $skor = 0;
    foreach ($kriteria as $i => $k) {
        $c = $i + 1;
        $nilai = $alt["c$c"];
        $normalisasi = $nilai; // asumsi normalisasi sederhana
        $skor += ($k['bobot'] / $total_bobot) * $normalisasi;
    }
    $smart[] = [
        'nama' => $alt['nama_alternatif'],
        'skor' => $skor
    ];
}
usort($smart, fn($a, $b) => $b['skor'] <=> $a['skor']);

// ----------------- TOPSIS -----------------
$matriks = [];
foreach ($alternatif as $alt) {
    $row = [];
    foreach ($kriteria as $i => $k) {
        $c = $i + 1;
        $row[] = $alt["c$c"];
    }
    $matriks[] = $row;
}

// Cek matriks terisi
if (empty($matriks)) {
    die("<p style='color:red'><strong>Error:</strong> Data matriks kosong. Pastikan data alternatif terisi dengan benar.</p>");
}

// Normalisasi matriks
$norm = [];
for ($j = 0; $j < count($kriteria); $j++) {
    $sum_sqr = 0;
    foreach ($matriks as $i => $row) {
        $sum_sqr += pow($row[$j], 2);
    }
    $sqrt_sum = sqrt($sum_sqr);

    if ($sqrt_sum == 0) {
        die("<p style='color:red'><strong>Error:</strong> Normalisasi gagal karena total kuadrat kolom ke-".($j+1)." adalah nol. Periksa input data Anda.</p>");
    }

    foreach ($matriks as $i => $row) {
        $norm[$i][$j] = $row[$j] / $sqrt_sum;
    }
}

// Bobot
$bobot = array_map(fn($k) => $k['bobot'] / $total_bobot, $kriteria);

// Matriks terbobot
$terbobot = [];
foreach ($norm as $i => $row) {
    foreach ($row as $j => $val) {
        $terbobot[$i][$j] = $val * $bobot[$j];
    }
}

// Solusi ideal
$ideal_plus = [];
$ideal_min = [];
foreach ($kriteria as $j => $k) {
    $col = array_column($terbobot, $j);

    if (empty($col)) {
        die("<p style='color:red'><strong>Error:</strong> Data kolom ke-".($j+1)." kosong saat menghitung solusi ideal. Periksa input data Anda.</p>");
    }

    if ($k['kategori'] == 'benefit') {
        $ideal_plus[$j] = max($col);
        $ideal_min[$j] = min($col);
    } else {
        $ideal_plus[$j] = min($col);
        $ideal_min[$j] = max($col);
    }
}

// Jarak ke solusi ideal
$jarak_plus = [];
$jarak_min = [];
foreach ($terbobot as $i => $row) {
    $sum_plus = 0; $sum_min = 0;
    foreach ($row as $j => $val) {
        $sum_plus += pow($val - $ideal_plus[$j], 2);
        $sum_min += pow($val - $ideal_min[$j], 2);
    }
    $jarak_plus[$i] = sqrt($sum_plus);
    $jarak_min[$i] = sqrt($sum_min);
}

// Nilai preferensi
$topsis = [];
foreach ($alternatif as $i => $alt) {
    $v = ($jarak_plus[$i] + $jarak_min[$i]) == 0 ? 0 : $jarak_min[$i] / ($jarak_plus[$i] + $jarak_min[$i]);
    $topsis[] = [
        'nama' => $alt['nama_alternatif'],
        'skor' => $v
    ];
}
usort($topsis, fn($a, $b) => $b['skor'] <=> $a['skor']);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Hasil Analisa</title>
    <style>
        body { font-family: Arial; padding: 20px; }
        h2 { color: #2980b9; }
        table { border-collapse: collapse; width: 100%; background: white; margin-bottom: 40px; }
        th, td { border: 1px solid #ccc; padding: 8px; text-align: center; }
    </style>
</head>
<body>

<h2>Hasil Perhitungan SMART</h2>
<table>
    <tr><th>Ranking</th><th>Nama Alternatif</th><th>Skor</th></tr>
    <?php foreach ($smart as $i => $row): ?>
        <tr>
            <td><?= $i+1 ?></td>
            <td><?= htmlspecialchars($row['nama']) ?></td>
            <td><?= number_format($row['skor'], 4) ?></td>
        </tr>
    <?php endforeach; ?>
</table>

<h2>Hasil Perhitungan TOPSIS</h2>
<table>
    <tr><th>Ranking</th><th>Nama Alternatif</th><th>Skor</th></tr>
    <?php foreach ($topsis as $i => $row): ?>
        <tr>
            <td><?= $i+1 ?></td>
            <td><?= htmlspecialchars($row['nama']) ?></td>
            <td><?= number_format($row['skor'], 4) ?></td>
        </tr>
    <?php endforeach; ?>
</table>

<a href="alternatif.php">← Kembali ke Data Alternatif</a>

</body>
</html>
