<?php
if(!isset($_SESSION['admin_email'])){
    echo "<script>window.open('login.php','_self')</script>";
    exit;
}

$edit_id = isset($_GET['edit_size']) ? (int)$_GET['edit_size'] : 0;

if($edit_id <= 0) {
    echo "<script>alert('Invalid size ID')</script>";
    echo "<script>window.open('index.php?view_sizes','_self')</script>";
    exit;
}

// Get size data
$get_size = "SELECT * FROM sizes WHERE size_id = '$edit_id'";
$run_size = mysqli_query($con, $get_size);

if(!$run_size || mysqli_num_rows($run_size) == 0) {
    echo "<script>alert('Size not found')</script>";
    echo "<script>window.open('index.php?view_sizes','_self')</script>";
    exit;
}

$row_size = mysqli_fetch_array($run_size);
$size_name = $row_size['size_name'];
$size_type = $row_size['size_type'];
$size_order = $row_size['size_order'];
$size_desc = isset($row_size['size_desc']) ? $row_size['size_desc'] : '';

if(isset($_POST['update'])){
    $new_size_name = mysqli_real_escape_string($con, trim($_POST['size_name']));
    $new_size_type = mysqli_real_escape_string($con, $_POST['size_type']);
    $new_size_order = (int)$_POST['size_order'];
    $new_size_desc = mysqli_real_escape_string($con, trim($_POST['size_desc']));
    
    if(empty($new_size_name)) {
        echo "<script>alert('Please enter a size name')</script>";
    } else {
        // Check if size already exists (excluding current size)
        $check_size = "SELECT size_id FROM sizes WHERE size_name = '$new_size_name' AND size_type = '$new_size_type' AND size_id != '$edit_id'";
        $run_check = mysqli_query($con, $check_size);
        
        if(mysqli_num_rows($run_check) > 0) {
            echo "<script>alert('This size already exists for the selected type')</script>";
        } else {
            $update_size = "UPDATE sizes SET 
                           size_name = '$new_size_name',
                           size_type = '$new_size_type',
                           size_order = '$new_size_order',
                           size_desc = '$new_size_desc'
                           WHERE size_id = '$edit_id'";
            $run_update = mysqli_query($con, $update_size);
            
            if($run_update) {
                echo "<script>alert('Size has been updated successfully')</script>";
                echo "<script>window.open('index.php?view_sizes','_self')</script>";
            } else {
                echo "<script>alert('Error updating size: " . mysqli_error($con) . "')</script>";
            }
        }
    }
}
?>

<div class="row">
    <div class="col-lg-12">
        <ol class="breadcrumb">
            <li class="active">
                <i class="fa fa-dashboard"></i> Dashboard / Edit Size
            </li>
        </ol>
    </div>
</div>

<div class="row">
    <div class="col-lg-12">
        <div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title">
                    <i class="fa fa-tags fa-fw"></i> Edit Size
                </h3>
            </div>
            <div class="panel-body">
                <form class="form-horizontal" method="post">
                    <div class="form-group">
                        <label class="col-md-3 control-label">Size Name</label>
                        <div class="col-md-6">
                            <input type="text" name="size_name" class="form-control" required 
                                   value="<?php echo htmlspecialchars($size_name); ?>"
                                   placeholder="e.g., XL, 42, Large">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="col-md-3 control-label">Size Type</label>
                        <div class="col-md-6">
                            <select name="size_type" class="form-control" required>
                                <option value="">Select Size Type</option>
                                <option value="clothing" <?php echo $size_type == 'clothing' ? 'selected' : ''; ?>>Clothing</option>
                                <option value="shoes_men" <?php echo $size_type == 'shoes_men' ? 'selected' : ''; ?>>Men's Shoes</option>
                                <option value="shoes_women" <?php echo $size_type == 'shoes_women' ? 'selected' : ''; ?>>Women's Shoes</option>
                                <option value="shoes_kids" <?php echo $size_type == 'shoes_kids' ? 'selected' : ''; ?>>Kids Shoes</option>
                                <option value="custom" <?php echo $size_type == 'custom' ? 'selected' : ''; ?>>Custom</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="col-md-3 control-label">Size Order</label>
                        <div class="col-md-6">
                            <input type="number" name="size_order" class="form-control" 
                                   value="<?php echo $size_order; ?>" min="1" max="100">
                            <p class="help-block">Lower numbers appear first</p>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="col-md-3 control-label">Size Description</label>
                        <div class="col-md-6">
                            <textarea name="size_desc" class="form-control" rows="3" 
                                      placeholder="Optional description for this size"><?php echo htmlspecialchars($size_desc); ?></textarea>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="col-md-3 control-label"></label>
                        <div class="col-md-6">
                            <input type="submit" name="update" value="Update Size" 
                                   class="btn btn-primary form-control">
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
