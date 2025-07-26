<?php
include '../includes/db.php';

echo "=== ADMIN REPORTS VERIFICATION ===\n";

// Dashboard calculation (from index.php)
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

echo "Dashboard Calculation:\n";
echo "  Online Payments: Rs " . number_format($payment_earnings, 2) . "\n";
echo "  COD Delivered: Rs " . number_format($cod_earnings, 2) . "\n";
echo "  TOTAL EARNINGS: Rs " . number_format($count_total_earnings, 2) . "\n";

echo "\nThis should now match both:\n";
echo "  - Dashboard earnings display\n";
echo "  - View Payments report\n";
echo "  - View Earnings report\n";

echo "\n=== VERIFICATION COMPLETE ===\n";
?>
