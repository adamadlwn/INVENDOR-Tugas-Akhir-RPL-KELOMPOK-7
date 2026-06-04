<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit;
}
require_once 'koneksi.php';

// Analitik Angka
$data_jenis = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) AS total_jenis FROM barang"));
$data_stok = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT SUM(stok) AS total_stok FROM barang"));
$total_stok_tampil = $data_stok['total_stok'] ?? 0;
$data_menipis = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) AS stok_menipis FROM barang WHERE stok < 5"));

// Ambil Seluruh Log Aktivitas Terakhir (Berfungsi sebagai Tabel Rekening Koran/Mutasi Stok untuk Pemilik Toko)
$riwayat_query = mysqli_query($koneksi, "SELECT r.*, u.nama_lengkap FROM riwayat_stok r LEFT JOIN users u ON r.id_user = u.id ORDER BY r.id DESC LIMIT 10");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Dashboard - INVENDOR</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Poppins', sans-serif; }
        body { background-color: #f4f7f6; display: flex; min-height: 100vh; }
        .sidebar { width: 250px; background-color: #1b5e20; color: white; padding: 20px; }
        .sidebar h3 { text-align: center; margin-bottom: 30px; font-weight: 700; letter-spacing: 1px; }
        .sidebar p { font-size: 13px; background: #2e7d32; padding: 10px; border-radius: 5px; margin-bottom: 20px; text-align: center; }
        .sidebar a { display: block; color: #cbd5e1; padding: 12px; text-decoration: none; border-radius: 5px; margin-bottom: 10px; }
        .sidebar a:hover, .sidebar a.active { background-color: #2e7d32; color: white; font-weight: bold; }
        .main-content { flex-grow: 1; padding: 40px; }
        .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; border-bottom: 2px solid #e2e8f0; padding-bottom: 15px; }
        .analytics-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 20px; margin-bottom: 40px; }
        .card { background: white; padding: 25px; border-radius: 10px; box-shadow: 0 2px 5px rgba(0,0,0,0.02); border-left: 5px solid #2e7d32; }
        .card.warning { border-left-color: #d84315; }
        .card h4 { color: #64748b; font-size: 13px; text-transform: uppercase; margin-bottom: 10px; }
        .card p { font-size: 28px; font-weight: 700; color: #1e293b; }
        
        /* Gaya Desain Tabel Log Baru */
        .log-container { background: white; padding: 25px; border-radius: 10px; box-shadow: 0 2px 5px rgba(0,0,0,0.02); }
        .log-container h3 { color: #1e293b; margin-bottom: 15px; font-size: 18px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #f1f5f9; font-size: 14px; }
        th { background-color: #f8fafc; color: #64748b; font-weight: 600; }
        .badge { padding: 4px 8px; border-radius: 4px; font-size: 11px; font-weight: bold; color: white; text-transform: uppercase; }
        .bg-masuk { background-color: #2e7d32; }
        .bg-edit { background-color: #0284c7; }
        .bg-hapus { background-color: #c62828; }
    </style>
</head>
<body>
    <div class="sidebar">
        <h3>INVENDOR</h3>
        <p>LOGIN: <strong><?= strtoupper($_SESSION['role']); ?></strong></p>
        <a href="index.php" class="active">🏠 Dashboard</a>
        <?php if ($_SESSION['role'] === 'admin') : ?>
            <a href="admin.php">📦 Kelola Barang (CRUD)</a>
        <?php else : ?>
            <a href="staf.php">👁️ Lihat Stok Barang</a>
        <?php endif; ?>
        <a href="logout.php" style="background-color: #c62828; text-align: center; margin-top: 50px;">🚪 Keluar</a>
    </div>
    <div class="main-content">
        <div class="header">
            <h2>Dashboard Analitik</h2>
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
        
        <!-- FITUR UTAMA TABEL MUTASI / AUDIT LOG UNTUK PEMILIK WEB -->
        <div class="log-container">
            <h3>📋 Jurnal Mutasi Stok & Riwayat Aktivitas Sistem</h3>
            <table>
                <thead>
                    <tr>
                        <th>Waktu Kejadian</th>
                        <th>Aktor (User)</th>
                        <th>Aktivitas</th>
                        <th>Detail Keterangan Riwayat</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(mysqli_num_rows($riwayat_query) > 0): ?>
                        <?php while($log = mysqli_fetch_assoc($riwayat_query)): 
                            // Pilih warna badge berdasarkan tipe aktivitas
                            $badge_class = 'bg-edit';
                            if($log['jenis_perubahan'] === 'BARANG MASUK') $badge_class = 'bg-masuk';
                            if($log['jenis_perubahan'] === 'BARANG DIHAPUS') $badge_class = 'bg-hapus';
                        ?>
                            <tr>
                                <td style="color: #94a3b8; font-size: 13px;"><?= $log['tanggal']; ?></td>
                                <td><strong><?= $log['nama_lengkap']; ?></strong></td>
                                <td><span class="badge <?= $badge_class; ?>"><?= $log['jenis_perubahan']; ?></span></td>
                                <td style="color: #475569;"><?= $log['keterangan']; ?></td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="4" style="text-align: center; color: #aaa;">Belum ada riwayat aktivitas.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>