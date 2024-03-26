ALTER TABLE `clients_reports` ADD COLUMN `client_reports_group_id` int(11) NOT NULL AFTER `client_id`;

ALTER TABLE `clients_reports`
  ADD CONSTRAINT `clients_reports_ibfk_2` FOREIGN KEY (`client_reports_group_id`) REFERENCES `clients_reports_groups` (`client_reports_group_id`) ON DELETE CASCADE ON UPDATE CASCADE;