<?php
session_start();
include 'koneksi.php'; 

// Validasi role kasir
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'kasir') {
    header("Location: login.php");
    exit();
}

// Koneksi database
$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Koneksi ke database gagal: " . $conn->connect_error);
}

// Tanggal laporan
$tanggal_laporan = $_GET['tanggal'] ?? date('Y-m-d');
$tanggal_laporan_formatted = date('d F Y', strtotime($tanggal_laporan));

// Variabel ringkasan
$total_pendapatan_hari_ini = 0;
$total_transaksi_lunas = 0;
$total_transaksi_belum_lunas = 0;

// Query ringkasan
$sql_summary = "
    SELECT 
        SUM(CASE WHEN status_bayar = 'Lunas' THEN total_harga ELSE 0 END) AS total_pendapatan,
        COUNT(CASE WHEN status_bayar = 'Lunas' THEN 1 END) AS count_lunas,
        COUNT(CASE WHEN status_bayar = 'Belum Lunas' THEN 1 END) AS count_belum_lunas
    FROM riwayat_transaksi
    WHERE tanggal_bayar = ?
";
$stmt = $conn->prepare($sql_summary);
$stmt->bind_param("s", $tanggal_laporan);
$stmt->execute();
$summary = $stmt->get_result()->fetch_assoc();
$stmt->close();

if ($summary) {
    $total_pendapatan_hari_ini   = $summary['total_pendapatan'] ?? 0;
    $total_transaksi_lunas       = $summary['count_lunas'] ?? 0;
    $total_transaksi_belum_lunas = $summary['count_belum_lunas'] ?? 0;
}

// Query detail transaksi
$sql_detail = "SELECT * FROM riwayat_transaksi WHERE tanggal_bayar = ? ORDER BY id_pembayaran ASC";
$stmt = $conn->prepare($sql_detail);
$stmt->bind_param("s", $tanggal_laporan);
$stmt->execute();
$detail_transaksi = $stmt->get_result();
$stmt->close();

$conn->close();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Harian - Smart Laundry</title>

    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

    <style>
        :root {
            --primary: #3caea3;
            --secondary: #1f3936;
            --bg: #f3f7fb;
            --shadow: 0 4px 12px rgba(0,0,0,0.08);
        }

        body {
            margin: 0;
            padding: 30px;
            font-family: 'Poppins', sans-serif;
            background: var(--bg);
        }

        .container {
            max-width: 1400px;
            margin: auto;
            background: #fff;
            padding: 30px;
            border-radius: 12px;
            box-shadow: var(--shadow);
        }

        h2 {
            color: var(--secondary);
            border-bottom: 2px solid var(--primary);
            padding-bottom: 10px;
            margin-bottom: 25px;
        }

        .btn-back {
            display: inline-block;
            margin-bottom: 20px;
            background: #6c757d;
            color: #fff;
            padding: 10px 15px;
            border-radius: 6px;
            text-decoration: none;
            font-weight: 600;
        }

        .filter-form {
            display: flex;
            gap: 10px;
            align-items: center;
            margin-bottom: 20px;
        }

        .filter-form input,
        .filter-form button {
            padding: 10px;
            border-radius: 4px;
            border: 1px solid #ccc;
        }

        .filter-form button {
            background: var(--primary);
            color: #fff;
            cursor: pointer;
        }

        .summary-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin: 25px 0;
        }

        .summary-card {
            padding: 20px;
            border-radius: 10px;
            box-shadow: var(--shadow);
            border-left: 5px solid;
        }

        .card-income { background: #e6fffb; border-color: #f7a01d; }
        .card-lunas { background: #e7ffe8; border-color: #28a745; }
        .card-belum { background: #fff8e1; border-color: #ffc107; }

        .summary-title { font-size: 13px; color: #555; }
        .summary-value { font-size: 28px; font-weight: 700; }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
            font-size: 14px;
        }

        th, td {
            padding: 12px;
            border-bottom: 1px solid #ddd;
            text-align: left;
        }

        th {
            background: var(--secondary);
            color: #fff;
            font-size: 12px;
            text-transform: uppercase;
        }

        tr:hover { background: #f5f5f5; }

        .status-lunas { color: #28a745; font-weight: bold; }
        .status-belum-lunas { color: #ffc107; font-weight: bold; }

        .btn-action-view {
            font-weight: bold;
            text-decoration: none;
            margin-right: 6px;
            color: var(--primary);
        }

        .no-data {
            text-align: center;
            padding: 30px;
            font-style: italic;
            color: #888;
        }
    </style>
</head>

<body>
<div class="container">

    <a href="dashboard_kasir.php" class="btn-back">
        <i class="fas fa-arrow-left"></i> Kembali
    </a>

    <h2>📊 Laporan Harian Transaksi</h2>

    <form method="GET" class="filter-form">
        <label>Tanggal:</label>
        <input type="date" name="tanggal" value="<?= $tanggal_laporan; ?>" max="<?= date('Y-m-d'); ?>">
        <button type="submit"><i class="fas fa-search"></i> Tampilkan</button>
    </form>

    <h3>Ringkasan Tanggal: <?= $tanggal_laporan_formatted; ?></h3>

    <div class="summary-cards">
        <div class="summary-card card-income">
            <div class="summary-title">Total Pendapatan Lunas</div>
            <div class="summary-value">Rp <?= number_format($total_pendapatan_hari_ini,0,',','.'); ?></div>
        </div>

        <div class="summary-card card-lunas">
            <div class="summary-title">Transaksi Lunas</div>
            <div class="summary-value"><?= $total_transaksi_lunas; ?></div>
        </div>

        <div class="summary-card card-belum">
            <div class="summary-title">Belum Lunas</div>
            <div class="summary-value"><?= $total_transaksi_belum_lunas; ?></div>
        </div>
    </div>

    <h4>Rincian Semua Transaksi</h4>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Total</th>
                <th>Metode</th>
                <th>Status Bayar</th>
                <th>Status Pesanan</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
        <?php if ($detail_transaksi->num_rows > 0): ?>
            <?php while ($row = $detail_transaksi->fetch_assoc()): ?>
            <tr>
                <td><?= $row['id_pembayaran']; ?></td>
                <td>Rp <?= number_format($row['total_harga'],0,',','.'); ?></td>
                <td><?= $row['metode']; ?></td>
                <td class="<?= $row['status_bayar']=='Lunas'?'status-lunas':'status-belum-lunas'; ?>">
                    <?= $row['status_bayar']; ?>
                </td>
                <td><?= $row['status_pesanan']; ?></td>
                <td>
                    <a href="detail_pesanan.php?id=<?= $row['id_pembayaran']; ?>" class="btn-action-view">🔍</a>
                    <a href="cetak_harian.php?tanggal=<?= $tanggal_laporan; ?>" target="_blank" class="btn-action-view">🖨️ H</a>
                    <a href="cetak_bulanan.php?bulan=<?= date('Y-m', strtotime($tanggal_laporan)); ?>" target="_blank" class="btn-action-view">🖨️ B</a>
                    <a href="cetak_tahunan.php?tahun=<?= date('Y', strtotime($tanggal_laporan)); ?>" target="_blank" class="btn-action-view">🖨️ T</a>
                </td>
            </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr><td colspan="6" class="no-data">Tidak ada transaksi</td></tr>
        <?php endif; ?>
        </tbody>
    </table>

</div>
</body>
</html>
