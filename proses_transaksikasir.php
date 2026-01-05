<?php
session_start();
// Pastikan hanya kasir yang bisa memproses
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'kasir') {
    header("Location: login.php");
    exit();
}

// 1. Ambil data dari FORM (gunakan POST)
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Pastikan koneksi.php sudah terhubung ke database ($conn)
    include 'koneksi.php'; 

    // Ambil data dan lindungi dari SQL Injection (Gunakan prepared statements)
    $nama_pelanggan = $_POST['nama_pelanggan'];
    $id_layanan = (int)$_POST['id_layanan'];
    $berat = (float)$_POST['berat'];
    $kasir_id = (int)$_POST['kasir_id'];
    $status = $_POST['status'];
    $tanggal_masuk = $_POST['tanggal_masuk'];
    
    // Hitung Total Biaya (Contoh sederhana: 8000 * Berat. Dalam praktik nyata, ini dari tabel layanan)
    // Asumsi: Kita asumsikan harga per kg Rp 8000 untuk contoh ini
    $harga_per_kg = 8000; 
    $total_bayar = $berat * $harga_per_kg;

    // 2. Tulis SQL Query INSERT
    // Pastikan nama tabel dan kolom sesuai dengan database Anda!
    $sql = "INSERT INTO transaksi (nama_pelanggan, id_layanan, berat, total_bayar, kasir_id, status_pesanan, tanggal_masuk) 
            VALUES (?, ?, ?, ?, ?, ?, ?)";

    // 3. Gunakan Prepared Statement untuk keamanan
    $stmt = $conn->prepare($sql);
    
    // Bind parameter (s=string, i=integer, d=double/float)
    $stmt->bind_param("siddiss", $nama_pelanggan, $id_layanan, $berat, $total_bayar, $kasir_id, $status, $tanggal_masuk);
    
    // 4. Jalankan Query
    if ($stmt->execute()) {
        // Jika sukses, alihkan kembali ke dashboard
        header("Location: dashboard_kasir.php?status=sukses");
        exit();
    } else {
        // Jika gagal
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
} else {
    // Jika diakses tanpa submit form
    header("Location: transaksi_baru.php");
    exit();
}
?>