<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: index.php");
    exit;
}
require_once 'koneksi.php';

// Query mengambil seluruh riwayat aktivitas dan nama aktornya
$query = "SELECT r.*, u.nama_lengkap FROM riwayat_stok r 
          LEFT JOIN users u ON r.id_user = u.id 
          ORDER BY r.id DESC";
$daftar_riwayat = mysqli_query($koneksi, $query);
$total_aktivitas = mysqli_num_rows($daftar_riwayat);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Jurnal_Mutasi_INVENDOR_<?= date('Y-m-d'); ?></title>
    <style>
        body {
            font-family: 'Courier New', Courier, monospace;
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
        .text-center { text-align: center; }
        
        .badge-text {
            font-weight: bold;
            font-family: Arial, sans-serif;
            font-size: 11px;
        }

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

        @media print {
            @page { size: A4 portrait; margin: 15mm; }
            body { margin: 0; }
            .no-print { display: none; }
        }
        
        .no-print-floating {
            position: fixed; top: 20px; left: 20px;
            background: #2e7d32; color: white; padding: 10px 15px;
            text-decoration: none; border-radius: 5px; font-family: Arial, sans-serif;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body>

    <a href="index.php" class="no-print-floating no-print">⬅️ Kembali ke Dashboard</a>

    <div class="header-laporan">
        <h1>SISTEM MANAJEMEN GUDANG - INVENDOR</h1>
        <p>Gedung Logistik Utama, Lantai 2 • Telp: (021) 555-1234 • Email: gudang@invendor.com</p>
        <p><strong>AUDIT TRAIL REPORT: LAPORAN JURNAL MUTASI STOK GUDANG</strong></p>
    </div>

    <div class="meta-laporan">
        <div>
            <p>Tanggal Cetak : <strong><?= date('d F Y / H:i'); ?> WITA</strong></p>
            <p>Dicetak Oleh   : <strong><?= $_SESSION['nama_lengkap']; ?> (<?= strtoupper($_SESSION['role']); ?>)</strong></p>
        </div>
        <div style="text-align: right;">
            <p>Total Rekam Aktivitas : <strong><?= $total_aktivitas; ?> Entri Log</strong></p>
            <p>Status Dokumen        : <strong>Dokumen Audit Sah</strong></p>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th width="5%" class="text-center">No</th>
                <th width="20%">Waktu Kejadian</th>
                <th width="15%">Operator System</th>
                <th width="15%">Aktivitas</th>
                <th width="45%">Detail Keterangan Kronologi Perubahan Data</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $no = 1;
            if(mysqli_num_rows($daftar_riwayat) > 0):
                while($row = mysqli_fetch_assoc($daftar_riwayat)): 
            ?>
                <tr>
                    <td class="text-center"><?= $no++; ?></td>
                    <td><?= $row['tanggal']; ?></td>
                    <td><strong><?= $row['nama_lengkap']; ?></strong></td>
                    <td class="badge-text"><?= $row['jenis_perubahan']; ?></td>
                    <td><?= $row['keterangan']; ?></td>
                </tr>
            <?php 
                endwhile;
            else:
            ?>
                <tr>
                    <td colspan="5" class="text-center" style="padding: 20px; color: #777;">Belum ada riwayat mutasi barang di database.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <div class="ttd-container">
        <div class="ttd-box">
            <p>Jakarta, <?= date('d F Y'); ?></p>
            <p>Mengetahui,</p>
            <p><strong>Kepala Inspektur Audit Logistik</strong></p>
            <div class="ttd-space"></div>
            <p style="text-decoration: underline;"><strong>(...........................)</strong></p>
            <p style="font-size: 11px; color: #555;">NIP. <?= date('Ymd'); ?>0299</p>
        </div>
    </div>

    <script>
        window.onload = function() {
            window.print();
        }
    </script>
</body>
</html>