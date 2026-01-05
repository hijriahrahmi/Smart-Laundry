<?php
session_start();
include 'koneksi.php'; // Pastikan file koneksi.php ada

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

$username = htmlspecialchars($_SESSION['username']);

$sidebar_links = [
    'dashboard_pelanggan.php' => '🏠 Beranda',
    'data_layanan.php' => '🧺 Layanan',
    'status_pesanan.php' => '📦 Status Pesanan',
    'riwayat_transaksi.php' => '🧾 Riwayat Transaksi',
    'logout.php' => '🚪 Logout',
];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Smart Laundry</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        @keyframes gradient-animation {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        @keyframes slide-gallery {
            0% { transform: translateX(0); } 
            100% { transform: translateX(-50%); } 
        }

        body { 
            margin:0; 
            font-family:'Poppins', sans-serif; 
            background: linear-gradient(-45deg, #3caea3, #45b649, #007bff, #3caea3);
            background-size: 400% 400%; 
            animation: gradient-animation 15s ease infinite; 
            color: #333; 
            min-height: 100vh;
        }

        .sidebar { 
            width:230px; 
            background:rgba(60, 174, 163, 0.95);
            color:white; 
            height:100vh; 
            position:fixed; 
            padding-top:30px; 
            box-shadow: 3px 0 10px rgba(0,0,0,0.1); 
            z-index: 10; 
        }

        .sidebar h2 {
            text-align: center;
            margin-bottom: 30px;
            font-size: 1.5em;
            border-bottom: 1px solid rgba(255,255,255,0.2);
            padding-bottom: 15px;
            font-weight: 700;
        }

        .sidebar a { 
            display:block; 
            padding:15px 25px; 
            color:white; 
            text-decoration:none; 
            transition: background 0.3s, padding-left 0.3s; 
            font-weight: 500;
        }

        .sidebar a:hover, 
        .sidebar a.active { 
            background:rgba(255,255,255,0.2); 
            padding-left: 30px; 
            border-left: 5px solid white;
        }

        .main-content { 
            margin-left:240px; 
            padding:30px; 
            position: relative; 
            z-index: 5; 
            overflow-x: hidden;
        }

        .welcome-box {
            background: #ffffff; 
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.05);
            max-width: 800px; 
        }

        .welcome-box h2 {
            color: #3caea3;
            margin-top: 0;
            font-weight: 600;
        }

        .gallery-container {
            margin-top: 30px;
            width: 100%; 
            overflow: hidden; 
        }

        .image-gallery {
            display: flex; 
            gap: 20px; 
            width: 200%; 
            animation: slide-gallery 20s linear infinite; 
        }

        .gallery-item {
            width: calc(33.33% - 13.33px); 
            min-width: 300px; 
        }

        .gallery-item img {
            width: 100%; 
            height: auto;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
        }
        
        .gallery-item img:hover {
            transform: scale(1.02); 
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <h2>Smart Laundry</h2>
        <?php 
        $current_page = basename($_SERVER['PHP_SELF']);
        foreach ($sidebar_links as $url => $label): ?>
            <a href="<?= $url ?>" class="<?= $current_page == $url ? 'active' : '' ?>">
                <?= $label ?>
            </a>
        <?php endforeach; ?>
    </div>

    <div class="main-content">
        <div class="welcome-box">
            <h2>Selamat Datang, <strong><?= $username; ?></strong> 👋</h2>
            <p>Pakaian bersih, mood pun fresh! Semua solusi laundry terbaik Anda ada di sini. 
            Jelajahi menu di sebelah kiri untuk <strong>memulai pemesanan cepat atau cek status cucian</strong>.</p>
        </div>
        
        <div class="gallery-container">
            <div class="image-gallery">
                <div class="gallery-item">
                    <img src="img/laundry1.png" alt="Layanan Express">
                    <p>Layanan Express</p>
                </div>

                <div class="gallery-item">
                    <img src="img/laundry2.png" alt="Proses Cuci">
                    <p>Proses Cuci</p>
                </div>

                <div class="gallery-item">
                    <img src="img/laundry3.png" alt="Interior Toko">
                    <p>Interior Toko</p>
                </div>
            </div>
        </div>
</body>
</html>
