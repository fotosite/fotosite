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
(53, 35, 39, '1', 'foto3_S#3', 1, '2026-07-19', 0),
(54, 35, 40, '4', 'foto7_S#4', 1, '2026-07-19', 0),
(55, 35, 41, '2', 'foto8_S#2', 1, '2026-07-19', 0),
(56, 36, 42, '4', 'foto5_S#4', 1, '2026-07-19', 0),
(58, 36, 40, '2', 'foto7_S#2', 1, '2026-07-19', 0),
(59, 36, 41, '3', 'foto8_S#3', 1, '2026-07-19', 0),
(60, 35, 44, '4', 'foto1', 1, '2026-07-20', 0),
(64, 28, 48, '2', 'newkid9@web.de', 1, '2026-07-30', 0),
(65, 28, 49, '2', 'bfex', 1, '2026-08-26', 0),
(66, 36, 50, '2', 'F6-Alias1', 1, '2026-08-26', 0),
(68, 41, 50, '2', 'Foto Sechs', 1, '2026-08-26', 0),
(69, 28, 52, '2', 'hntr2@mail.de', 1, '2026-08-26', 0);

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
(39, 'Foto3', 'foto3@keemail.me', '64645464654', 'Foto3', 'Foto3', NULL, 'dfsafa', 'xxxx', '$2y$12$Yy/ViJ88X.UQu8N9n4b0nujoAiZ5qbhfnmB52zWBVMu1poXLznWsu', 0, '2026-07-22 14:38:14', '3.9', NULL, NULL, 0),
(40, 'foto7', 'foto7@keemail.me', NULL, 'vfoto7', 'ffff', 'straße', 'stadt', NULL, '$2y$12$h144UB/vc.X5VSxThJV/x.mQM9aa3rZSRxQq9BWQ438fjPWvw3q3m', 0, '2026-07-30 11:55:11', '3.9', NULL, NULL, 0),
(41, 'foto8', 'foto8@keemail.me', NULL, 'foto8', 'foto8', 'foto8', 'foto8', NULL, '$2y$12$2QUn6nIYiZxT40SsU0AFAuN0fCs1IxNx5k1CasRoeNk7IfnKSkHtq', 0, '2026-07-29 18:31:31', '3.9', NULL, NULL, 1),
(42, 'foto5', 'foto5@keemail.me', NULL, 'foto5', 'foto5', NULL, NULL, NULL, '$2y$12$KSsTlr3runGpYhPZ.6E8P.nkide7eSksqBCHGRsOQinm50PhNLd0S', 0, '2026-08-26 14:44:08', '3.9', NULL, NULL, 0),
(44, 'ftot1_cust', 'foto1@keemail.me', NULL, 'ftot1_cust', 'ftot1_cust', 'ftot1_cust', 'ftot1_cust', NULL, '$2y$12$JVQP5vegubhSE1dRePHcfuW7GgzQOAUtkU/4/gAyuHcffEAtVsuOy', 0, '2026-07-20 12:21:45', '3.9', NULL, NULL, 0),
(48, 'newkid9@web.de', 'newkid9@web.de', NULL, 'newkid9@web.de', 'newkid9@web.de', 'newkid9@web.de', 'newkid9@web.de', NULL, '$2y$12$emuoZaDLgMd9xh1ApfHWWOkzgjZVrBTV0cMkHM6kS0c4CMfZgY/g6', 0, '2026-07-30 06:01:54', '3.9', NULL, NULL, 0),
(49, 'bfex', 'harburg_bergfex@alpenjodel.de', NULL, 'bb', 'Fex', 'Paul-Sorge-Str.', 'Hamburg', NULL, '$2y$12$aXHX0.QsPoWpgv14Bf3GbO8h3LYabg/rwc52rQRciJqwoAwXCYMEq', 0, '2026-08-26 10:30:55', '3.9', NULL, NULL, 0),
(50, 'Foto6', 'foto6@keemail.me', NULL, 'FF', 'Sechs', 'fdagaad', 'dfgaag', NULL, '$2y$12$sXHRhkOP/tz1Wi9yQrO6Iul35ivWLIJUyYLzQ5QHiyv7lJ6.HXTDy', 0, '2026-08-26 15:07:29', '3.9', NULL, NULL, 0),
(52, 'hntr2@mail.de', 'hntr2@mail.de', '13213121', 'hntr', 'Zwei', NULL, NULL, 'jkljkjlkj', '$2y$12$xYkyBKWkkKiII5L3erhiI.mX8Fm0CnDga4p0GRWAbiWLwc2ipjjgi', 0, '2026-08-26 17:19:23', '3.9', NULL, NULL, 0);

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
(28, 'Newb', 'newkid9@web.de', '132135', 'Newb', 'Newb', 'Newb', 'Newb', NULL, '$2y$12$YG3oCr6G6l4N9l0pLtrImOfbkBa2brZAmWGkMfPrnMLH6hsixb/iu', 0, 0, 1, NULL, 4, 0, '2026-07-10 12:31:18', '3.9', '2026-07-10 12:19:18', '3.1', 0),
(35, 'foto1', 'foto1@keemail.me', NULL, 'foto1', 'foto1', 'foto1', 'foto1', NULL, '$2y$12$GRZtNVVvOTnchwx6OLhfxeWdYGKIOwYIQrnz/87H6AjPIxBA2jNk2', 0, 0, 1, NULL, 3, 0, '2026-07-19 15:26:13', '3.9', '2026-07-19 15:26:16', '3.1', 0),
(36, 'foto4', 'foto4@keemail.me', NULL, 'foto4', 'foto4', 'foto4', 'foto4', NULL, '$2y$12$wjHmz6p/XvediNWy418OeeDjBTz7FhX9qTmW4wuphjNc5BGyQVQGi', 0, 0, 1, NULL, 0, 1, '2026-07-19 15:35:55', '3.9', '2026-07-19 15:35:57', '3.1', 0),
(38, 'schanzer3@web.de', 'schanzer3@web.de', '13231235564', 'mailto:schanzer3@web.de', 'mailto:schanzer3@web.de', NULL, NULL, NULL, '$2y$12$.8gr1z85IfYoELNK44pBQeOIKiRey4BXtZDtAwI0VjdNFVUTtDfw2', 0, 0, 1, NULL, 0, 0, '2026-07-30 06:27:59', '3.9', '2026-07-30 06:28:01', '3.1', 0),
(39, 'FotoZwei', 'foto2@keemail.me', NULL, 'foto', 'Zwei', 'Hamburg', 'Hamburg', NULL, '$2y$12$szA0sYH.Cyrpbm5nMEAnr.45pyU0IFqrL2Lhl4Jt2zPvvCZbav.BC', 0, 0, 1, NULL, 0, 1, '2026-08-26 10:39:40', '3.9', '2026-08-26 10:39:43', '3.1', 0),
(40, 'HeinPee', 'cristalblue@mail.de', NULL, 'Heiner', 'Petersen', 'jjjj', 'jjjj', NULL, '$2y$12$ER9.SwY1pSdI..mqZoStvu3BIKdzZcyY6wNuQeB.kz8YmPJiVWUCS', 0, 0, 1, NULL, 0, 1, '2026-08-26 11:04:36', '3.9', '2026-08-26 11:04:40', '3.1', 0),
(41, 'Hunter4', 'hntr4@mail.de', '1474525', 'Hunt', 'Ervier', NULL, NULL, NULL, '$2y$12$kaWHfmFks7ze9ZuzolEEDeN5Ey9G8LR.KxdG/ww30uxxJ1oohMABu', 0, 0, 1, NULL, 0, 1, '2026-08-26 15:00:43', '3.9', '2026-08-26 15:00:45', '3.1', 0),
(42, 'Don Key', 'donkey-shot@web.de', '165494651', 'donkey', 'shot', NULL, NULL, NULL, '$2y$12$jbS3RK5/TqUFPQjCZrLjxuLkk2Us2F3FnxYBSIYzL8VR1LSHi2dKG', 0, 0, 1, NULL, 0, 1, '2026-08-26 17:23:06', '3.9', '2026-08-26 17:23:06', '3.1', 0);

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

