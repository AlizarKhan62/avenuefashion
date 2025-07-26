<?php
if(!isset($_SESSION['admin_email'])){
    echo "<script>window.open('login.php','_self')</script>";
} else {

// Check if colors table exists
$check_table = "SHOW TABLES LIKE 'colors'";
$table_exists = mysqli_query($con, $check_table);

if(!$table_exists || mysqli_num_rows($table_exists) == 0) {
    echo "<div class='alert alert-danger'>";
    echo "<h3>Colors table not found!</h3>";
    echo "<p>The colors management system requires database setup.</p>";
    echo "<a href='quick_setup.php' class='btn btn-primary'>Run Database Setup</a>";
    echo "</div>";
    return;
}
?>

<div class="row">
<div class="col-lg-12">
<ol class="breadcrumb">
<li class="active">
<i class="fa fa-dashboard"></i> Dashboard / View Colors
</li>
</ol>
</div>
</div>

<div class="row">
<div class="col-lg-12">
<div class="panel panel-default">
<div class="panel-heading">
<h3 class="panel-title">
<i class="fa fa-paint-brush fa-fw"></i> View Colors
<a href="index.php?insert_colors" class="btn btn-primary btn-sm pull-right">
<i class="fa fa-plus"></i> Add New Color
</a>
</h3>
</div>
<div class="panel-body">

<div class="table-responsive">
<table class="table table-bordered table-hover table-striped">
<thead>
<tr>
<th>#</th>
<th>Color Name</th>
<th>Color Preview</th>
<th>Color Code</th>
<th>Description</th>
<th>Products Using</th>
<th>Created Date</th>
<th>Actions</th>
</tr>
</thead>
<tbody>

<?php
$i = 0;
$get_colors = "SELECT * FROM colors ORDER BY color_name";
$run_colors = mysqli_query($con, $get_colors);

if($run_colors && mysqli_num_rows($run_colors) > 0) {
    while($row_color = mysqli_fetch_array($run_colors)){
        $color_id = $row_color['color_id'];
        $color_name = $row_color['color_name'];
        $color_code = $row_color['color_code'];
        $color_desc = $row_color['color_desc'];
        $created_date = $row_color['created_date'];
        $i++;
        
        // Count products using this color name (with error checking)
        $count_products_sql = "SELECT COUNT(*) as total FROM product_variants WHERE LOWER(color_name) = LOWER('$color_name')";
        $run_count = mysqli_query($con, $count_products_sql);
        $products_count = 0;
        if($run_count) {
            $row_count = mysqli_fetch_array($run_count);
            $products_count = $row_count['total'];
        }
?>

<tr>
<td><?php echo $i; ?></td>
<td><strong><?php echo $color_name; ?></strong></td>
<td>
    <div style="width: 40px; height: 40px; background-color: <?php echo $color_code; ?>; border: 1px solid #ccc; border-radius: 4px; display: inline-block;"></div>
</td>
<td><code><?php echo $color_code; ?></code></td>
<td><?php echo $color_desc ? $color_desc : '<em>No description</em>'; ?></td>
<td>
    <?php if($products_count > 0): ?>
        <span class="badge" style="background-color: #337ab7;"><?php echo $products_count; ?> products</span>
    <?php else: ?>
        <span class="text-muted">No products</span>
    <?php endif; ?>
</td>
<td><?php echo date('M j, Y', strtotime($created_date)); ?></td>
<td>
    <div class="btn-group">
        <a href="index.php?edit_color=<?php echo $color_id; ?>" class="btn btn-sm btn-primary" title="Edit Color">
            <i class="fa fa-pencil"></i>
        </a>
        <?php if($products_count == 0): ?>
        <a href="index.php?delete_color=<?php echo $color_id; ?>" 
           onclick="return confirm('Are you sure you want to delete this color?')" 
           class="btn btn-sm btn-danger" title="Delete Color">
            <i class="fa fa-trash-o"></i>
        </a>
        <?php else: ?>
        <button class="btn btn-sm btn-danger" disabled title="Cannot delete - color is in use">
            <i class="fa fa-lock"></i>
        </button>
        <?php endif; ?>
    </div>
</td>
</tr>

<?php 
    }
} else {
    echo "<tr><td colspan='8' class='text-center'>";
    echo "<p>No colors found. <a href='index.php?insert_colors'>Add your first color</a></p>";
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

<style>
.badge {
    background-color: #337ab7;
    color: white;
    padding: 3px 8px;
    border-radius: 12px;
    font-size: 11px;
}
</style>

<?php } ?>
