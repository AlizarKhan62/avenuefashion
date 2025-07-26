<?php
include '../includes/db.php';

echo "=== ADMIN EARNINGS DEBUG ===\n";

// Check what's actually showing Rs 45834 on dashboard
echo "1. PENDING ORDERS TABLE - checking due_amount:\n";
$get_pending_earnings = "SELECT SUM(due_amount) as total FROM pending_orders WHERE order_status != 'pending'";
$run_pending = mysqli_query($con, $get_pending_earnings);
if($row_pending = mysqli_fetch_assoc($run_pending)) {
    echo "   Pending orders earnings: Rs " . ($row_pending['total'] ? number_format($row_pending['total'], 2) : '0.00') . "\n";
}

echo "\n2. CUSTOMER ORDERS TABLE - checking due_amount:\n";
$get_customer_earnings = "SELECT SUM(due_amount) as total FROM customer_orders WHERE order_status = 'delivered'";
$run_customer = mysqli_query($con, $get_customer_earnings);
if($row_customer = mysqli_fetch_assoc($run_customer)) {
    echo "   Customer orders earnings: Rs " . ($row_customer['total'] ? number_format($row_customer['total'], 2) : '0.00') . "\n";
}

echo "\n3. ALL CUSTOMER ORDERS - checking due_amount:\n";
$get_all_customer_earnings = "SELECT SUM(due_amount) as total FROM customer_orders";
$run_all_customer = mysqli_query($con, $get_all_customer_earnings);
if($row_all_customer = mysqli_fetch_assoc($run_all_customer)) {
    echo "   All customer orders: Rs " . ($row_all_customer['total'] ? number_format($row_all_customer['total'], 2) : '0.00') . "\n";
}

echo "\n4. PAYMENTS TABLE - checking amount:\n";
$get_payments_earnings = "SELECT SUM(amount) as total FROM payments";
$run_payments = mysqli_query($con, $get_payments_earnings);
if($row_payments = mysqli_fetch_assoc($run_payments)) {
    echo "   Payments table earnings: Rs " . ($row_payments['total'] ? number_format($row_payments['total'], 2) : '0.00') . "\n";
} else {
    echo "   No payments found or table doesn't exist\n";
}

echo "\n5. CHECKING ACTUAL CALCULATIONS USED IN ADMIN:\n";
// This mimics the calculation in admin_area/index.php
$get_payment_earnings = "SELECT SUM(amount) as PaymentTotal FROM payments";
$run_payment_earnings = mysqli_query($con, $get_payment_earnings);
if($run_payment_earnings) {
    $row_payment = mysqli_fetch_assoc($run_payment_earnings);
    $payment_earnings = $row_payment['PaymentTotal'] ? $row_payment['PaymentTotal'] : 0;
} else {
    $payment_earnings = 0;
}

$get_cod_earnings = "SELECT SUM(due_amount) as CODTotal FROM customer_orders WHERE order_status = 'delivered'";
$run_cod_earnings = mysqli_query($con, $get_cod_earnings);
if($run_cod_earnings) {
    $row_cod = mysqli_fetch_assoc($run_cod_earnings);
    $cod_earnings = $row_cod['CODTotal'] ? $row_cod['CODTotal'] : 0;
} else {
    $cod_earnings = 0;
}

$count_total_earnings = $payment_earnings + $cod_earnings;
echo "   Admin calculation: Rs " . number_format($count_total_earnings, 2) . "\n";
echo "   (Payments: Rs " . number_format($payment_earnings, 2) . " + COD: Rs " . number_format($cod_earnings, 2) . ")\n";

echo "\n6. PAYMENT RECORDS COUNT:\n";
$payment_count_query = "SELECT COUNT(*) as count FROM payments";
$payment_count_result = mysqli_query($con, $payment_count_query);
if($payment_count_row = mysqli_fetch_assoc($payment_count_result)) {
    echo "   Total payment records: " . $payment_count_row['count'] . "\n";
}

echo "\n=== END DEBUG ===\n";
?>
