<?php

if(!isset($_SESSION['admin_email'])){

echo "<script>window.open('login.php','_self')</script>";

}

else {

?>


<div class="row"><!--  1 row Starts -->

<div class="col-lg-12" ><!-- col-lg-12 Starts -->

<ol class="breadcrumb" ><!-- breadcrumb Starts -->

<li class="active" >

<i class="fa fa-dashboard"></i> Dashboard / View Ratings

</li>

</ol><!-- breadcrumb Ends -->

</div><!-- col-lg-12 Ends -->

</div><!--  1 row Ends -->

<div class="row" ><!-- 2 row Starts -->

<div class="col-lg-12" ><!-- col-lg-12 Starts -->

<div class="panel panel-default" ><!-- panel panel-default Starts -->

<div class="panel-heading" ><!-- panel-heading Starts -->

<h3 class="panel-title" ><!-- panel-title Starts -->

<i class="fa fa-star fa-fw" ></i> View Ratings

</h3><!-- panel-title Ends -->


</div><!-- panel-heading Ends -->

<div class="panel-body" ><!-- panel-body Starts -->

<div class="table-responsive" ><!-- table-responsive Starts -->

<table class="table table-bordered table-hover table-striped" ><!-- table table-bordered table-hover table-striped Starts -->

<thead>

<tr>
<th>Rating ID</th>
<th>Rating Title</th>
<th>Stars</th>
<th>Description</th>
<th>Delete</th>
<th>Edit</th>

</tr>

</thead>

<tbody>

<?php

$i = 0;

$get_rating = "select * from ratings";

$run_rating = mysqli_query($con,$get_rating);

while($row_rating=mysqli_fetch_array($run_rating)){

$rating_id = $row_rating['rating_id'];

$rating_title = $row_rating['rating_title'];

$rating_number = $row_rating['rating_number'];

$rating_desc = $row_rating['rating_desc'];

$i++;

?>

<tr>

<td><?php echo $rating_id; ?></td>

<td><?php echo $rating_title; ?></td>

<td>
<?php
// Display stars
for($star = 1; $star <= 5; $star++) {
    if($star <= $rating_number) {
        echo '<i class="fa fa-star" style="color: #FFD700;"></i>';
    } else {
        echo '<i class="fa fa-star-o" style="color: #ccc;"></i>';
    }
}
?>
</td>

<td><?php echo $rating_desc; ?></td>

<td>

<a href="index.php?delete_rating=<?php echo $rating_id; ?>">

<i class="fa fa-trash-o"> </i> Delete

</a>

</td>

<td>

<a href="index.php?edit_rating=<?php echo $rating_id; ?>">

<i class="fa fa-pencil"> </i> Edit

</a>

</td>

</tr>

<?php } ?>


</tbody>


</table><!-- table table-bordered table-hover table-striped Ends -->

</div><!-- table-responsive Ends -->

</div><!-- panel-body Ends -->

</div><!-- panel panel-default Ends -->

</div><!-- col-lg-12 Ends -->

</div><!-- 2 row Ends -->

<?php } ?>