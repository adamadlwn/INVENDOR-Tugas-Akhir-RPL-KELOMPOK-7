<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'staf') {
    header("Location: index.php");
    exit;
}
require_once 'koneksi.php';

$search = '';
if (isset($_GET['cari'])) {
    $search = mysqli_real_escape_string($koneksi, $_GET['keyword']);
    $query = "SELECT b.*, k.nama_kategori FROM barang b LEFT JOIN kategori k ON b.id_kategori = k.id WHERE b.nama_barang LIKE '%$search%' OR b.kode_barang LIKE '%$search%' OR k.nama_kategori LIKE '%$search%' ORDER BY b.id DESC";
} else {
    $query = "SELECT b.*, k.nama_kategori FROM barang b LEFT JOIN kategori k ON b.id_kategori = k.id ORDER BY b.id DESC";
}
$daftar_barang = mysqli_query($koneksi, $query);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Daftar Stok - INVENDOR</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Poppins', sans-serif; }
        body { 
            background: url('https://images.unsplash.com/photo-1586528116311-ad8dd3c8310d?q=80&w=2070') no-repeat center center fixed; 
            background-size: cover;
            display: flex; 
            min-height: 100vh; 
        }
        .sidebar { 
            width: 260px; 
            background: rgba(27, 94, 32, 0.75); 
            backdrop-filter: blur(15px);
            -webkit-backdrop-filter: blur(15px);
            color: white; 
            padding: 25px 20px; 
            border-right: 1px solid rgba(255,255,255,0.1);
        }
        .sidebar h3 { text-align: center; margin-bottom: 30px; font-weight: 700; }
        .sidebar a { display: block; color: #cbd5e1; padding: 12px; text-decoration: none; border-radius: 8px; margin-bottom: 10px; transition: 0.3s; }
        .sidebar a:hover, .sidebar a.active { background-color: rgba(255,255,255,0.2); color: white; font-weight: bold; }
        
        .main-content { flex-grow: 1; padding: 40px; background: rgba(244, 247, 246, 0.85); min-height: 100vh; overflow-y: auto; }
        .header { display: flex; justify-content: space-between; margin-bottom: 30px; border-bottom: 2px solid rgba(0,0,0,0.05); padding-bottom: 15px; }
        .search-container { margin-bottom: 20px; }
        .search-container input { padding: 8px; width: 300px; border: 1px solid #ccc; border-radius: 6px; font-size: 14px; background: white; }
        .btn-cari { padding: 8px 15px; background-color: #2e7d32; color: white; border: none; border-radius: 6px; cursor: pointer; font-weight: bold; }
        
        /* --- KOTAK SCROLL KHUSUS TABEL DATA STAF --- */
        .table-wrapper {
            max-height: 450px;
            overflow-y: auto;
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.01);
            border: 1px solid #e2e8f0;
        }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 12px 15px; text-align: left; border-bottom: 1px solid #e2e8f0; font-size: 14px; }
        th { 
            background-color: #2e7d32; 
            color: white; 
            position: sticky;
            top: 0;
            z-index: 10;
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <h3>INVENDOR</h3>
        <a href="index.php">🏠 Dashboard</a>
        <a href="staf.php" class="active">👁️ Lihat Stok Barang</a>
        <a href="logout.php" style="background-color: rgba(198, 40, 40, 0.85); text-align:center; margin-top: 25px; font-weight: bold;">🚪 Keluar</a>
    </div>
    <div class="main-content">
        <div class="header"><h2>Daftar Informasi Stok (Staf)</h2></div>
        <div class="search-container">
            <form action="staf.php" method="GET">
                <input type="text" name="keyword" placeholder="Cari nama / kode / kategori..." value="<?= htmlspecialchars($search); ?>" required>
                <button type="submit" name="cari" class="btn-cari">Cari</button>
                <?php if($search != ''): ?> <a href="staf.php" style="margin-left: 10px; color: #555;">Reset</a> <?php endif; ?>
            </form>
        </div>
        <div class="table-wrapper">
            <table>
                <thead><tr><th>Kode</th><th>Nama Barang</th><th>Kategori</th><th>Status Stok</th><th>Harga</th></tr></thead>
                <tbody>
                    <?php if(mysqli_num_rows($daftar_barang) > 0): ?>
                        <?php while ($row = mysqli_fetch_assoc($daftar_barang)): ?>
                        <tr>
                            <td><strong><?= $row['kode_barang']; ?></strong></td><td><?= $row['nama_barang']; ?></td><td><?= $row['nama_kategori'] ?? 'Tanpa Kategori'; ?></td>
                            <td>
                                <?php if($row['stok'] < 5): ?>
                                    <span style="color: #c62828; font-weight: bold;">⚠️ Sisa <?= $row['stok']; ?> Pcs (Kritis)</span>
                                <?php else: ?>
                                    <span style="color: #2e7d32;">✔️ Tersedia (<?= $row['stok']; ?> Pcs)</span>
                                <?php endif; ?>
                            </td>
                            <td>Rp <?= number_format($row['harga'], 0, ',', '.'); ?></td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="5" style="text-align: center; color: #777;">Data tidak ditemukan.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>