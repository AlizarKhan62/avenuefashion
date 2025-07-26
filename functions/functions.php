<?php
$db = mysqli_connect("localhost","root","","ecom_store");

/// IP address code starts /////
if (!function_exists('getRealUserIp')) {
    function getRealUserIp(){
        switch(true){
          case (!empty($_SERVER['HTTP_X_REAL_IP'])) : return $_SERVER['HTTP_X_REAL_IP'];
          case (!empty($_SERVER['HTTP_CLIENT_IP'])) : return $_SERVER['HTTP_CLIENT_IP'];
          case (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) : return $_SERVER['HTTP_X_FORWARDED_FOR'];
          default : return $_SERVER['REMOTE_ADDR'];
        }
    }
}
/// IP address code Ends /////

// items function Starts ///
if (!function_exists('items')) {
    function items(){
        global $db;
        $ip_add = getRealUserIp();
        $get_items = "select * from cart where ip_add='$ip_add'";
        $run_items = mysqli_query($db,$get_items);
        $count_items = mysqli_num_rows($run_items);
        echo $count_items;
    }
}
// items function Ends ///

// total_price function Starts //
if (!function_exists('total_price')) {
    function total_price(){
        global $db;
        $ip_add = getRealUserIp();
        $total = 0;
        $select_cart = "select * from cart where ip_add='$ip_add'";
        $run_cart = mysqli_query($db,$select_cart);
        
        while($record=mysqli_fetch_array($run_cart)){
            $pro_id = $record['p_id'];
            $pro_qty = $record['qty'];
            $sub_total = $record['p_price']*$pro_qty;
            $total += $sub_total;
        }
        echo "Rs" . number_format($total, 2);
    }
}
// total_price function Ends //

// getPro function Starts //
if (!function_exists('getPro')) {
    function getPro(){
        global $db;
        $get_products = "select * from products order by 1 DESC LIMIT 0,8";
        $run_products = mysqli_query($db,$get_products);
    
    echo '<div class="row">';
    $product_count = 0;
    
    while($row_products=mysqli_fetch_array($run_products)){
        // Start a new row after every 4 products
        if($product_count > 0 && $product_count % 4 == 0){
            echo '</div><div class="row">';
        }
        $product_count++;
        
        $pro_id = $row_products['product_id'];
        $pro_title = $row_products['product_title'];
        $pro_price = $row_products['product_price'];
        $pro_img1 = $row_products['product_img1'];
        $pro_label = $row_products['product_label'];
        // $manufacturer_id = $row_products['manufacturer_id'];
        
        // $get_manufacturer = "select * from manufacturers where manufacturer_id='$manufacturer_id'";
        // $run_manufacturer = mysqli_query($db,$get_manufacturer);
        // $row_manufacturer = mysqli_fetch_array($run_manufacturer);
        // $manufacturer_name = $row_manufacturer['manufacturer_title'];
        $pro_psp_price = $row_products['product_psp_price'];
        $pro_url = $row_products['product_url'];

        if($pro_label == "Sale" or $pro_label == "Gift"){
            $product_price = "<del> Rs" . number_format($pro_price, 2) . "</del>";
            $product_psp_price = "| Rs" . number_format($pro_psp_price, 2);
        } else {
            $product_psp_price = "";
            $product_price = "Rs" . number_format($pro_price, 2);
        }

        if($pro_label == ""){
            $product_label = "";
        } else {
            $product_label = "
            <a class='label sale' href='#' style='color:black;'>
                <div class='thelabel'>$pro_label</div>
                <div class='label-background'> </div>
            </a>";
        }

        echo "
        <div class='col-md-3 col-sm-6 single'>
            <div class='product'>
                <a href='$pro_url'>
                    <img src='admin_area/product_images/$pro_img1' class='img-responsive'>
                </a>
                <div class='text'>
                    <h3><a href='$pro_url'>$pro_title</a></h3>
                    <p class='price'> $product_price $product_psp_price </p>
                    <p class='buttons'>
                        <a href='$pro_url' class='btn btn-default'>View Details</a>
                        <a href='$pro_url' class='btn btn-danger'>
                            <i class='fa fa-shopping-cart'></i> Add To Cart
                        </a>
                    </p>
                </div>
                $product_label
            </div>
        </div>";
    }
    // Close the last row div
    echo '</div>';
    
    // Add Go to Shop button
    echo '
    <div class="row">
        <div class="col-md-12 text-center" style="margin: 30px 0;">
            <a href="shop.php" class="btn btn-primary btn-lg">
                Go to Shop
            </a>
        </div>
    </div>';
    }
}
// getPro function Ends //

