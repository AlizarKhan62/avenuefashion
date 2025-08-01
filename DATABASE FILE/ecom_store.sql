-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jul 18, 2025 at 04:39 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `ecom_store`
--

DELIMITER $$
--
-- Procedures
--
CREATE DEFINER=`root`@`localhost` PROCEDURE `AddSizesToProduct` (IN `p_product_id` INT, IN `p_size_ids` TEXT, IN `p_stock_quantities` TEXT)   BEGIN
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

CREATE DEFINER=`root`@`localhost` PROCEDURE `UpdateProductStock` (IN `p_product_id` INT)   BEGIN
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

-- --------------------------------------------------------

--
-- Table structure for table `about_us`
--

CREATE TABLE `about_us` (
  `about_id` int(10) NOT NULL,
  `about_heading` varchar(500) NOT NULL,
  `about_short_desc` text DEFAULT NULL,
  `about_desc` text DEFAULT NULL,
  `updated_date` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `about_us`
--

INSERT INTO `about_us` (`about_id`, `about_heading`, `about_short_desc`, `about_desc`, `updated_date`) VALUES
(1, 'About Our Store', '\r\nYour trusted online shopping destination\r\n', '\r\nWe are committed to providing high-quality products at competitive prices with excellent customer service. Our mission is to make online shopping easy, secure, and enjoyable for everyone.\r\n\r\nAvenue Fashion is Pakistan\'s premier online fashion destination, specializing in trendy clothing and footwear for men, women, and children. Founded with a vision to democratize fashion, we believe that style should be accessible to everyone, regardless of their budget or location.\r\n\r\nOur innovative Virtual Try-On feature sets us apart from traditional e-commerce platforms. Using cutting-edge technology, customers can upload their photos and see how our clothing items will look on them before making a purchase. This revolutionary feature eliminates the guesswork from online shopping and ensures customer satisfaction.\r\n\r\nWe carefully curate our collection from trusted manufacturers and brands, ensuring that every item meets our high standards for quality, comfort, and style. From casual wear to formal attire, from trendy sneakers to elegant heels, our diverse catalog caters to every fashion need and preference.\r\n\r\nAt Avenue Fashion, we understand that shopping should be convenient and hassle-free. That\'s why we offer:\r\n• Free shipping on orders above Rs 50,000\r\n• Secure payment options including Stripe integration\r\n• Easy returns and exchanges within 14 days\r\n• 24/7 customer support\r\n• Virtual Try-On technology for perfect fit\r\n• Size guides and detailed product descriptions\r\n• Regular sales and exclusive discounts\r\n\r\nOur commitment to customer satisfaction extends beyond just selling products. We provide comprehensive size guides, detailed product descriptions, and honest customer reviews to help you make informed decisions. Our customer service team is always ready to assist you with any queries or concerns.\r\n\r\nJoin thousands of satisfied customers who trust Avenue Fashion for their style needs. Experience the future of online fashion shopping with our Virtual Try-On feature and discover your perfect look today!\r\n', '2025-07-11 16:14:18');

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `admin_id` int(10) NOT NULL,
  `admin_name` varchar(255) NOT NULL,
  `admin_email` varchar(255) NOT NULL,
  `admin_pass` varchar(255) NOT NULL,
  `admin_image` text DEFAULT NULL,
  `admin_contact` varchar(255) DEFAULT NULL,
  `admin_country` varchar(100) DEFAULT NULL,
  `admin_job` varchar(255) DEFAULT NULL,
  `admin_about` text DEFAULT NULL,
  `admin_status` enum('active','inactive') DEFAULT 'active',
  `last_login` timestamp NULL DEFAULT NULL,
  `created_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_date` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`admin_id`, `admin_name`, `admin_email`, `admin_pass`, `admin_image`, `admin_contact`, `admin_country`, `admin_job`, `admin_about`, `admin_status`, `last_login`, `created_date`, `updated_date`) VALUES
(1, 'Admin', 'admin@mail.com', 'Password@123', 'admin.jpg', '+1234567890', 'Pakistan', 'Administrator', 'System Administrator', 'active', NULL, '2025-06-19 20:00:10', '2025-06-19 20:00:10');

-- --------------------------------------------------------

--
-- Table structure for table `admin_logs`
--

