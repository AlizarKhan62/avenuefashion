<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include("includes/db.php");

if(!isset($_SESSION['admin_email'])){
    echo "<script>window.open('login.php','_self')</script>";
    exit;
}

if(isset($_POST['submit'])){
    $color_name = mysqli_real_escape_string($con, trim($_POST['color_name']));
    $color_desc = mysqli_real_escape_string($con, $_POST['color_desc']);
    // Set a default color code if none provided
    $color_code = !empty($_POST['color_code']) ? mysqli_real_escape_string($con, $_POST['color_code']) : '#000000';
    
    if(empty($color_name)) {
        echo "<script>alert('Please enter a color name')</script>";
    } else {
        // Check if color name already exists (case insensitive)
        $check_color = "SELECT * FROM colors WHERE LOWER(color_name) = LOWER('$color_name')";
        $run_check = mysqli_query($con, $check_color);
        
        if(mysqli_num_rows($run_check) > 0) {
            echo "<script>alert('This color name already exists. Please use a different name.')</script>";
        } else {
            $insert_color = "INSERT INTO colors (color_name, color_code, color_desc) VALUES ('$color_name', '$color_code', '$color_desc')";
            $run_color = mysqli_query($con, $insert_color);
            
            if($run_color){
                echo "<script>alert('Color \"" . $color_name . "\" has been inserted successfully')</script>";
                echo "<script>window.open('index.php?view_colors','_self')</script>";
            } else {
                echo "<script>alert('Error inserting color: " . mysqli_error($con) . "')</script>";
            }
        }
    }
}
?>

<div class="row">
<div class="col-lg-12">
<ol class="breadcrumb">
<li class="active">
<i class="fa fa-dashboard"></i> Dashboard / Insert Colors
</li>
</ol>
</div>
</div>

<div class="row">
<div class="col-lg-12">
<div class="panel panel-default">
<div class="panel-heading">
<h3 class="panel-title">
<i class="fa fa-paint-brush fa-fw"></i> Insert Colors
</h3>
</div>
<div class="panel-body">

<form class="form-horizontal" method="post">

<div class="form-group">
<label class="col-md-3 control-label">Color Name</label>
<div class="col-md-6">
<input type="text" name="color_name" class="form-control" required 
       placeholder="e.g., Royal Blue, Green And White Stripes">
<p class="help-block">Enter descriptive color name or pattern description</p>
</div>
</div>

<div class="form-group">
<label class="col-md-3 control-label">Color Code (Optional)</label>
<div class="col-md-6">
<input type="color" name="color_code" class="form-control" style="height: 50px;">
<p class="help-block">Optional: Select an approximate color for display purposes</p>
</div>
</div>

<div class="form-group">
<label class="col-md-3 control-label">Color Description</label>
<div class="col-md-6">
<textarea name="color_desc" class="form-control" rows="4" placeholder="Optional description for this color"></textarea>
</div>
</div>

<div class="form-group">
<label class="col-md-3 control-label"></label>
<div class="col-md-6">
<input type="submit" name="submit" value="Insert Color" class="btn btn-primary form-control">
</div>
</div>

</form>

</div>
</div>
</div>
</div>
