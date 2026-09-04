-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Erstellungszeit: 04. Sep 2026 um 20:22
-- Server-Version: 10.11.10-MariaDB-cll-lve
-- PHP-Version: 8.3.11

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Datenbank: `u14bc1w8_v08_subscriptiondb`
--

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `invoice`
--

CREATE TABLE `invoice` (
  `re_nr` bigint(20) UNSIGNED NOT NULL,
  `sr_id` bigint(20) UNSIGNED NOT NULL,
  `invoice_date` date NOT NULL,
  `from_le_id` bigint(20) UNSIGNED NOT NULL,
  `to_le_id` bigint(20) UNSIGNED NOT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `currency` char(3) NOT NULL DEFAULT 'EUR'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `ledger_entry`
--

CREATE TABLE `ledger_entry` (
  `le_id` bigint(20) UNSIGNED NOT NULL,
  `sr_id` bigint(20) UNSIGNED NOT NULL,
  `sb_id` bigint(20) UNSIGNED DEFAULT NULL,
  `entry_type` enum('FO','GG','ZE','ZG','ZA') NOT NULL,
  `context_le_id` bigint(20) UNSIGNED DEFAULT NULL,
  `money_amount` decimal(10,2) DEFAULT NULL,
  `amount` decimal(10,2) DEFAULT NULL,
  `currency` char(3) NOT NULL DEFAULT 'EUR',
  `pl_code` varchar(20) DEFAULT NULL,
  `pl_version` varchar(10) DEFAULT NULL,
  `period_from` date DEFAULT NULL,
  `period_to` date DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  `booked_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Daten für Tabelle `ledger_entry`
--

INSERT INTO `ledger_entry` (`le_id`, `sr_id`, `sb_id`, `entry_type`, `context_le_id`, `money_amount`, `amount`, `currency`, `pl_code`, `pl_version`, `period_from`, `period_to`, `description`, `booked_at`) VALUES
(49, 30118, 115, 'FO', NULL, NULL, -25.00, 'EUR', 'BASIS', 'A1', '2026-02-01', '2026-02-28', NULL, '2026-02-01 06:00:00'),
(50, 30118, 170, 'FO', NULL, NULL, -35.00, 'EUR', 'PLUS', 'A1', '2026-02-01', '2026-02-28', NULL, '2026-02-01 06:00:00'),
(51, 30118, 205, 'FO', NULL, NULL, -40.00, 'EUR', 'PREMIUM', 'A1', '2026-02-01', '2026-02-28', NULL, '2026-02-01 06:00:00'),
(52, 30118, 205, 'GG', NULL, NULL, 10.00, 'EUR', 'PREMIUM', 'A1', '2026-02-01', '2026-02-28', 'Rabatt', '2026-02-03 09:15:00'),
(53, 30118, NULL, 'ZE', NULL, 110.00, NULL, 'EUR', NULL, NULL, NULL, NULL, 'Zahlungseingang, 3 Rechnungen', '2026-02-10 08:30:00'),
(54, 30118, 115, 'ZG', 53, NULL, 25.00, 'EUR', NULL, NULL, NULL, NULL, 'Umlage aus Buchung 53', '2026-02-10 08:31:00'),
(55, 30118, 170, 'ZG', 53, NULL, 35.00, 'EUR', NULL, NULL, NULL, NULL, 'Umlage aus Buchung 53', '2026-02-10 08:31:00'),
(56, 30118, 205, 'ZG', 53, NULL, 40.00, 'EUR', NULL, NULL, NULL, NULL, 'Umlage aus Buchung 53', '2026-02-10 08:31:00'),
(57, 30118, 205, 'ZA', NULL, -10.00, -10.00, 'EUR', NULL, NULL, NULL, NULL, 'Auszahlung Ende aller Vertraege', '2026-03-01 11:00:00');

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `plan`
--

CREATE TABLE `plan` (
  `pl_id` bigint(20) UNSIGNED NOT NULL,
  `pl_code` varchar(20) NOT NULL,
  `pl_version` varchar(10) NOT NULL,
  `pl_label` varchar(255) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `currency` char(3) NOT NULL DEFAULT 'EUR',
  `billing_interval` varchar(20) NOT NULL,
  `valid_from` date NOT NULL,
  `valid_to` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Daten für Tabelle `plan`
--

INSERT INTO `plan` (`pl_id`, `pl_code`, `pl_version`, `pl_label`, `price`, `currency`, `billing_interval`, `valid_from`, `valid_to`) VALUES
(1, 'BASIS', 'A1', 'Basis-Tarif', 25.00, 'EUR', 'monatlich', '2026-01-01', NULL),
(2, 'PLUS', 'A1', 'Plus-Tarif', 35.00, 'EUR', 'monatlich', '2026-01-01', NULL),
(3, 'PREMIUM', 'A1', 'Premium-Tarif', 40.00, 'EUR', 'monatlich', '2026-01-01', NULL);

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `subscriber`
--

CREATE TABLE `subscriber` (
  `sr_id` bigint(20) UNSIGNED NOT NULL,
  `sr_name` varchar(255) NOT NULL,
  `created_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Daten für Tabelle `subscriber`
--

INSERT INTO `subscriber` (`sr_id`, `sr_name`, `created_at`) VALUES
(30118, 'Demo-Subscriber (Beispiel aus Konzeptdoku)', '2026-01-15 10:00:00');

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `subscription`
--

CREATE TABLE `subscription` (
  `sb_id` bigint(20) UNSIGNED NOT NULL,
  `sr_id` bigint(20) UNSIGNED NOT NULL,
  `user_type` enum('mand','cust') NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `pl_code` varchar(20) NOT NULL,
  `pl_version_current` varchar(10) NOT NULL,
  `sb_status` varchar(20) NOT NULL DEFAULT 'active',
  `valid_from` date NOT NULL,
  `valid_to` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Daten für Tabelle `subscription`
--

INSERT INTO `subscription` (`sb_id`, `sr_id`, `user_type`, `user_id`, `pl_code`, `pl_version_current`, `sb_status`, `valid_from`, `valid_to`) VALUES
(115, 30118, 'mand', 28, 'BASIS', 'A1', 'expired', '2026-02-01', '2026-02-28'),
(170, 30118, 'cust', 38, 'PLUS', 'A1', 'expired', '2026-02-01', '2026-02-28'),
(205, 30118, 'cust', 44, 'PREMIUM', 'A1', 'expired', '2026-02-01', '2026-02-28');

--
-- Indizes der exportierten Tabellen
--

--
-- Indizes für die Tabelle `invoice`
--
ALTER TABLE `invoice`
  ADD PRIMARY KEY (`re_nr`),
  ADD KEY `idx_invoice_date` (`invoice_date`),
  ADD KEY `fk_invoice_subscriber` (`sr_id`),
  ADD KEY `fk_invoice_from_le` (`from_le_id`),
  ADD KEY `fk_invoice_to_le` (`to_le_id`);

--
-- Indizes für die Tabelle `ledger_entry`
--
ALTER TABLE `ledger_entry`
  ADD PRIMARY KEY (`le_id`),
  ADD KEY `idx_ledger_type` (`entry_type`),
  ADD KEY `idx_ledger_booked` (`booked_at`),
  ADD KEY `fk_ledger_subscriber` (`sr_id`),
  ADD KEY `fk_ledger_subscription` (`sb_id`),
  ADD KEY `fk_ledger_context` (`context_le_id`);

--
-- Indizes für die Tabelle `plan`
--
ALTER TABLE `plan`
  ADD PRIMARY KEY (`pl_id`),
  ADD UNIQUE KEY `uq_plan_code_version` (`pl_code`,`pl_version`),
  ADD KEY `idx_plan_code` (`pl_code`);

--
-- Indizes für die Tabelle `subscriber`
--
ALTER TABLE `subscriber`
  ADD PRIMARY KEY (`sr_id`);

--
-- Indizes für die Tabelle `subscription`
--
ALTER TABLE `subscription`
  ADD PRIMARY KEY (`sb_id`),
  ADD KEY `idx_subscription_user` (`user_type`,`user_id`),
  ADD KEY `idx_subscription_status` (`sb_status`),
  ADD KEY `fk_subscription_plan` (`pl_code`,`pl_version_current`),
  ADD KEY `fk_subscription_subscriber` (`sr_id`);

--
-- AUTO_INCREMENT für exportierte Tabellen
--

--
-- AUTO_INCREMENT für Tabelle `invoice`
--
ALTER TABLE `invoice`
  MODIFY `re_nr` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT für Tabelle `ledger_entry`
--
ALTER TABLE `ledger_entry`
  MODIFY `le_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=58;

--
-- AUTO_INCREMENT für Tabelle `plan`
--
ALTER TABLE `plan`
  MODIFY `pl_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT für Tabelle `subscriber`
--
ALTER TABLE `subscriber`
  MODIFY `sr_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30119;

--
-- AUTO_INCREMENT für Tabelle `subscription`
--
ALTER TABLE `subscription`
  MODIFY `sb_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=206;

--
-- Constraints der exportierten Tabellen
--

--
-- Constraints der Tabelle `invoice`
--
ALTER TABLE `invoice`
  ADD CONSTRAINT `fk_invoice_from_le` FOREIGN KEY (`from_le_id`) REFERENCES `ledger_entry` (`le_id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_invoice_subscriber` FOREIGN KEY (`sr_id`) REFERENCES `subscriber` (`sr_id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_invoice_to_le` FOREIGN KEY (`to_le_id`) REFERENCES `ledger_entry` (`le_id`) ON UPDATE CASCADE;

--
-- Constraints der Tabelle `ledger_entry`
--
ALTER TABLE `ledger_entry`
  ADD CONSTRAINT `fk_ledger_context` FOREIGN KEY (`context_le_id`) REFERENCES `ledger_entry` (`le_id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_ledger_subscriber` FOREIGN KEY (`sr_id`) REFERENCES `subscriber` (`sr_id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_ledger_subscription` FOREIGN KEY (`sb_id`) REFERENCES `subscription` (`sb_id`) ON UPDATE CASCADE;

--
-- Constraints der Tabelle `subscription`
--
ALTER TABLE `subscription`
  ADD CONSTRAINT `fk_subscription_plan` FOREIGN KEY (`pl_code`,`pl_version_current`) REFERENCES `plan` (`pl_code`, `pl_version`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_subscription_subscriber` FOREIGN KEY (`sr_id`) REFERENCES `subscriber` (`sr_id`) ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
