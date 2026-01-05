<?php
session_start();
include 'koneksi.php'; 

// Memastikan hanya user dengan role 'kasir' yang bisa mengakses
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'kasir') {
    header("Location: login.php");
    exit();
}

// 1. Ambil ID dari URL
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: cari_pesanan.php?error=no_id");
    exit();
}

$id_pesanan = $_GET['id'];
$total_harga = isset($_GET['total']) ? $_GET['total'] : 0; // Mengambil total harga dari GET

// 2. KONEKSI KE DATABASE
$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Koneksi ke database gagal: " . $conn->connect_error);
}

// 3. LOGIKA UPDATE PELUNASAN
// Asumsi: Kita mengupdate status menjadi 'Lunas', status pesanan menjadi 'Selesai', 
// dan total 'jumlah_bayar' diisi dengan 'total_harga' karena ini adalah pelunasan.
$new_status_bayar = 'Lunas';
$new_status_pesanan = 'Selesai'; 

$sql_update = "UPDATE riwayat_transaksi SET status_bayar = ?, status_pesanan = ?, jumlah_bayar = ? WHERE id_pembayaran = ?";
$stmt_update = $conn->prepare($sql_update);
// Menggunakan total_harga sebagai jumlah_bayar untuk melunasi
$stmt_update->bind_param("ssds", $new_status_bayar, $new_status_pesanan, $total_harga, $id_pesanan); 

if ($stmt_update->execute()) {
    $stmt_update->close();
    $conn->close();
    
    // REDIRECT SUKSES: Langsung ke halaman cetak nota
    header("Location: cetak_nota.php?id=" . urlencode($id_pesanan));
    
    exit();
} else {
    $error_message = $conn->error;
    $stmt_update->close();
    $conn->close();
    // Redirect jika gagal
    header("Location: detail_pesanan.php?id=" . urlencode($id_pesanan) . "&error=update_gagal&msg=" . urlencode($error_message));
    exit();
}
?>