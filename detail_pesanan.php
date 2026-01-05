<?php
session_start();
include 'koneksi.php'; 

// Memastikan hanya user dengan role 'kasir' yang bisa mengakses
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'kasir') {
    header("Location: login.php");
    exit();
}

// === 1. Ambil ID dari URL ===
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: cari_pesanan.php?error=no_id");
    exit();
}

$id_pesanan = $_GET['id'];

// === 2. KONEKSI KE DATABASE ===
$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Koneksi ke database gagal: " . $conn->connect_error);
}

// === 3. Ambil Data Pesanan dari Database ===
$sql_detail = "SELECT * FROM riwayat_transaksi WHERE id_pembayaran = ?";
$stmt = $conn->prepare($sql_detail);
$stmt->bind_param("s", $id_pesanan);
$stmt->execute();
$result = $stmt->get_result();
$pesanan = $result->fetch_assoc();
$stmt->close();

// Cek apakah pesanan ditemukan
if (!$pesanan) {
    $conn->close();
    header("Location: cari_pesanan.php?error=not_found");
    exit();
}

// Hitung Sisa Pembayaran (Asumsi: total_harga = tagihan, jumlah_bayar = sudah dibayar)
$sisa_bayar = $pesanan['total_harga'] - $pesanan['jumlah_bayar']; 

