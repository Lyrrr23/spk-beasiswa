<?php
session_start();
include '../config.php';

if (!isset($_SESSION['username']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit;
}

// Ambil data alternatif
$q = mysqli_query($koneksi, "SELECT * FROM alternatif ORDER BY id_alternatif ASC");
$alternatif = [];
while ($row = mysqli_fetch_assoc($q)) {
    $alternatif[] = [
        'id' => $row['id_alternatif'],
        'nama' => $row['nama_alternatif'],
        'values' => [$row['c1'], $row['c2'], $row['c3'], $row['c4'], $row['c5']],
    ];
}
if (empty($alternatif)) {
    die("<h2>Tidak ada data alternatif untuk dihitung SMART.</h2><a href='alternatif.php'>← Kembali</a>");
}

// Bobot sesuai Excel
$bobot = [0.3, 0.25, 0.14, 0.2, 0.2];

// Kategori kriteria
$kategori = ['benefit', 'cost', 'cost', 'benefit', 'benefit'];

// Matriks awal
$matriks = array_column($alternatif, 'values');

// Hitung min & max tiap kolom
$max = $min = [];
for ($j=0; $j<5; $j++) {
    $col = array_column($matriks, $j);
    $max[$j] = max($col);
    $min[$j] = min($col);
}

// Tahap 1: Normalisasi min-max
$normal = [];
foreach ($matriks as $i => $row) {
    foreach ($row as $j => $val) {
        $range = $max[$j] - $min[$j];
        if ($range == 0) {
            $normal[$i][$j] = 0; // menghindari div 0
        } else {
            if ($kategori[$j] == 'benefit') {
                $normal[$i][$j] = ($val - $min[$j]) / $range;
            } else { // cost
                $normal[$i][$j] = ($max[$j] - $val) / $range;
            }
        }
        $normal[$i][$j] = round($normal[$i][$j], 4);
    }
}

// Tahap 2: Normalisasi * bobot + total
$terbobot = [];
$hasil = [];
foreach ($normal as $i => $row) {
    $total = 0;
    foreach ($row as $j => $val) {
        $terbobot[$i][$j] = round($val * $bobot[$j], 4);
        $total += $terbobot[$i][$j];
    }
    $hasil[] = [
        'id' => $alternatif[$i]['id'],
        'nama' => $alternatif[$i]['nama'],
        'nilai' => $terbobot[$i],
        'total' => round($total, 4),
    ];
}

// Urutkan ranking
usort($hasil, fn($a, $b) => $b['total'] <=> $a['total']);

// Kosongkan hasil SMART lama & simpan hasil baru
mysqli_query($koneksi, "DELETE FROM hasil WHERE metode='SMART'");
foreach ($hasil as $rank => $row) {
    mysqli_query($koneksi, "INSERT INTO hasil (id_alternatif, metode, skor, rank) 
                            VALUES ('{$row['id']}', 'SMART', '{$row['total']}', '".($rank+1)."')");
}

// Nama kriteria untuk tampilan
$nama_kriteria = ['IPK', 'PENGHASILAN', 'TANGGUNGAN', 'PRESTASI', 'KEAKTIFAN'];
?>
<!DOCTYPE html>
<html>
<head>
    <title>Hasil SMART</title>
    <style>
        body { font-family: Arial; padding: 20px; background: #f4f4f4; }
        .box { padding: 20px; border-radius: 10px; margin-bottom: 30px; background: white; }
        h2 { margin-top: 0; color: #2e7d32; }
        table { border-collapse: collapse; width: 100%; margin-top: 10px; }
        th,td { border: 1px solid #ccc; padding: 8px; text-align: center; }
    </style>
</head>
<body>

<div class="box">
    <h2>1. Matriks Keputusan Normalisasi</h2>
    <table>
        <tr>
            <th>Alternatif</th>
            <?php foreach ($nama_kriteria as $k) echo "<th>$k</th>"; ?>
        </tr>
        <?php foreach ($alternatif as $i => $alt): ?>
        <tr>
            <td><?= htmlspecialchars($alt['nama']) ?></td>
            <?php foreach ($normal[$i] as $v) echo "<td>".number_format($v,4)."</td>"; ?>
        </tr>
        <?php endforeach; ?>
    </table>
</div>

<div class="box">
    <h2>2. Matriks Terbobot & Total</h2>
    <table>
        <tr>
            <th>Rank</th>
            <th>Alternatif</th>
            <?php foreach ($nama_kriteria as $k) echo "<th>$k</th>"; ?>
            <th>Total</th>
        </tr>
        <?php foreach ($hasil as $i => $row): ?>
        <tr>
            <td><?= $i+1 ?></td>
            <td><?= htmlspecialchars($row['nama']) ?></td>
            <?php foreach ($row['nilai'] as $v) echo "<td>".number_format($v,4)."</td>"; ?>
            <td><strong><?= number_format($row['total'],4) ?></strong></td>
        </tr>
        <?php endforeach; ?>
    </table>
</div>

<a href="alternatif.php" class="back" style="display:inline-block;margin-top:20px;text-decoration:none;color:white;background:#2980b9;padding:10px 20px;border-radius:6px;">← Kembali ke Data Alternatif</a>

</body>
</html>
