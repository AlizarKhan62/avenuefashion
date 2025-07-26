<?php
session_start();
include("includes/db.php");
include("includes/header.php");
include("functions/functions.php");
include("includes/main.php");

$product_id = @$_GET['pro_id'];
$get_product = "select * from products where product_url='$product_id'";
$run_product = mysqli_query($con,$get_product);

// Check if product exists
if(!$run_product || mysqli_num_rows($run_product) == 0){
    echo "<script> window.open('index.php','_self') </script>";
    exit;
}

$row_product = mysqli_fetch_array($run_product);
$p_cat_id = $row_product['p_cat_id'];
$pro_id = $row_product['product_id'];
$pro_title = $row_product['product_title'];
$pro_price = $row_product['product_price'];
$pro_desc = $row_product['product_desc'];
$pro_img1 = $row_product['product_img1'];
$pro_img2 = $row_product['product_img2'];
$pro_img3 = $row_product['product_img3'];
$pro_label = $row_product['product_label'];
$pro_psp_price = $row_product['product_psp_price'];
$pro_features = $row_product['product_features'];
$pro_video = $row_product['product_video'];
$status = $row_product['status'];
$pro_url = $row_product['product_url'];
$has_variants = isset($row_product['has_variants']) ? $row_product['has_variants'] : 0;

// Get product category info
$get_p_cat = "select * from product_categories where p_cat_id='$p_cat_id'";
$run_p_cat = mysqli_query($con,$get_p_cat);

if($run_p_cat && mysqli_num_rows($run_p_cat) > 0) {
    $row_p_cat = mysqli_fetch_array($run_p_cat);
    $p_cat_title = $row_p_cat['p_cat_title'];
    $sizing_type = isset($row_p_cat['sizing_type']) ? $row_p_cat['sizing_type'] : 'clothing';
} else {
    $p_cat_title = 'Unknown Category';
    $sizing_type = 'clothing';
}

// Get color variants
$color_variants = array();
if($has_variants) {
    $get_variants = "select * from product_variants where product_id='$pro_id'";
    $run_variants = mysqli_query($con, $get_variants);
    if($run_variants) {
        while($row_variant = mysqli_fetch_array($run_variants)) {
            $variant_images = json_decode($row_variant['variant_images'], true);
            if(!$variant_images) $variant_images = array();
            if(empty($variant_images) && !empty($row_variant['variant_image'])) {
                $variant_images[] = $row_variant['variant_image'];
            }
            $row_variant['images_array'] = $variant_images;
            $color_variants[] = $row_variant;
        }
    }
}

// Get available sizes
$available_sizes = array();
$get_sizes = "SELECT s.size_id, s.size_name, s.size_type, s.size_order, ps.stock_quantity 
              FROM sizes s 
              INNER JOIN product_sizes ps ON s.size_id = ps.size_id 
              WHERE ps.product_id = '$pro_id' AND ps.stock_quantity > 0
              ORDER BY s.size_type, s.size_order, s.size_name";

$run_sizes = mysqli_query($con, $get_sizes);
if($run_sizes) {
    while($row_size = mysqli_fetch_array($run_sizes)) {
        $available_sizes[] = $row_size;
    }
}

// Calculate discount percentage
$discount_percentage = 0;
if($pro_psp_price > 0 && $pro_price > $pro_psp_price) {
    $discount_percentage = round((($pro_price - $pro_psp_price) / $pro_price) * 100);
}

