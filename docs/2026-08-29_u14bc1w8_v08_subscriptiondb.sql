-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Erstellungszeit: 29. Aug 2026 um 12:56
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
  `entry_type` enum('FO','GG','ZE','ZG','LS') NOT NULL,
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

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `subscriber`
--

CREATE TABLE `subscriber` (
  `sr_id` bigint(20) UNSIGNED NOT NULL,
  `sr_name` varchar(255) NOT NULL,
  `created_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
  MODIFY `le_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT für Tabelle `plan`
--
ALTER TABLE `plan`
  MODIFY `pl_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT für Tabelle `subscriber`
--
ALTER TABLE `subscriber`
  MODIFY `sr_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT für Tabelle `subscription`
--
ALTER TABLE `subscription`
  MODIFY `sb_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

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
