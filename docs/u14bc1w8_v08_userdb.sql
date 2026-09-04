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
  `mand_id` bigint(20) UNSIGNED NOT NULL,
  `cust_id` bigint(20) UNSIGNED NOT NULL,
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
(56, 36, 42, '4', 'foto5_S#4', 1, '2026-07-19', 0),
(58, 36, 40, '2', 'foto7_S#2', 1, '2026-07-19', 0),
(59, 36, 41, '3', 'foto8_S#3', 1, '2026-07-19', 0),
(66, 36, 50, '2', 'F6-Alias1', 1, '2026-08-26', 0),
(68, 41, 50, '2', 'Foto Sechs', 1, '2026-08-26', 0),
(78, 40, 61, '1', 'wdd', 1, '2026-09-02', 0);

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `cust_user`
--

CREATE TABLE `cust_user` (
  `cust_id` bigint(20) UNSIGNED NOT NULL,
  `cust_uname` varchar(255) DEFAULT NULL,
  `cust_email` varchar(255) NOT NULL,
  `cust_tel` varchar(255) DEFAULT NULL,
  `cust_firstname` varchar(255) NOT NULL,
  `cust_lastname` varchar(255) NOT NULL,
  `cust_street+nr` varchar(255) DEFAULT NULL,
  `cust_postcode_city` varchar(255) DEFAULT NULL,
  `cust_company` varchar(255) DEFAULT NULL,
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
(40, 'foto7', 'foto7@keemail.me', NULL, 'vfoto7', 'ffff', 'straße', 'stadt', 'org', '$2y$12$h144UB/vc.X5VSxThJV/x.mQM9aa3rZSRxQq9BWQ438fjPWvw3q3m', 0, '2026-08-30 13:03:30', '4.0', NULL, NULL, 0),
(41, 'foto8', 'foto8@keemail.me', NULL, 'foto8', 'foto8', 'foto8', 'foto8', NULL, '$2y$12$2QUn6nIYiZxT40SsU0AFAuN0fCs1IxNx5k1CasRoeNk7IfnKSkHtq', 0, '2026-07-29 18:31:31', '3.9', NULL, NULL, 1),
(42, 'foto5', 'foto5@keemail.me', NULL, 'foto5', 'foto5', NULL, NULL, NULL, '$2y$12$KSsTlr3runGpYhPZ.6E8P.nkide7eSksqBCHGRsOQinm50PhNLd0S', 0, '2026-08-26 14:44:08', '3.9', NULL, NULL, 0),
(50, 'Foto6', 'foto6@keemail.me', NULL, 'FF', 'Sechs', 'fdagaad', 'dfgaag', NULL, '$2y$12$sXHRhkOP/tz1Wi9yQrO6Iul35ivWLIJUyYLzQ5QHiyv7lJ6.HXTDy', 0, '2026-08-26 15:07:29', '3.9', NULL, NULL, 0),
(61, 'Wd', 'ich-bin-wieder-da@bin-wieder-da.de', NULL, 'W', 'Dd', NULL, NULL, NULL, '$2y$12$WXLYnEg4yx7F9pt5otWsp.3rtU77NKy7SV54t9Hb63RZAQH7j.8K6', 0, '2026-09-02 12:53:55', '4.0', NULL, NULL, 0);

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
(999920, 'newkid9@web.de', '39b8c09754a7b3f58cdf6cdfef6e33c9123109ba9ebdc5a3112c6ddb32a26ca6', 'register', 'mand', NULL, NULL, 0, '2026-09-04 12:52:11', '2026-09-05 12:52:11');

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `mand_user`
--

CREATE TABLE `mand_user` (
  `mand_id` bigint(20) UNSIGNED NOT NULL,
  `mand_uname` varchar(255) DEFAULT NULL,
  `mand_email` varchar(255) NOT NULL,
  `mand_tel` varchar(255) DEFAULT NULL,
  `mand_firstname` varchar(255) NOT NULL,
  `mand_lastname` varchar(255) NOT NULL,
  `mand_street+nr` varchar(255) DEFAULT NULL,
  `mand_postcode+city` varchar(255) DEFAULT NULL,
  `mand_company` varchar(255) DEFAULT NULL,
  `mand_pw_hash` varchar(255) NOT NULL,
  `mand_prefstat` bigint(20) NOT NULL DEFAULT 0 COMMENT 'kann später verwendet werden, füt Zugriff auf sec_level 6 / DB-Speicherung\r\n',
  `has_public_content` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'public = systemweit public, Mitglieder können freigegebene Inhalte anderer Mand auch ohne expilzite Zuordnung sehen',
  `active` tinyint(1) NOT NULL DEFAULT 1 COMMENT 'für syst-user, Galerie offline stellen ',
  `valid_to` date DEFAULT NULL COMMENT 'option für zahlende mand bei Zahlungsausfall',
  `mand_deactivated_at` datetime DEFAULT NULL COMMENT 'Zeitpunkt der Deaktivierung durch Syst, Basis fuer Karenzzeit vor endgueltiger Loeschung',
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

INSERT INTO `mand_user` (`mand_id`, `mand_uname`, `mand_email`, `mand_tel`, `mand_firstname`, `mand_lastname`, `mand_street+nr`, `mand_postcode+city`, `mand_company`, `mand_pw_hash`, `mand_prefstat`, `has_public_content`, `active`, `valid_to`, `mand_deactivated_at`, `mand_cust_2fa`, `mand_2fa_opt_in`, `ds_accepted_at`, `ds_version`, `upload_terms_accepted_at`, `upload_terms_version`, `show_welcome`) VALUES
(36, 'foto4', 'foto4@keemail.me', NULL, 'foto4', 'foto4', 'foto4', 'foto4', NULL, '$2y$12$wjHmz6p/XvediNWy418OeeDjBTz7FhX9qTmW4wuphjNc5BGyQVQGi', 0, 0, 0, NULL, '2026-09-04 18:06:54', 0, 1, '2026-07-19 15:35:55', '3.9', '2026-07-19 15:35:57', '3.1', 0),
(38, 'schanzer3@web.de', 'schanzer3@web.de', '13231235564', 'mailto:schanzer3@web.de', 'mailto:schanzer3@web.de', NULL, NULL, NULL, '$2y$12$.8gr1z85IfYoELNK44pBQeOIKiRey4BXtZDtAwI0VjdNFVUTtDfw2', 0, 0, 1, NULL, NULL, 0, 0, '2026-07-30 06:27:59', '3.9', '2026-07-30 06:28:01', '3.1', 0),
(40, 'HeinPee', 'cristalblue@mail.de', NULL, 'Heiner', 'Petersen', 'jjjj', 'jjjj', NULL, '$2y$12$ER9.SwY1pSdI..mqZoStvu3BIKdzZcyY6wNuQeB.kz8YmPJiVWUCS', 0, 0, 1, NULL, NULL, 0, 0, '2026-09-02 12:34:41', '4.0', '2026-09-02 12:34:43', '3.2', 0),
(41, 'Hunter4', 'hntr4@mail.de', '1474525', 'Hunt', 'Ervier', NULL, NULL, NULL, '$2y$12$kaWHfmFks7ze9ZuzolEEDeN5Ey9G8LR.KxdG/ww30uxxJ1oohMABu', 0, 1, 1, '2030-01-01', NULL, 0, 1, '2026-08-26 15:00:43', '3.9', '2026-08-26 15:00:45', '3.1', 0),
(42, 'Don Key', 'donkey-shot@web.de', '165494651', 'donkey', 'shot', NULL, NULL, NULL, '$2y$12$jbS3RK5/TqUFPQjCZrLjxuLkk2Us2F3FnxYBSIYzL8VR1LSHi2dKG', 0, 0, 1, NULL, NULL, 0, 1, '2026-08-26 17:23:06', '3.9', '2026-08-26 17:23:06', '3.1', 0),
(45, 'Heinz´ Kram', 'newkid9@web.de', '32132564213', 'Hein´z', 'Krams\'s', NULL, NULL, NULL, '$2y$12$XnRC69GNt.AM06f1Mcf3Q.hmBgXI0UDCo.DnnDrOqCQHG2yak41/O', 0, 0, 1, NULL, NULL, 0, 1, '2026-09-04 16:49:06', '4.0', '2026-09-04 16:49:06', '3.2', 1);

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
  `aaguid` char(36) DEFAULT NULL,
  `sign_count` bigint(20) UNSIGNED NOT NULL DEFAULT 0,
  `device_name` varchar(255) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `last_used_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

--
-- Daten für Tabelle `passkey`
--

INSERT INTO `passkey` (`pk_id`, `user_type`, `user_id`, `credential_id`, `public_key`, `aaguid`, `sign_count`, `device_name`, `created_at`, `last_used_at`) VALUES
(107, 'cust', 40, 'nDg4x5AT7xhIXbb1ZGC5qzRRvyciRZBD4OgdfcihW_E', '{\"publicKeyCredentialId\":\"nDg4x5AT7xhIXbb1ZGC5qzRRvyciRZBD4OgdfcihW_E\",\"type\":\"public-key\",\"transports\":[],\"attestationType\":\"none\",\"trustPath\":[],\"aaguid\":\"53414d53-554e-4700-0000-000000000000\",\"credentialPublicKey\":\"pQECAyYgASFYIBn7rbedfIMheYWNIUvhKiOdzKAHZcaAHx-4oCVqfywcIlgga3ore_2JCHAZGn8--c_-6k0PL0eKOLy86miSX5gtsAw\",\"userHandle\":\"WTNWemREbzBNQQ\",\"counter\":0,\"backupEligible\":true,\"backupStatus\":true,\"uvInitialized\":true}', '53414d53-554e-4700-0000-000000000000', 0, 'Android – Firefox', '2026-08-31 15:05:51', '2026-08-31 15:06:05'),
(108, 'cust', 40, '6MWRTmsckHVEeno3-Om9RQ', '{\"publicKeyCredentialId\":\"6MWRTmsckHVEeno3-Om9RQ\",\"type\":\"public-key\",\"transports\":[],\"attestationType\":\"none\",\"trustPath\":[],\"aaguid\":\"ea9b8d66-4d01-1d21-3ce4-b6b48cb575d4\",\"credentialPublicKey\":\"pQECAyYgASFYIPrvOhiXELZ6XM-EllY_2WTnW2TbvctkdqF40Ga7u_3uIlggi-H5yvFf2TjaVr_rGLvLjP7iDgb1Kg4Xpx-qc9BKNnM\",\"userHandle\":\"WTNWemREbzBNQQ\",\"counter\":0,\"backupEligible\":true,\"backupStatus\":true,\"uvInitialized\":true}', 'ea9b8d66-4d01-1d21-3ce4-b6b48cb575d4', 0, 'Android – Chrome', '2026-08-31 15:08:10', '2026-09-01 08:34:22'),
(110, 'mand', 40, 'zPoAk5mDh9MnDvGfIq-Psw', '{\"publicKeyCredentialId\":\"zPoAk5mDh9MnDvGfIq-Psw\",\"type\":\"public-key\",\"transports\":[],\"attestationType\":\"none\",\"trustPath\":[],\"aaguid\":\"ea9b8d66-4d01-1d21-3ce4-b6b48cb575d4\",\"credentialPublicKey\":\"pQECAyYgASFYIIeOs_hYIZBcZksr1pVKLec7w3-oi9w756pm4KGR-ShyIlggwRymG50XKgeqzOapQI_nFaTTOQNt-WaGheTVzVfa8Oo\",\"userHandle\":\"YldGdVpEbzBNQQ\",\"counter\":0,\"backupEligible\":true,\"backupStatus\":true,\"uvInitialized\":true}', 'ea9b8d66-4d01-1d21-3ce4-b6b48cb575d4', 0, 'Windows – Chrome', '2026-09-02 12:35:45', '2026-09-02 12:46:49');

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
(28, 'cust', 40, 'andr', '19c1933742a2b192c2cb9d799bb62c85a1379d3e71683c94e460d1d3c7945ad8', '2026-08-31 15:04:11'),
(40, 'mand', 28, 'ios', '15dad07476467aacfd9d81435dceb8aace2f406fe7cc26fd5b340ae0668b7ca4', '2026-09-04 12:43:11'),
(41, 'mand', 28, 'win', 'dc48245976c0b33c00f9f386a819478f4ddf7d4bcd676820e560abff8ad89ce0', '2026-09-04 12:46:47');

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
('ds_version', '4.0', '2026-08-30 15:02:39'),
('upload_version', '3.2', '2026-08-30 15:05:25');

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
(1, 'Martins System', 'frank.euring@mail.de', '+49 162 560 21 02', 'Martin', 'System', '-', '-', '-', '$2y$12$JEb8dkHeFfcoPiZXiAmbMeHsr0ZccO8GcoCS6fmqqTqW.f1AUwtkK', 1),
(7, 'bergfex', 'harburg_bergfex@alpenjodel.de', 'Yde3#4rfc0987', 'bergfex', 'bergfex', '', '', '', '$2y$12$V8n6qqSLI7IcnNlUGzNAnOXCEPfChV1d274sQaF2Ql8NBm6bUtUv6', 1),
(11, 'subuz', 'anderelbe@unterderbruecke.de', 'subuz', 'subuz', 'subuz', '', '', '', '$2y$12$cmu0uwXOmb/GQGoqklBiSOYZ7rU4lF9LsB704huCVNyd0gfTKMMRG', 0),
(12, 'Newnew', 'newkid9@web.de', '7tzji', 'Newnew', 'Newnewc', '', '', '', '$2y$12$UmlAM77aVS0wYRQH9m5KZ.yO3nPqyYJk6VnPgnyRjOfBt3k82ATIa', 0),
(18, 'foto5', 'foto5@keemail.me', 'foto5', 'foto5', 'foto5', '', '', '', '$2y$12$hnpbxD/qyGHP6Y2520zgs.y3KciRuocs2ThwOSH7LaSYTZ2LFvEVG', 0);

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
  MODIFY `pcode_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=79;

--
-- AUTO_INCREMENT für Tabelle `cust_user`
--
ALTER TABLE `cust_user`
  MODIFY `cust_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=62;

--
-- AUTO_INCREMENT für Tabelle `invite`
--
ALTER TABLE `invite`
  MODIFY `inv_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=999924;

--
-- AUTO_INCREMENT für Tabelle `mand_user`
--
ALTER TABLE `mand_user`
  MODIFY `mand_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=46;

--
-- AUTO_INCREMENT für Tabelle `passkey`
--
ALTER TABLE `passkey`
  MODIFY `pk_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=124;

--
-- AUTO_INCREMENT für Tabelle `passkey_dismissed`
--
ALTER TABLE `passkey_dismissed`
  MODIFY `pd_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=42;

--
-- AUTO_INCREMENT für Tabelle `syst_user`
--
ALTER TABLE `syst_user`
  MODIFY `syst_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
