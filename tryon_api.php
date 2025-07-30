<?php
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    echo json_encode(['success' => false, 'error' => 'Invalid JSON input']);
    exit;
}

// Validate required fields
if (!isset($input['input']['garm_img']) || !isset($input['input']['human_img'])) {
    echo json_encode(['success' => false, 'error' => 'Missing required image URLs']);
    exit;
}

// Get API token from environment variable (secure approach)
$apiToken = getenv('REPLICATE_API_TOKEN') ?: $_ENV['REPLICATE_API_TOKEN'] ?? null;

// Fallback to config file if environment variable not set
if (!$apiToken) {
    if (file_exists('config/api_config.php')) {
        include_once 'config/api_config.php';
        $apiToken = defined('REPLICATE_API_TOKEN') ? REPLICATE_API_TOKEN : null;
    }
}

if (!$apiToken) {
    echo json_encode(['success' => false, 'error' => 'API token not configured. Please set REPLICATE_API_TOKEN environment variable.']);
    exit;
}

// Prepare the API request
$apiData = [
    'version' => $input['version'],
    'input' => [
        'garm_img' => $input['input']['garm_img'],
        'human_img' => $input['input']['human_img'],
        'garment_des' => $input['input']['garment_des'] ?? 'clothing item'
    ]
];

// Make the API request to Replicate
$ch = curl_init();
curl_setopt_array($ch, [
    CURLOPT_URL => 'https://api.replicate.com/v1/predictions',
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_HTTPHEADER => [
        'Authorization: Bearer ' . $apiToken,
        'Content-Type: application/json',
        'Prefer: wait'
    ],
    CURLOPT_POSTFIELDS => json_encode($apiData),
    CURLOPT_TIMEOUT => 120, // 2 minutes timeout
    CURLOPT_SSL_VERIFYPEER => true
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlError = curl_error($ch);
curl_close($ch);

if ($curlError) {
    echo json_encode(['success' => false, 'error' => 'Network error: ' . $curlError]);
    exit;
}

if ($httpCode !== 200 && $httpCode !== 201) {
    echo json_encode(['success' => false, 'error' => 'API request failed with status: ' . $httpCode]);
    exit;
}

$result = json_decode($response, true);

if (!$result) {
    echo json_encode(['success' => false, 'error' => 'Invalid API response']);
    exit;
}

// Check if the prediction was successful
if (isset($result['status']) && $result['status'] === 'succeeded' && isset($result['output'])) {
    // The output should be an image URL
    $outputUrl = is_array($result['output']) ? $result['output'][0] : $result['output'];
    
    echo json_encode([
        'success' => true,
        'output_url' => $outputUrl,
        'prediction_id' => $result['id'] ?? null
    ]);
} else if (isset($result['status']) && $result['status'] === 'failed') {
    echo json_encode([
        'success' => false, 
        'error' => 'Try-on generation failed: ' . ($result['error'] ?? 'Unknown error')
    ]);
} else {
    // If status is still processing, we might need to poll
    echo json_encode([
        'success' => false,
        'error' => 'Try-on is still processing. Please try again in a moment.',
        'status' => $result['status'] ?? 'unknown'
    ]);
}
?>