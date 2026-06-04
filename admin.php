<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit;
}
require_once 'koneksi.php';

$id_edit = ''; $kode = ''; $nama = ''; $id_kategori_pilih = ''; $stok = ''; $harga = '';
$is_edit = false;

// --- LOGIKA OTOMATIS GENERATE KODE BARANG ---
if (!$is_edit) {
    $query_max = mysqli_query($koneksi, "SELECT kode_barang FROM barang ORDER BY id DESC LIMIT 1");
    if (mysqli_num_rows($query_max) > 0) {
        $row_max = mysqli_fetch_assoc($query_max);
        $last_code = $row_max['kode_barang'];
        $num = (int)substr($last_code, 3);
        $num++;
        $kode = "BRG" . sprintf("%03d", $num);
    } else {
        $kode = "BRG001";
    }
}

// --- PROSES SIMPAN / EDIT ---
if (isset($_POST['simpan'])) {
    $nama = mysqli_real_escape_string($koneksi, $_POST['nama_barang']);
    $id_kategori = (int)$_POST['id_kategori'];
    $stok = (int)$_POST['stok'];
    $harga = (float)$_POST['harga'];
    $user_id = $_SESSION['id_user'];

    if ($_POST['id_edit'] != '') {
        // Update
        $id = $_POST['id_edit'];
        $kode_lama = $_POST['kode_barang'];
        mysqli_query($koneksi, "UPDATE barang SET nama_barang='$nama', id_kategori=$id_kategori, stok=$stok, harga=$harga WHERE id=$id");
        
        // Catat Log Audit
        mysqli_query($koneksi, "INSERT INTO riwayat_stok (id_barang, jenis_perubahan, jumlah_perubahan, id_user, keterangan) VALUES ($id, 'update_stok', $stok, $user_id, 'Memperbarui data barang: $nama (Stok sekarang: $stok)')");
    } else {
        // Insert Baru dengan Kode Otomatis
        $kode_baru = $_POST['kode_barang'];
        mysqli_query($koneksi, "INSERT INTO barang (kode_barang, nama_barang, id_kategori, stok, harga, id_user) VALUES ('$kode_baru', '$nama', $id_kategori, $stok, $harga, $user_id)");
        $new_id = mysqli_insert_id($koneksi);
        
        // Catat Log Audit
        mysqli_query($koneksi, "INSERT INTO riwayat_stok (id_barang, jenis_perubahan, jumlah_perubahan, id_user, keterangan) VALUES ($new_id, 'tambah_barang', $stok, $user_id, 'Menambahkan barang baru: $nama dengan stok $stok')");
    }
    header("Location: admin.php");
    exit;
}

// --- AMBIL DATA EDIT ---
if (isset($_GET['action']) && $_GET['action'] == 'edit') {
    $id = $_GET['id'];
    $result = mysqli_query($koneksi, "SELECT * FROM barang WHERE id=$id");
    if ($row = mysqli_fetch_assoc($result)) {
        $id_edit = $row['id']; $kode = $row['kode_barang']; $nama = $row['nama_barang']; $id_kategori_pilih = $row['id_kategori']; $stok = $row['stok']; $harga = $row['harga'];
        $is_edit = true;
    }
}

// --- PROSES HAPUS ---
if (isset($_GET['action']) && $_GET['action'] == 'delete') {
    $id = $_GET['id'];
    $user_id = $_SESSION['id_user'];
    
    // Ambil nama barang sebelum dihapus untuk kepentingan log
    $b = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT nama_barang FROM barang WHERE id=$id"));
    $nama_b = $b['nama_barang'] ?? 'Barang';
    
    mysqli_query($koneksi, "INSERT INTO riwayat_stok (id_barang, jenis_perubahan, jumlah_perubahan, id_user, keterangan) VALUES (NULL, 'hapus_barang', 0, $user_id, 'Menghapus barang: $nama_b')");
    mysqli_query($koneksi, "DELETE FROM barang WHERE id=$id");
    header("Location: admin.php");
    exit;
}

