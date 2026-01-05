<?php
include 'koneksi.php';

$email = isset($_GET['email']) ? $_GET['email'] : '';
$token = isset($_GET['token']) ? $_GET['token'] : '';
$message = "";
$success = false;

if (empty($email) || empty($token)) {
    $message = "❌ Tautan verifikasi tidak lengkap.";
} else {
    $query = "SELECT * FROM users WHERE email='$email' AND verification_token='$token' AND status='pending'";
    $result = mysqli_query($conn, $query);

    if (mysqli_num_rows($result) > 0) {
        $update = "UPDATE users SET status='verified', verification_token=NULL WHERE email='$email'";
        if (mysqli_query($conn, $update)) {
            $message = "✅ Verifikasi berhasil! Akun Anda sudah aktif.";
            $success = true;
        } else {
            $message = "Terjadi kesalahan saat memperbarui data.";
        }
    } else {
        $message = "Verifikasi gagal atau tautan sudah kadaluarsa.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Verifikasi Akun - Smart Laundry</title>
    <style>
        body { font-family:'Poppins',sans-serif; background:linear-gradient(135deg,#74ebd5,#ACB6E5);
            display:flex; justify-content:center; align-items:center; height:100vh; margin:0; }
        .container { background:#fff; padding:35px; border-radius:15px; width:400px; text-align:center; box-shadow:0 10px 25px rgba(0,0,0,0.2); }
        .success { color:#28a745; font-weight:bold; }
        .error { color:#dc3545; font-weight:bold; }
        a { display:inline-block; margin-top:15px; background:#3a8dde; color:white; padding:10px 20px; border-radius:8px; text-decoration:none; }
        a:hover { background:#6ab7ff; }
    </style>
</head>
<body>
<div class="container">
    <h2>Status Verifikasi Akun</h2>
    <p class="<?php echo $success ? 'success' : 'error'; ?>"><?php echo $message; ?></p>
    <a href="login.php">Lanjut ke Login</a>
</div>
</body>
</html>
