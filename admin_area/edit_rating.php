<?php

if(!isset($_SESSION['admin_email'])){

echo "<script>window.open('login.php','_self')</script>";

}

else {

?>

<?php

if(isset($_GET['edit_rating'])){

$edit_id = $_GET['edit_rating'];

$get_rating = "select * from ratings where rating_id='$edit_id'";

$run_edit = mysqli_query($con,$get_rating);

$row_edit = mysqli_fetch_array($run_edit);

$rating_id = $row_edit['rating_id'];

$rating_title = $row_edit['rating_title'];

$rating_number = $row_edit['rating_number'];

$rating_desc = $row_edit['rating_desc'];

}

?>


<div class="row"><!-- 1 row Starts -->

<div class="col-lg-12"><!-- col-lg-12 Starts -->

<ol class="breadcrumb"><!-- breadcrumb Starts -->

<li class="active">

<i class="fa fa-dashboard"></i> Dashboard / Edit Rating

</li>

</ol><!-- breadcrumb Ends -->

</div><!-- col-lg-12 Ends -->

</div><!-- 1 row Ends -->


<div class="row"><!-- 2 row Starts -->

<div class="col-lg-12"><!-- col-lg-12 Starts -->

<div class="panel panel-default"><!-- panel panel-default Starts -->

<div class="panel-heading"><!-- panel-heading Starts -->

<h3 class="panel-title"><!-- panel-title Starts -->

<i class="fa fa-star fa-fw"> </i> Edit Rating

</h3><!-- panel-title Ends -->

</div><!-- panel-heading Ends -->

<div class="panel-body"><!-- panel-body Starts -->

<form class="form-horizontal" action="" method="post"><!-- form-horizontal Starts -->

<div class="form-group"><!-- form-group Starts -->

<label class="col-md-3 control-label"> Rating Title </label>

<div class="col-md-6">

<input type="text" name="rating_title" class="form-control" value="<?php echo $rating_title; ?>">

</div>

</div><!-- form-group Ends -->

<div class="form-group"><!-- form-group Starts -->

<label class="col-md-3 control-label"> Rating Number (1-5) </label>

<div class="col-md-6">

<select name="rating_number" class="form-control">
<option value="<?php echo $rating_number; ?>"><?php echo $rating_number; ?> Stars</option>
<option value="5">5 Stars</option>
<option value="4">4 Stars</option>
<option value="3">3 Stars</option>
<option value="2">2 Stars</option>
<option value="1">1 Star</option>
</select>

</div>

</div><!-- form-group Ends -->

<div class="form-group"><!-- form-group Starts -->

<label class="col-md-3 control-label"> Rating Description </label>

<div class="col-md-6">

<textarea name="rating_desc" class="form-control" rows="3"><?php echo $rating_desc; ?></textarea>

</div>

</div><!-- form-group Ends -->

<div class="form-group"><!-- form-group Starts -->

<label class="col-md-3 control-label"> </label>

<div class="col-md-6">

<input type="submit" name="update" class="form-control btn btn-primary" value=" Update Rating " >

</div>

</div><!-- form-group Ends -->

</form><!-- form-horizontal Ends -->

</div><!-- panel-body Ends -->

</div><!-- panel panel-default Ends -->

</div><!-- col-lg-12 Ends -->

</div><!-- 2 row Ends -->

<?php

if(isset($_POST['update'])){

$rating_title = $_POST['rating_title'];

$rating_number = $_POST['rating_number'];

$rating_desc = $_POST['rating_desc'];

$update_rating = "update ratings set rating_title='$rating_title',rating_number='$rating_number',rating_desc='$rating_desc' where rating_id='$rating_id'";

$run_rating = mysqli_query($con,$update_rating);

if($run_rating){

echo "<script>alert('Rating Has Been Updated')</script>";

echo "<script>window.open('index.php?view_ratings','_self')</script>";

}

}

?>

<?php } ?>