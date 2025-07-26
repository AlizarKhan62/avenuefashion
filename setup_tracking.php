<?php
// Order Tracking Setup Script
// Run this file once to create the order tracking table and update existing tables

include("includes/db.php");

echo "<h1>Order Tracking Setup</h1>";

// Create order_tracking table if it doesn't exist
$create_tracking_table = "
CREATE TABLE IF NOT EXISTS `order_tracking` (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
";

if(mysqli_query($con, $create_tracking_table)) {
    echo "<p style='color: green;'>✓ Order tracking table created successfully!</p>";
} else {
    echo "<p style='color: red;'>✗ Error creating order tracking table: " . mysqli_error($con) . "</p>";
}

// Add tracking fields to customer_orders table if they don't exist
$add_tracking_number = "ALTER TABLE `customer_orders` ADD COLUMN IF NOT EXISTS `tracking_number` varchar(255) AFTER `payment_status`";
$add_current_status = "ALTER TABLE `customer_orders` ADD COLUMN IF NOT EXISTS `current_status` varchar(100) DEFAULT 'pending' AFTER `tracking_number`";
$add_estimated_delivery = "ALTER TABLE `customer_orders` ADD COLUMN IF NOT EXISTS `estimated_delivery` datetime AFTER `current_status`";
$add_actual_delivery = "ALTER TABLE `customer_orders` ADD COLUMN IF NOT EXISTS `actual_delivery` datetime AFTER `estimated_delivery`";

$queries = [
    $add_tracking_number => "tracking_number column",
    $add_current_status => "current_status column", 
    $add_estimated_delivery => "estimated_delivery column",
    $add_actual_delivery => "actual_delivery column"
];

foreach($queries as $query => $description) {
    if(mysqli_query($con, $query)) {
        echo "<p style='color: green;'>✓ Added $description to customer_orders table!</p>";
    } else {
        echo "<p style='color: orange;'>⚠ $description may already exist or error: " . mysqli_error($con) . "</p>";
    }
}

// Update existing orders to have tracking numbers if they don't have them
$update_tracking = "
UPDATE customer_orders 
SET tracking_number = CONCAT('TRK', DATE_FORMAT(order_date, '%Y%m%d'), LPAD(order_id, 6, '0'))
WHERE tracking_number IS NULL OR tracking_number = ''
";

if(mysqli_query($con, $update_tracking)) {
    $affected = mysqli_affected_rows($con);
    echo "<p style='color: green;'>✓ Updated $affected existing orders with tracking numbers!</p>";
} else {
    echo "<p style='color: red;'>✗ Error updating tracking numbers: " . mysqli_error($con) . "</p>";
}

// Update order_status enum values
$update_order_status = "
ALTER TABLE `customer_orders` 
MODIFY COLUMN `order_status` enum('pending','confirmed','processing','packed','shipped','out_for_delivery','delivered','cancelled','returned','refunded') DEFAULT 'pending'
";

if(mysqli_query($con, $update_order_status)) {
    echo "<p style='color: green;'>✓ Updated order_status enum values!</p>";
} else {
    echo "<p style='color: orange;'>⚠ Order status enum update may have failed: " . mysqli_error($con) . "</p>";
}

echo "<h2>Setup Complete!</h2>";
echo "<p>Your order tracking system is now ready. You can:</p>";
echo "<ul>";
echo "<li><a href='track_order.php'>Test the customer tracking page</a></li>";
echo "<li><a href='admin_area/index.php?order_tracking'>Access admin order tracking</a></li>";
echo "<li><a href='my_orders.php'>View customer order history</a> (requires login)</li>";
echo "</ul>";

echo "<p><strong>Note:</strong> You can safely delete this setup file after running it once.</p>";
?>
