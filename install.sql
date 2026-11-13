CREATE TABLE IF NOT EXISTS `users` (
                                       `id` int UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                                       `username` varchar(100) NOT NULL UNIQUE,
    `password_hash` varchar(255) NOT NULL,
    `created_at` datetime NOT NULL DEFAULT current_timestamp()
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- admin / admin
INSERT IGNORE INTO `users` (`username`,`password_hash`) VALUES ('admin','$2y$12$EOg47Nwzf6dRaggt3cx6YuD9v6p.pa.vJy96i1x/ZvsQGBr8uYkNe');

-- Reuse the existing users table and accounts. No default account is created.
-- With DB_PREFIX configured, apply the same prefix to ALL table names here.

CREATE TABLE IF NOT EXISTS `debt_items` (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `user_id` int UNSIGNED NOT NULL,
  `name` varchar(200) NOT NULL,
  `group_code` char(1) NOT NULL,
  `kind` varchar(10) NOT NULL,
  `amount_min` decimal(13,4) NOT NULL,
  `amount_base` decimal(13,4) NOT NULL,
  `amount_max` decimal(13,4) NOT NULL,
  `insurance` decimal(13,4) NOT NULL DEFAULT 0,
  `currency` char(3) NOT NULL DEFAULT 'HUF',
  `frequency` varchar(16) NOT NULL,
  `first_due` date NOT NULL,
  `end_date` date DEFAULT NULL,
  `installments` smallint UNSIGNED DEFAULT NULL,
  `notes` text NOT NULL,
  KEY `debt_items_user` (`user_id`, `group_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `debt_rates` (
  `user_id` int UNSIGNED NOT NULL,
  `currency` char(3) NOT NULL,
  `rate_min` decimal(13,4) NOT NULL,
  `rate_base` decimal(13,4) NOT NULL,
  `rate_max` decimal(13,4) NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`user_id`, `currency`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
