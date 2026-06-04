<?php
session_start();
// Pastikan hanya user yang sudah login yang bisa akses halaman cetak
if (!isset($_SESSION['username'])) {
    header("Location: index.php");
    exit;
}
require_once 'koneksi.php';

// Ambil data barang lengkap dengan kategori dan supplier
$query = "SELECT b.*, k.nama_kategori, s.nama_supplier 
          FROM barang b 
          LEFT JOIN kategori k ON b.id_kategori = k.id 
          LEFT JOIN supplier s ON b.id_supplier = s.id 
          ORDER BY b.kode_barang ASC";
$daftar_barang = mysqli_query($koneksi, $query);

// Ambil total ringkasan untuk header laporan
$total_jenis = mysqli_num_rows($daftar_barang);
$total_stok_query = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT SUM(stok) AS total FROM barang"));
$total_stok = $total_stok_query['total'] ?? 0;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan_Stok_INVENDOR_<?= date('Y-m-d'); ?></title>
    <style>
        body {
            font-family: 'Courier New', Courier, monospace; /* Gaya font khas laporan formal */
            color: #000;
            background: #fff;
            padding: 10px;
            font-size: 13px;
        }
        .header-laporan {
            text-align: center;
            border-bottom: 3px double #000;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }
        .header-laporan h1 { font-size: 24px; margin-bottom: 5px; text-transform: uppercase; font-family: Arial, sans-serif; }
        .header-laporan p { margin: 2px 0; color: #444; }
        
        .meta-laporan {
            display: flex;
            justify-content: space-between;
            margin-bottom: 15px;
            font-size: 12px;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        th, td {
            border: 1px solid #000;
            padding: 8px 10px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2 !important;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
            font-weight: bold;
        }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        
        /* Tanda Tangan di Bawah (Poin Nilai Plus dari Dosen) */
        .ttd-container {
            margin-top: 50px;
            display: flex;
            justify-content: flex-end;
        }
        .ttd-box {
            text-align: center;
            width: 250px;
        }
        .ttd-space {
            height: 70px;
        }

        /* CSS Pengatur Layout Saat Menjadi PDF */
        @media print {
            @page {
                size: A4 portrait;
                margin: 15mm;
            }
            body { margin: 0; }
            .no-print { display: none; }
        }
        
        /* Tombol melayang kembali / cetak manual jika otomatisnya gagal */
        .no-print-floating {
            position: fixed; top: 20px; left: 20px;
            background: #2e7d32; color: white; padding: 10px 15px;
            text-decoration: none; border-radius: 5px; font-family: Arial, sans-serif;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body>

    <!-- Tombol ini otomatis hilang saat dokumen disimpan jadi PDF atau dicetak -->
    <a href="index.php" class="no-print-floating no-print">⬅️ Kembali ke Dashboard</a>

    <div class="header-laporan">
        <h1>SISTEM MANAJEMEN GUDANG - INVENDOR</h1>
        <p>Gedung Logistik Utama, Lantai 2 • Telp: (021) 555-1234 • Email: gudang@invendor.com</p>
        <p><strong>LAPORAN BERKALA PERSEDIAAN STOK BARANG & SUPPLIER</strong></p>
    </div>

    <div class="meta-laporan">
        <div>
            <p>Tanggal Cetak : <strong><?= date('d F Y / H:i'); ?> WITA</strong></p>
            <p>Oleh Operator : <strong><?= $_SESSION['nama_lengkap']; ?> (<?= strtoupper($_SESSION['role']); ?>)</strong></p>
        </div>
        <div style="text-align: right;">
            <p>Total Jenis Barang : <strong><?= $total_jenis; ?> SKU</strong></p>
            <p>Total Muatan Stok  : <strong><?= $total_stok; ?> Pcs</strong></p>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th width="5%" class="text-center">No</th>
                <th width="15%">Kode Barang</th>
                <th width="25%">Nama Item Barang</th>
                <th width="15%">Kategori</th>
                <th width="20%">Pemasok (Supplier)</th>
                <th width="10%" class="text-center">Stok</th>
                <th width="15%" class="text-right">Harga Satuan</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $no = 1;
            if(mysqli_num_rows($daftar_barang) > 0):
                while($row = mysqli_fetch_assoc($daftar_barang)): 
            ?>
                <tr>
                    <td class="text-center"><?= $no++; ?></td>
                    <td><strong><?= $row['kode_barang']; ?></strong></td>
                    <td><?= $row['nama_barang']; ?></td>
                    <td><?= $row['nama_kategori'] ?? 'Uncategorized'; ?></td>
                    <td><?= $row['nama_supplier'] ?? '-'; ?></td>
                    <td class="text-center" <?= ($row['stok'] < 5) ? 'style="font-weight:bold; color:red;"' : ''; ?>>
                        <?= $row['stok']; ?> Pcs
                    </td>
                    <td class="text-right">Rp <?= number_format($row['harga'], 0, ',', '.'); ?></td>
                </tr>
            <?php 
                endwhile;
            else:
            ?>
                <tr>
                    <td colspan="7" class="text-center" style="padding: 20px; color: #777;">Tidak ada data logistik di dalam database.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <div class="ttd-container">
        <div class="ttd-box">
            <p>Jakarta, <?= date('d F Y'); ?></p>
            <p>Mengetahui,</p>
            <p><strong>Kepala Otoritas Gudang</strong></p>
            <div class="ttd-space"></div>
            <p style="text-decoration: underline;"><strong>(...........................)</strong></p>
            <p style="font-size: 11px; color: #555;">NIP. <?= date('Ymd'); ?>0122</p>
        </div>
    </div>

    <!-- SCRIPT OTOMATIS MEMBUKA JENDELA SIMPAN PDF / CETAK BROWSER -->
    <script>
        window.onload = function() {
            window.print();
        }
    </script>
</body>
</html>