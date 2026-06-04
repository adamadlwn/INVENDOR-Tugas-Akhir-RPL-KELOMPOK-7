<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit;
}
require_once 'koneksi.php';

$id_edit = ''; $kode = ''; $nama = ''; $kategori = ''; $stok = ''; $harga = '';
$is_edit = false;

if (isset($_POST['simpan'])) {
    $kode = mysqli_real_escape_string($koneksi, $_POST['kode_barang']);
    $nama = mysqli_real_escape_string($koneksi, $_POST['nama_barang']);
    $kategori = mysqli_real_escape_string($koneksi, $_POST['kategori']);
    $stok = (int)$_POST['stok'];
    $harga = (float)$_POST['harga'];

    if ($_POST['id_edit'] != '') {
        $id = $_POST['id_edit'];
        $query = "UPDATE barang SET kode_barang='$kode', nama_barang='$nama', kategori='$kategori', stok=$stok, harga=$harga WHERE id=$id";
    } else {
        $query = "INSERT INTO barang (kode_barang, nama_barang, kategori, stok, harga) VALUES ('$kode', '$nama', '$kategori', $stok, $harga)";
    }
    mysqli_query($koneksi, $query);
    header("Location: admin.php");
    exit;
}

if (isset($_GET['action']) && $_GET['action'] == 'edit') {
    $id = $_GET['id'];
    $result = mysqli_query($koneksi, "SELECT * FROM barang WHERE id=$id");
    if ($row = mysqli_fetch_assoc($result)) {
        $id_edit = $row['id']; $kode = $row['kode_barang']; $nama = $row['nama_barang']; $kategori = $row['kategori']; $stok = $row['stok']; $harga = $row['harga'];
        $is_edit = true;
    }
}

if (isset($_GET['action']) && $_GET['action'] == 'delete') {
    $id = $_GET['id'];
    mysqli_query($koneksi, "DELETE FROM barang WHERE id=$id");
    header("Location: admin.php");
    exit;
}

$daftar_barang = mysqli_query($koneksi, "SELECT * FROM barang ORDER BY id DESC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Kelola Barang - Invendor</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Segoe UI', sans-serif; }
        body { background-color: #f4f7f6; display: flex; min-height: 100vh; }
        .sidebar { width: 250px; background-color: #1b5e20; color: white; padding: 20px; }
        .sidebar h3 { text-align: center; margin-bottom: 30px; }
        .sidebar a { display: block; color: #cbd5e1; padding: 12px; text-decoration: none; border-radius: 5px; margin-bottom: 10px; }
        .sidebar a:hover, .sidebar a.active { background-color: #2e7d32; color: white; font-weight: bold; }
        .main-content { flex-grow: 1; padding: 40px; }
        .header { display: flex; justify-content: space-between; margin-bottom: 30px; border-bottom: 2px solid #e2e8f0; padding-bottom: 15px; }
        .form-container { background: white; padding: 20px; border-radius: 8px; margin-bottom: 30px; }
        .form-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 15px; margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; font-size: 14px; font-weight: 600; }
        .form-group input { width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px; }
        .btn { padding: 10px 20px; border: none; border-radius: 4px; color: white; font-weight: bold; cursor: pointer; }
        .btn-success { background-color: #2e7d32; }
        .btn-danger { background-color: #c62828; text-decoration: none; padding: 5px 10px; font-size: 12px; border-radius: 3px; }
        .btn-edit { background-color: #0284c7; color: white; text-decoration: none; padding: 5px 10px; font-size: 12px; border-radius: 3px; margin-right: 5px; }
        table { width: 100%; border-collapse: collapse; background: white; border-radius: 8px; overflow: hidden; }
        th, td { padding: 12px 15px; text-align: left; border-bottom: 1px solid #e2e8f0; }
        th { background-color: #2e7d32; color: white; }
    </style>
</head>
<body>
    <div class="sidebar">
        <h3>Invendor</h3>
        <a href="index.php">🏠 Dashboard</a>
        <a href="admin.php" class="active">📦 Kelola Barang</a>
        <a href="logout.php" style="background-color: #c62828; text-align:center; margin-top:50px;">🚪 Keluar</a>
    </div>
    <div class="main-content">
        <div class="header"><h1>Manajemen Stok Barang (Admin)</h1></div>
        <div class="form-container">
            <h3><?= $is_edit ? 'Ubah Data Barang' : 'Tambah Barang Baru'; ?></h3>
            <form action="admin.php" method="POST" style="margin-top: 15px;">
                <input type="hidden" name="id_edit" value="<?= $id_edit; ?>">
                <div class="form-grid">
                    <div class="form-group"><label>Kode Barang</label><input type="text" name="kode_barang" value="<?= $kode; ?>" required></div>
                    <div class="form-group"><label>Nama Barang</label><input type="text" name="nama_barang" value="<?= $nama; ?>" required></div>
                    <div class="form-group"><label>Kategori</label><input type="text" name="kategori" value="<?= $kategori; ?>" required></div>
                    <div class="form-group"><label>Jumlah Stok</label><input type="number" name="stok" value="<?= $stok; ?>" required min="0"></div>
                    <div class="form-group"><label>Harga Satuan</label><input type="number" name="harga" value="<?= $harga; ?>" required min="0"></div>
                </div>
                <button type="submit" name="simpan" class="btn btn-success"><?= $is_edit ? 'Simpan' : 'Tambah'; ?></button>
                <?php if($is_edit): ?> <a href="admin.php" style="margin-left:10px; color:#555;">Batal</a> <?php endif; ?>
            </form>
        </div>
        <table>
            <thead><tr><th>Kode</th><th>Nama Barang</th><th>Kategori</th><th>Stok</th><th>Harga</th><th>Aksi</th></tr></thead>
            <tbody>
                <?php while ($row = mysqli_fetch_assoc($daftar_barang)): ?>
                <tr>
                    <td><?= $row['kode_barang']; ?></td><td><?= $row['nama_barang']; ?></td><td><?= $row['kategori']; ?></td>
                    <td><span style="color: <?= ($row['stok'] < 5) ? '#c62828; font-weight:bold;' : '#333'; ?>"><?= $row['stok']; ?> Pcs</span></td>
                    <td>Rp <?= number_format($row['harga'], 0, ',', '.'); ?></td>
                    <td>
                        <a href="admin.php?action=edit&id=<?= $row['id']; ?>" class="btn-edit">Edit</a>
                        <a href="admin.php?action=delete&id=<?= $row['id']; ?>" class="btn-danger" onclick="return confirm('Hapus?')">Hapus</a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</body>
</html>