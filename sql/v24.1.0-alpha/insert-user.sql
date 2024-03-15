INSERT INTO `users` (`user_name`, `user_email`, `user_password`, `user_slug`, `user_status`, `user_is_deleted`) VALUES
('Sharp Soluções', 'testes@sharpsolucoes.com', '$2y$12$4EF0zEKbVB4ZXWGLquI2T.Q0mtK2DGPuQoY93A1HXl5eX.HtKu6l2', '1-sharp-solucoes', 'true', 'false');

UPDATE `users_permissions` SET `user_permission_status` = "true";