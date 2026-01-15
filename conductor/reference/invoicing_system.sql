/*
 Navicat Premium Dump SQL

 Source Server         : Webmin 103.255.109.151
 Source Server Type    : MariaDB
 Source Server Version : 100622 (10.6.22-MariaDB-0ubuntu0.22.04.1)
 Source Host           : 127.0.0.1:3306
 Source Schema         : invoicing_system

 Target Server Type    : MariaDB
 Target Server Version : 100622 (10.6.22-MariaDB-0ubuntu0.22.04.1)
 File Encoding         : 65001

 Date: 15/01/2026 15:14:06
*/

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
-- Table structure for invoice_items
-- ----------------------------
DROP TABLE IF EXISTS `invoice_items`;
CREATE TABLE `invoice_items`  (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `invoice_id` int(11) NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `quantity` decimal(10, 2) NOT NULL DEFAULT 0.00,
  `rate` decimal(15, 2) NOT NULL DEFAULT 0.00,
  `amount` decimal(15, 2) NOT NULL DEFAULT 0.00,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `invoice_id`(`invoice_id`) USING BTREE,
  CONSTRAINT `invoice_items_ibfk_1` FOREIGN KEY (`invoice_id`) REFERENCES `invoices` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT
) ENGINE = InnoDB AUTO_INCREMENT = 347 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of invoice_items
-- ----------------------------
INSERT INTO `invoice_items` VALUES (111, 622, 'Sit tempora expedita quod perferendis libero.', 389.00, 196.00, 76244.00, '2025-02-28 16:48:48', '2025-02-28 16:48:48');
INSERT INTO `invoice_items` VALUES (115, 624, 'PEMBELIAN LISENSI HOSTING 1 thn - bkpsdm.mataasn.konaweutarakab.go.id', 1.00, 600000.00, 600000.00, '2025-03-02 13:38:01', '2025-03-02 13:38:01');
INSERT INTO `invoice_items` VALUES (116, 625, 'PEMBELIAN LISENSI HOSTING 1 thn - https://test-talenta.kenzastore.my.id/', 1.00, 600000.00, 600000.00, '2025-03-02 13:41:24', '2025-03-02 13:41:24');
INSERT INTO `invoice_items` VALUES (117, 626, 'Biaya Lisensi Tahunan WhatsApp Gateway, Oktober 2024 -\r\nOktober 2025', 1.00, 600000.00, 600000.00, '2025-03-02 13:47:56', '2025-03-02 13:47:56');
INSERT INTO `invoice_items` VALUES (118, 626, 'Biaya Storage dan Hosting domain Ziska.id 1 Bulan (Oktober\r\n2024', 1.00, 300000.00, 300000.00, '2025-03-02 13:47:56', '2025-03-02 13:47:56');
INSERT INTO `invoice_items` VALUES (119, 627, 'Biaya Upgrade dan Lisensi Tahunan WhatsApp Gateway,\r\nOktober 2024 - Oktober 2025', 1.00, 300000.00, 300000.00, '2025-03-02 13:55:40', '2025-03-02 13:55:40');
INSERT INTO `invoice_items` VALUES (120, 628, 'Upgrade Template & Engine v2.7.4 (20704) Dashboard:\r\nv24.09.11 KZST WA Blast, September 2024', 1.00, 300000.00, 300000.00, '2025-03-02 13:58:22', '2025-03-02 13:58:22');
INSERT INTO `invoice_items` VALUES (121, 629, 'PERpanjangan Lisensi Hosting domain testtalenta.kenzastore.my.id 1 tahun ~ 5 Feb 2025 - 4 Feb 2026', 1.00, 600000.00, 600000.00, '2025-03-02 14:00:59', '2025-03-02 14:00:59');
INSERT INTO `invoice_items` VALUES (123, 631, 'Perpanjangan Lisensi Server VDS Februari 2024', 89.83, 17200.00, 1545076.00, '2025-03-02 14:08:36', '2025-03-02 14:08:36');
INSERT INTO `invoice_items` VALUES (133, 637, 'Akun FormApprovals.com 3 months subs', 34.00, 17300.00, 588200.00, '2025-03-06 11:28:42', '2025-03-06 11:28:42');
INSERT INTO `invoice_items` VALUES (134, 638, 'Perpanjangan Akun ZOOM.US Maret 2025', 28.41, 17400.00, 494334.00, '2025-03-10 09:22:32', '2025-03-10 09:22:32');
INSERT INTO `invoice_items` VALUES (141, 642, 'Perpanjangan Cloud Storage Wasabi Maret 2025', 128.95, 17400.00, 2243730.00, '2025-03-20 13:36:18', '2025-03-20 13:36:18');
INSERT INTO `invoice_items` VALUES (142, 642, 'Perpanjangan Hosting Digital Ocean/ CiptaHalal Maret 2025', 79.25, 17400.00, 1378950.00, '2025-03-20 13:36:18', '2025-03-20 13:36:18');
INSERT INTO `invoice_items` VALUES (143, 643, 'New Server Contabo VDS Maret\'25', 89.83, 17400.00, 1563042.00, '2025-03-20 13:55:41', '2025-03-20 13:55:41');
INSERT INTO `invoice_items` VALUES (144, 643, 'Panel Webuzo Maret\'25', 19.00, 17400.00, 330600.00, '2025-03-20 13:55:41', '2025-03-20 13:55:41');
INSERT INTO `invoice_items` VALUES (145, 643, 'chatgpt Maret\'25', 24.00, 17400.00, 417600.00, '2025-03-20 13:55:41', '2025-03-20 13:55:41');
INSERT INTO `invoice_items` VALUES (146, 645, 'SSL Comodo Positive Single DV - dashboard.pedulibaik.id (20/03/2025 -\r\n19/03/2026)', 300000.00, 1.00, 300000.00, '2025-03-21 13:18:50', '2025-03-21 13:18:50');
INSERT INTO `invoice_items` VALUES (147, 645, 'SSL Comodo Positive Single DV - pedulibaik.id (20/03/2025 -\r\n19/03/2026)', 300000.00, 1.00, 300000.00, '2025-03-21 13:18:50', '2025-03-21 13:18:50');
INSERT INTO `invoice_items` VALUES (149, 646, 'Api Connector Google Maps', 14.00, 17400.00, 243600.00, '2025-03-21 13:59:13', '2025-03-21 13:59:13');
INSERT INTO `invoice_items` VALUES (150, 647, 'Perpanjangan AKun Zoom Capacity 300 User', 28.41, 17400.00, 494334.00, '2025-04-14 08:14:54', '2025-04-14 08:14:54');
INSERT INTO `invoice_items` VALUES (151, 648, 'Wasabi', 154.11, 17400.00, 2681514.00, '2025-04-25 15:39:12', '2025-04-25 15:39:12');
INSERT INTO `invoice_items` VALUES (152, 650, 'Hosting Digital ocean/ CiptaHalal', 79.25, 17400.00, 1378950.00, '2025-04-25 15:45:19', '2025-04-25 15:45:19');
INSERT INTO `invoice_items` VALUES (153, 651, 'Contabo VDS Server', 89.83, 17400.00, 1563042.00, '2025-04-25 16:04:16', '2025-04-25 16:04:16');
INSERT INTO `invoice_items` VALUES (154, 652, 'CPanel Webuzo', 19.00, 17700.00, 336300.00, '2025-04-25 16:09:30', '2025-04-25 16:09:30');
INSERT INTO `invoice_items` VALUES (155, 653, 'Perpanjangan ChatGPT', 24.00, 17400.00, 417600.00, '2025-04-25 17:22:35', '2025-04-25 17:22:35');
INSERT INTO `invoice_items` VALUES (156, 654, 'Perpanjangan Zoom 300', 28.41, 17700.00, 502857.00, '2025-05-13 14:02:39', '2025-05-13 14:02:39');
INSERT INTO `invoice_items` VALUES (157, 630, 'Perpanjangan Lisensi API WhatsApp 6 Bulan (January 29, 2025 - July 29, 2025)', 1.00, 300000.00, 300000.00, '2025-05-13 21:36:42', '2025-05-13 21:36:42');
INSERT INTO `invoice_items` VALUES (158, 655, 'Perpanjangan Lisensi API WhatsApp 6 Bulan (July 29, 2025 -January 29, 2026)', 1.00, 300000.00, 300000.00, '2025-07-23 14:48:11', '2025-07-23 14:48:11');
INSERT INTO `invoice_items` VALUES (177, 623, 'Pembelian Lisensi 6 Bulan (Juli 2024- Januari 2025) API WhatsApp', 1.00, 300000.00, 300000.00, '2025-09-22 00:25:53', '2025-09-22 00:25:53');
INSERT INTO `invoice_items` VALUES (195, 675, 'Perpanjangan Akun ZOOM, September 2025', 28.41, 17700.00, 502857.00, '2025-09-26 10:35:17', '2025-09-26 10:35:17');
INSERT INTO `invoice_items` VALUES (221, 677, 'Perpanjangan VDS September', 99.71, 17700.00, 1764867.00, '2025-09-26 15:28:31', '2025-09-26 15:28:31');
INSERT INTO `invoice_items` VALUES (222, 677, 'Perpanjangan Lisensi WEbuzo September', 19.00, 17700.00, 336300.00, '2025-09-26 15:28:31', '2025-09-26 15:28:31');
INSERT INTO `invoice_items` VALUES (223, 677, 'Perpanjangan CHatGPT-AI', 26.20, 17700.00, 463740.00, '2025-09-26 15:28:31', '2025-09-26 15:28:31');
INSERT INTO `invoice_items` VALUES (226, 678, 'Perpanjangan Digital Ocean', 79.25, 17700.00, 1402725.00, '2025-09-26 15:35:29', '2025-09-26 15:35:29');
INSERT INTO `invoice_items` VALUES (227, 678, 'Perpanjangan CLoud Wasabi September', 155.48, 17700.00, 2751996.00, '2025-09-26 15:35:29', '2025-09-26 15:35:29');
INSERT INTO `invoice_items` VALUES (228, 679, 'Perpanjangan ZOOM.US Oktober 2025', 28.41, 17700.00, 502857.00, '2025-10-10 10:34:39', '2025-10-10 10:34:39');
INSERT INTO `invoice_items` VALUES (253, 683, 'Perpanjangan Hosting Digital Ocean Oktober 2025', 79.25, 17700.00, 1402725.00, '2025-10-28 10:42:41', '2025-10-28 10:42:41');
INSERT INTO `invoice_items` VALUES (254, 683, 'Perpanjangan Cloud Storage Wasabi Oktober 2025', 152.42, 17700.00, 2697834.00, '2025-10-28 10:42:41', '2025-10-28 10:42:41');
INSERT INTO `invoice_items` VALUES (259, 682, 'Perpanjangan ELevenLabs Oktober 2025', 26.00, 17700.00, 460200.00, '2025-10-28 10:46:46', '2025-10-28 10:46:46');
INSERT INTO `invoice_items` VALUES (260, 682, 'Perpanjangan  ChatGPT Oktober 2025', 26.20, 17700.00, 463740.00, '2025-10-28 10:46:46', '2025-10-28 10:46:46');
INSERT INTO `invoice_items` VALUES (261, 682, 'Perpanjangan Server VDS Oktober 2025', 115.68, 17700.00, 2047536.00, '2025-10-28 10:46:46', '2025-10-28 10:46:46');
INSERT INTO `invoice_items` VALUES (262, 682, 'Perpanjangan API WA 3 Device Oktober 2025', 3.00, 300000.00, 900000.00, '2025-10-28 10:46:46', '2025-10-28 10:46:46');
INSERT INTO `invoice_items` VALUES (264, 685, 'Perpanjangan Akun ZOOM November 2025', 28.41, 17900.00, 508539.00, '2025-11-11 16:37:33', '2025-11-11 16:37:33');
INSERT INTO `invoice_items` VALUES (266, 687, 'Perpanjangan Lisensi FormApproval.com November 2025', 40.00, 17500.00, 700000.00, '2025-11-24 15:20:44', '2025-11-24 15:20:44');
INSERT INTO `invoice_items` VALUES (275, 689, 'Perpanjangan Cloud Storage Wasabi - Oktober 2025', 154.74, 17900.00, 2769846.00, '2025-11-26 14:47:51', '2025-11-26 14:47:51');
INSERT INTO `invoice_items` VALUES (276, 689, 'Perpanjangan Hosting CiptaHalal', 79.25, 17900.00, 1418575.00, '2025-11-26 14:47:51', '2025-11-26 14:47:51');
INSERT INTO `invoice_items` VALUES (302, 690, 'Perpanjangan Akun Eleven Labs AI', 26.00, 17900.00, 465400.00, '2025-11-26 15:26:21', '2025-11-26 15:26:21');
INSERT INTO `invoice_items` VALUES (303, 690, 'Perpanjangan Akun Hosting Webuzoo', 19.00, 17900.00, 340100.00, '2025-11-26 15:26:21', '2025-11-26 15:26:21');
INSERT INTO `invoice_items` VALUES (304, 690, 'Register KiloCode - AI Agent', 14.00, 17900.00, 250600.00, '2025-11-26 15:26:21', '2025-11-26 15:26:21');
INSERT INTO `invoice_items` VALUES (305, 690, 'Perpanjangan ChatGPT', 26.20, 17900.00, 468980.00, '2025-11-26 15:26:21', '2025-11-26 15:26:21');
INSERT INTO `invoice_items` VALUES (306, 690, 'Perpanjangan Kombai - AI Agent', 24.00, 17900.00, 429600.00, '2025-11-26 15:26:21', '2025-11-26 15:26:21');
INSERT INTO `invoice_items` VALUES (309, 692, 'Item 1', 2.00, 100.00, 200.00, '2025-12-03 20:39:00', '2025-12-03 20:39:00');
INSERT INTO `invoice_items` VALUES (310, 692, 'Item 2', 1.00, 50.00, 50.00, '2025-12-03 20:39:00', '2025-12-03 20:39:00');
INSERT INTO `invoice_items` VALUES (313, 686, 'Akun ZOOM 100 User', 18.86, 17549.00, 330939.04, '2025-12-07 09:09:11', '2025-12-07 09:09:11');
INSERT INTO `invoice_items` VALUES (314, 694, 'Perpanjangan Lisensi Zoom.us Desember 2025', 28.41, 18500.00, 525585.00, '2025-12-12 08:41:28', '2025-12-12 08:41:28');
INSERT INTO `invoice_items` VALUES (322, 696, 'Perpanjangan Akun ZOOM 100 User 1 Yrs (Des. 2025 - Des. 2026)', 195.68, 17700.00, 3463536.00, '2025-12-18 15:17:24', '2025-12-18 15:17:24');
INSERT INTO `invoice_items` VALUES (325, 695, 'Perpanjangan Akun ZOOM 100 User', 18.86, 17700.00, 333822.00, '2025-12-22 10:57:34', '2025-12-22 10:57:34');
INSERT INTO `invoice_items` VALUES (342, 697, 'Perpanjangan CLoud Storage WASABI Januari 2026', 159.07, 17700.00, 2815539.00, '2025-12-22 14:08:10', '2025-12-22 14:08:10');
INSERT INTO `invoice_items` VALUES (343, 697, 'Perpanjangan AI Chat-GPT Januari 2026', 27.38, 17700.00, 484626.00, '2025-12-22 14:08:10', '2025-12-22 14:08:10');
INSERT INTO `invoice_items` VALUES (344, 697, 'Perpanjangan Lisensi Hosting Panel Webuzo Januari 2026', 19.85, 17700.00, 351345.00, '2025-12-22 14:08:10', '2025-12-22 14:08:10');
INSERT INTO `invoice_items` VALUES (345, 697, 'Perpanjangan AI eleven labs Januari 2026', 27.17, 17700.00, 480909.00, '2025-12-22 14:08:10', '2025-12-22 14:08:10');
INSERT INTO `invoice_items` VALUES (346, 698, 'Perpanjangan Akun ZOOM Januari 2026', 28.41, 17700.00, 502857.00, '2026-01-12 08:43:00', '2026-01-12 08:43:00');

