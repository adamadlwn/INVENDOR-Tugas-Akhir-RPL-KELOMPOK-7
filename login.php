<?php
session_start();
if (isset($_SESSION['username'])) {
    header("Location: index.php");
    exit;
}
require_once 'koneksi.php';
$error = '';

if (isset($_POST['login'])) {
    $username = mysqli_real_escape_string($koneksi, $_POST['username']);
    $password = $_POST['password'];

    $query  = "SELECT * FROM users WHERE username = '$username'";
    $result = mysqli_query($koneksi, $query);

    if (mysqli_num_rows($result) === 1) {
        $row = mysqli_fetch_assoc($result);
        if ($password === $row['password']) {
            $_SESSION['id_user']      = $row['id'];
            $_SESSION['username']     = $row['username'];
            $_SESSION['nama_lengkap']  = $row['nama_lengkap'];
            $_SESSION['role']         = $row['role'];
            header("Location: index.php");
            exit;
        } else { $error = "Password salah!"; }
    } else { $error = "Username tidak ditemukan!"; }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Login - INVENDOR</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Poppins', sans-serif; }
        body { background-color: #f4f7f6; display: flex; justify-content: center; align-items: center; height: 100vh; }
        .login-container { background: #ffffff; padding: 40px; border-radius: 10px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); width: 100%; max-width: 400px; border-top: 5px solid #2e7d32; }
        h2 { text-align: center; color: #1b5e20; font-weight: 700; letter-spacing: 1px; margin-bottom: 5px; }
        p.subtitle { text-align: center; color: #777; font-size: 13px; margin-bottom: 25px; }
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; margin-bottom: 8px; color: #555; font-weight: 600; font-size: 14px; }
        .form-group input { width: 100%; padding: 12px; border: 1px solid #ccc; border-radius: 6px; font-size: 14px; outline: none; }
        .form-group input:focus { border-color: #2e7d32; }
        .btn-login { width: 100%; padding: 12px; background-color: #2e7d32; border: none; border-radius: 6px; color: white; font-size: 16px; font-weight: bold; cursor: pointer; }
        .btn-login:hover { background-color: #1b5e20; }
        .alert { background-color: #ffebee; color: #c62828; padding: 12px; border-radius: 6px; font-size: 14px; margin-bottom: 20px; text-align: center; border: 1px solid #ffcdd2; }
    </style>
</head>
<body>
<div class="login-container">
    <h2>INVENDOR</h2>
    <p class="subtitle">Sistem Manajemen Inventaris UMKM</p>
    <?php if (!empty($error)) : ?><div class="alert"><?= $error; ?></div><?php endif; ?>
    <form action="" method="POST">
        <div class="form-group">
            <label>Username</label>
            <input type="text" name="username" placeholder="Masukkan username" required autocomplete="off">
        </div>
        <div class="form-group">
            <label>Password</label>
            <input type="password" name="password" placeholder="Masukkan password" required>
        </div>
        <button type="submit" name="login" class="btn-login">Masuk ke Sistem</button>
    </form>
</div>
</body>
</html>