<?php
session_start();
include 'koneksi.php';
if (!isset($_SESSION['username'])) { header("Location: login.php"); exit(); }

if (isset($_POST['tambah'])) {
    mysqli_query($conn, "INSERT INTO buku (kode_buku, judul, pengarang, tahun_terbit) VALUES 
    ('$_POST[kode_buku]', '$_POST[judul]', '$_POST[pengarang]', '$_POST[tahun_terbit]')");
    header("Location: data_buku.php");
}

if (isset($_POST['edit'])) {
    mysqli_query($conn, "UPDATE buku SET kode_buku='$_POST[kode_buku]', judul='$_POST[judul]', 
    pengarang='$_POST[pengarang]', tahun_terbit='$_POST[tahun_terbit]' WHERE id='$_POST[id]'");
    header("Location: data_buku.php");
}

if (isset($_GET['hapus'])) {
    mysqli_query($conn, "DELETE FROM buku WHERE id='$_GET[hapus]'");
    header("Location: data_buku.php");
}
?>
<!DOCTYPE html>
<html>
<head><meta charset="UTF-8"><title>Data Buku</title></head>
<body>
<?php include 'sidebar.php'; ?>

<div class="main-content">
    <h2>📚 Data Buku</h2>

    <form method="POST">
        <input type="text" name="kode_buku" placeholder="Kode" required>
        <input type="text" name="judul" placeholder="Judul" required>
        <input type="text" name="pengarang" placeholder="Pengarang" required>
        <input type="text" name="tahun_terbit" placeholder="Tahun" required>
        <button name="tambah">Tambah</button>
    </form><br>

    <table border="1" cellpadding="5">
        <tr><th>No</th><th>Kode</th><th>Judul</th><th>Pengarang</th><th>Tahun</th><th>Aksi</th></tr>
        <?php
        $no = 1;
        $data = mysqli_query($conn, "SELECT * FROM buku");
        while ($b = mysqli_fetch_assoc($data)) {
        ?>
        <tr>
            <td><?= $no++; ?></td>
            <td><?= $b['kode_buku']; ?></td>
            <td><?= $b['judul']; ?></td>
            <td><?= $b['pengarang']; ?></td>
            <td><?= $b['tahun_terbit']; ?></td>
            <td>
                <form method="POST" style="display:inline;">
                    <input type="hidden" name="id" value="<?= $b['id']; ?>">
                    <input type="text" name="kode_buku" value="<?= $b['kode_buku']; ?>">
                    <input type="text" name="judul" value="<?= $b['judul']; ?>">
                    <input type="text" name="pengarang" value="<?= $b['pengarang']; ?>">
                    <input type="text" name="tahun_terbit" value="<?= $b['tahun_terbit']; ?>">
                    <button name="edit">Edit</button>
                </form>
                <a href="?hapus=<?= $b['id']; ?>" onclick="return confirm('Hapus data ini?')">Hapus</a>
            </td>
        </tr>
        <?php } ?>
    </table>
</div>
</body>
</html>
