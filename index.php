<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit;
}
require_once 'koneksi.php';

// 1. Hitung Total Jenis Barang
$query_jenis = "SELECT COUNT(*) AS total_jenis FROM barang";
$result_jenis = mysqli_query($koneksi, $query_jenis);
$data_jenis = mysqli_fetch_assoc($result_jenis);

// 2. Hitung Total Seluruh Stok Barang
$query_stok = "SELECT SUM(stok) AS total_stok FROM barang";
$result_stok = mysqli_query($koneksi, $query_stok);
$data_stok = mysqli_fetch_assoc($result_stok);
$total_stok_tampil = $data_stok['total_stok'] ?? 0;

// 3. Peringatan Stok Menipis (< 5)
$query_menipis = "SELECT COUNT(*) AS stok_menipis FROM barang WHERE stok < 5";
$result_menipis = mysqli_query($koneksi, $query_menipis);
$data_menipis = mysqli_fetch_assoc($result_menipis);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Dashboard - Invendor</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Segoe UI', sans-serif; }
        body { background-color: #f4f7f6; display: flex; min-height: 100vh; }
        .sidebar { width: 250px; background-color: #1b5e20; color: white; padding: 20px; }
        .sidebar h3 { text-align: center; margin-bottom: 30px; font-size: 24px; }
        .sidebar p { font-size: 14px; background: #2e7d32; padding: 10px; border-radius: 5px; margin-bottom: 20px; text-align: center; }
        .sidebar a { display: block; color: #cbd5e1; padding: 12px; text-decoration: none; border-radius: 5px; margin-bottom: 10px; }
        .sidebar a:hover, .sidebar a.active { background-color: #2e7d32; color: white; font-weight: bold; }
        .main-content { flex-grow: 1; padding: 40px; }
        .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; border-bottom: 2px solid #e2e8f0; padding-bottom: 15px; }
        .analytics-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 20px; margin-bottom: 40px; }
        .card { background: white; padding: 25px; border-radius: 10px; box-shadow: 0 2px 5px rgba(0,0,0,0.02); border-left: 5px solid #2e7d32; }
        .card.warning { border-left-color: #d84315; }
        .card h4 { color: #64748b; font-size: 14px; text-transform: uppercase; margin-bottom: 10px; }
        .card p { font-size: 32px; font-weight: bold; color: #1e293b; }
        .welcome-box { background: white; padding: 30px; border-radius: 10px; }
        .btn-action { display: inline-block; margin-top: 15px; padding: 10px 20px; background-color: #2e7d32; color: white; text-decoration: none; border-radius: 5px; font-weight: bold; }
    </style>
</head>
<body>
    <div class="sidebar">
        <h3>Invendor</h3>
        <p>Login: <strong><?= strtoupper($_SESSION['role']); ?></strong></p>
        <a href="index.php" class="active">🏠 Dashboard</a>
        <?php if ($_SESSION['role'] === 'admin') : ?>
            <a href="admin.php">📦 Kelola Barang</a>
        <?php else : ?>
            <a href="staf.php">👁️ Lihat Stok Barang</a>
        <?php endif; ?>
        <a href="logout.php" style="background-color: #c62828; text-align: center; margin-top: 50px;">🚪 Keluar</a>
    </div>
    <div class="main-content">
        <div class="header">
            <h1>Dashboard Analitik</h1>
            <span>Halo, <strong><?= $_SESSION['nama_lengkap']; ?></strong>!</span>
        </div>
        <div class="analytics-grid">
            <div class="card">
                <h4>Total Jenis Barang</h4>
                <p><?= $data_jenis['total_jenis']; ?> SKU</p>
            </div>
            <div class="card">
                <h4>Total Stok Gabungan</h4>
                <p><?= $total_stok_tampil; ?> Pcs</p>
            </div>
            <div class="card <?= ($data_menipis['stok_menipis'] > 0) ? 'warning' : ''; ?>">
                <h4>Stok Menipis (&lt; 5)</h4>
                <p style="color: <?= ($data_menipis['stok_menipis'] > 0) ? '#c62828' : '#1e293b'; ?>"><?= $data_menipis['stok_menipis']; ?> Item</p>
            </div>
        </div>
        <div class="welcome-box">
            <h2>Selamat Datang di Sistem Invendor</h2>
            <p>Sistem analitik inventaris toko retail otomatis untuk efisiensi bisnis UMKM.</p>
            <?php if ($_SESSION['role'] === 'admin') : ?>
                <a href="admin.php" class="btn-action">Mulai Kelola Data &raquo;</a>
            <?php else : ?>
                <a href="staf.php" class="btn-action">Lihat Daftar Barang &raquo;</a>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>