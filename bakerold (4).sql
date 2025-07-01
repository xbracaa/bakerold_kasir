-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jul 01, 2025 at 04:09 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `bakerold`
--

DELIMITER $$
--
-- Procedures
--
CREATE DEFINER=`root`@`localhost` PROCEDURE `buat_transaksi_baru` (IN `p_id_kasir` INT, OUT `p_id_transaksi` INT)   BEGIN
  DECLARE new_kode VARCHAR(50);

  SET new_kode = CONCAT('TRX-', DATE_FORMAT(NOW(), '%Y%m%d%H%i%s'));

  INSERT INTO transaksi (kode_transaksi, id_kasir, total, bayar, kembalian)
  VALUES (new_kode, p_id_kasir, 0, 0, 0);

  SET p_id_transaksi = LAST_INSERT_ID();
END$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `detail_transaksi`
--

CREATE TABLE `detail_transaksi` (
  `id_detail` int(11) NOT NULL,
  `id_transaksi` int(11) NOT NULL,
  `id_produk` int(11) DEFAULT NULL,
  `qty` int(11) NOT NULL,
  `subtotal` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `detail_transaksi`
--

INSERT INTO `detail_transaksi` (`id_detail`, `id_transaksi`, `id_produk`, `qty`, `subtotal`) VALUES
(18, 25, 8, 1, 7000),
(19, 25, 9, 1, 8000),
(20, 25, 4, 1, 7000),
(21, 25, 11, 1, 0),
(22, 26, 4, 1, 0),
(23, 26, 2, 2, 160000),
(24, 26, 15, 1, 9000),
(25, 27, 6, 1, 8000),
(26, 27, 5, 1, 0),
(27, 28, 16, 1, 6500),
(28, 28, 11, 1, 7000),
(29, 28, 5, 1, 0),
(30, 29, 6, 1, 0);

-- --------------------------------------------------------

--
-- Table structure for table `kasir`
--

CREATE TABLE `kasir` (
  `id_kasir` int(11) NOT NULL,
  `nama_kasir` varchar(100) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `kasir`
--

INSERT INTO `kasir` (`id_kasir`, `nama_kasir`, `username`, `password`) VALUES
(1, 'kasir jayaraga garut', 'kasirjayaraga', 'admin123');

-- --------------------------------------------------------

--
-- Table structure for table `produk`
--

CREATE TABLE `produk` (
  `id_produk` int(11) NOT NULL,
  `kategori` varchar(100) DEFAULT NULL,
  `nama_produk` varchar(100) NOT NULL,
  `harga` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `produk`
--

INSERT INTO `produk` (`id_produk`, `kategori`, `nama_produk`, `harga`) VALUES
(1, 'makanan', 'Paket Berbagi Berkah 6 Roti', 45000),
(2, 'makanan', 'Paket Berbagi Berkah 12 Roti', 80000),
(3, 'makanan', 'Paket Berbagi Berkah 50 Roti', 300000),
(4, 'makanan', 'Roti Vanila', 7000),
(5, 'makanan', 'Roti Ori', 7000),
(6, 'makanan', 'Roti Keju', 8000),
(7, 'makanan', 'Roti Coklat', 8000),
(8, 'makanan', 'Roti Pandan Banana', 7000),
(9, 'makanan', 'Roti Pandan Coklat', 8000),
(10, 'makanan', 'Roti Pandan Butter', 7000),
(11, 'Dessert', 'Es Krim', 7000),
(12, 'dessert', 'Roti Es Krim', 12000),
(13, 'makanan', 'Paket Bundling Isi 3', 20000),
(14, 'packaging', 'Box Besar Isi 50', 20000),
(15, 'packaging', 'Box Besar Isi 12', 9000),
(16, 'Semua', 'Box Sedang Isi 6', 6500);

-- --------------------------------------------------------

--
-- Table structure for table `transaksi`
--

CREATE TABLE `transaksi` (
  `id_transaksi` int(11) NOT NULL,
  `kode_transaksi` varchar(50) NOT NULL,
  `id_kasir` int(11) DEFAULT NULL,
  `tanggal` datetime DEFAULT current_timestamp(),
  `total` int(11) DEFAULT NULL,
  `bayar` int(11) DEFAULT NULL,
  `kembalian` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `transaksi`
--

INSERT INTO `transaksi` (`id_transaksi`, `kode_transaksi`, `id_kasir`, `tanggal`, `total`, `bayar`, `kembalian`) VALUES
(1, 'TRX20250617015812', 1, '2025-06-17 06:58:12', 46000, 100000, 54000),
(2, 'TRX20250617020247', 1, '2025-06-17 07:02:47', 91000, 100000, 9000),
(3, 'TRX20250617020452', 1, '2025-06-17 07:04:52', 52000, 55000, 3000),
(4, 'TRX20250617020604', 1, '2025-06-17 07:06:04', 8000, 10000, 2000),
(5, 'TRX20250617020809', 1, '2025-06-17 07:08:09', 45000, 100000, 55000),
(6, 'TRX20250617021820', 1, '2025-06-17 07:18:20', 0, 1000, 1000),
(7, 'TRX-20250617030748', 1, '2025-06-17 08:07:48', 7000, 8000, 1000),
(8, 'TRX-20250617030914', 1, '2025-06-17 08:09:14', 7000, 8000, 1000),
(9, 'TRX-20250617030921', 1, '2025-06-17 08:09:21', 7000, 8000, 1000),
(10, 'TRX-20250617031104', 1, '2025-06-17 08:11:04', 7000, 8000, 1000),
(11, 'TRX20250617031116', 1, '2025-06-17 08:11:16', 7000, 8000, 1000),
(12, 'TRX20250617032059', 1, '2025-06-17 08:20:59', 300000, 300000, 0),
(13, 'TRX20250617034407', 1, '2025-06-17 08:44:07', 332000, 400000, 68000),
(14, 'TRX20250617034545', 1, '2025-06-17 08:45:45', 80000, 100000, 20000),
(15, 'TRX20250617035539', 1, '2025-06-17 08:55:39', 600000, 600000, 0),
(16, 'TRX20250617035646', 1, '2025-06-17 08:56:46', 24000, 25000, 1000),
(17, 'TRX20250701101525', 1, '2025-07-01 15:15:25', 88000, 90000, 2000),
(18, 'TRX20250701103115', 1, '2025-07-01 15:31:15', 8000, 8000, 0),
(19, 'TRX20250701111451', 1, '2025-07-01 16:14:51', 96000, 100000, 4000),
(20, 'TRX20250701113646', 1, '2025-07-01 16:36:46', 51500, 70000, 18500),
(21, 'TRX20250701123550', 1, '2025-07-01 17:35:50', 22000, 25000, 3000),
(22, 'TRX20250701123859', 1, '2025-07-01 17:38:59', 22000, 25000, 3000),
(23, 'TRX20250701124103', 1, '2025-07-01 17:41:03', 22000, 25000, 3000),
(24, 'TRX20250701124208', 1, '2025-07-01 17:42:08', 22000, 25000, 3000),
(25, 'TRX20250701124302', 1, '2025-07-01 17:43:02', 22000, 25000, 3000),
(26, 'TRX20250701130227', 1, '2025-07-01 18:02:27', 169000, 200000, 31000),
(27, 'TRX20250701131850', 1, '2025-07-01 18:18:50', 8000, 8000, 0),
(28, 'TRX20250701133744', 1, '2025-07-01 18:37:44', 13500, 15000, 1500),
(29, 'TRX20250701150018', 1, '2025-07-01 20:00:18', 0, 0, 0);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `detail_transaksi`
--
ALTER TABLE `detail_transaksi`
  ADD PRIMARY KEY (`id_detail`),
  ADD KEY `id_transaksi` (`id_transaksi`),
  ADD KEY `id_produk` (`id_produk`);

--
-- Indexes for table `kasir`
--
ALTER TABLE `kasir`
  ADD PRIMARY KEY (`id_kasir`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `produk`
--
ALTER TABLE `produk`
  ADD PRIMARY KEY (`id_produk`);

--
-- Indexes for table `transaksi`
--
ALTER TABLE `transaksi`
  ADD PRIMARY KEY (`id_transaksi`),
  ADD KEY `id_kasir` (`id_kasir`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `detail_transaksi`
--
ALTER TABLE `detail_transaksi`
  MODIFY `id_detail` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT for table `kasir`
--
ALTER TABLE `kasir`
  MODIFY `id_kasir` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `produk`
--
ALTER TABLE `produk`
  MODIFY `id_produk` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `transaksi`
--
ALTER TABLE `transaksi`
  MODIFY `id_transaksi` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `detail_transaksi`
--
ALTER TABLE `detail_transaksi`
  ADD CONSTRAINT `detail_transaksi_ibfk_1` FOREIGN KEY (`id_transaksi`) REFERENCES `transaksi` (`id_transaksi`),
  ADD CONSTRAINT `detail_transaksi_ibfk_2` FOREIGN KEY (`id_produk`) REFERENCES `produk` (`id_produk`);

--
-- Constraints for table `transaksi`
--
ALTER TABLE `transaksi`
  ADD CONSTRAINT `transaksi_ibfk_1` FOREIGN KEY (`id_kasir`) REFERENCES `kasir` (`id_kasir`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
