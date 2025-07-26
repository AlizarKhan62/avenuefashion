<?php
include '../includes/db.php';

echo "=== ORDER STATUS ANALYSIS ===\n";

// Check customer_orders statuses
$check_statuses = "SELECT order_status, COUNT(*) as count, SUM(due_amount) as total FROM customer_orders GROUP BY order_status";
$result_statuses = mysqli_query($con, $check_statuses);
if($result_statuses) {
    echo "Customer Orders by Status:\n";
    while($row = mysqli_fetch_assoc($result_statuses)) {
        echo "  Status: '" . $row['order_status'] . "' - Count: " . $row['count'] . " - Total: Rs " . ($row['total'] ? number_format($row['total'], 2) : '0.00') . "\n";
    }
} else {
    echo "Error checking customer_orders statuses: " . mysqli_error($con) . "\n";
}

// Check pending_orders statuses
echo "\n";
$check_pending_statuses = "SELECT order_status, COUNT(*) as count, SUM(due_amount) as total FROM pending_orders GROUP BY order_status";
$result_pending_statuses = mysqli_query($con, $check_pending_statuses);
if($result_pending_statuses) {
    echo "Pending Orders by Status:\n";
    while($row = mysqli_fetch_assoc($result_pending_statuses)) {
        echo "  Status: '" . $row['order_status'] . "' - Count: " . $row['count'] . " - Total: Rs " . ($row['total'] ? number_format($row['total'], 2) : '0.00') . "\n";
    }
} else {
    echo "Error checking pending_orders statuses: " . mysqli_error($con) . "\n";
}

// Show actual calculation the admin is doing
echo "\n=== ADMIN CALCULATION RECREATION ===\n";

// Payment earnings (from payments table)
$get_payment_earnings = "SELECT SUM(amount) as PaymentTotal FROM payments";
$run_payment_earnings = mysqli_query($con, $get_payment_earnings);
if($run_payment_earnings) {
    $row_payment = mysqli_fetch_assoc($run_payment_earnings);
    $payment_earnings = $row_payment['PaymentTotal'] ? $row_payment['PaymentTotal'] : 0;
} else {
    $payment_earnings = 0;
}

// COD earnings (only delivered orders from customer_orders)
$get_cod_earnings = "SELECT SUM(due_amount) as CODTotal FROM customer_orders WHERE order_status = 'delivered'";
$run_cod_earnings = mysqli_query($con, $get_cod_earnings);
if($run_cod_earnings) {
    $row_cod = mysqli_fetch_assoc($run_cod_earnings);
    $cod_earnings = $row_cod['CODTotal'] ? $row_cod['CODTotal'] : 0;
} else {
    $cod_earnings = 0;
}

$count_total_earnings = $payment_earnings + $cod_earnings;

echo "Payment earnings (payments table): Rs " . number_format($payment_earnings, 2) . "\n";
echo "COD earnings (delivered orders only): Rs " . number_format($cod_earnings, 2) . "\n";
echo "TOTAL EARNINGS (Admin calculation): Rs " . number_format($count_total_earnings, 2) . "\n";

echo "\n=== END ANALYSIS ===\n";
?>
