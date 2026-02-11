<?php
require_once 'database.php';
require_once 'header.php';

if (!isset($_SESSION['user_id']) || !isset($_SESSION['order_id'])) {
    header("Location: index.php");
    exit();
}

$order_id = $_SESSION['order_id'];
unset($_SESSION['order_id']);
?>

<h1>Order Successful!</h1>

<div style="text-align: center; padding: 40px;">
    <div style="font-size: 48px; color: #27ae60; margin-bottom: 20px;">✓</div>
    <h2>Thank You for Your Order!</h2>
    <p>Your order has been placed successfully.</p>
    <p><strong>Order ID: #<?php echo $order_id; ?></strong></p>
    <p>We will contact you soon regarding your order.</p>
    
    <div style="margin-top: 30px;">
        <a href="orders.php" class="btn">View My Orders</a>
        <a href="products.php" class="btn">Continue Shopping</a>
    </div>
</div>

<?php require_once 'footer.php'; ?>