// === 4. LOGIKA UPDATE STATUS PESANAN (Contoh Sederhana) ===
if (isset($_POST['update_status'])) {
    $new_status = $_POST['status_pesanan_baru'];
    
    // Ulangi koneksi jika sudah ditutup sebelumnya (atau gunakan $conn jika belum ditutup)
    $conn = new mysqli($host, $user, $pass, $db);
    
    $sql_update = "UPDATE riwayat_transaksi SET status_pesanan = ? WHERE id_pembayaran = ?";
    $stmt_update = $conn->prepare($sql_update);
    $stmt_update->bind_param("ss", $new_status, $id_pesanan);
    
    if ($stmt_update->execute()) {
        $stmt_update->close();
        $conn->close();
        // Redirect untuk refresh data
        header("Location: detail_pesanan.php?id=" . urlencode($id_pesanan) . "&success=status_updated");
        exit();
    } else {
        $stmt_update->close();
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Detail Pesanan #<?= htmlspecialchars($id_pesanan); ?> - Smart Laundry</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #3caea3;
            --secondary-color: #1f3936;
            --background-light: #f3f7fb;
            --card-shadow: 0 4px 12px rgba(0,0,0,0.08);
        }
        body { margin: 0; font-family:'Poppins', sans-serif; background: var(--background-light); padding: 30px; }
        .container { max-width: 900px; margin: 0 auto; background: white; padding: 30px; border-radius: 12px; box-shadow: var(--card-shadow); }
        h2 { color: var(--secondary-color); border-bottom: 2px solid var(--primary-color); padding-bottom: 10px; margin-bottom: 25px; }
        .detail-group { display: flex; margin-bottom: 15px; }
        .detail-label { font-weight: bold; width: 200px; color: #555; }
        .detail-value { flex-grow: 1; }

        .status-box { 
            display: inline-block; 
            padding: 5px 10px; 
            border-radius: 4px; 
            font-weight: bold; 
            color: white;
            margin-left: 10px;
        }
        /* Status Warna */
        .status-lunas { background-color: #28a745; }
        .status-belum-lunas { background-color: #ffc107; color: #333; }
        .status-selesai { background-color: #17a2b8; }
        .status-proses { background-color: #6c757d; }
        .status-menunggu { background-color: #fd7e14; }

        .btn-back { background-color: #6c757d; color: white; text-decoration: none; padding: 10px 15px; border-radius: 4px; display: inline-block; font-weight: 600; margin-bottom: 20px;}
        .section-header { color: var(--primary-color); border-bottom: 1px dashed #ccc; padding-bottom: 5px; margin-top: 25px; margin-bottom: 15px; font-weight: 600; }
        
        .action-panel {
            background: #f0f8ff;
            padding: 20px;
            border-radius: 8px;
            border: 1px solid #cceeff;
            margin-top: 30px;
        }
        .action-panel select, .action-panel input, .action-panel button {
            padding: 8px; border-radius: 4px; border: 1px solid #ccc; margin-right: 10px;
        }
        .btn-update { background-color: #f7a01d; color: white; font-weight: bold; border: none; cursor: pointer; }
    </style>
</head>
<body>
    <div class="container">
        <a href="status_pesanan.php" class="btn-back">« Kembali ke Pencarian</a>

        <h2>🧾 Detail Pesanan #<?= htmlspecialchars($pesanan['id_pembayaran']); ?></h2>
        
        <?php if (isset($_GET['success'])): ?>
            <div style="padding: 10px; background: #d4edda; color: #155724; border: 1px solid #c3e6cb; border-radius: 5px; margin-bottom: 15px;">
                Status berhasil diperbarui!
            </div>
        <?php endif; ?>

        <div class="section-header">Rincian Umum</div>
        <div class="detail-group">
            <div class="detail-label">Pelanggan:</div>
            <div class="detail-value"><?= htmlspecialchars($pesanan['nama_pelanggan']); ?></div>
        </div>
        <div class="detail-group">
            <div class="detail-label">Tanggal Masuk:</div>
            <div class="detail-value"><?= htmlspecialchars(date('d F Y', strtotime($pesanan['tanggal_bayar']))); ?></div>
        </div>
        <div class="detail-group">
            <div class="detail-label">Status Pembayaran:</div>
            <div class="detail-value">
                <span class="status-box <?= ($pesanan['status_bayar'] == 'Lunas') ? 'status-lunas' : 'status-belum-lunas'; ?>">
                    <?= htmlspecialchars($pesanan['status_bayar']); ?>
                </span>
            </div>
        </div>
        <div class="detail-group">
            <div class="detail-label">Status Pesanan:</div>
            <div class="detail-value">
                <span class="status-box status-<?= strtolower(str_replace(' ', '-', $pesanan['status_pesanan'])); ?>">
                    <?= htmlspecialchars($pesanan['status_pesanan']); ?>
                </span>
            </div>
        </div>

        <div class="section-header">Rincian Biaya</div>
        <div class="detail-group">
            <div class="detail-label">Total Harga (Tagihan):</div>
            <div class="detail-value" style="font-weight: bold;">
                Rp <?= htmlspecialchars(number_format($pesanan['total_harga'], 0, ',', '.')); ?>
            </div>
        </div>
        <div class="detail-group">
            <div class="detail-label">Jumlah Dibayar:</div>
            <div class="detail-value" style="color: #28a745; font-weight: bold;">
                Rp <?= htmlspecialchars(number_format($pesanan['jumlah_bayar'], 0, ',', '.')); ?>
            </div>
        </div>
        <div class="detail-group">
            <div class="detail-label">Sisa Tagihan:</div>
            <div class="detail-value" style="color: red; font-size: 1.2em; font-weight: bold;">
                Rp <?= htmlspecialchars(number_format($sisa_bayar, 0, ',', '.')); ?> 
            </div>
        </div>

        <div class="action-panel">
            <h4>⚙️ Aksi Cepat Kasir</h4>
            
            <form method="POST" style="margin-bottom: 15px;">
                <label for="status_pesanan_baru">Ubah Status Pengerjaan:</label>
                <select id="status_pesanan_baru" name="status_pesanan_baru">
                    <option value="Menunggu" <?= ($pesanan['status_pesanan'] == 'Menunggu') ? 'selected' : ''; ?>>Menunggu</option>
                    <option value="Proses" <?= ($pesanan['status_pesanan'] == 'Proses') ? 'selected' : ''; ?>>Proses</option>
                    <option value="Selesai" <?= ($pesanan['status_pesanan'] == 'Selesai') ? 'selected' : ''; ?>>Selesai (Siap Ambil)</option>
                </select>
                <button type="submit" name="update_status" class="btn-update">Update Status</button>
            </form>
            
            <hr>

            <h4>Pembayaran & Cetak</h4>
            
            <?php if ($sisa_bayar > 0): ?>
                <a href="proses_pelunasan.php?id=<?= urlencode($pesanan['id_pembayaran']); ?>&total=<?= urlencode($pesanan['total_harga']); ?>"
                    style="background:#28a745; color:#fff; padding:10px 16px; border-radius:6px; text-decoration:none; font-weight:bold; display:inline-block; margin-top:10px;">
                    ✔ Proses Pelunasan (Bayar Sisa Tagihan)
                </a>
            <?php else: ?>
                <div style="color: #28a745; font-weight: bold; margin-bottom: 10px;">Pesanan ini sudah LUNAS.</div>
            <?php endif; ?>

            <a href="cetak_nota.php?id=<?= urlencode($pesanan['id_pembayaran']); ?>"
                target="_blank" 
                style="background:#007bff; color:#fff; padding:10px 16px; border-radius:6px; text-decoration:none; font-weight:bold; display:inline-block; margin-top:10px; margin-left: <?= ($sisa_bayar > 0) ? '10px' : '0'; ?>;">
                🖨️ Cetak Ulang Nota
            </a>

        </div>
        
    </div>
</body>
</html>