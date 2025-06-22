-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Jun 19, 2025 at 06:08 AM
-- Server version: 8.0.30
-- PHP Version: 8.1.9

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `neuranest`
--

-- --------------------------------------------------------

--
-- Table structure for table `api_usage`
--

CREATE TABLE `api_usage` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `endpoint` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tokens_used` int DEFAULT '0',
  `cost` decimal(10,6) DEFAULT '0.000000',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `chat_sessions`
--

CREATE TABLE `chat_sessions` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT 'New Chat',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `chat_sessions`
--

INSERT INTO `chat_sessions` (`id`, `user_id`, `title`, `created_at`, `updated_at`) VALUES
(1, 1, 'Первый разговор с AI', '2025-06-16 16:51:55', '2025-06-18 16:51:55'),
(2, 1, 'Вопросы о программировании', '2025-06-17 16:51:55', '2025-06-18 16:51:55'),
(3, 1, 'Планирование проекта', '2025-06-18 16:51:55', '2025-06-18 16:51:55'),
(7, 2, 'тест', '2025-06-18 18:03:58', '2025-06-18 18:04:24');

-- --------------------------------------------------------

--
-- Table structure for table `messages`
--

CREATE TABLE `messages` (
  `id` int NOT NULL,
  `session_id` int NOT NULL,
  `role` enum('user','assistant') COLLATE utf8mb4_unicode_ci NOT NULL,
  `content` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `messages`
--

INSERT INTO `messages` (`id`, `session_id`, `role`, `content`, `created_at`) VALUES
(1, 1, 'user', 'Привет! Как дела?', '2025-06-16 16:51:55'),
(2, 1, 'assistant', 'Привет! У меня всё отлично, спасибо! Я готов помочь вам с любыми вопросами. Чем могу быть полезен?', '2025-06-16 16:52:55'),
(3, 2, 'user', 'Можешь объяснить, что такое рекурсия в программировании?', '2025-06-17 16:51:55'),
(4, 2, 'assistant', 'Рекурсия в программировании - это техника, при которой функция вызывает саму себя для решения подзадач. Основные компоненты рекурсии:\n\n1. **Базовый случай** - условие остановки рекурсии\n2. **Рекурсивный случай** - функция вызывает себя с изменёнными параметрами\n\nПример простой рекурсивной функции для вычисления факториала:\n\n```python\ndef factorial(n):\n    if n <= 1:  # базовый случай\n        return 1\n    return n * factorial(n - 1)  # рекурсивный случай\n```\n\nРекурсия полезна для решения задач, которые можно разбить на похожие подзадачи меньшего размера.', '2025-06-17 16:53:55'),
(5, 3, 'user', 'Помоги составить план разработки веб-приложения', '2025-06-18 16:51:55'),
(6, 3, 'assistant', 'Конечно! Вот структурированный план разработки веб-приложения:\n\n## 1. Планирование и анализ\n- Определение требований и целей\n- Анализ целевой аудитории\n- Выбор технологического стека\n- Создание wireframes и mockups\n\n## 2. Проектирование\n- Архитектура приложения\n- Дизайн базы данных\n- API дизайн\n- UI/UX дизайн\n\n## 3. Разработка\n- Настройка среды разработки\n- Backend разработка\n- Frontend разработка\n- Интеграция компонентов\n\n## 4. Тестирование\n- Unit тестирование\n- Интеграционное тестирование\n- Пользовательское тестирование\n- Тестирование безопасности\n\n## 5. Деплой и поддержка\n- Настройка production среды\n- Деплой приложения\n- Мониторинг и логирование\n- Обновления и поддержка\n\nКакой этап вас больше всего интересует?', '2025-06-18 16:52:55'),
(35, 7, 'user', 'тест', '2025-06-18 18:04:22'),
(36, 7, 'assistant', 'Похоже, вы проверяете связь. Если у вас есть конкретные вопросы или темы, которые вы хотели бы обсудить, пожалуйста, дайте знать. Я здесь, чтобы помочь.', '2025-06-18 18:04:24');

-- --------------------------------------------------------

