<?php
session_start();
include 'koneksi.php';

// Jika sudah login, redirect sesuai role
if (isset($_SESSION['role'])) {
    if ($_SESSION['role'] === 'kasir') {
        header("Location: dashboard_kasir.php"); exit();
    } elseif ($_SESSION['role'] === 'customer') {
        header("Location: dashboard_pelanggan.php"); exit();
    } elseif ($_SESSION['role'] === 'admin') {
        header("Location: dashboard_admin.php"); exit();
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $email    = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password'];
    $role     = $_POST['role'];

    // Validasi role
    if (!in_array($role, ['customer','kasir','admin'])) {
        $_SESSION['msg'] = "Role tidak valid";
        header("Location: login.php"); exit();
    }

    $query = "SELECT * FROM users WHERE email='$email' AND role='$role' LIMIT 1";
    $result = mysqli_query($conn, $query);

    if ($result && mysqli_num_rows($result) === 1) {

        $user = mysqli_fetch_assoc($result);

        // Cek password
        if (!password_verify($password, $user['password'])) {
            $_SESSION['msg'] = "Email atau password salah";
            header("Location: login.php"); exit();
        }

        // Customer wajib verifikasi
        if ($role === 'customer' && strtolower($user['status']) !== 'verified') {
            $_SESSION['msg'] = "Akun customer belum diverifikasi. Cek email.";
            header("Location: login.php"); exit();
        }

        // Set session
        $_SESSION['user_id']  = $user['id_user'] ?? $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role']     = $user['role'];

        // Redirect sesuai role
        if ($role === 'kasir') {
            header("Location: dashboard_kasir.php"); exit();
        } elseif ($role === 'customer') {
            header("Location: dashboard_pelanggan.php"); exit();
        } elseif ($role === 'admin') {
            header("Location: dashboard_admin.php"); exit();
        }

    } else {
        $_SESSION['msg'] = "Email atau role tidak sesuai";
        header("Location: login.php"); exit();
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Laundry Uci - Login</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">

<style>
:root {
    --primary: #00aaff;
    --secondary: #00ddc0;
    --bg1: #0a0f3d;
    --bg2: #1a237e;
}

body {
    margin: 0;
    min-height: 100vh;
    display: flex;
    justify-content: center;
    align-items: center;
    background: linear-gradient(135deg, var(--bg1), var(--bg2));
    font-family: 'Poppins', sans-serif;
}

.login-container {
    width: 380px;
    padding: 40px;
    background: rgba(255,255,255,0.12);
    border-radius: 20px;
    backdrop-filter: blur(15px);
    color: white;
}

.logo {
    font-size: 32px;
    font-weight: 700;
    color: var(--primary);
}
.logo span { color: var(--secondary); }

.subtitle {
    font-size: 14px;
    margin-bottom: 25px;
    font-weight: 600;
}

.msg-box {
    background: rgba(255,0,0,0.25);
    padding: 10px;
    border-radius: 10px;
    margin-bottom: 20px;
}

input {
    width: 100%;
    padding: 14px;
    border-radius: 12px;
    border: none;
    margin-bottom: 20px;
    background: rgba(255,255,255,0.2);
    color: white;
}

.role-box {
    display: flex;
    gap: 10px;
    margin-bottom: 20px;
}

.role-option {
    flex: 1;
    padding: 12px;
    border-radius: 12px;
    background: rgba(255,255,255,0.15);
    text-align: center;
    cursor: pointer;
    font-size: 13px;
    font-weight: 600;
}

.role-option input { display: none; }

.btn-login {
    width: 100%;
    padding: 14px;
    border-radius: 12px;
    border: none;
    font-size: 18px;
    font-weight: 700;
    color: white;
    cursor: pointer;
    background: linear-gradient(45deg, var(--primary), var(--secondary));
}

.register-btn {
    margin-top: 15px;
    padding: 14px;
    width: 100%;
    border-radius: 12px;
    font-size: 16px;
    font-weight: 700;
    cursor: pointer;
    text-align: center;
    color: white;
    text-decoration: none;
    display: block;
    background: rgba(255,255,255,0.15);
}
</style>
</head>

<body>

<div class="login-container">

    <div class="logo">Laundry<span>Uci</span></div>
    <div class="subtitle">Login sesuai peran Anda</div>

    <?php
    if (isset($_SESSION['msg'])) {
        echo '<div class="msg-box">'.$_SESSION['msg'].'</div>';
        unset($_SESSION['msg']);
    }
    ?>

    <form method="POST">

        <input type="email" name="email" placeholder="Email" required>
        <input type="password" name="password" placeholder="Password" required>

        <div class="role-box">
            <label class="role-option">
                <input type="radio" name="role" value="customer" required>Customer
            </label>
            <label class="role-option">
                <input type="radio" name="role" value="kasir" required>Kasir
            </label>
            <label class="role-option">
                <input type="radio" name="role" value="admin" required>Admin
            </label>
        </div>

        <button class="btn-login" type="submit">LOGIN</button>

        <a href="register.php" class="register-btn">DAFTAR AKUN BARU</a>

    </form>

</div>

</body>
</html>
