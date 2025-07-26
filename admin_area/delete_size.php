<?php
if(!isset($_SESSION['admin_email'])){
    echo "<script>window.open('login.php','_self')</script>";
    exit;
}

// Get and validate the size ID
$delete_id = isset($_GET['delete_size']) ? (int)$_GET['delete_size'] : 0;

if($delete_id <= 0) {
    echo "<script>alert('Invalid size ID')</script>";
    echo "<script>window.open('index.php?view_sizes','_self')</script>";
    exit;
}

// Check if size exists and get its details
$get_size = "SELECT * FROM sizes WHERE size_id = '$delete_id'";
$run_size = mysqli_query($con, $get_size);

if(!$run_size) {
    echo "<script>alert('Database error: " . mysqli_error($con) . "')</script>";
    echo "<script>window.open('index.php?view_sizes','_self')</script>";
    exit;
}

if(mysqli_num_rows($run_size) == 0) {
    echo "<script>alert('Size not found')</script>";
    echo "<script>window.open('index.php?view_sizes','_self')</script>";
    exit;
}

$row_size = mysqli_fetch_array($run_size);
$size_name = $row_size['size_name'];
$size_type = $row_size['size_type'];

// Check if this size is being used by any products
$check_usage = "SELECT COUNT(*) as count FROM product_sizes WHERE size_id = '$delete_id'";
$run_check = mysqli_query($con, $check_usage);

if($run_check) {
    $usage_row = mysqli_fetch_array($run_check);
    $usage_count = $usage_row['count'];
    
    if($usage_count > 0) {
        echo "<script>alert('Cannot delete this size. It is being used by $usage_count product(s). Please remove it from all products first.')</script>";
        echo "<script>window.open('index.php?view_sizes','_self')</script>";
        exit;
    }
}

// If we reach here, it's safe to delete the size
$delete_size = "DELETE FROM sizes WHERE size_id = '$delete_id'";
$run_delete = mysqli_query($con, $delete_size);

if($run_delete) {
    echo "<script>alert('Size \"$size_name\" has been deleted successfully')</script>";
    echo "<script>window.open('index.php?view_sizes','_self')</script>";
} else {
    echo "<script>alert('Error deleting size: " . mysqli_error($con) . "')</script>";
    echo "<script>window.open('index.php?view_sizes','_self')</script>";
}
?>