/// getProducts Function for shop page - Starts ///
if (!function_exists('getProducts')) {
    function getProducts(){
        global $db;
        
        // Enable error reporting for debugging
        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
    
    // Start products grid
    echo '<div id="Products">';
    echo '<div class="row">';
    
    $aWhere = array();
    
    // Enable error reporting
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
    
    // Start fresh output buffer
    ob_start();
    
    /// Manufacturers Code Starts ///
    if(isset($_REQUEST['man'])&&is_array($_REQUEST['man'])){
        foreach($_REQUEST['man'] as $sKey=>$sVal){
            if((int)$sVal!=0){
                $aWhere[] = 'manufacturer_id='.(int)$sVal;
            }
        }
    }
    /// Manufacturers Code Ends ///
    
    /// Products Categories Code Starts ///
    if(isset($_REQUEST['p_cat'])&&is_array($_REQUEST['p_cat'])){
        foreach($_REQUEST['p_cat'] as $sKey=>$sVal){
            if((int)$sVal!=0){
                $aWhere[] = 'p_cat_id='.(int)$sVal;
            }
        }
    }
    /// Products Categories Code Ends ///
    
    /// Categories Code Starts ///
    if(isset($_REQUEST['cat'])&&is_array($_REQUEST['cat'])){
        foreach($_REQUEST['cat'] as $sKey=>$sVal){
            if((int)$sVal!=0){
                $aWhere[] = 'cat_id='.(int)$sVal;
            }
        }
    }
    /// Categories Code Ends ///
    
    $per_page = 6;
    if(isset($_GET['page'])){
        $page = (int)$_GET['page'];
    } else {
        $page = 1;
    }
    
    // First get total number of products for pagination
    $count_query = "SELECT COUNT(*) as total FROM products";
    
    // Debug the WHERE clause construction
    error_log("Filters applied: " . print_r($aWhere, true));
    
    // Add sorting condition to ensure consistent order
    $sort_order = " ORDER BY product_title ASC";
    if(count($aWhere) > 0) {
        $count_query .= ' WHERE ' . implode(' AND ', $aWhere);
    }
    $count_result = mysqli_query($db, $count_query);
    if(!$count_result) {
        die("Query failed: " . mysqli_error($db));
    }
    $total_records = mysqli_fetch_assoc($count_result)['total'];
    
    // Calculate total pages
    $total_pages = ceil($total_records / $per_page);
    
    // Validate current page
    if($page > $total_pages) {
        $page = $total_pages;
    }
    if($page < 1) {
        $page = 1;
    }
    
    // Recalculate start position with validation
    $start_from = max(0, ($page-1) * $per_page);
    
    // Build main query with proper ordering and pagination
    $get_products = "SELECT * FROM products";
    
    // Add WHERE clause if filters exist
    if(count($aWhere) > 0) {
        $get_products .= ' WHERE ' . implode(' AND ', $aWhere);
    }
    
    // Add consistent ordering and pagination
    $get_products .= " ORDER BY product_id DESC LIMIT $start_from, $per_page";
    
    // Log the final query for debugging
    error_log("Products query: " . $get_products);
    
    // Debug the final query
    error_log("Final query: " . $get_products);
    
    // Execute query with error checking
    $run_products = mysqli_query($db, $get_products);
    if(!$run_products) {
        die("Query failed: " . mysqli_error($db));
    }
    
    // Check if there are any products
    if(mysqli_num_rows($run_products) == 0) {
        echo "<div class='alert alert-info'>No products found matching your criteria.</div>";
        return;
    }
    
    $product_count = 0;
    while($row_products = mysqli_fetch_array($run_products)){
        // Start a new row after every 3 products
        if($product_count > 0 && $product_count % 3 == 0){
            echo '</div><div class="row">';
        }
        $product_count++;
        
        $pro_id = $row_products['product_id'];
        $pro_title = $row_products['product_title'];
        $pro_price = $row_products['product_price'];
        $pro_img1 = $row_products['product_img1'];
        $pro_label = $row_products['product_label'];
        // $manufacturer_id = $row_products['manufacturer_id'];
        
        // $get_manufacturer = "select * from manufacturers where manufacturer_id='$manufacturer_id'";
        // $run_manufacturer = mysqli_query($db,$get_manufacturer);
        // $row_manufacturer = mysqli_fetch_array($run_manufacturer);
        // $manufacturer_name = $row_manufacturer['manufacturer_title'];
        $pro_psp_price = $row_products['product_psp_price'];
        $pro_url = $row_products['product_url'];

        if($pro_label == "Sale" or $pro_label == "Gift"){
            $product_price = "<del> Rs" . number_format($pro_price, 2) . "</del>";
            $product_psp_price = "| Rs" . number_format($pro_psp_price, 2);
        } else {
            $product_psp_price = "";
            $product_price = "Rs" . number_format($pro_price, 2);
        }

        if($pro_label == ""){
            $product_label = "";
        } else {
            $product_label = "
            <a class='label sale' href='#' style='color:black;'>
                <div class='thelabel'>$pro_label</div>
                <div class='label-background'> </div>
            </a>";
        }

        echo "
        <div class='col-md-4 col-sm-6 center-responsive'>
            <div class='product'>
                <a href='$pro_url'>
                    <img src='admin_area/product_images/$pro_img1' style='width:100%; height:300px; object-fit:cover; border-radius:8px;' class='img-responsive'>
                </a>
                <div class='text'>
                    
                    <h3><a href='$pro_url'>$pro_title</a></h3>
                    <p class='price'> $product_price $product_psp_price </p>
                    <p class='buttons'>
                        <a href='$pro_url' class='btn btn-default'>View details</a>
                        <a href='$pro_url' class='btn btn-danger'>
                            <i class='fa fa-shopping-cart' data-price='$pro_price'></i> Add To Cart
                        </a>
                    </p>
                </div>
                $product_label
            </div>
        </div>";
    }
    }
}
/// getProducts Function Ends ///

