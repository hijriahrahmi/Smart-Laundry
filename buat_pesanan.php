<?php
// Aktifkan Error Reporting untuk debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// ===================================================
// Bagian 1: Konfigurasi Database & Variabel Aplikasi
// ===================================================

$servername = "localhost"; 
$username   = "root";       
$password   = "";           
$dbname     = "db_smartlaundry"; 

$message = "";

// Set dashboard kasir
$dashboard_url = "dashboard_kasir.php";

// Daftar Layanan
$daftar_layanan = [
    ['id' => 1, 'nama' => 'Cuci Kering Reguler', 'harga' => 8000, 'satuan' => 'per Kg', 'estimasi' => '48 Jam'],
    ['id' => 2, 'nama' => 'Cuci + Setrika', 'harga' => 10000, 'satuan' => 'per Kg', 'estimasi' => '48 Jam'],
    ['id' => 3, 'nama' => 'Cuci Kering Express', 'harga' => 15000, 'satuan' => 'per Kg', 'estimasi' => '6 Jam'],
    ['id' => 4, 'nama' => 'Setrika Saja', 'harga' => 6000, 'satuan' => 'per Kg', 'estimasi' => '24 Jam'],
    ['id' => 5, 'nama' => 'Bed Cover Besar', 'harga' => 35000, 'satuan' => 'per Pcs', 'estimasi' => '72 Jam'],
    ['id' => 6, 'nama' => 'Gordyn Tebal', 'harga' => 45000, 'satuan' => 'per Pcs', 'estimasi' => '72 Jam'],
];


// ===================================================
// Bagian 2: Proses Form
// ===================================================
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    $nama_pelanggan = htmlspecialchars($_POST['nama_pelanggan']);
    $jenis_layanan  = htmlspecialchars($_POST['jenis_layanan']);
    $berat_kg       = (float)$_POST['berat_kg'];
    $total_harga    = (int)$_POST['total_harga'];

    if ($berat_kg <= 0 || $total_harga <= 0) {
        $message = "<div class='message error'>❌ Berat/Jumlah atau Total Harga harus lebih besar dari 0.</div>";
    } else {

        $tanggal_pesanan = date('Y-m-d H:i:s'); 

        $conn = new mysqli($servername, $username, $password, $dbname);

        if ($conn->connect_error) {
            $message = "<div class='message error'>❌ KONEKSI DATABASE GAGAL! Error: " . $conn->connect_error . "</div>";
        } else {

            $sql = "INSERT INTO pesanan (nama_pelanggan, tanggal_pesanan, jenis_layanan, berat_kg, total_harga)
                    VALUES (?, ?, ?, ?, ?)";
            
            $stmt = $conn->prepare($sql);

            if ($stmt) {
                $stmt->bind_param("sssdi", $nama_pelanggan, $tanggal_pesanan, $jenis_layanan, $berat_kg, $total_harga);

                if ($stmt->execute()) {
                    $last_id = $stmt->insert_id;
                    $message = "<div class='message success'>✅ Pesanan dengan ID <b>#$last_id</b> berhasil ditambahkan!</div>";
                } else {
                    $message = "<div class='message error'>❌ Error saat menyimpan data: " . $stmt->error . "</div>";
                }
                $stmt->close();
            } else {
                $message = "<div class='message error'>❌ Error dalam persiapan query: " . $conn->error . "</div>";
            }

            $conn->close();
        }
    }
}

