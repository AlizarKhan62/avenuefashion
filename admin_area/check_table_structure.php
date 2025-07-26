<?php
include '../includes/db.php';

echo "=== DATABASE TABLES CHECK ===\n";

// Show all tables
$show_tables = "SHOW TABLES";
$result = mysqli_query($con, $show_tables);
if($result) {
    echo "Available tables:\n";
    while($row = mysqli_fetch_array($result)) {
        echo "  - " . $row[0] . "\n";
    }
} else {
    echo "Error checking tables: " . mysqli_error($con) . "\n";
}

echo "\n=== CHECKING EARNINGS TABLES ===\n";

// Check if payments table exists and has data
$check_payments = "SELECT COUNT(*) as count, SUM(amount) as total FROM payments";
$result_payments = mysqli_query($con, $check_payments);
if($result_payments) {
    $row_payments = mysqli_fetch_assoc($result_payments);
    echo "Payments table: " . $row_payments['count'] . " records, Total: Rs " . ($row_payments['total'] ? number_format($row_payments['total'], 2) : '0.00') . "\n";
} else {
    echo "Payments table error: " . mysqli_error($con) . "\n";
}

// Check if customer_orders table exists and has data
$check_customer_orders = "SELECT COUNT(*) as count, SUM(due_amount) as total FROM customer_orders";
$result_customer_orders = mysqli_query($con, $check_customer_orders);
if($result_customer_orders) {
    $row_customer_orders = mysqli_fetch_assoc($result_customer_orders);
    echo "Customer_orders table: " . $row_customer_orders['count'] . " records, Total: Rs " . ($row_customer_orders['total'] ? number_format($row_customer_orders['total'], 2) : '0.00') . "\n";
} else {
    echo "Customer_orders table error: " . mysqli_error($con) . "\n";
}

// Check if pending_orders table exists
$check_pending_orders = "SELECT COUNT(*) as count FROM pending_orders";
$result_pending_orders = mysqli_query($con, $check_pending_orders);
if($result_pending_orders) {
    echo "Pending_orders table exists with records\n";
} else {
    echo "Pending_orders table does not exist or error: " . mysqli_error($con) . "\n";
}

echo "\n=== END TABLES CHECK ===\n";
?>
