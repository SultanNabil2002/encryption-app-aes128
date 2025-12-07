-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 20 Jul 2025 pada 17.59
-- Versi server: 10.4.27-MariaDB
-- Versi PHP: 7.4.33

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `db_kripto_web`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `activity_logs`
--

CREATE TABLE `activity_logs` (
  `id` int(11) NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT current_timestamp(),
  `user_id` int(11) DEFAULT NULL,
  `username` varchar(50) DEFAULT NULL,
  `action_type` varchar(50) NOT NULL,
  `description` text NOT NULL,
  `target_document_id` int(11) DEFAULT NULL,
  `target_user_id` int(11) DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data untuk tabel `activity_logs`
--

INSERT INTO `activity_logs` (`id`, `timestamp`, `user_id`, `username`, `action_type`, `description`, `target_document_id`, `target_user_id`, `ip_address`) VALUES
(52, '2025-07-16 04:04:18', NULL, 'admin (percobaan)', 'LOGIN_FAIL', 'Percobaan login gagal untuk username: \"admin\" (username tidak ditemukan).', NULL, NULL, '::1'),
(53, '2025-07-16 04:04:22', NULL, 'admin (percobaan)', 'LOGIN_FAIL', 'Percobaan login gagal untuk username: \"admin\" (username tidak ditemukan).', NULL, NULL, '::1'),
(54, '2025-07-16 04:04:27', NULL, 'admin (percobaan)', 'LOGIN_FAIL', 'Percobaan login gagal untuk username: \"admin\" (username tidak ditemukan).', NULL, NULL, '::1'),
(55, '2025-07-16 04:05:20', NULL, 'rafli', 'LOGIN_SUCCESS', 'Pengguna \"rafli\" berhasil login.', NULL, NULL, '::1'),
(56, '2025-07-16 04:06:15', NULL, 'rafli', 'LOGIN_SUCCESS', 'Pengguna \"rafli\" berhasil login.', NULL, NULL, '::1'),
(59, '2025-07-16 04:25:04', 6, 'admin', 'USER_REGISTER_PUBLIC', 'Pengguna baru \"admin\" dengan role \"super_admin\" berhasil mendaftar (via publik).', NULL, 6, '::1'),
(60, '2025-07-16 04:25:11', 6, 'admin', 'LOGIN_SUCCESS', 'Pengguna \"admin\" berhasil login.', NULL, NULL, '::1'),
(62, '2025-07-16 04:27:13', 6, 'admin', 'FILE_ENCRYPT', 'Pengguna \"admin\" berhasil mengenkripsi file \"19. Raiky Adhies N.A_SPV HCMGA.pdf\" menjadi \"1752640033_68772a2192641_19._Raiky_Adhies_N.A_SPV_HCMGA.pdf.enc\".', NULL, NULL, '::1'),
(63, '2025-07-16 04:27:53', 6, 'admin', 'FILE_DECRYPT_SUCCESS', 'Pengguna \"admin\" berhasil mendekripsi file \"19. Raiky Adhies N.A_SPV HCMGA.pdf\" (ID: 11) menjadi \"1752640073_68772a49266e8_19. Raiky Adhies N.A_SPV HCMGA.pdf\".', NULL, NULL, '::1'),
(65, '2025-07-16 04:30:14', 6, 'admin', 'FILE_ENCRYPT', 'Pengguna \"admin\" berhasil mengenkripsi file \"19. Raiky Adhies N.A_SPV HCMGA.pdf\" menjadi \"1752640214_68772ad66ab42_19._Raiky_Adhies_N.A_SPV_HCMGA.pdf.enc\".', NULL, NULL, '::1'),
(66, '2025-07-16 04:30:38', 6, 'admin', 'FILE_DECRYPT_SUCCESS', 'Pengguna \"admin\" berhasil mendekripsi file \"19. Raiky Adhies N.A_SPV HCMGA.pdf\" (ID: 12) menjadi \"1752640238_68772aeed8621_19. Raiky Adhies N.A_SPV HCMGA.pdf\".', NULL, NULL, '::1'),
(67, '2025-07-16 04:52:26', 6, 'admin', 'FILE_ENCRYPT', 'Pengguna \"admin\" berhasil mengenkripsi file \"58. Muhammad Noor_Foreman GA.pdf\" menjadi \"1752641546_6877300a8ca5f_58._Muhammad_Noor_Foreman_GA.pdf.enc\".', NULL, NULL, '::1'),
(68, '2025-07-16 04:53:04', 6, 'admin', 'FILE_DECRYPT_SUCCESS', 'Pengguna \"admin\" berhasil mendekripsi file \"58. Muhammad Noor_Foreman GA.pdf\" (ID: 13) menjadi \"1752641584_68773030989b1_58. Muhammad Noor_Foreman GA.pdf\".', NULL, NULL, '::1'),
(69, '2025-07-16 04:53:36', 6, 'admin', 'FILE_ENCRYPT', 'Pengguna \"admin\" berhasil mengenkripsi file \"191. Juna B_Admin HRGA.pdf\" menjadi \"1752641616_68773050bf138_191._Juna_B_Admin_HRGA.pdf.enc\".', NULL, NULL, '::1'),
(70, '2025-07-16 04:54:01', 6, 'admin', 'FILE_DECRYPT_SUCCESS', 'Pengguna \"admin\" berhasil mendekripsi file \"191. Juna B_Admin HRGA.pdf\" (ID: 14) menjadi \"1752641641_68773069e125f_191. Juna B_Admin HRGA.pdf\".', NULL, NULL, '::1'),
(71, '2025-07-16 04:55:34', 6, 'admin', 'FILE_ENCRYPT', 'Pengguna \"admin\" berhasil mengenkripsi file \"RAPORT PK Desember 2024.xlsx\" menjadi \"1752641734_687730c67a99f_RAPORT_PK_Desember_2024.xlsx.enc\".', NULL, NULL, '::1'),
(72, '2025-07-16 04:57:43', 6, 'admin', 'FILE_DECRYPT_SUCCESS', 'Pengguna \"admin\" berhasil mendekripsi file \"RAPORT PK Desember 2024.xlsx\" (ID: 15) menjadi \"1752641863_6877314751eee_RAPORT PK Desember 2024.xlsx\".', NULL, NULL, '::1'),
(73, '2025-07-16 04:58:55', 6, 'admin', 'FILE_ENCRYPT', 'Pengguna \"admin\" berhasil mengenkripsi file \"0. Master Surat Pengalaman KerjaCertificat.docx\" menjadi \"1752641935_6877318fcd58c_0._Master_Surat_Pengalaman_KerjaCertificat.docx.enc\".', NULL, NULL, '::1'),
(74, '2025-07-16 04:59:20', 6, 'admin', 'FILE_DECRYPT_SUCCESS', 'Pengguna \"admin\" berhasil mendekripsi file \"0. Master Surat Pengalaman KerjaCertificat.docx\" (ID: 16) menjadi \"1752641960_687731a89b15c_0. Master Surat Pengalaman KerjaCertificat.docx\".', NULL, NULL, '::1'),
(75, '2025-07-20 15:04:54', 6, 'admin', 'LOGIN_SUCCESS', 'Pengguna \"admin\" berhasil login.', NULL, NULL, '::1'),
(76, '2025-07-20 15:05:18', 6, 'admin', 'FILE_DOWNLOAD_DECRYPTED_FAIL', 'Percobaan unduh file (decrypted) gagal: File fisik tidak ditemukan di server untuk dokumen ID 16 oleh pengguna \"admin\". Path diharapkan: C:\\xampp\\htdocs\\AplikasiWebKripto\\public\\dashboard/../Hasil/hasildekripsi/1752641960_687731a89b15c_0. Master Surat Pengalaman KerjaCertificat.docx', NULL, NULL, '::1'),
(82, '2025-07-20 15:07:04', 6, 'admin', 'FILE_ENCRYPT', 'Pengguna \"admin\" berhasil mengenkripsi file \"19. Raiky Adhies N.A_SPV HCMGA.pdf\" menjadi \"1753024024_687d0618e5adf_19._Raiky_Adhies_N.A_SPV_HCMGA.pdf.enc\".', NULL, NULL, '::1'),
(83, '2025-07-20 15:08:26', 6, 'admin', 'FILE_ENCRYPT', 'Pengguna \"admin\" berhasil mengenkripsi file \"19. Raiky Adhies N.A_SPV HCMGA.pdf\" menjadi \"1753024106_687d066a836e9_19._Raiky_Adhies_N.A_SPV_HCMGA.pdf.enc\".', NULL, NULL, '::1'),
(84, '2025-07-20 15:09:05', 6, 'admin', 'FILE_DECRYPT_SUCCESS', 'Pengguna \"admin\" berhasil mendekripsi file \"19. Raiky Adhies N.A_SPV HCMGA.pdf\" (ID: 18) menjadi \"1753024145_687d06918488c_19. Raiky Adhies N.A_SPV HCMGA.pdf\".', NULL, NULL, '::1'),
(85, '2025-07-20 15:09:40', 6, 'admin', 'FILE_ENCRYPT', 'Pengguna \"admin\" berhasil mengenkripsi file \"58. Muhammad Noor_Foreman GA.pdf\" menjadi \"1753024180_687d06b4ba714_58._Muhammad_Noor_Foreman_GA.pdf.enc\".', NULL, NULL, '::1'),
(86, '2025-07-20 15:10:07', 6, 'admin', 'FILE_DECRYPT_SUCCESS', 'Pengguna \"admin\" berhasil mendekripsi file \"58. Muhammad Noor_Foreman GA.pdf\" (ID: 19) menjadi \"1753024207_687d06cf37e81_58. Muhammad Noor_Foreman GA.pdf\".', NULL, NULL, '::1'),
(87, '2025-07-20 15:10:44', 6, 'admin', 'FILE_ENCRYPT', 'Pengguna \"admin\" berhasil mengenkripsi file \"191. Juna B_Admin HRGA.pdf\" menjadi \"1753024244_687d06f46d5f8_191._Juna_B_Admin_HRGA.pdf.enc\".', NULL, NULL, '::1'),
(88, '2025-07-20 15:11:08', 6, 'admin', 'FILE_DECRYPT_SUCCESS', 'Pengguna \"admin\" berhasil mendekripsi file \"191. Juna B_Admin HRGA.pdf\" (ID: 20) menjadi \"1753024268_687d070c10508_191. Juna B_Admin HRGA.pdf\".', NULL, NULL, '::1'),
(89, '2025-07-20 15:12:37', 6, 'admin', 'FILE_ENCRYPT', 'Pengguna \"admin\" berhasil mengenkripsi file \"RAPORT PK Desember 2024.xlsx\" menjadi \"1753024357_687d07654ffe7_RAPORT_PK_Desember_2024.xlsx.enc\".', NULL, NULL, '::1'),
(95, '2025-07-20 15:48:26', 6, 'admin', 'FILE_ENCRYPT', 'Pengguna \"admin\" berhasil mengenkripsi file \"MARS budi Luhur.mp4\" menjadi \"1753026506_687d0fca71502_MARS_budi_Luhur.mp4.enc\".', NULL, NULL, '::1'),
(96, '2025-07-20 15:53:07', 6, 'admin', 'FILE_DECRYPT_SUCCESS', 'Pengguna \"admin\" berhasil mendekripsi file \"MARS budi Luhur.mp4\" (ID: 22) menjadi \"1753026787_687d10e33a491_MARS budi Luhur.mp4\".', NULL, NULL, '::1'),
(98, '2025-07-20 15:54:30', 6, 'admin', 'FILE_ENCRYPT', 'Pengguna \"admin\" berhasil mengenkripsi file \"19. Raiky Adhies N.A_SPV HCMGA.pdf\" menjadi \"1753026870_687d1136798fe_19._Raiky_Adhies_N.A_SPV_HCMGA.pdf.enc\".', NULL, NULL, '::1'),
(99, '2025-07-20 15:55:00', 6, 'admin', 'FILE_ENCRYPT', 'Pengguna \"admin\" berhasil mengenkripsi file \"19. Raiky Adhies N.A_SPV HCMGA.pdf\" menjadi \"1753026900_687d11546c08f_19._Raiky_Adhies_N.A_SPV_HCMGA.pdf.enc\".', 24, NULL, '::1'),
(101, '2025-07-20 15:55:18', 6, 'admin', 'FILE_ENCRYPT', 'Pengguna \"admin\" berhasil mengenkripsi file \"19. Raiky Adhies N.A_SPV HCMGA.pdf\" menjadi \"1753026918_687d1166ac989_19._Raiky_Adhies_N.A_SPV_HCMGA.pdf.enc\".', 25, NULL, '::1'),
(102, '2025-07-20 15:55:31', 6, 'admin', 'FILE_DECRYPT_SUCCESS', 'Pengguna \"admin\" berhasil mendekripsi file \"19. Raiky Adhies N.A_SPV HCMGA.pdf\" (ID: 25) menjadi \"1753026931_687d1173b4d5f_19. Raiky Adhies N.A_SPV HCMGA.pdf\".', 25, NULL, '::1'),
(103, '2025-07-20 15:55:45', 6, 'admin', 'FILE_ENCRYPT', 'Pengguna \"admin\" berhasil mengenkripsi file \"58. Muhammad Noor_Foreman GA.pdf\" menjadi \"1753026945_687d11814ad29_58._Muhammad_Noor_Foreman_GA.pdf.enc\".', 26, NULL, '::1'),
(104, '2025-07-20 15:55:55', 6, 'admin', 'FILE_DECRYPT_SUCCESS', 'Pengguna \"admin\" berhasil mendekripsi file \"58. Muhammad Noor_Foreman GA.pdf\" (ID: 26) menjadi \"1753026955_687d118bc104b_58. Muhammad Noor_Foreman GA.pdf\".', 26, NULL, '::1'),
(105, '2025-07-20 15:56:17', 6, 'admin', 'FILE_ENCRYPT', 'Pengguna \"admin\" berhasil mengenkripsi file \"191. Juna B_Admin HRGA.pdf\" menjadi \"1753026977_687d11a1a05ee_191._Juna_B_Admin_HRGA.pdf.enc\".', 27, NULL, '::1'),
(106, '2025-07-20 15:56:18', 6, 'admin', 'FILE_ENCRYPT', 'Pengguna \"admin\" berhasil mengenkripsi file \"191. Juna B_Admin HRGA.pdf\" menjadi \"1753026978_687d11a274e49_191._Juna_B_Admin_HRGA.pdf.enc\".', NULL, NULL, '::1'),
(108, '2025-07-20 15:56:43', 6, 'admin', 'FILE_DECRYPT_SUCCESS', 'Pengguna \"admin\" berhasil mendekripsi file \"191. Juna B_Admin HRGA.pdf\" (ID: 27) menjadi \"1753027003_687d11bbd8776_191. Juna B_Admin HRGA.pdf\".', 27, NULL, '::1'),
(109, '2025-07-20 15:57:06', 6, 'admin', 'FILE_ENCRYPT', 'Pengguna \"admin\" berhasil mengenkripsi file \"RAPORT PK Desember 2024.xlsx\" menjadi \"1753027026_687d11d2d04bd_RAPORT_PK_Desember_2024.xlsx.enc\".', 29, NULL, '::1'),
(110, '2025-07-20 15:57:50', 6, 'admin', 'FILE_DECRYPT_SUCCESS', 'Pengguna \"admin\" berhasil mendekripsi file \"RAPORT PK Desember 2024.xlsx\" (ID: 29) menjadi \"1753027070_687d11fea24e5_RAPORT PK Desember 2024.xlsx\".', 29, NULL, '::1'),
(111, '2025-07-20 15:58:14', 6, 'admin', 'FILE_ENCRYPT', 'Pengguna \"admin\" berhasil mengenkripsi file \"0. Master Surat Pengalaman KerjaCertificat.docx\" menjadi \"1753027094_687d12162239b_0._Master_Surat_Pengalaman_KerjaCertificat.docx.enc\".', 30, NULL, '::1'),
(112, '2025-07-20 15:58:24', 6, 'admin', 'FILE_DECRYPT_SUCCESS', 'Pengguna \"admin\" berhasil mendekripsi file \"0. Master Surat Pengalaman KerjaCertificat.docx\" (ID: 30) menjadi \"1753027104_687d1220dca6a_0. Master Surat Pengalaman KerjaCertificat.docx\".', 30, NULL, '::1'),
(113, '2025-07-20 15:58:58', 6, 'admin', 'FILE_DOWNLOAD_DECRYPTED', 'Pengguna \"admin\" berhasil mengunduh file (decrypted): \"0. Master Surat Pengalaman KerjaCertificat.docx\" (ID Dok: 30). Nama file unduhan: \"1753027104_687d1220dca6a_0. Master Surat Pengalaman KerjaCertificat.docx\".', 30, NULL, '::1'),
(114, '2025-07-20 15:59:00', 6, 'admin', 'FILE_DOWNLOAD_DECRYPTED', 'Pengguna \"admin\" berhasil mengunduh file (decrypted): \"RAPORT PK Desember 2024.xlsx\" (ID Dok: 29). Nama file unduhan: \"1753027070_687d11fea24e5_RAPORT PK Desember 2024.xlsx\".', 29, NULL, '::1'),
(115, '2025-07-20 15:59:02', 6, 'admin', 'FILE_DOWNLOAD_DECRYPTED', 'Pengguna \"admin\" berhasil mengunduh file (decrypted): \"191. Juna B_Admin HRGA.pdf\" (ID Dok: 27). Nama file unduhan: \"1753027003_687d11bbd8776_191. Juna B_Admin HRGA.pdf\".', 27, NULL, '::1'),
(116, '2025-07-20 15:59:03', 6, 'admin', 'FILE_DOWNLOAD_ENCRYPTED', 'Pengguna \"admin\" berhasil mengunduh file (encrypted): \"19. Raiky Adhies N.A_SPV HCMGA.pdf\" (ID Dok: 24). Nama file unduhan: \"1753026900_687d11546c08f_19._Raiky_Adhies_N.A_SPV_HCMGA.pdf.enc\".', 24, NULL, '::1');

-- --------------------------------------------------------

--
-- Struktur dari tabel `documents`
--

CREATE TABLE `documents` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `original_filename` varchar(255) NOT NULL,
  `mime_type` varchar(100) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `upload_timestamp` timestamp NOT NULL DEFAULT current_timestamp(),
  `encrypted_filename` varchar(255) NOT NULL,
  `encrypted_filepath` varchar(255) NOT NULL,
  `filesize_original_kb` decimal(10,2) DEFAULT NULL,
  `filesize_encrypted_kb` decimal(10,2) DEFAULT NULL,
  `iv` varchar(32) NOT NULL,
  `kdf_salt` varchar(32) NOT NULL,
  `encryption_password_hash` varchar(255) NOT NULL,
  `encryption_password_salt` varchar(32) NOT NULL,
  `encryption_duration_seconds` float DEFAULT NULL,
  `encryption_timestamp` timestamp NULL DEFAULT NULL,
  `decrypted_filename` varchar(255) DEFAULT NULL,
  `decrypted_filepath` varchar(255) DEFAULT NULL,
  `filesize_decrypted_kb` decimal(10,2) DEFAULT NULL,
  `decryption_duration_seconds` float DEFAULT NULL,
  `decryption_timestamp` timestamp NULL DEFAULT NULL,
  `decrypted_by_user_id` int(11) DEFAULT NULL,
  `file_status` enum('Terenkripsi','Terdekripsi') NOT NULL DEFAULT 'Terenkripsi'
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data untuk tabel `documents`
--

INSERT INTO `documents` (`id`, `user_id`, `original_filename`, `mime_type`, `description`, `upload_timestamp`, `encrypted_filename`, `encrypted_filepath`, `filesize_original_kb`, `filesize_encrypted_kb`, `iv`, `kdf_salt`, `encryption_password_hash`, `encryption_password_salt`, `encryption_duration_seconds`, `encryption_timestamp`, `decrypted_filename`, `decrypted_filepath`, `filesize_decrypted_kb`, `decryption_duration_seconds`, `decryption_timestamp`, `decrypted_by_user_id`, `file_status`) VALUES
(24, 6, '19. Raiky Adhies N.A_SPV HCMGA.pdf', 'application/pdf', 'slipgaji', '2025-07-20 15:55:00', '1753026900_687d11546c08f_19._Raiky_Adhies_N.A_SPV_HCMGA.pdf.enc', 'Hasil/hasilenkripsi/1753026900_687d11546c08f_19._Raiky_Adhies_N.A_SPV_HCMGA.pdf.enc', '205.15', '205.16', '70d18883f26ea919688a778116f3fc2a', '22b3cae2fcf20d0f3af8a924ae229d5d', '41354639c71eb99e5bf6b085adc8e2887d23b0021be74b998fbaaed79b292b47', '2bdf1316af7669a6432088a001de50ac', 0.95, '2025-07-20 10:55:00', NULL, NULL, NULL, NULL, NULL, NULL, 'Terenkripsi'),
(25, 6, '19. Raiky Adhies N.A_SPV HCMGA.pdf', 'application/pdf', 'slipgaji', '2025-07-20 15:55:18', '1753026918_687d1166ac989_19._Raiky_Adhies_N.A_SPV_HCMGA.pdf.enc', 'Hasil/hasilenkripsi/1753026918_687d1166ac989_19._Raiky_Adhies_N.A_SPV_HCMGA.pdf.enc', '205.15', '205.16', '081f0b08327e2a410c4cb78d4dfd449c', '228cf13057b92e9491dee1504a0ef892', 'ef788c44c0fc6086ab0beea27d3a8d51b36c9f16636fc7132783708825d4f789', '151f7bc2c9e70f0d5121d222be387a7d', 1.02, '2025-07-20 10:55:18', '1753026931_687d1173b4d5f_19. Raiky Adhies N.A_SPV HCMGA.pdf', 'Hasil/hasildekripsi/1753026931_687d1173b4d5f_19. Raiky Adhies N.A_SPV HCMGA.pdf', '205.15', 2.21, '0000-00-00 00:00:00', 6, 'Terdekripsi'),
(26, 6, '58. Muhammad Noor_Foreman GA.pdf', 'application/pdf', 'slipgaji', '2025-07-20 15:55:45', '1753026945_687d11814ad29_58._Muhammad_Noor_Foreman_GA.pdf.enc', 'Hasil/hasilenkripsi/1753026945_687d11814ad29_58._Muhammad_Noor_Foreman_GA.pdf.enc', '202.23', '202.23', 'f52b27c7c16ae57b8849c2d1ccc9001d', '4839c5b50379016a510c79b1b9287284', '44213bcae42c0f2995db300f477d1610cedecc6774c38b432a231d3d07d554bd', '048cb197cf574bfd9b2eae33b5875ad3', 0.99, '2025-07-20 10:55:45', '1753026955_687d118bc104b_58. Muhammad Noor_Foreman GA.pdf', 'Hasil/hasildekripsi/1753026955_687d118bc104b_58. Muhammad Noor_Foreman GA.pdf', '202.23', 2.16, '0000-00-00 00:00:00', 6, 'Terdekripsi'),
(27, 6, '191. Juna B_Admin HRGA.pdf', 'application/pdf', 'slipgaji', '2025-07-20 15:56:17', '1753026977_687d11a1a05ee_191._Juna_B_Admin_HRGA.pdf.enc', 'Hasil/hasilenkripsi/1753026977_687d11a1a05ee_191._Juna_B_Admin_HRGA.pdf.enc', '180.29', '180.30', '6c1162b1f0728e63054329be4767e136', '8e5403b7ea0ac9d353994d5bc8d4b42f', '3808ee1f45b41e8ccb98011e9c340f9fd75aa743cd185ef80ae120718d16df2a', '5b4899283e963c27776b0624f98ed984', 0.85, '2025-07-20 10:56:17', '1753027003_687d11bbd8776_191. Juna B_Admin HRGA.pdf', 'Hasil/hasildekripsi/1753027003_687d11bbd8776_191. Juna B_Admin HRGA.pdf', '180.29', 1.9, '0000-00-00 00:00:00', 6, 'Terdekripsi'),
(29, 6, 'RAPORT PK Desember 2024.xlsx', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'data', '2025-07-20 15:57:06', '1753027026_687d11d2d04bd_RAPORT_PK_Desember_2024.xlsx.enc', 'Hasil/hasilenkripsi/1753027026_687d11d2d04bd_RAPORT_PK_Desember_2024.xlsx.enc', '2025.22', '2025.23', '8f717b7737c6c395f0c56bdf65b2584f', '8b42c1464f12efc3c905e71069c687d7', 'c69054e3dec1d30d739ae1876598bd0803d4b33e923f0434a7745a3a0c883900', 'b818729f1d160ef3c1533cc6a8caba77', 9.13, '2025-07-20 10:57:06', '1753027070_687d11fea24e5_RAPORT PK Desember 2024.xlsx', 'Hasil/hasildekripsi/1753027070_687d11fea24e5_RAPORT PK Desember 2024.xlsx', '2025.22', 20.88, '0000-00-00 00:00:00', 6, 'Terdekripsi'),
(30, 6, '0. Master Surat Pengalaman KerjaCertificat.docx', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'data', '2025-07-20 15:58:14', '1753027094_687d12162239b_0._Master_Surat_Pengalaman_KerjaCertificat.docx.enc', 'Hasil/hasilenkripsi/1753027094_687d12162239b_0._Master_Surat_Pengalaman_KerjaCertificat.docx.enc', '151.18', '151.19', '8712dc769ec7589edd8eaa00a4fd739e', '21daaeae81642abaa12bdeaa2dcb11ac', '16144bcad389eba519d38eac4da7558468882789bd09f943448d73f33bd69ef6', '982c025338339a98e64137ca114e9311', 0.67, '2025-07-20 10:58:14', '1753027104_687d1220dca6a_0. Master Surat Pengalaman KerjaCertificat.docx', 'Hasil/hasildekripsi/1753027104_687d1220dca6a_0. Master Surat Pengalaman KerjaCertificat.docx', '151.18', 1.54, '0000-00-00 00:00:00', 6, 'Terdekripsi');

-- --------------------------------------------------------

--
-- Struktur dari tabel `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `role` enum('super_admin','admin') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data untuk tabel `users`
--

INSERT INTO `users` (`id`, `username`, `password_hash`, `role`, `created_at`) VALUES
(6, 'admin', '$2y$10$bMaKylGqSln6dYSu1Sp0iuN9kyoO0ePDN2LPFFAXiqVlBwKeqN9R2', 'super_admin', '2025-07-16 04:25:04');

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `target_document_id` (`target_document_id`),
  ADD KEY `target_user_id` (`target_user_id`);

--
-- Indeks untuk tabel `documents`
--
ALTER TABLE `documents`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `decrypted_by_user_id` (`decrypted_by_user_id`);

--
-- Indeks untuk tabel `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `activity_logs`
--
ALTER TABLE `activity_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=117;

--
-- AUTO_INCREMENT untuk tabel `documents`
--
ALTER TABLE `documents`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT untuk tabel `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD CONSTRAINT `activity_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `activity_logs_ibfk_2` FOREIGN KEY (`target_document_id`) REFERENCES `documents` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `activity_logs_ibfk_3` FOREIGN KEY (`target_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Ketidakleluasaan untuk tabel `documents`
--
ALTER TABLE `documents`
  ADD CONSTRAINT `documents_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `documents_ibfk_2` FOREIGN KEY (`decrypted_by_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
