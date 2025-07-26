
<?php
session_start();
include("includes/db.php");

if(!isset($_SESSION['customer_email'])){
    echo "<script>window.open('../customer_register.php','_self')</script>";
    exit();
}

if(isset($_GET['delete_wishlist'])){
    $delete_id = (int)$_GET['delete_wishlist'];
    $customer_email = $_SESSION['customer_email'];

    // Delete wishlist item securely using customer email
    $delete_wishlist = "DELETE FROM wishlist WHERE wishlist_id = ? AND customer_email = ?";
    $stmt = mysqli_prepare($con, $delete_wishlist);
    
    if($stmt) {
        mysqli_stmt_bind_param($stmt, "is", $delete_id, $customer_email);
        $run_delete = mysqli_stmt_execute($stmt);

        if($run_delete && mysqli_stmt_affected_rows($stmt) > 0){
            echo "<script>alert('Product Has Been Removed From Wishlist')</script>";
        } else {
            echo "<script>alert('Error: Could not remove product from wishlist')</script>";
        }
        mysqli_stmt_close($stmt);
    } else {
        echo "<script>alert('Error preparing delete statement: " . mysqli_error($con) . "')</script>";
    }

    echo "<script>window.open('my_account.php?my_wishlist','_self')</script>";
}
?>