<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include("includes/db.php");

if(!isset($_SESSION['customer_email'])){
    // Store the intended destination in session
    $_SESSION['redirect_after_login'] = '../customer/my_account.php?my_wishlist';
    echo "<script>window.open('../customer_register.php','_self')</script>";
    exit();
}

// Get customer_id using prepared statement
$customer_email = $_SESSION['customer_email'];
$stmt = mysqli_prepare($con, "SELECT customer_id FROM customers WHERE customer_email = ?");
mysqli_stmt_bind_param($stmt, "s", $customer_email);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$row_customer = mysqli_fetch_array($result);

if(!$row_customer){
    echo "Customer not found";
    exit();
}

$customer_email = $_SESSION['customer_email'];
$i = 0;

// Get wishlist items using customer email (matches database structure)
$stmt = mysqli_prepare($con, "SELECT w.*, p.product_title, p.product_url, p.product_img1 
                            FROM wishlist w 
                            INNER JOIN products p ON w.product_id = p.product_id 
                            WHERE w.customer_email = ?");

if($stmt) {
    mysqli_stmt_bind_param($stmt, "s", $customer_email);
    mysqli_stmt_execute($stmt);
    $run_wishlist = mysqli_stmt_get_result($stmt);
    
    if(!$run_wishlist){
        echo "Error fetching wishlist: " . mysqli_error($con);
        exit();
    }
} else {
    echo "Error preparing statement: " . mysqli_error($con);
    exit();
}
?>

<center><!-- center Starts -->

<h1> My Wishlist </h1>

<p class="lead"> Your all Wishlist Products on one place. </p>

</center><!-- center Ends -->

<hr>

<div class="table-responsive"><!-- table-responsive Starts -->

<table class="table table-bordered table-hover"><!-- table table-bordered table-hover Starts -->

<thead>

<tr>

<th> Wishlist No: </th>

<th> Wishlist Product </th>

<th> Delete Wishlist </th>

</tr>

</thead>

<tbody>

<?php

if(mysqli_num_rows($run_wishlist) == 0){
    echo "<tr><td colspan='3' class='text-center'>Your wishlist is empty</td></tr>";
}

while($row_wishlist = mysqli_fetch_array($run_wishlist)){

$wishlist_id = $row_wishlist['wishlist_id'];

$product_title = htmlspecialchars($row_wishlist['product_title']);
$product_url = htmlspecialchars($row_wishlist['product_url']);
$product_img1 = htmlspecialchars($row_wishlist['product_img1']);

$i++;

?>

<tr>

<td width="100"> <?php echo $i; ?> </td>

<td>

<img src="../admin_area/product_images/<?php echo $product_img1; ?>" width="60" height="60">

&nbsp;&nbsp;&nbsp; 

<a href="../<?php echo $product_url; ?>">

<?php echo $product_title; ?>

</a>

</td>

<td>

<a href="my_account.php?delete_wishlist=<?php echo $wishlist_id; ?>" class="btn btn-primary">

<i class="fa fa-trash-o"> </i> Delete

</a>

</td>

</tr>

<?php } ?>

</tbody>

</table><!-- table table-bordered table-hover Ends -->

</div><!-- table-responsive Ends -->