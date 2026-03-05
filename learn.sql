-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 05, 2026 at 05:21 AM
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
-- Database: `learn`
--

-- --------------------------------------------------------

--
-- Table structure for table `cache`
--

CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `cache`
--

INSERT INTO `cache` (`key`, `value`, `expiration`) VALUES
('laravel-cache-1531310c10aa46d3e970e23315836dae', 'i:1;', 1772682905),
('laravel-cache-1531310c10aa46d3e970e23315836dae:timer', 'i:1772682905;', 1772682905),
('laravel-cache-704b944c2c435bd1c224dd540dbe8b21', 'i:1;', 1772683404),
('laravel-cache-704b944c2c435bd1c224dd540dbe8b21:timer', 'i:1772683404;', 1772683404);

-- --------------------------------------------------------

--
-- Table structure for table `cache_locks`
--

CREATE TABLE `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`, `description`, `created_at`, `updated_at`) VALUES
(1, 'Perulangan', 'Mengajarkan materi perulangan dasar', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `courses`
--

CREATE TABLE `courses` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` varchar(255) NOT NULL,
  `category_id` varchar(255) NOT NULL,
  `link` text DEFAULT NULL,
  `file` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `courses`
--

INSERT INTO `courses` (`id`, `title`, `description`, `category_id`, `link`, `file`, `created_at`, `updated_at`) VALUES
(1, 'Pengenalan', 'Materi pengenalan dari perulangan sebagai dasar untuk belajar beberapa kategori perulangan', '1', '<div style=\"width: 100%;\"><div style=\"position: relative; padding-bottom: 56.25%; padding-top: 0; height: 0;\"><iframe title=\"Next\" frameborder=\"0\" width=\"1200\" height=\"675\" style=\"position: absolute; top: 0; left: 0; width: 100%; height: 100%;\" src=\"https://view.genially.com/696e3b86b08b5ef1b1e51e81\" type=\"text/html\" allowscriptaccess=\"always\" allowfullscreen=\"true\" scrolling=\"yes\" allownetworking=\"all\"></iframe> </div> </div>', NULL, '2026-02-09 21:33:16', '2026-02-21 03:51:10'),
(2, 'For', 'Perulangan for adalah perintah dalam pemrograman yang digunakan untuk menjalankan sekumpulan kode secara berulang-ulang dengan jumlah yang sudah ditentukan di awal.', '1', '<div style=\"width: 100%;\"><div style=\"position: relative; padding-bottom: 56.25%; padding-top: 0; height: 0;\"><iframe title=\"Perulangan for\" frameborder=\"0\" width=\"1200\" height=\"675\" style=\"position: absolute; top: 0; left: 0; width: 100%; height: 100%;\" src=\"https://view.genially.com/699ab8f9ed6392a7e05c7ae7\" type=\"text/html\" allowscriptaccess=\"always\" allowfullscreen=\"true\" scrolling=\"yes\" allownetworking=\"all\"></iframe> </div> </div>', NULL, '2026-02-10 00:39:21', '2026-02-24 18:58:54'),
(5, 'While', 'Perulangan while berjalan selama kondisi terpenuhi (true)', '1', '<div style=\"width: 100%;\"><div style=\"position: relative; padding-bottom: 56.25%; padding-top: 0; height: 0;\"><iframe title=\"Jangan dihapus_while\" frameborder=\"0\" width=\"1200\" height=\"675\" style=\"position: absolute; top: 0; left: 0; width: 100%; height: 100%;\" src=\"https://view.genially.com/69a12972ca7177d5857ece73\" type=\"text/html\" allowscriptaccess=\"always\" allowfullscreen=\"true\" scrolling=\"yes\" allownetworking=\"all\"></iframe> </div> </div>', NULL, '2026-03-03 08:50:08', '2026-03-03 08:50:52'),
(6, 'Do While', 'Perulangan berjalan dilakukan minimal satu kali kemudian cek kondisi', '1', '<div style=\"width: 100%;\"><div style=\"position: relative; padding-bottom: 56.25%; padding-top: 0; height: 0;\"><iframe title=\"jangan dihapus_do while\" frameborder=\"0\" width=\"1200\" height=\"675\" style=\"position: absolute; top: 0; left: 0; width: 100%; height: 100%;\" src=\"https://view.genially.com/69a3e19ba8dd2f113b23de8f\" type=\"text/html\" allowscriptaccess=\"always\" allowfullscreen=\"true\" scrolling=\"yes\" allownetworking=\"all\"></iframe> </div> </div>', NULL, '2026-03-03 10:05:04', '2026-03-03 10:05:25');

-- --------------------------------------------------------

--
-- Table structure for table `course_progress`
--

CREATE TABLE `course_progress` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `course_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `course_progress`
--

INSERT INTO `course_progress` (`id`, `user_id`, `course_id`, `created_at`, `updated_at`) VALUES
(1, 9, 1, '2026-02-09 22:52:53', '2026-02-09 22:52:53'),
(4, 9, 2, '2026-03-04 00:33:20', '2026-03-04 00:33:20'),
(5, 9, 5, '2026-03-04 00:40:21', '2026-03-04 00:40:21');

-- --------------------------------------------------------

--
-- Table structure for table `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` varchar(255) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `jobs`
--

CREATE TABLE `jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` tinyint(3) UNSIGNED NOT NULL,
  `reserved_at` int(10) UNSIGNED DEFAULT NULL,
  `available_at` int(10) UNSIGNED NOT NULL,
  `created_at` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `job_batches`
--

CREATE TABLE `job_batches` (
  `id` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `total_jobs` int(11) NOT NULL,
  `pending_jobs` int(11) NOT NULL,
  `failed_jobs` int(11) NOT NULL,
  `failed_job_ids` longtext NOT NULL,
  `options` mediumtext DEFAULT NULL,
  `cancelled_at` int(11) DEFAULT NULL,
  `created_at` int(11) NOT NULL,
  `finished_at` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` int(10) UNSIGNED NOT NULL,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '0001_01_01_000000_create_users_table', 1),
(2, '0001_01_01_000001_create_cache_table', 1),
(3, '0001_01_01_000002_create_jobs_table', 1),
(4, '2025_08_26_100418_add_two_factor_columns_to_users_table', 1),
(5, '2026_02_10_021238_create_tests_table', 2),
(6, '2026_02_10_031250_create_courses_table', 3),
(7, '2026_02_10_031426_create_categories_table', 3),
(8, '2026_02_10_031517_create_primms_table', 3),
(9, '2026_02_10_045327_create_course_progress_table', 4),
(10, '2026_02_10_134516_create_primm_questions_table', 5),
(11, '2026_02_10_135016_make_soal_nullable_in_primms_table', 6),
(12, '2026_02_10_140710_create_student_answers_table', 7),
(13, '2026_02_21_104933_change_link_to_text_in_courses_table', 8),
(14, '2026_02_25_022038_add_pembahasan_to_primm_questions_table', 9),
(15, '2026_03_03_080004_add_soft_deletes_to_users_table', 10),
(16, '2026_03_03_154506_make_link_nullable_in_courses_table', 11);

-- --------------------------------------------------------

--
-- Table structure for table `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `primms`
--

CREATE TABLE `primms` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `course_id` bigint(20) UNSIGNED NOT NULL,
  `tahap` enum('predict','run','investigate','modify','make') NOT NULL,
  `gambar` varchar(255) DEFAULT NULL,
  `soal` text DEFAULT NULL,
  `link_colab` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `primms`
--

INSERT INTO `primms` (`id`, `course_id`, `tahap`, `gambar`, `soal`, `link_colab`, `created_at`, `updated_at`) VALUES
(31, 2, 'predict', 'primm/predict/ojrHTHK1WXvLHiWW2oQfZynX61KLBuIGpbY0v9YJ.png', NULL, NULL, '2026-03-03 07:56:33', '2026-03-03 07:56:33'),
(32, 2, 'predict', 'primm/predict/QUJcHs0AcwpDT9O4eAwCkoehaPHYihJmtENwkBDH.png', NULL, NULL, '2026-03-03 07:56:33', '2026-03-03 07:56:33'),
(33, 2, 'run', 'primm/run/8YWTqvtTnV6SDsiIZPrJLrvaVUPvkbhgzVdRfnDf.png', NULL, NULL, '2026-03-03 07:59:44', '2026-03-03 07:59:44'),
(34, 2, 'run', 'primm/run/1bPyoslDlqdefzvMELsar3g1UG9j30Pmct8dtdgP.png', NULL, NULL, '2026-03-03 07:59:44', '2026-03-03 07:59:44'),
(35, 2, 'investigate', 'primm/investigate/LLmn94hz2RzY54Cl5QSTsyaT13Svr04hEShYxKx9.png', NULL, NULL, '2026-03-03 08:15:49', '2026-03-03 08:15:49'),
(36, 2, 'investigate', 'primm/investigate/fijrVvgIuDQr5QYYmsnXChAdAUXXTF64xDuHpdJx.png', NULL, NULL, '2026-03-03 08:15:49', '2026-03-03 08:15:49'),
(37, 2, 'investigate', 'primm/investigate/ADeRuX1NIfEZyZNQvjfYcOSVk2KcY7DnepJgSIIh.png', NULL, NULL, '2026-03-03 08:15:49', '2026-03-03 08:15:49'),
(38, 2, 'modify', 'primm/modify/HhtPFc7AvQUyERz9MpABbcbmQhacygZKQowEVdm0.png', NULL, 'https://colab.research.google.com/drive/13vLco1dfDlomlMu7-1_tZMfA61OIxJSB?usp=sharing', '2026-03-03 08:25:14', '2026-03-03 19:49:22'),
(39, 2, 'make', NULL, NULL, 'https://colab.research.google.com/drive/1bdGoosnRiTYVKKJ6ByiLsWftOWyQVbMB?usp=sharing', '2026-03-03 08:27:36', '2026-03-03 19:50:57'),
(40, 5, 'predict', 'primm/predict/7Jp58epy8DO5TqucM6CnVmuoejrXoq0yeJ23IQc8.png', NULL, NULL, '2026-03-03 09:06:55', '2026-03-03 09:06:55'),
(41, 5, 'predict', 'primm/predict/Pgz1WcqTrdwWhDnoZd2w06JRK5lAyfsv4AgqxhGO.png', NULL, NULL, '2026-03-03 09:06:55', '2026-03-03 09:06:55'),
(42, 5, 'run', 'primm/run/l2rYx8R07gCu9gmQbgkJJAL4RYLY9aILiKkP6Ij0.png', NULL, NULL, '2026-03-03 09:13:32', '2026-03-03 09:13:32'),
(43, 5, 'run', 'primm/run/kfcefS8ipXOJP0v2dSgS1PdDyJUH8KZ1VF6aMIpP.png', NULL, NULL, '2026-03-03 09:13:33', '2026-03-03 09:13:33'),
(44, 5, 'investigate', 'primm/investigate/CMtCEYZAjc6qEAUDxyUbTQI7zYDTLRRV8LEgFkxG.png', NULL, NULL, '2026-03-03 09:34:53', '2026-03-03 09:34:53'),
(45, 5, 'investigate', 'primm/investigate/9J7L0lptwEHPxZmDukDc3TenhAoJdTAJWhMehY7D.png', NULL, NULL, '2026-03-03 09:34:54', '2026-03-03 09:34:54'),
(46, 5, 'investigate', 'primm/investigate/3RCdHUCNzHj9k7BPgDTiO5yiVTW2jgv8vLgyY2Pr.png', NULL, NULL, '2026-03-03 09:34:54', '2026-03-03 09:34:54'),
(48, 5, 'modify', 'primm/modify/g1peydVy5dFAkHUEQbV2FhmQGfdL3oBbJESn4pRa.png', NULL, 'https://colab.research.google.com/drive/1eN7BWlnT5-wLJGEx0Dv99pXTAmJDdrKv?usp=sharing', '2026-03-03 09:56:22', '2026-03-03 09:56:22'),
(49, 5, 'make', NULL, NULL, 'https://colab.research.google.com/drive/1iHdtmeprjbFga7ZodzsY7ko4CNkxPAA1?usp=sharing', '2026-03-03 10:00:00', '2026-03-03 10:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `primm_questions`
--

CREATE TABLE `primm_questions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `primm_id` bigint(20) UNSIGNED NOT NULL,
  `pertanyaan` text NOT NULL,
  `pembahasan` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `primm_questions`
--

INSERT INTO `primm_questions` (`id`, `primm_id`, `pertanyaan`, `pembahasan`, `created_at`, `updated_at`) VALUES
(51, 31, 'Apa yang menjadi keluaran dari kode program diatas?', 'Keluaran program tersebut adalah setiap karakter dari kata “Pemrograman” yang ditampilkan secara berurutan dari huruf pertama hingga huruf terakhir. Karena dicetak satu per satu dalam perulangan dan setiap cetakan berpindah ke baris baru, maka masing-masing huruf muncul pada baris yang berbeda.', '2026-03-03 07:56:33', '2026-03-03 07:56:33'),
(52, 31, 'Apa yang akan terjadi  jika for i in huruf diubah menjadi for i in reversed(huruf)?', 'Jika perulangan diubah menjadi for i in reversed(huruf), maka karakter dari kata “Pemrograman” akan ditampilkan dalam urutan terbalik, yaitu dimulai dari huruf terakhir hingga huruf pertama. Setiap huruf tetap dicetak satu per satu pada baris yang berbeda, tetapi urutannya menjadi dari bawah ke atas dibandingkan dengan program sebelumnya.', '2026-03-03 07:56:33', '2026-03-03 07:56:33'),
(53, 32, 'Apa yang menjadi keluaran dari kode diatas?', 'Keluaran dari program tersebut adalah angka 10, 8, 6, 4, dan 2 yang ditampilkan secara berurutan dari terbesar ke terkecil. Setiap angka dicetak pada baris yang berbeda karena perintah cetak dijalankan berulang kali di dalam perulangan.', '2026-03-03 07:56:33', '2026-03-03 07:56:33'),
(54, 32, 'Apa yang akan terjadi jika nilai range diubah menjadi (12)?', 'Jika nilai range diubah menjadi range(12), maka program akan mencetak bilangan mulai dari 0 hingga 11. Hal ini karena perulangan range(12) dimulai dari 0 secara default dan berhenti sebelum angka 12 karena sebelum var=nilaiAkhir. Setiap angka akan ditampilkan satu per satu pada baris yang berbeda.', '2026-03-03 07:56:33', '2026-03-03 07:56:33'),
(55, 33, 'Apakah hasil keluaran dari program yang dijalankan sesuai dengan prediksi kalian?', 'Ya, hasil keluaran dari program sudah sesuai dengan prediksi. Program mencetak setiap karakter dari kata \"Pemrograman\" secara berurutan dari huruf pertama hingga terakhir.', '2026-03-03 07:59:44', '2026-03-03 07:59:44'),
(56, 33, 'b.	Jelaskan jika hasil keluaran berbeda ataupun sama dari prediksi kalian!', 'Hasil keluaran sama dengan prediksi karena program dijalankan sesuai dengan cara perulangan membaca string dari huruf pertama hingga terakhir, sehingga huruf ditampilkan berurutan.', '2026-03-03 07:59:44', '2026-03-03 07:59:44'),
(57, 34, 'Apakah keluaran dari program yang dijalankan sesuai dengan prediksi kalian?', 'Ya, keluaran dari program 2a yang dijalankan sudah sesuai dengan prediksi. Program akan mencetak perulangan dimulai dari 10, kemudian nilainya berkurang 2 setiap kali perulangan, dan berhenti sebelum mencapai 1.', '2026-03-03 07:59:44', '2026-03-03 07:59:44'),
(58, 34, 'Jelaskan jika hasil keluaran berbeda ataupun sama dari prediksi kalian! (', 'Hasil keluaran yang diperoleh sesuai dengan prediksi. Perulangan berjalan menurun karena menggunakan langkah negatif -2, sehingga nilai berkurang setiap perulangan dan menghasilkan urutan bilangan dari 10, 8, 6, 4, 2.', '2026-03-03 07:59:44', '2026-03-03 07:59:44'),
(59, 35, 'Jelaskan fungsi variabel huruf pada program di atas?', 'Variabel huruf berfungsi untuk menyimpan data berupa string yang akan diproses dalam perulangan. Nilai yang tersimpan di dalam variabel tersebut kemudian diambil satu per satu untuk ditampilkan atau diolah sesuai perintah di dalam program', '2026-03-03 08:15:49', '2026-03-03 08:15:49'),
(60, 35, 'Apa yang akan terjadi jika nilai huruf diubah menjadi \"Python\"?', 'Jika nilai huruf diubah menjadi \"Python\", maka program akan menampilkan setiap karakter dari kata tersebut secara berurutan. Huruf P, y, t, h, o, dan n akan dicetak satu per satu sesuai urutan dalam kata tersebut', '2026-03-03 08:15:49', '2026-03-03 08:15:49'),
(61, 35, 'Bagaimana cara perulangan for i in huruf memproses isi string?', 'Perulangan for i in huruf digunakan untuk mengambil setiap karakter yang ada di dalam string secara satu per satu. Artinya, huruf pertama diproses terlebih dahulu, lalu huruf kedua, dan seterusnya sampai semua karakter dalam string selesai diproses. Dengan cara ini, setiap huruf dalam teks bisa ditampilkan atau diolah secara berurutan.', '2026-03-03 08:15:49', '2026-03-03 08:15:49'),
(62, 36, 'Apa yang akan terjadi jika nilai -2 diubah menjadi -3?', 'Jika nilai -2 diubah menjadi -3, maka perulangan akan berjalan dengan langkah turun 3 setiap kali pengulangan. Artinya, perhitungan dimulai dari 10 kemudian berkurang 3 menjadi 7, lalu 4, dan berhenti sebelum melewati batas yang ditentukan. Dengan demikian, output yang dihasilkan adalah 10, 7, dan 4, masing-masing ditampilkan pada baris yang berbeda', '2026-03-03 08:15:49', '2026-03-03 08:15:49'),
(63, 36, 'Apa yang akan terjadi jika range(10, 1, -2) diubah menjadi range(10, 1, 2)?Jelaskan', 'Jika range(10, 1, -2) diubah menjadi range(10, 1, 2), maka perulangan tidak akan dijalankan sama sekali. Hal ini karena langkah bernilai positif (bertambah), sedangkan nilai awalnya 10 dan nilai akhirnya 1. Karena 10 sudah lebih besar dari 1 dan langkahnya bertambah, kondisi perulangan tidak pernah terpenuhi sehingga tidak ada output yang ditampilkan', '2026-03-03 08:15:49', '2026-03-03 08:15:49'),
(64, 36, 'Bagaimana cara kerja perulangan range(10, 1, -2) dalam menghasilkan urutan angka?', 'Perulangan dimulai dari angka 10 sebagai nilai awal. Setiap kali perulangan dijalankan, nilai tersebut akan berkurang 2 karena langkahnya bernilai -2. Setelah mencetak 10, nilainya menjadi 8, kemudian 6, 4, dan 2. Proses ini terus berlangsung selama nilainya masih lebih besar dari 1. Ketika nilainya sudah melewati batas akhir, yaitu kurang dari atau sama dengan 1, maka perulangan berhenti.', '2026-03-03 08:15:49', '2026-03-03 08:15:49'),
(65, 37, 'Apa yang terjadi jika kata ‘ngoding’ dalam kalimat ditulis dalam huruf besar (Ngoding atau NGODING) ?', 'Program tidak akan menyensor kata “Ngoding” jika ditulis dengan huruf besar atau kombinasi huruf besar–kecil, karena perbandingan string bersifat case-sensitive. sensor = \"ngoding\" (huruf kecil), sedangkan kata dalam kalimat adalah \"Ngoding\" (huruf besar di awal). Karena Python membedakan huruf besar dan kecil, kondisi kata == sensor bernilai False, sehingga kata tetap dicetak apa adanya, bukan diganti dengan \"*******\".', '2026-03-03 08:15:49', '2026-03-03 08:15:49'),
(66, 37, 'Apa yang terjadi jika baris 8 saling bertukar dengan baris 6?', 'Setelah baris 6 dan baris 8 ditukar, perintah (\"*******\") berada di dalam else. Karena tidak ada kata dalam kalimat yang sama persis dengan nilai sensor, maka setiap perulangan masuk ke else. Akibatnya, semua kata dicetak sebagai \"*******\", sehingga muncul enam baris bintang.', '2026-03-03 08:15:49', '2026-03-03 08:15:49'),
(67, 37, 'Apa yang terjadi jika pada baris 5 tanda = nya hanya 1?', 'Jika tanda == diubah menjadi =, program akan mengalami error (SyntaxError). Hal ini karena == digunakan untuk membandingkan nilai, sedangkan = digunakan untuk memberi nilai pada variabel dan tidak bisa dipakai dalam kondisi if', '2026-03-03 08:15:49', '2026-03-03 08:15:49'),
(68, 38, 'Siswa kelas X SMK jurusan Pengembangan Perangkat Lunak dan Gim (PPLG) sedang belajar pemrograman Python. Setiap harinya, mereka mengikuti sesi belajar pemrograman selama 45 menit. Kegiatan belajar tersebut berlangsung mulai hari Selasa hingga Jumat setiap minggunya. Mereka ingin mengetahui total waktu belajar pemrograman selama seminggu tersebut dan rata-rata waktu belajar per harinya menggunakan program Python yang mereka buat sendiri. Agar program lebih fleksibel, jumlah hari belajar dimasukkan oleh pengguna.\r\nGuru memberikan contoh seperti program diatas.\r\na.	Perbaiki agar jumlah hari belajar dapat dimasukkan oleh pengguna \r\nb.	Perbaiki agar program dapat menghitung dan menampilkan total waktu belajar pemrograman sesuai dengan jumlah hari yang dimasukkan oleh pengguna\r\nc.	Perbaiki agar program dapat menampilkan rata-rata waktu belajar per harinya', 'https://colab.research.google.com/drive/13MZV1YJVPJlin3UGhi77F4deqb-mMrAq?usp=sharing', '2026-03-03 08:25:14', '2026-03-03 08:25:14'),
(69, 39, 'Masing-masing siswa PPLG diminta untuk menghitung rata-rata nilai dari 5 tugas yang telah mereka kerjakan. Setiap siswa diminta membuat program yang meminta pengguna memasukkan 5 nilai tugas, lalu menggunakan perulangan for untuk menghitung dan menampilkan rata-rata nilai tersebut. Buatkan programnya sesuai dengan ketentuan berikut.\r\na.	Pengguna dapat memasukkan nilai tugas secara berulang sebanyak 5 kali\r\nb.	Program dapat menghitung rata-rata nilai tugas dari nilai yang diinputkan oleh pengguna\r\nc.	Tampilkan keluaran kode programnya.', 'https://colab.research.google.com/drive/1c0ekH16aMKjhP4yXBByMs_ApetaTetFV?usp=sharing', '2026-03-03 08:27:36', '2026-03-03 08:27:36'),
(70, 40, 'Apa yang menjadi keluaran program diatas?', 'Nilai awal x adalah 1. Selama x <= 4, program akan mencetak \"belajar python\" lalu menambah nilai x sebanyak 1. Perulangan terjadi saat x bernilai 1, 2, 3, dan 4. Ketika x menjadi 5, kondisi tidak terpenuhi dan perulangan berhenti. Jadi, kalimat \"belajar python\" ditampilkan sebanyak 4 kali.', '2026-03-03 09:06:55', '2026-03-03 09:06:55'),
(71, 40, 'Apa yang akan terjadi jika x <= 4 diubah menjadi x < 4?', 'Jika kondisi diubah menjadi x < 4, maka perulangan hanya berjalan saat nilai x kurang dari 4. Dengan nilai awal x = 1, program akan mencetak \"belajar python\" saat x bernilai 1, 2, dan 3. Ketika x menjadi 4, kondisi x < 4 tidak terpenuhi, sehingga perulangan berhenti. Oleh karena itu, kalimat tersebut hanya ditampilkan sebanyak 3 kali', '2026-03-03 09:06:55', '2026-03-03 09:06:55'),
(72, 41, 'Apa yang menjadi keluaran program diatas?', 'Nilai awal variabel nilai adalah 5. Selama nilai > 0, program akan mencetak nilai tersebut lalu menguranginya 1 setiap kali perulangan.\r\nProgram akan menampilkan 5 terlebih dahulu, kemudian 4, 3, 2, dan 1. Saat nilai menjadi 0, kondisi tidak terpenuhi sehingga perulangan berhenti. \r\nJadi, keluaran program adalah angka 5 sampai 1 secara menurun', '2026-03-03 09:06:55', '2026-03-03 09:06:55'),
(73, 41, 'Apa yang akan terjadi jika nilai diubah menjadi 10?', 'Jika nilai awal diubah menjadi 10, maka perulangan akan dimulai dari angka 10. Selama nilai masih lebih dari 0, program akan mencetak angka tersebut lalu menguranginya 1 setiap kali perulangan. Akibatnya, program akan menampilkan angka 10, 9, 8, dan seterusnya hingga 1. Ketika nilai menjadi 0, perulangan berhenti.', '2026-03-03 09:06:55', '2026-03-03 09:06:55'),
(74, 42, 'Apakah hasil keluaran dari program 1a yang dijalankan sesuai dengan prediksi kalian? Jelaskan!', 'Hasilnya menampilkan tulisan “belajar python” sebanyak 4 kali. Hal ini terjadi karena perulangan while dijalankan selama kondisi x <= 4 terpenuhi, dengan nilai awal x = 1 dan bertambah 1 setiap pengulangan. Setelah x bernilai 5, kondisi menjadi salah sehingga perulangan berhenti', '2026-03-03 09:13:32', '2026-03-03 09:13:32'),
(75, 42, 'Jelaskan jika hasil keluaran berbeda ataupun sama dari prediksi kalian!', 'Menampilkan tulisan “belajar python” sebanyak 4 kali. Hal ini terjadi karena perulangan while dijalankan selama kondisi x <= 4 terpenuhi, dengan nilai awal x = 1 dan bertambah 1 setiap pengulangan. Setelah x bernilai 5, kondisi menjadi salah sehingga perulangan berhenti', '2026-03-03 09:13:32', '2026-03-03 09:13:32'),
(76, 43, 'Apakah hasil keluaran dari program 1a yang dijalankan sesuai dengan prediksi kalian? Jelaskan!', 'Hasil keluaran sesuai dengan prediksi. Program menampilkan angka 5, 4, 3, 2, dan 1 secara berurutan. Hal ini karena nilai awal nilai adalah 5 dan selama nilai > 0, program akan mencetak nilai tersebut lalu menguranginya satu per satu hingga perulangan berhenti.', '2026-03-03 09:13:33', '2026-03-03 09:13:33'),
(77, 43, 'Jelaskan jika hasil keluaran berbeda ataupun sama dari prediksi kalian!', 'Hasil keluaran sesuai dengan prediksi. Program menampilkan angka 5, 4, 3, 2, dan 1 secara berurutan. Hal ini karena nilai awal nilai adalah 5 dan selama nilai > 0, program akan mencetak nilai tersebut lalu menguranginya satu per satu hingga perulangan berhenti.', '2026-03-03 09:13:33', '2026-03-03 09:13:33'),
(78, 44, 'Apa yang terjadi jika baris 4 dipindahkan ke baris 3?', 'Jika x += 1 dipindahkan ke atas sebelum print, maka nilai x akan bertambah terlebih dahulu setiap kali perulangan dijalankan, baru kemudian perintah print dieksekusi.\r\nNamun, karena perintah print(\"belajar python\") tidak bergantung pada nilai x, jumlah cetakan tetap sama, yaitu 4 kali. Perbedaannya hanya pada urutan prosesnya saja: nilai x dinaikkan terlebih dahulu, lalu kalimat dicetak.', '2026-03-03 09:34:54', '2026-03-03 09:34:54'),
(79, 44, 'Apa fungsi baris x += 1 dalam program tersebut?', 'Baris x += 1 berfungsi untuk menambah nilai variabel x sebanyak 1 setiap kali perulangan dijalankan. Penambahan ini penting agar nilai x terus berubah dan akhirnya tidak lagi memenuhi kondisi x <= 4.\r\nJika baris tersebut tidak ada, maka nilai x akan tetap 1 dan kondisi akan selalu bernilai benar. Akibatnya, perulangan akan berjalan terus-menerus (infinite loop) dan program tidak akan pernah berhenti', '2026-03-03 09:34:54', '2026-03-03 09:34:54'),
(80, 44, 'Bagaimana cara kerja kondisi while x <= 4 pada program diatas?', 'Kondisi while x <= 4 bekerja dengan cara memeriksa nilai x sebelum setiap perulangan dijalankan. Selama nilai x kurang dari atau sama dengan 4, maka perintah di dalam blok while akan terus dieksekusi.\r\nPada program tersebut, karena nilai awal x = 1, maka kondisi bernilai benar dan perulangan dimulai. Setiap selesai mencetak, nilai x bertambah 1. Proses ini berulang saat x bernilai 1, 2, 3, dan 4. Ketika x menjadi 5, kondisi x <= 4 bernilai salah sehingga perulangan berhenti. Itulah sebabnya kalimat “belajar python” ditampilkan sebanyak 4 kali', '2026-03-03 09:34:54', '2026-03-03 09:34:54'),
(81, 45, 'Apa yang akan terjadi jika nilai > 0 diubah menjadi nilai > 5', 'Program tidak akan mencetak apa-apa karena nilai awalnya 5, dan 5 tidak lebih besar dari 5. Perulangan langsung berhenti.', '2026-03-03 09:34:54', '2026-03-03 09:34:54'),
(82, 45, 'Apa yang akan terjadi jika nilai -= 1 dihapus?', 'Kalau nilai -= 1 dihapus, nilai tidak akan berkurang. Karena kondisi nilai > 0 selalu benar (nilai tetap 5), program akan terus mencetak 5 tanpa henti yang disebut infinite loop.', '2026-03-03 09:34:54', '2026-03-03 09:34:54'),
(83, 45, 'Bagaimana cara kerja while pada program diatas?', 'Perulangan dimulai dengan nilai awal nilai = 5. Kondisi while nilai > 0 akan diperiksa terlebih dahulu. Karena 5 lebih besar dari 0, maka program mencetak angka 5.\r\nSetelah itu, nilai dikurangi 1 menjadi 4. Selama nilai masih lebih besar dari 0, perulangan akan terus berjalan. Program kemudian mencetak 4, 3, 2, dan 1 secara berurutan. Ketika nilai menjadi 0, kondisi nilai > 0 tidak terpenuhi, sehingga perulangan berhenti.', '2026-03-03 09:34:54', '2026-03-03 09:34:54'),
(84, 46, 'Apa yang akan terjadi jika baris 1 dan baris 7 ditukar?', 'Program akan langsung tidak berjalan sama sekali karena fokus awalnya False. Perulangan while fokus: tidak akan dieksekusi, dan program langsung mencetak “Belajar berhenti”.', '2026-03-03 09:34:54', '2026-03-03 09:34:54'),
(85, 46, 'Mengapa nilai fokus diubah menjadi False?', 'Nilai fokus diubah supaya perulangan berhenti setelah belajar 3 jam. Kalau tidak diubah, perulangan while fokus: akan jalan terus tanpa berhenti (infinite loop).', '2026-03-03 09:34:54', '2026-03-03 09:34:54'),
(86, 46, 'Bagaimana cara kerja while pada program diatas?', 'Perulangan dimulai dengan nilai fokus = True, sehingga kondisi while fokus bernilai benar dan program masuk ke dalam perulangan. Setiap perulangan, program mencetak “masih fokus belajar” lalu menambah nilai jam sebanyak 1. Ketika nilai jam sudah lebih dari 3, kondisi pada if jam > 3 terpenuhi sehingga fokus diubah menjadi False. Karena fokus sudah bernilai False, perulangan berhenti dan program menampilkan “Belajar berhenti”.', '2026-03-03 09:34:54', '2026-03-03 09:34:54'),
(88, 48, 'Alda sedang mengikuti praktik di laboratorium. Guru ingin memastikan nilai praktik minimal 70. Alda harus terus mengerjakan praktik sampai nilainya mencapai atau melebihi batas minimal. Guru akan memeriksa nilai Alda secara berulang hingga nilainya cukup.\r\na. Ubah program tersebut agar nilai awal Alda dimasukkan oleh pengguna menggunakan input()\r\nb. Modifikasi program agar setiap kali nilai masih di bawah 70, program menampilkan nilai Alda saat ini.\r\nc. Tambahkan perintah agar setelah nilai mencapai atau melebihi 70, program menampilkan pesan:\r\n\"Selamat, nilai Alda cukup!\"', 'Jika ingin melihat hasil silahkan salin link ini dan kemudian buka di browser google colab\r\n\r\nhttps://colab.research.google.com/drive/1h8bVxzKGgbn5MQBI47XjBIeCgmDKJqx0?usp=sharing', '2026-03-03 09:56:22', '2026-03-03 20:03:02'),
(89, 49, 'Andi ingin menabung uang untuk membeli hadiah kecil. Target tabungannya adalah Rp50.000. Setiap kali menabung, dia memasukkan sejumlah uang. Program akan terus meminta input selama total tabungan masih kurang dari target/Rp50.000.\r\na.	Meminta input dari pengguna.\r\nb.	Hitung berapa kali Andi menabung sampai target tercapai.\r\nc.	Tampilkan jumlah berapa kali menabung setelah target tercapai', 'Silahkan copy link dibawah ini dan buka di browser google colab untuk membandingkan hasil \r\n\r\n  https://colab.research.google.com/drive/1Z1MEK-K4GLSH4UnJ0CoTA5KFGa7wMk6h?usp=sharing', '2026-03-03 10:00:00', '2026-03-03 10:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `sessions`
--

CREATE TABLE `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` longtext NOT NULL,
  `last_activity` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `sessions`
--

INSERT INTO `sessions` (`id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity`) VALUES
('7Q9AuitCzi9waZYufJJOESPNR9EZikoEXqFEyAF8', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 'YToyOntzOjY6Il90b2tlbiI7czo0MDoiV2xxNE42U1RiUjE5MDZOQnF4SGhKbXNsVGNHc1pGYXpkZnlCZ2tUaCI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1772683755),
('yaGVdpbjOWbWzktArMLkaotj1v4aVu4e1tAya7Lt', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 'YToyOntzOjY6Il90b2tlbiI7czo0MDoiNEpaODI0SUpheVJRZ1J5eUd2d0dySkRTVVhqZ013YnNHaVBZdUZyaCI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1772611143);

-- --------------------------------------------------------

--
-- Table structure for table `student_answers`
--

CREATE TABLE `student_answers` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `primm_question_id` bigint(20) UNSIGNED NOT NULL,
  `jawaban_siswa` text NOT NULL,
  `skor` int(11) DEFAULT NULL,
  `feedback` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `student_answers`
--

INSERT INTO `student_answers` (`id`, `user_id`, `primm_question_id`, `jawaban_siswa`, `skor`, `feedback`, `created_at`, `updated_at`) VALUES
(34, 9, 51, 'a', NULL, NULL, '2026-03-03 21:54:52', '2026-03-03 21:54:52'),
(35, 9, 52, 'b', NULL, NULL, '2026-03-03 21:54:52', '2026-03-03 21:54:52'),
(36, 9, 53, 'c', NULL, NULL, '2026-03-03 21:54:52', '2026-03-03 21:54:52'),
(37, 9, 54, 'd', NULL, NULL, '2026-03-03 21:54:52', '2026-03-03 21:54:52'),
(38, 9, 55, 'a', NULL, NULL, '2026-03-03 21:56:52', '2026-03-03 21:56:52'),
(39, 9, 56, 'b', NULL, NULL, '2026-03-03 21:56:52', '2026-03-03 21:56:52'),
(40, 9, 57, 'c', NULL, NULL, '2026-03-03 21:56:52', '2026-03-03 21:56:52'),
(41, 9, 58, 'd', NULL, NULL, '2026-03-03 21:56:52', '2026-03-03 21:56:52'),
(42, 9, 59, 'a', NULL, NULL, '2026-03-03 22:01:22', '2026-03-03 22:01:22'),
(43, 9, 60, 'b', NULL, NULL, '2026-03-03 22:01:22', '2026-03-03 22:01:22'),
(44, 9, 61, 'c', NULL, NULL, '2026-03-03 22:01:22', '2026-03-03 22:01:22'),
(45, 9, 62, 'd', NULL, NULL, '2026-03-03 22:01:22', '2026-03-03 22:01:22'),
(46, 9, 63, 'e', NULL, NULL, '2026-03-03 22:01:22', '2026-03-03 22:01:22'),
(47, 9, 64, 'f', NULL, NULL, '2026-03-03 22:01:22', '2026-03-03 22:01:22'),
(48, 9, 65, 'g', NULL, NULL, '2026-03-03 22:01:22', '2026-03-03 22:01:22'),
(49, 9, 66, 'h', NULL, NULL, '2026-03-03 22:01:22', '2026-03-03 22:01:22'),
(50, 9, 67, 'i', NULL, NULL, '2026-03-03 22:01:22', '2026-03-03 22:01:22'),
(51, 9, 68, 'https://colab.research.google.com/drive/1czbbVH8QNDBHulfwQuAxlkPw8JEUpdgY?usp=sharing', NULL, NULL, '2026-03-03 23:38:59', '2026-03-03 23:38:59'),
(53, 9, 69, 'https://colab.research.google.com/drive/12pz3AsKiIH9dYwEjKzbBnR81nLaiJoGt?usp=sharing', NULL, NULL, '2026-03-04 00:33:08', '2026-03-04 00:33:08'),
(54, 9, 70, 'a', NULL, NULL, '2026-03-04 00:36:34', '2026-03-04 00:36:34'),
(55, 9, 71, 'b', NULL, NULL, '2026-03-04 00:36:34', '2026-03-04 00:36:34'),
(56, 9, 72, 'c', NULL, NULL, '2026-03-04 00:36:34', '2026-03-04 00:36:34'),
(57, 9, 73, 'd', NULL, NULL, '2026-03-04 00:36:34', '2026-03-04 00:36:34'),
(58, 9, 74, 'a', NULL, NULL, '2026-03-04 00:37:10', '2026-03-04 00:37:10'),
(59, 9, 75, 'b', NULL, NULL, '2026-03-04 00:37:10', '2026-03-04 00:37:10'),
(60, 9, 76, 'c', NULL, NULL, '2026-03-04 00:37:10', '2026-03-04 00:37:10'),
(61, 9, 77, 'd', NULL, NULL, '2026-03-04 00:37:10', '2026-03-04 00:37:10'),
(62, 9, 78, 'a', NULL, NULL, '2026-03-04 00:38:13', '2026-03-04 00:38:13'),
(63, 9, 79, 'q', NULL, NULL, '2026-03-04 00:38:13', '2026-03-04 00:38:13'),
(64, 9, 80, 'c', NULL, NULL, '2026-03-04 00:38:13', '2026-03-04 00:38:13'),
(65, 9, 81, 'q', NULL, NULL, '2026-03-04 00:38:13', '2026-03-04 00:38:13'),
(66, 9, 82, 'd', NULL, NULL, '2026-03-04 00:38:13', '2026-03-04 00:38:13'),
(67, 9, 83, '1', NULL, NULL, '2026-03-04 00:38:13', '2026-03-04 00:38:13'),
(68, 9, 84, 'q', NULL, NULL, '2026-03-04 00:38:13', '2026-03-04 00:38:13'),
(69, 9, 85, 'q', NULL, NULL, '2026-03-04 00:38:13', '2026-03-04 00:38:13'),
(70, 9, 86, 'q', NULL, NULL, '2026-03-04 00:38:13', '2026-03-04 00:38:13'),
(71, 9, 88, 'sasasa', NULL, NULL, '2026-03-04 00:38:59', '2026-03-04 00:38:59'),
(72, 9, 89, 'ty', NULL, NULL, '2026-03-04 00:40:15', '2026-03-04 00:40:15');

-- --------------------------------------------------------

--
-- Table structure for table `tests`
--

CREATE TABLE `tests` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `link` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `tests`
--

INSERT INTO `tests` (`id`, `title`, `description`, `link`, `created_at`, `updated_at`) VALUES
(1, 'Pretest', 'Pretest adalah test yang diberikan sebelum kegiatan pembelajaran dimulai. Test ini bertujuan untuk mengukur kemampuan awal terhadap materi yang diajarkan', 'https://forms.gle/JWaF72FKUKqSmh1s5', '2026-02-09 19:51:08', '2026-02-28 01:56:19'),
(2, 'Postest', 'Postest adalah test yang diberikan di akhir pembelajaran. Test ini bertujuan untuk mengukur pengetahuan setelah materi diajarkan', 'https://forms.gle/x15s1GkcjnFyd5DB8', '2026-02-28 01:58:50', '2026-02-28 01:58:50');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `jk` enum('L','P') NOT NULL,
  `email` varchar(255) NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `role` enum('siswa','guru') NOT NULL DEFAULT 'siswa',
  `password` varchar(255) NOT NULL,
  `two_factor_secret` text DEFAULT NULL,
  `two_factor_recovery_codes` text DEFAULT NULL,
  `two_factor_confirmed_at` timestamp NULL DEFAULT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `jk`, `email`, `email_verified_at`, `role`, `password`, `two_factor_secret`, `two_factor_recovery_codes`, `two_factor_confirmed_at`, `remember_token`, `created_at`, `updated_at`, `deleted_at`) VALUES
(7, 'Si', 'P', 'sitinuraeni8754@gmail.com', NULL, 'guru', '$2y$12$jm68RcFUDPYQ.V8GfCiSheSQME0KoQChqD1p3VFg8Pvd1bGlSdVyq', NULL, NULL, NULL, NULL, '2026-01-05 01:37:44', '2026-01-05 02:32:52', NULL),
(8, 'Siti Nuraeni', 'P', 'sitinuraeni02@gmail.com', NULL, 'guru', '$2y$12$npdxLS8t3KZzErhGLOTeduKqYBLzbwhE.89Pp6AeS6ExPtfWB0Zje', NULL, NULL, NULL, NULL, '2026-02-09 18:55:28', '2026-02-20 07:36:44', NULL),
(9, 'Eni', 'P', 'eni1@gmail.com', NULL, 'siswa', '$2y$12$Hvkt3UkbUoljEiojjutHyO8qIml3EykrJcIF5Dw5bUM60rFSHNbsy', NULL, NULL, NULL, NULL, '2026-02-09 21:46:50', '2026-02-20 07:38:37', NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `cache`
--
ALTER TABLE `cache`
  ADD PRIMARY KEY (`key`);

--
-- Indexes for table `cache_locks`
--
ALTER TABLE `cache_locks`
  ADD PRIMARY KEY (`key`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `courses`
--
ALTER TABLE `courses`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `course_progress`
--
ALTER TABLE `course_progress`
  ADD PRIMARY KEY (`id`),
  ADD KEY `course_progress_user_id_foreign` (`user_id`),
  ADD KEY `course_progress_course_id_foreign` (`course_id`);

--
-- Indexes for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

--
-- Indexes for table `jobs`
--
ALTER TABLE `jobs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `jobs_queue_index` (`queue`);

--
-- Indexes for table `job_batches`
--
ALTER TABLE `job_batches`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`email`);

--
-- Indexes for table `primms`
--
ALTER TABLE `primms`
  ADD PRIMARY KEY (`id`),
  ADD KEY `primms_course_id_foreign` (`course_id`);

--
-- Indexes for table `primm_questions`
--
ALTER TABLE `primm_questions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `primm_questions_primm_id_foreign` (`primm_id`);

--
-- Indexes for table `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sessions_user_id_index` (`user_id`),
  ADD KEY `sessions_last_activity_index` (`last_activity`);

--
-- Indexes for table `student_answers`
--
ALTER TABLE `student_answers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `student_answers_user_id_foreign` (`user_id`),
  ADD KEY `student_answers_primm_question_id_foreign` (`primm_question_id`);

--
-- Indexes for table `tests`
--
ALTER TABLE `tests`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `courses`
--
ALTER TABLE `courses`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `course_progress`
--
ALTER TABLE `course_progress`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `primms`
--
ALTER TABLE `primms`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=50;

--
-- AUTO_INCREMENT for table `primm_questions`
--
ALTER TABLE `primm_questions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=90;

--
-- AUTO_INCREMENT for table `student_answers`
--
ALTER TABLE `student_answers`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=73;

--
-- AUTO_INCREMENT for table `tests`
--
ALTER TABLE `tests`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `course_progress`
--
ALTER TABLE `course_progress`
  ADD CONSTRAINT `course_progress_course_id_foreign` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `course_progress_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `primms`
--
ALTER TABLE `primms`
  ADD CONSTRAINT `primms_course_id_foreign` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `primm_questions`
--
ALTER TABLE `primm_questions`
  ADD CONSTRAINT `primm_questions_primm_id_foreign` FOREIGN KEY (`primm_id`) REFERENCES `primms` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `student_answers`
--
ALTER TABLE `student_answers`
  ADD CONSTRAINT `student_answers_primm_question_id_foreign` FOREIGN KEY (`primm_question_id`) REFERENCES `primm_questions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `student_answers_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
