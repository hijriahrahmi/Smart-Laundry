<?php
session_start();
// Pastikan file koneksi.php berisi variabel $host, $user, $pass, $db

// === 1. Panggil Koneksi dan Autentikasi ===
include 'koneksi.php'; 

if (!isset($_SESSION['username']) || !isset($_SESSION['role'])) {
    header("Location: login.php");
    exit();
}

$user_role = $_SESSION['role']; 
$username = $_SESSION['username']; 
$is_kasir = ($user_role === 'kasir');
$dashboard_url = ($is_kasir) ? 'dashboard_kasir.php' : 'dashboard_pelanggan.php';

// === 2. DATABASE CONNECTION (Menggunakan mysqli object) ===
// Ciptakan koneksi baru
$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    // Lebih aman untuk tidak menampilkan error sensitif langsung ke user
    die("Sistem gagal terhubung ke database. Silakan coba lagi nanti.");
}

// === 3. QUERY DATA BERDASARKAN ROLE (Prepared Statement) ===
$sql_history = "SELECT id_pembayaran, tanggal_bayar, total_harga, metode, status_bayar, status_pesanan 
                FROM riwayat_transaksi 
                WHERE 1=1";
$params = [];
$types = '';

if ($user_role === 'pelanggan') {
    // Pelanggan hanya bisa melihat data mereka sendiri
    // Diasumsikan id_pelanggan disimpan dalam sesi username
    $sql_history .= " AND id_pelanggan = ?";
    $params[] = $username;
    $types .= 's'; // 's' menandakan string
}

// Tambahkan pengurutan
$sql_history .= " ORDER BY tanggal_bayar DESC, id_pembayaran DESC";

// Eksekusi Prepared Statement
$stmt = $conn->prepare($sql_history);

if (!$stmt) {
    // Penanganan error jika query gagal disiapkan
    die("Error menyiapkan statement: " . $conn->error);
}

