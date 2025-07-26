<?php
session_start();
include("includes/db.php");
include("functions/functions.php");
include("includes/stripe_config.php");

// Check if customer is logged in
if(!isset($_SESSION['customer_email'])) {
    echo "<script>window.open('checkout.php','_self')</script>";
    exit();
}

$session_email = $_SESSION['customer_email'];
$select_customer = "select * from customers where customer_email='$session_email'";
$run_customer = mysqli_query($con, $select_customer);
$row_customer = mysqli_fetch_array($run_customer);
$customer_id = $row_customer['customer_id'];

// Get payment intent from URL
if(isset($_GET['payment_intent'])) {
    $payment_intent_id = $_GET['payment_intent'];
    
    try {
        // Retrieve the payment intent from Stripe
        $payment_intent = \Stripe\PaymentIntent::retrieve($payment_intent_id);
        
        if($payment_intent->status === 'succeeded') {
            // Payment successful - process the order
            $ip_add = getRealUserIp();
            $status = "Complete";
            $invoice_no = mt_rand();
            $tracking_number = 'TRK' . date('Ymd') . str_pad($invoice_no, 6, '0', STR_PAD_LEFT);
            
            // Calculate total amount
            $total_amount = $payment_intent->amount / 100; // Convert from cents
            
            // Get cart items
            $select_cart = "select * from cart where ip_add='$ip_add'";
            $run_cart = mysqli_query($con, $select_cart);
            
            $order_items = [];
            while($row_cart = mysqli_fetch_array($run_cart)) {
                $pro_id = $row_cart['p_id'];
                $pro_size = $row_cart['size'];
                $pro_qty = $row_cart['qty'];
                $pro_price = $row_cart['p_price'];
                
                $order_items[] = [
                    'product_id' => $pro_id,
                    'size' => $pro_size,
                    'qty' => $pro_qty,
                    'price' => $pro_price
                ];
            }
            
            // Insert main order
            $insert_customer_order = "INSERT INTO customer_orders (
                customer_id, 
                due_amount, 
                invoice_no, 
                qty, 
                size, 
                order_date, 
                order_status,
                tracking_number
            ) VALUES (
                '$customer_id', 
                '$total_amount', 
                '$invoice_no', 
                '" . count($order_items) . "', 
                'Mixed', 
                NOW(), 
                '$status',
                '$tracking_number'
            )";
            
            $run_customer_order = mysqli_query($con, $insert_customer_order);
            $order_id = mysqli_insert_id($con);
            
            if($run_customer_order) {
                // Insert each product in the order
                foreach($order_items as $item) {
                    $insert_pending_order = "INSERT INTO pending_orders (
                        customer_id, 
                        invoice_no, 
                        product_id, 
                        qty, 
                        size, 
                        order_status
                    ) VALUES (
                        '$customer_id', 
                        '$invoice_no', 
                        '{$item['product_id']}', 
                        '{$item['qty']}', 
                        '{$item['size']}', 
                        '$status'
                    )";
                    mysqli_query($con, $insert_pending_order);
                }
                
                // Insert payment record
                $insert_payment = "INSERT INTO payments (
                    invoice_no, 
                    amount, 
                    payment_mode, 
                    ref_no, 
                    code, 
                    payment_date
                ) VALUES (
                    '$invoice_no', 
                    '$total_amount', 
                    'Stripe', 
                    '$payment_intent_id', 
                    '$tracking_number', 
                    NOW()
                )";
                mysqli_query($con, $insert_payment);
                
                // Clear the cart
                $delete_cart = "DELETE FROM cart WHERE ip_add='$ip_add'";
                mysqli_query($con, $delete_cart);
                
                // Success message and redirect
                echo "<script>alert('Payment successful! Your order has been placed. Tracking Number: $tracking_number');</script>";
                echo "<script>window.open('customer/my_account.php?my_orders','_self')</script>";
                
            } else {
                echo "<script>alert('Error processing order. Please contact support.');</script>";
                echo "<script>window.open('cart.php','_self')</script>";
            }
            
        } else {
            // Payment failed
            echo "<script>alert('Payment was not successful. Please try again.');</script>";
            echo "<script>window.open('payment_options.php','_self')</script>";
        }
        
    } catch (Exception $e) {
        echo "<script>alert('Error verifying payment: " . $e->getMessage() . "');</script>";
        echo "<script>window.open('payment_options.php','_self')</script>";
    }
    
} else {
    // No payment intent provided
    echo "<script>window.open('cart.php','_self')</script>";
}
?>
