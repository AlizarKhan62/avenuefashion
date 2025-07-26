<?php


if(!isset($_SESSION['admin_email'])){

echo "<script>window.open('login.php','_self')</script>";

}

else {


?>


<div class="row"><!-- 1 row Starts -->

<div class="col-lg-12"><!-- col-lg-12 Starts -->

<ol class="breadcrumb"><!-- breadcrumb Starts -->

<li class="active">

<i class="fa fa-dashboard"></i> Dashboard / View Payments

</li>

</ol><!-- breadcrumb Ends -->

</div><!-- col-lg-12 Ends -->

</div><!-- 1 row Ends -->


<div class="row"><!-- 2 row Starts -->

<div class="col-lg-12"><!-- col-lg-12 Starts -->

<div class="panel panel-default"><!-- panel panel-default Starts -->

<div class="panel-heading"><!-- panel-heading Starts -->

<h3 class="panel-title"><!-- panel-title Starts -->

<i class="fa fa-money fa-fw"> </i> View Payments

</h3><!-- panel-title Ends -->

</div><!-- panel-heading Ends -->

<div class="panel-body"><!-- panel-body Starts -->

<?php
// Calculate payment summary using same logic as dashboard
// Payment earnings (from payments table)
$get_payment_earnings = "SELECT SUM(amount) as PaymentTotal FROM payments";
$run_payment_earnings = mysqli_query($con, $get_payment_earnings);
if($run_payment_earnings) {
    $row_payment = mysqli_fetch_assoc($run_payment_earnings);
    $payment_earnings = $row_payment['PaymentTotal'] ? $row_payment['PaymentTotal'] : 0;
} else {
    $payment_earnings = 0;
}

// COD earnings (only delivered orders from customer_orders)
$get_cod_earnings = "SELECT SUM(due_amount) as CODTotal FROM customer_orders WHERE order_status = 'delivered'";
$run_cod_earnings = mysqli_query($con, $get_cod_earnings);
if($run_cod_earnings) {
    $row_cod = mysqli_fetch_assoc($run_cod_earnings);
    $cod_earnings = $row_cod['CODTotal'] ? $row_cod['CODTotal'] : 0;
} else {
    $cod_earnings = 0;
}

// Total earnings = Online payments + COD delivered orders (matches dashboard)
$total_earnings = $payment_earnings + $cod_earnings;

// Detailed payment breakdowns
$get_stripe_payments = "SELECT SUM(amount) as total FROM payments WHERE payment_mode='Stripe'";
$run_stripe = mysqli_query($con,$get_stripe_payments);
$row_stripe = mysqli_fetch_array($run_stripe);
$stripe_total = $row_stripe['total'] ? $row_stripe['total'] : 0;

$get_other_payments = "SELECT SUM(amount) as total FROM payments WHERE payment_mode!='Stripe'";
$run_other = mysqli_query($con,$get_other_payments);
$row_other = mysqli_fetch_array($run_other);
$other_total = $row_other['total'] ? $row_other['total'] : 0;

$get_payment_count = "SELECT COUNT(*) as count FROM payments";
$run_count = mysqli_query($con,$get_payment_count);
$row_count = mysqli_fetch_array($run_count);
$payment_count = $row_count['count'];

$get_cod_count = "SELECT COUNT(*) as count FROM customer_orders WHERE order_status = 'delivered'";
$run_cod_count = mysqli_query($con,$get_cod_count);
$row_cod_count = mysqli_fetch_array($run_cod_count);
$cod_count = $row_cod_count['count'];
?>

<div class="row" style="margin-bottom: 20px;">
    <div class="col-md-3">
        <div class="panel panel-primary">
            <div class="panel-body text-center">
                <h4>Total Earnings</h4>
                <h3><strong>Rs <?php echo number_format($total_earnings, 2); ?></strong></h3>
                <p><?php echo ($payment_count + $cod_count); ?> transactions</p>
                <small><em>Matches Dashboard</em></small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="panel panel-success">
            <div class="panel-body text-center">
                <h4>Online Payments</h4>
                <h3><strong>Rs <?php echo number_format($payment_earnings, 2); ?></strong></h3>
                <p><?php echo $payment_count; ?> payments</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="panel panel-info">
            <div class="panel-body text-center">
                <h4>COD Delivered</h4>
                <h3><strong>Rs <?php echo number_format($cod_earnings, 2); ?></strong></h3>
                <p><?php echo $cod_count; ?> orders</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="panel panel-warning">
            <div class="panel-body text-center">
                <h4>Stripe Payments</h4>
                <h3><strong>Rs <?php echo number_format($stripe_total, 2); ?></strong></h3>
                <p>Credit/Debit cards</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="panel panel-warning">
            <div class="panel-body text-center">
                <h4>Average Transaction</h4>
                <h3><strong>Rs <?php echo ($payment_count + $cod_count) > 0 ? number_format($total_earnings / ($payment_count + $cod_count), 2) : '0.00'; ?></strong></h3>
                <p>Per transaction</p>
            </div>
        </div>
    </div>
</div>

<!-- Add section for COD Orders that make up the earnings -->
<div class="row" style="margin-bottom: 20px;">
    <div class="col-md-12">
        <div class="panel panel-info">
            <div class="panel-heading">
                <h4><i class="fa fa-truck"></i> COD Delivered Orders (Main Earnings Source)</h4>
            </div>
            <div class="panel-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Invoice No</th>
                                <th>Customer</th>
                                <th>Amount</th>
                                <th>Order Date</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $get_cod_orders = "SELECT co.*, c.customer_name FROM customer_orders co 
                                             LEFT JOIN customers c ON co.customer_id = c.customer_id 
                                             WHERE co.order_status = 'delivered' 
                                             ORDER BY co.order_date DESC";
                            $run_cod_orders = mysqli_query($con, $get_cod_orders);
                            $i = 1;
                            
                            if(mysqli_num_rows($run_cod_orders) > 0):
                                while($row_cod = mysqli_fetch_array($run_cod_orders)):
                            ?>
                            <tr>
                                <td><?php echo $i++; ?></td>
                                <td><strong><?php echo $row_cod['invoice_no']; ?></strong></td>
                                <td><?php echo $row_cod['customer_name'] ? $row_cod['customer_name'] : 'N/A'; ?></td>
                                <td><strong>Rs <?php echo number_format($row_cod['due_amount'], 2); ?></strong></td>
                                <td><?php echo date('M d, Y', strtotime($row_cod['order_date'])); ?></td>
                                <td><span class="label label-success">Delivered</span></td>
                                <td>
                                    <a href="?view_order=<?php echo $row_cod['order_id']; ?>" class="btn btn-primary btn-xs">
                                        <i class="fa fa-eye"></i> View
                                    </a>
                                </td>
                            </tr>
                            <?php 
                                endwhile;
                            else:
                            ?>
                            <tr>
                                <td colspan="7" class="text-center text-muted">No delivered COD orders found</td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Online Payments Section -->
<div class="row">
    <div class="col-md-12">
        <div class="panel panel-primary">
            <div class="panel-heading">
                <h4><i class="fa fa-credit-card"></i> Online Payment Transactions</h4>
                <small>Stripe, PayPal, and other online payment methods</small>
            </div>
            <div class="panel-body">

<div class="table-responsive"><!-- table-responsive Starts -->

<table class="table table-hover table-bordered table-striped"><!-- table table-hover table-bordered table-striped Starts -->

<thead><!-- thead Starts -->

<tr>

<th>#</th>
<th>Invoice No</th>
<th>Amount Paid</th>
<th>Payment Method</th>
<th>Reference #</th>
<th>Payment Code</th>
<th>Payment Date</th>
<th>Customer</th>
<th>Status</th>
<th>Action</th>

</tr>

</thead><!-- thead Ends -->

<tbody><!-- tbody Starts -->

<?php

$i = 0;

$get_payments = "SELECT p.*, c.customer_name 
                FROM payments p 
                LEFT JOIN customer_orders co ON p.invoice_no = co.invoice_no 
                LEFT JOIN customers c ON co.customer_id = c.customer_id 
                GROUP BY p.payment_id 
                ORDER BY p.payment_date DESC";

$run_payments = mysqli_query($con,$get_payments);

if(mysqli_num_rows($run_payments) > 0) {
    while($row_payments = mysqli_fetch_array($run_payments)){

$payment_id = $row_payments['payment_id'];

$invoice_no = $row_payments['invoice_no'];

$amount = $row_payments['amount'];

$payment_mode = $row_payments['payment_mode'];

$ref_no = $row_payments['ref_no'];

$code = $row_payments['code'];

$payment_date = $row_payments['payment_date'];

$customer_name = $row_payments['customer_name'] ? $row_payments['customer_name'] : 'Unknown';

// Get payment status
$get_order_status = "SELECT order_status FROM customer_orders WHERE invoice_no='$invoice_no' LIMIT 1";
$run_order_status = mysqli_query($con, $get_order_status);
$row_order_status = mysqli_fetch_array($run_order_status);
$order_status = $row_order_status['order_status'] ? $row_order_status['order_status'] : 'completed';

$i++;


?>


<tr>

<td><?php echo $i; ?></td>

<td bgcolor="yellow" ><?php echo $invoice_no; ?></td>

<td><strong>Rs <?php echo number_format($amount, 2); ?></strong></td>

<td>
    <?php if($payment_mode == 'Stripe'): ?>
        <span class="label label-primary">
            <i class="fa fa-credit-card"></i> <?php echo $payment_mode; ?>
        </span>
    <?php else: ?>
        <span class="label label-info">
            <i class="fa fa-money"></i> <?php echo $payment_mode; ?>
        </span>
    <?php endif; ?>
</td>

<td><?php echo $ref_no; ?></td>

<td><?php echo $code; ?></td>

<td><?php echo date('M d, Y H:i', strtotime($payment_date)); ?></td>

<td><?php echo $customer_name; ?></td>

<td>
    <?php if($order_status == 'delivered'): ?>
        <span class="label label-success">
            <i class="fa fa-check"></i> Delivered
        </span>
    <?php elseif($order_status == 'pending'): ?>
        <span class="label label-warning">
            <i class="fa fa-clock-o"></i> Pending
        </span>
    <?php else: ?>
        <span class="label label-info">
            <i class="fa fa-truck"></i> <?php echo ucfirst($order_status); ?>
        </span>
    <?php endif; ?>
</td>

<td>

<a href="index.php?payment_delete=<?php echo $payment_id; ?>" onclick="return confirm('Are you sure you want to delete this payment record?')" class="btn btn-danger btn-sm">

<i class="fa fa-trash-o" ></i> Delete

</a>

</td>


</tr>


<?php 
    }
} else {
    // No online payments found
    ?>
    <tr>
        <td colspan="10" class="text-center text-muted" style="padding: 30px;">
            <i class="fa fa-info-circle fa-2x" style="margin-bottom: 10px;"></i><br>
            <strong>No Online Payments Found</strong><br>
            <small>All current earnings (Rs <?php echo number_format($total_earnings, 2); ?>) are from COD delivered orders shown above.</small>
        </td>
    </tr>
    <?php
}
?>

</tbody><!-- tbody Ends -->

</table><!-- table table-hover table-bordered table-striped Ends -->

</div><!-- table-responsive Ends -->

            </div><!-- panel-body Ends -->
        </div><!-- panel panel-primary Ends -->
    </div><!-- col-md-12 Ends -->
</div><!-- row Ends -->

</div><!-- panel-body Ends -->

</div><!-- panel panel-default Ends -->

</div><!-- col-lg-12 Ends -->

</div><!-- 2 row Ends -->


<?php } ?>