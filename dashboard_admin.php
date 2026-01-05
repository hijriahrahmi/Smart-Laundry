<?php
session_start();
include 'koneksi.php'; // Koneksi database

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

$username = htmlspecialchars($_SESSION['username']);

// Ambil statistik data dari database
$total_pelanggan = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM users WHERE role='pelanggan'"))['total'];
$total_pesanan = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM pesanan"))['total'];
$pesanan_aktif = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM pesanan WHERE status_pesanan='proses' OR status_pesanan='menunggu'"))['total'];
$pesanan_selesai = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM pesanan WHERE status_pesanan='selesai'"))['total'];

// Jika ada transaksi & harga
$total_pendapatan = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(total_harga) AS total FROM pesanan"))['total'];
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - Smart Laundry</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">

    <style>
        /* Animasi Background */
        @keyframes gradient-animation {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        body {
            margin: 0;
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(-45deg, #3caea3, #45b649, #007bff, #3caea3);
            background-size: 400% 400%;
            animation: gradient-animation 15s ease infinite;
            min-height: 100vh;
            color: white;
        }

        /* Sidebar */
        .sidebar {
            width: 230px;
            position: fixed;
            height: 100vh;
            background: rgba(0, 0, 0, 0.6);
            padding-top: 30px;
            box-shadow: 2px 0 8px rgba(0,0,0,0.3);
        }

        .sidebar h2 {
            text-align: center;
            color: white;
            font-weight: 700;
            margin-bottom: 30px;
        }

        .sidebar a {
            display: block;
            padding: 15px 25px;
            color: white;
            text-decoration: none;
            transition: all 0.3s;
        }

        .sidebar a:hover, .sidebar a.active {
            background: rgba(255, 255, 255, 0.2);
            border-left: 5px solid white;
        }

        /* Konten */
        .main-content {
            margin-left: 250px;
            padding: 30px;
        }

        .dashboard-title {
            font-size: 28px;
            font-weight: 600;
            margin-bottom: 20px;
        }

        /* Statistik Box */
        .stats-container {
            display: flex;
            gap: 20px;
            flex-wrap: wrap;
        }

        .stat-box {
            flex: 1;
            min-width: 220px;
            background: rgba(255,255,255,0.15);
            backdrop-filter: blur(5px);
            padding: 20px;
            border-radius: 12px;
            text-align: center;
            box-shadow: 0 4px 12px rgba(0,0,0,0.3);
        }

        .stat-box h3 {
            margin: 0;
            font-size: 20px;
            font-weight: 600;
        }

        .stat-box p {
            font-size: 28px;
            margin-top: 10px;
            font-weight: 700;
            color: #FFD700;
        }

    </style>
</head>

<body>

    <div class="sidebar">
        <h2>Smart Laundry</h2>
        <a href="dashboard_admin.php" class="active">📊 Dashboard</a>
        <a href="status_pesanan.php" > '📦 Status Pesanan</a>
        <a href="riwayat_transaksi.php">🧾 Riwayat Transaksi</a>
        <a href="logout.php">🚪 Logout</a>
    </div>

    <div class="main-content">
        <h2 class="dashboard-title">Selamat Datang Admin, <strong><?= $username; ?></strong> 👋</h2>

        <div class="stats-container">
            <div class="stat-box">
                <h3>Total Pelanggan</h3>
                <p><?= $total_pelanggan ?></p>
            </div>

            <div class="stat-box">
                <h3>Pesanan Aktif</h3>
                <p><?= $pesanan_aktif ?></p>
            </div>

            <div class="stat-box">
                <h3>Pesanan Selesai</h3>
                <p><?= $pesanan_selesai ?></p>
            </div>

            <div class="stat-box">
                <h3>Total Pesanan</h3>
                <p><?= $total_pesanan ?></p>
            </div>

            <div class="stat-box">
                <h3>Total Pendapatan</h3>
                <p>Rp <?= number_format($total_pendapatan, 0, ',', '.') ?></p>
            </div>
        </div>
    </div>

</body>

</html>
