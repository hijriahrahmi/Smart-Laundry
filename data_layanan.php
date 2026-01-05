<?php
// File: data_layanan.php
session_start();

// Cek apakah pengguna sudah login
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

// === GANTI DENGAN DATA LAYANAN STATIS (MOCKUP) ===
$daftar_layanan = [
    ['id' => 1, 'nama' => 'Cuci Kering Reguler', 'harga' => 8000, 'satuan' => 'per Kg', 'estimasi' => '48 Jam'],
    ['id' => 2, 'nama' => 'Cuci + Setrika', 'harga' => 10000, 'satuan' => 'per Kg', 'estimasi' => '48 Jam'],
    ['id' => 3, 'nama' => 'Cuci Kering Express', 'harga' => 15000, 'satuan' => 'per Kg', 'estimasi' => '6 Jam'],
    ['id' => 4, 'nama' => 'Setrika Saja', 'harga' => 6000, 'satuan' => 'per Kg', 'estimasi' => '24 Jam'],
    ['id' => 5, 'nama' => 'Bed Cover Besar', 'harga' => 35000, 'satuan' => 'per Pcs', 'estimasi' => '72 Jam'],
    ['id' => 6, 'nama' => 'Gordyn Tebal', 'harga' => 45000, 'satuan' => 'per Pcs', 'estimasi' => '72 Jam'],
];

// Catatan: Semua kode koneksi ke database ($host, $user, $pass, $db, $conn, $sql, $result) telah dihapus.
// =================================================

?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Daftar Layanan - Smart Laundry</title>
    <style>
        /* Styling Sidebar & Layout (Untuk mencegah overlap) */
        body { margin:0; font-family:'Poppins', sans-serif; background:#f3f7fb; }
        .sidebar { 
            width: 230px; 
            background: #3caea3; 
            color: white; 
            height: 100vh; 
            position: fixed; /* Menjadikan sidebar tetap */
            padding-top: 30px; 
            box-sizing: border-box; /* Agar padding tidak menambah lebar */
        }
        .sidebar h2 { margin: 0 0 20px 25px; }
        .sidebar a { display: block; padding: 12px 25px; color: white; text-decoration: none; transition: background 0.3s; }
        .sidebar a:hover, .sidebar a.active { background: rgba(255,255,255,0.2); }
        
        /* Area Konten Utama */
        .main-content { 
            margin-left: 240px; /* Jarak yang cukup dari sidebar (230px + 10px) */
            padding: 30px; 
        }

        /* Styling Tabel Layanan */
        .table-container { 
            overflow-x: auto; /* Untuk responsif jika tabel terlalu lebar */
            margin-top: 20px;
        }
        table { 
            width: 100%; /* Memastikan tabel mengisi 100% dari main-content */
            border-collapse: collapse; 
        }
        th, td { 
            border: 1px solid #ddd; 
            padding: 12px; 
            text-align: left; 
        }
        th { 
            background-color: #3caea3; 
            color: white; 
        }
        tr:nth-child(even) { background-color: #f9f9f9; }
        .price-col {
            font-weight: bold;
            color: #d9534f;
        }
    </style>
</head>
<body>

    <div class="sidebar">
        <h2>Smart Laundry</h2>
        <a href="dashboard_pelanggan.php">🏠 Beranda</a>
        <a href="data_layanan.php" class="active">🧺 Layanan</a> 
        <a href="status_pesanan.php">📦 Status Pesanan</a>
        <a href="riwayat_transaksi.php">🧾 Riwayat Transaksi</a>
        <a href="logout.php">🚪 Logout</a>
    </div>

    <div class="main-content">
        <h2>Daftar Harga Layanan</h2>
        
        <div class="table-container">
            <?php
            // === 4. TAMPILKAN DATA DARI ARRAY ===
            if (!empty($daftar_layanan)) {
                echo "<table>";
                echo "<thead>";
                echo "<tr>";
                echo "<th>Nama Layanan</th>";
                echo "<th>Harga</th>";
                echo "<th>Satuan</th>"; // Kolom baru dari data statis
                echo "<th>Estimasi Pengerjaan</th>";
                echo "</tr>";
                echo "</thead>";
                echo "<tbody>";

                // Loop untuk mengambil setiap baris data dari array
                foreach($daftar_layanan as $layanan) {
                    echo "<tr>";
                    echo "<td>" . htmlspecialchars($layanan["nama"]) . "</td>"; 
                    
                    // Format harga menjadi Rp 8.000
                    echo "<td class='price-col'>Rp " . number_format($layanan["harga"], 0, ',', '.') . "</td>"; 
                    
                    // Output Satuan
                    echo "<td>" . htmlspecialchars($layanan["satuan"]) . "</td>"; 
                    
                    // Output Estimasi Waktu
                    echo "<td>" . htmlspecialchars($layanan["estimasi"]) . "</td>"; 
                    echo "</tr>";
                }

                echo "</tbody>";
                echo "</table>";
            } else {
                echo "<p>⚠️ Tidak ada data layanan yang tersedia saat ini.</p>";
            }
            ?>
        </div>
    </div>
</body>
</html>