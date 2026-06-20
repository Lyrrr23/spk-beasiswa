<?php
session_start();
include '../config.php';

if (!isset($_SESSION['username'])) {
    header("Location: ../login.php");
    exit;
}

// ================== AMBIL DATA DARI DATABASE ==================
// Ambil hasil SAW
$q_saw = mysqli_query($koneksi, "
    SELECT a.nama_alternatif AS nama, h.skor, h.rank 
    FROM hasil h 
    JOIN alternatif a ON h.id_alternatif = a.id_alternatif 
    WHERE h.metode='SAW' 
    ORDER BY h.rank ASC
");
$saw = [];
while ($d = mysqli_fetch_assoc($q_saw)) $saw[] = $d;

// Ambil hasil SMART
$q_smart = mysqli_query($koneksi, "
    SELECT a.nama_alternatif AS nama, h.skor, h.rank 
    FROM hasil h 
    JOIN alternatif a ON h.id_alternatif = a.id_alternatif 
    WHERE h.metode='SMART' 
    ORDER BY h.rank ASC
");
$smart = [];
while ($d = mysqli_fetch_assoc($q_smart)) $smart[] = $d;

// Ambil rekomendasi terbaik
$rekom_saw = !empty($saw) ? $saw[0] : ['nama'=>'-', 'skor'=>0];
$rekom_smart = !empty($smart) ? $smart[0] : ['nama'=>'-', 'skor'=>0];
?>

<!DOCTYPE html>
<html>
<head>
    <title>Rekomendasi Penerima Beasiswa - SMART & SAW</title>
    <style>
    body {
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        min-height: 100vh;
        margin: 0;
        padding: 20px;
    }
    .container {
        max-width: 1200px;
        margin: 0 auto;
        background: white;
        border-radius: 15px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.3);
        padding: 30px;
    }
    .header {
        text-align: center;
        color: #333;
        margin-bottom: 30px;
    }
    .header h1 {
        font-size: 2.5em;
        margin-bottom: 10px;
        color: #4a4a4a;
    }
    .header p {
        font-size: 1.2em;
        color: #555;
    }
    .results-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 30px;
        margin-bottom: 30px;
    }
    .method-box {
        background: #f9f9f9;
        padding: 25px;
        border-radius: 12px;
        box-shadow: 0 8px 20px rgba(0,0,0,0.15);
        transition: transform 0.3s ease;
    }
    .method-box:hover {
        transform: translateY(-5px);
    }
    .smart-box { border-left: 8px solid #43a047; }
    .topsis-box { border-left: 8px solid #ff5722; }
    .method-title {
        font-size: 1.8em;
        margin: 0 0 15px 0;
        color: #333;
    }
    .smart-box .method-title { color: #2e7d32; }
    .topsis-box .method-title { color: #d84315; }
    .result-content {
        font-size: 1.1em;
        line-height: 1.6;
    }
    .winner-name {
        font-size: 1.4em;
        font-weight: bold;
        color: #1a237e;
        margin-bottom: 10px;
    }
    .score {
        font-size: 1.2em;
        font-weight: bold;
        padding: 10px 15px;
        border-radius: 8px;
        display: inline-block;
        margin-top: 10px;
    }
    .smart-score { background-color: #c8e6c9; color: #1b5e20; }
    .topsis-score { background-color: #ffccbc; color: #bf360c; }
    .rankings {
        background: #f9f9f9;
        padding: 30px;
        border-radius: 12px;
        box-shadow: 0 8px 20px rgba(0,0,0,0.15);
        margin-bottom: 30px;
    }
    .rankings h2 {
        color: #333;
        text-align: center;
        margin-bottom: 30px;
        font-size: 1.8em;
    }
    .ranking-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 30px;
    }
    .ranking-list {
        background: white;
        padding: 20px;
        border-radius: 10px;
    }
    .ranking-list h3 {
        margin-top: 0;
        color: #495057;
        text-align: center;
        font-size: 1.4em;
        margin-bottom: 20px;
    }
    .ranking-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 10px 15px;
        margin-bottom: 10px;
        background: #f8f9fa;
        border-radius: 8px;
        box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    }
    .rank-number {
        background: #6c757d;
        color: white;
        width: 30px;
        height: 30px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
    }
    .rank-number.first { background: #ffd700; color: #333; }
    .rank-number.second { background: #c0c0c0; color: #333; }
    .rank-number.third { background: #cd7f32; color: white; }
    .back, .print-btn {
        display: inline-block;
        text-decoration: none;
        color: white;
        background: linear-gradient(45deg, #2980b9, #3498db);
        padding: 15px 30px;
        border-radius: 30px;
        font-size: 1.1em;
        font-weight: bold;
        transition: all 0.3s ease;
        box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        margin: 5px;
    }
    .back:hover, .print-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(0,0,0,0.3);
    }
    .text-center { text-align: center; }
    @media (max-width: 768px) {
        .results-grid, .ranking-grid { grid-template-columns: 1fr; }
        .header h1 { font-size: 2em; }
    }
</style>
</head>
<body>

<div class="container">
    <div class="header">
        <h1>🎓 Rekomendasi Penerima Beasiswa</h1>
        <p>Perbandingan Metode SMART dan SAW</p>
    </div>

    <div class="results-grid">
        <div class="method-box smart-box">
            <h2 class="method-title">📊 Metode SMART</h2>
            <div class="result-content">
                <div class="winner-name"><?= htmlspecialchars($rekom_smart['nama']) ?></div>
                <p>Direkomendasikan sebagai penerima beasiswa berdasarkan metode SMART (Simple Multi-Attribute Rating Technique)</p>
                <div class="score smart-score">
                    Skor SMART: <?= number_format($rekom_smart['skor'], 4) ?>
                </div>
            </div>
        </div>

        <div class="method-box topsis-box">
            <h2 class="method-title">📈 Metode SAW</h2>
            <div class="result-content">
                <div class="winner-name"><?= htmlspecialchars($rekom_saw['nama']) ?></div>
                <p>Direkomendasikan sebagai penerima beasiswa berdasarkan metode SAW (Simple Additive Weighting)</p>
                <div class="score topsis-score">
                    Skor SAW: <?= number_format($rekom_saw['skor'], 4) ?>
                </div>
            </div>
        </div>
    </div>

    <div class="rankings">
        <h2>🏆 Ranking Lengkap</h2>
        <div class="ranking-grid">
            <div class="ranking-list">
                <h3>Ranking SMART</h3>
                <?php foreach ($smart as $i => $result): ?>
                <div class="ranking-item">
                    <div style="display: flex; align-items: center; gap: 15px;">
                        <div class="rank-number <?= $i == 0 ? 'first' : ($i == 1 ? 'second' : ($i == 2 ? 'third' : '')) ?>">
                            <?= $i + 1 ?>
                        </div>
                        <span><?= htmlspecialchars($result['nama']) ?></span>
                    </div>
                    <span style="font-weight: bold; color: #2e7d32;">
                        <?= number_format($result['skor'], 4) ?>
                    </span>
                </div>
                <?php endforeach; ?>
            </div>

            <div class="ranking-list">
                <h3>Ranking SAW</h3>
                <?php foreach ($saw as $i => $result): ?>
                <div class="ranking-item">
                    <div style="display: flex; align-items: center; gap: 15px;">
                        <div class="rank-number <?= $i == 0 ? 'first' : ($i == 1 ? 'second' : ($i == 2 ? 'third' : '')) ?>">
                            <?= $i + 1 ?>
                        </div>
                        <span><?= htmlspecialchars($result['nama']) ?></span>
                    </div>
                    <span style="font-weight: bold; color: #d84315;">
                        <?= number_format($result['skor'], 4) ?>
                    </span>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <div class="text-center">
        <a href="alternatif.php" class="back">← Kembali ke Data Alternatif</a>
        <button onclick="window.print()" class="print-btn" style="background: linear-gradient(45deg, #16a085, #1abc9c); border:none; cursor:pointer;">
            🖨️ Cetak Halaman
        </button>
    </div>
</div>

</body>
</html>
