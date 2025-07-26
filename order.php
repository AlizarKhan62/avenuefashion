<?php

include("includes/db.php");
include("includes/header.php");
include("functions/functions.php");

?>

<?php

if(isset($_GET['c_id'])){
    $customer_id = $_GET['c_id'];
}

$ip_add = getRealUserIp();
$status = "pending";
$invoice_no = mt_rand();

// Generate unique tracking number
$tracking_number = 'TRK' . date('Ymd') . str_pad($invoice_no, 6, '0', STR_PAD_LEFT);

$select_cart = "select * from cart where ip_add='$ip_add'";
$run_cart = mysqli_query($con,$select_cart);

$total_amount = 0;
$order_items = [];

while($row_cart = mysqli_fetch_array($run_cart)){
    $pro_id = $row_cart['p_id'];
    $pro_size = $row_cart['size'];
    $pro_qty = $row_cart['qty'];
    $sub_total = $row_cart['p_price']*$pro_qty;
    $total_amount += $sub_total;
    
    $order_items[] = [
        'product_id' => $pro_id,
        'size' => $pro_size,
        'quantity' => $pro_qty,
        'price' => $row_cart['p_price'],
        'subtotal' => $sub_total
    ];
}

// Add shipping cost to total
$shipping_cost = 0;
if($total_amount > 0 && $total_amount < 50000) {
    $shipping_cost = 250;
}
$final_total = $total_amount + $shipping_cost;

if(!empty($order_items)) {
    // Insert main order record with tracking
    $insert_customer_order = "INSERT INTO customer_orders 
        (customer_id, due_amount, invoice_no, qty, size, order_date, order_status, tracking_number, current_status, estimated_delivery) 
        VALUES 
        ('$customer_id', '$final_total', '$invoice_no', '1', '', NOW(), '$status', '$tracking_number', '$status', DATE_ADD(NOW(), INTERVAL 7 DAY))";
    
    $run_customer_order = mysqli_query($con, $insert_customer_order);
    $order_id = mysqli_insert_id($con);
    
    if($run_customer_order) {
        // Insert individual order items and update stock
        foreach($order_items as $item) {
            $insert_pending_order = "INSERT INTO pending_orders 
                (customer_id, invoice_no, product_id, qty, size, order_status) 
                VALUES 
                ('$customer_id', '$invoice_no', '{$item['product_id']}', '{$item['quantity']}', '{$item['size']}', '$status')";
            mysqli_query($con, $insert_pending_order);
            
            // Update product stock - decrement quantity
            $pro_id = $item['product_id'];
            $pro_qty = $item['quantity'];
            $pro_size = $item['size'];
            
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
        
        // Insert initial tracking record
        $insert_tracking = "INSERT INTO order_tracking 
            (order_id, invoice_no, tracking_number, status, status_message, updated_by) 
            VALUES 
            ('$order_id', '$invoice_no', '$tracking_number', '$status', 'Order placed successfully. We have received your order and it is being processed.', 'System')";
        mysqli_query($con, $insert_tracking);
        
        // Insert payment record for COD (will be updated when delivered)
        $insert_payment = "INSERT INTO payments (invoice_no, amount, payment_mode, ref_no, code, payment_date) 
                          VALUES ('$invoice_no', '$final_total', 'Cash on Delivery', 'COD-$invoice_no', 'PENDING', NOW())";
        mysqli_query($con, $insert_payment);
        
        // Clear cart
        $delete_cart = "DELETE FROM cart WHERE ip_add='$ip_add'";
        mysqli_query($con, $delete_cart);
        
        echo "<script>
            alert('Your order has been submitted successfully!\\n\\nOrder Details:\\nInvoice: $invoice_no\\nTracking Number: $tracking_number\\n\\nYou can track your order status anytime.');
            window.open('track_order.php?tracking=$tracking_number','_self');
        </script>";
    } else {
        echo "<script>alert('Error placing order. Please try again.');</script>";
        echo "<script>window.open('cart.php','_self');</script>";
    }
} else {
    echo "<script>alert('No items in cart');</script>";
    echo "<script>window.open('cart.php','_self');</script>";
}

?>
