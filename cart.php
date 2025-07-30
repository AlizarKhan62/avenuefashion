<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
include("includes/db.php");
include("includes/header.php");
include("functions/functions.php");
include("includes/main.php");
?>

<main>
    <div class="nero">
        <div class="nero__heading">
            <span class="nero__bold">SHOP</span> Cart
        </div>
        <p class="nero__text"></p>
    </div>
</main>

<div id="content">
    <div class="container">
        <div class="col-md-9" id="cart">
            <div class="box">
                <form action="cart.php" method="post" enctype="multipart-form-data">
                    <h1>Shopping Cart</h1>
                    <?php
                    $ip_add = getRealUserIp();
                    
                    // Handle localhost IP variations
                    if($ip_add == '' || $ip_add == '127.0.0.1' || $ip_add == '::1') {
                        // Check what IP is actually in the cart table
                        $check_ip_query = "SELECT DISTINCT ip_add FROM cart LIMIT 1";
                        $check_ip_result = mysqli_query($con, $check_ip_query);
                        if($check_ip_result && mysqli_num_rows($check_ip_result) > 0) {
                            $ip_row = mysqli_fetch_array($check_ip_result);
                            $ip_add = $ip_row['ip_add'];
                        }
                    }
                    
                    // For all users, show items by IP address only
                    $select_cart = "SELECT * FROM cart WHERE ip_add='$ip_add'";
                    
                    $run_cart = mysqli_query($con, $select_cart);
                    
                    // Check if query was successful
                    $count = 0;
                    if($run_cart) {
                        $count = mysqli_num_rows($run_cart);
                    } else {
                        // Log the error for debugging
                        error_log("Cart query failed: " . mysqli_error($con));
                    }
                    ?>
                    <p class="text-muted">You currently have <?php echo $count; ?> item(s) in your cart.</p>

                    <?php if($count > 0): ?>
                    
                    <div class="table-responsive">
                        <!-- Desktop Table View -->
                        <table class="table hidden-xs" style="table-layout: fixed; width: 100%;">
                            <thead>
                                <tr>
                                    <th style="width: 15%; text-align: center;">Product</th>
                                    <th style="width: 25%; text-align: left;">Name</th>
                                    <th style="width: 10%; text-align: center;">Size</th>
                                    <th style="width: 12%; text-align: center;">Quantity</th>
                                    <th style="width: 13%; text-align: center;">Unit Price</th>
                                    <th style="width: 8%; text-align: center;"><i class="fa fa-trash" title="Remove Item"></i></th>
                                    <th style="width: 17%; text-align: center;">Sub Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $total = 0;
                                $run_cart_reset = mysqli_query($con, $select_cart); // Reset the query
                                while($row_cart = mysqli_fetch_array($run_cart_reset)){
                                    $pro_id = $row_cart['p_id'];
                                    $pro_size = isset($row_cart['size']) ? $row_cart['size'] : '';
                                    $pro_qty = $row_cart['qty'];
                                    $only_price = $row_cart['p_price'];
                                    $color_variant = isset($row_cart['color_variant']) ? $row_cart['color_variant'] : '';
                                    $variant_image = isset($row_cart['variant_image']) ? $row_cart['variant_image'] : '';
                                    $cart_id = isset($row_cart['cart_id']) ? $row_cart['cart_id'] : $row_cart['p_id'];
                                    
                                    // If no size in cart, try to get first available size for this product
                                    if(empty($pro_size)) {
                                        $get_size = "SELECT size_name FROM sizes WHERE product_id='$pro_id' LIMIT 1";
                                        $run_size = mysqli_query($con, $get_size);
                                        if($run_size && mysqli_num_rows($run_size) > 0) {
                                            $row_size = mysqli_fetch_array($run_size);
                                            $pro_size = $row_size['size_name'];
                                        }
                                    }
                                    
                                    $get_products = "SELECT * FROM products WHERE product_id='$pro_id'";
                                    $run_products = mysqli_query($con, $get_products);
                                    
                                    if($run_products && mysqli_num_rows($run_products) > 0) {
                                        $row_products = mysqli_fetch_array($run_products);
                                        $product_title = $row_products['product_title'];
                                        $product_img1 = $row_products['product_img1'];
                                        $product_url = isset($row_products['product_url']) ? $row_products['product_url'] : "details.php?pro_id=$pro_id";
                                        $sub_total = $only_price * $pro_qty;
                                        $_SESSION['pro_qty'] = $pro_qty;
                                        $total += $sub_total;
                                        
                                        // Determine which image to show
                                        $display_image = $product_img1; // Default to main product image
                                        if(!empty($color_variant)) {
                                            // First try the variant image stored in cart
                                            if(!empty($variant_image) && file_exists("admin_area/product_images/" . $variant_image)) {
                                                $display_image = $variant_image;
                                            } else {
                                                // Get variant images from database
                                                $get_variant = "SELECT variant_image, variant_images FROM product_variants WHERE product_id='$pro_id' AND color_name='$color_variant'";
                                                $run_variant = mysqli_query($con, $get_variant);
                                                if($run_variant && mysqli_num_rows($run_variant) > 0) {
                                                    $row_variant = mysqli_fetch_array($run_variant);
                                                    $variant_images = json_decode($row_variant['variant_images'], true);
                                                    
                                                    // Try to use first image from variant_images array
                                                    if(!empty($variant_images) && file_exists("admin_area/product_images/" . $variant_images[0])) {
                                                        $display_image = $variant_images[0];
                                                    }
                                                    // Finally fall back to variant_image field
                                                    else if(!empty($row_variant['variant_image']) && file_exists("admin_area/product_images/" . $row_variant['variant_image'])) {
                                                        $display_image = $row_variant['variant_image'];
                                                    }
                                                }
                                            }
                                            
                                            // Log debug info if we couldn't find a variant image
                                            if($display_image === $product_img1) {
                                                error_log("Debug - No variant image found:");
                                                error_log("Color: " . $color_variant);
                                                error_log("Product ID: " . $pro_id);
                                                error_log("Stored variant image: " . $variant_image);
                                            }
                                        }
                                ?>
                                <tr>
                                    <td style="text-align: center; vertical-align: middle; width: 15%;">
                                        <?php
                                        $image_url = "admin_area/product_images/" . $display_image;
                                        if(!file_exists($image_url)) {
                                            error_log("Final image path not found: " . $image_url);
                                            // If the image doesn't exist, try the default product image
                                            $display_image = $product_img1;
                                        }
                                        ?>
                                        <img src="admin_area/product_images/<?php echo $display_image; ?>" 
                                             style="width: 60px; height: 60px; object-fit: cover; border-radius: 8px;"
                                             alt="<?php echo $product_title; ?>"
                                             title="<?php echo !empty($color_variant) ? 'Color: ' . $color_variant : ''; ?>">
                                        <?php if(defined('DEVELOPMENT_MODE')): ?>
                                        <!-- Debug info -->
                                        <div style="display: none;">
                                            <small>Debug: 
                                                Variant Image: <?php echo $variant_image; ?><br>
                                                Display Image: <?php echo $display_image; ?><br>
                                                Color: <?php echo $color_variant; ?>
                                            </small>
                                        </div>
                                        <?php endif; ?>
                                    </td>
                                    <td style="text-align: left; vertical-align: middle; width: 25%;">
                                        <a href="<?php echo $product_url; ?>" style="text-decoration: none; color: #333;">
                                            <strong><?php echo $product_title; ?></strong>
                                        </a>
                                    </td>
                                    <td style="text-align: center; vertical-align: middle; width: 10%;">
                                        <?php 
                                        if(!empty($pro_size)): ?>
                                            <?php echo strtoupper($pro_size); ?>
                                        <?php else: ?>
                                            One Size
                                        <?php endif; ?>
                                    </td>
                                    <!-- Color column commented out
                                    <td>
                                        <?php if(!empty($color_variant)): ?>
                                            <div class="color-badge">
                                                <?php echo $color_variant; ?>
                                            </div>
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                    -->
                                    <td style="text-align: center; vertical-align: middle; width: 12%;">
                                        <input type="number" 
                                               name="quantity[<?php echo $cart_id; ?>]" 
                                               value="<?php echo $pro_qty; ?>" 
                                               min="1" 
                                               max="10"
                                               data-cart-id="<?php echo $cart_id; ?>" 
                                               class="quantity form-control" 
                                               style="width: 70px; margin: 0 auto;">
                                    </td>
                                    <td style="text-align: center; vertical-align: middle; width: 13%;">
                                        <strong>Rs <?php echo number_format($only_price, 2); ?></strong>
                                    </td>
                                    <td style="text-align: center; vertical-align: middle; width: 8%;">
                                        <a href="cart.php?remove_item=<?php echo $cart_id; ?>" 
                                           onclick="return confirm('Are you sure you want to remove <?php echo htmlspecialchars($product_title); ?> from your cart?');"
                                           style="color: #dc3545; text-decoration: none; font-size: 16px;"
                                           title="Remove <?php echo htmlspecialchars($product_title); ?>">
                                            <i class="fa fa-trash"></i>
                                        </a>
                                    </td>
                                    <td style="text-align: center; vertical-align: middle; width: 17%;">
                                        <strong style="color: #151413ff; font-size: 16px;">Rs <?php echo number_format($sub_total, 2); ?></strong>
                                    </td>
                                </tr>
                                <?php 
                                    }
                                } 
                                ?>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th colspan="6" style="text-align: right;">Total</th>
                                    <th style="text-align: center;"><strong>Rs <?php echo number_format($total, 2); ?></strong></th>
                                </tr>
                            </tfoot>
                        </table>

                        <!-- Mobile Card View -->
                        <div class="visible-xs mobile-cart-view">
                            <?php
                            $total_mobile = 0;
                            foreach($cart_items as $item) {
                                $pro_id = $item['pro_id'];
                                $pro_size = $item['pro_size'];
                                $pro_qty = $item['pro_qty'];
                                $only_price = $item['only_price'];
                                $color_variant = $item['color_variant'];
                                $variant_image = $item['variant_image'];
                                $cart_id = $item['cart_id'];
                                $product_title = $item['product_title'];
                                $product_img1 = $item['product_img1'];
                                $product_url = $item['product_url'];
                                $sub_total = $item['sub_total'];
                                $total_mobile += $sub_total;
                                
                                // Same image logic as desktop
                                $display_image = $product_img1;
                                if(!empty($color_variant) && !empty($variant_image)) {
                                    if(file_exists("admin_area/product_images/" . $variant_image)) {
                                        $display_image = $variant_image;
                                    }
                                }
                            ?>
                                    if($run_size && mysqli_num_rows($run_size) > 0) {
                                        $row_size = mysqli_fetch_array($run_size);
                                        $pro_size = $row_size['size_name'];
                                    }
                                }
                                
                                $get_products = "SELECT * FROM products WHERE product_id='$pro_id'";
                                $run_products = mysqli_query($con, $get_products);
                                
                                if($run_products && mysqli_num_rows($run_products) > 0) {
                                    $row_products = mysqli_fetch_array($run_products);
                                    $product_title = $row_products['product_title'];
                                    $product_img1 = $row_products['product_img1'];
                                    $product_url = isset($row_products['product_url']) ? $row_products['product_url'] : "details.php?pro_id=$pro_id";
                                    $sub_total = $only_price * $pro_qty;
                                    $total_mobile += $sub_total;
                                    
                                    // Same image logic as desktop
                                    $display_image = $product_img1;
                                    if(!empty($color_variant)) {
                                        if(!empty($variant_image) && file_exists("admin_area/product_images/" . $variant_image)) {
                                            $display_image = $variant_image;
                                        } else {
                                            $get_variant = "SELECT variant_image, variant_images FROM product_variants WHERE product_id='$pro_id' AND color_name='$color_variant'";
                                            $run_variant = mysqli_query($con, $get_variant);
                                            if($run_variant && mysqli_num_rows($run_variant) > 0) {
                                                $row_variant = mysqli_fetch_array($run_variant);
                                                $variant_images = json_decode($row_variant['variant_images'], true);
                                                
                                                if(!empty($variant_images) && file_exists("admin_area/product_images/" . $variant_images[0])) {
                                                    $display_image = $variant_images[0];
                                                } else if(!empty($row_variant['variant_image']) && file_exists("admin_area/product_images/" . $row_variant['variant_image'])) {
                                                    $display_image = $row_variant['variant_image'];
                                                }
                                            }
                                        }
                                    }
                            ?>
                            <div class="mobile-cart-item">
                                <div class="row">
                                    <div class="col-xs-4 item-image">
                                        <img src="admin_area/product_images/<?php echo $display_image; ?>" 
                                             style="width: 100%; max-width: 100px; height: 100px; object-fit: cover; border-radius: 8px;"
                                             alt="<?php echo $product_title; ?>">
                                    </div>
                                    <div class="col-xs-8 item-details">
                                        <h4 style="margin: 0 0 10px 0; font-size: 16px;">
                                            <a href="<?php echo $product_url; ?>" style="text-decoration: none; color: #333;">
                                                <?php echo $product_title; ?>
                                            </a>
                                        </h4>
                                        <p style="margin: 0; color: #666;">
                                            <strong>Size:</strong> 
                                            <?php if(!empty($pro_size)): ?>
                                                <span class="label label-primary" style="background-color: #ff6b35; padding: 4px 8px; border-radius: 4px; color: white; font-size: 11px;">
                                                    <?php echo strtoupper($pro_size); ?>
                                                </span>
                                            <?php else: ?>
                                                <span class="text-muted" style="font-style: italic;">One Size</span>
                                            <?php endif; ?>
                                        </p>
                                        <p style="margin: 0; color: #666;">
                                            <strong>Price:</strong> Rs <?php echo number_format($only_price, 2); ?>
                                        </p>
                                        <p style="margin: 0; color: #666;">
                                            <strong>Subtotal:</strong> Rs <?php echo number_format($sub_total, 2); ?>
                                        </p>
                                    </div>
                                </div>
                                <div class="item-controls" style="margin-top: 15px; border-top: 1px solid #eee; padding-top: 15px;">
                                    <div style="display: flex; align-items: center; gap: 10px;">
                                        <label style="margin: 0; font-weight: bold;">Qty:</label>
                                        <input type="number" 
                                               name="quantity[<?php echo $cart_id; ?>]" 
                                               value="<?php echo $pro_qty; ?>" 
                                               min="1" 
                                               max="10"
                                               data-cart-id="<?php echo $cart_id; ?>" 
                                               class="quantity form-control" 
                                               style="width: 80px; display: inline-block;">
                                    </div>
                                    <div style="display: flex; align-items: center; gap: 10px;">
                                        <label style="margin: 0; font-weight: bold;">Remove:</label>
                                        <a href="cart.php?remove_item=<?php echo $cart_id; ?>" 
                                           onclick="return confirm('Are you sure you want to remove <?php echo htmlspecialchars($product_title); ?> from your cart?');"
                                           style="color: #dc3545; text-decoration: none; font-size: 16px;"
                                           title="Remove <?php echo htmlspecialchars($product_title); ?>">
                                            <i class="fa fa-trash"></i> Delete
                                        </a>
                                    </div>
                                </div>
                            </div>
                            <?php 
                            } 
                            ?>
                            <div class="mobile-total" style="background: #f8f9fa; padding: 15px; border-radius: 8px; margin-top: 20px;">
                                <h4 style="margin: 0; text-align: center;">
                                    <strong>Total: Rs <?php echo number_format($total_mobile, 2); ?></strong>
                                </h4>
                            </div>
                        </div>
                    </div>

                   <!-- Add this to your cart.php where the "Try On" button is -->
                   <a href="virtual_tryon.php" class="btn btn-primary">
                       <i class="fa fa-magic"></i> Virtual Try-On
                   </a>

                    <div class="box-footer">
                        <div class="pull-left">
                            <a href="shop.php" class="btn btn-default">
                                <i class="fa fa-chevron-left"></i> Continue Shopping
                            </a>
                        </div>
                        <div class="pull-right">
                            <button class="btn btn-info" type="submit" name="update" value="Update Cart">
                                <i class="fa fa-refresh"></i> Update Cart
                            </button>
                            <button class="btn btn-warning" type="submit" name="clear_cart" value="Clear Cart" onclick="return confirm('Are you sure you want to remove all items from your cart? This action cannot be undone.');">
                                <i class="fa fa-trash"></i> Clear Cart
                            </button>
                            <a href="checkout.php" class="btn btn-success">
                                Proceed to Checkout <i class="fa fa-chevron-right"></i>
                            </a>
                        </div>
                    </div>
                    
                    <?php else: ?>
                    <div class="alert alert-info text-center">
                        <h3><i class="fa fa-shopping-cart"></i> Your cart is empty</h3>
                        <p>Looks like you haven't added any items to your cart yet.</p>
                        <a href="shop.php" class="btn btn-primary btn-lg">
                            <i class="fa fa-shopping-bag"></i> Start Shopping
                        </a>
                    </div>
                    <?php endif; ?>
                </form>
                
            </div>

            <?php
            // Handle coupon application
            // if(isset($_POST['apply_coupon'])){
            //     $code = mysqli_real_escape_string($con, $_POST['code']);
            //     if($code != ""){
            //         $get_coupons = "SELECT * FROM coupons WHERE coupon_code='$code'";
            //         $run_coupons = mysqli_query($con, $get_coupons);
                    
            //         if($run_coupons && mysqli_num_rows($run_coupons) > 0){
            //             $row_coupons = mysqli_fetch_array($run_coupons);
            //             $coupon_pro = $row_coupons['product_id'];
            //             $coupon_price = $row_coupons['coupon_price'];
            //             $coupon_limit = isset($row_coupons['coupon_limit']) ? $row_coupons['coupon_limit'] : 0;
            //             $coupon_used = isset($row_coupons['coupon_used']) ? $row_coupons['coupon_used'] : 0;

            //             if($coupon_limit > 0 && $coupon_limit <= $coupon_used){
            //                 echo "<script>alert('Your Coupon Code Has Been Expired')</script>";
            //             } else {
            //                 $get_cart = "SELECT * FROM cart WHERE p_id='$coupon_pro' AND ip_add='$ip_add'";
            //                 $run_cart = mysqli_query($con, $get_cart);
                            
            //                 if($run_cart && mysqli_num_rows($run_cart) >= 1){
            //                     $add_used = "UPDATE coupons SET coupon_used=coupon_used+1 WHERE coupon_code='$code'";
            //                     $run_used = mysqli_query($con, $add_used);
            //                     $update_cart = "UPDATE cart SET p_price='$coupon_price' WHERE p_id='$coupon_pro' AND ip_add='$ip_add'";
            //                     $run_update = mysqli_query($con, $update_cart);
            //                     echo "<script>alert('Your Coupon Code Has Been Applied')</script>";
            //                     echo "<script>window.open('cart.php','_self')</script>";
            //                 } else {
            //                     echo "<script>alert('Product Does Not Exist In Cart')</script>";
            //                 }
            //             }
            //         } else {
            //             echo "<script>alert('Your Coupon Code Is Not Valid')</script>";
            //         }
            //     }
            // }

            // Handle cart updates and removals
            if(isset($_POST['update'])){
                // Handle quantity updates
                if(isset($_POST['quantity']) && is_array($_POST['quantity'])) {
                    foreach($_POST['quantity'] as $cart_id => $new_qty) {
                        $cart_id = (int)$cart_id;
                        $new_qty = (int)$new_qty;
                        
                        if($new_qty > 0 && $new_qty <= 10) {
                            // Check if cart_id column exists
                            $check_column = mysqli_query($con, "SHOW COLUMNS FROM cart LIKE 'cart_id'");
                            
                            if($check_column && mysqli_num_rows($check_column) > 0) {
                                // Use cart_id column
                                $update_qty = "UPDATE cart SET qty='$new_qty' WHERE cart_id='$cart_id'";
                            } else {
                                // Fallback to p_id column
                                $update_qty = "UPDATE cart SET qty='$new_qty' WHERE p_id='$cart_id' AND ip_add='$ip_add'";
                            }
                            
                            mysqli_query($con, $update_qty);
                        }
                    }
                }
                
                // Handle item removals
                if(isset($_POST['remove']) && is_array($_POST['remove'])) {
                    foreach($_POST['remove'] as $cart_id) {
                        $cart_id = (int)$cart_id;
                        
                        // Check if cart_id column exists
                        $check_column = mysqli_query($con, "SHOW COLUMNS FROM cart LIKE 'cart_id'");
                        
                        if($check_column && mysqli_num_rows($check_column) > 0) {
                            // Use cart_id column
                            $delete_product = "DELETE FROM cart WHERE cart_id='$cart_id'";
                        } else {
                            // Fallback to p_id column
                            $delete_product = "DELETE FROM cart WHERE p_id='$cart_id' AND ip_add='$ip_add'";
                        }
                        
                        mysqli_query($con, $delete_product);
                    }
                }
                
                echo "<script>alert('Cart updated successfully!')</script>";
                echo "<script>window.open('cart.php','_self')</script>";
            }

            // Handle single item removal via delete icon
            if(isset($_GET['remove_item'])){
                $remove_cart_id = (int)$_GET['remove_item'];
                $ip_add = getRealUserIp();
                
                // Handle localhost IP variations
                if($ip_add == '' || $ip_add == '127.0.0.1' || $ip_add == '::1') {
                    $check_ip_query = "SELECT DISTINCT ip_add FROM cart LIMIT 1";
                    $check_ip_result = mysqli_query($con, $check_ip_query);
                    if($check_ip_result && mysqli_num_rows($check_ip_result) > 0) {
                        $ip_row = mysqli_fetch_array($check_ip_result);
                        $ip_add = $ip_row['ip_add'];
                    }
                }
                
                if($remove_cart_id > 0) {
                    // Check if cart_id column exists
                    $check_column = mysqli_query($con, "SHOW COLUMNS FROM cart LIKE 'cart_id'");
                    
                    if($check_column && mysqli_num_rows($check_column) > 0) {
                        // Use cart_id column
                        $delete_product = "DELETE FROM cart WHERE cart_id='$remove_cart_id'";
                    } else {
                        // Fallback to p_id column with IP
                        $delete_product = "DELETE FROM cart WHERE p_id='$remove_cart_id' AND ip_add='$ip_add'";
                    }
                    
                    $run_delete = mysqli_query($con, $delete_product);
                    
                    if($run_delete && mysqli_affected_rows($con) > 0) {
                        echo "<script>alert('Item removed from cart successfully!')</script>";
                    } else {
                        echo "<script>alert('Error removing item from cart!')</script>";
                    }
                    
                    echo "<script>window.location.href='cart.php'</script>";
                }
            }

            // Handle clear cart
            if(isset($_POST['clear_cart'])){
                $ip_add = getRealUserIp();
                $clear_cart = "DELETE FROM cart WHERE ip_add='$ip_add'";
                $run_clear = mysqli_query($con, $clear_cart);
                
                if($run_clear) {
                    echo "<script>alert('All items have been removed from your cart!')</script>";
                    echo "<script>window.open('cart.php','_self')</script>";
                } else {
                    echo "<script>alert('Error clearing cart: " . mysqli_error($con) . "')</script>";
                }
            }
            ?>

            <?php if($count > 0): ?>
            
            <!-- Recommended Products Section -->
            <!-- <div class="row" >
                <div class="col-md-12">
                    <div class="box">
                        <div class="box-header text-center" >
                            <h3 >You may like these Products</h3>
                            <p >Recommended items based on your cart</p>
                        </div>
                        
                        <div class="row" >
                            <?php
                            $get_products = "SELECT * FROM products ORDER BY RAND() LIMIT 0,3";
                            $run_products = mysqli_query($con, $get_products);
                            
                            if($run_products) {
                                while($row_products = mysqli_fetch_array($run_products)){
                                    $pro_id = $row_products['product_id'];
                                    $pro_title = $row_products['product_title'];
                                    $pro_price = $row_products['product_price'];
                                    $pro_img1 = $row_products['product_img1'];
                                    $pro_label = isset($row_products['product_label']) ? $row_products['product_label'] : '';
                                    $manufacturer_id = isset($row_products['manufacturer_id']) ? $row_products['manufacturer_id'] : 0;
                                    $pro_url = isset($row_products['product_url']) ? $row_products['product_url'] : "details.php?pro_id=$pro_id";
                                    
                                    // Get manufacturer name
                                    $manufacturer_name = "Unknown";
                                    if($manufacturer_id > 0) {
                                        $get_manufacturer = "SELECT * FROM manufacturers WHERE manufacturer_id='$manufacturer_id'";
                                        $run_manufacturer = mysqli_query($con, $get_manufacturer);
                                        if($run_manufacturer && mysqli_num_rows($run_manufacturer) > 0) {
                                            $row_manufacturer = mysqli_fetch_array($run_manufacturer);
                                            $manufacturer_name = $row_manufacturer['manufacturer_title'];
                                        }
                                    }
                                    
                                    $pro_psp_price = isset($row_products['product_psp_price']) ? $row_products['product_psp_price'] : 0;

                                    if($pro_label == "Sale" || $pro_label == "Gift"){
                                        $product_price = "<del> Rs " . number_format($pro_price, 2) . "</del>";
                                        $product_psp_price = "| Rs " . number_format($pro_psp_price, 2);
                                    } else {
                                        $product_psp_price = "";
                                        $product_price = "Rs " . number_format($pro_price, 2);
                                    }

                                    if($pro_label != ""){
                                        $product_label = "
                                        <a class='label sale' href='#' style='color:black;'>
                                            <div class='thelabel'>$pro_label</div>
                                            <div class='label-background'></div>
                                        </a>";
                                    } else {
                                        $product_label = "";
                                    }

                                    echo "
                                    <div class='col-md-4 col-sm-6 center-responsive'>
                                        <div class='product recommended-product'>
                                            <a href='$pro_url'>
                                                <img src='admin_area/product_images/$pro_img1' class='img-responsive'>
                                            </a>
                                            <div class='text'>
                                                <h3><a href='$pro_url'>$pro_title</a></h3>
                                                <p class='price'>$product_price $product_psp_price</p>
                                                <p class='buttons'>
                                                    <a href='$pro_url' class='btn btn-default'>View Details</a>
                                                    <a href='$pro_url' class='btn btn-danger'>
                                                        <i class='fa fa-shopping-cart'></i> Add To Cart
                                                    </a>
                                                </p>
                                            </div>
                                            $product_label
                                        </div>
                                    </div>";
                                }
                            }
                            ?>
                        </div>
                    </div>
                </div>
            </div> -->
            <?php endif; ?>
        </div>

        <div class="col-md-3">
            <div class="box" id="order-summary">
                <div class="box-header">
                    <h3>Order Summary</h3>
                </div>
                <p class="text-muted">
                    Shipping and additional costs are calculated based on the values you have entered.
                </p>
                <div class="table-responsive">
                    <table class="table">
                        <tbody>
                            <tr>
                                <td>Order Subtotal</td>
                                <th>Rs <?php echo isset($total) ? number_format($total, 2) : '0.00'; ?></th>
                            </tr>
                            <tr>
                                <td>Shipping and handling</td>
                                <th>
                                    <?php 
                                    $shipping_cost = 0;
                                    if(isset($total) && $total > 0) {
                                        if($total < 50000) {
                                            $shipping_cost = 250; // Rs 250 for orders under Rs 50,000
                                        } else {
                                            $shipping_cost = 0; // Free shipping for orders Rs 50,000 and above
                                        }
                                    }
                                    echo "Rs " . number_format($shipping_cost, 2);
                                    ?>
                                </th>
                            </tr>
                            <!-- Tax section commented out
                            <tr>
                                <td>Tax (GST 17%)</td>
                                <th>
                                    <?php 
                                    // $tax_rate = 0.17; // 17% GST
                                    // $tax_amount = isset($total) ? ($total * $tax_rate) : 0;
                                    // echo "Rs " . number_format($tax_amount, 2);
                                    ?>
                                </th>
                            </tr>
                            -->
                            <tr class="total" style="border-top: 2px solid #ff6b35; font-weight: bold; font-size: 16px;">
                                <td><strong>Total</strong></td>
                                <th>
                                    <?php 
                                    $final_total = isset($total) ? ($total + $shipping_cost) : 0; // Removed tax from calculation
                                    echo "Rs " . number_format($final_total, 2);
                                    ?>
                                </th>
                            </tr>
                        </tbody>
                    </table>
                </div>
                
                <?php if(isset($total) && $total > 0 && $total < 50000): ?>
                <div class="alert alert-info" style="margin-top: 15px; font-size: 12px;">
                    <i class="fa fa-truck"></i> 
                    <strong>Free Shipping:</strong> Add Rs <?php echo number_format(50000 - $total, 2); ?> more to your cart to get free shipping!
                </div>
                <?php elseif(isset($total) && $total >= 50000): ?>
                <div class="alert alert-success" style="margin-top: 15px; font-size: 12px;">
                    <i class="fa fa-check-circle"></i> 
                    <strong>Congratulations!</strong> You qualify for free shipping!
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include("includes/footer.php"); ?>

<script src="js/jquery.min.js"></script>
<script src="js/bootstrap.min.js"></script>
<script>
$(document).ready(function(){
    // Handle quantity changes
    $(document).on('change', '.quantity', function(){
        var cartId = $(this).data("cart-id");
        var quantity = $(this).val();
        
        if(quantity < 1) {
            $(this).val(1);
            quantity = 1;
        }
        
        if(quantity > 10) {
            $(this).val(10);
            quantity = 10;
        }
    });
    
    // Add hover effects for delete buttons
    $('.btn-danger').hover(
        function() {
            $(this).css('transform', 'scale(1.1)');
        },
        function() {
            $(this).css('transform', 'scale(1)');
        }
    );
    
    // Add visual feedback when hovering over product rows
    $('tr').hover(
        function() {
            if($(this).find('.btn-danger').length > 0) {
                $(this).css('background-color', '#f8f8f8');
            }
        },
        function() {
            $(this).css('background-color', '');
        }
    );
});
</script>
</body>
</html>
