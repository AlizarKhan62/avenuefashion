<?php
if(!isset($_SESSION['admin_email'])){
    echo "<script>window.open('login.php','_self')</script>";
} else {

if(isset($_POST['update'])){
    $color_id = $_GET['edit_color'];
    $color_name = mysqli_real_escape_string($con, trim($_POST['color_name']));
    $color_code = !empty($_POST['color_code']) ? mysqli_real_escape_string($con, $_POST['color_code']) : '#000000';
    $color_desc = mysqli_real_escape_string($con, $_POST['color_desc']);
    
    if(empty($color_name)) {
        echo "<script>alert('Please enter a color name')</script>";
        return;
    }
    
    // Check if another color with this name exists (excluding current color)
    $check_color = "SELECT * FROM colors WHERE LOWER(color_name) = LOWER('$color_name') AND color_id != '$color_id'";
    $run_check = mysqli_query($con, $check_color);
    
    if(mysqli_num_rows($run_check) > 0) {
        echo "<script>alert('This color name already exists. Please use a different name.')</script>";
        return;
    }
    
    $update_color = "UPDATE colors SET color_name='$color_name', color_code='$color_code', color_desc='$color_desc' WHERE color_id='$color_id'";
    $run_update = mysqli_query($con, $update_color);
    
    if($run_update){
        echo "<script>alert('Color has been updated successfully')</script>";
        echo "<script>window.open('index.php?view_colors','_self')</script>";
    }
}

if(isset($_GET['edit_color'])){
    $edit_id = $_GET['edit_color'];
    $get_color = "SELECT * FROM colors WHERE color_id='$edit_id'";
    $run_edit = mysqli_query($con, $get_color);
    $row_edit = mysqli_fetch_array($run_edit);
    
    $color_name = $row_edit['color_name'];
    $color_code = $row_edit['color_code'];
    $color_desc = $row_edit['color_desc'];
?>

<div class="row">
<div class="col-lg-12">
<ol class="breadcrumb">
<li class="active">
<i class="fa fa-dashboard"></i> Dashboard / Edit Color
</li>
</ol>
</div>
</div>

<div class="row">
<div class="col-lg-12">
<div class="panel panel-default">
<div class="panel-heading">
<h3 class="panel-title">
<i class="fa fa-paint-brush fa-fw"></i> Edit Color
</h3>
</div>
<div class="panel-body">

<form class="form-horizontal" method="post">

<div class="form-group">
<label class="col-md-3 control-label">Color Name</label>
<div class="col-md-6">
<input type="text" name="color_name" class="form-control" required value="<?php echo $color_name; ?>"
       placeholder="e.g., Royal Blue, Green And White Stripes">
<p class="help-block">Enter descriptive color name or pattern description</p>
</div>
</div>

<div class="form-group">
<label class="col-md-3 control-label">Color Code (Optional)</label>
<div class="col-md-6">
<input type="color" name="color_code" class="form-control" value="<?php echo $color_code; ?>" style="height: 50px;">
<p class="help-block">Optional: Select an approximate color for display purposes</p>
</div>
</div>

<div class="form-group">
<label class="col-md-3 control-label">Color Description</label>
<div class="col-md-6">
<textarea name="color_desc" class="form-control" rows="4"><?php echo $color_desc; ?></textarea>
</div>
</div>

<div class="form-group">
<label class="col-md-3 control-label"></label>
<div class="col-md-6">
<input type="submit" name="update" value="Update Color" class="btn btn-primary form-control">
</div>
</div>

</form>

</div>
</div>
</div>
</div>

<?php 
}
} 
?>
