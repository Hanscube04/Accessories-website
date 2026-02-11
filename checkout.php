<?php
require_once 'database.php';
require_once 'header.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Get cart total
$total_sql = "SELECT SUM(p.price * c.quantity) as total 
              FROM cart c 
              JOIN products p ON c.product_id = p.id 
              WHERE c.user_id = $user_id";
$total_result = $conn->query($total_sql);
$cart_total = $total_result->fetch_assoc()['total'];

// Handle coupon application
$discount = 0;
$coupon_code = '';
$coupon_message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['apply_coupon'])) {
        $coupon_code = sanitize($_POST['coupon_code']);
        
        // Validate coupon
        $coupon_sql = "SELECT * FROM coupons WHERE code = '$coupon_code' 
                      AND valid_from <= CURDATE() 
                      AND valid_to >= CURDATE()";
        $coupon_result = $conn->query($coupon_sql);
        
        if ($coupon_result->num_rows > 0) {
            $coupon = $coupon_result->fetch_assoc();
            
            // Check minimum order
            if ($coupon['min_order'] > 0 && $cart_total < $coupon['min_order']) {
                $coupon_message = "Minimum order of Tsh " . number_format($coupon['min_order']) . " required";
            } else {
                // Check max uses
                $uses_sql = "SELECT COUNT(*) as used FROM orders WHERE coupon_code = '$coupon_code'";
                $uses_result = $conn->query($uses_sql);
                $used = $uses_result->fetch_assoc()['used'];
                
                if ($coupon['max_uses'] > 0 && $used >= $coupon['max_uses']) {
                    $coupon_message = "Coupon has reached maximum uses";
                } else {
                    // Apply discount
                    if ($coupon['discount_type'] == 'percentage') {
                        $discount = ($cart_total * $coupon['discount_value']) / 100;
                    } else {
                        $discount = $coupon['discount_value'];
                    }
                    
                    // Ensure discount doesn't exceed total
                    if ($discount > $cart_total) {
                        $discount = $cart_total;
                    }
                    
                    $coupon_message = "Coupon applied successfully!";
                }
            }
        } else {
            $coupon_message = "Invalid or expired coupon code";
        }
    }
    
    // Process checkout
    if (isset($_POST['place_order'])) {
        $shipping_address = sanitize($_POST['shipping_address']);
        $payment_method = sanitize($_POST['payment_method']);
        $phone = sanitize($_POST['phone']);
        
        // Final amount after discount
        $final_amount = $cart_total - $discount + 10000; // Add shipping
        
        // Create order
        $order_sql = "INSERT INTO orders (user_id, total_amount, discount, coupon_code, shipping_address, payment_method, phone) 
                      VALUES ($user_id, $final_amount, $discount, '$coupon_code', '$shipping_address', '$payment_method', '$phone')";
        
        if ($conn->query($order_sql)) {
            $order_id = $conn->insert_id;
            
            // Get cart items
            $cart_items = $conn->query("SELECT c.*, p.price FROM cart c JOIN products p ON c.product_id = p.id WHERE c.user_id = $user_id");
            
            while ($item = $cart_items->fetch_assoc()) {
                $conn->query("INSERT INTO order_items (order_id, product_id, quantity, price) 
                             VALUES ($order_id, {$item['product_id']}, {$item['quantity']}, {$item['price']})");
                
                // Update stock
                $conn->query("UPDATE products SET stock_quantity = stock_quantity - {$item['quantity']} WHERE id = {$item['product_id']}");
            }
            
            // Clear cart
            $conn->query("DELETE FROM cart WHERE user_id = $user_id");
            
            $_SESSION['order_id'] = $order_id;
            header("Location: order_success.php");
            exit();
        }
    }
}

// Get user details
$user_sql = "SELECT * FROM users WHERE id = $user_id";
$user_result = $conn->query($user_sql);
$user = $user_result->fetch_assoc();

// Calculate totals
$subtotal = $cart_total;
$shipping = 10000;
$total = $subtotal - $discount + $shipping;
?>

<h1>Checkout</h1>

