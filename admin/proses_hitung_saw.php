<?php
session_start();
include '../config.php';

if (!isset($_SESSION['username']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit;
}

// Ambil alternatif
$q = mysqli_query($koneksi, "SELECT * FROM alternatif ORDER BY id_alternatif ASC");
$alternatif = [];
while ($r = mysqli_fetch_assoc($q)) {
    $alternatif[] = [
        'id' => $r['id_alternatif'],
        'nama' => $r['nama_alternatif'],
        'values' => [$r['c1'], $r['c2'], $r['c3'], $r['c4'], $r['c5']],
    ];
}
if (empty($alternatif)) die("<h2>Tidak ada data alternatif.</h2>");

// Bobot & kategori
$bobot = [0.3,0.25,0.14,0.2,0.2];
$kategori = ['benefit','cost','cost','benefit','benefit'];
$matriks = array_column($alternatif,'values');

// Hitung min max
$max=[];$min=[];
for($j=0;$j<5;$j++){
    $col=array_column($matriks,$j);
    $max[$j]=max($col);
    $min[$j]=min($col);
}

// Normalisasi & nilai total
$norm=[];$hasil=[];
foreach($matriks as $i=>$row){
    $total=0;
    foreach($row as $j=>$val){
        if($kategori[$j]=='benefit'){
            $norm[$i][$j]=$val/$max[$j];
        }else{
            $norm[$i][$j]=$min[$j]/$val;
        }
        $total+=round($norm[$i][$j]*$bobot[$j],4);
    }
    $hasil[]= ['id'=>$alternatif[$i]['id'],'nama'=>$alternatif[$i]['nama'],'nilai'=>$norm[$i],'total'=>round($total,4)];
}

// Urutkan
usort($hasil,fn($a,$b)=>$b['total']<=>$a['total']);

// Simpan ke tabel hasil
mysqli_query($koneksi,"DELETE FROM hasil WHERE metode='SAW'");
foreach($hasil as $rank=>$row){
    mysqli_query($koneksi,"INSERT INTO hasil (id_alternatif, metode, skor, rank) VALUES ('{$row['id']}','SAW','{$row['total']}','".($rank+1)."')");
}

// Nama kriteria
$nama_kriteria=['IPK','PENGHASILAN','TANGGUNGAN','PRESTASI','KEAKTIFAN'];
?>
<!DOCTYPE html>
<html>
<head><title>Hasil SAW</title>
<style>
body{font-family:Arial;padding:20px;background:#f4f4f4;}
.box{padding:20px;border-radius:10px;margin-bottom:30px;background:white;}
h2{margin-top:0;color:#1565c0;}
table{border-collapse:collapse;width:100%;margin-top:10px;}
th,td{border:1px solid #ccc;padding:8px;text-align:center;}
strong{color:#d32f2f;}
</style></head>
<body>

<div class="box">
    <h2>1. Matriks Normalisasi SAW</h2>
    <table>
        <tr><th>Alternatif</th><?php foreach($nama_kriteria as $k)echo"<th>$k</th>";?></tr>
        <?php foreach($hasil as $row): ?>
        <tr>
            <td><?=htmlspecialchars($row['nama'])?></td>
            <?php foreach($row['nilai']as$v)echo"<td>".number_format($v,4)."</td>";?>
        </tr>
        <?php endforeach;?>
    </table>
</div>

<div class="box">
    <h2>2. Hasil Akhir SAW</h2>
    <table>
        <tr><th>Rank</th><th>Alternatif</th><?php foreach($nama_kriteria as$k)echo"<th>$k</th>";?><th>Total</th></tr>
        <?php foreach($hasil as$i=>$row):?>
        <tr>
            <td><?=$i+1?></td>
            <td><?=htmlspecialchars($row['nama'])?></td>
            <?php foreach($row['nilai']as$v)echo"<td>".number_format($v,4)."</td>";?>
            <td><strong><?=number_format($row['total'],4)?></strong></td>
        </tr>
        <?php endforeach;?>
    </table>
</div>

<a href="alternatif.php" style="display:inline-block;margin-top:20px;text-decoration:none;color:white;background:#1565c0;padding:10px 20px;border-radius:6px;">← Kembali ke Data Alternatif</a>

</body></html>
