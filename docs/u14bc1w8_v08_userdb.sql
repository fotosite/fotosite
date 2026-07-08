-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Erstellungszeit: 29. Jun 2026 um 19:22
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
-- Datenbank: `u14bc1w8_v08_userdb`
--

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `cust_invite`
--

CREATE TABLE `cust_invite` (
  `invite_id` bigint(20) UNSIGNED NOT NULL,
  `mand_id` bigint(20) UNSIGNED NOT NULL,
  `cust_email` varchar(255) NOT NULL,
  `cust_alias` varchar(255) NOT NULL,
  `sec_level` tinyint(3) UNSIGNED NOT NULL,
  `token` varchar(128) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `expires_at` datetime NOT NULL,
  `used` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `cust_pcode`
--

CREATE TABLE `cust_pcode` (
  `pcode_id` bigint(20) UNSIGNED NOT NULL,
  `mand_id` bigint(20) NOT NULL,
  `cust_id` bigint(20) NOT NULL,
  `cust_passcode` varchar(255) NOT NULL COMMENT '= sec_level (num)',
  `cust_alias` varchar(255) DEFAULT NULL COMMENT 'Interner Name des Mandanten für diesen Kunden',
  `pcode_prefstat` bigint(20) NOT NULL,
  `mand_sort_date` date NOT NULL DEFAULT current_timestamp(),
  `cust_mailrequest` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

--
-- Daten für Tabelle `cust_pcode`
--

INSERT INTO `cust_pcode` (`pcode_id`, `mand_id`, `cust_id`, `cust_passcode`, `cust_alias`, `pcode_prefstat`, `mand_sort_date`, `cust_mailrequest`) VALUES
(26, 16, 15, '5', 'huhnter', 0, '2026-06-15', 1),
(32, 6, 26, '3', 'Nachtbar', 1, '2026-06-21', 0),
(45, 30, 33, '3', 'Nk', 1, '2026-06-25', 0),
(48, 28, 34, '5', 'Wdd', 1, '2026-06-27', 0),
(49, 28, 35, '5', 'Hint', 1, '2026-06-27', 0);

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `cust_user`
--

CREATE TABLE `cust_user` (
  `cust_id` bigint(20) UNSIGNED NOT NULL,
  `cust_uname` varchar(255) DEFAULT NULL,
  `cust_email` varchar(255) NOT NULL,
  `cust_tel` varchar(255) NOT NULL DEFAULT 'nicht vorhanden',
  `cust_firstname` varchar(255) NOT NULL,
  `cust_lastname` varchar(255) NOT NULL,
  `cust_street+nr` varchar(255) NOT NULL DEFAULT 'nicht vorhanden',
  `cust_postcode_city` varchar(255) NOT NULL DEFAULT 'nicht vorhanden',
  `cust_company` varchar(255) NOT NULL DEFAULT 'nicht vorhanden',
  `cust_pw_hash` varchar(255) NOT NULL,
  `cust_2fa_opt_in` tinyint(1) NOT NULL DEFAULT 0,
  `ds_accepted_at` datetime DEFAULT NULL,
  `ds_version` varchar(20) DEFAULT NULL,
  `upload_terms_accepted_at` datetime DEFAULT NULL,
  `upload_terms_version` varchar(20) DEFAULT NULL,
  `show_welcome` tinyint(1) UNSIGNED NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

--
-- Daten für Tabelle `cust_user`
--

INSERT INTO `cust_user` (`cust_id`, `cust_uname`, `cust_email`, `cust_tel`, `cust_firstname`, `cust_lastname`, `cust_street+nr`, `cust_postcode_city`, `cust_company`, `cust_pw_hash`, `cust_2fa_opt_in`, `ds_accepted_at`, `ds_version`, `upload_terms_accepted_at`, `upload_terms_version`, `show_welcome`) VALUES
(15, 'Hazwo', 'subumaster@web.de', 'xy', 'Hazwode', 'Gehzwo', 'x', 'dfbxbfgnbx', 'x', '$2y$12$PT.fn7P6JGj48Gvz8IoageZ1ROnKwy6QNuuXHHJiNzlM7qDQUgccG', 0, '2026-06-21 15:17:37', '2.9', '2026-06-21 15:17:45', '2.2', 0),
(33, 'Nk', 'newkid9@web.de', 'nicht vorhanden', 'Ml', 'Mk', 'Jhjk', 'LG hbl', 'nicht vorhanden', '$2y$12$LUYhoh3ZWOZMeC8WWAx.2exWaXHXRprwhhGNhFKwH0neYYdz6aLOi', 0, '2026-06-29 15:26:29', '3.1', '2026-06-29 15:26:32', '2.5', 0),
(34, 'Wdf', 'ich-bin-wieder-da@bin-wieder-da.de', 'nicht vorhanden', 'H', 'H', 'J', 'J', 'nicht vorhanden', '$2y$12$siV7I1mBn02XkBKa0RziHe4KG5LA2qXWtUW6cQCIFE6fFBtY8ZaXm', 0, '2026-06-29 16:23:34', '3.1', '2026-06-29 16:23:36', '2.5', 0),
(35, 'Hint', 'hntr2@mail.de', 'nicht vorhanden', 'Haber', 'H', 'Vh', 'Hh', 'nicht vorhanden', '$2y$12$S5Mk25wKSysK/h6guJ5Zs.UPo00a42wuGlZsi5arufv77RXcCYT1K', 0, '2026-06-29 15:59:22', '3.1', '2026-06-29 15:59:23', '2.5', 0);

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `invite`
--

CREATE TABLE `invite` (
  `inv_id` bigint(20) UNSIGNED NOT NULL,
  `inv_email` varchar(255) NOT NULL,
  `inv_token_hash` varchar(255) NOT NULL,
  `inv_type` enum('register','pw_reset','email_change') NOT NULL,
  `inv_user_type` enum('syst','mand','cust') NOT NULL,
  `inv_user_id` bigint(20) UNSIGNED DEFAULT NULL COMMENT 'NULL for register, set for pw_reset',
  `inv_mand_id` bigint(20) UNSIGNED DEFAULT NULL COMMENT 'Only for cust: related mandant',
  `is_primary` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `expires_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

--
-- Daten für Tabelle `invite`
--

INSERT INTO `invite` (`inv_id`, `inv_email`, `inv_token_hash`, `inv_type`, `inv_user_type`, `inv_user_id`, `inv_mand_id`, `is_primary`, `created_at`, `expires_at`) VALUES
(4, 'cristalblue@mail.de', '0d05148dcf696d5dd88f28b840c642e36b9c923f7cbace95fdedd5c636587a8f', 'register', 'mand', NULL, NULL, 0, '2026-05-12 15:36:20', '2026-05-13 15:36:20'),
(15, 'wer-hat@die-genossen.de', 'b0e34114988a6a43331df7cdcd884ea0ca70b018e922c3615a47eedbb50440d0', 'register', 'mand', NULL, NULL, 0, '2026-05-29 17:10:31', '2026-05-30 17:10:31'),
(16, 'newkid9@web.de', '29254cb3df8a602055520f44b79d618ac80ec1a36f38097a05a7608de70e460f', 'register', 'mand', NULL, NULL, 0, '2026-05-30 06:20:33', '2026-05-31 06:20:33'),
(22, 'x519@kommespaeter.de', '5e9bc5c7546bd0f9ddb499f2c0f10509fb09dbcc59986b8c0b7e3f220d0e52d0', 'register', 'mand', NULL, NULL, 0, '2026-06-10 07:50:45', '2026-06-11 07:50:45'),
(27, 'schanzer3@web.de', '6b8ec8b0deb3c912415b669c379e98c68666753b95fb72c326a0b08738e541eb', 'pw_reset', 'mand', 6, NULL, 0, '2026-06-12 14:16:08', '2026-06-13 14:16:08'),
(32, 'donkey-shot@web.de', '7a83253a582954bd68f2f103c13c9e95d47efdfba9263096ea7b4936bbe3b136', 'pw_reset', 'mand', 16, NULL, 0, '2026-06-13 08:55:11', '2026-06-14 08:55:11'),
(36, 'subumaster@web.de', '426f7b8e5c97e36e02f0e4ca27f7a1a875880a583f47fdd81079304af072d0f2', 'pw_reset', 'cust', 21, NULL, 0, '2026-06-14 17:51:42', '2026-06-15 17:51:42'),
(39, 'neandertal.man@web.de', '4de4c3c7c86bef596dfb25a2b495e9d13ec1db432db83d8a591dad6175358f29', 'register', 'mand', NULL, NULL, 0, '2026-06-17 10:20:17', '2026-06-18 10:20:17'),
(41, 'neandertal.man@web.de', '81c0ac61a868b5eec2738333f86a3e6151f489a38f9c348d28e970c8b036dcef', 'register', 'mand', NULL, NULL, 0, '2026-06-17 10:59:03', '2026-06-18 10:59:03'),
(42, 'neandertal.man@web.de', 'f6c6a165b52937e0d8ff2b1083f6838a5d6cb8478335b66e8d6c9c48b6334976', 'register', 'mand', NULL, NULL, 0, '2026-06-17 11:49:28', '2026-06-18 11:49:28'),
(43, 'neandertal.man@web.de', 'd37a45173eb0acda7c54d3898a880444797f5a02d57e83c9799de30d97a525be', 'register', 'mand', NULL, NULL, 0, '2026-06-17 11:57:45', '2026-06-18 11:57:45'),
(44, 'x519@quantentunnel.de', 'd78053090f29584f703fd9c45914a24f4af7f421faa033bc0305d5a62f4b6e26', 'register', 'mand', NULL, NULL, 0, '2026-06-17 11:59:14', '2026-06-18 11:59:14'),
(58, 'hntr2@mail.de', 'f297c7e7ab414bcbae34a43b27148fde16a59b5f500997769a3dc151840e9171', 'pw_reset', 'cust', 15, NULL, 0, '2026-06-18 17:27:19', '2026-06-19 17:27:19'),
(62, 'subumaster@web.de', '972485088644e06095a73d80656587f9ef2925aa00ac3007482930d3d5fcbc42', 'pw_reset', 'cust', 21, NULL, 0, '2026-06-19 09:06:57', '2026-06-20 09:06:57'),
(63, 'moonshine_gf@web.de', '87dba12580608615f4b86914884b2f5b71b0c8ce1e97934b45ad74b3dc29ca82', 'pw_reset', 'mand', 17, NULL, 0, '2026-06-19 09:10:53', '2026-06-20 09:10:53'),
(90, 'cristalblue@mail.de', '59f9c00c48806d017ffcce66a269fb7b771cc0dad37f5100bcfb1f7387cabf76', 'register', 'mand', NULL, NULL, 0, '2026-06-23 18:51:08', '2026-06-24 18:51:08'),
(102, 'harburg_bergfex@alpenjodel.de', 'a88e1bacfa9426d99f269b2c95b444e7c31aac87e4df34447f206c63610aeab7', 'pw_reset', 'syst', 7, NULL, 0, '2026-06-28 08:20:45', '2026-06-29 08:20:45'),
(103, 'schanzer3@web.de', 'ad6f505bbc1427b11d7b98fe8c85ead9ea88394b3dd27778aea913844a45dece', 'register', 'syst', NULL, NULL, 0, '2026-06-29 10:54:51', '2026-06-30 10:54:51'),
(104, 'cristalblue@mail.de', 'f924f827e1e85871588d6fd7e2065919f3eb271bbb489e9d4ce6903d558594af', 'register', 'mand', NULL, NULL, 0, '2026-06-29 11:00:31', '2026-06-30 11:00:31'),
(105, 'harburg_bergfex@alpenjodel.de', 'ef4f94d619a4211e5e52bcf633d44c0bd0719648412876b8319483a90e10fcdc', 'pw_reset', 'syst', 7, NULL, 0, '2026-06-29 11:04:57', '2026-06-30 11:04:57'),
(110, 'hntr4@mail.de', '1e1bf717cb8b97786937a3f03a89189ed980a6673a23a2dae6017d5ad8230f0d', 'register', 'syst', NULL, NULL, 0, '2026-06-29 13:40:08', '2026-06-30 13:40:08'),
(111, 'hntr4@mail.de', '5f62b88c241e28c3c2de2e396c42427d20285a58bd3e5eecbe109b09465afd38', 'register', 'syst', NULL, NULL, 0, '2026-06-29 13:40:09', '2026-06-30 13:40:09'),
(112, 'ich-bin-wieder-da@bin-wieder-da.de', 'd5ad01ad09c5f8e887f1edb294951852ac9d345b7b7cb4b8d63a4fed827a3c02', 'pw_reset', 'cust', 34, NULL, 0, '2026-06-29 16:24:25', '2026-06-30 16:24:25'),
(113, 'donkey-shot@web.de', 'fa8fe4a0ebe64bc686cada442c691fef87137ea591561a96dbb604c6dde5b3bf', 'pw_reset', 'mand', 16, NULL, 0, '2026-06-29 16:26:56', '2026-06-30 16:26:56');

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `mand_user`
--

CREATE TABLE `mand_user` (
  `mand_id` bigint(20) UNSIGNED NOT NULL,
  `mand_uname` varchar(255) DEFAULT NULL,
  `mand_email` varchar(255) NOT NULL,
  `mand_tel` varchar(255) NOT NULL DEFAULT 'nicht vorhanden',
  `mand_firstname` varchar(255) NOT NULL,
  `mand_lastname` varchar(255) NOT NULL,
  `mand_street+nr` varchar(255) NOT NULL DEFAULT 'nicht vorhanden',
  `mand_postcode+city` varchar(255) NOT NULL DEFAULT 'nicht vorhanden',
  `mand_company` varchar(255) NOT NULL DEFAULT 'nicht vorhanden',
  `mand_pw_hash` varchar(255) NOT NULL,
  `mand_prefstat` bigint(20) NOT NULL DEFAULT 0 COMMENT 'kann später verwendet werden, füt Zugriff auf sec_level 6 / DB-Speicherung\r\n',
  `has_public_content` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'public = systemweit public, Mitglieder können freigegebene Inhalte anderer Mand auch ohne expilzite Zuordnung sehen',
  `active` tinyint(1) NOT NULL DEFAULT 1 COMMENT 'für syst-user, Galerie offline stellen ',
  `valid_to` date DEFAULT NULL COMMENT 'option für zahlende mand bei Zahlungsausfall',
  `mand_cust_2fa` tinyint(3) UNSIGNED NOT NULL DEFAULT 3 COMMENT 'Ab dieser Sicherheitsstufe wird 2FA für Mitglieder erzwungen (0=nie, 7=immer)',
  `mand_2fa_opt_in` tinyint(1) NOT NULL DEFAULT 1 COMMENT '2FA per Email aktiv (Standard: ja)',
  `ds_accepted_at` datetime DEFAULT NULL,
  `ds_version` varchar(20) DEFAULT NULL,
  `upload_terms_accepted_at` datetime DEFAULT NULL,
  `upload_terms_version` varchar(20) DEFAULT NULL,
  `show_welcome` tinyint(1) UNSIGNED NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

--
-- Daten für Tabelle `mand_user`
--

INSERT INTO `mand_user` (`mand_id`, `mand_uname`, `mand_email`, `mand_tel`, `mand_firstname`, `mand_lastname`, `mand_street+nr`, `mand_postcode+city`, `mand_company`, `mand_pw_hash`, `mand_prefstat`, `has_public_content`, `active`, `valid_to`, `mand_cust_2fa`, `mand_2fa_opt_in`, `ds_accepted_at`, `ds_version`, `upload_terms_accepted_at`, `upload_terms_version`, `show_welcome`) VALUES
(16, 'Pinz', 'donkey-shot@web.de', 'kk', 'Peterx', 'Hinz', 'asdSD', 'sada', 'klköl', '$2y$12$s9yau/Rb6zqlZx3UuwDbdOLPZx8Tcj304G2jNP.AzibrYdourDoQe', 0, 0, 1, NULL, 1, 1, '2026-06-21 15:17:00', '2.9', '2026-06-21 15:17:10', '2.2', 0),
(17, 'moonshine', 'moonshine_gf@web.de', 'adsfghjkjghsf', 'moon', 'shine', '', '', 'sfhdgjkf', '$2y$12$tDd9gIE0Y/dZOAIz1FkYn.L4yMBWU9XZ9BIC5S4hOhyNoegIm7z4K', 0, 0, 1, NULL, 0, 1, '2026-06-17 09:54:56', '1.0', '2026-06-17 09:54:56', '1.0', 1),
(28, 'Newb', 'newkid9@web.de', 'nicht vorhanden', 'Newb', 'Newb', 'Newb', 'Newb', 'nicht vorhanden', '$2y$12$zJT/hlyNRFwn5Jpm0Qg2Uu2pSpFH5WBNGXQ9fexhGlf/QWt7R1Zh6', 0, 0, 1, NULL, 4, 1, '2026-06-29 16:01:42', '3.1', '2026-06-29 16:01:44', '2.5', 0),
(30, 'Ade', 'anderelbe@unterderbruecke.de', 'nicht vorhanden', 'Ade', 'Ade', 'Ade', 'Ade', 'nicht vorhanden', '$2y$12$2X2QNrLGmZvj0EmxTRXJ0.jX3EnlPMuLezloKqQGzIA5DU6lJd.2G', 0, 0, 1, NULL, 0, 1, '2026-06-25 16:51:16', '2.9', '2026-06-25 16:51:30', '2.2', 0),
(31, 'Hnt', 'hntr4@mail.de', 'nicht vorhanden', 'Hnt', 'Hnt', 'Hnt', 'Hnt', 'nicht vorhanden', '$2y$12$e3kQF0wB1cP8VEpzzfGa8.sFlYri.EM0MYzyOOtTfZbWixkX3uuLm', 0, 0, 1, NULL, 0, 1, '2026-06-29 15:24:20', '3.1', '2026-06-29 15:24:25', '2.5', 0);

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `passkey`
--

CREATE TABLE `passkey` (
  `pk_id` bigint(20) UNSIGNED NOT NULL,
  `user_type` enum('syst','mand','cust') NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `credential_id` varchar(512) NOT NULL,
  `public_key` text NOT NULL,
  `sign_count` bigint(20) UNSIGNED NOT NULL DEFAULT 0,
  `device_name` varchar(255) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `last_used_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `passkey_dismissed`
--

CREATE TABLE `passkey_dismissed` (
  `pd_id` bigint(20) UNSIGNED NOT NULL,
  `user_type` enum('mand','cust') NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `os` enum('win','andr','ios') NOT NULL,
  `ua_hash` varchar(64) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

--
-- Daten für Tabelle `passkey_dismissed`
--

INSERT INTO `passkey_dismissed` (`pd_id`, `user_type`, `user_id`, `os`, `ua_hash`, `created_at`) VALUES
(9, 'mand', 28, 'ios', '48da1af908be076363e16c00999867b2c05b0261ae4e010e737f12eae1e71aff', '2026-06-27 17:13:37');

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `policy_versions`
--

CREATE TABLE `policy_versions` (
  `pv_key` varchar(50) NOT NULL,
  `pv_value` varchar(20) NOT NULL,
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

--
-- Daten für Tabelle `policy_versions`
--

INSERT INTO `policy_versions` (`pv_key`, `pv_value`, `updated_at`) VALUES
('ds_version', '3.1', '2026-06-29 17:22:01'),
('upload_version', '2.5', '2026-06-29 17:22:04');

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `syst_user`
--

CREATE TABLE `syst_user` (
  `syst_id` bigint(20) UNSIGNED NOT NULL,
  `syst_uname` varchar(255) DEFAULT NULL,
  `syst_email` varchar(255) NOT NULL,
  `syst_tel` varchar(255) NOT NULL,
  `syst_firstname` varchar(255) NOT NULL,
  `syst_lastname` varchar(255) NOT NULL,
  `syst_street+nr` varchar(255) NOT NULL,
  `syst_pcode+city` varchar(255) NOT NULL,
  `syst_company` varchar(255) NOT NULL,
  `syst_pw_hash` varchar(255) NOT NULL,
  `is_primary` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

--
-- Daten für Tabelle `syst_user`
--

INSERT INTO `syst_user` (`syst_id`, `syst_uname`, `syst_email`, `syst_tel`, `syst_firstname`, `syst_lastname`, `syst_street+nr`, `syst_pcode+city`, `syst_company`, `syst_pw_hash`, `is_primary`) VALUES
(1, 'Martins System', 'frank.euring@mail.de', '+49 162 560 21 02', 'Martin', 'System', '-', '-', '-', '$2y$12$lyU7U/Hw7Pe6h5Dtexcm2O7p3eZXQPAFXHyLSmbLpXYYWK/fSb95K', 1),
(7, 'bergfex', 'harburg_bergfex@alpenjodel.de', 'Yde3#4rfc0987', 'bergfex', 'bergfex', '', '', '', '$2y$12$swwAPkjCiVK/0Zfm7QdqUOceWfzWQixZ5itTCls7plkaubrSYtPYW', 1),
(11, 'subuz', 'anderelbe@unterderbruecke.de', 'subuz', 'subuz', 'subuz', '', '', '', '$2y$12$gitV2Y0ni32PqO3/s51iCuiowJ7lDEdn/DwCdlIZZZHQPG2Vxd.AO', 0),
(12, 'Newnew', 'newkid9@web.de', '7tzji', 'Newnew', 'Newnewc', '', '', '', '$2y$12$mU33sxavqhWlFuGTJfhj3uWsWJms88NrPE/pBX4nnLg.5y4smpI0q', 0);

--
-- Indizes der exportierten Tabellen
--

--
-- Indizes für die Tabelle `cust_invite`
--
ALTER TABLE `cust_invite`
  ADD PRIMARY KEY (`invite_id`),
  ADD UNIQUE KEY `cust_invite_token_unique` (`token`),
  ADD KEY `cust_invite_mand_id_index` (`mand_id`);

--
-- Indizes für die Tabelle `cust_pcode`
--
ALTER TABLE `cust_pcode`
  ADD PRIMARY KEY (`pcode_id`),
  ADD KEY `cust_pcode_mand_id_index` (`mand_id`),
  ADD KEY `cust_pcode_cust_id_index` (`cust_id`);

--
-- Indizes für die Tabelle `cust_user`
--
ALTER TABLE `cust_user`
  ADD PRIMARY KEY (`cust_id`),
  ADD UNIQUE KEY `cust_user_cust_email_unique` (`cust_email`),
  ADD UNIQUE KEY `cust_user_cust_uname_unique` (`cust_uname`);

--
-- Indizes für die Tabelle `invite`
--
ALTER TABLE `invite`
  ADD PRIMARY KEY (`inv_id`),
  ADD KEY `invite_inv_token_hash_index` (`inv_token_hash`),
  ADD KEY `invite_expires_at_index` (`expires_at`);

--
-- Indizes für die Tabelle `mand_user`
--
ALTER TABLE `mand_user`
  ADD PRIMARY KEY (`mand_id`),
  ADD UNIQUE KEY `mand_user_mand_email_unique` (`mand_email`),
  ADD UNIQUE KEY `mand_user_mand_uname_unique` (`mand_uname`),
  ADD KEY `mand_user_mand_prefstat_index` (`mand_prefstat`);

--
-- Indizes für die Tabelle `passkey`
--
ALTER TABLE `passkey`
  ADD PRIMARY KEY (`pk_id`),
  ADD UNIQUE KEY `passkey_credential_id_unique` (`credential_id`),
  ADD KEY `passkey_user_index` (`user_type`,`user_id`);

--
-- Indizes für die Tabelle `passkey_dismissed`
--
ALTER TABLE `passkey_dismissed`
  ADD PRIMARY KEY (`pd_id`),
  ADD UNIQUE KEY `passkey_dismissed_unique` (`user_type`,`user_id`,`os`,`ua_hash`),
  ADD KEY `passkey_dismissed_user_idx` (`user_type`,`user_id`);

--
-- Indizes für die Tabelle `policy_versions`
--
ALTER TABLE `policy_versions`
  ADD PRIMARY KEY (`pv_key`);

--
-- Indizes für die Tabelle `syst_user`
--
ALTER TABLE `syst_user`
  ADD PRIMARY KEY (`syst_id`),
  ADD UNIQUE KEY `syst_user_syst_email_unique` (`syst_email`),
  ADD UNIQUE KEY `syst_user_syst_tel_unique` (`syst_tel`),
  ADD UNIQUE KEY `syst_user_syst_uname_unique` (`syst_uname`);

--
-- AUTO_INCREMENT für exportierte Tabellen
--

--
-- AUTO_INCREMENT für Tabelle `cust_invite`
--
ALTER TABLE `cust_invite`
  MODIFY `invite_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT für Tabelle `cust_pcode`
--
ALTER TABLE `cust_pcode`
  MODIFY `pcode_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=51;

--
-- AUTO_INCREMENT für Tabelle `cust_user`
--
ALTER TABLE `cust_user`
  MODIFY `cust_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=37;

--
-- AUTO_INCREMENT für Tabelle `invite`
--
ALTER TABLE `invite`
  MODIFY `inv_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=117;

--
-- AUTO_INCREMENT für Tabelle `mand_user`
--
ALTER TABLE `mand_user`
  MODIFY `mand_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;

--
-- AUTO_INCREMENT für Tabelle `passkey`
--
ALTER TABLE `passkey`
  MODIFY `pk_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT für Tabelle `passkey_dismissed`
--
ALTER TABLE `passkey_dismissed`
  MODIFY `pd_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT für Tabelle `syst_user`
--
ALTER TABLE `syst_user`
  MODIFY `syst_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
