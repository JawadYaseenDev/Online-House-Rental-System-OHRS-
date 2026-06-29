-- ============================================================
-- Online House Rental System (OHRS) — Database Schema
-- Version: 1.0
-- Engine: InnoDB | Charset: utf8mb4
-- ============================================================

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";

CREATE DATABASE IF NOT EXISTS `ohrs` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `ohrs`;

-- --------------------------------------------------------
-- Table: users
-- --------------------------------------------------------
CREATE TABLE `users` (
  `id`             INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `first_name`     VARCHAR(60)  NOT NULL,
  `last_name`      VARCHAR(60)  NOT NULL,
  `cnic`           VARCHAR(15)  NOT NULL UNIQUE,
  `dob`            DATE         NOT NULL,
  `email`          VARCHAR(120) NOT NULL UNIQUE,
  `phone`          VARCHAR(20)  NOT NULL,
  `password`       VARCHAR(255) NOT NULL,
  `profile_pic`    VARCHAR(255) DEFAULT 'default-avatar.png',
  `family_members` TINYINT(3) UNSIGNED DEFAULT 1,
  `role`           ENUM('customer','admin') DEFAULT 'customer',
  `status`         ENUM('active','inactive','banned') DEFAULT 'active',
  `remember_token` VARCHAR(100) DEFAULT NULL,
  `created_at`     DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at`     DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_email` (`email`),
  INDEX `idx_role`  (`role`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table: houses
-- --------------------------------------------------------
CREATE TABLE `houses` (
  `id`          INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `title`       VARCHAR(150) NOT NULL,
  `description` TEXT,
  `location`    VARCHAR(120) NOT NULL,
  `address`     VARCHAR(255) NOT NULL,
  `rent`        DECIMAL(10,2) NOT NULL,
  `capacity`    TINYINT(3) UNSIGNED DEFAULT 1,
  `bedrooms`    TINYINT(3) UNSIGNED DEFAULT 1,
  `bathrooms`   TINYINT(3) UNSIGNED DEFAULT 1,
  `area`        DECIMAL(8,2) DEFAULT NULL COMMENT 'in sq ft',
  `status`      ENUM('available','reserved','occupied','inactive') DEFAULT 'available',
  `amenities`   TEXT COMMENT 'JSON-encoded array of amenity strings',
  `created_at`  DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at`  DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_status`   (`status`),
  INDEX `idx_location` (`location`),
  INDEX `idx_rent`     (`rent`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table: house_images
-- --------------------------------------------------------
CREATE TABLE `house_images` (
  `id`         INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `house_id`   INT(11) UNSIGNED NOT NULL,
  `image_path` VARCHAR(255) NOT NULL,
  `is_primary` TINYINT(1) DEFAULT 0,
  `sort_order` TINYINT(3) UNSIGNED DEFAULT 0,
  PRIMARY KEY (`id`),
  CONSTRAINT `fk_hi_house` FOREIGN KEY (`house_id`) REFERENCES `houses`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table: reservations
-- --------------------------------------------------------
CREATE TABLE `reservations` (
  `id`         INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id`    INT(11) UNSIGNED NOT NULL,
  `house_id`   INT(11) UNSIGNED NOT NULL,
  `start_date` DATE NOT NULL,
  `end_date`   DATE DEFAULT NULL,
  `status`     ENUM('pending','approved','cancelled','completed') DEFAULT 'pending',
  `notes`      TEXT DEFAULT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  CONSTRAINT `fk_res_user`  FOREIGN KEY (`user_id`)  REFERENCES `users`(`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_res_house` FOREIGN KEY (`house_id`) REFERENCES `houses`(`id`) ON DELETE CASCADE,
  INDEX `idx_res_status` (`status`),
  INDEX `idx_res_user`   (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table: payments
-- --------------------------------------------------------
CREATE TABLE `payments` (
  `id`             INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `reservation_id` INT(11) UNSIGNED NOT NULL,
  `user_id`        INT(11) UNSIGNED NOT NULL,
  `account_number` VARCHAR(30) NOT NULL,
  `amount`         DECIMAL(10,2) NOT NULL,
  `transaction_id` VARCHAR(100) NOT NULL,
  `payment_date`   DATE NOT NULL,
  `status`         ENUM('pending','paid','failed') DEFAULT 'pending',
  `notes`          VARCHAR(255) DEFAULT NULL,
  `created_at`     DATETIME DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  CONSTRAINT `fk_pay_res`  FOREIGN KEY (`reservation_id`) REFERENCES `reservations`(`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_pay_user` FOREIGN KEY (`user_id`)        REFERENCES `users`(`id`) ON DELETE CASCADE,
  INDEX `idx_pay_status` (`status`),
  INDEX `idx_pay_user`   (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table: feedback
-- --------------------------------------------------------
CREATE TABLE `feedback` (
  `id`         INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id`    INT(11) UNSIGNED NOT NULL,
  `house_id`   INT(11) UNSIGNED DEFAULT NULL,
  `rating`     TINYINT(1) UNSIGNED DEFAULT 5 COMMENT '1–5 stars',
  `review`     VARCHAR(255) DEFAULT NULL,
  `comment`    TEXT DEFAULT NULL,
  `suggestion` TEXT DEFAULT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  CONSTRAINT `fk_fb_user`  FOREIGN KEY (`user_id`)  REFERENCES `users`(`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_fb_house` FOREIGN KEY (`house_id`) REFERENCES `houses`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table: offers
-- --------------------------------------------------------
CREATE TABLE `offers` (
  `id`           INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `house_id`     INT(11) UNSIGNED DEFAULT NULL,
  `title`        VARCHAR(150) NOT NULL,
  `description`  TEXT DEFAULT NULL,
  `discount_pct` DECIMAL(5,2) DEFAULT 0.00,
  `start_date`   DATE NOT NULL,
  `end_date`     DATE NOT NULL,
  `status`       ENUM('active','expired','inactive') DEFAULT 'active',
  `created_at`   DATETIME DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  CONSTRAINT `fk_off_house` FOREIGN KEY (`house_id`) REFERENCES `houses`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table: advertisements
-- --------------------------------------------------------
CREATE TABLE `advertisements` (
  `id`         INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `title`      VARCHAR(150) NOT NULL,
  `image`      VARCHAR(255) NOT NULL,
  `link`       VARCHAR(255) DEFAULT NULL,
  `position`   ENUM('header','sidebar','footer','home') DEFAULT 'home',
  `status`     ENUM('active','inactive') DEFAULT 'active',
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table: contact_messages
-- --------------------------------------------------------
CREATE TABLE `contact_messages` (
  `id`         INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name`       VARCHAR(100) NOT NULL,
  `email`      VARCHAR(120) NOT NULL,
  `subject`    VARCHAR(200) NOT NULL,
  `message`    TEXT NOT NULL,
  `is_read`    TINYINT(1) DEFAULT 0,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- SEED DATA
-- ============================================================

-- Admin user  (password: Admin@123)
INSERT INTO `users` (`first_name`,`last_name`,`cnic`,`dob`,`email`,`phone`,`password`,`role`,`family_members`) VALUES
('System','Admin','35201-1234567-1','1990-01-01','admin@ohrs.com','+92-300-0000000',
 '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','admin',1);

-- Test customer  (password: Test@1234)
INSERT INTO `users` (`first_name`,`last_name`,`cnic`,`dob`,`email`,`phone`,`password`,`role`,`family_members`) VALUES
('Ali','Hassan','35201-9876543-2','1995-06-15','ali@example.com','+92-321-1234567',
 '$2y$10$TKh8H1.PfuphPBtRcAlvIeUJEbMiYpfm5p.GBPlwFqHaVEFgZb3Oi','customer',4);

-- Sample houses
INSERT INTO `houses` (`title`,`description`,`location`,`address`,`rent`,`capacity`,`bedrooms`,`bathrooms`,`area`,`status`,`amenities`) VALUES
('Modern Family Villa',
 'A spacious and beautifully designed family villa in a prime location. This property features high ceilings, large windows, and an open-concept living area. Perfect for families seeking comfort and style.',
 'Lahore','House 12, Block C, DHA Phase 5, Lahore',85000.00,8,4,3,3200.00,'available',
 '["WiFi","Air Conditioning","Generator Backup","Parking (2 Cars)","Garden","Security Camera","Water Tank","Natural Gas"]'),

('Cozy Studio Apartment',
 'A well-furnished studio apartment ideal for students or working professionals. Located in the heart of the city with easy access to public transport, markets, and universities.',
 'Lahore','Flat 3B, Gulberg II, Lahore',25000.00,2,1,1,650.00,'available',
 '["WiFi","Air Conditioning","Kitchenette","Laundry Access","24/7 Security"]'),

('Executive 3-Bedroom Apartment',
 'Premium executive apartment with modern fittings and a stunning city view. Fully furnished with high-end appliances. Ideal for professionals and small families.',
 'Islamabad','Apartment 7, F-7/4, Islamabad',65000.00,5,3,2,1800.00,'available',
 '["WiFi","Air Conditioning","Generator Backup","Parking","Gym Access","Swimming Pool","Elevator","24/7 Security"]'),

('Spacious Corner House',
 'A large corner house situated in a quiet, family-friendly neighborhood. Offers ample outdoor space, a beautiful garden, and a rooftop terrace with panoramic views.',
 'Karachi','House 88, Clifton Block 4, Karachi',95000.00,10,5,4,4000.00,'reserved',
 '["WiFi","Air Conditioning","Generator Backup","Parking (3 Cars)","Rooftop Terrace","Garden","CCTV","Servant Quarter"]'),

('Budget 2-Bedroom Flat',
 'An affordable and clean 2-bedroom flat in a well-connected area. Suitable for small families and couples. Close to schools, hospitals, and shopping centers.',
 'Rawalpindi','Flat 12, Satellite Town, Rawalpindi',22000.00,4,2,1,900.00,'available',
 '["WiFi","Ceiling Fans","Water Tank","UPS Backup","Street Parking"]'),

('Luxury Penthouse',
 'An ultra-luxury penthouse offering the finest living experience. Features include a private terrace, premium Italian marble flooring, smart home automation, and panoramic skyline views.',
 'Islamabad','Penthouse, Centaurus, F-8, Islamabad',250000.00,6,4,4,5500.00,'available',
 '["WiFi","Smart Home Automation","Air Conditioning","Generator","Parking (4 Cars)","Gym","Pool","Concierge","City Views"]'),

('Townhouse with Garden',
 'A charming 3-story townhouse featuring a private garden and terrace. Located in a gated community with 24/7 security and excellent amenities.',
 'Lahore','Townhouse 5, Lake City, Lahore',75000.00,6,3,3,2200.00,'available',
 '["WiFi","Air Conditioning","Garden","Parking (2 Cars)","Gated Community","Backup Generator","CCTV"]'),

('Single Room Apartment',
 'A compact, affordable single room apartment perfect for solo travelers or students. Well-maintained building with reliable utilities.',
 'Karachi','Room 2, Block A, North Nazimabad, Karachi',12000.00,1,1,1,400.00,'available',
 '["WiFi","Ceiling Fan","Shared Parking","Water Supply"]');

-- House images (using placeholder paths — replace with actual upload paths)
INSERT INTO `house_images` (`house_id`,`image_path`,`is_primary`,`sort_order`) VALUES
(1,'house1_main.jpg',1,0),(1,'house1_living.jpg',0,1),(1,'house1_kitchen.jpg',0,2),
(2,'house2_main.jpg',1,0),(2,'house2_room.jpg',0,1),
(3,'house3_main.jpg',1,0),(3,'house3_view.jpg',0,1),(3,'house3_kitchen.jpg',0,2),
(4,'house4_main.jpg',1,0),(4,'house4_garden.jpg',0,1),
(5,'house5_main.jpg',1,0),
(6,'house6_main.jpg',1,0),(6,'house6_terrace.jpg',0,1),(6,'house6_interior.jpg',0,2),
(7,'house7_main.jpg',1,0),(7,'house7_garden.jpg',0,1),
(8,'house8_main.jpg',1,0);

-- Sample reservation
INSERT INTO `reservations` (`user_id`,`house_id`,`start_date`,`end_date`,`status`,`notes`) VALUES
(2,4,'2026-07-01','2027-06-30','approved','Approved reservation for the Karachi corner house.');

-- Sample payment
INSERT INTO `payments` (`reservation_id`,`user_id`,`account_number`,`amount`,`transaction_id`,`payment_date`,`status`) VALUES
(1,2,'PK36SCBL0000001123456702',95000.00,'TXN20260701001','2026-07-01','paid');

-- Sample feedback
INSERT INTO `feedback` (`user_id`,`house_id`,`rating`,`review`,`comment`) VALUES
(2,4,5,'Excellent property!','The house is exactly as described. Very clean and well-maintained. Highly recommend OHRS for rental services.');

-- Sample offers
INSERT INTO `offers` (`house_id`,`title`,`description`,`discount_pct`,`start_date`,`end_date`,`status`) VALUES
(1,'Summer Special — 10% Off','Book the Modern Family Villa this summer and enjoy a 10% discount on your first month rent.',10.00,'2026-07-01','2026-08-31','active'),
(3,'Executive Apartment Deal','Move into the Executive Apartment with a 15% discount for a 6-month commitment.',15.00,'2026-07-01','2026-09-30','active'),
(NULL,'New Member Welcome Offer','Register today and get 5% off your first reservation on any available property.',5.00,'2026-06-01','2026-12-31','active');

-- Sample contact message
INSERT INTO `contact_messages` (`name`,`email`,`subject`,`message`) VALUES
('Ahmed Khan','ahmed@example.com','Inquiry about DHA Property','I would like to know more details about the villa in DHA Phase 5. Please contact me at your earliest convenience.');

COMMIT;
