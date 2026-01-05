<?php
session_start();
include 'koneksi.php';

// Cek login
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

// Cek apakah id dikirim
if (!isset($_GET['id'])) {
    echo "<script>alert('ID transaksi tidak ditemukan'); window.location='riwayat_transaksi.php';</script>";
    exit();
}

$id_pembayaran = $_GET['id'];

// Hapus data transaksi
$query = "DELETE FROM riwayat_transaksi WHERE id_pembayaran='$id_pembayaran'";
if (mysqli_query($conn, $query)) {
    echo "<script>alert('Transaksi berhasil dihapus'); window.location='riwayat_transaksi.php';</script>";
} else {
    echo "<script>alert('Gagal menghapus transaksi: " . mysqli_error($conn) . "'); window.location='riwayat_transaksi.php';</script>";
}
?>
