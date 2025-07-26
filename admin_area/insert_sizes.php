<?php
if(!isset($_SESSION['admin_email'])){
    echo "<script>window.open('login.php','_self')</script>";
    exit;
}

if(isset($_POST['submit'])){
    $size_name = mysqli_real_escape_string($con, trim($_POST['size_name']));
    $size_type = mysqli_real_escape_string($con, $_POST['size_type']);
    $size_order = (int)$_POST['size_order'];
    $size_desc = mysqli_real_escape_string($con, trim($_POST['size_desc']));
    
    if(empty($size_name)) {
        echo "<script>alert('Please enter a size name')</script>";
    } else {
        // Check if size already exists
        $check_size = "SELECT size_id FROM sizes WHERE size_name = '$size_name' AND size_type = '$size_type'";
        $run_check = mysqli_query($con, $check_size);
        
        if(!$run_check) {
            echo "<script>alert('Database error: " . mysqli_error($con) . "')</script>";
        } else if(mysqli_num_rows($run_check) > 0) {
            echo "<script>alert('This size already exists for the selected type')</script>";
        } else {
            $insert_size = "INSERT INTO sizes (size_name, size_type, size_order, size_desc) 
                           VALUES ('$size_name', '$size_type', '$size_order', '$size_desc')";
            $run_size = mysqli_query($con, $insert_size);
            
            if($run_size) {
                echo "<script>alert('Size has been inserted successfully')</script>";
                echo "<script>window.open('index.php?view_sizes','_self')</script>";
            } else {
                echo "<script>alert('Error inserting size: " . mysqli_error($con) . "')</script>";
            }
        }
    }
}
?>

<div class="row">
    <div class="col-lg-12">
        <ol class="breadcrumb">
            <li class="active">
                <i class="fa fa-dashboard"></i> Dashboard / Insert Size
            </li>
        </ol>
    </div>
</div>

<div class="row">
    <div class="col-lg-12">
        <div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title">
                    <i class="fa fa-tags fa-fw"></i> Insert Size
                </h3>
            </div>
            <div class="panel-body">
                <form class="form-horizontal" method="post">
                    <div class="form-group">
                        <label class="col-md-3 control-label">Size Name</label>
                        <div class="col-md-6">
                            <input type="text" name="size_name" class="form-control" required 
                                   placeholder="e.g., XL, 42, Large">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="col-md-3 control-label">Size Type</label>
                        <div class="col-md-6">
                            <select name="size_type" class="form-control" required>
                                <option value="">Select Size Type</option>
                                <option value="clothing">Clothing</option>
                                <option value="shoes_men">Men's Shoes</option>
                                <option value="shoes_women">Women's Shoes</option>
                                <option value="shoes_kids">Kids Shoes</option>
                                <option value="custom">Custom</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="col-md-3 control-label">Size Order</label>
                        <div class="col-md-6">
                            <input type="number" name="size_order" class="form-control" 
                                   value="1" min="1" max="100">
                            <p class="help-block">Lower numbers appear first</p>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="col-md-3 control-label">Size Description</label>
                        <div class="col-md-6">
                            <textarea name="size_desc" class="form-control" rows="3" 
                                      placeholder="Optional description for this size"></textarea>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="col-md-3 control-label"></label>
                        <div class="col-md-6">
                            <input type="submit" name="submit" value="Insert Size" 
                                   class="btn btn-primary form-control">
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