<form method="POST" action="" onsubmit="return validateForm()">
    <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 30px;">
        <!-- Left Column: Shipping Info -->
        <div>
            <h3>Shipping Information</h3>
            
            <div class="form-group">
                <label>Full Name *</label>
                <input type="text" name="fullname" class="form-control" value="<?php echo $user['full_name']; ?>" required readonly>
            </div>
            
            <div class="form-group">
                <label>Email *</label>
                <input type="email" name="email" class="form-control" value="<?php echo $user['email']; ?>" required readonly>
            </div>
            
            <div class="form-group">
                <label>Phone Number *</label>
                <input type="tel" name="phone" class="form-control" value="<?php echo $user['phone']; ?>" required>
            </div>
            
            <div class="form-group">
                <label>Shipping Address *</label>
                <textarea name="shipping_address" class="form-control" rows="4" required 
                          placeholder="Street, City, Region"></textarea>
            </div>
            
            <div class="form-group">
                <label>Payment Method *</label>
                <select name="payment_method" class="form-control" required>
                    <option value="">Select Payment Method</option>
                    <option value="vodacom_mpesa">Vodacom M-Pesa</option>
                    <option value="airtel_money">Airtel Money</option>
                    <option value="tigo_pesa">Tigo Pesa</option>
                    <option value="halotel">Halotel</option>
                    <option value="cash_on_delivery">Cash on Delivery</option>
                </select>
            </div>
        </div>
        
        <!-- Right Column: Order Summary -->
        <div>
            <h3>Order Summary</h3>
            <div style="background: #f8f9fa; padding: 20px; border-radius: 5px;">
                <!-- Coupon Section -->
                <div style="margin-bottom: 20px;">
                    <label><strong>Have a coupon code?</strong></label>
                    <div style="display: flex; gap: 10px; margin-top: 10px;">
                        <input type="text" name="coupon_code" class="form-control" 
                               value="<?php echo $coupon_code; ?>" placeholder="Enter coupon code">
                        <button type="submit" name="apply_coupon" class="btn">Apply</button>
                    </div>
                    <?php if($coupon_message): ?>
                        <p style="margin-top: 10px; color: <?php echo strpos($coupon_message, 'success') !== false ? 'green' : 'red'; ?>;">
                            <?php echo $coupon_message; ?>
                        </p>
                    <?php endif; ?>
                </div>
                
                <!-- Order Summary -->
                <div style="border-top: 1px solid #ddd; padding-top: 15px;">
                    <div style="display: flex; justify-content: space-between; margin-bottom: 10px;">
                        <span>Subtotal:</span>
                        <span>Tsh <?php echo number_format($subtotal); ?></span>
                    </div>
                    
                    <?php if($discount > 0): ?>
                    <div style="display: flex; justify-content: space-between; margin-bottom: 10px; color: green;">
                        <span>Discount:</span>
                        <span>- Tsh <?php echo number_format($discount); ?></span>
                    </div>
                    <?php endif; ?>
                    
                    <div style="display: flex; justify-content: space-between; margin-bottom: 10px;">
                        <span>Shipping:</span>
                        <span>Tsh <?php echo number_format($shipping); ?></span>
                    </div>
                    
                    <div style="display: flex; justify-content: space-between; font-size: 18px; font-weight: bold; border-top: 2px solid #ddd; padding-top: 15px; margin-top: 15px;">
                        <span>Total:</span>
                        <span>Tsh <?php echo number_format($total); ?></span>
                    </div>
                </div>
                
                <!-- Available Coupons -->
                <div style="margin-top: 20px;">
                    <h4>Available Coupons</h4>
                    <?php
                    $available_coupons = $conn->query("SELECT * FROM coupons WHERE valid_from <= CURDATE() AND valid_to >= CURDATE() LIMIT 3");
                    if ($available_coupons->num_rows > 0):
                    ?>
                        <div style="font-size: 14px;">
                            <?php while($coupon = $available_coupons->fetch_assoc()): ?>
                            <div style="background: white; padding: 8px; margin-bottom: 5px; border: 1px dashed #ddd; border-radius: 3px;">
                                <strong><?php echo $coupon['code']; ?></strong> - 
                                <?php if($coupon['discount_type'] == 'percentage'): ?>
                                    <?php echo $coupon['discount_value']; ?>% off
                                <?php else: ?>
                                    Tsh <?php echo number_format($coupon['discount_value']); ?> off
                                <?php endif; ?>
                                <?php if($coupon['min_order']): ?>
                                    (Min: Tsh <?php echo number_format($coupon['min_order']); ?>)
                                <?php endif; ?>
                            </div>
                            <?php endwhile; ?>
                        </div>
                    <?php endif; ?>
                </div>
                
                <!-- Place Order Button -->
                <button type="submit" name="place_order" class="btn" style="width: 100%; margin-top: 20px; padding: 12px; font-size: 16px;">
                    Place Order
                </button>
            </div>
        </div>
    </div>
</form>

<?php require_once 'footer.php'; ?>