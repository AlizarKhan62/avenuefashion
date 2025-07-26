-- ========================================
-- ENHANCED E-COMMERCE DATABASE STRUCTURE
-- Fixed and Updated with all necessary tables
-- Resolves product_sizes table issues
-- Added debugging and enhanced functionality
-- ========================================

-- Create database if not exists
CREATE DATABASE IF NOT EXISTS `ecom_store` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `ecom_store`;
-- USE `if0_39556660_ecom_store`;

-- Disable foreign key checks temporarily
SET FOREIGN_KEY_CHECKS = 0;

-- Drop existing tables to ensure clean structure
DROP TABLE IF EXISTS `product_answers`;
DROP TABLE IF EXISTS `product_questions`;
DROP TABLE IF EXISTS `product_reviews`;
DROP TABLE IF EXISTS `recently_viewed`;
DROP TABLE IF EXISTS `wishlist`;
DROP TABLE IF EXISTS `product_sizes`;
DROP TABLE IF EXISTS `product_variants`;
DROP TABLE IF EXISTS `order_tracking`;
DROP TABLE IF EXISTS `pending_orders`;
DROP TABLE IF EXISTS `customer_orders`;
DROP TABLE IF EXISTS `cart`;
DROP TABLE IF EXISTS `payments`;
DROP TABLE IF EXISTS `products`;
DROP TABLE IF EXISTS `sizes`;
DROP TABLE IF EXISTS `colors`;
DROP TABLE IF EXISTS `manufacturers`;
DROP TABLE IF EXISTS `product_categories`;
DROP TABLE IF EXISTS `categories`;
DROP TABLE IF EXISTS `customers`;
DROP TABLE IF EXISTS `admins`;
DROP TABLE IF EXISTS `slider`;
DROP TABLE IF EXISTS `boxes_section`;
DROP TABLE IF EXISTS `terms`;
DROP TABLE IF EXISTS `about_us`;
DROP TABLE IF EXISTS `contact_us`;
DROP TABLE IF EXISTS `enquiry_types`;
DROP TABLE IF EXISTS `coupons`;
DROP TABLE IF EXISTS `admin_logs`;

-- --------------------------------------------------------
-- Table structure for table `admins`
-- --------------------------------------------------------

