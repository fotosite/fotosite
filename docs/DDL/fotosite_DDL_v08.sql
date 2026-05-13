
	--------- USERDB ---------------------------------------------------



CREATE TABLE `syst_user`(
    `syst_id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `syst_uname` VARCHAR(255) NULL,
    `syst_email` VARCHAR(255) NOT NULL,
    `syst_tel` VARCHAR(255) NOT NULL,
    `syst_firstname` VARCHAR(255) NOT NULL,
    `syst_lastname` VARCHAR(255) NOT NULL,
    `syst_street+nr` VARCHAR(255) NOT NULL,
    `syst_pcode+city` VARCHAR(255) NOT NULL,
    `syst_company` VARCHAR(255) NOT NULL,
    `syst_pw_hash` VARCHAR(255) NOT NULL,
    PRIMARY KEY(`syst_id`)
);
ALTER TABLE
    `syst_user` ADD UNIQUE `syst_user_syst_uname_unique`(`syst_uname`);
ALTER TABLE
    `syst_user` ADD UNIQUE `syst_user_syst_email_unique`(`syst_email`);
ALTER TABLE
    `syst_user` ADD UNIQUE `syst_user_syst_tel_unique`(`syst_tel`);



CREATE TABLE `cust_user`(
    `cust_id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `cust_uname` VARCHAR(255) NULL,
    `cust_email` VARCHAR(255) NOT NULL,
    `cust_tel` VARCHAR(255) NOT NULL,
    `cust_firstname` VARCHAR(255) NOT NULL,
    `cust_lastname` VARCHAR(255) NOT NULL,
    `cust_street+nr` VARCHAR(255) NOT NULL,
    `cust_postcode_city` VARCHAR(255) NOT NULL,
    `cust_company` VARCHAR(255) NOT NULL,
    `cust_pw_hash` VARCHAR(255) NOT NULL,
    PRIMARY KEY(`cust_id`)
);
ALTER TABLE
    `cust_user` ADD UNIQUE `cust_user_cust_uname_unique`(`cust_uname`);
ALTER TABLE
    `cust_user` ADD UNIQUE `cust_user_cust_email_unique`(`cust_email`);
ALTER TABLE
    `cust_user` ADD UNIQUE `cust_user_cust_tel_unique`(`cust_tel`);


CREATE TABLE `mand_user`(
    `mand_id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `mand_uname` VARCHAR(255) NULL,
    `mand_email` VARCHAR(255) NOT NULL,
    `mand_tel` VARCHAR(255) NOT NULL,
    `mand_firstname` VARCHAR(255) NOT NULL,
    `mand_lastname` VARCHAR(255) NOT NULL,
    `mand_street+nr` VARCHAR(255) NOT NULL,
    `mand_postcode+city` VARCHAR(255) NOT NULL,
    `mand_company` VARCHAR(255) NOT NULL,
    `mand_pw_hash` VARCHAR(255) NOT NULL,
    `mand_prefstat` BIGINT NOT NULL,
    PRIMARY KEY(`mand_id`)
);
ALTER TABLE
    `mand_user` ADD UNIQUE `mand_user_mand_uname_unique`(`mand_uname`);
ALTER TABLE
    `mand_user` ADD UNIQUE `mand_user_mand_email_unique`(`mand_email`);
ALTER TABLE
    `mand_user` ADD UNIQUE `mand_user_mand_tel_unique`(`mand_tel`);
ALTER TABLE
    `mand_user` ADD INDEX `mand_user_mand_prefstat_index`(`mand_prefstat`);


CREATE TABLE `cust_pcode`(
    `pcode_id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `mand_id` BIGINT NOT NULL,
    `cust_id` BIGINT NOT NULL,
    `cust_passcode` VARCHAR(255) NOT NULL,
    `pcode_prefstat` BIGINT NOT NULL,
    PRIMARY KEY(`pcode_id`)
);
ALTER TABLE
    `cust_pcode` ADD INDEX `cust_pcode_mand_id_index`(`mand_id`);
ALTER TABLE
    `cust_pcode` ADD INDEX `cust_pcode_cust_id_index`(`cust_id`);


	ALTER TABLE
    `cust_pcode` ADD CONSTRAINT `cust_pcode_mand_id_foreign` FOREIGN KEY(`mand_id`) REFERENCES `mand_user`(`mand_id`);


	--------- SESSIONDB ---------------------------------------------------


