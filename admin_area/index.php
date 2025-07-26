<?php
session_start();
include("includes/db.php");

if(!isset($_SESSION['admin_email'])){
    echo "<script>window.open('login.php','_self')</script>";
} else {

$admin_session = $_SESSION['admin_email'];
$get_admin = "select * from admins where admin_email='$admin_session'";
$run_admin = mysqli_query($con,$get_admin);
$row_admin = mysqli_fetch_array($run_admin);

$admin_id = $row_admin['admin_id'];
$admin_name = $row_admin['admin_name'];
$admin_email = $row_admin['admin_email'];
$admin_image = $row_admin['admin_image'];
$admin_country = $row_admin['admin_country'];
$admin_job = $row_admin['admin_job'];
$admin_contact = $row_admin['admin_contact'];
$admin_about = $row_admin['admin_about'];

// Dashboard Statistics with error checking
$get_products = "SELECT * FROM products";
$run_products = mysqli_query($con,$get_products);
$count_products = $run_products ? mysqli_num_rows($run_products) : 0;

$get_customers = "SELECT * FROM customers";
$run_customers = mysqli_query($con,$get_customers);
$count_customers = $run_customers ? mysqli_num_rows($run_customers) : 0;

$get_p_categories = "SELECT * FROM product_categories";
$run_p_categories = mysqli_query($con,$get_p_categories);
$count_p_categories = $run_p_categories ? mysqli_num_rows($run_p_categories) : 0;

$get_total_orders = "SELECT * FROM pending_orders";
$run_total_orders = mysqli_query($con,$get_total_orders);
$count_total_orders = $run_total_orders ? mysqli_num_rows($run_total_orders) : 0;

$get_pending_orders = "SELECT * FROM pending_orders WHERE order_status='pending'";
$run_pending_orders = mysqli_query($con,$get_pending_orders);
$count_pending_orders = $run_pending_orders ? mysqli_num_rows($run_pending_orders) : 0;

$get_completed_orders = "SELECT * FROM pending_orders WHERE order_status!='pending'";
$run_completed_orders = mysqli_query($con,$get_completed_orders);
$count_completed_orders = $run_completed_orders ? mysqli_num_rows($run_completed_orders) : 0;

// Calculate earnings from payments table and completed orders
$get_payment_earnings = "SELECT SUM(amount) as PaymentTotal FROM payments";
$run_payment_earnings = mysqli_query($con, $get_payment_earnings);
if($run_payment_earnings) {
    $row_payment = mysqli_fetch_assoc($run_payment_earnings);
    $payment_earnings = $row_payment['PaymentTotal'] ? $row_payment['PaymentTotal'] : 0;
} else {
    $payment_earnings = 0;
}

// Calculate earnings from cash on delivery orders (completed orders from customer_orders table)
$get_cod_earnings = "SELECT SUM(due_amount) as CODTotal FROM customer_orders WHERE order_status = 'delivered'";
$run_cod_earnings = mysqli_query($con, $get_cod_earnings);
if($run_cod_earnings) {
    $row_cod = mysqli_fetch_assoc($run_cod_earnings);
    $cod_earnings = $row_cod['CODTotal'] ? $row_cod['CODTotal'] : 0;
} else {
    $cod_earnings = 0;
}

// Total earnings = Online payments + COD delivered orders
$count_total_earnings = $payment_earnings + $cod_earnings;

?>

<!DOCTYPE html>
<html>
<head>
<title>Admin Panel</title>
<link href="css/bootstrap.min.css" rel="stylesheet">
<link href="css/style.css" rel="stylesheet">
<link href="font-awesome/css/font-awesome.min.css" rel="stylesheet">
<link rel="shortcut icon" href="//cdn.shopify.com/s/files/1/2484/9148/files/SDQSDSQ_32x32.png?v=1511436147" type="image/png">
</head>

<body>
<div id="wrapper">

<?php include("includes/sidebar.php"); ?>

<div id="page-wrapper">
<div class="container-fluid">

<?php
// Dashboard
if(isset($_GET['dashboard'])){
    include("dashboard.php");
}

// Products - Use fixed versions
if(isset($_GET['insert_product'])){
    include("insert_product.php");
}
if(isset($_GET['view_products'])){
    include("view_products.php");
}
if(isset($_GET['delete_product'])){
    include("delete_product.php");
}
if(isset($_GET['edit_product'])){
    include("edit_product.php");
}

// Colors Management - NEW
if(isset($_GET['insert_colors'])){
    include("insert_colors.php");
}
if(isset($_GET['view_colors'])){
    include("view_colors.php");
}
if(isset($_GET['edit_color'])){
    include("edit_colors.php");
}
if(isset($_GET['delete_color'])){
    include("delete_color.php");
}

// Sizes Management - NEW
if(isset($_GET['insert_sizes'])){
    include("insert_sizes.php");
}
if(isset($_GET['view_sizes'])){
    include("view_sizes.php");
}
if(isset($_GET['edit_size'])){
    include("edit_sizes.php");
}
if(isset($_GET['delete_size'])){
    include("delete_size.php");
}

// Stock Management - NEW
if(isset($_GET['view_stock'])){
    include("view_stock.php");
}

// Product Categories
if(isset($_GET['insert_p_cat'])){
    include("insert_p_cat.php");
}
if(isset($_GET['view_p_cats'])){
    include("view_p_cats.php");
}
if(isset($_GET['delete_p_cat'])){
    include("delete_p_cat.php");
}
if(isset($_GET['edit_p_cat'])){
    include("edit_p_cat.php");
}

// Main Categories
if(isset($_GET['insert_cat'])){
    include("insert_cat.php");
}
if(isset($_GET['view_cats'])){
    include("view_cats.php");
}
if(isset($_GET['delete_cat'])){
    include("delete_cat.php");
}
if(isset($_GET['edit_cat'])){
    include("edit_cat.php");
}

// Slides
if(isset($_GET['insert_slide'])){
    include("insert_slide.php");
}
if(isset($_GET['view_slides'])){
    include("view_slides.php");
}
if(isset($_GET['delete_slide'])){
    include("delete_slide.php");
}
if(isset($_GET['edit_slide'])){
    include("edit_slide.php");
}

// Customers
if(isset($_GET['view_customers'])){
    include("view_customers.php");
}
if(isset($_GET['customer_delete'])){
    include("customer_delete.php");
}

// Orders
if(isset($_GET['view_orders'])){
    include("view_orders.php");
}
if(isset($_GET['order_tracking'])){
    include("order_tracking.php");
}
if(isset($_GET['order_delete'])){
    include("order_delete.php");
}

// Payments
if(isset($_GET['view_payments'])){
    include("view_payments.php");
}
if(isset($_GET['view_earnings'])){
    include("view_earnings.php");
}
if(isset($_GET['payment_delete'])){
    include("payment_delete.php");
}

// Users
if(isset($_GET['insert_user'])){
    include("insert_user.php");
}
if(isset($_GET['view_users'])){
    include("view_users.php");
}
if(isset($_GET['user_delete'])){
    include("user_delete.php");
}
if(isset($_GET['user_profile'])){
    include("user_profile.php");
}

// Boxes
if(isset($_GET['insert_box'])){
    include("insert_box.php");
}
if(isset($_GET['view_boxes'])){
    include("view_boxes.php");
}
if(isset($_GET['delete_box'])){
    include("delete_box.php");
}
if(isset($_GET['edit_box'])){
    include("edit_box.php");
}

// Terms
if(isset($_GET['insert_term'])){
    include("insert_term.php");
}
if(isset($_GET['view_terms'])){
    include("view_terms.php");
}
if(isset($_GET['delete_term'])){
    include("delete_term.php");
}
if(isset($_GET['edit_term'])){
    include("edit_term.php");
}

// CSS
if(isset($_GET['edit_css'])){
    include("edit_css.php");
}

// Manufacturers
if(isset($_GET['insert_manufacturer'])){
    include("insert_manufacturer.php");
}
if(isset($_GET['view_manufacturers'])){
    include("view_manufacturers.php");
}
if(isset($_GET['delete_manufacturer'])){
    include("delete_manufacturer.php");
}
if(isset($_GET['edit_manufacturer'])){
    include("edit_manufacturer.php");
}

// Icons
if(isset($_GET['insert_icon'])){
    include("insert_icon.php");
}
if(isset($_GET['view_icons'])){
    include("view_icons.php");
}
if(isset($_GET['delete_icon'])){
    include("delete_icon.php");
}
if(isset($_GET['edit_icon'])){
    include("edit_icon.php");
}

// Contact Us
if(isset($_GET['edit_contact_us'])){
    include("edit_contact_us.php");
}

// Enquiry
if(isset($_GET['insert_enquiry'])){
    include("insert_enquiry.php");
}
if(isset($_GET['view_enquiry'])){
    include("view_enquiry.php");
}
if(isset($_GET['delete_enquiry'])){
    include("delete_enquiry.php");
}
if(isset($_GET['edit_enquiry'])){
    include("edit_enquiry.php");
}

// About Us
if(isset($_GET['edit_about_us'])){
    include("edit_about_us.php");
}

// Store
if(isset($_GET['insert_store'])){
    include("insert_store.php");
}
if(isset($_GET['view_store'])){
    include("view_store.php");
}
if(isset($_GET['delete_store'])){
    include("delete_store.php");
}
if(isset($_GET['edit_store'])){
    include("edit_store.php");
}

?>

</div>
</div>
</div>

<script src="js/jquery.min.js"></script>
<script src="js/bootstrap.min.js"></script>
</body>
</html>

<?php } ?>
