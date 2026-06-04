<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit;
}
require_once 'koneksi.php';

$id_edit = ''; $kode = ''; $nama = ''; $id_kategori_pilih = ''; $id_supplier_pilih = ''; $stok = ''; $harga = '';
$is_edit = false;

// Auto-generate kode barang
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

// Proses Tambah Kategori
if (isset($_POST['tambah_kategori'])) {
    $kategori_baru = mysqli_real_escape_string($koneksi, $_POST['nama_kategori_baru']);
    if (!empty($kategori_baru)) {
        $cek = mysqli_query($koneksi, "SELECT * FROM kategori WHERE nama_kategori = '$kategori_baru'");
        if (mysqli_num_rows($cek) == 0) {
            mysqli_query($koneksi, "INSERT INTO kategori (nama_kategori) VALUES ('$kategori_baru')");
        }
    }
    header("Location: admin.php");
    exit;
}

// Proses Tambah Supplier Baru
if (isset($_POST['tambah_supplier'])) {
    $nama_sp = mysqli_real_escape_string($koneksi, $_POST['nama_supplier']);
    $telp_sp = mysqli_real_escape_string($koneksi, $_POST['telepon']);
    $alamat_sp = mysqli_real_escape_string($koneksi, $_POST['alamat']);
    
    if (!empty($nama_sp)) {
        mysqli_query($koneksi, "INSERT INTO supplier (nama_supplier, telepon, alamat) VALUES ('$nama_sp', '$telp_sp', '$alamat_sp')");
    }
    header("Location: admin.php");
    exit;
}

// Proses Simpan / Edit Barang
if (isset($_POST['simpan'])) {
    $nama = mysqli_real_escape_string($koneksi, $_POST['nama_barang']);
    $id_kategori = (int)$_POST['id_kategori'];
    $id_supplier = (int)$_POST['id_supplier'];
    $stok = (int)$_POST['stok'];
    $harga = (float)$_POST['harga'];
    $user_id = $_SESSION['id_user'];

    if ($_POST['id_edit'] != '') {
        $id = $_POST['id_edit'];
        $lama = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT stok FROM barang WHERE id=$id"));
        $stok_lama = $lama['stok'];
        $selisih = $stok - $stok_lama;
        
        mysqli_query($koneksi, "UPDATE barang SET nama_barang='$nama', id_kategori=$id_kategori, id_supplier=$id_supplier, stok=$stok, harga=$harga WHERE id=$id");
        
        $ket_log = "Memperbarui data barang [$nama]. Stok diubah dari $stok_lama menjadi $stok.";
        $ket_log_aman = mysqli_real_escape_string($koneksi, $ket_log);
        mysqli_query($koneksi, "INSERT INTO riwayat_stok (id_barang, jenis_perubahan, jumlah_perubahan, id_user, keterangan) VALUES ($id, 'PROSES EDIT', $selisih, $user_id, '$ket_log_aman')");
    } else {
        $kode_baru = $_POST['kode_barang'];
        mysqli_query($koneksi, "INSERT INTO barang (kode_barang, nama_barang, id_kategori, id_supplier, stok, harga, id_user) VALUES ('$kode_baru', '$nama', $id_kategori, $id_supplier, $stok, $harga, $user_id)");
        $new_id = mysqli_insert_id($koneksi);
        
        $ket_log_baru = "Menambahkan barang baru: $nama dengan stok awal $stok Pcs";
        $ket_log_baru_aman = mysqli_real_escape_string($koneksi, $ket_log_baru);
        mysqli_query($koneksi, "INSERT INTO riwayat_stok (id_barang, jenis_perubahan, jumlah_perubahan, id_user, keterangan) VALUES ($new_id, 'BARANG MASUK', $stok, $user_id, '$ket_log_baru_aman')");
    }
    header("Location: admin.php");
    exit;
}

