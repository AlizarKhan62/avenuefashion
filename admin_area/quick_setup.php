<?php
// Quick setup script to create missing tables and fix issues
include("includes/db.php");

echo "<h1>Quick Setup & Fix</h1>";
echo "<style>body{font-family:Arial;margin:20px;} .success{color:green;} .error{color:red;}</style>";

// 1. Create missing tables
echo "<h2>1. Creating Missing Tables</h2>";

$tables = [
    "colors" => "CREATE TABLE IF NOT EXISTS `colors` (
        `color_id` int(10) NOT NULL AUTO_INCREMENT,
        `color_name` varchar(50) NOT NULL,
        `color_code` varchar(7) NOT NULL,
        `color_desc` text DEFAULT NULL,
        `created_date` timestamp DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`color_id`),
        UNIQUE KEY `color_name` (`color_name`),
        UNIQUE KEY `color_code` (`color_code`)
    ) ENGINE=InnoDB DEFAULT CHARSET=latin1",
    
    "sizes" => "CREATE TABLE IF NOT EXISTS `sizes` (
        `size_id` int(10) NOT NULL AUTO_INCREMENT,
        `size_name` varchar(20) NOT NULL,
        `size_type` enum('clothing','shoes','numeric','custom') DEFAULT 'clothing',
        `size_order` int(3) DEFAULT 1,
        `size_desc` text DEFAULT NULL,
        `created_date` timestamp DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`size_id`),
        KEY `size_type` (`size_type`),
        KEY `size_order` (`size_order`)
    ) ENGINE=InnoDB DEFAULT CHARSET=latin1",
    
    "product_variants" => "CREATE TABLE IF NOT EXISTS `product_variants` (
        `variant_id` int(10) NOT NULL AUTO_INCREMENT,
        `product_id` int(10) NOT NULL,
        `color_name` varchar(50) NOT NULL,
        `color_code` varchar(7) NOT NULL,
        `variant_image` text DEFAULT NULL,
        `variant_images` text DEFAULT NULL,
        `stock_quantity` int(10) DEFAULT 0,
        `variant_price` decimal(10,2) DEFAULT NULL,
        `size_id` int(10) DEFAULT NULL,
        PRIMARY KEY (`variant_id`),
        KEY `product_id` (`product_id`),
        KEY `size_id` (`size_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=latin1"
];

foreach($tables as $table_name => $sql) {
    if(mysqli_query($con, $sql)) {
        echo "<span class='success'>✓ Table '$table_name' created/verified</span><br>";
    } else {
        echo "<span class='error'>✗ Error with table '$table_name': " . mysqli_error($con) . "</span><br>";
    }
}

// 2. Add missing columns
echo "<h2>2. Adding Missing Columns</h2>";

$columns = [
    "ALTER TABLE `products` ADD COLUMN IF NOT EXISTS `has_variants` tinyint(1) DEFAULT 0",
    "ALTER TABLE `product_categories` ADD COLUMN IF NOT EXISTS `sizing_type` enum('clothing','shoes') DEFAULT 'clothing'",
    "ALTER TABLE `cart` ADD COLUMN IF NOT EXISTS `color_variant` varchar(50) DEFAULT NULL",
    "ALTER TABLE `pending_orders` ADD COLUMN IF NOT EXISTS `color_variant` varchar(50) DEFAULT NULL"
];

foreach($columns as $sql) {
    if(mysqli_query($con, $sql)) {
        echo "<span class='success'>✓ Column added/verified</span><br>";
    } else {
        echo "<span class='error'>✗ Error adding column: " . mysqli_error($con) . "</span><br>";
    }
}

// 3. Insert default data
echo "<h2>3. Inserting Default Data</h2>";

// Default colors
$default_colors = [
    ['Black', '#000000', 'Classic black color'],
    ['White', '#FFFFFF', 'Pure white color'],
    ['Red', '#FF0000', 'Bright red color'],
    ['Blue', '#0000FF', 'Classic blue color'],
    ['Green', '#008000', 'Natural green color']
];

foreach($default_colors as $color) {
    $sql = "INSERT IGNORE INTO colors (color_name, color_code, color_desc) VALUES ('{$color[0]}', '{$color[1]}', '{$color[2]}')";
    if(mysqli_query($con, $sql)) {
        if(mysqli_affected_rows($con) > 0) {
            echo "<span class='success'>✓ Added color: {$color[0]}</span><br>";
        }
    }
}

// Default sizes
$default_sizes = [
    ['XS', 'clothing', 1, 'Extra Small'],
    ['S', 'clothing', 2, 'Small'],
    ['M', 'clothing', 3, 'Medium'],
    ['L', 'clothing', 4, 'Large'],
    ['XL', 'clothing', 5, 'Extra Large'],
    ['38', 'shoes', 1, 'EU Size 38'],
    ['39', 'shoes', 2, 'EU Size 39'],
    ['40', 'shoes', 3, 'EU Size 40'],
    ['41', 'shoes', 4, 'EU Size 41'],
    ['42', 'shoes', 5, 'EU Size 42']
];

foreach($default_sizes as $size) {
    $sql = "INSERT IGNORE INTO sizes (size_name, size_type, size_order, size_desc) VALUES ('{$size[0]}', '{$size[1]}', {$size[2]}, '{$size[3]}')";
    if(mysqli_query($con, $sql)) {
        if(mysqli_affected_rows($con) > 0) {
            echo "<span class='success'>✓ Added size: {$size[0]}</span><br>";
        }
    }
}

echo "<h2>4. Setup Complete!</h2>";
echo "<p><strong>Next Steps:</strong></p>";
echo "<ol>";
echo "<li>Replace your current index.php with index_complete_fixed.php</li>";
echo "<li>Make sure all the new PHP files are in your admin_area folder</li>";
echo "<li>Test the color management, size management, and stock management links</li>";
echo "</ol>";

echo "<p><a href='index.php?dashboard' style='background:#007bff;color:white;padding:10px 20px;text-decoration:none;border-radius:5px;'>Go to Admin Dashboard</a></p>";
?>
