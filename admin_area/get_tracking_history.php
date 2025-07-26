<?php
session_start();
include("includes/db.php");

if(!isset($_SESSION['admin_email'])){
    echo "<div class='alert alert-danger'>Access denied</div>";
    exit;
}

if(isset($_GET['order_id'])) {
    $order_id = mysqli_real_escape_string($con, $_GET['order_id']);
    
    // Get order details
    $get_order = "SELECT co.*, c.customer_name 
                  FROM customer_orders co 
                  LEFT JOIN customers c ON co.customer_id = c.customer_id 
                  WHERE co.order_id = '$order_id'";
    $run_order = mysqli_query($con, $get_order);
    $order = mysqli_fetch_array($run_order);
    
    if($order) {
        echo "<div class='row'>
                <div class='col-md-6'>
                    <strong>Order ID:</strong> #{$order['order_id']}<br>
                    <strong>Invoice:</strong> {$order['invoice_no']}<br>
                    <strong>Customer:</strong> " . ($order['customer_name'] ?: 'Guest') . "
                </div>
                <div class='col-md-6'>
                    <strong>Tracking:</strong> {$order['tracking_number']}<br>
                    <strong>Amount:</strong> $" . number_format($order['due_amount'], 2) . "<br>
                    <strong>Order Date:</strong> " . date('M d, Y', strtotime($order['order_date'])) . "
                </div>
              </div><hr>";
        
        // Get tracking history
        $get_tracking = "SELECT * FROM order_tracking 
                        WHERE order_id = '$order_id' 
                        ORDER BY created_date DESC";
        $run_tracking = mysqli_query($con, $get_tracking);
        
        if($run_tracking && mysqli_num_rows($run_tracking) > 0) {
            echo "<div class='table-responsive'>
                    <table class='table table-striped'>
                        <thead>
                            <tr>
                                <th>Status</th>
                                <th>Message</th>
                                <th>Location</th>
                                <th>Updated By</th>
                                <th>Date/Time</th>
                            </tr>
                        </thead>
                        <tbody>";
            
            while($tracking = mysqli_fetch_array($run_tracking)) {
                $status_class = '';
                switch($tracking['status']) {
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
                
                echo "<tr>
                        <td>
                            <span class='label {$status_class}'>
                                " . ucwords(str_replace('_', ' ', $tracking['status'])) . "
                            </span>
                        </td>
                        <td>{$tracking['status_message']}</td>
                        <td>" . ($tracking['location'] ?: '-') . "</td>
                        <td>{$tracking['updated_by']}</td>
                        <td>" . date('M d, Y h:i A', strtotime($tracking['created_date'])) . "</td>
                      </tr>";
            }
            
            echo "    </tbody>
                    </table>
                  </div>";
        } else {
            echo "<div class='alert alert-info'>No tracking history available for this order.</div>";
        }
    } else {
        echo "<div class='alert alert-danger'>Order not found.</div>";
    }
} else {
    echo "<div class='alert alert-danger'>Invalid request.</div>";
}
?>
