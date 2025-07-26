<?php
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['user_image']) || !isset($input['product_image'])) {
    echo json_encode(['success' => false, 'error' => 'Missing required parameters']);
    exit;
}

$userImage = $input['user_image'];
$productImage = $input['product_image'];
$productId = $input['product_id'] ?? null;

// Replicate API configuration
// IMPORTANT: Replace with your actual API token from replicate.com
$apiToken = 'r8_Y7NmTZy8Cdo8WIrzjTmArQagjCnm4TT2YFQH9'; // Replace this!

// Updated model options - choose one:
// Option 1: IDM-VTON (Best quality, most popular)
$modelOwner = 'cuuupid';
$modelName = 'idm-vton';

// Option 2: Flux-VTON (Alternative)
// $modelOwner = 'subhash25rawat';
// $modelName = 'flux-vton';

// Option 3: E-commerce virtual try-on
// $modelOwner = 'wolverinn';
// $modelName = 'ecommerce-virtual-try-on';

try {
    // Validate inputs are URLs or base64
    if (!filter_var($userImage, FILTER_VALIDATE_URL) && !isBase64Image($userImage)) {
        throw new Exception('Invalid user image format');
    }
    
    if (!filter_var($productImage, FILTER_VALIDATE_URL) && !isBase64Image($productImage)) {
        throw new Exception('Invalid product image format');
    }

    // Get latest model version
    $modelUrl = "https://api.replicate.com/v1/models/$modelOwner/$modelName";
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $modelUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Authorization: Token " . $apiToken,
        "User-Agent: VirtualTryOn/1.0"
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode !== 200) {
        throw new Exception("Failed to get model info (HTTP $httpCode): " . $response);
    }
    
    $modelInfo = json_decode($response, true);
    $latestVersion = $modelInfo['latest_version']['id'];
    
    // Create prediction with latest version
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "https://api.replicate.com/v1/predictions");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
        "version" => $latestVersion,
        "input" => [
            "human_img" => $userImage,        // IDM-VTON uses 'human_img'
            "garm_img" => $productImage,      // IDM-VTON uses 'garm_img'
            "garment_des" => "clothing item"  // Optional description
        ]
    ]));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Authorization: Token " . $apiToken,
        "Content-Type: application/json",
        "User-Agent: VirtualTryOn/1.0"
    ]);
    
    // Add SSL verification and timeout
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);

    // Better error handling
    if ($curlError) {
        throw new Exception('cURL Error: ' . $curlError);
    }

    if ($httpCode !== 201) {
        $errorData = json_decode($response, true);
        $errorMessage = $errorData['detail'] ?? $response;
        throw new Exception("API Error (HTTP $httpCode): " . $errorMessage);
    }

    $result = json_decode($response, true);
    
    if (!isset($result['id'])) {
        throw new Exception('Invalid API response: Missing prediction ID');
    }
    
    $predictionId = $result['id'];

    // Poll for completion with exponential backoff
    $maxAttempts = 60; // 5 minutes max
    $attempt = 0;
    $waitTime = 3; // Start with 3 seconds
    
    do {
        sleep($waitTime);
        $attempt++;
        
        // Exponential backoff: increase wait time slightly each attempt
        $waitTime = min($waitTime + 1, 10); // Max 10 seconds between checks
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://api.replicate.com/v1/predictions/" . $predictionId);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Authorization: Token " . $apiToken,
            "User-Agent: VirtualTryOn/1.0"
        ]);
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($curlError) {
            throw new Exception('cURL Error during polling: ' . $curlError);
        }

        if ($httpCode !== 200) {
            throw new Exception("Failed to get prediction status (HTTP $httpCode)");
        }

        $result = json_decode($response, true);
        $status = $result['status'] ?? 'unknown';
        
        if ($status === 'succeeded') {
            // Validate output exists
            if (!isset($result['output']) || empty($result['output'])) {
                throw new Exception('Prediction succeeded but no output received');
            }
            
            // Save result to database if needed
            if ($productId) {
                saveTrialResult($productId, $userImage, $result['output']);
            }
            
            echo json_encode([
                'success' => true,
                'output_url' => $result['output'],
                'prediction_id' => $predictionId,
                'processing_time' => $attempt * $waitTime . ' seconds'
            ]);
            exit;
            
        } elseif ($status === 'failed') {
            $errorMsg = $result['error'] ?? 'Unknown error';
            throw new Exception('Prediction failed: ' . $errorMsg);
        } elseif ($status === 'canceled') {
            throw new Exception('Prediction was canceled');
        }
        
    } while (($status === 'starting' || $status === 'processing') && $attempt < $maxAttempts);
    
    if ($attempt >= $maxAttempts) {
        throw new Exception('Prediction timed out after ' . ($maxAttempts * 5) . ' seconds');
    }
    
} catch (Exception $e) {
    error_log('Try-on API error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'timestamp' => date('Y-m-d H:i:s')
    ]);
}

function saveTrialResult($productId, $userImage, $resultImage) {
    try {
        include("includes/db.php");
        
        if (!$con) {
            error_log('Database connection failed in saveTrialResult');
            return false;
        }
        
        $productId = mysqli_real_escape_string($con, $productId);
        $userImage = mysqli_real_escape_string($con, $userImage);
        $resultImage = mysqli_real_escape_string($con, $resultImage);
        $sessionId = session_id() ?: 'no-session';
        
        $query = "INSERT INTO virtual_tryons (product_id, user_image, result_image, session_id, created_at) 
                  VALUES ('$productId', '$userImage', '$resultImage', '$sessionId', NOW())";
        
        $result = mysqli_query($con, $query);
        
        if (!$result) {
            error_log('Database insert failed: ' . mysqli_error($con));
            return false;
        }
        
        return true;
        
    } catch (Exception $e) {
        error_log('saveTrialResult error: ' . $e->getMessage());
        return false;
    }
}

function isBase64Image($string) {
    // Check if string starts with data:image
    if (strpos($string, 'data:image/') === 0) {
        return true;
    }
    
    // Check if it's a valid base64 string
    if (base64_encode(base64_decode($string, true)) === $string) {
        return true;
    }
    
    return false;
}

// Optional: Test function to get available models
function getAvailableModels($apiToken) {
    $models = [
        'cuuupid/idm-vton',
        'subhash25rawat/flux-vton',
        'wolverinn/ecommerce-virtual-try-on'
    ];
    
    $available = [];
    
    foreach ($models as $model) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://api.replicate.com/v1/models/$model");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Authorization: Token " . $apiToken
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode === 200) {
            $modelInfo = json_decode($response, true);
            $available[] = [
                'name' => $model,
                'version' => $modelInfo['latest_version']['id'],
                'description' => $modelInfo['description']
            ];
        }
    }
    
    return $available;
}
?>