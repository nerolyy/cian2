-- Create database (run as a user with permission)
CREATE DATABASE IF NOT EXISTS `sss` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `sss`;

-- Properties table
CREATE TABLE IF NOT EXISTS `properties` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `title` VARCHAR(255) NOT NULL,
  `address` VARCHAR(255) NOT NULL,
  `metro` VARCHAR(100) DEFAULT NULL,
  `floor` VARCHAR(50) DEFAULT NULL,
  `purpose` VARCHAR(100) DEFAULT NULL, -- аптека, салон красоты и т.д.
  `area_sqm` DECIMAL(10,2) NOT NULL,
  `price_per_month` INT UNSIGNED NOT NULL,
  `image_url` VARCHAR(500) DEFAULT NULL,
  `contact_phone` VARCHAR(20) DEFAULT NULL,
  `description` TEXT NULL,
  `lessor_type` ENUM('owner','company') NOT NULL DEFAULT 'owner',
  `lessor_name` VARCHAR(190) DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_price` (`price_per_month`),
  KEY `idx_area` (`area_sqm`),
  KEY `idx_purpose` (`purpose`),
  KEY `idx_metro` (`metro`)
) ENGINE=InnoDB;



-- Users and roles
CREATE TABLE IF NOT EXISTS `users` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `email` VARCHAR(190) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `name` VARCHAR(100) DEFAULT NULL,
  `phone` VARCHAR(20) DEFAULT NULL,
  `role` ENUM('user','admin') NOT NULL DEFAULT 'user',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB;

-- Property images (gallery)
CREATE TABLE IF NOT EXISTS `property_images` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `property_id` INT UNSIGNED NOT NULL,
  `image_url` VARCHAR(500) NOT NULL,
  `sort_order` INT UNSIGNED NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `idx_property` (`property_id`),
  CONSTRAINT `fk_property_images_property` FOREIGN KEY (`property_id`) REFERENCES `properties`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Dictionary of purposes
CREATE TABLE IF NOT EXISTS `purposes` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(100) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_name` (`name`)
) ENGINE=InnoDB;

-- Seed default purposes
INSERT IGNORE INTO `purposes` (`name`) VALUES
('свободное назначение'),
('салон красоты'),
('аптека'),
('кафе'),
('пекарня'),
('кофейня'),
('магазин'),
('офис'),
('склад');

-- Seed purposes