-- ----------------------------
-- Table structure for invoices
-- ----------------------------
DROP TABLE IF EXISTS `invoices`;
CREATE TABLE `invoices`  (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `invoice_number` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `invoice_date` date NOT NULL,
  `payment_terms` int(11) NOT NULL,
  `due_date` date NOT NULL,
  `bill_to` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `ship_to` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `notes` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `subtotal` decimal(15, 2) NOT NULL DEFAULT 0.00,
  `discount` decimal(15, 2) NOT NULL DEFAULT 0.00,
  `tax` decimal(15, 2) NOT NULL DEFAULT 0.00,
  `shipping` decimal(15, 2) NOT NULL DEFAULT 0.00,
  `total` decimal(15, 2) NOT NULL DEFAULT 0.00,
  `is_paid` tinyint(4) NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE CURRENT_TIMESTAMP,
  `pdf_path` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `invoice_number`(`invoice_number`) USING BTREE,
  INDEX `fk_invoices_users`(`user_id`) USING BTREE,
  CONSTRAINT `fk_invoices_users` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT
) ENGINE = InnoDB AUTO_INCREMENT = 699 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of invoices
-- ----------------------------
INSERT INTO `invoices` VALUES (616, 8, '322', '2025-06-29', 598, '2025-05-19', 'your.email+fakedata90371@gmail.com', '90861', '238', 56320.00, 0.00, 40.00, 0.00, 56320.00, 1, '2025-02-28 16:30:19', '2025-03-02 14:12:47', 'invoices/322.pdf');
INSERT INTO `invoices` VALUES (622, 8, '172', '2024-11-20', 548, '2025-06-28', 'your.email+fakedata84840@gmail.com', '84727', '516', 76244.00, 0.00, 511.00, 0.00, 76244.00, 1, '2025-02-28 16:48:48', '2025-03-02 14:12:47', 'invoices/172.pdf');
INSERT INTO `invoices` VALUES (623, 7, 'INV-JUL-XXIV-373', '2024-07-28', 1, '2025-01-01', 'Amin Anwar', '62 813-2952-0552', '*)Setiap transaksi Payment Service dikenakan fee 5% dari total. Minimal Fee $2 USD.\r\n*)Total transaksi <$50 USD, dikenakan minimal fee ($2 USD).\r\n*)Rate PP hari ini Per $1 USD Rp.17.300,00\r\n', 300000.00, 0.00, 0.00, 0.00, 300000.00, 1, '2025-03-02 13:33:25', '2025-03-02 14:12:47', 'invoices/INV-JUL-XXIV-373.pdf');
INSERT INTO `invoices` VALUES (624, 7, 'INV-JUL-XXIV-361', '2024-07-24', 1, '2025-07-24', 'People Development Consulting', 'Jl. Raya Nginden No.3 Kav.10, \r\nNginden Jangkungan, Kec. Sukolilo, Surabaya, Jawa Timur 60118', '*)Setiap transaksi Payment Service dikenakan fee 5% dari total. Minimal Fee $2 USD.\r\n*)Total transaksi <$50 USD, dikenakan minimal fee ($2 USD).\r\n*)Rate PP hari ini Per $1 USD Rp.17.300,00\r\n', 600000.00, 0.00, 0.00, 0.00, 600000.00, 1, '2025-03-02 13:38:01', '2025-03-02 14:12:47', 'invoices/INV-JUL-XXIV-361.pdf');
INSERT INTO `invoices` VALUES (625, 7, 'INV-JAN-XXIV-340', '2024-01-31', 1, '2025-01-31', 'People Development Consulting', 'Jl. Raya Nginden No.3 Kav.10, \r\nNginden Jangkungan, Kec. Sukolilo, Surabaya, Jawa Timur 60118', '*)Setiap transaksi Payment Service dikenakan fee 5% dari total. Minimal Fee $2 USD.\r\n*)Total transaksi <$50 USD, dikenakan minimal fee ($2 USD).\r\n*)Rate PP hari ini Per $1 USD Rp.17.300,00\r\n', 600000.00, 0.00, 0.00, 0.00, 600000.00, 1, '2025-03-02 13:41:24', '2025-03-02 14:12:48', 'invoices/INV-JAN-XXIV-340.pdf');
INSERT INTO `invoices` VALUES (626, 7, 'INV-NOV-XXIV-425', '2024-10-31', 1, '2025-10-31', 'LAZISMU Jatim', 'PWM Jatim, Jl. Kertomenanggal IV No. 1 Surabaya', '*)Setiap transaksi Payment Service dikenakan fee 5% dari total. Minimal Fee $2 USD.\r\n*)Total transaksi <$50 USD, dikenakan minimal fee ($2 USD).\r\n*)Rate PP hari ini Per $1 USD Rp.17.300,00\r\n', 900000.00, 0.00, 0.00, 0.00, 900000.00, 1, '2025-03-02 13:47:56', '2025-03-02 14:12:48', 'invoices/INV-NOV-XXIV-425.pdf');
INSERT INTO `invoices` VALUES (627, 7, 'INV-OKT-XXIV-411', '2024-10-17', 1, '2025-10-17', 'YDSF Banyuwangi', 'Jl. Simpang GajahMada 05, Banyuwangi', '*)Setiap transaksi Payment Service dikenakan fee 5% dari total. Minimal Fee $2 USD.\r\n*)Total transaksi <$50 USD, dikenakan minimal fee ($2 USD).\r\n*)Rate PP hari ini Per $1 USD Rp.17.300,00\r\n', 300000.00, 0.00, 0.00, 0.00, 300000.00, 1, '2025-03-02 13:55:40', '2025-03-02 14:12:48', 'invoices/INV-OKT-XXIV-411.pdf');
INSERT INTO `invoices` VALUES (628, 7, 'INV-SEP-XXIV-391', '2024-11-19', 1, '2025-11-19', 'YDSF Penghimpunan SBY', 'Kertajaya 8C No 17', '*)Setiap transaksi Payment Service dikenakan fee 5% dari total. Minimal Fee $2 USD.\r\n*)Total transaksi <$50 USD, dikenakan minimal fee ($2 USD).\r\n*)Rate PP hari ini Per $1 USD Rp.17.300,00\r\n', 300000.00, 0.00, 0.00, 0.00, 300000.00, 1, '2025-03-02 13:58:22', '2025-03-02 14:12:48', 'invoices/INV-SEP-XXIV-391.pdf');
INSERT INTO `invoices` VALUES (629, 7, 'INV-JAN-XXV-459', '2025-02-05', 1, '2026-02-05', 'People Development Consulting', 'Jl. Raya Nginden No.3 Kav.10, \r\nNginden Jangkungan, Kec. Sukolilo, Surabaya, Jawa Timur 60118', '*)Setiap transaksi Payment Service dikenakan fee 5% dari total. Minimal Fee $2 USD.\r\n*)Total transaksi <$50 USD, dikenakan minimal fee ($2 USD).\r\n*)Rate PP hari ini Per $1 USD Rp.17.300,00\r\n', 600000.00, 0.00, 0.00, 0.00, 600000.00, 1, '2025-03-02 14:00:59', '2025-03-02 14:12:48', 'invoices/INV-JAN-XXV-459.pdf');
INSERT INTO `invoices` VALUES (630, 7, 'INV-JAN-XXV-461', '2025-01-29', 1, '2025-07-29', 'Amin Anwar', '62 813-2952-0552', '*)Setiap transaksi Payment Service dikenakan fee 5% dari total. Minimal Fee $2 USD.\r\n*)Total transaksi <$50 USD, dikenakan minimal fee ($2 USD).\r\n*)Rate PP hari ini Per $1 USD Rp.17.300,00\r\n', 300000.00, 0.00, 0.00, 0.00, 300000.00, 1, '2025-03-02 14:03:59', '2025-05-13 21:36:42', 'invoices/INV-JAN-XXV-461.pdf');
INSERT INTO `invoices` VALUES (631, 7, 'INV-JAN-XXV-463', '2025-01-31', 1, '2025-02-28', 'YDSF', 'Kertajaya 8C No 17', '*)Setiap transaksi Payment Service dikenakan fee 5% dari total. Minimal Fee $2 USD.\r\n*)Total transaksi <$50 USD, dikenakan minimal fee ($2 USD).\r\n*)Rate PP hari ini Per $1 USD Rp.17.200,00\r\n', 1545076.00, 0.00, 0.00, 0.00, 1545076.00, 1, '2025-03-02 14:08:36', '2025-03-02 14:12:48', 'invoices/INV-JAN-XXV-463.pdf');
INSERT INTO `invoices` VALUES (637, 7, 'INV-FEB-XXV-475', '2025-02-26', 1, '2026-02-26', 'YDSF', 'Kertajaya 8C No 17', '*)Setiap transaksi Payment Service dikenakan fee 5% dari total. Minimal Fee $2 USD.\r\n*)Total transaksi <$50 USD, dikenakan minimal fee ($2 USD).\r\n*)Rate PP hari ini Per $1 USD Rp.17.400,00\r\n', 588200.00, 0.00, 0.00, 0.00, 588200.00, 1, '2025-03-06 11:28:42', '2025-03-06 14:51:48', 'invoices/INV-FEB-XXV-475.pdf');
INSERT INTO `invoices` VALUES (638, 7, 'INV-MAR-XXV-477', '2025-03-10', 1, '2025-03-10', 'YDSF', 'Kertajaya 8C No 17', '*)Setiap transaksi Payment Service dikenakan fee 5% dari total. Minimal Fee $2 USD.\r\n*)Total transaksi <$50 USD, dikenakan minimal fee ($2 USD).\r\n*)Rate PP hari ini Per $1 USD Rp.17.400,00\r\n', 494334.00, 0.00, 0.00, 0.00, 494334.00, 1, '2025-03-10 09:22:32', '2025-04-25 15:33:54', 'invoices/INV-MAR-XXV-477.pdf');
INSERT INTO `invoices` VALUES (642, 7, 'INV-MAR-XXV-483', '2025-03-05', 1, '2025-03-05', 'YDSF', 'Kertajaya 8C No 17', '*)Setiap transaksi Payment Service dikenakan fee 5% dari total. Minimal Fee $2 USD.\r\n*)Total transaksi <$50 USD, dikenakan minimal fee ($2 USD).\r\n*)Rate PP hari ini Per $1 USD Rp.17.300,00\r\n', 3622680.00, 0.00, 0.00, 0.00, 3622680.00, 1, '2025-03-20 13:36:18', '2025-04-25 15:33:54', 'invoices/INV-MAR-XXV-483.pdf');
INSERT INTO `invoices` VALUES (643, 7, 'INV-MAR-XXV-485', '2025-03-20', 1, '2025-03-20', 'YDSF', 'Kertajaya 8C No 17', '*)Setiap transaksi Payment Service dikenakan fee 5% dari total. Minimal Fee $2 USD.\r\n*)Total transaksi <$50 USD, dikenakan minimal fee ($2 USD).\r\n*)Rate PP hari ini Per $1 USD Rp.17.400,00\r\n', 2311242.00, 0.00, 0.00, 0.00, 2311242.00, 1, '2025-03-20 13:55:41', '2025-04-25 15:33:53', 'invoices/INV-MAR-XXV-485.pdf');
INSERT INTO `invoices` VALUES (645, 7, 'INV-MAR-XXV-487', '2025-03-12', 1, '2025-03-12', 'YDSF', 'Kertajaya 8C No 17', '*)Setiap transaksi Payment Service dikenakan fee 5% dari total. Minimal Fee $2 USD.\r\n*)Total transaksi <$50 USD, dikenakan minimal fee ($2 USD).\r\n*)Rate PP hari ini Per $1 USD Rp.17.400,00\r\n', 600000.00, 0.00, 0.00, 0.00, 600000.00, 1, '2025-03-21 13:18:50', '2025-04-25 15:33:53', 'invoices/INV-MAR-XXV-487.pdf');
INSERT INTO `invoices` VALUES (646, 7, 'INV-MAR-XXV-489', '2025-03-13', 1, '2025-03-13', 'YDSF', 'Kertajaya 8C No 17', '*)Setiap transaksi Payment Service dikenakan fee 5% dari total. Minimal Fee $2 USD.\r\n*)Total transaksi <$50 USD, dikenakan minimal fee ($2 USD).\r\n*)Rate PP hari ini Per $1 USD Rp.17.400,00\r\n', 243600.00, 0.00, 0.00, 0.00, 243600.00, 1, '2025-03-21 13:57:15', '2025-04-25 15:33:53', 'invoices/INV-MAR-XXV-489.pdf');
INSERT INTO `invoices` VALUES (647, 7, 'INV-APR-XXV-491', '2025-04-10', 1, '2025-04-10', 'YDSF', 'Kertajaya 8C No 17', '*)Setiap transaksi Payment Service dikenakan fee 5% dari total. Minimal Fee $2 USD.\r\n*)Total transaksi <$50 USD, dikenakan minimal fee ($2 USD).\r\n*)Rate PP hari ini Per $1 USD Rp.17.400,00\r\n', 494334.00, 0.00, 0.00, 0.00, 494334.00, 1, '2025-04-14 08:14:54', '2025-04-25 15:33:52', 'invoices/INV-APR-XXV-491.pdf');
INSERT INTO `invoices` VALUES (648, 7, 'INV-APR-XXV-493', '2025-04-04', 1, '2025-04-04', 'YDSF', 'Kertajaya 8C No 17', '*)Setiap transaksi Payment Service dikenakan fee 5% dari total. Minimal Fee $2 USD.\r\n*)Total transaksi <$50 USD, dikenakan minimal fee ($2 USD).\r\n*)Rate PP hari ini Per $1 USD Rp.17.400,00\r\n', 2681514.00, 0.00, 0.00, 0.00, 2681514.00, 1, '2025-04-25 15:39:12', '2025-04-25 16:10:25', 'invoices/INV-APR-XXV-493.pdf');
INSERT INTO `invoices` VALUES (650, 7, 'INV-APR-XXV-495', '2025-04-05', 1, '2025-04-05', 'YDSF', 'Kertajaya 8C No 17', '*)Setiap transaksi Payment Service dikenakan fee 5% dari total. Minimal Fee $2 USD.\r\n*)Total transaksi <$50 USD, dikenakan minimal fee ($2 USD).\r\n*)Rate PP hari ini Per $1 USD Rp.17.400,00\r\n', 1378950.00, 0.00, 0.00, 0.00, 1378950.00, 1, '2025-04-25 15:45:19', '2025-04-25 16:10:22', 'invoices/INV-APR-XXV-495.pdf');
INSERT INTO `invoices` VALUES (651, 7, 'INV-APR-XXV-497', '2025-04-18', 1, '2025-04-18', 'YDSF', 'Kertajaya 8C No 17', '*)Setiap transaksi Payment Service dikenakan fee 5% dari total. Minimal Fee $2 USD.\r\n*)Total transaksi <$50 USD, dikenakan minimal fee ($2 USD).\r\n*)Rate PP hari ini Per $1 USD Rp.17.400,00\r\n', 1563042.00, 0.00, 0.00, 0.00, 1563042.00, 1, '2025-04-25 16:04:16', '2025-04-25 16:10:24', 'invoices/INV-APR-XXV-497.pdf');
INSERT INTO `invoices` VALUES (652, 7, 'INV-APR-XXV-499', '2025-04-24', 1, '2025-04-24', 'YDSF', 'Kertajaya 8C No 17', '*)Setiap transaksi Payment Service dikenakan fee 5% dari total. Minimal Fee $2 USD.\r\n*)Total transaksi <$50 USD, dikenakan minimal fee ($2 USD).\r\n*)Rate PP hari ini Per $1 USD Rp.17.700,00\r\n', 336300.00, 0.00, 0.00, 0.00, 336300.00, 1, '2025-04-25 16:09:30', '2025-04-25 16:10:20', 'invoices/INV-APR-XXV-499.pdf');
INSERT INTO `invoices` VALUES (653, 7, 'INV-APR-XXV-501', '2025-04-21', 1, '2025-04-21', 'YDSF', 'Kertajaya 8C No 17', '*)Setiap transaksi Payment Service dikenakan fee 5% dari total. Minimal Fee $2 USD.\r\n*)Total transaksi <$50 USD, dikenakan minimal fee ($2 USD).\r\n*)Rate PP hari ini Per $1 USD Rp.17.400,00\r\n', 417600.00, 0.00, 0.00, 0.00, 417600.00, NULL, '2025-04-25 17:22:35', '2025-04-25 17:22:35', 'invoices/INV-APR-XXV-501.pdf');
INSERT INTO `invoices` VALUES (654, 7, 'INV-MEI-XXV-503', '2025-05-13', 1, '2025-05-13', 'YDSF', 'Kertajaya 8C No 17', '*)Setiap transaksi Payment Service dikenakan fee 5% dari total. Minimal Fee $2 USD.\r\n*)Total transaksi <$50 USD, dikenakan minimal fee ($2 USD).\r\n*)Rate PP hari ini Per $1 USD Rp.17.300,00\r\n', 502857.00, 0.00, 0.00, 0.00, 502857.00, NULL, '2025-05-13 14:02:39', '2025-05-13 14:02:39', 'invoices/INV-MEI-XXV-503.pdf');
INSERT INTO `invoices` VALUES (655, 7, 'INV-JUL-XXV-527', '2025-07-29', 1, '2026-01-29', 'Amin Anwar', '62 813-2952-0552', '*)Setiap transaksi Payment Service dikenakan fee 5% dari total. Minimal Fee $2 USD.\r\n*)Total transaksi <$50 USD, dikenakan minimal fee ($2 USD).\r\n*)Rate PP hari ini Per $1 USD Rp.17.700,00\r\n', 300000.00, 0.00, 0.00, 0.00, 300000.00, 1, '2025-07-23 14:48:11', '2025-07-28 11:01:51', 'invoices/INV-JUL-XXV-527.pdf');
INSERT INTO `invoices` VALUES (675, 7, 'INV-SEP-XXV-545', '2025-09-11', 1, '2025-09-11', 'YDSF', 'Kertajaya 8C No 17', '*)Setiap transaksi Payment Service dikenakan fee 5% dari total. Minimal Fee $2 USD.\r\n*)Total transaksi <= $50 USD, dikenakan minimal fee ($2 USD).\r\n*)Rate PP hari ini Per $1 USD Rp.17.700,00\r\n', 502857.00, 0.00, 0.00, 0.00, 502857.00, NULL, '2025-09-26 10:35:17', '2025-09-26 10:35:17', 'invoices/INV-SEP-XXV-545.pdf');
INSERT INTO `invoices` VALUES (677, 7, 'INV-SEP-XXV-551', '2025-09-18', 1, '2025-09-18', 'YDSF', 'Kertajaya 8C No 17', '*)Setiap transaksi Payment Service dikenakan fee 5% dari total. Minimal Fee $2 USD.\r\n*)Total transaksi <= $50 USD, dikenakan minimal fee ($2 USD).\r\n*)Rate PP hari ini Per $1 USD Rp.17.700,00\r\n', 2564907.00, 0.00, 0.00, 0.00, 2564907.00, 1, '2025-09-26 15:28:31', '2025-11-27 18:57:28', 'invoices/INV-SEP-XXV-551.pdf');
INSERT INTO `invoices` VALUES (678, 7, 'INV-SEP-XXV-549', '2025-09-04', 1, '2025-09-04', 'YDSF', 'Kertajaya 8C No 17', '*)Setiap transaksi Payment Service dikenakan fee 5% dari total. Minimal Fee $2 USD.\r\n*)Total transaksi <= $50 USD, dikenakan minimal fee ($2 USD).\r\n*)Rate PP hari ini Per $1 USD Rp.17.700,00\r\n', 4154721.00, 0.00, 0.00, 0.00, 4154721.00, NULL, '2025-09-26 15:35:29', '2025-09-26 15:35:29', 'invoices/INV-SEP-XXV-549.pdf');
INSERT INTO `invoices` VALUES (679, 7, 'INV-OCT-XXV-551', '2025-10-10', 1, '2025-10-10', 'YDSF Pusat', 'Gubeng Kertajaya 8C no 17', '*)Setiap transaksi Payment Service dikenakan fee 5% dari total. Minimal Fee $2 USD.\r\n*)Total transaksi <= $50 USD, dikenakan minimal fee ($2 USD).\r\n*)Rate PP hari ini Per $1 USD Rp.17.700,00\r\n', 502857.00, 0.00, 0.00, 0.00, 502857.00, 1, '2025-10-10 10:34:39', '2025-10-10 10:51:10', 'invoices/INV-OCT-XXV-551.pdf');
INSERT INTO `invoices` VALUES (682, 7, 'INV-OCT-XXV-553', '2025-10-27', 1, '2025-10-27', 'YDSF Pusat', 'Gubeng Kertajaya 8C No 17', '*)Setiap transaksi Payment Service dikenakan fee 5% dari total. Minimal Fee $2 USD.\r\n*)Total transaksi <= $50 USD, dikenakan minimal fee ($2 USD).\r\n*)Rate PP hari ini Per $1 USD Rp.17.700,00', 3871476.00, 0.00, 0.00, 0.00, 3871476.00, NULL, '2025-10-28 09:56:15', '2025-10-28 10:44:32', 'invoices/INV-OCT-XXV-553.pdf');
INSERT INTO `invoices` VALUES (683, 7, 'INV-OCT-XXV-555', '2025-10-28', 1, '2025-10-28', 'YDSF Pusat', 'Gubeng Kertajaya 8C No 17', '*)Setiap transaksi Payment Service dikenakan fee 5% dari total. Minimal Fee $2 USD.\r\n*)Total transaksi <= $50 USD, dikenakan minimal fee ($2 USD).\r\n*)Rate PP hari ini Per $1 USD Rp.17.700,00\r\n', 4100559.00, 0.00, 0.00, 0.00, 4100559.00, NULL, '2025-10-28 10:42:07', '2025-10-28 10:42:29', 'invoices/INV-OCT-XXV-555.pdf');
INSERT INTO `invoices` VALUES (685, 7, 'INV-NOV-XXV-557', '2025-11-11', 1, '2025-11-11', 'YDSF Pusat', 'Gubeng Kertajaya 8C No 17', '*)Setiap transaksi Payment Service dikenakan fee 5% dari total. Minimal Fee $2 USD.\r\n*)Total transaksi <= $50 USD, dikenakan minimal fee ($2 USD).\r\n*)Rate PP hari ini Per $1 USD Rp.17.900,00\r\n', 508539.00, 0.00, 0.00, 0.00, 508539.00, NULL, '2025-11-11 16:37:33', '2025-11-11 16:37:33', 'invoices/INV-NOV-XXV-557.pdf');
INSERT INTO `invoices` VALUES (686, 7, 'INV-NOV-XXV-559', '2025-11-17', 1, '2025-12-17', 'arimaindonesia.com', 'Jl. Raya Wiyung Indah No.7 Surabaya 60228', '*)Setiap transaksi Payment Service dikenakan fee 5% dari total. Minimal Fee $2 USD.\r\n*)Total transaksi <= $50 USD, dikenakan minimal fee ($2 USD).\r\n*)Rate PP hari ini Per $1 USD Rp.17.549,00\r\n', 330939.04, 0.00, 0.00, 0.00, 330939.04, 1, '2025-11-17 14:14:36', '2025-12-07 09:09:11', 'invoices/INV-NOV-XXV-559.pdf');
INSERT INTO `invoices` VALUES (687, 7, 'INV-NOV-XXV-561', '2025-11-24', 1, '2025-11-24', 'YDSF PUSAT', 'Kertajaya 8C No 17', '*)Setiap transaksi Payment Service dikenakan fee 5% dari total. Minimal Fee $2 USD.\r\n*)Total transaksi <= $50 USD, dikenakan minimal fee ($2 USD).\r\n*)Rate PP hari ini Per $1 USD Rp.17.500,00\r\n', 700000.00, 0.00, 0.00, 0.00, 700000.00, 1, '2025-11-24 15:20:44', '2025-11-27 08:12:44', 'invoices/INV-NOV-XXV-561.pdf');
INSERT INTO `invoices` VALUES (689, 7, 'INV-NOV-XXV-563', '2025-11-04', 1, '2025-11-04', 'YDSF PUSAT', 'GUBENG KERTAJAYA 8C NO 17', '*)Setiap transaksi Payment Service dikenakan fee 5% dari total. Minimal Fee $2 USD.\r\n*)Total transaksi <= $50 USD, dikenakan minimal fee ($2 USD).\r\n*)Rate PP hari ini Per $1 USD Rp.17.900,00\r\n', 4188421.00, 0.00, 0.00, 0.00, 4188421.00, 1, '2025-11-26 14:47:51', '2025-11-27 18:56:29', 'invoices/INV-NOV-XXV-563.pdf');
INSERT INTO `invoices` VALUES (690, 7, 'INV-NOV-XXV-565', '2025-11-26', 1, '2025-11-26', 'YDSF PUSAT', 'GUBENG KERTAJAYA 8C NO 17', '*)Setiap transaksi Payment Service dikenakan fee 5% dari total. Minimal Fee $2 USD.\r\n*)Total transaksi <= $50 USD, dikenakan minimal fee ($2 USD).\r\n*)Rate PP hari ini Per $1 USD Rp.17.900,00\r\n', 1954680.00, 0.00, 0.00, 0.00, 1954680.00, 1, '2025-11-26 15:16:08', '2025-11-27 18:56:12', 'invoices/INV-NOV-XXV-565.pdf');
INSERT INTO `invoices` VALUES (692, 32, 'INV-TEST-001', '2025-12-03', 14, '2025-12-03', 'Test Customer', 'Test Address', '', 250.00, 0.00, 10.00, 0.00, 275.00, NULL, '2025-12-03 20:39:00', '2025-12-03 20:39:00', 'https://inv.kenzastore.my.id/invoices/INV-TEST-001.pdf');
INSERT INTO `invoices` VALUES (694, 7, 'INV-DEC-XXV-567', '2025-12-12', 1, '2025-12-12', 'YDSF PUSAT', 'Kertajaya 8C No 17', '*)Setiap transaksi Payment Service dikenakan fee 5% dari total. Minimal Fee $2 USD.\r\n*)Total transaksi <= $50 USD, dikenakan minimal fee ($2 USD).\r\n*)Rate PP hari ini Per $1 USD Rp.18.500,00\r\n', 525585.00, 0.00, 0.00, 0.00, 525585.00, 1, '2025-12-12 08:41:28', '2025-12-13 08:18:42', 'invoices/INV-DEC-XXV-567.pdf');
INSERT INTO `invoices` VALUES (695, 7, 'INV-DEC-XXV-569', '2025-12-18', 1, '2026-01-18', 'arimaindonesia.com', 'Jl. Raya Wiyung Indah No.7 Surabaya 60228', '*)Setiap transaksi Payment Service dikenakan fee 5% dari total. Minimal Fee $2 USD.\r\n*)Total transaksi <= $50 USD, dikenakan minimal fee ($2 USD).\r\n*)Rate PP hari ini Per $1 USD Rp.17.700,00\r\n\r\nPembayaran ke Rekening : \r\nBtpn jenius/ smbc \r\n90120033145 an R. Khairul Anam', 333822.00, 0.00, 0.00, 0.00, 333822.00, 1, '2025-12-18 14:17:02', '2025-12-22 10:56:59', 'invoices/INV-DEC-XXV-569.pdf');
INSERT INTO `invoices` VALUES (696, 7, 'INV-DEC-XXV-571', '2025-12-18', 1, '2026-12-18', 'arimaindonesia.com', 'Jl. Raya Wiyung Indah No.7 Surabaya 60228', '*)Setiap transaksi Payment Service dikenakan fee 5% dari total. Minimal Fee $2 USD.\r\n*)Total transaksi <= $50 USD, dikenakan minimal fee ($2 USD).\r\n*)Rate PP hari ini Per $1 USD Rp.17.700,00\r\n\r\nPembayaran ke Rekening : \r\nBtpn jenius/ smbc \r\n90120033145 an R. Khairul Anam', 3463536.00, 0.00, 0.00, 0.00, 3463536.00, NULL, '2025-12-18 15:13:54', '2025-12-18 15:15:45', 'invoices/INV-DEC-XXV-571.pdf');
INSERT INTO `invoices` VALUES (697, 7, 'INV-DEC-XXV-573', '2025-12-22', 1, '2025-12-22', 'YDSF Pusat', 'GUbeng Kertajaya 8C no 17', '*)Setiap transaksi Payment Service dikenakan fee 5% dari total. Minimal Fee $2 USD.\r\n*)Total transaksi <= $50 USD, dikenakan minimal fee ($2 USD).\r\n*)Rate PP hari ini Per $1 USD Rp.17.700,00\r\n', 4132419.00, 0.00, 0.00, 0.00, 4132419.00, NULL, '2025-12-22 10:59:38', '2025-12-22 14:05:44', 'invoices/INV-DEC-XXV-573.pdf');
INSERT INTO `invoices` VALUES (698, 7, 'INV-JAN-XXVI-001', '2026-01-12', 1, '2026-01-12', 'YDSF Pusat', 'Gubeng Kertajaya 8C No 17', '*)Setiap transaksi Payment Service dikenakan fee 5% dari total. Minimal Fee $2 USD.\r\n*)Total transaksi <= $50 USD, dikenakan minimal fee ($2 USD).\r\n*)Rate PP hari ini Per $1 USD Rp.17.300,00\r\n', 502857.00, 0.00, 0.00, 0.00, 502857.00, NULL, '2026-01-12 08:43:00', '2026-01-12 08:43:00', 'invoices/INV-JAN-XXVI-001.pdf');

