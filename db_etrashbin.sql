-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Oct 30, 2025 at 09:29 AM
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
-- Database: `db_etrashbin`
--

-- --------------------------------------------------------

--
-- Table structure for table `tb_device`
--

CREATE TABLE `tb_device` (
  `device_id` int(11) NOT NULL,
  `device_name` varchar(50) NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 0 COMMENT '1=online,0=offline'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tb_device`
--

INSERT INTO `tb_device` (`device_id`, `device_name`, `status`) VALUES
(5, 'XI TKJ 3', 0),
(6, 'XI TKJ 2', 0),
(7, 'XI ULW 2', 0),
(8, 'XI TKJ 6', 0);

--
-- Triggers `tb_device`
--
DELIMITER $$
CREATE TRIGGER `trigger_device_id` AFTER INSERT ON `tb_device` FOR EACH ROW BEGIN
    INSERT INTO `tb_device_status` (
        `device_id`, 
        `wifi_signal`, 
        `last_update`,
        `sensor_cam`,
        `sensor_ultrasonic`,
        `sensor_proximity`,
        `servo`,
        `lcd`,
        `kapasitas_organik`,
        `kapasitas_anorganik`,
        `kapasitas_logam`,
        `sorting_today`
    ) VALUES (
        NEW.device_id,
        0,
        NOW(),
        0, 0, 0, 0, 0, 0, 0, 0, 0
    );
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `trigger_status_info` AFTER UPDATE ON `tb_device` FOR EACH ROW BEGIN
-- JIKA SEBELUMNYA STATUSNYA ONLINE DAN MEJADI OFFLINE
IF NEW.status = 1 AND OLD.status = 0 THEN INSERT INTO tb_history(device_id,message) VALUES(NEW.device_id,CONCAT('Perangkat Aktif!'));
END IF;

-- JIKA TERJADI PERUBAHAN DARI OFFLINE KE ONLINE
IF NEW.status = 0 AND OLD.status = 1 THEN INSERT INTO tb_history(device_id,message) VALUES(NEW.device_id,CONCAT('Perangkat Mati!'));
END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `tb_device_status`
--

CREATE TABLE `tb_device_status` (
  `id` int(11) NOT NULL,
  `device_id` int(11) NOT NULL,
  `wifi_signal` int(11) NOT NULL DEFAULT 0,
  `last_update` datetime NOT NULL,
  `sensor_cam` tinyint(1) NOT NULL DEFAULT 0 COMMENT '1=online,0=offline',
  `sensor_ultrasonic` tinyint(1) NOT NULL DEFAULT 0 COMMENT '1=online,0=offline',
  `sensor_proximity` tinyint(1) NOT NULL DEFAULT 0 COMMENT '1=online,0=offline',
  `servo` tinyint(1) NOT NULL DEFAULT 0 COMMENT '1=online,0=offline',
  `lcd` tinyint(1) NOT NULL DEFAULT 0 COMMENT '1=online,0=offline',
  `kapasitas_organik` int(11) NOT NULL DEFAULT 0,
  `kapasitas_anorganik` int(11) NOT NULL DEFAULT 0,
  `kapasitas_logam` int(11) NOT NULL DEFAULT 0,
  `sorting_today` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tb_device_status`
--

INSERT INTO `tb_device_status` (`id`, `device_id`, `wifi_signal`, `last_update`, `sensor_cam`, `sensor_ultrasonic`, `sensor_proximity`, `servo`, `lcd`, `kapasitas_organik`, `kapasitas_anorganik`, `kapasitas_logam`, `sorting_today`) VALUES
(7, 5, 0, '2025-10-30 15:27:55', 0, 0, 0, 0, 0, 87, 56, 90, 32),
(8, 6, 0, '2025-10-28 13:15:03', 0, 0, 0, 0, 0, 0, 0, 0, 0),
(9, 7, 0, '2025-10-28 13:15:12', 0, 0, 0, 0, 0, 0, 0, 0, 78),
(10, 8, 0, '2025-10-28 13:15:20', 0, 0, 0, 0, 0, 0, 0, 0, 0);

--
-- Triggers `tb_device_status`
--
DELIMITER $$
CREATE TRIGGER `trigger_kapasitas_warning` AFTER UPDATE ON `tb_device_status` FOR EACH ROW BEGIN
  DECLARE msg VARCHAR(255);
  -- Organik
  IF NEW.kapasitas_organik IS NOT NULL 
     AND NEW.kapasitas_organik >= 80 
     AND (OLD.kapasitas_organik IS NULL OR OLD.kapasitas_organik < 80) THEN

     SET msg = CONCAT('Kapasitas organik mencapai ', NEW.kapasitas_organik, '%');

     -- Cegah duplikat: cek apakah pesan yang sama sudah dibuat dalam 2 menit terakhir
     IF NOT EXISTS(
        SELECT 1 FROM tb_history h 
        WHERE h.device_id = NEW.device_id 
          AND h.message = msg 
          AND h.created_at >= NOW() - INTERVAL 2 MINUTE
     ) THEN
        INSERT INTO tb_history(device_id, message) VALUES (NEW.device_id, msg);
     END IF;
  END IF;

  -- Anorganik
  IF NEW.kapasitas_anorganik IS NOT NULL 
     AND NEW.kapasitas_anorganik >= 80 
     AND (OLD.kapasitas_anorganik IS NULL OR OLD.kapasitas_anorganik < 80) THEN

     SET msg = CONCAT('Kapasitas anorganik mencapai ', NEW.kapasitas_anorganik, '%');

     IF NOT EXISTS(
        SELECT 1 FROM tb_history h 
        WHERE h.device_id = NEW.device_id 
          AND h.message = msg 
          AND h.created_at >= NOW() - INTERVAL 2 MINUTE
     ) THEN
        INSERT INTO tb_history(device_id, message) VALUES (NEW.device_id, msg);
     END IF;
  END IF;

  -- Logam
  IF NEW.kapasitas_logam IS NOT NULL 
     AND NEW.kapasitas_logam >= 80 
     AND (OLD.kapasitas_logam IS NULL OR OLD.kapasitas_logam < 80) THEN

     SET msg = CONCAT('Kapasitas logam mencapai ', NEW.kapasitas_logam, '%');

     IF NOT EXISTS(
        SELECT 1 FROM tb_history h 
        WHERE h.device_id = NEW.device_id 
          AND h.message = msg 
          AND h.created_at >= NOW() - INTERVAL 2 MINUTE
     ) THEN
        INSERT INTO tb_history(device_id, message) VALUES (NEW.device_id, msg);
     END IF;
  END IF;

END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `tb_history`
--

CREATE TABLE `tb_history` (
  `id` int(11) NOT NULL,
  `device_id` int(11) NOT NULL,
  `message` text NOT NULL COMMENT 'Pesan history',
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tb_history`
--

INSERT INTO `tb_history` (`id`, `device_id`, `message`, `created_at`) VALUES
(46, 5, 'Perangkat Aktif!', '2025-10-30 15:27:55'),
(47, 5, 'Perangkat Mati!', '2025-10-30 15:28:58');

-- --------------------------------------------------------

--
-- Table structure for table `tb_sorting_history`
--

CREATE TABLE `tb_sorting_history` (
  `id` int(11) NOT NULL,
  `device_id` int(11) NOT NULL,
  `jenis_sampah` enum('logam','organik','anorganik') NOT NULL,
  `jumlah` int(11) NOT NULL DEFAULT 1,
  `tanggal` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tb_sorting_history`
--

INSERT INTO `tb_sorting_history` (`id`, `device_id`, `jenis_sampah`, `jumlah`, `tanggal`) VALUES
(109, 5, 'organik', 13, '2025-10-29 15:30:10'),
(110, 5, 'logam', 9, '2025-10-29 15:31:39'),
(111, 5, 'anorganik', 16, '2025-10-29 15:31:39'),
(112, 5, 'organik', 44, '2025-01-15 15:34:34'),
(113, 5, 'anorganik', 32, '2025-01-14 15:34:34'),
(114, 5, 'anorganik', 1, '2025-10-30 07:56:01'),
(115, 5, 'anorganik', 1, '2025-10-30 07:56:13'),
(116, 5, 'logam', 1, '2025-10-30 07:57:28'),
(117, 5, 'logam', 1, '2025-10-30 07:57:35'),
(118, 5, 'logam', 1, '2025-10-30 07:57:37'),
(119, 5, 'logam', 1, '2025-10-30 07:57:37'),
(120, 5, 'logam', 1, '2025-10-30 07:57:38'),
(121, 5, 'logam', 1, '2025-10-30 07:57:38'),
(122, 5, 'organik', 1, '2025-10-30 07:57:50'),
(123, 5, 'organik', 1, '2025-10-30 07:57:52'),
(124, 5, 'organik', 1, '2025-10-30 07:57:53'),
(125, 5, 'organik', 1, '2025-10-30 07:57:54'),
(126, 5, 'organik', 1, '2025-10-30 07:57:54'),
(127, 5, 'organik', 1, '2025-10-30 07:57:55'),
(128, 5, 'organik', 1, '2025-10-30 07:57:56'),
(129, 5, 'organik', 1, '2025-10-30 07:57:56'),
(130, 5, 'organik', 1, '2025-10-30 07:57:57'),
(131, 5, 'organik', 1, '2025-10-30 07:57:57'),
(132, 5, 'organik', 1, '2025-10-30 07:57:58'),
(133, 5, 'anorganik', 1, '2025-10-30 08:00:44'),
(134, 5, 'anorganik', 1, '2025-10-30 08:00:50'),
(135, 5, 'anorganik', 1, '2025-10-30 08:00:52'),
(136, 5, 'anorganik', 1, '2025-10-30 08:01:02'),
(137, 5, 'anorganik', 1, '2025-10-30 08:01:03'),
(138, 5, 'anorganik', 1, '2025-10-30 08:01:04'),
(139, 5, 'organik', 1, '2025-10-30 08:26:30'),
(140, 5, 'organik', 1, '2025-10-30 08:26:32'),
(141, 5, 'organik', 0, '2025-10-30 08:26:32'),
(142, 5, 'organik', 32, '2025-02-11 08:29:02'),
(143, 5, 'anorganik', 25, '2025-03-30 08:29:07'),
(144, 5, 'logam', 15, '2025-04-30 08:29:07'),
(145, 5, 'organik', 41, '2025-05-30 08:29:07'),
(146, 5, 'anorganik', 27, '2025-06-30 08:29:07'),
(147, 5, 'anorganik', 30, '2025-07-30 08:29:07'),
(148, 5, 'anorganik', 18, '2025-08-30 08:29:07'),
(149, 5, 'anorganik', 60, '2025-09-30 08:29:07');

-- --------------------------------------------------------

--
-- Table structure for table `tb_user`
--

CREATE TABLE `tb_user` (
  `id_user` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tb_user`
--

INSERT INTO `tb_user` (`id_user`, `username`, `password`) VALUES
(1, 'petugas_1', 'petugas_1'),
(2, 'petugas_2', 'petugas_2'),
(8, 'petugas_3', 'PETUGAS_3');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `tb_device`
--
ALTER TABLE `tb_device`
  ADD PRIMARY KEY (`device_id`);

--
-- Indexes for table `tb_device_status`
--
ALTER TABLE `tb_device_status`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_device_id` (`device_id`);

--
-- Indexes for table `tb_history`
--
ALTER TABLE `tb_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_history_device` (`device_id`),
  ADD KEY `idx_device_date` (`device_id`,`created_at`);

--
-- Indexes for table `tb_sorting_history`
--
ALTER TABLE `tb_sorting_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_sorting_device` (`device_id`);

--
-- Indexes for table `tb_user`
--
ALTER TABLE `tb_user`
  ADD PRIMARY KEY (`id_user`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `tb_device`
--
ALTER TABLE `tb_device`
  MODIFY `device_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `tb_device_status`
--
ALTER TABLE `tb_device_status`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `tb_history`
--
ALTER TABLE `tb_history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=48;

--
-- AUTO_INCREMENT for table `tb_sorting_history`
--
ALTER TABLE `tb_sorting_history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=150;

--
-- AUTO_INCREMENT for table `tb_user`
--
ALTER TABLE `tb_user`
  MODIFY `id_user` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `tb_device_status`
--
ALTER TABLE `tb_device_status`
  ADD CONSTRAINT `fk_device_status_device` FOREIGN KEY (`device_id`) REFERENCES `tb_device` (`device_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `tb_history`
--
ALTER TABLE `tb_history`
  ADD CONSTRAINT `fk_history_device` FOREIGN KEY (`device_id`) REFERENCES `tb_device` (`device_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `tb_sorting_history`
--
ALTER TABLE `tb_sorting_history`
  ADD CONSTRAINT `fk_sorting_device` FOREIGN KEY (`device_id`) REFERENCES `tb_device` (`device_id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
