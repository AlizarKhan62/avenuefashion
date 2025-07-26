<?php
// Stripe Configuration
define('STRIPE_PUBLISHABLE_KEY', 'pk_test_51RdNxrG3xkJlXxxMWXIP0Vt8UriSlVMxyv36phYG3fWf702hHXtCFGYc5kOaXRyNC1xjtQRKvuwzsufzG6SQjtwS0092fADEUB');
define('STRIPE_SECRET_KEY', 'sk_test_51RdNxrG3xkJlXxxMw81UrZoPvGyr9DkBszxeIPQcXRIaCLxeacuQ9z0cJEVdEjfVQBKVaiipg2Ho4ggEphm5LfIx00bEdluYrt');

// Include Stripe PHP library
require_once dirname(__FILE__) . '/../stripe-php/init.php';

// Set the API key
\Stripe\Stripe::setApiKey(STRIPE_SECRET_KEY);

// Helper function to calculate cart total
function getCartTotal() {
    global $con;
    $ip_add = getRealUserIp();
    $total = 0;
    
    $select_cart = "select * from cart where ip_add='$ip_add'";
    $run_cart = mysqli_query($con, $select_cart);
    
    while($row_cart = mysqli_fetch_array($run_cart)) {
        $pro_price = $row_cart['p_price'];
        $pro_qty = $row_cart['qty'];
        $sub_total = $pro_price * $pro_qty;
        $total += $sub_total;
    }
    
    return $total;
}

// Helper function to get cart items for Stripe
function getCartItems() {
    global $con;
    $ip_add = getRealUserIp();
    $items = [];
    
    $select_cart = "select * from cart where ip_add='$ip_add'";
    $run_cart = mysqli_query($con, $select_cart);
    
    while($row_cart = mysqli_fetch_array($run_cart)) {
        $pro_id = $row_cart['p_id'];
        $pro_qty = $row_cart['qty'];
        $pro_price = $row_cart['p_price'];
        
        $get_products = "select * from products where product_id='$pro_id'";
        $run_products = mysqli_query($con, $get_products);
        $row_products = mysqli_fetch_array($run_products);
        
        $items[] = [
            'id' => $pro_id,
            'title' => $row_products['product_title'],
            'price' => $pro_price,
            'quantity' => $pro_qty
        ];
    }
    
    return $items;
}
?>
