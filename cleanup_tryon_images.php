<?php
// This script can be run periodically to clean up old uploaded images
// You can set it up as a cron job to run daily

$uploadDir = 'uploads/tryon/';
$maxAge = 24 * 60 * 60; // 24 hours in seconds

if (is_dir($uploadDir)) {
    $files = glob($uploadDir . '*');
    $now = time();
    
    foreach ($files as $file) {
        if (is_file($file)) {
            $fileAge = $now - filemtime($file);
            if ($fileAge > $maxAge) {
                unlink($file);
                echo "Deleted old file: " . basename($file) . "\n";
            }
        }
    }
}

echo "Cleanup completed.\n";
?>