--
-- Daten für Tabelle `passkey`
--

INSERT INTO `passkey` (`pk_id`, `user_type`, `user_id`, `credential_id`, `public_key`, `sign_count`, `device_name`, `created_at`, `last_used_at`) VALUES
(76, 'cust', 39, 'OjJ0hoGoBraS9w9-Qy8UmCJMebsQ6jKk2oJK1XCHqMU', '{\"publicKeyCredentialId\":\"OjJ0hoGoBraS9w9-Qy8UmCJMebsQ6jKk2oJK1XCHqMU\",\"type\":\"public-key\",\"transports\":[],\"attestationType\":\"none\",\"trustPath\":[],\"aaguid\":\"00000000-0000-0000-0000-000000000000\",\"credentialPublicKey\":\"pAEDAzkBACBZAQDP1PdqtGer9KTo_Q7nIs7EnfDHUoomw7F-kkp_hPyMigmxbRU8CfSveUXP_ZA0wYYMn0Sc-1XDl92i7PdPyea2zoUIspc9s9KCrh70GDxJFgSrv2PalgClzVuRioasGw_ahUpJWvFWIhlYjtvYDoVua16m4xM1vrMdByaM3oYkw6mSIfRqtuNTVOjRyFeqikPTkKurCqzYK4_xaaz65F6we9HbOPdYFlT6JSfUTcOSwWJYgOS6d1vmP_c9qsiVrsUXLpuX4KrDHNfNDIy7zQMF0booW4SvH8-eB1BjO8-hQnd-K2rfmjOJ2NfUNb7JcJLjZyt3qasOFXhfGD6gcg6xIUMBAAE\",\"userHandle\":\"WTNWemREb3pPUQ\",\"counter\":0,\"backupEligible\":false,\"backupStatus\":false,\"uvInitialized\":true}', 5, 'W-C-oA_cust', '2026-08-04 13:47:50', '2026-08-04 14:30:23'),
(77, 'mand', 35, 'Zw3iJPVUMNEk955k2gGC0e30pNzVtCaDsbwkD8ZvN1c', '{\"publicKeyCredentialId\":\"Zw3iJPVUMNEk955k2gGC0e30pNzVtCaDsbwkD8ZvN1c\",\"type\":\"public-key\",\"transports\":[],\"attestationType\":\"none\",\"trustPath\":[],\"aaguid\":\"00000000-0000-0000-0000-000000000000\",\"credentialPublicKey\":\"pAEDAzkBACBZAQC_LKo7Att46A2vXgFEMbcbm4lUChbnuuYoeCn6Kr2PgaK4jufqlNLO3eC16fj70l4kFTm9CKgtA1yYAlozikJXqKij334KTXiywQZhnIsj9nuXk32uBVo96QscUGCwQXqp6q-GfJz2KjjTNvEtpU0oh_-XuN67IxwQg9YdjOsthmJT1M6rPJNpdX8eaeMLSz8yU4O7fUrqMnEi7pjh3Vj2crnvla6dLiXeWKEG4vUkUN7TsvmSNJoXdR9QFgqTJ1dFwP4t0q3pklnwfPVEqVN9IJp1zGoLiIL7G2RBtRXzYqtdQw9WSs8OgXlTNw5cGsaRGuoavLbbXKTeCm-e_v21IUMBAAE\",\"userHandle\":\"YldGdVpEb3pOUQ\",\"counter\":0,\"backupEligible\":false,\"backupStatus\":false,\"uvInitialized\":true}', 6, 'W-C-oA-mand', '2026-08-04 13:52:12', '2026-08-04 14:23:25'),
(78, 'cust', 39, '9MCvORT2X5-Z0zfkObqs6Q', '{\"publicKeyCredentialId\":\"9MCvORT2X5-Z0zfkObqs6Q\",\"type\":\"public-key\",\"transports\":[],\"attestationType\":\"none\",\"trustPath\":[],\"aaguid\":\"ea9b8d66-4d01-1d21-3ce4-b6b48cb575d4\",\"credentialPublicKey\":\"pQECAyYgASFYIDjjajTkWvcFK1MTMyjcveupc5eOzI9JUVbxHC0Iw3FbIlggAQoe1ZmWdZrN6tPXLFNymn0g63tpDF3LMHmb9Bz63co\",\"userHandle\":\"WTNWemREb3pPUQ\",\"counter\":0,\"backupEligible\":true,\"backupStatus\":true,\"uvInitialized\":true}', 0, 'A.c.oa', '2026-08-04 13:58:38', '2026-08-04 14:04:21');

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
(22, 'cust', 39, 'win', '6235d36597d64fc0e0c3883911a8fb2f9451d0254ab3379d980f5a3426585d87', '2026-08-02 17:05:49');

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
('ds_version', '3.9', '2026-07-10 14:30:21'),
('upload_version', '3.1', '2026-07-10 14:18:19');

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
  MODIFY `pcode_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=70;

--
-- AUTO_INCREMENT für Tabelle `cust_user`
--
ALTER TABLE `cust_user`
  MODIFY `cust_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=53;

--
-- AUTO_INCREMENT für Tabelle `invite`
--
ALTER TABLE `invite`
  MODIFY `inv_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=999920;

--
-- AUTO_INCREMENT für Tabelle `mand_user`
--
ALTER TABLE `mand_user`
  MODIFY `mand_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=43;

--
-- AUTO_INCREMENT für Tabelle `passkey`
--
ALTER TABLE `passkey`
  MODIFY `pk_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=79;

--
-- AUTO_INCREMENT für Tabelle `passkey_dismissed`
--
ALTER TABLE `passkey_dismissed`
  MODIFY `pd_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT für Tabelle `syst_user`
--
ALTER TABLE `syst_user`
  MODIFY `syst_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
