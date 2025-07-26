<?php
if(!isset($_SESSION['admin_email'])){
    echo "<script>window.open('login.php','_self')</script>";
} else {

// Get admin details for tracking updates
$admin_session = $_SESSION['admin_email'];
$get_admin = "SELECT admin_name FROM admins WHERE admin_email='$admin_session'";
$run_admin = mysqli_query($con, $get_admin);
$admin_name = 'Admin'; // Default fallback
if($run_admin && mysqli_num_rows($run_admin) > 0) {
    $row_admin = mysqli_fetch_array($run_admin);
    $admin_name = $row_admin['admin_name'] ?? 'Admin';
}

?>

<div class="row">
    <div class="col-lg-12">
        <ol class="breadcrumb">
            <li class="active">
                <i class="fa fa-dashboard"></i> Dashboard / Order Tracking Management
            </li>
        </ol>
    </div>
</div>

<div class="row">
    <div class="col-lg-12">
        <div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title">
                    <i class="fa fa-truck fa-fw"></i> Order Tracking Management
                </h3>
            </div>
            
            <div class="panel-body">
                <?php
                // Handle status update
                if(isset($_POST['update_tracking'])) {
                    $order_id = mysqli_real_escape_string($con, $_POST['order_id']);
                    $new_status = mysqli_real_escape_string($con, $_POST['new_status']);
                    $status_message = mysqli_real_escape_string($con, $_POST['status_message']);
                    $location = mysqli_real_escape_string($con, $_POST['location']);
                    $estimated_delivery = !empty($_POST['estimated_delivery']) ? "'{$_POST['estimated_delivery']}'" : 'NULL';
                    
                    // Get order details
                    $get_order = "SELECT * FROM customer_orders WHERE order_id = '$order_id'";
                    $run_order = mysqli_query($con, $get_order);
                    $order_data = mysqli_fetch_array($run_order);
                    
                    if($order_data) {
                        // Update customer_orders table
                        $update_order = "UPDATE customer_orders SET 
                                       current_status = '$new_status', 
                                       order_status = '$new_status',
                                       estimated_delivery = $estimated_delivery
                                       WHERE order_id = '$order_id'";
                        
                        // Update pending_orders table
                        $update_pending = "UPDATE pending_orders SET 
                                         order_status = '$new_status' 
                                         WHERE invoice_no = '{$order_data['invoice_no']}'";
                        
                        // Insert tracking record
                        $insert_tracking = "INSERT INTO order_tracking 
                                          (order_id, invoice_no, tracking_number, status, status_message, location, updated_by, estimated_delivery) 
                                          VALUES 
                                          ('$order_id', '{$order_data['invoice_no']}', '{$order_data['tracking_number']}', '$new_status', '$status_message', '$location', '$admin_name', $estimated_delivery)";
                        
                        if(mysqli_query($con, $update_order) && mysqli_query($con, $update_pending) && mysqli_query($con, $insert_tracking)) {
                            echo "<div class='alert alert-success'>
                                    <i class='fa fa-check'></i> Order status updated successfully!
                                  </div>";
                        } else {
                            echo "<div class='alert alert-danger'>
                                    <i class='fa fa-exclamation-triangle'></i> Error updating order status: " . mysqli_error($con) . "
                                  </div>";
                        }
                    }
                }
                
                // Handle delivery confirmation
                if(isset($_POST['confirm_delivery'])) {
                    $order_id = mysqli_real_escape_string($con, $_POST['order_id']);
                    
                    $get_order = "SELECT * FROM customer_orders WHERE order_id = '$order_id'";
                    $run_order = mysqli_query($con, $get_order);
                    $order_data = mysqli_fetch_array($run_order);
                    
                    if($order_data) {
                        $update_delivery = "UPDATE customer_orders SET 
                                          current_status = 'delivered', 
                                          order_status = 'delivered',
                                          actual_delivery = NOW()
                                          WHERE order_id = '$order_id'";
                        
                        $update_pending = "UPDATE pending_orders SET 
                                         order_status = 'delivered' 
                                         WHERE invoice_no = '{$order_data['invoice_no']}'";
                        
                        $insert_tracking = "INSERT INTO order_tracking 
                                          (order_id, invoice_no, tracking_number, status, status_message, updated_by, actual_delivery) 
                                          VALUES 
                                          ('$order_id', '{$order_data['invoice_no']}', '{$order_data['tracking_number']}', 'delivered', 'Package delivered successfully', '$admin_name', NOW())";
                        
                        if(mysqli_query($con, $update_delivery) && mysqli_query($con, $update_pending) && mysqli_query($con, $insert_tracking)) {
                            echo "<div class='alert alert-success'>
                                    <i class='fa fa-check'></i> Order marked as delivered successfully!
                                  </div>";
                        }
                    }
                }
                ?>
                
                <!-- Search and Filter -->
                <div class="row" style="margin-bottom: 20px;">
                    <div class="col-md-6">
                        <form method="GET" action="" class="form-inline">
                            <input type="hidden" name="order_tracking" value="">
                            <div class="form-group">
                                <input type="text" name="search" class="form-control" 
                                       placeholder="Search by Invoice, Tracking, or Customer" 
                                       value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                            </div>
                            <button type="submit" class="btn btn-primary">
                                <i class="fa fa-search"></i> Search
                            </button>
                        </form>
                    </div>
                    <div class="col-md-6">
                        <form method="GET" action="" class="form-inline">
                            <input type="hidden" name="order_tracking" value="">
                            <div class="form-group">
                                <select name="status_filter" class="form-control">
                                    <option value="">All Statuses</option>
                                    <option value="pending" <?php echo (isset($_GET['status_filter']) && $_GET['status_filter'] == 'pending') ? 'selected' : ''; ?>>Pending</option>
                                    <option value="confirmed" <?php echo (isset($_GET['status_filter']) && $_GET['status_filter'] == 'confirmed') ? 'selected' : ''; ?>>Confirmed</option>
                                    <option value="processing" <?php echo (isset($_GET['status_filter']) && $_GET['status_filter'] == 'processing') ? 'selected' : ''; ?>>Processing</option>
                                    <option value="packed" <?php echo (isset($_GET['status_filter']) && $_GET['status_filter'] == 'packed') ? 'selected' : ''; ?>>Packed</option>
                                    <option value="shipped" <?php echo (isset($_GET['status_filter']) && $_GET['status_filter'] == 'shipped') ? 'selected' : ''; ?>>Shipped</option>
                                    <option value="out_for_delivery" <?php echo (isset($_GET['status_filter']) && $_GET['status_filter'] == 'out_for_delivery') ? 'selected' : ''; ?>>Out for Delivery</option>
                                    <option value="delivered" <?php echo (isset($_GET['status_filter']) && $_GET['status_filter'] == 'delivered') ? 'selected' : ''; ?>>Delivered</option>
                                </select>
                            </div>
                            <button type="submit" class="btn btn-info">
                                <i class="fa fa-filter"></i> Filter
                            </button>
                        </form>
                    </div>
                </div>
                
                <!-- Orders Table -->
                <div class="table-responsive">
                    <table class="table table-bordered table-hover table-striped">
                        <thead>
                            <tr>
                                <th>Order ID</th>
                                <th>Customer</th>
                                <th>Invoice</th>
                                <th>Tracking Number</th>
                                <th>Amount</th>
                                <th>Status</th>
                                <th>Order Date</th>
                                <th>Est. Delivery</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            // Build query with search and filter
                            $where_conditions = [];
                            
                            if(isset($_GET['search']) && !empty($_GET['search'])) {
                                $search = mysqli_real_escape_string($con, $_GET['search']);
                                $where_conditions[] = "(co.invoice_no LIKE '%$search%' OR co.tracking_number LIKE '%$search%' OR c.customer_name LIKE '%$search%')";
                            }
                            
                            if(isset($_GET['status_filter']) && !empty($_GET['status_filter'])) {
                                $status_filter = mysqli_real_escape_string($con, $_GET['status_filter']);
                                $where_conditions[] = "co.current_status = '$status_filter'";
                            }
                            
                            $where_clause = !empty($where_conditions) ? "WHERE " . implode(" AND ", $where_conditions) : "";
                            
                            $get_orders = "SELECT co.*, c.customer_name, c.customer_email 
                                         FROM customer_orders co 
                                         LEFT JOIN customers c ON co.customer_id = c.customer_id 
                                         $where_clause
                                         ORDER BY co.order_date DESC 
                                         LIMIT 50";
                            
                            $run_orders = mysqli_query($con, $get_orders);
                            
                            if($run_orders && mysqli_num_rows($run_orders) > 0) {
                                while($row_order = mysqli_fetch_array($run_orders)) {
                                    $status_class = '';
                                    switch($row_order['current_status']) {
                                        case 'pending': $status_class = 'label-warning'; break;
                                        case 'confirmed': $status_class = 'label-info'; break;
                                        case 'processing': $status_class = 'label-primary'; break;
                                        case 'packed': $status_class = 'label-default'; break;
                                        case 'shipped': $status_class = 'label-success'; break;
                                        case 'out_for_delivery': $status_class = 'label-info'; break;
                                        case 'delivered': $status_class = 'label-success'; break;
                                        case 'cancelled': $status_class = 'label-danger'; break;
                                        default: $status_class = 'label-default';
                                    }
                                    ?>
                                    <tr>
                                        <td>#<?php echo $row_order['order_id']; ?></td>
                                        <td>
                                            <?php echo $row_order['customer_name'] ? $row_order['customer_name'] : 'Guest'; ?>
                                            <?php if($row_order['customer_email']): ?>
                                                <br><small class="text-muted"><?php echo $row_order['customer_email']; ?></small>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo $row_order['invoice_no']; ?></td>
                                        <td>
                                            <code><?php echo $row_order['tracking_number']; ?></code>
                                            <br>
                                            <a href="../track_order.php?tracking=<?php echo $row_order['tracking_number']; ?>" 
                                               target="_blank" class="btn btn-xs btn-info">
                                                <i class="fa fa-external-link"></i> View
                                            </a>
                                        </td>
                                        <td>Rs <?php echo number_format($row_order['due_amount'], 2); ?></td>
                                        <td>
                                            <span class="label <?php echo $status_class; ?>">
                                                <?php echo ucwords(str_replace('_', ' ', $row_order['current_status'])); ?>
                                            </span>
                                        </td>
                                        <td><?php echo date('M d, Y', strtotime($row_order['order_date'])); ?></td>
                                        <td>
                                            <?php if($row_order['estimated_delivery']): ?>
                                                <?php echo date('M d, Y', strtotime($row_order['estimated_delivery'])); ?>
                                            <?php else: ?>
                                                <span class="text-muted">Not set</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <button class="btn btn-primary btn-sm" 
                                                    onclick="updateTracking(<?php echo $row_order['order_id']; ?>, '<?php echo $row_order['invoice_no']; ?>', '<?php echo $row_order['current_status']; ?>')">
                                                <i class="fa fa-edit"></i> Update
                                            </button>
                                            
                                            <?php if($row_order['current_status'] != 'delivered'): ?>
                                            <button class="btn btn-success btn-sm" 
                                                    onclick="confirmDelivery(<?php echo $row_order['order_id']; ?>)">
                                                <i class="fa fa-check"></i> Delivered
                                            </button>
                                            <?php endif; ?>
                                            
                                            <button class="btn btn-info btn-sm" 
                                                    onclick="viewTrackingHistory(<?php echo $row_order['order_id']; ?>)">
                                                <i class="fa fa-history"></i> History
                                            </button>
                                        </td>
                                    </tr>
                                    <?php
                                }
                            } else {
                                echo "<tr><td colspan='9' class='text-center'>No orders found</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Update Tracking Modal -->
<div class="modal fade" id="updateTrackingModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form method="POST" action="">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                    <h4 class="modal-title">Update Order Tracking</h4>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="order_id" id="modal_order_id">
                    
                    <div class="form-group">
                        <label>Order Status</label>
                        <select name="new_status" id="modal_status" class="form-control" required>
                            <option value="pending">Pending</option>
                            <option value="confirmed">Confirmed</option>
                            <option value="processing">Processing</option>
                            <option value="packed">Packed</option>
                            <option value="shipped">Shipped</option>
                            <option value="out_for_delivery">Out for Delivery</option>
                            <option value="delivered">Delivered</option>
                            <option value="cancelled">Cancelled</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label>Status Message</label>
                        <textarea name="status_message" class="form-control" rows="3" 
                                  placeholder="Enter status update message" required></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label>Location (Optional)</label>
                        <input type="text" name="location" class="form-control" 
                               placeholder="Current location of package">
                    </div>
                    
                    <div class="form-group">
                        <label>Estimated Delivery Date (Optional)</label>
                        <input type="datetime-local" name="estimated_delivery" class="form-control">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                    <button type="submit" name="update_tracking" class="btn btn-primary">
                        <i class="fa fa-save"></i> Update Tracking
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Confirm Delivery Modal -->
<div class="modal fade" id="confirmDeliveryModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-sm" role="document">
        <div class="modal-content">
            <form method="POST" action="">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                    <h4 class="modal-title">Confirm Delivery</h4>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="order_id" id="delivery_order_id">
                    <p>Are you sure you want to mark this order as delivered?</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                    <button type="submit" name="confirm_delivery" class="btn btn-success">
                        <i class="fa fa-check"></i> Confirm Delivery
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Tracking History Modal -->
<div class="modal fade" id="trackingHistoryModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
                <h4 class="modal-title">Tracking History</h4>
            </div>
            <div class="modal-body" id="tracking-history-content">
                <!-- Content will be loaded here -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
function updateTracking(orderId, invoiceNo, currentStatus) {
    $('#modal_order_id').val(orderId);
    $('#modal_status').val(currentStatus);
    $('#updateTrackingModal').modal('show');
}

function confirmDelivery(orderId) {
    $('#delivery_order_id').val(orderId);
    $('#confirmDeliveryModal').modal('show');
}

function viewTrackingHistory(orderId) {
    $('#tracking-history-content').html('<div class="text-center"><i class="fa fa-spinner fa-spin"></i> Loading...</div>');
    $('#trackingHistoryModal').modal('show');
    
    // Load tracking history via AJAX
    $.get('get_tracking_history.php', {order_id: orderId}, function(data) {
        $('#tracking-history-content').html(data);
    }).fail(function() {
        $('#tracking-history-content').html('<div class="alert alert-danger">Error loading tracking history.</div>');
    });
}

// Auto-fill status messages based on status selection
$('#modal_status').change(function() {
    var status = $(this).val();
    var messages = {
        'pending': 'Order is pending confirmation',
        'confirmed': 'Order has been confirmed and is being prepared',
        'processing': 'Order is being processed and packed',
        'packed': 'Order has been packed and ready for shipment',
        'shipped': 'Order has been shipped and is on the way',
        'out_for_delivery': 'Order is out for delivery',
        'delivered': 'Order has been successfully delivered',
        'cancelled': 'Order has been cancelled'
    };
    
    if(messages[status]) {
        $('textarea[name="status_message"]').val(messages[status]);
    }
});
</script>

<?php } ?>
