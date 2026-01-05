<?php
session_start();
include 'koneksi.php'; // Pastikan file ini koneksi databasenya benar

// Memastikan hanya user dengan role 'kasir' yang bisa mengakses
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'kasir') {
    header("Location: login.php");
    exit();
}

// Ambil data pelanggan beserta jumlah transaksi berdasarkan nama pelanggan
$query = "
    SELECT 
        pel.pelanggan_id AS id, 
        pel.nama_pelanggan AS nama, 
        pel.no_hp AS telepon, 
        pel.alamat AS alamat,
        COUNT(ps.order_id) AS total_transaksi
    FROM pelanggan pel
    LEFT JOIN pesanan ps ON pel.nama_pelanggan = ps.nama_pelanggan
    GROUP BY pel.pelanggan_id
    ORDER BY total_transaksi DESC
";

$result = mysqli_query($conn, $query);

$pelanggan = [];
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $pelanggan[] = $row;
    }
} else {
    echo "Error Query: " . mysqli_error($conn);
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Data Pelanggan - Smart Laundry</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #3caea3;
            --secondary-color: #1f3936;
            --background-light: #f3f7fb;
            --card-shadow: 0 4px 12px rgba(0,0,0,0.08);
        }
        body { margin:0; font-family:'Poppins', sans-serif; background: var(--background-light); padding: 30px; }
        h1 { color: var(--secondary-color); }
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
        .add-button:hover { background: #1e5fa0; }
        .table-container {
            margin-top: 20px;
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: var(--card-shadow);
            overflow-x: auto;
        }
        table { width: 100%; border-collapse: collapse; font-size: 0.9em; }
        th, td { padding: 12px 15px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background-color: var(--primary-color); color: white; font-weight: 600; }
        tr:nth-child(even) { background-color: #f8f8f8; }
        .action-link {
            text-decoration: none;
            color: #2a7ae4;
            margin-right: 10px;
            font-weight: 500;
        }
        .action-link:hover {
            color: var(--secondary-color);
        }
    </style>
</head>
<body>
    <h1>👤 Data Pelanggan</h1>
    <p>Di halaman ini, kasir dapat melihat daftar, menambah, dan mengedit data pelanggan berdasarkan transaksi.</p>
    
    <a href="tambah_pelanggan.php" class="add-button">➕ Tambah Pelanggan Baru</a>

    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nama Pelanggan</th>
                    <th>No. Telepon</th>
                    <th>Alamat</th>
                    <th>Total Transaksi</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($pelanggan)): ?>
                    <?php foreach ($pelanggan as $data): ?>
                        <tr>
                            <td><?= htmlspecialchars($data['id']); ?></td>
                            <td><?= htmlspecialchars($data['nama']); ?></td>
                            <td><?= htmlspecialchars($data['telepon']); ?></td>
                            <td><?= htmlspecialchars($data['alamat']); ?></td>
                            <td><?= htmlspecialchars($data['total_transaksi']); ?>x</td>
                            <td>
                                <a href="edit_pelanggan.php?id=<?= $data['id']; ?>" class="action-link">✏️ Edit</a>
                                <a href="hapus_pelanggan.php?id=<?= $data['id']; ?>" class="action-link" onclick="return confirm('Yakin ingin menghapus data ini?');">🗑️ Hapus</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" style="text-align: center;">Belum ada data pelanggan yang tersedia.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <p style="margin-top: 30px;">
        <a href="dashboard_kasir.php">« Kembali ke Dashboard</a>
    </p>
</body>
</html>
