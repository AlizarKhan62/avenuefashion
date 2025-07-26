<?php
session_start();
include("includes/db.php");
include("includes/header.php");
include("functions/functions.php");
include("includes/main.php");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Track Your Order</title>
    <link rel="stylesheet" href="styles/bootstrap-337.min.css">
    <link rel="stylesheet" href="font-awesome/css/font-awesome.min.css">
    <style>
        .tracking-container {
            max-width: 800px;
            margin: 40px auto;
            padding: 20px;
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .tracking-header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #f8f9fa;
        }
        
        .tracking-search {
            margin-bottom: 30px;
        }
        
        .tracking-input {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
        }
        
        .tracking-input input {
            flex: 1;
            padding: 12px;
            border: 2px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
        }
        
        .tracking-input button {
            padding: 12px 24px;
            background: #007bff;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            transition: background 0.3s;
        }
        
        .tracking-input button:hover {
            background: #0056b3;
        }
        
        .order-info {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 30px;
        }
        
        .order-info h4 {
            color: #333;
            margin-bottom: 15px;
        }
        
        .info-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            padding: 5px 0;
            border-bottom: 1px solid #eee;
        }
        
        .info-row:last-child {
            border-bottom: none;
        }
        
        .tracking-timeline {
            position: relative;
            padding-left: 30px;
        }
        
        .tracking-timeline::before {
            content: '';
            position: absolute;
            left: 15px;
            top: 0;
            bottom: 0;
            width: 2px;
            background: #ddd;
        }
        
        .timeline-item {
            position: relative;
            padding: 20px 0 20px 20px;
            margin-bottom: 20px;
        }
        
        .timeline-item::before {
            content: '';
            position: absolute;
            left: -25px;
            top: 25px;
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background: #ddd;
            border: 3px solid #fff;
            box-shadow: 0 0 0 2px #ddd;
        }
        
        .timeline-item.active::before {
            background: #28a745;
            box-shadow: 0 0 0 2px #28a745;
        }
        
        .timeline-item.current::before {
            background: #007bff;
            box-shadow: 0 0 0 2px #007bff;
            animation: pulse 2s infinite;
        }
        
        @keyframes pulse {
            0% { box-shadow: 0 0 0 2px #007bff, 0 0 0 0 rgba(0,123,255,0.7); }
            70% { box-shadow: 0 0 0 2px #007bff, 0 0 0 10px rgba(0,123,255,0); }
            100% { box-shadow: 0 0 0 2px #007bff, 0 0 0 0 rgba(0,123,255,0); }
        }
        
        .timeline-content {
            background: #fff;
            padding: 15px;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        
        .timeline-content h5 {
            margin: 0 0 5px 0;
            color: #333;
        }
        
        .timeline-content p {
            margin: 0 0 5px 0;
            color: #666;
        }
        
        .timeline-content .time {
            font-size: 12px;
            color: #999;
        }
        
        .status-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
        }
        
        .status-pending { background: #ffc107; color: #856404; }
        .status-confirmed { background: #17a2b8; color: #0c5460; }
        .status-processing { background: #fd7e14; color: #8a2308; }
        .status-packed { background: #6f42c1; color: #3d1a78; }
        .status-shipped { background: #20c997; color: #0f5132; }
        .status-out_for_delivery { background: #0dcaf0; color: #055160; }
        .status-delivered { background: #198754; color: #0a3622; }
        .status-cancelled { background: #dc3545; color: #721c24; }
        
        .no-tracking {
            text-align: center;
            padding: 40px;
            color: #666;
        }
        
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border: 1px solid transparent;
            border-radius: 4px;
        }
        
        .alert-danger {
            color: #721c24;
            background-color: #f8d7da;
            border-color: #f5c6cb;
        }
        
        .alert-info {
            color: #055160;
            background-color: #d1ecf1;
            border-color: #bee5eb;
        }
    </style>
</head>

<body>
    <main>
        <div class="nero">
            <div class="nero__heading">
                <span class="nero__bold">TRACK</span> Your Order
            </div>
            <p class="nero__text">Enter your tracking number or invoice number to check your order status</p>
        </div>
    </main>

    <div class="tracking-container">
        <div class="tracking-header">
            <h2><i class="fa fa-truck"></i> Order Tracking</h2>
            <p>Track your order in real-time and get delivery updates</p>
        </div>

        <div class="tracking-search">
            <form method="GET" action="">
                <div class="tracking-input">
                    <input type="text" 
                           name="tracking" 
                           placeholder="Enter Tracking Number (e.g., TRK20250623123456) or Invoice Number" 
                           value="<?php echo isset($_GET['tracking']) ? htmlspecialchars($_GET['tracking']) : ''; ?>"
                           required>
                    <button type="submit">
                        <i class="fa fa-search"></i> Track Order
                    </button>
                </div>
            </form>
            
            <div class="alert alert-info">
                <strong>How to track:</strong> You can use either your tracking number (starts with TRK) or invoice number to track your order.
            </div>
        </div>

        <?php
        if (isset($_GET['tracking']) && !empty($_GET['tracking'])) {
            $tracking_input = mysqli_real_escape_string($con, $_GET['tracking']);
            
            // Search by tracking number or invoice number
            $search_query = "SELECT co.*, c.customer_name, c.customer_email, c.customer_contact 
                           FROM customer_orders co 
                           LEFT JOIN customers c ON co.customer_id = c.customer_id 
                           WHERE co.tracking_number = '$tracking_input' 
                           OR co.invoice_no = '$tracking_input' 
                           LIMIT 1";
            
            $search_result = mysqli_query($con, $search_query);
            
            if ($search_result && mysqli_num_rows($search_result) > 0) {
                $order = mysqli_fetch_array($search_result);
                
                // Get tracking history
                $tracking_query = "SELECT * FROM order_tracking 
                                 WHERE order_id = '{$order['order_id']}' 
                                 OR tracking_number = '{$order['tracking_number']}' 
                                 ORDER BY created_date ASC";
                $tracking_result = mysqli_query($con, $tracking_query);
                
                // Get order items
                $items_query = "SELECT po.*, p.product_title, p.product_img1 
                              FROM pending_orders po 
                              LEFT JOIN products p ON po.product_id = p.product_id 
                              WHERE po.invoice_no = '{$order['invoice_no']}'";
                $items_result = mysqli_query($con, $items_query);
                ?>
                
                <div class="order-info">
                    <h4><i class="fa fa-info-circle"></i> Order Information</h4>
                    <div class="info-row">
                        <strong>Order ID:</strong>
                        <span>#<?php echo $order['order_id']; ?></span>
                    </div>
                    <div class="info-row">
                        <strong>Invoice Number:</strong>
                        <span><?php echo $order['invoice_no']; ?></span>
                    </div>
                    <div class="info-row">
                        <strong>Tracking Number:</strong>
                        <span><?php echo $order['tracking_number']; ?></span>
                    </div>
                    <div class="info-row">
                        <strong>Order Date:</strong>
                        <span><?php echo date('M d, Y h:i A', strtotime($order['order_date'])); ?></span>
                    </div>
                    <div class="info-row">
                        <strong>Total Amount:</strong>
                        <span>Rs <?php echo number_format($order['due_amount'], 2); ?></span>
                    </div>
                    <div class="info-row">
                        <strong>Current Status:</strong>
                        <span class="status-badge status-<?php echo str_replace(' ', '_', $order['current_status']); ?>">
                            <?php echo ucwords(str_replace('_', ' ', $order['current_status'])); ?>
                        </span>
                    </div>
                    <?php if ($order['estimated_delivery']): ?>
                    <div class="info-row">
                        <strong>Estimated Delivery:</strong>
                        <span><?php echo date('M d, Y', strtotime($order['estimated_delivery'])); ?></span>
                    </div>
                    <?php endif; ?>
                    <?php if ($order['customer_name']): ?>
                    <div class="info-row">
                        <strong>Customer:</strong>
                        <span><?php echo $order['customer_name']; ?></span>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Order Items -->
                <?php if ($items_result && mysqli_num_rows($items_result) > 0): ?>
                <div class="order-info">
                    <h4><i class="fa fa-shopping-bag"></i> Order Items</h4>
                    <?php while ($item = mysqli_fetch_array($items_result)): ?>
                    <div class="info-row">
                        <div>
                            <strong><?php echo $item['product_title']; ?></strong>
                            <?php if ($item['size']): ?>
                                <br><small>Size: <?php echo $item['size']; ?></small>
                            <?php endif; ?>
                        </div>
                        <span>Qty: <?php echo $item['qty']; ?></span>
                    </div>
                    <?php endwhile; ?>
                </div>
                <?php endif; ?>

                <!-- Tracking Timeline -->
                <div class="tracking-timeline">
                    <h4 style="margin-left: -30px; margin-bottom: 20px;">
                        <i class="fa fa-clock-o"></i> Tracking History
                    </h4>
                    
                    <?php
                    if ($tracking_result && mysqli_num_rows($tracking_result) > 0) {
                        $tracking_steps = [];
                        while ($step = mysqli_fetch_array($tracking_result)) {
                            $tracking_steps[] = $step;
                        }
                        
                        // If no tracking steps, create default one
                        if (empty($tracking_steps)) {
                            $tracking_steps[] = [
                                'status' => $order['current_status'],
                                'status_message' => 'Order placed successfully',
                                'updated_date' => $order['order_date'],
                                'updated_by' => 'System'
                            ];
                        }
                        
                        $current_status = $order['current_status'];
                        
                        foreach ($tracking_steps as $index => $step) {
                            $is_current = ($step['status'] == $current_status && $index == count($tracking_steps) - 1);
                            $is_active = !$is_current;
                            ?>
                            <div class="timeline-item <?php echo $is_current ? 'current' : ($is_active ? 'active' : ''); ?>">
                                <div class="timeline-content">
                                    <h5>
                                        <?php echo ucwords(str_replace('_', ' ', $step['status'])); ?>
                                        <span class="status-badge status-<?php echo $step['status']; ?>">
                                            <?php echo ucwords(str_replace('_', ' ', $step['status'])); ?>
                                        </span>
                                    </h5>
                                    <p><?php echo $step['status_message']; ?></p>
                                    <?php if (isset($step['location']) && $step['location']): ?>
                                        <p><i class="fa fa-map-marker"></i> <?php echo $step['location']; ?></p>
                                    <?php endif; ?>
                                    <div class="time">
                                        <i class="fa fa-clock-o"></i> 
                                        <?php echo date('M d, Y h:i A', strtotime($step['updated_date'])); ?>
                                        <?php if (isset($step['updated_by']) && $step['updated_by']): ?>
                                            | Updated by: <?php echo $step['updated_by']; ?>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            <?php
                        }
                    } else {
                        // Show default tracking step if no tracking history exists
                        ?>
                        <div class="timeline-item current">
                            <div class="timeline-content">
                                <h5>
                                    <?php echo ucwords(str_replace('_', ' ', $order['current_status'])); ?>
                                    <span class="status-badge status-<?php echo $order['current_status']; ?>">
                                        <?php echo ucwords(str_replace('_', ' ', $order['current_status'])); ?>
                                    </span>
                                </h5>
                                <p>Your order is currently being processed.</p>
                                <div class="time">
                                    <i class="fa fa-clock-o"></i> 
                                    <?php echo date('M d, Y h:i A', strtotime($order['order_date'])); ?>
                                </div>
                            </div>
                        </div>
                        <?php
                    }
                    ?>
                </div>
                
                <?php
            } else {
                ?>
                <div class="alert alert-danger">
                    <i class="fa fa-exclamation-triangle"></i>
                    <strong>Order Not Found!</strong> 
                    Please check your tracking number or invoice number and try again.
                </div>
                <?php
            }
        } else {
            ?>
            <div class="no-tracking">
                <i class="fa fa-search" style="font-size: 48px; color: #ddd; margin-bottom: 20px;"></i>
                <h4>Enter your tracking details above to see your order status</h4>
                <p>You can find your tracking number in the order confirmation email or SMS.</p>
            </div>
            <?php
        }
        ?>
    </div>

    <?php include("includes/footer.php"); ?>

    <script src="js/jquery.min.js"></script>
    <script src="js/bootstrap.min.js"></script>
</body>
</html>