// Mengambil data untuk Form Edit
if (isset($_GET['action']) && $_GET['action'] == 'edit') {
    $id = $_GET['id'];
    $result = mysqli_query($koneksi, "SELECT * FROM barang WHERE id=$id");
    if ($row = mysqli_fetch_assoc($result)) {
        $id_edit = $row['id']; $kode = $row['kode_barang']; $nama = $row['nama_barang']; 
        $id_kategori_pilih = $row['id_kategori']; $id_supplier_pilih = $row['id_supplier']; $stok = $row['stok']; $harga = $row['harga'];
        $is_edit = true;
    }
}

// Proses Hapus Barang
if (isset($_GET['action']) && $_GET['action'] == 'delete') {
    $id = $_GET['id'];
    $user_id = $_SESSION['id_user'];
    $b = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT nama_barang, stok FROM barang WHERE id=$id"));
    $nama_b = $b['nama_barang'] ?? 'Barang';
    $stok_b = $b['stok'] ?? 0;
    
    $ket_log_hapus = "Menghapus barang dari sistem: $nama_b (Stok terakhir: $stok_b Pcs)";
    $ket_log_hapus_aman = mysqli_real_escape_string($koneksi, $ket_log_hapus);
    mysqli_query($koneksi, "INSERT INTO riwayat_stok (id_barang, jenis_perubahan, jumlah_perubahan, id_user, keterangan) VALUES (NULL, 'BARANG DIHAPUS', -$stok_b, $user_id, '$ket_log_hapus_aman')");
    mysqli_query($koneksi, "DELETE FROM barang WHERE id=$id");
    header("Location: admin.php");
    exit;
}

$kategori_options = mysqli_query($koneksi, "SELECT * FROM kategori ORDER BY nama_kategori ASC");
$supplier_options = mysqli_query($koneksi, "SELECT * FROM supplier ORDER BY nama_supplier ASC");

