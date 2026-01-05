<?php
// File: status_pesanan.php

// ===============================================
// 1. KONFIGURASI DAN INKLUSI
// ===============================================
session_start();
// Asumsi 'koneksi.php' berisi detail koneksi $host, $user, $pass, $db
include 'koneksi.php'; 

// Cek autentikasi pengguna
if (!isset($_SESSION['username']) || !isset($_SESSION['role'])) {
    header("Location: login.php");
    exit();
}

// Tentukan Peran Pengguna
$user_role = $_SESSION['role'];
$username = $_SESSION['username'];
$is_admin_or_kasir = ($user_role === 'admin' || $user_role === 'kasir');

// === DATA LAYANAN (UNTUK DISPLAY SATUAN) - MOCKUP ===
// Data ini biasanya diambil dari tabel 'layanan' di database
$daftar_layanan = [
    ['id' => 1, 'nama' => 'Cuci Kering Reguler', 'harga' => 8000, 'satuan' => 'per Kg', 'estimasi' => '48 Jam'],
    ['id' => 2, 'nama' => 'Cuci + Setrika', 'harga' => 10000, 'satuan' => 'per Kg', 'estimasi' => '48 Jam'],
    ['id' => 3, 'nama' => 'Cuci Kering Express', 'harga' => 15000, 'satuan' => 'per Kg', 'estimasi' => '6 Jam'],
    ['id' => 4, 'nama' => 'Setrika Saja', 'harga' => 6000, 'satuan' => 'per Kg', 'estimasi' => '24 Jam'],
    ['id' => 5, 'nama' => 'Bed Cover Besar', 'harga' => 35000, 'satuan' => 'per Pcs', 'estimasi' => '72 Jam'],
    ['id' => 6, 'nama' => 'Gordyn Tebal', 'harga' => 45000, 'satuan' => 'per Pcs', 'estimasi' => '72 Jam'],
];
// ========================================================

// ===============================================
// 2. KONEKSI DAN PENGAMBILAN DATA
// ===============================================
$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Koneksi ke database gagal: " . $conn->connect_error);
}

// --- AMBIL DAFTAR NAMA PELANGGAN UNIK + JUMLAH PESANAN (KHUSUS ADMIN/KASIR) ---
$unique_customers_result = null;
if ($is_admin_or_kasir) {
    // Menggunakan GROUP BY dan COUNT untuk mendapatkan total pesanan per pelanggan
    $sql_unique_customers = "SELECT `nama_pelanggan`, COUNT(`order_id`) AS total_orders 
                             FROM pesanan 
                             GROUP BY `nama_pelanggan` 
                             ORDER BY total_orders DESC, `nama_pelanggan` ASC";
    $unique_customers_result = $conn->query($sql_unique_customers);
}
// -------------------------------------------------------------

// 3. **Query untuk Mengambil SEMUA Data Pesanan (Tanpa Filter Pelanggan)**
$all_orders_result = null;
// Query sekarang mengambil SEMUA data pesanan karena filter WHERE dihapus/tidak digunakan
$sql_all_orders = "SELECT `order_id`, `nama_pelanggan`, `tanggal_pesanan`, `jenis_layanan`, `berat_kg`, `total_harga`, `status_pesanan` FROM pesanan ORDER BY tanggal_pesanan DESC";

// Eksekusi Query
$all_orders_result = $conn->query($sql_all_orders);

