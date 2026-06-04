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
        body { 
            background: url('https://images.unsplash.com/photo-1586528116311-ad8dd3c8310d?q=80&w=2070') no-repeat center center fixed; 
            background-size: cover;
            display: flex; 
            justify-content: center; 
            align-items: center; 
            height: 100vh; 
        }
        /* Efek Glassmorphism Wadah Login */
        .login-container { 
            background: rgba(255, 255, 255, 0.75); 
            backdrop-filter: blur(15px); 
            -webkit-backdrop-filter: blur(15px);
            padding: 40px; 
            border-radius: 15px; 
            box-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.15); 
            width: 100%; 
            max-width: 400px; 
            border: 1px solid rgba(255, 255, 255, 0.3);
        }
        h2 { text-align: center; color: #1b5e20; font-weight: 700; letter-spacing: 1px; margin-bottom: 5px; }
        p.subtitle { text-align: center; color: #444; font-size: 13px; margin-bottom: 25px; font-weight: 400; }
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; margin-bottom: 8px; color: #222; font-weight: 600; font-size: 14px; }
        .form-group input { width: 100%; padding: 12px; border: 1px solid rgba(0,0,0,0.15); border-radius: 8px; font-size: 14px; outline: none; background: rgba(255,255,255,0.8); }
        .form-group input:focus { border-color: #1b5e20; background: #fff; }
        .btn-login { width: 100%; padding: 12px; background-color: #1b5e20; border: none; border-radius: 8px; color: white; font-size: 16px; font-weight: bold; cursor: pointer; transition: 0.3s; }
        .btn-login:hover { background-color: #2e7d32; box-shadow: 0 4px 12px rgba(27,94,32,0.3); }
        .alert { background-color: rgba(239, 68, 68, 0.2); color: #b91c1c; padding: 12px; border-radius: 8px; font-size: 14px; margin-bottom: 20px; text-align: center; border: 1px solid rgba(239, 68, 68, 0.3); backdrop-filter: blur(5px); }
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