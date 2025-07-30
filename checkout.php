<?php
session_start();
include("includes/db.php");
include("includes/header.php");
include("functions/functions.php");
include("includes/main.php");
?>

<!-- MAIN -->
<main>
    <!-- HERO -->
    <div class="nero">
        <div class="nero__heading">
            <span class="nero__bold">Checkout</span>
        </div>
        <p class="nero__text"></p>
    </div>
</main>

<div id="content">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-12">
                <?php
                if(!isset($_SESSION['customer_email'])){
                    include("customer/customer_login.php");
                } else {
                    echo '<div class="col-lg-8 mx-auto" style="max-width: 600px; display: flex; flex-direction: column; align-items: center;">';
                    
                    // Add Virtual Try-On section before payment options
                    if(isset($_SESSION['customer_email'])) {
                        $customer_email = $_SESSION['customer_email'];
                        $get_customer = "SELECT * FROM customers WHERE customer_email='$customer_email'";
                        $run_customer = mysqli_query($con, $get_customer);
                        $row_customer = mysqli_fetch_array($run_customer);
                        $customer_id = $row_customer['customer_id'];
                        
                        // Check if customer has items in cart
                        $get_cart_count = "SELECT COUNT(*) as count FROM cart WHERE customer_id='$customer_id' OR ip_add='".$_SERVER['REMOTE_ADDR']."'";
                        $run_cart_count = mysqli_query($con, $get_cart_count);
                        $cart_count = mysqli_fetch_array($run_cart_count)['count'];
                        
                        if($cart_count > 0) {
                            echo '
                            <div class="card mb-4" style="border: 2px solid #6366f1; border-radius: 15px; background: linear-gradient(135deg, #f8f9ff 0%, #ffffff 100%);">
                                <div class="card-body text-center py-4">
                                    <div class="mb-3">
                                        <i class="fas fa-magic fa-3x text-primary mb-3"></i>
                                        <h4 class="fw-bold text-primary">Try Before You Buy!</h4>
                                        <p class="text-muted mb-4">See how your selected items look on you with our Virtual Try-On technology</p>
                                    </div>
                                    <a href="virtual_tryon.php" class="btn btn-primary btn-lg" style="background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%); border: none; border-radius: 50px; padding: 12px 30px; font-weight: 600; text-transform: uppercase; letter-spacing: 1px;">
                                        <i class="fas fa-camera me-2"></i>Virtual Try-On
                                    </a>
                                    <div class="mt-3">
                                        <small class="text-muted">
                                            <i class="fas fa-shield-alt me-1"></i>
                                            Your photos are secure and automatically deleted after 30 days
                                        </small>
                                    </div>
                                </div>
                            </div>';
                        }
                    }
                    
                    include("payment_options.php");
                    echo '</div>';
                }
                ?>
            </div>
        </div>
    </div>
</div>

<?php
include("includes/footer.php");
?>

<script src="js/jquery.min.js"></script>
<script src="js/bootstrap.min.js"></script>
</body>
</html>