CREATE TABLE `admin_logs` (
  `log_id` int(10) NOT NULL,
  `admin_id` int(10) NOT NULL,
  `action` varchar(255) NOT NULL,
  `table_name` varchar(100) DEFAULT NULL,
  `record_id` int(10) DEFAULT NULL,
  `old_values` text DEFAULT NULL,
  `new_values` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `created_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin_logs`
--

INSERT INTO `admin_logs` (`log_id`, `admin_id`, `action`, `table_name`, `record_id`, `old_values`, `new_values`, `ip_address`, `user_agent`, `created_date`) VALUES
(1, 1, 'UPDATE', 'products', 1, 'title:adidas Men\'s Essentials Trouser Pants|price:3000.00|stock:0', 'title:adidas Men\'s Essentials Trouser Pants|price:3000.00|stock:0', '127.0.0.1', NULL, '2025-06-20 11:14:39'),
(2, 1, 'UPDATE', 'products', 1, 'title:adidas Men\'s Essentials Trouser Pants|price:3000.00|stock:0', 'title:adidas Men\'s Essentials Trouser Pants|price:3000.00|stock:0', '127.0.0.1', NULL, '2025-06-20 11:15:27'),
(3, 1, 'UPDATE', 'products', 1, 'title:adidas Men\'s Essentials Trouser Pants|price:3000.00|stock:0', 'title:adidas Men\'s Essentials Trouser Pants|price:3000.00|stock:0', '127.0.0.1', NULL, '2025-06-20 11:16:07'),
(4, 1, 'UPDATE', 'products', 2, 'title:Men\'s Lightweight Digital Stripe Long Sleeve Shirt|price:3000.00|stock:0', 'title:Men\'s Lightweight Digital Stripe Long Sleeve Shirt|price:3000.00|stock:0', '127.0.0.1', NULL, '2025-06-20 11:17:27'),
(5, 1, 'UPDATE', 'products', 2, 'title:Men\'s Lightweight Digital Stripe Long Sleeve Shirt|price:3000.00|stock:0', 'title:Men\'s Lightweight Digital Stripe Long Sleeve Shirt|price:3000.00|stock:0', '127.0.0.1', NULL, '2025-06-20 11:18:21'),
(6, 1, 'UPDATE', 'products', 3, 'title:Ultraboost 21 PrimeBlue Shoess|price:1500.00|stock:0', 'title:Ultraboost 21 PrimeBlue Shoess|price:1500.00|stock:0', '127.0.0.1', NULL, '2025-06-20 11:18:38'),
(7, 1, 'UPDATE', 'products', 3, 'title:Ultraboost 21 PrimeBlue Shoess|price:1500.00|stock:0', 'title:Ultraboost 21 PrimeBlue Shoess|price:1500.00|stock:0', '127.0.0.1', NULL, '2025-06-20 11:19:57'),
(8, 1, 'UPDATE', 'products', 3, 'title:Ultraboost 21 PrimeBlue Shoess|price:1500.00|stock:0', 'title:Ultraboost 21 PrimeBlue Shoess|price:1500.00|stock:0', '127.0.0.1', NULL, '2025-06-20 11:20:08'),
(9, 1, 'UPDATE', 'products', 4, 'title:Ultraboost 21 PrimeBlue Shoesss|price:2499.00|stock:0', 'title:Ultraboost 21 PrimeBlue Shoesss|price:2499.00|stock:0', '127.0.0.1', NULL, '2025-06-20 11:20:26'),
(10, 1, 'UPDATE', 'products', 4, 'title:Ultraboost 21 PrimeBlue Shoesss|price:2499.00|stock:0', 'title:Ultraboost 21 PrimeBlue Shoesss|price:2499.00|stock:0', '127.0.0.1', NULL, '2025-06-20 11:21:49'),
(11, 1, 'UPDATE', 'products', 5, 'title:Vssjavun Mens Knit Polo Shirts Short Sleeve|price:2499.00|stock:0', 'title:Vssjavun Mens Knit Polo Shirts Short Sleeve|price:2499.00|stock:0', '127.0.0.1', NULL, '2025-06-20 11:22:00'),
(12, 1, 'UPDATE', 'products', 5, 'title:Vssjavun Mens Knit Polo Shirts Short Sleeve|price:2499.00|stock:0', 'title:Vssjavun Mens Knit Polo Shirts Short Sleeve|price:2499.00|stock:0', '127.0.0.1', NULL, '2025-06-20 11:23:31'),
(13, 1, 'UPDATE', 'products', 1, 'title:adidas Men\'s Essentials Trouser Pants|price:3000.00|stock:0', 'title:adidas Men\'s Essentials coat|price:3000.00|stock:0', '127.0.0.1', NULL, '2025-06-20 15:15:23'),
(14, 1, 'UPDATE', 'products', 2, 'title:Men\'s Lightweight Digital Stripe Long Sleeve Shirt|price:3000.00|stock:0', 'title:Men\'s Lightweight Digital Stripe Long Sleeve Shirt|price:3000.00|stock:0', '127.0.0.1', NULL, '2025-06-20 15:16:13'),
(15, 1, 'UPDATE', 'products', 3, 'title:Ultraboost 21 PrimeBlue Shoess|price:1500.00|stock:0', 'title:Ultraboost 21 PrimeBlue Shoess|price:1500.00|stock:0', '127.0.0.1', NULL, '2025-06-20 15:17:19'),
(16, 1, 'UPDATE', 'products', 4, 'title:Ultraboost 21 PrimeBlue Shoesss|price:2499.00|stock:0', 'title:Ultraboost 21 PrimeBlue Pant|price:2499.00|stock:0', '127.0.0.1', NULL, '2025-06-20 15:18:17'),
(17, 1, 'UPDATE', 'products', 5, 'title:Vssjavun Mens Knit Polo Shirts Short Sleeve|price:2499.00|stock:0', 'title:Vssjavun Mens Knit trouser|price:2499.00|stock:0', '127.0.0.1', NULL, '2025-06-20 15:19:14'),
(18, 1, 'UPDATE', 'products', 3, 'title:Ultraboost 21 PrimeBlue Shoess|price:1500.00|stock:0', 'title:Ultraboost 21 PrimeBlue Shoess|price:1500.00|stock:0', '127.0.0.1', NULL, '2025-06-22 00:57:48'),
(19, 1, 'UPDATE', 'products', 2, 'title:Men\'s Lightweight Digital Stripe Long Sleeve Shirt|price:3000.00|stock:0', 'title:Men\'s Lightweight Digital Stripe Long Sleeve Shirt|price:3000.00|stock:0', '127.0.0.1', NULL, '2025-07-10 04:02:25'),
(20, 1, 'UPDATE', 'products', 2, 'title:Men\'s Lightweight Digital Stripe Long Sleeve Shirt|price:3000.00|stock:0', 'title:Men\'s Lightweight Digital Stripe Long Sleeve Shirt|price:3000.00|stock:0', '127.0.0.1', NULL, '2025-07-10 05:31:02'),
(21, 1, 'UPDATE', 'products', 20, 'title:Men\'s Solid Maroon Dress Shirt Long Sleeve|price:10942.00|stock:0', 'title:Men\'s Solid Maroon Dress Shirt Long Sleeve|price:10942.00|stock:0', '127.0.0.1', NULL, '2025-07-12 06:51:56'),
(22, 1, 'UPDATE', 'products', 18, 'title:Men\'s Distressed Black Denim Jeans Slim Fit|price:19265.00|stock:0', 'title:Men\'s Distressed Black Denim Jeans Slim Fit|price:19265.00|stock:0', '127.0.0.1', NULL, '2025-07-12 06:51:56'),
(23, 1, 'UPDATE', 'products', 15, 'title:Men\'s Sherpa Lined Trucker Jacket|price:22659.00|stock:0', 'title:Men\'s Sherpa Lined Trucker Jacket|price:22659.00|stock:0', '127.0.0.1', NULL, '2025-07-12 06:51:57'),
(24, 1, 'UPDATE', 'products', 25, 'title:Men\'s Casual Grey Track Trouser Loose Fit|price:10959.00|stock:0', 'title:Men\'s Casual Grey Track Trouser Loose Fit|price:10959.00|stock:0', '127.0.0.1', NULL, '2025-07-12 07:01:25'),
(25, 1, 'UPDATE', 'products', 35, 'title:Women\'s Chunky Platform Sneakers Shoes|price:14572.00|stock:0', 'title:Women\'s Chunky Platform Sneakers Shoes|price:14572.00|stock:0', '127.0.0.1', NULL, '2025-07-12 07:40:47'),
(26, 1, 'UPDATE', 'products', 30, 'title:Women\'s Charcoal Grey Elastic Waist Ankle Pants|price:7539.00|stock:0', 'title:Women\'s Charcoal Grey Elastic Waist Ankle Pants|price:7539.00|stock:0', '127.0.0.1', NULL, '2025-07-12 07:40:47'),
(27, 1, 'UPDATE', 'products', 31, 'title: Women\'s Black Pinstripe Button-Up Shirt|price:10005.00|stock:0', 'title: Women\'s Black Pinstripe Button-Up Shirt|price:10005.00|stock:0', '127.0.0.1', NULL, '2025-07-12 07:53:20'),
(28, 1, 'UPDATE', 'products', 35, 'title:Women\'s Chunky Platform Sneakers Shoes|price:14572.00|stock:0', 'title:Women\'s Chunky Platform Sneakers Shoes|price:14572.00|stock:0', '127.0.0.1', NULL, '2025-07-12 07:56:29'),
(29, 1, 'UPDATE', 'products', 34, 'title:Women\'s Nude Pointed Toe Slingback Heels|price:25001.00|stock:0', 'title:Women\'s Nude Pointed Toe Slingback Heels|price:25001.00|stock:0', '127.0.0.1', NULL, '2025-07-12 08:07:00'),
(30, 1, 'UPDATE', 'products', 30, 'title:Women\'s Charcoal Grey Elastic Waist Ankle Pants|price:7539.00|stock:0', 'title:Women\'s Charcoal Grey Elastic Waist Ankle Pants|price:7539.00|stock:0', '127.0.0.1', NULL, '2025-07-12 08:11:22'),
(31, 1, 'UPDATE', 'products', 25, 'title:Men\'s Casual Grey Track Trouser Loose Fit|price:10959.00|stock:0', 'title:Men\'s Casual Grey Track Trouser Loose Fit|price:10959.00|stock:0', '127.0.0.1', NULL, '2025-07-12 09:09:05'),
(32, 1, 'UPDATE', 'products', 31, 'title: Women\'s Black Pinstripe Button-Up Shirt|price:10005.00|stock:0', 'title: Women\'s Black Pinstripe Button-Up Shirt|price:10005.00|stock:0', '127.0.0.1', NULL, '2025-07-12 09:09:49'),
(33, 1, 'UPDATE', 'products', 30, 'title:Women\'s Charcoal Grey Elastic Waist Ankle Pants|price:7539.00|stock:0', 'title:Women\'s Charcoal Grey Elastic Waist Ankle Pants|price:7539.00|stock:0', '127.0.0.1', NULL, '2025-07-12 12:49:33'),
(34, 1, 'UPDATE', 'products', 32, 'title:Women\'s Oversized Plaid/Color Block Button-Up Shirt|price:11864.00|stock:0', 'title:Women\'s Oversized Plaid/Color Block Button-Up Shirt|price:11864.00|stock:0', '127.0.0.1', NULL, '2025-07-12 12:49:33'),
(35, 1, 'UPDATE', 'products', 32, 'title:Women\'s Oversized Plaid/Color Block Button-Up Shirt|price:11864.00|stock:0', 'title:Women\'s Oversized Plaid/Color Block Button-Up Shirt|price:11864.00|stock:0', '127.0.0.1', NULL, '2025-07-12 12:49:33'),
(36, 1, 'UPDATE', 'products', 23, 'title:Men\'s Classic Black Canvas Low-Top Sneakers|price:10377.00|stock:0', 'title:Men\'s Classic Black Canvas Low-Top Sneakers|price:10377.00|stock:0', '127.0.0.1', NULL, '2025-07-12 12:49:33'),
(37, 1, 'UPDATE', 'products', 6, 'stock:92', 'stock:93', '127.0.0.1', NULL, '2025-07-13 07:27:01'),
(38, 1, 'UPDATE', 'products', 7, 'stock:0', 'stock:150', '127.0.0.1', NULL, '2025-07-13 07:27:06'),
(39, 1, 'UPDATE', 'products', 7, 'stock:150', 'stock:151', '127.0.0.1', NULL, '2025-07-13 07:27:14'),
(40, 1, 'UPDATE', 'products', 7, 'stock:151', 'stock:152', '127.0.0.1', NULL, '2025-07-13 07:27:20'),
(41, 1, 'UPDATE', 'products', 7, 'stock:152', 'stock:201', '127.0.0.1', NULL, '2025-07-13 07:27:26'),
(42, 1, 'UPDATE', 'products', 18, 'stock:0', 'stock:20', '127.0.0.1', NULL, '2025-07-13 07:27:31'),
(43, 1, 'UPDATE', 'products', 18, 'stock:20', 'stock:21', '127.0.0.1', NULL, '2025-07-13 07:27:40'),
(44, 1, 'UPDATE', 'products', 18, 'stock:21', 'stock:22', '127.0.0.1', NULL, '2025-07-13 07:27:51'),
(45, 1, 'UPDATE', 'products', 18, 'stock:22', 'stock:32', '127.0.0.1', NULL, '2025-07-13 07:28:02'),
(46, 1, 'UPDATE', 'products', 18, 'stock:32', 'stock:42', '127.0.0.1', NULL, '2025-07-13 07:28:10'),
(47, 1, 'UPDATE', 'products', 18, 'stock:42', 'stock:41', '127.0.0.1', NULL, '2025-07-13 07:28:19'),
(48, 1, 'UPDATE', 'products', 18, 'stock:41', 'stock:23', '127.0.0.1', NULL, '2025-07-13 07:28:34'),
(49, 1, 'UPDATE', 'products', 18, 'stock:23', 'stock:22', '127.0.0.1', NULL, '2025-07-13 08:01:51'),
(50, 1, 'UPDATE', 'products', 18, 'stock:22', 'stock:24', '127.0.0.1', NULL, '2025-07-14 02:56:53'),
(51, 1, 'UPDATE', 'products', 18, 'stock:24', 'stock:23', '127.0.0.1', NULL, '2025-07-14 08:27:36'),
(52, 1, 'UPDATE', 'products', 18, 'stock:23', 'stock:22', '127.0.0.1', NULL, '2025-07-14 12:30:28'),
(53, 1, 'UPDATE', 'products', 18, 'stock:22', 'stock:23', '127.0.0.1', NULL, '2025-07-14 13:04:58'),
(54, 1, 'UPDATE', 'products', 18, 'stock:23', 'stock:22', '127.0.0.1', NULL, '2025-07-14 13:05:06'),
(55, 1, 'UPDATE', 'products', 18, 'stock:22', 'stock:21', '127.0.0.1', NULL, '2025-07-14 13:06:42'),
(56, 1, 'UPDATE', 'products', 18, 'stock:21', 'stock:22', '127.0.0.1', NULL, '2025-07-14 13:41:38'),
(57, 1, 'UPDATE', 'products', 18, 'stock:22', 'stock:23', '127.0.0.1', NULL, '2025-07-14 13:41:45'),
(58, 1, 'UPDATE', 'products', 18, 'stock:23', 'stock:24', '127.0.0.1', NULL, '2025-07-14 13:41:54'),
(59, 1, 'UPDATE', 'products', 18, 'stock:24', 'stock:23', '127.0.0.1', NULL, '2025-07-14 14:13:12'),
(60, 1, 'UPDATE', 'products', 18, 'stock:23', 'stock:22', '127.0.0.1', NULL, '2025-07-14 14:13:51'),
(61, 1, 'UPDATE', 'products', 18, 'stock:22', 'stock:23', '127.0.0.1', NULL, '2025-07-14 18:30:18'),
(62, 1, 'UPDATE', 'products', 18, 'stock:23', 'stock:22', '127.0.0.1', NULL, '2025-07-14 18:31:16');

-- --------------------------------------------------------

--
-- Table structure for table `boxes_section`
--

CREATE TABLE `boxes_section` (
  `box_id` int(10) NOT NULL,
  `box_title` varchar(255) NOT NULL,
  `box_desc` text DEFAULT NULL,
  `box_icon` varchar(100) DEFAULT NULL,
  `box_order` int(3) DEFAULT 1,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_date` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cart`
--

CREATE TABLE `cart` (
  `cart_id` int(10) NOT NULL,
  `p_id` int(10) NOT NULL,
  `ip_add` varchar(45) NOT NULL,
  `customer_id` int(10) DEFAULT NULL,
  `qty` int(10) NOT NULL DEFAULT 1,
  `p_price` decimal(10,2) NOT NULL,
  `size` varchar(50) DEFAULT NULL,
  `color_variant` varchar(100) DEFAULT NULL,
  `variant_image` text DEFAULT NULL,
  `session_id` varchar(255) DEFAULT NULL,
  `added_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_date` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `cart`
--

INSERT INTO `cart` (`cart_id`, `p_id`, `ip_add`, `customer_id`, `qty`, `p_price`, `size`, `color_variant`, `variant_image`, `session_id`, `added_date`, `updated_date`) VALUES
(70, 30, '::1', NULL, 2, 7539.00, 'M', 'Cream', 'variant_Cream_1752219815_0.webp', NULL, '2025-07-18 02:30:28', '2025-07-18 02:30:28');

-- --------------------------------------------------------

--
-- Stand-in structure for view `cart_details_view`
-- (See below for the actual view)
--
CREATE TABLE `cart_details_view` (
`cart_id` int(10)
,`p_id` int(10)
,`ip_add` varchar(45)
,`customer_id` int(10)
,`qty` int(10)
,`p_price` decimal(10,2)
,`size` varchar(50)
,`color_variant` varchar(100)
,`variant_image` text
,`session_id` varchar(255)
,`added_date` timestamp
,`updated_date` timestamp
,`product_title` varchar(500)
,`product_img1` text
,`product_url` varchar(500)
,`product_availability` enum('In Stock','Out of Stock','Pre-Order','Limited Stock')
,`p_cat_title` varchar(255)
,`manufacturer_title` varchar(255)
,`total_price` decimal(20,2)
);

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `cat_id` int(10) NOT NULL,
  `cat_title` varchar(255) NOT NULL,
  `cat_top` enum('yes','no') DEFAULT 'no',
  `cat_image` text DEFAULT NULL,
  `cat_desc` text DEFAULT NULL,
  `cat_status` enum('active','inactive') DEFAULT 'active',
  `created_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_date` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`cat_id`, `cat_title`, `cat_top`, `cat_image`, `cat_desc`, `cat_status`, `created_date`, `updated_date`) VALUES
(8, 'Men', 'yes', 'malelg.png', NULL, 'active', '2025-07-10 07:50:48', '2025-07-10 07:50:48'),
(9, 'Feminine', 'yes', 'feminelg.png', NULL, 'active', '2025-07-10 07:51:01', '2025-07-10 07:51:01'),
(10, 'Kids', 'yes', 'kidslg.jpg', NULL, 'active', '2025-07-10 07:51:18', '2025-07-10 07:51:18');

-- --------------------------------------------------------

--
-- Table structure for table `colors`
--

CREATE TABLE `colors` (
  `color_id` int(10) NOT NULL,
  `color_name` varchar(50) NOT NULL,
  `color_code` varchar(7) NOT NULL,
  `color_desc` text DEFAULT NULL,
  `color_status` enum('active','inactive') DEFAULT 'active',
  `created_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_date` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `colors`
--

INSERT INTO `colors` (`color_id`, `color_name`, `color_code`, `color_desc`, `color_status`, `created_date`, `updated_date`) VALUES
(1, 'Black', '#000000', 'Classic black color', 'active', '2025-06-19 20:00:09', '2025-06-19 20:00:09'),
(2, 'White', '#FFFFFF', 'Pure white color', 'active', '2025-06-19 20:00:09', '2025-06-19 20:00:09'),
(3, 'Red', '#FF0000', 'Bright red color', 'active', '2025-06-19 20:00:09', '2025-06-19 20:00:09'),
(4, 'Blue', '#0000FF', 'Classic blue color', 'active', '2025-06-19 20:00:09', '2025-06-19 20:00:09'),
(5, 'Green', '#008000', 'Natural green color', 'active', '2025-06-19 20:00:09', '2025-06-19 20:00:09'),
(6, 'Yellow', '#FFFF00', 'Bright yellow color', 'active', '2025-06-19 20:00:09', '2025-06-19 20:00:09'),
(7, 'Purple', '#800080', 'Royal purple color', 'active', '2025-06-19 20:00:09', '2025-06-19 20:00:09'),
(8, 'Orange', '#FFA500', 'Vibrant orange color', 'active', '2025-06-19 20:00:09', '2025-06-19 20:00:09'),
(9, 'Pink', '#FFC0CB', 'Soft pink color', 'active', '2025-06-19 20:00:09', '2025-06-19 20:00:09'),
(10, 'Gray', '#808080', 'Neutral gray color', 'active', '2025-06-19 20:00:09', '2025-06-19 20:00:09'),
(11, 'Navy', '#000080', 'Navy blue color', 'active', '2025-06-19 20:00:09', '2025-06-19 20:00:09'),
(12, 'Brown', '#A52A2A', 'Brown color', 'active', '2025-06-19 20:00:09', '2025-06-19 20:00:09'),
(13, 'Beige', '#F5F5DC', 'Beige color', 'active', '2025-06-19 20:00:09', '2025-06-19 20:00:09'),
(14, 'Maroon', '#800000', 'Maroon color', 'active', '2025-06-19 20:00:09', '2025-06-19 20:00:09'),
(15, 'Teal', '#008080', 'Teal color', 'active', '2025-06-19 20:00:09', '2025-06-19 20:00:09');

-- --------------------------------------------------------

--
-- Table structure for table `contact_us`
--

CREATE TABLE `contact_us` (
  `contact_id` int(10) NOT NULL,
  `contact_heading` varchar(500) NOT NULL,
  `contact_desc` text DEFAULT NULL,
  `contact_email` varchar(255) NOT NULL,
  `contact_phone` varchar(50) DEFAULT NULL,
  `contact_address` text DEFAULT NULL,
  `updated_date` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `contact_us`
--

INSERT INTO `contact_us` (`contact_id`, `contact_heading`, `contact_desc`, `contact_email`, `contact_phone`, `contact_address`, `updated_date`) VALUES
(1, 'Contact Us', 'Get in touch with our customer service team for any questions or support', 'support@ecomstore.com', '+1-234-567-8900', '123 Business Street, City, Country', '2025-06-19 20:00:10');

-- --------------------------------------------------------

--
-- Table structure for table `customers`
--

CREATE TABLE `customers` (
  `customer_id` int(10) NOT NULL,
  `customer_name` varchar(255) NOT NULL,
  `customer_email` varchar(255) NOT NULL,
  `customer_pass` varchar(255) NOT NULL,
  `customer_country` varchar(100) DEFAULT NULL,
  `customer_city` varchar(100) DEFAULT NULL,
  `customer_contact` varchar(255) DEFAULT NULL,
  `customer_address` text DEFAULT NULL,
  `customer_image` text DEFAULT NULL,
  `customer_ip` varchar(45) DEFAULT NULL,
  `customer_confirm_code` varchar(255) DEFAULT NULL,
  `email_verified` tinyint(1) DEFAULT 0,
  `customer_status` enum('active','inactive','suspended') DEFAULT 'active',
  `last_login` timestamp NULL DEFAULT NULL,
  `created_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_date` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `customers`
--

INSERT INTO `customers` (`customer_id`, `customer_name`, `customer_email`, `customer_pass`, `customer_country`, `customer_city`, `customer_contact`, `customer_address`, `customer_image`, `customer_ip`, `customer_confirm_code`, `email_verified`, `customer_status`, `last_login`, `created_date`, `updated_date`) VALUES
(1, 'Khan', 'alizarsial1122@gmail.com', 'alizarsial1122@', 'Pakistan', 'faisalabad', '03489753612', 'faislabad,punjab,pakistan', 'image-86664-800.jpg', '::1', '', 0, 'active', NULL, '2025-06-23 14:39:37', '2025-07-10 04:11:35');

-- --------------------------------------------------------

--
-- Table structure for table `customer_orders`
--

CREATE TABLE `customer_orders` (
  `order_id` int(10) NOT NULL,
  `customer_id` int(10) NOT NULL,
  `due_amount` decimal(10,2) NOT NULL,
  `invoice_no` varchar(100) NOT NULL,
  `qty` int(10) NOT NULL,
  `size` varchar(50) DEFAULT NULL,
  `color_variant` varchar(100) DEFAULT NULL,
  `order_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `order_status` enum('pending','confirmed','processing','packed','shipped','out_for_delivery','delivered','cancelled','returned','refunded') DEFAULT 'pending',
  `shipping_address` text DEFAULT NULL,
  `billing_address` text DEFAULT NULL,
  `payment_status` enum('pending','paid','failed','refunded','partial') DEFAULT 'pending',
  `tracking_number` varchar(255) DEFAULT NULL,
  `current_status` varchar(100) DEFAULT 'pending',
  `estimated_delivery` datetime DEFAULT NULL,
  `actual_delivery` datetime DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `updated_date` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `customer_orders`
--

INSERT INTO `customer_orders` (`order_id`, `customer_id`, `due_amount`, `invoice_no`, `qty`, `size`, `color_variant`, `order_date`, `order_status`, `shipping_address`, `billing_address`, `payment_status`, `tracking_number`, `current_status`, `estimated_delivery`, `actual_delivery`, `notes`, `updated_date`) VALUES
(1, 1, 1999.00, '677653740', 1, 'S', NULL, '2025-07-09 17:01:10', 'delivered', NULL, NULL, 'pending', NULL, 'pending', NULL, NULL, NULL, '2025-07-10 04:36:03'),
(6, 1, 2000.00, '1692799644', 1, 'XL', NULL, '2025-07-09 17:10:51', 'delivered', NULL, NULL, 'pending', 'TRK202507091692799644', 'pending', NULL, NULL, NULL, '2025-07-10 04:36:03'),
(7, 1, 2000.00, '1322571127', 1, '', NULL, '2025-07-10 04:02:25', 'delivered', NULL, NULL, 'pending', 'TRK202507101322571127', 'delivered', '2025-07-17 09:02:25', '2025-07-10 09:58:19', NULL, '2025-07-10 04:58:19'),
(8, 1, 4000.00, '1163341295', 2, 'S', NULL, '2025-07-10 05:31:02', 'delivered', NULL, NULL, 'pending', 'TRK202507101163341295', 'pending', NULL, NULL, NULL, '2025-07-10 05:31:02'),
(9, 1, 48083.00, '791151977', 1, '', NULL, '2025-07-12 06:51:56', 'pending', NULL, NULL, 'pending', 'TRK20250712791151977', 'pending', '2025-07-19 11:51:56', NULL, NULL, '2025-07-12 06:51:56'),
(10, 1, 8132.00, '2075295770', 1, '', NULL, '2025-07-12 07:01:25', 'delivered', NULL, NULL, 'pending', 'TRK202507122075295770', 'delivered', '2025-07-19 12:01:25', '2025-07-13 14:15:21', NULL, '2025-07-13 09:15:21'),
(11, 1, 18734.00, '304529084', 1, '', NULL, '2025-07-12 07:40:47', 'pending', NULL, NULL, 'pending', 'TRK20250712304529084', 'pending', '2025-07-19 12:40:47', NULL, NULL, '2025-07-12 07:40:47'),
(12, 1, 6444.00, '941784233', 1, 'L', NULL, '2025-07-12 07:53:19', 'delivered', NULL, NULL, 'pending', 'TRK20250712941784233', 'pending', NULL, NULL, NULL, '2025-07-12 07:53:19'),
(13, 1, 11445.00, '1150232168', 1, '', NULL, '2025-07-12 07:56:29', 'delivered', NULL, NULL, 'pending', 'TRK202507121150232168', 'delivered', '2025-07-19 12:56:29', '2025-07-13 13:46:21', NULL, '2025-07-13 08:46:21'),
(14, 1, 15158.00, '789164911', 1, '8', NULL, '2025-07-12 08:06:59', 'delivered', NULL, NULL, 'pending', 'TRK20250712789164911', 'delivered', NULL, '2025-07-13 13:46:54', NULL, '2025-07-13 08:46:54'),
(15, 1, 7789.00, '1771604839', 1, '', NULL, '2025-07-12 08:11:22', 'delivered', NULL, NULL, 'pending', 'TRK202507121771604839', 'delivered', '2025-07-19 13:11:22', '2025-07-12 13:12:11', NULL, '2025-07-12 08:12:11'),
(16, 1, 8382.00, '285273528', 1, '', NULL, '2025-07-12 09:09:05', 'delivered', NULL, NULL, 'pending', 'TRK20250712285273528', 'delivered', '2025-07-19 14:09:05', '2025-07-13 13:37:58', NULL, '2025-07-13 08:37:58'),
(17, 1, 6444.00, '2080830937', 1, 'L', NULL, '2025-07-12 09:09:49', 'delivered', NULL, NULL, 'pending', 'TRK202507122080830937', 'delivered', NULL, '2025-07-12 17:51:50', NULL, '2025-07-12 12:51:50'),
(18, 1, 42191.00, '1339115616', 1, '', NULL, '2025-07-12 12:49:33', 'delivered', NULL, NULL, 'pending', 'TRK202507121339115616', 'delivered', '2025-07-19 17:49:33', '2025-07-12 17:52:07', NULL, '2025-07-12 12:52:07'),
(19, 1, 14732.00, '1135209877', 1, '', NULL, '2025-07-13 08:01:51', 'delivered', NULL, NULL, 'pending', 'TRK202507131135209877', 'delivered', '2025-07-20 13:01:51', '2025-07-13 13:02:27', NULL, '2025-07-13 08:02:27'),
(20, 1, 16514.00, '1410437783', 1, '', NULL, '2025-07-14 08:06:25', 'pending', NULL, NULL, 'pending', 'TRK202507141410437783', 'pending', '2025-07-21 13:06:25', NULL, NULL, '2025-07-14 08:06:25'),
(21, 1, 6444.00, '1980609891', 1, '', NULL, '2025-07-14 08:11:29', 'delivered', NULL, NULL, 'pending', 'TRK202507141980609891', 'delivered', '2025-07-21 13:11:29', '2025-07-14 13:11:48', NULL, '2025-07-14 08:11:48'),
(22, 1, 9067.00, '187216790', 1, '', NULL, '2025-07-14 08:15:56', 'delivered', NULL, NULL, 'pending', 'TRK20250714187216790', 'delivered', '2025-07-21 13:15:56', '2025-07-14 13:20:00', NULL, '2025-07-14 08:20:00'),
(23, 1, 7789.00, '1774800208', 1, '', NULL, '2025-07-14 08:20:56', 'pending', NULL, NULL, 'pending', 'TRK202507141774800208', 'pending', '2025-07-21 13:20:56', NULL, NULL, '2025-07-14 08:20:56'),
(24, 1, 14732.00, '829287780', 1, '36', NULL, '2025-07-14 08:27:36', 'delivered', NULL, NULL, 'pending', 'TRK20250714829287780', 'pending', NULL, NULL, NULL, '2025-07-14 08:27:36'),
(25, 1, 14732.00, '1996178028', 1, '', NULL, '2025-07-14 12:30:28', 'delivered', NULL, NULL, 'pending', 'TRK202507141996178028', 'delivered', '2025-07-21 17:30:28', '2025-07-14 17:31:02', NULL, '2025-07-14 12:31:02'),
(26, 1, 14732.00, '1901971405', 1, '', NULL, '2025-07-14 13:06:42', 'delivered', NULL, NULL, 'pending', 'TRK202507141901971405', 'delivered', '2025-07-21 18:06:42', '2025-07-14 18:07:03', NULL, '2025-07-14 13:07:03'),
(27, 1, 14732.00, '1278459863', 1, '', NULL, '2025-07-14 14:13:51', 'pending', NULL, NULL, 'pending', 'TRK202507141278459863', 'pending', '2025-07-21 19:13:51', NULL, NULL, '2025-07-14 14:13:51'),
(28, 1, 6444.00, '1832074763', 1, '', NULL, '2025-07-14 14:32:20', 'delivered', NULL, NULL, 'pending', 'TRK202507141832074763', 'delivered', '2025-07-21 19:32:20', '2025-07-14 19:33:59', NULL, '2025-07-14 14:33:59'),
(29, 1, 14253.00, '74987628', 1, '', NULL, '2025-07-14 18:23:17', 'pending', NULL, NULL, 'pending', 'TRK2025071474987628', 'pending', '2025-07-21 23:23:17', NULL, NULL, '2025-07-14 18:23:17'),
(30, 1, 14732.00, '1223040877', 1, '', NULL, '2025-07-14 18:31:16', 'pending', NULL, NULL, 'pending', 'TRK202507141223040877', 'pending', '2025-07-21 23:31:16', NULL, NULL, '2025-07-14 18:31:16'),
(31, 1, 14253.00, '1446201650', 1, '', NULL, '2025-07-14 19:19:12', 'delivered', NULL, NULL, 'pending', 'TRK202507141446201650', 'delivered', '2025-07-22 00:19:12', '2025-07-15 00:19:38', NULL, '2025-07-14 19:19:38');

-- --------------------------------------------------------

--
-- Table structure for table `enquiry_types`
--

CREATE TABLE `enquiry_types` (
  `enquiry_id` int(10) NOT NULL,
  `enquiry_title` varchar(255) NOT NULL,
  `enquiry_desc` text DEFAULT NULL,
  `created_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `enquiry_types`
--

INSERT INTO `enquiry_types` (`enquiry_id`, `enquiry_title`, `enquiry_desc`, `created_date`) VALUES
(1, 'General Inquiry', 'General questions about products or services', '2025-06-19 20:00:10'),
(2, 'Technical Support', 'Technical issues and support requests', '2025-06-19 20:00:10'),
(3, 'Order Support', 'Questions about orders and shipping', '2025-06-19 20:00:10'),
(4, 'Return/Refund', 'Return and refund related inquiries', '2025-06-19 20:00:10'),
(5, 'Partnership', 'Business partnership inquiries', '2025-06-19 20:00:10'),
(6, 'Bulk Orders', 'Wholesale and bulk order inquiries', '2025-06-19 20:00:10');

-- --------------------------------------------------------

--
-- Table structure for table `manufacturers`
--

CREATE TABLE `manufacturers` (
  `manufacturer_id` int(10) NOT NULL,
  `manufacturer_title` varchar(255) NOT NULL,
  `manufacturer_top` enum('yes','no') DEFAULT 'no',
  `manufacturer_image` text DEFAULT NULL,
  `manufacturer_desc` text DEFAULT NULL,
  `manufacturer_status` enum('active','inactive') DEFAULT 'active',
  `created_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_date` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `manufacturers`
--

INSERT INTO `manufacturers` (`manufacturer_id`, `manufacturer_title`, `manufacturer_top`, `manufacturer_image`, `manufacturer_desc`, `manufacturer_status`, `created_date`, `updated_date`) VALUES
(1, 'Apple', 'no', 'apple-logo.png', 'Technology company', 'active', '2025-06-19 20:00:10', '2025-06-19 20:00:10'),
(2, 'Samsung', 'no', 'samsung-logo.png', 'Electronics manufacturer', 'active', '2025-06-19 20:00:10', '2025-06-19 20:00:10'),
(3, 'Sony', 'no', 'sony-logo.png', 'Electronics and entertainment', 'active', '2025-06-19 20:00:10', '2025-06-19 20:00:10'),
(4, 'Nike', 'yes', 'nike-logo.png', 'Sports apparel and footwear', 'active', '2025-06-19 20:00:10', '2025-06-19 20:00:10'),
(5, 'Adidas', 'yes', 'adidas-logo.png', 'Sports apparel and footwear', 'active', '2025-06-19 20:00:10', '2025-06-19 20:00:10'),
(6, 'Puma', 'no', 'puma-logo.png', 'Sports apparel', 'active', '2025-06-19 20:00:10', '2025-06-19 20:00:10'),
(7, 'H&M', 'no', 'hm-logo.png', 'Fashion retailer', 'active', '2025-06-19 20:00:10', '2025-06-19 20:00:10'),
(8, 'Zara', 'no', 'zara-logo.png', 'Fashion retailer', 'active', '2025-06-19 20:00:10', '2025-06-19 20:00:10'),
(9, 'Uniqlo', 'no', 'uniqlo-logo.png', 'Casual wear', 'active', '2025-06-19 20:00:10', '2025-06-19 20:00:10'),
(10, 'Levi\'s', 'no', 'levis-logo.png', 'Denim and casual wear', 'active', '2025-06-19 20:00:10', '2025-06-19 20:00:10');

-- --------------------------------------------------------

--
-- Stand-in structure for view `order_summary_view`
-- (See below for the actual view)
--
CREATE TABLE `order_summary_view` (
`order_id` int(10)
,`customer_id` int(10)
,`due_amount` decimal(10,2)
,`invoice_no` varchar(100)
,`qty` int(10)
,`size` varchar(50)
,`color_variant` varchar(100)
,`order_date` timestamp
,`order_status` enum('pending','confirmed','processing','packed','shipped','out_for_delivery','delivered','cancelled','returned','refunded')
,`shipping_address` text
,`billing_address` text
,`payment_status` enum('pending','paid','failed','refunded','partial')
,`tracking_number` varchar(255)
,`notes` text
,`updated_date` timestamp
,`customer_name` varchar(255)
,`customer_email` varchar(255)
,`customer_contact` varchar(255)
,`payment_status_detail` enum('pending','completed','failed','refunded','cancelled')
,`payment_mode` varchar(100)
,`transaction_id` varchar(255)
);

-- --------------------------------------------------------

--
-- Table structure for table `order_tracking`
--

CREATE TABLE `order_tracking` (
  `tracking_id` int(10) NOT NULL,
  `order_id` int(10) NOT NULL,
  `invoice_no` varchar(100) NOT NULL,
  `tracking_number` varchar(255) NOT NULL,
  `status` enum('pending','confirmed','processing','packed','shipped','out_for_delivery','delivered','cancelled','returned','refunded') DEFAULT 'pending',
  `status_message` text DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL,
  `estimated_delivery` datetime DEFAULT NULL,
  `actual_delivery` datetime DEFAULT NULL,
  `updated_by` varchar(100) DEFAULT NULL,
  `updated_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `created_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_tracking`
--

INSERT INTO `order_tracking` (`tracking_id`, `order_id`, `invoice_no`, `tracking_number`, `status`, `status_message`, `location`, `estimated_delivery`, `actual_delivery`, `updated_by`, `updated_date`, `created_date`) VALUES
(1, 6, '1692799644', 'TRK202507091692799644', 'confirmed', 'Order confirmed and payment received via Stripe', NULL, NULL, NULL, 'System', '2025-07-09 17:10:51', '2025-07-09 17:10:51'),
(2, 7, '1322571127', 'TRK202507101322571127', 'pending', 'Order placed successfully. We have received your order and it is being processed.', NULL, NULL, NULL, 'System', '2025-07-10 04:02:25', '2025-07-10 04:02:25'),
(3, 7, '1322571127', 'TRK202507101322571127', 'delivered', 'Package delivered successfully', NULL, NULL, '2025-07-10 09:52:10', '', '2025-07-10 04:52:10', '2025-07-10 04:52:10'),
(4, 7, '1322571127', 'TRK202507101322571127', 'delivered', 'Package delivered successfully', NULL, NULL, '2025-07-10 09:58:19', 'Admin', '2025-07-10 04:58:19', '2025-07-10 04:58:19'),
(5, 8, '1163341295', 'TRK202507101163341295', 'confirmed', 'Order confirmed and payment received via Stripe', NULL, NULL, NULL, 'System', '2025-07-10 05:31:02', '2025-07-10 05:31:02'),
(6, 9, '791151977', 'TRK20250712791151977', 'pending', 'Order placed successfully. We have received your order and it is being processed.', NULL, NULL, NULL, 'System', '2025-07-12 06:51:57', '2025-07-12 06:51:57'),
(7, 10, '2075295770', 'TRK202507122075295770', 'pending', 'Order placed successfully. We have received your order and it is being processed.', NULL, NULL, NULL, 'System', '2025-07-12 07:01:25', '2025-07-12 07:01:25'),
(8, 11, '304529084', 'TRK20250712304529084', 'pending', 'Order placed successfully. We have received your order and it is being processed.', NULL, NULL, NULL, 'System', '2025-07-12 07:40:47', '2025-07-12 07:40:47'),
(9, 12, '941784233', 'TRK20250712941784233', 'confirmed', 'Order confirmed and payment received via Stripe', NULL, NULL, NULL, 'System', '2025-07-12 07:53:20', '2025-07-12 07:53:20'),
(10, 13, '1150232168', 'TRK202507121150232168', 'pending', 'Order placed successfully. We have received your order and it is being processed.', NULL, NULL, NULL, 'System', '2025-07-12 07:56:29', '2025-07-12 07:56:29'),
(11, 14, '789164911', 'TRK20250712789164911', 'confirmed', 'Order confirmed and payment received via Stripe', NULL, NULL, NULL, 'System', '2025-07-12 08:07:00', '2025-07-12 08:07:00'),
(12, 15, '1771604839', 'TRK202507121771604839', 'pending', 'Order placed successfully. We have received your order and it is being processed.', NULL, NULL, NULL, 'System', '2025-07-12 08:11:22', '2025-07-12 08:11:22'),
(13, 15, '1771604839', 'TRK202507121771604839', 'delivered', 'Package delivered successfully', NULL, NULL, '2025-07-12 13:12:11', 'Admin', '2025-07-12 08:12:11', '2025-07-12 08:12:11'),
(14, 16, '285273528', 'TRK20250712285273528', 'pending', 'Order placed successfully. We have received your order and it is being processed.', NULL, NULL, NULL, 'System', '2025-07-12 09:09:05', '2025-07-12 09:09:05'),
(15, 17, '2080830937', 'TRK202507122080830937', 'confirmed', 'Order confirmed and payment received via Stripe', NULL, NULL, NULL, 'System', '2025-07-12 09:09:49', '2025-07-12 09:09:49'),
(16, 18, '1339115616', 'TRK202507121339115616', 'pending', 'Order placed successfully. We have received your order and it is being processed.', NULL, NULL, NULL, 'System', '2025-07-12 12:49:33', '2025-07-12 12:49:33'),
(17, 17, '2080830937', 'TRK202507122080830937', 'delivered', 'Package delivered successfully', NULL, NULL, '2025-07-12 17:51:50', 'Admin', '2025-07-12 12:51:50', '2025-07-12 12:51:50'),
(18, 18, '1339115616', 'TRK202507121339115616', 'delivered', 'Package delivered successfully', NULL, NULL, '2025-07-12 17:52:01', 'Admin', '2025-07-12 12:52:01', '2025-07-12 12:52:01'),
(19, 18, '1339115616', 'TRK202507121339115616', 'delivered', 'Package delivered successfully', NULL, NULL, '2025-07-12 17:52:07', 'Admin', '2025-07-12 12:52:07', '2025-07-12 12:52:07'),
(20, 19, '1135209877', 'TRK202507131135209877', 'pending', 'Order placed successfully. We have received your order and it is being processed.', NULL, NULL, NULL, 'System', '2025-07-13 08:01:51', '2025-07-13 08:01:51'),
(21, 19, '1135209877', 'TRK202507131135209877', 'delivered', 'Package delivered successfully', NULL, NULL, '2025-07-13 13:02:27', 'Admin', '2025-07-13 08:02:27', '2025-07-13 08:02:27'),
(22, 16, '285273528', 'TRK20250712285273528', 'delivered', 'Package delivered successfully', NULL, NULL, '2025-07-13 13:37:58', 'Admin', '2025-07-13 08:37:58', '2025-07-13 08:37:58'),
(23, 13, '1150232168', 'TRK202507121150232168', 'delivered', 'Package delivered successfully', NULL, NULL, '2025-07-13 13:38:36', 'Admin', '2025-07-13 08:38:36', '2025-07-13 08:38:36'),
(24, 13, '1150232168', 'TRK202507121150232168', 'delivered', 'Package delivered successfully', NULL, NULL, '2025-07-13 13:43:29', 'Admin', '2025-07-13 08:43:29', '2025-07-13 08:43:29'),
(25, 13, '1150232168', 'TRK202507121150232168', 'delivered', 'Package delivered successfully', NULL, NULL, '2025-07-13 13:43:37', 'Admin', '2025-07-13 08:43:37', '2025-07-13 08:43:37'),
(26, 13, '1150232168', 'TRK202507121150232168', 'delivered', 'Package delivered successfully', NULL, NULL, '2025-07-13 13:46:21', 'Admin', '2025-07-13 08:46:21', '2025-07-13 08:46:21'),
(27, 14, '789164911', 'TRK20250712789164911', 'delivered', 'Package delivered successfully', NULL, NULL, '2025-07-13 13:46:54', 'Admin', '2025-07-13 08:46:54', '2025-07-13 08:46:54'),
(28, 10, '2075295770', 'TRK202507122075295770', 'delivered', 'Package delivered successfully', NULL, NULL, '2025-07-13 14:15:21', 'Admin', '2025-07-13 09:15:21', '2025-07-13 09:15:21'),
(29, 20, '1410437783', 'TRK202507141410437783', 'pending', 'Order placed successfully. We have received your order and it is being processed.', NULL, NULL, NULL, 'System', '2025-07-14 08:06:25', '2025-07-14 08:06:25'),
(30, 21, '1980609891', 'TRK202507141980609891', 'pending', 'Order placed successfully. We have received your order and it is being processed.', NULL, NULL, NULL, 'System', '2025-07-14 08:11:29', '2025-07-14 08:11:29'),
(31, 21, '1980609891', 'TRK202507141980609891', 'delivered', 'Package delivered successfully', NULL, NULL, '2025-07-14 13:11:48', 'Admin', '2025-07-14 08:11:48', '2025-07-14 08:11:48'),
(32, 22, '187216790', 'TRK20250714187216790', 'pending', 'Order placed successfully. We have received your order and it is being processed.', NULL, NULL, NULL, 'System', '2025-07-14 08:15:56', '2025-07-14 08:15:56'),
(33, 22, '187216790', 'TRK20250714187216790', 'delivered', 'Package delivered successfully', NULL, NULL, '2025-07-14 13:16:20', 'Admin', '2025-07-14 08:16:20', '2025-07-14 08:16:20'),
(34, 22, '187216790', 'TRK20250714187216790', 'delivered', 'Package delivered successfully', NULL, NULL, '2025-07-14 13:20:00', 'Admin', '2025-07-14 08:20:00', '2025-07-14 08:20:00'),
(35, 23, '1774800208', 'TRK202507141774800208', 'pending', 'Order placed successfully. We have received your order and it is being processed.', NULL, NULL, NULL, 'System', '2025-07-14 08:20:56', '2025-07-14 08:20:56'),
(36, 24, '829287780', 'TRK20250714829287780', 'confirmed', 'Order confirmed and payment received via Stripe', NULL, NULL, NULL, 'System', '2025-07-14 08:27:36', '2025-07-14 08:27:36'),
(37, 25, '1996178028', 'TRK202507141996178028', 'pending', 'Order placed successfully. We have received your order and it is being processed.', NULL, NULL, NULL, 'System', '2025-07-14 12:30:28', '2025-07-14 12:30:28'),
(38, 25, '1996178028', 'TRK202507141996178028', 'delivered', 'Package delivered successfully', NULL, NULL, '2025-07-14 17:31:02', 'Admin', '2025-07-14 12:31:02', '2025-07-14 12:31:02'),
(39, 26, '1901971405', 'TRK202507141901971405', 'pending', 'Order placed successfully. We have received your order and it is being processed.', NULL, NULL, NULL, 'System', '2025-07-14 13:06:42', '2025-07-14 13:06:42'),
(40, 26, '1901971405', 'TRK202507141901971405', 'delivered', 'Package delivered successfully', NULL, NULL, '2025-07-14 18:07:03', 'Admin', '2025-07-14 13:07:03', '2025-07-14 13:07:03'),
(41, 27, '1278459863', 'TRK202507141278459863', 'pending', 'Order placed successfully. We have received your order and it is being processed.', NULL, NULL, NULL, 'System', '2025-07-14 14:13:51', '2025-07-14 14:13:51'),
(42, 28, '1832074763', 'TRK202507141832074763', 'pending', 'Order placed successfully. We have received your order and it is being processed.', NULL, NULL, NULL, 'System', '2025-07-14 14:32:20', '2025-07-14 14:32:20'),
(43, 28, '1832074763', 'TRK202507141832074763', 'delivered', 'Package delivered successfully', NULL, NULL, '2025-07-14 19:33:59', 'Admin', '2025-07-14 14:33:59', '2025-07-14 14:33:59'),
(44, 29, '74987628', 'TRK2025071474987628', 'pending', 'Order placed successfully. We have received your order and it is being processed.', NULL, NULL, NULL, 'System', '2025-07-14 18:23:17', '2025-07-14 18:23:17'),
(45, 30, '1223040877', 'TRK202507141223040877', 'pending', 'Order placed successfully. We have received your order and it is being processed.', NULL, NULL, NULL, 'System', '2025-07-14 18:31:16', '2025-07-14 18:31:16'),
(46, 31, '1446201650', 'TRK202507141446201650', 'pending', 'Order placed successfully. We have received your order and it is being processed.', NULL, NULL, NULL, 'System', '2025-07-14 19:19:12', '2025-07-14 19:19:12'),
(47, 31, '1446201650', 'TRK202507141446201650', 'delivered', 'Package delivered successfully', NULL, NULL, '2025-07-15 00:19:38', 'Admin', '2025-07-14 19:19:38', '2025-07-14 19:19:38');

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `payment_id` int(10) NOT NULL,
  `invoice_no` varchar(100) NOT NULL,
  `customer_id` int(10) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `payment_mode` varchar(100) NOT NULL,
  `payment_gateway` varchar(100) DEFAULT NULL,
  `transaction_id` varchar(255) DEFAULT NULL,
  `ref_no` varchar(100) DEFAULT NULL,
  `code` varchar(100) DEFAULT NULL,
  `payment_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `payment_status` enum('pending','completed','failed','refunded','cancelled') DEFAULT 'pending',
  `gateway_response` text DEFAULT NULL,
  `updated_date` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `pending_orders`
--

CREATE TABLE `pending_orders` (
  `order_id` int(10) NOT NULL,
  `customer_id` int(10) NOT NULL,
  `invoice_no` varchar(100) NOT NULL,
  `product_id` int(10) NOT NULL,
  `qty` int(10) NOT NULL,
  `size` varchar(50) DEFAULT NULL,
  `color_variant` varchar(100) DEFAULT NULL,
  `variant_image` text DEFAULT NULL,
  `unit_price` decimal(10,2) NOT NULL,
  `total_price` decimal(10,2) NOT NULL,
  `order_status` enum('pending','processing','shipped','delivered','cancelled') DEFAULT 'pending',
  `created_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_date` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pending_orders`
--

INSERT INTO `pending_orders` (`order_id`, `customer_id`, `invoice_no`, `product_id`, `qty`, `size`, `color_variant`, `variant_image`, `unit_price`, `total_price`, `order_status`, `created_date`, `updated_date`) VALUES
(9, 1, '791151977', 20, 1, 'M', NULL, NULL, 0.00, 0.00, 'pending', '2025-07-12 06:51:56', '2025-07-12 06:51:56'),
(10, 1, '791151977', 18, 1, '32', NULL, NULL, 0.00, 0.00, 'pending', '2025-07-12 06:51:56', '2025-07-12 06:51:56'),
(11, 1, '791151977', 15, 1, 'S', NULL, NULL, 0.00, 0.00, 'pending', '2025-07-12 06:51:56', '2025-07-12 06:51:56'),
(12, 1, '2075295770', 25, 1, 'M', NULL, NULL, 0.00, 0.00, 'delivered', '2025-07-12 07:01:25', '2025-07-13 09:15:21'),
(13, 1, '304529084', 35, 1, '9', NULL, NULL, 0.00, 0.00, 'pending', '2025-07-12 07:40:47', '2025-07-12 07:40:47'),
(14, 1, '304529084', 30, 1, 'L', NULL, NULL, 0.00, 0.00, 'pending', '2025-07-12 07:40:47', '2025-07-12 07:40:47'),
(15, 1, '941784233', 31, 1, 'L', NULL, NULL, 0.00, 0.00, 'delivered', '2025-07-12 07:53:20', '2025-07-12 07:53:20'),
(16, 1, '1150232168', 35, 1, '8', NULL, NULL, 0.00, 0.00, 'delivered', '2025-07-12 07:56:29', '2025-07-13 08:38:36'),
(17, 1, '789164911', 34, 1, '8', NULL, NULL, 0.00, 0.00, 'delivered', '2025-07-12 08:06:59', '2025-07-12 08:06:59'),
(18, 1, '1771604839', 30, 1, 'XL', NULL, NULL, 0.00, 0.00, 'delivered', '2025-07-12 08:11:22', '2025-07-12 08:12:11'),
(19, 1, '285273528', 25, 1, 'M', NULL, NULL, 0.00, 0.00, 'delivered', '2025-07-12 09:09:05', '2025-07-13 08:37:58'),
(20, 1, '2080830937', 31, 1, 'L', NULL, NULL, 0.00, 0.00, 'delivered', '2025-07-12 09:09:49', '2025-07-12 09:09:49'),
(21, 1, '1339115616', 30, 1, 'XL', NULL, NULL, 0.00, 0.00, 'delivered', '2025-07-12 12:49:33', '2025-07-12 12:52:01'),
(22, 1, '1339115616', 32, 2, 'L', NULL, NULL, 0.00, 0.00, 'delivered', '2025-07-12 12:49:33', '2025-07-12 12:52:01'),
(23, 1, '1339115616', 32, 1, 'S', NULL, NULL, 0.00, 0.00, 'delivered', '2025-07-12 12:49:33', '2025-07-12 12:52:01'),
(24, 1, '1339115616', 23, 1, '8', NULL, NULL, 0.00, 0.00, 'delivered', '2025-07-12 12:49:33', '2025-07-12 12:52:01'),
(25, 1, '1135209877', 18, 1, '32', NULL, NULL, 0.00, 0.00, 'delivered', '2025-07-13 08:01:51', '2025-07-13 08:02:27'),
(26, 1, '1410437783', 25, 2, 'L', NULL, NULL, 0.00, 0.00, 'pending', '2025-07-14 08:06:25', '2025-07-14 08:06:25'),
(27, 1, '1980609891', 31, 1, 'L', NULL, NULL, 0.00, 0.00, 'delivered', '2025-07-14 08:11:29', '2025-07-14 08:11:48'),
(28, 1, '187216790', 32, 1, 'L', NULL, NULL, 0.00, 0.00, 'delivered', '2025-07-14 08:15:56', '2025-07-14 08:16:20'),
(29, 1, '1774800208', 30, 1, 'M', NULL, NULL, 0.00, 0.00, 'pending', '2025-07-14 08:20:56', '2025-07-14 08:20:56'),
(30, 1, '829287780', 18, 1, '36', NULL, NULL, 0.00, 0.00, 'delivered', '2025-07-14 08:27:36', '2025-07-14 08:27:36'),
(31, 1, '1996178028', 18, 1, '32', NULL, NULL, 0.00, 0.00, 'delivered', '2025-07-14 12:30:28', '2025-07-14 12:31:02'),
(32, 1, '1901971405', 18, 1, '36', NULL, NULL, 0.00, 0.00, 'delivered', '2025-07-14 13:06:42', '2025-07-14 13:07:03'),
(33, 1, '1278459863', 18, 1, '36', NULL, NULL, 0.00, 0.00, 'pending', '2025-07-14 14:13:51', '2025-07-14 14:13:51'),
(34, 1, '1832074763', 31, 1, 'L', NULL, NULL, 0.00, 0.00, 'delivered', '2025-07-14 14:32:20', '2025-07-14 14:33:59'),
(35, 1, '74987628', 33, 1, '10', NULL, NULL, 0.00, 0.00, 'pending', '2025-07-14 18:23:17', '2025-07-14 18:23:17'),
(36, 1, '1223040877', 18, 1, '38', NULL, NULL, 0.00, 0.00, 'pending', '2025-07-14 18:31:16', '2025-07-14 18:31:16'),
(37, 1, '1446201650', 33, 1, '8', NULL, NULL, 0.00, 0.00, 'delivered', '2025-07-14 19:19:12', '2025-07-14 19:19:38');

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `product_id` int(10) NOT NULL,
  `p_cat_id` int(10) NOT NULL,
  `cat_id` int(10) NOT NULL,
  `manufacturer_id` int(10) NOT NULL,
  `date` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `product_title` varchar(500) NOT NULL,
  `product_brand` varchar(100) DEFAULT NULL,
  `product_url` varchar(500) NOT NULL,
  `product_img1` text NOT NULL,
  `product_img2` text DEFAULT NULL,
  `product_img3` text DEFAULT NULL,
  `product_img4` text DEFAULT NULL,
  `product_img5` text DEFAULT NULL,
  `product_img6` text DEFAULT NULL,
  `product_img7` text DEFAULT NULL,
  `product_img8` text DEFAULT NULL,
  `product_price` decimal(10,2) NOT NULL,
  `product_psp_price` decimal(10,2) DEFAULT 0.00,
  `product_desc` text DEFAULT NULL,
  `product_features` text DEFAULT NULL,
  `product_video` text DEFAULT NULL,
  `product_keywords` text DEFAULT NULL,
  `product_label` varchar(100) DEFAULT NULL,
  `product_weight` varchar(50) DEFAULT NULL,
  `product_dimensions` varchar(100) DEFAULT NULL,
  `product_model` varchar(100) DEFAULT NULL,
  `product_warranty` varchar(200) DEFAULT NULL,
  `product_availability` enum('In Stock','Out of Stock','Pre-Order','Limited Stock') DEFAULT 'In Stock',
  `shipping_info` text DEFAULT NULL,
  `return_policy` text DEFAULT NULL,
  `status` enum('product','draft','discontinued') DEFAULT 'product',
  `has_variants` tinyint(1) DEFAULT 0,
  `has_sizes` tinyint(1) DEFAULT 1,
  `stock_quantity` int(10) DEFAULT 0,
  `min_stock_alert` int(10) DEFAULT 5,
  `views_count` int(10) DEFAULT 0,
  `sales_count` int(10) DEFAULT 0,
  `created_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_date` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`product_id`, `p_cat_id`, `cat_id`, `manufacturer_id`, `date`, `product_title`, `product_brand`, `product_url`, `product_img1`, `product_img2`, `product_img3`, `product_img4`, `product_img5`, `product_img6`, `product_img7`, `product_img8`, `product_price`, `product_psp_price`, `product_desc`, `product_features`, `product_video`, `product_keywords`, `product_label`, `product_weight`, `product_dimensions`, `product_model`, `product_warranty`, `product_availability`, `shipping_info`, `return_policy`, `status`, `has_variants`, `has_sizes`, `stock_quantity`, `min_stock_alert`, `views_count`, `sales_count`, `created_date`, `updated_date`) VALUES
(6, 17, 10, 1, '2025-07-13 07:27:01', 'Kids Two-Tone Hooded Varsity Jacket', NULL, 'Kids-Two-Tone', 'main_1752134485_1.webp', 'main_1752134485_2.webp', 'main_1752134485_3.webp', NULL, NULL, NULL, NULL, NULL, 10563.00, 8802.00, 'This stylish two-tone hooded varsity jacket for kids features a classic black body with contrasting red raglan sleeves, giving it a sporty and trendy look. It has a button-front closure, ribbed cuffs and hem with white stripes, and two functional side pockets. The jacket is adorned with a large white \"A\" patch on the left chest and a bold \"3\" graphic with additional lettering on the left sleeve. A white turtleneck is shown underneath, suggesting it\'s suitable for layering in cooler weather. This versatile jacket is perfect for casual wear, school, or outdoor activities, offering both comfort and style for young boys and girls.', 'Size: S\r\n\r\nHeight (cm): 110-120\r\n\r\nWeight (kg): 20-25\r\n\r\nChest (cm): 60-64;\r\n\r\nSize: M\r\n\r\nHeight (cm): 120-130\r\n\r\nWeight (kg): 25-30\r\n\r\nChest (cm): 64-68;\r\n\r\nSize: L\r\n\r\nHeight (cm): 130-140\r\n\r\nWeight (kg): 30-35\r\n\r\nChest (cm): 68-72;\r\n\r\nSize: XL\r\n\r\nHeight (cm): 140-150\r\n\r\nWeight (kg): 35-40\r\n\r\nChest (cm): 72-76', '', 'Kids Two-Tone', 'Sale', NULL, NULL, NULL, NULL, 'In Stock', NULL, NULL, 'product', 1, 1, 93, 5, 0, 0, '2025-07-10 08:01:25', '2025-07-13 07:27:01'),
(7, 17, 10, 1, '2025-07-13 07:27:26', 'Kids\' Pink Quilted Puffer Jacket with Faux Fur Hood', NULL, 'Quilted-Puffer-Jacket', 'main_1752140775_1.webp', 'main_1752140775_2.webp', 'main_1752140775_3.webp', NULL, NULL, NULL, NULL, NULL, 15563.00, 8802.00, 'A charming and warm pink quilted puffer jacket for kids, perfect for colder weather. It features a cozy faux fur-lined hood, a belted waist with a decorative buckle, and two front pockets adorned with cute pom-poms. The jacket has a button-up front and a stylish quilted pattern with heart-shaped details.', 'Size: S\r\n\r\nHeight (cm): 100-110\r\n\r\nWeight (kg): 18-22\r\n\r\nChest (cm): 56-60;\r\n\r\nSize: M\r\n\r\nHeight (cm): 110-120\r\n\r\nWeight (kg): 22-27\r\n\r\nChest (cm): 60-64;\r\n\r\nSize: L\r\n\r\nHeight (cm): 120-130\r\n\r\nWeight (kg): 27-32\r\n\r\nChest (cm): 64-68;\r\n\r\nSize: XL\r\n\r\nHeight (cm): 130-140\r\n\r\nWeight (kg): 32-37\r\n\r\nChest (cm): 68-72', '', 'Quilted Puffer Jacket', 'Gift', NULL, NULL, NULL, NULL, 'In Stock', NULL, NULL, 'product', 1, 1, 201, 5, 0, 0, '2025-07-10 09:46:15', '2025-07-13 07:27:26'),
(8, 14, 10, 1, '2025-07-10 11:32:41', 'Kids\' Stylish Cargo Jogger Pants Cotton Blend', NULL, 'Pants-Cotton-Blend', 'main_1752147161_1.webp', 'main_1752147161_2.webp', 'main_1752147161_3.webp', NULL, NULL, NULL, NULL, NULL, 13169.00, 10195.00, 'These Kids\' Stylish Cargo Jogger Pants are designed for active comfort and trendy streetwear style. Made with a breathable cotton blend, these khaki joggers feature elasticated cuffs, an adjustable waistband, and dual side cargo pockets with a sporty patch label. Whether for casual playtime or a cool day out, they provide flexibility, durability, and flair.', '4Y–5Y\r\nWaist (Relaxed–Stretched): 48–60 cm\r\n\r\nHip: 66 cm\r\n\r\nHeight : 105–115 cm\r\n\r\nInseam: 41 cm;\r\n\r\n6Y–7Y\r\nWaist (Relaxed–Stretched): 52–64 cm\r\n\r\nHip: 70 cm\r\n\r\nHeight (Child): 115–125 cm\r\n\r\nInseam: 46 cm;\r\n\r\n8Y–9Y\r\nWaist (Relaxed–Stretched): 56–68 cm\r\n\r\nHip: 74 cm\r\n\r\nHeight (Child): 125–135 cm\r\n\r\nInseam: 51 cm;\r\n\r\n10Y–11Y\r\nWaist (Relaxed–Stretched): 60–72 cm\r\n\r\nHip: 78 cm\r\n\r\nHeight (Child): 135–145 cm\r\n\r\nInseam: 56 cm\r\n\r\n', '', 'Pants Cotton Blend', 'Sale', NULL, NULL, NULL, NULL, 'In Stock', NULL, NULL, 'product', 1, 1, 0, 5, 0, 0, '2025-07-10 11:32:41', '2025-07-10 11:32:41'),
(9, 14, 10, 1, '2025-07-10 12:21:19', 'Girls\' Butterfly Print Soft Denim-Look Pants', NULL, 'Denim-Look-Pants', 'main_1752150079_1.webp', 'main_1752150079_2.webp', 'main_1752150079_3.webp', NULL, NULL, NULL, NULL, NULL, 5729.00, 4306.00, 'Let your little girl shine in these Girls\' Butterfly Print Soft Denim-Look Pants, designed for comfort and charm. Although they look like jeans, these pants are made from a soft, stretchable fabric that offers more flexibility and breathability than traditional denim. Adorned with beautiful butterfly and floral prints, these pants are perfect for casual outings, school days, or playdates.\r\n\r\n', '4Y–5Y\r\nWaist (Relaxed–Stretched): 48–60 cm\r\n\r\nHip: 66 cm\r\n\r\nHeight (Child): 105–115 cm\r\n\r\nInseam: 41 cm;\r\n\r\n6Y–7Y\r\nWaist (Relaxed–Stretched): 52–64 cm\r\n\r\nHip: 70 cm\r\n\r\nHeight (Child): 115–125 cm\r\n\r\nInseam: 46 cm;\r\n\r\n8Y–9Y\r\nWaist (Relaxed–Stretched): 56–68 cm\r\n\r\nHip: 74 cm\r\n\r\nHeight (Child): 125–135 cm\r\n\r\nInseam: 51 cm;\r\n\r\n10Y–11Y\r\nWaist (Relaxed–Stretched): 60–72 cm\r\n\r\nHip: 78 cm\r\n\r\nHeight (Child): 135–145 cm\r\n\r\nInseam: 56 cm\r\n\r\n', '', 'Denim Look Pants', 'Gift', NULL, NULL, NULL, NULL, 'In Stock', NULL, NULL, 'product', 1, 1, 0, 5, 0, 0, '2025-07-10 12:21:19', '2025-07-10 12:21:19'),
(10, 13, 10, 1, '2025-07-10 12:31:36', 'Kids\' Graphic Gaming T-Shirt Pixel Warrior Design', NULL, 'Gaming-T-Shirt', 'main_1752150696_1.webp', 'main_1752150696_2.webp', 'main_1752150696_3.webp', NULL, NULL, NULL, NULL, NULL, 2573.00, 0.00, 'Fuel your child\'s imagination with this Kids\' Graphic Gaming T-Shirt featuring a powerful pixel warrior design. Inspired by popular block-building games, this bold and vivid print captures the thrill of adventure. Crafted from soft, breathable fabric, it’s perfect for everyday wear, whether it’s school, playtime, or gaming marathons.', 'Small (S)\r\nChest: 64 cm\r\n\r\nLength: 45 cm\r\n\r\nShoulder: 26 cm;\r\n\r\nMedium (M)\r\nChest: 68 cm\r\n\r\nLength: 48 cm\r\n\r\nShoulder: 28 cm;\r\n\r\nLarge (L)\r\nChest: 72 cm\r\n\r\nLength: 51 cm\r\n\r\nShoulder: 30 cm;\r\n\r\nExtra Large (XL)\r\nChest: 76 cm\r\n\r\nLength: 54 cm\r\n\r\nShoulder: 32 cm\r\n\r\n', '', 'Gaming T-Shirt', 'New', NULL, NULL, NULL, NULL, 'In Stock', NULL, NULL, 'product', 1, 1, 0, 5, 0, 0, '2025-07-10 12:31:36', '2025-07-10 12:31:36'),
(11, 13, 10, 1, '2025-07-10 12:39:51', 'Kids\' Graphic Gaming T-Shirt Pink Pixel', NULL, 'T-Shirt-Pink', 'main_1752151191_1.webp', 'main_1752151191_2.webp', 'main_1752151191_3.webp', NULL, NULL, NULL, NULL, NULL, 4869.00, 2581.00, 'Spark your child\'s creativity with this vibrant Kids\' Graphic Gaming T-Shirt. It features an enchanting pixelated design with a prominent heart-shaped tree, a charming female character in pink, and an array of adorable pixel animals. This playful and colorful print is inspired by popular block-building adventure games, bringing their virtual worlds to life. Made from soft, breathable fabric, this tee is ideal for comfortable everyday wear, from school to gaming sessions and imaginative play.', 'Small (S)\r\n\r\nChest: 64 cm\r\n\r\nLength: 45 cm\r\n\r\nShoulder: 26 cm;\r\nMedium (M)\r\n\r\nChest: 68 cm\r\n\r\nLength: 48 cm\r\n\r\nShoulder: 28 cm;\r\nLarge (L)\r\n\r\nChest: 72 cm\r\n\r\nLength: 51 cm\r\n\r\nShoulder: 30 cm;\r\nExtra Large (XL)\r\n\r\nChest: 76 cm\r\n\r\nLength: 54 cm\r\n\r\nShoulder: 32 cm', '', 'T-Shirt Pink', 'Sale', NULL, NULL, NULL, NULL, 'In Stock', NULL, NULL, 'product', 1, 1, 0, 5, 0, 0, '2025-07-10 12:39:51', '2025-07-10 12:39:51'),
(12, 18, 10, 1, '2025-07-10 12:49:34', 'Kids\' Iridescent Multi-Color Sneakers', NULL, 'Iridescent-Multi-Color', 'main_1752151774_1.webp', 'main_1752151774_2.webp', 'main_1752151774_3.webp', NULL, NULL, NULL, NULL, NULL, 17776.00, 14140.00, 'These vibrant kids\' sneakers are designed for both style and comfort. Featuring a dazzling iridescent multi-color upper, they catch the light with every step, making them a fun choice for any outfit. The convenient hook-and-loop strap, combined with elastic laces, ensures a secure and easy fit for active feet. With a sturdy, contrasting sole and playful accents, these shoes are perfect for daily wear, school, playtime, and light athletic activities.', 'Size 1: Approx. foot length 10.5 - 11.5 cm;\r\n\r\nSize 2: Approx. foot length 11.5 - 12.5 cm;\r\n\r\nSize 3: Approx. foot length 12.5 - 13.5 cm;\r\n\r\nSize 4: Approx. foot length 13.5 - 14.5 cm;\r\n\r\nSize 5: Approx. foot length 14.5 - 15.5 cm;\r\n\r\nSize 6: Approx. foot length 15.5 - 16.5 cm', '', 'Iridescent Multi-Color ', 'Gift', NULL, NULL, NULL, NULL, 'In Stock', NULL, NULL, 'product', 1, 1, 0, 5, 0, 0, '2025-07-10 12:49:34', '2025-07-10 12:49:34'),
(13, 17, 8, 1, '2025-07-10 15:38:05', 'Men\'s Classic Checkered Coat Slim Fit', NULL, 'Coat-Slim-Fit', 'main_1752161885_1.webp', 'main_1752161885_2.webp', 'main_1752161885_3.webp', NULL, NULL, NULL, NULL, NULL, 42758.00, 25392.00, 'Elevate your wardrobe with this sophisticated Men\'s Classic Checkered Blazer, perfect for both formal and smart-casual occasions. Crafted with a subtle checkered pattern in versatile shades of charcoal and black, this blazer offers a timeless look. Its slim-fit design provides a modern silhouette, while the two-button closure and notch lapel maintain a classic appeal. Featuring a breast pocket with a stylish pocket square accent and two flap pockets, it combines functionality with refined style. Ideal for business meetings, social gatherings, or adding a touch of elegance to your everyday attire.', 'Small (S)\r\n\r\nChest: 96 - 100 cm\r\n\r\nShoulder: 42 - 44 cm\r\n\r\nLength (back, from collar seam): 70 - 72 cm\r\n\r\nSleeve Length: 62 - 64 cm;\r\n\r\nMedium (M)\r\n\r\nChest: 100 - 104 cm\r\n\r\nShoulder: 44 - 46 cm\r\n\r\nLength (back, from collar seam): 72 - 74 cm\r\n\r\nSleeve Length: 64 - 66 cm;\r\n\r\nLarge (L)\r\n\r\nChest: 104 - 108 cm\r\n\r\nShoulder: 46 - 48 cm\r\n\r\nLength (back, from collar seam): 74 - 76 cm\r\n\r\nSleeve Length: 66 - 68 cm;\r\n\r\nExtra Large (XL)\r\n\r\nChest: 108 - 112 cm\r\n\r\nShoulder: 48 - 50 cm\r\n\r\nLength (back, from collar seam): 76 - 78 cm\r\n\r\nSleeve Length: 68 - 70 cm', '', 'Coat Slim Fit', 'Sale', NULL, NULL, NULL, NULL, 'In Stock', NULL, NULL, 'product', 1, 1, 0, 5, 0, 0, '2025-07-10 15:38:05', '2025-07-10 15:38:05'),
(14, 17, 8, 1, '2025-07-10 15:45:11', 'Men\'s Textured Gray Blazer Modern Fit', NULL, 'Blazer-Modern-Fit', 'main_1752162310_1.webp', 'main_1752162311_2.webp', 'main_1752162311_3.webp', NULL, NULL, NULL, NULL, NULL, 42749.00, 25392.00, 'Command attention with this stylish Men\'s Textured Gray Blazer, a versatile piece perfect for a range of occasions from business casual to sophisticated social events. Crafted from a fabric with a subtle, rich texture (often referred to as slub, tweed-like, or melange), this blazer offers a contemporary look with classic appeal. It features a modern fit that provides a sharp silhouette without being overly restrictive, a two-button front closure, and notched lapels. With a breast pocket and two flap pockets at the waist, it blends practicality with elegant design. Ideal for pairing with dress shirts, polos, or even a fine knit, this blazer adds a touch of refined sophistication to any ensemble.', 'Small (S)\r\n\r\nChest: 96 - 100 cm\r\n\r\nShoulder: 42 - 44 cm\r\n\r\nLength (back, from collar seam): 70 - 72 cm\r\n\r\nSleeve Length: 62 - 64 cm;\r\n\r\nMedium (M)\r\n\r\nChest: 100 - 104 cm\r\n\r\nShoulder: 44 - 46 cm\r\n\r\nLength (back, from collar seam): 72 - 74 cm\r\n\r\nSleeve Length: 64 - 66 cm;\r\n\r\nLarge (L)\r\n\r\nChest: 104 - 108 cm\r\n\r\nShoulder: 46 - 48 cm\r\n\r\nLength (back, from collar seam): 74 - 76 cm\r\n\r\nSleeve Length: 66 - 68 cm;\r\n\r\nExtra Large (XL)\r\n\r\nChest: 108 - 112 cm\r\n\r\nShoulder: 48 - 50 cm\r\n\r\nLength (back, from collar seam): 76 - 78 cm\r\n\r\nSleeve Length: 68 - 70 cm', '', 'Blazer Modern Fit', 'Gift', NULL, NULL, NULL, NULL, 'In Stock', NULL, NULL, 'product', 1, 1, 0, 5, 0, 0, '2025-07-10 15:45:11', '2025-07-10 15:45:11'),
(15, 17, 8, 1, '2025-07-12 06:51:57', 'Men\'s Sherpa Lined Trucker Jacket', NULL, 'Lined-Trucker-Jacket', 'main_1752184589_1.jpg', 'main_1752184589_2.jpg', 'main_1752184589_3.jpg', NULL, NULL, NULL, NULL, NULL, 22659.00, 0.00, 'Stay warm and stylish with this rugged Men\'s Sherpa Lined Trucker Jacket, designed for comfort and durability in cooler weather. This classic workwear-inspired coat features a robust canvas exterior in a rich brown hue, paired with a plush, cozy sherpa fleece lining and collar for superior warmth. The full-button front closure and two chest flap pockets with button closures add to its timeless utility design. Ideal for outdoor activities, casual everyday wear, or layering during autumn and winter, this jacket offers both practical warmth and a timeless, masculine aesthetic.\r\n\r\n', 'Small (S)\r\n\r\nChest: 104 - 108 cm\r\n\r\nShoulder: 44 - 46 cm\r\n\r\nLength (back, from collar seam): 66 - 68 cm\r\n\r\nSleeve Length: 64 - 66 cm;\r\n\r\nMedium (M)\r\n\r\nChest: 108 - 112 cm\r\n\r\nShoulder: 46 - 48 cm\r\n\r\nLength (back, from collar seam): 68 - 70 cm\r\n\r\nSleeve Length: 66 - 68 cm;\r\n\r\nLarge (L)\r\n\r\nChest: 112 - 116 cm\r\n\r\nShoulder: 48 - 50 cm\r\n\r\nLength (back, from collar seam): 70 - 72 cm\r\n\r\nSleeve Length: 68 - 70 cm;\r\n\r\nExtra Large (XL)\r\n\r\nChest: 116 - 120 cm\r\n\r\nShoulder: 50 - 52 cm\r\n\r\nLength (back, from collar seam): 72 - 74 cm\r\n\r\nSleeve Length: 70 - 72 cm', '', 'Lined Trucker Jacket', 'New', NULL, NULL, NULL, NULL, 'Out of Stock', NULL, NULL, 'product', 1, 1, 0, 5, 0, 0, '2025-07-10 21:56:29', '2025-07-12 06:51:57'),
(16, 17, 8, 1, '2025-07-10 22:05:13', 'Men\'s Lightweight Hooded Puffer Jacket', NULL, 'Hooded-Puffer-Jacket', 'main_1752185113_1.jpg', 'main_1752185113_2.jpg', 'main_1752185113_3.jpg', NULL, NULL, NULL, NULL, NULL, 31240.00, 21441.00, 'Stay warm without the bulk in this Men\'s Lightweight Hooded Puffer Jacket, an essential for chilly days and travel. This versatile jacket features horizontal quilting for even insulation distribution, offering excellent warmth-to-weight ratio. The attached hood provides extra protection against the elements, while the full-zip front allows for easy temperature regulation. Its minimalist design makes it perfect for everyday wear, layering, or as a packable option for your adventures. Ideal for autumn, mild winter days, and cool evenings.', 'Small (S)\r\n\r\nChest: 100 - 104 cm\r\n\r\nShoulder: 44 - 46 cm\r\n\r\nLength (back, from collar seam): 66 - 68 cm\r\n\r\nSleeve Length: 64 - 66 cm;\r\n\r\nMedium (M)\r\n\r\nChest: 104 - 108 cm\r\n\r\nShoulder: 46 - 48 cm\r\n\r\nLength (back, from collar seam): 68 - 70 cm\r\n\r\nSleeve Length: 66 - 68 cm;\r\n\r\nLarge (L)\r\n\r\nChest: 108 - 112 cm\r\n\r\nShoulder: 48 - 50 cm\r\n\r\nLength (back, from collar seam): 70 - 72 cm\r\n\r\nSleeve Length: 68 - 70 cm;\r\n\r\nExtra Large (XL)\r\n\r\nChest: 112 - 116 cm\r\n\r\nShoulder: 50 - 52 cm\r\n\r\nLength (back, from collar seam): 72 - 74 cm\r\n\r\nSleeve Length: 70 - 72 cm', '', 'Hooded Puffer Jacket', 'Gift', NULL, NULL, NULL, NULL, 'In Stock', NULL, NULL, 'product', 1, 1, 0, 5, 0, 0, '2025-07-10 22:05:13', '2025-07-10 22:05:13'),
(17, 14, 8, 1, '2025-07-10 22:29:08', 'Men\'s Slim Fit Pinstripe Dress Pants', NULL, 'Pinstripe-Dress-Pants', 'main_1752186548_1.webp', 'main_1752186548_2.webp', 'main_1752186548_3.webp', NULL, NULL, NULL, NULL, NULL, 19234.00, 14693.00, 'Refine your formal or smart-casual attire with these stylish Men\'s Slim Fit Pinstripe Dress Pants. Featuring a timeless black background with subtle vertical pinstripes, these trousers offer a sophisticated and elongating effect. Designed with a modern slim fit and a contemporary ankle-length cut, they are perfect for showcasing your footwear. Complete with belt loops, side pockets, and back welt pockets (implied), these pants blend classic elegance with modern tailoring. Ideal for office wear, special events, or an elevated everyday look', 'Size 28\r\n\r\nWaist: 28 inches (approx. 71 cm)\r\n\r\nHip: 34-35 inches (approx. 86-89 cm)\r\n\r\nInseam: 28-29 inches (approx. 71-74 cm - for ankle length);\r\n\r\nSize 30\r\n\r\nWaist: 30 inches (approx. 76 cm)\r\n\r\nHip: 36-37 inches (approx. 91-94 cm)\r\n\r\nInseam: 29-30 inches (approx. 74-76 cm);\r\n\r\nSize 32\r\n\r\nWaist: 32 inches (approx. 81 cm)\r\n\r\nHip: 38-39 inches (approx. 96-99 cm)\r\n\r\nInseam: 29-30 inches (approx. 74-76 cm);\r\n\r\nSize 34\r\n\r\nWaist: 34 inches (approx. 86 cm)\r\n\r\nHip: 40-41 inches (approx. 101-104 cm)\r\n\r\nInseam: 30-31 inches (approx. 76-79 cm);\r\n\r\nSize 36\r\n\r\nWaist: 36 inches (approx. 91 cm)\r\n\r\nHip: 42-43 inches (approx. 106-109 cm)\r\n\r\nInseam: 30-31 inches (approx. 76-79 cm);\r\n\r\nSize 38\r\n\r\nWaist: 38 inches (approx. 96 cm)\r\n\r\nHip: 44-45 inches (approx. 111-114 cm)\r\n\r\nInseam: 31-32 inches (approx. 79-81 cm)\r\n;\r\nSize 40\r\n\r\nWaist: 40 inches (approx. 101 cm)\r\n\r\nHip: 46-47 inches (approx. 116-119 cm)\r\n\r\nInseam: 31-32 inches (approx. 79-81 cm)', '', 'Pinstripe Dress Pants', 'Sale', NULL, NULL, NULL, NULL, 'In Stock', NULL, NULL, 'product', 1, 1, 0, 5, 0, 0, '2025-07-10 22:29:08', '2025-07-10 22:29:08'),
(18, 14, 8, 1, '2025-07-14 18:31:16', 'Men\'s Distressed Black Denim Jeans Slim Fit', NULL, 'Jeans-Slim-Fit', 'main_1752199295_1.webp', 'main_1752199295_2.webp', 'main_1752199295_3.webp', NULL, NULL, NULL, NULL, NULL, 19265.00, 14482.00, 'Achieve a rugged yet stylish look with these Men\'s Distressed Black Denim Jeans. Featuring a modern slim fit that tapers through the leg, these jeans offer a contemporary silhouette. The dark wash provides a versatile base, while strategic fading, whiskering, and light distressing (ripped details) add a worn-in, edgy character. Made from a comfortable stretch denim, these jeans are perfect for everyday casual wear, pairing easily with t-shirts, jackets, and sneakers.', 'Size 28\r\n\r\nWaist: 28 inches (approx. 71 cm)\r\n\r\nInseam: 30-31 inches (approx. 76-79 cm);\r\n\r\nSize 30\r\n\r\nWaist: 30 inches (approx. 76 cm)\r\n\r\nInseam: 30-32 inches (approx. 76-81 cm);\r\n\r\nSize 32\r\n\r\nWaist: 32 inches (approx. 81 cm)\r\n\r\nInseam: 31-32 inches (approx. 79-81 cm);\r\n\r\nSize 34\r\n\r\nWaist: 34 inches (approx. 86 cm)\r\n\r\nInseam: 31-33 inches (approx. 79-84 cm);\r\n\r\nSize 36\r\n\r\nWaist: 36 inches (approx. 91 cm)\r\n\r\nInseam: 32-33 inches (approx. 81-84 cm);\r\n\r\nSize 38\r\n\r\nWaist: 38 inches (approx. 96 cm)\r\n\r\nInseam: 32-34 inches (approx. 81-86 cm);\r\n\r\nSize 40\r\n\r\nWaist: 40 inches (approx. 101 cm)\r\n\r\nInseam: 33-34 inches (approx. 84-86 cm)\r\n\r\n', '', 'Jeans Slim Fit', 'Sale', NULL, NULL, NULL, NULL, 'In Stock', NULL, NULL, 'product', 1, 1, 22, 5, 0, 0, '2025-07-11 02:01:35', '2025-07-14 18:31:16'),
(19, 13, 8, 1, '2025-07-11 02:39:32', ' Men\'s Textured Mesh Knit Polo Shirt Short Sleeve', NULL, 'Polo-Shirt-Short', 'main_1752201572_1.webp', 'main_1752201572_2.webp', 'main_1752201572_3.webp', NULL, NULL, NULL, NULL, NULL, 11182.00, 8574.00, 'Elevate your casual wardrobe with this Men\'s Textured Mesh Knit Polo Shirt, perfect for comfort and style in warmer weather. Crafted from a unique textured mesh fabric, it offers exceptional breathability and a modern aesthetic. This polo features a classic collar, a three-button placket, and short sleeves, making it a versatile choice for various occasions. Its solid color and subtle texture ensure it\'s easy to pair with jeans, chinos, or shorts for a refined yet relaxed look.', 'Small (S)\r\n\r\nChest: 96 - 100 cm\r\n\r\nLength (back, from collar seam): 68 - 70 cm\r\n\r\nShoulder: 42 - 44 cm;\r\n\r\nMedium (M)\r\n\r\nChest: 100 - 104 cm\r\n\r\nLength (back, from collar seam): 70 - 72 cm\r\n\r\nShoulder: 44 - 46 cm;\r\n\r\nLarge (L)\r\n\r\nChest: 104 - 108 cm\r\n\r\nLength (back, from collar seam): 72 - 74 cm\r\n\r\nShoulder: 46 - 48 cm;\r\n\r\nExtra Large (XL)\r\n\r\nChest: 108 - 112 cm\r\n\r\nLength (back, from collar seam): 74 - 76 cm\r\n\r\nShoulder: 48 - 50 cm\r\n\r\n', '', 'Polo Shirt Short', 'Sale', NULL, NULL, NULL, NULL, 'In Stock', NULL, NULL, 'product', 1, 1, 0, 5, 0, 0, '2025-07-11 02:39:32', '2025-07-11 02:39:32'),
(20, 13, 8, 1, '2025-07-12 06:51:56', 'Men\'s Solid Maroon Dress Shirt Long Sleeve', NULL, 'Maroon-Dress-Shirt', 'main_1752202253_1.webp', 'main_1752202253_2.webp', 'main_1752202253_3.webp', NULL, NULL, NULL, NULL, NULL, 10942.00, 0.00, 'Add a touch of rich color to your formal or business casual wardrobe with this Men\'s Solid Maroon Dress Shirt. Crafted from a smooth, comfortable fabric, this shirt offers a polished and sophisticated look. It features a classic spread collar, a full-button front closure, and long sleeves with adjustable button cuffs (shown rolled up in the image). The versatile maroon hue makes it an excellent choice for pairing with suits, blazers, or dress trousers for various occasions, from office wear to special events. The \"FLEX\" label suggests a fabric with some stretch for enhanced comfort and movement.', 'Small (S)\r\n\r\nCollar: 38 - 39 cm\r\n\r\nChest: 96 - 100 cm\r\n\r\nLength (back, from collar seam): 74 - 76 cm\r\n\r\nSleeve Length: 62 - 64 cm;\r\n\r\nMedium (M)\r\n\r\nCollar: 39 - 40 cm\r\n\r\nChest: 100 - 104 cm\r\n\r\nLength (back, from collar seam): 76 - 78 cm\r\n\r\nSleeve Length: 64 - 66 cm;\r\n\r\nLarge (L)\r\n\r\nCollar: 41 - 42 cm\r\n\r\nChest: 104 - 108 cm\r\n\r\nLength (back, from collar seam): 78 - 80 cm\r\n\r\nSleeve Length: 66 - 68 cm;\r\n\r\nExtra Large (XL)\r\n\r\nCollar: 43 - 44 cm\r\n\r\nChest: 108 - 112 cm\r\n\r\nLength (back, from collar seam): 80 - 82 cm\r\n\r\nSleeve Length: 68 - 70 cm', '', 'Maroon Dress Shirt', 'New', NULL, NULL, NULL, NULL, 'Out of Stock', NULL, NULL, 'product', 1, 1, 0, 5, 0, 0, '2025-07-11 02:50:53', '2025-07-12 06:51:56'),
(21, 15, 8, 1, '2025-07-11 03:01:13', 'Men\'s Classic Brown Combat / Work Boots', NULL, 'Classic-Brown-Combat', 'main_1752202873_1.webp', 'main_1752202873_2.webp', 'main_1752202873_3.webp', NULL, NULL, NULL, NULL, NULL, 27894.00, 21816.00, 'Step out in rugged style with these Men\'s Classic Brown Combat/Work Boots. Designed for durability and comfort, these versatile boots feature a rich, distressed brown faux leather upper that offers a timeless, worn-in look. The lace-up front ensures a secure and adjustable fit, while a convenient side zipper allows for easy on and off. Built with a sturdy lug sole, they provide excellent traction and support for everyday wear, outdoor activities, or adding a touch of edgy sophistication to your casual attire.\r\n\r\n', 'US Men\'s Size 6\r\n\r\nApprox. Foot Length: 24 cm;\r\n\r\nUS Men\'s Size 7\r\n\r\nApprox. Foot Length: 25 cm;\r\n\r\nUS Men\'s Size 8\r\n\r\nApprox. Foot Length: 26 cm;\r\n\r\nUS Men\'s Size 9\r\n\r\nApprox. Foot Length: 27 cm;\r\n\r\nUS Men\'s Size 10\r\n\r\nApprox. Foot Length: 28 cm;\r\n\r\nUS Men\'s Size 11\r\n\r\nApprox. Foot Length: 29 cm;\r\n\r\nUS Men\'s Size 12\r\n\r\nApprox. Foot Length: 30 cm', '', 'Classic Brown Combat', 'Sale', NULL, NULL, NULL, NULL, 'In Stock', NULL, NULL, 'product', 1, 1, 0, 5, 0, 0, '2025-07-11 03:01:13', '2025-07-11 03:01:13'),
(22, 15, 8, 1, '2025-07-11 03:08:20', 'Men\'s Classic Black Cap-Toe Oxford Dress Shoes', NULL, 'Black-Cap-Toe', 'main_1752203300_1.webp', 'main_1752203300_2.webp', 'main_1752203300_3.webp', NULL, NULL, NULL, NULL, NULL, 14706.00, 10331.00, 'Elevate your formal and business attire with these timeless Men\'s Classic Black Cap-Toe Oxford Dress Shoes. Crafted with a sleek, polished finish, these shoes embody sophisticated elegance. The cap-toe design, characterized by a horizontal seam across the toe, is a hallmark of traditional formal footwear. Featuring a closed lacing system, these Oxfords offer a refined silhouette perfect for suits, tuxedos, and smart business wear. Ideal for formal events, office settings, and special occasions where a sharp, distinguished look is paramount.', 'US Men\'s Size 6\r\n\r\nApprox. Foot Length: 24 cm;\r\n\r\nUS Men\'s Size 7\r\n\r\nApprox. Foot Length: 25 cm;\r\n\r\nUS Men\'s Size 8\r\n\r\nApprox. Foot Length: 26 cm;\r\n\r\nUS Men\'s Size 9\r\n\r\nApprox. Foot Length: 27 cm;\r\n\r\nUS Men\'s Size 10\r\n\r\nApprox. Foot Length: 28 cm;\r\n\r\nUS Men\'s Size 11\r\n\r\nApprox. Foot Length: 29 cm;\r\n\r\nUS Men\'s Size 12\r\n\r\nApprox. Foot Length: 30 cm', '', 'Black Cap Toe', 'Gift', NULL, NULL, NULL, NULL, 'In Stock', NULL, NULL, 'product', 1, 1, 0, 5, 0, 0, '2025-07-11 03:08:20', '2025-07-11 03:08:20'),
(23, 18, 8, 1, '2025-07-12 12:49:33', 'Men\'s Classic Black Canvas Low-Top Sneakers', NULL, 'Low-Top-Sneakers', 'main_1752204287_1.webp', 'main_1752204287_2.webp', 'main_1752204287_3.webp', NULL, NULL, NULL, NULL, NULL, 10377.00, 7951.00, 'Embrace effortless casual style with these Men\'s Classic Black Canvas Low-Top Sneakers. Designed for everyday comfort, these versatile shoes feature a durable canvas upper in black, highlighted by stark white contrast stitching and a smooth white rubber toe cap. A classic lace-up design ensures a secure fit, while the sturdy white rubber midsole and textured outsole provide reliable grip. Finished with subtle beige accents at the heel and tongue and retro \"FASHION\" branding labels, these sneakers are a perfect addition to any casual wardrobe.', 'US Men\'s Size 6\r\n\r\nApprox. Foot Length: 24 cm;\r\n\r\nUS Men\'s Size 7\r\n\r\nApprox. Foot Length: 25 cm;\r\n\r\nUS Men\'s Size 8\r\n\r\nApprox. Foot Length: 26 cm;\r\n\r\nUS Men\'s Size 9\r\n\r\nApprox. Foot Length: 27 cm;\r\n\r\nUS Men\'s Size 10\r\n\r\nApprox. Foot Length: 28 cm;\r\n\r\nUS Men\'s Size 11\r\n\r\nApprox. Foot Length: 29 cm;\r\n\r\nUS Men\'s Size 12\r\n\r\nApprox. Foot Length: 30 cm', '', 'Low Top Sneakers', 'Sale', NULL, NULL, NULL, NULL, 'Out of Stock', NULL, NULL, 'product', 1, 1, 0, 5, 0, 0, '2025-07-11 03:24:47', '2025-07-12 12:49:33'),
(24, 18, 8, 1, '2025-07-11 03:34:18', 'Men\'s Two-Tone Athletic Sneakers Grey & Black', NULL, 'Two-Tone-Athletic', 'main_1752204858_1.webp', 'main_1752204858_2.webp', 'main_1752204858_3.webp', NULL, NULL, NULL, NULL, NULL, 10361.00, 6951.00, 'Step up your casual footwear game with these Men\'s Two-Tone Athletic Sneakers. Combining a modern design with practical comfort, these trainers feature a stylish mix of black and various shades of grey synthetic panels. The lace-up closure provides a secure fit, while the padded collar and tongue ensure comfort for all-day wear. The clean white midsole and durable rubber outsole offer excellent support and a crisp, contrasting look. Light blue accents add a subtle pop of color. Perfect for adding a sporty, on-trend touch to your everyday outfits, whether for light activity or urban exploration.', 'US Men\'s Size 6\r\n\r\nApprox. Foot Length: 24 cm;\r\n\r\nUS Men\'s Size 7\r\n\r\nApprox. Foot Length: 25 cm;\r\n\r\nUS Men\'s Size 8\r\n\r\nApprox. Foot Length: 26 cm;\r\n\r\nUS Men\'s Size 9\r\n\r\nApprox. Foot Length: 27 cm;\r\n\r\nUS Men\'s Size 10\r\n\r\nApprox. Foot Length: 28 cm;\r\n\r\nUS Men\'s Size 11\r\n\r\nApprox. Foot Length: 29 cm;\r\n\r\nUS Men\'s Size 12\r\n\r\nApprox. Foot Length: 30 cm', '', 'Two Tone Athletic', 'Sale', NULL, NULL, NULL, NULL, 'In Stock', NULL, NULL, 'product', 1, 1, 0, 5, 0, 0, '2025-07-11 03:34:18', '2025-07-11 03:34:18'),
(25, 16, 8, 1, '2025-07-12 07:01:25', 'Men\'s Casual Grey Track Trouser Loose Fit', NULL, 'Grey-Track-Trouser', 'main_1752206947_1.webp', 'main_1752206947_2.webp', 'main_1752206947_3.webp', NULL, NULL, NULL, NULL, NULL, 10959.00, 8132.00, 'Experience ultimate comfort and relaxed style with these Men\'s Casual Grey Track Pants. Designed for versatility, these trousers feature a comfortable loose fit with a modern straight leg, making them ideal for lounging, casual outings, or light activity. The soft grey fabric is complemented by vertical stitching details that add a subtle tailored touch. An adjustable drawstring waist ensures a perfect fit, while side pockets provide practicality. Pair them with sneakers and a casual top for an effortlessly cool look.\r\n\r\n', 'Small (S)\r\n\r\nWaist (relaxed): 70-75 cm\r\n\r\nInseam: 74-76 cm\r\n\r\nHip: 96-100 cm;\r\n\r\nMedium (M)\r\n\r\nWaist (relaxed): 75-80 cm\r\n\r\nInseam: 76-78 cm\r\n\r\nHip: 100-104 cm;\r\n\r\nLarge (L)\r\n\r\nWaist (relaxed): 80-85 cm\r\n\r\nInseam: 78-80 cm\r\n\r\nHip: 104-108 cm;\r\n\r\nExtra Large (XL)\r\n\r\nWaist (relaxed): 85-90 cm\r\n\r\nInseam: 80-82 cm\r\n\r\nHip: 108-112 cm\r\n\r\n', '', 'Grey Track Trouser', 'Sale', NULL, NULL, NULL, NULL, 'Out of Stock', NULL, NULL, 'product', 1, 1, 0, 5, 0, 0, '2025-07-11 04:09:07', '2025-07-12 07:01:25'),
(26, 17, 9, 1, '2025-07-11 04:18:58', 'Women\'s Red Hooded Rain Parka Plaid Lined', NULL, 'Red-Hooded-Rain', 'main_1752207538_1.webp', 'main_1752207538_2.webp', 'main_1752207538_3.webp', NULL, NULL, NULL, NULL, NULL, 29474.00, 27348.00, 'Stay stylishly prepared for changing weather with this vibrant Women\'s Red Hooded Rain Parka. This lightweight yet protective jacket features a striking red exterior that offers a pop of color, while its comfortable and eye-catching plaid lining (in shades of red, black, white, and tan) adds a touch of classic charm. Designed with practical details, it includes a full-zip front with a snap-button placket for extra weather protection, an adjustable drawstring waist for a flattering silhouette, and a functional hood. Two large flap pockets provide convenient storage. Perfect for casual outings, commutes, or light outdoor adventures in unpredictable weather.', 'Small (S)\r\n\r\nBust: 90 - 95 cm\r\n\r\nWaist (adjustable): 70 - 75 cm\r\n\r\nLength (back): 75 - 78 cm\r\n\r\nSleeve Length: 59 - 61 cm;\r\n\r\nMedium (M)\r\n\r\nBust: 95 - 100 cm\r\n\r\nWaist (adjustable): 75 - 80 cm\r\n\r\nLength (back): 77 - 80 cm\r\n\r\nSleeve Length: 60 - 62 cm;\r\n\r\nLarge (L)\r\n\r\nBust: 100 - 105 cm\r\n\r\nWaist (adjustable): 80 - 85 cm\r\n\r\nLength (back): 79 - 82 cm\r\n\r\nSleeve Length: 61 - 63 cm;\r\n\r\nExtra Large (XL)\r\n\r\nBust: 105 - 110 cm\r\n\r\nWaist (adjustable): 85 - 90 cm\r\n\r\nLength (back): 81 - 84 cm\r\n\r\nSleeve Length: 62 - 64 cm\r\n\r\n', '', 'Red Hooded Rain', 'Sale', NULL, NULL, NULL, NULL, 'In Stock', NULL, NULL, 'product', 1, 1, 0, 5, 0, 0, '2025-07-11 04:18:58', '2025-07-11 04:18:58'),
(27, 17, 9, 1, '2025-07-11 06:34:55', 'Women\'s Classic Diamond Quilted Jacket Button Front', NULL, 'Jacket-Button-Front', 'main_1752215695_1.webp', 'main_1752215695_2.webp', 'main_1752215695_3.webp', NULL, NULL, NULL, NULL, NULL, 24412.00, 0.00, 'Add a touch of timeless elegance and cozy comfort to your wardrobe with this Women\'s Classic Diamond Quilted Jacket. Perfect for transitional weather, this lightweight jacket features a sophisticated diamond quilting pattern throughout, offering both warmth and a chic texture. It boasts a traditional rounded collar, a full-button front closure (with visible snaps), and two large, practical patch pockets at the front. The versatile beige/tan hue makes it an ideal layering piece over sweaters, blouses, or tees, suitable for casual outings, weekend strolls, or a smart-casual office look.', 'Small (S)\r\n\r\nBust: 90 - 95 cm\r\n\r\nLength (back, from collar seam): 58 - 60 cm\r\n\r\nShoulder: 38 - 40 cm\r\n\r\nSleeve Length: 58 - 60 cm;\r\n\r\nMedium (M)\r\n\r\nBust: 95 - 100 cm\r\n\r\nLength (back, from collar seam): 60 - 62 cm\r\n\r\nShoulder: 40 - 42 cm\r\n\r\nSleeve Length: 60 - 62 cm;\r\n\r\nLarge (L)\r\n\r\nBust: 100 - 105 cm\r\n\r\nLength (back, from collar seam): 62 - 64 cm\r\n\r\nShoulder: 42 - 44 cm\r\n\r\nSleeve Length: 62 - 64 cm;\r\n\r\nExtra Large (XL)\r\n\r\nBust: 105 - 110 cm\r\n\r\nLength (back, from collar seam): 64 - 66 cm\r\n\r\nShoulder: 44 - 46 cm\r\n\r\nSleeve Length: 64 - 66 cm', '', 'Jacket Button Front', 'New', NULL, NULL, NULL, NULL, 'In Stock', NULL, NULL, 'product', 1, 1, 0, 5, 0, 0, '2025-07-11 06:34:55', '2025-07-11 06:34:55'),
(28, 17, 9, 1, '2025-07-11 07:08:09', 'Women\'s Plaid Hooded Sweater Jacket Full-Zip', NULL, 'Jacket-Full-Zip', 'main_1752217689_1.webp', 'main_1752217689_2.webp', 'main_1752217689_3.webp', NULL, NULL, NULL, NULL, NULL, 18652.00, 0.00, 'Wrap yourself in warmth and style with this cozy Women\'s Plaid Hooded Sweater Jacket. This eye-catching piece features a bold, classic red and black plaid pattern knitted from a soft, chunky fabric. Designed for a comfortable, relaxed fit with dropped shoulders, it\'s the perfect layering piece for cooler days. The jacket includes a full-zip front for easy wear, an attached hood for extra warmth, and two spacious front pockets to keep your hands warm or store essentials. Ideal for adding a pop of color and comfort to any casual outfit.', 'Small (S)\r\n\r\nBust: 90 - 98 cm\r\n\r\nLength (back, from collar seam): 60 - 62 cm\r\n\r\nShoulder: 40 - 44 cm (dropped shoulder style)\r\n\r\nSleeve Length: 55 - 57 cm;\r\n\r\nMedium (M)\r\n\r\nBust: 98 - 106 cm\r\n\r\nLength (back, from collar seam): 62 - 64 cm\r\n\r\nShoulder: 44 - 48 cm (dropped shoulder style)\r\n\r\nSleeve Length: 56 - 58 cm;\r\n\r\nLarge (L)\r\n\r\nBust: 106 - 114 cm\r\n\r\nLength (back, from collar seam): 64 - 66 cm\r\n\r\nShoulder: 48 - 52 cm (dropped shoulder style)\r\n\r\nSleeve Length: 57 - 59 cm;\r\n\r\nExtra Large (XL)\r\n\r\nBust: 114 - 122 cm\r\n\r\nLength (back, from collar seam): 66 - 68 cm\r\n\r\nShoulder: 52 - 56 cm (dropped shoulder style)\r\n\r\nSleeve Length: 58 - 60 cm', '', 'Jacket Full Zip', 'New', NULL, NULL, NULL, NULL, 'In Stock', NULL, NULL, 'product', 1, 1, 0, 5, 0, 0, '2025-07-11 07:08:09', '2025-07-11 07:08:09'),
(29, 14, 9, 1, '2025-07-11 07:34:08', 'Women\'s Floral & Plaid Wide-Leg Trousers', NULL, 'Plaid-Wide-Leg', 'main_1752219248_1.webp', 'main_1752219248_2.webp', 'main_1752219248_3.webp', NULL, NULL, NULL, NULL, NULL, 10188.00, 7539.00, 'Embrace a relaxed yet stylish look with these unique Women\'s Floral & Plaid Wide-Leg Trousers. These pants feature an eye-catching, multi-layered print combining large, muted flowers over a subtle green and grey plaid background. The comfortable elastic waistband provides an easy, pull-on fit, while the wide-leg silhouette offers a flowing, breezy feel. Crafted from a lightweight, breathable fabric, these trousers are ideal for adding a touch of casual elegance and bohemian flair to your warm-weather wardrobe or loungewear collection.', 'Small (S)\r\n\r\nWaist (relaxed): 64 - 70 cm\r\n\r\nHip: 90 - 95 cm\r\n\r\nInseam: 70 - 72 cm;\r\n\r\nMedium (M)\r\n\r\nWaist (relaxed): 70 - 76 cm\r\n\r\nHip: 95 - 100 cm\r\n\r\nInseam: 72 - 74 cm;\r\n\r\nLarge (L)\r\n\r\nWaist (relaxed): 76 - 82 cm\r\n\r\nHip: 100 - 105 cm\r\n\r\nInseam: 74 - 76 cm;\r\n\r\nExtra Large (XL)\r\n\r\nWaist (relaxed): 82 - 88 cm\r\n\r\nHip: 105 - 110 cm\r\n\r\nInseam: 76 - 78 cm\r\n\r\n', '', 'Plaid Wide Leg', 'Sale', NULL, NULL, NULL, NULL, 'In Stock', NULL, NULL, 'product', 1, 1, 0, 5, 0, 0, '2025-07-11 07:34:08', '2025-07-11 07:34:08'),
(30, 14, 9, 1, '2025-07-12 07:40:47', 'Women\'s Charcoal Grey Elastic Waist Ankle Pants', NULL, 'Waist-Ankle-Pants', 'main_1752219815_1.webp', 'main_1752219815_2.webp', 'main_1752219815_3.webp', NULL, NULL, NULL, NULL, NULL, 7539.00, 0.00, 'Combine comfort and contemporary style with these versatile Women\'s Charcoal Grey Ankle Pants. Crafted from a smooth, breathable fabric, these trousers are designed for effortless wear. They feature a comfortable elasticated waistband with stylish ruching detail and an adjustable drawstring, ensuring a perfect fit. With convenient side pockets and a modern ankle length, these pants offer a relaxed yet tailored silhouette, enhanced by subtle side slits at the hem. Ideal for activewear, casual outings, travel, or elevated loungewear, they are a foundational piece for any modern wardrobe.', 'Small (S)\r\n\r\nWaist (relaxed): 64 - 70 cm\r\n\r\nHip: 90 - 95 cm\r\n\r\nInseam: 66 - 68 cm;\r\n\r\nMedium (M)\r\n\r\nWaist (relaxed): 70 - 76 cm\r\n\r\nHip: 95 - 100 cm\r\n\r\nInseam: 68 - 70 cm;\r\n\r\nLarge (L)\r\n\r\nWaist (relaxed): 76 - 82 cm\r\n\r\nHip: 100 - 105 cm\r\n\r\nInseam: 70 - 72 cm;\r\n\r\nExtra Large (XL)\r\n\r\nWaist (relaxed): 82 - 88 cm\r\n\r\nHip: 105 - 110 cm\r\n\r\nInseam: 72 - 74 cm', '', 'Waist Ankle Pants', 'New', NULL, NULL, NULL, NULL, 'Out of Stock', NULL, NULL, 'product', 1, 1, 0, 5, 0, 0, '2025-07-11 07:43:35', '2025-07-12 07:40:47'),
(31, 13, 9, 1, '2025-07-12 07:53:20', ' Women\'s Black Pinstripe Button-Up Shirt', NULL, 'Button-Up-Shirt', 'main_1752222527_1.webp', 'main_1752222527_2.webp', 'main_1752222527_3.webp', NULL, NULL, NULL, NULL, NULL, 10005.00, 6194.00, 'Embrace a timeless and sophisticated look with this Women\'s Black Pinstripe Button-Up Shirt. This classic blouse features crisp vertical white pinstripes on a sleek black background, offering an elongating and elegant aesthetic. Designed with a relaxed fit, it provides comfortable wear and effortless drape. It comes with a traditional collar, a full-button front closure, and long sleeves with buttoned cuffs (often rolled up for a casual vibe). Perfect for professional settings, smart-casual ensembles, or layered over a camisole for a chic, open look.', 'Small (S)\r\n\r\nBust: 90 - 95 cm\r\n\r\nLength (back, from collar seam): 68 - 70 cm\r\n\r\nShoulder: 38 - 40 cm\r\n\r\nSleeve Length: 58 - 60 cm;\r\n\r\nMedium (M)\r\n\r\nBust: 95 - 100 cm\r\n\r\nLength (back, from collar seam): 70 - 72 cm\r\n\r\nShoulder: 40 - 42 cm\r\n\r\nSleeve Length: 60 - 62 cm;\r\n\r\nLarge (L)\r\n\r\nBust: 100 - 105 cm\r\n\r\nLength (back, from collar seam): 72 - 74 cm\r\n\r\nShoulder: 42 - 44 cm\r\n\r\nSleeve Length: 62 - 64 cm;\r\n\r\nExtra Large (XL)\r\n\r\nBust: 105 - 110 cm\r\n\r\nLength (back, from collar seam): 74 - 76 cm\r\n\r\nShoulder: 44 - 46 cm\r\n\r\nSleeve Length: 64 - 66 cm\r\n\r\n', '', 'Button Up Shirt', 'Sale', NULL, NULL, NULL, NULL, 'Out of Stock', NULL, NULL, 'product', 1, 1, 0, 5, 0, 0, '2025-07-11 08:28:47', '2025-07-12 07:53:20'),
(32, 13, 9, 1, '2025-07-12 12:49:33', 'Women\'s Oversized Plaid/Color Block Button-Up Shirt', NULL, 'Block-Button-Up', 'main_1752223251_1.webp', 'main_1752223251_2.webp', 'main_1752223251_3.webp', NULL, NULL, NULL, NULL, NULL, 11864.00, 8817.00, 'Achieve a relaxed, contemporary look with this Women\'s Oversized Button-Up Shirt. This blouse features a stylish plaid or color-block pattern in rich, earthy tones of brown, beige, tan, and dark grey. Designed for a comfortable, loose fit, it offers a modern silhouette with a fashionable high-low hemline. The shirt includes a stand collar, a full button-front closure with contrasting white buttons, and a single chest pocket. Crafted from a lightweight fabric, it is ideal for casual wear, versatile enough to be worn tucked, untucked, or layered over a top.', 'Small (S)\r\n\r\nBust: 100 - 105 cm\r\n\r\nLength (back): 75 - 78 cm (High-low hem)\r\n\r\nShoulder (dropped): 50+ cm;\r\n\r\nMedium (M)\r\n\r\nBust: 105 - 110 cm\r\n\r\nLength (back): 77 - 80 cm (High-low hem)\r\n\r\nShoulder (dropped): 52+ cm;\r\n\r\nLarge (L)\r\n\r\nBust: 110 - 115 cm\r\n\r\nLength (back): 79 - 82 cm (High-low hem)\r\n\r\nShoulder (dropped): 54+ cm;\r\n\r\nExtra Large (XL)\r\n\r\nBust: 115 - 120 cm\r\n\r\nLength (back): 81 - 84 cm (High-low hem)\r\n\r\nShoulder (dropped): 56+ cm', '', 'Block Button Up', 'Sale', NULL, NULL, NULL, NULL, 'Out of Stock', NULL, NULL, 'product', 1, 1, 0, 5, 0, 0, '2025-07-11 08:40:51', '2025-07-12 12:49:33'),
(33, 15, 9, 1, '2025-07-11 08:58:02', ' Women\'s Pointed Toe Leopard Print Shoes Flats', NULL, 'Print-Shoes-Flats', 'main_1752224282_1.webp', 'main_1752224282_2.webp', 'main_1752224282_3.webp', NULL, NULL, NULL, NULL, NULL, 14003.00, 0.00, 'Elevate your everyday style with these chic Women\'s Pointed Toe Flats. These shoes feature a sophisticated contrast design, combining a bold leopard print on the toe with smooth, solid brown side panels for a modern color-block effect. The pointed toe silhouette and classic flat design offer a sleek and versatile look. Designed as a comfortable slip-on, these flats are perfect for office wear, casual outings, or adding a stylish flair to any outfit.', 'US Women\'s Size 6\r\n\r\nApprox. Foot Length: 22.5 cm;\r\n\r\nUS Women\'s Size 7\r\n\r\nApprox. Foot Length: 23.5 cm;\r\n\r\nUS Women\'s Size 8\r\n\r\nApprox. Foot Length: 24.5 cm;\r\n\r\nUS Women\'s Size 9\r\n\r\nApprox. Foot Length: 25.5 cm;\r\n\r\nUS Women\'s Size 10\r\n\r\nApprox. Foot Length: 26.5 cm;\r\n\r\nUS Women\'s Size 11\r\n\r\nApprox. Foot Length: 27.5 cm', '', 'Print Shoes Flats', 'New', NULL, NULL, NULL, NULL, 'In Stock', NULL, NULL, 'product', 1, 1, 0, 5, 0, 0, '2025-07-11 08:58:02', '2025-07-11 08:58:02'),
(34, 15, 9, 1, '2025-07-12 08:07:00', 'Women\'s Nude Pointed Toe Slingback Heels', NULL, 'Toe-Slingback-Heels', 'main_1752225283_1.webp', 'main_1752225283_2.webp', 'main_1752225283_3.webp', NULL, NULL, NULL, NULL, NULL, 25001.00, 14908.00, 'Step into sophisticated elegance with these timeless Women\'s Nude Pointed Toe Slingback Heels. These versatile pumps feature a chic pointed toe design and a modern mid-height flared or block heel, offering both comfort and style. The classic slingback strap ensures a secure fit around the ankle, while the smooth, polished finish in a versatile nude/beige hue makes them a perfect match for any outfit. Ideal for formal events, office wear, or elevating your casual ensembles.', 'US Women\'s Size 6\r\n\r\nApprox. Foot Length: 22.5 cm;\r\n\r\nUS Women\'s Size 7\r\n\r\nApprox. Foot Length: 23.5 cm;\r\n\r\nUS Women\'s Size 8\r\n\r\nApprox. Foot Length: 24.5 cm;\r\n\r\nUS Women\'s Size 9\r\n\r\nApprox. Foot Length: 25.5 cm;\r\n\r\nUS Women\'s Size 10\r\n\r\nApprox. Foot Length: 26.5 cm;\r\n\r\nUS Women\'s Size 11\r\n\r\nApprox. Foot Length: 27.5 cm', '', 'Toe Slingback Heels', 'Gift', NULL, NULL, NULL, NULL, 'Out of Stock', NULL, NULL, 'product', 1, 1, 0, 5, 0, 0, '2025-07-11 09:14:43', '2025-07-12 08:07:00'),
(35, 18, 9, 1, '2025-07-12 07:40:47', 'Women\'s Chunky Platform Sneakers Shoes', NULL, 'Chunky-Platform-Sneakers', 'main_1752225877_1.webp', 'main_1752225877_2.webp', 'main_1752225877_3.webp', NULL, NULL, NULL, NULL, NULL, 14572.00, 11195.00, 'Step into comfort and style with these trendy Women\'s Chunky Platform Sneakers. Featuring a modern silhouette with an elevated, sculpted sole, these trainers combine fashion-forward design with everyday wearability. The upper is crafted from breathable mesh paired with subtle beige/taupe synthetic overlays, creating a clean, monochromatic look. Wavy design details on the sides add a unique touch, while the lace-up closure ensures a secure fit. Perfect for athleisure looks, casual outings, or adding a stylish edge to your streetwear ensemble.', 'US Women\'s Size 5\r\n\r\nApprox. Foot Length: 22 cm;\r\n\r\nUS Women\'s Size 6\r\n\r\nApprox. Foot Length: 22.9 cm;\r\n\r\nUS Women\'s Size 7\r\n\r\nApprox. Foot Length: 23.7 cm;\r\n\r\nUS Women\'s Size 8\r\n\r\nApprox. Foot Length: 24.6 cm;\r\n\r\nUS Women\'s Size 9\r\n\r\nApprox. Foot Length: 25.4 cm;\r\n\r\nUS Women\'s Size 10\r\n\r\nApprox. Foot Length: 26.2 cm;\r\n\r\nUS Women\'s Size 11\r\n\r\nApprox. Foot Length: 27.1 cm', '', 'Chunky Platform Sneakers', 'Sale', NULL, NULL, NULL, NULL, 'Out of Stock', NULL, NULL, 'product', 1, 1, 0, 5, 0, 0, '2025-07-11 09:24:37', '2025-07-12 07:40:47');

--
-- Triggers `products`
--
DELIMITER $$
CREATE TRIGGER `log_admin_actions_products_safe` AFTER UPDATE ON `products` FOR EACH ROW BEGIN 
            IF OLD.stock_quantity != NEW.stock_quantity THEN
                INSERT INTO admin_logs (admin_id, action, table_name, record_id, old_values, new_values, ip_address) 
                VALUES (1, 'UPDATE', 'products', NEW.product_id, 
                       CONCAT('stock:', OLD.stock_quantity), 
                       CONCAT('stock:', NEW.stock_quantity), 
                       '127.0.0.1');
            END IF;
        END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `product_answers`
--

CREATE TABLE `product_answers` (
  `answer_id` int(10) NOT NULL,
  `question_id` int(10) NOT NULL,
  `answerer_email` varchar(100) NOT NULL,
  `answerer_name` varchar(100) NOT NULL,
  `answer_text` text NOT NULL,
  `answer_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `helpful_votes` int(10) DEFAULT 0,
  `status` enum('pending','approved','rejected') DEFAULT 'pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `product_categories`
--

CREATE TABLE `product_categories` (
  `p_cat_id` int(10) NOT NULL,
  `p_cat_title` varchar(255) NOT NULL,
  `p_cat_top` enum('yes','no') DEFAULT 'no',
  `p_cat_image` text DEFAULT NULL,
  `p_cat_desc` text DEFAULT NULL,
  `sizing_type` enum('clothing','shoes','custom','none') DEFAULT 'clothing',
  `p_cat_status` enum('active','inactive') DEFAULT 'active',
  `created_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_date` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `product_categories`
--

INSERT INTO `product_categories` (`p_cat_id`, `p_cat_title`, `p_cat_top`, `p_cat_image`, `p_cat_desc`, `sizing_type`, `p_cat_status`, `created_date`, `updated_date`) VALUES
(13, 'Shirts', 'yes', 'tshirticn.png', NULL, 'clothing', 'active', '2025-07-10 07:47:28', '2025-07-10 07:47:28'),
(14, 'Pants', 'yes', 'panticn.png', NULL, 'clothing', 'active', '2025-07-10 07:47:49', '2025-07-10 07:47:49'),
(15, 'Shoes', 'yes', 'shoesicn.png', NULL, 'clothing', 'active', '2025-07-10 07:48:07', '2025-07-10 07:48:07'),
(16, 'Trousers', 'yes', 'trousericn.png', NULL, 'clothing', 'active', '2025-07-10 07:48:30', '2025-07-10 07:48:30'),
(17, 'Coats&Jackets', 'yes', 'coaticn.png', NULL, 'clothing', 'active', '2025-07-10 07:48:53', '2025-07-10 07:50:06'),
(18, 'Sneakers&Joggers', 'yes', 'sneakericn.png', NULL, 'clothing', 'active', '2025-07-10 07:49:47', '2025-07-10 07:49:47');

-- --------------------------------------------------------

--
-- Stand-in structure for view `product_details_view`
-- (See below for the actual view)
--
CREATE TABLE `product_details_view` (
`product_id` int(10)
,`p_cat_id` int(10)
,`cat_id` int(10)
,`manufacturer_id` int(10)
,`date` timestamp
,`product_title` varchar(500)
,`product_brand` varchar(100)
,`product_url` varchar(500)
,`product_img1` text
,`product_img2` text
,`product_img3` text
,`product_img4` text
,`product_img5` text
,`product_img6` text
,`product_img7` text
,`product_img8` text
,`product_price` decimal(10,2)
,`product_psp_price` decimal(10,2)
,`product_desc` text
,`product_features` text
,`product_video` text
,`product_keywords` text
,`product_label` varchar(100)
,`product_weight` varchar(50)
,`product_dimensions` varchar(100)
,`product_model` varchar(100)
,`product_warranty` varchar(200)
,`product_availability` enum('In Stock','Out of Stock','Pre-Order','Limited Stock')
,`shipping_info` text
,`return_policy` text
,`status` enum('product','draft','discontinued')
,`has_variants` tinyint(1)
,`has_sizes` tinyint(1)
,`stock_quantity` int(10)
,`min_stock_alert` int(10)
,`views_count` int(10)
,`sales_count` int(10)
,`created_date` timestamp
,`updated_date` timestamp
,`p_cat_title` varchar(255)
,`sizing_type` enum('clothing','shoes','custom','none')
,`cat_title` varchar(255)
,`manufacturer_title` varchar(255)
,`total_stock` decimal(32,0)
,`avg_rating` decimal(14,4)
,`review_count` bigint(21)
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `product_performance_view`
-- (See below for the actual view)
--
CREATE TABLE `product_performance_view` (
`product_id` int(10)
,`product_title` varchar(500)
,`product_price` decimal(10,2)
,`stock_quantity` int(10)
,`views_count` int(10)
,`sales_count` int(10)
,`avg_rating` decimal(14,4)
,`total_reviews` bigint(21)
,`cart_count` bigint(21)
,`wishlist_count` bigint(21)
);

-- --------------------------------------------------------

--
-- Table structure for table `product_questions`
--

CREATE TABLE `product_questions` (
  `question_id` int(10) NOT NULL,
  `product_id` int(10) NOT NULL,
  `customer_email` varchar(100) NOT NULL,
  `customer_name` varchar(100) NOT NULL,
  `question_text` text NOT NULL,
  `question_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('pending','approved','rejected') DEFAULT 'pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `product_reviews`
--

CREATE TABLE `product_reviews` (
  `review_id` int(10) NOT NULL,
  `product_id` int(10) NOT NULL,
  `customer_email` varchar(255) NOT NULL,
  `customer_name` varchar(255) NOT NULL,
  `review_title` varchar(500) DEFAULT NULL,
  `review_text` text NOT NULL,
  `review_rating` int(1) NOT NULL CHECK (`review_rating` >= 1 and `review_rating` <= 5),
  `review_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `verified_purchase` tinyint(1) DEFAULT 0,
  `helpful_votes` int(10) DEFAULT 0,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `admin_reply` text DEFAULT NULL,
  `updated_date` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `product_sizes`
--

CREATE TABLE `product_sizes` (
  `ps_id` int(10) NOT NULL,
  `product_id` int(10) NOT NULL,
  `size_id` int(10) NOT NULL,
  `stock_quantity` int(10) DEFAULT 0,
  `price_adjustment` decimal(10,2) DEFAULT 0.00,
  `ps_status` enum('active','inactive') DEFAULT 'active',
  `created_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_date` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `product_sizes`
--

INSERT INTO `product_sizes` (`ps_id`, `product_id`, `size_id`, `stock_quantity`, `price_adjustment`, `ps_status`, `created_date`, `updated_date`) VALUES
(82, 6, 2, 12, 0.00, 'active', '2025-07-10 08:01:25', '2025-07-10 08:01:25'),
(83, 6, 3, 12, 0.00, 'active', '2025-07-10 08:01:25', '2025-07-14 18:12:06'),
(84, 6, 4, 13, 0.00, 'active', '2025-07-10 08:01:25', '2025-07-14 14:32:36'),
(85, 6, 5, 12, 0.00, 'active', '2025-07-10 08:01:25', '2025-07-14 16:02:48'),
(86, 7, 2, 15, 0.00, 'active', '2025-07-10 09:46:15', '2025-07-10 09:46:15'),
(87, 7, 3, 13, 0.00, 'active', '2025-07-10 09:46:15', '2025-07-10 09:46:15'),
(88, 7, 4, 14, 0.00, 'active', '2025-07-10 09:46:15', '2025-07-10 09:46:15'),
(89, 7, 5, 11, 0.00, 'active', '2025-07-10 09:46:15', '2025-07-10 09:46:15'),
(90, 8, 44, 12, 0.00, 'active', '2025-07-10 11:32:41', '2025-07-10 11:32:41'),
(91, 8, 45, 15, 0.00, 'active', '2025-07-10 11:32:41', '2025-07-10 11:32:41'),
(92, 8, 46, 13, 0.00, 'active', '2025-07-10 11:32:41', '2025-07-10 11:32:41'),
(93, 8, 47, 11, 0.00, 'active', '2025-07-10 11:32:41', '2025-07-10 11:32:41'),
(94, 9, 44, 12, 0.00, 'active', '2025-07-10 12:21:19', '2025-07-10 12:21:19'),
(95, 9, 45, 16, 0.00, 'active', '2025-07-10 12:21:19', '2025-07-10 12:21:19'),
(96, 9, 46, 15, 0.00, 'active', '2025-07-10 12:21:19', '2025-07-10 12:21:19'),
(97, 9, 47, 14, 0.00, 'active', '2025-07-10 12:21:19', '2025-07-10 12:21:19'),
(98, 10, 1, 12, 0.00, 'active', '2025-07-10 12:31:36', '2025-07-10 12:31:36'),
(99, 10, 2, 11, 0.00, 'active', '2025-07-10 12:31:36', '2025-07-10 12:31:36'),
(100, 10, 3, 13, 0.00, 'active', '2025-07-10 12:31:36', '2025-07-10 12:31:36'),
(101, 10, 4, 15, 0.00, 'active', '2025-07-10 12:31:36', '2025-07-10 12:31:36'),
(102, 11, 2, 11, 0.00, 'active', '2025-07-10 12:39:51', '2025-07-10 12:39:51'),
(103, 11, 3, 12, 0.00, 'active', '2025-07-10 12:39:51', '2025-07-10 12:39:51'),
(104, 11, 4, 13, 0.00, 'active', '2025-07-10 12:39:51', '2025-07-10 12:39:51'),
(105, 11, 6, 14, 0.00, 'active', '2025-07-10 12:39:51', '2025-07-10 12:39:51'),
(106, 12, 23, 11, 0.00, 'active', '2025-07-10 12:49:34', '2025-07-10 12:49:34'),
(107, 12, 24, 12, 0.00, 'active', '2025-07-10 12:49:34', '2025-07-10 12:49:34'),
(108, 12, 25, 14, 0.00, 'active', '2025-07-10 12:49:34', '2025-07-10 12:49:34'),
(109, 12, 27, 11, 0.00, 'active', '2025-07-10 12:49:34', '2025-07-10 12:49:34'),
(110, 12, 28, 16, 0.00, 'active', '2025-07-10 12:49:34', '2025-07-10 12:49:34'),
(111, 13, 2, 14, 0.00, 'active', '2025-07-10 15:38:05', '2025-07-10 15:38:05'),
(112, 13, 3, 12, 0.00, 'active', '2025-07-10 15:38:05', '2025-07-10 15:38:05'),
(113, 13, 4, 13, 0.00, 'active', '2025-07-10 15:38:05', '2025-07-10 15:38:05'),
(114, 13, 5, 11, 0.00, 'active', '2025-07-10 15:38:05', '2025-07-10 15:38:05'),
(115, 14, 2, 14, 0.00, 'active', '2025-07-10 15:45:11', '2025-07-10 15:45:11'),
(116, 14, 3, 16, 0.00, 'active', '2025-07-10 15:45:11', '2025-07-10 15:45:11'),
(117, 14, 4, 12, 0.00, 'active', '2025-07-10 15:45:11', '2025-07-10 15:45:11'),
(118, 15, 2, 12, 0.00, 'active', '2025-07-10 21:56:29', '2025-07-10 21:56:29'),
(119, 15, 3, 11, 0.00, 'active', '2025-07-10 21:56:29', '2025-07-10 21:56:29'),
(120, 15, 4, 13, 0.00, 'active', '2025-07-10 21:56:29', '2025-07-10 21:56:29'),
(121, 15, 5, 14, 0.00, 'active', '2025-07-10 21:56:29', '2025-07-10 21:56:29'),
(122, 16, 2, 11, 0.00, 'active', '2025-07-10 22:05:13', '2025-07-10 22:05:13'),
(123, 16, 3, 13, 0.00, 'active', '2025-07-10 22:05:13', '2025-07-10 22:05:13'),
(124, 16, 4, 15, 0.00, 'active', '2025-07-10 22:05:13', '2025-07-10 22:05:13'),
(125, 16, 5, 16, 0.00, 'active', '2025-07-10 22:05:13', '2025-07-10 22:05:13'),
(126, 17, 48, 12, 0.00, 'active', '2025-07-10 22:29:08', '2025-07-10 22:29:08'),
(127, 17, 49, 13, 0.00, 'active', '2025-07-10 22:29:08', '2025-07-10 22:29:08'),
(128, 17, 50, 14, 0.00, 'active', '2025-07-10 22:29:08', '2025-07-10 22:29:08'),
(129, 17, 51, 16, 0.00, 'active', '2025-07-10 22:29:08', '2025-07-10 22:29:08'),
(130, 17, 54, 16, 0.00, 'active', '2025-07-10 22:29:08', '2025-07-10 22:29:08'),
(131, 18, 48, 2, 0.00, 'active', '2025-07-11 02:01:35', '2025-07-11 02:01:35'),
(132, 18, 50, 0, 0.00, 'active', '2025-07-11 02:01:35', '2025-07-14 12:30:28'),
(133, 18, 52, 1, 0.00, 'active', '2025-07-11 02:01:35', '2025-07-14 14:18:36'),
(134, 18, 53, 1, 0.00, 'active', '2025-07-11 02:01:35', '2025-07-14 18:31:16'),
(135, 19, 2, 15, 0.00, 'active', '2025-07-11 02:39:32', '2025-07-11 02:39:32'),
(136, 19, 3, 7, 0.00, 'active', '2025-07-11 02:39:32', '2025-07-11 02:39:32'),
(137, 19, 4, 2, 0.00, 'active', '2025-07-11 02:39:32', '2025-07-11 02:39:32'),
(138, 19, 5, 16, 0.00, 'active', '2025-07-11 02:39:32', '2025-07-11 02:39:32'),
(139, 20, 2, 11, 0.00, 'active', '2025-07-11 02:50:53', '2025-07-11 02:50:53'),
(140, 20, 3, 12, 0.00, 'active', '2025-07-11 02:50:53', '2025-07-11 02:50:53'),
(141, 20, 4, 13, 0.00, 'active', '2025-07-11 02:50:53', '2025-07-11 02:50:53'),
(142, 20, 5, 14, 0.00, 'active', '2025-07-11 02:50:53', '2025-07-11 02:50:53'),
(143, 21, 8, 11, 0.00, 'active', '2025-07-11 03:01:13', '2025-07-11 03:01:13'),
(144, 21, 9, 12, 0.00, 'active', '2025-07-11 03:01:13', '2025-07-11 03:01:13'),
(145, 21, 10, 13, 0.00, 'active', '2025-07-11 03:01:13', '2025-07-11 03:01:13'),
(146, 21, 11, 6, 0.00, 'active', '2025-07-11 03:01:13', '2025-07-11 03:01:13'),
(147, 21, 12, 14, 0.00, 'active', '2025-07-11 03:01:13', '2025-07-11 03:01:13'),
(148, 21, 13, 11, 0.00, 'active', '2025-07-11 03:01:13', '2025-07-11 03:01:13'),
(149, 21, 14, 16, 0.00, 'active', '2025-07-11 03:01:13', '2025-07-11 03:01:13'),
(150, 22, 8, 11, 0.00, 'active', '2025-07-11 03:08:20', '2025-07-11 03:08:20'),
(151, 22, 9, 12, 0.00, 'active', '2025-07-11 03:08:20', '2025-07-11 03:08:20'),
(152, 22, 10, 13, 0.00, 'active', '2025-07-11 03:08:20', '2025-07-11 03:08:20'),
(153, 22, 11, 14, 0.00, 'active', '2025-07-11 03:08:20', '2025-07-11 03:08:20'),
(154, 22, 12, 10, 0.00, 'active', '2025-07-11 03:08:20', '2025-07-11 03:08:20'),
(155, 22, 13, 11, 0.00, 'active', '2025-07-11 03:08:20', '2025-07-11 03:08:20'),
(156, 23, 8, 11, 0.00, 'active', '2025-07-11 03:24:47', '2025-07-11 03:24:47'),
(157, 23, 9, 11, 0.00, 'active', '2025-07-11 03:24:47', '2025-07-11 03:24:47'),
(158, 23, 10, 10, 0.00, 'active', '2025-07-11 03:24:47', '2025-07-11 03:24:47'),
(159, 23, 11, 10, 0.00, 'active', '2025-07-11 03:24:47', '2025-07-11 03:24:47'),
(160, 23, 12, 10, 0.00, 'active', '2025-07-11 03:24:47', '2025-07-11 03:24:47'),
(161, 23, 13, 10, 0.00, 'active', '2025-07-11 03:24:47', '2025-07-11 03:24:47'),
(162, 23, 14, 10, 0.00, 'active', '2025-07-11 03:24:47', '2025-07-11 03:24:47'),
(163, 24, 8, 11, 0.00, 'active', '2025-07-11 03:34:18', '2025-07-11 03:34:18'),
(164, 24, 10, 13, 0.00, 'active', '2025-07-11 03:34:18', '2025-07-11 03:34:18'),
(165, 24, 12, 14, 0.00, 'active', '2025-07-11 03:34:18', '2025-07-11 03:34:18'),
(166, 24, 13, 11, 0.00, 'active', '2025-07-11 03:34:18', '2025-07-11 03:34:18'),
(167, 24, 14, 15, 0.00, 'active', '2025-07-11 03:34:18', '2025-07-11 03:34:18'),
(168, 25, 2, 10, 0.00, 'active', '2025-07-11 04:09:07', '2025-07-11 04:09:07'),
(169, 25, 3, 10, 0.00, 'active', '2025-07-11 04:09:07', '2025-07-11 04:09:07'),
(170, 25, 4, 10, 0.00, 'active', '2025-07-11 04:09:07', '2025-07-11 04:09:07'),
(171, 25, 5, 5, 0.00, 'active', '2025-07-11 04:09:07', '2025-07-11 04:09:07'),
(172, 26, 2, 10, 0.00, 'active', '2025-07-11 04:18:58', '2025-07-11 04:18:58'),
(173, 26, 3, 10, 0.00, 'active', '2025-07-11 04:18:58', '2025-07-11 04:18:58'),
(174, 26, 4, 12, 0.00, 'active', '2025-07-11 04:18:58', '2025-07-11 04:18:58'),
(175, 26, 5, 10, 0.00, 'active', '2025-07-11 04:18:58', '2025-07-11 04:18:58'),
(176, 27, 2, 10, 0.00, 'active', '2025-07-11 06:34:55', '2025-07-11 06:34:55'),
(177, 27, 3, 10, 0.00, 'active', '2025-07-11 06:34:55', '2025-07-11 06:34:55'),
(178, 27, 4, 15, 0.00, 'active', '2025-07-11 06:34:55', '2025-07-11 06:34:55'),
(179, 27, 5, 1, 0.00, 'active', '2025-07-11 06:34:55', '2025-07-11 06:34:55'),
(180, 28, 2, 10, 0.00, 'active', '2025-07-11 07:08:09', '2025-07-11 07:08:09'),
(181, 28, 3, 10, 0.00, 'active', '2025-07-11 07:08:09', '2025-07-11 07:08:09'),
(182, 28, 4, 10, 0.00, 'active', '2025-07-11 07:08:09', '2025-07-11 07:08:09'),
(183, 28, 5, 10, 0.00, 'active', '2025-07-11 07:08:09', '2025-07-11 07:08:09'),
(184, 29, 2, 11, 0.00, 'active', '2025-07-11 07:34:08', '2025-07-11 07:34:08'),
(185, 29, 3, 12, 0.00, 'active', '2025-07-11 07:34:08', '2025-07-11 07:34:08'),
(186, 29, 4, 12, 0.00, 'active', '2025-07-11 07:34:08', '2025-07-11 07:34:08'),
(187, 29, 5, 11, 0.00, 'active', '2025-07-11 07:34:08', '2025-07-11 07:34:08'),
(188, 30, 2, 10, 0.00, 'active', '2025-07-11 07:43:35', '2025-07-11 07:43:35'),
(189, 30, 3, 10, 0.00, 'active', '2025-07-11 07:43:35', '2025-07-11 07:43:35'),
(190, 30, 4, 10, 0.00, 'active', '2025-07-11 07:43:35', '2025-07-11 07:43:35'),
(191, 30, 5, 10, 0.00, 'active', '2025-07-11 07:43:35', '2025-07-11 07:43:35'),
(192, 31, 2, 10, 0.00, 'active', '2025-07-11 08:28:47', '2025-07-11 08:28:47'),
(193, 31, 3, 10, 0.00, 'active', '2025-07-11 08:28:47', '2025-07-11 08:28:47'),
(194, 31, 4, 12, 0.00, 'active', '2025-07-11 08:28:47', '2025-07-14 14:32:20'),
(195, 31, 5, 15, 0.00, 'active', '2025-07-11 08:28:47', '2025-07-11 08:28:47'),
(196, 32, 2, 11, 0.00, 'active', '2025-07-11 08:40:51', '2025-07-11 08:40:51'),
(197, 32, 4, 19, 0.00, 'active', '2025-07-11 08:40:51', '2025-07-11 08:40:51'),
(198, 32, 5, 11, 0.00, 'active', '2025-07-11 08:40:51', '2025-07-11 08:40:51'),
(199, 33, 17, 10, 0.00, 'active', '2025-07-11 08:58:02', '2025-07-11 08:58:02'),
(200, 33, 18, 11, 0.00, 'active', '2025-07-11 08:58:02', '2025-07-11 08:58:02'),
(201, 33, 19, 13, 0.00, 'active', '2025-07-11 08:58:02', '2025-07-14 19:19:12'),
(202, 33, 20, 14, 0.00, 'active', '2025-07-11 08:58:02', '2025-07-11 08:58:02'),
(203, 33, 21, 9, 0.00, 'active', '2025-07-11 08:58:02', '2025-07-14 18:23:17'),
(204, 33, 22, 10, 0.00, 'active', '2025-07-11 08:58:02', '2025-07-11 08:58:02'),
(205, 34, 17, 11, 0.00, 'active', '2025-07-11 09:14:43', '2025-07-11 09:14:43'),
(206, 34, 18, 12, 0.00, 'active', '2025-07-11 09:14:43', '2025-07-11 09:14:43'),
(207, 34, 19, 13, 0.00, 'active', '2025-07-11 09:14:43', '2025-07-11 09:14:43'),
(208, 34, 20, 14, 0.00, 'active', '2025-07-11 09:14:43', '2025-07-11 09:14:43'),
(209, 34, 21, 15, 0.00, 'active', '2025-07-11 09:14:43', '2025-07-11 09:14:43'),
(210, 34, 22, 16, 0.00, 'active', '2025-07-11 09:14:43', '2025-07-11 09:14:43'),
(211, 35, 17, 10, 0.00, 'active', '2025-07-11 09:24:37', '2025-07-11 09:24:37'),
(212, 35, 18, 10, 0.00, 'active', '2025-07-11 09:24:37', '2025-07-11 09:24:37'),
(213, 35, 19, 10, 0.00, 'active', '2025-07-11 09:24:37', '2025-07-11 09:24:37'),
(214, 35, 20, 10, 0.00, 'active', '2025-07-11 09:24:37', '2025-07-11 09:24:37'),
(215, 35, 21, 11, 0.00, 'active', '2025-07-11 09:24:37', '2025-07-11 09:24:37');

--
-- Triggers `product_sizes`
--
DELIMITER $$
CREATE TRIGGER `update_product_stock_after_size_change` AFTER UPDATE ON `product_sizes` FOR EACH ROW BEGIN
    UPDATE `products` 
    SET `stock_quantity` = (
        SELECT COALESCE(SUM(stock_quantity), 0) 
        FROM `product_sizes` 
        WHERE `product_id` = NEW.product_id AND `ps_status` = 'active'
    )
    WHERE `product_id` = NEW.product_id AND `has_variants` = 0;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `product_variants`
--

CREATE TABLE `product_variants` (
  `variant_id` int(10) NOT NULL,
  `product_id` int(10) NOT NULL,
  `color_name` varchar(100) NOT NULL,
  `color_code` varchar(7) NOT NULL,
  `variant_image` text DEFAULT NULL,
  `variant_images` text DEFAULT NULL,
  `stock_quantity` int(10) DEFAULT 0,
  `price_adjustment` decimal(10,2) DEFAULT 0.00,
  `variant_status` enum('active','inactive') DEFAULT 'active',
  `created_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_date` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `product_variants`
--

INSERT INTO `product_variants` (`variant_id`, `product_id`, `color_name`, `color_code`, `variant_image`, `variant_images`, `stock_quantity`, `price_adjustment`, `variant_status`, `created_date`, `updated_date`) VALUES
(23, 6, 'Red', '#FF0000', 'variant_Red_1752134485_0.webp', '[\"variant_Red_1752134485_0.webp\",\"variant_Red_1752134485_1.webp\"]', 41, 0.00, 'active', '2025-07-10 08:01:25', '2025-07-13 07:26:49'),
(24, 6, 'Green', '#008000', 'variant_Green_1752134485_0.webp', '[\"variant_Green_1752134485_0.webp\",\"variant_Green_1752134485_1.webp\"]', 52, 0.00, 'active', '2025-07-10 08:01:25', '2025-07-13 07:27:01'),
(25, 7, 'Pink', '#FFC0CB', 'variant_Pink_1752140775_0.webp', '[\"variant_Pink_1752140775_0.webp\",\"variant_Pink_1752140775_1.webp\"]', 52, 0.00, 'active', '2025-07-10 09:46:15', '2025-07-13 07:27:14'),
(26, 7, 'Blue', '#0000FF', 'variant_Blue_1752140775_0.webp', '[\"variant_Blue_1752140775_0.webp\",\"variant_Blue_1752140775_1.webp\"]', 49, 0.00, 'active', '2025-07-10 09:46:15', '2025-07-10 09:46:15'),
(27, 7, 'Khaki', '#000000', 'variant_Khaki_1752140775_0.webp', '[\"variant_Khaki_1752140775_0.webp\",\"variant_Khaki_1752140775_1.webp\"]', 100, 0.00, 'active', '2025-07-10 09:46:15', '2025-07-13 07:27:26'),
(28, 8, 'Khaki', '#000000', 'variant_Khaki_1752147161_0.webp', '[\"variant_Khaki_1752147161_0.webp\",\"variant_Khaki_1752147161_1.webp\"]', 50, 0.00, 'active', '2025-07-10 11:32:41', '2025-07-10 11:32:41'),
(29, 8, 'Green', '#008000', 'variant_Green_1752147161_0.webp', '[\"variant_Green_1752147161_0.webp\",\"variant_Green_1752147161_1.webp\"]', 40, 0.00, 'active', '2025-07-10 11:32:41', '2025-07-10 11:32:41'),
(30, 8, 'Black', '#000000', 'variant_Black_1752147161_0.webp', '[\"variant_Black_1752147161_0.webp\",\"variant_Black_1752147161_1.webp\"]', 60, 0.00, 'active', '2025-07-10 11:32:41', '2025-07-10 11:32:41'),
(31, 9, 'Blue', '#0000FF', 'variant_Blue_1752150079_0.webp', '[\"variant_Blue_1752150079_0.webp\",\"variant_Blue_1752150079_1.webp\"]', 40, 0.00, 'active', '2025-07-10 12:21:19', '2025-07-10 12:21:19'),
(32, 9, 'Navy Blue', '#000000', 'variant_Navy Blue_1752150079_0.webp', '[\"variant_Navy Blue_1752150079_0.webp\",\"variant_Navy Blue_1752150079_1.webp\"]', 49, 0.00, 'active', '2025-07-10 12:21:19', '2025-07-10 12:21:19'),
(33, 9, 'Sky Blue', '#000000', 'variant_Sky Blue_1752150079_0.webp', '[\"variant_Sky Blue_1752150079_0.webp\",\"variant_Sky Blue_1752150079_1.webp\"]', 51, 0.00, 'active', '2025-07-10 12:21:19', '2025-07-10 12:21:19'),
(34, 10, 'Blue', '#0000FF', 'variant_Blue_1752150696_0.webp', '[\"variant_Blue_1752150696_0.webp\",\"variant_Blue_1752150696_1.webp\"]', 40, 0.00, 'active', '2025-07-10 12:31:36', '2025-07-10 12:31:36'),
(35, 10, 'Black', '#000000', 'variant_Black_1752150696_0.webp', '[\"variant_Black_1752150696_0.webp\",\"variant_Black_1752150696_1.webp\"]', 54, 0.00, 'active', '2025-07-10 12:31:36', '2025-07-10 12:31:36'),
(36, 10, 'Green', '#008000', 'variant_Green_1752150696_0.webp', '[\"variant_Green_1752150696_0.webp\",\"variant_Green_1752150696_1.webp\"]', 39, 0.00, 'active', '2025-07-10 12:31:36', '2025-07-10 12:31:36'),
(37, 11, 'Pink', '#FFC0CB', 'variant_Pink_1752151191_0.webp', '[\"variant_Pink_1752151191_0.webp\",\"variant_Pink_1752151191_1.webp\"]', 47, 0.00, 'active', '2025-07-10 12:39:51', '2025-07-10 12:39:51'),
(38, 11, 'Dark Pink', '#000000', 'variant_Dark Pink_1752151191_0.webp', '[\"variant_Dark Pink_1752151191_0.webp\",\"variant_Dark Pink_1752151191_1.webp\"]', 50, 0.00, 'active', '2025-07-10 12:39:51', '2025-07-10 12:39:51'),
(39, 12, 'Multicolor 1', '#000000', 'variant_Multicolor 1_1752151774_0.webp', '[\"variant_Multicolor 1_1752151774_0.webp\",\"variant_Multicolor 1_1752151774_1.webp\"]', 50, 0.00, 'active', '2025-07-10 12:49:34', '2025-07-10 12:49:34'),
(40, 12, 'Multicolor 2', '#000000', 'variant_Multicolor 2_1752151774_0.webp', '[\"variant_Multicolor 2_1752151774_0.webp\",\"variant_Multicolor 2_1752151774_1.webp\"]', 49, 0.00, 'active', '2025-07-10 12:49:34', '2025-07-10 12:49:34'),
(41, 13, 'Black', '#000000', 'variant_Black_1752161885_0.webp', '[\"variant_Black_1752161885_0.webp\",\"variant_Black_1752161885_1.webp\"]', 40, 0.00, 'active', '2025-07-10 15:38:05', '2025-07-10 15:38:05'),
(42, 13, 'Blue', '#0000FF', 'variant_Blue_1752161885_0.webp', '[\"variant_Blue_1752161885_0.webp\",\"variant_Blue_1752161885_1.webp\"]', 50, 0.00, 'active', '2025-07-10 15:38:05', '2025-07-10 15:38:05'),
(43, 14, 'Grey', '#000000', 'variant_Grey_1752162311_0.webp', '[\"variant_Grey_1752162311_0.webp\",\"variant_Grey_1752162311_1.webp\"]', 50, 0.00, 'active', '2025-07-10 15:45:11', '2025-07-10 15:45:11'),
(44, 14, 'Blue', '#0000FF', 'variant_Blue_1752162311_0.webp', '[\"variant_Blue_1752162311_0.webp\",\"variant_Blue_1752162311_1.webp\"]', 50, 0.00, 'active', '2025-07-10 15:45:11', '2025-07-10 15:45:11'),
(45, 15, 'Coffee', '#000000', 'variant_Coffee_1752184589_0.webp', '[\"variant_Coffee_1752184589_0.webp\",\"variant_Coffee_1752184589_1.webp\"]', 40, 0.00, 'active', '2025-07-10 21:56:29', '2025-07-10 21:56:29'),
(46, 15, 'Green', '#008000', 'variant_Green_1752184589_0.jpg', '[\"variant_Green_1752184589_0.jpg\",\"variant_Green_1752184589_1.jpg\"]', 50, 0.00, 'active', '2025-07-10 21:56:29', '2025-07-10 21:56:29'),
(47, 15, 'Khaki', '#000000', 'variant_Khaki_1752184589_0.jpg', '[\"variant_Khaki_1752184589_0.jpg\",\"variant_Khaki_1752184589_1.jpg\"]', 46, 0.00, 'active', '2025-07-10 21:56:29', '2025-07-10 21:56:29'),
(48, 15, 'Black', '#000000', 'variant_Black_1752184589_0.jpg', '[\"variant_Black_1752184589_0.jpg\",\"variant_Black_1752184589_1.jpg\"]', 49, 0.00, 'active', '2025-07-10 21:56:29', '2025-07-10 21:56:29'),
(49, 16, 'Black', '#000000', 'variant_Black_1752185113_0.jpg', '[\"variant_Black_1752185113_0.jpg\",\"variant_Black_1752185113_1.jpg\"]', 43, 0.00, 'active', '2025-07-10 22:05:13', '2025-07-10 22:05:13'),
(50, 16, 'Grey', '#000000', 'variant_Grey_1752185113_0.jpg', '[\"variant_Grey_1752185113_0.jpg\",\"variant_Grey_1752185113_1.jpg\"]', 40, 0.00, 'active', '2025-07-10 22:05:13', '2025-07-10 22:05:13'),
(51, 16, 'Blue', '#0000FF', 'variant_Blue_1752185113_0.jpg', '[\"variant_Blue_1752185113_0.jpg\",\"variant_Blue_1752185113_1.jpg\"]', 50, 0.00, 'active', '2025-07-10 22:05:13', '2025-07-10 22:05:13'),
(52, 17, 'Blue', '#0000FF', 'variant_Blue_1752186548_0.webp', '[\"variant_Blue_1752186548_0.webp\",\"variant_Blue_1752186548_1.webp\"]', 50, 0.00, 'active', '2025-07-10 22:29:08', '2025-07-10 22:29:08'),
(53, 17, 'Black', '#000000', 'variant_Black_1752186548_0.webp', '[\"variant_Black_1752186548_0.webp\",\"variant_Black_1752186548_1.webp\",\"variant_Black_1752186548_2.webp\"]', 30, 0.00, 'active', '2025-07-10 22:29:08', '2025-07-10 22:29:08'),
(54, 18, 'Black', '#000000', 'variant_Black_1752199295_0.webp', '[\"variant_Black_1752199295_0.webp\",\"variant_Black_1752199295_1.webp\"]', 3, 0.00, 'active', '2025-07-11 02:01:35', '2025-07-14 18:30:18'),
(55, 18, 'Blue', '#0000FF', 'variant_Blue_1752199295_0.webp', '[\"variant_Blue_1752199295_0.webp\",\"variant_Blue_1752199295_1.webp\",\"variant_Blue_1752199295_2.webp\"]', 20, 0.00, 'active', '2025-07-11 02:01:35', '2025-07-14 08:27:36'),
(56, 19, 'Grey', '#000000', 'variant_Grey_1752201572_0.webp', '[\"variant_Grey_1752201572_0.webp\",\"variant_Grey_1752201572_1.webp\"]', 35, 0.00, 'active', '2025-07-11 02:39:32', '2025-07-11 02:39:32'),
(57, 19, 'Olive Green', '#000000', 'variant_Olive Green_1752201572_0.webp', '[\"variant_Olive Green_1752201572_0.webp\",\"variant_Olive Green_1752201572_1.webp\",\"variant_Olive Green_1752201572_2.webp\"]', 41, 0.00, 'active', '2025-07-11 02:39:32', '2025-07-11 02:39:32'),
(58, 19, 'Black', '#000000', 'variant_Black_1752201572_0.webp', '[\"variant_Black_1752201572_0.webp\",\"variant_Black_1752201572_1.webp\"]', 30, 0.00, 'active', '2025-07-11 02:39:32', '2025-07-11 02:39:32'),
(59, 20, 'Black', '#000000', 'variant_Black_1752202253_0.webp', '[\"variant_Black_1752202253_0.webp\",\"variant_Black_1752202253_1.webp\"]', 50, 0.00, 'active', '2025-07-11 02:50:53', '2025-07-11 02:50:53'),
(60, 20, 'White', '#FFFFFF', 'variant_White_1752202253_0.webp', '[\"variant_White_1752202253_0.webp\",\"variant_White_1752202253_1.webp\",\"variant_White_1752202253_2.webp\"]', 50, 0.00, 'active', '2025-07-11 02:50:53', '2025-07-11 02:50:53'),
(61, 20, 'Red', '#FF0000', 'variant_Red_1752202253_0.webp', '[\"variant_Red_1752202253_0.webp\",\"variant_Red_1752202253_1.webp\"]', 49, 0.00, 'active', '2025-07-11 02:50:53', '2025-07-11 02:50:53'),
(62, 20, 'Grey', '#000000', 'variant_Grey_1752202253_0.webp', '[\"variant_Grey_1752202253_0.webp\",\"variant_Grey_1752202253_1.webp\"]', 51, 0.00, 'active', '2025-07-11 02:50:53', '2025-07-11 02:50:53'),
(63, 20, 'Purple', '#800080', 'variant_Purple_1752202253_0.webp', '[\"variant_Purple_1752202253_0.webp\",\"variant_Purple_1752202253_1.webp\"]', 39, 0.00, 'active', '2025-07-11 02:50:53', '2025-07-11 02:50:53'),
(64, 21, 'Brown', '#A52A2A', 'variant_Brown_1752202873_0.webp', '[\"variant_Brown_1752202873_0.webp\",\"variant_Brown_1752202873_1.webp\"]', 58, 0.00, 'active', '2025-07-11 03:01:13', '2025-07-11 03:01:13'),
(65, 21, 'Black', '#000000', 'variant_Black_1752202873_0.webp', '[\"variant_Black_1752202873_0.webp\",\"variant_Black_1752202873_1.webp\"]', 52, 0.00, 'active', '2025-07-11 03:01:13', '2025-07-11 03:01:13'),
(66, 22, 'Black', '#000000', 'variant_Black_1752203300_0.webp', '[\"variant_Black_1752203300_0.webp\",\"variant_Black_1752203300_1.webp\"]', 42, 0.00, 'active', '2025-07-11 03:08:20', '2025-07-11 03:08:20'),
(67, 22, 'Brown', '#A52A2A', 'variant_Brown_1752203300_0.webp', '[\"variant_Brown_1752203300_0.webp\",\"variant_Brown_1752203300_1.webp\"]', 38, 0.00, 'active', '2025-07-11 03:08:20', '2025-07-11 03:08:20'),
(68, 23, 'Black', '#000000', 'variant_Black_1752204287_0.webp', '[\"variant_Black_1752204287_0.webp\"]', 71, 0.00, 'active', '2025-07-11 03:24:47', '2025-07-11 03:24:47'),
(69, 23, 'Blue', '#0000FF', 'variant_Blue_1752204287_0.webp', '[\"variant_Blue_1752204287_0.webp\",\"variant_Blue_1752204287_1.webp\"]', 68, 0.00, 'active', '2025-07-11 03:24:47', '2025-07-11 03:24:47'),
(70, 23, 'Green', '#008000', 'variant_Green_1752204287_0.webp', '[\"variant_Green_1752204287_0.webp\",\"variant_Green_1752204287_1.webp\"]', 81, 0.00, 'active', '2025-07-11 03:24:47', '2025-07-11 03:24:47'),
(71, 24, 'Grey and Black', '#000000', 'variant_Grey and Black_1752204858_0.webp', '[\"variant_Grey and Black_1752204858_0.webp\",\"variant_Grey and Black_1752204858_1.webp\"]', 50, 0.00, 'active', '2025-07-11 03:34:18', '2025-07-11 03:34:18'),
(72, 24, 'White and Grey', '#000000', 'variant_White and Grey_1752204858_0.webp', '[\"variant_White and Grey_1752204858_0.webp\",\"variant_White and Grey_1752204858_1.webp\"]', 49, 0.00, 'active', '2025-07-11 03:34:18', '2025-07-11 03:34:18'),
(73, 25, 'Grey', '#000000', 'variant_Grey_1752206947_0.webp', '[\"variant_Grey_1752206947_0.webp\",\"variant_Grey_1752206947_1.webp\"]', 50, 0.00, 'active', '2025-07-11 04:09:07', '2025-07-11 04:09:07'),
(74, 25, 'Khaki', '#000000', 'variant_Khaki_1752206947_0.webp', '[\"variant_Khaki_1752206947_0.webp\",\"variant_Khaki_1752206947_1.webp\"]', 40, 0.00, 'active', '2025-07-11 04:09:07', '2025-07-11 04:09:07'),
(75, 26, 'Red', '#FF0000', 'variant_Red_1752207538_0.webp', '[\"variant_Red_1752207538_0.webp\",\"variant_Red_1752207538_1.webp\"]', 41, 0.00, 'active', '2025-07-11 04:18:58', '2025-07-11 04:18:58'),
(76, 26, 'Black', '#000000', 'variant_Black_1752207538_0.webp', '[\"variant_Black_1752207538_0.webp\",\"variant_Black_1752207538_1.webp\"]', 45, 0.00, 'active', '2025-07-11 04:18:58', '2025-07-11 04:18:58'),
(77, 26, 'Khaki', '#000000', 'variant_Khaki_1752207538_0.webp', '[\"variant_Khaki_1752207538_0.webp\",\"variant_Khaki_1752207538_1.webp\"]', 39, 0.00, 'active', '2025-07-11 04:18:58', '2025-07-11 04:18:58'),
(78, 27, 'Khaki', '#000000', 'variant_Khaki_1752215695_0.webp', '[\"variant_Khaki_1752215695_0.webp\",\"variant_Khaki_1752215695_1.webp\"]', 50, 0.00, 'active', '2025-07-11 06:34:55', '2025-07-11 06:34:55'),
(79, 27, 'White', '#FFFFFF', 'variant_White_1752215695_0.webp', '[\"variant_White_1752215695_0.webp\",\"variant_White_1752215695_1.webp\"]', 42, 0.00, 'active', '2025-07-11 06:34:55', '2025-07-11 06:34:55'),
(80, 28, 'Black', '#000000', 'variant_Black_1752217689_0.webp', '[\"variant_Black_1752217689_0.webp\",\"variant_Black_1752217689_1.webp\"]', 40, 0.00, 'active', '2025-07-11 07:08:09', '2025-07-11 07:08:09'),
(81, 28, 'Red', '#FF0000', 'variant_Red_1752217689_0.webp', '[\"variant_Red_1752217689_0.webp\",\"variant_Red_1752217689_1.webp\"]', 45, 0.00, 'active', '2025-07-11 07:08:09', '2025-07-11 07:08:09'),
(82, 28, 'Coffee', '#000000', 'variant_Coffee_1752217689_0.webp', '[\"variant_Coffee_1752217689_0.webp\",\"variant_Coffee_1752217689_1.webp\"]', 48, 0.00, 'active', '2025-07-11 07:08:09', '2025-07-11 07:08:09'),
(83, 29, 'Multicolor 1', '#000000', 'variant_Multicolor 1_1752219248_0.webp', '[\"variant_Multicolor 1_1752219248_0.webp\",\"variant_Multicolor 1_1752219248_1.webp\"]', 50, 0.00, 'active', '2025-07-11 07:34:08', '2025-07-11 07:34:08'),
(84, 29, 'Multicolor 2', '#000000', 'variant_Multicolor 2_1752219248_0.webp', '[\"variant_Multicolor 2_1752219248_0.webp\",\"variant_Multicolor 2_1752219248_1.webp\"]', 43, 0.00, 'active', '2025-07-11 07:34:08', '2025-07-11 07:34:08'),
(85, 29, 'Multicolor 3', '#000000', 'variant_Multicolor 3_1752219248_0.webp', '[\"variant_Multicolor 3_1752219248_0.webp\",\"variant_Multicolor 3_1752219248_1.webp\"]', 41, 0.00, 'active', '2025-07-11 07:34:08', '2025-07-11 07:34:08'),
(86, 29, 'Multicolor 4', '#000000', 'variant_Multicolor 4_1752219248_0.webp', '[\"variant_Multicolor 4_1752219248_0.webp\",\"variant_Multicolor 4_1752219248_1.webp\"]', 50, 0.00, 'active', '2025-07-11 07:34:08', '2025-07-11 07:34:08'),
(87, 30, 'Black', '#000000', 'variant_Black_1752219815_0.webp', '[\"variant_Black_1752219815_0.webp\",\"variant_Black_1752219815_1.webp\"]', 44, 0.00, 'active', '2025-07-11 07:43:35', '2025-07-14 13:05:27'),
(88, 30, 'Grey', '#000000', 'variant_Grey_1752219815_0.webp', '[\"variant_Grey_1752219815_0.webp\",\"variant_Grey_1752219815_1.webp\"]', 40, 0.00, 'active', '2025-07-11 07:43:35', '2025-07-11 07:43:35'),
(89, 30, 'Cream', '#000000', 'variant_Cream_1752219815_0.webp', '[\"variant_Cream_1752219815_0.webp\",\"variant_Cream_1752219815_1.webp\"]', 50, 0.00, 'active', '2025-07-11 07:43:35', '2025-07-11 07:43:35'),
(90, 31, 'Blue', '#0000FF', 'variant_Blue_1752222527_0.webp', '[\"variant_Blue_1752222527_0.webp\",\"variant_Blue_1752222527_1.webp\"]', 50, 0.00, 'active', '2025-07-11 08:28:47', '2025-07-11 08:28:47'),
(91, 31, 'Black', '#000000', 'variant_Black_1752222527_0.webp', '[\"variant_Black_1752222527_0.webp\",\"variant_Black_1752222527_1.webp\"]', 40, 0.00, 'active', '2025-07-11 08:28:47', '2025-07-11 08:28:47'),
(92, 31, 'White', '#FFFFFF', 'variant_White_1752222527_0.webp', '[\"variant_White_1752222527_0.webp\",\"variant_White_1752222527_1.webp\"]', 50, 0.00, 'active', '2025-07-11 08:28:47', '2025-07-11 08:28:47'),
(93, 32, 'Blue', '#0000FF', 'variant_Blue_1752223251_0.webp', '[\"variant_Blue_1752223251_0.webp\",\"variant_Blue_1752223251_1.webp\"]', 35, 0.00, 'active', '2025-07-11 08:40:51', '2025-07-11 08:40:51'),
(94, 32, 'Coffee', '#000000', 'variant_Coffee_1752223251_0.webp', '[\"variant_Coffee_1752223251_0.webp\",\"variant_Coffee_1752223251_1.webp\"]', 39, 0.00, 'active', '2025-07-11 08:40:51', '2025-07-11 08:40:51'),
(95, 32, 'Red', '#FF0000', 'variant_Red_1752223251_0.webp', '[\"variant_Red_1752223251_0.webp\",\"variant_Red_1752223251_1.webp\"]', 41, 0.00, 'active', '2025-07-11 08:40:51', '2025-07-11 08:40:51'),
(96, 33, 'White', '#FFFFFF', 'variant_White_1752224282_0.webp', '[\"variant_White_1752224282_0.webp\",\"variant_White_1752224282_1.webp\"]', 71, 0.00, 'active', '2025-07-11 08:58:02', '2025-07-11 08:58:02'),
(97, 33, 'Brown', '#A52A2A', 'variant_Brown_1752224282_0.webp', '[\"variant_Brown_1752224282_0.webp\",\"variant_Brown_1752224282_1.webp\"]', 69, 0.00, 'active', '2025-07-11 08:58:02', '2025-07-11 08:58:02'),
(98, 33, 'Black', '#000000', 'variant_Black_1752224282_0.webp', '[\"variant_Black_1752224282_0.webp\",\"variant_Black_1752224282_1.webp\"]', 80, 0.00, 'active', '2025-07-11 08:58:02', '2025-07-11 08:58:02'),
(99, 34, 'Khaki', '#000000', 'variant_Khaki_1752225283_0.webp', '[\"variant_Khaki_1752225283_0.webp\",\"variant_Khaki_1752225283_1.webp\"]', 61, 0.00, 'active', '2025-07-11 09:14:43', '2025-07-11 09:14:43'),
(100, 34, 'Black', '#000000', 'variant_Black_1752225283_0.webp', '[\"variant_Black_1752225283_0.webp\",\"variant_Black_1752225283_1.webp\"]', 70, 0.00, 'active', '2025-07-11 09:14:43', '2025-07-11 09:14:43'),
(101, 34, 'White', '#FFFFFF', 'variant_White_1752225283_0.webp', '[\"variant_White_1752225283_0.webp\",\"variant_White_1752225283_1.webp\"]', 69, 0.00, 'active', '2025-07-11 09:14:43', '2025-07-11 09:14:43'),
(102, 35, 'White and Khaki', '#000000', 'variant_White and Khaki_1752225877_0.webp', '[\"variant_White and Khaki_1752225877_0.webp\",\"variant_White and Khaki_1752225877_1.webp\"]', 50, 0.00, 'active', '2025-07-11 09:24:37', '2025-07-11 09:24:37'),
(103, 35, 'Red And White', '#000000', 'variant_Red And White_1752225877_0.webp', '[\"variant_Red And White_1752225877_0.webp\",\"variant_Red And White_1752225877_1.webp\"]', 70, 0.00, 'active', '2025-07-11 09:24:37', '2025-07-11 09:24:37'),
(104, 35, 'White', '#FFFFFF', 'variant_White_1752225877_0.webp', '[\"variant_White_1752225877_0.webp\",\"variant_White_1752225877_1.webp\"]', 60, 0.00, 'active', '2025-07-11 09:24:37', '2025-07-11 09:24:37'),
(105, 35, 'Black', '#000000', 'variant_Black_1752225877_0.webp', '[\"variant_Black_1752225877_0.webp\",\"variant_Black_1752225877_1.webp\"]', 69, 0.00, 'active', '2025-07-11 09:24:37', '2025-07-11 09:24:37');

-- --------------------------------------------------------

--
-- Table structure for table `recently_viewed`
--

CREATE TABLE `recently_viewed` (
  `view_id` int(10) NOT NULL,
  `customer_email` varchar(100) DEFAULT NULL,
  `session_id` varchar(100) DEFAULT NULL,
  `product_id` int(10) NOT NULL,
  `view_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sizes`
--

CREATE TABLE `sizes` (
  `size_id` int(10) NOT NULL,
  `size_name` varchar(50) NOT NULL,
  `size_type` enum('clothing','shoes_men','shoes_women','shoes_kids','custom') DEFAULT 'clothing',
  `size_order` int(3) DEFAULT 1,
  `size_desc` text DEFAULT NULL,
  `size_status` enum('active','inactive') DEFAULT 'active',
  `created_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_date` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sizes`
--

INSERT INTO `sizes` (`size_id`, `size_name`, `size_type`, `size_order`, `size_desc`, `size_status`, `created_date`, `updated_date`) VALUES
(1, 'XS', 'clothing', 1, 'Extra Small', 'active', '2025-06-19 20:00:10', '2025-06-19 20:00:10'),
(2, 'S', 'clothing', 2, 'Small', 'active', '2025-06-19 20:00:10', '2025-06-19 20:00:10'),
(3, 'M', 'clothing', 3, 'Medium', 'active', '2025-06-19 20:00:10', '2025-06-19 20:00:10'),
(4, 'L', 'clothing', 4, 'Large', 'active', '2025-06-19 20:00:10', '2025-06-19 20:00:10'),
(5, 'XL', 'clothing', 5, 'Extra Large', 'active', '2025-06-19 20:00:10', '2025-06-19 20:00:10'),
(6, 'XXL', 'clothing', 6, 'Double Extra Large', 'active', '2025-06-19 20:00:10', '2025-06-19 20:00:10'),
(8, '6', 'shoes_men', 1, 'Men Size 6', 'active', '2025-06-19 20:00:10', '2025-06-19 20:00:10'),
(9, '7', 'shoes_men', 2, 'Men Size 7', 'active', '2025-06-19 20:00:10', '2025-06-19 20:00:10'),
(10, '8', 'shoes_men', 3, 'Men Size 8', 'active', '2025-06-19 20:00:10', '2025-06-19 20:00:10'),
(11, '9', 'shoes_men', 4, 'Men Size 9', 'active', '2025-06-19 20:00:10', '2025-06-19 20:00:10'),
(12, '10', 'shoes_men', 5, 'Men Size 10', 'active', '2025-06-19 20:00:10', '2025-06-19 20:00:10'),
(13, '11', 'shoes_men', 6, 'Men Size 11', 'active', '2025-06-19 20:00:10', '2025-06-19 20:00:10'),
(14, '12', 'shoes_men', 7, 'Men Size 12', 'active', '2025-06-19 20:00:10', '2025-06-19 20:00:10'),
(15, '13', 'shoes_men', 8, 'Men Size 13', 'active', '2025-06-19 20:00:10', '2025-06-19 20:00:10'),
(16, '5', 'shoes_women', 1, 'Women Size 5', 'active', '2025-06-19 20:00:10', '2025-06-19 20:00:10'),
(17, '6', 'shoes_women', 2, 'Women Size 6', 'active', '2025-06-19 20:00:10', '2025-06-19 20:00:10'),
(18, '7', 'shoes_women', 3, 'Women Size 7', 'active', '2025-06-19 20:00:10', '2025-06-19 20:00:10'),
(19, '8', 'shoes_women', 4, 'Women Size 8', 'active', '2025-06-19 20:00:10', '2025-06-19 20:00:10'),
(20, '9', 'shoes_women', 5, 'Women Size 9', 'active', '2025-06-19 20:00:10', '2025-06-19 20:00:10'),
(21, '10', 'shoes_women', 6, 'Women Size 10', 'active', '2025-06-19 20:00:10', '2025-06-19 20:00:10'),
(22, '11', 'shoes_women', 7, 'Women Size 11', 'active', '2025-06-19 20:00:10', '2025-06-19 20:00:10'),
(23, '1', 'shoes_kids', 1, 'Kids Size 1', 'active', '2025-06-19 20:00:10', '2025-06-19 20:00:10'),
(24, '2', 'shoes_kids', 2, 'Kids Size 2', 'active', '2025-06-19 20:00:10', '2025-06-19 20:00:10'),
(25, '3', 'shoes_kids', 3, 'Kids Size 3', 'active', '2025-06-19 20:00:10', '2025-06-19 20:00:10'),
(26, '4', 'shoes_kids', 4, 'Kids Size 4', 'active', '2025-06-19 20:00:10', '2025-06-19 20:00:10'),
(27, '5', 'shoes_kids', 5, 'Kids Size 5', 'active', '2025-06-19 20:00:10', '2025-06-19 20:00:10'),
(28, '6', 'shoes_kids', 6, 'Kids Size 6', 'active', '2025-06-19 20:00:10', '2025-06-19 20:00:10'),
(44, '4Y-5Y', 'shoes_kids', 1, '', 'active', '2025-07-10 11:17:01', '2025-07-10 11:37:53'),
(45, '6Y-7Y', 'shoes_kids', 2, '', 'active', '2025-07-10 11:17:24', '2025-07-10 11:38:05'),
(46, '8Y-9Y', 'shoes_kids', 3, '', 'active', '2025-07-10 11:18:08', '2025-07-10 11:38:16'),
(47, '10Y-11Y', 'shoes_kids', 5, '', 'active', '2025-07-10 11:18:40', '2025-07-10 11:38:26'),
(48, '28', 'clothing', 1, '', 'active', '2025-07-10 11:19:43', '2025-07-10 11:19:43'),
(49, '30', 'clothing', 2, '', 'active', '2025-07-10 11:19:58', '2025-07-10 11:19:58'),
(50, '32', 'clothing', 3, '', 'active', '2025-07-10 11:20:10', '2025-07-10 11:20:10'),
(51, '34', 'clothing', 4, '', 'active', '2025-07-10 11:20:28', '2025-07-10 11:20:28'),
(52, '36', 'clothing', 5, '', 'active', '2025-07-10 11:20:47', '2025-07-10 11:20:47'),
(53, '38', 'clothing', 6, '', 'active', '2025-07-10 11:21:00', '2025-07-10 11:21:00'),
(54, '40', 'clothing', 7, '', 'active', '2025-07-10 11:21:13', '2025-07-10 11:21:13');

-- --------------------------------------------------------

--
-- Table structure for table `slider`
--

CREATE TABLE `slider` (
  `slide_id` int(10) NOT NULL,
  `slide_name` varchar(255) NOT NULL,
  `slide_image` text NOT NULL,
  `slide_url` varchar(500) DEFAULT NULL,
  `slide_text` text DEFAULT NULL,
  `slide_order` int(3) DEFAULT 1,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_date` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `stock_movements`
--

CREATE TABLE `stock_movements` (
  `movement_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `variant_id` int(11) DEFAULT NULL,
  `movement_type` enum('sale','restock','adjustment','return') NOT NULL,
  `quantity_change` int(11) NOT NULL,
  `previous_stock` int(11) NOT NULL,
  `new_stock` int(11) NOT NULL,
  `reference_id` varchar(50) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `created_by` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `terms`
--

CREATE TABLE `terms` (
  `term_id` int(10) NOT NULL,
  `term_title` varchar(255) NOT NULL,
  `term_link` varchar(255) NOT NULL,
  `term_desc` text DEFAULT NULL,
  `created_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_date` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `terms`
--

INSERT INTO `terms` (`term_id`, `term_title`, `term_link`, `term_desc`, `created_date`, `updated_date`) VALUES
(1, 'Privacy Policy', 'privacy-policy', '<h3>Privacy Policy</h3>\r\n        <p>At Avenue Fashion, we are committed to protecting your privacy and personal information. This privacy policy explains how we collect, use, and protect your data when you use our website and services.</p>\r\n        \r\n        <h4>Information We Collect</h4>\r\n        <ul>\r\n            <li><strong>Personal Information:</strong> Name, email address, phone number, shipping and billing addresses</li>\r\n            <li><strong>Payment Information:</strong> Credit card details, payment preferences (processed securely through Stripe)</li>\r\n            <li><strong>Shopping Data:</strong> Purchase history, cart contents, wishlist items</li>\r\n            <li><strong>Virtual Try-On Data:</strong> Photos uploaded for virtual fitting (stored securely and deleted after 30 days)</li>\r\n            <li><strong>Website Usage:</strong> Browsing patterns, IP address, device information, cookies</li>\r\n        </ul>\r\n        \r\n        <h4>How We Use Your Information</h4>\r\n        <ul>\r\n            <li>Process and fulfill your orders</li>\r\n            <li>Provide customer support and respond to inquiries</li>\r\n            <li>Improve our Virtual Try-On technology</li>\r\n            <li>Send order updates and promotional emails (with your consent)</li>\r\n            <li>Analyze website usage to improve our services</li>\r\n            <li>Prevent fraud and ensure security</li>\r\n        </ul>\r\n        \r\n        <h4>Data Security</h4>\r\n        <p>We implement industry-standard security measures including SSL encryption, secure payment processing through Stripe, and regular security audits. Your Virtual Try-On photos are encrypted and automatically deleted after 30 days.</p>\r\n        \r\n        <h4>Your Rights</h4>\r\n        <p>You have the right to access, update, or delete your personal information. Contact us at privacy@avenuefashion.com for any privacy-related requests.</p>', '2025-07-11 12:09:29', '2025-07-11 12:09:29'),
(2, 'Terms of Service', 'terms-of-service', '<h3>Terms of Service</h3>\r\n        <p>Welcome to Avenue Fashion. By using our website and services, you agree to comply with these terms and conditions.</p>\r\n        \r\n        <h4>Use of Our Website</h4>\r\n        <ul>\r\n            <li>You must be at least 18 years old to make purchases</li>\r\n            <li>Provide accurate and complete information during registration and checkout</li>\r\n            <li>Use the Virtual Try-On feature responsibly and only upload your own photos</li>\r\n            <li>Do not misuse our website or attempt to interfere with its functionality</li>\r\n        </ul>\r\n        \r\n        <h4>Product Information</h4>\r\n        <ul>\r\n            <li>We strive to display accurate product colors, but actual colors may vary due to monitor settings</li>\r\n            <li>Sizes are provided as guidelines; refer to our size chart for best fit</li>\r\n            <li>Virtual Try-On results are estimates and may not perfectly represent actual fit</li>\r\n            <li>Product availability is subject to change without notice</li>\r\n        </ul>\r\n        \r\n        <h4>Intellectual Property</h4>\r\n        <p>All content on our website, including images, text, logos, and Virtual Try-On technology, is protected by copyright and trademark laws. Unauthorized use is prohibited.</p>\r\n        \r\n        <h4>Limitation of Liability</h4>\r\n        <p>Avenue Fashion is not liable for any indirect, incidental, or consequential damages arising from your use of our website or products.</p>', '2025-07-11 12:09:29', '2025-07-11 12:09:29'),
(3, 'Shipping & Returns', 'shipping-returns', '<h3>Shipping Information</h3>\r\n        <p>We offer reliable shipping services across Pakistan with multiple delivery options to suit your needs.</p>\r\n        \r\n        <h4>Shipping Rates & Delivery Times</h4>\r\n        <ul>\r\n            <li><strong>Standard Delivery (3-5 business days):</strong> Rs 250 (Free for orders over Rs 50,000)</li>\r\n            <li><strong>Express Delivery (1-2 business days):</strong> Rs 500</li>\r\n            <li><strong>Same Day Delivery:</strong> Rs 800 (Available in major cities for orders placed before 12 PM)</li>\r\n        </ul>\r\n        \r\n        <h4>Shipping Locations</h4>\r\n        <p>We currently deliver to all major cities in Pakistan including Karachi, Lahore, Islamabad, Faisalabad, Multan, Peshawar, and Quetta. Remote areas may require additional 1-2 business days.</p>\r\n        \r\n        <h3>Return Policy</h3>\r\n        <p>We want you to be completely satisfied with your purchase. If you are not happy with your order, you can return it within 14 days of delivery.</p>\r\n        \r\n        <h4>Return Conditions</h4>\r\n        <ul>\r\n            <li>Items must be in original condition with tags attached</li>\r\n            <li>Unworn, unwashed, and free from any damage or alterations</li>\r\n            <li>Original packaging and accessories must be included</li>\r\n            <li>Certain items like undergarments, swimwear, and personalized items cannot be returned</li>\r\n        </ul>\r\n        \r\n        <h4>Return Process</h4>\r\n        <ol>\r\n            <li>Contact our customer service at returns@avenuefashion.com</li>\r\n            <li>Receive a return authorization and prepaid shipping label</li>\r\n            <li>Pack items securely and attach the return label</li>\r\n            <li>Drop off at any TCS or Leopards courier office</li>\r\n            <li>Refund will be processed within 5-7 business days after we receive your return</li>\r\n        </ol>\r\n        \r\n        <h4>Exchange Policy</h4>\r\n        <p>We offer free size exchanges within 14 days. Use our Virtual Try-On feature to select the right size and minimize exchanges.</p>', '2025-07-11 12:09:29', '2025-07-11 15:12:27'),
(4, 'Size Guide', 'size-guide', '<h3>Size Guide</h3>\r\n        <p>Finding the perfect fit is essential for your comfort and style. Use our comprehensive size guide along with our Virtual Try-On feature for the best results.</p>\r\n        \r\n        <h4>How to Measure Yourselfs</h4>\r\n        <ul>\r\n            <li><strong>Chest/Bust:</strong> Measure around the fullest part of your chest</li>\r\n            <li><strong>Waist:</strong> Measure around your natural waistline</li>\r\n            <li><strong>Hips:</strong> Measure around the fullest part of your hips</li>\r\n            <li><strong>Inseam:</strong> Measure from crotch to ankle</li>\r\n            <li><strong>Shoulder:</strong> Measure from shoulder point to shoulder point across the back</li>\r\n        </ul>\r\n        \r\n        <h4>Mens Clothing Sizes</h4>\r\n        <table class=\"table table-bordered\">\r\n            <tr><th>Size</th><th>Chest (inches)</th><th>Waist (inches)</th><th>Hip (inches)</th></tr>\r\n            <tr><td>S</td><td>36-38</td><td>30-32</td><td>36-38</td></tr>\r\n            <tr><td>M</td><td>38-40</td><td>32-34</td><td>38-40</td></tr>\r\n            <tr><td>L</td><td>40-42</td><td>34-36</td><td>40-42</td></tr>\r\n            <tr><td>XL</td><td>42-44</td><td>36-38</td><td>42-44</td></tr>\r\n            <tr><td>XXL</td><td>44-46</td><td>38-40</td><td>44-46</td></tr>\r\n        </table>\r\n        \r\n        <h4>Womens Clothing Sizes</h4>\r\n        <table class=\"table table-bordered\">\r\n            <tr><th>Size</th><th>Bust (inches)</th><th>Waist (inches)</th><th>Hip (inches)</th></tr>\r\n            <tr><td>XS</td><td>32-34</td><td>24-26</td><td>34-36</td></tr>\r\n            <tr><td>S</td><td>34-36</td><td>26-28</td><td>36-38</td></tr>\r\n            <tr><td>M</td><td>36-38</td><td>28-30</td><td>38-40</td></tr>\r\n            <tr><td>L</td><td>38-40</td><td>30-32</td><td>40-42</td></tr>\r\n            <tr><td>XL</td><td>40-42</td><td>32-34</td><td>42-44</td></tr>\r\n        </table>\r\n        \r\n        <h4>Shoe Sizes</h4>\r\n        <p>Our shoes are available in UK sizes. Refer to the conversion chart below:</p>\r\n        <table class=\"table table-bordered\">\r\n            <tr><th>UK Size</th><th>EU Size</th><th>US Men</th><th>US Women</th></tr>\r\n            <tr><td>6</td><td>39</td><td>7</td><td>8.5</td></tr>\r\n            <tr><td>7</td><td>40</td><td>8</td><td>9.5</td></tr>\r\n            <tr><td>8</td><td>41</td><td>9</td><td>10.5</td></tr>\r\n            <tr><td>9</td><td>42</td><td>10</td><td>11.5</td></tr>\r\n            <tr><td>10</td><td>43</td><td>11</td><td>12.5</td></tr>\r\n        </table>\r\n        \r\n        <h4>Virtual Try-On Tips</h4>\r\n        <ul>\r\n            <li>Upload a clear, well-lit photo of yourself</li>\r\n            <li>Stand straight with arms slightly away from your body</li>\r\n            <li>Wear fitted clothing for better accuracy</li>\r\n            <li>Use a plain background if possible</li>\r\n            <li>The Virtual Try-On provides a visual guide but always refer to size measurements</li>\r\n        </ul>', '2025-07-11 12:09:29', '2025-07-11 15:11:39'),
(5, 'Payment & Security', 'payment-security', '<h3>Payment Options</h3>\r\n        <p>We offer secure and convenient payment methods to make your shopping experience smooth and safe.</p>\r\n        \r\n        <h4>Accepted Payment Methods</h4>\r\n        <ul>\r\n            <li><strong>Credit/Debit Cards:</strong> Visa, MasterCard, American Express (processed through Stripe)</li>\r\n            \r\n            \r\n            <li><strong>Cash on Delivery:</strong> Available for orders under Rs 20,000 (additional Rs 100 fee)</li>\r\n        </ul>\r\n        \r\n        <h4>Payment Security</h4>\r\n        <ul>\r\n            <li>All payments are processed through Stripe, ensuring PCI DSS compliance</li>\r\n            <li>SSL encryption protects your payment information during transmission</li>\r\n            <li>We never store your complete credit card information on our servers</li>\r\n            <li>Two-factor authentication available for account security</li>\r\n            <li>Regular security audits and monitoring for fraud prevention</li>\r\n        </ul>\r\n        \r\n        <h4>Currency</h4>\r\n        <p>All prices are displayed in Pakistani Rupees (PKR). International cards are accepted with automatic currency conversion.</p>\r\n        \r\n        <h4>Payment Processing</h4>\r\n        <ul>\r\n            <li>Card payments are processed immediately upon order confirmation</li>\r\n            <li>Digital wallet payments may take 1-2 hours to verify</li>\r\n            <li>Cash on Delivery orders are confirmed once payment is received</li>\r\n            <li>Failed payments will automatically cancel the order after 24 hours</li>\r\n        </ul>\r\n        \r\n        <h4>Refunds</h4>\r\n        <ul>\r\n            <li>Card refunds are processed within 5-7 business days</li>\r\n            <li>Digital wallet refunds are instant once processed</li>\r\n            <li>Bank transfer refunds may take 3-5 business days</li>\r\n            <li>Cash on Delivery returns are refunded via bank transfer</li>\r\n        </ul>', '2025-07-11 12:09:29', '2025-07-11 15:13:55');

-- --------------------------------------------------------

--
-- Table structure for table `wishlist`
--

CREATE TABLE `wishlist` (
  `wishlist_id` int(10) NOT NULL,
  `customer_email` varchar(255) NOT NULL,
  `product_id` int(10) NOT NULL,
  `date_added` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure for view `cart_details_view`
--
DROP TABLE IF EXISTS `cart_details_view`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `cart_details_view`  AS SELECT `c`.`cart_id` AS `cart_id`, `c`.`p_id` AS `p_id`, `c`.`ip_add` AS `ip_add`, `c`.`customer_id` AS `customer_id`, `c`.`qty` AS `qty`, `c`.`p_price` AS `p_price`, `c`.`size` AS `size`, `c`.`color_variant` AS `color_variant`, `c`.`variant_image` AS `variant_image`, `c`.`session_id` AS `session_id`, `c`.`added_date` AS `added_date`, `c`.`updated_date` AS `updated_date`, `p`.`product_title` AS `product_title`, `p`.`product_img1` AS `product_img1`, `p`.`product_url` AS `product_url`, `p`.`product_availability` AS `product_availability`, `pc`.`p_cat_title` AS `p_cat_title`, `m`.`manufacturer_title` AS `manufacturer_title`, `c`.`qty`* `c`.`p_price` AS `total_price` FROM (((`cart` `c` left join `products` `p` on(`c`.`p_id` = `p`.`product_id`)) left join `product_categories` `pc` on(`p`.`p_cat_id` = `pc`.`p_cat_id`)) left join `manufacturers` `m` on(`p`.`manufacturer_id` = `m`.`manufacturer_id`)) ;

-- --------------------------------------------------------

--
-- Structure for view `order_summary_view`
--
DROP TABLE IF EXISTS `order_summary_view`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `order_summary_view`  AS SELECT `co`.`order_id` AS `order_id`, `co`.`customer_id` AS `customer_id`, `co`.`due_amount` AS `due_amount`, `co`.`invoice_no` AS `invoice_no`, `co`.`qty` AS `qty`, `co`.`size` AS `size`, `co`.`color_variant` AS `color_variant`, `co`.`order_date` AS `order_date`, `co`.`order_status` AS `order_status`, `co`.`shipping_address` AS `shipping_address`, `co`.`billing_address` AS `billing_address`, `co`.`payment_status` AS `payment_status`, `co`.`tracking_number` AS `tracking_number`, `co`.`notes` AS `notes`, `co`.`updated_date` AS `updated_date`, `c`.`customer_name` AS `customer_name`, `c`.`customer_email` AS `customer_email`, `c`.`customer_contact` AS `customer_contact`, `p`.`payment_status` AS `payment_status_detail`, `p`.`payment_mode` AS `payment_mode`, `p`.`transaction_id` AS `transaction_id` FROM ((`customer_orders` `co` left join `customers` `c` on(`co`.`customer_id` = `c`.`customer_id`)) left join `payments` `p` on(`co`.`invoice_no` = `p`.`invoice_no`)) ;

-- --------------------------------------------------------

--
-- Structure for view `product_details_view`
--
DROP TABLE IF EXISTS `product_details_view`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `product_details_view`  AS SELECT `p`.`product_id` AS `product_id`, `p`.`p_cat_id` AS `p_cat_id`, `p`.`cat_id` AS `cat_id`, `p`.`manufacturer_id` AS `manufacturer_id`, `p`.`date` AS `date`, `p`.`product_title` AS `product_title`, `p`.`product_brand` AS `product_brand`, `p`.`product_url` AS `product_url`, `p`.`product_img1` AS `product_img1`, `p`.`product_img2` AS `product_img2`, `p`.`product_img3` AS `product_img3`, `p`.`product_img4` AS `product_img4`, `p`.`product_img5` AS `product_img5`, `p`.`product_img6` AS `product_img6`, `p`.`product_img7` AS `product_img7`, `p`.`product_img8` AS `product_img8`, `p`.`product_price` AS `product_price`, `p`.`product_psp_price` AS `product_psp_price`, `p`.`product_desc` AS `product_desc`, `p`.`product_features` AS `product_features`, `p`.`product_video` AS `product_video`, `p`.`product_keywords` AS `product_keywords`, `p`.`product_label` AS `product_label`, `p`.`product_weight` AS `product_weight`, `p`.`product_dimensions` AS `product_dimensions`, `p`.`product_model` AS `product_model`, `p`.`product_warranty` AS `product_warranty`, `p`.`product_availability` AS `product_availability`, `p`.`shipping_info` AS `shipping_info`, `p`.`return_policy` AS `return_policy`, `p`.`status` AS `status`, `p`.`has_variants` AS `has_variants`, `p`.`has_sizes` AS `has_sizes`, `p`.`stock_quantity` AS `stock_quantity`, `p`.`min_stock_alert` AS `min_stock_alert`, `p`.`views_count` AS `views_count`, `p`.`sales_count` AS `sales_count`, `p`.`created_date` AS `created_date`, `p`.`updated_date` AS `updated_date`, `pc`.`p_cat_title` AS `p_cat_title`, `pc`.`sizing_type` AS `sizing_type`, `c`.`cat_title` AS `cat_title`, `m`.`manufacturer_title` AS `manufacturer_title`, coalesce((select sum(`pv`.`stock_quantity`) from `product_variants` `pv` where `pv`.`product_id` = `p`.`product_id` and `pv`.`variant_status` = 'active'),(select sum(`ps`.`stock_quantity`) from `product_sizes` `ps` where `ps`.`product_id` = `p`.`product_id` and `ps`.`ps_status` = 'active'),`p`.`stock_quantity`) AS `total_stock`, (select avg(`product_reviews`.`review_rating`) from `product_reviews` where `product_reviews`.`product_id` = `p`.`product_id` and `product_reviews`.`status` = 'approved') AS `avg_rating`, (select count(0) from `product_reviews` where `product_reviews`.`product_id` = `p`.`product_id` and `product_reviews`.`status` = 'approved') AS `review_count` FROM (((`products` `p` left join `product_categories` `pc` on(`p`.`p_cat_id` = `pc`.`p_cat_id`)) left join `categories` `c` on(`p`.`cat_id` = `c`.`cat_id`)) left join `manufacturers` `m` on(`p`.`manufacturer_id` = `m`.`manufacturer_id`)) WHERE `p`.`status` = 'product' ;

-- --------------------------------------------------------

--
-- Structure for view `product_performance_view`
--
DROP TABLE IF EXISTS `product_performance_view`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `product_performance_view`  AS SELECT `p`.`product_id` AS `product_id`, `p`.`product_title` AS `product_title`, `p`.`product_price` AS `product_price`, `p`.`stock_quantity` AS `stock_quantity`, `p`.`views_count` AS `views_count`, `p`.`sales_count` AS `sales_count`, coalesce(avg(`pr`.`review_rating`),0) AS `avg_rating`, count(`pr`.`review_id`) AS `total_reviews`, (select count(0) from `cart` where `cart`.`p_id` = `p`.`product_id`) AS `cart_count`, (select count(0) from (`wishlist` `w` join `products` `prod` on(`w`.`product_id` = `prod`.`product_id`)) where `w`.`product_id` = `p`.`product_id`) AS `wishlist_count` FROM (`products` `p` left join `product_reviews` `pr` on(`p`.`product_id` = `pr`.`product_id` and `pr`.`status` = 'approved')) WHERE `p`.`status` = 'product' GROUP BY `p`.`product_id` ;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `about_us`
--
ALTER TABLE `about_us`
  ADD PRIMARY KEY (`about_id`);

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`admin_id`),
  ADD UNIQUE KEY `admin_email` (`admin_email`),
  ADD KEY `admin_status` (`admin_status`);

--
-- Indexes for table `admin_logs`
--
ALTER TABLE `admin_logs`
  ADD PRIMARY KEY (`log_id`),
  ADD KEY `admin_id` (`admin_id`),
  ADD KEY `action` (`action`),
  ADD KEY `created_date` (`created_date`);

--
-- Indexes for table `boxes_section`
--
ALTER TABLE `boxes_section`
  ADD PRIMARY KEY (`box_id`),
  ADD KEY `status` (`status`);

--
-- Indexes for table `cart`
--
ALTER TABLE `cart`
  ADD PRIMARY KEY (`cart_id`),
  ADD KEY `p_id` (`p_id`),
  ADD KEY `ip_add` (`ip_add`),
  ADD KEY `customer_id` (`customer_id`),
  ADD KEY `session_id` (`session_id`),
  ADD KEY `idx_cart_ip_product` (`ip_add`,`p_id`),
  ADD KEY `idx_cart_customer_product` (`customer_id`,`p_id`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`cat_id`),
  ADD KEY `cat_status` (`cat_status`),
  ADD KEY `cat_top` (`cat_top`);

--
-- Indexes for table `colors`
--
ALTER TABLE `colors`
  ADD PRIMARY KEY (`color_id`),
  ADD UNIQUE KEY `color_name` (`color_name`),
  ADD UNIQUE KEY `color_code` (`color_code`),
  ADD KEY `color_status` (`color_status`);

--
-- Indexes for table `contact_us`
--
ALTER TABLE `contact_us`
  ADD PRIMARY KEY (`contact_id`);

--
-- Indexes for table `customers`
--
ALTER TABLE `customers`
  ADD PRIMARY KEY (`customer_id`),
  ADD UNIQUE KEY `customer_email` (`customer_email`),
  ADD KEY `customer_status` (`customer_status`),
  ADD KEY `email_verified` (`email_verified`);

--
-- Indexes for table `customer_orders`
--
ALTER TABLE `customer_orders`
  ADD PRIMARY KEY (`order_id`),
  ADD UNIQUE KEY `invoice_no` (`invoice_no`),
  ADD KEY `customer_id` (`customer_id`),
  ADD KEY `order_status` (`order_status`),
  ADD KEY `payment_status` (`payment_status`),
  ADD KEY `order_date` (`order_date`),
  ADD KEY `idx_orders_customer_status` (`customer_id`,`order_status`),
  ADD KEY `idx_orders_date_status` (`order_date`,`order_status`);

--
-- Indexes for table `enquiry_types`
--
ALTER TABLE `enquiry_types`
  ADD PRIMARY KEY (`enquiry_id`);

--
-- Indexes for table `manufacturers`
--
ALTER TABLE `manufacturers`
  ADD PRIMARY KEY (`manufacturer_id`),
  ADD KEY `manufacturer_status` (`manufacturer_status`);

--
-- Indexes for table `order_tracking`
--
ALTER TABLE `order_tracking`
  ADD PRIMARY KEY (`tracking_id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `tracking_number` (`tracking_number`),
  ADD KEY `status` (`status`),
  ADD KEY `invoice_no` (`invoice_no`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`payment_id`),
  ADD KEY `invoice_no` (`invoice_no`),
  ADD KEY `customer_id` (`customer_id`),
  ADD KEY `payment_status` (`payment_status`),
  ADD KEY `transaction_id` (`transaction_id`),
  ADD KEY `idx_payments_date_status` (`payment_date`,`payment_status`);

--
-- Indexes for table `pending_orders`
--
ALTER TABLE `pending_orders`
  ADD PRIMARY KEY (`order_id`),
  ADD KEY `customer_id` (`customer_id`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `invoice_no` (`invoice_no`),
  ADD KEY `order_status` (`order_status`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`product_id`),
  ADD UNIQUE KEY `product_url` (`product_url`),
  ADD KEY `p_cat_id` (`p_cat_id`),
  ADD KEY `cat_id` (`cat_id`),
  ADD KEY `manufacturer_id` (`manufacturer_id`),
  ADD KEY `status` (`status`),
  ADD KEY `has_variants` (`has_variants`),
  ADD KEY `has_sizes` (`has_sizes`),
  ADD KEY `product_availability` (`product_availability`),
  ADD KEY `idx_products_status_variants` (`status`,`has_variants`),
  ADD KEY `idx_products_availability` (`product_availability`,`status`);
ALTER TABLE `products` ADD FULLTEXT KEY `search_text` (`product_title`,`product_desc`,`product_keywords`);

--
-- Indexes for table `product_answers`
--
ALTER TABLE `product_answers`
  ADD PRIMARY KEY (`answer_id`),
  ADD KEY `question_id` (`question_id`),
  ADD KEY `status` (`status`);

--
-- Indexes for table `product_categories`
--
ALTER TABLE `product_categories`
  ADD PRIMARY KEY (`p_cat_id`),
  ADD KEY `p_cat_status` (`p_cat_status`),
  ADD KEY `sizing_type` (`sizing_type`);

--
-- Indexes for table `product_questions`
--
ALTER TABLE `product_questions`
  ADD PRIMARY KEY (`question_id`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `status` (`status`);

--
-- Indexes for table `product_reviews`
--
ALTER TABLE `product_reviews`
  ADD PRIMARY KEY (`review_id`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `customer_email` (`customer_email`),
  ADD KEY `status` (`status`),
  ADD KEY `review_rating` (`review_rating`),
  ADD KEY `idx_reviews_product_status` (`product_id`,`status`);

--
-- Indexes for table `product_sizes`
--
ALTER TABLE `product_sizes`
  ADD PRIMARY KEY (`ps_id`),
  ADD UNIQUE KEY `product_size` (`product_id`,`size_id`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `size_id` (`size_id`),
  ADD KEY `ps_status` (`ps_status`),
  ADD KEY `idx_product_sizes_product_size` (`product_id`,`size_id`),
  ADD KEY `idx_product_sizes_stock` (`stock_quantity`,`ps_status`);

--
-- Indexes for table `product_variants`
--
ALTER TABLE `product_variants`
  ADD PRIMARY KEY (`variant_id`),
  ADD UNIQUE KEY `product_color` (`product_id`,`color_name`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `color_name` (`color_name`),
  ADD KEY `variant_status` (`variant_status`),
  ADD KEY `idx_variants_product_color` (`product_id`,`color_name`);

--
-- Indexes for table `recently_viewed`
--
ALTER TABLE `recently_viewed`
  ADD PRIMARY KEY (`view_id`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `customer_email` (`customer_email`),
  ADD KEY `session_id` (`session_id`),
  ADD KEY `view_date` (`view_date`);

--
-- Indexes for table `sizes`
--
ALTER TABLE `sizes`
  ADD PRIMARY KEY (`size_id`),
  ADD UNIQUE KEY `size_type_name` (`size_type`,`size_name`),
  ADD KEY `size_type` (`size_type`),
  ADD KEY `size_order` (`size_order`),
  ADD KEY `size_status` (`size_status`),
  ADD KEY `idx_sizes_type_order` (`size_type`,`size_order`);

--
-- Indexes for table `slider`
--
ALTER TABLE `slider`
  ADD PRIMARY KEY (`slide_id`),
  ADD KEY `status` (`status`),
  ADD KEY `slide_order` (`slide_order`);

--
-- Indexes for table `stock_movements`
--
ALTER TABLE `stock_movements`
  ADD PRIMARY KEY (`movement_id`);

--
-- Indexes for table `terms`
--
ALTER TABLE `terms`
  ADD PRIMARY KEY (`term_id`);

--
-- Indexes for table `wishlist`
--
ALTER TABLE `wishlist`
  ADD PRIMARY KEY (`wishlist_id`),
  ADD UNIQUE KEY `customer_product` (`customer_email`,`product_id`),
  ADD KEY `customer_email` (`customer_email`),
  ADD KEY `product_id` (`product_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `about_us`
--
ALTER TABLE `about_us`
  MODIFY `about_id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `admin_id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `admin_logs`
--
ALTER TABLE `admin_logs`
  MODIFY `log_id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=63;

--
-- AUTO_INCREMENT for table `boxes_section`
--
ALTER TABLE `boxes_section`
  MODIFY `box_id` int(10) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `cart`
--
ALTER TABLE `cart`
  MODIFY `cart_id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=71;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `cat_id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `colors`
--
ALTER TABLE `colors`
  MODIFY `color_id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `contact_us`
--
ALTER TABLE `contact_us`
  MODIFY `contact_id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `customers`
--
ALTER TABLE `customers`
  MODIFY `customer_id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `customer_orders`
--
ALTER TABLE `customer_orders`
  MODIFY `order_id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- AUTO_INCREMENT for table `enquiry_types`
--
ALTER TABLE `enquiry_types`
  MODIFY `enquiry_id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `manufacturers`
--
ALTER TABLE `manufacturers`
  MODIFY `manufacturer_id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `order_tracking`
--
ALTER TABLE `order_tracking`
  MODIFY `tracking_id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=48;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `payment_id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `pending_orders`
--
ALTER TABLE `pending_orders`
  MODIFY `order_id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=38;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `product_id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=36;

--
-- AUTO_INCREMENT for table `product_answers`
--
ALTER TABLE `product_answers`
  MODIFY `answer_id` int(10) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `product_categories`
--
ALTER TABLE `product_categories`
  MODIFY `p_cat_id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `product_questions`
--
ALTER TABLE `product_questions`
  MODIFY `question_id` int(10) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `product_reviews`
--
ALTER TABLE `product_reviews`
  MODIFY `review_id` int(10) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `product_sizes`
--
ALTER TABLE `product_sizes`
  MODIFY `ps_id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=216;

--
-- AUTO_INCREMENT for table `product_variants`
--
ALTER TABLE `product_variants`
  MODIFY `variant_id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=106;

--
-- AUTO_INCREMENT for table `recently_viewed`
--
ALTER TABLE `recently_viewed`
  MODIFY `view_id` int(10) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `sizes`
--
ALTER TABLE `sizes`
  MODIFY `size_id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=55;

--
-- AUTO_INCREMENT for table `slider`
--
ALTER TABLE `slider`
  MODIFY `slide_id` int(10) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `stock_movements`
--
ALTER TABLE `stock_movements`
  MODIFY `movement_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `terms`
--
ALTER TABLE `terms`
  MODIFY `term_id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `wishlist`
--
ALTER TABLE `wishlist`
  MODIFY `wishlist_id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `admin_logs`
--
ALTER TABLE `admin_logs`
  ADD CONSTRAINT `fk_logs_admin` FOREIGN KEY (`admin_id`) REFERENCES `admins` (`admin_id`) ON DELETE CASCADE;

--
-- Constraints for table `cart`
--
ALTER TABLE `cart`
  ADD CONSTRAINT `fk_cart_customer` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`customer_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_cart_product` FOREIGN KEY (`p_id`) REFERENCES `products` (`product_id`) ON DELETE CASCADE;

--
-- Constraints for table `customer_orders`
--
ALTER TABLE `customer_orders`
  ADD CONSTRAINT `fk_orders_customer` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`customer_id`) ON DELETE CASCADE;

--
-- Constraints for table `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `fk_payments_customer` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`customer_id`) ON DELETE CASCADE;

--
-- Constraints for table `pending_orders`
--
ALTER TABLE `pending_orders`
  ADD CONSTRAINT `fk_pending_customer` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`customer_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_pending_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`) ON DELETE CASCADE;

--
-- Constraints for table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `fk_products_cat` FOREIGN KEY (`cat_id`) REFERENCES `categories` (`cat_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_products_manufacturer` FOREIGN KEY (`manufacturer_id`) REFERENCES `manufacturers` (`manufacturer_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_products_p_cat` FOREIGN KEY (`p_cat_id`) REFERENCES `product_categories` (`p_cat_id`) ON DELETE CASCADE;

--
-- Constraints for table `product_answers`
--
ALTER TABLE `product_answers`
  ADD CONSTRAINT `fk_answers_question` FOREIGN KEY (`question_id`) REFERENCES `product_questions` (`question_id`) ON DELETE CASCADE;

--
-- Constraints for table `product_questions`
--
ALTER TABLE `product_questions`
  ADD CONSTRAINT `fk_questions_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`) ON DELETE CASCADE;

--
-- Constraints for table `product_reviews`
--
ALTER TABLE `product_reviews`
  ADD CONSTRAINT `fk_reviews_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`) ON DELETE CASCADE;

--
-- Constraints for table `product_sizes`
--
ALTER TABLE `product_sizes`
  ADD CONSTRAINT `fk_product_sizes_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_product_sizes_size` FOREIGN KEY (`size_id`) REFERENCES `sizes` (`size_id`) ON DELETE CASCADE;

--
-- Constraints for table `product_variants`
--
ALTER TABLE `product_variants`
  ADD CONSTRAINT `fk_variants_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`) ON DELETE CASCADE;

--
-- Constraints for table `recently_viewed`
--
ALTER TABLE `recently_viewed`
  ADD CONSTRAINT `fk_recently_viewed_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`) ON DELETE CASCADE;

--
-- Constraints for table `wishlist`
--
ALTER TABLE `wishlist`
  ADD CONSTRAINT `fk_wishlist_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
