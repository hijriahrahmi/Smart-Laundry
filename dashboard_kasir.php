<?php
// Mulai sesi
session_start();

// Pastikan file koneksi.php sudah tersedia
include 'koneksi.php'; 

// === 1. PENGAMANAN AKSES (Hanya untuk Kasir) ===
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'kasir') {
    header("Location: login.php");
    exit();
}

// Ambil data user dari session
$nama_user = $_SESSION['nama'] ?? $_SESSION['username'];

// Tentukan data tanggal untuk query metrik
$tanggal_hari_ini = date('Y-m-d');
$bulan_ini = date('m');
$tahun_ini = date('Y');

$message = ''; // Variabel untuk pesan notifikasi

// KONEKSI KE DATABASE (Pastikan $host, $user, $pass, $db dari koneksi.php)
$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    // Tampilkan error jika koneksi gagal
    die("Koneksi ke database gagal: " . $conn->connect_error);
}

// ===========================================
// === 2. PROSES UPDATE STATUS PESANAN & BAYAR ===
// ===========================================
if (isset($_POST['update_status'])) {
    $id_pesanan = trim($_POST['id_pesanan']); 
    $status_baru = $_POST['status_baru'];
    $jenis_update = $_POST['jenis_update']; // 'pesanan' atau 'bayar'
    
    $success = false;
    $error_msg = '';
    $sql_update = '';

    if ($jenis_update === 'pesanan') {
        // Update Status Pesanan (Menunggu/Proses/Selesai)
        $sql_update = "UPDATE riwayat_transaksi SET status_pesanan = ? WHERE id_pembayaran = ?";
        $stmt = $conn->prepare($sql_update);
        // Parameter binding: 'ss' (dua string: status_baru, id_pesanan)
        $stmt->bind_param("ss", $status_baru, $id_pesanan); 
        
        if ($stmt->execute()) {
            $message = "✅ Status Pesanan #$id_pesanan berhasil diubah menjadi **$status_baru**.";
            $success = true;
        } else {
            $error_msg = $stmt->error;
        }
        $stmt->close();
        
    } elseif ($jenis_update === 'bayar' && $status_baru === 'Lunas') {
        // Update Status Pembayaran menjadi Lunas (sekaligus set tanggal_bayar)
        $tanggal_bayar_update = date('Y-m-d H:i:s');
        
        $sql_update = "UPDATE riwayat_transaksi SET status_bayar = 'Lunas', tanggal_bayar = ? WHERE id_pembayaran = ?";
        $stmt = $conn->prepare($sql_update);
        // Parameter binding: 'ss' (dua string: tanggal_bayar_update, id_pesanan)
        $stmt->bind_param("ss", $tanggal_bayar_update, $id_pesanan); 
        
        if ($stmt->execute()) {
            $message = "✅ Pembayaran Pesanan #$id_pesanan berhasil diupdate menjadi **Lunas**.";
            $success = true;
        } else {
            $error_msg = $stmt->error;
        }
        $stmt->close();
    } 
    
    // Pola Post/Redirect/Get (PRG) untuk mencegah resubmission form
    if ($success) {
        header("Location: dashboard_kasir.php?msg=" . urlencode(strip_tags($message)));
        exit();
    } elseif (!$success && $error_msg) {
        // Jika ada error, kirim pesan error
        header("Location: dashboard_kasir.php?err=" . urlencode($error_msg));
        exit();
    }
}

// Menangani pesan notifikasi dari redirect (msg/err)
if (isset($_GET['msg'])) {
    $message = urldecode($_GET['msg']);
} elseif (isset($_GET['err'])) {
    $message = "❌ Error saat update: " . urldecode($_GET['err']);
}


// ===========================================
// === 3. QUERY METRIK UTAMA DASHBOARD ===
// ===========================================

// A. Pesanan Masuk / Aktif (Status Proses/Menunggu)
$sql_masuk = "SELECT COUNT(id_pembayaran) AS count_masuk FROM riwayat_transaksi WHERE status_pesanan IN ('Proses', 'Menunggu')";
$result_masuk = $conn->query($sql_masuk);
$pesanan_masuk_count = $result_masuk->fetch_assoc()['count_masuk'] ?? 0;