CREATE TABLE `pw_list`(
    `pwlist_id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `mand_id` BIGINT NOT NULL,
    `pw1` VARCHAR(255) NOT NULL,
    `pw2` VARCHAR(255) NOT NULL,
    `pw3` VARCHAR(255) NOT NULL,
    `pw4` VARCHAR(255) NOT NULL,
    `pw5` VARCHAR(255) NOT NULL,
    `pw6` VARCHAR(255) NOT NULL,
    `valid_from` DATETIME NOT NULL,
    `valid_until` DATETIME NOT NULL,
    PRIMARY KEY(`pwlist_id`)
);
ALTER TABLE
    `pw_list` ADD INDEX `pw_list_mand_id_index`(`mand_id`);

CREATE TABLE `session`(
    `sess_id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `sess_token` VARCHAR(128) NOT NULL,
    `user_type` ENUM('anon', 'cust', 'mand', 'syst') NOT NULL,
    `syst_id` BIGINT UNSIGNED NULL,
    `mand_id` BIGINT UNSIGNED NULL COMMENT 'Nur für zuordsnung Mand-Admin-content, bleibt bei Cust-Uugriffen
unbenutzt',
    `cust_id` BIGINT UNSIGNED NULL,
    `cust_passcode` TINYINT UNSIGNED NOT NULL,
    `ip_hash` VARCHAR(64) NOT NULL,
    `ua_hash` VARCHAR(64) NOT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP(), `last_activity` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP(), `expires_at` DATETIME NOT NULL);
ALTER TABLE
    `session` ADD UNIQUE `session_sess_token_unique`(`sess_token`);

ALTER TABLE
    `session` ADD INDEX `session_expires_at_index`(`expires_at`);

gEHT NICHT WEGEN DB-TRENNUNG.
ALTER TABLE
    `pw_list` ADD CONSTRAINT `pw_list_mand_id_foreign` FOREIGN KEY(`mand_id`) REFERENCES `mand_user`(`mand_id`);


	------------ FOTODB ------------------------------------------------


CREATE TABLE `activity_group`(
    `ag_id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `ag_title` VARCHAR(255) NOT NULL,
    `ag_subtitle` VARCHAR(255) NOT NULL,
    `ag_text` VARCHAR(255) NOT NULL,
    `mand_id` BIGINT NOT NULL,
    `ag_sec_code` BIGINT NOT NULL,
    `ag_prefstat` BIGINT NOT NULL DEFAULT 50,
    PRIMARY KEY(`ag_id`)
);
ALTER TABLE
    `activity_group` ADD INDEX `activity_group_mand_id_index`(`mand_id`);


CREATE TABLE `activity_subgroup`(
    `asg_id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `asg_title` VARCHAR(255) NOT NULL,
    `asg_subtitle` VARCHAR(255) NOT NULL,
    `asg_text` VARCHAR(255) NOT NULL,
    `asg_public` BOOLEAN NOT NULL,
    `mand_id` BIGINT NOT NULL,
    `asg_sec_code` VARCHAR(255) NOT NULL,
    `ag_id` BIGINT NOT NULL,
    `asg_prefstat` BIGINT NOT NULL DEFAULT 50,
    `asg_date` DATE NOT NULL,
    PRIMARY KEY(`asg_id`)
);
ALTER TABLE
    `activity_subgroup` ADD INDEX `activity_subgroup_ag_id_index`(`ag_id`);
ALTER TABLE
    `activity_subgroup` ADD INDEX `activity_subgroup_mand_id_index`(`mand_id`);


CREATE TABLE `foto_obj`(
    `fo_id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `fo_is_video` BOOLEAN NOT NULL,
    `fo_filename` VARCHAR(255) NOT NULL,
    `fo_title` VARCHAR(255) NOT NULL,
    `fo_subtitle` VARCHAR(255) NOT NULL,
    `fo_text` VARCHAR(255) NOT NULL,
    `mand_id` BIGINT NOT NULL,
    `fo_sec_code` VARCHAR(255) NOT NULL,
    `fo_datetime` DATETIME NOT NULL,
    `db_saved` BOOLEAN NOT NULL,
    `fo_filepath` VARCHAR(255) NOT NULL,
    `fo_prefstat` BIGINT NOT NULL DEFAULT 50,
    PRIMARY KEY(`fo_id`)
);


CREATE TABLE `ag_fo_context`(
    `ag_fo_id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `ag_banner` BOOLEAN NOT NULL,
    `ag_id` BIGINT NOT NULL,
    `fo_id` BIGINT NOT NULL,
    `ag_is_banner` BOOLEAN NOT NULL DEFAULT 0,
    PRIMARY KEY(`ag_fo_id`)
);
ALTER TABLE
    `ag_fo_context` ADD INDEX `ag_fo_context_ag_id_index`(`ag_id`);


CREATE TABLE `asg_fo_context`(
    `asg_fo_id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `asg_id` BIGINT NOT NULL,
    `fo_id` BIGINT NOT NULL,
    `ags_is_banner` BOOLEAN NOT NULL DEFAULT 0,
    PRIMARY KEY(`asg_fo_id`)
);
ALTER TABLE
    `asg_fo_context` ADD INDEX `asg_fo_context_asg_id_index`(`asg_id`);


CREATE TABLE `mand_profile`(
    `mp_id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `mand_id` BIGINT NOT NULL,
    `mp_name` BIGINT NOT NULL,
    `mp_title` BIGINT NOT NULL,
    `mp_text` BIGINT NOT NULL COMMENT 'Langtext mit Vorstellung des Mand',
    `mp_title_start` VARCHAR(255) NOT NULL COMMENT 'Überschrift für die Startseite',
    `mp_subtitle_start` VARCHAR(255) NOT NULL
);


