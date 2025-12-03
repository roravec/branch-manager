-- todo: toto neni pekne riesenie mat tu natvrdo nazov db, najlepsie by bolo to renderovat podla DatabaseConfig_template.php
CREATE DATABASE IF NOT EXISTS `webapp`
  DEFAULT CHARACTER SET utf8mb4
  DEFAULT COLLATE utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `branch` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(255) NOT NULL,
  `coordinates` VARCHAR(255) DEFAULT NULL,
  `address` VARCHAR(255) DEFAULT NULL,
  `address2` VARCHAR(255) DEFAULT NULL,
  `description` TEXT DEFAULT NULL,
  `employees` INT UNSIGNED DEFAULT 0,
  `utilization` INT UNSIGNED DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- todo: doplnit ostatne tabulky + init data do nich
