CREATE TABLE `clients_reports_groups` (
  `client_reports_group_id` int(11) PRIMARY KEY AUTO_INCREMENT,
  `client_id` int(11) NOT NULL,
  `client_reports_group_description` varchar(255) NOT NULL,
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE `clients_reports_groups`
  ADD CONSTRAINT `clients_reports_groups_ibfk_1` FOREIGN KEY (`client_id`) REFERENCES `clients` (`client_id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `clients_reports_groups` ADD COLUMN `client_reports_group_is_deleted` enum('true','false') NOT NULL DEFAULT 'false';