// B. Siap Diambil (Selesai tapi Belum Lunas)
$sql_siap_ambil = "SELECT COUNT(id_pembayaran) AS count_siap FROM riwayat_transaksi WHERE status_pesanan = 'Selesai' AND status_bayar = 'Belum Lunas'";
$result_siap_ambil = $conn->query($sql_siap_ambil);
$siap_ambil_count = $result_siap_ambil->fetch_assoc()['count_siap'] ?? 0;

// C. Total Penjualan Harian (Lunas Hari Ini)
// Menggunakan Prepared Statement untuk tanggal guna menghindari SQL Injection (walau tgl sudah di-generate dari PHP)
$sql_harian = "SELECT SUM(total_harga) AS total_uang FROM riwayat_transaksi WHERE status_bayar = 'Lunas' AND DATE(tanggal_bayar) = ?";
$stmt_harian = $conn->prepare($sql_harian);
$stmt_harian->bind_param("s", $tanggal_hari_ini);
$stmt_harian->execute();
$result_harian = $stmt_harian->get_result();
$total_penjualan_harian = $result_harian->fetch_assoc()['total_uang'] ?? 0;
$stmt_harian->close();


// D. Total Penjualan Bulan Ini (Filter menggunakan fungsi MySQL MONTH() dan YEAR())
$sql_bulan = "SELECT SUM(total_harga) AS total_uang FROM riwayat_transaksi WHERE status_bayar = 'Lunas' AND MONTH(tanggal_bayar) = ? AND YEAR(tanggal_bayar) = ?";
$stmt_bulan = $conn->prepare($sql_bulan);
// Parameter binding: 'ss' (dua string: bulan_ini, tahun_ini)
$stmt_bulan->bind_param("ss", $bulan_ini, $tahun_ini); 
$stmt_bulan->execute();
$result_bulan = $stmt_bulan->get_result();
$total_penjualan_bulan = $result_bulan->fetch_assoc()['total_uang'] ?? 0;
$stmt_bulan->close();


// E. Total Penjualan Tahun Ini
$sql_tahun = "SELECT SUM(total_harga) AS total_uang FROM riwayat_transaksi WHERE status_bayar = 'Lunas' AND YEAR(tanggal_bayar) = ?";
$stmt_tahun = $conn->prepare($sql_tahun);
$stmt_tahun->bind_param("s", $tahun_ini);
$stmt_tahun->execute();
$result_tahun = $stmt_tahun->get_result();
$total_penjualan_tahun = $result_tahun->fetch_assoc()['total_uang'] ?? 0;
$stmt_tahun->close();


// F. Total Pesanan Keseluruhan
$sql_total_pesanan = "SELECT COUNT(id_pembayaran) AS total_order FROM riwayat_transaksi";
$result_total_pesanan = $conn->query($sql_total_pesanan);
$total_pesanan_keseluruhan = $result_total_pesanan->fetch_assoc()['total_order'] ?? 0;

// G. Total Pelanggan Unik
$sql_pelanggan = "SELECT COUNT(DISTINCT nama_pelanggan) AS total_uniq FROM riwayat_transaksi";
$result_pelanggan = $conn->query($sql_pelanggan);
$total_pelanggan = $result_pelanggan ? ($result_pelanggan->fetch_assoc()['total_uniq'] ?? 0) : 0;


// H. Mengambil 5 Pesanan Terakhir
$sql_recent = "SELECT * FROM riwayat_transaksi ORDER BY id_pembayaran DESC LIMIT 5";
$result_recent = $conn->query($sql_recent);

