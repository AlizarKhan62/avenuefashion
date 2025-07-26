<?php
if(!isset($_SESSION['admin_email'])){
    echo "<script>window.open('login.php','_self')</script>";
    exit;
}

// Process form submission
if(isset($_POST['submit'])){
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
    $status = "product";

    // Handle main product images
    $product_img1 = '';
    $product_img2 = '';
    $product_img3 = '';

    if(isset($_FILES['product_img1']) && $_FILES['product_img1']['error'] == 0) {
        $file_extension = pathinfo($_FILES['product_img1']['name'], PATHINFO_EXTENSION);
        $product_img1 = 'main_' . time() . '_1.' . $file_extension;
        if(!move_uploaded_file($_FILES['product_img1']['tmp_name'], "product_images/$product_img1")) {
            echo "<script>alert('Error uploading main image')</script>";
            $product_img1 = '';
        }
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
        // Insert product into database
        $insert_product = "INSERT INTO products (p_cat_id, cat_id, manufacturer_id, date, product_title, product_url, product_img1, product_img2, product_img3, product_price, product_psp_price, product_desc, product_features, product_video, product_keywords, product_label, status, has_variants) VALUES ('$product_cat', '$cat', '$manufacturer_id', NOW(), '$product_title', '$product_url', '$product_img1', '$product_img2', '$product_img3', '$product_price', '$psp_price', '$product_desc', '$product_features', '$product_video', '$product_keywords', '$product_label', '$status', '$has_variants')";

        $run_product = mysqli_query($con, $insert_product);

        if($run_product){
            $product_id = mysqli_insert_id($con);
            
            // Insert color variants if they exist
            if($has_variants && isset($_POST['color_name']) && is_array($_POST['color_name'])) {
                $color_names = $_POST['color_name'];
                $color_codes = $_POST['color_code'];
                $stock_quantities = $_POST['stock_quantity'];
                
                for($i = 0; $i < count($color_names); $i++) {
                    if(!empty($color_names[$i])) {                            $color_name = mysqli_real_escape_string($con, trim($color_names[$i]));
                            // Get color code from colors table if it exists
                            $get_color = "SELECT color_code FROM colors WHERE LOWER(color_name) = LOWER('$color_name')";
                            $run_color = mysqli_query($con, $get_color);
                            if($run_color && mysqli_num_rows($run_color) > 0) {
                                $row_color = mysqli_fetch_array($run_color);
                                $color_code = $row_color['color_code'];
                            } else {
                                $color_code = '#000000'; // Default color code if not found
                            }
                            $stock_qty = (int)$stock_quantities[$i];
                            
                            $variant_images = array();
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
                        
                        $insert_variant = "INSERT INTO product_variants (product_id, color_name, color_code, variant_image, variant_images, stock_quantity) VALUES ('$product_id', '$color_name', '$color_code', '$main_variant_image', '$variant_images_json', '$stock_qty')";
                        mysqli_query($con, $insert_variant);
                    }
                }
            }

            // Insert sizes - Updated to use database sizes
            if(isset($_POST['selected_sizes']) && is_array($_POST['selected_sizes'])) {
                foreach($_POST['selected_sizes'] as $size_id => $stock_qty) {
                    $size_id = (int)$size_id;
                    $stock_qty = (int)$stock_qty;
                    
                    if($stock_qty > 0) {
                        $insert_product_size = "INSERT INTO product_sizes (product_id, size_id, stock_quantity) VALUES ('$product_id', '$size_id', '$stock_qty')";
                        mysqli_query($con, $insert_product_size);
                    }
                }
            }

            echo "<script>alert('Product has been inserted successfully')</script>";
            echo "<script>window.open('index.php?view_products','_self')</script>";
            exit;
        } else {
            echo "<script>alert('Error inserting product: " . mysqli_error($con) . "')</script>";
        }
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
?>

<!DOCTYPE html>
<html>
<head>
<title>Insert Products</title>
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
</style>
</head>
<body>

<div class="row">
<div class="col-lg-12">
<ol class="breadcrumb">
<li class="active">
<i class="fa fa-dashboard"></i> Dashboard / Insert Products
</li>
</ol>
</div>
</div>

<div class="row"> 
<div class="col-lg-12">
<div class="panel panel-default">
<div class="panel-heading">
<h3 class="panel-title">
<i class="fa fa-money fa-fw"></i> Insert Products
</h3>
</div>
<div class="panel-body">

<form class="form-horizontal" method="post" enctype="multipart/form-data">

<div class="form-group">
<label class="col-md-3 control-label">Product Title</label>
<div class="col-md-6">
<input type="text" name="product_title" class="form-control" required>
</div>
</div>

<div class="form-group">
<label class="col-md-3 control-label">Product Url</label>
<div class="col-md-6">
<input type="text" name="product_url" class="form-control" required>
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
while ($row_p_cats=mysqli_fetch_array($run_p_cats)) {
$p_cat_id = $row_p_cats['p_cat_id'];
$p_cat_title = $row_p_cats['p_cat_title'];
$sizing_type = isset($row_p_cats['sizing_type']) ? $row_p_cats['sizing_type'] : 'clothing';
echo "<option value='$p_cat_id' data-sizing='$sizing_type'>$p_cat_title</option>";
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
while ($row_cat=mysqli_fetch_array($run_cat)) {
$cat_id = $row_cat['cat_id'];
$cat_title = $row_cat['cat_title'];
echo "<option value='$cat_id'>$cat_title</option>";
}
?>
</select>
</div>
</div>

<div class="form-group">
<label class="col-md-3 control-label">Product Image 1</label>
<div class="col-md-6">
<input type="file" name="product_img1" class="form-control" required>
</div>
</div>

<div class="form-group">
<label class="col-md-3 control-label">Product Image 2</label>
<div class="col-md-6">
<input type="file" name="product_img2" class="form-control">
</div>
</div>

<div class="form-group">
<label class="col-md-3 control-label">Product Image 3</label>
<div class="col-md-6">
<input type="file" name="product_img3" class="form-control">
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
        <div class="size-item">
            <label>
                <input type="checkbox" onchange="toggleSizeStock(<?php echo $size['size_id']; ?>)"> 
                <?php echo htmlspecialchars($size['size_name']); ?>
            </label>
            <input type="number" 
                   name="selected_sizes[<?php echo $size['size_id']; ?>]" 
                   id="stock_<?php echo $size['size_id']; ?>"
                   placeholder="Stock" 
                   min="0" 
                   value="0" 
                   disabled>
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
<input type="checkbox" id="has_variants" name="has_variants" value="1"> This product has color variants
</label>
</div>
</div>
</div>

<div id="variants_section" style="display:none;">
<div class="form-group">
<label class="col-md-3 control-label">Add Color Variants</label>
<div class="col-md-6">
<div id="color_variants">
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
<div class="variant-images-section">
<label>Variant Image 2:</label>
<input type="file" name="variant_images_0[]" class="form-control" accept="image/*">
</div>
<div class="variant-images-section">
<label>Variant Image 3:</label>
<input type="file" name="variant_images_0[]" class="form-control" accept="image/*">
</div>
</div>
<button type="button" class="btn btn-sm btn-info" onclick="addVariantImage(0)">Add More Images for this Color</button>
</div>
</div>
</div>
</div>
<button type="button" class="btn btn-info" onclick="addColorVariant()">Add Another Color</button>
</div>
</div>
</div>

<div class="form-group">
<label class="col-md-3 control-label">Product Price</label>
<div class="col-md-6">
<input type="text" name="product_price" class="form-control" required>
</div>
</div>

<div class="form-group">
<label class="col-md-3 control-label">Product Sale Price</label>
<div class="col-md-6">
<input type="text" name="psp_price" class="form-control" required>
</div>
</div>

<div class="form-group">
<label class="col-md-3 control-label">Product Keywords</label>
<div class="col-md-6">
<input type="text" name="product_keywords" class="form-control" required>
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
<textarea name="product_desc" class="form-control" rows="15" id="product_desc"></textarea>
</div>
<div id="features" class="tab-pane fade in">
<br>
<textarea name="product_features" class="form-control" rows="15" id="product_features"></textarea>
</div>
<!-- <div id="video" class="tab-pane fade in">
<br>
<textarea name="product_video" class="form-control" rows="15"></textarea>
</div> -->
</div>
</div>
</div>

<div class="form-group">
<label class="col-md-3 control-label">Product Label</label>
<div class="col-md-6">
<input type="text" name="product_label" class="form-control" required>
</div>
</div>

<div class="form-group">
<label class="col-md-3 control-label"></label>
<div class="col-md-6">
<input type="submit" name="submit" value="Insert Product" class="btn btn-primary form-control">
</div>
</div>

</form>

</div>
</div>
</div>
</div>

<script>
var variantCount = 1;
var variantImageCounts = [3];

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
    
    if(checkbox.checked) {
        stockInput.disabled = false;
        stockInput.value = 10; // Default stock
        stockInput.focus();
    } else {
        stockInput.disabled = true;
        stockInput.value = 0;
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
