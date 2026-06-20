<?php
session_start();
include '../config.php';

if (!isset($_SESSION['username']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit;
}

// Data manual persis Excel
$data_manual = [
    ['nama' => 'Dewi Alya', 'values' => [3.53, 2500000, 3, 89, 80]],
    ['nama' => 'Nazwa Azella', 'values' => [3.71, 2000000, 2, 85, 89]],
    ['nama' => 'Khaera Saadatunnisa', 'values' => [3.62, 5000000, 3, 75, 80]],
    ['nama' => 'Mohamad Burhan Adli', 'values' => [3.81, 3500000, 5, 90, 85]],
];


// Bobot sesuai Excel
$bobot = [0.3, 0.25, 0.14, 0.2, 0.2];

// Kategori kriteria
$kategori = ['benefit', 'cost', 'cost', 'benefit', 'benefit'];

// Matriks awal
$matriks = [];
foreach ($data_manual as $row) $matriks[] = $row['values'];

// Hitung max & min
$max = []; $min = [];
for ($j=0; $j<5; $j++) {
    $col = array_column($matriks, $j);
    $max[$j] = max($col);
    $min[$j] = min($col);
}

// Normalisasi SAW
$norm = [];
foreach ($matriks as $i => $row) {
    foreach ($row as $j => $val) {
        if ($kategori[$j] == 'benefit') {
            $norm[$i][$j] = $val / $max[$j];
        } else {
            $norm[$i][$j] = $min[$j] / $val;
        }
    }
}

// Hitung nilai SAW
$saw = [];
foreach ($norm as $i => $row) {
    $total = 0;
    foreach ($row as $j => $val) {
        $total += $val * $bobot[$j];
    }
    $saw[] = ['nama' => $data_manual[$i]['nama'], 'skor' => $total];
}

// Urutkan
usort($saw, fn($a, $b) => $b['skor'] <=> $a['skor']);

// Simpan ke tabel hasil
mysqli_query($koneksi, "DELETE FROM hasil WHERE metode='SAW'"); // kosongkan dulu

foreach ($saw as $rank => $row) {
    $nama = mysqli_real_escape_string($koneksi, $row['nama']);
    $skor = $row['skor'];
    $r = $rank + 1;

    // Ambil id_alternatif sesuai nama
    $q_id = mysqli_query($koneksi, "SELECT id_alternatif FROM alternatif WHERE nama_alternatif='$nama'");
$d_id = mysqli_fetch_assoc($q_id);
if (!$d_id) {
    die("❌ Gagal: Nama '$nama' tidak ditemukan di tabel alternatif. Pastikan namanya persis sama!");
}
$id_alternatif = $d_id['id_alternatif'];

mysqli_query($koneksi, "INSERT INTO hasil (id_alternatif, metode, skor, rank) 
                        VALUES ('$id_alternatif', 'SAW', '$skor', '$r')");


}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Hasil SAW</title>
    <style>
        body {font-family:Arial; padding:20px; background:#f4f4f4;}
        .box {padding:20px; border-radius:10px; margin-bottom:30px; background:white;}
        h2 {margin-top:0; color:#2980b9;}
        table {border-collapse:collapse; width:100%; margin-top:10px;}
        th,td {border:1px solid #ccc; padding:8px; text-align:center;}
    </style>
</head>
<body>

<div class="box">
    <h2>1. Matriks Awal</h2>
    <table>
        <tr>
            <th>Alternatif</th><th>C1</th><th>C2</th><th>C3</th><th>C4</th><th>C5</th>
        </tr>
        <?php foreach ($data_manual as $i=>$row): ?>
        <tr>
            <td><?= htmlspecialchars($row['nama']) ?></td>
            <?php foreach ($row['values'] as $v) echo "<td>".($v>=1e6?number_format($v):number_format($v,2))."</td>"; ?>
        </tr>
        <?php endforeach; ?>
    </table>
</div>

<div class="box">
    <h2>2. Normalisasi SAW (Benefit: Xij/max, Cost: min/Xij)</h2>
    <table>
        <tr><th>Alternatif</th><th>C1</th><th>C2</th><th>C3</th><th>C4</th><th>C5</th></tr>
        <?php foreach ($data_manual as $i=>$row): ?>
        <tr>
            <td><?= htmlspecialchars($row['nama']) ?></td>
            <?php foreach ($norm[$i] as $v) echo "<td>".number_format($v,4)."</td>"; ?>
        </tr>
        <?php endforeach; ?>
    </table>
</div>

<div class="box">
    <h2>3. Hasil Akhir SAW</h2>
    <table>
        <tr><th>Rank</th><th>Alternatif</th><th>Skor</th></tr>
        <?php foreach ($saw as $rank=>$row): ?>
        <tr>
            <td><?= $rank+1 ?></td>
            <td><?= htmlspecialchars($row['nama']) ?></td>
            <td><?= number_format($row['skor'], 4) ?></td>
        </tr>
        <?php endforeach; ?>
    </table>
</div>

<a href="alternatif.php" class="back" style="display:inline-block;margin-top:20px;text-decoration:none;color:white;background:#2980b9;padding:10px 20px;border-radius:6px;">← Kembali ke Data Alternatif</a>

</body>
</html>
