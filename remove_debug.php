<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
include("includes/db.php");
include("functions/functions.php");

echo "<h2>Remove Product Debug Test</h2>";

if(isset($_POST['update'])) {
    echo "<h3>POST Data Received:</h3>";
    echo "<pre>";
    print_r($_POST);
    echo "</pre>";
    
    if(isset($_POST['remove'])) {
        echo "<h3>Remove Array:</h3>";
        echo "<pre>";
        print_r($_POST['remove']);
        echo "</pre>";
        
        echo "<h3>Processing Removals:</h3>";
        foreach($_POST['remove'] as $cart_id) {
            $cart_id = (int)$cart_id;
            echo "Cart ID to remove: $cart_id<br>";
            
            $delete_query = "DELETE FROM cart WHERE cart_id='$cart_id'";
            echo "Delete query: $delete_query<br>";
            
            $result = mysqli_query($con, $delete_query);
            if($result) {
                $affected = mysqli_affected_rows($con);
                echo "✓ Query executed, affected rows: $affected<br>";
            } else {
                echo "✗ Query failed: " . mysqli_error($con) . "<br>";
            }
            echo "<br>";
        }
    } else {
        echo "<p style='color: red;'>No remove data found!</p>";
    }
}

// Get current cart items
$ip_add = getRealUserIp();
if($ip_add == '' || $ip_add == '127.0.0.1' || $ip_add == '::1') {
    $check_ip_query = "SELECT DISTINCT ip_add FROM cart LIMIT 1";
    $check_ip_result = mysqli_query($con, $check_ip_query);
    if($check_ip_result && mysqli_num_rows($check_ip_result) > 0) {
        $ip_row = mysqli_fetch_array($check_ip_result);
        $ip_add = $ip_row['ip_add'];
    }
}

echo "<h3>Current Cart Items:</h3>";
$cart_query = "SELECT * FROM cart WHERE ip_add='$ip_add'";
$cart_result = mysqli_query($con, $cart_query);

if($cart_result && mysqli_num_rows($cart_result) > 0) {
    echo "<form action='remove_debug.php' method='post'>";
    echo "<table border='1'>";
    echo "<tr><th>Cart ID</th><th>Product ID</th><th>Quantity</th><th>Remove</th></tr>";
    
    while($row = mysqli_fetch_array($cart_result)) {
        $cart_id = $row['cart_id'];
        $product_id = $row['p_id'];
        $qty = $row['qty'];
        
        echo "<tr>";
        echo "<td>$cart_id</td>";
        echo "<td>$product_id</td>";
        echo "<td>$qty</td>";
        echo "<td><input type='checkbox' name='remove[]' value='$cart_id'> Remove</td>";
        echo "</tr>";
    }
    echo "</table>";
    echo "<br><button type='submit' name='update'>Test Remove</button>";
    echo "</form>";
} else {
    echo "No items in cart.";
}

echo "<p><a href='cart.php'>Back to Cart</a></p>";
?>
