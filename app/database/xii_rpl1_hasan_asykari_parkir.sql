-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Oct 08, 2026 at 04:01 AM
-- Server version: 8.4.3
-- PHP Version: 8.5.5

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `db_parkir`
--

-- --------------------------------------------------------

--
-- Table structure for table `tb_area_parkir`
--

CREATE TABLE `tb_area_parkir` (
  `id_area` int NOT NULL,
  `nama_area` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `kapasitas` int NOT NULL,
  `peruntukan` enum('member','non_member','campuran') COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'campuran',
  `terisi` int DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tb_log_aktivitas`
--

CREATE TABLE `tb_log_aktivitas` (
  `id_log` int NOT NULL,
  `id_user` int NOT NULL,
  `aktivitas` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `waktu_aktivitas` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tb_member`
--

CREATE TABLE `tb_member` (
  `id_member` int NOT NULL,
  `nama_member` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `jenis_kelamin` enum('L','P') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `no_hp` varchar(15) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `tanggal_daftar` date NOT NULL,
  `status_aktif` enum('aktif','suspended') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT 'aktif',
  `id_user` int NOT NULL,
  `id_area` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tb_parkir`
--

CREATE TABLE `tb_parkir` (
  `id_parkir` int NOT NULL,
  `id_member` int DEFAULT NULL,
  `id_area` int DEFAULT NULL,
  `tipe_parkir` enum('member','non_member') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'member',
  `plat_nomor` varchar(15) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `warna_kendaraan` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `jenis_kendaraan` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `id_tarif` int DEFAULT NULL,
  `waktu_masuk` datetime NOT NULL,
  `id_user_masuk` int NOT NULL,
  `status` enum('masuk','keluar') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT 'masuk'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tb_tarif`
--

CREATE TABLE `tb_tarif` (
  `id_tarif` int NOT NULL,
  `jenis_kendaraan` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `tarif_per_jam` decimal(10,2) NOT NULL,
  `diskon_member` decimal(5,2) NOT NULL DEFAULT '0.00'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tb_transaksi`
--

CREATE TABLE `tb_transaksi` (
  `id_transaksi` int NOT NULL,
  `id_parkir` int DEFAULT NULL,
  `plat_nomor` varchar(15) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `warna_kendaraan` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `jenis_kendaraan_snapshot` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `tarif_per_jam_snapshot` decimal(10,2) NOT NULL,
  `tarif_dasar_snapshot` decimal(10,2) NOT NULL DEFAULT '0.00',
  `diskon_persen_snapshot` decimal(5,2) NOT NULL DEFAULT '0.00',
  `nama_member_snapshot` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `tipe_parkir_snapshot` enum('member','non_member') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'member',
  `nama_area_snapshot` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `nama_petugas_masuk_snapshot` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `waktu_keluar` datetime NOT NULL,
  `durasi_jam` int NOT NULL,
  `biaya_total` decimal(10,2) NOT NULL,
  `id_user_keluar` int DEFAULT NULL,
  `nama_petugas_keluar_snapshot` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `status` enum('lunas','pending') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT 'lunas'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tb_user`
--

CREATE TABLE `tb_user` (
  `id_user` int NOT NULL,
  `nama_lengkap` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `username` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `role` enum('admin','petugas','owner') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `status_aktif` tinyint(1) DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tb_user`
--

INSERT INTO `tb_user` (`id_user`, `nama_lengkap`, `username`, `password`, `role`, `status_aktif`) VALUES
(1, 'Admin Utama', 'admin', '$2b$10$Qvh1ny8mhsLH2mAf/h33/OEqajEZH9iHqU.rmxYSEvOZyPK.aTOUK', 'admin', 1),
(2, 'Petugas Satu', 'petugas1', '$2b$10$KxbzHjg5NwTZprlF0t8Z1OrfKbCbEL7um89csnAIstIQD4B1cqOOm', 'petugas', 1),
(3, 'Owner Utama', 'owner', '$2b$10$GU70TgwBQTnXIYIIC2E/weFm/LuDhdYm5T1hIqTG/Bh4TPVrRMgOi', 'owner', 1);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `tb_area_parkir`
--
ALTER TABLE `tb_area_parkir`
  ADD PRIMARY KEY (`id_area`),
  ADD UNIQUE KEY `uq_nama_area` (`nama_area`);

--
-- Indexes for table `tb_log_aktivitas`
--
ALTER TABLE `tb_log_aktivitas`
  ADD PRIMARY KEY (`id_log`),
  ADD KEY `id_user` (`id_user`);

--
-- Indexes for table `tb_member`
--
ALTER TABLE `tb_member`
  ADD PRIMARY KEY (`id_member`),
  ADD KEY `id_user` (`id_user`),
  ADD KEY `id_area` (`id_area`);

--
-- Indexes for table `tb_parkir`
--
ALTER TABLE `tb_parkir`
  ADD PRIMARY KEY (`id_parkir`),
  ADD KEY `id_member` (`id_member`),
  ADD KEY `id_tarif` (`id_tarif`),
  ADD KEY `id_user_masuk` (`id_user_masuk`),
  ADD KEY `tb_parkir_ibfk_4` (`id_area`);

--
-- Indexes for table `tb_tarif`
--
ALTER TABLE `tb_tarif`
  ADD PRIMARY KEY (`id_tarif`),
  ADD UNIQUE KEY `uq_jenis_kendaraan` (`jenis_kendaraan`);

--
-- Indexes for table `tb_transaksi`
--
ALTER TABLE `tb_transaksi`
  ADD PRIMARY KEY (`id_transaksi`),
  ADD KEY `id_parkir` (`id_parkir`),
  ADD KEY `id_user_keluar` (`id_user_keluar`),
  ADD KEY `waktu_keluar` (`waktu_keluar`);

--
-- Indexes for table `tb_user`
--
ALTER TABLE `tb_user`
  ADD PRIMARY KEY (`id_user`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `tb_area_parkir`
--
ALTER TABLE `tb_area_parkir`
  MODIFY `id_area` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tb_log_aktivitas`
--
ALTER TABLE `tb_log_aktivitas`
  MODIFY `id_log` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tb_member`
--
ALTER TABLE `tb_member`
  MODIFY `id_member` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tb_parkir`
--
ALTER TABLE `tb_parkir`
  MODIFY `id_parkir` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tb_tarif`
--
ALTER TABLE `tb_tarif`
  MODIFY `id_tarif` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tb_transaksi`
--
ALTER TABLE `tb_transaksi`
  MODIFY `id_transaksi` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tb_user`
--
ALTER TABLE `tb_user`
  MODIFY `id_user` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `tb_log_aktivitas`
--
ALTER TABLE `tb_log_aktivitas`
  ADD CONSTRAINT `tb_log_aktivitas_ibfk_1` FOREIGN KEY (`id_user`) REFERENCES `tb_user` (`id_user`) ON DELETE RESTRICT;

--
-- Constraints for table `tb_member`
--
ALTER TABLE `tb_member`
  ADD CONSTRAINT `tb_member_ibfk_1` FOREIGN KEY (`id_user`) REFERENCES `tb_user` (`id_user`) ON DELETE RESTRICT,
  ADD CONSTRAINT `tb_member_ibfk_2` FOREIGN KEY (`id_area`) REFERENCES `tb_area_parkir` (`id_area`) ON DELETE RESTRICT;

--
-- Constraints for table `tb_parkir`
--
ALTER TABLE `tb_parkir`
  ADD CONSTRAINT `tb_parkir_ibfk_1` FOREIGN KEY (`id_member`) REFERENCES `tb_member` (`id_member`) ON DELETE SET NULL,
  ADD CONSTRAINT `tb_parkir_ibfk_2` FOREIGN KEY (`id_tarif`) REFERENCES `tb_tarif` (`id_tarif`) ON DELETE SET NULL,
  ADD CONSTRAINT `tb_parkir_ibfk_3` FOREIGN KEY (`id_user_masuk`) REFERENCES `tb_user` (`id_user`) ON DELETE RESTRICT,
  ADD CONSTRAINT `tb_parkir_ibfk_4` FOREIGN KEY (`id_area`) REFERENCES `tb_area_parkir` (`id_area`) ON DELETE SET NULL;

--
-- Constraints for table `tb_transaksi`
--
ALTER TABLE `tb_transaksi`
  ADD CONSTRAINT `tb_transaksi_ibfk_1` FOREIGN KEY (`id_parkir`) REFERENCES `tb_parkir` (`id_parkir`) ON DELETE SET NULL,
  ADD CONSTRAINT `tb_transaksi_ibfk_2` FOREIGN KEY (`id_user_keluar`) REFERENCES `tb_user` (`id_user`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
