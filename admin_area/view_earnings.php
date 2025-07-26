<?php

if(!isset($_SESSION['admin_email'])){
    echo "<script>window.open('login.php','_self')</script>";
} else {

?>

<div class="row">
    <div class="col-lg-12">
        <ol class="breadcrumb">
            <li class="active">
                <i class="fa fa-dashboard"></i> Dashboard / View Earnings Report
            </li>
        </ol>
    </div>
</div>

<div class="row">
    <div class="col-lg-12">
        <div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title">
                    <i class="fa fa-money fa-fw"></i> Comprehensive Earnings Report
                </h3>
            </div>
            <div class="panel-body">

                <?php
                // Calculate earnings using same logic as dashboard
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

                // Total earnings = Online payments + COD delivered orders (matches dashboard)
                $total_earnings = $payment_earnings + $cod_earnings;

                // Additional breakdowns
                $get_stripe_earnings = "SELECT SUM(amount) as total FROM payments WHERE payment_mode = 'Stripe'";
                $run_stripe = mysqli_query($con,$get_stripe_earnings);
                $row_stripe = mysqli_fetch_array($run_stripe);
                $stripe_earnings = $row_stripe['total'] ? $row_stripe['total'] : 0;

                // Get pending orders value (not included in earnings)
                $get_pending_value = "SELECT SUM(due_amount) as total FROM customer_orders WHERE order_status = 'pending'";
                $run_pending = mysqli_query($con,$get_pending_value);
                $row_pending = mysqli_fetch_array($run_pending);
                $pending_value = $row_pending['total'] ? $row_pending['total'] : 0;

                // Get order counts
                $get_delivered_count = "SELECT COUNT(*) as count FROM customer_orders WHERE order_status = 'delivered'";
                $run_delivered_count = mysqli_query($con,$get_delivered_count);
                $row_delivered_count = mysqli_fetch_array($run_delivered_count);
                $delivered_count = $row_delivered_count['count'];

                $get_pending_count = "SELECT COUNT(*) as count FROM customer_orders WHERE order_status = 'pending'";
                $run_pending_count = mysqli_query($con,$get_pending_count);
                $row_pending_count = mysqli_fetch_array($run_pending_count);
                $pending_count = $row_pending_count['count'];

                $get_payment_count = "SELECT COUNT(*) as count FROM payments";
                $run_payment_count = mysqli_query($con,$get_payment_count);
                $row_payment_count = mysqli_fetch_array($run_payment_count);
                $payment_count = $row_payment_count['count'];

                // Monthly earnings (updated calculation)
                $get_monthly_payments = "SELECT SUM(amount) as total FROM payments WHERE MONTH(payment_date) = MONTH(CURDATE()) AND YEAR(payment_date) = YEAR(CURDATE())";
                $run_monthly_payments = mysqli_query($con,$get_monthly_payments);
                $row_monthly_payments = mysqli_fetch_array($run_monthly_payments);
                $monthly_payment_earnings = $row_monthly_payments['total'] ? $row_monthly_payments['total'] : 0;

                $get_monthly_cod = "SELECT SUM(due_amount) as total FROM customer_orders WHERE order_status = 'delivered' AND MONTH(order_date) = MONTH(CURDATE()) AND YEAR(order_date) = YEAR(CURDATE())";
                $run_monthly_cod = mysqli_query($con,$get_monthly_cod);
                $row_monthly_cod = mysqli_fetch_array($run_monthly_cod);
                $monthly_cod_earnings = $row_monthly_cod['total'] ? $row_monthly_cod['total'] : 0;

                $monthly_earnings = $monthly_payment_earnings + $monthly_cod_earnings;

                // Today's earnings (updated calculation) 
                $get_today_payments = "SELECT SUM(amount) as total FROM payments WHERE DATE(payment_date) = CURDATE()";
                $run_today_payments = mysqli_query($con,$get_today_payments);
                $row_today_payments = mysqli_fetch_array($run_today_payments);
                $today_payment_earnings = $row_today_payments['total'] ? $row_today_payments['total'] : 0;

                $get_today_cod = "SELECT SUM(due_amount) as total FROM customer_orders WHERE order_status = 'delivered' AND DATE(order_date) = CURDATE()";
                $run_today_cod = mysqli_query($con,$get_today_cod);
                $row_today_cod = mysqli_fetch_array($run_today_cod);
                $today_cod_earnings = $row_today_cod['total'] ? $row_today_cod['total'] : 0;

                $today_earnings = $today_payment_earnings + $today_cod_earnings;

                ?>

                <!-- Summary Cards -->
                <div class="row">
                    <div class="col-md-2">
                        <div class="panel panel-success">
                            <div class="panel-body text-center">
                                <h4>Total Earnings</h4>
                                <h3><strong>Rs <?php echo number_format($total_earnings, 2); ?></strong></h3>
                                <p><small><em>Matches Dashboard</em></small></p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="panel panel-warning">
                            <div class="panel-body text-center">
                                <h4>Pending Orders</h4>
                                <h3><strong>Rs <?php echo number_format($pending_value, 2); ?></strong></h3>
                                <p><?php echo $pending_count; ?> orders</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="panel panel-primary">
                            <div class="panel-body text-center">
                                <h4>Online Payments</h4>
                                <h3><strong>Rs <?php echo number_format($payment_earnings, 2); ?></strong></h3>
                                <p><?php echo $payment_count; ?> payments</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="panel panel-info">
                            <div class="panel-body text-center">
                                <h4>COD Delivered</h4>
                                <h3><strong>Rs <?php echo number_format($cod_earnings, 2); ?></strong></h3>
                                <p><?php echo $delivered_count; ?> orders</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="panel panel-default">
                            <div class="panel-body text-center">
                                <h4>This Month</h4>
                                <h3><strong>Rs <?php echo number_format($monthly_earnings, 2); ?></strong></h3>
                                <p><?php echo date('M Y'); ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="panel panel-default">
                            <div class="panel-body text-center">
                                <h4>Today</h4>
                                <h3><strong>Rs <?php echo number_format($today_earnings, 2); ?></strong></h3>
                                <p><?php echo date('M d'); ?></p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Detailed Breakdown -->
                <div class="row">
                    <div class="col-md-6">
                        <div class="panel panel-default">
                            <div class="panel-heading">
                                <h4>Payment Method Breakdown</h4>
                            </div>
                            <div class="panel-body">
                                <table class="table table-hover">
                                    <tr>
                                        <td><i class="fa fa-credit-card text-primary"></i> Stripe Payments</td>
                                        <td class="text-right"><strong>Rs <?php echo number_format($stripe_earnings, 2); ?></strong></td>
                                        <td class="text-right"><?php echo $total_earnings > 0 ? round(($stripe_earnings / $total_earnings) * 100, 1) : 0; ?>%</td>
                                    </tr>
                                    <tr>
                                        <td><i class="fa fa-money text-success"></i> COD Delivered</td>
                                        <td class="text-right"><strong>Rs <?php echo number_format($cod_earnings, 2); ?></strong></td>
                                        <td class="text-right"><?php echo $total_earnings > 0 ? round(($cod_earnings / $total_earnings) * 100, 1) : 0; ?>%</td>
                                    </tr>
                                    <tr>
                                        <td><i class="fa fa-clock-o text-warning"></i> COD Pending</td>
                                        <td class="text-right"><strong>Rs <?php echo number_format($pending_value, 2); ?></strong></td>
                                        <td class="text-right"><?php echo ($total_earnings + $pending_value) > 0 ? round(($pending_value / ($total_earnings + $pending_value)) * 100, 1) : 0; ?>%</td>
                                    </tr>
                                    <tr class="success">
                                        <td><strong>Total Potential</strong></td>
                                        <td class="text-right"><strong>Rs <?php echo number_format($total_earnings + $pending_value, 2); ?></strong></td>
                                        <td class="text-right"><strong>100%</strong></td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="panel panel-default">
                            <div class="panel-heading">
                                <h4>Recent Earnings Activity</h4>
                            </div>
                            <div class="panel-body">
                                <?php
                                $get_recent = "SELECT p.*, co.tracking_number 
                                              FROM payments p 
                                              LEFT JOIN customer_orders co ON p.invoice_no = co.invoice_no 
                                              WHERE (p.payment_mode != 'Cash on Delivery' OR p.code != 'PENDING')
                                              ORDER BY p.payment_date DESC 
                                              LIMIT 5";
                                $run_recent = mysqli_query($con, $get_recent);
                                
                                if(mysqli_num_rows($run_recent) > 0):
                                ?>
                                <table class="table table-hover table-sm">
                                    <thead>
                                        <tr>
                                            <th>Invoice</th>
                                            <th>Amount</th>
                                            <th>Method</th>
                                            <th>Date</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while($row_recent = mysqli_fetch_array($run_recent)): ?>
                                        <tr>
                                            <td>#<?php echo $row_recent['invoice_no']; ?></td>
                                            <td>Rs <?php echo number_format($row_recent['amount'], 0); ?></td>
                                            <td>
                                                <?php if($row_recent['payment_mode'] == 'Stripe'): ?>
                                                    <span class="label label-primary">Stripe</span>
                                                <?php else: ?>
                                                    <span class="label label-success">COD</span>
                                                <?php endif; ?>
                                            </td>
                                            <td><?php echo date('M d', strtotime($row_recent['payment_date'])); ?></td>
                                        </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                                <?php else: ?>
                                <p class="text-muted">No recent earnings activity found.</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Monthly Trend -->
                <div class="row">
                    <div class="col-md-12">
                        <div class="panel panel-default">
                            <div class="panel-heading">
                                <h4>Monthly Earnings Trend (Last 6 Months)</h4>
                            </div>
                            <div class="panel-body">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Month</th>
                                            <th>Stripe Earnings</th>
                                            <th>COD Earnings</th>
                                            <th>Total Earnings</th>
                                            <th>Orders</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        for($i = 5; $i >= 0; $i--) {
                                            $month = date('Y-m', strtotime("-$i months"));
                                            $month_name = date('M Y', strtotime("-$i months"));
                                            
                                            $get_month_stripe = "SELECT SUM(amount) as total FROM payments WHERE DATE_FORMAT(payment_date, '%Y-%m') = '$month' AND payment_mode = 'Stripe'";
                                            $run_month_stripe = mysqli_query($con, $get_month_stripe);
                                            $row_month_stripe = mysqli_fetch_array($run_month_stripe);
                                            $month_stripe = $row_month_stripe['total'] ? $row_month_stripe['total'] : 0;
                                            
                                            $get_month_cod = "SELECT SUM(amount) as total FROM payments WHERE DATE_FORMAT(payment_date, '%Y-%m') = '$month' AND payment_mode = 'Cash on Delivery' AND code != 'PENDING'";
                                            $run_month_cod = mysqli_query($con, $get_month_cod);
                                            $row_month_cod = mysqli_fetch_array($run_month_cod);
                                            $month_cod = $row_month_cod['total'] ? $row_month_cod['total'] : 0;
                                            
                                            $month_total = $month_stripe + $month_cod;
                                            
                                            $get_month_orders = "SELECT COUNT(DISTINCT invoice_no) as count FROM payments WHERE DATE_FORMAT(payment_date, '%Y-%m') = '$month'";
                                            $run_month_orders = mysqli_query($con, $get_month_orders);
                                            $row_month_orders = mysqli_fetch_array($run_month_orders);
                                            $month_orders = $row_month_orders['count'];
                                        ?>
                                        <tr>
                                            <td><?php echo $month_name; ?></td>
                                            <td>Rs <?php echo number_format($month_stripe, 2); ?></td>
                                            <td>Rs <?php echo number_format($month_cod, 2); ?></td>
                                            <td><strong>Rs <?php echo number_format($month_total, 2); ?></strong></td>
                                            <td><?php echo $month_orders; ?> orders</td>
                                        </tr>
                                        <?php } ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<?php } ?>
