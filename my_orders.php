<?php
session_start();
include("includes/db.php");
include("includes/header.php");
include("functions/functions.php");
include("includes/main.php");

// Check if customer is logged in
if(!isset($_SESSION['customer_email'])){
    echo "<script>window.open('customer_login.php','_self')</script>";
    exit;
}

$customer_email = $_SESSION['customer_email'];
$get_customer = "SELECT * FROM customers WHERE customer_email='$customer_email'";
$run_customer = mysqli_query($con, $get_customer);
$customer = mysqli_fetch_array($run_customer);
$customer_id = $customer['customer_id'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Orders - Order History</title>
    <link rel="stylesheet" href="styles/bootstrap-337.min.css">
    <link rel="stylesheet" href="font-awesome/css/font-awesome.min.css">
    <style>
        .order-card {
            border: 1px solid #ddd;
            border-radius: 8px;
            margin-bottom: 20px;
            overflow: hidden;
        }
        
        .order-header {
            background: #f8f9fa;
            padding: 15px 20px;
            border-bottom: 1px solid #ddd;
        }
        
        .order-body {
            padding: 20px;
        }
        
        .order-footer {
            background: #f8f9fa;
            padding: 15px 20px;
            border-top: 1px solid #ddd;
        }
        
        .status-badge {
            display: inline-block;
            padding: 6px 12px;
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
        
        .order-item {
            display: flex;
            align-items: center;
            padding: 10px 0;
            border-bottom: 1px solid #eee;
        }
        
        .order-item:last-child {
            border-bottom: none;
        }
        
        .order-item img {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 5px;
            margin-right: 15px;
        }
        
        .tracking-info {
            background: #e3f2fd;
            padding: 15px;
            border-radius: 5px;
            margin-top: 15px;
        }
    </style>
</head>

<body>
    <main>
        <div class="nero">
            <div class="nero__heading">
                <span class="nero__bold">MY</span> Orders
            </div>
            <p class="nero__text">Track and manage your order history</p>
        </div>
    </main>

    <div class="container" style="margin-top: 30px; margin-bottom: 30px;">
        <div class="row">
            <div class="col-md-3">
                <!-- Customer Account Sidebar -->
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h4>My Account</h4>
                    </div>
                    <div class="panel-body">
                        <ul class="nav nav-pills nav-stacked">
                            <li><a href="my_account.php"><i class="fa fa-user"></i> My Profile</a></li>
                            <li class="active"><a href="my_orders.php"><i class="fa fa-list"></i> My Orders</a></li>
                            <li><a href="wishlist.php"><i class="fa fa-heart"></i> My Wishlist</a></li>
                            <li><a href="logout.php"><i class="fa fa-sign-out"></i> Logout</a></li>
                        </ul>
                    </div>
                </div>
            </div>
            
            <div class="col-md-9">
                <h2><i class="fa fa-shopping-bag"></i> My Orders</h2>
                <p class="text-muted">View your order history and track current orders</p>
                
                <?php
                // Get customer orders
                $get_orders = "SELECT co.*, 
                              (SELECT COUNT(*) FROM pending_orders po WHERE po.invoice_no = co.invoice_no) as item_count
                              FROM customer_orders co 
                              WHERE co.customer_id = '$customer_id' 
                              ORDER BY co.order_date DESC";
                              
                $run_orders = mysqli_query($con, $get_orders);
                
                if(mysqli_num_rows($run_orders) > 0) {
                    while($order = mysqli_fetch_array($run_orders)) {
                        ?>
                        <div class="order-card">
                            <div class="order-header">
                                <div class="row">
                                    <div class="col-md-3">
                                        <strong>Order #<?php echo $order['order_id']; ?></strong><br>
                                        <small class="text-muted">Invoice: <?php echo $order['invoice_no']; ?></small>
                                    </div>
                                    <div class="col-md-3">
                                        <strong>Order Date</strong><br>
                                        <small><?php echo date('M d, Y', strtotime($order['order_date'])); ?></small>
                                    </div>
                                    <div class="col-md-3">
                                        <strong>Total Amount</strong><br>
                                        <small>Rs <?php echo number_format($order['due_amount'], 2); ?></small>
                                    </div>
                                    <div class="col-md-3">
                                        <strong>Status</strong><br>
                                        <span class="status-badge status-<?php echo str_replace(' ', '_', $order['current_status']); ?>">
                                            <?php echo ucwords(str_replace('_', ' ', $order['current_status'])); ?>
                                        </span>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="order-body">
                                <!-- Order Items -->
                                <h5><i class="fa fa-shopping-bag"></i> Items (<?php echo $order['item_count']; ?>)</h5>
                                
                                <?php
                                // Get order items
                                $get_items = "SELECT po.*, p.product_title, p.product_img1 
                                            FROM pending_orders po 
                                            LEFT JOIN products p ON po.product_id = p.product_id 
                                            WHERE po.invoice_no = '{$order['invoice_no']}' 
                                            LIMIT 3";
                                $run_items = mysqli_query($con, $get_items);
                                
                                while($item = mysqli_fetch_array($run_items)) {
                                    ?>
                                    <div class="order-item">
                                        <img src="admin_area/product_images/<?php echo $item['product_img1']; ?>" alt="Product">
                                        <div class="flex-grow-1">
                                            <h6 style="margin: 0;"><?php echo $item['product_title']; ?></h6>
                                            <small class="text-muted">
                                                Qty: <?php echo $item['qty']; ?>
                                                <?php if($item['size']): ?>
                                                    | Size: <?php echo $item['size']; ?>
                                                <?php endif; ?>
                                            </small>
                                        </div>
                                    </div>
                                    <?php
                                }
                                
                                if($order['item_count'] > 3) {
                                    echo "<p class='text-muted'><small>... and " . ($order['item_count'] - 3) . " more items</small></p>";
                                }
                                ?>
                                
                                <!-- Tracking Information -->
                                <?php if($order['tracking_number']): ?>
                                <div class="tracking-info">
                                    <h6><i class="fa fa-truck"></i> Tracking Information</h6>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <strong>Tracking Number:</strong><br>
                                            <code><?php echo $order['tracking_number']; ?></code>
                                        </div>
                                        <div class="col-md-6">
                                            <?php if($order['estimated_delivery']): ?>
                                                <strong>Estimated Delivery:</strong><br>
                                                <span><?php echo date('M d, Y', strtotime($order['estimated_delivery'])); ?></span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="order-footer">
                                <div class="row">
                                    <div class="col-md-6">
                                        <?php if($order['tracking_number']): ?>
                                        <a href="track_order.php?tracking=<?php echo $order['tracking_number']; ?>" 
                                           class="btn btn-primary">
                                            <i class="fa fa-truck"></i> Track Order
                                        </a>
                                        <?php endif; ?>
                                        
                                        <?php if($order['current_status'] == 'delivered'): ?>
                                        <a href="reorder.php?invoice=<?php echo $order['invoice_no']; ?>" 
                                           class="btn btn-success">
                                            <i class="fa fa-refresh"></i> Reorder
                                        </a>
                                        <?php endif; ?>
                                    </div>
                                    <div class="col-md-6 text-right">
                                        <a href="order_details.php?invoice=<?php echo $order['invoice_no']; ?>" 
                                           class="btn btn-default">
                                            <i class="fa fa-eye"></i> View Details
                                        </a>
                                        
                                        <?php if(in_array($order['current_status'], ['pending', 'confirmed'])): ?>
                                        <a href="cancel_order.php?order_id=<?php echo $order['order_id']; ?>" 
                                           class="btn btn-danger"
                                           onclick="return confirm('Are you sure you want to cancel this order?')">
                                            <i class="fa fa-times"></i> Cancel
                                        </a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php
                    }
                } else {
                    ?>
                    <div class="alert alert-info text-center">
                        <h4><i class="fa fa-shopping-bag"></i> No Orders Found</h4>
                        <p>You haven't placed any orders yet.</p>
                        <a href="shop.php" class="btn btn-primary">
                            <i class="fa fa-shopping-cart"></i> Start Shopping
                        </a>
                    </div>
                    <?php
                }
                ?>
            </div>
        </div>
    </div>

    <?php include("includes/footer.php"); ?>

    <script src="js/jquery.min.js"></script>
    <script src="js/bootstrap.min.js"></script>
</body>
</html>
