<?php

session_start();

if(!isset($_SESSION['customer_email'])){

echo "<script>window.open('../checkout.php','_self')</script>";


}else {

include("includes/db.php");
include("../includes/header.php");
include("functions/functions.php");
include("includes/main.php");


?>
  <main>
    <!-- HERO -->
    <div class="nero">
      <div class="nero__heading">
        <span class="nero__bold">My </span>Account
      </div>
      <p class="nero__text">
      </p>
    </div>
  </main>

<div id="content" ><!-- content Starts -->
<div class="container" ><!-- container Starts -->



<div class="col-md-12"><!-- col-md-12 Starts -->

<?php

$c_email = $_SESSION['customer_email'];

$get_customer = "select * from customers where customer_email='$c_email'";

$run_customer = mysqli_query($con,$get_customer);

$row_customer = mysqli_fetch_array($run_customer);

$customer_confirm_code = $row_customer['customer_confirm_code'];

$c_name = $row_customer['customer_name'];

if(!empty($customer_confirm_code)){

?>

<div class="alert alert-warning"><!-- alert alert-warning Starts -->

<strong> Warning! </strong> Please Confirm Your Email. 

<div style="margin-top: 10px;">
    <a href="my_account.php?send_email" class="btn btn-sm btn-primary">
        <i class="fa fa-envelope"></i> Send Email Again
    </a>
    
    <!-- Demo bypass button -->
    <a href="my_account.php?<?php echo $customer_confirm_code; ?>" class="btn btn-sm btn-success" 
       onclick="return confirm('This will confirm your email for demo purposes. Continue?')">
        <i class="fa fa-check"></i> Confirm Email (Demo)
    </a>
</div>

<small style="display: block; margin-top: 10px; color: #666;">
    <strong>For Demo:</strong> If email is not working, use the "Confirm Email (Demo)" button above.
</small>

</div><!-- alert alert-warning Ends -->

<?php } ?>

</div><!-- col-md-12 Ends -->

<div class="col-md-3"><!-- col-md-3 Starts -->

<?php include("includes/sidebar.php"); ?>

</div><!-- col-md-3 Ends -->

<div class="col-md-9" ><!--- col-md-9 Starts -->

<div class="box" ><!-- box Starts -->

<?php

if(isset($_GET[$customer_confirm_code])){

$update_customer = "update customers set customer_confirm_code='' where customer_confirm_code='$customer_confirm_code'";

$run_confirm = mysqli_query($con,$update_customer);

echo "<script>alert('Your Email Has Been Confirmed')</script>";

echo "<script>window.open('my_account.php?my_orders','_self')</script>";

}

if(isset($_GET['send_email'])){

// Include email configuration
include('../includes/email_config.php');

// Send confirmation email using the helper function
$email_sent = sendConfirmationEmail($c_email, $c_name, $customer_confirm_code);

if($email_sent) {
    echo "<script>alert('Confirmation email has been sent to $c_email. Please check your inbox and spam folder.')</script>";
} else {
    echo "<script>alert('Sorry, there was an issue sending the email. This might be because email service is not configured on this server. For demo purposes, you can manually confirm your account by contacting support.')</script>";
}

echo "<script>window.open('my_account.php?my_orders','_self')</script>";

}



if(isset($_GET['my_orders'])){

include("my_orders.php");

}

// if(isset($_GET['pay_offline'])) {

// include("pay_offline.php");

// }

if(isset($_GET['edit_account'])) {

include("edit_account.php");

}

if(isset($_GET['change_pass'])){

include("change_pass.php");

}

if(isset($_GET['delete_account'])){

include("delete_account.php");

}

if(isset($_GET['my_wishlist'])){

include("my_wishlist.php");

}

if(isset($_GET['delete_wishlist'])){

include("delete_wishlist.php");

}

?>

</div><!-- box Ends -->


</div><!--- col-md-9 Ends -->

</div><!-- container Ends -->
</div><!-- content Ends -->



<?php

include("includes/footer.php");

?>

<script src="js/jquery.min.js"> </script>

<script src="js/bootstrap.min.js"></script>

</body>
</html>
<?php } ?>
