-- ----------------------------
-- Table structure for recurring_invoices
-- ----------------------------
DROP TABLE IF EXISTS `recurring_invoices`;
CREATE TABLE `recurring_invoices`  (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `invoice_template_id` int(11) NOT NULL,
  `frequency` enum('weekly', 'monthly', 'annually') NOT NULL,
  `start_date` date NOT NULL,
  `next_run_date` date NOT NULL,
  `last_run_at` timestamp NULL DEFAULT NULL,
  `status` enum('active', 'paused') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `user_id`(`user_id`) USING BTREE,
  INDEX `invoice_template_id`(`invoice_template_id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Dynamic;
