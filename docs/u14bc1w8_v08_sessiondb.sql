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
-- Datenbank: `u14bc1w8_v08_sessiondb`
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
-- Tabellenstruktur für Tabelle `pw_list`
--

CREATE TABLE `pw_list` (
  `pwlist_id` bigint(20) UNSIGNED NOT NULL,
  `mand_id` bigint(20) NOT NULL,
  `pw1` varchar(255) NOT NULL,
  `pw2` varchar(255) NOT NULL,
  `pw3` varchar(255) NOT NULL,
  `pw4` varchar(255) NOT NULL,
  `pw5` varchar(255) NOT NULL,
  `pw6` varchar(255) NOT NULL,
  `valid_from` datetime NOT NULL,
  `valid_until` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

--
-- Daten für Tabelle `pw_list`
--

INSERT INTO `pw_list` (`pwlist_id`, `mand_id`, `pw1`, `pw2`, `pw3`, `pw4`, `pw5`, `pw6`, `valid_from`, `valid_until`) VALUES
(2, 4, 'eyJpdiI6IjUvcFd4M3g3aThqVnQ1MmZRTEFaVVE9PSIsInZhbHVlIjoiQ3hOeTZncG1qYlpPYVhkckVTKzVHSkFYZGc3cEVjeHhqdnhINHc2YmF5bz0iLCJtYWMiOiI3MWJhYjMzNTJiZWQxZGFlOWVlZDE4MDI0YWUyZjdiODkyM2I3M2Y5NmZjNWI0ZjdlNjJmYzczZTAwODE0YjRhIiwidGFnIjoiIn0=', 'eyJpdiI6InNVVi8vZ00zR28vRmRWNDE2STNLSnc9PSIsInZhbHVlIjoiUFBnMGJ6US81b05nZi91RzdMVE9icjcyNFNDL2dTcnhoSTNLU3ZrVlhvUT0iLCJtYWMiOiJhNzhjOTkzNTZkYTc3NWQ4Nzg2ZWRlZjdhNjQ0MDE2MTY3MTYzZWM5NjE3Y2IwMGM0MzRhYjFkMDJhNzA5MDY2IiwidGFnIjoiIn0=', 'eyJpdiI6ImZQbzlEdnVBMWxTZSthQVAyTjN6Smc9PSIsInZhbHVlIjoiaXF5WDQxV0tJTkNjcmRYR0tWcjRiM0JndENrdEM3OHNHcXVrYUpScEdKTT0iLCJtYWMiOiJiNWJmYWFmMWI5ZmQ5YzU2ODNhOWFiOGVhNjRkMDE1Njc4Y2NiMDU0NDYzMzZmMWNkYzlmNWI1MzMxOTc3ZDM1IiwidGFnIjoiIn0=', 'eyJpdiI6IjkvREdLcE9jV0F4dTVKVnloTW55dVE9PSIsInZhbHVlIjoiSTdYQWY1clhuZmRxRGd3Qm1wRHhNUT09IiwibWFjIjoiZDdmOWM0ZGE4YTEzNDAxMmZiZTdmYjc1ODA3MTFkYWMwZmRjZDlmYmFmNjg5ODdjYjA2ZDFlYzUwNDc5YjRlOSIsInRhZyI6IiJ9', 'eyJpdiI6IlYzUXFIeE5VY0pRYmZRU0FQVVg5anc9PSIsInZhbHVlIjoicTlRdlc2elhlVXc0Mk5HeEUvRlF5a0NTdzZvY1pMWjIwU3ZzVUNCayt3RT0iLCJtYWMiOiI4N2Y2MmE5MTE1MmU4MGNhNzRlMmM0NjM3MmNjY2IzZjllYTYzZjEzYzJkMDUyMzRlMTRlZWQ1MmIyZjNhMzUwIiwidGFnIjoiIn0=', 'eyJpdiI6Ikx3Z21QaldGZEppeHFXUjgrekJaNlE9PSIsInZhbHVlIjoiQUo1MmVBZWlxRjYvTEl4TXhEeGRCU09ra0hDRjBIWHNHb2Q2eC9aTndoVT0iLCJtYWMiOiI4OWRlYzEwNGFkYWRjMWEwZTJiM2NhMTg4OTc1ODkzYmEyMDM1ZGY0OWMyNDEyMGIyOWNiZTk5ZmY5Njk4NTg0IiwidGFnIjoiIn0=', '2026-05-30 00:00:00', '2026-07-30 00:00:00'),
(3, 5, 'eyJpdiI6IlI2bGd1WHR6Y3dhTzJhTWdaL2lyd0E9PSIsInZhbHVlIjoiNjJhbUVTOGlNdnlHWks1WGpkV2ljMm42RHZhWlBvRStXTHdQRDNadXBHbz0iLCJtYWMiOiJhNjYxZDBiZGQ1YTMwZmNkZTk3YTE5YjJjMTdiYjAwNzI0OGJjYmYzZDVlMDJjOTlmNWVmOTc2OGZjNDYyNzBkIiwidGFnIjoiIn0=', 'eyJpdiI6InZiRXhwbGsvZU1qeVl6UUJYNkZuNkE9PSIsInZhbHVlIjoiLzVPZktxSDV4V1FueGZYWnFtb3FVNW1xRjdaTHlEcCtldUlLT3lNeHpQWT0iLCJtYWMiOiJmYzExYjRhN2QyZWU3ODQ1MGVmMWZiYTEzYTA0NjFjYzZlZTk4ZTJiMTY0MzkzNjQyZmRlOGY3ZjQ0ZDVjNTFjIiwidGFnIjoiIn0=', 'eyJpdiI6IjhneTZmQ0hoTnE2WXhQT0FsRzdZaFE9PSIsInZhbHVlIjoiS3d0eWlPMUtYS3dhcU5pSGwvRVNiRlhYZjdVSFdJblB5ZHprNUV0L0xzaz0iLCJtYWMiOiJlZGM2MTIwZTI2ZDY1MzM3ZDU0OTJiYzk4ODE5ZTBlMzljMmNmZTljMmYwNjhhZjg2ZTBkNGVhYmU0ZmFhMzYzIiwidGFnIjoiIn0=', 'eyJpdiI6ImxZY01lblNLUHVKaXZXVTh0TUdsK2c9PSIsInZhbHVlIjoiYU1rT2txL2ovN0YzN3p2b01ncHh0MC9ITVAvV1czVmtWMFhiOTl5dUpTZz0iLCJtYWMiOiI5OWYwMjBjMWE4NjU0YWEwN2NmMWE0MjkxNDBlNjYwMjRmYmU3ODBmMDA2MDU0MzY0ZWQzZWRkMTQ5YTc1MWFlIiwidGFnIjoiIn0=', 'eyJpdiI6IkNocDNMZDZVSkR6RDRNVDdxTlZXd0E9PSIsInZhbHVlIjoiTFhkMEFldDhKTFdreG4rdWlpRHhic0pBREQ5czdzQWNPTldNQmNaenVyMD0iLCJtYWMiOiJmOGZjYmEyMmVmM2IwOWY5ZDFkYzI2MTZiODM3NTBlMDkyYTlmMTlkZDc0YjY4MDdkMmM3ZTQzZTkxYzU2NDM1IiwidGFnIjoiIn0=', 'eyJpdiI6IjRVRTlzUDkrUGorcXpqNEQvdDN6NUE9PSIsInZhbHVlIjoiYmt3elYzczJFYVJwTDJVY1VXc1k3QWJlaHlQdU1XYUxYNFhGY1JlaytLQT0iLCJtYWMiOiI0MWFjODVlODBmYzVhOWY4MDI5NmRiZDliMDBiNTc2YWEzNDVkNjI4OTYwYWRkZmUyZjQ2MGZiNWUzZjE5YmY0IiwidGFnIjoiIn0=', '2026-05-30 00:00:00', '2026-08-31 00:00:00'),
(4, 12, 'eyJpdiI6ImhyUjFsQjZMV3dZNmllcUFEa0VTT3c9PSIsInZhbHVlIjoicFBDWHhZN2t0bmMwNzdvTXJkekdSeUFLeG1jNFBEeEVuTGRDcEZubktkVT0iLCJtYWMiOiJmMGQ5NzIzOThhMjFmODZhYzk0YjI3YTkxOWQ0MDA1NWFlZjJmYjI2MGQ4NmI3MzIwZGIwZDg2ODQzY2ZkODZjIiwidGFnIjoiIn0=', 'eyJpdiI6IjVrVEVxd092N1EvSlNCaXFHdXc2N2c9PSIsInZhbHVlIjoiUUxDS2Fyc2NpWVM3Y3ZVQm5NMWJIVDNRWjFVeC9zbGlXOGJMbFdtNjUrcz0iLCJtYWMiOiJjNTNmMzZlZjVmMTAwNThiN2QxMGI4NzNlM2ZiNjllZDI2MWZiZGNiZWRmNTNiZDhmZDA4NTA4YmFiOTdjZTZhIiwidGFnIjoiIn0=', 'eyJpdiI6InQ4Y3FLV3hNRklDcWFhOTF5czRiMXc9PSIsInZhbHVlIjoiakQ3d0RUVFNKemo1QXp5MjZuKytoczRmelVrekFaNGVRQUFMQWVqditLMD0iLCJtYWMiOiJhNTczM2I2ZjI4ZjZkYjM2NzI5Y2MwM2I3MjZiNjUxNWE3OTMxMjgyZTljM2YwNTA1ZTZlMjc4YzI4NDFmOWI3IiwidGFnIjoiIn0=', 'eyJpdiI6IjBpZ2tDRlgwUW5HbEVEcDBFR1NKVVE9PSIsInZhbHVlIjoic2J0R2Q2WUllT3NDTmpzOTBIVFpEYy9rL0NhWDJMRENidmxOdENwelNJVT0iLCJtYWMiOiIyOGFmOGFkM2M5NTVkYjVlMzkxNDVhMTZmNDI3MTcyNDM4NTYyM2RjYzkwMGE5N2I1MjdjMDE4Y2VjMTJmNTIzIiwidGFnIjoiIn0=', 'eyJpdiI6IkpseW9sNFJHNEQ3cUcyaDhxWWZ0d3c9PSIsInZhbHVlIjoiVCswQmNTbUx1eGRHMytFUnZPMTczYzNLeDkxTHFkZGU4MTlnTjR2ZGpzWT0iLCJtYWMiOiIzYjgzYmJmMjdhYTA1YjU3MjE4NzcyM2VmNDczZjc0ZWJmNjI2MTY1MTgwMTZiNTAwNTZkNTkwMmMzZjlkY2MzIiwidGFnIjoiIn0=', 'eyJpdiI6IjNTNWZGRjhjN3lVZEV0VXJvdXpXbWc9PSIsInZhbHVlIjoiUlFjcmU4K1pZby9lcVdXY0V0eGpQQmYraDdaYVhrVU5IYnczcDQ0eXpKdz0iLCJtYWMiOiI4ZDhjNGM5MzVlZDkyYTI0NmI0YTk1ZDE0NDUzODA3YmIwNDYwMTg4YjhiM2FjYjk0Nzg5NWJmNzE1YzA5MjA2IiwidGFnIjoiIn0=', '2026-05-30 00:00:00', '2026-06-30 00:00:00'),
(5, 14, 'eyJpdiI6Im03ZzQ4cit2N3RiZFFoYUNCaER2dlE9PSIsInZhbHVlIjoiSFlDV04zdVJFeSt2L2N6dHFHK3NDbWVZVTZPT05PZ3NSU1hkUng5N3J0MD0iLCJtYWMiOiJkMDU1ZWUyYmRmZTNmYmFmMWZlODBmMTk2OWQyYTA3ZGM3NzI1YWE2MjkzNGIzZWQyYmE0NjhkODc5NDUwMWRiIiwidGFnIjoiIn0=', 'eyJpdiI6IlgrUjh6c3BjQ04yWHlicjllcCt6Vnc9PSIsInZhbHVlIjoiYU1PVXhIbzlDVzhyM2ExV2Z5MVY0cmovdEpndkt5dW1oUEFlaGI3UVhucz0iLCJtYWMiOiIxOTZjMmZiNjE2ZWJkZTA5ODQ3N2Q5ZWZhZTcyZWE2MDJmNjQ3YWZlMWUyMDNjNzlkZjQ0Y2FkOWFkMDQyZjAwIiwidGFnIjoiIn0=', 'eyJpdiI6ImN1U3pPWHE2S0habHBJcG56L092NWc9PSIsInZhbHVlIjoiSjdoTEtFSlIwTnVtajRFOTc5RS9CaVRBMm8zUjB3YmtvWGZkaExZTjZvST0iLCJtYWMiOiI3ODUxNDlmZjk4MjdmNWE2NmQxYzdlM2M4M2ExMjA0NjY0MDExZTEwYTUwMzU1OWM5MjgwZGFkODYwNzNlMGI2IiwidGFnIjoiIn0=', 'eyJpdiI6IjhOaFNoTEpyd0tQYVVKVEZwd1Ruenc9PSIsInZhbHVlIjoiRTBNZHRhb0Y1SHMwZUlYc1RsZ0lncDFteEVFOGpvSHNQQ0U1MEwxRWpNTT0iLCJtYWMiOiJlY2NkZDJlMTFkYjFkMWZmOTMyZGRiMDJmOTQ2NWVhZTQzOGNjMDRkOTBhYTM0MjQzMDc2OWUxOWYwOWY3ODI2IiwidGFnIjoiIn0=', 'eyJpdiI6IklUVFBtWU5RdUpPTDBZb2NJQTVRN1E9PSIsInZhbHVlIjoiVDdSWHFDZk1IWHp6bTJ4WFd5eEdjUnlic3licS9xUi9UcmR1R2JXUk43az0iLCJtYWMiOiIxY2QzZDJiYTZiZjlmOGYzYWJkMzgzODE5OTM1ZTUyYmQ4M2YwYzljZWU3Mjk5OGQzNmZjYzkxMWY4OGYzNjUzIiwidGFnIjoiIn0=', 'eyJpdiI6IkhpZFpIaklCWnFXTkNBTXFEYzB5eGc9PSIsInZhbHVlIjoidTI1SkJQci9NeDRyY3dXclpGNi8zcUNZSWtTSTZsZGRKV0J1WjlueTJzMD0iLCJtYWMiOiIxMTU4MTM2OGUzYWFiNGJkMmI5MWY1Yzk2ODk2MjQ2YWFkZGJkOWU2YzNiOTNlNDYxZDM0YjFlZDAzZTA1NDU1IiwidGFnIjoiIn0=', '2026-05-30 00:00:00', '2026-05-31 00:00:00'),
(8, 15, 'eyJpdiI6ImQzUVlhTEFvLzNYbjViVXQ3V2pDekE9PSIsInZhbHVlIjoiN3M1M3g5VkE2M2VIbjFOeE44NkdaQWEwNFlVTDB4blBteCt3M01yTGV3dz0iLCJtYWMiOiIxODcwZDRjYzY3NTk0ZGVlMzY1MDhiZWMwMDVhMTcyZjQ5YzNjMmFkNTg5MzgxNzNmZDlmMDc1NTY1N2JiMmY1IiwidGFnIjoiIn0=', 'eyJpdiI6Ikx3b3pCeXZobWZLdURhS25sT0hoNXc9PSIsInZhbHVlIjoicHdPVVpvWnZuK253eGFmQktJMHBVcUF0QnZDa0V4aG0rRk8yV3NIZTVmOD0iLCJtYWMiOiI3MGM0NjJkNDU0MDcxMzdmOWUyM2Q2YmZlNzczYTkyMTUyNGI0YTFiODFlMDI3MWUxOWY0MzBjZWVjMjcwNDVjIiwidGFnIjoiIn0=', 'eyJpdiI6Ii9zQjJ5MktsMTgvOXMwN1RxYXFyMEE9PSIsInZhbHVlIjoidDZpWHdaOVR2ZHB1M2N4MUlFdzdycjluUWcyNzlHRGdrN1I1SHFOYUZMUT0iLCJtYWMiOiJhODZhMjczMWMwNTE4ZGNhMzY0YmRiN2Q0NGU5OGQwMDU5NjRiOWMyYzBmYWVlM2IxYjUwNjk0MWFhZTM5ODY5IiwidGFnIjoiIn0=', 'eyJpdiI6ImtmSm1pcGt0Z0hXd0JWWXRQSkMyc3c9PSIsInZhbHVlIjoiQUw4RjNRR0ZiVmZjS2hyYjFPZXF5Vk8xRUJ1bkQ5N2NLS1dZRXdTRlJmMD0iLCJtYWMiOiI4ZjVkZWE3MTUyMzI4MjA5NGZjNjYwMjJiNGY0NTQ1MTNlZWUyYjAyZDUwZjdiZmM2YzA4M2YwMTU2MTQyMGEzIiwidGFnIjoiIn0=', 'eyJpdiI6IllKaXVUSGFIalJWbHh6dXRabTUvb3c9PSIsInZhbHVlIjoibHkzWUdoamFnbGlFNUtKaCtPYys1dGgyMmIwQklqdW1TMkRRZ0FSMnVzVT0iLCJtYWMiOiI1NjVmZDc4NWRiMWIzN2UyNTU5ZTIyMTFhZmY4ZGJlMmJhOTk4MWQ4YTMxZTAwMDBkYjUyNDQ5Y2FkMmNlMWI5IiwidGFnIjoiIn0=', 'eyJpdiI6IlBObWtYZWlpandaTk02MnFsVUdvSWc9PSIsInZhbHVlIjoidzVjSlFLbXpMQ05uQjdWQWpnVm02ZEIwWEZPR05hSndrWGxiTHowTEJZWT0iLCJtYWMiOiI3ZDUxNjVlOGFjY2Q2ODc2NmM3ZWQyZjhjZmJmMGIzZDg2M2JmMWYzNzM0ODNkM2QyMDI0ZjJkOTY1ZTU2Mzg5IiwidGFnIjoiIn0=', '2026-05-13 00:00:00', '2026-07-31 00:00:00'),
(9, 16, 'eyJpdiI6IkFYWGsrM05oQzYvOUF3V3lUcDZjWWc9PSIsInZhbHVlIjoiazNXMHVnUlRrNzRvNEp6bnloSzc2U042aVQ3MCtQUFRBNlJNNnFLemVtMD0iLCJtYWMiOiI1OTdmOGYwYjVjMjUwNDAxZjA0ZTczY2VjODA5NjY2ZGU5MzM1MWFmOGIyMjdlN2I1NjVhNzUwMWM4MTkyOWExIiwidGFnIjoiIn0=', 'eyJpdiI6Im5uMFp0Z0xKd1lGdWRaT016a3hWNmc9PSIsInZhbHVlIjoiUnZxMTNXVVEzWGpjeFR3SW52TkVwWFl5RlR3eEV3OVgwV2p5MU9GT29LQT0iLCJtYWMiOiI5MzJhNWE0Y2E1ZmNiZDkzMGU3YmI3NTU0ODE5ZDBhMjdhNDU3OGNiNTczYWM3ZjBmNzQ4NTIyMDQxZGMxZDNiIiwidGFnIjoiIn0=', 'eyJpdiI6IlpBTkF4SlUxZVFxREtUanMvYXk2QXc9PSIsInZhbHVlIjoiaE5IMjFDU2VRaGt6Uzk2MVZvUVVJWllSdnpYaWxNRW5kMVorTDdtVHJVND0iLCJtYWMiOiI4MWU2MWQ3ZGNlZjE0MjBjNjFmYTgwMjM1NTg2MjI4Yjg3MWE3MjBkMDBhYWNkMWMyODFiMjJkMWE1ZjJhYmM3IiwidGFnIjoiIn0=', 'eyJpdiI6InRmN0kya3JYMUJJMnpEaVdqZ2t1YXc9PSIsInZhbHVlIjoicy9FZVpyVHc0bjRDU2U4MDNwZHg4OG5LYzlEWGxodytBTDN1dzdRT1ZLRT0iLCJtYWMiOiI2M2I1MDk2MTQzMDJkMzc0NDYzN2E0Y2I0NWQxNTNiMjQzOGMzNWJjZDExZWE1ODM3M2VkYmVkYmMxOTExOWFkIiwidGFnIjoiIn0=', 'eyJpdiI6IlR0M0d1YTI1Z0xQd3B6RXRyQ3EzMHc9PSIsInZhbHVlIjoiUVBMWDhRbGU3bEI2WDR1U1lNVzgrOUFrNXovTGV1N2ptcW9heHpVRFdnMD0iLCJtYWMiOiI1ZDNiMTk5NDI0YmQ5YzZjZDY5ZDliMDczN2FmYTRiMGExNTU2ZTRjMjllNzcxZDNjNWY4ZjM5NTc3OTlhYWE2IiwidGFnIjoiIn0=', 'eyJpdiI6Inc5dHl6SU04elUyZDA5MFNXRTNTVUE9PSIsInZhbHVlIjoiNnp4TklRWmtDMncxZ2ZrZWJiZEtmZFROZ0xHTlM3YVpZMmIwN254ODB5QT0iLCJtYWMiOiI3NjEzNzJmY2UyMDEyMzhkNjAwOTQ4MzJkMGE0ZDdiNmQ2MmRhMWViMmM1NDAzMzgzMDYxMzNmYWQ1M2Q5OTRkIiwidGFnIjoiIn0=', '2026-06-18 00:00:00', '2026-10-31 00:00:00'),
(10, 26, 'eyJpdiI6Ijdja0JTZE94V2ZLUnJOUXZueUhHeFE9PSIsInZhbHVlIjoiVllBdldaeGVtWTZnSm9aYnc4YUtvZz09IiwibWFjIjoiNWYzMjc4OTE0ZDVjNzk5MTRhNzQxMzQ0Mzk5YzU3NmM3NmFmOThhZWQwY2IxMTVlMWZhYjFmMjgzMjc2MGQ2ZiIsInRhZyI6IiJ9', 'eyJpdiI6InRHV09JK2JrSVBJbEl0MEhDanFibmc9PSIsInZhbHVlIjoidXQxY01rdDlUdnQwc2NVT3BCb0hJdz09IiwibWFjIjoiNTFjZTAwMjhlNDdhYTdkNzNjNWFmMTI4MThkZTA2NzI5MzgwMzljNGUwZWU3OGQ1MTdhZWUwYzVhMmNjZGI0YSIsInRhZyI6IiJ9', 'eyJpdiI6Im4xcGoyNXZwdmFIbUxSQVVHVGttcmc9PSIsInZhbHVlIjoiNEp1M3lDN21takl6Z1FkeFBJZGdXdz09IiwibWFjIjoiY2Y4NmQ2ZmUwMGRlMTQ0MzA4YzdhZTZlZjI2OWJjZDAzZTYwODJkZTE3OWYwOWQ5OGEzYzM0NDQ4MWVkNDk0ZiIsInRhZyI6IiJ9', 'eyJpdiI6IkxpdVBQS2hWWHVXbVZrN21oakIxWVE9PSIsInZhbHVlIjoic0U3bzFzN2tvREtBUHpYc0RrZHY1Zz09IiwibWFjIjoiNWNhZGVmNGRjN2NhOTk0ZGNhOGU1N2Y5NGMzZTRjNThkY2MzOTllYmM4NGMxNDE0YWRhNDQ1ODQ2NWEzMWViMyIsInRhZyI6IiJ9', 'eyJpdiI6IkdXM1FjK2dUM2l0ZVdEYXJSY2xEUnc9PSIsInZhbHVlIjoiR1c1bTk0SnBwMTdKTnZtS21yNXExM1RtNUNydm8vVzcxd00zT0Z5SVc1cz0iLCJtYWMiOiIyMzk3NWM0ZjZkODRiYmM5MjZjNTAyZWUxMjE5NGMyMGMxY2Y3ODVhMjdlNzFiNTI3MzhjZjBkYWVmMTg4MzI3IiwidGFnIjoiIn0=', 'eyJpdiI6IjQzeXcwaU16dkJoNFQ0UnEydUhSRUE9PSIsInZhbHVlIjoiZXlwc2RtMFR6Y2R5V1pFVGU1S1IwUW16c253MjRLQkViMmgzbkFlRnZWST0iLCJtYWMiOiI0N2EyNTcwZGNmMGU1ZDNkOWI2ZTJjZjk2MWI1ODIzOTQzNTRkZTQ4N2Q5YWM4Y2YzYzA1YWI0ZTVhYTZlMGNkIiwidGFnIjoiIn0=', '2026-06-23 00:00:00', '2026-07-07 00:00:00'),
(11, 28, 'eyJpdiI6IldiZFUxbWU0M3h2REdqWEQxU0FaV1E9PSIsInZhbHVlIjoiYmZHV0QvcnU3TVB6enRycWpBd3Z2Z2RTTVRVRWFKU28wVVhBY0ZGZDNJND0iLCJtYWMiOiJlOTg2NmFkZDBiMmM2ZmJiOGY3YmZhNDBlY2RiNTQyYjY0OTk1NTc3YjZmMGQzMzY4NThjZTIwODFlYTFhOWYyIiwidGFnIjoiIn0=', 'eyJpdiI6InQySFpjckNkQy9YU0lwVmpBZGF3Q2c9PSIsInZhbHVlIjoiRTYrRnNHT2hNRDRjdktkSzdSN0p3VU4wRmhrMXZHT0d4d3kwN3gxRmQ1bz0iLCJtYWMiOiIxYmUzOWNkNWQxNWVmMDk5ZjIwMjEwODZkMTk4NGM4MWQyMjk2ZmUxMjBiMzA4MDA1MTBhNmViZTUxNGE3YjU1IiwidGFnIjoiIn0=', 'eyJpdiI6ImIzRy9hajdrY3dnYUx6YTFZQmlJYXc9PSIsInZhbHVlIjoiakhnNXFiMzhOTWhNYm42VXpQL3g1UnlWckR0WDl5WnVpeDVadmNFTDFqMD0iLCJtYWMiOiIwY2E4ZmVjMWIyZTYyMmM3MWM2YzFiOTE4OTRkMTg5MDA2MmRiZDkxM2RhMWI4MjMxZDQzNjM3ZTBlMGNmZGJjIiwidGFnIjoiIn0=', 'eyJpdiI6Im5CbTU1QmpzYkdpcC9XN2R5QWF4eGc9PSIsInZhbHVlIjoiNXkwc3BoL1ZydVVxbTVqbXVWQTAzVDNtYWNDNjNXK2p0bGVKQmxrM3VLYz0iLCJtYWMiOiJmNDNjMGE4MGM3ZTNiNGJmNmE1ZGNjM2ZkZDRhNjZkM2IxNjM3MDQxMTM0ZTUxMDIxMThkYWY0MWExZTFmZmRhIiwidGFnIjoiIn0=', 'eyJpdiI6IlRaRWxabU1tQzZKT242bnVvMS8wN0E9PSIsInZhbHVlIjoiaVZBUjRvOUZhY2tabEdJVEYvMVd2bkl1eGc5dGwybEo0UjBTU01tTXVmaz0iLCJtYWMiOiIwNTZmNGY0ZWUzY2M4ZDA5NzAyZmJmMzM3NjllZmE1YmFkYzE4YTA5ODk4MjZjMGU3ZjVhNTk4Y2NlZmE4OTM5IiwidGFnIjoiIn0=', 'eyJpdiI6IjVTc1RoMWlTbjRIYWQ5cFpwUmFHREE9PSIsInZhbHVlIjoiZmo5L3RyR3hvUzRIK3VNeFZZRU11MFljRms5WGhwdVRPdjgzVThQNTFUYz0iLCJtYWMiOiJmYjdhZmNhOTE2NTlkNWU0ZDY4MWRkMzJjYzIyOTMyMDAzODM5NWUxNTRjNWU1MjBlNzUyZDEwOTUyNmUyMDk0IiwidGFnIjoiIn0=', '2026-07-17 00:00:00', '2026-12-31 00:00:00');

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `session`
--

CREATE TABLE `session` (
  `sess_id` bigint(20) UNSIGNED NOT NULL,
  `sess_token` varchar(128) NOT NULL,
  `payload` longtext NOT NULL,
  `user_type` enum('anon','cust','mand','syst') NOT NULL,
  `syst_id` bigint(20) UNSIGNED DEFAULT NULL,
  `mand_id` bigint(20) UNSIGNED DEFAULT NULL COMMENT 'Nur für zuordsnung Mand-Admin-content, bleibt bei Cust-Uugriffen\r\nunbenutzt',
  `cust_id` bigint(20) UNSIGNED DEFAULT NULL,
  `cust_passcode` tinyint(3) UNSIGNED NOT NULL DEFAULT 0,
  `ip_hash` varchar(64) NOT NULL,
  `ua_hash` varchar(64) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `last_activity` datetime NOT NULL DEFAULT current_timestamp(),
  `expires_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

--
-- Daten für Tabelle `session`
--

INSERT INTO `session` (`sess_id`, `sess_token`, `payload`, `user_type`, `syst_id`, `mand_id`, `cust_id`, `cust_passcode`, `ip_hash`, `ua_hash`, `created_at`, `last_activity`, `expires_at`) VALUES
(3639, 'ewxFcjJACIHyAkgGGpZrhHbxonTZy1od2mwQjtDR', '{\"_token\":\"GS0roolNE0gUcDJeuI6ITE4tj7b6ro5ysZPh4TgE\",\"_previous\":{\"url\":\"https:\\/\\/fotos.martinwagner.de\\/system\\/mandanten\",\"route\":\"system.mandanten.index\"},\"_flash\":{\"old\":[],\"new\":[]},\"_ip_hash\":\"a0f2cb3652a00ee7730c263cd25d73da9b2af3697752567f9436d5e081499a24\",\"_ua_hash\":\"0b5bf65a3c87672ee6bd66722063c699806278ae67c8a7fac1ccf6083917e6cf\",\"_last_activity\":1788540304,\"_user_type\":\"syst\",\"_syst_id\":7,\"_is_primary\":true}', 'syst', 7, NULL, NULL, 0, 'a0f2cb3652a00ee7730c263cd25d73da9b2af3697752567f9436d5e081499a24', '0b5bf65a3c87672ee6bd66722063c699806278ae67c8a7fac1ccf6083917e6cf', '2026-09-04 16:44:35', '2026-09-04 16:45:04', '2026-09-04 18:45:04'),
(3643, '8rhjjJuLPLyjGS9pjOdoO9kd4BzdqBd70lFvLuhk', '{\"_token\":\"lXiSlueJVt91MghA5DrcfwJY400eHWtnEqrTk93O\",\"_previous\":{\"url\":\"https:\\/\\/fotos.martinwagner.de\\/system\\/mandanten\\/41\",\"route\":\"system.mandanten.show\"},\"_flash\":{\"old\":[],\"new\":[]},\"_ip_hash\":\"18c2acf3514260c2e840410b77f0bf116e38057e8212fa1151bc87fe188cfa14\",\"_ua_hash\":\"2e91c7de4f0f00b7f98eb6f1f5fd9ce72726e7b922b4415dbc20978a36fb132c\",\"_last_activity\":1788545656,\"_user_type\":\"syst\",\"_syst_id\":1,\"_is_primary\":true}', 'syst', 1, NULL, NULL, 0, '18c2acf3514260c2e840410b77f0bf116e38057e8212fa1151bc87fe188cfa14', '2e91c7de4f0f00b7f98eb6f1f5fd9ce72726e7b922b4415dbc20978a36fb132c', '2026-09-04 18:06:26', '2026-09-04 18:14:16', '2026-09-04 20:14:16');

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `share_link`
--

CREATE TABLE `share_link` (
  `sl_id` int(10) UNSIGNED NOT NULL,
  `code` varchar(10) NOT NULL,
  `mand_id` bigint(20) UNSIGNED NOT NULL,
  `sec_level` tinyint(3) UNSIGNED NOT NULL,
  `created_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Daten für Tabelle `share_link`
--

INSERT INTO `share_link` (`sl_id`, `code`, `mand_id`, `sec_level`, `created_at`) VALUES
(1, 'GwwSZEP', 28, 1, '2026-08-01 11:38:34'),
(2, 'SB3Omt4', 28, 2, '2026-08-01 11:38:34'),
(5, '3XdG9I4', 28, 5, '2026-08-01 11:38:34'),
(6, 'KuZrksE', 28, 6, '2026-08-01 11:38:34'),
(7, 'sN2dp2w', 28, 4, '2026-08-01 12:05:34'),
(14, '1lOgbZI', 28, 3, '2026-08-01 12:53:31');

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `trusted_device`
--

CREATE TABLE `trusted_device` (
  `td_id` bigint(20) UNSIGNED NOT NULL,
  `user_type` enum('mand','cust') NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `token_hash` varchar(64) NOT NULL,
  `ua_hash` varchar(64) NOT NULL,
  `device_label` varchar(255) DEFAULT NULL,
  `last_used_at` datetime DEFAULT NULL,
  `expires_at` datetime NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `twofa_code`
--

CREATE TABLE `twofa_code` (
  `tfa_id` bigint(20) UNSIGNED NOT NULL,
  `user_type` enum('syst','mand','cust') NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `tfa_purpose` enum('login','pw_change','critical') NOT NULL,
  `tfa_code_hash` varchar(255) NOT NULL,
  `tfa_expires_at` datetime NOT NULL,
  `tfa_used` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

--
-- Daten für Tabelle `twofa_code`
--

INSERT INTO `twofa_code` (`tfa_id`, `user_type`, `user_id`, `tfa_purpose`, `tfa_code_hash`, `tfa_expires_at`, `tfa_used`, `created_at`) VALUES
(999965, 'mand', 44, 'login', '$2y$12$XK6j/t3EmN8Z3INdIFwDUOLZdsO5lkXo0VAd2bDhc1/UHczUGog7C', '2026-09-04 16:53:03', 1, '2026-09-04 16:43:03'),
(999966, 'syst', 7, 'login', '$2y$12$CO3nPAi.Ob.iBZ.0cUDavOpaF0BdOms/Xtp8/OpkkoRENjoB70aKO', '2026-09-04 16:54:04', 1, '2026-09-04 16:44:04'),
(999967, 'syst', 11, 'login', '$2y$12$akwsPmAdNBMeSSLBogZBr.utvUn0esDdQoaOL8ihCatn6oXHs3X7O', '2026-09-04 16:56:50', 1, '2026-09-04 16:46:50'),
(999968, 'syst', 1, 'login', '$2y$12$cSXNFYB2ky8QLk/wTYfg8eQBT6wQve6bZwom2z4O7uwgO/QqZ0KYK', '2026-09-04 18:16:02', 1, '2026-09-04 18:06:02');

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
-- Indizes für die Tabelle `pw_list`
--
ALTER TABLE `pw_list`
  ADD PRIMARY KEY (`pwlist_id`),
  ADD KEY `pw_list_mand_id_index` (`mand_id`);

--
-- Indizes für die Tabelle `session`
--
ALTER TABLE `session`
  ADD PRIMARY KEY (`sess_id`),
  ADD UNIQUE KEY `session_sess_token_unique` (`sess_token`),
  ADD KEY `session_expires_at_index` (`expires_at`);

--
-- Indizes für die Tabelle `share_link`
--
ALTER TABLE `share_link`
  ADD PRIMARY KEY (`sl_id`),
  ADD UNIQUE KEY `share_link_code_unique` (`code`),
  ADD UNIQUE KEY `share_link_mand_level_unique` (`mand_id`,`sec_level`);

--
-- Indizes für die Tabelle `trusted_device`
--
ALTER TABLE `trusted_device`
  ADD PRIMARY KEY (`td_id`),
  ADD UNIQUE KEY `trusted_device_token_unique` (`token_hash`),
  ADD KEY `trusted_device_user_idx` (`user_type`,`user_id`),
  ADD KEY `trusted_device_expires_idx` (`expires_at`);

--
-- Indizes für die Tabelle `twofa_code`
--
ALTER TABLE `twofa_code`
  ADD PRIMARY KEY (`tfa_id`),
  ADD KEY `twofa_code_user_idx` (`user_type`,`user_id`),
  ADD KEY `twofa_code_expires_idx` (`tfa_expires_at`);

--
-- AUTO_INCREMENT für exportierte Tabellen
--

--
-- AUTO_INCREMENT für Tabelle `cust_invite`
--
ALTER TABLE `cust_invite`
  MODIFY `invite_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=999921;

--
-- AUTO_INCREMENT für Tabelle `pw_list`
--
ALTER TABLE `pw_list`
  MODIFY `pwlist_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT für Tabelle `session`
--
ALTER TABLE `session`
  MODIFY `sess_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3644;

--
-- AUTO_INCREMENT für Tabelle `share_link`
--
ALTER TABLE `share_link`
  MODIFY `sl_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT für Tabelle `trusted_device`
--
ALTER TABLE `trusted_device`
  MODIFY `td_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=999940;

--
-- AUTO_INCREMENT für Tabelle `twofa_code`
--
ALTER TABLE `twofa_code`
  MODIFY `tfa_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=999969;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
