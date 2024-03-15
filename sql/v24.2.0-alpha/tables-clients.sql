CREATE TABLE `clients` (
  `client_id` int(11) PRIMARY KEY AUTO_INCREMENT,
  `client_name` varchar(255) NOT NULL,
  `client_logo` varchar(255) NOT NULL,
  `client_cnpj` varchar(32) NOT NULL,
  `client_email` varchar(255) NOT NULL,
  `client_phone` varchar(32) DEFAULT NULL,
  `client_street` varchar(256) NOT NULL,
  `client_number` varchar(256) NOT NULL,
  `client_complement` varchar(256) NOT NULL,
  `client_neighborhood` varchar(256) NOT NULL,
  `client_city` varchar(256) NOT NULL,
  `client_state` varchar(2) NOT NULL,
  `client_zip` varchar(16) NOT NULL,
  `client_slug` varchar(255) NOT NULL,
  `client_status` enum('true','false') NOT NULL DEFAULT 'true',
  `client_is_deleted` enum('true','false') NOT NULL DEFAULT 'false'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `clients_users` (
  `client_user_id` int(11) PRIMARY KEY AUTO_INCREMENT,
  `client_id` int(11) NOT NULL,
  `client_user_name` varchar(255) NOT NULL,
  `client_user_email` varchar(255) NOT NULL,
  `client_user_phone` varchar(32) DEFAULT NULL,
  `client_user_slug` varchar(255) NOT NULL,
  `client_user_status` enum('true','false') NOT NULL DEFAULT 'true',
  `client_user_is_deleted` enum('true','false') NOT NULL DEFAULT 'false'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE `clients_users`
  ADD CONSTRAINT `clients_users_ibfk_1` FOREIGN KEY (`client_id`) REFERENCES `clients` (`client_id`) ON DELETE CASCADE ON UPDATE CASCADE;

CREATE TABLE `clients_reports` (
  `client_report_id` int(11) PRIMARY KEY AUTO_INCREMENT,
  `client_id` int(11) NOT NULL,
  `client_report_description` varchar(255) NOT NULL,
  `client_report_url` varchar(255) NOT NULL,
  `client_report_is_deleted` enum('true','false') NOT NULL DEFAULT 'false'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE `clients_reports`
  ADD CONSTRAINT `clients_reports_ibfk_1` FOREIGN KEY (`client_id`) REFERENCES `clients` (`client_id`) ON DELETE CASCADE ON UPDATE CASCADE;