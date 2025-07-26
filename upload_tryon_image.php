<?php
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['success' => false, 'error' => 'No image uploaded or upload error']);
    exit;
}

$uploadDir = 'uploads/tryon/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

$allowedTypes = ['image/jpeg', 'image/png', 'image/jpg'];
$fileType = $_FILES['image']['type'];

if (!in_array($fileType, $allowedTypes)) {
    echo json_encode(['success' => false, 'error' => 'Invalid file type. Only JPEG and PNG allowed.']);
    exit;
}

// Generate unique filename
$extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
$filename = uniqid('tryon_', true) . '.' . $extension;
$filepath = $uploadDir . $filename;

if (move_uploaded_file($_FILES['image']['tmp_name'], $filepath)) {
    $fullUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . 
               '://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['REQUEST_URI']) . '/' . $filepath;
    
    echo json_encode([
        'success' => true,
        'url' => $fullUrl,
        'filename' => $filename
    ]);
} else {
    echo json_encode(['success' => false, 'error' => 'Failed to save uploaded file']);
}
?>
