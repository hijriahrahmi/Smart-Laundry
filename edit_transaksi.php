<?php
session_start();
// Pastikan koneksi.php menggunakan mysqli object (misal: $conn = new mysqli(...))
include 'koneksi.php'; 

// Cek login
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

// 1. **Koneksi ke Database** (Jika koneksi.php belum menggunakan mysqli object)
// Jika $conn dari koneksi.php adalah objek mysqli, lewati bagian ini
if (!isset($conn) || get_class($conn) !== 'mysqli') {
    // Asumsi Anda menggunakan variabel koneksi dari file koneksi.php
    $conn = new mysqli($host, $user, $pass, $db);
    if ($conn->connect_error) {
        die("Koneksi ke database gagal: " . $conn->connect_error);
    }
}


// Ambil data transaksi berdasarkan id
if (!isset($_GET['id'])) {
    echo "<script>alert('ID transaksi tidak ditemukan'); window.location='riwayat_transaksi.php';</script>";
    exit();
}

$id_pembayaran = $_GET['id'];

// --- MENGGUNAKAN PREPARED STATEMENT UNTUK SELECT ---
$stmt_select = $conn->prepare("SELECT * FROM riwayat_transaksi WHERE id_pembayaran = ?");
$stmt_select->bind_param("s", $id_pembayaran);
$stmt_select->execute();
$res = $stmt_select->get_result();
$edit_data = $res->fetch_assoc();
$stmt_select->close();

if (!$edit_data) {
    echo "<script>alert('Data transaksi tidak ditemukan'); window.location='riwayat_transaksi.php';</script>";
    exit();
}

// Proses update data
if (isset($_POST['edit'])) {
    // Ambil data POST
    $tanggal_bayar  = $_POST['tanggal_bayar'];
    $total_harga    = (float)$_POST['total_harga']; // Konversi ke float
    $jumlah_bayar   = (float)$_POST['jumlah_bayar']; // Konversi ke float
    $metode         = $_POST['metode'];
    $status_bayar   = $_POST['status_bayar'];
    $status_pesanan = $_POST['status_pesanan'];
    
    // --- MENGGUNAKAN PREPARED STATEMENT UNTUK UPDATE (JAUH LEBIH AMAN) ---
    $query = "UPDATE riwayat_transaksi SET 
        tanggal_bayar = ?,
        total_harga = ?,
        jumlah_bayar = ?,
        metode = ?,
        status_bayar = ?,
        status_pesanan = ?
        WHERE id_pembayaran = ?";

    $stmt_update = $conn->prepare($query);
    // Tipe data: string, double, double, string, string, string, string (sddsss)
    $stmt_update->bind_param("sddssss", 
        $tanggal_bayar, $total_harga, $jumlah_bayar, $metode, $status_bayar, $status_pesanan, $id_pembayaran
    );

    if ($stmt_update->execute()) {
        echo "<script>alert('Transaksi berhasil diupdate'); window.location='riwayat_transaksi.php';</script>";
    } else {
        // Tampilkan error jika update gagal
        die("Error saat mengupdate transaksi: " . $stmt_update->error); 
    }
    $stmt_update->close();
}

// Tutup koneksi jika dibuka di file ini
// $conn->close(); // Biasanya ditutup setelah semua query selesai
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Edit Transaksi</title>
</head>
<body>
    <h2>Edit Transaksi ID <?= htmlspecialchars($edit_data['id_pembayaran']); ?></h2>

    <form method="POST">
        <input type="hidden" name="id_pembayaran_original" value="<?= htmlspecialchars($edit_data['id_pembayaran']); ?>">
        
        <label>Tanggal Bayar:</label><br>
        <input type="date" name="tanggal_bayar" value="<?= htmlspecialchars($edit_data['tanggal_bayar']); ?>" required><br>
        
        <label>Total Harga:</label><br>
        <input type="number" step="0.01" name="total_harga" value="<?= htmlspecialchars($edit_data['total_harga']); ?>" required><br>
        
        <label>Jumlah Bayar:</label><br>
        <input type="number" step="0.01" name="jumlah_bayar" value="<?= htmlspecialchars($edit_data['jumlah_bayar']); ?>" required><br>
        
        <label>Metode:</label><br>
        <input type="text" name="metode" value="<?= htmlspecialchars($edit_data['metode']); ?>" required><br>
        
        <label>Status Bayar:</label><br>
        <select name="status_bayar">
            <option value="Lunas" <?= $edit_data['status_bayar']=='Lunas' ? 'selected' : ''; ?>>Lunas</option>
            <option value="Belum Lunas" <?= $edit_data['status_bayar']=='Belum Lunas' ? 'selected' : ''; ?>>Belum Lunas</option>
        </select><br>
        
        <label>Status Pesanan:</label><br>
        <select name="status_pesanan">
            <option value="Selesai" <?= $edit_data['status_pesanan']=='Selesai' ? 'selected' : ''; ?>>Selesai</option>
            <option value="Proses" <?= $edit_data['status_pesanan']=='Proses' ? 'selected' : ''; ?>>Proses</option>
            <option value="Menunggu" <?= $edit_data['status_pesanan']=='Menunggu' ? 'selected' : ''; ?>>Menunggu</option>
        </select><br><br>
        
        <button type="submit" name="edit">Update</button>
        <a href="riwayat_transaksi.php"><button type="button">Batal</button></a>
    </form>
</body>
</html>