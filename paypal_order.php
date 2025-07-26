<?php

session_start();
include("includes/db.php");
include("functions/functions.php");

// Stripe Configuration
define('STRIPE_PUBLISHABLE_KEY', 'pk_test_51RdNxrG3xkJlXxxMWXIP0Vt8UriSlVMxyv36phYG3fWf702hHXtCFGYc5kOaXRyNC1xjtQRKvuwzsufzG6SQjtwS0092fADEUB');
define('STRIPE_SECRET_KEY', 'sk_test_51RdNxrG3xkJlXxxMw81UrZoPvGyr9DkBszxeIPQcXRIaCLxeacuQ9z0cJEVdEjfVQBKVaiipg2Ho4ggEphm5LfIx00bEdluYrt');

// Include Stripe PHP library (you need to download this)
require_once 'stripe-php/init.php';
\Stripe\Stripe::setApiKey(STRIPE_SECRET_KEY);

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

// Get cart total
$ip_add = getRealUserIp();
$total_amount = 0;
$cart_items = [];

$select_cart = "select * from cart where ip_add='$ip_add'";
$run_cart = mysqli_query($con, $select_cart);

while($row_cart = mysqli_fetch_array($run_cart)) {
    $pro_id = $row_cart['p_id'];
    $pro_qty = $row_cart['qty'];
    $pro_price = $row_cart['p_price'];
    $pro_size = $row_cart['size'];
    
    $get_products = "select * from products where product_id='$pro_id'";
    $run_products = mysqli_query($con, $get_products);
    $row_products = mysqli_fetch_array($run_products);
    
    $sub_total = $pro_price * $pro_qty;
    $total_amount += $sub_total;
    
    $cart_items[] = [
        'id' => $pro_id,
        'title' => $row_products['product_title'],
        'price' => $pro_price,
        'quantity' => $pro_qty,
        'size' => $pro_size
    ];
}

// Add shipping cost
$shipping_cost = 0;
if($total_amount > 0 && $total_amount < 50000) {
    $shipping_cost = 250;
}
$final_total = $total_amount + $shipping_cost;

if($final_total <= 0) {
    echo "<script>alert('Your cart is empty!'); window.open('cart.php','_self')</script>";
    exit();
}

// Create Stripe Payment Intent
try {
    $payment_intent = \Stripe\PaymentIntent::create([
        'amount' => $final_total * 100, // Convert to cents
        'currency' => 'pkr',
        'metadata' => [
            'customer_id' => $customer_id,
            'customer_email' => $session_email
        ]
    ]);
} catch (Exception $e) {
    echo "<script>alert('Error creating payment: " . $e->getMessage() . "'); window.open('cart.php','_self')</script>";
    exit();
}