$kategori_options = mysqli_query($koneksi, "SELECT * FROM kategori");
$daftar_barang = mysqli_query($koneksi, "SELECT b.*, k.nama_kategori FROM barang b LEFT JOIN kategori k ON b.id_kategori = k.id ORDER BY b.id DESC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Kelola Barang - INVENDOR</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Poppins', sans-serif; }
        body { background-color: #f4f7f6; display: flex; min-height: 100vh; }
        .sidebar { width: 250px; background-color: #1b5e20; color: white; padding: 20px; }
        .sidebar h3 { text-align: center; margin-bottom: 30px; font-weight: 700; }
        .sidebar a { display: block; color: #cbd5e1; padding: 12px; text-decoration: none; border-radius: 5px; margin-bottom: 10px; }
        .sidebar a:hover, .sidebar a.active { background-color: #2e7d32; color: white; font-weight: bold; }
        .main-content { flex-grow: 1; padding: 40px; }
        .header { display: flex; justify-content: space-between; margin-bottom: 30px; border-bottom: 2px solid #e2e8f0; padding-bottom: 15px; }
        .form-container { background: white; padding: 20px; border-radius: 8px; margin-bottom: 30px; box-shadow: 0 2px 5px rgba(0,0,0,0.02); }
        .form-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(140px, 1fr)); gap: 15px; margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; font-size: 13px; font-weight: 600; }
        .form-group input, .form-group select { width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px; font-size: 14px; }
        .btn { padding: 10px 20px; border: none; border-radius: 4px; color: white; font-weight: bold; cursor: pointer; }
        .btn-success { background-color: #2e7d32; }
        .btn-danger { background-color: #c62828; text-decoration: none; padding: 5px 10px; font-size: 12px; border-radius: 3px; }
        .btn-edit { background-color: #0284c7; color: white; text-decoration: none; padding: 5px 10px; font-size: 12px; border-radius: 3px; margin-right: 5px; }
        table { width: 100%; border-collapse: collapse; background: white; border-radius: 8px; overflow: hidden; }
        th, td { padding: 12px 15px; text-align: left; border-bottom: 1px solid #e2e8f0; font-size: 14px; }
        th { background-color: #2e7d32; color: white; }
    </style>
</head>
<body>
    <div class="sidebar">
        <h3>INVENDOR</h3>
        <a href="index.php">🏠 Dashboard</a>
        <a href="admin.php" class="active">📦 Kelola Barang (CRUD)</a>
        <a href="logout.php" style="background-color: #c62828; text-align:center; margin-top:50px;">🚪 Keluar</a>
    </div>
    <div class="main-content">
        <div class="header"><h2>Manajemen Stok Barang (Admin)</h2></div>
        <div class="form-container">
            <h3><?= $is_edit ? 'Ubah Data Barang' : 'Tambah Barang Baru'; ?></h3>
            <form action="admin.php" method="POST" style="margin-top: 15px;">
                <input type="hidden" name="id_edit" value="<?= $id_edit; ?>">
                <div class="form-grid">
                    <div class="form-group">
                        <label>Kode Barang (Otomatis)</label>
                        <input type="text" name="kode_barang" value="<?= $kode; ?>" readonly style="background-color: #e9ecef; font-weight: bold; color: #495057;">
                    </div>
                    <div class="form-group"><label>Nama Barang</label><input type="text" name="nama_barang" value="<?= $nama; ?>" required></div>
                    <div class="form-group">
                        <label>Kategori</label>
                        <select name="id_kategori" required>
                            <option value="">-- Pilih Kategori --</option>
                            <?php while($kat = mysqli_fetch_assoc($kategori_options)): ?>
                                <option value="<?= $kat['id']; ?>" <?= ($kat['id'] == $id_kategori_pilih) ? 'selected' : ''; ?>><?= $kat['nama_kategori']; ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="form-group"><label>Jumlah Stok</label><input type="number" name="stok" value="<?= $stok; ?>" required min="0"></div>
                    <div class="form-group"><label>Harga Satuan</label><input type="number" name="harga" value="<?= $harga; ?>" required min="0"></div>
                </div>
                <button type="submit" name="simpan" class="btn btn-success"><?= $is_edit ? 'Simpan Perubahan' : 'Tambah Data'; ?></button>
                <?php if($is_edit): ?> <a href="admin.php" style="margin-left:10px; color:#555;">Batal</a> <?php endif; ?>
            </form>
        </div>
        <table>
            <thead><tr><th>Kode</th><th>Nama Barang</th><th>Kategori</th><th>Stok</th><th>Harga</th><th>Aksi</th></tr></thead>
            <tbody>
                <?php while ($row = mysqli_fetch_assoc($daftar_barang)): ?>
                <tr>
                    <td><strong><?= $row['kode_barang']; ?></strong></td><td><?= $row['nama_barang']; ?></td><td><?= $row['nama_kategori'] ?? 'Tanpa Kategori'; ?></td>
                    <td><span style="color: <?= ($row['stok'] < 5) ? '#c62828; font-weight:bold;' : '#333'; ?>"><?= $row['stok']; ?> Pcs</span></td>
                    <td>Rp <?= number_format($row['harga'], 0, ',', '.'); ?></td>
                    <td>
                        <a href="admin.php?action=edit&id=<?= $row['id']; ?>" class="btn-edit">Edit</a>
                        <a href="admin.php?action=delete&id=<?= $row['id']; ?>" class="btn-danger" onclick="return confirm('Hapus barang ini?')">Hapus</a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</body>
</html>