// Handle add to cart
if(isset($_POST['add_cart'])){
    $ip_add = getRealUserIp();
    $p_id = $pro_id;
    $product_qty = isset($_POST['product_qty']) ? (int)$_POST['product_qty'] : 1;
    $product_size = isset($_POST['product_size']) ? mysqli_real_escape_string($con, $_POST['product_size']) : '';
    $color_variant = isset($_POST['color_variant']) ? mysqli_real_escape_string($con, $_POST['color_variant']) : '';
    $selected_variant_image = isset($_POST['selected_variant_image']) ? mysqli_real_escape_string($con, $_POST['selected_variant_image']) : '';
    
    // Basic validation
    if($product_qty <= 0) {
        $product_qty = 1;
    }
    
    // Use the selected variant image if available
    $variant_image = '';
    if(!empty($selected_variant_image)) {
        $variant_image = $selected_variant_image;
    } 
    // Fallback to getting the variant image from database if not directly selected
    else if(!empty($color_variant)) {
        $get_variant_img = "SELECT variant_image, variant_images FROM product_variants WHERE product_id='$p_id' AND color_name='$color_variant'";
        $run_variant_img = mysqli_query($con, $get_variant_img);
        if($run_variant_img && mysqli_num_rows($run_variant_img) > 0) {
            $row_variant_img = mysqli_fetch_array($run_variant_img);
            $variant_images_array = json_decode($row_variant_img['variant_images'], true);
            if(!empty($variant_images_array)) {
                $variant_image = $variant_images_array[0];
            } elseif(!empty($row_variant_img['variant_image'])) {
                $variant_image = $row_variant_img['variant_image'];
            }
            $variant_image = "variant_" . $color_variant . "_" . $pro_id . "_0.jpg";
            
            if(empty($variant_image)) {
                $variant_images_array = json_decode($row_variant_img['variant_images'], true);
                if(!empty($variant_images_array)) {
                    $variant_image = $variant_images_array[0];
                }
            }
        }
    }
    
    // Determine the price to use
    $product_price = ($pro_label == "Sale" || $pro_label == "Gift") ? $pro_psp_price : $pro_price;
    
    // Check if the product already exists in cart with the same options
    $check_product = "SELECT * FROM cart WHERE ip_add='$ip_add' AND p_id='$p_id'";
    
    // Add color and size to the check for separate cart items
    if(!empty($color_variant)) {
        $check_product .= " AND color_variant='$color_variant'";
    } else {
        $check_product .= " AND (color_variant IS NULL OR color_variant='')";
    }
    
    if(!empty($product_size)) {
        $check_product .= " AND size='$product_size'";
    } else {
        $check_product .= " AND (size IS NULL OR size='')";
    }
    
    $run_check = mysqli_query($con, $check_product);
    
    if($run_check && mysqli_num_rows($run_check) > 0){
        // Update existing item
        $row_existing = mysqli_fetch_array($run_check);
        $existing_qty = $row_existing['qty'];
        $new_qty = $existing_qty + $product_qty;
        
        $update_cart = "UPDATE cart SET qty='$new_qty'";
        
        // Update the variant image if we have a new one
        if(!empty($variant_image)) {
            $update_cart .= ", variant_image='$variant_image'";
        }
        
        $update_cart .= " WHERE ip_add='$ip_add' AND p_id='$p_id'";
        
        if(!empty($color_variant)) {
            $update_cart .= " AND color_variant='$color_variant'";
        } else {
            $update_cart .= " AND (color_variant IS NULL OR color_variant='')";
        }
        if(!empty($product_size)) {
            $update_cart .= " AND size='$product_size'";
        } else {
            $update_cart .= " AND (size IS NULL OR size='')";
        }
        
        $run_update = mysqli_query($con, $update_cart);
        
        if($run_update) {
            echo "<script>alert('Product quantity updated in cart!');</script>";
            echo "<script>window.location.href='cart.php';</script>";
            exit;
        } else {
            echo "<script>alert('Error updating cart: " . mysqli_error($con) . "');</script>";
        }
    } else {
        // Insert new item
        $insert_cart = "INSERT INTO cart (p_id, ip_add, qty, p_price, size, color_variant, variant_image) 
                       VALUES ('$p_id', '$ip_add', '$product_qty', '$product_price', '$product_size', '$color_variant', '$variant_image')";
        
        $run_insert = mysqli_query($con, $insert_cart);
        
        if($run_insert) {
            echo "<script>alert('Product added to cart successfully!');</script>";
            // Stay on same page, don't redirect to cart
        } else {
            echo "<script>alert('Error adding product to cart: " . mysqli_error($con) . "');</script>";
        }
    }
}

// Handle add to wishlist
if(isset($_POST['add_wishlist'])){
    if(!isset($_SESSION['customer_email'])){
        // Store the current page URL to return after login
        $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
        echo "<script>alert('You Must Login To Add Product In Wishlist')</script>";
        echo "<script>window.open('checkout.php','_self')</script>";
        exit();
    } else {
        $customer_session = $_SESSION['customer_email'];
        
        $customer_session = $_SESSION['customer_email'];
        
        // Check if already in wishlist using customer email
        $check_stmt = mysqli_prepare($con, "SELECT * FROM wishlist WHERE customer_email=? AND product_id=?");
        if($check_stmt) {
            mysqli_stmt_bind_param($check_stmt, "si", $customer_session, $pro_id);
            mysqli_stmt_execute($check_stmt);
            $check_result = mysqli_stmt_get_result($check_stmt);

            if(mysqli_num_rows($check_result) >= 1){
                echo "<script>alert('This Product Has Been already Added In Wishlist')</script>";
                // Stay on same page, don't redirect
            } else {
                // Insert into wishlist using customer email
                $insert_stmt = mysqli_prepare($con, "INSERT INTO wishlist (customer_email, product_id) VALUES (?, ?)");
                if($insert_stmt) {
                    mysqli_stmt_bind_param($insert_stmt, "si", $customer_session, $pro_id);
                    
                    if(mysqli_stmt_execute($insert_stmt)){
                        echo "<script>alert('Product Has Been Added To Wishlist Successfully!')</script>";
                        // Stay on same page, don't redirect
                    } else {
                        echo "<script>alert('Error adding to wishlist: " . mysqli_error($con) . "')</script>";
                    }
                    mysqli_stmt_close($insert_stmt);
                } else {
                    echo "<script>alert('Error preparing insert statement: " . mysqli_error($con) . "')</script>";
                }
            }
            mysqli_stmt_close($check_stmt);
        } else {
            echo "<script>alert('Error preparing check statement: " . mysqli_error($con) . "')</script>";
        }
    }
}
?>

