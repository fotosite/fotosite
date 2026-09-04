-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Erstellungszeit: 04. Sep 2026 um 20:23
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
-- Datenbank: `u14bc1w8_v08_fotoblobdb`
--

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `foto_obj`
--

CREATE TABLE `foto_obj` (
  `fod_id` bigint(20) UNSIGNED NOT NULL,
  `fo_id` bigint(20) NOT NULL,
  `fod_obj` blob NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

--
-- Indizes der exportierten Tabellen
--

--
-- Indizes für die Tabelle `foto_obj`
--
ALTER TABLE `foto_obj`
  ADD PRIMARY KEY (`fod_id`),
  ADD KEY `foto_obj_fo_id_index` (`fo_id`);

--
-- AUTO_INCREMENT für exportierte Tabellen
--

--
-- AUTO_INCREMENT für Tabelle `foto_obj`
--
ALTER TABLE `foto_obj`
  MODIFY `fod_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
