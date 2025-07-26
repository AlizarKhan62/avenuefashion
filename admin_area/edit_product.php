<?php
if(!isset($_SESSION['admin_email'])){
    echo "<script>window.open('login.php','_self')</script>";
    exit;
}

$product_id = @$_GET['edit_product'];

// Validate product ID
if(empty($product_id) || !is_numeric($product_id)) {
    echo "<script>alert('Invalid product ID')</script>";
    echo "<script>window.open('index.php?view_products','_self')</script>";
    exit;
}

// Get product data with error checking
$get_product = "select * from products where product_id='$product_id'";
$run_product = mysqli_query($con,$get_product);

if(!$run_product || mysqli_num_rows($run_product) == 0) {
    echo "<script>alert('Product not found')</script>";
    echo "<script>window.open('index.php?view_products','_self')</script>";
    exit;
}

$row_product = mysqli_fetch_array($run_product);

$pro_id = $row_product['product_id'];
$pro_title = $row_product['product_title'];
$pro_cat = $row_product['p_cat_id'];
$cat = $row_product['cat_id'];
// Manufacturer functionality removed
$m_id = 0; // Default value
$pro_img1 = $row_product['product_img1'];
$pro_img2 = $row_product['product_img2'];
$pro_img3 = $row_product['product_img3'];
$pro_price = $row_product['product_price'];
$pro_desc = $row_product['product_desc'];
$pro_keywords = $row_product['product_keywords'];
$psp_price = $row_product['product_psp_price'];
$pro_label = $row_product['product_label'];
$pro_url = $row_product['product_url'];
$pro_features = $row_product['product_features'];
$pro_video = $row_product['product_video'];
$has_variants = isset($row_product['has_variants']) ? $row_product['has_variants'] : 0;

// Get existing color variants
$existing_variants = array();
$get_variants = "select * from product_variants where product_id='$pro_id'";
$run_variants = mysqli_query($con, $get_variants);
if($run_variants) {
    while($row_variant = mysqli_fetch_array($run_variants)) {
        $variant_images = json_decode($row_variant['variant_images'], true);
        if(!$variant_images) $variant_images = array();
        $row_variant['images_array'] = $variant_images;
        $existing_variants[] = $row_variant;
    }
}

// Get all available sizes from database
$all_sizes = array();
$get_all_sizes = "SELECT * FROM sizes WHERE size_status = 'active' ORDER BY size_type, size_order, size_name";
$run_all_sizes = mysqli_query($con, $get_all_sizes);
if($run_all_sizes) {
    while($row_size = mysqli_fetch_array($run_all_sizes)) {
        $all_sizes[] = $row_size;
    }
}

// Get existing product sizes
$existing_product_sizes = array();
$get_product_sizes = "SELECT ps.*, s.size_name, s.size_type FROM product_sizes ps 
                      JOIN sizes s ON ps.size_id = s.size_id 
                      WHERE ps.product_id = '$pro_id'";
$run_product_sizes = mysqli_query($con, $get_product_sizes);
if($run_product_sizes) {
    while($row_ps = mysqli_fetch_array($run_product_sizes)) {
        $existing_product_sizes[$row_ps['size_id']] = $row_ps['stock_quantity'];
    }
}