<main>
    <!-- HERO -->
    <div class="nero">
      <div class="nero__heading">
        <span class="nero__bold">Product </span>View
      </div>
      <p class="nero__text">
      </p>
    </div>
  </main>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pro_title; ?> - Product Details</title>
    <link rel="stylesheet" href="styles/bootstrap.min.css">
    <link rel="stylesheet" href="font-awesome/css/font-awesome.min.css">
    <style>
        /* INLINE STYLES TO OVERRIDE EVERYTHING */
        * {
            box-sizing: border-box !important;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif !important;
            line-height: 1.6 !important;
            color: #333 !important;
            margin: 0 !important;
            padding: 0 !important;
        }

        .modern-product-container {
            max-width: 1200px !important;
            margin: 30px auto !important;
            padding: 40px !important;
            background: white !important;
            border-radius: 20px !important;
            box-shadow: 0 8px 40px rgba(0, 0, 0, 0.12) !important;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif !important;
        }

        .modern-breadcrumb {
            background: #f8f9fa !important;
            padding: 20px 0 !important;
            margin-bottom: 0 !important;
        }

        .modern-breadcrumb a {
            color: #6c757d !important;
            text-decoration: none !important;
            font-weight: 500 !important;
        }

        .modern-breadcrumb a:hover {
            color: #ff6b35 !important;
        }

        .modern-product-row {
            display: flex !important;
            flex-wrap: wrap !important;
            margin: 0 !important;
        }

        .modern-image-col {
            flex: 0 0 45% !important;
            max-width: 45% !important;
        }

        .modern-info-col {
            flex: 0 0 45% !important;
            max-width: 45% !important;
        }

        .modern-main-image {
            width: 100% !important;
            height: 600px !important;
            object-fit: cover !important;
            border-radius: 20px !important;
            border: 3px solid #f0f0f0 !important;
            transition: all 0.3s ease !important;
            margin-bottom: 25px !important;
        }

        .modern-main-image:hover {
            transform: scale(1.02) !important;
            border-color: #ff6b35 !important;
        }

        .modern-thumbnails {
            display: flex !important;
            gap: 15px !important;
            overflow-x: auto !important;
            padding: 10px 0 !important;
        }

        .modern-thumbnail {
            width: 90px !important;
            height: 90px !important;
            object-fit: cover !important;
            border-radius: 12px !important;
            border: 3px solid transparent !important;
            cursor: pointer !important;
            transition: all 0.3s ease !important;
            flex-shrink: 0 !important;
        }

        .modern-thumbnail:hover,
        .modern-thumbnail.active {
            border-color: #ff6b35 !important;
            transform: scale(1.1) !important;
            box-shadow: 0 8px 20px rgba(255, 107, 53, 0.4) !important;
        }

        .modern-product-title {
            font-size: 24px !important;
            font-weight: 700 !important;
            color: #2c3e50 !important;
            margin-bottom: 15px !important;
            line-height: 1.2 !important;
        }

        .modern-rating {
            display: flex !important;
            align-items: center !important;
            gap: 15px !important;
            margin-bottom: 30px !important;
        }

        .modern-stars {
            color: #ffa500 !important;
            font-size: 20px !important;
            display: flex !important;
            gap: 3px !important;
        }

        .modern-rating-text {
            color: #7f8c8d !important;
            font-size: 16px !important;
            font-weight: 600 !important;
        }

        .modern-price-box {
            margin-bottom: 25px !important;
            padding: 20px !important;
            background: linear-gradient(135deg, #fff5f2 0%, #ffe8e1 100%) !important;
            border-radius: 15px !important;
            border-left: 6px solid #ff6b35 !important;
        }

        .modern-current-price {
            font-size: 28px !important;
            font-weight: 800 !important;
            color: #ff6b35 !important;
            display: inline-block !important;
            margin-right: 15px !important;
        }

        .modern-original-price {
            font-size: 18px !important;
            color: #95a5a6 !important;
            text-decoration: line-through !important;
            margin-right: 15px !important;
            vertical-align: top !important;
            margin-top: 8px !important;
            display: inline-block !important;
        }

        .modern-discount-badge {
            background: linear-gradient(135deg, #ff6b35 0%, #e55a2b 100%) !important;
            color: white !important;
            padding: 8px 15px !important;
            border-radius: 25px !important;
            font-size: 12px !important;
            font-weight: 700 !important;
            box-shadow: 0 4px 12px rgba(255, 107, 53, 0.4) !important;
            display: inline-block !important;
            vertical-align: top !important;
            margin-top: 8px !important;
        }

        .modern-variant-box {
            margin-bottom: 25px !important;
            padding: 20px !important;
            background: #fafbfc !important;
            border-radius: 15px !important;
            border: 2px solid #ecf0f1 !important;
            transition: all 0.3s ease !important;
        }

        .modern-variant-box:hover {
            border-color: #ff6b35 !important;
            background: #fff !important;
            box-shadow: 0 4px 15px rgba(255, 107, 53, 0.1) !important;
        }

        .modern-variant-label {
            font-weight: 600 !important;
            margin-bottom: 15px !important;
            color: #2c3e50 !important;
            font-size: 16px !important;
        }

        .modern-color-options {
            display: flex !important;
            gap: 15px !important;
            flex-wrap: wrap !important;
        }

        .modern-color-option {
            width: 80px !important;
            height: 80px !important;
            border-radius: 12px !important;
            border: 3px solid transparent !important;
            cursor: pointer !important;
            transition: all 0.3s ease !important;
            position: relative !important;
            overflow: hidden !important;
            background-size: cover !important;
            background-position: center !important;
            background-repeat: no-repeat !important;
        }

        .modern-color-option:hover {
            border-color: #34495e !important;
            transform: scale(1.1) !important;
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.2) !important;
        }

        .modern-color-option.selected {
            border-color: #ff6b35 !important;
            transform: scale(1.1) !important;
            box-shadow: 0 6px 20px rgba(255, 107, 53, 0.4) !important;
        }

        .modern-color-option.selected::after {
            content: "✓" !important;
            position: absolute !important;
            top: 50% !important;
            left: 50% !important;
            transform: translate(-50%, -50%) !important;
            color: white !important;
            font-weight: bold !important;
            font-size: 16px !important;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.8) !important;
            background: rgba(0, 0, 0, 0.6) !important;
            border-radius: 50% !important;
            width: 25px !important;
            height: 25px !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
        }

        .modern-size-options {
            display: flex !important;
            gap: 12px !important;
            flex-wrap: wrap !important;
        }

        .modern-size-option {
            padding: 12px 18px !important;
            border: 2px solid #bdc3c7 !important;
            border-radius: 8px !important;
            cursor: pointer !important;
            transition: all 0.3s ease !important;
            font-weight: 600 !important;
            min-width: 50px !important;
            text-align: center !important;
            background: white !important;
            font-size: 14px !important;
            color: #2c3e50 !important;
        }

        .modern-size-option:hover {
            border-color: #ff6b35 !important;
            background: #fff5f2 !important;
            transform: translateY(-2px) !important;
            box-shadow: 0 4px 12px rgba(255, 107, 53, 0.2) !important;
        }

        .modern-size-option.selected {
            border-color: #ff6b35 !important;
            background: #ff6b35 !important;
            color: white !important;
            transform: translateY(-2px) !important;
            box-shadow: 0 4px 12px rgba(255, 107, 53, 0.4) !important;
        }

        .modern-size-option.unavailable {
            background: #f5f5f5 !important;
            color: #999 !important;
            cursor: not-allowed !important;
            text-decoration: line-through !important;
            opacity: 0.6 !important;
        }

        .modern-quantity-box {
            display: flex !important;
            align-items: center !important;
            gap: 15px !important;
            background: white !important;
            padding: 12px !important;
            border-radius: 10px !important;
            border: 2px solid #ecf0f1 !important;
            width: fit-content !important;
        }

        .modern-quantity-btn {
            width: 40px !important;
            height: 40px !important;
            border: 2px solid #bdc3c7 !important;
            background: white !important;
            cursor: pointer !important;
            border-radius: 8px !important;
            font-size: 18px !important;
            font-weight: 700 !important;
            transition: all 0.3s ease !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            color: #2c3e50 !important;
        }

        .modern-quantity-btn:hover {
            background: #ff6b35 !important;
            color: white !important;
            border-color: #ff6b35 !important;
            transform: scale(1.1) !important;
        }

        .modern-quantity-input {
            width: 60px !important;
            height: 40px !important;
            text-align: center !important;
            border: 2px solid #bdc3c7 !important;
            border-radius: 8px !important;
            font-size: 16px !important;
            font-weight: 600 !important;
            color: #2c3e50 !important;
        }

        .modern-action-buttons {
            display: flex !important;
            gap: 15px !important;
            margin: 30px 0 !important;
        }

        .modern-add-cart {
            flex: 1 !important;
            background: linear-gradient(135deg, #ff6b35 0%, #e55a2b 100%) !important;
            color: white !important;
            border: none !important;
            padding: 18px 30px !important;
            border-radius: 12px !important;
            font-size: 16px !important;
            font-weight: 700 !important;
            cursor: pointer !important;
            transition: all 0.3s ease !important;
            box-shadow: 0 6px 20px rgba(255, 107, 53, 0.4) !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            gap: 10px !important;
        }

        .modern-add-cart:hover {
            transform: translateY(-3px) !important;
            box-shadow: 0 8px 25px rgba(255, 107, 53, 0.6) !important;
        }

        .modern-wishlist {
            padding: 18px 25px !important;
            border: 3px solid #ff6b35 !important;
            background: white !important;
            color: #ff6b35 !important;
            border-radius: 12px !important;
            cursor: pointer !important;
            transition: all 0.3s ease !important;
            font-size: 16px !important;
            font-weight: 600 !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
        }

        .modern-wishlist:hover {
            background: #ff6b35 !important;
            color: white !important;
            transform: translateY(-3px) !important;
            box-shadow: 0 8px 25px rgba(255, 107, 53, 0.4) !important;
        }

        .modern-tabs {
            margin-top: 40px !important;
            padding-top: 30px !important;
            border-top: 2px solid #ecf0f1 !important;
            width: 100% !important;
            grid-column: 1 / -1 !important;
        }

        .modern-tab-buttons {
            display: flex !important;
            gap: 25px !important;
            margin-bottom: 25px !important;
            border-bottom: 2px solid #ecf0f1 !important;
        }

        .modern-tab-btn {
            padding: 15px 25px !important;
            border: none !important;
            background: none !important;
            cursor: pointer !important;
            font-weight: 600 !important;
            color: #7f8c8d !important;
            border-bottom: 4px solid transparent !important;
            transition: all 0.3s ease !important;
            font-size: 16px !important;
        }

        .modern-tab-btn:hover {
            color: #ff6b35 !important;
        }

        .modern-tab-btn.active {
            color: #ff6b35 !important;
            border-bottom-color: #ff6b35 !important;
            font-weight: 700 !important;
        }

        .modern-tab-content {
            padding: 25px 0 !important;
            line-height: 1.7 !important;
            color: #2c3e50 !important;
            font-size: 16px !important;
        }

        .modern-tab-pane {
            display: none !important;
        }

        .modern-tab-pane.active {
            display: block !important;
            animation: modernFadeIn 0.4s ease-in !important;
        }

        @keyframes modernFadeIn {
            from {
                opacity: 0 !important;
                transform: translateY(15px) !important;
            }
            to {
                opacity: 1 !important;
                transform: translateY(0) !important;
            }
        }

        .modern-stock-info {
            background: linear-gradient(135deg, #e8f5e8 0%, #d4edda 100%) !important;
            padding: 15px 20px !important;
            border-radius: 10px !important;
            margin-bottom: 20px !important;
            border-left: 4px solid #28a745 !important;
            font-weight: 600 !important;
            font-size: 14px !important;
            color: #155724 !important;
        }

        .modern-stock-info.low-stock {
            background: linear-gradient(135deg, #fff3cd 0%, #ffeaa7 100%) !important;
            border-left-color: #ffc107 !important;
            color: #856404 !important;
        }

        .modern-stock-info.out-of-stock {
            background: linear-gradient(135deg, #f8d7da 0%, #f5c6cb 100%) !important;
            border-left-color: #dc3545 !important;
            color: #721c24 !important;
        }

        .modern-no-sizes {
            padding: 15px !important;
            text-align: center !important;
            background: #f8f9fa !important;
            border-radius: 8px !important;
            color: #7f8c8d !important;
            font-style: italic !important;
        }

        .modern-full-width-tabs {
            width: 100% !important;
            margin-top: 40px !important;
            grid-column: 1 / -1 !important;
        }

        .modern-product-layout {
            display: flex !important;
            flex-wrap: wrap !important;
            gap: 40px !important;
            margin-bottom: 40px !important;
        }

        .modern-image-col {
            flex: 0 0 45% !important;
            max-width: 45% !important;
        }

        .modern-info-col {
            flex: 0 0 45% !important;
            max-width: 45% !important;
        }

        /* Mobile Responsive */
        @media (max-width: 768px) {
            .modern-product-container {
                margin: 15px !important;
                padding: 20px !important;
            }

            .modern-product-layout {
                grid-template-columns: 1fr !important;
                gap: 25px !important;
            }

            .modern-product-title {
                font-size: 20px !important;
            }

            .modern-current-price {
                font-size: 24px !important;
            }

            .modern-main-image {
                height: 300px !important;
            }

            .modern-action-buttons {
                flex-direction: column !important;
            }

            .modern-color-option {
                width: 60px !important;
                height: 60px !important;
            }
        }
    </style>
</head>
<body>

<!-- Breadcrumb -->
<div class="modern-breadcrumb">
    <div class="container">
        <a href="index.php">Home</a> > 
        <a href="shop.php">Shop</a> > 
        <a href="shop.php?p_cat=<?php echo $p_cat_id; ?>"><?php echo $p_cat_title; ?></a> > 
        <span><?php echo $pro_title; ?></span>
    </div>
</div>

<!-- Product Details Container -->
<div class="modern-product-container">
    <div class="modern-product-layout">
        <!-- Product Images Column -->
        <div class="modern-image-col">
            <img id="modernMainImage" src="admin_area/product_images/<?php echo $pro_img1; ?>" alt="<?php echo $pro_title; ?>" class="modern-main-image">
            
            <div class="modern-thumbnails">
                <img src="admin_area/product_images/<?php echo $pro_img1; ?>" alt="Image 1" class="modern-thumbnail active" onclick="changeModernImage(this)">
                <?php if($pro_img2): ?>
                <img src="admin_area/product_images/<?php echo $pro_img2; ?>" alt="Image 2" class="modern-thumbnail" onclick="changeModernImage(this)">
                <?php endif; ?>
                <?php if($pro_img3): ?>
                <img src="admin_area/product_images/<?php echo $pro_img3; ?>" alt="Image 3" class="modern-thumbnail" onclick="changeModernImage(this)">
                <?php endif; ?>
                
                <!-- Color variant images -->
                <?php foreach($color_variants as $variant): ?>
                    <?php foreach($variant['images_array'] as $img): ?>
                        <img src="admin_area/product_images/<?php echo $img; ?>" alt="<?php echo $variant['color_name']; ?>" class="modern-thumbnail variant-image" data-color="<?php echo $variant['color_name']; ?>" onclick="changeModernImage(this)" style="display: none;">
                    <?php endforeach; ?>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Product Info Column -->
        <div class="modern-info-col">
            <h1 class="modern-product-title"><?php echo $pro_title; ?></h1>

            <div class="modern-price-box">
                <?php if($pro_psp_price > 0 && $pro_price > $pro_psp_price): ?>
                    <span class="modern-current-price">Rs <?php echo number_format($pro_psp_price); ?></span>
                    <span class="modern-original-price">Rs <?php echo number_format($pro_price); ?></span>
                    <span class="modern-discount-badge"><?php echo $discount_percentage; ?>% OFF</span>
                <?php else: ?>
                    <span class="modern-current-price">Rs <?php echo number_format($pro_price); ?></span>
                <?php endif; ?>
            </div>

            <!-- SIMPLIFIED FORM - NO JAVASCRIPT VALIDATION -->
            <form method="post" action="">
                <!-- Color Variants -->
                <?php if(!empty($color_variants)): ?>
                <div class="modern-variant-box">
                    <div class="modern-variant-label">Color: <span id="modernSelectedColor">Select a color</span></div>
                    <div class="modern-color-options">
                        <?php foreach($color_variants as $variant): ?>
                        <?php 
                        $variant_display_image = '';
                        if(!empty($variant['images_array'])) {
                            $variant_display_image = $variant['images_array'][0];
                        } elseif(!empty($variant['variant_image'])) {
                            $variant_display_image = $variant['variant_image'];
                        }
                        ?>
                        <div class="modern-color-option" 
                             style="background-image: url('admin_area/product_images/<?php echo $variant_display_image; ?>');"
                             data-color="<?php echo $variant['color_name']; ?>"
                             data-stock="<?php echo $variant['stock_quantity']; ?>"
                             onclick="selectModernColor(this)">
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <input type="hidden" name="color_variant" id="modernColorInput">
                </div>
                <?php endif; ?>

                <!-- Size Selection -->
                <?php if(!empty($available_sizes)): ?>
                <div class="modern-variant-box">
                    <div class="modern-variant-label">Size: <span id="modernSelectedSize">Select a size</span></div>
                    <div class="modern-size-options">
                        <?php foreach($available_sizes as $size): ?>
                        <div class="modern-size-option <?php echo $size['stock_quantity'] == 0 ? 'unavailable' : ''; ?>" 
                             data-size="<?php echo $size['size_name']; ?>"
                             data-type="<?php echo $size['size_type']; ?>"
                             data-stock="<?php echo $size['stock_quantity']; ?>"
                             onclick="<?php echo $size['stock_quantity'] > 0 ? 'selectModernSize(this)' : ''; ?>">
                            <?php echo strtoupper($size['size_name']); ?>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <input type="hidden" name="product_size" id="modernSizeInput">
                </div>
                <?php endif; ?>

                <!-- Quantity -->
                <div class="modern-variant-box">
                    <div class="modern-variant-label">Quantity</div>
                    <div class="modern-quantity-box">
                        <button type="button" class="modern-quantity-btn" onclick="changeModernQuantity(-1)">-</button>
                        <input type="number" name="product_qty" id="modernQuantityInput" value="1" min="1" max="10" class="modern-quantity-input">
                        <button type="button" class="modern-quantity-btn" onclick="changeModernQuantity(1)">+</button>
                    </div>
                </div>

                <!-- Stock Info -->
                <div id="modernStockInfo" class="modern-stock-info" style="display: none;">
                    <span id="modernStockText"></span>
                </div>

                <!-- Action Buttons -->
                <div class="modern-action-buttons">
                    <button type="submit" name="add_cart" class="modern-add-cart">
                        <i class="fa fa-shopping-cart"></i> Add to Cart
                    </button>
                    <button type="submit" name="add_wishlist" class="modern-wishlist">
                        <i class="fa fa-heart"></i>
                    </button>
                </div>
                <input type="hidden" name="selected_variant_image" id="selectedVariantImage" value="<?php echo $pro_img1; ?>">
            </form>
        </div>
    </div>

    <!-- Product Features - Full Width -->
    <div class="modern-full-width-tabs">
        <div class="modern-tabs">
            <div class="modern-tab-buttons">
                <button type="button" class="modern-tab-btn active" onclick="showModernTab('description', this)">Description</button>
                <button type="button" class="modern-tab-btn" onclick="showModernTab('features', this)">Size Guide</button>
            </div>
            
            <div class="modern-tab-content">
                <div id="description" class="modern-tab-pane active">
                    <?php echo $pro_desc; ?>
                </div>
                <div id="features" class="modern-tab-pane">
                    <?php echo $pro_features; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Recommendation System -->
<div class="container" style="margin-top: 30px;">
    <div class="row" id="row same-height-row">
        <?php if($status == "product"): ?>
        
        <div class="col-md-3 col-sm-6">
            <div class="box same-height headline">
                <h3 class="text-center"> You may also like these Products: We provide you top 3 product items. </h3>
            </div>
        </div>

        <?php
        $get_products = "select * from products order by rand() LIMIT 0,3";
        $run_products = mysqli_query($con,$get_products);

        while($row_products = mysqli_fetch_array($run_products)) {
            $rec_pro_id = $row_products['product_id'];
            $rec_pro_title = $row_products['product_title'];
            $rec_pro_price = $row_products['product_price'];
            $rec_pro_img1 = $row_products['product_img1'];
            $rec_pro_label = $row_products['product_label'];
            $rec_manufacturer_id = $row_products['manufacturer_id'];

            $get_manufacturer = "select * from manufacturers where manufacturer_id='$rec_manufacturer_id'";
            $run_manufacturer = mysqli_query($con,$get_manufacturer);
            $row_manufacturer = mysqli_fetch_array($run_manufacturer);
            $rec_manufacturer_name = $row_manufacturer['manufacturer_title'];

            $rec_pro_psp_price = $row_products['product_psp_price'];
            $rec_pro_url = $row_products['product_url'];

            if($rec_pro_label == "Sale" or $rec_pro_label == "Gift"){
                $rec_product_price = "<del> Rs $rec_pro_price </del>";
                $rec_product_psp_price = "| Rs $rec_pro_psp_price";
            } else {
                $rec_product_psp_price = "";
                $rec_product_price = "Rs $rec_pro_price";
            }

            if($rec_pro_label == ""){
                $rec_product_label = "";
            } else {
                $rec_product_label = "
                <a class='label sale' href='#' style='color:black;'>
                    <div class='thelabel'>$rec_pro_label</div>
                    <div class='label-background'></div>
                </a>";
            }

            echo "
            <div class='col-md-3 col-sm-6 center-responsive'>
                <div class='product'>
                    <a href='$rec_pro_url'>
                        <img src='admin_area/product_images/$rec_pro_img1' class='img-responsive'>
                    </a>
                    <div class='text'>
                        
                        <h3><a href='$rec_pro_url'>$rec_pro_title</a></h3>
                        <p class='price'>$rec_product_price $rec_product_psp_price</p>
                        <p class='buttons'>
                            <a href='$rec_pro_url' class='btn btn-default'>View Details</a>
                            <a href='$rec_pro_url' class='btn btn-danger'>
                                <i class='fa fa-shopping-cart'></i> Add To Cart
                            </a>
                        </p>
                    </div>
                    $rec_product_label
                </div>
            </div>";
        }
        ?>

        
        
        <?php endif; ?>
    </div>
</div>

<script>
let modernSelectedColor = '';
let modernSelectedSize = '';
let modernCurrentStock = 0;

function changeModernImage(thumbnail) {
    const mainImage = document.getElementById('modernMainImage');
    mainImage.src = thumbnail.src;
    
    document.querySelectorAll('.modern-thumbnail').forEach(thumb => thumb.classList.remove('active'));
    thumbnail.classList.add('active');
    
    // Update the selected variant image for cart
    const variantImagePath = mainImage.src.split('/').pop();
    document.getElementById('selectedVariantImage').value = variantImagePath;
}

function selectModernColor(colorElement) {
    document.querySelectorAll('.modern-color-option').forEach(option => option.classList.remove('selected'));
    colorElement.classList.add('selected');
    
    modernSelectedColor = colorElement.dataset.color;
    modernCurrentStock = parseInt(colorElement.dataset.stock);
    
    document.getElementById('modernSelectedColor').textContent = modernSelectedColor;
    document.getElementById('modernColorInput').value = modernSelectedColor;
    
    // Show/hide variant images based on selected color
    document.querySelectorAll('.variant-image').forEach(img => {
        if(img.dataset.color === modernSelectedColor) {
            img.style.display = 'block';
            // Auto-select the first variant image
            if(!document.querySelector('.variant-image.active[data-color="' + modernSelectedColor + '"]')) {
                changeModernImage(img);
            }
        } else {
            img.style.display = 'none';
        }
    });
    
    updateModernStockInfo();
}

function selectModernSize(sizeElement) {
    if(sizeElement.classList.contains('unavailable')) return;
    
    document.querySelectorAll('.modern-size-option').forEach(option => option.classList.remove('selected'));
    sizeElement.classList.add('selected');
    
    modernSelectedSize = sizeElement.dataset.size;
    modernCurrentStock = parseInt(sizeElement.dataset.stock);
    
    document.getElementById('modernSelectedSize').textContent = modernSelectedSize;
    document.getElementById('modernSizeInput').value = modernSelectedSize;
    
    updateModernStockInfo();
}

function updateModernStockInfo() {
    const stockInfo = document.getElementById('modernStockInfo');
    const stockText = document.getElementById('modernStockText');
    
    if(modernCurrentStock > 0) {
        stockInfo.style.display = 'block';
        stockInfo.className = 'modern-stock-info';
        
        if(modernCurrentStock <= 5) {
            stockInfo.className = 'modern-stock-info low-stock';
            stockText.textContent = `Only ${modernCurrentStock} left in stock!`;
        } else {
            stockText.textContent = `${modernCurrentStock} items available`;
        }
    } else if(modernSelectedColor || modernSelectedSize) {
        stockInfo.style.display = 'block';
        stockInfo.className = 'modern-stock-info out-of-stock';
        stockText.textContent = 'Out of stock';
    } else {
        stockInfo.style.display = 'none';
    }
}

function changeModernQuantity(change) {
    const quantityInput = document.getElementById('modernQuantityInput');
    let newValue = parseInt(quantityInput.value) + change;
    
    if(newValue < 1) newValue = 1;
    if(newValue > modernCurrentStock && modernCurrentStock > 0) newValue = modernCurrentStock;
    if(newValue > 10) newValue = 10;
    
    quantityInput.value = newValue;
}

function showModernTab(tabName, button) {
    document.querySelectorAll('.modern-tab-pane').forEach(pane => pane.classList.remove('active'));
    document.querySelectorAll('.modern-tab-btn').forEach(btn => btn.classList.remove('active'));
    
    document.getElementById(tabName).classList.add('active');
    if(button) {
        button.classList.add('active');
    }
}

// Initialize
document.addEventListener('DOMContentLoaded', function() {
    <?php if(count($color_variants) == 1): ?>
    const singleColorOption = document.querySelector('.modern-color-option');
    if(singleColorOption) {
        selectModernColor(singleColorOption);
    }
    <?php endif; ?>
    
    <?php if(count($available_sizes) == 1): ?>
    const singleSizeOption = document.querySelector('.modern-size-option:not(.unavailable)');
    if(singleSizeOption) {
        selectModernSize(singleSizeOption);
    }
    <?php endif; ?>

    // Set initial variant image
    const mainImage = document.getElementById('modernMainImage');
    const variantImagePath = mainImage.src.split('/').pop();
    document.getElementById('selectedVariantImage').value = variantImagePath;
});
</script>

<?php include("includes/footer.php"); ?>

</body>
</html>
