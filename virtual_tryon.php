<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
include("includes/db.php");
include("includes/header.php");
include("functions/functions.php");
include("includes/main.php");

// Get user's IP address (same logic as cart.php)
$ip_add = getRealUserIp();
if($ip_add == '' || $ip_add == '127.0.0.1' || $ip_add == '::1') {
    $check_ip_query = "SELECT DISTINCT ip_add FROM cart LIMIT 1";
    $check_ip_result = mysqli_query($con, $check_ip_query);
    if($check_ip_result && mysqli_num_rows($check_ip_result) > 0) {
        $ip_row = mysqli_fetch_array($check_ip_result);
        $ip_add = $ip_row['ip_add'];
    }
}

// Fetch cart items with product details
$cart_items = [];
$select_cart = "SELECT c.*, p.product_title, p.product_img1, p.product_url 
                FROM cart c 
                LEFT JOIN products p ON c.p_id = p.product_id 
                WHERE c.ip_add='$ip_add'";
$run_cart = mysqli_query($con, $select_cart);

if($run_cart && mysqli_num_rows($run_cart) > 0) {
    while($row = mysqli_fetch_array($run_cart)) {
        $display_image = $row['product_img1']; // Default image
        
        // Get variant image if color variant exists
        if(!empty($row['color_variant'])) {
            if(!empty($row['variant_image']) && file_exists("admin_area/product_images/" . $row['variant_image'])) {
                $display_image = $row['variant_image'];
            } else {
                // Get variant images from database
                $get_variant = "SELECT variant_image, variant_images FROM product_variants 
                               WHERE product_id='".$row['p_id']."' AND color_name='".$row['color_variant']."'";
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
        
        $cart_items[] = [
            'cart_id' => $row['cart_id'],
            'product_id' => $row['p_id'],
            'product_title' => $row['product_title'],
            'color_variant' => $row['color_variant'],
            'size' => $row['size'],
            'qty' => $row['qty'],
            'display_image' => $display_image,
            'product_url' => $row['product_url']
        ];
    }
}
?>

<main>
    <div class="nero">
        <div class="nero__heading">
            <span class="nero__bold">VIRTUAL</span> Try-On
        </div>
        <p class="nero__text">See how your cart items look on you with AI-powered virtual try-on</p>
    </div>
</main>

<div id="content">
    <div class="container">
        <div class="row">
            <?php if(empty($cart_items)): ?>
            <div class="col-md-12">
                <div class="alert alert-info text-center">
                    <h3><i class="fa fa-shopping-cart"></i> No items in cart</h3>
                    <p>Add some items to your cart first to use the virtual try-on feature.</p>
                    <a href="shop.php" class="btn btn-primary btn-lg">
                        <i class="fa fa-shopping-bag"></i> Start Shopping
                    </a>
                </div>
            </div>
            <?php else: ?>
            
            <!-- Cart Items Section -->
            <div class="col-md-4">
                <div class="box">
                    <div class="box-header">
                        <h3>Your Cart Items</h3>
                        <p class="text-muted">Select multiple items to create your outfit</p>
                    </div>
                    <div class="cart-items-list">
                        <?php foreach($cart_items as $index => $item): ?>
                        <div class="cart-item-card" 
                             data-product-id="<?php echo $item['product_id']; ?>"
                             data-cart-id="<?php echo $item['cart_id']; ?>"
                             data-image="admin_area/product_images/<?php echo $item['display_image']; ?>"
                             data-title="<?php echo htmlspecialchars($item['product_title']); ?>"
                             data-color="<?php echo htmlspecialchars($item['color_variant']); ?>"
                             data-size="<?php echo htmlspecialchars($item['size']); ?>">
                            
                            <div class="item-checkbox">
                                <input type="checkbox" class="item-selector" id="item-<?php echo $item['cart_id']; ?>" <?php echo $index === 0 ? 'checked' : ''; ?>>
                                <label for="item-<?php echo $item['cart_id']; ?>"></label>
                            </div>
                            
                            <div class="item-image">
                                <img src="admin_area/product_images/<?php echo $item['display_image']; ?>" 
                                     alt="<?php echo htmlspecialchars($item['product_title']); ?>"
                                     style="width: 80px; height: 80px; object-fit: cover; border-radius: 8px;">
                            </div>
                            
                            <div class="item-details">
                                <h5><?php echo $item['product_title']; ?></h5>
                                <!-- <?php if(!empty($item['color_variant'])): ?>
                                <p class="text-muted">Color: <?php echo $item['color_variant']; ?></p>
                                <?php endif; ?>
                                <?php if(!empty($item['size'])): ?>
                                <p class="text-muted">Size: <?php echo strtoupper($item['size']); ?></p>
                                <?php endif; ?> -->
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- Upload & Try-On Section -->
            <div class="col-md-8">
                <div class="box">
                    <div class="box-header">
                        <h3>Virtual Try-On Studio</h3>
                        <p class="text-muted">Upload your photo and see how the selected item looks on you</p>
                    </div>
                    
                    <!-- Selected Items Display -->
                    <div class="selected-items-display">
                        <div class="row">
                            <div class="col-md-6">
                                <h4>Selected Items for Outfit:</h4>
                                <div id="selected-items-info">
                                    <div class="selected-items-container">
                                        <!-- Selected items will be dynamically populated here -->
                                    </div>
                                    <div id="no-items-selected" class="text-muted" style="display: none;">
                                        <p><i class="fa fa-info-circle"></i> Select items from your cart to create an outfit</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <h4>Upload Your Photo:</h4>
                                <div class="upload-area">
                                    <input type="file" id="user-photo" accept="image/*" style="display: none;">
                                    <div id="upload-zone" class="upload-zone">
                                        <i class="fa fa-cloud-upload fa-3x"></i>
                                        <p>Click to upload your full body photo</p>
                                        <small class="text-muted">Supported formats: JPG, PNG, WEBP (Max 10MB)</small>
                                    </div>
                                    <div id="uploaded-image-preview" style="display: none;">
                                        <img id="user-image-preview" src="/placeholder.svg" alt="Your Photo" 
                                             style="max-width: 200px; max-height: 200px; object-fit: cover; border-radius: 8px;">
                                        <button type="button" id="change-photo-btn" class="btn btn-sm btn-secondary">
                                            <i class="fa fa-edit"></i> Change Photo
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Try-On Button -->
                    <div class="try-on-section text-center" style="margin: 30px 0;">
                        <button id="try-on-btn" class="btn btn-success btn-lg" disabled>
                            <i class="fa fa-magic"></i> Generate Virtual Try-On
                        </button>
                        <div id="loading-indicator" style="display: none; margin-top: 15px;">
                            <i class="fa fa-spinner fa-spin"></i> Processing your virtual try-on... This may take a few moments.
                        </div>
                    </div>

                    <!-- Results Section -->
                    <div id="results-section" style="display: none;">
                        <h4>Your Virtual Try-On Result:</h4>
                        <div class="result-container text-center">
                            <img id="result-image" src="/placeholder.svg" alt="Try-On Result" 
                                 style="max-width: 100%; height: auto; border-radius: 8px; box-shadow: 0 4px 8px rgba(0,0,0,0.1);">
                            <div class="result-actions" style="margin-top: 20px;">
                                <button id="download-result-btn" class="btn btn-primary">
                                    <i class="fa fa-download"></i> Download Result
                                </button>
                                <button id="try-another-btn" class="btn btn-secondary">
                                    <i class="fa fa-refresh"></i> Try Another Item
                                </button>
                                <a href="checkout.php" class="btn btn-success">
                                    <i class="fa fa-shopping-cart"></i> Proceed to Checkout
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Error Section -->
                    <div id="error-section" style="display: none;">
                        <div class="alert alert-danger">
                            <h4><i class="fa fa-exclamation-triangle"></i> Error</h4>
                            <p id="error-message"></p>
                            <button id="retry-btn" class="btn btn-danger">
                                <i class="fa fa-refresh"></i> Try Again
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
.cart-item-card {
    display: flex;
    align-items: center;
    padding: 15px;
    border: 2px solid #e0e0e0;
    border-radius: 8px;
    margin-bottom: 15px;
    cursor: pointer;
    transition: all 0.3s ease;
    position: relative;
}

.cart-item-card:hover {
    border-color: #ff6b35;
    box-shadow: 0 2px 8px rgba(255, 107, 53, 0.2);
}

.cart-item-card.selected {
    border-color: #ff6b35;
    background-color: #fff5f2;
    box-shadow: 0 2px 8px rgba(255, 107, 53, 0.3);
}

.item-checkbox {
    position: absolute;
    top: 10px;
    right: 10px;
    z-index: 10;
}

.item-checkbox input[type="checkbox"] {
    display: none;
}

.item-checkbox label {
    display: block;
    width: 20px;
    height: 20px;
    border: 2px solid #ddd;
    border-radius: 4px;
    cursor: pointer;
    position: relative;
    transition: all 0.2s ease;
    background: white;
}

.item-checkbox input[type="checkbox"]:checked + label {
    background-color: #ff6b35;
    border-color: #ff6b35;
}

.item-checkbox input[type="checkbox"]:checked + label:after {
    content: '✓';
    color: white;
    font-size: 12px;
    font-weight: bold;
    position: absolute;
    top: 1px;
    left: 4px;
}

.cart-item-card .item-image {
    margin-right: 15px;
}

.cart-item-card .item-details {
    flex: 1;
}

.cart-item-card .item-details h5 {
    margin: 0 0 5px 0;
    font-size: 14px;
    font-weight: bold;
}

.cart-item-card .item-details p {
    margin: 2px 0;
    font-size: 12px;
}

.selected-items-container {
    max-height: 300px;
    overflow-y: auto;
}

.selected-item-mini {
    display: flex;
    align-items: center;
    padding: 10px;
    border: 1px solid #e0e0e0;
    border-radius: 6px;
    background-color: #f9f9f9;
    margin-bottom: 8px;
    position: relative;
}

.selected-item-mini img {
    width: 50px;
    height: 50px;
    object-fit: cover;
    border-radius: 4px;
    margin-right: 10px;
}

.selected-item-mini .item-info {
    flex: 1;
}

.selected-item-mini .item-info h6 {
    margin: 0 0 3px 0;
    font-size: 12px;
    font-weight: bold;
    color: #333;
}

.selected-item-mini .item-info p {
    margin: 0;
    font-size: 10px;
    color: #666;
}

.selected-item-mini .remove-item {
    position: absolute;
    top: 5px;
    right: 8px;
    color: #999;
    cursor: pointer;
    font-size: 12px;
    transition: color 0.2s ease;
}

.selected-item-mini .remove-item:hover {
    color: #ff6b35;
}

.upload-zone {
    border: 2px dashed #ccc;
    border-radius: 8px;
    padding: 40px 20px;
    text-align: center;
    cursor: pointer;
    transition: all 0.3s ease;
}

.upload-zone:hover {
    border-color: #ff6b35;
    background-color: #fff5f2;
}

.upload-zone.dragover {
    border-color: #ff6b35;
    background-color: #fff5f2;
}

.upload-zone i {
    color: #ccc;
    margin-bottom: 10px;
}

.upload-zone:hover i {
    color: #ff6b35;
}

#uploaded-image-preview {
    text-align: center;
    padding: 20px;
}

#uploaded-image-preview img {
    display: block;
    margin: 0 auto 15px auto;
    border: 2px solid #e0e0e0;
}