CREATE TABLE `mp_fo_context`(
    `mp_fo_id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `mp_id` BIGINT NOT NULL,
    `fo_id` BIGINT NOT NULL,
    PRIMARY KEY(`mp_fo_id`)
);
ALTER TABLE
    `mp_fo_context` ADD INDEX `mp_fo_context_mp_id_index`(`mp_id`);


		------------ FOTOBLOBDB ------- NICHT IMPLEMENTIERT -----------------------------------------



CREATE TABLE `foto_obj_db`(
    `fod_id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `fo_id` BIGINT NOT NULL,
    `fod_obj` BLOB NOT NULL,
    PRIMARY KEY(`fod_id`)
);
ALTER TABLE
    `foto_obj_db` ADD INDEX `foto_obj_db_fo_id_index`(`fo_id`);




			------------ CONSTRAINTS ----------- NICHT IMPLEMENTIERT-------------------------------------



ALTER TABLE
    `activity_subgroup` ADD CONSTRAINT `activity_subgroup_ag_id_foreign` FOREIGN KEY(`ag_id`) REFERENCES `activity_group`(`ag_id`);
ALTER TABLE
    `asg_fo_context` ADD CONSTRAINT `asg_fo_context_fo_id_foreign` FOREIGN KEY(`fo_id`) REFERENCES `foto_obj`(`fo_id`);
ALTER TABLE
    `mp_fo_context` ADD CONSTRAINT `mp_fo_context_fo_id_foreign` FOREIGN KEY(`fo_id`) REFERENCES `foto_obj`(`fo_id`);
ALTER TABLE
    `ag_fo_context` ADD CONSTRAINT `ag_fo_context_fo_id_foreign` FOREIGN KEY(`fo_id`) REFERENCES `foto_obj`(`fo_id`);
ALTER TABLE
    `ag_fo_context` ADD CONSTRAINT `ag_fo_context_ag_id_foreign` FOREIGN KEY(`ag_id`) REFERENCES `activity_group`(`ag_id`);
ALTER TABLE
    `mp_fo_context` ADD CONSTRAINT `mp_fo_context_mp_id_foreign` FOREIGN KEY(`mp_id`) REFERENCES `mand_profile`(`mp_id`);
ALTER TABLE
    `asg_fo_context` ADD CONSTRAINT `asg_fo_context_asg_id_foreign` FOREIGN KEY(`asg_id`) REFERENCES `activity_subgroup`(`asg_id`);


GEHT NICHT WEGEN DB-TRENNUNG
ALTER TABLE
    `foto_obj` ADD CONSTRAINT `foto_obj_mand_id_foreign` FOREIGN KEY(`mand_id`) REFERENCES `mand_user`(`mand_id`);
ALTER TABLE
    `cust_pcode` ADD CONSTRAINT `cust_pcode_cust_id_foreign` FOREIGN KEY(`cust_id`) REFERENCES `cust_user`(`cust_id`);
ALTER TABLE
    `mand_profile` ADD CONSTRAINT `mand_profile_mand_id_foreign` FOREIGN KEY(`mand_id`) REFERENCES `mand_user`(`mand_id`);
ALTER TABLE
    `activity_group` ADD CONSTRAINT `activity_group_mand_id_foreign` FOREIGN KEY(`mand_id`) REFERENCES `mand_user`(`mand_id`);
ALTER TABLE
    `activity_subgroup` ADD CONSTRAINT `activity_subgroup_mand_id_foreign` FOREIGN KEY(`mand_id`) REFERENCES `mand_user`(`mand_id`);


-- ============================================================
-- Ergänzungen für WebAuthn / Passkey-Infrastruktur
-- id-Spalten als Trigger-Lösung (AUTO_INCREMENT nicht doppelt möglich)
-- Datum: [aktuelles Datum]
-- ============================================================

-- mand_user: id-Spalte anlegen
ALTER TABLE `mand_user`
    ADD COLUMN `id` BIGINT UNSIGNED NULL,
    ADD UNIQUE KEY `mand_user_id_unique`(`id`);

-- Trigger: id nach INSERT mit mand_id-Wert befüllen
CREATE TRIGGER mand_user_after_insert
AFTER INSERT ON `mand_user`
FOR EACH ROW
    UPDATE `mand_user` SET `id` = NEW.mand_id WHERE `mand_id` = NEW.mand_id;

-- cust_user: id-Spalte anlegen
ALTER TABLE `cust_user`
    ADD COLUMN `id` BIGINT UNSIGNED NULL,
    ADD UNIQUE KEY `cust_user_id_unique`(`id`);

-- Trigger: id nach INSERT mit cust_id-Wert befüllen
CREATE TRIGGER cust_user_after_insert
AFTER INSERT ON `cust_user`
FOR EACH ROW
    UPDATE `cust_user` SET `id` = NEW.cust_id WHERE `cust_id` = NEW.cust_id;