// Tutup koneksi database
$conn->close();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Kasir - Smart Laundry</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    
    <style>
        :root { 
            --primary-color: #3caea3; 
            --secondary-color: #1f3936; 
            --background-light: #f3f7fb; 
            --card-shadow: 0 4px 12px rgba(0,0,0,0.08); 
            --sidebar-width: 250px; 
        }
        body { margin: 0; font-family:'Poppins', sans-serif; background: var(--background-light); display: flex; }
        
        /* Sidebar Styles */
        .sidebar { width: var(--sidebar-width); background-color: var(--primary-color); color: white; height: 100vh; padding-top: 20px; position: fixed; box-shadow: 2px 0 5px rgba(0, 0, 0, 0.1); }
        .logo { padding: 0 20px; font-size: 1.5em; font-weight: 700; margin-bottom: 30px; }
        .nav a { display: flex; align-items: center; padding: 15px 20px; text-decoration: none; color: rgba(255, 255, 255, 0.8); transition: background-color 0.3s; }
        .nav a:hover, .nav a.active { background-color: var(--secondary-color); color: white; }
        .nav i { margin-right: 10px; }
        
        /* Main Content Styles */
        .main-content { margin-left: var(--sidebar-width); flex-grow: 1; padding: 30px; }
        .header h1 { color: var(--secondary-color); font-size: 2em; margin-bottom: 5px; }
        .role-tag { background-color: #3caea3; color: white; padding: 4px 8px; border-radius: 4px; font-size: 0.9em; font-weight: 600; margin-left: 10px; }
        
        /* Card Styles (Metrics) */
        .dashboard-cards-top { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 30px; }
        .card-top { background: var(--primary-color); color: white; padding: 20px; border-radius: 10px; text-align: center; box-shadow: var(--card-shadow); }
        .card-top:nth-child(2) { background: #FFC107; } /* Pesanan Aktif */
        .card-top:nth-child(3) { background: #4CAF50; } /* Siap Diambil/Bayar */
        .card-top-value { font-size: 2.5em; font-weight: 700; color: white; margin: 5px 0 0; }

        .dashboard-cards-main { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; margin-bottom: 30px; }
        .card-main { background: white; padding: 25px; border-radius: 10px; box-shadow: var(--card-shadow); border-left: 5px solid; }
        .card-main-header { display: flex; align-items: center; font-weight: 600; color: #555; margin-bottom: 10px; font-size: 1.1em; }
        .card-main-value { font-size: 2em; font-weight: 700; color: var(--secondary-color); margin-top: -5px; }
        
        /* Specific Card Borders */
        .card-main.active { border-left-color: orange; }
        .card-main.ready { border-left-color: #28a745; }
        .income-day { border-left-color: #00bcd4; }
        .income-month { border-left-color: #2196f3; }
        .income-year { border-left-color: #3f51b5; }
        
        /* Table Styles */
        .section-title { font-size: 1.2em; font-weight: 700; color: var(--secondary-color); margin-top: 40px; margin-bottom: 15px; border-bottom: 2px solid #ddd; padding-bottom: 10px; }
        .table-container { background: white; padding: 20px; border-radius: 10px; box-shadow: var(--card-shadow); overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        table th, table td { text-align: left; padding: 12px; border-bottom: 1px solid #eee; vertical-align: middle; }
        table th { background-color: #f8f9fa; color: var(--secondary-color); font-weight: 600; }
        
        /* Badge Styles */
        .badge { padding: 5px 10px; border-radius: 20px; font-size: 0.8em; font-weight: 600; white-space: nowrap; }
        .badge-proses { background: #ffeeba; color: #856404; }
        .badge-selesai { background: #d4edda; color: #155724; }
        .badge-menunggu { background: #f8d7da; color: #721c24; }
        .badge-lunas { background: #cce5ff; color: #004085; }
        .badge-belum { background: #f8d7da; color: #721c24; }
        
        /* Quick Action Form Styles */
        .quick-action-form { display: flex; gap: 5px; align-items: center; }
        .quick-action-form select, .quick-action-form button { padding: 6px 8px; border-radius: 4px; border: 1px solid #ccc; font-size: 0.85em; cursor: pointer; transition: all 0.2s; }
        .quick-action-form button.btn-status { background-color: #4CAF50; color: white; border: none; }
        .quick-action-form button.btn-bayar { background-color: #2196F3; color: white; border: none; }
        .quick-action-form button:hover { opacity: 0.8; }
        .quick-action-form select:disabled { background-color: #f0f0f0; }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="logo">Smart Laundry</div>
        <nav class="nav">
            <a href="dashboard_kasir.php"><i class="fas fa-home"></i> Beranda</a>
            <a href="buat_pesanan.php"><i class="fas fa-cart-plus"></i> Buat Pesanan</a>
            <a href="buat_transaksi_baru.php"><i class="fas fa-receipt"></i> Buat Transaksi Baru</a>
            <a href="laporan_harian.php"><i class="fas fa-chart-line"></i> Laporan Harian</a>
            <a href="riwayat_transaksi.php"><i class="fas fa-history"></i> Riwayat Transaksi</a>
            <a href="status_pesanan.php"><i class="fas fa-tasks"></i> Status Proses</a>
            <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </nav>
    </div>


    <div class="main-content">
        <div class="header">
            <h1>Halo, **<?= htmlspecialchars($nama_user); ?>**!<span class="role-tag">KASIR</span></h1>
            <p class="welcome-text">Selamat bertugas! Berikut ringkasan aktivitas hari ini, **<?= date('d F Y'); ?>**.</p>
        </div>

        <?php if (!empty($message)): ?>
            <?php 
                $is_success = strpos($message, '✅') !== false;
                $bg_color = $is_success ? '#d4edda' : '#f8d7da';
                $text_color = $is_success ? '#155724' : '#721c24';
                $border_color = $is_success ? '#c3e6cb' : '#f5c6cb';
            ?>
            <div style="padding: 10px; margin-bottom: 20px; border-radius: 4px; font-weight: bold; background-color: <?= $bg_color; ?>; color: <?= $text_color; ?>; border: 1px solid <?= $border_color; ?>;">
                <?= $message; ?>
            </div>
        <?php endif; ?>

        <div class="dashboard-cards-top">
            <div class="card-top">
                <p>Pesanan Aktif</p>
                <div class="card-top-value"><?= number_format($pesanan_masuk_count, 0, ',', '.') ?></div>
            </div>
            <div class="card-top">
                <p>Siap Diambil/Bayar</p>
                <div class="card-top-value"><?= number_format($siap_ambil_count, 0, ',', '.') ?></div>
            </div>
            <div class="card-top" style="background: #2196F3;">
                <p>Total Transaksi</p>
                <div class="card-top-value"><?= number_format($total_pesanan_keseluruhan, 0, ',', '.') ?></div>
            </div>
        </div>

        <div class="section-title">Status & Keuangan</div>
        <hr>

        <div class="dashboard-cards-main">
            <div class="card-main active">
                <div class="card-main-header" style="color: orange;">📦 Antrian</div>
                <div class="card-main-value"><?= number_format($pesanan_masuk_count, 0, ',', '.') ?></div>
                <p>Total antrian yang harus segera diproses (Menunggu/Proses).</p>
            </div>
            <div class="card-main ready">
                <div class="card-main-header" style="color: #28a745;">✅ Siap Diambil</div>
                <div class="card-main-value"><?= number_format($siap_ambil_count, 0, ',', '.') ?></div>
                <p>Pesanan Selesai namun menunggu pembayaran/pengambilan.</p>
            </div>
            <div class="card-main income income-day">
                <div class="card-main-header" style="color: #00bcd4;">📅 Harian</div>
                <div class="card-main-value">Rp <?= number_format($total_penjualan_harian, 0, ',', '.') ?></div>
                <p>Total uang masuk hari ini.</p>
            </div>
            <div class="card-main income income-month">
                <div class="card-main-header" style="color: #2196f3;">🗓️ Bulan Ini</div>
                <div class="card-main-value">Rp <?= number_format($total_penjualan_bulan, 0, ',', '.') ?></div>
                <p>Akumulasi penjualan bulan ini.</p>
            </div>
            <div class="card-main income income-year">
                <div class="card-main-header" style="color: #3f51b5;">📈 Tahun Ini</div>
                <div class="card-main-value">Rp <?= number_format($total_penjualan_tahun, 0, ',', '.') ?></div>
                <p>Total pendapatan sepanjang tahun ini.</p>
            </div>
        </div>

        <div class="section-title">Pesanan Terbaru</div>
        <hr>
        
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Pelanggan</th>
                        <th>Paket</th>
                        <th>Total</th>
                        <th>Status Pesanan</th>
                        <th>Status Bayar</th>
                        <th>Aksi Cepat</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result_recent && $result_recent->num_rows > 0): ?>
                        <?php while($row = $result_recent->fetch_assoc()): 
                                // Amankan variabel dan berikan nilai default jika kolom tidak ada
                                $id_pembayaran = htmlspecialchars($row['id_pembayaran'] ?? 'N/A');
                                $nama_pelanggan = htmlspecialchars($row['nama_pelanggan'] ?? 'Nama Tidak Ada');
                                $jenis_paket = htmlspecialchars($row['jenis_paket'] ?? 'N/A');
                                $total_harga = $row['total_harga'] ?? 0;
                                $status = htmlspecialchars($row['status_pesanan'] ?? 'Menunggu');
                                $status_bayar = htmlspecialchars($row['status_bayar'] ?? 'Belum Lunas');
                            ?>
                            <tr>
                                <td>#<?= $id_pembayaran; ?></td>
                                <td style="font-weight:bold; color:#3caea3;"><?= $nama_pelanggan; ?></td>
                                <td><?= $jenis_paket; ?></td>
                                <td>Rp <?= number_format($total_harga, 0, ',', '.'); ?></td>
                                <td>
                                    <?php 
                                         $badgeClass = match($status) {
                                            'Selesai' => 'badge-selesai',
                                            'Proses' => 'badge-proses',
                                            default => 'badge-menunggu', // Default 'Menunggu'
                                        };
                                    ?>
                                    <span class="badge <?= $badgeClass ?>"><?= $status; ?></span>
                                </td>
                                <td>
                                    <?php 
                                        $badgeBayarClass = ($status_bayar == 'Lunas') ? 'badge-lunas' : 'badge-belum';
                                    ?>
                                    <span class="badge <?= $badgeBayarClass ?>"><?= $status_bayar; ?></span>
                                </td>
                                <td>
                                    <div style="display: flex; gap: 10px;">
                                        <form method="POST" action="dashboard_kasir.php" class="quick-action-form">
                                            <input type="hidden" name="id_pesanan" value="<?= $id_pembayaran; ?>">
                                            <input type="hidden" name="jenis_update" value="pesanan">
                                            <select name="status_baru" required>
                                                <option value="" disabled selected>Ubah Status</option>
                                                <option value="Menunggu" <?= ($status == 'Menunggu') ? 'disabled' : ''; ?>>Menunggu</option>
                                                <option value="Proses" <?= ($status == 'Proses') ? 'disabled' : ''; ?>>Proses</option>
                                                <option value="Selesai" <?= ($status == 'Selesai') ? 'disabled' : ''; ?>>Selesai</option>
                                            </select>
                                            <button type="submit" name="update_status" class="btn-status" title="Update Status Pesanan">
                                                <i class="fas fa-sync-alt"></i>
                                            </button>
                                        </form>

                                        <?php if ($status_bayar == 'Belum Lunas' && $status == 'Selesai'): ?>
                                            <form method="POST" action="dashboard_kasir.php" class="quick-action-form">
                                                <input type="hidden" name="id_pesanan" value="<?= $id_pembayaran; ?>">
                                                <input type="hidden" name="jenis_update" value="bayar">
                                                <input type="hidden" name="status_baru" value="Lunas">
                                                <button type="submit" name="update_status" class="btn-bayar" title="Tandai sebagai Lunas" onclick="return confirm('Yakin mengubah status pembayaran #<?= $id_pembayaran; ?> menjadi LUNAS?')">
                                                    Bayar Lunas
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="7" style="text-align:center; padding: 20px; color: #777;">Belum ada pesanan masuk.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    
</body>
</html>