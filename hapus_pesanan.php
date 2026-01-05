<?php
// File: hapus_pesanan.php

session_start();
// Asumsi 'koneksi.php' berisi detail koneksi $host, $user, $pass, $db
include 'koneksi.php'; 

// ===============================================
// 1. CEK OTORISASI (HANYA UNTUK ADMIN/KASIR)
// ===============================================
if (!isset($_SESSION['username']) || ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'kasir')) {
    // Alihkan jika tidak login atau tidak memiliki peran yang benar
    header("Location: login.php");
    exit();
}

// ===============================================
// 2. KONEKSI KE DATABASE
// ===============================================
$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Koneksi ke database gagal: " . $conn->connect_error);
}

// ===============================================
// 3. PROSES HAPUS DATA
// ===============================================
if (isset($_GET['id']) && !empty($_GET['id'])) { 
    // Ambil ID dan pastikan sebagai integer untuk keamanan
    $order_id_to_delete = (int)$_GET['id']; 

    // Mulai transaksi untuk memastikan operasi delete berjalan atomik
    $conn->begin_transaction();
    $success = true;

    try {
        // --- LANGKAH 1: Hapus data terkait di tabel anak (untuk memenuhi Foreign Key Constraint) ---
        
        // Asumsi Tabel Anak 1: detail_pesanan
        // Meskipun kode Anda menggunakan try-catch, kita tetap menggunakan prepared statement.
        $sql_delete_detail = "DELETE FROM detail_pesanan WHERE order_id = ?";
        $stmt_detail = $conn->prepare($sql_delete_detail);
        if ($stmt_detail) {
            $stmt_detail->bind_param("i", $order_id_to_delete);
            $stmt_detail->execute();
            $stmt_detail->close();
        }
        
        // ASUMSI: Jika ada tabel anak lain (misal: riwayat_status, pembayaran)
        // Lakukan penghapusan di sini juga:
        /*
        $sql_delete_status = "DELETE FROM riwayat_status WHERE order_id = ?";
        $stmt_status = $conn->prepare($sql_delete_status);
        if ($stmt_status) {
            $stmt_status->bind_param("i", $order_id_to_delete);
            $stmt_status->execute();
            $stmt_status->close();
        }
        */

        // --- LANGKAH 2: Hapus data induk (Tabel Pesanan) ---
        $sql_delete_induk = "DELETE FROM pesanan WHERE order_id = ?";
        
        $stmt_induk = $conn->prepare($sql_delete_induk);
        $stmt_induk->bind_param("i", $order_id_to_delete); 
        
        if ($stmt_induk->execute()) {
            // Commit transaksi jika semua penghapusan berhasil
            $conn->commit();
            $stmt_induk->close();
            $conn->close();
            
            // Redirect ke halaman status_pesanan.php dengan pesan sukses
            header("Location: status_pesanan.php?action=deleted&id=" . urlencode($order_id_to_delete));
            exit();
        } else {
            throw new Exception("Gagal menghapus pesanan induk. " . $stmt_induk->error);
        }

    } catch (Exception $e) {
        // Rollback transaksi jika terjadi kesalahan
        $conn->rollback();
        
        // Tampilkan pesan error dan redirect
        $error_message = urlencode("❌ Gagal Hapus Pesanan #$order_id_to_delete. Detail: " . $e->getMessage());
        header("Location: status_pesanan.php?error=db_error&msg=" . $error_message);
        exit();
    }
} else {
    // Jika tidak ada parameter ID yang dikirim
    header("Location: status_pesanan.php?error=missing_id");
    exit();
}

// Tutup koneksi jika belum ditutup
if ($conn) {
    $conn->close();
}
?>