<?php
/**
 * Email Confirmation Bypass for Demo
 * This file allows manual email confirmation for demo purposes
 * when actual email sending is not available
 */

include("../includes/db.php");

if(isset($_GET['confirm']) && isset($_GET['email'])) {
    $confirm_code = mysqli_real_escape_string($con, $_GET['confirm']);
    $email = mysqli_real_escape_string($con, $_GET['email']);
    
    // Find customer with this confirmation code and email
    $check_customer = "SELECT * FROM customers WHERE customer_confirm_code='$confirm_code' AND customer_email='$email'";
    $run_check = mysqli_query($con, $check_customer);
    
    if($run_check && mysqli_num_rows($run_check) > 0) {
        $customer = mysqli_fetch_array($run_check);
        
        // Confirm the customer account
        $update_customer = "UPDATE customers SET customer_confirm_code='' WHERE customer_confirm_code='$confirm_code'";
        $run_confirm = mysqli_query($con, $update_customer);
        
        if($run_confirm) {
            echo "<!DOCTYPE html>
            <html>
            <head>
                <title>Email Confirmed</title>
                <link rel='stylesheet' href='../styles/bootstrap-337.min.css'>
                <style>
                    body { background: #f8f9fa; padding: 50px 0; }
                    .confirmation-box { max-width: 500px; margin: 0 auto; background: white; padding: 40px; border-radius: 10px; box-shadow: 0 2px 20px rgba(0,0,0,0.1); text-align: center; }
                    .success-icon { font-size: 60px; color: #28a745; margin-bottom: 20px; }
                </style>
            </head>
            <body>
                <div class='confirmation-box'>
                    <div class='success-icon'>✓</div>
                    <h2>Email Confirmed Successfully!</h2>
                    <p>Thank you, <strong>" . $customer['customer_name'] . "</strong>!</p>
                    <p>Your email address has been confirmed. You can now enjoy full access to your account.</p>
                    <a href='my_account.php?my_orders' class='btn btn-primary'>Go to My Account</a>
                </div>
            </body>
            </html>";
        } else {
            echo "Error confirming email. Please try again.";
        }
    } else {
        echo "Invalid confirmation link or email already confirmed.";
    }
} else {
    echo "Missing confirmation parameters.";
}
?>
