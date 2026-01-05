<?php
session_start();
include 'koneksi.php';
require __DIR__ . '/dompdf/autoload.inc.php';
use Dompdf\Dompdf;

$bulan = $_GET['bulan']; // YYYY-MM

$conn = new mysqli($host, $user, $pass, $db);

$sql = "SELECT id_pembayaran, total_harga, metode, status_bayar 
        FROM riwayat_transaksi
        WHERE DATE_FORMAT(tanggal_bayar, '%Y-%m') = ?
        ORDER BY tanggal_bayar ASC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $bulan);
$stmt->execute();
$result = $stmt->get_result();

$html = "
<style>
body { font-family: Arial; font-size: 12px; }
h2 { text-align:center; }
table { width:100%; border-collapse: collapse; margin-top:10px; }
th, td { border:1px solid #000; padding:6px; }
th { background:#f2f2f2; }
</style>

<h2>LAPORAN BULANAN TRANSAKSI</h2>
<p><b>Bulan:</b> $bulan</p>

<table>
<tr>
    <th>ID</th>
    <th>Total</th>
    <th>Metode</th>
    <th>Status</th>
</tr>
";

$total = 0;
while ($row = $result->fetch_assoc()) {
    $total += $row['total_harga'];
    $html .= "
    <tr>
        <td>{$row['id_pembayaran']}</td>
        <td>Rp ".number_format($row['total_harga'],0,',','.')."</td>
        <td>{$row['metode']}</td>
        <td>{$row['status_bayar']}</td>
    </tr>";
}

$html .= "
<tr>
    <th colspan='3'>TOTAL BULANAN</th>
    <th>Rp ".number_format($total,0,',','.')."</th>
</tr>
</table>
";

$pdf = new Dompdf();
$pdf->loadHtml($html);
$pdf->setPaper('A4', 'portrait');
$pdf->render();
$pdf->stream("laporan_bulanan_$bulan.pdf", ["Attachment" => false]);
