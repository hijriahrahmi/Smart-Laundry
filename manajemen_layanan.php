<?php
session_start();
// Memastikan hanya user dengan role 'kasir' atau 'customer' yang bisa mengakses
if (!isset($_SESSION['role']) || ($_SESSION['role'] !== 'kasir' && $_SESSION['role'] !== 'customer')) {
    header("Location: login.php");
    exit();
}

$user_role = $_SESSION['role']; // Ambil role pengguna saat ini

// Di sini Anda akan menghubungkan ke database dan mengambil data layanan:
// include 'koneksi.php'; 

// ======================================================================
// === DATA MOCKUP (CONTOH) - GANTI DENGAN QUERY DATABASE ANDA ===
// ======================================================================
$daftar_layanan = [
    ['id' => 1, 'nama' => 'Cuci Kering Reguler', 'harga' => 8000, 'satuan' => 'per Kg', 'estimasi' => '48 Jam'],
    ['id' => 2, 'nama' => 'Cuci + Setrika', 'harga' => 10000, 'satuan' => 'per Kg', 'estimasi' => '48 Jam'],
    ['id' => 3, 'nama' => 'Cuci Kering Express', 'harga' => 15000, 'satuan' => 'per Kg', 'estimasi' => '6 Jam'],
    ['id' => 4, 'nama' => 'Setrika Saja', 'harga' => 6000, 'satuan' => 'per Kg', 'estimasi' => '24 Jam'],
    ['id' => 5, 'nama' => 'Bed Cover Besar', 'harga' => 35000, 'satuan' => 'per Pcs', 'estimasi' => '72 Jam'],
    ['id' => 6, 'nama' => 'Gordyn Tebal', 'harga' => 45000, 'satuan' => 'per Pcs', 'estimasi' => '72 Jam'],
];
// ======================================================================

// Tentukan tujuan tombol kembali
$dashboard_link = ($user_role === 'kasir') ? 'dashboard_kasir.php' : 'dashboard_pelanggan.php';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Manajemen Layanan - Smart Laundry</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #3caea3;
            --secondary-color: #1f3936;
            --background-light: #f3f7fb;
            --card-shadow: 0 4px 12px rgba(0,0,0,0.08);
        }
        body { 
            margin:0; 
            font-family:'Poppins', sans-serif; 
            background: var(--background-light); 
            padding: 30px; 
        }
        h1 {
            color: var(--secondary-color);
        }
        
        /* Gaya Tabel */
        .table-container {
            margin-top: 20px;
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: var(--card-shadow);
            overflow-x: auto;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.9em;
        }
        th, td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background-color: var(--primary-color);
            color: white;
            font-weight: 600;
        }
        tr:nth-child(even) {
            background-color: #f8f8f8;
        }
        .action-link {
            text-decoration: none;
            color: #2a7ae4;
            margin-right: 10px;
            font-weight: 500;
        }
        .action-link:hover {
            color: var(--secondary-color);
        }
        .add-button {
            display: inline-block;
            background: #2a7ae4;
            color: white;
            padding: 10px 20px;
            border-radius: 5px;
            text-decoration: none;
            font-weight: 600;
            margin-bottom: 20px;
            transition: background 0.3s;
        }
        .add-button:hover {
            background: #1e5fa0;
        }
        .back-link {
            display: inline-block;
            margin-top: 30px;
            color: #2a7ae4;
            text-decoration: none;
        }
        .price-col {
            font-weight: 600;
            color: #d9534f; /* Warna merah agar harga menonjol */
        }
        /* Styling khusus untuk customer */
        .customer-notice {
            padding: 10px;
            background-color: #d1ecf1;
            border: 1px solid #bee5eb;
            color: #0c5460;
            border-radius: 5px;
            margin-bottom: 15px;
            font-style: italic;
        }
    </style>
</head>
<body>
    <h1>🧺 Daftar Layanan Smart Laundry</h1>
    <p>Informasi layanan dan tarif saat ini.</p>
    
    <?php if ($user_role === 'kasir'): ?>
        <a href="tambah_layanan.php" class="add-button">➕ Tambah Layanan Baru</a>
    <?php else: ?>
        <div class="customer-notice">
            Anda melihat daftar harga layanan. Untuk pemesanan, silakan kembali ke beranda.
        </div>
    <?php endif; ?>

    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <?php if ($user_role === 'kasir'): ?>
                        <th>ID</th>
                    <?php endif; ?>
                    <th>Nama Layanan</th>
                    <th>Harga</th>
                    <th>Satuan</th>
                    <th>Estimasi Selesai</th>
                    
                    <?php if ($user_role === 'kasir'): ?>
                        <th>Aksi</th>
                    <?php endif; ?>
                </tr>
            </thead>
            <tbody>
                <?php 
                if (!empty($daftar_layanan)): 
                    foreach ($daftar_layanan as $layanan): 
                ?>
                        <tr>
                            <?php if ($user_role === 'kasir'): ?>
                                <td><?= htmlspecialchars($layanan['id']); ?></td>
                            <?php endif; ?>
                            
                            <td><?= htmlspecialchars($layanan['nama']); ?></td>
                            <td class="price-col">Rp <?= number_format($layanan['harga'], 0, ',', '.'); ?></td>
                            <td><?= htmlspecialchars($layanan['satuan']); ?></td>
                            <td><?= htmlspecialchars($layanan['estimasi']); ?></td>
                            
                            <?php if ($user_role === 'kasir'): ?>
                                <td>
                                    <a href="edit_layanan.php?id=<?= $layanan['id']; ?>" class="action-link">✏️ Edit</a>
                                    <a href="hapus_layanan.php?id=<?= $layanan['id']; ?>" class="action-link" onclick="return confirm('Yakin ingin menghapus layanan ini?');">🗑️ Hapus</a>
                                </td>
                            <?php endif; ?>
                        </tr>
                    <?php 
                    endforeach; 
                else: 
                ?>
                    <tr>
                        <td colspan="<?= ($user_role === 'kasir') ? 6 : 5; ?>" style="text-align: center;">Belum ada data layanan yang tersedia.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <a href="<?= $dashboard_link; ?>" class="back-link">« Kembali ke Dashboard</a>
</body>
</html>