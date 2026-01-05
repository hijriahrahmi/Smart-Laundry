<?php
// File: daftar_harga.php
// Daftar Layanan Statis (Mockup)

$daftar_layanan = [
    ['id' => 1, 'nama' => 'Cuci Kering Reguler', 'harga' => 8000, 'satuan' => 'per Kg', 'estimasi' => '48 Jam'],
    ['id' => 2, 'nama' => 'Cuci + Setrika', 'harga' => 10000, 'satuan' => 'per Kg', 'estimasi' => '48 Jam'],
    ['id' => 3, 'nama' => 'Cuci Kering Express', 'harga' => 15000, 'satuan' => 'per Kg', 'estimasi' => '6 Jam'],
    ['id' => 4, 'nama' => 'Setrika Saja', 'harga' => 6000, 'satuan' => 'per Kg', 'estimasi' => '24 Jam'],
    ['id' => 5, 'nama' => 'Bed Cover Besar', 'harga' => 35000, 'satuan' => 'per Pcs', 'estimasi' => '72 Jam'],
    ['id' => 6, 'nama' => 'Gordyn Tebal', 'harga' => 45000, 'satuan' => 'per Pcs', 'estimasi' => '72 Jam'],
];

?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Harga Layanan Laundry</title>
    <style>
        /* CSS Sederhana untuk Tampilan Tabel */
        body { 
            font-family: 'Arial', sans-serif; 
            margin: 20px; 
            background-color: #f4f4f4;
        }
        h1 { 
            color: #333; 
            border-bottom: 2px solid #3caea3;
            padding-bottom: 10px;
        }
        table { 
            width: 90%; 
            border-collapse: collapse; 
            margin-top: 20px; 
            background-color: white;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            border-radius: 8px;
            overflow: hidden; /* Penting untuk radius */
        }
        th, td { 
            border: 1px solid #ddd; 
            padding: 15px; 
            text-align: left; 
        }
        th { 
            background-color: #3caea3; 
            color: white; 
            font-weight: 600;
            text-transform: uppercase;
        }
        tr:nth-child(even) { 
            background-color: #f9f9f9; 
        }
        .price-col {
            font-weight: bold;
            color: #d9534f; /* Warna merah agar harga menonjol */
        }
        .center-text {
            text-align: center;
        }
    </style>
</head>
<body>

    <h1>📋 Daftar Harga Layanan Laundry</h1>
    <p>Berikut adalah daftar lengkap layanan dan tarif yang kami tawarkan:</p>

    <?php
    // === TAMPILKAN DATA DARI ARRAY ===
    if (!empty($daftar_layanan)) {
        echo "<table>";
        echo "<thead>";
        echo "<tr>";
        echo "<th style='width: 5%;'>ID</th>";
        echo "<th style='width: 40%;'>Nama Layanan</th>";
        echo "<th style='width: 20%;'>Harga (Rp)</th>";
        echo "<th style='width: 10%;' class='center-text'>Satuan</th>";
        echo "<th style='width: 25%;'>Estimasi Pengerjaan</th>";
        echo "</tr>";
        echo "</thead>";
        echo "<tbody>";

        // Loop untuk mengambil setiap baris data dari array
        foreach($daftar_layanan as $layanan) {
            echo "<tr>";
            
            // ID
            echo "<td>" . htmlspecialchars($layanan["id"]) . "</td>";
            
            // Nama Layanan
            echo "<td>" . htmlspecialchars($layanan["nama"]) . "</td>"; 
            
            // Harga (diformat Rupiah)
            echo "<td class='price-col'>" . number_format($layanan["harga"], 0, ',', '.') . "</td>"; 
            
            // Satuan
            echo "<td class='center-text'>" . htmlspecialchars($layanan["satuan"]) . "</td>"; 
            
            // Estimasi Waktu
            echo "<td>" . htmlspecialchars($layanan["estimasi"]) . "</td>"; 
            
            echo "</tr>";
        }

        echo "</tbody>";
        echo "</table>";
    } else {
        echo "<p>⚠️ Tidak ada data layanan yang tersedia saat ini.</p>";
    }
    ?>

</body>
</html>