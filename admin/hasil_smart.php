
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
