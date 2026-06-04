-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 04, 2026 at 09:58 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `invendor`
--

-- --------------------------------------------------------

--
-- Table structure for table `barang`
--

CREATE TABLE `barang` (
  `id` int(11) NOT NULL,
  `kode_barang` varchar(20) NOT NULL,
  `nama_barang` varchar(100) NOT NULL,
  `id_kategori` int(11) DEFAULT NULL,
  `id_supplier` int(11) DEFAULT NULL,
  `stok` int(11) NOT NULL DEFAULT 0,
  `harga` decimal(10,2) NOT NULL,
  `id_user` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `barang`
--

INSERT INTO `barang` (`id`, `kode_barang`, `nama_barang`, `id_kategori`, `id_supplier`, `stok`, `harga`, `id_user`) VALUES
(3, 'BRG003', 'Buku Nota Kontan', 1, NULL, 20, 5000.00, 1),
(4, 'BRG004', 'Mouse RTX', 2, 1, 10, 600000.00, 1),
(6, 'BRG006', 'Sapu', 4, 2, 10, 55000.00, 1),
(7, 'BRG007', 'Laptop Thinkpad', 2, 3, 10, 40000000.00, 1),
(8, 'BRG008', 'Keyboard Mechanical', 2, 1, 25, 350000.00, 1),
(9, 'BRG009', 'Penghapus', 1, 2, 15, 5000.00, 1),
(10, 'BRG010', 'Pulpen', 1, 2, 15, 5000.00, 1),
(11, 'BRG011', 'Lemari Pakaian', 4, 1, 3, 1500000.00, 1),
(12, 'BRG012', 'Jam Dinding', 4, 1, 15, 75000.00, 1),
(13, 'BRG013', 'Televisi', 2, 1, 4, 1350000.00, 1),
(15, 'BRG015', 'Kulkas 2 Pintu', 2, 1, 15, 2000000.00, 1);

-- --------------------------------------------------------

--
-- Table structure for table `kategori`
--

CREATE TABLE `kategori` (
  `id` int(11) NOT NULL,
  `nama_kategori` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `kategori`
--

INSERT INTO `kategori` (`id`, `nama_kategori`) VALUES
(4, 'Alat Rumah Tangga'),
(1, 'Alat Tulis'),
(2, 'Elektronik'),
(3, 'Sembako');

-- --------------------------------------------------------

--
-- Table structure for table `riwayat_stok`
--

CREATE TABLE `riwayat_stok` (
  `id` int(11) NOT NULL,
  `id_barang` int(11) DEFAULT NULL,
  `jenis_perubahan` varchar(50) NOT NULL,
  `jumlah_perubahan` int(11) NOT NULL,
  `tanggal` timestamp NOT NULL DEFAULT current_timestamp(),
  `id_user` int(11) DEFAULT NULL,
  `keterangan` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `riwayat_stok`
--

INSERT INTO `riwayat_stok` (`id`, `id_barang`, `jenis_perubahan`, `jumlah_perubahan`, `tanggal`, `id_user`, `keterangan`) VALUES
(1, NULL, 'Stok Awal', 12, '2026-06-04 10:26:55', 1, 'Inisialisasi barang Kertas HVS A4'),
(2, NULL, 'Stok Awal', 3, '2026-06-04 10:26:55', 1, 'Inisialisasi barang Tinta Printer Hitam'),
(3, 3, 'Stok Awal', 25, '2026-06-04 10:26:55', 1, 'Inisialisasi barang Buku Nota Kontan'),
(4, 4, 'tambah_barang', 50, '2026-06-04 11:36:41', 1, 'Menambahkan barang baru: Mouse RTX dengan stok 50'),
(5, 4, 'PROSES EDIT', 20, '2026-06-04 12:00:16', 1, 'Memperbarui data barang [Mouse RTX]. Stok diubah dari 30 menjadi 50.'),
(6, NULL, 'BARANG MASUK', 10, '2026-06-04 12:01:00', 1, 'Menambahkan barang baru: Spidol dengan stok awal 10 Pcs'),
(7, NULL, 'PROSES EDIT', 10, '2026-06-04 12:01:14', 1, 'Memperbarui data barang [Spidol]. Stok diubah dari 10 menjadi 20.'),
(8, 6, 'BARANG MASUK', 5, '2026-06-04 12:02:24', 1, 'Menambahkan barang baru: Sapu dengan stok awal 5 Pcs'),
(9, NULL, 'PROSES EDIT', 37, '2026-06-04 12:02:35', 1, 'Memperbarui data barang [Tinta Printer Hitam]. Stok diubah dari 3 menjadi 40.'),
(10, 3, 'PROSES EDIT', -5, '2026-06-04 12:07:31', 1, 'Memperbarui data barang [Buku Nota Kontan]. Stok diubah dari 25 menjadi 20.'),
(11, NULL, 'BARANG DIHAPUS', -12, '2026-06-04 12:08:07', 1, 'Menghapus barang dari sistem: Kertas HVS A4 (Stok terakhir: 12 Pcs)'),
(12, NULL, 'BARANG DIHAPUS', -20, '2026-06-04 12:08:11', 1, 'Menghapus barang dari sistem: Spidol (Stok terakhir: 20 Pcs)'),
(13, 7, 'BARANG MASUK', 10, '2026-06-04 12:25:16', 1, 'Menambahkan barang baru: Laptop Thinkpad dengan stok awal 10 Pcs'),
(14, NULL, 'BARANG DIHAPUS', -40, '2026-06-04 12:25:40', 1, 'Menghapus barang dari sistem: Tinta Printer Hitam (Stok terakhir: 40 Pcs)'),
(15, 7, 'PROSES EDIT', 0, '2026-06-04 13:51:55', 1, 'Memperbarui data barang [Laptop Thinkpad]. Stok diubah dari 10 menjadi 10.'),
(16, 6, 'PROSES EDIT', 5, '2026-06-04 13:52:43', 1, 'Memperbarui data barang [Sapu]. Stok diubah dari 5 menjadi 10.'),
(17, 4, 'PROSES EDIT', -40, '2026-06-04 14:02:59', 1, 'Memperbarui data barang [Mouse RTX]: Stok (50 -> 10), Harga (Rp 500.000 -> Rp 600.000), Supplier (Belum Ada Supplier -> PT. Maju Logistik)'),
(18, 8, 'BARANG MASUK', 25, '2026-06-04 14:09:16', 1, 'Menambahkan barang baru: Keyboard Mechanical dengan stok awal 25 Pcs'),
(19, 9, 'BARANG MASUK', 15, '2026-06-04 14:10:00', 1, 'Menambahkan barang baru: Penghapus dengan stok awal 15 Pcs'),
(20, 10, 'BARANG MASUK', 15, '2026-06-04 14:10:38', 1, 'Menambahkan barang baru: Pulpen dengan stok awal 15 Pcs'),
(21, 11, 'BARANG MASUK', 3, '2026-06-04 14:11:14', 1, 'Menambahkan barang baru: Lemari Pakaian dengan stok awal 3 Pcs'),
(22, 12, 'BARANG MASUK', 15, '2026-06-04 14:12:06', 1, 'Menambahkan barang baru: Jam Dinding dengan stok awal 15 Pcs'),
(23, 13, 'BARANG MASUK', 4, '2026-06-04 14:12:46', 1, 'Menambahkan barang baru: Televisi dengan stok awal 4 Pcs'),
(24, NULL, 'BARANG MASUK', 10, '2026-06-04 14:13:20', 1, 'Menambahkan barang baru: Sofa dengan stok awal 10 Pcs'),
(25, 15, 'BARANG MASUK', 15, '2026-06-04 14:14:33', 1, 'Menambahkan barang baru: Kulkas 2 Pintu dengan stok awal 15 Pcs'),
(26, NULL, 'BARANG DIHAPUS', -10, '2026-06-04 14:18:08', 1, 'Menghapus barang dari sistem: Sofa (Stok terakhir: 10 Pcs)');

-- --------------------------------------------------------

--
-- Table structure for table `supplier`
--

CREATE TABLE `supplier` (
  `id` int(11) NOT NULL,
  `nama_supplier` varchar(100) NOT NULL,
  `telepon` varchar(20) NOT NULL,
  `alamat` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `supplier`
--

INSERT INTO `supplier` (`id`, `nama_supplier`, `telepon`, `alamat`) VALUES
(1, 'PT. Maju Logistik', '08123456789', 'Jl. Industri No. 12, Jakarta'),
(2, 'CV. Sumber Berkah', '08571122334', 'Jl. Niaga Blok C, Bandung'),
(3, 'cv. barokah berjaya', '081356514410', 'Samarinda, ponpes al aziziyah');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `nama_lengkap` varchar(100) NOT NULL,
  `role` enum('admin','staf') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `nama_lengkap`, `role`) VALUES
(1, 'admin', 'admin123', 'Administrator Utama', 'admin'),
(2, 'staf', 'staf123', 'Staf Gudang', 'staf');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `barang`
--
ALTER TABLE `barang`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `kode_barang` (`kode_barang`),
  ADD KEY `id_kategori` (`id_kategori`),
  ADD KEY `id_user` (`id_user`);

--
-- Indexes for table `kategori`
--
ALTER TABLE `kategori`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nama_kategori` (`nama_kategori`);

--
-- Indexes for table `riwayat_stok`
--
ALTER TABLE `riwayat_stok`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_user` (`id_user`),
  ADD KEY `id_barang` (`id_barang`);

--
-- Indexes for table `supplier`
--
ALTER TABLE `supplier`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `barang`
--
ALTER TABLE `barang`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `kategori`
--
ALTER TABLE `kategori`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `riwayat_stok`
--
ALTER TABLE `riwayat_stok`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT for table `supplier`
--
ALTER TABLE `supplier`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `barang`
--
ALTER TABLE `barang`
  ADD CONSTRAINT `barang_ibfk_1` FOREIGN KEY (`id_kategori`) REFERENCES `kategori` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `barang_ibfk_2` FOREIGN KEY (`id_user`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `riwayat_stok`
--
ALTER TABLE `riwayat_stok`
  ADD CONSTRAINT `riwayat_stok_ibfk_1` FOREIGN KEY (`id_user`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `riwayat_stok_ibfk_2` FOREIGN KEY (`id_barang`) REFERENCES `barang` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