// Process form submission
if(isset($_POST['update'])){
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    
    if (!is_dir("product_images")) {
        mkdir("product_images", 0755, true);
    }

    // Get form data with validation
    $product_title = mysqli_real_escape_string($con, $_POST['product_title']);
    $product_cat = mysqli_real_escape_string($con, $_POST['product_cat']);
    $cat = mysqli_real_escape_string($con, $_POST['cat']);
    // Manufacturer functionality removed
    $manufacturer_id = '1'; // Set to default manufacturer id
    $product_price = mysqli_real_escape_string($con, $_POST['product_price']);
    $product_desc = mysqli_real_escape_string($con, $_POST['product_desc']);
    $product_keywords = mysqli_real_escape_string($con, $_POST['product_keywords']);
    $psp_price = mysqli_real_escape_string($con, $_POST['psp_price']);
    $product_label = mysqli_real_escape_string($con, $_POST['product_label']);
    $product_url = mysqli_real_escape_string($con, $_POST['product_url']);
    $product_features = mysqli_real_escape_string($con, $_POST['product_features']);
    $product_video = $_POST['product_video'];
    $has_variants = isset($_POST['has_variants']) ? 1 : 0;

    // Handle main product images
    $product_img1 = $pro_img1;
    $product_img2 = $pro_img2;
    $product_img3 = $pro_img3;

    if(isset($_FILES['product_img1']) && $_FILES['product_img1']['error'] == 0) {
        $file_extension = pathinfo($_FILES['product_img1']['name'], PATHINFO_EXTENSION);
        $product_img1 = 'main_' . time() . '_1.' . $file_extension;
        move_uploaded_file($_FILES['product_img1']['tmp_name'], "product_images/$product_img1");
    }
    
    if(isset($_FILES['product_img2']) && $_FILES['product_img2']['error'] == 0) {
        $file_extension = pathinfo($_FILES['product_img2']['name'], PATHINFO_EXTENSION);
        $product_img2 = 'main_' . time() . '_2.' . $file_extension;
        move_uploaded_file($_FILES['product_img2']['tmp_name'], "product_images/$product_img2");
    }
    
    if(isset($_FILES['product_img3']) && $_FILES['product_img3']['error'] == 0) {
        $file_extension = pathinfo($_FILES['product_img3']['name'], PATHINFO_EXTENSION);
        $product_img3 = 'main_' . time() . '_3.' . $file_extension;
        move_uploaded_file($_FILES['product_img3']['tmp_name'], "product_images/$product_img3");
    }

    if(empty($product_title) || empty($product_cat) || empty($cat)) {
        echo "<script>alert('Please fill all required fields')</script>";
    } else {
        // Update product in database
        $update_product = "UPDATE products SET 
                          p_cat_id='$product_cat', 
                          cat_id='$cat', 
                          manufacturer_id='$manufacturer_id', 
                          date=NOW(), 
                          product_title='$product_title', 
                          product_url='$product_url', 
                          product_img1='$product_img1', 
                          product_img2='$product_img2', 
                          product_img3='$product_img3', 
                          product_price='$product_price', 
                          product_psp_price='$psp_price', 
                          product_desc='$product_desc', 
                          product_features='$product_features', 
                          product_video='$product_video', 
                          product_keywords='$product_keywords', 
                          product_label='$product_label', 
                          has_variants='$has_variants' 
                          WHERE product_id='$pro_id'";

        $run_product = mysqli_query($con, $update_product);

        if($run_product){
            // Handle color variants - only update if has_variants is checked
            if($has_variants) {
                // Delete existing variants only if we're updating them
                mysqli_query($con, "DELETE FROM product_variants WHERE product_id='$pro_id'");
                
                // Insert new color variants if they exist
                if(isset($_POST['color_name']) && is_array($_POST['color_name'])) {
                    $color_names = $_POST['color_name'];
                    $color_codes = $_POST['color_code'];
                    $stock_quantities = $_POST['stock_quantity'];
                    $keep_existing_images = isset($_POST['keep_existing_images']) ? $_POST['keep_existing_images'] : array();
                    
                    for($i = 0; $i < count($color_names); $i++) {
                        if(!empty($color_names[$i])) {
                            $color_name = mysqli_real_escape_string($con, $color_names[$i]);
                            $color_code = mysqli_real_escape_string($con, $color_codes[$i]);
                            $stock_qty = (int)$stock_quantities[$i];
                            
                            $variant_images = array();
                            
                            // Keep existing images if specified
                            if(isset($keep_existing_images[$i]) && is_array($keep_existing_images[$i])) {
                                $variant_images = $keep_existing_images[$i];
                            }
                            
                            // Add new images
                            $variant_field_name = "variant_images_$i";
                            if(isset($_FILES[$variant_field_name]) && is_array($_FILES[$variant_field_name]['name'])) {
                                for($j = 0; $j < count($_FILES[$variant_field_name]['name']); $j++) {
                                    if(!empty($_FILES[$variant_field_name]['name'][$j]) && $_FILES[$variant_field_name]['error'][$j] == 0) {
                                        $image_name = $_FILES[$variant_field_name]['name'][$j];
                                        $image_tmp = $_FILES[$variant_field_name]['tmp_name'][$j];
                                        
                                        $file_extension = pathinfo($image_name, PATHINFO_EXTENSION);
                                        $unique_name = 'variant_' . $color_name . '_' . time() . '_' . $j . '.' . $file_extension;
                                        
                                        if(move_uploaded_file($image_tmp, "product_images/$unique_name")) {
                                            $variant_images[] = $unique_name;
                                        }
                                    }
                                }
                            }
                            
                            $variant_images_json = json_encode($variant_images);
                            $main_variant_image = !empty($variant_images) ? $variant_images[0] : '';
                            
                            $insert_variant = "INSERT INTO product_variants (product_id, color_name, color_code, variant_image, variant_images, stock_quantity) VALUES ('$pro_id', '$color_name', '$color_code', '$main_variant_image', '$variant_images_json', '$stock_qty')";
                            mysqli_query($con, $insert_variant);
                        }
                    }
                }
            } else {
                // If has_variants is unchecked, delete all variants
                mysqli_query($con, "DELETE FROM product_variants WHERE product_id='$pro_id'");
            }

            // Delete existing product sizes
            mysqli_query($con, "DELETE FROM product_sizes WHERE product_id='$pro_id'");
            
            // Insert sizes - Updated to use database sizes
            if(isset($_POST['selected_sizes']) && is_array($_POST['selected_sizes'])) {
                foreach($_POST['selected_sizes'] as $size_id => $stock_qty) {
                    $size_id = (int)$size_id;
                    $stock_qty = (int)$stock_qty;
                    
                    if($stock_qty > 0) {
                        $insert_product_size = "INSERT INTO product_sizes (product_id, size_id, stock_quantity) VALUES ('$pro_id', '$size_id', '$stock_qty')";
                        mysqli_query($con, $insert_product_size);
                    }
                }
            }

            echo "<script>alert('Product has been updated successfully')</script>";
            echo "<script>window.open('index.php?view_products','_self')</script>";
            exit;
        } else {
            echo "<script>alert('Error updating product: " . mysqli_error($con) . "')</script>";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Edit Product</title>
<script src="//cdn.tinymce.com/4/tinymce.min.js"></script>
<script>tinymce.init({ selector:'#product_desc,#product_features' });</script>
<style>
.color-variant-section {
    border: 1px solid #ddd;
    padding: 15px;
    margin: 10px 0;
    border-radius: 5px;
    background: #f9f9f9;
}
.color-input {
    width: 50px;
    height: 30px;
    border: none;
    cursor: pointer;
    border-radius: 4px;
}
.variant-images-section {
    border: 1px solid #eee;
    padding: 10px;
    margin: 5px 0;
    border-radius: 3px;
    background: white;
}
.size-management {
    border: 1px solid #ddd;
    padding: 15px;
    margin: 10px 0;
    border-radius: 5px;
    background: #f0f8ff;
}
.size-group {
    margin-bottom: 20px;
    padding: 15px;
    border: 1px solid #e0e0e0;
    border-radius: 5px;
    background: white;
}
.size-group h6 {
    margin-bottom: 10px;
    color: #333;
    font-weight: bold;
}
.size-item {
    display: inline-block;
    margin: 5px 10px 5px 0;
    padding: 8px;
    border: 1px solid #ddd;
    border-radius: 4px;
    background: #f9f9f9;
    min-width: 120px;
}
.size-item.selected {
    background: #e8f5e8;
    border-color: #28a745;
}
.size-item label {
    font-weight: normal;
    margin-bottom: 5px;
    display: block;
}
.size-item input[type="number"] {
    width: 60px;
    padding: 2px 5px;
    border: 1px solid #ccc;
    border-radius: 3px;
}
.existing-image {
    width: 50px;
    height: 50px;
    object-fit: cover;
    margin: 2px;
    border: 1px solid #ddd;
    border-radius: 3px;
}
.existing-images-container {
    background: #f8f9fa;
    padding: 10px;
    border-radius: 5px;
    margin-bottom: 10px;
}
.image-item {
    display: inline-block;
    margin: 5px;
    position: relative;
}
.remove-image-btn {
    position: absolute;
    top: -5px;
    right: -5px;
    background: #dc3545;
    color: white;
    border: none;
    border-radius: 50%;
    width: 20px;
    height: 20px;
    font-size: 12px;
    cursor: pointer;
}
</style>
</head>
<body>

<div class="row">
<div class="col-lg-12">
<ol class="breadcrumb">
<li class="active">
<i class="fa fa-dashboard"></i> Dashboard / Edit Product
</li>
</ol>
</div>
</div>

<div class="row"> 
<div class="col-lg-12">
<div class="panel panel-default">
<div class="panel-heading">
<h3 class="panel-title">
<i class="fa fa-money fa-fw"></i> Edit Product
</h3>
</div>
<div class="panel-body">

<form class="form-horizontal" method="post" enctype="multipart/form-data">

<div class="form-group">
<label class="col-md-3 control-label">Product Title</label>
<div class="col-md-6">
<input type="text" name="product_title" class="form-control" required value="<?php echo htmlspecialchars($pro_title); ?>">
</div>
</div>

<div class="form-group">
<label class="col-md-3 control-label">Product Url</label>
<div class="col-md-6">
<input type="text" name="product_url" class="form-control" required value="<?php echo htmlspecialchars($pro_url); ?>">
<br>
<p style="font-size:15px; font-weight:bold;">
Product Url Example: navy-blue-t-shirt
</p>
</div>
</div>

<!-- Manufacturer section removed
<div class="form-group">
<label class="col-md-3 control-label">Select A Manufacturer</label>
<div class="col-md-6">
<select class="form-control" name="manufacturer">
<option value="">Select A Manufacturer</option>
</select>
</div>
</div>
-->

<div class="form-group">
<label class="col-md-3 control-label">Product Category</label>
<div class="col-md-6">
<select name="product_cat" class="form-control" id="product_category" required>
<option value="">Select a Product Category</option>
<?php
$get_p_cats = "select * from product_categories";
$run_p_cats = mysqli_query($con,$get_p_cats);
if($run_p_cats) {
    while ($row_p_cats=mysqli_fetch_array($run_p_cats)) {
        $p_cat_id = $row_p_cats['p_cat_id'];
        $p_cat_title = $row_p_cats['p_cat_title'];
        $sizing_type = isset($row_p_cats['sizing_type']) ? $row_p_cats['sizing_type'] : 'clothing';
        $selected = ($p_cat_id == $pro_cat) ? 'selected' : '';
        echo "<option value='$p_cat_id' data-sizing='$sizing_type' $selected>$p_cat_title</option>";
    }
}
?>
</select>
</div>
</div>

<div class="form-group">
<label class="col-md-3 control-label">Category</label>
<div class="col-md-6">
<select name="cat" class="form-control" required>
<option value="">Select a Category</option>
<?php
$get_cat = "select * from categories ";
$run_cat = mysqli_query($con,$get_cat);
if($run_cat) {
    while ($row_cat=mysqli_fetch_array($run_cat)) {
        $cat_id = $row_cat['cat_id'];
        $cat_title = $row_cat['cat_title'];
        $selected = ($cat_id == $cat) ? 'selected' : '';
        echo "<option value='$cat_id' $selected>$cat_title</option>";
    }
}
?>
</select>
</div>
</div>

<div class="form-group">
<label class="col-md-3 control-label">Product Image 1</label>
<div class="col-md-6">
<input type="file" name="product_img1" class="form-control">
<br><?php if($pro_img1): ?><img src="product_images/<?php echo $pro_img1; ?>" width="70" height="70"><?php endif; ?>
</div>
</div>

<div class="form-group">
<label class="col-md-3 control-label">Product Image 2</label>
<div class="col-md-6">
<input type="file" name="product_img2" class="form-control">
<br><?php if($pro_img2): ?><img src="product_images/<?php echo $pro_img2; ?>" width="70" height="70"><?php endif; ?>
</div>
</div>

<div class="form-group">
<label class="col-md-3 control-label">Product Image 3</label>
<div class="col-md-6">
<input type="file" name="product_img3" class="form-control">
<br><?php if($pro_img3): ?><img src="product_images/<?php echo $pro_img3; ?>" width="70" height="70"><?php endif; ?>
</div>
</div>

<!-- Size Management Section -->
<div class="form-group">
<label class="col-md-3 control-label">Size Management</label>
<div class="col-md-6">
<div class="size-management">
<h5>Product Sizes 
    <a href="index.php?view_sizes" target="_blank" class="btn btn-xs btn-info">Manage Sizes</a>
    <a href="index.php?insert_size" target="_blank" class="btn btn-xs btn-success">Add New Size</a>
</h5>
<p>Select sizes and set stock quantities for this product:</p>

<?php if(!empty($all_sizes)): ?>
    <?php
    // Group sizes by type
    $grouped_sizes = array();
    foreach($all_sizes as $size) {
        $grouped_sizes[$size['size_type']][] = $size;
    }
    ?>
    
    <?php foreach($grouped_sizes as $type => $sizes): ?>
    <div class="size-group">
        <h6><?php echo ucfirst(str_replace('_', ' ', $type)); ?> Sizes:</h6>
        <?php foreach($sizes as $size): ?>
        <?php 
        $is_selected = isset($existing_product_sizes[$size['size_id']]);
        $current_stock = $is_selected ? $existing_product_sizes[$size['size_id']] : 0;
        ?>
        <div class="size-item <?php echo $is_selected ? 'selected' : ''; ?>">
            <label>
                <input type="checkbox" 
                       <?php echo $is_selected ? 'checked' : ''; ?>
                       onchange="toggleSizeStock(<?php echo $size['size_id']; ?>)"> 
                <?php echo htmlspecialchars($size['size_name']); ?>
            </label>
            <input type="number" 
                   name="selected_sizes[<?php echo $size['size_id']; ?>]" 
                   id="stock_<?php echo $size['size_id']; ?>"
                   placeholder="Stock" 
                   min="0" 
                   value="<?php echo $current_stock; ?>" 
                   <?php echo $is_selected ? '' : 'disabled'; ?>>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endforeach; ?>
    
<?php else: ?>
    <div class="alert alert-warning">
        <strong>No sizes available!</strong> 
        <a href="index.php?insert_size" target="_blank" class="btn btn-sm btn-primary">Add sizes first</a>
    </div>
<?php endif; ?>

</div>
</div>
</div>

<!-- Color Variants Section -->
<div class="form-group">
<label class="col-md-3 control-label">Color Variants</label>
<div class="col-md-6">
<div class="checkbox">
<label>
<input type="checkbox" id="has_variants" name="has_variants" value="1" <?php echo $has_variants ? 'checked' : ''; ?>> This product has color variants
</label>
</div>
</div>
</div>

<div id="variants_section" style="<?php echo $has_variants ? 'display:block;' : 'display:none;'; ?>">
<div class="form-group">
<label class="col-md-3 control-label">Edit Color Variants</label>
<div class="col-md-6">
<div id="color_variants">
<?php if(!empty($existing_variants)): ?>
<?php foreach($existing_variants as $index => $variant): ?>
<div class="color-variant-section">
<h5>Color Variant <?php echo $index + 1; ?> 
<button type="button" class="btn btn-sm btn-danger" onclick="removeVariant(this)">Remove</button></h5>
<div class="row">
<div class="col-md-4">
<label>Color Name:</label>
<input type="text" name="color_name[]" class="form-control" value="<?php echo htmlspecialchars($variant['color_name']); ?>">
</div>
<div class="col-md-4">
<label>Color Code:</label>
<input type="color" name="color_code[]" class="color-input form-control" value="<?php echo $variant['color_code']; ?>">
</div>
<div class="col-md-4">
<label>Stock Quantity:</label>
<input type="number" name="stock_quantity[]" class="form-control" value="<?php echo $variant['stock_quantity']; ?>" min="0">
</div>
</div>
<div class="row" style="margin-top:10px;">
<div class="col-md-12">
<label>Color Variant Images:</label>
<?php if(!empty($variant['images_array'])): ?>
<div class="existing-images-container">
<strong>Existing Images (will be kept):</strong><br>
<?php foreach($variant['images_array'] as $img_index => $image): ?>
<div class="image-item">
<img src="product_images/<?php echo $image; ?>" class="existing-image" alt="Variant Image">
<input type="hidden" name="keep_existing_images[<?php echo $index; ?>][]" value="<?php echo $image; ?>">
</div>
<?php endforeach; ?>
</div>
<?php endif; ?>
<div id="variant_images_<?php echo $index; ?>">
<div class="variant-images-section">
<label>Add New Image 1:</label>
<input type="file" name="variant_images_<?php echo $index; ?>[]" class="form-control" accept="image/*">
</div>
<div class="variant-images-section">
<label>Add New Image 2:</label>
<input type="file" name="variant_images_<?php echo $index; ?>[]" class="form-control" accept="image/*">
</div>
<div class="variant-images-section">
<label>Add New Image 3:</label>
<input type="file" name="variant_images_<?php echo $index; ?>[]" class="form-control" accept="image/*">
</div>
</div>
<button type="button" class="btn btn-sm btn-info" onclick="addVariantImage(<?php echo $index; ?>)">Add More Images</button>
</div>
</div>
</div>
<?php endforeach; ?>
<?php else: ?>
<div class="color-variant-section">
<h5>Color Variant 1</h5>
<div class="row">
<div class="col-md-4">
<label>Color Name:</label>
<input type="text" name="color_name[]" class="form-control" placeholder="e.g., Red">
</div>
<div class="col-md-4">
<label>Color Code:</label>
<input type="color" name="color_code[]" class="color-input form-control" value="#ff0000">
</div>
<div class="col-md-4">
<label>Stock Quantity:</label>
<input type="number" name="stock_quantity[]" class="form-control" value="0" min="0">
</div>
</div>
<div class="row" style="margin-top:10px;">
<div class="col-md-12">
<label>Color Variant Images (Multiple):</label>
<div id="variant_images_0">
<div class="variant-images-section">
<label>Variant Image 1:</label>
<input type="file" name="variant_images_0[]" class="form-control" accept="image/*">
</div>
</div>
<button type="button" class="btn btn-sm btn-info" onclick="addVariantImage(0)">Add More Images</button>
</div>
</div>
</div>
<?php endif; ?>
</div>
<button type="button" class="btn btn-info" onclick="addColorVariant()">Add Another Color</button>
</div>
</div>
</div>

<div class="form-group">
<label class="col-md-3 control-label">Product Price</label>
<div class="col-md-6">
<input type="text" name="product_price" class="form-control" required value="<?php echo $pro_price; ?>">
</div>
</div>

<div class="form-group">
<label class="col-md-3 control-label">Product Sale Price</label>
<div class="col-md-6">
<input type="text" name="psp_price" class="form-control" required value="<?php echo $psp_price; ?>">
</div>
</div>

<div class="form-group">
<label class="col-md-3 control-label">Product Keywords</label>
<div class="col-md-6">
<input type="text" name="product_keywords" class="form-control" required value="<?php echo htmlspecialchars($pro_keywords); ?>">
</div>
</div>

<div class="form-group">
<label class="col-md-3 control-label">Product Tabs</label>
<div class="col-md-6">
<ul class="nav nav-tabs">
<li class="active">
<a data-toggle="tab" href="#description">Product Description</a>
</li>
<li>
<a data-toggle="tab" href="#features">Size Guide</a>
</li>
<!-- <li>
<a data-toggle="tab" href="#video">Sounds And Videos</a>
</li> -->
</ul>
<div class="tab-content">
<div id="description" class="tab-pane fade in active">
<br>
<textarea name="product_desc" class="form-control" rows="15" id="product_desc"><?php echo htmlspecialchars($pro_desc); ?></textarea>
</div>
<div id="features" class="tab-pane fade in">
<br>
<textarea name="product_features" class="form-control" rows="15" id="product_features"><?php echo htmlspecialchars($pro_features); ?></textarea>
</div>
<!-- <div id="video" class="tab-pane fade in">
<br>
<textarea name="product_video" class="form-control" rows="15"><?php echo htmlspecialchars($pro_video); ?></textarea>
</div> -->
</div>
</div>
</div>

<div class="form-group">
<label class="col-md-3 control-label">Product Label</label>
<div class="col-md-6">
<input type="text" name="product_label" class="form-control" required value="<?php echo htmlspecialchars($pro_label); ?>">
</div>
</div>

<div class="form-group">
<label class="col-md-3 control-label"></label>
<div class="col-md-6">
<input type="submit" name="update" value="Update Product" class="btn btn-primary form-control">
</div>
</div>

</form>

</div>
</div>
</div>
</div>

<script>
var variantCount = <?php echo count($existing_variants) > 0 ? count($existing_variants) : 1; ?>;
var variantImageCounts = [<?php echo count($existing_variants) > 0 ? implode(',', array_map(function($v) { return count($v['images_array']) + 3; }, $existing_variants)) : '3'; ?>];

document.getElementById('has_variants').addEventListener('change', function() {
    var variantsSection = document.getElementById('variants_section');
    if (this.checked) {
        variantsSection.style.display = 'block';
    } else {
        variantsSection.style.display = 'none';
    }
});

function toggleSizeStock(sizeId) {
    var checkbox = event.target;
    var stockInput = document.getElementById('stock_' + sizeId);
    var sizeItem = checkbox.closest('.size-item');
    
    if(checkbox.checked) {
        stockInput.disabled = false;
        if(stockInput.value == 0) {
            stockInput.value = 10; // Default stock
        }
        stockInput.focus();
        sizeItem.classList.add('selected');
    } else {
        stockInput.disabled = true;
        stockInput.value = 0;
        sizeItem.classList.remove('selected');
    }
}

function addColorVariant() {
    variantCount++;
    var variantIndex = variantCount - 1;
    variantImageCounts[variantIndex] = 3;
    
    var variantsContainer = document.getElementById('color_variants');
    var newVariant = document.createElement('div');
    newVariant.className = 'color-variant-section';
    newVariant.innerHTML = `
        <h5>Color Variant ${variantCount} <button type="button" class="btn btn-sm btn-danger" onclick="removeVariant(this)">Remove</button></h5>
        <div class="row">
            <div class="col-md-4">
                <label>Color Name:</label>
                <input type="text" name="color_name[]" class="form-control" placeholder="e.g., Blue">
            </div>
            <div class="col-md-4">
                <label>Color Code:</label>
                <input type="color" name="color_code[]" class="color-input form-control" value="#0000ff">
            </div>
            <div class="col-md-4">
                <label>Stock Quantity:</label>
                <input type="number" name="stock_quantity[]" class="form-control" value="0" min="0">
            </div>
        </div>
        <div class="row" style="margin-top:10px;">
            <div class="col-md-12">
                <label>Color Variant Images (Multiple):</label>
                <div id="variant_images_${variantIndex}">
                    <div class="variant-images-section">
                        <label>Variant Image 1:</label>
                        <input type="file" name="variant_images_${variantIndex}[]" class="form-control" accept="image/*">
                    </div>
                    <div class="variant-images-section">
                        <label>Variant Image 2:</label>
                        <input type="file" name="variant_images_${variantIndex}[]" class="form-control" accept="image/*">
                    </div>
                    <div class="variant-images-section">
                        <label>Variant Image 3:</label>
                        <input type="file" name="variant_images_${variantIndex}[]" class="form-control" accept="image/*">
                    </div>
                </div>
                <button type="button" class="btn btn-sm btn-info" onclick="addVariantImage(${variantIndex})">Add More Images for this Color</button>
            </div>
        </div>
    `;
    variantsContainer.appendChild(newVariant);
}

function removeVariant(button) {
    button.closest('.color-variant-section').remove();
}

function addVariantImage(variantIndex) {
    if(variantImageCounts[variantIndex] >= 10) {
        alert('Maximum 10 images allowed per color variant');
        return;
    }
    variantImageCounts[variantIndex]++;
    
    var variantImagesContainer = document.getElementById('variant_images_' + variantIndex);
    var newImageDiv = document.createElement('div');
    newImageDiv.className = 'variant-images-section';
    newImageDiv.innerHTML = `
        <label>Variant Image ${variantImageCounts[variantIndex]}: <button type="button" class="btn btn-xs btn-danger" onclick="removeVariantImage(this)">Remove</button></label>
        <input type="file" name="variant_images_${variantIndex}[]" class="form-control" accept="image/*">
    `;
    variantImagesContainer.appendChild(newImageDiv);
}

function removeVariantImage(button) {
    button.closest('.variant-images-section').remove();
}
</script>

</body>
</html>
