<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
// include("includes/db.php");
// include("includes/header.php");
// include("functions/functions.php");
// include("includes/main.php");

// Check if user is logged in
if(!isset($_SESSION['customer_email'])) {
    echo "<script>window.open('checkout.php','_self')</script>";
    exit();
}
?>
<div id="content">
    <div class="container">
        <div class="col-md-12">
            <div class="box"><!-- box Starts -->

<?php

$session_email = $_SESSION['customer_email'];

$select_customer = "select * from customers where customer_email='$session_email'";

$run_customer = mysqli_query($con,$select_customer);

$row_customer = mysqli_fetch_array($run_customer);

$customer_id = $row_customer['customer_id'];

?>

<h1 class="text-center" style="margin-bottom: 30px;">Payment Options For You</h1>

<style>
.payment-card {
    transition: transform 0.2s ease-in-out;
    cursor: pointer;
}
.payment-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}
.panel-primary .panel-heading {
    background-color: #ff6b35 !important;
    border-color: #ff6b35 !important;
}
.panel-primary {
    border-color: #ff6b35 !important;
}
</style>

<div class="row">
    <div class="col-md-8 col-md-offset-2">

<?php
// Calculate total amount for display
$total_amount = 0;
$ip_add = getRealUserIp();
$get_cart = "select * from cart where ip_add='$ip_add'";
$run_cart = mysqli_query($con,$get_cart);

while($row_cart = mysqli_fetch_array($run_cart)){
    $pro_price = $row_cart['p_price'];
    $pro_qty = $row_cart['qty'];
    $sub_total = $pro_price * $pro_qty;
    $total_amount += $sub_total;
}

// Add shipping cost
$shipping_cost = 0;
if($total_amount > 0 && $total_amount < 50000) {
    $shipping_cost = 250;
}
$final_total = $total_amount + $shipping_cost;
?>

<div class="row">
    <div class="col-md-6">
        <div class="panel panel-primary payment-card">
            <div class="panel-heading">
                <h3 class="panel-title">
                    <i class="fa fa-credit-card"></i> Online Payment (Recommended)
                </h3>
            </div>
            <div class="panel-body text-center">
                <h4>Total Amount: <strong>Rs <?php echo number_format($final_total, 2); ?></strong></h4>
                <p class="text-muted">Secure payment with Stripe</p>
                <form action="paypal_order.php" method="post">
                    <button type="submit" class="btn btn-primary btn-lg" style="background-color: #635BFF; border-color: #635BFF; padding: 15px 30px; width: 100%;">
                        <i class="fa fa-credit-card"></i> Pay with Stripe
                    </button>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-md-6">
        <div class="panel panel-default payment-card">
            <div class="panel-heading">
                <h3 class="panel-title">
                    <i class="fa fa-money"></i> Cash on Delivery
                </h3>
            </div>
            <div class="panel-body text-center">
                <h4>Total Amount: <strong>Rs <?php echo number_format($final_total, 2); ?></strong></h4>
                <p class="text-muted">Pay when your order arrives</p>
                <a href="order.php?c_id=<?php echo $customer_id; ?>" class="btn btn-default btn-lg" style="padding: 15px 30px; width: 100%;">
                    <i class="fa fa-money"></i> Cash on Delivery
                </a>
            </div>
        </div>
    </div>
</div>

<script src="https://js.stripe.com/v3/"></script>

    </div><!-- col-md-8 col-md-offset-2 Ends -->
</div><!-- row Ends -->

</div><!-- box Ends -->

        </div><!-- col-md-12 Ends -->
    </div><!-- container Ends -->
</div><!-- content Ends -->

<?php //include("includes/footer.php"); ?>