--
-- Table structure for table `payment_transactions`
--

CREATE TABLE `payment_transactions` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `transaction_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `currency` varchar(3) COLLATE utf8mb4_unicode_ci DEFAULT 'RUB',
  `status` enum('pending','completed','failed','refunded') COLLATE utf8mb4_unicode_ci DEFAULT 'pending',
  `payment_method` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `subscription_type` enum('monthly','yearly') COLLATE utf8mb4_unicode_ci DEFAULT 'monthly',
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `payment_transactions`
--

INSERT INTO `payment_transactions` (`id`, `user_id`, `transaction_id`, `amount`, `currency`, `status`, `payment_method`, `subscription_type`, `expires_at`, `created_at`, `updated_at`) VALUES
(1, 2, 'TXN_1750267713_2', '2990.00', 'RUB', 'completed', 'sberbank', 'yearly', '2026-06-18 17:28:33', '2025-06-18 17:28:33', '2025-06-18 17:28:33');

-- --------------------------------------------------------

--
-- Table structure for table `system_logs`
--

CREATE TABLE `system_logs` (
  `id` int NOT NULL,
  `user_id` int DEFAULT NULL,
  `action` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `details` text COLLATE utf8mb4_unicode_ci,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `display_name` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `avatar` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_verified` tinyint(1) DEFAULT '0',
  `is_premium` tinyint(1) DEFAULT '0',
  `language` enum('ru','en') COLLATE utf8mb4_unicode_ci DEFAULT 'ru',
  `theme` enum('light','dark') COLLATE utf8mb4_unicode_ci DEFAULT 'light',
  `two_factor_enabled` tinyint(1) DEFAULT '0',
  `last_login` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `email`, `password`, `display_name`, `avatar`, `is_verified`, `is_premium`, `language`, `theme`, `two_factor_enabled`, `last_login`, `created_at`, `updated_at`) VALUES
(1, 'admin@neuranest.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrator', NULL, 1, 1, 'ru', 'light', 0, NULL, '2025-06-18 16:51:55', '2025-06-18 16:51:55'),
(2, 'avpgame42@gmail.com', '$2y$10$96CnYm2XGdXxkkgDVdZo0egOB41l0eJzR8BIUGQ6kvBSVNuQbW0OS', 'test', 'avatar_2_1750267737.gif', 1, 1, 'ru', 'light', 0, '2025-06-18 20:04:05', '2025-06-18 17:06:58', '2025-06-18 20:04:05');

-- --------------------------------------------------------

--
-- Table structure for table `user_sessions`
--

CREATE TABLE `user_sessions` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `session_token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expires_at` timestamp NOT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `user_settings`
--

CREATE TABLE `user_settings` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `setting_key` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `setting_value` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `verification_codes`
--

