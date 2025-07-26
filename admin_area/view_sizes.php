<?php
if(!isset($_SESSION['admin_email'])){
    echo "<script>window.open('login.php','_self')</script>";
    exit;
}

// Check if tables exist and create them if they don't
$check_sizes_table = "SHOW TABLES LIKE 'sizes'";
$sizes_table_exists = mysqli_query($con, $check_sizes_table);

$check_product_sizes_table = "SHOW TABLES LIKE 'product_sizes'";
$product_sizes_table_exists = mysqli_query($con, $check_product_sizes_table);

if(mysqli_num_rows($sizes_table_exists) == 0 || mysqli_num_rows($product_sizes_table_exists) == 0) {
    // Create tables if they don't exist
    $create_tables_sql = "
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

    INSERT IGNORE INTO `sizes` (`size_name`, `size_type`, `size_order`, `size_desc`) VALUES
    ('XS', 'clothing', 1, 'Extra Small'),
    ('S', 'clothing', 2, 'Small'),
    ('M', 'clothing', 3, 'Medium'),
    ('L', 'clothing', 4, 'Large'),
    ('XL', 'clothing', 5, 'Extra Large'),
    ('XXL', 'clothing', 6, 'Double Extra Large'),
    ('XXXL', 'clothing', 7, 'Triple Extra Large');
    ";
    
    mysqli_multi_query($con, $create_tables_sql);
    
    // Clear any remaining results
    while(mysqli_next_result($con)) {
        if($result = mysqli_store_result($con)) {
            mysqli_free_result($result);
        }
    }
}

// Check if size_status column exists, if not add it
$check_column = "SHOW COLUMNS FROM sizes LIKE 'size_status'";
$column_exists = mysqli_query($con, $check_column);
if(mysqli_num_rows($column_exists) == 0) {
    $add_column = "ALTER TABLE sizes ADD COLUMN size_status enum('active','inactive') DEFAULT 'active'";
    mysqli_query($con, $add_column);
}

// Check if created_date column exists, if not add it
$check_created_date = "SHOW COLUMNS FROM sizes LIKE 'created_date'";
$created_date_exists = mysqli_query($con, $check_created_date);
if(mysqli_num_rows($created_date_exists) == 0) {
    $add_created_date = "ALTER TABLE sizes ADD COLUMN created_date timestamp DEFAULT CURRENT_TIMESTAMP";
    mysqli_query($con, $add_created_date);
}
?>

<div class="row">
    <div class="col-lg-12">
        <ol class="breadcrumb">
            <li class="active">
                <i class="fa fa-dashboard"></i> Dashboard / View Sizes
            </li>
        </ol>
    </div>
</div>

<div class="row">
    <div class="col-lg-12">
        <div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title">
                    <i class="fa fa-tags fa-fw"></i> View Sizes
                </h3>
            </div>
            <div class="panel-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover table-striped">
                        <thead>
                            <tr>
                                <th>Size ID</th>
                                <th>Size Name</th>
                                <th>Size Type</th>
                                <th>Size Order</th>
                                <th>Products Using</th>
                                <th>Created Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $i = 0;
                            
                            // Build query based on available columns
                            $base_query = "SELECT s.size_id, s.size_name, s.size_type, s.size_order";
                            
                            // Check if size_status column exists
                            $check_status_col = "SHOW COLUMNS FROM sizes LIKE 'size_status'";
                            $status_col_exists = mysqli_query($con, $check_status_col);
                            $has_status = mysqli_num_rows($status_col_exists) > 0;
                            
                            // Check if created_date column exists
                            $check_date_col = "SHOW COLUMNS FROM sizes LIKE 'created_date'";
                            $date_col_exists = mysqli_query($con, $check_date_col);
                            $has_date = mysqli_num_rows($date_col_exists) > 0;
                            
                            if($has_date) {
                                $base_query .= ", s.created_date";
                            }
                            
                            $get_sizes = $base_query . ", COUNT(ps.product_id) as product_count 
                                         FROM sizes s 
                                         LEFT JOIN product_sizes ps ON s.size_id = ps.size_id";
                            
                            if($has_status) {
                                $get_sizes .= " WHERE s.size_status = 'active'";
                            }
                            
                            $get_sizes .= " GROUP BY s.size_id 
                                           ORDER BY s.size_type, s.size_order, s.size_name";
                            
                            $run_sizes = mysqli_query($con, $get_sizes);
                            
                            if(!$run_sizes) {
                                echo "<tr><td colspan='7'>Error loading sizes: " . mysqli_error($con) . "</td></tr>";
                            } else if(mysqli_num_rows($run_sizes) == 0) {
                                echo "<tr><td colspan='7' class='text-center'>No sizes found. <a href='index.php?insert_size' class='btn btn-sm btn-primary'>Add your first size</a></td></tr>";
                            } else {
                                while($row_sizes = mysqli_fetch_array($run_sizes)) {
                                    $size_id = $row_sizes['size_id'];
                                    $size_name = $row_sizes['size_name'];
                                    $size_type = $row_sizes['size_type'];
                                    $size_order = $row_sizes['size_order'];
                                    $product_count = $row_sizes['product_count'];
                                    $created_date = $has_date && isset($row_sizes['created_date']) ? date('M d, Y', strtotime($row_sizes['created_date'])) : 'N/A';
                                    $i++;
                            ?>
                            <tr>
                                <td><?php echo $size_id; ?></td>
                                <td><strong><?php echo htmlspecialchars($size_name); ?></strong></td>
                                <td>
                                    <span class="label label-<?php 
                                        echo $size_type == 'clothing' ? 'primary' : 
                                             (strpos($size_type, 'shoes') !== false ? 'info' : 'default'); 
                                    ?>">
                                        <?php echo ucfirst(str_replace('_', ' ', $size_type)); ?>
                                    </span>
                                </td>
                                <td><?php echo $size_order; ?></td>
                                <td>
                                    <?php if($product_count > 0): ?>
                                        <span class="badge badge-success"><?php echo $product_count; ?> products</span>
                                    <?php else: ?>
                                        <span class="text-muted">Not used</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo $created_date; ?></td>
                                <td>
                                    <a href="index.php?edit_size=<?php echo $size_id; ?>" class="btn btn-primary btn-sm">
                                        <i class="fa fa-pencil"></i> Edit
                                    </a>
                                    <?php if($product_count == 0): ?>
                                    <a href="index.php?delete_size=<?php echo $size_id; ?>" 
                                       class="btn btn-danger btn-sm"
                                       onclick="return confirm('Are you sure you want to delete this size?')">
                                        <i class="fa fa-trash-o"></i> Delete
                                    </a>
                                    <?php else: ?>
                                    <button class="btn btn-danger btn-sm" disabled title="Cannot delete - size is being used by products">
                                        <i class="fa fa-trash-o"></i> Delete
                                    </button>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php 
                                }
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
                
                <?php if($i == 0): ?>
                <div class="alert alert-info">
                    <strong>No sizes found!</strong> 
                    <a href="index.php?insert_size" class="btn btn-primary btn-sm">Add your first size</a>
                </div>
                <?php endif; ?>
                
                <div class="text-right">
                    <a href="index.php?insert_size" class="btn btn-success">
                        <i class="fa fa-plus"></i> Add New Size
                    </a>
                    <a href="javascript:location.reload()" class="btn btn-info">
                        <i class="fa fa-refresh"></i> Refresh
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>