if (!empty($params)) {
    // Menggunakan call_user_func_array untuk bind_param
    // Ini adalah cara yang lebih dinamis dan modern
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$result_history = $stmt->get_result();

$stmt->close();
$conn->close();

// === 4. FUNGSI BANTUAN UNTUK FORMATTING TAMPILAN ===
function formatRupiah($angka) {
    return 'Rp ' . number_format($angka, 0, ',', '.');
}

function getStatusClass($status_text, $type = 'bayar') {
    $status_text = strtolower($status_text);
    if ($type === 'bayar') {
        if ($status_text === 'lunas') return 'status-lunas';
        return 'status-belum-lunas';
    } else { // type === 'pesanan'
        if ($status_text === 'selesai') return 'status-selesai';
        if ($status_text === 'proses') return 'status-proses';
        if ($status_text === 'menunggu') return 'status-menunggu';
        return '';
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Riwayat Transaksi - Smart Laundry</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        /* CSS yang sudah bagus, tidak perlu diubah, hanya memastikan konsistensi */
        :root {
            --primary-color: #3caea3;
            --secondary-color: #1f3936;
            --background-light: #f3f7fb;
            --card-shadow: 0 4px 12px rgba(0,0,0,0.08);
        }
        body { margin: 0; font-family:'Poppins', sans-serif; background: var(--background-light); padding: 30px; }
        .container { max-width: 1400px; margin: 0 auto; background: white; padding: 30px; border-radius: 12px; box-shadow: var(--card-shadow); }
        h2 { color: var(--secondary-color); border-bottom: 2px solid var(--primary-color); padding-bottom: 10px; margin-bottom: 25px; }
        
        /* Tombol Kembali */
        .btn-back { 
            display: inline-block; 
            margin-bottom: 20px; 
            background-color: #6c757d; 
            color: white; 
            padding: 10px 15px; 
            border-radius: 6px; 
            text-decoration: none; 
            font-weight: 600;
            transition: background 0.3s;
        }
        .btn-back:hover { background-color: #5a6268; }

        /* Table Style */
        .data-table { width: 100%; border-collapse: collapse; margin-top: 20px; font-size: 0.9em; }
        .data-table th, .data-table td { padding: 12px; text-align: left; border-bottom: 1px solid #dee2e6; }
        .data-table th { background-color: var(--primary-color); color: white; text-transform: uppercase; font-size: 0.85em; }
        .data-table tr:hover { background-color: #f1f1f1; }
        
        /* Status Colors */
        .status-lunas { color: #28a745; font-weight: bold; }
        .status-belum-lunas { color: #ffc107; font-weight: bold; }
        .status-selesai { color: #17a2b8; font-weight: bold; }
        .status-proses, .status-menunggu { color: #6c757d; font-weight: bold; }
        .btn-action-view { color: var(--primary-color); text-decoration: none; font-weight: bold; margin-right: 5px; }
        .btn-action-edit { color: #ffc107; text-decoration: none; font-weight: bold; margin-right: 5px; }
        .btn-action-hapus { color: #dc3545; text-decoration: none; font-weight: bold; }
        .no-data { text-align: center; padding: 40px; color: #888; font-style: italic; }
    </style>
</head>
<body>
    <div class="container">
        
        <a href="<?= htmlspecialchars($dashboard_url); ?>" class="btn-back">
            <i class="fas fa-arrow-left"></i> Kembali ke Dashboard (<?= strtoupper($user_role); ?>)
        </a>
        
        <h2>📚 Riwayat Transaksi Anda</h2>
        <p>
            Menampilkan semua catatan pesanan 
            <?php echo $is_kasir ? 'yang ada dalam sistem.' : 'milik Anda.'; ?>
        </p>

        <div class="table-container">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>ID Pembayaran</th>
                        <th>Tanggal Bayar</th>
                        <th>Total Harga</th>
                        <th>Metode</th>
                        <th>Status Bayar</th>
                        <th>Status Pesanan</th>
                        <th>Aksi</th>
                        <?php if ($is_kasir): ?>
                            <th>Opsi Kasir</th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    if ($result_history && $result_history->num_rows > 0): 
                        while($row = $result_history->fetch_assoc()):
                            // Menggunakan fungsi bantuan yang dibuat di atas
                            $class_bayar = getStatusClass($row['status_bayar'], 'bayar');
                            $class_pesanan = getStatusClass($row['status_pesanan'], 'pesanan');
                    ?>
                        <tr>
                            <td><?= htmlspecialchars($row['id_pembayaran']); ?></td>
                            <td><?= htmlspecialchars(date('d M Y', strtotime($row['tanggal_bayar']))); ?></td>
                            <td><?= formatRupiah($row['total_harga']); ?></td>
                            <td><?= htmlspecialchars($row['metode']); ?></td>
                            <td class="<?= $class_bayar; ?>"><?= htmlspecialchars($row['status_bayar']); ?></td>
                            <td class="status-<?= $class_pesanan; ?>"><?= htmlspecialchars($row['status_pesanan']); ?></td>
                            <td>
                                <a href="detail_pesanan.php?id=<?= urlencode($row['id_pembayaran']); ?>" class="btn-action-view">Lihat Detail</a>
                            </td>
                            <?php if ($is_kasir): ?>
                                <td>
                                    <a href="edit_transaksi.php?id=<?= urlencode($row['id_pembayaran']); ?>" class="btn-action-edit">Edit</a> |
                                    <a href="hapus_transaksi.php?id=<?= urlencode($row['id_pembayaran']); ?>" class="btn-action-hapus" onclick="return confirm('Yakin hapus transaksi ID <?= $row['id_pembayaran']; ?>?')">Hapus</a>
                                </td>
                            <?php endif; ?>
                        </tr>
                    <?php 
                        endwhile;
                    else: 
                    ?>
                        <tr>
                            <td colspan="<?php echo $is_kasir ? '8' : '7'; ?>" class="no-data">
                                <?php echo $is_kasir ? 'Belum ada data transaksi yang tercatat dalam sistem.' : 'Anda belum memiliki riwayat transaksi.'; ?>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>