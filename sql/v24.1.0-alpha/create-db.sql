-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Tempo de geração: 13-Mar-2024 às 15:28
-- Versão do servidor: 10.4.28-MariaDB
-- versão do PHP: 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Banco de dados: `u524077001_mf_consultoria`
--
CREATE DATABASE IF NOT EXISTS `u524077001_mf_consultoria` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `u524077001_mf_consultoria`;

-- --------------------------------------------------------

--
-- Estrutura da tabela `api_sessions`
--

CREATE TABLE `api_sessions` (
  `api_session_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `api_session_token` varchar(255) NOT NULL,
  `api_session_expires` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `user_name` varchar(255) NOT NULL,
  `user_avatar` varchar(255) DEFAULT '/default.png',
  `user_email` varchar(255) NOT NULL,
  `user_phone` varchar(32) DEFAULT NULL,
  `user_password` varchar(255) NOT NULL,
  `user_slug` varchar(255) NOT NULL,
  `user_status` enum('true','false') NOT NULL DEFAULT 'true',
  `user_is_deleted` enum('true','false') NOT NULL DEFAULT 'false'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Acionadores `users`
--
DELIMITER $$
CREATE TRIGGER `insert_users_permissions` AFTER INSERT ON `users` FOR EACH ROW BEGIN
    INSERT INTO `users_permissions` (`user_id`, `user_permission_permission`) VALUES (NEW.user_id, 'users.create');
    INSERT INTO `users_permissions` (`user_id`, `user_permission_permission`) VALUES (NEW.user_id, 'users.read');
    INSERT INTO `users_permissions` (`user_id`, `user_permission_permission`) VALUES (NEW.user_id, 'users.update');
    INSERT INTO `users_permissions` (`user_id`, `user_permission_permission`) VALUES (NEW.user_id, 'users.delete');
    
    INSERT INTO `users_permissions` (`user_id`, `user_permission_permission`) VALUES (NEW.user_id, 'clients.create');
    INSERT INTO `users_permissions` (`user_id`, `user_permission_permission`) VALUES (NEW.user_id, 'clients.read');
    INSERT INTO `users_permissions` (`user_id`, `user_permission_permission`) VALUES (NEW.user_id, 'clients.update');
    INSERT INTO `users_permissions` (`user_id`, `user_permission_permission`) VALUES (NEW.user_id, 'clients.delete');
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Estrutura da tabela `users_logs`
--

CREATE TABLE `users_logs` (
  `user_log_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `user_log_date` datetime NOT NULL,
  `user_log_action` varchar(82) NOT NULL,
  `user_log_description` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`user_log_description`))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `users_permissions`
--

CREATE TABLE `users_permissions` (
  `user_permission_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `user_permission_permission` varchar(255) NOT NULL,
  `user_permission_status` enum('true','false') NOT NULL DEFAULT 'false'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Índices para tabelas despejadas
--

--
-- Índices para tabela `api_sessions`
--
ALTER TABLE `api_sessions`
  ADD PRIMARY KEY (`api_session_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Índices para tabela `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`);

--
-- Índices para tabela `users_logs`
--
ALTER TABLE `users_logs`
  ADD PRIMARY KEY (`user_log_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Índices para tabela `users_permissions`
--
ALTER TABLE `users_permissions`
  ADD PRIMARY KEY (`user_permission_id`),
  ADD KEY `user_id` (`user_id`);

--
-- AUTO_INCREMENT de tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `api_sessions`
--
ALTER TABLE `api_sessions`
  MODIFY `api_session_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `users_logs`
--
ALTER TABLE `users_logs`
  MODIFY `user_log_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `users_permissions`
--
ALTER TABLE `users_permissions`
  MODIFY `user_permission_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Restrições para despejos de tabelas
--

--
-- Limitadores para a tabela `api_sessions`
--
ALTER TABLE `api_sessions`
  ADD CONSTRAINT `api_sessions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Limitadores para a tabela `users_logs`
--
ALTER TABLE `users_logs`
  ADD CONSTRAINT `users_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Limitadores para a tabela `users_permissions`
--
ALTER TABLE `users_permissions`
  ADD CONSTRAINT `users_permissions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
