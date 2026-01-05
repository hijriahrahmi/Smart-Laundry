<?php
session_start();
include 'koneksi.php';

// Cegah akses tanpa login
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

// ===============================
// GENERATE ID PEMBAYARAN OTOMATIS
// ===============================
$prefix = 'TR-' . date('ymd');

$sql_last = "SELECT id_pembayaran FROM riwayat_transaksi 
             WHERE id_pembayaran LIKE '$prefix%' 
             ORDER BY id_pembayaran DESC LIMIT 1";
$result = mysqli_query($conn, $sql_last);

if ($row = mysqli_fetch_assoc($result)) {
    $last_number = (int)substr($row['id_pembayaran'], -3);
} else {
    $last_number = 0;
}

$id_pembayaran_baru = $prefix . '-' . str_pad($last_number + 1, 3, '0', STR_PAD_LEFT);

// =====================
// PROSES SIMPAN DATA
// =====================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $id_pembayaran   = $id_pembayaran_baru;
    $tanggal_bayar   = $_POST['tanggal_bayar'];
    $total_harga     = $_POST['total_harga'];
    $jumlah_bayar    = $_POST['jumlah_bayar'];
    $metode          = $_POST['metode'];
    $status_bayar    = $_POST['status_bayar'];
    $status_pesanan  = $_POST['status_pesanan'];

    $kode_bayar = NULL;
    $rekening_tujuan = NULL;

    // Jika QRIS
    if ($metode === 'QRIS') {
        $kode_bayar = 'QR-' . date('Ymd-His');
    }

    // Jika Transfer
    if ($metode === 'Transfer') {
        $rekening_tujuan = '1234567890 (BCA a.n Laundry Bersih)';
    }

    $sql = "INSERT INTO riwayat_transaksi 
        (id_pembayaran, tanggal_bayar, total_harga, jumlah_bayar, metode, kode_bayar, rekening_tujuan, status_bayar, status_pesanan)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param(
        "sssddssss",
        $id_pembayaran,
        $tanggal_bayar,
        $total_harga,
        $jumlah_bayar,
        $metode,
        $kode_bayar,
        $rekening_tujuan,
        $status_bayar,
        $status_pesanan
    );

    if ($stmt->execute()) {
        echo "<script>alert('✅ Transaksi berhasil disimpan'); window.location='riwayat_transaksi.php';</script>";
        exit();
    } else {
        echo "Error: " . $stmt->error;
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Buat Transaksi</title>

<style>
body {
    font-family: Arial;
    background: #f4f6f8;
}
.container {
    width: 450px;
    margin: 30px auto;
    background: #fff;
    padding: 25px;
    border-radius: 10px;
    box-shadow: 0 0 10px #ccc;
}
h2 {
    text-align: center;
}
label {
    font-weight: bold;
}
input, select {
    width: 100%;
    padding: 10px;
    margin: 8px 0 15px;
    border-radius: 6px;
    border: 1px solid #aaa;
}
input[readonly] {
    background: #eee;
}
button {
    width: 100%;
    padding: 12px;
    background: #28a745;
    color: #fff;
    border: none;
    font-size: 16px;
    border-radius: 6px;
    cursor: pointer;
}
button:hover {
    background: #218838;
}
.back {
    display: block;
    text-align: center;
    margin-top: 10px;
    background: #007bff;
    color: #fff;
    padding: 10px;
    text-decoration: none;
    border-radius: 6px;
}
.back:hover {
    background: #0056b3;
}
.qris-box {
    text-align: center;
    background: #f1f1f1;
    padding: 15px;
    border-radius: 8px;
}
</style>
</head>

<body>

<div class="container">
<h2>💳 Transaksi Baru</h2>

<form method="POST">

<label>ID Pembayaran</label>
<input type="text" value="<?= $id_pembayaran_baru ?>" readonly>

<label>Tanggal Bayar</label>
<input type="date" name="tanggal_bayar" required>

<label>Total Harga</label>
<input type="number" step="0.01" name="total_harga" required>

<label>Jumlah Bayar</label>
<input type="number" step="0.01" name="jumlah_bayar" required>

<label>Metode Pembayaran</label>
<select name="metode" id="metode" required>
    <option value="">-- Pilih --</option>
    <option value="Cash">Cash</option>
    <option value="QRIS">QRIS</option>
    <option value="Transfer">Transfer</option>
</select>

<!-- QRIS -->
<div id="qrisBox" class="qris-box" style="display:none;">
    <label>Scan QRIS</label><br><br>
    <img src="assets/qris.png" width="200"><br>
    <small>Silakan scan untuk membayar</small>
</div>

<!-- TRANSFER -->
<div id="transferBox" style="display:none;">
    <label>Rekening Tujuan</label>
    <input type="text" value="1234567890 (BCA a.n Laundry Bersih)" readonly>
</div>

<label>Status Pembayaran</label>
<select name="status_bayar" required>
    <option value="Lunas">Lunas</option>
    <option value="Belum Lunas">Belum Lunas</option>
</select>

<label>Status Pesanan</label>
<select name="status_pesanan" required>
    <option value="Proses">Proses</option>
    <option value="Selesai">Selesai</option>
</select>

<button type="submit">💾 Simpan Transaksi</button>
</form>

<a href="dashboard_kasir.php" class="back">⬅ Kembali</a>
</div>

<script>
const metode = document.getElementById("metode");
const qrisBox = document.getElementById("qrisBox");
const transferBox = document.getElementById("transferBox");

metode.addEventListener("change", function () {
    if (this.value === "QRIS") {
        qrisBox.style.display = "block";
        transferBox.style.display = "none";
    } else if (this.value === "Transfer") {
        qrisBox.style.display = "none";
        transferBox.style.display = "block";
    } else {
        qrisBox.style.display = "none";
        transferBox.style.display = "none";
    }
});
</script>

</body>
</html>
