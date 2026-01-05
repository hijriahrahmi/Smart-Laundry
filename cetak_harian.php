<?php
session_start();
include 'koneksi.php';

// validasi login
if (!isset($_SESSION['username'])) {
    die('Akses ditolak');
}

// load dompdf
require __DIR__ . '/dompdf/autoload.inc.php';
use Dompdf\Dompdf;

$tanggal = $_GET['tanggal'] ?? date('Y-m-d');
$tanggal_format = date('d F Y', strtotime($tanggal));

$conn = new mysqli($host, $user, $pass, $db);

// query harian
$sql = "SELECT id_pembayaran, total_harga, metode, status_bayar 
        FROM riwayat_transaksi 
        WHERE tanggal_bayar = ?
        ORDER BY id_pembayaran ASC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $tanggal);
$stmt->execute();
$result = $stmt->get_result();

// HTML PDF
$html = "
<style>
body { font-family: Arial; font-size: 12px; }
h2 { text-align:center; }
table { width:100%; border-collapse: collapse; margin-top:10px; }
th, td { border:1px solid #000; padding:6px; }
th { background:#f2f2f2; }
.footer { margin-top:40px; text-align:right; }
</style>

<h2>LAPORAN HARIAN TRANSAKSI</h2>
<p><b>Tanggal:</b> $tanggal_format</p>

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
    <th colspan='3'>TOTAL</th>
    <th>Rp ".number_format($total,0,',','.')."</th>
</tr>
</table>

<div class='footer'>
    Dicetak oleh,<br><br>
    <b>{$_SESSION['username']}</b>
</div>
";

$pdf = new Dompdf();
$pdf->loadHtml($html);
$pdf->setPaper('A4', 'portrait');
$pdf->render();
$pdf->stream("laporan_harian_$tanggal.pdf", ["Attachment" => false]);
