-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jan 05, 2026 at 07:22 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `db_smartlaundry`
--

-- --------------------------------------------------------

--
-- Table structure for table `buku`
--

CREATE TABLE `buku` (
  `id` int(11) NOT NULL,
  `kode_buku` varchar(50) DEFAULT NULL,
  `judul` varchar(100) DEFAULT NULL,
  `pengarang` varchar(100) DEFAULT NULL,
  `tahun_terbit` varchar(10) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `buku`
--

INSERT INTO `buku` (`id`, `kode_buku`, `judul`, `pengarang`, `tahun_terbit`) VALUES
(1, '000', 'Matematika', 'Thabrani', '2017'),
(3, '021', 'agama', 'daniel', '2022');

-- --------------------------------------------------------

--
-- Table structure for table `detail_pesanan`
--

CREATE TABLE `detail_pesanan` (
  `detail_id` int(11) NOT NULL,
  `pesanan_id` int(11) NOT NULL,
  `layanan_id` int(11) NOT NULL,
  `berat_kg` decimal(10,0) NOT NULL,
  `subtotal` decimal(10,0) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `dosen`
--

CREATE TABLE `dosen` (
  `id` int(11) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `alamat` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `dosen`
--

INSERT INTO `dosen` (`id`, `nama`, `alamat`) VALUES
(2, 'YERIKA', 'SUDIANG'),
(3, 'THABRANI', 'MAROS');

-- --------------------------------------------------------

--
-- Table structure for table `layanan`
--

CREATE TABLE `layanan` (
  `layanan_id` int(11) NOT NULL,
  `nama_layanan` varchar(100) NOT NULL,
  `harga_per_kg` decimal(10,2) NOT NULL,
  `estimasi_hari` int(3) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `layanan`
--

INSERT INTO `layanan` (`layanan_id`, `nama_layanan`, `harga_per_kg`, `estimasi_hari`) VALUES
(1, 'Cuci Kering Standar', 6000.00, 3),
(2, 'Cuci Setrika Express', 10000.00, 1),
(3, 'Setrika Saja', 4500.00, 2);

-- --------------------------------------------------------

--
-- Table structure for table `pelanggan`
--

CREATE TABLE `pelanggan` (
  `pelanggan_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `nama_pelanggan` varchar(100) DEFAULT NULL,
  `alamat` text DEFAULT NULL,
  `no_hp` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `pesanan`
--

CREATE TABLE `pesanan` (
  `order_id` int(11) NOT NULL,
  `nama_pelanggan` varchar(100) NOT NULL,
  `tanggal_pesanan` datetime NOT NULL,
  `jenis_layanan` varchar(100) DEFAULT NULL,
  `berat_kg` decimal(5,2) DEFAULT NULL,
  `total_harga` int(11) NOT NULL,
  `status_pesanan` enum('Baru','Diproses','Dikirim','Selesai') NOT NULL DEFAULT 'Baru',
  `pelanggan_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pesanan`
--

INSERT INTO `pesanan` (`order_id`, `nama_pelanggan`, `tanggal_pesanan`, `jenis_layanan`, `berat_kg`, `total_harga`, `status_pesanan`, `pelanggan_id`) VALUES
(1, 'fauzi', '2025-11-22 00:00:00', 'Cuci + Setrika', 3.00, 30000, 'Selesai', NULL),
(2, 'nopal', '2025-11-23 18:01:44', 'Bed Cover Besar', 5.00, 175000, 'Selesai', NULL),
(3, 'Naufal', '2025-11-25 00:00:00', 'Cuci Kering Reguler', 3.00, 24000, 'Selesai', NULL),
(4, 'Nabil', '2025-11-26 16:21:57', 'Setrika Saja', 2.90, 17400, 'Selesai', NULL),
(5, 'yerika', '2025-11-27 04:23:43', 'Cuci Kering Reguler', 4.00, 32000, 'Selesai', NULL),
(6, 'hilmi', '2025-11-28 16:21:31', 'Setrika Saja', 3.00, 18000, 'Selesai', NULL),
(7, 'sudra', '2025-11-28 16:22:07', 'Cuci Kering Reguler', 3.00, 24000, 'Selesai', NULL),
(8, 'mutia', '2025-11-28 16:22:23', 'Setrika Saja', 4.00, 24000, 'Selesai', NULL),
(9, 'daniel', '2025-12-04 16:22:33', 'Cuci + Setrika', 3.00, 30000, 'Selesai', NULL),
(10, 'hasan', '2025-12-04 16:22:42', 'Cuci Kering Reguler', 2.00, 16000, 'Selesai', NULL),
(11, 'Nhia', '2025-12-07 07:35:41', 'Cuci Kering Reguler', 3.00, 24000, 'Diproses', NULL),
(12, 'Inci', '2025-12-08 07:36:10', 'Cuci Kering Express', 3.00, 45000, 'Diproses', NULL),
(13, 'Novi', '2025-12-09 07:36:26', 'Setrika Saja', 4.00, 24000, 'Diproses', NULL),
(14, 'Anto', '2025-12-18 07:21:35', 'Cuci + Setrika', 3.00, 30000, 'Baru', NULL),
(15, 'Idsyam', '2025-12-06 07:35:11', 'Bed Cover Besar', 2.00, 70000, 'Diproses', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `riwayat_transaksi`
--

CREATE TABLE `riwayat_transaksi` (
  `id_pembayaran` varchar(50) NOT NULL,
  `nama_pelanggan` varchar(100) NOT NULL,
  `tanggal_bayar` date NOT NULL,
  `total_harga` decimal(15,2) NOT NULL,
  `jumlah_bayar` decimal(15,2) NOT NULL,
  `metode` varchar(50) NOT NULL,
  `status_bayar` enum('Lunas','Belum Lunas') NOT NULL,
  `status_pesanan` enum('Selesai','Proses') NOT NULL,
  `kode_bayar` varchar(50) DEFAULT NULL,
  `rekening_tujuan` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `riwayat_transaksi`
--

INSERT INTO `riwayat_transaksi` (`id_pembayaran`, `nama_pelanggan`, `tanggal_bayar`, `total_harga`, `jumlah_bayar`, `metode`, `status_bayar`, `status_pesanan`, `kode_bayar`, `rekening_tujuan`) VALUES
('TR-251126-001', '', '2025-11-26', 17400.00, 18000.00, 'Transfer', 'Lunas', 'Selesai', NULL, NULL),
('TR-251128-001', '', '2025-11-25', 24.00, 24.00, 'Tunai', 'Lunas', 'Selesai', NULL, NULL),
('TR-251128-002', '', '2025-11-22', 30000.00, 0.00, 'Transfer', 'Lunas', 'Selesai', NULL, NULL),
('TR-251128-003', '', '2025-11-23', 175000.00, 0.00, 'Tunai', 'Lunas', 'Selesai', NULL, NULL),
('TR-251128-005', '', '2025-11-28', 18000.00, 0.00, 'Tunai', 'Lunas', 'Selesai', NULL, NULL),
('TR-251128-006', '', '2025-11-29', 24000.00, 24000.00, 'Transfer', 'Lunas', 'Selesai', NULL, NULL),
('TR-251128-007', '', '2025-11-29', 24000.00, 30000.00, 'Transfer', 'Lunas', 'Selesai', NULL, NULL),
('TR-251204-001', '', '2025-12-04', 30000.00, 30000.00, 'Tunai', 'Lunas', 'Selesai', NULL, NULL),
('TR-251204-002', '', '2025-12-04', 16000.00, 16000.00, 'Transfer', 'Lunas', 'Selesai', NULL, NULL),
('TR-251206-001', '', '2025-12-06', 24000.00, 24000.00, 'Tunai', 'Lunas', 'Selesai', NULL, NULL),
('TR-251207-001', '', '2025-12-07', 45000.00, 45000.00, 'Tunai', 'Lunas', 'Selesai', NULL, NULL),
('TR-251208-001', '', '2025-12-08', 24000.00, 24000.00, 'Transfer', 'Lunas', 'Selesai', NULL, NULL),
('TR-251210-001', '', '2025-12-10', 30000.00, 30000.00, 'Transfer', 'Lunas', 'Selesai', NULL, NULL),
('TR-251211-001', '', '2025-12-11', 70000.00, 70000.00, 'Transfer', 'Lunas', 'Selesai', NULL, NULL),
('TR-251215-001', '', '2025-11-27', 32000.00, 32000.00, 'Tunai', 'Lunas', 'Selesai', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `transaksi`
--

CREATE TABLE `transaksi` (
  `id_transaksi` int(11) NOT NULL,
  `nama_pelanggan` varchar(100) NOT NULL,
  `id_layanan` int(11) NOT NULL,
  `berat` decimal(10,2) NOT NULL,
  `total_bayar` decimal(10,2) NOT NULL,
  `kasir_id` int(11) NOT NULL,
  `status_pesanan` varchar(50) NOT NULL DEFAULT 'Baru',
  `tanggal_masuk` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(255) NOT NULL,
  `alamat` text DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','kasir','customer') NOT NULL DEFAULT 'customer',
  `status` varchar(255) NOT NULL DEFAULT 'pending',
  `verification_token` varchar(64) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `username`, `email`, `alamat`, `password`, `role`, `status`, `verification_token`, `created_at`) VALUES
(29, 'fauzi', 'andinurfauzi2102@gmail.com', 'sulsel', '$2y$10$8vl9cZ/8u/q7pgrccgXaXO5OCbwe434jP6mX/ebu7/dGC9OBVnIbW', 'customer', 'verified', NULL, '2025-11-27 05:03:03'),
(30, 'kasir', 'heningtersirat@gmail.com', 'dipa', '$2y$10$5rAF1lGTVTJT12n209qIw.LsV1dSeZLcoTwuXhpckROeG32NxLsGa', 'kasir', 'active', NULL, '2025-12-03 04:02:32'),
(39, 'admin', 'untukkegabutansaja05@gmail.com', 'maros', '$2y$10$NMC3W1gpEfr7qIXvVsFLpucOdETZuxLCI/ThrX.Be23XBpaeHb.Bu', 'admin', 'active', NULL, '2025-12-15 03:49:42');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `buku`
--
ALTER TABLE `buku`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `detail_pesanan`
--
ALTER TABLE `detail_pesanan`
  ADD PRIMARY KEY (`detail_id`);

--
-- Indexes for table `dosen`
--
ALTER TABLE `dosen`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `layanan`
--
ALTER TABLE `layanan`
  ADD PRIMARY KEY (`layanan_id`);

--
-- Indexes for table `pelanggan`
--
ALTER TABLE `pelanggan`
  ADD PRIMARY KEY (`pelanggan_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `pesanan`
--
ALTER TABLE `pesanan`
  ADD PRIMARY KEY (`order_id`);

--
-- Indexes for table `riwayat_transaksi`
--
ALTER TABLE `riwayat_transaksi`
  ADD PRIMARY KEY (`id_pembayaran`);

--
-- Indexes for table `transaksi`
--
ALTER TABLE `transaksi`
  ADD PRIMARY KEY (`id_transaksi`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `buku`
--
ALTER TABLE `buku`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `detail_pesanan`
--
ALTER TABLE `detail_pesanan`
  MODIFY `detail_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `dosen`
--
ALTER TABLE `dosen`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `layanan`
--
ALTER TABLE `layanan`
  MODIFY `layanan_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `pelanggan`
--
ALTER TABLE `pelanggan`
  MODIFY `pelanggan_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `pesanan`
--
ALTER TABLE `pesanan`
  MODIFY `order_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=46;

--
-- AUTO_INCREMENT for table `transaksi`
--
ALTER TABLE `transaksi`
  MODIFY `id_transaksi` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=40;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `pelanggan`
--
ALTER TABLE `pelanggan`
  ADD CONSTRAINT `pelanggan_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
