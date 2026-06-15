-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Jun 15, 2026 at 04:13 PM
-- Server version: 8.0.30
-- PHP Version: 8.1.10

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `desa_kutapohaci`
--

-- --------------------------------------------------------

--
-- Table structure for table `kategori`
--

CREATE TABLE `kategori` (
  `id_kategori` int NOT NULL,
  `nama_kategori` enum('Infrastruktur','Lingkungan','Keamanan','Kesehatan','Sosial','Pelayanan Publik') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `kategori`
--

INSERT INTO `kategori` (`id_kategori`, `nama_kategori`) VALUES
(1, 'Infrastruktur'),
(2, 'Lingkungan'),
(3, 'Keamanan'),
(4, 'Kesehatan'),
(5, 'Sosial'),
(6, 'Pelayanan Publik');

-- --------------------------------------------------------

--
-- Table structure for table `komentar`
--

CREATE TABLE `komentar` (
  `id_komentar` int NOT NULL,
  `id_laporan` int NOT NULL,
  `id_user` int NOT NULL,
  `isi_komentar` text NOT NULL,
  `tanggal` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `komentar`
--

INSERT INTO `komentar` (`id_komentar`, `id_laporan`, `id_user`, `isi_komentar`, `tanggal`) VALUES
(1, 1, 2, 'wow nice saran', '2026-05-28 06:39:49'),
(2, 1, 2, 'kelarr yee', '2026-05-28 06:40:21'),
(3, 3, 2, 'scam', '2026-05-28 11:39:08'),
(5, 5, 2, 'aneh lu', '2026-06-07 10:24:30');

-- --------------------------------------------------------

--
-- Table structure for table `laporan`
--

CREATE TABLE `laporan` (
  `id_laporan` int NOT NULL,
  `nomor_laporan` varchar(20) NOT NULL,
  `id_user` int NOT NULL,
  `id_kategori` int NOT NULL,
  `jenis_laporan` enum('Pengaduan','Aspirasi') NOT NULL,
  `judul` varchar(255) NOT NULL,
  `isi_laporan` text NOT NULL,
  `tanggal_kejadian` date DEFAULT NULL,
  `lokasi_kejadian` varchar(255) DEFAULT NULL,
  `prioritas` enum('Rendah','Sedang','Tinggi') DEFAULT 'Sedang',
  `status_laporan` enum('menunggu','diproses','selesai','ditolak') DEFAULT 'menunggu',
  `diproses_oleh` int DEFAULT NULL,
  `diproses_pada` timestamp NULL DEFAULT NULL,
  `selesai_oleh` int DEFAULT NULL,
  `selesai_pada` timestamp NULL DEFAULT NULL,
  `file_path` varchar(255) DEFAULT NULL,
  `tanggal_laporan` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `laporan`
--

INSERT INTO `laporan` (`id_laporan`, `nomor_laporan`, `id_user`, `id_kategori`, `jenis_laporan`, `judul`, `isi_laporan`, `tanggal_kejadian`, `lokasi_kejadian`, `prioritas`, `status_laporan`, `diproses_oleh`, `diproses_pada`, `selesai_oleh`, `selesai_pada`, `file_path`, `tanggal_laporan`, `updated_at`) VALUES
(1, 'LPK-2026-2572', 1, 6, 'Aspirasi', 'Peningkatan Keaktifan dan Pelayanan Perangkat Desa', 'Saya berharap perangkat desa dapat lebih aktif dalam melayani masyarakat, baik dalam memberikan informasi, menanggapi keluhan warga, maupun hadir dalam kegiatan desa. Selain itu, diharapkan pelayanan administrasi dapat dilakukan lebih cepat dan ramah agar masyarakat merasa lebih terbantu. Dengan meningkatnya keaktifan perangkat desa, komunikasi antara pemerintah desa dan warga juga akan menjadi lebih baik.', NULL, NULL, 'Sedang', 'selesai', 2, '2026-05-28 06:39:49', 2, '2026-05-28 06:40:21', NULL, '2026-05-26 01:14:59', '2026-05-28 06:40:21'),
(2, 'LPK-2026-0955', 1, 1, 'Aspirasi', 'Peningkatan Keaktifan dan Pelayanan Perangkat Desa', 'lorem ipsum lorem ipsum lorem ipsum lorem ipsum lorem ipsum lorem ipsum lorem ipsum lorem ipsum lorem ipsum lorem ipsum lorem ipsum', NULL, NULL, 'Rendah', 'selesai', 2, '2026-05-28 11:25:24', 2, '2026-05-28 11:25:48', NULL, '2026-05-28 11:24:46', '2026-05-28 11:25:48'),
(3, 'LPK-2026-1799', 1, 3, 'Pengaduan', 'semangkuk mie ayam sebelum mati', 'lorem ipsum lorem ipsum lorem ipsum lorem ipsum lorem ipsum lorem ipsum lorem ipsum', '2026-05-28', 'jl. kotapohachi', 'Sedang', 'ditolak', 2, '2026-05-28 11:39:08', NULL, NULL, 'assets/uploads/laporan/lpk_6a1829367bbd48.54347928.png', '2026-05-28 11:38:30', '2026-05-28 11:39:08'),
(5, 'LPK-2026-1534', 1, 1, 'Aspirasi', 'sikancil', 'kkjjghjgjhhjgjghjhghjgjhmnbnbmnmb', NULL, NULL, 'Sedang', 'ditolak', 2, '2026-06-07 10:24:30', NULL, NULL, NULL, '2026-06-07 09:53:01', '2026-06-07 10:24:30');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id_user` int NOT NULL,
  `nik` varchar(16) NOT NULL,
  `nama_lengkap` varchar(100) NOT NULL,
  `tempat_tinggal` varchar(255) NOT NULL,
  `tanggal_lahir` date NOT NULL,
  `jenis_kelamin` enum('L','P') NOT NULL,
  `no_telp` varchar(15) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `status_akun` enum('aktif','nonaktif') DEFAULT 'aktif',
  `role` enum('user','admin') DEFAULT 'user',
  `terdaftar_sejak` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id_user`, `nik`, `nama_lengkap`, `tempat_tinggal`, `tanggal_lahir`, `jenis_kelamin`, `no_telp`, `username`, `email`, `password`, `status_akun`, `role`, `terdaftar_sejak`) VALUES
(1, '1234567891234567', 'Cakramukti Hasibuan', 'Bekasi', '2004-08-11', 'L', '085960660485', 'Changse', 'cakramukti50@gmail.com', '$2y$10$inh8qeB7ifOBSNlAOzcUde1J4xxZmfpA3hWqI.tgoj3dhg1q5YP/2', 'aktif', 'user', '2026-05-25 12:26:28'),
(2, '3216549871234567', 'Budi Santoso', 'Kutapohaci', '1990-05-12', 'L', '081234567890', 'adminbudi', 'budiadmin@gmail.com', '$2y$10$9I1ymPsxnWGcKvvAxReBHuirjMGrEN89X/uFXD0OgUntjQtoUlBS.', 'aktif', 'admin', '2026-05-28 06:19:57'),
(17, '1234567891234569', 'Nafhan Haqiqi', 'Karawang', '2004-04-01', 'L', '0878734465465', 'hangod', 'nafhanhaqiqi144@gmail.com', '$2y$10$pbWWnHUzms2TAu58Nq9zXuSY0f18nm46y6/rFgSnYoN3bS46V/nZa', 'aktif', 'user', '2026-06-15 12:35:09');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `kategori`
--
ALTER TABLE `kategori`
  ADD PRIMARY KEY (`id_kategori`);

--
-- Indexes for table `komentar`
--
ALTER TABLE `komentar`
  ADD PRIMARY KEY (`id_komentar`),
  ADD KEY `id_laporan` (`id_laporan`),
  ADD KEY `id_user` (`id_user`);

--
-- Indexes for table `laporan`
--
ALTER TABLE `laporan`
  ADD PRIMARY KEY (`id_laporan`),
  ADD UNIQUE KEY `nomor_laporan` (`nomor_laporan`),
  ADD KEY `id_user` (`id_user`),
  ADD KEY `id_kategori` (`id_kategori`),
  ADD KEY `fk_diproses_oleh` (`diproses_oleh`),
  ADD KEY `fk_selesai_oleh` (`selesai_oleh`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id_user`),
  ADD UNIQUE KEY `nik` (`nik`),
  ADD UNIQUE KEY `no_telp` (`no_telp`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `kategori`
--
ALTER TABLE `kategori`
  MODIFY `id_kategori` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `komentar`
--
ALTER TABLE `komentar`
  MODIFY `id_komentar` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `laporan`
--
ALTER TABLE `laporan`
  MODIFY `id_laporan` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id_user` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `komentar`
--
ALTER TABLE `komentar`
  ADD CONSTRAINT `komentar_ibfk_1` FOREIGN KEY (`id_laporan`) REFERENCES `laporan` (`id_laporan`) ON DELETE CASCADE,
  ADD CONSTRAINT `komentar_ibfk_2` FOREIGN KEY (`id_user`) REFERENCES `users` (`id_user`) ON DELETE CASCADE;

--
-- Constraints for table `laporan`
--
ALTER TABLE `laporan`
  ADD CONSTRAINT `fk_diproses_oleh` FOREIGN KEY (`diproses_oleh`) REFERENCES `users` (`id_user`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_selesai_oleh` FOREIGN KEY (`selesai_oleh`) REFERENCES `users` (`id_user`) ON DELETE SET NULL,
  ADD CONSTRAINT `laporan_ibfk_1` FOREIGN KEY (`id_user`) REFERENCES `users` (`id_user`) ON DELETE CASCADE,
  ADD CONSTRAINT `laporan_ibfk_2` FOREIGN KEY (`id_kategori`) REFERENCES `kategori` (`id_kategori`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
