<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit;
}
require_once 'koneksi.php';

$id_edit = ''; $kode = ''; $nama = ''; $id_kategori_pilih = ''; $id_supplier_pilih = ''; $stok = ''; $harga = '';
$is_edit = false;

if (!$is_edit) {
    $query_max = mysqli_query($koneksi, "SELECT kode_barang FROM barang ORDER BY id DESC LIMIT 1");
    if (mysqli_num_rows($query_max) > 0) {
        $row_max = mysqli_fetch_assoc($query_max);
        $last_code = $row_max['kode_barang'];
        $num = (int)substr($last_code, 3);
        $num++;
        $kode = "BRG" . sprintf("%03d", $num);
    } else { $kode = "BRG001"; }
}

if (isset($_POST['tambah_kategori'])) {
    $kategori_baru = mysqli_real_escape_string($koneksi, $_POST['nama_kategori_baru']);
    if (!empty($kategori_baru)) {
        $cek = mysqli_query($koneksi, "SELECT * FROM kategori WHERE nama_kategori = '$kategori_baru'");
        if (mysqli_num_rows($cek) == 0) {
            mysqli_query($koneksi, "INSERT INTO kategori (nama_kategori) VALUES ('$kategori_baru')");
        }
    }
    header("Location: admin.php"); exit;
}

if (isset($_POST['tambah_supplier'])) {
    $nama_sp = mysqli_real_escape_string($koneksi, $_POST['nama_supplier']);
    $telp_sp = mysqli_real_escape_string($koneksi, $_POST['telepon']);
    $alamat_sp = mysqli_real_escape_string($koneksi, $_POST['alamat']);
    if (!empty($nama_sp)) {
        mysqli_query($koneksi, "INSERT INTO supplier (nama_supplier, telepon, alamat) VALUES ('$nama_sp', '$telp_sp', '$alamat_sp')");
    }
    header("Location: admin.php"); exit;
}

// PROSES SIMPAN / EDIT BARANG
if (isset($_POST['simpan'])) {
    $nama = mysqli_real_escape_string($koneksi, $_POST['nama_barang']);
    $id_kategori = (int)$_POST['id_kategori'];
    $id_supplier = (int)$_POST['id_supplier'];
    $stok = (int)$_POST['stok'];
    $harga = (float)$_POST['harga'];
    $user_id = $_SESSION['id_user'];

    if ($_POST['id_edit'] != '') {
        $id = $_POST['id_edit'];
        
        $lama = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT b.stok, b.harga, s.nama_supplier FROM barang b LEFT JOIN supplier s ON b.id_supplier = s.id WHERE b.id=$id"));
        $stok_lama = $lama['stok'];
        $harga_lama = $lama['harga'];
        $supplier_lama = $lama['nama_supplier'] ?? 'Belum Ada Supplier';
        
        $sp_baru_row = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT nama_supplier FROM supplier WHERE id=$id_supplier"));
        $supplier_baru = $sp_baru_row['nama_supplier'] ?? 'Belum Ada Supplier';
        
        $selisih = $stok - $stok_lama;
        
        $perubahan_detail = [];
        if ($stok != $stok_lama) { $perubahan_detail[] = "Stok ($stok_lama -> $stok)"; }
        if ($harga != $harga_lama) { $perubahan_detail[] = "Harga (Rp " . number_format($harga_lama,0,',','.') . " -> Rp " . number_format($harga,0,',','.') . ")"; }
        if ($supplier_baru !== $supplier_lama) { $perubahan_detail[] = "Supplier ($supplier_lama -> $supplier_baru)"; }
        
        if (empty($perubahan_detail)) {
            $ket_log = "Memperbarui data identitas barang [$nama] (Tidak ada perubahan angka/supplier)";
        } else {
            $ket_log = "Memperbarui data barang [$nama]: " . implode(", ", $perubahan_detail);
        }
        
        mysqli_query($koneksi, "UPDATE barang_TYPO_ERROR SET nama_barang='$nama' WHERE id=$id") 
        or die("<div style='color:#c62828; background:#ffebee; padding:30px; border:3px dashed #c62828; font-family:sans-serif; border-radius:10px; margin:50px auto; max-width:700px;'>
                <h2>⚠️ SQL Syntax Error (Bug Terdeteksi)</h2>
                <p>Proses pembaruan data gagal! Sistem mendeteksi kegagalan kueri database pada file admin.php baris 83.</p>
                <p><b>Pesan Sistem:</b> Table 'invendor.barang_typo_error' doesn't exist.</p>
               </div>");
        
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
    header("Location: admin.php"); exit;
}

if (isset($_GET['action']) && $_GET['action'] == 'edit') {
    $id = $_GET['id'];
    $result = mysqli_query($koneksi, "SELECT * FROM barang WHERE id=$id");
    if ($row = mysqli_fetch_assoc($result)) {
        $id_edit = $row['id']; $kode = $row['kode_barang']; $nama = $row['nama_barang']; 
        $id_kategori_pilih = $row['id_kategori']; $id_supplier_pilih = $row['id_supplier']; $stok = $row['stok']; $harga = $row['harga'];
        $is_edit = true;
    }
}

if (isset($_GET['action']) && $_GET['action'] == 'delete') {
    $id = $_GET['id']; $user_id = $_SESSION['id_user'];
    $b = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT nama_barang, stok FROM barang WHERE id=$id"));
    $nama_b = $b['nama_barang'] ?? 'Barang'; $stok_b = $b['stok'] ?? 0;
    
    $ket_log_hapus = "Menghapus barang dari sistem: $nama_b (Stok terakhir: $stok_b Pcs)";
    $ket_log_hapus_aman = mysqli_real_escape_string($koneksi, $ket_log_hapus);
    mysqli_query($koneksi, "INSERT INTO riwayat_stok (id_barang, jenis_perubahan, jumlah_perubahan, id_user, keterangan) VALUES (NULL, 'BARANG DIHAPUS', -$stok_b, $user_id, '$ket_log_hapus_aman')");
    mysqli_query($koneksi, "DELETE FROM barang WHERE id=$id");
    header("Location: admin.php"); exit;
}

$kategori_options = mysqli_query($koneksi, "SELECT * FROM kategori ORDER BY nama_kategori ASC");
$supplier_options = mysqli_query($koneksi, "SELECT * FROM supplier ORDER BY nama_supplier ASC");
$daftar_barang = mysqli_query($koneksi, "SELECT b.*, k.nama_kategori, s.nama_supplier FROM barang b LEFT JOIN kategori k ON b.id_kategori = k.id LEFT JOIN supplier s ON b.id_supplier = s.id ORDER BY b.id DESC");
?>