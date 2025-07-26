<?php
if(!isset($_SESSION['admin_email'])){
    echo "<script>window.open('login.php','_self')</script>";
} else {

// Check if product_variants table exists
$check_table = "SHOW TABLES LIKE 'product_variants'";
$table_exists = mysqli_query($con, $check_table);

if(!$table_exists || mysqli_num_rows($table_exists) == 0) {
    echo "<div class='alert alert-danger'>";
    echo "<h3>Product Variants table not found!</h3>";
    echo "<p>The stock management system requires database setup.</p>";
    echo "<a href='quick_setup.php' class='btn btn-primary'>Run Database Setup</a>";
    echo "</div>";
    return;
}
?>

<div class="row">
<div class="col-lg-12">
<ol class="breadcrumb">
<li class="active">
<i class="fa fa-dashboard"></i> Dashboard / Stock Management
</li>
</ol>
</div>
</div>

<div class="row">
<div class="col-lg-12">
<div class="panel panel-default">
<div class="panel-heading">
<h3 class="panel-title">
<i class="fa fa-cubes fa-fw"></i> Stock Management
</h3>
</div>
<div class="panel-body">

<!-- Stock Summary Cards -->
<div class="row" style="margin-bottom: 20px;">
<div class="col-md-3">
<div class="panel panel-primary">
<div class="panel-body text-center">
<h4><?php 
$total_stock_sql = "SELECT SUM(stock_quantity) as total FROM product_variants";
$run_total = mysqli_query($con, $total_stock_sql);
$total_stock = 0;
if($run_total) {
    $row_total = mysqli_fetch_array($run_total);
    $total_stock = $row_total['total'] ? $row_total['total'] : 0;
}
echo $total_stock; 
?></h4>
<p>Total Stock</p>
</div>
</div>
</div>

<div class="col-md-3">
<div class="panel panel-warning">
<div class="panel-body text-center">
<h4><?php 
$low_stock_sql = "SELECT COUNT(*) as total FROM product_variants WHERE stock_quantity <= 5 AND stock_quantity > 0";
$run_low = mysqli_query($con, $low_stock_sql);
$low_stock = 0;
if($run_low) {
    $row_low = mysqli_fetch_array($run_low);
    $low_stock = $row_low['total'];
}
echo $low_stock; 
?></h4>
<p>Low Stock</p>
</div>
</div>
</div>

<div class="col-md-3">
<div class="panel panel-danger">
<div class="panel-body text-center">
<h4><?php 
$out_stock_sql = "SELECT COUNT(*) as total FROM product_variants WHERE stock_quantity = 0";
$run_out = mysqli_query($con, $out_stock_sql);
$out_stock = 0;
if($run_out) {
    $row_out = mysqli_fetch_array($run_out);
    $out_stock = $row_out['total'];
}
echo $out_stock; 
?></h4>
<p>Out of Stock</p>
</div>
</div>
</div>

<div class="col-md-3">
<div class="panel panel-success">
<div class="panel-body text-center">
<h4><?php 
$in_stock_sql = "SELECT COUNT(*) as total FROM product_variants WHERE stock_quantity > 5";
$run_in = mysqli_query($con, $in_stock_sql);
$in_stock = 0;
if($run_in) {
    $row_in = mysqli_fetch_array($run_in);
    $in_stock = $row_in['total'];
}
echo $in_stock; 
?></h4>
<p>In Stock</p>
</div>
</div>
</div>
</div>

<!-- Stock Details Table -->
<div class="table-responsive">
<table class="table table-bordered table-hover table-striped">
<thead>
<tr>
<th>#</th>
<th>Product</th>
<th>Image</th>
<th>Color Variant</th>
<th>Current Stock</th>
<th>Status</th>
<th>Update Stock</th>
</tr>
</thead>
<tbody>

<?php
$i = 0;
$get_stock = "SELECT pv.*, p.product_title, p.product_img1 
              FROM product_variants pv 
              LEFT JOIN products p ON pv.product_id = p.product_id 
              ORDER BY pv.stock_quantity ASC, p.product_title";
$run_stock = mysqli_query($con, $get_stock);

if($run_stock && mysqli_num_rows($run_stock) > 0) {
    while($row_stock = mysqli_fetch_array($run_stock)){
        $variant_id = $row_stock['variant_id'];
        $product_title = $row_stock['product_title'] ? $row_stock['product_title'] : 'Unknown Product';
        $product_img = $row_stock['product_img1'];
        $color_name = $row_stock['color_name'];
        $color_code = $row_stock['color_code'];
        $stock_qty = $row_stock['stock_quantity'];
        $i++;
        
        // Determine stock status
        if($stock_qty == 0) {
            $status = '<span class="label label-danger">Out of Stock</span>';
        } elseif($stock_qty <= 5) {
            $status = '<span class="label label-warning">Low Stock</span>';
        } else {
            $status = '<span class="label label-success">In Stock</span>';
        }
?>

<tr>
<td><?php echo $i; ?></td>
<td><?php echo $product_title; ?></td>
<td>
    <?php if($product_img): ?>
    <img src="product_images/<?php echo $product_img; ?>" width="50" height="50" style="object-fit: cover;">
    <?php else: ?>
    <div style="width:50px;height:50px;background:#f0f0f0;display:flex;align-items:center;justify-content:center;border:1px solid #ddd;">
        <i class="fa fa-image"></i>
    </div>
    <?php endif; ?>
</td>
<td>
    <div style="display: flex; align-items: center;">
        <div style="width: 20px; height: 20px; background-color: <?php echo $color_code; ?>; border: 1px solid #ccc; border-radius: 3px; margin-right: 8px;"></div>
        <?php echo $color_name; ?>
    </div>
</td>
<td><strong><?php echo $stock_qty; ?></strong></td>
<td><?php echo $status; ?></td>
<td>
    <form method="post" style="display: inline-block;">
        <div class="input-group" style="width: 150px;">
            <input type="number" name="new_stock" class="form-control input-sm" value="<?php echo $stock_qty; ?>" min="0">
            <input type="hidden" name="variant_id" value="<?php echo $variant_id; ?>">
            <span class="input-group-btn">
                <button type="submit" name="update_stock" class="btn btn-sm btn-primary">
                    <i class="fa fa-save"></i>
                </button>
            </span>
        </div>
    </form>
</td>
</tr>

<?php 
    }
} else {
    echo "<tr><td colspan='7' class='text-center'>";
    echo "<p>No product variants found. <a href='index.php?insert_product'>Add products with variants</a> to manage stock.</p>";
    echo "</td></tr>";
}
?>

</tbody>
</table>
</div>

</div>
</div>
</div>
</div>

<?php 
// Handle stock update
if(isset($_POST['update_stock'])){
    $variant_id = (int)$_POST['variant_id'];
    $new_stock = (int)$_POST['new_stock'];
    
    $update_stock = "UPDATE product_variants SET stock_quantity='$new_stock' WHERE variant_id='$variant_id'";
    $run_update = mysqli_query($con, $update_stock);
    
    if($run_update){
        echo "<script>alert('Stock updated successfully')</script>";
        echo "<script>window.open('index.php?view_stock','_self')</script>";
    } else {
        echo "<script>alert('Error updating stock: " . mysqli_error($con) . "')</script>";
    }
}
?>

<style>
.huge {
    font-size: 40px;
}
.panel-yellow {
    border-color: #f0ad4e;
}
.panel-yellow > .panel-heading {
    background-color: #f0ad4e;
    border-color: #f0ad4e;
    color: white;
}
</style>

<?php } ?>
