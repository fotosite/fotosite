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

--
-- Daten für Tabelle `cust_invite`
--

INSERT INTO `cust_invite` (`invite_id`, `mand_id`, `cust_email`, `cust_alias`, `sec_level`, `token`, `created_at`, `expires_at`, `used`) VALUES
(10, 10, 'harburg_bergfex@alpenjodel.de', 'alpenjodel', 3, 'uHUtJGEejIFwS36DAX09C9xaAaTaGskubnPnzLeXRASrczClDgCTe6bIyrRNvMkE', '2026-05-30 18:05:14', '2026-06-01 18:05:14', 1),
(11, 10, 'hntr2@mail.de', 'hinnerk', 4, 'VOrylhKZbrU69WBAVBU35S9ExzlKRXKu1m7fmESHAnabg6J6facrB1HMyHzCqYs5', '2026-05-30 18:07:40', '2026-06-01 18:07:40', 1),
(12, 4, 'harburg_bergfex@alpenjodel.de', 'Anna', 5, 'mI7QbNXUP60M3KMrN6RjLBhxLqeJYZ5ksqISKb2GbFANRMRSMWg0Bgj440gtAaHO', '2026-05-30 18:10:45', '2026-06-01 18:10:45', 1),
(13, 4, 'ich-bin-wieder-da@bin-wieder-da.de', 'wie der da', 1, '6guTVGkk79vjdkRvLKcps1KFKlLH2RMYaIUhZl0XiT94okbyBxFA6h5evkwXErmv', '2026-05-30 18:17:52', '2026-06-01 18:17:52', 0),
(14, 10, 'hntr2@mail.de', 'testuser1', 6, 'xVMVR9myihLuGqoba5q7bsfUyZKqtCX4Z30nohuhMPQnPDmIfYJXAX7AXrw8XyY5', '2026-06-02 13:47:54', '2026-06-04 13:47:54', 1),
(15, 10, 'hntr2@mail.de', 'x', 6, 'V2AjZVoOsBEbLvexZR2nf08jtV0o5UViKAY6lRqJnI3VnlRllitsMKheDWxcJyQ7', '2026-06-02 13:50:33', '2026-06-04 13:50:33', 1),
(16, 10, 'hntr2@mail.de', 'a', 6, '94RDYMYQujzJWOhvmusuw7xtQOu5FQXeh2Us5SubOnqOCoqnysnIMTg7OSd0vvro', '2026-06-02 13:55:37', '2026-06-04 13:55:37', 1),
(17, 10, 'hntr2@mail.de', 'xx', 2, 'pGH4neSDNB19RwkeUovHPNlhVk0zSvNpYIbUFcj1c9scsMBoQiCH2vzQ3IwhehcK', '2026-06-02 14:19:19', '2026-06-04 14:19:19', 0),
(18, 10, 'hntr2@mail.de', 's', 3, 'YqmKAD2n15T7KCOyAHFoRNjYGfN64vxYMjTVG2aDXCSuXX6u3RVdiajuueKnAI8I', '2026-06-02 14:37:01', '2026-06-04 14:37:01', 1),
(19, 10, 'hntr2@mail.de', 'y', 4, 'LkcBTpsxXKNGjGzlt4pJ5WlAi6btqN2miCdf1FgETievCBxgMz7L29UhxJShd0w8', '2026-06-02 14:42:59', '2026-06-04 14:42:59', 0),
(20, 4, 'harburg_bergfex@alpenjodel.de', 'x', 1, 'YnZmCcgQlbsjmQPXRpjf10ONSEqLJs9v9QuYVjSO8fCS9wqoLGvCIpELk677D2Wc', '2026-06-02 14:55:23', '2026-06-04 14:55:23', 1),
(21, 10, 'hntr2@mail.de', 'sdf', 1, 'UORmMXs3fGaIc2ICe7TVcLaoLvBzRBY0tXhRAZuYHcBmncOFeLiD38TuvhyWhOpt', '2026-06-02 15:00:17', '2026-06-04 15:00:17', 1),
(22, 10, 'hntr2@mail.de', 'skhj', 1, 'NRvEgtAxxqIRDloa8aP8Oyz2Grx4oxGzvjdhMxXwqaQGFX2fWIZZMtyDPAHPMKs7', '2026-06-02 15:02:39', '2026-06-04 15:02:39', 1),
(26, 16, 'harburg_bergfex@alpenjodel.de', 'aqnna', 2, 'D8V23mIGg5lfrbp8U32lASJArHxseG4oJj6YTmh7wv9orFIqscyYh0XfkBIgr0MJ', '2026-06-02 15:36:11', '2026-06-04 15:36:11', 1),
(27, 16, 'hntr2@mail.de', 'hm', 3, 'yAqN6gbYMnMSjlYvLyhxwYcSYNELQjnFlIpkydq6QNr2qfQXGHQJGXWhVW6qCgWW', '2026-06-02 15:37:19', '2026-06-04 15:37:19', 1),
(30, 16, 'x519@quantentunnel.de', 'Gerd', 1, 'cm30QyDSNj1pkKYZDWh7MGaM0SebyiIeHmAAhhTXbDyeZzlK3L3Fy81j9iUi5ENA', '2026-06-10 08:30:12', '2026-06-12 08:30:12', 0),
(31, 16, 'x519@quantentunnel.de', 'Gerd', 1, 'bolJJz6fSr0h0DwZ1IifY1egxB3UaVH6SHrLsl5yg9o3nkCLxVeJ0807ccGNjTTD', '2026-06-10 08:34:39', '2026-06-12 08:34:39', 0),
(32, 16, 'x519@quantentunnel.de', 'Gerd', 1, '2nDMgP4u8bJJhr63gD6D0lIZFZEmw9RSV0Ra8hMP18zkpZAlOOUHeeNXMrjUs7vU', '2026-06-10 08:40:51', '2026-06-12 08:40:51', 0),
(33, 16, 'x519@vollbio.de', 'Vollbio', 3, '5e7Fn5fAzE7Z2R2KIjdsmtQ6vMugRdgpBAX5HYaxjRHEUDNHzMIhBJB46OC1Mzsp', '2026-06-13 09:15:56', '2026-06-15 09:15:56', 1),
(34, 16, 'x519@abwesend.de', 'Abwesend', 2, 'S4tqbEATH0mB9qnIZG29ObAX1SWQEO1YvMJz3crxV2nYFrhziT8Aig3sXMFsmPqI', '2026-06-13 09:36:53', '2026-06-15 09:36:53', 1),
(35, 16, 'subumaster@web.de', 'master', 5, 'NZbg9EOkPSSGIvb26dBBhqV6P3tFbvcHkPFLX4DSQc0PE8gfgvSCWd6M0J6f8Qx6', '2026-06-14 17:03:20', '2026-06-16 17:03:20', 0),
(36, 16, 'subumaster@web.de', 'master', 4, 'kAg8suB5qILQj8uvTvjvzVkqpVVQqeXMTIUu4BZwuYNS1OPC00BDsJMtnaOPwTmf', '2026-06-14 17:32:54', '2026-06-16 17:32:54', 0),
(37, 16, 'subumaster@web.de', 'suma', 4, 'Uhlgdd3SSJcivocqy5QiGvfnDHxMQydpkpZc9VODEjLkPuKFjqbNdQjs8M2ib8ic', '2026-06-14 17:43:15', '2026-06-16 17:43:15', 1),
(38, 16, 'hntr2@mail.de', 'Hunterzwei', 3, 'HMmTTQ7Z0Za5WDffnJn0C4t5nRy4alDNQ27E0c9oNsRq2JS54vbdN6fSVlbOTkXG', '2026-06-15 07:51:32', '2026-06-17 07:51:32', 1),
(39, 6, 'hntr2@mail.de', 'huhnter', 5, 'pVlcNMVZLNudELyfiQ18Qm6TmIeRX41mIDRsGqJzhjeZ9pg5J7ClZRep5Pr49wWK', '2026-06-15 07:56:13', '2026-06-17 07:56:13', 1),
(40, 16, 'subumember1@web.de', 'sume1', 3, 'Nv8Q7Eaqltxsc0pdtk9noJ56h2td3nsqtvDZ5wc4dUTJkdZs3EtvWBFALRr2yu6w', '2026-06-16 17:06:29', '2026-06-18 17:06:29', 0),
(41, 16, 'subumember1@web.de', 'sume1', 3, 'Ils2WFAD0BkLl53usfVsRHrGMRJDv2YG6pQkaBjbe6rBCJDomhrTTS6QmxP982lD', '2026-06-16 17:07:39', '2026-06-18 17:07:39', 0),
(42, 16, 'subumember1@web.de', 'member 1', 2, 'd0eiaqwhATJ8TOGtENhJeQ34JHbB3UwTSsPdtH1U3MAJjlaQPgMu0jUd102crHZi', '2026-06-17 09:11:07', '2026-06-19 09:11:07', 0),
(43, 16, 'subumember1@web.de', 'sume1', 2, 'qHGof79HSn28dCyj4cMfIcQdqLQ8mwPtHzIjK3Vxv34pNH72RBWinkVZLm5v5YgK', '2026-06-17 09:31:49', '2026-06-19 09:31:49', 1),
(44, 16, 'subumember2@web.de', 'subumember2@web.de', 2, '5PApLv0gh5cUOeMWQTjcVhtpHN8dNd1Uqt7xoSNjOvOW5u9rtMghtRUqpNZGzOxN', '2026-06-17 09:36:50', '2026-06-19 09:36:50', 0),
(45, 17, 'x519@volloeko.de', 'v', 2, 'ccAEpp3Yae8sMxziINFSI6puuc4IbjKQJtwXsq81sNNAtWexNljjxGGcIKfQYYdH', '2026-06-17 09:57:58', '2026-06-19 09:57:58', 0),
(46, 16, 'neandertal.man@web.de', 'n ea', 2, 'X14qntbR1IXVTW4F797kNuDeqWABTzabuNj40ujFH5aZnlS1XtFWz9gcB0rCAsUY', '2026-06-17 10:35:18', '2026-06-19 10:35:18', 0),
(47, 16, 'subumember2@web.de', 'suubuzwei', 2, 'D660CuyyJcwe39mJ1ZNAsQoSrLNH7xoYbj2Q2dw8b5UfGcRmPzcgwx0bcGt6wblg', '2026-06-18 17:46:16', '2026-06-20 17:46:16', 1),
(48, 16, 'subumember2@web.de', 'subusu2', 2, 'rC9vvjPrA8lSsyKL4w0uCli6n6rYq0dt3RQtFJVE63SsZiO3TGrdVt0oRzm4TuXK', '2026-06-18 18:02:07', '2026-06-20 18:02:07', 1),
(49, 20, 'harburg_bergfex@alpenjodel.de', 'Anna Hausmann', 2, 'BUtQerBclZzzEWYnDfEUf6NkGeTLIZfrWmLUDGdRYCMV2JhMvBn7baoh4tmG9skx', '2026-06-21 14:16:30', '2026-06-23 14:16:30', 1),
(51, 6, 'lugareno@web.de', 'Nachtbar', 3, 'bKBNBDlyosvQDvGPuRoDy44sHvMhedBxFBwII1fhLwNrw3lc69b1OWghrh9POYiK', '2026-06-21 17:46:33', '2026-06-23 17:46:33', 1),
(66, 30, 'newkid9@web.de', 'Nk', 3, 'byn2QUGSjZaiPrPHPuEd3IeEWhdBjtBMMwPJ0rgppsF81He4UYp171nfyKps8HH9', '2026-06-25 16:52:47', '2026-06-27 16:52:47', 1),
(68, 28, 'hntr2@mail.de', 'Hink', 5, 'MN7K7r9tWbmvWA7iIlDsfwU2KIU952AWZUtuznShJCABGgpMjlu9FocAamUhfUJo', '2026-06-27 05:26:41', '2026-06-29 05:26:41', 1),
(69, 28, 'hntr2@mail.de', 'Hint', 5, 'ran608F6Qm1Wth41cxIw6P3DXvQXU3Covmn08LatCcgUDIZnpGFS73hBLZ9alx7x', '2026-06-27 05:44:14', '2026-06-29 05:44:14', 1),
(70, 28, 'ich-bin-wieder-da@bin-wieder-da.de', 'Wdd', 5, 'ixjmo7WsQ3hpPK1AWfc8Z4PSNjdDLDU96CQ7u3pCdlknXGIlJHlSJx6IlCzVSWlA', '2026-06-27 05:46:01', '2026-06-29 05:46:01', 1);

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
(9, 16, 'eyJpdiI6IndkTVJoMnh0QnpwdHBNVUxjZGZKTVE9PSIsInZhbHVlIjoiNnMrRjNVdkdNL0xDSVQ3VnRxK3NkdFpXeEFYQ2pWMDQzSFk1UDNRY0JLOD0iLCJtYWMiOiI0ZjY2OTBmNzcwYzkwOGRjNGRhMTRiYzRmMjQzZDNiZWNhMGM0MTk1NzBjODZlZTYwOWIzNjJkZmNhN2JkMWRiIiwidGFnIjoiIn0=', 'eyJpdiI6Ii96bnJYd3JRRTEvdjJubEZMeWNRcFE9PSIsInZhbHVlIjoiWHZxYWVCNlAyYVpaZnFjZmE4L24yc1pFbklobnFOZ1h3UVJjejVZbGpVaz0iLCJtYWMiOiIwYzgyM2NiODM2NWFjMTdkNjU1Njc0ZmViZGY0YWQyZDM1M2QxZjYyOWI2YmJmNzUzMThjMDAxOWU5MWNjNmIyIiwidGFnIjoiIn0=', 'eyJpdiI6IktncDhLUnZLZ0hYdUVOTXhYUXYwUmc9PSIsInZhbHVlIjoiWFlLaU9kcmZmTWtCbEMrOUtFTkk0azR6bFNlYmRUV1RSc3IvbVJleEFHcz0iLCJtYWMiOiI1MmRhNjcxMDc4ZTY5NTE4ZDRlODFkZjIyNmMzNzAxYjU4NjZmMzA5NjMxZWM4Y2YyOGM3MjdkMGRlMmY5MzI0IiwidGFnIjoiIn0=', 'eyJpdiI6ImxIZHcwZVhGSUtSMVR1NlNmNUd1NHc9PSIsInZhbHVlIjoiOGVlSUN1YVJQUlJybWJZeHVJYUllaGFsRDRPOVN4MFp3bzU2UExHdEMzdz0iLCJtYWMiOiJjYTA0NzViZTY2ZTg3NzY0ZWMyYjNjODc1NGY3MDVmODJhODYxZGUzNjA2YWYwZDc2NTVlMTBkZDNkNTEyZmZmIiwidGFnIjoiIn0=', 'eyJpdiI6InFabGhKeWxpbGMzR3krQ2MvNS81K2c9PSIsInZhbHVlIjoiczZjWkxjQmpRMmZHeWg5dTF4Y3RGOTBDbklpZXRvSWFVNzhFR1ZZbHl3QT0iLCJtYWMiOiJiYjE3YmVkNjdlZGQ4YmM5YmJhZTc3ZDZhN2M1ZWY5Mjg5N2E5Yjg4ZWVkODA2YjEyYTdjNTJhYjE0MjhjZTJiIiwidGFnIjoiIn0=', 'eyJpdiI6Im9Md2hvNk52VVBUVWpFQmpKcE5OK0E9PSIsInZhbHVlIjoiMDZWN2phRnArSjA1Q2p2K3phekVTSlEwUmdNVzFvMVNqSU00VE9aeVJHST0iLCJtYWMiOiJiYmMzMWNmNDU0YzAwY2RjYzVlMWVmODVlZWU5ZGU4YWJjNmE3NjBlNzQzYTdlZWQzOWJlZmM1YTg1NjBhMjdkIiwidGFnIjoiIn0=', '2026-06-02 00:00:00', '2026-07-02 00:00:00'),
(10, 26, 'eyJpdiI6Ijdja0JTZE94V2ZLUnJOUXZueUhHeFE9PSIsInZhbHVlIjoiVllBdldaeGVtWTZnSm9aYnc4YUtvZz09IiwibWFjIjoiNWYzMjc4OTE0ZDVjNzk5MTRhNzQxMzQ0Mzk5YzU3NmM3NmFmOThhZWQwY2IxMTVlMWZhYjFmMjgzMjc2MGQ2ZiIsInRhZyI6IiJ9', 'eyJpdiI6InRHV09JK2JrSVBJbEl0MEhDanFibmc9PSIsInZhbHVlIjoidXQxY01rdDlUdnQwc2NVT3BCb0hJdz09IiwibWFjIjoiNTFjZTAwMjhlNDdhYTdkNzNjNWFmMTI4MThkZTA2NzI5MzgwMzljNGUwZWU3OGQ1MTdhZWUwYzVhMmNjZGI0YSIsInRhZyI6IiJ9', 'eyJpdiI6Im4xcGoyNXZwdmFIbUxSQVVHVGttcmc9PSIsInZhbHVlIjoiNEp1M3lDN21takl6Z1FkeFBJZGdXdz09IiwibWFjIjoiY2Y4NmQ2ZmUwMGRlMTQ0MzA4YzdhZTZlZjI2OWJjZDAzZTYwODJkZTE3OWYwOWQ5OGEzYzM0NDQ4MWVkNDk0ZiIsInRhZyI6IiJ9', 'eyJpdiI6IkxpdVBQS2hWWHVXbVZrN21oakIxWVE9PSIsInZhbHVlIjoic0U3bzFzN2tvREtBUHpYc0RrZHY1Zz09IiwibWFjIjoiNWNhZGVmNGRjN2NhOTk0ZGNhOGU1N2Y5NGMzZTRjNThkY2MzOTllYmM4NGMxNDE0YWRhNDQ1ODQ2NWEzMWViMyIsInRhZyI6IiJ9', 'eyJpdiI6IkdXM1FjK2dUM2l0ZVdEYXJSY2xEUnc9PSIsInZhbHVlIjoiR1c1bTk0SnBwMTdKTnZtS21yNXExM1RtNUNydm8vVzcxd00zT0Z5SVc1cz0iLCJtYWMiOiIyMzk3NWM0ZjZkODRiYmM5MjZjNTAyZWUxMjE5NGMyMGMxY2Y3ODVhMjdlNzFiNTI3MzhjZjBkYWVmMTg4MzI3IiwidGFnIjoiIn0=', 'eyJpdiI6IjQzeXcwaU16dkJoNFQ0UnEydUhSRUE9PSIsInZhbHVlIjoiZXlwc2RtMFR6Y2R5V1pFVGU1S1IwUW16c253MjRLQkViMmgzbkFlRnZWST0iLCJtYWMiOiI0N2EyNTcwZGNmMGU1ZDNkOWI2ZTJjZjk2MWI1ODIzOTQzNTRkZTQ4N2Q5YWM4Y2YzYzA1YWI0ZTVhYTZlMGNkIiwidGFnIjoiIn0=', '2026-06-23 00:00:00', '2026-07-07 00:00:00');

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
(1691, 'tFjU0ts9JjHfjIiiFjkyB1sjsb5Xp5m1j01F8PLa', '{\"_token\":\"rCeR2CKkgTzdF9Y9vCE0iiFI3vuU5M3sqgVTau75\",\"_ip_hash\":\"30b12f484689952acc86246d905fc7c2aaf3110d793b8c8c6ad6a393dd31eb79\",\"_ua_hash\":\"48da1af908be076363e16c00999867b2c05b0261ae4e010e737f12eae1e71aff\",\"_last_activity\":1782746382,\"_previous\":{\"url\":\"https:\\/\\/fotos.martinwagner.de\\/backstage\",\"route\":\"system.backstage.login\"},\"_flash\":{\"old\":[],\"new\":[]},\"2fa_syst_id\":1}', 'anon', NULL, NULL, NULL, 0, '30b12f484689952acc86246d905fc7c2aaf3110d793b8c8c6ad6a393dd31eb79', '48da1af908be076363e16c00999867b2c05b0261ae4e010e737f12eae1e71aff', '2026-06-29 15:19:08', '2026-06-29 15:19:42', '2026-06-29 17:19:42'),
(1693, 'YZns89LF1Y1ICmMTlOoRH4xMaLpwyMYXfRPRA7J5', '{\"_token\":\"xmcSHJ7cYAHiaqKkdlw72gksUQ22nxG0mF93ac9q\",\"_flash\":{\"old\":[],\"new\":[]},\"_ip_hash\":\"30b12f484689952acc86246d905fc7c2aaf3110d793b8c8c6ad6a393dd31eb79\",\"_ua_hash\":\"48da1af908be076363e16c00999867b2c05b0261ae4e010e737f12eae1e71aff\",\"_last_activity\":1782746639,\"_previous\":{\"url\":\"https:\\/\\/fotos.martinwagner.de\\/mandant\\/login\\/2fa\",\"route\":\"mandant.login.2fa\"},\"pending_mand_id\":31}', 'anon', NULL, NULL, NULL, 0, '30b12f484689952acc86246d905fc7c2aaf3110d793b8c8c6ad6a393dd31eb79', '48da1af908be076363e16c00999867b2c05b0261ae4e010e737f12eae1e71aff', '2026-06-29 15:22:29', '2026-06-29 15:23:59', '2026-06-29 17:23:59'),
(1695, '4R9DTPZA9UVciSeyhjhetvzD7eMgKVbmk5gz0iA4', '{\"_token\":\"ODLRBtxTqQ4dXYI3ZzYa7vxPeo9qNQKeDV4MQAJR\",\"_flash\":{\"new\":[],\"old\":[]},\"_ip_hash\":\"30b12f484689952acc86246d905fc7c2aaf3110d793b8c8c6ad6a393dd31eb79\",\"_ua_hash\":\"48da1af908be076363e16c00999867b2c05b0261ae4e010e737f12eae1e71aff\",\"_last_activity\":1782746744,\"_previous\":{\"url\":\"https:\\/\\/fotos.martinwagner.de\",\"route\":\"home\"}}', 'anon', NULL, NULL, NULL, 0, '30b12f484689952acc86246d905fc7c2aaf3110d793b8c8c6ad6a393dd31eb79', '48da1af908be076363e16c00999867b2c05b0261ae4e010e737f12eae1e71aff', '2026-06-29 15:25:43', '2026-06-29 15:25:44', '2026-06-29 17:25:44'),
(1696, 'sZqXjhdyLWzn3lN1av4rKfbXVkDBdJ80g5epp2X3', '{\"_token\":\"Wb34oLi4irWdTOpmGBjmWM6Yn5tBQ0a6QXs0kiHN\",\"_flash\":{\"new\":[],\"old\":[]},\"_ip_hash\":\"30b12f484689952acc86246d905fc7c2aaf3110d793b8c8c6ad6a393dd31eb79\",\"_ua_hash\":\"48da1af908be076363e16c00999867b2c05b0261ae4e010e737f12eae1e71aff\",\"_last_activity\":1782746793,\"_previous\":{\"url\":\"https:\\/\\/fotos.martinwagner.de\\/customer\\/content\",\"route\":\"customer.content\"},\"_user_type\":\"cust\",\"_cust_id\":33,\"_mand_id\":30,\"_sec_level\":\"3\",\"_prompt_passkey\":true,\"_passkey_os\":\"ios\",\"_passkey_browser\":\"safari\",\"_passkey_uahash\":\"48da1af908be076363e16c00999867b2c05b0261ae4e010e737f12eae1e71aff\"}', 'anon', NULL, NULL, NULL, 0, '30b12f484689952acc86246d905fc7c2aaf3110d793b8c8c6ad6a393dd31eb79', '48da1af908be076363e16c00999867b2c05b0261ae4e010e737f12eae1e71aff', '2026-06-29 15:26:26', '2026-06-29 15:26:33', '2026-06-29 17:26:33'),
(1697, 'MpzPrl5dq3Exi0wTZvtaVIxMEpNMOYQVSV5No6Hc', '{\"_token\":\"cKX6mpToX5KdDMSnsivdoyv15poyHV5hm9x8hU02\",\"_ip_hash\":\"30b12f484689952acc86246d905fc7c2aaf3110d793b8c8c6ad6a393dd31eb79\",\"_ua_hash\":\"48da1af908be076363e16c00999867b2c05b0261ae4e010e737f12eae1e71aff\",\"_last_activity\":1782747101,\"_previous\":{\"url\":\"https:\\/\\/fotos.martinwagner.de\\/backstage\",\"route\":\"system.backstage.login\"},\"_flash\":{\"old\":[],\"new\":[]},\"2fa_syst_id\":1}', 'anon', NULL, NULL, NULL, 0, '30b12f484689952acc86246d905fc7c2aaf3110d793b8c8c6ad6a393dd31eb79', '48da1af908be076363e16c00999867b2c05b0261ae4e010e737f12eae1e71aff', '2026-06-29 15:31:09', '2026-06-29 15:31:41', '2026-06-29 17:31:41'),
(1699, '3TkCuzqOIewjaWd1ksHd1lySYQmeRfnwlPsfZX3K', '{\"_token\":\"8RRD7d9iBfBQDLRgw3eS5PxMErjO5GD3QUDDeLQR\",\"_flash\":{\"old\":[],\"new\":[]},\"_ip_hash\":\"30b12f484689952acc86246d905fc7c2aaf3110d793b8c8c6ad6a393dd31eb79\",\"_ua_hash\":\"48da1af908be076363e16c00999867b2c05b0261ae4e010e737f12eae1e71aff\",\"_last_activity\":1782747191,\"_previous\":{\"url\":\"https:\\/\\/fotos.martinwagner.de\",\"route\":\"home\"}}', 'anon', NULL, NULL, NULL, 0, '30b12f484689952acc86246d905fc7c2aaf3110d793b8c8c6ad6a393dd31eb79', '48da1af908be076363e16c00999867b2c05b0261ae4e010e737f12eae1e71aff', '2026-06-29 15:33:11', '2026-06-29 15:33:11', '2026-06-29 17:33:11'),
(1700, '2kMB9VS9VoUpfKi6KafxtMg8MXq3Jis1LkJ568RO', '{\"_token\":\"SpNC86LzqHU5WKM35UL9gD2GC2EwIZIgXNOcPw36\",\"_ip_hash\":\"30b12f484689952acc86246d905fc7c2aaf3110d793b8c8c6ad6a393dd31eb79\",\"_ua_hash\":\"48da1af908be076363e16c00999867b2c05b0261ae4e010e737f12eae1e71aff\",\"_last_activity\":1782747342,\"_previous\":{\"url\":\"https:\\/\\/fotos.martinwagner.de\\/customer\\/login\\/2fa\",\"route\":\"customer.login.2fa\"},\"_flash\":{\"old\":[],\"new\":[]},\"pending_cust_id\":35,\"pending_mand_id\":28,\"pending_sec_level\":\"5\"}', 'anon', NULL, NULL, NULL, 0, '30b12f484689952acc86246d905fc7c2aaf3110d793b8c8c6ad6a393dd31eb79', '48da1af908be076363e16c00999867b2c05b0261ae4e010e737f12eae1e71aff', '2026-06-29 15:33:29', '2026-06-29 15:35:42', '2026-06-29 17:35:42'),
(1701, 'ZkZyUOJMiM3vbQmDPjGB9F1I2wt5Ju4QH3qkxj3D', '{\"_token\":\"EnLVlmEOzbtYMEpAJCPR8VSh6oOQU6Eaz1QIX27T\",\"_ip_hash\":\"30b12f484689952acc86246d905fc7c2aaf3110d793b8c8c6ad6a393dd31eb79\",\"_ua_hash\":\"48da1af908be076363e16c00999867b2c05b0261ae4e010e737f12eae1e71aff\",\"_last_activity\":1782747873,\"_previous\":{\"url\":\"https:\\/\\/fotos.martinwagner.de\",\"route\":\"home\"},\"_flash\":{\"old\":[],\"new\":[]}}', 'anon', NULL, NULL, NULL, 0, '30b12f484689952acc86246d905fc7c2aaf3110d793b8c8c6ad6a393dd31eb79', '48da1af908be076363e16c00999867b2c05b0261ae4e010e737f12eae1e71aff', '2026-06-29 15:44:33', '2026-06-29 15:44:33', '2026-06-29 17:44:33'),
(1702, 'HMu4m9ykRzuIRRGftVe7srkPv5JLCxvDfQRaOtBF', '{\"_token\":\"pmhjRNcamIyIcRN9nSAQhgP7vVs84BKER9sJNWR2\",\"_ip_hash\":\"30b12f484689952acc86246d905fc7c2aaf3110d793b8c8c6ad6a393dd31eb79\",\"_ua_hash\":\"48da1af908be076363e16c00999867b2c05b0261ae4e010e737f12eae1e71aff\",\"_last_activity\":1782748264,\"_previous\":{\"url\":\"https:\\/\\/fotos.martinwagner.de\\/mandant\\/login\\/2fa\",\"route\":\"mandant.login.2fa\"},\"_flash\":{\"old\":[],\"new\":[]},\"pending_mand_id\":31}', 'anon', NULL, NULL, NULL, 0, '30b12f484689952acc86246d905fc7c2aaf3110d793b8c8c6ad6a393dd31eb79', '48da1af908be076363e16c00999867b2c05b0261ae4e010e737f12eae1e71aff', '2026-06-29 15:48:20', '2026-06-29 15:51:04', '2026-06-29 17:51:04'),
(1704, 'jXjS9d0bEtf1i6JI5qT0mhBv35jYDiMCFfyl8giJ', '{\"_token\":\"4SUHVsmeAxK4EuW7m0NZZ8bTuGHPusc8ZkEchZ84\",\"_flash\":{\"new\":[],\"old\":[]},\"_ip_hash\":\"30b12f484689952acc86246d905fc7c2aaf3110d793b8c8c6ad6a393dd31eb79\",\"_ua_hash\":\"48da1af908be076363e16c00999867b2c05b0261ae4e010e737f12eae1e71aff\",\"_last_activity\":1782748509,\"_previous\":{\"url\":\"https:\\/\\/fotos.martinwagner.de\\/customer\\/login\\/2fa\",\"route\":\"customer.login.2fa\"},\"2fa_syst_id\":1,\"pending_cust_id\":35,\"pending_mand_id\":28,\"pending_sec_level\":\"5\"}', 'anon', NULL, NULL, NULL, 0, '30b12f484689952acc86246d905fc7c2aaf3110d793b8c8c6ad6a393dd31eb79', '48da1af908be076363e16c00999867b2c05b0261ae4e010e737f12eae1e71aff', '2026-06-29 15:51:24', '2026-06-29 15:55:10', '2026-06-29 17:55:10'),
(1705, 'NO5euvRzop6grv4JFe496DMH4s3PH8RNFn8WhxDz', '{\"_token\":\"6BDB4uv6pif7A7WfSK7zcUNufaqJi1MhR8YebnFn\",\"_flash\":{\"new\":[],\"old\":[]},\"_ip_hash\":\"30b12f484689952acc86246d905fc7c2aaf3110d793b8c8c6ad6a393dd31eb79\",\"_ua_hash\":\"48da1af908be076363e16c00999867b2c05b0261ae4e010e737f12eae1e71aff\",\"_last_activity\":1782748462,\"_previous\":{\"url\":\"https:\\/\\/fotos.martinwagner.de\\/customer\\/login\\/2fa\",\"route\":\"customer.login.2fa\"},\"2fa_syst_id\":1,\"_user_type\":\"cust\",\"_cust_id\":35,\"_mand_id\":28,\"_sec_level\":\"5\",\"_prompt_passkey\":true,\"_passkey_os\":\"ios\",\"_passkey_browser\":\"safari\",\"_passkey_uahash\":\"48da1af908be076363e16c00999867b2c05b0261ae4e010e737f12eae1e71aff\"}', 'anon', NULL, NULL, NULL, 0, '30b12f484689952acc86246d905fc7c2aaf3110d793b8c8c6ad6a393dd31eb79', '48da1af908be076363e16c00999867b2c05b0261ae4e010e737f12eae1e71aff', '2026-06-29 15:54:22', '2026-06-29 15:54:22', '2026-06-29 17:54:22'),
(1706, 'NOmOp1zOCKWpD6kCo6HYbbjlu9pH4iBWKsKYG3me', '{\"_token\":\"QM7muxPOe3thxwRIYEUweCXwpYhVhlWG1qFAjMXA\",\"_flash\":{\"new\":[],\"old\":[]},\"_ip_hash\":\"30b12f484689952acc86246d905fc7c2aaf3110d793b8c8c6ad6a393dd31eb79\",\"_ua_hash\":\"48da1af908be076363e16c00999867b2c05b0261ae4e010e737f12eae1e71aff\",\"_last_activity\":1782748529,\"_previous\":{\"url\":\"https:\\/\\/fotos.martinwagner.de\\/customer\\/policy-update\",\"route\":\"customer.policy.update\"},\"2fa_syst_id\":1,\"_user_type\":\"cust\",\"_cust_id\":35,\"_mand_id\":28,\"_sec_level\":\"5\",\"_prompt_passkey\":true,\"_passkey_os\":\"ios\",\"_passkey_browser\":\"safari\",\"_passkey_uahash\":\"48da1af908be076363e16c00999867b2c05b0261ae4e010e737f12eae1e71aff\",\"_policy_update\":\"ds\"}', 'anon', NULL, NULL, NULL, 0, '30b12f484689952acc86246d905fc7c2aaf3110d793b8c8c6ad6a393dd31eb79', '48da1af908be076363e16c00999867b2c05b0261ae4e010e737f12eae1e71aff', '2026-06-29 15:55:28', '2026-06-29 15:55:29', '2026-06-29 17:55:29'),
(1707, 'NJDzX7YPr0AU4RV3jlUdMHHHkXT9EZ4JvFZojb8H', '{\"_token\":\"JTM4OzarcNwxzjUgzhMjKxfDTwS1WPWVmG8AbyrV\",\"_flash\":{\"new\":[],\"old\":[]},\"_previous\":{\"url\":\"https:\\/\\/fotos.martinwagner.de\\/customer\\/login\\/2fa\",\"route\":\"customer.login.2fa\"},\"_ip_hash\":\"0c10b1fab59892746bda93ce0a8ddb59a5e8e14413fe3227b51f7e0267dae889\",\"_ua_hash\":\"d86eb46e24fd10d4d82809477c280ef54af9e84bc167e96c3071b0216c424779\",\"_last_activity\":1782748732,\"pending_cust_id\":35,\"pending_mand_id\":28,\"pending_sec_level\":\"5\"}', 'anon', NULL, NULL, NULL, 0, '0c10b1fab59892746bda93ce0a8ddb59a5e8e14413fe3227b51f7e0267dae889', 'd86eb46e24fd10d4d82809477c280ef54af9e84bc167e96c3071b0216c424779', '2026-06-29 15:57:39', '2026-06-29 15:58:52', '2026-06-29 17:58:52'),
(1709, 'NMIIHNSJxHViV61J5Pe2tqfUa9OCsAvxeyltr0tI', '{\"_token\":\"rl3twIcGuMcgqaOQbruCShaMnlyurDnmSxOJTgDN\",\"_flash\":{\"new\":[],\"old\":[]},\"_ip_hash\":\"0c10b1fab59892746bda93ce0a8ddb59a5e8e14413fe3227b51f7e0267dae889\",\"_ua_hash\":\"d86eb46e24fd10d4d82809477c280ef54af9e84bc167e96c3071b0216c424779\",\"_last_activity\":1782748874,\"_previous\":{\"url\":\"https:\\/\\/fotos.martinwagner.de\\/mandant\\/login\\/2fa\",\"route\":\"mandant.login.2fa\"},\"pending_mand_id\":28}', 'anon', NULL, NULL, NULL, 0, '0c10b1fab59892746bda93ce0a8ddb59a5e8e14413fe3227b51f7e0267dae889', 'd86eb46e24fd10d4d82809477c280ef54af9e84bc167e96c3071b0216c424779', '2026-06-29 15:59:26', '2026-06-29 16:01:14', '2026-06-29 18:01:14'),
(1711, 'OsYNU6Bf0f3FOUNITlm5nJuvHlC77nu3WsgX0y4W', '{\"_token\":\"VgWZFn8m3d75thVhgj9ZbTIROw7Saggo0BqKu1Mu\",\"_flash\":{\"new\":[],\"old\":[]},\"_ip_hash\":\"0c10b1fab59892746bda93ce0a8ddb59a5e8e14413fe3227b51f7e0267dae889\",\"_ua_hash\":\"d86eb46e24fd10d4d82809477c280ef54af9e84bc167e96c3071b0216c424779\",\"_last_activity\":1782749051,\"_previous\":{\"url\":\"https:\\/\\/fotos.martinwagner.de\\/backstage\",\"route\":\"system.backstage.login\"},\"2fa_syst_id\":1}', 'anon', NULL, NULL, NULL, 0, '0c10b1fab59892746bda93ce0a8ddb59a5e8e14413fe3227b51f7e0267dae889', 'd86eb46e24fd10d4d82809477c280ef54af9e84bc167e96c3071b0216c424779', '2026-06-29 16:01:48', '2026-06-29 16:04:11', '2026-06-29 18:04:11'),
(1712, 't4rLH0guk9PeWIQgVBdoHHNiRcPGi4QGjpB66tsN', '{\"_token\":\"SZamgsJGHxaMUiw63xFtikpBkRombRO4VPejlHLc\",\"_flash\":{\"new\":[],\"old\":[]},\"_ip_hash\":\"0c10b1fab59892746bda93ce0a8ddb59a5e8e14413fe3227b51f7e0267dae889\",\"_ua_hash\":\"d86eb46e24fd10d4d82809477c280ef54af9e84bc167e96c3071b0216c424779\",\"_last_activity\":1782749076,\"_previous\":{\"url\":\"https:\\/\\/fotos.martinwagner.de\\/system\\/dashboard\",\"route\":\"system.dashboard\"},\"_user_type\":\"syst\",\"_syst_id\":1,\"_is_primary\":true}', 'anon', NULL, NULL, NULL, 0, '0c10b1fab59892746bda93ce0a8ddb59a5e8e14413fe3227b51f7e0267dae889', 'd86eb46e24fd10d4d82809477c280ef54af9e84bc167e96c3071b0216c424779', '2026-06-29 16:04:29', '2026-06-29 16:04:36', '2026-06-29 18:04:36'),
(1713, 'I6j4vKHEkUBlYb7EqkJ5XHiWcWr8HHwnrYglcvj1', '{\"_token\":\"ByI7oXcnI04CQkCswLXrSGPwVU005ZEALoRHB1vf\",\"_ip_hash\":\"30b12f484689952acc86246d905fc7c2aaf3110d793b8c8c6ad6a393dd31eb79\",\"_ua_hash\":\"48da1af908be076363e16c00999867b2c05b0261ae4e010e737f12eae1e71aff\",\"_last_activity\":1782749475,\"_previous\":{\"url\":\"https:\\/\\/fotos.martinwagner.de\\/backstage\",\"route\":\"system.backstage.login\"},\"_flash\":{\"old\":[],\"new\":[]},\"2fa_syst_id\":1}', 'anon', NULL, NULL, NULL, 0, '30b12f484689952acc86246d905fc7c2aaf3110d793b8c8c6ad6a393dd31eb79', '48da1af908be076363e16c00999867b2c05b0261ae4e010e737f12eae1e71aff', '2026-06-29 16:10:32', '2026-06-29 16:11:15', '2026-06-29 18:11:15'),
(1714, 'qP1xIWllJcNYoBfGsaWgdPKOHFiXl8AlezMQcwqC', '{\"_token\":\"RVqAyq68iqPDNZG6NDw2XEhAD4iH14oYLZUqkmar\",\"_ip_hash\":\"30b12f484689952acc86246d905fc7c2aaf3110d793b8c8c6ad6a393dd31eb79\",\"_ua_hash\":\"48da1af908be076363e16c00999867b2c05b0261ae4e010e737f12eae1e71aff\",\"_last_activity\":1782749495,\"_previous\":{\"url\":\"https:\\/\\/fotos.martinwagner.de\\/system\\/users\",\"route\":\"system.users.index\"},\"_flash\":{\"old\":[],\"new\":[]},\"_user_type\":\"syst\",\"_syst_id\":1,\"_is_primary\":true}', 'anon', NULL, NULL, NULL, 0, '30b12f484689952acc86246d905fc7c2aaf3110d793b8c8c6ad6a393dd31eb79', '48da1af908be076363e16c00999867b2c05b0261ae4e010e737f12eae1e71aff', '2026-06-29 16:11:32', '2026-06-29 16:11:35', '2026-06-29 18:11:35'),
(1715, 'Tdq8KKJAfmXsrfC7SzpJRkyIOaPp4DDwgU6MwTTo', '{\"_token\":\"K5o3MC4T5PYcEbNUyztnCuxq4Fr4iT63ICxKFRdY\",\"_ip_hash\":\"30b12f484689952acc86246d905fc7c2aaf3110d793b8c8c6ad6a393dd31eb79\",\"_ua_hash\":\"48da1af908be076363e16c00999867b2c05b0261ae4e010e737f12eae1e71aff\",\"_last_activity\":1782749713,\"_previous\":{\"url\":\"https:\\/\\/fotos.martinwagner.de\\/backstage\",\"route\":\"system.backstage.login\"},\"_flash\":{\"old\":[],\"new\":[]},\"2fa_syst_id\":1}', 'anon', NULL, NULL, NULL, 0, '30b12f484689952acc86246d905fc7c2aaf3110d793b8c8c6ad6a393dd31eb79', '48da1af908be076363e16c00999867b2c05b0261ae4e010e737f12eae1e71aff', '2026-06-29 16:14:50', '2026-06-29 16:15:14', '2026-06-29 18:15:14'),
(1717, 'lF9B99KOKDlYer7JsMqVJzo8yDbnihhPpwAEAPV0', '{\"_token\":\"ZzOS1FeSLyYM6IZhfVHzwYxk1wUSNOz5I5I1W7oo\",\"_flash\":{\"old\":[],\"new\":[]},\"_ip_hash\":\"30b12f484689952acc86246d905fc7c2aaf3110d793b8c8c6ad6a393dd31eb79\",\"_ua_hash\":\"48da1af908be076363e16c00999867b2c05b0261ae4e010e737f12eae1e71aff\",\"_last_activity\":1782749806,\"_previous\":{\"url\":\"https:\\/\\/fotos.martinwagner.de\\/backstage\",\"route\":\"system.backstage.login\"},\"2fa_syst_id\":12}', 'anon', NULL, NULL, NULL, 0, '30b12f484689952acc86246d905fc7c2aaf3110d793b8c8c6ad6a393dd31eb79', '48da1af908be076363e16c00999867b2c05b0261ae4e010e737f12eae1e71aff', '2026-06-29 16:16:01', '2026-06-29 16:16:46', '2026-06-29 18:16:46'),
(1719, 'BWxsJxFZhy7GxeVKbdbRMStSxxsvWCUzGVl9RdR7', '{\"_token\":\"vlguBwKKDBIxzDGBhLl6sae798uRKhZVWyUiWAvE\",\"_flash\":{\"old\":[],\"new\":[]},\"_ip_hash\":\"30b12f484689952acc86246d905fc7c2aaf3110d793b8c8c6ad6a393dd31eb79\",\"_ua_hash\":\"48da1af908be076363e16c00999867b2c05b0261ae4e010e737f12eae1e71aff\",\"_last_activity\":1782750186,\"_previous\":{\"url\":\"https:\\/\\/fotos.martinwagner.de\\/customer\\/login\\/2fa\",\"route\":\"customer.login.2fa\"},\"pending_cust_id\":34,\"pending_mand_id\":28,\"pending_sec_level\":\"5\"}', 'anon', NULL, NULL, NULL, 0, '30b12f484689952acc86246d905fc7c2aaf3110d793b8c8c6ad6a393dd31eb79', '48da1af908be076363e16c00999867b2c05b0261ae4e010e737f12eae1e71aff', '2026-06-29 16:22:14', '2026-06-29 16:23:06', '2026-06-29 18:23:06'),
(1721, 'c952RiwE80qKdyUE8g3VKyKkbzJsFSM8OQc7m9JR', '{\"_token\":\"VP5ZEZfhayEjSPe6CjC0jNGaBpx6Ja6F8fVCHTDK\",\"_flash\":{\"new\":[],\"old\":[]},\"_ip_hash\":\"30b12f484689952acc86246d905fc7c2aaf3110d793b8c8c6ad6a393dd31eb79\",\"_ua_hash\":\"48da1af908be076363e16c00999867b2c05b0261ae4e010e737f12eae1e71aff\",\"_last_activity\":1782750547,\"_previous\":{\"url\":\"https:\\/\\/fotos.martinwagner.de\\/customer\\/login\\/2fa\",\"route\":\"customer.login.2fa\"},\"pending_cust_id\":34,\"pending_mand_id\":28,\"pending_sec_level\":\"5\"}', 'anon', NULL, NULL, NULL, 0, '30b12f484689952acc86246d905fc7c2aaf3110d793b8c8c6ad6a393dd31eb79', '48da1af908be076363e16c00999867b2c05b0261ae4e010e737f12eae1e71aff', '2026-06-29 16:23:50', '2026-06-29 16:29:07', '2026-06-29 18:29:07'),
(1722, 'dGeGu5kcbBwcmWLSUM91Cmirju4ZfqZYFMiDiDkc', '{\"_token\":\"YAadiAmtNtGWYpwABmcqtd6uefzqRqRqhSYUUP7a\",\"_flash\":{\"new\":[],\"old\":[]},\"_ip_hash\":\"30b12f484689952acc86246d905fc7c2aaf3110d793b8c8c6ad6a393dd31eb79\",\"_ua_hash\":\"48da1af908be076363e16c00999867b2c05b0261ae4e010e737f12eae1e71aff\",\"_last_activity\":1782750596,\"_previous\":{\"url\":\"https:\\/\\/fotos.martinwagner.de\\/customer\\/dashboard\",\"route\":\"customer.dashboard\"},\"_user_type\":\"cust\",\"_cust_id\":34,\"_mand_id\":28,\"_sec_level\":\"5\",\"_prompt_passkey\":false,\"_passkey_os\":\"ios\",\"_passkey_browser\":\"safari\",\"_passkey_uahash\":\"48da1af908be076363e16c00999867b2c05b0261ae4e010e737f12eae1e71aff\"}', 'anon', NULL, NULL, NULL, 0, '30b12f484689952acc86246d905fc7c2aaf3110d793b8c8c6ad6a393dd31eb79', '48da1af908be076363e16c00999867b2c05b0261ae4e010e737f12eae1e71aff', '2026-06-29 16:29:23', '2026-06-29 16:29:56', '2026-06-29 18:29:56'),
(1723, 'NxUR0iCHRbY4bOiUGz8bSQpHWkgS8ov3I4WArYbH', '{\"_token\":\"Nu61idwwHp3I4sAVnlEspNDbfr49f1xkalcvKxeU\",\"_ip_hash\":\"30b12f484689952acc86246d905fc7c2aaf3110d793b8c8c6ad6a393dd31eb79\",\"_ua_hash\":\"48da1af908be076363e16c00999867b2c05b0261ae4e010e737f12eae1e71aff\",\"_last_activity\":1782750788,\"_previous\":{\"url\":\"https:\\/\\/fotos.martinwagner.de\\/backstage\",\"route\":\"system.backstage.login\"},\"_flash\":{\"old\":[],\"new\":[]},\"2fa_syst_id\":1}', 'anon', NULL, NULL, NULL, 0, '30b12f484689952acc86246d905fc7c2aaf3110d793b8c8c6ad6a393dd31eb79', '48da1af908be076363e16c00999867b2c05b0261ae4e010e737f12eae1e71aff', '2026-06-29 16:32:17', '2026-06-29 16:33:08', '2026-06-29 18:33:08'),
(1725, '5FAxtmiPpu83dFnZtmyuyupX6TUF5kvCiVf0ZWWe', '{\"_token\":\"fAMC0K9bwp66dVT5m7MQBeQwmUcLN1uAKTUtH6Tz\",\"_flash\":{\"old\":[],\"new\":[]},\"_ip_hash\":\"30b12f484689952acc86246d905fc7c2aaf3110d793b8c8c6ad6a393dd31eb79\",\"_ua_hash\":\"48da1af908be076363e16c00999867b2c05b0261ae4e010e737f12eae1e71aff\",\"_last_activity\":1782750839,\"_previous\":{\"url\":\"https:\\/\\/fotos.martinwagner.de\",\"route\":\"home\"}}', 'anon', NULL, NULL, NULL, 0, '30b12f484689952acc86246d905fc7c2aaf3110d793b8c8c6ad6a393dd31eb79', '48da1af908be076363e16c00999867b2c05b0261ae4e010e737f12eae1e71aff', '2026-06-29 16:33:59', '2026-06-29 16:33:59', '2026-06-29 18:33:59'),
(1726, '43fAddxIJJSrOhiNURRHSP8XgsB45usAjeQJwPHJ', '{\"_token\":\"VxFnUOw3UY4FXinVdHi5rvPwmidoJHlrDUKgoQ0K\",\"_ip_hash\":\"30b12f484689952acc86246d905fc7c2aaf3110d793b8c8c6ad6a393dd31eb79\",\"_ua_hash\":\"48da1af908be076363e16c00999867b2c05b0261ae4e010e737f12eae1e71aff\",\"_last_activity\":1782752612,\"_previous\":{\"url\":\"https:\\/\\/fotos.martinwagner.de\",\"route\":\"home\"},\"_flash\":{\"old\":[],\"new\":[]}}', 'anon', NULL, NULL, NULL, 0, '30b12f484689952acc86246d905fc7c2aaf3110d793b8c8c6ad6a393dd31eb79', '48da1af908be076363e16c00999867b2c05b0261ae4e010e737f12eae1e71aff', '2026-06-29 16:57:21', '2026-06-29 17:03:32', '2026-06-29 19:03:32'),
(1727, 'dzN3ETkpKWPrFOnKTYRj0DoM0M7NvRqLjJ3Elxjq', '{\"_token\":\"7ZMJ9YU6ByrbR6v49GYfvdXTJqVWT8HZ6r6HoFkN\",\"_ip_hash\":\"459baae5b870d820a211c6055b807db1bfca5bad4f88d2aff0f063d206dd914a\",\"_ua_hash\":\"f9d9e5fc7a9e5166686bdd114f1297037eeccac3a3d2e78979e5f727bf4bb895\",\"_last_activity\":1782752699,\"_previous\":{\"url\":\"https:\\/\\/fotos.martinwagner.de\\/customer\\/login\\/2fa\",\"route\":\"customer.login.2fa\"},\"_flash\":{\"old\":[],\"new\":[]},\"pending_cust_id\":34,\"pending_mand_id\":28,\"pending_sec_level\":\"5\"}', 'anon', NULL, NULL, NULL, 0, '459baae5b870d820a211c6055b807db1bfca5bad4f88d2aff0f063d206dd914a', 'f9d9e5fc7a9e5166686bdd114f1297037eeccac3a3d2e78979e5f727bf4bb895', '2026-06-29 16:59:04', '2026-06-29 17:04:59', '2026-06-29 19:04:59'),
(1729, 'HGAvnCCemSGq3cuqfCkX4NAC6uaAjm9DdwN6VcMO', '{\"_token\":\"Ad7NnCIlGy0cxIAmBWq00ggE8lMSBY9qG9TTwx6B\",\"_flash\":{\"new\":[],\"old\":[]},\"_ip_hash\":\"459baae5b870d820a211c6055b807db1bfca5bad4f88d2aff0f063d206dd914a\",\"_ua_hash\":\"f9d9e5fc7a9e5166686bdd114f1297037eeccac3a3d2e78979e5f727bf4bb895\",\"_last_activity\":1782752818,\"_previous\":{\"url\":\"https:\\/\\/fotos.martinwagner.de\\/backstage\",\"route\":\"system.backstage.login\"},\"2fa_syst_id\":7}', 'anon', NULL, NULL, NULL, 0, '459baae5b870d820a211c6055b807db1bfca5bad4f88d2aff0f063d206dd914a', 'f9d9e5fc7a9e5166686bdd114f1297037eeccac3a3d2e78979e5f727bf4bb895', '2026-06-29 17:06:26', '2026-06-29 17:06:58', '2026-06-29 19:06:58'),
(1731, 'XZUmYo9Bghp9K0h5MXqhYMuv6fK6SWoPBuUmZu87', '{\"_token\":\"KpSyRaXDwMCGw4OgBuhNUps5tXcHvOIGv1U84uXr\",\"_flash\":{\"old\":[],\"new\":[]},\"_ip_hash\":\"459baae5b870d820a211c6055b807db1bfca5bad4f88d2aff0f063d206dd914a\",\"_ua_hash\":\"f9d9e5fc7a9e5166686bdd114f1297037eeccac3a3d2e78979e5f727bf4bb895\",\"_last_activity\":1782753048,\"_previous\":{\"url\":\"https:\\/\\/fotos.martinwagner.de\\/backstage\",\"route\":\"system.backstage.login\"}}', 'anon', NULL, NULL, NULL, 0, '459baae5b870d820a211c6055b807db1bfca5bad4f88d2aff0f063d206dd914a', 'f9d9e5fc7a9e5166686bdd114f1297037eeccac3a3d2e78979e5f727bf4bb895', '2026-06-29 17:09:24', '2026-06-29 17:10:48', '2026-06-29 19:10:48');

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
(1, 'syst', 1, 'login', '$2y$12$MWK6qm3VZpvSHqEC4ixT3OykbnFfRi.oeVilOpeMXL4YxKaPvmehW', '2026-06-29 16:35:07', 1, '2026-06-29 16:33:07'),
(2, 'syst', 2, 'login', '$2y$12$swMXY/nlkePcPI/Vxps0fOj/VBxcX2Wclyfx.4deaoFznb8s8koiq', '2026-05-12 15:08:54', 1, '2026-05-12 14:58:54'),
(3, 'syst', 3, 'login', '$2y$12$GOT5fz9KwHkdKkd7wtpHyuHsEvpwFfP/lpNsqh40apghfFT17pVcu', '2026-06-21 17:03:25', 1, '2026-06-21 17:01:25'),
(4, 'syst', 4, 'login', '$2y$12$91me09zv8U91jODwNcgVru4JV2Y8psYKEFIo0mXJtvRpfJgJM0Jli', '2026-05-12 16:28:46', 1, '2026-05-12 16:18:46'),
(5, 'mand', 9, 'login', '$2y$12$5tlI1UMjXE8BGSgPSlvGSuLDacgxEJmYkro5JLIxEOeVleBg73UGK', '2026-05-29 17:00:17', 1, '2026-05-29 16:58:17'),
(6, 'mand', 4, 'login', '$2y$12$Cb1cpB7ouSlnjh7enkCNKuWJYkZZE5jP/wgWOzmDECUaN6fTn8Q5K', '2026-06-02 14:54:08', 1, '2026-06-02 14:52:08'),
(7, 'mand', 10, 'login', '$2y$12$IiR6GXAPFhFDEl9daDfjLutn5Jhuxy5rJVwwiKOIlh4KLegKt5WcW', '2026-06-02 14:56:13', 1, '2026-06-02 14:54:13'),
(8, 'mand', 5, 'login', '$2y$12$oAOYYxBzd61HR7nnU4zTqOHDRe8sUT//AR5ZPHmHOQPTRyVyKkY3u', '2026-05-30 06:14:31', 1, '2026-05-30 06:12:31'),
(9, 'mand', 12, 'login', '$2y$12$1miPl1KDcStmJ4azJNYJn./U0JByZsRZ7XF.oduG2Ov/SeB4kIR/K', '2026-05-30 06:48:06', 1, '2026-05-30 06:46:06'),
(10, 'mand', 6, 'login', '$2y$12$rdqo.D2F3voHVvv1fWdkCu0DjmnlmZfqO3MIGLjE/oBcVVDBfZxBq', '2026-06-21 17:37:12', 1, '2026-06-21 17:35:12'),
(11, 'mand', 13, 'login', '$2y$12$PapVYW8XDUEPrQKkIfUEEua9Urlz5tgfpAmgL.VXbBJ7A5WpqPmke', '2026-05-30 06:55:30', 1, '2026-05-30 06:53:30'),
(12, 'mand', 14, 'login', '$2y$12$dZu7eIo2/z26vj4lk50VQOJR5E/VCV7V.tRvXJMUlVrQizLxztrxK', '2026-06-21 15:40:30', 1, '2026-06-21 15:38:30'),
(13, 'mand', 15, 'login', '$2y$12$oxyyhovo.UOohFjrqnzO/.bK/QSlbt7U5ozSAEVfWez9ATBpnQ.ia', '2026-06-21 18:31:47', 1, '2026-06-21 18:29:47'),
(14, 'mand', 16, 'login', '$2y$12$5KJVljPixwQyThSg5n3ALuxaVxJXgO1daRHFHmEJoRyM/3E7ieXVu', '2026-06-23 12:22:15', 1, '2026-06-23 12:20:15'),
(15, 'cust', 14, 'login', '$2y$12$FLpLa0RXaKWu/8JRCtyJsOQ5lPYAQ2s.ivVybs8UwtdwMS8YAsby6', '2026-06-17 12:15:33', 1, '2026-06-17 12:13:33'),
(16, 'cust', 17, 'login', '$2y$12$MNKDhzmwk9UCUKtQKMI80uGiyU5CY4nFfp5cgAQSpy/cMJyoUYOTi', '2026-06-13 09:30:03', 1, '2026-06-13 09:28:03'),
(17, 'cust', 21, 'login', '$2y$12$p485UanJQZKvDmenF/qjvOqR./ePCui5MAtAXfpt7UWyjmCD8RRi2', '2026-06-19 09:09:46', 1, '2026-06-19 09:07:46'),
(18, 'cust', 15, 'login', '$2y$12$nvI7D0omLw0fCa04ff0ywO89hBtz6RmzbmgebCbNF0Xj0gSd8.9Ja', '2026-06-27 05:01:48', 0, '2026-06-27 04:59:48'),
(19, 'mand', 17, 'login', '$2y$12$p.67r/4jmUml.sci6joUX.FQIfL/ZoY8FUWk1IJFY8/Sz2/OxxesO', '2026-06-17 09:59:01', 1, '2026-06-17 09:57:01'),
(20, 'mand', 19, 'login', '$2y$12$bs1A4pP3TOSAGOmaLK0.oeKsE0OELKmvcteVfXp6dtWj9nbr0SymO', '2026-06-23 10:02:47', 1, '2026-06-23 10:00:47'),
(21, 'mand', 20, 'login', '$2y$12$FqRpar6.B.EPktRyNpFON.QxYC6QPsysnpiqgwBmoBSVn4hq0d6Hu', '2026-06-21 18:41:59', 1, '2026-06-21 18:39:59'),
(22, 'cust', 25, 'login', '$2y$12$UHg5s1DVFlf3E5QYeaI0G.i7JwrrBCTWPs9iH8jAAuaUryFUJh9h2', '2026-06-21 14:35:36', 1, '2026-06-21 14:33:36'),
(23, 'syst', 5, 'login', '$2y$12$ZC7.pGg7vNt4bttwqKHn8OO9LPtgk7/N3sBnWjYzv2MCcS5vWy4gy', '2026-06-22 16:44:59', 1, '2026-06-22 16:42:59'),
(24, 'cust', 26, 'login', '$2y$12$/r3Ob754VcqSuM2X1aoL7OPRjvaumaWGw6jakOKXzRd0JmYGhM1R2', '2026-06-21 17:56:05', 1, '2026-06-21 17:54:05'),
(25, 'mand', 21, 'login', '$2y$12$zY6e.T.4tUDc2ZySBi/QceLLTnQSVp0NRcjecv2NFm7bulFXV/O7e', '2026-06-22 13:44:14', 1, '2026-06-22 13:42:14'),
(26, 'syst', 6, 'login', '$2y$12$NkmsUz1LlM5czGuEGYe6Ce0fdUq0QJR1VslO5prSVfTnsSsQO7VW.', '2026-06-22 13:59:17', 1, '2026-06-22 13:57:17'),
(27, 'mand', 22, 'login', '$2y$12$3YqAdBSaVu6cymOoAN/2IOi.ni3/Pwjbv71wNCMls0WJUcEHwylZG', '2026-06-22 15:29:45', 1, '2026-06-22 15:27:46'),
(28, 'cust', 30, 'login', '$2y$12$99YJDoT2DcolqOrEXjidXeMPoSH1l9sgzPz1HfCd9vAjDbXwq53mO', '2026-06-22 15:27:35', 1, '2026-06-22 15:25:35'),
(29, 'mand', 23, 'login', '$2y$12$Kl4mt0sQ4ZKTr2E8gQ5KquFoy.BePG1tSIDwcoNBxwIjuAWsniTUq', '2026-06-25 11:24:46', 1, '2026-06-25 11:22:46'),
(30, 'cust', 31, 'login', '$2y$12$2WWm1JiYXVfFP23iKnkrLe9xBz/rrU.9WXxkt3kue9Eu6vdFTjUvq', '2026-06-22 16:15:41', 1, '2026-06-22 16:13:41'),
(31, 'syst', 7, 'login', '$2y$12$SM92OK5/3PAa/iLq2ZJGWeGzpvvS.YL/pAet7H2O6HAf765YleZDy', '2026-06-29 17:08:57', 1, '2026-06-29 17:06:57'),
(32, 'syst', 9, 'login', '$2y$12$29zN27aGDU0UeIiLH2G0nOLYacQcifirkJwjEvPzxvyiM9clB5uSi', '2026-06-22 16:55:29', 1, '2026-06-22 16:53:29'),
(33, 'syst', 10, 'login', '$2y$12$8Jn4Dg7WoKINUZR3rhnMlOxRn67U5.H.g5QtaPXD78A9tyiU98KOS', '2026-06-22 16:58:43', 1, '2026-06-22 16:56:43'),
(34, 'syst', 11, 'login', '$2y$12$0XrC.PRiZ/98.BEK5khyx.Zf5qeWvOjyegg67fRKqpFuMxR8Hgid.', '2026-06-27 05:23:02', 1, '2026-06-27 05:21:02'),
(35, 'mand', 24, 'login', '$2y$12$g1gfxF928lHGcKlGYBlU0unlZ8rk2aBxlA3VLDbI5vDTjshRtAtli', '2026-06-23 10:19:45', 0, '2026-06-23 10:17:45'),
(36, 'mand', 25, 'login', '$2y$12$8FvWQxMxXGkD08C2tEK.j.RhR8DJSrL1S.FhaQZ6x2gSxkC47qDdW', '2026-06-23 11:15:42', 0, '2026-06-23 11:13:42'),
(37, 'mand', 26, 'login', '$2y$12$9KBjdkxGf6QzlsytreK1veKrAHyjIeu4YWRDbiMFgz4.cU/5D7zNy', '2026-06-25 11:18:02', 1, '2026-06-25 11:16:02'),
(38, 'mand', 27, 'login', '$2y$12$KByXE7firCdBvbS8TzOUWuNpJhuiMSoadKHVeWL6EFnxAPSqaqhUe', '2026-06-25 16:59:55', 1, '2026-06-25 16:57:55'),
(39, 'mand', 28, 'login', '$2y$12$sMOEL/Rt4LZd92natf7KN.OJ6pWWU3jwcObZSN2Xfx5oywdc047xS', '2026-06-29 16:03:12', 1, '2026-06-29 16:01:12'),
(40, 'syst', 12, 'login', '$2y$12$DjUWuxRxrFIcAezzCaUhsOY4QK1UaQx.KEoV0cBVNGA8AVv4UK7xm', '2026-06-29 16:18:45', 1, '2026-06-29 16:16:45'),
(41, 'mand', 29, 'login', '$2y$12$KbLYG5CGlXwvvFAWI/iHIevCaQbR5/k10aTVPRyDLYUuzcNHoq/gW', '2026-06-25 17:18:03', 1, '2026-06-25 17:16:03'),
(42, 'syst', 13, 'login', '$2y$12$48PSWZt5NpyMo/GoQW51lOitRxdJkAvRFs0y7dSDCK/3nrK8oFa3S', '2026-06-25 17:16:24', 1, '2026-06-25 17:14:24'),
(43, 'mand', 30, 'login', '$2y$12$zEmiBh6PyFh83s2Wd.LkKOkwAUYILIQfbTljjWQzS6HAesPmzHdHG', '2026-06-25 17:04:12', 1, '2026-06-25 17:02:12'),
(44, 'cust', 27, 'login', '$2y$12$zzUpwk3D.d1KozN6AYOtuu7v8aAzejFv93Sy8hs9AZxh3AkjS2Rcy', '2026-06-27 05:41:33', 1, '2026-06-27 05:39:33'),
(45, 'cust', 34, 'login', '$2y$12$oBHIcATNuTS8qpaSwju6TuR.7YTNRy1JYhqT1t5AYrC2z2/d7LQ6q', '2026-06-29 17:06:57', 1, '2026-06-29 17:04:57'),
(46, 'cust', 35, 'login', '$2y$12$MdbFPIYaVSknwZmNTG/kCe08THTygE8.GLK0SFezZoVElBbODdzWS', '2026-06-29 16:00:51', 1, '2026-06-29 15:58:51'),
(47, 'mand', 32, 'login', '$2y$12$CavJGH7EhhoTVRsSLlNxbeFOh0Ac3fTAcCqzEtQDIY9XNs0Z957L2', '2026-06-29 13:23:32', 1, '2026-06-29 13:21:32'),
(48, 'syst', 15, 'login', '$2y$12$05ieGkB2xQfZKuzI/POL9esn9r7RRfV64je.iXF.SsyfHPFJR5s.6', '2026-06-29 13:49:25', 1, '2026-06-29 13:47:26'),
(49, 'cust', 36, 'login', '$2y$12$lsgIRxGvuff7K2ZMVLA5yOi.yMAt4UhoMk/1UMdC5U539T4tvxANe', '2026-06-29 13:17:55', 1, '2026-06-29 13:15:55'),
(50, 'mand', 31, 'login', '$2y$12$Nbe0k5COJm6VA5nPNeKwJuj71npOE2ESitH8pntJIxXat9H6OMFfy', '2026-06-29 15:53:03', 1, '2026-06-29 15:51:03');

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
  MODIFY `invite_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=72;

--
-- AUTO_INCREMENT für Tabelle `pw_list`
--
ALTER TABLE `pw_list`
  MODIFY `pwlist_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT für Tabelle `session`
--
ALTER TABLE `session`
  MODIFY `sess_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1732;

--
-- AUTO_INCREMENT für Tabelle `twofa_code`
--
ALTER TABLE `twofa_code`
  MODIFY `tfa_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=51;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