/// getPaginator Function Starts ///
if (!function_exists('getPaginator')) {
    function getPaginator(){
        $per_page = 6;
    global $db;
    $aWhere = array();
    $aPath = '';
    
    /// Manufacturers Code Starts ///
    if(isset($_REQUEST['man'])&&is_array($_REQUEST['man'])){
        foreach($_REQUEST['man'] as $sKey=>$sVal){
            if((int)$sVal!=0){
                $aWhere[] = 'manufacturer_id='.(int)$sVal;
                $aPath .= 'man[]='.(int)$sVal.'&';
            }
        }
    }
    /// Manufacturers Code Ends ///
    
    /// Products Categories Code Starts ///
    if(isset($_REQUEST['p_cat'])&&is_array($_REQUEST['p_cat'])){
        foreach($_REQUEST['p_cat'] as $sKey=>$sVal){
            if((int)$sVal!=0){
                $aWhere[] = 'p_cat_id='.(int)$sVal;
                $aPath .= 'p_cat[]='.(int)$sVal.'&';
            }
        }
    }
    /// Products Categories Code Ends ///
    
    /// Categories Code Starts ///
    if(isset($_REQUEST['cat'])&&is_array($_REQUEST['cat'])){
        foreach($_REQUEST['cat'] as $sKey=>$sVal){
            if((int)$sVal!=0){
                $aWhere[] = 'cat_id='.(int)$sVal;
                $aPath .= 'cat[]='.(int)$sVal.'&';
            }
        }
    }
    /// Categories Code Ends ///
    
    // Build query for total count
    $count_query = "SELECT COUNT(*) as total FROM products";
    if(count($aWhere) > 0) {
        $count_query .= ' WHERE ' . implode(' AND ', $aWhere);
    }
    
    // Execute count query with error checking
    $count_result = mysqli_query($db, $count_query);
    if(!$count_result) {
        error_log("Count query failed: " . mysqli_error($db));
        die("Error retrieving product count. Please try again.");
    }
    
    // Get total records and calculate pages
    $total_records = (int)mysqli_fetch_assoc($count_result)['total'];
    $total_pages = max(1, ceil($total_records / $per_page));
    
    // Get and validate current page
    $current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $current_page = max(1, min($current_page, $total_pages)); // Ensure page is within valid range
    
    // Log pagination info for debugging
    error_log(sprintf(
        "Pagination info - Total records: %d, Per page: %d, Total pages: %d, Current page: %d",
        $total_records, $per_page, $total_pages, $current_page
    ));
    
    // Show first page button
    $firstClass = ($current_page == 1) ? ' class="disabled"' : '';
    echo "<li$firstClass><a href='shop.php?page=1" . (!empty($aPath) ? "&".$aPath : "") . "'>&laquo; First</a></li>";
    
    // Previous page
    if ($current_page > 1) {
        echo "<li><a href='shop.php?page=" . ($current_page - 1) . (!empty($aPath) ? "&".$aPath : "") . "'>&lsaquo; Previous</a></li>";
    }
    
    // Show page numbers with ellipsis
    $start_page = max(1, $current_page - 2);
    $end_page = min($total_pages, $current_page + 2);
    
    if ($start_page > 1) {
        echo "<li><span>...</span></li>";
    }
    
    for ($i = $start_page; $i <= $end_page; $i++) {
        $activeClass = ($i == $current_page) ? ' class="active"' : '';
        echo "<li$activeClass><a href='shop.php?page=" . $i . (!empty($aPath) ? "&".$aPath : "") . "'>" . $i . "</a></li>";
    }
    
    if ($end_page < $total_pages) {
        echo "<li><span>...</span></li>";
    }
    
    // Next page
    if ($current_page < $total_pages) {
        echo "<li><a href='shop.php?page=" . ($current_page + 1) . (!empty($aPath) ? "&".$aPath : "") . "'>Next &rsaquo;</a></li>";
    }
    
    // Show last page button
    $lastClass = ($current_page == $total_pages) ? ' class="disabled"' : '';
    echo "<li$lastClass><a href='shop.php?page=$total_pages" . (!empty($aPath) ? "&".$aPath : "") . "'>Last &raquo;</a></li>";
    }
}
/// getPaginator Function Ends ///
?>