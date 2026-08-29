-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Erstellungszeit: 27. Aug 2026 um 19:43
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
-- Datenbank: `u14bc1w8_v08_fotodb`
--

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `activity_group`
--

CREATE TABLE `activity_group` (
  `ag_id` bigint(20) UNSIGNED NOT NULL,
  `ag_title` varchar(255) NOT NULL,
  `ag_subtitle` varchar(255) NOT NULL,
  `ag_text` varchar(255) NOT NULL,
  `mand_id` bigint(20) NOT NULL,
  `ag_sec_level` tinyint(3) UNSIGNED NOT NULL,
  `ag_prefstat` bigint(20) NOT NULL DEFAULT 50,
  `ag_sort_date` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `activity_subgroup`
--

CREATE TABLE `activity_subgroup` (
  `asg_id` bigint(20) UNSIGNED NOT NULL,
  `asg_title` varchar(255) NOT NULL,
  `asg_subtitle` varchar(255) NOT NULL,
  `asg_text` varchar(255) NOT NULL,
  `asg_public` tinyint(1) NOT NULL,
  `mand_id` bigint(20) NOT NULL,
  `asg_sec_level` tinyint(3) UNSIGNED NOT NULL,
  `ag_id` bigint(20) UNSIGNED NOT NULL,
  `asg_prefstat` bigint(20) NOT NULL DEFAULT 5,
  `asg_sort_date` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `ag_fo_context`
--

CREATE TABLE `ag_fo_context` (
  `ag_fo_id` bigint(20) UNSIGNED NOT NULL,
  `ag_is_banner` tinyint(1) NOT NULL,
  `ag_id` bigint(20) NOT NULL,
  `fo_id` bigint(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `asg_fo_context`
--

CREATE TABLE `asg_fo_context` (
  `asg_fo_id` bigint(20) UNSIGNED NOT NULL,
  `asg_id` bigint(20) NOT NULL,
  `fo_id` bigint(20) NOT NULL,
  `ags_is_banner` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `foto_obj`
--

CREATE TABLE `foto_obj` (
  `fo_id` bigint(20) UNSIGNED NOT NULL,
  `fo_is_video` tinyint(1) NOT NULL,
  `fo_filename` varchar(255) NOT NULL,
  `fo_title` varchar(255) NOT NULL,
  `fo_subtitle` varchar(255) NOT NULL,
  `fo_text` varchar(255) NOT NULL,
  `mand_id` bigint(20) NOT NULL,
  `fo_sec_level` tinyint(3) UNSIGNED NOT NULL,
  `fo_datetime` datetime NOT NULL,
  `db_saved` tinyint(1) NOT NULL,
  `fo_filepath` varchar(255) NOT NULL,
  `fo_prefstat` bigint(20) NOT NULL DEFAULT 50,
  `foto_comment` blob NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `mand_profile`
--

CREATE TABLE `mand_profile` (
  `mp_id` bigint(20) UNSIGNED NOT NULL,
  `mand_id` bigint(20) NOT NULL,
  `mp_name` varchar(255) NOT NULL,
  `mp_title` varchar(255) NOT NULL,
  `mp_text` text NOT NULL COMMENT 'Langtext mit Vorstellung des Mand',
  `mp_title_start` varchar(255) NOT NULL COMMENT 'Überschrift für die Startseite',
  `mp_subtitle_start` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `mp_fo_context`
--

CREATE TABLE `mp_fo_context` (
  `mp_fo_id` bigint(20) UNSIGNED NOT NULL,
  `mp_id` bigint(20) NOT NULL,
  `fo_id` bigint(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

--
-- Indizes der exportierten Tabellen
--

--
-- Indizes für die Tabelle `activity_group`
--
ALTER TABLE `activity_group`
  ADD PRIMARY KEY (`ag_id`),
  ADD KEY `activity_group_mand_id_index` (`mand_id`);

--
-- Indizes für die Tabelle `activity_subgroup`
--
ALTER TABLE `activity_subgroup`
  ADD PRIMARY KEY (`asg_id`),
  ADD KEY `activity_subgroup_ag_id_index` (`ag_id`),
  ADD KEY `activity_subgroup_mand_id_index` (`mand_id`);

--
-- Indizes für die Tabelle `ag_fo_context`
--
ALTER TABLE `ag_fo_context`
  ADD PRIMARY KEY (`ag_fo_id`),
  ADD KEY `ag_fo_context_ag_id_index` (`ag_id`);

--
-- Indizes für die Tabelle `asg_fo_context`
--
ALTER TABLE `asg_fo_context`
  ADD PRIMARY KEY (`asg_fo_id`),
  ADD KEY `asg_fo_context_asg_id_index` (`asg_id`);

--
-- Indizes für die Tabelle `foto_obj`
--
ALTER TABLE `foto_obj`
  ADD PRIMARY KEY (`fo_id`);

--
-- Indizes für die Tabelle `mand_profile`
--
ALTER TABLE `mand_profile`
  ADD PRIMARY KEY (`mp_id`);

--
-- Indizes für die Tabelle `mp_fo_context`
--
ALTER TABLE `mp_fo_context`
  ADD PRIMARY KEY (`mp_fo_id`),
  ADD KEY `mp_fo_context_mp_id_index` (`mp_id`);

--
-- AUTO_INCREMENT für exportierte Tabellen
--

--
-- AUTO_INCREMENT für Tabelle `activity_group`
--
ALTER TABLE `activity_group`
  MODIFY `ag_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT für Tabelle `activity_subgroup`
--
ALTER TABLE `activity_subgroup`
  MODIFY `asg_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT für Tabelle `ag_fo_context`
--
ALTER TABLE `ag_fo_context`
  MODIFY `ag_fo_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT für Tabelle `asg_fo_context`
--
ALTER TABLE `asg_fo_context`
  MODIFY `asg_fo_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT für Tabelle `foto_obj`
--
ALTER TABLE `foto_obj`
  MODIFY `fo_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT für Tabelle `mand_profile`
--
ALTER TABLE `mand_profile`
  MODIFY `mp_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT für Tabelle `mp_fo_context`
--
ALTER TABLE `mp_fo_context`
  MODIFY `mp_fo_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- Constraints der exportierten Tabellen
--

--
-- Constraints der Tabelle `activity_subgroup`
--
ALTER TABLE `activity_subgroup`
  ADD CONSTRAINT `activity_subgroup_ag_id_foreign` FOREIGN KEY (`ag_id`) REFERENCES `activity_group` (`ag_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
