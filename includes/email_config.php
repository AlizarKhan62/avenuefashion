<?php
/**
 * Email Configuration and Helper Functions
 * This file provides email functionality for the e-commerce system
 */

// Email configuration
define('SMTP_HOST', 'localhost');  // For XAMPP Mercury/sendmail
define('SMTP_PORT', 25);
define('FROM_EMAIL', 'noreply@yourstore.com');
define('FROM_NAME', 'Your E-commerce Store');

/**
 * Send email using PHP mail function with proper headers
 */
function sendEmail($to, $subject, $message, $isHTML = true) {
    $headers = array();
    
    if($isHTML) {
        $headers[] = 'MIME-Version: 1.0';
        $headers[] = 'Content-type: text/html; charset=UTF-8';
    }
    
    $headers[] = 'From: ' . FROM_NAME . ' <' . FROM_EMAIL . '>';
    $headers[] = 'Reply-To: ' . FROM_EMAIL;
    $headers[] = 'X-Mailer: PHP/' . phpversion();
    
    $header_string = implode("\r\n", $headers);
    
    return mail($to, $subject, $message, $header_string);
}

/**
 * Send confirmation email to customer
 */
function sendConfirmationEmail($customer_email, $customer_name, $confirm_code) {
    global $_SERVER;
    
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    $domain = $_SERVER['HTTP_HOST'];
    $currentPath = dirname($_SERVER['REQUEST_URI']);
    
    // Remove '/customer' from path if present
    $currentPath = str_replace('/customer', '', $currentPath);
    
    $confirmation_link = $protocol . '://' . $domain . $currentPath . '/customer/my_account.php?' . $confirm_code;
    
    $subject = "Please Confirm Your Email Address";
    
    $message = "
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset='UTF-8'>
        <title>Email Confirmation</title>
        <style>
            body { font-family: Arial, sans-serif; margin: 0; padding: 20px; background-color: #f4f4f4; }
            .container { max-width: 600px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
            .header { text-align: center; margin-bottom: 30px; }
            .logo { font-size: 24px; font-weight: bold; color: #333; }
            .content { line-height: 1.6; color: #555; }
            .btn { display: inline-block; background: #007bff; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; margin: 20px 0; }
            .btn:hover { background: #0056b3; }
            .footer { margin-top: 30px; padding-top: 20px; border-top: 1px solid #eee; text-align: center; color: #888; font-size: 12px; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <div class='logo'>Your E-commerce Store</div>
            </div>
            <div class='content'>
                <h2>Welcome, $customer_name!</h2>
                <p>Thank you for registering with our store. To complete your registration and start shopping, please confirm your email address.</p>
                <p>Click the button below to verify your email:</p>
                <p style='text-align: center;'>
                    <a href='$confirmation_link' class='btn'>Confirm Email Address</a>
                </p>
                <p>If the button doesn't work, copy and paste this link into your browser:</p>
                <p style='word-break: break-all; background: #f8f9fa; padding: 10px; border-radius: 5px;'>$confirmation_link</p>
                <p><strong>Note:</strong> This link will expire after 24 hours for security reasons.</p>
            </div>
            <div class='footer'>
                <p>If you didn't create an account with us, please ignore this email.</p>
                <p>&copy; " . date('Y') . " Your E-commerce Store. All rights reserved.</p>
            </div>
        </div>
    </body>
    </html>";
    
    return sendEmail($customer_email, $subject, $message, true);
}

/**
 * Send order confirmation email
 */
function sendOrderConfirmationEmail($customer_email, $customer_name, $order_details) {
    $subject = "Order Confirmation - Invoice #" . $order_details['invoice_no'];
    
    $message = "
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset='UTF-8'>
        <title>Order Confirmation</title>
        <style>
            body { font-family: Arial, sans-serif; margin: 0; padding: 20px; background-color: #f4f4f4; }
            .container { max-width: 600px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
            .header { text-align: center; margin-bottom: 30px; }
            .order-info { background: #f8f9fa; padding: 20px; border-radius: 5px; margin: 20px 0; }
            .footer { margin-top: 30px; text-align: center; color: #888; font-size: 12px; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h1>Order Confirmation</h1>
            </div>
            <div class='content'>
                <p>Dear $customer_name,</p>
                <p>Thank you for your order! We've received your payment and your order is being processed.</p>
                <div class='order-info'>
                    <h3>Order Details:</h3>
                    <p><strong>Invoice Number:</strong> " . $order_details['invoice_no'] . "</p>
                    <p><strong>Tracking Number:</strong> " . $order_details['tracking_number'] . "</p>
                    <p><strong>Total Amount:</strong> Rs " . number_format($order_details['amount'], 2) . "</p>
                    <p><strong>Payment Method:</strong> " . $order_details['payment_method'] . "</p>
                </div>
                <p>You can track your order status using the tracking number provided above.</p>
            </div>
            <div class='footer'>
                <p>&copy; " . date('Y') . " Your E-commerce Store. All rights reserved.</p>
            </div>
        </div>
    </body>
    </html>";
    
    return sendEmail($customer_email, $subject, $message, true);
}

/**
 * Test email functionality
 */
function testEmailFunction() {
    $test_email = "test@example.com";
    $test_subject = "Email Test";
    $test_message = "<h1>Test Email</h1><p>If you receive this, email is working correctly.</p>";
    
    return sendEmail($test_email, $test_subject, $test_message, true);
}

// For development/testing - disable in production
if(isset($_GET['test_email']) && $_GET['test_email'] === 'true') {
    if(testEmailFunction()) {
        echo "Email test successful!";
    } else {
        echo "Email test failed. Check your email configuration.";
    }
}
?>
