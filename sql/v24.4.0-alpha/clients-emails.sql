CREATE TABLE `clients_emails` (
    `client_email_id` int(11) PRIMARY KEY AUTO_INCREMENT,
    `client_email_email` varchar(255) NOT NULL,
    `client_id` int(11),
    `client_user_id` int(11),
    `client_email_is_deleted` enum('true', 'false') NOT NULL DEFAULT 'false'
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

ALTER TABLE
    `clients_emails`
ADD
    CONSTRAINT `clients_emails_ibfk_1` FOREIGN KEY (`client_id`) REFERENCES `clients` (`client_id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE
    `clients_emails`
ADD
    CONSTRAINT `clients_emails_ibfk_2` FOREIGN KEY (`client_user_id`) REFERENCES `clients_users` (`client_user_id`) ON DELETE CASCADE ON UPDATE CASCADE;
