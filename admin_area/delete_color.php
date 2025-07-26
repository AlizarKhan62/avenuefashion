<?php
if(!isset($_SESSION['admin_email'])){
    echo "<script>window.open('login.php','_self')</script>";
} else {

if(isset($_GET['delete_color'])){
    $delete_id = $_GET['delete_color'];
    
    // Check if color is being used in any product variants
    $check_usage = "SELECT COUNT(*) as total FROM product_variants pv 
                    JOIN colors c ON pv.color_code = c.color_code 
                    WHERE c.color_id='$delete_id'";
    $run_check = mysqli_query($con, $check_usage);
    $row_check = mysqli_fetch_array($run_check);
    
    if($row_check['total'] > 0){
        echo "<script>alert('Cannot delete this color as it is being used in " . $row_check['total'] . " product variants')</script>";
        echo "<script>window.open('index.php?view_colors','_self')</script>";
    } else {
        $delete_color = "DELETE FROM colors WHERE color_id='$delete_id'";
        $run_delete = mysqli_query($con, $delete_color);
        
        if($run_delete){
            echo "<script>alert('Color has been deleted successfully')</script>";
            echo "<script>window.open('index.php?view_colors','_self')</script>";
        }
    }
}

}
?>
