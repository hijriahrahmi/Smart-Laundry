<?php
session_start();
// Memastikan hanya user dengan role 'kasir' yang bisa mengakses
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'kasir') {
    header("Location: login.php");
    exit();
}

include 'koneksi.php'; // Hubungkan ke database

$status_message = ''; // Variabel untuk pesan sukses/gagal

// ==========================================================
// A. PROSES PENYIMPANAN DATA (Jika form di-submit)
// ==========================================================
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // 1. Ambil & Sanitasi Data
    $nama_pelanggan = htmlspecialchars(trim($_POST['nama_pelanggan']));
    $id_layanan = (int)$_POST['id_layanan'];
    $berat = (float)$_POST['berat'];
    $kasir_id = $_SESSION['user_id'] ?? 0;
    
    // Logika Bisnis: Hitung Total Bayar
    // Query untuk mendapatkan harga layanan (Asumsi: Tabel layanan memiliki kolom harga)
    $stmt_harga = $conn->prepare("SELECT harga_per_kg FROM layanan WHERE id_layanan = ?");
    $stmt_harga->bind_param("i", $id_layanan);
    $stmt_harga->execute();
    $result_harga = $stmt_harga->get_result();
    $layanan = $result_harga->fetch_assoc();
    $harga_per_kg = $layanan['harga_per_kg'] ?? 0;

    $total_bayar = $berat * $harga_per_kg;

    // Default status dan tanggal
    $status_awal = 'Baru';
    $tanggal_masuk = date('Y-m-d H:i:s'); 

    // 2. Query INSERT menggunakan Prepared Statements (Keamanan!)
    $sql = "INSERT INTO transaksi (nama_pelanggan, id_layanan, berat, total_bayar, kasir_id, status_pesanan, tanggal_masuk) 
            VALUES (?, ?, ?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);
    
    // Bind parameter: s=string, i=integer, d=double/float
    $stmt->bind_param("sididss", $nama_pelanggan, $id_layanan, $berat, $total_bayar, $kasir_id, $status_awal, $tanggal_masuk);
    
    // 3. Jalankan Query dan Cek Hasil
    if ($stmt->execute()) {
        $status_message = '<div style="color: green; background: #e6ffe6; padding: 10px; border-radius: 5px; margin-bottom: 20px;">✅ Transaksi baru berhasil dicatat!</div>';
    } else {
        $status_message = '<div style="color: red; background: #ffe6e6; padding: 10px; border-radius: 5px; margin-bottom: 20px;">❌ Gagal mencatat transaksi. Error: ' . $stmt->error . '</div>';
    }

    $stmt->close();
}

// ==========================================================
// B. AMBIL DATA LAYANAN UNTUK DROPDOWN FORM
// ==========================================================
$layanan_list = [];
$result_layanan = $conn->query("SELECT id_layanan, nama_layanan, harga_per_kg FROM layanan ORDER BY nama_layanan");
while ($row = $result_layanan->fetch_assoc()) {
    $layanan_list[] = $row;
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Transaksi Baru - Smart Laundry</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        /* ... (CSS dari dashboard_kasir.php untuk sidebar dan layout) ... */
        :root { --primary-color: #3caea3; --secondary-color: #1f3936; --background-light: #f3f7fb; --card-shadow: 0 4px 12px rgba(0,0,0,0.08); }
        body { margin:0; font-family:'Poppins', sans-serif; background: var(--background-light); display: flex; }
        .sidebar { width:250px; background: var(--primary-color); color:white; min-height:100vh; padding-top:20px; box-shadow: 2px 0 10px rgba(0,0,0,0.15); }
        .sidebar-header { text-align: center; padding: 15px 0 30px 0; border-bottom: 1px solid rgba(255,255,255,0.2); }
        .sidebar h2 { margin: 0; font-size: 1.6em; }
        .sidebar a { display:block; padding:15px 25px; color:white; text-decoration:none; transition: background 0.3s, border-left 0.3s; }
        .sidebar a:hover { background: var(--secondary-color); border-left: 5px solid white; padding-left: 20px; }

        .main-content { flex-grow: 1; padding: 30px; }
        .form-container { background: white; padding: 30px; border-radius: 10px; box-shadow: var(--card-shadow); max-width: 600px; }
        .form-container label { display: block; margin-top: 15px; margin-bottom: 5px; font-weight: 600; color: var(--secondary-color); }
        .form-container input[type="text"], 
        .form-container input[type="number"], 
        .form-container select { 
            width: 100%; 
            padding: 10px; 
            margin-bottom: 20px; 
            border: 1px solid #ddd; 
            border-radius: 5px; 
            box-sizing: border-box; 
        }
        .form-container button {
            background: var(--primary-color);
            color: white;
            padding: 12px 25px;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.3s;
        }
        .form-container button:hover { background: #2a8b81; }
    </style>
</head>
<body>
    <div class="sidebar">
        <div class="sidebar-header"><h2>Smart Laundry</h2></div>
        <a href="dashboard_kasir.php">🏠 Beranda</a>
        <a href="transaksi_baru.php" class="active">💰 Transaksi Baru</a>
        <a href="laporan_harian.php">📊 Laporan Harian</a>
        <a href="manajemen_layanan.php">🧺 Manajemen Layanan</a> 
        <a href="logout.php">🚪 Logout</a>
    </div>

    <div class="main-content">
        <h1>💰 Transaksi Baru</h1>
        <p>Catat pesanan laundry yang masuk hari ini.</p>
        
        <?= $status_message; // Tampilkan pesan sukses/gagal ?>

        <div class="form-container">
            <form action="transaksi_baru.php" method="POST">
                
                <label for="pelanggan">Nama Pelanggan:</label>
                <input type="text" id="pelanggan" name="nama_pelanggan" required>
                
                <label for="layanan">Jenis Layanan:</label>
                <select id="layanan" name="id_layanan" required>
                    <?php 
                    // Tampilkan layanan dari database
                    if (!empty($layanan_list)):
                        foreach ($layanan_list as $layanan):
                            echo '<option value="' . $layanan['id_layanan'] . '">' 
                                . htmlspecialchars($layanan['nama_layanan']) 
                                . ' (Rp ' . number_format($layanan['harga_per_kg'], 0, ',', '.') . '/kg)' 
                                . '</option>';
                        endforeach;
                    else:
                        echo '<option value="">-- Tambahkan Layanan di Manajemen Layanan --</option>';
                    endif;
                    ?>
                </select>
                
                <label for="berat">Berat (kg):</label>
                <input type="number" id="berat" name="berat" step="0.1" min="0.1" required>
                
                <button type="submit">➕ Simpan Transaksi</button>
            </form>
        </div>
    </div>
</body>
</html>