.result-container {
    padding: 20px;
    background-color: #f9f9f9;
    border-radius: 8px;
    margin-top: 20px;
}

.result-actions {
    display: flex;
    justify-content: center;
    gap: 10px;
    flex-wrap: wrap;
}

.outfit-summary {
    background: linear-gradient(135deg, #ff6b35 0%, #ff8c5a 100%);
    color: white;
    padding: 10px 15px;
    border-radius: 8px;
    margin-bottom: 15px;
    text-align: center;
}

.outfit-summary h6 {
    margin: 0;
    font-size: 14px;
    font-weight: bold;
}

@media (max-width: 768px) {
    .cart-item-card {
        flex-direction: column;
        text-align: center;
        padding-top: 35px;
    }
    
    .cart-item-card .item-image {
        margin-right: 0;
        margin-bottom: 10px;
    }
    
    .item-checkbox {
        top: 10px;
        right: 15px;
    }
    
    .selected-item-mini {
        flex-direction: column;
        text-align: center;
        padding-top: 25px;
    }
    
    .selected-item-mini img {
        margin-right: 0;
        margin-bottom: 8px;
    }
    
    .result-actions {
        flex-direction: column;
        align-items: center;
    }
    
    .result-actions .btn {
        width: 100%;
        max-width: 250px;
        margin-bottom: 10px;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const cartItems = document.querySelectorAll('.cart-item-card');
    const itemCheckboxes = document.querySelectorAll('.item-selector');
    const userPhotoInput = document.getElementById('user-photo');
    const uploadZone = document.getElementById('upload-zone');
    const uploadedImagePreview = document.getElementById('uploaded-image-preview');
    const userImagePreview = document.getElementById('user-image-preview');
    const changePhotoBtn = document.getElementById('change-photo-btn');
    const tryOnBtn = document.getElementById('try-on-btn');
    const loadingIndicator = document.getElementById('loading-indicator');
    const resultsSection = document.getElementById('results-section');
    const errorSection = document.getElementById('error-section');
    const resultImage = document.getElementById('result-image');
    const errorMessage = document.getElementById('error-message');
    const retryBtn = document.getElementById('retry-btn');
    const downloadResultBtn = document.getElementById('download-result-btn');
    const tryAnotherBtn = document.getElementById('try-another-btn');

    const selectedItemsContainer = document.querySelector('.selected-items-container');
    const noItemsSelected = document.getElementById('no-items-selected');

    let selectedItems = [];
    let uploadedImageFile = null;

    // Initialize with first item selected
    if (itemCheckboxes.length > 0) {
        itemCheckboxes[0].checked = true;
        updateSelectedItems();
    }

    // Item selection handling
    cartItems.forEach((card, index) => {
        const checkbox = card.querySelector('.item-selector');
        
        card.addEventListener('click', function(e) {
            if (e.target !== checkbox && e.target.tagName !== 'LABEL') {
                checkbox.click();
            }
        });
        
        checkbox.addEventListener('change', function() {
            if (this.checked) {
                card.classList.add('selected');
            } else {
                card.classList.remove('selected');
            }
            updateSelectedItems();
        });
    });

    function updateSelectedItems() {
        selectedItems = [];
        selectedItemsContainer.innerHTML = '';

        itemCheckboxes.forEach((checkbox, index) => {
            if (checkbox.checked) {
                const card = cartItems[index];
                const itemData = {
                    productId: card.dataset.productId,
                    cartId: card.dataset.cartId,
                    image: card.dataset.image,
                    title: card.dataset.title,
                    color: card.dataset.color,
                    size: card.dataset.size
                };
                selectedItems.push(itemData);

                // Create mini display for selected item
                const miniItem = document.createElement('div');
                miniItem.className = 'selected-item-mini';
                miniItem.innerHTML = `
                    <img src="${itemData.image}" alt="${itemData.title}">
                    <div class="item-info">
                        <h6>${itemData.title}</h6>
                        ${itemData.color ? `<p>Color: ${itemData.color}</p>` : ''}
                        ${itemData.size ? `<p>Size: ${itemData.size.toUpperCase()}</p>` : ''}
                    </div>
                    <span class="remove-item" data-cart-id="${itemData.cartId}">×</span>
                `;
                selectedItemsContainer.appendChild(miniItem);

                // Add remove functionality
                miniItem.querySelector('.remove-item').addEventListener('click', function() {
                    const cartId = this.dataset.cartId;
                    const targetCheckbox = document.getElementById(`item-${cartId}`);
                    if (targetCheckbox) {
                        targetCheckbox.checked = false;
                        targetCheckbox.closest('.cart-item-card').classList.remove('selected');
                        updateSelectedItems();
                    }
                });
            }
        });

        // Show/hide no items message
        if (selectedItems.length === 0) {
            noItemsSelected.style.display = 'block';
            selectedItemsContainer.style.display = 'none';
        } else {
            noItemsSelected.style.display = 'none';
            selectedItemsContainer.style.display = 'block';
            
            // Add outfit summary
            if (selectedItems.length > 1) {
                const outfitSummary = document.createElement('div');
                outfitSummary.className = 'outfit-summary';
                outfitSummary.innerHTML = `<h6>Complete Outfit (${selectedItems.length} items)</h6>`;
                selectedItemsContainer.insertBefore(outfitSummary, selectedItemsContainer.firstChild);
            }
        }

        checkTryOnButton();
    }

    // File upload handling
    uploadZone.addEventListener('click', function() {
        userPhotoInput.click();
    });

    if (changePhotoBtn) {
        changePhotoBtn.addEventListener('click', function() {
            userPhotoInput.click();
        });
    }

    // Drag and drop functionality
    uploadZone.addEventListener('dragover', function(e) {
        e.preventDefault();
        uploadZone.classList.add('dragover');
    });

    uploadZone.addEventListener('dragleave', function(e) {
        e.preventDefault();
        uploadZone.classList.remove('dragover');
    });

    uploadZone.addEventListener('drop', function(e) {
        e.preventDefault();
        uploadZone.classList.remove('dragover');
        
        const files = e.dataTransfer.files;
        if (files.length > 0) {
            handleFileUpload(files[0]);
        }
    });

    userPhotoInput.addEventListener('change', function(e) {
        if (e.target.files.length > 0) {
            handleFileUpload(e.target.files[0]);
        }
    });

    function handleFileUpload(file) {
        // Validate file
        if (!file.type.startsWith('image/')) {
            alert('Please upload an image file (JPG, PNG, WEBP).');
            return;
        }

        if (file.size > 10 * 1024 * 1024) { // 10MB limit
            alert('File size must be less than 10MB.');
            return;
        }

        uploadedImageFile = file;

        // Create preview
        const reader = new FileReader();
        reader.onload = function(e) {
            userImagePreview.src = e.target.result;
            uploadZone.style.display = 'none';
            uploadedImagePreview.style.display = 'block';
            checkTryOnButton();
        };
        reader.readAsDataURL(file);
    }

    function checkTryOnButton() {
        if (selectedItems.length > 0 && uploadedImageFile) {
            tryOnBtn.disabled = false;
            if (selectedItems.length === 1) {
                tryOnBtn.innerHTML = '<i class="fa fa-magic"></i> Generate Virtual Try-On';
            } else {
                tryOnBtn.innerHTML = `<i class="fa fa-magic"></i> Generate Outfit Try-On (${selectedItems.length} items)`;
            }
        } else {
            tryOnBtn.disabled = true;
            if (selectedItems.length === 0 && !uploadedImageFile) {
                tryOnBtn.innerHTML = '<i class="fa fa-info-circle"></i> Select items & upload photo';
            } else if (selectedItems.length === 0) {
                tryOnBtn.innerHTML = '<i class="fa fa-tshirt"></i> Select items to try on';
            } else if (!uploadedImageFile) {
                tryOnBtn.innerHTML = '<i class="fa fa-camera"></i> Upload your photo';
            }
        }
    }

    // Try-on functionality
    tryOnBtn.addEventListener('click', function() {
        performTryOn();
    });

    retryBtn.addEventListener('click', function() {
        performTryOn();
    });

    async function performTryOn() {
        // Hide previous results/errors
        resultsSection.style.display = 'none';
        errorSection.style.display = 'none';
        
        // Show loading
        loadingIndicator.style.display = 'block';
        tryOnBtn.disabled = true;

        try {
            // Upload the user image first
            const userImageUrl = await uploadImageToServer(uploadedImageFile);
            
            // For multiple items, we'll use the first selected item as primary
            // In future, you can enhance this to handle multiple items differently
            const primaryItem = selectedItems[0];
            
            // Get the garment image URL (convert relative path to absolute)
            const garmentImageUrl = window.location.origin + '/ecommerce-website/' + primaryItem.image;
            
            // Create description for all selected items
            let garmentDescription = selectedItems.map(item => 
                `${item.title}${item.color ? ' in ' + item.color : ''}`
            ).join(', ');

            // Prepare the API request
            const apiData = {
                version: "0513734a452173b8173e907e3a59d19a36266e55b48528559432bd21c7d7e985",
                input: {
                    garm_img: garmentImageUrl,
                    human_img: userImageUrl,
                    garment_des: garmentDescription
                }
            };

            // Call the try-on API
            const response = await fetch('tryon_api.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(apiData)
            });

            const result = await response.json();

            if (result.success) {
                // Show result
                resultImage.src = result.output_url;
                resultsSection.style.display = 'block';
                
                // Set up download functionality
                downloadResultBtn.onclick = function() {
                    const filename = selectedItems.length > 1 
                        ? `outfit_tryon_${selectedItems.length}_items.png`
                        : `tryon_${primaryItem.title.replace(/\s+/g, '_')}.png`;
                    downloadImage(result.output_url, filename);
                };
            } else {
                throw new Error(result.error || 'Try-on failed');
            }

        } catch (error) {
            console.error('Try-on error:', error);
            errorMessage.textContent = error.message || 'An error occurred during the try-on process. Please try again.';
            errorSection.style.display = 'block';
        } finally {
            loadingIndicator.style.display = 'none';
            checkTryOnButton();
        }
    }

    async function uploadImageToServer(file) {
        const formData = new FormData();
        formData.append('user_image', file);

        const response = await fetch('upload_tryon_image.php', {
            method: 'POST',
            body: formData
        });

        const result = await response.json();
        
        if (result.success) {
            return result.image_url;
        } else {
            throw new Error(result.error || 'Failed to upload image');
        }
    }

    function downloadImage(url, filename) {
        const link = document.createElement('a');
        link.href = url;
        link.download = filename;
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    }

    // Try another functionality
    tryAnotherBtn.addEventListener('click', function() {
        resultsSection.style.display = 'none';
        errorSection.style.display = 'none';
        // Scroll back to top
        window.scrollTo({ top: 0, behavior: 'smooth' });
    });

    // Initialize the display
    updateSelectedItems();
});
</script>

<?php include("includes/footer.php"); ?>