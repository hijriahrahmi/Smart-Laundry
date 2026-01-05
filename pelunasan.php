<?php
include 'koneksi.php';

$order_id = $_GET['order_id'];

$q = mysqli_query($koneksi, "SELECT * FROM pesanan WHERE order_id='$order_id'");
$d = mysqli_fetch_assoc($q);

$sisa = $d['total'] - $d['dibayar'];
?>

<h2>Pelunasan Pesanan</h2>

<p><b>Nama Pelanggan:</b> <?= $d['nama_pelanggan']; ?></p>
<p><b>Total:</b> Rp <?= number_format($d['total']); ?></p>
<p><b>Sudah Dibayar:</b> Rp <?= number_format($d['dibayar']); ?></p>
<p><b>Sisa Tagihan:</b> <span style="color:red;">Rp <?= number_format($sisa); ?></span></p>

<hr>

<form action="proses_pelunasan.php" method="POST">
    <input type="hidden" name="order_id" value="<?= $order_id; ?>">
    <input type="hidden" name="sisa" value="<?= $sisa; ?>">

    <label>Jumlah Pelunasan:</label>
    <input type="number" name="bayar" class="form-control" required>

    <br>
    <button class="btn btn-primary">💳 Bayar & Cetak Nota</button>
</form>
