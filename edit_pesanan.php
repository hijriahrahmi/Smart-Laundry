<?php
// File: edit_pesanan.php

session_start();
// Asumsi file ini berisi detail koneksi $host, $user, $pass, $db
include 'koneksi.php'; 

// Cek autentikasi & otorisasi
if (!isset($_SESSION['username']) || ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'kasir')) {
    // Jika tidak login atau bukan admin/kasir, alihkan ke login
    header("Location: login.php");
    exit();
}

// Data Layanan (Mockup - harus konsisten)
$daftar_layanan = [
    ['id' => 1, 'nama' => 'Cuci Kering Reguler', 'harga' => 8000, 'satuan' => 'per Kg', 'estimasi' => '48 Jam'],
    ['id' => 2, 'nama' => 'Cuci + Setrika', 'harga' => 10000, 'satuan' => 'per Kg', 'estimasi' => '48 Jam'],
    ['id' => 3, 'nama' => 'Cuci Kering Express', 'harga' => 15000, 'satuan' => 'per Kg', 'estimasi' => '6 Jam'],
    ['id' => 4, 'nama' => 'Setrika Saja', 'harga' => 6000, 'satuan' => 'per Kg', 'estimasi' => '24 Jam'],
    ['id' => 5, 'nama' => 'Bed Cover Besar', 'harga' => 35000, 'satuan' => 'per Pcs', 'estimasi' => '72 Jam'],
    ['id' => 6, 'nama' => 'Gordyn Tebal', 'harga' => 45000, 'satuan' => 'per Pcs', 'estimasi' => '72 Jam'],
];

$message = '';
$order_data = null;
$order_id = null;

// Koneksi ke Database
$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Koneksi ke database gagal: " . $conn->connect_error);
}

// 1. **Proses UPDATE Data (Saat Formulir Disubmit)**
if (isset($_POST['submit_edit_order'])) {
    $order_id_to_update = (int)$_POST['order_id'];
    $nama_pelanggan     = trim($_POST['nama_pelanggan']);
    $tanggal_pesanan    = $_POST['tanggal_pesanan'] . date(' H:i:s'); // Pertahankan jam saat ini/default
    
    $jenis_layanan      = $_POST['jenis_layanan'];
    $berat_kg           = (float)$_POST['berat_kg'];

    // Bersihkan input harga dari format titik/koma ribuan sebelum disimpan
    $harga_bersih = preg_replace('/[^\d]/', '', $_POST['total_harga']); 
    $total_harga = (int)$harga_bersih; 
    
    $status_pesanan = $_POST['status_pesanan'];
    
    // Query UPDATE menggunakan Prepared Statement
    $sql_update = "UPDATE pesanan SET 
                     nama_pelanggan = ?, 
                     tanggal_pesanan = ?, 
                     jenis_layanan = ?, 
                     berat_kg = ?,
                     total_harga = ?, 
                     status_pesanan = ? 
                     WHERE order_id = ?";
    
    $stmt = $conn->prepare($sql_update);
    
    // Tipe data (7 parameter): s (nama), s (tgl), s (layanan), d (berat), i (harga), s (status), i (id)
    $stmt->bind_param("sssdisi", 
        $nama_pelanggan, 
        $tanggal_pesanan, 
        $jenis_layanan, 
        $berat_kg, 
        $total_harga, 
        $status_pesanan, 
        $order_id_to_update
    ); 
    
    if ($stmt->execute()) {
        $message = "✅ Data pesanan **#$order_id_to_update** berhasil diperbarui!";
        // Setelah update, set order_id agar data terbaru bisa diambil
        $order_id = $order_id_to_update;
    } else {
        $message = "❌ Error saat mengupdate pesanan: " . $stmt->error;
        $order_id = $order_id_to_update;
    }
    $stmt->close();

} 
// 2. **Proses FETCH Data Awal (Saat Diakses dari link Edit)**
else if (isset($_GET['id']) && !empty($_GET['id'])) {
    $order_id = (int)$_GET['id'];
}