// Koneksi akan ditutup di akhir file
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>📦 Status Pesanan - Smart Laundry</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        /* Styling Sidebar & Layout */
        :root {
            --primary-color: #3caea3;
            --secondary-color: #1f3936;
        }
        body { margin:0; font-family:'Poppins', sans-serif; background:#f3f7fb; }
        .sidebar { 
            width: 230px; background: var(--primary-color); color: white; height: 100vh; 
            position: fixed; padding-top: 30px; box-sizing: border-box; 
        }
        .sidebar h2 { margin: 0 0 20px 25px; }
        .sidebar a { display: block; padding: 12px 25px; color: white; text-decoration: none; transition: background 0.3s; }
        .sidebar a:hover, .sidebar a.active { background: rgba(255,255,255,0.2); }
        .main-content { margin-left: 240px; padding: 30px; }
        
        /* Styling Umum Konten */
        h2 { border-bottom: 2px solid var(--primary-color); padding-bottom: 10px; margin-bottom: 25px; color: #333; }
        .data-table-container { 
            padding: 20px; background: white; border-radius: 8px; 
            box-shadow: 0 2px 4px rgba(0,0,0,0.05); 
            margin-bottom: 30px; 
        }
        
        /* Styling Tabel Utama */
        .data-table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        .data-table th, .data-table td { padding: 12px; text-align: left; border-bottom: 1px solid #dee2e6; }
        .data-table th { background-color: var(--primary-color); color: white; font-size: 0.9em; text-transform: uppercase; font-weight: 600;}
        .data-table tr:hover { background-color: #f1f1f1; }

        /* Styling Daftar Pelanggan Aktif (Hanya Admin/Kasir) */
        .customer-summary-table { 
            width: 100%; 
            border-collapse: collapse;
            font-size: 0.95em;
        }
        .customer-summary-table th, .customer-summary-table td {
            padding: 10px;
            border: 1px solid #ccc;
        }
        .customer-summary-table th {
            background-color: var(--secondary-color);
            color: white;
            text-align: left;
        }
        .customer-list-box {
            padding: 0;
            box-shadow: none;
        }

        /* Status Text */
        .status-pending-text { color: #856404; }
        .status-diproses-text { color: #0c5460; }
        .status-dikirim-text { color: #155724; }
        .status-selesai-text { color: #004085; }
        
        /* CSS Aksi */
        .btn-action-edit { color: #ffc107; text-decoration: none; font-weight: bold; margin-right: 5px; }
        .btn-action-hapus { color: #dc3545; text-decoration: none; font-weight: bold; }
    </style>
</head>
<body>
    <div class="sidebar">
        <h2>Smart Laundry</h2>
        <a href="<?php echo $is_admin_or_kasir ? 'dashboard_kasir.php' : 'dashboard_pelanggan.php'; ?>">🏠 Beranda</a>
        <?php if ($is_admin_or_kasir): ?>
            <a href="data_layanan.php">🧺 Layanan</a>
        <?php endif; ?>
        <a href="status_pesanan.php" class="active">📦 Status Pesanan</a> 
        <a href="riwayat_transaksi.php">🧾 Riwayat Transaksi</a>
        <a href="logout.php">🚪 Logout</a>
    </div>

    <div class="main-content">
        
        <?php if ($is_admin_or_kasir): ?>
        <h2>👤 Pelanggan Aktif Berdasarkan Jumlah Pesanan</h2>
        <div class="data-table-container customer-list-box">
            <?php if ($unique_customers_result && $unique_customers_result->num_rows > 0): ?>
                <table class="customer-summary-table">
                    <thead>
                        <tr>
                            <th style="width: 70%;">Nama Pelanggan</th>
                            <th style="width: 30%; text-align: center;">Total Pesanan</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($c_row = $unique_customers_result->fetch_assoc()): ?>
                            <tr>
                                <td><?= htmlspecialchars($c_row['nama_pelanggan']); ?></td>
                                <td style="text-align: center; font-weight: bold;"><?= htmlspecialchars($c_row['total_orders']); ?></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p style="margin: 0;">Belum ada satupun pesanan yang tercatat dalam sistem.</p>
            <?php endif; ?>
        </div>
        <hr style="margin-top: 30px; margin-bottom: 30px;">
        <?php endif; ?>

        <h2>📋 Daftar Pesanan Dalam Sistem</h2>
        
        <div class="data-table-container">
            <?php if ($all_orders_result && $all_orders_result->num_rows > 0): ?>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Pelanggan</th> 
                            <th>Layanan</th>
                            <th>Berat/Jumlah</th>
                            <th>Tgl. Pesanan</th>
                            <th>Total Harga</th>
                            <th>Status</th>
                            <?php if ($is_admin_or_kasir): ?>
                                <th>Aksi</th>
                            <?php endif; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($row = $all_orders_result->fetch_assoc()): 
                            // Tentukan kelas CSS berdasarkan status
                            $status = strtolower($row['status_pesanan']);
                            $class_text = '';
                            switch ($status) {
                                case 'pending': $class_text = 'status-pending-text'; break;
                                case 'diproses': $class_text = 'status-diproses-text'; break;
                                case 'dikirim': $class_text = 'status-dikirim-text'; break;
                                case 'selesai': $class_text = 'status-selesai-text'; break;
                                default: $class_text = '';
                            }
                            
                            // Tentukan Satuan Display (Kg atau Pcs)
                            $satuan_display_list = 'Kg'; 
                            $layanan_info_list = array_filter($daftar_layanan, fn($l) => $l['nama'] === $row['jenis_layanan']);
                            if (!empty($layanan_info_list)) {
                                $satuan_display_list = array_values($layanan_info_list)[0]['satuan'];
                            }
                        ?>
                            <tr>
                                <td><?= htmlspecialchars($row['order_id']); ?></td>
                                <td><?= htmlspecialchars($row['nama_pelanggan']); ?></td>
                                
                                <td><?= htmlspecialchars($row['jenis_layanan'] ?? '-'); ?></td>
                                <td><?= htmlspecialchars(number_format($row['berat_kg'], 1, ',', '.')); ?> <?= htmlspecialchars($satuan_display_list); ?></td>
                                
                                <td><?php 
                                    $tgl_pesanan_list = $row['tanggal_pesanan'];
                                    if ($tgl_pesanan_list && $tgl_pesanan_list !== '0000-00-00 00:00:00') {
                                        echo htmlspecialchars(date('d M Y', strtotime($tgl_pesanan_list)));
                                    } else {
                                        echo "-";
                                    }
                                    ?></td>
                                <td>Rp<?= number_format($row['total_harga'], 0, ',', '.'); ?></td>
                                <td><span class="<?= $class_text; ?>" style="font-weight: bold;"><?= htmlspecialchars($row['status_pesanan']); ?></span></td>
                                
                                <?php if ($is_admin_or_kasir): ?>
                                    <td>
                                        <a href="edit_pesanan.php?id=<?= $row['order_id']; ?>" class="btn-action-edit">Edit</a> |
                                        <a href="hapus_pesanan.php?id=<?= $row['order_id']; ?>" class="btn-action-hapus" onclick="return confirm('Yakin hapus pesanan #<?= $row['order_id']; ?>?')">Hapus</a>
                                    </td>
                                <?php endif; ?>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>Belum ada data pesanan yang tercatat dalam sistem.</p>
            <?php endif; ?>
        </div>
    </div>

<?php 
// Tutup koneksi database setelah semua data diambil
if ($conn) {
    $conn->close();
}
?>
</body>
</html>