-- ----------------------------
-- Table structure for logs
-- ----------------------------
DROP TABLE IF EXISTS `logs`;
CREATE TABLE `logs`  (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `action` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `invoice_id` int(11) NULL DEFAULT NULL,
  `details` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `user_id`(`user_id`) USING BTREE,
  INDEX `logs_ibfk_2`(`invoice_id`) USING BTREE,
  CONSTRAINT `logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  CONSTRAINT `logs_ibfk_2` FOREIGN KEY (`invoice_id`) REFERENCES `invoices` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT
) ENGINE = InnoDB AUTO_INCREMENT = 150 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of logs
-- ----------------------------
INSERT INTO `logs` VALUES (2, 8, 'Created Invoice', NULL, 'Invoice Number: 322', '2025-02-28 16:30:19');
INSERT INTO `logs` VALUES (3, 8, 'Created Invoice', NULL, 'Invoice Number: 374', '2025-02-28 16:33:08');
INSERT INTO `logs` VALUES (4, 8, 'Created Invoice', NULL, 'Invoice Number: 224', '2025-02-28 16:38:26');
INSERT INTO `logs` VALUES (6, 8, 'Deleted Invoice', NULL, 'Deleted Invoice Number: 374', '2025-02-28 16:46:05');
INSERT INTO `logs` VALUES (8, 8, 'Deleted Invoice', NULL, 'Deleted Invoice Number: 286', '2025-02-28 16:48:18');
INSERT INTO `logs` VALUES (9, 8, 'Created Invoice', 622, 'Invoice Number: 172', '2025-02-28 16:48:48');
INSERT INTO `logs` VALUES (10, 8, 'Deleted Invoice', NULL, 'Deleted Invoice Number: 207', '2025-02-28 16:58:16');
INSERT INTO `logs` VALUES (11, 7, 'Deleted Invoice', NULL, 'Deleted Invoice Number: 329', '2025-03-02 13:28:36');
INSERT INTO `logs` VALUES (12, 7, 'Deleted Invoice', NULL, 'Deleted Invoice Number: 475', '2025-03-02 13:28:49');
INSERT INTO `logs` VALUES (15, 7, 'Deleted Invoice', NULL, 'Deleted Invoice Number: 494', '2025-03-02 13:30:09');
INSERT INTO `logs` VALUES (16, 7, 'Deleted Invoice', NULL, 'Deleted Invoice Number: 15', '2025-03-02 13:30:18');
INSERT INTO `logs` VALUES (17, 7, 'Created Invoice', 623, 'Invoice Number: INV-JUL-XXIV-373', '2025-03-02 13:33:25');
INSERT INTO `logs` VALUES (18, 7, 'Created Invoice', 624, 'Invoice Number: INV-JUL-XXIV-361', '2025-03-02 13:38:01');
INSERT INTO `logs` VALUES (19, 7, 'Created Invoice', 625, 'Invoice Number: INV-JAN-XXIV-340', '2025-03-02 13:41:24');
INSERT INTO `logs` VALUES (20, 7, 'Created Invoice', 626, 'Invoice Number: INV-NOV-XXIV-425', '2025-03-02 13:47:56');
INSERT INTO `logs` VALUES (21, 7, 'Created Invoice', 627, 'Invoice Number: INV-OKT-XXIV-411', '2025-03-02 13:55:40');
INSERT INTO `logs` VALUES (22, 7, 'Created Invoice', 628, 'Invoice Number: INV-SEP-XXIV-391', '2025-03-02 13:58:22');
INSERT INTO `logs` VALUES (23, 7, 'Created Invoice', 629, 'Invoice Number: INV-JAN-XXV-459', '2025-03-02 14:00:59');
INSERT INTO `logs` VALUES (24, 7, 'Created Invoice', 630, 'Invoice Number: INV-JAN-XXV-461', '2025-03-02 14:03:59');
INSERT INTO `logs` VALUES (25, 7, 'Created Invoice', 631, 'Invoice Number: INV-JAN-XXV-463', '2025-03-02 14:08:36');
INSERT INTO `logs` VALUES (28, 7, 'Deleted Invoice', NULL, 'Deleted Invoice Number: 334', '2025-03-05 15:56:16');
INSERT INTO `logs` VALUES (33, 7, 'Deleted Invoice', NULL, 'Deleted Invoice Number: 292', '2025-03-06 11:07:51');
INSERT INTO `logs` VALUES (34, 7, 'Deleted Invoice', NULL, 'Deleted Invoice Number: 102', '2025-03-06 11:07:54');
INSERT INTO `logs` VALUES (38, 7, 'Created Invoice', 637, 'Invoice Number: INV-FEB-XXV-475', '2025-03-06 11:28:42');
INSERT INTO `logs` VALUES (39, 7, 'Deleted Invoice', NULL, 'Deleted Invoice Number: 538', '2025-03-06 15:44:09');
INSERT INTO `logs` VALUES (40, 7, 'Created Invoice', 638, 'Invoice Number: INV-MAR-XXV-477', '2025-03-10 09:22:32');
INSERT INTO `logs` VALUES (42, 7, 'Deleted Invoice', NULL, 'Deleted Invoice Number: 367', '2025-03-14 13:31:04');
INSERT INTO `logs` VALUES (48, 7, 'Created Invoice', 642, 'Invoice Number: INV-MAR-XXV-483', '2025-03-20 13:36:18');
INSERT INTO `logs` VALUES (49, 7, 'Created Invoice', 643, 'Invoice Number: INV-MAR-XXV-485', '2025-03-20 13:55:41');
INSERT INTO `logs` VALUES (50, 7, 'Deleted Invoice', NULL, 'Deleted Invoice Number: INV-MAR-XXV-479', '2025-03-20 14:00:10');
INSERT INTO `logs` VALUES (51, 7, 'Deleted Invoice', NULL, 'Deleted Invoice Number: INV-MAR-XXV-481', '2025-03-20 14:00:28');
INSERT INTO `logs` VALUES (52, 7, 'Created Invoice', 645, 'Invoice Number: INV-MAR-XXV-487', '2025-03-21 13:18:50');
INSERT INTO `logs` VALUES (53, 7, 'Created Invoice', 646, 'Invoice Number: INV-MAR-XXV-489', '2025-03-21 13:57:15');
INSERT INTO `logs` VALUES (54, 7, 'Updated Invoice', 646, 'Updated Invoice Number: INV-MAR-XXV-489', '2025-03-21 13:59:13');
INSERT INTO `logs` VALUES (55, 7, 'Created Invoice', 647, 'Invoice Number: INV-APR-XXV-491', '2025-04-14 08:14:54');
INSERT INTO `logs` VALUES (56, 7, 'Created Invoice', 648, 'Invoice Number: INV-APR-XXV-493', '2025-04-25 15:39:12');
INSERT INTO `logs` VALUES (57, 7, 'Created Invoice', 650, 'Invoice Number: INV-APR-XXV-495', '2025-04-25 15:45:19');
INSERT INTO `logs` VALUES (58, 7, 'Created Invoice', 651, 'Invoice Number: INV-APR-XXV-497', '2025-04-25 16:04:16');
INSERT INTO `logs` VALUES (59, 7, 'Created Invoice', 652, 'Invoice Number: INV-APR-XXV-499', '2025-04-25 16:09:30');
INSERT INTO `logs` VALUES (60, 7, 'Created Invoice', 653, 'Invoice Number: INV-APR-XXV-501', '2025-04-25 17:22:35');
INSERT INTO `logs` VALUES (61, 7, 'Created Invoice', 654, 'Invoice Number: INV-MEI-XXV-503', '2025-05-13 14:02:39');
INSERT INTO `logs` VALUES (62, 7, 'Updated Invoice', 630, 'Updated Invoice Number: INV-JAN-XXV-461', '2025-05-13 21:36:42');
INSERT INTO `logs` VALUES (63, 7, 'Created Invoice', 655, 'Invoice Number: INV-JUL-XXV-527', '2025-07-23 14:48:11');
INSERT INTO `logs` VALUES (69, 7, 'Deleted Invoice', NULL, 'Deleted Invoice Number: INV-463', '2025-09-18 19:31:36');
INSERT INTO `logs` VALUES (70, 7, 'Deleted Invoice', NULL, 'Deleted Invoice Number: INV-318', '2025-09-18 19:31:40');
INSERT INTO `logs` VALUES (71, 7, 'Deleted Invoice', NULL, 'Deleted Invoice Number: INV-459', '2025-09-18 19:31:43');
INSERT INTO `logs` VALUES (79, 7, 'Deleted Invoice', NULL, 'Deleted Invoice Number: INV-SEP-XXV-551', '2025-09-22 00:27:51');
INSERT INTO `logs` VALUES (80, 7, 'Deleted Invoice', NULL, 'Deleted Invoice Number: INV-SEP-XXV-549', '2025-09-22 00:27:59');
INSERT INTO `logs` VALUES (88, 7, 'Deleted Invoice', NULL, 'Deleted Invoice Number: INV-SEP-XXV-561', '2025-09-26 10:14:54');
INSERT INTO `logs` VALUES (89, 7, 'Deleted Invoice', NULL, 'Deleted Invoice Number: INV-SEP-XXV-559', '2025-09-26 10:14:58');
INSERT INTO `logs` VALUES (90, 7, 'Deleted Invoice', NULL, 'Deleted Invoice Number: INV-SEP-XXV-557', '2025-09-26 10:15:01');
INSERT INTO `logs` VALUES (91, 7, 'Deleted Invoice', NULL, 'Deleted Invoice Number: INV-SEP-XXV-555', '2025-09-26 10:15:08');
INSERT INTO `logs` VALUES (92, 7, 'Deleted Invoice', NULL, 'Deleted Invoice Number: INV-SEP-XXV-553', '2025-09-26 10:15:10');
INSERT INTO `logs` VALUES (93, 7, 'Deleted Invoice', NULL, 'Deleted Invoice Number: INV-SEP-XXV-551', '2025-09-26 10:15:13');
INSERT INTO `logs` VALUES (94, 7, 'Deleted Invoice', NULL, 'Deleted Invoice Number: INV-SEP-XXV-549', '2025-09-26 10:15:16');
INSERT INTO `logs` VALUES (95, 7, 'Deleted Invoice', NULL, 'Deleted Invoice Number: INV-SEP-XXV-547', '2025-09-26 10:15:23');
INSERT INTO `logs` VALUES (96, 7, 'Deleted Invoice', NULL, 'Deleted Invoice Number: INV-SEPT-XXV-545', '2025-09-26 10:15:26');
INSERT INTO `logs` VALUES (97, 7, 'Deleted Invoice', NULL, 'Deleted Invoice Number: INV-492', '2025-09-26 10:15:30');
INSERT INTO `logs` VALUES (98, 7, 'Deleted Invoice', NULL, 'Deleted Invoice Number: INV-448', '2025-09-26 10:19:33');
INSERT INTO `logs` VALUES (99, 7, 'Deleted Invoice', NULL, 'Deleted Invoice Number: INV-465', '2025-09-26 10:19:36');
INSERT INTO `logs` VALUES (100, 7, 'Deleted Invoice', NULL, 'Deleted Invoice Number: 491', '2025-09-26 10:19:46');
INSERT INTO `logs` VALUES (101, 7, 'Deleted Invoice', NULL, 'Deleted Invoice Number: INV-650', '2025-09-26 10:19:52');
INSERT INTO `logs` VALUES (102, 7, 'Created Invoice', 675, 'Invoice Number: INV-SEP-XXV-545', '2025-09-26 10:35:17');
INSERT INTO `logs` VALUES (104, 7, 'Created Invoice', 677, 'Invoice Number: INV-SEP-XXV-551', '2025-09-26 15:28:31');
INSERT INTO `logs` VALUES (105, 7, 'Deleted Invoice', NULL, 'Deleted Invoice Number: INV-SEP-XXV-549', '2025-09-26 15:33:54');
INSERT INTO `logs` VALUES (106, 7, 'Created Invoice', 678, 'Invoice Number: INV-SEP-XXV-549', '2025-09-26 15:35:29');
INSERT INTO `logs` VALUES (107, 7, 'Created Invoice', 679, 'Invoice Number: INV-OCT-XXV-551', '2025-10-10 10:34:39');
INSERT INTO `logs` VALUES (109, 7, 'Deleted Invoice', NULL, 'Deleted Invoice Number: 68', '2025-10-22 15:45:38');
INSERT INTO `logs` VALUES (111, 7, 'Deleted Invoice', NULL, 'Deleted Invoice Number: 179', '2025-10-22 15:46:05');
INSERT INTO `logs` VALUES (112, 7, 'Created Invoice', 682, 'Invoice Number: INV-OCT-XXV-553', '2025-10-28 09:56:15');
INSERT INTO `logs` VALUES (113, 7, 'Created Invoice', 683, 'Invoice Number: INV-OCT-XXV-555', '2025-10-28 10:42:07');
INSERT INTO `logs` VALUES (115, 7, 'Deleted Invoice', NULL, 'Deleted Invoice Number: INV-OCT-XXV-557', '2025-10-31 15:55:49');
INSERT INTO `logs` VALUES (116, 7, 'Created Invoice', 685, 'Invoice Number: INV-NOV-XXV-557', '2025-11-11 16:37:33');
INSERT INTO `logs` VALUES (117, 7, 'Created Invoice', 686, 'Invoice Number: INV-NOV-XXV-559', '2025-11-17 14:14:37');
INSERT INTO `logs` VALUES (118, 7, 'Updated Invoice Status', 686, 'Changed status to PAID', '2025-11-24 10:23:51');
INSERT INTO `logs` VALUES (119, 7, 'Created Invoice', 687, 'Invoice Number: INV-NOV-XXV-561', '2025-11-24 15:20:44');
INSERT INTO `logs` VALUES (121, 7, 'Deleted Invoice', NULL, 'Deleted Invoice Number: 625', '2025-11-25 15:28:14');
INSERT INTO `logs` VALUES (122, 7, 'Updated Invoice Status', 687, 'Changed status to PAID', '2025-11-26 05:53:25');
INSERT INTO `logs` VALUES (123, 7, 'Updated Invoice Status', 687, 'Changed status to UNPAID', '2025-11-26 05:53:34');
INSERT INTO `logs` VALUES (124, 7, 'Updated Invoice Status', 687, 'Changed status to PAID', '2025-11-26 05:53:37');
INSERT INTO `logs` VALUES (125, 7, 'Updated Invoice Status', 687, 'Changed status to UNPAID', '2025-11-26 05:53:39');
INSERT INTO `logs` VALUES (126, 7, 'Updated Invoice Status', 687, 'Changed status to PAID', '2025-11-26 05:53:41');
INSERT INTO `logs` VALUES (127, 7, 'Updated Invoice Status', 687, 'Changed status to UNPAID', '2025-11-26 05:53:54');
INSERT INTO `logs` VALUES (128, 7, 'Updated Invoice Status', 687, 'Changed status to PAID', '2025-11-26 05:53:58');
INSERT INTO `logs` VALUES (129, 7, 'Created Invoice', 689, 'Invoice Number: INV-NOV-XXV-563', '2025-11-26 14:47:51');
INSERT INTO `logs` VALUES (130, 7, 'Created Invoice', 690, 'Invoice Number: INV-NOV-XXV-565', '2025-11-26 15:16:08');
INSERT INTO `logs` VALUES (131, 7, 'Updated Invoice Status', 687, 'Changed status to UNPAID', '2025-11-27 08:12:42');
INSERT INTO `logs` VALUES (132, 7, 'Updated Invoice Status', 687, 'Changed status to PAID', '2025-11-27 08:12:44');
INSERT INTO `logs` VALUES (133, 7, 'Updated Invoice Status', 690, 'Changed status to PAID', '2025-11-27 18:56:12');
INSERT INTO `logs` VALUES (134, 7, 'Updated Invoice Status', 689, 'Changed status to PAID', '2025-11-27 18:56:30');
INSERT INTO `logs` VALUES (135, 7, 'Updated Invoice Status', 677, 'Changed status to PAID', '2025-11-27 18:57:28');
INSERT INTO `logs` VALUES (136, 7, 'Updated Invoice Status', 686, 'Changed status to UNPAID', '2025-11-30 22:08:11');
INSERT INTO `logs` VALUES (137, 7, 'Updated Invoice Status', 686, 'Changed status to PAID', '2025-11-30 22:08:15');
INSERT INTO `logs` VALUES (141, 7, 'Deleted Invoice', NULL, 'Deleted Invoice Number: 3453535', '2025-12-04 04:27:38');
INSERT INTO `logs` VALUES (142, 7, 'Deleted Invoice', NULL, 'Deleted Invoice Number: INV-DEC-XXV-567', '2025-12-04 04:27:43');
INSERT INTO `logs` VALUES (143, 7, 'Created Invoice', 694, 'Invoice Number: INV-DEC-XXV-567', '2025-12-12 08:41:28');
INSERT INTO `logs` VALUES (144, 7, 'Updated Invoice Status', 694, 'Changed status to PAID', '2025-12-13 08:18:42');
INSERT INTO `logs` VALUES (145, 7, 'Created Invoice', 695, 'Invoice Number: INV-DEC-XXV-569', '2025-12-18 14:17:02');
INSERT INTO `logs` VALUES (146, 7, 'Created Invoice', 696, 'Invoice Number: INV-DEC-XXV-571', '2025-12-18 15:13:54');
INSERT INTO `logs` VALUES (147, 7, 'Updated Invoice Status', 695, 'Changed status to PAID', '2025-12-22 10:55:58');
INSERT INTO `logs` VALUES (148, 7, 'Created Invoice', 697, 'Invoice Number: INV-DEC-XXV-573', '2025-12-22 10:59:38');
INSERT INTO `logs` VALUES (149, 7, 'Created Invoice', 698, 'Invoice Number: INV-JAN-XXVI-001', '2026-01-12 08:43:00');

-- ----------------------------
-- Table structure for users
-- ----------------------------
DROP TABLE IF EXISTS `users`;
CREATE TABLE `users`  (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `password_hash` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `verified` tinyint(1) NOT NULL DEFAULT 0,
  `verification_token` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `email`(`email`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 34 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of users
-- ----------------------------
INSERT INTO `users` VALUES (1, 'test@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '2025-02-27 08:59:08', 0, NULL);
INSERT INTO `users` VALUES (7, 'karel.it@ydsf.org', '$2y$10$QqW5SH0dOIIiuUo1o5gGwe2qXZ6/xSqycmugwHW6qEhcojnzkXR62', '2025-02-27 10:00:38', 1, NULL);
INSERT INTO `users` VALUES (8, 'kenzastoreid@gmail.com', '$2y$10$H87nrI9BQzKBskYCIMr1Yu7U0c1AD0ZNmWiJ.n0NZa7Nw.i2j5BYO', '2025-02-27 14:48:30', 1, NULL);
INSERT INTO `users` VALUES (12, 'tester1243124141@gmail.com', '$2y$10$GIHIVy.5If/dh0Xsf3pbe.Cm7rqofO8msp9za2lu0Fb4./YxuKzzO', '2025-03-07 04:57:57', 0, NULL);
INSERT INTO `users` VALUES (13, 'gixat97803@easipro.com', '$2y$10$/IG.ytEJWf2a3Bc376KtbOx1.qMuDljLHiPv7Cf.8FB7xFDsK22n2', '2025-03-10 14:26:05', 0, NULL);
INSERT INTO `users` VALUES (14, 'test@gmail.com', '$2y$10$055h506OM7Ykub9GW0lZauCR7WfaIzZj8JPPYlPLVjEve7qnZW2vW', '2025-03-10 14:29:13', 0, NULL);
INSERT INTO `users` VALUES (15, 'victimakif@gmail.com', '$2y$10$DIcx/B7uyf8os09qo0xoxeZCU2gxTtDV2L1Nks0e0LX5SCidjiOLS', '2025-04-12 13:05:37', 0, NULL);
INSERT INTO `users` VALUES (16, 'aspaninbeg@stayhome.li', '$2y$10$sNQieZV/ALbnrJV1srT02ektYSnBjhqKvqFFE91OPvOFvbO8RP7xq', '2025-04-20 00:33:16', 0, NULL);
INSERT INTO `users` VALUES (17, 'gecodi3083@bauscn.com', '$2y$10$Z11J9gG9Il2spbq3CIPMh.vEUpAt2fohgAt5WkcFrLznX4IWTR1PW', '2025-04-23 17:15:32', 0, NULL);
INSERT INTO `users` VALUES (18, 'titoqiro@dreamclarify.org', '$2y$10$FVNb6/3WVycEwn5MNnDwj.giLAeBrOcaAW1CB93H6ePIKYbuOFAua', '2025-04-23 17:21:01', 0, NULL);
INSERT INTO `users` VALUES (19, 'deruk_tes1@proton.me', '$2y$10$GJwKJOynOHIWtRCMQXnoOeRh9l9.qBIU3iHFnyrnhF1vVUvm2Afu2', '2025-05-01 14:11:43', 0, NULL);
INSERT INTO `users` VALUES (20, 'bakeja6257@benznoi.com', '$2y$10$/17cnu3RwMvlhn6deSDbT.JGsQ0yr5WBrYwLripZi1ChbCjVMwgn6', '2025-05-07 20:06:58', 0, NULL);
INSERT INTO `users` VALUES (21, 'xovam65466@bocapies.com', '$2y$10$x3C2./S4AEm34dr5EQEeEe4pKv8my82HtCMkcTWySy71TBRUGulRO', '2025-05-07 20:13:30', 0, NULL);
INSERT INTO `users` VALUES (22, 'gibewo4181@bamsrad.com', '$2y$10$RROkNwPfOBRRugiTaADWx.uw9E0QYZOCS/c8rAlovODGoyWjegQwS', '2025-05-12 21:20:25', 0, NULL);
INSERT INTO `users` VALUES (23, 'bodobe1637@deusa7.com', '$2y$10$OUsAYkODeqZcNgq/H1WDZ.y0hDToYckcuv3eb2qEjz18RH/0SH1m6', '2025-05-14 19:03:20', 0, NULL);
INSERT INTO `users` VALUES (24, '7411close@dcpa.net', '$2y$10$vcM.EroSZvSAfQdAS./PVO1SX3.ZakWucMNha/8RYQsoyQ6Pkq/n.', '2025-05-16 21:24:13', 0, NULL);
INSERT INTO `users` VALUES (25, 'teyim79888@neuraxo.com', '$2y$10$pTG6WgcROpYzlQ.LuqCI6udzBVvU.02JzwAYLCURrCy79h4T1atei', '2025-05-17 22:30:42', 0, NULL);
INSERT INTO `users` VALUES (26, 'hosix78676@magpit.com', '$2y$10$RW89idICZuAm7wExXhFAEuCiGQChj9uTRC0e.jYMtmm4Lz0j.PvY.', '2025-05-17 23:36:38', 0, NULL);
INSERT INTO `users` VALUES (27, 'sarveshm624@gmail.com', '$2y$10$vgcH/v0WRBZomLgGTfgP8egFW9OmLasAqcljLrtRv5GuviaUu1adC', '2025-05-17 23:40:18', 0, NULL);
INSERT INTO `users` VALUES (28, 'ali.hsn0x1@gmail.com', '$2y$10$t28z68MaJlMb8Laol0FuousR.i3TwHy9dTHFbw80hCM6m056Ghnuy', '2025-05-25 03:04:09', 0, NULL);
INSERT INTO `users` VALUES (29, 'iamvengeance123@gmail.com', '$2y$10$/H/dh67l84KvaBMp/vdD.estAJh2t12mKrrJIfnrCQqKGvq7uH1W.', '2025-05-27 14:18:49', 0, NULL);
INSERT INTO `users` VALUES (30, 'nuqerifo@forexnews.bg', '$2y$10$hwhX5f9W5dZHo3gUp2D9p.5FFEMZHY6wVULDfimlqE/h92.CMtTES', '2025-08-13 13:37:49', 0, NULL);
INSERT INTO `users` VALUES (31, 'firica4449@cerisun.com', '$2y$10$eHJkhwcgx.NE5UG/uXUZ.OEILawamYJux/WVzosJVkCB3Av4XlGre', '2025-09-18 18:47:27', 0, NULL);
INSERT INTO `users` VALUES (32, 'apitest@example.com', '$2y$10$JvCO.6Hz2LPFcbHrMrd88.dicKJjthigcIIjB5ECnYNgcXhTm8gt2', '2025-11-27 17:13:37', 1, NULL);
INSERT INTO `users` VALUES (33, 'hendra.pejuangdakwah@gmail.com', '$2y$10$GNhgmGhhygZnnR9gOA2/6u9g6Gba2lczfHh8uwQ57pqNuMgkxygp2', '2025-12-19 17:00:25', 1, NULL);

SET FOREIGN_KEY_CHECKS = 1;