// 3. **Ambil Data Pesanan (Setelah update atau saat dimuat pertama kali)**
if ($order_id) {
    $sql_fetch = "SELECT `order_id`, `nama_pelanggan`, `tanggal_pesanan`, `jenis_layanan`, `berat_kg`, `total_harga`, `status_pesanan` FROM pesanan WHERE order_id = ?";
    $stmt_fetch = $conn->prepare($sql_fetch);
    $stmt_fetch->bind_param("i", $order_id);
    $stmt_fetch->execute();
    $result_fetch = $stmt_fetch->get_result();

    if ($result_fetch && $result_fetch->num_rows > 0) {
        $order_data = $result_fetch->fetch_assoc();
    } else {
        $message = "❌ Pesanan dengan ID #$order_id tidak ditemukan.";
        $order_id = null;
    }
    $stmt_fetch->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>🛠️ Edit Pesanan #<?php echo htmlspecialchars($order_id ?? 'N/A'); ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        /* CSS Styling */
        :root { --primary-color: #3caea3; --secondary-color: #1f3936; }
        body { font-family:'Poppins', sans-serif; background:#f3f7fb; margin: 0; }
        .container { max-width: 650px; margin: 50px auto; padding: 30px; background: white; border-radius: 12px; box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1); }
        h2 { border-bottom: 2px solid var(--primary-color); padding-bottom: 10px; margin-bottom: 25px; color: var(--primary-color); }
        label { display: block; margin-top: 15px; margin-bottom: 5px; font-weight: 600; color: #555; }
        input[type="text"], input[type="number"], input[type="date"], select { 
            width: 100%; padding: 10px; margin-bottom: 5px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; 
        }
        button { padding: 10px 15px; background-color: var(--primary-color); color: white; border: none; border-radius: 4px; cursor: pointer; transition: background 0.3s; margin-right: 10px; font-weight: 500;}
        button:hover { background-color: #2e8b7e; }
        .btn-secondary { background-color: #6c757d; }
        .btn-secondary:hover { background-color: #5a6268; }
        .notification { padding: 15px; margin-bottom: 20px; border-radius: 4px; font-weight: 500; }
        .notification.success { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .notification.error { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .notification.info { background-color: #fff3cd; color: #856404; border: 1px solid #ffeeba; }
    </style>
    
    <script>
        // Fungsi untuk membersihkan dan memformat harga
        function formatRupiah(angka) {
            let number_string = angka.toString().replace(/[^,\d]/g, '');
            let split = number_string.split(',');
            let sisa = split[0].length % 3;
            let rupiah = split[0].substr(0, sisa);
            let ribuan = split[0].substr(sisa).match(/\d{3}/gi);
    
            if (ribuan) {
                let separator = sisa ? '.' : '';
                rupiah += separator + ribuan.join('.');
            }
            rupiah = split[1] != undefined ? rupiah + ',' + split[1] : rupiah;
            return rupiah;
        }

        // Ambil data layanan dari PHP (untuk perhitungan harga)
        const daftarLayananJS = <?= json_encode($daftar_layanan); ?>;
        const hargaMapJS = daftarLayananJS.reduce((acc, current) => {
            acc[current.nama] = {
                harga: current.harga,
                satuan: current.satuan
            };
            return acc;
        }, {});
        
        function hitungTotalHarga() {
            const selectLayanan = document.getElementById('jenis_layanan');
            const inputBerat = document.getElementById('berat_kg');
            const inputHarga = document.getElementById('total_harga');

            const layananPilihan = selectLayanan.value;
            const berat = parseFloat(inputBerat.value) || 0;
            let totalHarga = 0;

            if (layananPilihan && berat > 0 && hargaMapJS[layananPilihan]) {
                const info = hargaMapJS[layananPilihan];
                const hargaPerUnit = info.harga;

                // Hitung total
                totalHarga = hargaPerUnit * berat;
            }

            // Format angka untuk tampilan (tanpa simbol Rp)
            inputHarga.value = formatRupiah(Math.round(totalHarga));
        }

        document.addEventListener('DOMContentLoaded', () => {
            const selectLayanan = document.getElementById('jenis_layanan');
            const inputBerat = document.getElementById('berat_kg');
            const inputHarga = document.getElementById('total_harga'); // Tambahkan listener ke Total Harga untuk membantu Kasir
            
            selectLayanan.addEventListener('change', hitungTotalHarga);
            inputBerat.addEventListener('input', hitungTotalHarga);

            // Opsional: Hilangkan format rupiah saat fokus pada Total Harga
            inputHarga.addEventListener('focus', function() {
                this.value = this.value.replace(/[.]/g, ''); // Hapus titik ribuan
            });
            // Opsional: Terapkan format rupiah lagi saat blur
            inputHarga.addEventListener('blur', function() {
                this.value = formatRupiah(this.value);
            });
            
            // Inisialisasi hitung harga saat dimuat
            hitungTotalHarga(); 
        });
    </script>
</head>
<body>
    <div class="container">
        <h2>🛠️ Edit Data Pesanan #<?php echo htmlspecialchars($order_id ?? 'N/A'); ?></h2>
        
        <?php if (!empty($message)): ?>
            <div class="notification <?php echo (strpos($message, '✅') !== false) ? 'success' : 'error'; ?>">
                <?php echo nl2br(htmlspecialchars($message)); ?>
            </div>
        <?php endif; ?>

        <?php if ($order_data): ?>
            <form method="POST" action="edit_pesanan.php">
                <input type="hidden" name="order_id" value="<?php echo htmlspecialchars($order_data['order_id']); ?>">

                <label for="nama_pelanggan">Nama Pelanggan:</label>
                <input type="text" id="nama_pelanggan" name="nama_pelanggan" 
                        value="<?php echo htmlspecialchars($order_data['nama_pelanggan']); ?>" required>

                <label for="jenis_layanan">Jenis Layanan:</label>
                <select id="jenis_layanan" name="jenis_layanan" required>
                    <?php 
                    foreach ($daftar_layanan as $layanan): 
                        // Tambahkan info harga/satuan di option text untuk digunakan JS
                        $option_text = htmlspecialchars($layanan['nama']);
                        $selected = ($order_data['jenis_layanan'] === $layanan['nama']) ? 'selected' : '';
                        echo "<option value=\"" . htmlspecialchars($layanan['nama']) . "\" $selected>$option_text</option>";
                    endforeach; 
                    ?>
                </select>

                <label for="berat_kg">Berat/Jumlah Cucian (Kg/Pcs):</label>
                <input type="number" step="0.1" min="0.1" id="berat_kg" name="berat_kg" required 
                        value="<?php echo htmlspecialchars($order_data['berat_kg']); ?>" 
                        placeholder="Contoh: 2.5">
                        
                <label for="tanggal_pesanan">Tanggal Pesanan:</label>
                <input type="date" id="tanggal_pesanan" name="tanggal_pesanan" 
                        value="<?php echo htmlspecialchars(date('Y-m-d', strtotime($order_data['tanggal_pesanan']))); ?>" required>

                <label for="total_harga">Total Harga (Rp):</label>
                <input type="text" id="total_harga" name="total_harga" min="0" 
                        value="<?php echo number_format($order_data['total_harga'], 0, ',', '.'); ?>" 
                        placeholder="Masukkan angka tanpa titik/koma" required>

                <label for="status_pesanan">Status Pesanan:</label>
                <select id="status_pesanan" name="status_pesanan" required>
                    <?php
                    // Status Dibatalkan ditambahkan untuk Kasir
                    $statuses = ['Pending', 'Diproses', 'Dikirim', 'Selesai', 'Dibatalkan']; 
                    foreach ($statuses as $status) {
                        $selected = ($order_data['status_pesanan'] === $status) ? 'selected' : '';
                        echo "<option value=\"$status\" $selected>$status</option>";
                    }
                    ?>
                </select>
                
                <hr style="margin-top: 25px; margin-bottom: 25px;">
                <button type="submit" name="submit_edit_order">Simpan Perubahan</button>
                <a href="status_pesanan.php"><button type="button" class="btn-secondary">Batal / Kembali</button></a>
            </form>
        <?php else: ?>
            <div class="notification info">
                Pesanan tidak valid atau tidak ditemukan. Mohon periksa ID Pesanan.
            </div>
            <a href="status_pesanan.php"><button class="btn-secondary" style="margin-top: 15px;">Kembali ke Status Pesanan</button></a>
        <?php endif; ?>
    </div>
</body>
</html>