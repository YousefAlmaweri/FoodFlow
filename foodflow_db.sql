-- FoodFlow Database Export
-- CIT6224 Group 19 | Import via phpMyAdmin or mysql CLI
-- NOTE: Use setup_db.php for correct password hashes (recommended)
-- This file is provided as a backup schema reference.

SET FOREIGN_KEY_CHECKS = 0;

CREATE DATABASE IF NOT EXISTS `foodflow_db`
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE `foodflow_db`;

DROP TABLE IF EXISTS `order_items`;
DROP TABLE IF EXISTS `orders`;
DROP TABLE IF EXISTS `menu_items`;
DROP TABLE IF EXISTS `restaurants`;
DROP TABLE IF EXISTS `users`;

CREATE TABLE `users` (
  `id`         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `name`       VARCHAR(100) NOT NULL,
  `email`      VARCHAR(100) NOT NULL UNIQUE,
  `phone`      VARCHAR(20)  DEFAULT NULL,
  `password`   VARCHAR(255) NOT NULL,
  `role`       ENUM('customer','partner','admin','rider') NOT NULL DEFAULT 'customer',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `restaurants` (
  `id`                INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `partner_id`        INT UNSIGNED NOT NULL,
  `name`              VARCHAR(150) NOT NULL,
  `image`             TEXT DEFAULT NULL,
  `cuisine`           VARCHAR(80) NOT NULL,
  `rating`            DECIMAL(3,1) NOT NULL DEFAULT 0.0,
  `min_order`         DECIMAL(8,2) NOT NULL DEFAULT 0.00,
  `est_delivery_time` INT NOT NULL DEFAULT 30,
  `created_at`        TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`partner_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `menu_items` (
  `id`            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `restaurant_id` INT UNSIGNED NOT NULL,
  `name`          VARCHAR(150) NOT NULL,
  `description`   TEXT DEFAULT NULL,
  `price`         DECIMAL(8,2) NOT NULL,
  `image`         TEXT DEFAULT NULL,
  `is_available`  TINYINT(1) NOT NULL DEFAULT 1,
  `created_at`    TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`restaurant_id`) REFERENCES `restaurants`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `orders` (
  `id`               INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `customer_id`      INT UNSIGNED NOT NULL,
  `restaurant_id`    INT UNSIGNED NOT NULL,
  `total_amount`     DECIMAL(10,2) NOT NULL,
  `delivery_fee`     DECIMAL(8,2) NOT NULL DEFAULT 5.00,
  `delivery_address` TEXT NOT NULL,
  `payment_method`   ENUM('cash','card') NOT NULL DEFAULT 'cash',
  `status`           ENUM('pending','accepted','rejected','picking_up','delivering','delivered') NOT NULL DEFAULT 'pending',
  `created_at`       TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`customer_id`)   REFERENCES `users`(`id`)       ON DELETE CASCADE,
  FOREIGN KEY (`restaurant_id`) REFERENCES `restaurants`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `order_items` (
  `id`           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `order_id`     INT UNSIGNED NOT NULL,
  `menu_item_id` INT UNSIGNED NOT NULL,
  `quantity`     INT UNSIGNED NOT NULL DEFAULT 1,
  `unit_price`   DECIMAL(8,2) NOT NULL,
  `subtotal`     DECIMAL(10,2) NOT NULL,
  FOREIGN KEY (`order_id`)     REFERENCES `orders`(`id`)     ON DELETE CASCADE,
  FOREIGN KEY (`menu_item_id`) REFERENCES `menu_items`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;

-- ⚠️  Run setup_db.php instead of importing this file
-- to get correctly hashed passwords for all seed users.