// Handle payment intent confirmation (if coming from Stripe)
if(isset($_GET['payment_intent'])) {
    $payment_intent_id = $_GET['payment_intent'];
    
    try {
        $payment_intent = \Stripe\PaymentIntent::retrieve($payment_intent_id);
        
        if($payment_intent->status === 'succeeded') {
            // Payment successful - process the order
            $status = "delivered";
            $invoice_no = mt_rand();
            $tracking_number = 'TRK' . date('Ymd') . str_pad($invoice_no, 6, '0', STR_PAD_LEFT);
            
            // Process each item in cart
            $select_cart = "select * from cart where ip_add='$ip_add'";
            $run_cart = mysqli_query($con, $select_cart);
            
            $order_ids = []; // Store order IDs for tracking
            $first_item = true; // Track if this is the first item to add shipping cost
            
            while($row_cart = mysqli_fetch_array($run_cart)) {
                $pro_id = $row_cart['p_id'];
                $pro_size = $row_cart['size'];
                $pro_qty = $row_cart['qty'];
                $pro_price = $row_cart['p_price'];
                $sub_total = $pro_price * $pro_qty;
                
                // Add shipping cost to first item only
                if($first_item) {
                    $item_total = $sub_total + $shipping_cost;
                    $first_item = false;
                } else {
                    $item_total = $sub_total;
                }
                
                // Insert customer order
                $insert_customer_order = "insert into customer_orders (customer_id,due_amount,invoice_no,qty,size,order_date,order_status,tracking_number) values ('$customer_id','$item_total','$invoice_no','$pro_qty','$pro_size',NOW(),'$status','$tracking_number')";
                $run_customer_order = mysqli_query($con, $insert_customer_order);
                
                if($run_customer_order) {
                    $order_id = mysqli_insert_id($con);
                    $order_ids[] = $order_id;
                }
                
                // Insert pending order
                $insert_pending_order = "insert into pending_orders (customer_id,invoice_no,product_id,qty,size,order_status) values ('$customer_id','$invoice_no','$pro_id','$pro_qty','$pro_size','$status')";
                $run_pending_order = mysqli_query($con, $insert_pending_order);
                
                // Update product stock - decrement quantity
                $get_product_info = "SELECT has_variants FROM products WHERE product_id='$pro_id'";
                $run_product_info = mysqli_query($con, $get_product_info);
                $row_product_info = mysqli_fetch_array($run_product_info);
                $has_variants = $row_product_info['has_variants'];
                
                if($has_variants == 1) {
                    // Product has color variants - update variant stock
                    $update_variant_stock = "UPDATE product_variants SET stock_quantity = stock_quantity - $pro_qty 
                                           WHERE product_id='$pro_id' AND stock_quantity >= $pro_qty 
                                           ORDER BY stock_quantity DESC LIMIT 1";
                    mysqli_query($con, $update_variant_stock);
                } else {
                    // Product has sizes - update size stock
                    $update_size_stock = "UPDATE product_sizes SET stock_quantity = stock_quantity - $pro_qty 
                                        WHERE product_id='$pro_id' AND size_id=(SELECT size_id FROM sizes WHERE size_name='$pro_size') 
                                        AND stock_quantity >= $pro_qty";
                    mysqli_query($con, $update_size_stock);
                }
                
                // Update main product stock and availability
                if($has_variants == 1) {
                    $update_main_stock = "UPDATE products SET stock_quantity = (
                        SELECT COALESCE(SUM(stock_quantity), 0) FROM product_variants 
                        WHERE product_id='$pro_id' AND variant_status='active'
                    ) WHERE product_id='$pro_id'";
                } else {
                    $update_main_stock = "UPDATE products SET stock_quantity = (
                        SELECT COALESCE(SUM(stock_quantity), 0) FROM product_sizes 
                        WHERE product_id='$pro_id' AND ps_status='active'
                    ) WHERE product_id='$pro_id'";
                }
                mysqli_query($con, $update_main_stock);
                
                // Update product availability based on new stock
                $check_stock = "SELECT stock_quantity FROM products WHERE product_id='$pro_id'";
                $run_check = mysqli_query($con, $check_stock);
                $row_check = mysqli_fetch_array($run_check);
                $current_stock = $row_check['stock_quantity'];
                
                if($current_stock == 0) {
                    $update_availability = "UPDATE products SET product_availability='Out of Stock' WHERE product_id='$pro_id'";
                } elseif($current_stock <= 5) {
                    $update_availability = "UPDATE products SET product_availability='Limited Stock' WHERE product_id='$pro_id'";
                } else {
                    $update_availability = "UPDATE products SET product_availability='In Stock' WHERE product_id='$pro_id'";
                }
                mysqli_query($con, $update_availability);
            }
            
            // Insert initial order tracking for each order
            foreach($order_ids as $order_id) {
                $insert_tracking = "INSERT INTO order_tracking (order_id, invoice_no, tracking_number, status, status_message, updated_by, created_date) 
                                  VALUES ('$order_id', '$invoice_no', '$tracking_number', 'confirmed', 'Order confirmed and payment received via Stripe', 'System', NOW())";
                mysqli_query($con, $insert_tracking);
            }
            
            // Insert payment record
            $insert_payment = "INSERT INTO payments (invoice_no, amount, payment_mode, ref_no, payment_date) VALUES ('$invoice_no', '$final_total', 'Stripe', '$payment_intent_id', NOW())";
            mysqli_query($con, $insert_payment);
            
            // Clear cart
            $delete_cart = "delete from cart where ip_add='$ip_add'";
            $run_delete = mysqli_query($con, $delete_cart);
            
            echo "<script>alert('Your order has been submitted successfully via Stripe! Tracking: $tracking_number')</script>";
            echo "<script>window.open('customer/my_account.php?my_orders','_self')</script>";
            exit();
        }
    } catch (Exception $e) {
        echo "<script>alert('Payment verification failed: " . $e->getMessage() . "'); window.open('checkout.php','_self')</script>";
        exit();
    }
}

?>

