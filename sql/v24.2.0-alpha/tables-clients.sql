CREATE TABLE `clients` (
  `id` int(11) PRIMARY KEY AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `logo` varchar(255) NOT NULL,
  `cnpj` varchar(32) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(32) DEFAULT NULL,
  `street` varchar(256) NOT NULL,
  `number` varchar(256) NOT NULL,
  `complement` varchar(256) NOT NULL,
  `neighborhood` varchar(256) NOT NULL,
  `city` varchar(256) NOT NULL,
  `state` varchar(2) NOT NULL,
  `zip` varchar(16) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `status` enum('true','false') NOT NULL DEFAULT 'true',
  `is_deleted` enum('true','false') NOT NULL DEFAULT 'false'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `clients_users` (
  `id` int(11) PRIMARY KEY AUTO_INCREMENT,
  `client_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(32) DEFAULT NULL,
  `slug` varchar(255) NOT NULL,
  `status` enum('true','false') NOT NULL DEFAULT 'true',
  `is_deleted` enum('true','false') NOT NULL DEFAULT 'false'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE `clients_users`
  ADD CONSTRAINT `clients_users_ibfk_1` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

CREATE TABLE `clients_reports` (
  `id` int(11) PRIMARY KEY AUTO_INCREMENT,
  `client_id` int(11) NOT NULL,
  `description` varchar(255) NOT NULL,
  `url` varchar(255) NOT NULL,
  `is_deleted` enum('true','false') NOT NULL DEFAULT 'false'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE `clients_reports`
  ADD CONSTRAINT `clients_reports_ibfk_1` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;