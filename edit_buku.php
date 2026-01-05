<?php
session_start();
include 'koneksi.php';

// Cegah akses tanpa login
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

$id = $_GET['id'];
$result = mysqli_query($conn, "SELECT * FROM buku WHERE id='$id'");
$data = mysqli_fetch_assoc($result);

// Update data buku
if (isset($_POST['update'])) {
    $kode_buku = $_POST['kode_buku'];
    $judul = $_POST['judul'];
    $pengarang = $_POST['pengarang'];
    $tahun_terbit = $_POST['tahun_terbit'];

    mysqli_query($conn, "UPDATE buku SET 
        kode_buku='$kode_buku',
        judul='$judul',
        pengarang='$pengarang',
        tahun_terbit='$tahun_terbit'
        WHERE id='$id'");

    header("Location: data_buku.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Edit Buku - SmartLaundry</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f5f6fa;
            padding: 20px;
        }
        .container {
            width: 50%;
            margin: auto;
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        h1 {
            text-align: center;
            color: #2f3640;
        }
        label {
            font-weight: bold;
        }
        input[type=text] {
            width: 100%;
            padding: 8px;
            margin: 6px 0 15px 0;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        button {
            background: #487eb0;
            color: white;
            border: none;
            padding: 10px 15px;
            border-radius: 5px;
            cursor: pointer;
        }
        a {
            text-decoration: none;
            color: #273c75;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Edit Buku</h1>
        <form method="post">
            <label>Kode Buku:</label>
            <input type="text" name="kode_buku" value="<?= $data['kode_buku']; ?>" required>

            <label>Judul:</label>
            <input type="text" name="judul" value="<?= $data['judul']; ?>" required>

            <label>Pengarang:</label>
            <input type="text" name="pengarang" value="<?= $data['pengarang']; ?>" required>

            <label>Tahun Terbit:</label>
            <input type="text" name="tahun_terbit" value="<?= $data['tahun_terbit']; ?>" required>

            <button type="submit" name="update">Update</button>
            <a href="data_buku.php">Batal</a>
        </form>
    </div>
</body>
</html>
