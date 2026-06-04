<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit;
}
require_once 'koneksi.php';

$data_jenis = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) AS total_jenis FROM barang"));
$data_stok = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT SUM(stok) AS total_stok FROM barang"));
$total_stok_tampil = $data_stok['total_stok'] ?? 0;

// Ambil jumlah barang yang stoknya menipis
$data_menipis = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) AS stok_menipis FROM barang WHERE stok < 5"));

// Ambil DAFTAR NAMA BARANG yang stoknya menipis untuk fiture pengingat kritis
$list_kritis = mysqli_query($koneksi, "SELECT nama_barang, stok FROM barang WHERE stok < 5");

$riwayat_query = mysqli_query($koneksi, "SELECT r.*, u.nama_lengkap FROM riwayat_stok r LEFT JOIN users u ON r.id_user = u.id ORDER BY r.id DESC LIMIT 30");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Dashboard - INVENDOR</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Poppins', sans-serif; }
        body { 
            background: url('https://images.unsplash.com/photo-1586528116311-ad8dd3c8310d?q=80&w=2070') no-repeat center center fixed; 
            background-size: cover; display: flex; min-height: 100vh; 
        }
        .sidebar { 
            width: 260px; background: rgba(27, 94, 32, 0.75); backdrop-filter: blur(15px); -webkit-backdrop-filter: blur(15px);
            color: white; padding: 25px 20px; border-right: 1px solid rgba(255,255,255,0.1);
        }
        .sidebar h3 { text-align: center; margin-bottom: 30px; font-weight: 700; letter-spacing: 1px; }
        .sidebar p { font-size: 13px; background: rgba(255,255,255,0.15); padding: 10px; border-radius: 8px; margin-bottom: 25px; text-align: center; border: 1px solid rgba(255,255,255,0.1); }
        .sidebar a { display: block; color: #e2e8f0; padding: 12px; text-decoration: none; border-radius: 8px; margin-bottom: 10px; transition: 0.3s; }
        .sidebar a:hover, .sidebar a.active { background-color: rgba(255,255,255,0.2); color: white; font-weight: bold; }
        
        .main-content { flex-grow: 1; padding: 40px; background: rgba(244, 247, 246, 0.85); min-height: 100vh; overflow-y: auto; }
        .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; border-bottom: 2px solid rgba(0,0,0,0.05); padding-bottom: 15px; }
        
        /* --- TAHAP 2: STYLE BANNER PENGINGAT STOK KRITIS --- */
        .alert-danger-gudang {
            background: #ffeead; border-left: 6px solid #d84315; color: #c62828;
            padding: 15px 20px; border-radius: 8px; margin-bottom: 25px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.05);
        }
        .alert-danger-gudang h4 { font-size: 16px; margin-bottom: 5px; display: flex; align-items: center; gap: 8px; }
        .alert-danger-gudang ul { margin-left: 20px; font-size: 14px; font-weight: 500; }

        .analytics-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 20px; margin-bottom: 40px; }
        .card { background: white; padding: 25px; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.01); border-left: 5px solid #1b5e20; }
        .card.warning { border-left-color: #d84315; }
        .card h4 { color: #64748b; font-size: 13px; text-transform: uppercase; margin-bottom: 10px; }
        .card p { font-size: 28px; font-weight: 700; color: #1e293b; }
        
        .log-container { background: white; padding: 25px; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.01); }
        .log-container h3 { color: #1e293b; margin-bottom: 15px; font-size: 18px; }
        
        .table-wrapper { max-height: 350px; overflow-y: auto; border: 1px solid #e2e8f0; border-radius: 8px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #f1f5f9; font-size: 14px; transition: background-color 0.2s; }
        th { background-color: #f8fafc; color: #64748b; font-weight: 600; position: sticky; top: 0; z-index: 10; box-shadow: inset 0 -1px 0 #e2e8f0; }
        
        /* --- TAHAP 3: DESAIN UI/UX HOVER HIGHLIGHT --- */
        tbody tr:hover td { 
            background-color: rgba(46, 125, 50, 0.06); /* Warna hijau transparan lembut saat disorot */
            cursor: default;
        }

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
            <a href="admin.php">📦 Kelola Barang</a>
        <?php else : ?>
            <a href="staf.php">👁️ Lihat Stok Barang</a>
        <?php endif; ?>
        <a href="logout.php" style="background-color: rgba(198, 40, 40, 0.85); text-align: center; margin-top: 25px; font-weight: bold;">🚪 Keluar</a>
    </div>
    
    <div class="main-content">
        <div class="header">
            <div>
        <h2>Dashboard Analitik</h2>
        <span style="font-size: 14px; color: #475569;">Halo, <strong><?= $_SESSION['nama_lengkap']; ?></strong>!</span>
    </div>
    <!-- TOMBOL CETAK LAPORAN BARU -->
    <a href="cetak.php" target="_blank" style="background-color: #e65100; color: white; text-decoration: none; padding: 10px 18px; border-radius: 6px; font-weight: bold; font-size: 14px; display: flex; align-items: center; gap: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); transition: 0.2s;">
        🖨️ Cetak Laporan PDF
    </a>
</div>

        <!-- TAHAP 2: NOTIFIKASI PENGINGAT STOK KRITIS -->
        <?php if ($data_menipis['stok_menipis'] > 0): ?>
            <div class="alert-danger-gudang">
                <h4>⚠️ PENGINGAT SISTEM: Stok Logistik Kritis!</h4>
                <p style="font-size: 13px; margin-bottom: 5px; color: #475569;">Barang-barang di bawah ini memiliki stok kurang dari 5 pcs dan membutuhkan order ulang secepatnya:</p>
                <ul>
                    <?php while($kritis = mysqli_fetch_assoc($list_kritis)): ?>
                        <li><strong><?= $kritis['nama_barang']; ?></strong> (Sisa stok: <span style="text-decoration: underline; font-weight: bold;"><?= $kritis['stok']; ?> Pcs</span>)</li>
                    <?php endwhile; ?>
                </ul>
            </div>
        <?php endif; ?>

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
        
        <div class="log-container">
            <h3>📋 Jurnal Mutasi Stok & Riwayat Aktivitas Sistem</h3>
            <div class="table-wrapper" style="margin-top: 15px;">
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
    </div>
</body>
</html>