CREATE TABLE `admins` (
  `admin_id` int(10) NOT NULL AUTO_INCREMENT,
  `admin_name` varchar(255) NOT NULL,
  `admin_email` varchar(255) NOT NULL UNIQUE,
  `admin_pass` varchar(255) NOT NULL,
  `admin_image` text,
  `admin_contact` varchar(255),
  `admin_country` varchar(100),
  `admin_job` varchar(255),
  `admin_about` text,
  `admin_status` enum('active','inactive') DEFAULT 'active',
  `last_login` timestamp NULL,
  `created_date` timestamp DEFAULT CURRENT_TIMESTAMP,
  `updated_date` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`admin_id`),
  KEY `admin_status` (`admin_status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Table structure for table `categories`
-- --------------------------------------------------------

CREATE TABLE `categories` (
  `cat_id` int(10) NOT NULL AUTO_INCREMENT,
  `cat_title` varchar(255) NOT NULL,
  `cat_top` enum('yes','no') DEFAULT 'no',
  `cat_image` text,
  `cat_desc` text,
  `cat_status` enum('active','inactive') DEFAULT 'active',
  `created_date` timestamp DEFAULT CURRENT_TIMESTAMP,
  `updated_date` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`cat_id`),
  KEY `cat_status` (`cat_status`),
  KEY `cat_top` (`cat_top`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Table structure for table `product_categories`
-- --------------------------------------------------------

CREATE TABLE `product_categories` (
  `p_cat_id` int(10) NOT NULL AUTO_INCREMENT,
  `p_cat_title` varchar(255) NOT NULL,
  `p_cat_top` enum('yes','no') DEFAULT 'no',
  `p_cat_image` text,
  `p_cat_desc` text,
  `sizing_type` enum('clothing','shoes','custom','none') DEFAULT 'clothing',
  `p_cat_status` enum('active','inactive') DEFAULT 'active',
  `created_date` timestamp DEFAULT CURRENT_TIMESTAMP,
  `updated_date` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`p_cat_id`),
  KEY `p_cat_status` (`p_cat_status`),
  KEY `sizing_type` (`sizing_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Table structure for table `manufacturers`
-- --------------------------------------------------------

CREATE TABLE `manufacturers` (
  `manufacturer_id` int(10) NOT NULL AUTO_INCREMENT,
  `manufacturer_title` varchar(255) NOT NULL,
  `manufacturer_top` enum('yes','no') DEFAULT 'no',
  `manufacturer_image` text,
  `manufacturer_desc` text,
  `manufacturer_status` enum('active','inactive') DEFAULT 'active',
  `created_date` timestamp DEFAULT CURRENT_TIMESTAMP,
  `updated_date` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`manufacturer_id`),
  KEY `manufacturer_status` (`manufacturer_status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Table structure for table `colors`
-- --------------------------------------------------------

CREATE TABLE `colors` (
  `color_id` int(10) NOT NULL AUTO_INCREMENT,
  `color_name` varchar(50) NOT NULL,
  `color_code` varchar(7) NOT NULL,
  `color_desc` text,
  `color_status` enum('active','inactive') DEFAULT 'active',
  `created_date` timestamp DEFAULT CURRENT_TIMESTAMP,
  `updated_date` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`color_id`),
  UNIQUE KEY `color_name` (`color_name`),
  UNIQUE KEY `color_code` (`color_code`),
  KEY `color_status` (`color_status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Table structure for table `sizes` (ENHANCED)
-- --------------------------------------------------------

CREATE TABLE `sizes` (
  `size_id` int(10) NOT NULL AUTO_INCREMENT,
  `size_name` varchar(50) NOT NULL,
  `size_type` enum('clothing','shoes_men','shoes_women','shoes_kids','custom') DEFAULT 'clothing',
  `size_order` int(3) DEFAULT 1,
  `size_desc` text,
  `size_status` enum('active','inactive') DEFAULT 'active',
  `created_date` timestamp DEFAULT CURRENT_TIMESTAMP,
  `updated_date` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`size_id`),
  KEY `size_type` (`size_type`),
  KEY `size_order` (`size_order`),
  KEY `size_status` (`size_status`),
  UNIQUE KEY `size_type_name` (`size_type`, `size_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Table structure for table `products` (ENHANCED)
-- --------------------------------------------------------

CREATE TABLE `products` (
  `product_id` int(10) NOT NULL AUTO_INCREMENT,
  `p_cat_id` int(10) NOT NULL,
  `cat_id` int(10) NOT NULL,
  `manufacturer_id` int(10) NOT NULL,
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `product_title` varchar(500) NOT NULL,
  `product_brand` varchar(100),
  `product_url` varchar(500) NOT NULL UNIQUE,
  `product_img1` text NOT NULL,
  `product_img2` text,
  `product_img3` text,
  `product_img4` text,
  `product_img5` text,
  `product_img6` text,
  `product_img7` text,
  `product_img8` text,
  `product_price` decimal(10,2) NOT NULL,
  `product_psp_price` decimal(10,2) DEFAULT 0.00,
  `product_desc` text,
  `product_features` text,
  `product_video` text,
  `product_keywords` text,
  `product_label` varchar(100),
  `product_weight` varchar(50),
  `product_dimensions` varchar(100),
  `product_model` varchar(100),
  `product_warranty` varchar(200),
  `product_availability` enum('In Stock','Out of Stock','Pre-Order','Limited Stock') DEFAULT 'In Stock',
  `shipping_info` text,
  `return_policy` text,
  `status` enum('product','draft','discontinued') DEFAULT 'product',
  `has_variants` tinyint(1) DEFAULT 0,
  `has_sizes` tinyint(1) DEFAULT 1,
  `stock_quantity` int(10) DEFAULT 0,
  `min_stock_alert` int(10) DEFAULT 5,
  `views_count` int(10) DEFAULT 0,
  `sales_count` int(10) DEFAULT 0,
  `created_date` timestamp DEFAULT CURRENT_TIMESTAMP,
  `updated_date` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`product_id`),
  KEY `p_cat_id` (`p_cat_id`),
  KEY `cat_id` (`cat_id`),
  KEY `manufacturer_id` (`manufacturer_id`),
  KEY `status` (`status`),
  KEY `has_variants` (`has_variants`),
  KEY `has_sizes` (`has_sizes`),
  KEY `product_availability` (`product_availability`),
  FULLTEXT KEY `search_text` (`product_title`, `product_desc`, `product_keywords`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Table structure for table `product_variants` (ENHANCED)
-- --------------------------------------------------------

CREATE TABLE `product_variants` (
  `variant_id` int(10) NOT NULL AUTO_INCREMENT,
  `product_id` int(10) NOT NULL,
  `color_name` varchar(100) NOT NULL,
  `color_code` varchar(7) NOT NULL,
  `variant_image` text,
  `variant_images` text,
  `stock_quantity` int(10) DEFAULT 0,
  `price_adjustment` decimal(10,2) DEFAULT 0.00,
  `variant_status` enum('active','inactive') DEFAULT 'active',
  `created_date` timestamp DEFAULT CURRENT_TIMESTAMP,
  `updated_date` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`variant_id`),
  KEY `product_id` (`product_id`),
  KEY `color_name` (`color_name`),
  KEY `variant_status` (`variant_status`),
  UNIQUE KEY `product_color` (`product_id`, `color_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Table structure for table `product_sizes` (ENHANCED)
-- --------------------------------------------------------

CREATE TABLE `product_sizes` (
  `ps_id` int(10) NOT NULL AUTO_INCREMENT,
  `product_id` int(10) NOT NULL,
  `size_id` int(10) NOT NULL,
  `stock_quantity` int(10) DEFAULT 0,
  `price_adjustment` decimal(10,2) DEFAULT 0.00,
  `ps_status` enum('active','inactive') DEFAULT 'active',
  `created_date` timestamp DEFAULT CURRENT_TIMESTAMP,
  `updated_date` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`ps_id`),
  KEY `product_id` (`product_id`),
  KEY `size_id` (`size_id`),
  KEY `ps_status` (`ps_status`),
  UNIQUE KEY `product_size` (`product_id`, `size_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Table structure for table `customers` (ENHANCED)
-- --------------------------------------------------------

CREATE TABLE `customers` (
  `customer_id` int(10) NOT NULL AUTO_INCREMENT,
  `customer_name` varchar(255) NOT NULL,
  `customer_email` varchar(255) NOT NULL UNIQUE,
  `customer_pass` varchar(255) NOT NULL,
  `customer_country` varchar(100),
  `customer_city` varchar(100),
  `customer_contact` varchar(255),
  `customer_address` text,
  `customer_image` text,
  `customer_ip` varchar(45),
  `customer_confirm_code` varchar(255),
  `email_verified` tinyint(1) DEFAULT 0,
  `customer_status` enum('active','inactive','suspended') DEFAULT 'active',
  `last_login` timestamp NULL,
  `created_date` timestamp DEFAULT CURRENT_TIMESTAMP,
  `updated_date` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`customer_id`),
  KEY `customer_status` (`customer_status`),
  KEY `email_verified` (`email_verified`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Table structure for table `cart` (ENHANCED)
-- --------------------------------------------------------

CREATE TABLE `cart` (
  `cart_id` int(10) NOT NULL AUTO_INCREMENT,
  `p_id` int(10) NOT NULL,
  `ip_add` varchar(45) NOT NULL,
  `customer_id` int(10) NULL,
  `qty` int(10) NOT NULL DEFAULT 1,
  `p_price` decimal(10,2) NOT NULL,
  `size` varchar(50),
  `color_variant` varchar(100),
  `variant_image` text,
  `session_id` varchar(255),
  `added_date` timestamp DEFAULT CURRENT_TIMESTAMP,
  `updated_date` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`cart_id`),
  KEY `p_id` (`p_id`),
  KEY `ip_add` (`ip_add`),
  KEY `customer_id` (`customer_id`),
  KEY `session_id` (`session_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Table structure for table `customer_orders` (ENHANCED)
-- --------------------------------------------------------

CREATE TABLE `customer_orders` (
  `order_id` int(10) NOT NULL AUTO_INCREMENT,
  `customer_id` int(10) NOT NULL,
  `due_amount` decimal(10,2) NOT NULL,
  `invoice_no` varchar(100) NOT NULL UNIQUE,
  `qty` int(10) NOT NULL,
  `size` varchar(50),
  `color_variant` varchar(100),
  `order_date` timestamp DEFAULT CURRENT_TIMESTAMP,
  `order_status` enum('pending','confirmed','processing','packed','shipped','out_for_delivery','delivered','cancelled','returned','refunded') DEFAULT 'pending',
  `shipping_address` text,
  `billing_address` text,
  `payment_status` enum('pending','paid','failed','refunded','partial') DEFAULT 'pending',
  `tracking_number` varchar(255),
  `current_status` varchar(100) DEFAULT 'pending',
  `estimated_delivery` datetime,
  `actual_delivery` datetime,
  `notes` text,
  `updated_date` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`order_id`),
  KEY `customer_id` (`customer_id`),
  KEY `order_status` (`order_status`),
  KEY `payment_status` (`payment_status`),
  KEY `order_date` (`order_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Table structure for table `pending_orders` (ENHANCED)
-- --------------------------------------------------------

CREATE TABLE `pending_orders` (
  `order_id` int(10) NOT NULL AUTO_INCREMENT,
  `customer_id` int(10) NOT NULL,
  `invoice_no` varchar(100) NOT NULL,
  `product_id` int(10) NOT NULL,
  `qty` int(10) NOT NULL,
  `size` varchar(50),
  `color_variant` varchar(100),
  `variant_image` text,
  `unit_price` decimal(10,2) NOT NULL,
  `total_price` decimal(10,2) NOT NULL,
  `order_status` enum('pending','processing','shipped','delivered','cancelled') DEFAULT 'pending',
  `created_date` timestamp DEFAULT CURRENT_TIMESTAMP,
  `updated_date` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`order_id`),
  KEY `customer_id` (`customer_id`),
  KEY `product_id` (`product_id`),
  KEY `invoice_no` (`invoice_no`),
  KEY `order_status` (`order_status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Table structure for table `order_tracking`
-- --------------------------------------------------------

CREATE TABLE `order_tracking` (
  `tracking_id` int(10) NOT NULL AUTO_INCREMENT,
  `order_id` int(10) NOT NULL,
  `invoice_no` varchar(100) NOT NULL,
  `tracking_number` varchar(255) NOT NULL,
  `status` enum('pending','confirmed','processing','packed','shipped','out_for_delivery','delivered','cancelled','returned','refunded') DEFAULT 'pending',
  `status_message` text,
  `location` varchar(255),
  `estimated_delivery` datetime,
  `actual_delivery` datetime,
  `updated_by` varchar(100),
  `updated_date` timestamp DEFAULT CURRENT_TIMESTAMP,
  `created_date` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`tracking_id`),
  KEY `order_id` (`order_id`),
  KEY `tracking_number` (`tracking_number`),
  KEY `status` (`status`),
  KEY `invoice_no` (`invoice_no`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Table structure for table `payments` (ENHANCED)
-- --------------------------------------------------------

CREATE TABLE `payments` (
  `payment_id` int(10) NOT NULL AUTO_INCREMENT,
  `invoice_no` varchar(100) NOT NULL,
  `customer_id` int(10) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `payment_mode` varchar(100) NOT NULL,
  `payment_gateway` varchar(100),
  `transaction_id` varchar(255),
  `ref_no` varchar(100),
  `code` varchar(100),
  `payment_date` timestamp DEFAULT CURRENT_TIMESTAMP,
  `payment_status` enum('pending','completed','failed','refunded','cancelled') DEFAULT 'pending',
  `gateway_response` text,
  `updated_date` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`payment_id`),
  KEY `invoice_no` (`invoice_no`),
  KEY `customer_id` (`customer_id`),
  KEY `payment_status` (`payment_status`),
  KEY `transaction_id` (`transaction_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Table structure for table `coupons` (NEW)
-- --------------------------------------------------------

CREATE TABLE `coupons` (
  `coupon_id` int(10) NOT NULL AUTO_INCREMENT,
  `coupon_title` varchar(255) NOT NULL,
  `coupon_code` varchar(50) NOT NULL,
  `coupon_desc` text,
  `product_id` int(10),
  `coupon_price` decimal(10,2) NOT NULL,
  `coupon_limit` int(10) NOT NULL DEFAULT 1,
  `coupon_used` int(10) DEFAULT 0,
  `coupon_type` enum('fixed','percentage') DEFAULT 'fixed',
  `minimum_amount` decimal(10,2) DEFAULT 0.00,
  `start_date` date,
  `end_date` date,
  `coupon_status` enum('active','inactive','expired') DEFAULT 'active',
  `created_date` timestamp DEFAULT CURRENT_TIMESTAMP,
  `updated_date` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`coupon_id`),
  UNIQUE KEY `uk_coupon_code` (`coupon_code`),
  KEY `product_id` (`product_id`),
  KEY `coupon_status` (`coupon_status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Table structure for table `wishlist`
-- --------------------------------------------------------

CREATE TABLE `wishlist` (
  `wishlist_id` int(10) NOT NULL AUTO_INCREMENT,
  `customer_email` varchar(255) NOT NULL,
  `product_id` int(10) NOT NULL,
  `date_added` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`wishlist_id`),
  KEY `customer_email` (`customer_email`),
  KEY `product_id` (`product_id`),
  UNIQUE KEY `customer_product` (`customer_email`, `product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Table structure for table `product_reviews` (ENHANCED)
-- --------------------------------------------------------

CREATE TABLE `product_reviews` (
  `review_id` int(10) NOT NULL AUTO_INCREMENT,
  `product_id` int(10) NOT NULL,
  `customer_email` varchar(255) NOT NULL,
  `customer_name` varchar(255) NOT NULL,
  `review_title` varchar(500),
  `review_text` text NOT NULL,
  `review_rating` int(1) NOT NULL CHECK (review_rating >= 1 AND review_rating <= 5),
  `review_date` timestamp DEFAULT CURRENT_TIMESTAMP,
  `verified_purchase` tinyint(1) DEFAULT 0,
  `helpful_votes` int(10) DEFAULT 0,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `admin_reply` text,
  `updated_date` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`review_id`),
  KEY `product_id` (`product_id`),
  KEY `customer_email` (`customer_email`),
  KEY `status` (`status`),
  KEY `review_rating` (`review_rating`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Table structure for table `recently_viewed`
-- --------------------------------------------------------

CREATE TABLE `recently_viewed` (
  `view_id` int(10) NOT NULL AUTO_INCREMENT,
  `customer_email` varchar(100),
  `session_id` varchar(100),
  `product_id` int(10) NOT NULL,
  `view_date` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`view_id`),
  KEY `product_id` (`product_id`),
  KEY `customer_email` (`customer_email`),
  KEY `session_id` (`session_id`),
  KEY `view_date` (`view_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Table structure for table `product_questions`
-- --------------------------------------------------------

CREATE TABLE `product_questions` (
  `question_id` int(10) NOT NULL AUTO_INCREMENT,
  `product_id` int(10) NOT NULL,
  `customer_email` varchar(100) NOT NULL,
  `customer_name` varchar(100) NOT NULL,
  `question_text` text NOT NULL,
  `question_date` timestamp DEFAULT CURRENT_TIMESTAMP,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  PRIMARY KEY (`question_id`),
  KEY `product_id` (`product_id`),
  KEY `status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Table structure for table `product_answers`
-- --------------------------------------------------------

CREATE TABLE `product_answers` (
  `answer_id` int(10) NOT NULL AUTO_INCREMENT,
  `question_id` int(10) NOT NULL,
  `answerer_email` varchar(100) NOT NULL,
  `answerer_name` varchar(100) NOT NULL,
  `answer_text` text NOT NULL,
  `answer_date` timestamp DEFAULT CURRENT_TIMESTAMP,
  `helpful_votes` int(10) DEFAULT 0,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  PRIMARY KEY (`answer_id`),
  KEY `question_id` (`question_id`),
  KEY `status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Table structure for table `slider`
-- --------------------------------------------------------

CREATE TABLE `slider` (
  `slide_id` int(10) NOT NULL AUTO_INCREMENT,
  `slide_name` varchar(255) NOT NULL,
  `slide_image` text NOT NULL,
  `slide_url` varchar(500),
  `slide_text` text,
  `slide_order` int(3) DEFAULT 1,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_date` timestamp DEFAULT CURRENT_TIMESTAMP,
  `updated_date` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`slide_id`),
  KEY `status` (`status`),
  KEY `slide_order` (`slide_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Table structure for table `boxes_section`
-- --------------------------------------------------------

CREATE TABLE `boxes_section` (
  `box_id` int(10) NOT NULL AUTO_INCREMENT,
  `box_title` varchar(255) NOT NULL,
  `box_desc` text,
  `box_icon` varchar(100),
  `box_order` int(3) DEFAULT 1,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_date` timestamp DEFAULT CURRENT_TIMESTAMP,
  `updated_date` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`box_id`),
  KEY `status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Table structure for table `terms`
-- --------------------------------------------------------

CREATE TABLE `terms` (
  `term_id` int(10) NOT NULL AUTO_INCREMENT,
  `term_title` varchar(255) NOT NULL,
  `term_link` varchar(255) NOT NULL,
  `term_desc` text,
  `created_date` timestamp DEFAULT CURRENT_TIMESTAMP,
  `updated_date` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`term_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Table structure for table `about_us`
-- --------------------------------------------------------

CREATE TABLE `about_us` (
  `about_id` int(10) NOT NULL AUTO_INCREMENT,
  `about_heading` varchar(500) NOT NULL,
  `about_short_desc` text,
  `about_desc` text,
  `updated_date` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`about_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Table structure for table `contact_us`
-- --------------------------------------------------------

CREATE TABLE `contact_us` (
  `contact_id` int(10) NOT NULL AUTO_INCREMENT,
  `contact_heading` varchar(500) NOT NULL,
  `contact_desc` text,
  `contact_email` varchar(255) NOT NULL,
  `contact_phone` varchar(50),
  `contact_address` text,
  `updated_date` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`contact_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Table structure for table `enquiry_types`
-- --------------------------------------------------------

CREATE TABLE `enquiry_types` (
  `enquiry_id` int(10) NOT NULL AUTO_INCREMENT,
  `enquiry_title` varchar(255) NOT NULL,
  `enquiry_desc` text,
  `created_date` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`enquiry_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Table structure for table `admin_logs` (NEW)
-- --------------------------------------------------------

CREATE TABLE `admin_logs` (
  `log_id` int(10) NOT NULL AUTO_INCREMENT,
  `admin_id` int(10) NOT NULL,
  `action` varchar(255) NOT NULL,
  `table_name` varchar(100),
  `record_id` int(10),
  `old_values` text,
  `new_values` text,
  `ip_address` varchar(45),
  `user_agent` text,
  `created_date` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`log_id`),
  KEY `admin_id` (`admin_id`),
  KEY `action` (`action`),
  KEY `created_date` (`created_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ========================================
-- FOREIGN KEY CONSTRAINTS
-- ========================================

ALTER TABLE `products`
  ADD CONSTRAINT `fk_products_p_cat` FOREIGN KEY (`p_cat_id`) REFERENCES `product_categories` (`p_cat_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_products_cat` FOREIGN KEY (`cat_id`) REFERENCES `categories` (`cat_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_products_manufacturer` FOREIGN KEY (`manufacturer_id`) REFERENCES `manufacturers` (`manufacturer_id`) ON DELETE CASCADE;

ALTER TABLE `product_variants`
  ADD CONSTRAINT `fk_variants_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`) ON DELETE CASCADE;

ALTER TABLE `product_sizes`
  ADD CONSTRAINT `fk_product_sizes_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_product_sizes_size` FOREIGN KEY (`size_id`) REFERENCES `sizes` (`size_id`) ON DELETE CASCADE;

ALTER TABLE `cart`
  ADD CONSTRAINT `fk_cart_product` FOREIGN KEY (`p_id`) REFERENCES `products` (`product_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_cart_customer` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`customer_id`) ON DELETE CASCADE;

ALTER TABLE `customer_orders`
  ADD CONSTRAINT `fk_orders_customer` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`customer_id`) ON DELETE CASCADE;

ALTER TABLE `pending_orders`
  ADD CONSTRAINT `fk_pending_customer` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`customer_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_pending_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`) ON DELETE CASCADE;

ALTER TABLE `payments`
  ADD CONSTRAINT `fk_payments_customer` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`customer_id`) ON DELETE CASCADE;

ALTER TABLE `coupons`
  ADD CONSTRAINT `fk_coupons_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`) ON DELETE CASCADE;

ALTER TABLE `wishlist`
  ADD CONSTRAINT `fk_wishlist_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`) ON DELETE CASCADE;

ALTER TABLE `product_reviews`
  ADD CONSTRAINT `fk_reviews_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`) ON DELETE CASCADE;

ALTER TABLE `recently_viewed`
  ADD CONSTRAINT `fk_recently_viewed_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`) ON DELETE CASCADE;

ALTER TABLE `product_questions`
  ADD CONSTRAINT `fk_questions_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`) ON DELETE CASCADE;

ALTER TABLE `product_answers`
  ADD CONSTRAINT `fk_answers_question` FOREIGN KEY (`question_id`) REFERENCES `product_questions` (`question_id`) ON DELETE CASCADE;

ALTER TABLE `admin_logs`
  ADD CONSTRAINT `fk_logs_admin` FOREIGN KEY (`admin_id`) REFERENCES `admins` (`admin_id`) ON DELETE CASCADE;

-- ========================================
-- INSERT DEFAULT DATA
-- ========================================

-- Insert default colors
INSERT INTO `colors` (`color_name`, `color_code`, `color_desc`) VALUES
('Black', '#000000', 'Classic black color'),
('White', '#FFFFFF', 'Pure white color'),
('Red', '#FF0000', 'Bright red color'),
('Blue', '#0000FF', 'Classic blue color'),
('Green', '#008000', 'Natural green color'),
('Yellow', '#FFFF00', 'Bright yellow color'),
('Purple', '#800080', 'Royal purple color'),
('Orange', '#FFA500', 'Vibrant orange color'),
('Pink', '#FFC0CB', 'Soft pink color'),
('Gray', '#808080', 'Neutral gray color'),
('Navy', '#000080', 'Navy blue color'),
('Brown', '#A52A2A', 'Brown color'),
('Beige', '#F5F5DC', 'Beige color'),
('Maroon', '#800000', 'Maroon color'),
('Teal', '#008080', 'Teal color');

-- Insert default clothing sizes
INSERT INTO `sizes` (`size_name`, `size_type`, `size_order`, `size_desc`) VALUES
('XS', 'clothing', 1, 'Extra Small'),
('S', 'clothing', 2, 'Small'),
('M', 'clothing', 3, 'Medium'),
('L', 'clothing', 4, 'Large'),
('XL', 'clothing', 5, 'Extra Large'),
('XXL', 'clothing', 6, 'Double Extra Large'),
('XXXL', 'clothing', 7, 'Triple Extra Large');

-- Insert men's shoe sizes
INSERT INTO `sizes` (`size_name`, `size_type`, `size_order`, `size_desc`) VALUES
('6', 'shoes_men', 1, 'Men Size 6'),
('7', 'shoes_men', 2, 'Men Size 7'),
('8', 'shoes_men', 3, 'Men Size 8'),
('9', 'shoes_men', 4, 'Men Size 9'),
('10', 'shoes_men', 5, 'Men Size 10'),
('11', 'shoes_men', 6, 'Men Size 11'),
('12', 'shoes_men', 7, 'Men Size 12'),
('13', 'shoes_men', 8, 'Men Size 13');

-- Insert women's shoe sizes
INSERT INTO `sizes` (`size_name`, `size_type`, `size_order`, `size_desc`) VALUES
('5', 'shoes_women', 1, 'Women Size 5'),
('6', 'shoes_women', 2, 'Women Size 6'),
('7', 'shoes_women', 3, 'Women Size 7'),
('8', 'shoes_women', 4, 'Women Size 8'),
('9', 'shoes_women', 5, 'Women Size 9'),
('10', 'shoes_women', 6, 'Women Size 10'),
('11', 'shoes_women', 7, 'Women Size 11');

-- Insert kids shoe sizes
INSERT INTO `sizes` (`size_name`, `size_type`, `size_order`, `size_desc`) VALUES
('1', 'shoes_kids', 1, 'Kids Size 1'),
('2', 'shoes_kids', 2, 'Kids Size 2'),
('3', 'shoes_kids', 3, 'Kids Size 3'),
('4', 'shoes_kids', 4, 'Kids Size 4'),
('5', 'shoes_kids', 5, 'Kids Size 5'),
('6', 'shoes_kids', 6, 'Kids Size 6');

-- Insert default manufacturers
INSERT INTO `manufacturers` (`manufacturer_title`, `manufacturer_top`, `manufacturer_image`, `manufacturer_desc`) VALUES
('Apple', 'no', 'apple-logo.png', 'Technology company'),
('Samsung', 'no', 'samsung-logo.png', 'Electronics manufacturer'),
('Sony', 'no', 'sony-logo.png', 'Electronics and entertainment'),
('Nike', 'yes', 'nike-logo.png', 'Sports apparel and footwear'),
('Adidas', 'yes', 'adidas-logo.png', 'Sports apparel and footwear'),
('Puma', 'no', 'puma-logo.png', 'Sports apparel'),
('H&M', 'no', 'hm-logo.png', 'Fashion retailer'),
('Zara', 'no', 'zara-logo.png', 'Fashion retailer'),
('Uniqlo', 'no', 'uniqlo-logo.png', 'Casual wear'),
('Levi\'s', 'no', 'levis-logo.png', 'Denim and casual wear');

-- Insert default categories
INSERT INTO `categories` (`cat_title`, `cat_top`, `cat_image`, `cat_desc`) VALUES
('Electronics', 'yes', 'electronics.jpg', 'Electronic devices and gadgets'),
('Fashion', 'yes', 'fashion.jpg', 'Clothing and accessories'),
('Sports', 'yes', 'sports.jpg', 'Sports and outdoor equipment'),
('Home & Garden', 'no', 'home.jpg', 'Home improvement and garden supplies'),
('Books', 'no', 'books.jpg', 'Books and literature'),
('Beauty', 'no', 'beauty.jpg', 'Beauty and personal care'),
('Toys', 'no', 'toys.jpg', 'Toys and games');

-- Insert default product categories
INSERT INTO `product_categories` (`p_cat_title`, `p_cat_top`, `p_cat_image`, `p_cat_desc`, `sizing_type`) VALUES
('Smartphones', 'yes', 'smartphones.jpg', 'Mobile phones and accessories', 'none'),
('Laptops', 'yes', 'laptops.jpg', 'Portable computers', 'none'),
('Cameras', 'no', 'cameras.jpg', 'Digital cameras and equipment', 'none'),
('Mens Clothing', 'yes', 'mens-clothing.jpg', 'Clothing for men', 'clothing'),
('Womens Clothing', 'yes', 'womens-clothing.jpg', 'Clothing for women', 'clothing'),
('Kids Clothing', 'no', 'kids-clothing.jpg', 'Clothing for children', 'clothing'),
('Mens Shoes', 'yes', 'mens-shoes.jpg', 'Footwear for men', 'shoes'),
('Womens Shoes', 'yes', 'womens-shoes.jpg', 'Footwear for women', 'shoes'),
('Kids Shoes', 'no', 'kids-shoes.jpg', 'Footwear for children', 'shoes'),
('Accessories', 'no', 'accessories.jpg', 'Fashion accessories', 'clothing'),
('Sportswear', 'yes', 'sportswear.jpg', 'Athletic and sports clothing', 'clothing'),
('Bags', 'no', 'bags.jpg', 'Bags and luggage', 'none');

-- Insert default admin user (password should be hashed in production)
INSERT INTO `admins` (`admin_name`, `admin_email`, `admin_pass`, `admin_image`, `admin_contact`, `admin_country`, `admin_job`, `admin_about`) VALUES
('Admin', 'admin@mail.com', 'Password@123', 'admin.jpg', '+1234567890', 'Pakistan', 'Administrator', 'System Administrator');

-- Insert default enquiry types
INSERT INTO `enquiry_types` (`enquiry_title`, `enquiry_desc`) VALUES
('General Inquiry', 'General questions about products or services'),
('Technical Support', 'Technical issues and support requests'),
('Order Support', 'Questions about orders and shipping'),
('Return/Refund', 'Return and refund related inquiries'),
('Partnership', 'Business partnership inquiries'),
('Bulk Orders', 'Wholesale and bulk order inquiries');

-- Insert default about us content
INSERT INTO `about_us` (`about_heading`, `about_short_desc`, `about_desc`) VALUES
('About Our Store', 'Your trusted online shopping destination', 'We are committed to providing high-quality products at competitive prices with excellent customer service. Our mission is to make online shopping easy, secure, and enjoyable for everyone.');

-- Insert default contact us content
INSERT INTO `contact_us` (`contact_heading`, `contact_desc`, `contact_email`, `contact_phone`, `contact_address`) VALUES
('Contact Us', 'Get in touch with our customer service team for any questions or support', 'support@ecomstore.com', '+1-234-567-8900', '123 Business Street, City, Country');

-- Insert sample coupons
INSERT INTO `coupons` (`coupon_title`, `coupon_code`, `coupon_desc`, `coupon_price`, `coupon_limit`, `coupon_type`, `minimum_amount`, `start_date`, `end_date`) VALUES
('Welcome Discount', 'WELCOME10', '10% off for new customers', 10.00, 100, 'percentage', 50.00, CURDATE(), DATE_ADD(CURDATE(), INTERVAL 30 DAY)),
('Summer Sale', 'SUMMER20', '20% off summer collection', 20.00, 50, 'percentage', 100.00, CURDATE(), DATE_ADD(CURDATE(), INTERVAL 60 DAY)),
('Free Shipping', 'FREESHIP', 'Free shipping on orders over $75', 15.00, 200, 'fixed', 75.00, CURDATE(), DATE_ADD(CURDATE(), INTERVAL 90 DAY));

-- ========================================
-- ENHANCED INDEXES FOR PERFORMANCE
-- ========================================

CREATE INDEX `idx_products_status_variants` ON `products` (`status`, `has_variants`);
CREATE INDEX `idx_products_availability` ON `products` (`product_availability`, `status`);
CREATE INDEX `idx_variants_product_color` ON `product_variants` (`product_id`, `color_name`);
CREATE INDEX `idx_sizes_type_order` ON `sizes` (`size_type`, `size_order`);
CREATE INDEX `idx_cart_ip_product` ON `cart` (`ip_add`, `p_id`);
CREATE INDEX `idx_cart_customer_product` ON `cart` (`customer_id`, `p_id`);
CREATE INDEX `idx_orders_customer_status` ON `customer_orders` (`customer_id`, `order_status`);
CREATE INDEX `idx_orders_date_status` ON `customer_orders` (`order_date`, `order_status`);
CREATE INDEX `idx_reviews_product_status` ON `product_reviews` (`product_id`, `status`);
CREATE INDEX `idx_product_sizes_product_size` ON `product_sizes` (`product_id`, `size_id`);
CREATE INDEX `idx_product_sizes_stock` ON `product_sizes` (`stock_quantity`, `ps_status`);
CREATE INDEX `idx_payments_date_status` ON `payments` (`payment_date`, `payment_status`);
CREATE INDEX `idx_coupons_code_status` ON `coupons` (`coupon_code`, `coupon_status`);

-- ========================================
-- ENHANCED TRIGGERS FOR STOCK MANAGEMENT
-- ========================================

DELIMITER $$

-- Trigger to update product stock when variant stock changes
CREATE TRIGGER `update_product_stock_after_variant_change` 
AFTER UPDATE ON `product_variants`
FOR EACH ROW
BEGIN
    UPDATE `products` 
    SET `stock_quantity` = (
        SELECT COALESCE(SUM(stock_quantity), 0) 
        FROM `product_variants` 
        WHERE `product_id` = NEW.product_id AND `variant_status` = 'active'
    )
    WHERE `product_id` = NEW.product_id AND `has_variants` = 1;
END$$

-- Trigger to update product stock when size stock changes
CREATE TRIGGER `update_product_stock_after_size_change` 
AFTER UPDATE ON `product_sizes`
FOR EACH ROW
BEGIN
    UPDATE `products` 
    SET `stock_quantity` = (
        SELECT COALESCE(SUM(stock_quantity), 0) 
        FROM `product_sizes` 
        WHERE `product_id` = NEW.product_id AND `ps_status` = 'active'
    )
    WHERE `product_id` = NEW.product_id AND `has_variants` = 0;
END$$

-- Trigger to update product availability based on stock
CREATE TRIGGER `update_product_availability_after_stock_change` 
AFTER UPDATE ON `products`
FOR EACH ROW
BEGIN
    IF NEW.stock_quantity != OLD.stock_quantity THEN
        IF NEW.stock_quantity = 0 THEN
            UPDATE `products` SET `product_availability` = 'Out of Stock' WHERE `product_id` = NEW.product_id;
        ELSEIF NEW.stock_quantity <= NEW.min_stock_alert THEN
            UPDATE `products` SET `product_availability` = 'Limited Stock' WHERE `product_id` = NEW.product_id;
        ELSE
            UPDATE `products` SET `product_availability` = 'In Stock' WHERE `product_id` = NEW.product_id;
        END IF;
    END IF;
END$$

-- Trigger to log admin actions
CREATE TRIGGER `log_admin_actions_products` 
AFTER UPDATE ON `products`
FOR EACH ROW
BEGIN
    INSERT INTO `admin_logs` (`admin_id`, `action`, `table_name`, `record_id`, `old_values`, `new_values`, `ip_address`)
    VALUES (1, 'UPDATE', 'products', NEW.product_id, 
            CONCAT('title:', OLD.product_title, '|price:', OLD.product_price, '|stock:', OLD.stock_quantity),
            CONCAT('title:', NEW.product_title, '|price:', NEW.product_price, '|stock:', NEW.stock_quantity),
            '127.0.0.1');
END$$

DELIMITER ;

-- ========================================
-- ENHANCED VIEWS FOR COMMON QUERIES
-- ========================================

-- Enhanced product details view
CREATE OR REPLACE VIEW `product_details_view` AS
SELECT 
    p.*,
    pc.p_cat_title,
    pc.sizing_type,
    c.cat_title,
    m.manufacturer_title,
    COALESCE(
        (SELECT SUM(pv.stock_quantity) FROM product_variants pv WHERE pv.product_id = p.product_id AND pv.variant_status = 'active'),
        (SELECT SUM(ps.stock_quantity) FROM product_sizes ps WHERE ps.product_id = p.product_id AND ps.ps_status = 'active'),
        p.stock_quantity
    ) as total_stock,
    (SELECT AVG(review_rating) FROM product_reviews WHERE product_id = p.product_id AND status = 'approved') as avg_rating,
    (SELECT COUNT(*) FROM product_reviews WHERE product_id = p.product_id AND status = 'approved') as review_count
FROM products p
LEFT JOIN product_categories pc ON p.p_cat_id = pc.p_cat_id
LEFT JOIN categories c ON p.cat_id = c.cat_id
LEFT JOIN manufacturers m ON p.manufacturer_id = m.manufacturer_id
WHERE p.status = 'product';

-- Enhanced cart details view
CREATE OR REPLACE VIEW `cart_details_view` AS
SELECT 
    c.*,
    p.product_title,
    p.product_img1,
    p.product_url,
    p.product_availability,
    pc.p_cat_title,
    m.manufacturer_title,
    (c.qty * c.p_price) as total_price
FROM cart c
LEFT JOIN products p ON c.p_id = p.product_id
LEFT JOIN product_categories pc ON p.p_cat_id = pc.p_cat_id
LEFT JOIN manufacturers m ON p.manufacturer_id = m.manufacturer_id;

-- Order summary view
CREATE OR REPLACE VIEW `order_summary_view` AS
SELECT 
    co.*,
    c.customer_name,
    c.customer_email,
    c.customer_contact,
    p.payment_status as payment_status_detail,
    p.payment_mode,
    p.transaction_id
FROM customer_orders co
LEFT JOIN customers c ON co.customer_id = c.customer_id
LEFT JOIN payments p ON co.invoice_no = p.invoice_no;

-- Product performance view
CREATE OR REPLACE VIEW `product_performance_view` AS
SELECT 
    p.product_id,
    p.product_title,
    p.product_price,
    p.stock_quantity,
    p.views_count,
    p.sales_count,
    COALESCE(AVG(pr.review_rating), 0) as avg_rating,
    COUNT(pr.review_id) as total_reviews,
    (SELECT COUNT(*) FROM cart WHERE p_id = p.product_id) as cart_count,
    (SELECT COUNT(*) FROM wishlist w JOIN products prod ON w.product_id = prod.product_id WHERE w.product_id = p.product_id) as wishlist_count
FROM products p
LEFT JOIN product_reviews pr ON p.product_id = pr.product_id AND pr.status = 'approved'
WHERE p.status = 'product'
GROUP BY p.product_id;

-- ========================================
-- STORED PROCEDURES FOR COMMON OPERATIONS
-- ========================================

-- Drop existing procedures first
DROP PROCEDURE IF EXISTS `AddSizesToProduct`;
DROP PROCEDURE IF EXISTS `UpdateProductStock`;

DELIMITER $$

-- Create AddSizesToProduct procedure
CREATE PROCEDURE `AddSizesToProduct`(
    IN p_product_id INT,
    IN p_size_ids TEXT,
    IN p_stock_quantities TEXT
)
BEGIN
    DECLARE done INT DEFAULT FALSE;
    DECLARE size_id INT;
    DECLARE stock_qty INT;
    DECLARE size_cursor CURSOR FOR 
        SELECT CAST(SUBSTRING_INDEX(SUBSTRING_INDEX(p_size_ids, ',', numbers.n), ',', -1) AS UNSIGNED) as size_id,
               CAST(SUBSTRING_INDEX(SUBSTRING_INDEX(p_stock_quantities, ',', numbers.n), ',', -1) AS UNSIGNED) as stock_qty
        FROM (SELECT 1 n UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 UNION SELECT 5 UNION SELECT 6 UNION SELECT 7 UNION SELECT 8 UNION SELECT 9 UNION SELECT 10) numbers
        WHERE CHAR_LENGTH(p_size_ids) - CHAR_LENGTH(REPLACE(p_size_ids, ',', '')) >= numbers.n - 1;
    
    DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = TRUE;
    
    OPEN size_cursor;
    
    read_loop: LOOP
        FETCH size_cursor INTO size_id, stock_qty;
        IF done THEN
            LEAVE read_loop;
        END IF;
        
        INSERT INTO product_sizes (product_id, size_id, stock_quantity) 
        VALUES (p_product_id, size_id, stock_qty)
        ON DUPLICATE KEY UPDATE stock_quantity = stock_qty;
    END LOOP;
    
    CLOSE size_cursor;
    
    -- Update product stock after adding sizes
    CALL UpdateProductStock(p_product_id);
END$$

-- Create UpdateProductStock procedure 
CREATE PROCEDURE `UpdateProductStock`(
    IN p_product_id INT
)
BEGIN
    DECLARE has_variants_flag INT DEFAULT 0;
    DECLARE total_stock INT DEFAULT 0;
    
    SELECT has_variants INTO has_variants_flag FROM products WHERE product_id = p_product_id;
    
    IF has_variants_flag = 1 THEN
        SELECT COALESCE(SUM(stock_quantity), 0) INTO total_stock 
        FROM product_variants 
        WHERE product_id = p_product_id AND variant_status = 'active';
    ELSE
        SELECT COALESCE(SUM(stock_quantity), 0) INTO total_stock 
        FROM product_sizes 
        WHERE product_id = p_product_id AND ps_status = 'active';
    END IF;
    
    UPDATE products SET stock_quantity = total_stock WHERE product_id = p_product_id;
    
    -- Update product availability based on new stock
    IF total_stock = 0 THEN
        UPDATE products SET product_availability = 'Out of Stock' WHERE product_id = p_product_id;
    ELSEIF total_stock <= (SELECT min_stock_alert FROM products WHERE product_id = p_product_id) THEN
        UPDATE products SET product_availability = 'Limited Stock' WHERE product_id = p_product_id;
    ELSE
        UPDATE products SET product_availability = 'In Stock' WHERE product_id = p_product_id;
    END IF;
END$$

DELIMITER ;

-- Re-enable foreign key checks
SET FOREIGN_KEY_CHECKS = 1;

-- ========================================
-- COMPLETION MESSAGE
-- ========================================

SELECT 'Enhanced database structure created successfully! All tables including product_sizes are now properly configured with additional features.' as Status;

-- First, let's ensure all required tables exist with correct structure
USE ecom_store;

-- Create sizes table if it doesn't exist
CREATE TABLE IF NOT EXISTS `sizes` (
  `size_id` int(10) NOT NULL AUTO_INCREMENT,
  `size_name` varchar(50) NOT NULL,
  `size_type` enum('clothing','shoes_men','shoes_women','shoes_kids','custom') DEFAULT 'clothing',
  `size_order` int(3) DEFAULT 1,
  `size_desc` text,
  `size_status` enum('active','inactive') DEFAULT 'active',
  `created_date` timestamp DEFAULT CURRENT_TIMESTAMP,
  `updated_date` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`size_id`),
  KEY `size_type` (`size_type`),
  KEY `size_order` (`size_order`),
  UNIQUE KEY `size_type_name` (`size_type`, `size_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Create product_sizes table if it doesn't exist
CREATE TABLE IF NOT EXISTS `product_sizes` (
  `ps_id` int(10) NOT NULL AUTO_INCREMENT,
  `product_id` int(10) NOT NULL,
  `size_id` int(10) NOT NULL,
  `stock_quantity` int(10) DEFAULT 0,
  `price_adjustment` decimal(10,2) DEFAULT 0.00,
  `ps_status` enum('active','inactive') DEFAULT 'active',
  `created_date` timestamp DEFAULT CURRENT_TIMESTAMP,
  `updated_date` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`ps_id`),
  KEY `product_id` (`product_id`),
  KEY `size_id` (`size_id`),
  UNIQUE KEY `product_size` (`product_id`, `size_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert default sizes if they don't exist
INSERT IGNORE INTO `sizes` (`size_name`, `size_type`, `size_order`, `size_desc`) VALUES
('XS', 'clothing', 1, 'Extra Small'),
('S', 'clothing', 2, 'Small'),
('M', 'clothing', 3, 'Medium'),
('L', 'clothing', 4, 'Large'),
('XL', 'clothing', 5, 'Extra Large'),
('XXL', 'clothing', 6, 'Double Extra Large'),
('XXXL', 'clothing', 7, 'Triple Extra Large');

-- Insert men's shoe sizes
INSERT IGNORE INTO `sizes` (`size_name`, `size_type`, `size_order`, `size_desc`) VALUES
('6', 'shoes_men', 1, 'Men Size 6'),
('7', 'shoes_men', 2, 'Men Size 7'),
('8', 'shoes_men', 3, 'Men Size 8'),
('9', 'shoes_men', 4, 'Men Size 9'),
('10', 'shoes_men', 5, 'Men Size 10'),
('11', 'shoes_men', 6, 'Men Size 11'),
('12', 'shoes_men', 7, 'Men Size 12');

-- Insert women's shoe sizes
INSERT IGNORE INTO `sizes` (`size_name`, `size_type`, `size_order`, `size_desc`) VALUES
('5', 'shoes_women', 1, 'Women Size 5'),
('6', 'shoes_women', 2, 'Women Size 6'),
('7', 'shoes_women', 3, 'Women Size 7'),
('8', 'shoes_women', 4, 'Women Size 8'),
('9', 'shoes_women', 5, 'Women Size 9'),
('10', 'shoes_women', 6, 'Women Size 10'),
('11', 'shoes_women', 7, 'Women Size 11');

-- Ensure has_variants column exists in products table
ALTER TABLE `products` ADD COLUMN IF NOT EXISTS `has_variants` tinyint(1) DEFAULT 0;

-- Ensure variant_image column exists in cart table
ALTER TABLE `cart` ADD COLUMN IF NOT EXISTS `variant_image` text;

-- Ensure variant_image column exists in pending_orders table
ALTER TABLE `pending_orders` ADD COLUMN IF NOT EXISTS `variant_image` text;

SELECT 'Database tables fixed successfully!' as Status;
