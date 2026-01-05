<?php
session_start();
include 'koneksi.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

// PHPMailer
require __DIR__ . '/vendor/src/Exception.php';
require __DIR__ . '/vendor/src/PHPMailer.php';
require __DIR__ . '/vendor/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $username = trim($_POST['username']);
    $email = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL);
    $password_raw = $_POST['password'];
    $role = $_POST['role'];
    $alamat = trim($_POST['alamat']);

    if (!$email) {
        echo "<script>alert('Format email tidak valid'); window.location='register.php';</script>";
        exit;
    }

    if (strlen($password_raw) < 6) {
        echo "<script>alert('Password minimal 6 karakter'); window.location='register.php';</script>";
        exit;
    }

    $status = ($role === 'customer') ? 'pending' : 'active';
    $token  = ($role === 'customer') ? bin2hex(random_bytes(32)) : null;
    $password_hashed = password_hash($password_raw, PASSWORD_DEFAULT);

    // Cek email
    $stmt = mysqli_prepare($conn, "SELECT user_id FROM users WHERE email=?");
    mysqli_stmt_bind_param($stmt, "s", $email);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_store_result($stmt);

    if (mysqli_stmt_num_rows($stmt) > 0) {
        echo "<script>alert('Email sudah terdaftar'); window.location='register.php';</script>";
        exit;
    }
    mysqli_stmt_close($stmt);

    // Insert user
    $stmt = mysqli_prepare($conn,
        "INSERT INTO users (username,email,password,role,alamat,verification_token,status)
         VALUES (?,?,?,?,?,?,?)"
    );
    mysqli_stmt_bind_param($stmt, "sssssss",
        $username, $email, $password_hashed, $role, $alamat, $token, $status
    );

    if (mysqli_stmt_execute($stmt)) {

        if ($role === 'customer') {

            $mail = new PHPMailer(true);

            try {
                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com';
                $mail->SMTPAuth = true;
                $mail->Username = "andinurfauzi2102@gmail.com";
                $mail->Password = "jzbvgyezbdhnarjj";
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port = 587;

                $mail->setFrom("smartlaundry.customer@gmail.com", "Smart Laundry");
                $mail->addAddress($email, $username);

                $verifyLink = "http://localhost/FAUZI/verify.php?email="
                    . urlencode($email) . "&token=" . urlencode($token);

                $mail->isHTML(true);
                $mail->Subject = "Verifikasi Akun Smart Laundry";
                $mail->Body = "
                    <h3>Halo $username</h3>
                    <p>Silakan klik link berikut untuk verifikasi akun Anda:</p>
                    <a href='$verifyLink'>VERIFIKASI AKUN</a>
                    <p>Terima kasih</p>
                ";

                $mail->send();

                echo "<script>alert('Registrasi berhasil! Cek email untuk verifikasi'); window.location='login.php';</script>";
                exit;

            } catch (Exception $e) {
                mysqli_query($conn, "DELETE FROM users WHERE email='$email'");
                echo "<script>alert('Gagal mengirim email'); window.location='register.php';</script>";
                exit;
            }

        } else {
            echo "<script>alert('Registrasi berhasil!'); window.location='login.php';</script>";
            exit;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Registrasi Smart Laundry</title>

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">

<style>
body {
    margin: 0;
    height: 100vh;
    display: flex;
    justify-content: center;
    align-items: center;
    background: linear-gradient(130deg, #6a11cb, #2575fc);
    font-family: 'Poppins', sans-serif;
}

.card {
    width: 400px;
    padding: 30px;
    border-radius: 20px;
    background: rgba(255,255,255,0.2);
    backdrop-filter: blur(12px);
    box-shadow: 0 8px 30px rgba(0,0,0,0.2);
    text-align: center;
    color: white;
}

input, textarea, select {
    width: 100%;
    padding: 12px;
    margin: 10px 0;
    border-radius: 10px;
    border: none;
}

button {
    width: 100%;
    padding: 14px;
    border-radius: 10px;
    border: none;
    background: white;
    color: #2575fc;
    font-weight: 600;
    cursor: pointer;
}

.login-link {
    margin-top: 15px;
    font-size: 14px;
}

.login-link a {
    color: white;
    font-weight: 600;
}
</style>
</head>

<body>

<div class="card">

    <!-- GIF ANIMASI -->
    <img
      src="https://media.giphy.com/media/xUPGcguWZHRC2HyBRS/giphy.gif"
      alt="Register"
      style="width:160px;height:160px;margin:auto;display:block;"
    >

    <h2>Registrasi Smart Laundry</h2>
    <p>Akun customer perlu verifikasi email</p>

    <form method="POST">
        <input type="text" name="username" placeholder="Nama Anda" required>
        <input type="email" name="email" placeholder="Email Anda" required>
        <input type="password" name="password" placeholder="Password" required>
        <textarea name="alamat" placeholder="Alamat lengkap"></textarea>

        <select name="role" required>
            <option value="" disabled selected>Pilih Peran</option>
            <option value="customer">Customer</option>
            <option value="kasir">Kasir</option>
            <option value="admin">Admin</option>
        </select>

        <button type="submit">Daftar</button>
    </form>

    <div class="login-link">
        Sudah punya akun? <a href="login.php">Login</a>
    </div>

</div>

</body>
</html>