?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Form Pembuatan Pesanan Laundry</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

    <style>
        body { 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
            background-color: #e9ebee; 
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
        }
        .container { 
            width: 100%;
            max-width: 450px; 
            background: white; 
            padding: 30px; 
            border-radius: 12px; 
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1); 
        }
        h2 { 
            text-align: center; 
            color: #1abc9c; 
            margin-bottom: 25px;
        }
        .form-group { margin-bottom: 15px; }
        label { font-weight: 600; }
        input[type="text"], input[type="number"], select { 
            width: 100%; 
            padding: 10px; 
            border: 1px solid #ced4da; 
            border-radius: 6px;
        }
        #total_harga {
            background-color: #f8f9fa; 
            font-weight: bold;
        }
        .button-group {
            margin-top: 20px;
            display: flex;
            flex-direction: column;
            gap: 10px;
        }
        button, .btn-back { 
            padding: 12px 15px; 
            border-radius: 6px; 
            cursor: pointer; 
            width: 100%; 
            font-weight: bold;
            font-size: 16px;
            text-align: center;
            text-decoration: none; 
        }
        button { 
            background-color: #1abc9c; 
            color: white; 
        }
        .btn-back {
            background-color: #95a5a6;
            color: white;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        .btn-back:hover { background-color: #7f8c8d; }
        .message { padding: 12px; margin-bottom: 20px; border-radius: 8px; }
        .success { background-color: #e8f8f5; border: 1px solid #1abc9c; color: #14a085; }
        .error   { background-color: #fcebeb; border: 1px solid #e74c3c; color: #c0392b; }
    </style>
</head>

<body>

<div class="container">
    <h2>📝 Buat Pesanan Laundry</h2>

    <?= $message; ?>

    <form method="POST" action="buat_pesanan.php">
        
        <div class="form-group">
            <label for="nama_pelanggan">Nama Pelanggan:</label>
            <input type="text" id="nama_pelanggan" name="nama_pelanggan" required>
        </div>
        
        <div class="form-group">
            <label for="jenis_layanan">Jenis Layanan:</label>
            <select id="jenis_layanan" name="jenis_layanan" required onchange="updateUIAndCalculate()">
                <?php foreach ($daftar_layanan as $layanan): ?>
                    <option 
                        value="<?= htmlspecialchars($layanan['nama']); ?>" 
                        data-harga="<?= $layanan['harga']; ?>"
                        data-satuan="<?= htmlspecialchars($layanan['satuan']); ?>"
                    >
                        <?= htmlspecialchars($layanan['nama']) . " (Rp " . number_format($layanan['harga'], 0, ',', '.') . " / " . $layanan['satuan'] . ")"; ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        
        <div class="form-group">
            <label for="berat_kg">Berat/Jumlah (<span id="satuan_unit">Kg</span>):</label>
            <input type="number" id="berat_kg" name="berat_kg" step="0.01" value="0.1" min="0.1" required oninput="updateUIAndCalculate()">
        </div>
        
        <div class="form-group">
            <label for="total_harga">Total Harga (Rp):</label>
            <input type="number" id="total_harga" name="total_harga" readonly value="0">
        </div>
        
        <div class="button-group">
            <button type="submit">Tambah Pesanan</button>

            <!-- Tombol kembali dashboard kasir -->
            <a href="dashboard_kasir.php" class="btn-back">
                <i class="fas fa-arrow-left"></i> Kembali ke Dashboard Kasir
            </a>
        </div>

    </form>
</div>

<script>
function updateUIAndCalculate() {

    const jenis = document.getElementById('jenis_layanan');
    const beratInput = document.getElementById('berat_kg');
    const total = document.getElementById('total_harga');
    const satuanUnit = document.getElementById('satuan_unit');

    const opt = jenis.options[jenis.selectedIndex];
    const harga = parseFloat(opt.getAttribute('data-harga')) || 0;
    const satuan = opt.getAttribute('data-satuan');

    satuanUnit.textContent = satuan.replace('per ', '');

    if (satuan.includes("Pcs")) {
        beratInput.step = "1";
        beratInput.min = "1";
        if (beratInput.value < 1) beratInput.value = 1;
    } else {
        beratInput.step = "0.01";
        beratInput.min = "0.1";
    }

    let berat = parseFloat(beratInput.value);
    if (isNaN(berat) || berat <= 0) berat = 0;

    let totalHarga = Math.round(harga * berat);

    total.value = totalHarga;
}

window.onload = updateUIAndCalculate;
</script>

</body>
</html>