CREATE TABLE `verification_codes` (
  `id` int NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `code` varchar(6) COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` enum('registration','password_reset','email_change','login_2fa') COLLATE utf8mb4_unicode_ci NOT NULL,
  `expires_at` timestamp NOT NULL,
  `used` tinyint(1) DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `verification_codes`
--

INSERT INTO `verification_codes` (`id`, `email`, `code`, `type`, `expires_at`, `used`, `created_at`) VALUES
(2, 'binancejeka@gmail.com', '452751', 'registration', '2025-06-18 17:19:20', 1, '2025-06-18 17:04:20'),
(3, 'binancejeka@gmail.com', '345746', 'registration', '2025-06-18 17:19:54', 1, '2025-06-18 17:04:54'),
(4, 'binancejeka@gmail.com', '357701', 'registration', '2025-06-18 17:21:51', 1, '2025-06-18 17:06:51'),
(5, 'binancejeka@gmail.com', '730359', 'email_change', '2025-06-18 18:03:25', 1, '2025-06-18 17:48:25'),
(6, 'avpgame42@gmail.com', '669966', 'email_change', '2025-06-18 18:03:25', 1, '2025-06-18 17:48:25'),
(7, 'avpgame42@gmail.com', '962416', 'password_reset', '2025-06-18 18:04:00', 1, '2025-06-18 17:49:00'),
(8, 'binancejeka@gmail.com', '607379', 'registration', '2025-06-18 20:08:56', 0, '2025-06-18 19:53:56');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `api_usage`
--
ALTER TABLE `api_usage`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_api_usage_user_id` (`user_id`),
  ADD KEY `idx_api_usage_endpoint` (`endpoint`),
  ADD KEY `idx_api_usage_created_at` (`created_at`);

--
-- Indexes for table `chat_sessions`
--
ALTER TABLE `chat_sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_chat_sessions_user_id` (`user_id`),
  ADD KEY `idx_chat_sessions_created_at` (`created_at`);

--
-- Indexes for table `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_messages_session_id` (`session_id`),
  ADD KEY `idx_messages_role` (`role`),
  ADD KEY `idx_messages_created_at` (`created_at`);

--
-- Indexes for table `payment_transactions`
--
ALTER TABLE `payment_transactions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `transaction_id` (`transaction_id`),
  ADD KEY `idx_payment_transactions_user_id` (`user_id`),
  ADD KEY `idx_payment_transactions_transaction_id` (`transaction_id`),
  ADD KEY `idx_payment_transactions_status` (`status`),
  ADD KEY `idx_payment_transactions_expires_at` (`expires_at`);

--
-- Indexes for table `system_logs`
--
ALTER TABLE `system_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_system_logs_user_id` (`user_id`),
  ADD KEY `idx_system_logs_action` (`action`),
  ADD KEY `idx_system_logs_created_at` (`created_at`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_users_email` (`email`),
  ADD KEY `idx_users_verified` (`is_verified`),
  ADD KEY `idx_users_premium` (`is_premium`);

--
-- Indexes for table `user_sessions`
--
ALTER TABLE `user_sessions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `session_token` (`session_token`),
  ADD KEY `idx_user_sessions_user_id` (`user_id`),
  ADD KEY `idx_user_sessions_session_token` (`session_token`),
  ADD KEY `idx_user_sessions_expires_at` (`expires_at`);

--
-- Indexes for table `user_settings`
--
ALTER TABLE `user_settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_user_setting` (`user_id`,`setting_key`),
  ADD KEY `idx_user_settings_user_id` (`user_id`),
  ADD KEY `idx_user_settings_setting_key` (`setting_key`);

--
-- Indexes for table `verification_codes`
--
ALTER TABLE `verification_codes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_verification_codes_email` (`email`),
  ADD KEY `idx_verification_codes_code` (`code`),
  ADD KEY `idx_verification_codes_type` (`type`),
  ADD KEY `idx_verification_codes_expires_at` (`expires_at`),
  ADD KEY `idx_verification_codes_used` (`used`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `api_usage`
--
ALTER TABLE `api_usage`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `chat_sessions`
--
ALTER TABLE `chat_sessions`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `messages`
--
ALTER TABLE `messages`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=37;

--
-- AUTO_INCREMENT for table `payment_transactions`
--
ALTER TABLE `payment_transactions`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `system_logs`
--
ALTER TABLE `system_logs`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `user_sessions`
--
ALTER TABLE `user_sessions`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `user_settings`
--
ALTER TABLE `user_settings`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `verification_codes`
--
ALTER TABLE `verification_codes`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `api_usage`
--
ALTER TABLE `api_usage`
  ADD CONSTRAINT `api_usage_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `chat_sessions`
--
ALTER TABLE `chat_sessions`
  ADD CONSTRAINT `chat_sessions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `messages`
--
ALTER TABLE `messages`
  ADD CONSTRAINT `messages_ibfk_1` FOREIGN KEY (`session_id`) REFERENCES `chat_sessions` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `payment_transactions`
--
ALTER TABLE `payment_transactions`
  ADD CONSTRAINT `payment_transactions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `system_logs`
--
ALTER TABLE `system_logs`
  ADD CONSTRAINT `system_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `user_sessions`
--
ALTER TABLE `user_sessions`
  ADD CONSTRAINT `user_sessions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `user_settings`
--
ALTER TABLE `user_settings`
  ADD CONSTRAINT `user_settings_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
