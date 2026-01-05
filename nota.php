<?php
include 'koneksi.php';

$order_id = $_GET['order_id'];
$q = mysqli_query($koneksi, "SELECT * FROM pesanan WHERE order_id='$order_id'");
$d = mysqli_fetch_assoc($q);
?>

<html>
<head>
<title>Cetak Nota</title>

<style>
body{
    font-family: Arial;
    padding: 10px;
}
.nota{
    width: 280px;
    padding: 10px;
}
hr{
    border: none;
    border-bottom: 1px dashed #777;
}
</style>

<script>
// Tutup otomatis setelah print (opsional)
window.onafterprint = function(){
    window.close();
};
</script>

</head>

<body onload="window.print();">

<div class="nota">
    <h3 style="text-align:center;">NOTA PEMBAYARAN</h3>
    <hr>

    <p>No Pesanan : <?= $d['order_id']; ?></p>
    <p>Pelanggan  : <?= $d['nama_pelanggan']; ?></p>
    <p>Tanggal    : <?= $d['tanggal_pesanan']; ?></p>

    <hr>
    <p>Total   : Rp <?= number_format($d['total']); ?></p>
    <p>Dibayar : Rp <?= number_format($d['dibayar']); ?></p>
    <p>Status  : <?= $d['status']; ?></p>
    <hr>

    <p style="text-align:center;">Terima Kasih 🙏</p>
</div>

</body>
</html>