<!DOCTYPE html>
<html>
<head>
    <title>Stripe Payment - Complete Your Order</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="styles/bootstrap-337.min.css">
    <link rel="stylesheet" href="font-awesome/css/font-awesome.min.css">
    <link rel="stylesheet" href="styles/style.css">
    <script src="https://js.stripe.com/v3/"></script>
    <style>
        .payment-container {
            max-width: 600px;
            margin: 50px auto;
            padding: 30px;
            border: 1px solid #ddd;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .order-summary {
            background: #f8f9fa;
            padding: 20px;
            margin-bottom: 30px;
            border-radius: 5px;
            border: 1px solid #e9ecef;
        }
        .order-item {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid #eee;
        }
        .order-item:last-child {
            border-bottom: none;
            font-weight: bold;
            margin-top: 10px;
            padding-top: 15px;
            border-top: 2px solid #dee2e6;
        }
        #payment-element {
            margin-bottom: 20px;
        }
        .btn-pay {
            width: 100%;
            padding: 15px;
            font-size: 16px;
            font-weight: bold;
            background: #635BFF;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        .btn-pay:hover {
            background: #5a54d8;
        }
        .btn-pay:disabled {
            background: #6c757d;
            cursor: not-allowed;
        }
        .error-message {
            color: #dc3545;
            margin-top: 15px;
            padding: 10px;
            background: #f8d7da;
            border-radius: 5px;
        }
        .spinner {
            display: none;
            width: 20px;
            height: 20px;
            border: 2px solid #ffffff;
            border-top: 2px solid transparent;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="payment-container">
            <h2 class="text-center mb-4">Complete Your Payment</h2>
            
            <div class="order-summary">
                <h4><i class="fa fa-shopping-cart"></i> Order Summary</h4>
                <?php foreach($cart_items as $item): ?>
                <div class="order-item">
                    <span><?php echo $item['title']; ?> (Size: <?php echo $item['size']; ?>) x<?php echo $item['quantity']; ?></span>
                    <span>Rs <?php echo number_format($item['price'] * $item['quantity'], 2); ?></span>
                </div>
                <?php endforeach; ?>
                <?php if($shipping_cost > 0): ?>
                <div class="order-item">
                    <span>Subtotal:</span>
                    <span>Rs <?php echo number_format($total_amount, 2); ?></span>
                </div>
                <div class="order-item">
                    <span>Shipping:</span>
                    <span>Rs <?php echo number_format($shipping_cost, 2); ?></span>
                </div>
                <?php endif; ?>
                <div class="order-item">
                    <span>Total Amount:</span>
                    <span>Rs <?php echo number_format($final_total, 2); ?></span>
                </div>
            </div>

            <form id="payment-form">
                <div id="payment-element">
                    <!-- Stripe Elements will create form elements here -->
                </div>
                <button id="submit" class="btn-pay">
                    <div class="spinner" id="spinner"></div>
                    <span id="button-text"><i class="fa fa-lock"></i> Pay Rs <?php echo number_format($final_total, 2); ?></span>
                </button>
                <div id="payment-messages" class="error-message" style="display: none;"></div>
            </form>

            <div class="text-center mt-4">
                <a href="checkout.php" class="btn btn-secondary">
                    <i class="fa fa-arrow-left"></i> Back to Payment Options
                </a>
            </div>
        </div>
    </div>

    <script>
        const stripe = Stripe('<?php echo STRIPE_PUBLISHABLE_KEY; ?>');
        const clientSecret = '<?php echo $payment_intent->client_secret; ?>';
        
        const appearance = {
            theme: 'stripe',
            variables: {
                colorPrimary: '#635BFF',
                colorBackground: '#ffffff',
                colorText: '#30313d',
                colorDanger: '#df1b41',
                fontFamily: 'Ideal Sans, system-ui, sans-serif',
                spacingUnit: '2px',
                borderRadius: '4px',
            }
        };
        
        const elements = stripe.elements({ 
            clientSecret: clientSecret,
            appearance: appearance 
        });
        
        const paymentElement = elements.create('payment');
        paymentElement.mount('#payment-element');
        
        document.querySelector('#payment-form').addEventListener('submit', handleSubmit);
        
        async function handleSubmit(event) {
            event.preventDefault();
            setLoading(true);
            
            const {error} = await stripe.confirmPayment({
                elements,
                confirmParams: {
                    return_url: window.location.protocol + '//' + window.location.host + 
                               window.location.pathname + '?payment_intent=' + clientSecret.split('_secret')[0],
                },
            });
            
            if (error) {
                if (error.type === 'card_error' || error.type === 'validation_error') {
                    showMessage(error.message);
                } else {
                    showMessage('An unexpected error occurred. Please try again.');
                }
                setLoading(false);
            }
        }
        
        function showMessage(messageText) {
            const messageContainer = document.querySelector('#payment-messages');
            messageContainer.textContent = messageText;
            messageContainer.style.display = 'block';
        }
        
        function setLoading(isLoading) {
            const submitButton = document.querySelector('#submit');
            const spinner = document.querySelector('#spinner');
            const buttonText = document.querySelector('#button-text');
            
            if (isLoading) {
                submitButton.disabled = true;
                spinner.style.display = 'inline-block';
                buttonText.style.display = 'none';
            } else {
                submitButton.disabled = false;
                spinner.style.display = 'none';
                buttonText.style.display = 'inline-block';
            }
        }
    </script>
</body>
</html>