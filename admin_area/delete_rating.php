<?php

if(!isset($_SESSION['admin_email'])){

echo "<script>window.open('login.php','_self')</script>";

}

else {

?>

<?php

if(isset($_GET['delete_rating'])){

$delete_id = $_GET['delete_rating'];

$delete_rating = "delete from ratings where rating_id='$delete_id'";

$run_delete = mysqli_query($con,$delete_rating);

if($run_delete){

echo "<script>alert('One Rating Has been deleted')</script>";

echo "<script>window.open('index.php?view_ratings','_self')</script>";

}

}

?>

<?php } ?>