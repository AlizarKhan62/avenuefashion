<?php
// Test view_earnings.php for undefined variables
session_start();
$_SESSION['admin_email'] = 'test@admin.com'; // Fake admin session

// Include database connection
include '../includes/db.php';

// Buffer output to catch warnings
ob_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "Testing view_earnings.php...\n";

// Include the earnings view
include 'view_earnings.php';

// Get any output/errors
$output = ob_get_clean();

// Check for warnings
if (strpos($output, 'Warning') !== false || strpos($output, 'Notice') !== false) {
    echo "ERRORS FOUND:\n";
    echo $output;
} else {
    echo "No PHP errors found in view_earnings.php\n";
}

echo "Test completed.\n";
?>
