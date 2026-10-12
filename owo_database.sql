-- ============================================================
-- OWO E-Commerce — Database Schema & Seed Data
-- Import via phpMyAdmin or: mysql -u root -p < owo_database.sql
-- ============================================================

CREATE DATABASE IF NOT EXISTS `owo_store`
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE `owo_store`;

-- ─── Users (customers) ───────────────────────────────────────
CREATE TABLE IF NOT EXISTS `users` (
  `id`           INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name`         VARCHAR(120) NOT NULL,
  `email`        VARCHAR(191) NOT NULL UNIQUE,
  `password_hash`VARCHAR(255) NOT NULL,
  `phone`        VARCHAR(20)  DEFAULT NULL,
  `created_at`   TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─── Admins ──────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `admins` (
  `id`           INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `username`     VARCHAR(80)  NOT NULL UNIQUE,
  `password_hash`VARCHAR(255) NOT NULL,
  `created_at`   TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─── Products ─────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `products` (
  `id`          INT UNSIGNED  NOT NULL AUTO_INCREMENT,
  `name`        VARCHAR(191)  NOT NULL,
  `description` TEXT          DEFAULT NULL,
  `price`       DECIMAL(10,2) NOT NULL,
  `stock`       INT UNSIGNED  NOT NULL DEFAULT 0,
  `category`    VARCHAR(80)   NOT NULL DEFAULT 'uncategorized',
  `sizes`       VARCHAR(50)   NOT NULL DEFAULT 'S,M,L,XL',
  `image_path`  VARCHAR(255)  DEFAULT NULL,
  `is_deleted`  TINYINT(1)    NOT NULL DEFAULT 0,
  `created_at`  TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_category` (`category`),
  INDEX `idx_price`    (`price`),
  INDEX `idx_deleted`  (`is_deleted`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─── Orders ───────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `orders` (
  `id`                   INT UNSIGNED  NOT NULL AUTO_INCREMENT,
  `user_id`              INT UNSIGNED  DEFAULT NULL,
  `guest_name`           VARCHAR(120)  DEFAULT NULL,
  `guest_email`          VARCHAR(191)  DEFAULT NULL,
  `guest_phone`          VARCHAR(20)   DEFAULT NULL,
  `shipping_address`     TEXT          DEFAULT NULL,
  `total_amount`         DECIMAL(10,2) NOT NULL,
  `khalti_pidx`          VARCHAR(100)  DEFAULT NULL,
  `khalti_transaction_id`VARCHAR(100)  DEFAULT NULL,
  `status`               ENUM('pending','paid','shipped','delivered','cancelled')
                         NOT NULL DEFAULT 'pending',
  `created_at`           TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_user`   (`user_id`),
  INDEX `idx_status` (`status`),
  CONSTRAINT `fk_order_user` FOREIGN KEY (`user_id`)
    REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─── Order Items ──────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `order_items` (
  `id`               INT UNSIGNED  NOT NULL AUTO_INCREMENT,
  `order_id`         INT UNSIGNED  NOT NULL,
  `product_id`       INT UNSIGNED  DEFAULT NULL,
  `product_name`     VARCHAR(191)  NOT NULL,
  `quantity`         INT UNSIGNED  NOT NULL DEFAULT 1,
  `size`             VARCHAR(10)   NOT NULL,
  `price_at_purchase`DECIMAL(10,2) NOT NULL,
  PRIMARY KEY (`id`),
  INDEX `idx_order` (`order_id`),
  CONSTRAINT `fk_item_order`   FOREIGN KEY (`order_id`)
    REFERENCES `orders`(`id`)   ON DELETE CASCADE,
  CONSTRAINT `fk_item_product` FOREIGN KEY (`product_id`)
    REFERENCES `products`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- Seed Data
-- ============================================================

-- Default admin (password: admin123 — CHANGE IN PRODUCTION)
INSERT INTO `admins` (`username`, `password_hash`) VALUES
('admin', '$2y$10$evyYZnk4f8NfJU1kdWFhfuGw/./jBPhWXyYb7oZVyUk7u/lUaB2Va');

-- Sample products (images will use placeholder until uploaded)
INSERT INTO `products` (`name`, `description`, `price`, `stock`, `category`, `sizes`, `image_path`) VALUES
('OWO Essential Tee',    'Our signature heavyweight cotton tee. Cut boxy, sits perfectly. 100% ring-spun cotton.', 1999.00, 50, 'tees',     'S,M,L,XL',    NULL),
('OWO Cargo Pant',       'Relaxed fit cargo in washed black twill. Six pockets, tapered leg, zip fly.',            4500.00, 30, 'bottoms',  'S,M,L,XL',    NULL),
('OWO Hoodie Classic',   'Heavyweight fleece hoodie. Dropped shoulders, kangaroo pocket, ribbed cuffs.',           5500.00, 25, 'hoodies',  'S,M,L,XL,XXL',NULL),
('OWO Logo Cap',         'Structured 6-panel cap in black twill. Embroidered OWO wordmark on front.',              1200.00, 100,'accessories','ONE SIZE',   NULL),
('OWO Oversized Shirt',  'Woven poplin shirt. Oversized fit, double breast pockets, pearl snap buttons.',          3200.00, 40, 'shirts',   'S,M,L,XL',    NULL),
('OWO Track Jacket',     'Vintage-inspired track jacket in recycled nylon. Contrast trim, full zip.',              6500.00, 20, 'outerwear','S,M,L,XL',    NULL),
('OWO Beanie',           'Ribbed acrylic beanie with subtle OWO embroidery. One size fits all.',                    800.00, 80, 'accessories','ONE SIZE',   NULL),
('OWO Longline Tee',     'Extended-length tee with curved hem. Washed black. 200gsm cotton.',                     2400.00, 35, 'tees',     'S,M,L,XL',    NULL);
