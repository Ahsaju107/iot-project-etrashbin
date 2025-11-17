-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 17, 2025 at 02:47 AM
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
(11, 'XI TKJ 1', 0);

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
(7, 5, 0, '2025-11-15 10:20:46', 0, 0, 0, 0, 0, 41, 38, 5, 447),
(8, 6, 30, '2025-10-31 20:21:17', 0, 0, 0, 0, 0, 52, 44, 10, 0),
(13, 11, 0, '2025-10-31 20:34:03', 0, 0, 0, 0, 0, 0, 0, 0, 0);

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
(83, 6, 'Perangkat Mati!', '2025-10-31 20:12:33'),
(86, 6, 'Perangkat Aktif!', '2025-10-31 20:21:17'),
(89, 6, 'Perangkat Mati!', '2025-10-31 20:25:38');

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
(149, 5, 'anorganik', 60, '2025-09-30 08:29:07'),
(150, 5, 'organik', 1, '2025-10-31 18:08:04'),
(151, 5, 'organik', 1, '2025-10-31 18:08:13'),
(152, 5, 'anorganik', 1, '2025-10-31 18:08:26'),
(153, 5, 'anorganik', 1, '2025-10-31 18:08:40'),
(154, 5, 'organik', 1, '2025-10-31 18:08:50'),
(155, 5, 'logam', 1, '2025-10-31 18:08:59'),
(156, 5, 'organik', 1, '2025-11-03 15:51:58'),
(157, 5, 'organik', 1, '2025-11-03 15:52:21'),
(158, 5, 'organik', 1, '2025-11-03 15:52:22'),
(159, 5, 'organik', 1, '2025-11-03 15:52:23'),
(160, 5, 'organik', 1, '2025-11-03 15:52:23'),
(161, 5, 'organik', 1, '2025-11-03 15:52:24'),
(162, 5, 'organik', 1, '2025-11-03 15:52:24'),
(163, 5, 'organik', 1, '2025-11-03 15:52:24'),
(164, 5, 'organik', 1, '2025-11-03 15:52:25'),
(165, 5, 'anorganik', 1, '2025-11-03 15:52:29'),
(166, 5, 'anorganik', 1, '2025-11-03 15:52:29'),
(167, 5, 'anorganik', 1, '2025-11-03 15:52:30'),
(168, 5, 'anorganik', 1, '2025-11-03 15:52:30'),
(169, 5, 'anorganik', 1, '2025-11-03 15:52:31'),
(170, 5, 'anorganik', 1, '2025-11-03 15:52:31'),
(171, 5, 'anorganik', 1, '2025-11-03 15:52:31'),
(172, 5, 'anorganik', 1, '2025-11-03 15:52:32'),
(173, 5, 'anorganik', 1, '2025-11-03 15:52:32'),
(174, 5, 'anorganik', 1, '2025-11-03 15:52:33'),
(175, 5, 'anorganik', 1, '2025-11-03 15:52:33'),
(176, 5, 'logam', 1, '2025-11-03 15:52:37'),
(177, 5, 'logam', 1, '2025-11-03 15:52:38'),
(178, 5, 'logam', 1, '2025-11-03 15:52:38'),
(179, 5, 'logam', 1, '2025-11-03 15:52:39'),
(180, 5, 'logam', 1, '2025-11-03 15:52:39'),
(181, 5, 'logam', 1, '2025-11-03 15:52:40'),
(182, 5, 'logam', 1, '2025-11-03 15:52:42'),
(183, 5, 'logam', 1, '2025-11-10 15:57:31'),
(184, 5, 'logam', 1, '2025-11-10 15:59:35'),
(185, 5, 'logam', 1, '2025-11-10 16:00:15'),
(186, 5, 'logam', 1, '2025-11-10 16:08:59'),
(187, 5, 'logam', 1, '2025-11-10 16:12:10'),
(188, 5, 'logam', 1, '2025-11-10 16:24:49'),
(189, 5, 'logam', 1, '2025-11-10 16:24:52'),
(190, 5, 'logam', 1, '2025-11-10 16:24:55'),
(191, 5, 'logam', 1, '2025-11-10 16:24:58'),
(192, 5, 'logam', 1, '2025-11-10 16:25:01'),
(193, 5, 'logam', 1, '2025-11-10 16:25:04'),
(194, 5, 'logam', 1, '2025-11-10 16:25:07'),
(195, 5, 'logam', 1, '2025-11-10 16:25:10'),
(196, 5, 'logam', 1, '2025-11-10 16:25:13'),
(197, 5, 'logam', 1, '2025-11-10 16:25:16'),
(198, 5, 'logam', 1, '2025-11-10 16:25:24'),
(199, 5, 'logam', 1, '2025-11-10 16:25:27'),
(200, 5, 'logam', 1, '2025-11-10 16:25:30'),
(201, 5, 'logam', 1, '2025-11-10 16:25:33'),
(202, 5, 'logam', 1, '2025-11-10 16:25:36'),
(203, 5, 'logam', 1, '2025-11-10 16:25:39'),
(204, 5, 'logam', 1, '2025-11-10 16:25:42'),
(205, 5, 'logam', 1, '2025-11-10 16:25:45'),
(206, 5, 'logam', 1, '2025-11-10 16:25:48'),
(207, 5, 'logam', 1, '2025-11-10 16:25:52'),
(208, 5, 'logam', 1, '2025-11-10 16:26:00'),
(209, 5, 'logam', 1, '2025-11-10 16:26:03'),
(210, 5, 'logam', 1, '2025-11-10 16:26:06'),
(211, 5, 'logam', 1, '2025-11-10 16:26:25'),
(212, 5, 'logam', 1, '2025-11-10 16:26:29'),
(213, 5, 'logam', 1, '2025-11-10 16:26:34'),
(214, 5, 'logam', 1, '2025-11-10 16:26:41'),
(215, 5, 'logam', 1, '2025-11-10 16:26:44'),
(216, 5, 'logam', 1, '2025-11-10 16:26:47'),
(217, 5, 'logam', 1, '2025-11-10 16:26:50'),
(218, 5, 'logam', 1, '2025-11-10 16:26:53'),
(219, 5, 'logam', 1, '2025-11-10 16:26:56'),
(220, 5, 'logam', 1, '2025-11-10 16:26:59'),
(221, 5, 'logam', 1, '2025-11-10 16:27:02'),
(222, 5, 'logam', 1, '2025-11-10 16:27:05'),
(223, 5, 'logam', 1, '2025-11-10 16:27:08'),
(224, 5, 'logam', 1, '2025-11-10 16:27:17'),
(225, 5, 'logam', 1, '2025-11-10 16:27:20'),
(226, 5, 'logam', 1, '2025-11-10 16:27:22'),
(227, 5, 'logam', 1, '2025-11-10 16:27:26'),
(228, 5, 'logam', 1, '2025-11-10 16:27:28'),
(229, 5, 'logam', 1, '2025-11-10 16:27:31'),
(230, 5, 'logam', 1, '2025-11-10 16:27:34'),
(231, 5, 'logam', 1, '2025-11-10 16:27:37'),
(232, 5, 'logam', 1, '2025-11-10 16:27:40'),
(233, 5, 'logam', 1, '2025-11-10 16:27:43'),
(234, 5, 'logam', 1, '2025-11-10 16:28:43'),
(235, 5, 'logam', 1, '2025-11-10 16:28:51'),
(236, 5, 'logam', 1, '2025-11-10 16:28:54'),
(237, 5, 'logam', 1, '2025-11-10 16:29:25'),
(238, 5, 'logam', 1, '2025-11-10 16:30:01'),
(239, 5, 'logam', 1, '2025-11-10 16:30:38'),
(240, 5, 'logam', 1, '2025-11-10 16:30:46'),
(241, 5, 'logam', 1, '2025-11-10 16:31:16'),
(242, 5, 'logam', 1, '2025-11-10 16:31:24'),
(243, 5, 'logam', 1, '2025-11-10 16:31:55'),
(244, 5, 'logam', 1, '2025-11-10 16:32:03'),
(245, 5, 'logam', 1, '2025-11-10 16:32:06'),
(246, 5, 'logam', 1, '2025-11-10 16:32:37'),
(247, 5, 'logam', 1, '2025-11-10 16:33:42'),
(248, 5, 'logam', 1, '2025-11-10 16:34:18'),
(249, 5, 'logam', 1, '2025-11-10 16:34:54'),
(250, 5, 'logam', 1, '2025-11-10 16:35:02'),
(251, 5, 'logam', 1, '2025-11-10 16:36:02'),
(252, 5, 'logam', 1, '2025-11-10 16:36:04'),
(253, 5, 'logam', 1, '2025-11-10 16:36:08'),
(254, 5, 'logam', 1, '2025-11-10 16:36:13'),
(255, 5, 'logam', 1, '2025-11-10 16:36:28'),
(256, 5, 'logam', 1, '2025-11-11 10:01:41'),
(257, 5, 'logam', 1, '2025-11-11 10:01:49'),
(258, 5, 'logam', 1, '2025-11-11 10:02:21'),
(259, 5, 'logam', 1, '2025-11-11 10:02:56'),
(260, 5, 'anorganik', 1, '2025-11-11 10:03:11'),
(261, 5, 'anorganik', 1, '2025-11-11 10:03:20'),
(262, 5, 'anorganik', 1, '2025-11-11 10:03:37'),
(263, 5, 'organik', 1, '2025-11-11 10:23:46'),
(264, 5, 'organik', 1, '2025-11-11 10:23:50'),
(265, 5, 'organik', 1, '2025-11-11 10:23:54'),
(266, 5, 'anorganik', 1, '2025-11-11 10:23:58'),
(267, 5, 'organik', 1, '2025-11-11 10:44:03'),
(268, 5, 'organik', 1, '2025-11-11 10:44:07'),
(269, 5, 'anorganik', 1, '2025-11-11 10:44:26'),
(270, 5, 'anorganik', 1, '2025-11-11 10:47:38'),
(271, 5, 'logam', 1, '2025-11-11 10:47:45'),
(272, 5, 'organik', 1, '2025-11-11 10:47:57'),
(273, 5, 'anorganik', 1, '2025-11-11 10:52:07'),
(274, 5, 'anorganik', 1, '2025-11-11 10:55:03'),
(275, 5, 'anorganik', 1, '2025-11-11 10:56:57'),
(276, 5, 'organik', 1, '2025-11-11 10:57:22'),
(277, 5, 'anorganik', 1, '2025-11-11 10:57:27'),
(278, 5, 'anorganik', 1, '2025-11-11 11:14:24'),
(279, 5, 'anorganik', 1, '2025-11-11 11:14:48'),
(280, 5, 'anorganik', 1, '2025-11-11 11:15:00'),
(281, 5, 'anorganik', 1, '2025-11-11 11:15:13'),
(282, 5, 'logam', 1, '2025-11-11 11:15:30'),
(283, 5, 'anorganik', 1, '2025-11-11 11:16:06'),
(284, 5, 'anorganik', 1, '2025-11-11 11:16:24'),
(285, 5, 'anorganik', 1, '2025-11-11 11:20:13'),
(286, 5, 'anorganik', 1, '2025-11-11 11:21:25'),
(287, 5, 'anorganik', 1, '2025-11-11 11:21:48'),
(288, 5, 'anorganik', 1, '2025-11-11 11:22:01'),
(289, 5, 'logam', 1, '2025-11-11 11:22:25'),
(290, 5, 'anorganik', 1, '2025-11-11 11:22:39'),
(291, 5, 'anorganik', 1, '2025-11-11 11:23:28'),
(292, 5, 'anorganik', 1, '2025-11-11 11:24:22'),
(293, 5, 'anorganik', 1, '2025-11-11 11:24:34'),
(294, 5, 'anorganik', 1, '2025-11-11 11:24:47'),
(295, 5, 'anorganik', 1, '2025-11-11 11:24:59'),
(296, 5, 'anorganik', 1, '2025-11-11 13:30:02'),
(297, 5, 'logam', 1, '2025-11-11 13:30:12'),
(298, 5, 'anorganik', 1, '2025-11-11 13:30:32'),
(299, 5, 'anorganik', 1, '2025-11-11 13:30:56'),
(300, 5, 'anorganik', 1, '2025-11-11 13:31:45'),
(301, 5, 'anorganik', 1, '2025-11-11 13:32:40'),
(302, 5, 'anorganik', 1, '2025-11-11 13:33:23'),
(303, 5, 'anorganik', 1, '2025-11-11 13:34:07'),
(304, 5, 'anorganik', 1, '2025-11-11 13:34:20'),
(305, 5, 'organik', 1, '2025-11-13 11:55:52'),
(306, 5, 'organik', 1, '2025-11-13 11:55:54'),
(307, 5, 'organik', 1, '2025-11-13 11:55:55'),
(308, 5, 'organik', 1, '2025-11-13 11:55:56'),
(309, 5, 'organik', 1, '2025-11-13 11:55:56'),
(310, 5, 'organik', 1, '2025-11-13 11:55:57'),
(311, 5, 'organik', 1, '2025-11-13 11:55:57'),
(312, 5, 'organik', 1, '2025-11-13 11:55:58'),
(313, 5, 'organik', 1, '2025-11-13 11:56:07'),
(314, 5, 'anorganik', 1, '2025-11-13 11:56:17'),
(315, 5, 'anorganik', 1, '2025-11-13 11:56:18'),
(316, 5, 'anorganik', 1, '2025-11-13 11:56:19'),
(317, 5, 'anorganik', 1, '2025-11-13 11:56:19'),
(318, 5, 'anorganik', 1, '2025-11-13 11:56:20'),
(319, 5, 'anorganik', 1, '2025-11-13 11:56:21'),
(320, 5, 'anorganik', 1, '2025-11-13 11:56:21'),
(321, 5, 'anorganik', 1, '2025-11-13 11:56:22'),
(322, 5, 'anorganik', 1, '2025-11-13 11:56:22'),
(323, 5, 'anorganik', 1, '2025-11-13 11:56:23'),
(324, 5, 'anorganik', 1, '2025-11-13 11:56:23'),
(325, 5, 'anorganik', 1, '2025-11-13 11:56:24'),
(326, 5, 'anorganik', 1, '2025-11-13 11:56:24'),
(327, 5, 'anorganik', 1, '2025-11-13 11:56:25'),
(328, 5, 'anorganik', 1, '2025-11-13 11:56:26'),
(329, 5, 'logam', 1, '2025-11-13 11:56:38'),
(330, 5, 'logam', 1, '2025-11-13 11:56:39'),
(331, 5, 'logam', 1, '2025-11-13 11:56:40'),
(332, 5, 'logam', 1, '2025-11-13 11:56:41'),
(333, 5, 'logam', 1, '2025-11-13 11:56:41'),
(334, 5, 'logam', 1, '2025-11-13 11:56:42'),
(335, 5, 'logam', 1, '2025-11-13 11:56:42'),
(336, 5, 'anorganik', 1, '2025-11-15 04:32:52'),
(337, 5, 'anorganik', 1, '2025-11-15 04:33:32'),
(338, 5, 'anorganik', 1, '2025-11-15 04:33:46'),
(339, 5, 'anorganik', 1, '2025-11-15 04:34:08'),
(340, 5, 'organik', 1, '2025-11-15 04:34:32'),
(341, 5, 'organik', 1, '2025-11-15 04:34:59'),
(342, 5, 'organik', 1, '2025-11-15 04:35:25'),
(343, 5, 'organik', 1, '2025-11-15 04:39:44'),
(344, 5, 'organik', 1, '2025-11-15 04:39:56'),
(345, 5, 'organik', 1, '2025-11-15 04:40:09'),
(346, 5, 'organik', 1, '2025-11-15 04:40:28'),
(347, 5, 'organik', 1, '2025-11-15 04:43:05'),
(348, 5, 'organik', 1, '2025-11-15 04:43:27'),
(349, 5, 'organik', 1, '2025-11-15 04:44:03'),
(350, 5, 'organik', 1, '2025-11-15 04:44:21'),
(351, 5, 'organik', 1, '2025-11-15 04:44:47'),
(352, 5, 'organik', 1, '2025-11-15 04:45:00'),
(353, 5, 'anorganik', 1, '2025-11-15 04:48:34'),
(354, 5, 'anorganik', 1, '2025-11-15 04:48:38'),
(355, 5, 'anorganik', 1, '2025-11-15 04:48:45'),
(356, 5, 'anorganik', 1, '2025-11-15 04:48:53'),
(357, 5, 'organik', 1, '2025-11-15 04:48:58'),
(358, 5, 'organik', 1, '2025-11-15 04:49:05'),
(359, 5, 'organik', 1, '2025-11-15 04:49:12'),
(360, 5, 'organik', 1, '2025-11-15 04:49:27'),
(361, 5, 'organik', 1, '2025-11-15 04:49:46'),
(362, 5, 'logam', 1, '2025-11-15 04:51:12'),
(363, 5, 'organik', 1, '2025-11-15 04:51:24'),
(364, 5, 'organik', 1, '2025-11-15 04:51:31'),
(365, 5, 'organik', 1, '2025-11-15 04:51:35'),
(366, 5, 'organik', 1, '2025-11-15 04:51:48'),
(367, 5, 'organik', 1, '2025-11-15 04:51:56'),
(368, 5, 'organik', 1, '2025-11-15 04:52:12'),
(369, 5, 'organik', 1, '2025-11-15 04:52:29'),
(370, 5, 'organik', 1, '2025-11-15 04:52:39'),
(371, 5, 'organik', 1, '2025-11-15 04:52:54'),
(372, 5, 'organik', 1, '2025-11-15 04:53:11'),
(373, 5, 'organik', 1, '2025-11-15 04:53:15'),
(374, 5, 'organik', 1, '2025-11-15 04:53:49'),
(375, 5, 'organik', 1, '2025-11-15 04:54:27'),
(376, 5, 'organik', 1, '2025-11-15 04:55:06'),
(377, 5, 'organik', 1, '2025-11-15 04:55:30'),
(378, 5, 'organik', 1, '2025-11-15 04:55:43'),
(379, 5, 'organik', 1, '2025-11-15 04:55:48'),
(380, 5, 'organik', 1, '2025-11-15 04:55:53'),
(381, 5, 'organik', 1, '2025-11-15 04:55:57'),
(382, 5, 'organik', 1, '2025-11-15 04:56:02'),
(383, 5, 'organik', 1, '2025-11-15 04:56:05'),
(384, 5, 'organik', 1, '2025-11-15 04:56:12'),
(385, 5, 'organik', 1, '2025-11-15 04:56:25'),
(386, 5, 'organik', 1, '2025-11-15 04:57:20'),
(387, 5, 'organik', 1, '2025-11-15 04:57:36'),
(388, 5, 'organik', 1, '2025-11-15 04:57:40'),
(389, 5, 'organik', 1, '2025-11-15 04:57:59'),
(390, 5, 'anorganik', 1, '2025-11-15 04:59:42'),
(391, 5, 'anorganik', 1, '2025-11-15 04:59:51'),
(392, 5, 'anorganik', 1, '2025-11-15 05:00:04'),
(393, 5, 'anorganik', 1, '2025-11-15 05:00:10'),
(394, 5, 'organik', 1, '2025-11-15 05:00:16'),
(395, 5, 'organik', 1, '2025-11-15 05:00:26'),
(396, 5, 'organik', 1, '2025-11-15 05:00:30'),
(397, 5, 'organik', 1, '2025-11-15 05:00:35'),
(398, 5, 'organik', 1, '2025-11-15 05:00:47'),
(399, 5, 'organik', 1, '2025-11-15 05:01:40'),
(400, 5, 'organik', 1, '2025-11-15 05:01:46'),
(401, 5, 'organik', 1, '2025-11-15 05:01:50'),
(402, 5, 'organik', 1, '2025-11-15 05:01:55'),
(403, 5, 'logam', 1, '2025-11-15 05:02:00'),
(404, 5, 'logam', 1, '2025-11-15 05:02:04'),
(405, 5, 'organik', 1, '2025-11-15 05:02:22'),
(406, 5, 'organik', 1, '2025-11-15 05:02:26'),
(407, 5, 'logam', 1, '2025-11-15 05:02:30'),
(408, 5, 'logam', 1, '2025-11-15 05:02:34'),
(409, 5, 'organik', 1, '2025-11-15 05:02:55'),
(410, 5, 'organik', 1, '2025-11-15 05:03:11'),
(411, 5, 'organik', 1, '2025-11-15 05:03:20'),
(412, 5, 'organik', 1, '2025-11-15 05:04:10'),
(413, 5, 'logam', 1, '2025-11-15 05:05:02'),
(414, 5, 'logam', 1, '2025-11-15 05:05:10'),
(415, 5, 'organik', 1, '2025-11-15 05:05:15'),
(416, 5, 'organik', 1, '2025-11-15 05:05:22'),
(417, 5, 'organik', 1, '2025-11-15 05:05:27'),
(418, 5, 'organik', 1, '2025-11-15 05:05:31'),
(419, 5, 'organik', 1, '2025-11-15 05:05:36'),
(420, 5, 'organik', 1, '2025-11-15 05:05:50'),
(421, 5, 'organik', 1, '2025-11-15 05:05:58'),
(422, 5, 'organik', 1, '2025-11-15 05:06:06'),
(423, 5, 'organik', 1, '2025-11-15 05:06:11'),
(424, 5, 'logam', 1, '2025-11-15 05:06:17'),
(425, 5, 'organik', 1, '2025-11-15 05:06:22'),
(426, 5, 'organik', 1, '2025-11-15 05:06:27'),
(427, 5, 'organik', 1, '2025-11-15 05:06:33'),
(428, 5, 'organik', 1, '2025-11-15 05:07:05'),
(429, 5, 'organik', 1, '2025-11-15 05:07:09'),
(430, 5, 'organik', 1, '2025-11-15 05:07:15'),
(431, 5, 'organik', 1, '2025-11-15 05:07:20'),
(432, 5, 'organik', 1, '2025-11-15 05:07:24'),
(433, 5, 'organik', 1, '2025-11-15 05:07:28'),
(434, 5, 'organik', 1, '2025-11-15 05:07:35'),
(435, 5, 'organik', 1, '2025-11-15 05:07:45'),
(436, 5, 'organik', 1, '2025-11-15 05:07:49'),
(437, 5, 'organik', 1, '2025-11-15 05:08:08'),
(438, 5, 'organik', 1, '2025-11-15 05:08:29'),
(439, 5, 'organik', 1, '2025-11-15 05:08:34'),
(440, 5, 'organik', 1, '2025-11-15 05:09:00'),
(441, 5, 'organik', 1, '2025-11-15 05:09:59'),
(442, 5, 'organik', 1, '2025-11-15 05:10:04'),
(443, 5, 'organik', 1, '2025-11-15 05:10:19'),
(444, 5, 'organik', 1, '2025-11-15 05:10:31'),
(445, 5, 'organik', 1, '2025-11-15 05:10:37'),
(446, 5, 'anorganik', 1, '2025-11-15 05:13:29'),
(447, 5, 'anorganik', 1, '2025-11-15 05:13:33'),
(448, 5, 'organik', 1, '2025-11-15 05:13:49'),
(449, 5, 'organik', 1, '2025-11-15 05:13:53'),
(450, 5, 'organik', 1, '2025-11-15 05:13:58'),
(451, 5, 'organik', 1, '2025-11-15 05:14:06'),
(452, 5, 'organik', 1, '2025-11-15 05:14:16'),
(453, 5, 'organik', 1, '2025-11-15 05:14:21'),
(454, 5, 'organik', 1, '2025-11-15 05:14:26'),
(455, 5, 'organik', 1, '2025-11-15 05:14:30'),
(456, 5, 'organik', 1, '2025-11-15 05:14:34'),
(457, 5, 'organik', 1, '2025-11-15 05:14:39'),
(458, 5, 'organik', 1, '2025-11-15 05:14:56'),
(459, 5, 'organik', 1, '2025-11-15 05:15:01'),
(460, 5, 'organik', 1, '2025-11-15 05:15:05'),
(461, 5, 'organik', 1, '2025-11-15 05:16:32'),
(462, 5, 'organik', 1, '2025-11-15 05:16:40'),
(463, 5, 'organik', 1, '2025-11-15 05:16:45'),
(464, 5, 'anorganik', 1, '2025-11-15 05:20:14'),
(465, 5, 'organik', 1, '2025-11-15 05:20:19'),
(466, 5, 'organik', 1, '2025-11-15 05:20:24'),
(467, 5, 'organik', 1, '2025-11-15 05:20:28'),
(468, 5, 'organik', 1, '2025-11-15 05:20:32'),
(469, 5, 'organik', 1, '2025-11-15 05:20:43'),
(470, 5, 'organik', 1, '2025-11-15 05:20:47'),
(471, 5, 'organik', 1, '2025-11-15 05:20:54'),
(472, 5, 'organik', 1, '2025-11-15 05:20:58'),
(473, 5, 'organik', 1, '2025-11-15 05:21:04'),
(474, 5, 'organik', 1, '2025-11-15 05:21:09'),
(475, 5, 'organik', 1, '2025-11-15 05:21:13'),
(476, 5, 'organik', 1, '2025-11-15 05:21:19'),
(477, 5, 'organik', 1, '2025-11-15 05:21:23'),
(478, 5, 'organik', 1, '2025-11-15 05:21:28'),
(479, 5, 'organik', 1, '2025-11-15 05:21:32'),
(480, 5, 'organik', 1, '2025-11-15 05:21:37'),
(481, 5, 'organik', 1, '2025-11-15 05:21:41'),
(482, 5, 'organik', 1, '2025-11-15 05:21:46'),
(483, 5, 'organik', 1, '2025-11-15 05:21:50'),
(484, 5, 'organik', 1, '2025-11-15 05:21:54'),
(485, 5, 'organik', 1, '2025-11-15 05:21:58'),
(486, 5, 'organik', 1, '2025-11-15 05:22:10'),
(487, 5, 'organik', 1, '2025-11-15 05:22:17'),
(488, 5, 'organik', 1, '2025-11-15 05:22:23'),
(489, 5, 'organik', 1, '2025-11-15 05:22:32'),
(490, 5, 'organik', 1, '2025-11-15 05:22:42'),
(491, 5, 'organik', 1, '2025-11-15 05:22:46'),
(492, 5, 'organik', 1, '2025-11-15 05:22:50'),
(493, 5, 'organik', 1, '2025-11-15 05:22:55'),
(494, 5, 'organik', 1, '2025-11-15 05:22:59'),
(495, 5, 'organik', 1, '2025-11-15 05:23:03'),
(496, 5, 'organik', 1, '2025-11-15 05:23:11'),
(497, 5, 'organik', 1, '2025-11-15 05:23:37'),
(498, 5, 'organik', 1, '2025-11-15 05:23:43'),
(499, 5, 'organik', 1, '2025-11-15 05:23:49'),
(500, 5, 'organik', 1, '2025-11-15 05:23:58'),
(501, 5, 'organik', 1, '2025-11-15 05:24:02'),
(502, 5, 'organik', 1, '2025-11-15 05:24:06'),
(503, 5, 'organik', 1, '2025-11-15 05:24:11'),
(504, 5, 'organik', 1, '2025-11-15 05:24:21'),
(505, 5, 'organik', 1, '2025-11-15 05:24:25'),
(506, 5, 'organik', 1, '2025-11-15 05:24:30'),
(507, 5, 'organik', 1, '2025-11-15 05:24:39'),
(508, 5, 'organik', 1, '2025-11-15 05:24:44'),
(509, 5, 'organik', 1, '2025-11-15 05:24:49'),
(510, 5, 'organik', 1, '2025-11-15 05:24:56'),
(511, 5, 'organik', 1, '2025-11-15 05:25:02'),
(512, 5, 'organik', 1, '2025-11-15 05:25:06'),
(513, 5, 'organik', 1, '2025-11-15 05:25:11'),
(514, 5, 'organik', 1, '2025-11-15 05:25:17'),
(515, 5, 'organik', 1, '2025-11-15 05:25:21'),
(516, 5, 'organik', 1, '2025-11-15 05:25:31'),
(517, 5, 'organik', 1, '2025-11-15 05:25:35'),
(518, 5, 'organik', 1, '2025-11-15 05:25:40'),
(519, 5, 'organik', 1, '2025-11-15 05:25:45'),
(520, 5, 'organik', 1, '2025-11-15 05:25:50'),
(521, 5, 'organik', 1, '2025-11-15 05:25:54'),
(522, 5, 'organik', 1, '2025-11-15 05:25:58'),
(523, 5, 'organik', 1, '2025-11-15 05:26:02'),
(524, 5, 'organik', 1, '2025-11-15 05:26:06'),
(525, 5, 'organik', 1, '2025-11-15 05:26:13'),
(526, 5, 'organik', 1, '2025-11-15 05:26:17'),
(527, 5, 'organik', 1, '2025-11-15 05:26:22'),
(528, 5, 'organik', 1, '2025-11-15 05:26:26'),
(529, 5, 'organik', 1, '2025-11-15 05:26:31'),
(530, 5, 'organik', 1, '2025-11-15 05:26:35'),
(531, 5, 'organik', 1, '2025-11-15 05:26:39'),
(532, 5, 'organik', 1, '2025-11-15 05:26:44'),
(533, 5, 'organik', 1, '2025-11-15 05:27:10'),
(534, 5, 'organik', 1, '2025-11-15 05:27:15'),
(535, 5, 'organik', 1, '2025-11-15 05:27:20'),
(536, 5, 'organik', 1, '2025-11-15 05:27:24'),
(537, 5, 'organik', 1, '2025-11-15 05:27:28'),
(538, 5, 'organik', 1, '2025-11-15 05:27:33'),
(539, 5, 'organik', 1, '2025-11-15 05:27:38'),
(540, 5, 'organik', 1, '2025-11-15 05:27:42'),
(541, 5, 'organik', 1, '2025-11-15 05:27:47'),
(542, 5, 'organik', 1, '2025-11-15 05:27:53'),
(543, 5, 'organik', 1, '2025-11-15 05:27:56'),
(544, 5, 'organik', 1, '2025-11-15 05:28:01'),
(545, 5, 'organik', 1, '2025-11-15 05:28:06'),
(546, 5, 'organik', 1, '2025-11-15 05:28:10'),
(547, 5, 'organik', 1, '2025-11-15 05:28:15'),
(548, 5, 'organik', 1, '2025-11-15 05:28:19'),
(549, 5, 'organik', 1, '2025-11-15 05:28:24'),
(550, 5, 'organik', 1, '2025-11-15 05:28:29'),
(551, 5, 'organik', 1, '2025-11-15 05:28:34'),
(552, 5, 'organik', 1, '2025-11-15 05:28:38'),
(553, 5, 'logam', 1, '2025-11-15 05:28:42'),
(554, 5, 'organik', 1, '2025-11-15 10:13:06'),
(555, 5, 'organik', 1, '2025-11-15 10:13:12'),
(556, 5, 'logam', 1, '2025-11-15 10:13:34'),
(557, 5, 'organik', 1, '2025-11-15 10:14:03'),
(558, 5, 'organik', 1, '2025-11-15 10:14:08'),
(559, 5, 'organik', 1, '2025-11-15 10:14:36'),
(560, 5, 'organik', 1, '2025-11-15 10:17:32'),
(561, 5, 'organik', 1, '2025-11-15 10:17:36'),
(562, 5, 'organik', 1, '2025-11-15 10:19:27'),
(563, 5, 'organik', 1, '2025-11-15 10:19:32'),
(564, 5, 'organik', 1, '2025-11-15 10:20:46');

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
  MODIFY `device_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `tb_device_status`
--
ALTER TABLE `tb_device_status`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `tb_history`
--
ALTER TABLE `tb_history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=242;

--
-- AUTO_INCREMENT for table `tb_sorting_history`
--
ALTER TABLE `tb_sorting_history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=565;

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