// Query JOIN 3 Tabel (barang + kategori + supplier)
$daftar_barang = mysqli_query($koneksi, "SELECT b.*, k.nama_kategori, s.nama_supplier FROM barang b LEFT JOIN kategori k ON b.id_kategori = k.id LEFT JOIN supplier s ON b.id_supplier = s.id ORDER BY b.id DESC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Kelola Barang - INVENDOR</title>
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
            width: 260px; background: rgba(27, 94, 32, 0.75); backdrop-filter: blur(15px); -webkit-backdrop-filter: blur(15px);
            color: white; padding: 25px 20px; border-right: 1px solid rgba(255,255,255,0.1);
        }
        .sidebar h3 { text-align: center; margin-bottom: 30px; font-weight: 700; }
        .sidebar a { display: block; color: #cbd5e1; padding: 12px; text-decoration: none; border-radius: 8px; margin-bottom: 10px; transition: 0.3s; }
        .sidebar a:hover, .sidebar a.active { background-color: rgba(255,255,255,0.2); color: white; font-weight: bold; }
        
        .main-content { flex-grow: 1; padding: 40px; background: rgba(244, 247, 246, 0.85); min-height: 100vh; overflow-y: auto; }
        .header { display: flex; justify-content: space-between; margin-bottom: 30px; border-bottom: 2px solid rgba(0,0,0,0.05); padding-bottom: 15px; }
        
        .management-zone { display: grid; grid-template-columns: 1.8fr 1.2fr; gap: 20px; margin-bottom: 30px; }
        .form-container { background: white; padding: 20px; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.01); }
        .form-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(130px, 1fr)); gap: 12px; margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; font-size: 13px; font-weight: 600; }
        .form-group input, .form-group select, .form-group textarea { width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 6px; font-size: 14px; }
        
        .btn { padding: 10px 20px; border: none; border-radius: 6px; color: white; font-weight: bold; cursor: pointer; display: inline-block; }
        .btn-success { background-color: #2e7d32; }
        .btn-primary { background-color: #0284c7; width: 100%; }
        .btn-danger { background-color: #c62828; text-decoration: none; padding: 5px 10px; font-size: 12px; border-radius: 3px; cursor: pointer; }
        .btn-edit { background-color: #0284c7; color: white; text-decoration: none; padding: 5px 10px; font-size: 12px; border-radius: 3px; margin-right: 5px; }
        
        .table-wrapper { max-height: 400px; overflow-y: auto; background: white; border-radius: 12px; border: 1px solid #e2e8f0; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 12px 15px; text-align: left; border-bottom: 1px solid #e2e8f0; font-size: 14px; }
        th { background-color: #2e7d32; color: white; position: sticky; top: 0; z-index: 10; }

        .modal-overlay {
            position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0, 0, 0, 0.4); backdrop-filter: blur(8px);
            display: none; justify-content: center; align-items: center; z-index: 9999;
        }
        .modal-box {
            background: white; padding: 30px; border-radius: 16px; width: 90%; max-width: 420px; text-align: center; box-shadow: 0 10px 25px rgba(0,0,0,0.2);
        }
        .modal-buttons { display: flex; justify-content: center; gap: 15px; margin-top: 20px; }
    </style>
</head>
<body>
    <div class="sidebar">
        <h3>INVENDOR</h3>
        <a href="index.php">🏠 Dashboard</a>
        <a href="admin.php" class="active">📦 Kelola Barang (CRUD)</a>
        <a href="logout.php" style="background-color: rgba(198, 40, 40, 0.85); text-align:center; margin-top: 25px; font-weight: bold;">🚪 Keluar</a>
    </div>
    
    <div class="main-content">
        <div class="header"><h2>Manajemen Stok & Supplier (Admin)</h2></div>
        
        <div class="management-zone">
            <!-- FORM INPUT BARANG -->
            <div class="form-container">
                <h3><?= $is_edit ? '⚙️ Ubah Data Barang' : '➕ Tambah Barang Baru'; ?></h3>
                <form action="admin.php" method="POST" style="margin-top: 15px;">
                    <input type="hidden" name="id_edit" value="<?= $id_edit; ?>">
                    <div class="form-grid">
                        <div class="form-group">
                            <label>Kode Barang</label>
                            <input type="text" name="kode_barang" value="<?= $kode; ?>" readonly style="background-color: #e9ecef; font-weight: bold;">
                        </div>
                        <div class="form-group"><label>Nama Barang</label><input type="text" name="nama_barang" value="<?= $nama; ?>" required></div>
                        <div class="form-group">
                            <label>Kategori</label>
                            <select name="id_kategori" required>
                                <option value="">-- Pilih --</option>
                                <?php mysqli_data_seek($kategori_options, 0); while($kat = mysqli_fetch_assoc($kategori_options)): ?>
                                    <option value="<?= $kat['id']; ?>" <?= ($kat['id'] == $id_kategori_pilih) ? 'selected' : ''; ?>><?= $kat['nama_kategori']; ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Supplier</label>
                            <select name="id_supplier" required>
                                <option value="">-- Pilih Supplier --</option>
                                <?php mysqli_data_seek($supplier_options, 0); while($sp = mysqli_fetch_assoc($supplier_options)): ?>
                                    <option value="<?= $sp['id']; ?>" <?= ($sp['id'] == $id_supplier_pilih) ? 'selected' : ''; ?>><?= $sp['nama_supplier']; ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="form-group"><label>Stok</label><input type="number" name="stok" value="<?= $stok; ?>" required min="0"></div>
                        <div class="form-group"><label>Harga</label><input type="number" name="harga" value="<?= $harga; ?>" required min="0"></div>
                    </div>
                    <button type="submit" name="simpan" class="btn btn-success"><?= $is_edit ? 'Simpan Perubahan' : 'Tambah Data'; ?></button>
                    <?php if($is_edit): ?> <a href="admin.php" style="margin-left:10px; color:#555;">Batal</a> <?php endif; ?>
                </form>
            </div>

            <!-- FORM ENTITAS SAMPINGAN (KATEGORI & SUPPLIER) -->
            <div style="display: flex; flex-direction: column; gap: 15px;">
                <div class="form-container" style="border-left: 4px solid #0284c7; padding: 15px;">
                    <h3>📁 Kategori Baru</h3>
                    <form action="admin.php" method="POST" style="margin-top: 10px; display: flex; gap: 10px;">
                        <input type="text" name="nama_kategori_baru" placeholder="Sembako" required style="flex-grow:1; padding: 6px; border-radius:4px; border:1px solid #ccc;">
                        <button type="submit" name="tambah_kategori" class="btn btn-primary" style="width:auto; padding:6px 12px;">➕</button>
                    </form>
                </div>

                <div class="form-container" style="border-left: 4px solid #e65100; padding: 15px;">
                    <h3>🏢 Supplier Baru</h3>
                    <form action="admin.php" method="POST" style="margin-top: 10px;">
                        <input type="text" name="nama_supplier" placeholder="Nama Perusahaan" required style="width:100%; margin-bottom:8px; padding: 6px; border-radius:4px; border:1px solid #ccc;">
                        <input type="text" name="telepon" placeholder="No. Telepon" required style="width:100%; margin-bottom:8px; padding: 6px; border-radius:4px; border:1px solid #ccc;">
                        <textarea name="alamat" placeholder="Alamat Gudang" required style="width:100%; margin-bottom:8px; padding: 6px; border-radius:4px; border:1px solid #ccc; height: 50px;"></textarea>
                        <button type="submit" name="tambah_supplier" class="btn btn-primary" style="background-color: #e65100;">➕ Buat Supplier</button>
                    </form>
                </div>
            </div>
        </div>

        <!-- TABEL UTAMA BARANG -->
        <div class="table-wrapper">
            <table>
                <thead><tr><th>Kode</th><th>Nama Barang</th><th>Kategori</th><th>Supplier</th><th>Stok</th><th>Harga</th><th>Aksi</th></tr></thead>
                <tbody>
                    <?php while ($row = mysqli_fetch_assoc($daftar_barang)): ?>
                    <tr>
                        <td><strong><?= $row['kode_barang']; ?></strong></td>
                        <td><?= $row['nama_barang']; ?></td>
                        <td><?= $row['nama_kategori'] ?? 'Uncategorized'; ?></td>
                        <td style="color: #e65100; font-weight: 500;"><?= $row['nama_supplier'] ?? 'Belum Ada'; ?></td>
                        <td><span style="color: <?= ($row['stok'] < 5) ? '#c62828; font-weight:bold;' : '#333'; ?>"><?= $row['stok']; ?> Pcs</span></td>
                        <td>Rp <?= number_format($row['harga'], 0, ',', '.'); ?></td>
                        <td>
                            <a href="admin.php?action=edit&id=<?= $row['id']; ?>" class="btn-edit">Edit</a>
                            <button type="button" class="btn-danger" onclick="bukaModalHapus(<?= $row['id']; ?>, '<?= htmlspecialchars($row['nama_barang'], ENT_QUOTES); ?>')">Hapus</button>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- MODAL HAPUS -->
    <div id="modalHapus" class="modal-overlay">
        <div class="modal-box">
            <h3 style="color:#c62828;">⚠️ Konfirmasi Hapus</h3>
            <p style="margin-top:10px;">Apakah yakin ingin menghapus <strong id="namaBarangTeks"></strong>?</p>
            <div class="modal-buttons">
                <button type="button" style="padding:8px 15px; background:#cbd5e1; border:none; border-radius:4px; cursor:pointer;" onclick="tutupModalHapus()">Batal</button>
                <a id="linkKonfirmasiHapus" href="#" class="btn" style="background:#c62828; text-decoration:none;">Ya, Hapus</a>
            </div>
        </div>
    </div>

    <script>
        function bukaModalHapus(id, namaBarang) {
            document.getElementById('namaBarangTeks').innerText = namaBarang;
            document.getElementById('linkKonfirmasiHapus').href = 'admin.php?action=delete&id=' + id;
            document.getElementById('modalHapus').style.display = 'flex';
        }
        function tutupModalHapus() { document.getElementById('modalHapus').style.display = 'none'; }
    </script>
</body>
</html>