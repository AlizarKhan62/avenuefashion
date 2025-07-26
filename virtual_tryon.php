<?php
session_start();
include("includes/db.php");
include("includes/header.php");
include("functions/functions.php");
include("includes/main.php");

$ip_add = getRealUserIp();

// Handle localhost IP variations (same as cart.php)
if($ip_add == '' || $ip_add == '127.0.0.1' || $ip_add == '::1') {
    // Check what IP is actually in the cart table
    $check_ip_query = "SELECT DISTINCT ip_add FROM cart LIMIT 1";
    $check_ip_result = mysqli_query($con, $check_ip_query);
    if($check_ip_result && mysqli_num_rows($check_ip_result) > 0) {
        $ip_row = mysqli_fetch_array($check_ip_result);
        $ip_add = $ip_row['ip_add'];
    }
}

// Get cart items with color variants and proper images
$select_cart = "SELECT * FROM cart WHERE ip_add='$ip_add'";
$run_cart = mysqli_query($con, $select_cart);
$cart_items = [];
while($row_cart = mysqli_fetch_array($run_cart)) {
    $pro_id = $row_cart['p_id'];
    $pro_size = isset($row_cart['size']) ? $row_cart['size'] : '';
    $color_variant = isset($row_cart['color_variant']) ? $row_cart['color_variant'] : '';
    $variant_image = isset($row_cart['variant_image']) ? $row_cart['variant_image'] : '';
    
    $get_product = "SELECT * FROM products WHERE product_id='$pro_id'";
    $run_product = mysqli_query($con, $get_product);
    if($product = mysqli_fetch_array($run_product)) {
        // Determine which image to show (same logic as cart.php)
        $display_image = $product['product_img1']; // Default to main product image
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
        }
        
        $cart_items[] = [
            'id' => $pro_id,
            'name' => $product['product_title'],
            'image' => $display_image,
            'color_variant' => $color_variant,
            'size' => $pro_size,
            'category' => 'clothing'
        ];
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Virtual Try-On - AI Powered</title>
    <link rel="stylesheet" href="styles/bootstrap-337.min.css">
    <link rel="stylesheet" href="font-awesome/css/font-awesome.min.css">
    <style>
        body { 
            font-family: 'Arial', sans-serif; 
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }
        .tryon-container { 
            max-width: 1200px; 
            margin: 0 auto; 
            padding: 20px; 
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            margin-top: 20px;
        }
        .hero-section {
            text-align: center;
            padding: 40px 0;
            background: linear-gradient(135deg, #ff6b6b, #ee5a24);
            color: white;
            border-radius: 15px;
            margin-bottom: 30px;
        }
        .hero-section h1 {
            font-size: 3em;
            margin-bottom: 10px;
            font-weight: 300;
        }
        .hero-section p {
            font-size: 1.3em;
            opacity: 0.9;
        }
        .tech-badge {
            display: inline-block;
            background: rgba(255,255,255,0.2);
            padding: 8px 16px;
            border-radius: 20px;
            margin: 5px;
            font-size: 0.9em;
        }
        .tryon-grid { 
            display: grid; 
            grid-template-columns: 1fr 1fr; 
            gap: 30px; 
        }
        @media (max-width: 768px) { 
            .tryon-grid { grid-template-columns: 1fr; } 
        }
        .upload-area { 
            border: 3px dashed #ddd; 
            padding: 40px; 
            text-align: center; 
            border-radius: 15px;
            cursor: pointer;
            transition: all 0.3s;
            background: #f8f9fa;
        }
        .upload-area:hover { 
            border-color: #007bff; 
            background: #e3f2fd; 
            transform: translateY(-2px);
        }
        .upload-area.dragover { 
            border-color: #007bff; 
            background: #e3f2fd; 
        }
        .item-grid { 
            display: grid; 
            grid-template-columns: repeat(auto-fit, minmax(120px, 1fr)); 
            gap: 15px; 
        }
        .item-card { 
            border: 2px solid #ddd; 
            padding: 10px; 
            border-radius: 10px; 
            cursor: pointer;
            transition: all 0.3s;
            background: white;
        }
        .item-card.selected { 
            border-color: #007bff; 
            background: #e3f2fd; 
            transform: scale(1.05);
        }
        .item-card img { 
            width: 100%; 
            height: 80px; 
            object-fit: cover; 
            border-radius: 8px; 
        }
        .preview-img { 
            max-width: 100%; 
            height: 300px; 
            object-fit: cover; 
            border-radius: 15px; 
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .result-frame { 
            width: 100%; 
            height: 500px; 
            border: none; 
            border-radius: 15px; 
            box-shadow: 0 5px 15px rgba(0,0,0,0.1); 
        }
        .btn-tryon { 
            background: linear-gradient(45deg, #007bff, #6f42c1); 
            color: white; 
            border: none; 
            padding: 15px 30px; 
            border-radius: 25px; 
            font-size: 16px; 
            cursor: pointer;
            transition: all 0.3s;
            font-weight: bold;
        }
        .btn-tryon:hover { 
            transform: translateY(-3px); 
            box-shadow: 0 8px 25px rgba(0,0,0,0.2); 
        }
        .btn-tryon:disabled { 
            opacity: 0.6; 
            cursor: not-allowed; 
            transform: none;
        }
        .loading { 
            display: none; 
            text-align: center; 
            padding: 40px; 
        }
        .loading.show { 
            display: block; 
        }
        .progress-bar { 
            width: 100%; 
            height: 8px; 
            background: #ddd; 
            border-radius: 4px; 
            overflow: hidden; 
            margin: 10px 0;
        }
        .progress-fill { 
            height: 100%; 
            background: linear-gradient(45deg, #007bff, #6f42c1); 
            width: 0%; 
            transition: width 0.3s; 
        }
        .hero-section {
            text-align: center;
            padding: 40px 0;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 15px;
            margin-bottom: 30px;
        }
        .hero-section h1 {
            font-size: 3rem;
            margin-bottom: 10px;
            font-weight: bold;
        }
        .hero-section p {
            font-size: 1.2rem;
            opacity: 0.9;
        }
        .box {
            background: white;
            padding: 25px;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        .box h3 {
            color: #333;
            margin-bottom: 20px;
            font-weight: bold;
        }
    </style>
</head>
<body>

<div class="tryon-container">
    <!-- Hero Section -->
    <div class="hero-section">
        <h1>🚀 Revolutionary AI Virtual Try-On</h1>
        <p>Advanced clothing replacement technology that actually removes and replaces your clothing!</p>
        <div style="margin-top: 20px;">
            <span class="tech-badge">🧠 Body Region Analysis</span>
            <span class="tech-badge">🎯 Clothing Detection</span>
            <span class="tech-badge">🔄 Smart Inpainting</span>
            <span class="tech-badge">✨ Realistic Blending</span>
        </div>
    </div>

    <div class="tryon-grid">
        <!-- Left Side: Upload & Controls -->
        <div>
            <div class="box">
                <h3>📸 Upload Your Photo</h3>
                <div class="upload-area" id="uploadArea">
                    <div id="uploadPrompt">
                        <i class="fa fa-cloud-upload" style="font-size: 48px; color: #ddd; margin-bottom: 15px;"></i>
                        <p>Click or drag your photo here</p>
                        <small>JPG, PNG, WEBP (Max 10MB)</small>
                    </div>
                    <div id="imagePreview" style="display: none;">
                        <img id="previewImg" class="preview-img" alt="Preview">
                        <button type="button" onclick="clearImage()" class="btn btn-secondary" style="margin-top: 10px;">Change Photo</button>
                    </div>
                </div>
                <input type="file" id="imageInput" accept="image/*" style="display: none;">
            </div>

            <div class="box">
                <h3>👕 Select Items to Try On (<?php echo count($cart_items); ?> items in cart)</h3>
                <?php if(empty($cart_items)): ?>
                    <div style="text-align: center; padding: 20px; color: #666;">
                        <i class="fa fa-shopping-cart" style="font-size: 48px; margin-bottom: 15px;"></i>
                        <p>Your cart is empty!</p>
                        <a href="shop.php" class="btn btn-primary">Go Shopping</a>
                    </div>
                <?php else: ?>
                    <div class="item-grid" id="itemGrid">
                        <?php foreach($cart_items as $item): ?>
                        <div class="item-card" data-id="<?php echo $item['id']; ?>" onclick="toggleItem(this)">
                            <img src="admin_area/product_images/<?php echo $item['image']; ?>" alt="<?php echo $item['name']; ?>">
                            <small style="display: block; font-weight: bold;"><?php echo substr($item['name'], 0, 20); ?>...</small>
                            <?php if(!empty($item['color_variant'])): ?>
                                <!-- <small style="display: block; color: #666; font-size: 10px;">Color: <?php echo $item['color_variant']; ?></small> -->
                            <?php endif; ?>
                            <?php if(!empty($item['size'])): ?>
                                <!-- <small style="display: block; color: #666; font-size: 10px;">Size: <?php echo strtoupper($item['size']); ?></small> -->
                            <?php endif; ?>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <div style="margin-top: 15px;">
                        <button type="button" onclick="selectAll()" class="btn btn-outline-primary btn-sm">Select All</button>
                        <button type="button" onclick="clearAll()" class="btn btn-outline-secondary btn-sm" style="margin-left: 10px;">Clear All</button>
                    </div>
                <?php endif; ?>
            </div>

            <div style="text-align: center;">
                <button class="btn-tryon" id="tryonBtn" onclick="startTryOn()" disabled>
                    ✨ Start AI Virtual Try-On
                </button>
            </div>
        </div>

        <!-- Right Side: Results -->
        <div>
            <div class="box">
                <h3>🎯 Try-On Result</h3>
                <div id="resultArea">
                    <div id="waitingState" style="text-align: center; padding: 60px 20px; color: #666;">
                        <i class="fa fa-magic" style="font-size: 48px; margin-bottom: 15px; color: #007bff;"></i>
                        <p><strong>Ready for AI Magic!</strong></p>
                        <p>Upload your photo and select items to see the virtual try-on in action!</p>
                    </div>
                    
                    <div class="loading" id="loadingState">
                        <i class="fa fa-magic fa-spin" style="font-size: 48px; margin-bottom: 15px; color: #ff6b6b;"></i>
                        <p><strong>🚀 Revolutionary AI is replacing your clothing...</strong></p>
                        <div class="progress-bar">
                            <div class="progress-fill" id="progressFill"></div>
                        </div>
                        <small id="progressText">🧠 Analyzing body regions...</small>
                    </div>
                    
                    <div id="resultState" style="display: none; text-align: center;">
                        <iframe id="resultFrame" class="result-frame" src="/placeholder.svg"></iframe>
                        <div style="margin-top: 15px;">
                            <button class="btn btn-success" onclick="openResultInNewTab()">
                                <i class="fa fa-external-link"></i> Open Full View
                            </button>
                            <button class="btn btn-secondary" onclick="resetTryOn()" style="margin-left: 10px;">
                                <i class="fa fa-refresh"></i> Try Again
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="js/jquery.min.js"></script>
<script src="js/bootstrap.min.js"></script>
<script>
let selectedItems = [];
let uploadedImage = null;
let resultUrl = null;

// Cart items data from PHP
const cartItems = <?php echo json_encode($cart_items); ?>;

// File upload handling
document.getElementById('uploadArea').addEventListener('click', () => {
    document.getElementById('imageInput').click();
});

document.getElementById('uploadArea').addEventListener('dragover', (e) => {
    e.preventDefault();
    e.currentTarget.classList.add('dragover');
});

document.getElementById('uploadArea').addEventListener('dragleave', (e) => {
    e.currentTarget.classList.remove('dragover');
});

document.getElementById('uploadArea').addEventListener('drop', (e) => {
    e.preventDefault();
    e.currentTarget.classList.remove('dragover');
    const files = e.dataTransfer.files;
    if (files.length > 0) {
        handleImageUpload(files[0]);
    }
});

document.getElementById('imageInput').addEventListener('change', (e) => {
    if (e.target.files.length > 0) {
        handleImageUpload(e.target.files[0]);
    }
});

function handleImageUpload(file) {
    if (file.size > 10 * 1024 * 1024) {
        alert('File too large! Please choose an image under 10MB.');
        return;
    }
    
    const reader = new FileReader();
    reader.onload = (e) => {
        uploadedImage = e.target.result;
        document.getElementById('previewImg').src = uploadedImage;
        document.getElementById('uploadPrompt').style.display = 'none';
        document.getElementById('imagePreview').style.display = 'block';
        updateTryOnButton();
    };
    reader.readAsDataURL(file);
}

function clearImage() {
    uploadedImage = null;
    document.getElementById('uploadPrompt').style.display = 'block';
    document.getElementById('imagePreview').style.display = 'none';
    document.getElementById('imageInput').value = '';
    updateTryOnButton();
}

function toggleItem(element) {
    const itemId = element.dataset.id;
    if (selectedItems.includes(itemId)) {
        selectedItems = selectedItems.filter(id => id !== itemId);
        element.classList.remove('selected');
    } else {
        selectedItems.push(itemId);
        element.classList.add('selected');
    }
    updateTryOnButton();
}

function selectAll() {
    selectedItems = [];
    document.querySelectorAll('.item-card').forEach(card => {
        selectedItems.push(card.dataset.id);
        card.classList.add('selected');
    });
    updateTryOnButton();
}

function clearAll() {
    selectedItems = [];
    document.querySelectorAll('.item-card').forEach(card => {
        card.classList.remove('selected');
    });
    updateTryOnButton();
}

function updateTryOnButton() {
    const btn = document.getElementById('tryonBtn');
    btn.disabled = !uploadedImage || selectedItems.length === 0;
    
    if (btn.disabled) {
        btn.innerHTML = '✨ Select Photo & Items First';
    } else {
        btn.innerHTML = `✨ Try On ${selectedItems.length} Items with AI`;
    }
}

async function startTryOn() {
    if (!uploadedImage || selectedItems.length === 0) return;
    
    // Show loading state
    document.getElementById('waitingState').style.display = 'none';
    document.getElementById('loadingState').classList.add('show');
    document.getElementById('tryonBtn').disabled = true;
    
    // Progressive status updates
    const statusMessages = [
        { progress: 10, text: '📤 Uploading your image...' },
        { progress: 25, text: '🧠 Analyzing body regions...' },
        { progress: 40, text: '🎯 Detecting existing clothing...' },
        { progress: 60, text: '🔄 Removing old clothing with inpainting...' },
        { progress: 80, text: '👕 Fitting new garments to body shape...' },
        { progress: 95, text: '✨ Applying realistic lighting and blending...' }
    ];
    
    let currentStep = 0;
    const progressInterval = setInterval(() => {
        if (currentStep < statusMessages.length) {
            const step = statusMessages[currentStep];
            document.getElementById('progressFill').style.width = step.progress + '%';
            document.getElementById('progressText').textContent = step.text;
            currentStep++;
        }
    }, 1000);
    
    try {
        // First upload the user image
        const formData = new FormData();
        const response = await fetch(uploadedImage);
        const blob = await response.blob();
        formData.append('image', blob);
        
        const uploadResult = await fetch('upload_temp_image.php', {
            method: 'POST',
            body: formData
        });
        
        const uploadData = await uploadResult.json();
        if (!uploadData.success) {
            throw new Error('Failed to upload image: ' + uploadData.error);
        }
        
        // Prepare garment data with smart type detection
        const garmentData = selectedItems.map(itemId => {
            const item = cartItems.find(i => i.id == itemId);
            const name = item ? item.name.toLowerCase() : '';
            
            // Smart garment type detection
            let type = 'clothing'; // default
            if (name.includes('trouser') || name.includes('pant') || name.includes('jean')) {
                type = 'pants';
            } else if (name.includes('shoe') || name.includes('boot') || name.includes('sneaker')) {
                type = 'shoes';
            } else if (name.includes('jacket') || name.includes('coat') || name.includes('blazer')) {
                type = 'jacket';
            } else if (name.includes('shirt') || name.includes('top') || name.includes('blouse')) {
                type = 'shirt';
            }
            
            return {
                name: item ? item.name : 'Unknown Item',
                image: item ? 'admin_area/product_images/' + item.image : '',
                type: type
            };
        });
        
        // Process with Advanced CP-VTON System
        let result;
        let methodUsed = '';
        
        try {
            console.log('Trying Working Virtual Try-On processor first...');
            console.log('Garment data:', garmentData);
            
            result = await fetch('working_virtual_tryon.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    person_image: uploadData.temp_path,
                    garments: garmentData.map(item => ({
                        image: item.image,
                        type: item.type,
                        name: item.name
                    }))
                })
            });
            
            console.log('Working Virtual Try-On response status:', result.status);
            methodUsed = 'Working Virtual Try-On';
            
            if (!result.ok) {
                throw new Error(`Working Virtual Try-On returned ${result.status}: ${result.statusText}`);
            }
            
        } catch (workingError) {
            console.log('Working Virtual Try-On failed:', workingError.message);
            console.log('Trying Enhanced Realistic Try-On fallback...');
            document.getElementById('progressText').textContent = '⚡ Switching to enhanced realistic mode...';
            
            try {
                result = await fetch('enhanced_realistic_tryon.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        person_image: uploadData.temp_path,
                        garments: garmentData.map(item => ({
                            image: item.image,
                            type: item.type,
                            name: item.name
                        }))
                    })
                });
                
                console.log('Enhanced Realistic Try-On response status:', result.status);
                methodUsed = 'Enhanced Realistic Try-On';
                
                if (!result.ok) {
                    throw new Error(`Enhanced Realistic Try-On returned ${result.status}: ${result.statusText}`);
                }
                
            } catch (enhancedError) {
                console.log('Enhanced Realistic Try-On failed:', enhancedError.message);
                console.log('Trying Advanced CP-VTON fallback...');
                document.getElementById('progressText').textContent = '⚡ Switching to advanced processing mode...';
                
                try {
                    result = await fetch('revolutionary_multi_garment_tryon.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({
                            person_image: uploadData.temp_path,
                            garments: garmentData.map(item => ({
                                image: item.image,
                                type: item.type,
                                name: item.name
                            })),
                            garment_type: garmentData[0]?.type || 'shirt',
                            preferences: {
                                lighting: 'natural',
                                fabric_type: 'cotton',
                                environment: 'indoor',
                                color_preference: 'natural'
                            },
                            quality_level: 'ultra_high',
                            processing_mode: 'advanced_cpvton'
                        })
                    });
                    
                    console.log('Advanced CP-VTON response status:', result.status);
                    methodUsed = 'Advanced CP-VTON';
                    
                    if (!result.ok) {
                        throw new Error(`Advanced CP-VTON returned ${result.status}: ${result.statusText}`);
                    }
                    
                } catch (advancedError) {
                    console.log('Advanced CP-VTON failed:', advancedError.message);
                    console.log('Trying revolutionary clothing replacement fallback...');
                    document.getElementById('progressText').textContent = '⚡ Switching to simplified processing mode...';
                    
                    try {
                        result = await fetch('revolutionary_clothing_replacement.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                            },
                            body: JSON.stringify({
                                person_image: uploadData.temp_path,
                                items: garmentData,
                                output_dir: 'tryon_results'
                            })
                        });
                        
                        console.log('Revolutionary clothing replacement response status:', result.status);
                        methodUsed = 'Revolutionary Clothing Replacement';
                        
                        if (!result.ok) {
                            throw new Error(`Revolutionary processor returned ${result.status}: ${result.statusText}`);
                        }
                        
                    } catch (fallbackError) {
                        console.log('Revolutionary replacement also failed:', fallbackError.message);
                        throw new Error(`All processors failed. Working: ${workingError.message}, Enhanced: ${enhancedError.message}, Advanced: ${advancedError.message}, Revolutionary: ${fallbackError.message}`);
                    }
                }
            }
        }
        
        if (!result.ok) {
            throw new Error(`Server error: ${result.status} - ${result.statusText}`);
        }
        
        const responseText = await result.text();
        console.log('Server response:', responseText);
        console.log('Method used:', methodUsed);
        
        let data;
        try {
            data = JSON.parse(responseText);
        } catch (jsonError) {
            console.error('JSON parse error:', jsonError);
            console.error('Raw response:', responseText);
            throw new Error(`Invalid server response (${methodUsed}): ${responseText.substring(0, 200)}...`);
        }
        
        if (data.success) {
            clearInterval(progressInterval);
            document.getElementById('progressFill').style.width = '100%';
            document.getElementById('progressText').textContent = '🎉 Virtual try-on completed!';
            
            // Build web-accessible result URL
            if (data.result_image) {
                resultUrl = 'tryon_results/' + data.result_image;
            } else if (data.full_path) {
                // Extract filename from full path and construct web URL
                const filename = data.full_path.split(/[/\\]/).pop();
                resultUrl = 'tryon_results/' + filename;
            } else {
                throw new Error('No result image path provided');
            }
            
            console.log('Result URL:', resultUrl);
            
            setTimeout(() => {
                document.getElementById('loadingState').classList.remove('show');
                document.getElementById('resultFrame').src = resultUrl;
                document.getElementById('resultState').style.display = 'block';
                
                // Show success message with method info
                if (data.method) {
                    console.log('Success! Method used:', data.method);
                }
            }, 1000);
        } else {
            clearInterval(progressInterval);
            throw new Error(data.error || 'Try-on processing failed');
        }
        
    } catch (error) {
        clearInterval(progressInterval);
        console.error('Try-on error:', error);
        alert('Try-on failed: ' + error.message);
        document.getElementById('loadingState').classList.remove('show');
        document.getElementById('waitingState').style.display = 'block';
    } finally {
        clearInterval(progressInterval);
        document.getElementById('tryonBtn').disabled = false;
        updateTryOnButton();
    }
}

function openResultInNewTab() {
    if (resultUrl) {
        window.open(resultUrl, '_blank');
    }
}

function resetTryOn() {
    document.getElementById('resultState').style.display = 'none';
    document.getElementById('waitingState').style.display = 'block';
    clearImage();
    clearAll();
    resultUrl = null;
}

// Initialize
updateTryOnButton();
</script>

</body>
</html>

<?php include("includes/footer.php"); ?>
