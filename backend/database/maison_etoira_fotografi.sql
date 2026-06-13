-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 13, 2026 at 12:27 PM
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
-- Database: `maison_etoira_fotografi`
--

-- --------------------------------------------------------

--
-- Table structure for table `booking_jasa`
--

CREATE TABLE `booking_jasa` (
  `id_booking` int(11) NOT NULL,
  `id_pelanggan` int(11) DEFAULT NULL,
  `id_fotografer` int(11) DEFAULT NULL,
  `id_paket` int(11) DEFAULT NULL,
  `tanggal_acara` date DEFAULT NULL,
  `lokasi_acara` varchar(200) DEFAULT NULL,
  `catatan` text DEFAULT NULL,
  `status_booking` enum('menunggu','menunggu_pembayaran','dikonfirmasi','selesai','dibatalkan') DEFAULT 'menunggu',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `booking_jasa`
--

INSERT INTO `booking_jasa` (`id_booking`, `id_pelanggan`, `id_fotografer`, `id_paket`, `tanggal_acara`, `lokasi_acara`, `catatan`, `status_booking`, `created_at`) VALUES
(1, 3, 1, 1, '2026-06-20', 'Surakarta', 'Acara wedding keluarga', 'menunggu', '2026-06-13 10:22:23');

-- --------------------------------------------------------

--
-- Table structure for table `fotografer`
--

CREATE TABLE `fotografer` (
  `id_fotografer` int(11) NOT NULL,
  `id_user` int(11) DEFAULT NULL,
  `nama_brand` varchar(100) DEFAULT NULL,
  `alamat` varchar(200) DEFAULT NULL,
  `deskripsi` text DEFAULT NULL,
  `pengalaman` varchar(100) DEFAULT NULL,
  `rating` decimal(2,1) DEFAULT 0.0,
  `status` enum('aktif','tidak aktif') DEFAULT 'aktif'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `fotografer`
--

INSERT INTO `fotografer` (`id_fotografer`, `id_user`, `nama_brand`, `alamat`, `deskripsi`, `pengalaman`, `rating`, `status`) VALUES
(1, 2, 'Nayla Putri', 'Surakarta', 'Spesialis Wedding, Prewedding dan Event Photography', '5 Tahun', 4.9, 'aktif');

-- --------------------------------------------------------

--
-- Table structure for table `jadwal_fotografer`
--

CREATE TABLE `jadwal_fotografer` (
  `id_jadwal` int(11) NOT NULL,
  `id_fotografer` int(11) DEFAULT NULL,
  `tanggal` date DEFAULT NULL,
  `jam` time DEFAULT NULL,
  `status` enum('tersedia','sudah_booking') DEFAULT 'tersedia'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `jadwal_fotografer`
--

INSERT INTO `jadwal_fotografer` (`id_jadwal`, `id_fotografer`, `tanggal`, `jam`, `status`) VALUES
(1, 1, '2026-06-20', '09:00:00', 'tersedia'),
(2, 1, '2026-06-25', '10:00:00', 'tersedia'),
(3, 1, '2026-06-30', '13:00:00', 'sudah_booking');

-- --------------------------------------------------------

--
-- Table structure for table `paket_fotografi`
--

CREATE TABLE `paket_fotografi` (
  `id_paket` int(11) NOT NULL,
  `id_fotografer` int(11) DEFAULT NULL,
  `nama_paket` varchar(100) DEFAULT NULL,
  `harga` int(11) DEFAULT NULL,
  `durasi` varchar(50) DEFAULT NULL,
  `jumlah_foto` int(11) DEFAULT NULL,
  `deskripsi` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `paket_fotografi`
--

INSERT INTO `paket_fotografi` (`id_paket`, `id_fotografer`, `nama_paket`, `harga`, `durasi`, `jumlah_foto`, `deskripsi`) VALUES
(1, 1, 'Wedding Premium', 5000000, '8 Jam', 300, 'Paket lengkap dokumentasi wedding dengan fotografer profesional.'),
(2, 1, 'Prewedding Elegant', 3000000, '4 Jam', 150, 'Foto prewedding dengan konsep outdoor maupun indoor.'),
(3, 1, 'Event Documentation', 2000000, '5 Jam', 200, 'Dokumentasi acara seperti seminar, ulang tahun, dan event lainnya.');

-- --------------------------------------------------------

--
-- Table structure for table `pembayaran`
--

CREATE TABLE `pembayaran` (
  `id_pembayaran` int(11) NOT NULL,
  `id_booking` int(11) DEFAULT NULL,
  `bukti_transfer` varchar(255) DEFAULT NULL,
  `tanggal_pembayaran` date DEFAULT NULL,
  `status_pembayaran` enum('menunggu','diterima','ditolak') DEFAULT 'menunggu'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pembayaran`
--

INSERT INTO `pembayaran` (`id_pembayaran`, `id_booking`, `bukti_transfer`, `tanggal_pembayaran`, `status_pembayaran`) VALUES
(1, 1, 'bukti_transfer_fani.jpg', '2026-06-14', 'menunggu');

-- --------------------------------------------------------

--
-- Table structure for table `portofolio`
--

CREATE TABLE `portofolio` (
  `id_portofolio` int(11) NOT NULL,
  `id_fotografer` int(11) DEFAULT NULL,
  `judul` varchar(100) DEFAULT NULL,
  `gambar` varchar(255) DEFAULT NULL,
  `deskripsi` text DEFAULT NULL,
  `tanggal_upload` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `portofolio`
--

INSERT INTO `portofolio` (`id_portofolio`, `id_fotografer`, `judul`, `gambar`, `deskripsi`, `tanggal_upload`) VALUES
(1, 1, 'Wedding Elegant Moment', 'wedding1.jpg', 'Dokumentasi pernikahan dengan konsep elegant dan timeless.', '2026-06-13 10:15:08'),
(2, 1, 'Prewedding Sunset', 'prewedding1.jpg', 'Sesi foto prewedding dengan suasana outdoor saat sunset.', '2026-06-13 10:15:08'),
(3, 1, 'Graduation Memories', 'graduation1.jpg', 'Dokumentasi wisuda dan momen spesial kelulusan.', '2026-06-13 10:15:08');

-- --------------------------------------------------------

--
-- Table structure for table `review_fotografer`
--

CREATE TABLE `review_fotografer` (
  `id_review` int(11) NOT NULL,
  `id_booking` int(11) DEFAULT NULL,
  `id_user` int(11) DEFAULT NULL,
  `rating` int(11) DEFAULT NULL,
  `komentar` text DEFAULT NULL,
  `tanggal_review` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `review_fotografer`
--

INSERT INTO `review_fotografer` (`id_review`, `id_booking`, `id_user`, `rating`, `komentar`, `tanggal_review`) VALUES
(1, 1, 3, 5, 'Pelayanan sangat baik, hasil foto sangat bagus dan profesional.', '2026-06-13 10:25:26');

-- --------------------------------------------------------

--
-- Table structure for table `status_pesanan`
--

CREATE TABLE `status_pesanan` (
  `id_status` int(11) NOT NULL,
  `id_booking` int(11) DEFAULT NULL,
  `status` varchar(50) DEFAULT NULL,
  `keterangan` text DEFAULT NULL,
  `waktu` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `status_pesanan`
--

INSERT INTO `status_pesanan` (`id_status`, `id_booking`, `status`, `keterangan`, `waktu`) VALUES
(1, 1, 'menunggu pembayaran', 'Menunggu pelanggan melakukan pembayaran.', '2026-06-13 10:24:26'),
(2, 1, 'pembayaran diterima', 'Pembayaran telah diverifikasi oleh admin.', '2026-06-13 10:24:26');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id_user` int(11) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `nomor_telepon` varchar(20) DEFAULT NULL,
  `role` enum('admin','fotografer','pelanggan') DEFAULT 'pelanggan',
  `foto_profile` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id_user`, `nama`, `email`, `password`, `nomor_telepon`, `role`, `foto_profile`, `created_at`) VALUES
(1, 'Admin Maison Étoira', 'yourpre4@gmail.com', 'admin19', '0895327348234', 'admin', NULL, '2026-06-13 10:06:18'),
(2, 'Nayla Putri', 'naylaanjani57077@gmail.com', '12345', '081226349094', 'fotografer', NULL, '2026-06-13 10:06:18'),
(3, 'Fani', 'steffani@gmail.com', '123456', '082136520167', 'pelanggan', NULL, '2026-06-13 10:06:18');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `booking_jasa`
--
ALTER TABLE `booking_jasa`
  ADD PRIMARY KEY (`id_booking`),
  ADD KEY `id_pelanggan` (`id_pelanggan`),
  ADD KEY `id_fotografer` (`id_fotografer`),
  ADD KEY `id_paket` (`id_paket`);

--
-- Indexes for table `fotografer`
--
ALTER TABLE `fotografer`
  ADD PRIMARY KEY (`id_fotografer`),
  ADD KEY `id_user` (`id_user`);

--
-- Indexes for table `jadwal_fotografer`
--
ALTER TABLE `jadwal_fotografer`
  ADD PRIMARY KEY (`id_jadwal`),
  ADD KEY `id_fotografer` (`id_fotografer`);

--
-- Indexes for table `paket_fotografi`
--
ALTER TABLE `paket_fotografi`
  ADD PRIMARY KEY (`id_paket`),
  ADD KEY `id_fotografer` (`id_fotografer`);

--
-- Indexes for table `pembayaran`
--
ALTER TABLE `pembayaran`
  ADD PRIMARY KEY (`id_pembayaran`),
  ADD KEY `id_booking` (`id_booking`);

--
-- Indexes for table `portofolio`
--
ALTER TABLE `portofolio`
  ADD PRIMARY KEY (`id_portofolio`),
  ADD KEY `id_fotografer` (`id_fotografer`);

--
-- Indexes for table `review_fotografer`
--
ALTER TABLE `review_fotografer`
  ADD PRIMARY KEY (`id_review`),
  ADD KEY `id_booking` (`id_booking`),
  ADD KEY `id_user` (`id_user`);

--
-- Indexes for table `status_pesanan`
--
ALTER TABLE `status_pesanan`
  ADD PRIMARY KEY (`id_status`),
  ADD KEY `id_booking` (`id_booking`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id_user`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `booking_jasa`
--
ALTER TABLE `booking_jasa`
  MODIFY `id_booking` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `fotografer`
--
ALTER TABLE `fotografer`
  MODIFY `id_fotografer` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `jadwal_fotografer`
--
ALTER TABLE `jadwal_fotografer`
  MODIFY `id_jadwal` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `paket_fotografi`
--
ALTER TABLE `paket_fotografi`
  MODIFY `id_paket` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `pembayaran`
--
ALTER TABLE `pembayaran`
  MODIFY `id_pembayaran` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `portofolio`
--
ALTER TABLE `portofolio`
  MODIFY `id_portofolio` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `review_fotografer`
--
ALTER TABLE `review_fotografer`
  MODIFY `id_review` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `status_pesanan`
--
ALTER TABLE `status_pesanan`
  MODIFY `id_status` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id_user` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `booking_jasa`
--
ALTER TABLE `booking_jasa`
  ADD CONSTRAINT `booking_jasa_ibfk_1` FOREIGN KEY (`id_pelanggan`) REFERENCES `users` (`id_user`),
  ADD CONSTRAINT `booking_jasa_ibfk_2` FOREIGN KEY (`id_fotografer`) REFERENCES `fotografer` (`id_fotografer`),
  ADD CONSTRAINT `booking_jasa_ibfk_3` FOREIGN KEY (`id_paket`) REFERENCES `paket_fotografi` (`id_paket`);

--
-- Constraints for table `fotografer`
--
ALTER TABLE `fotografer`
  ADD CONSTRAINT `fotografer_ibfk_1` FOREIGN KEY (`id_user`) REFERENCES `users` (`id_user`) ON DELETE CASCADE;

--
-- Constraints for table `jadwal_fotografer`
--
ALTER TABLE `jadwal_fotografer`
  ADD CONSTRAINT `jadwal_fotografer_ibfk_1` FOREIGN KEY (`id_fotografer`) REFERENCES `fotografer` (`id_fotografer`) ON DELETE CASCADE;

--
-- Constraints for table `paket_fotografi`
--
ALTER TABLE `paket_fotografi`
  ADD CONSTRAINT `paket_fotografi_ibfk_1` FOREIGN KEY (`id_fotografer`) REFERENCES `fotografer` (`id_fotografer`) ON DELETE CASCADE;

--
-- Constraints for table `pembayaran`
--
ALTER TABLE `pembayaran`
  ADD CONSTRAINT `pembayaran_ibfk_1` FOREIGN KEY (`id_booking`) REFERENCES `booking_jasa` (`id_booking`) ON DELETE CASCADE;

--
-- Constraints for table `portofolio`
--
ALTER TABLE `portofolio`
  ADD CONSTRAINT `portofolio_ibfk_1` FOREIGN KEY (`id_fotografer`) REFERENCES `fotografer` (`id_fotografer`) ON DELETE CASCADE;

--
-- Constraints for table `review_fotografer`
--
ALTER TABLE `review_fotografer`
  ADD CONSTRAINT `review_fotografer_ibfk_1` FOREIGN KEY (`id_booking`) REFERENCES `booking_jasa` (`id_booking`),
  ADD CONSTRAINT `review_fotografer_ibfk_2` FOREIGN KEY (`id_user`) REFERENCES `users` (`id_user`);

--
-- Constraints for table `status_pesanan`
--
ALTER TABLE `status_pesanan`
  ADD CONSTRAINT `status_pesanan_ibfk_1` FOREIGN KEY (`id_booking`) REFERENCES `booking_jasa` (`id_booking`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
