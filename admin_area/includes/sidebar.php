<?php
if(!isset($_SESSION['admin_email'])){
    echo "<script>window.open('login.php','_self')</script>";
} else {
?>

<nav class="navbar navbar-inverse navbar-fixed-top">
<div class="navbar-header">
<button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-ex1-collapse">
<span class="sr-only">Toggle Navigation</span>
<span class="icon-bar"></span>
<span class="icon-bar"></span>
<span class="icon-bar"></span>
</button>
<a class="navbar-brand" href="index.php?dashboard">Admin Panel</a>
</div>

<ul class="nav navbar-right top-nav">
<li class="dropdown">
<a href="#" class="dropdown-toggle" data-toggle="dropdown">
<i class="fa fa-user"></i>
<?php echo $admin_name; ?> <b class="caret"></b>
</a>
<ul class="dropdown-menu">
<li>
<a href="index.php?user_profile=<?php echo $admin_id; ?>">
<i class="fa fa-fw fa-user"></i> Profile
</a>
</li>
<li>
<a href="index.php?view_products">
<i class="fa fa-fw fa-envelope"></i> Products 
<span class="badge"><?php echo $count_products; ?></span>
</a>
</li>
<li>
<a href="index.php?view_customers">
<i class="fa fa-fw fa-gear"></i> Customers
<span class="badge"><?php echo $count_customers; ?></span>
</a>
</li>
<li>
<a href="index.php?view_p_cats">
<i class="fa fa-fw fa-gear"></i> Product Categories
<span class="badge"><?php echo $count_p_categories; ?></span>
</a>
</li>
<li class="divider"></li>
<li>
<a href="logout.php">
<i class="fa fa-fw fa-power-off"></i> Log Out
</a>
</li>
</ul>
</li>
</ul>

<div class="collapse navbar-collapse navbar-ex1-collapse">
<ul class="nav navbar-nav side-nav">

<li>
<a href="index.php?dashboard">
<i class="fa fa-fw fa-dashboard"></i> Dashboard
</a>
</li>

<!-- Products Section -->
<li>
<a href="#" data-toggle="collapse" data-target="#products">
<i class="fa fa-fw fa-table"></i> Products
<i class="fa fa-fw fa-caret-down"></i>
</a>
<ul id="products" class="collapse">
<li><a href="index.php?insert_product">Insert Products</a></li>
<li><a href="index.php?view_products">View Products</a></li>
</ul>
</li>

<!-- Colors Management -->
<li>
<a href="#" data-toggle="collapse" data-target="#colors">
<i class="fa fa-fw fa-paint-brush"></i> Colors Management
<i class="fa fa-fw fa-caret-down"></i>
</a>
<ul id="colors" class="collapse">
<li><a href="index.php?insert_colors">Insert Colors</a></li>
<li><a href="index.php?view_colors">View Colors</a></li>
</ul>
</li>

<!-- Sizes Management -->
<li>
<a href="#" data-toggle="collapse" data-target="#sizes">
<i class="fa fa-fw fa-expand"></i> Sizes Management
<i class="fa fa-fw fa-caret-down"></i>
</a>
<ul id="sizes" class="collapse">
<li><a href="index.php?insert_sizes">Insert Sizes</a></li>
<li><a href="index.php?view_sizes">View Sizes</a></li>
</ul>
</li>

<!-- Stock Management -->
<li>
<a href="index.php?view_stock">
<i class="fa fa-fw fa-cubes"></i> Stock Management
</a>
</li>

<!-- Manufacturers -->
<li>
<a href="#" data-toggle="collapse" data-target="#manufacturers">
<i class="fa fa-fw fa-briefcase"></i> Manufacturers
<i class="fa fa-fw fa-caret-down"></i>
</a>
<ul id="manufacturers" class="collapse">
<li><a href="index.php?insert_manufacturer">Insert Manufacturer</a></li>
<li><a href="index.php?view_manufacturers">View Manufacturers</a></li>
</ul>
</li>

<!-- Product Categories -->
<li>
<a href="#" data-toggle="collapse" data-target="#p_cat">
<i class="fa fa-fw fa-pencil"></i> Products Categories
<i class="fa fa-fw fa-caret-down"></i>
</a>
<ul id="p_cat" class="collapse">
<li><a href="index.php?insert_p_cat">Insert Product Category</a></li>
<li><a href="index.php?view_p_cats">View Products Categories</a></li>
</ul>
</li>

<!-- Main Categories -->
<li>
<a href="#" data-toggle="collapse" data-target="#cat">
<i class="fa fa-fw fa-arrows-v"></i> Categories
<i class="fa fa-fw fa-caret-down"></i>
</a>
<ul id="cat" class="collapse">
<li><a href="index.php?insert_cat">Insert Category</a></li>
<li><a href="index.php?view_cats">View Categories</a></li>
</ul>
</li>

<!-- Contact Us Section -->
<li>
<a href="#" data-toggle="collapse" data-target="#contact_us">
<i class="fa fa-fw fa-pencil"></i> Contact Us Section
<i class="fa fa-fw fa-caret-down"></i>
</a>
<ul id="contact_us" class="collapse">
<li><a href="index.php?edit_contact_us">Edit Contact Us</a></li>
<li><a href="index.php?insert_enquiry">Insert Enquiry Type</a></li>
<li><a href="index.php?view_enquiry">View Enquiry Types</a></li>
</ul>
</li>

<!-- About Us -->
<li>
<a href="index.php?edit_about_us">
<i class="fa fa-fw fa-edit"></i> Edit About Us Page
</a>
</li>

<!-- Terms -->
<li>
<a href="#" data-toggle="collapse" data-target="#terms">
<i class="fa fa-fw fa-table"></i> Terms
<i class="fa fa-fw fa-caret-down"></i>
</a>
<ul id="terms" class="collapse">
<li><a href="index.php?insert_term">Insert Terms</a></li>
<li><a href="index.php?view_terms">View Terms</a></li>
</ul>
</li>

<!-- Customers -->
<li>
<a href="index.php?view_customers">
<i class="fa fa-fw fa-edit"></i> View Customers
</a>
</li>

<!-- Orders -->
<li>
<a href="index.php?view_orders">
<i class="fa fa-fw fa-list"></i> View Orders
</a>
</li>

<li>
<a href="index.php?order_tracking">
<i class="fa fa-fw fa-truck"></i> Order Tracking
</a>
</li>

<!-- Payments & Earnings -->
<li>
<a href="#" data-toggle="collapse" data-target="#payments">
<i class="fa fa-fw fa-money"></i> Payments & Earnings
<i class="fa fa-fw fa-caret-down"></i>
</a>
<ul id="payments" class="collapse">
<li><a href="index.php?view_payments"><i class="fa fa-list"></i> View Payments</a></li>
<li><a href="index.php?view_earnings"><i class="fa fa-bar-chart"></i> Earnings Report</a></li>
</ul>
</li>

<!-- Users -->
<li>
<a href="#" data-toggle="collapse" data-target="#users">
<i class="fa fa-fw fa-gear"></i> Users
<i class="fa fa-fw fa-caret-down"></i>
</a>
<ul id="users" class="collapse">
<li><a href="index.php?insert_user">Insert User</a></li>
<li><a href="index.php?view_users">View Users</a></li>
<li><a href="index.php?user_profile=<?php echo $admin_id; ?>">Edit Profile</a></li>
</ul>
</li>

<!-- Logout -->
<li>
<a href="logout.php">
<i class="fa fa-fw fa-power-off"></i> Log Out
</a>
</li>

</ul>
</div>
</nav>

<?php } ?>
