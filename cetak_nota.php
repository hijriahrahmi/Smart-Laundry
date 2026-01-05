<?php
session_start();
include 'koneksi.php'; // Koneksi ke database

// 1. Validasi Akses dan ID
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'kasir') {
    header("Location: login.php");
    exit();
}

if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("ID Pesanan tidak ditemukan.");
}

$id_pesanan = $_GET['id'];

// 2. KONEKSI KE DATABASE
$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Koneksi ke database gagal: " . $conn->connect_error);
}

// 3. Ambil Data Pesanan Utama (Ringkasan)
$sql_pesanan = "SELECT * FROM riwayat_transaksi WHERE id_pembayaran = ?";
$stmt_pesanan = $conn->prepare($sql_pesanan);
$stmt_pesanan->bind_param("s", $id_pesanan);
$stmt_pesanan->execute();
$result_pesanan = $stmt_pesanan->get_result();
$pesanan = $result_pesanan->fetch_assoc();
$stmt_pesanan->close();

if (!$pesanan) {
    $conn->close();
    die("Nota pesanan tidak ditemukan!");
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Nota Transaksi #<?= htmlspecialchars($id_pesanan); ?></title>
    <style>
        body { 
            font-family: monospace; 
            font-size: 10px; 
            width: 80mm; /* Lebar standar printer thermal, sesuaikan jika perlu */
            margin: 0 auto; 
            padding: 10px;
        }
        .center { text-align: center; }
        .right { text-align: right; }
        hr { border: 0; border-top: 1px dashed #000; margin: 5px 0; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 5px; }
        table td { padding: 2px 0; }
        
        /* Gaya untuk Tombol (Khusus Tampilan Layar) */
        .btn-back {
            display: inline-block;
            background-color: #f7a01d; /* Warna Orange */
            color: white;
            padding: 8px 15px;
            text-decoration: none;
            border-radius: 4px;
            font-size: 12px;
            margin-bottom: 15px;
            cursor: pointer;
        }

        /* MEDIA QUERY: Menyembunyikan tombol saat dicetak */
        @media print {
            .btn-back {
                display: none; /* Sembunyikan tombol ini saat print */
            }
        }
    </style>
</head>
<body onload="window.print()">
    <a href="detail_pesanan.php?id=<?= urlencode($id_pesanan); ?>" class="btn-back">
        ⬅️ Kembali ke Detail Pesanan
    </a>

    <div class="center">
        <h4>SMART LAUNDRY</h4>
        <p style="margin-top: -10px; margin-bottom: 5px;">Jl. Contoh No. 123, Kota Anda</p>
    </div>
    <hr>
    
    <table>
        <tr>
            <td>Nota ID:</td>
            <td class="right"><?= htmlspecialchars($pesanan['id_pembayaran']); ?></td>
        </tr>
        <tr>
            <td>Tanggal Masuk:</td>
            <td class="right"><?= htmlspecialchars(date('d/m/Y H:i', strtotime($pesanan['tanggal_bayar']))); ?></td>
        </tr>
        <tr>
            <td>Kasir:</td>
            <td class="right"><?= htmlspecialchars($_SESSION['username']); ?></td>
        </tr>
        <tr>
            <td>Pelanggan:</td>
            <td class="right"><?= htmlspecialchars($pesanan['nama_pelanggan']); ?></td>
        </tr>
    </table>
    <hr>
    
    <div class="center" style="margin: 15px 0; font-style: italic;">
        --- Rincian Layanan Tidak Tersedia ---
    </div>

    <hr>
    
    <table>
        <tr>
            <td>Metode Pembayaran:</td>
            <td class="right"><?= htmlspecialchars($pesanan['metode']); ?></td>
        </tr>
        <tr>
            <td>Jumlah Dibayar:</td>
            <td class="right">Rp <?= number_format($pesanan['jumlah_bayar'], 0, ',', '.'); ?></td>
        </tr>
        <tr>
            <td>TOTAL TAGIHAN:</td>
            <td class="right" style="font-size: 14px; font-weight: bold;">Rp <?= number_format($pesanan['total_harga'], 0, ',', '.'); ?></td>
        </tr>
        <tr>
            <td>Status Bayar:</td>
            <td class="right" style="font-weight: bold;"><?= htmlspecialchars(strtoupper($pesanan['status_bayar'])); ?></td>
        </tr>
    </table>
    <hr>

    <div class="center">
        <p>Mohon simpan nota ini untuk pengambilan. <br>
        Terima Kasih!</p>
    </div>
</body>
</html>