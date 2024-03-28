CREATE TABLE `api_sessions_clients` (
    `api_session_client_id` int(11) PRIMARY KEY AUTO_INCREMENT,
    `client_id` int(11),
    `client_user_id` int(11),
    `api_session_client_token` varchar(255) NOT NULL,
    `api_session_client_expires` datetime NOT NULL
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

ALTER TABLE
    `api_sessions_clients`
ADD
    CONSTRAINT `api_sessions_clients_ibfk_1` FOREIGN KEY (`client_id`) REFERENCES `clients` (`client_id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE
    `api_sessions_clients`
ADD
    CONSTRAINT `api_sessions_clients_ibfk_2` FOREIGN KEY (`client_user_id`) REFERENCES `clients_users` (`client_user_id`) ON DELETE CASCADE ON UPDATE CASCADE;