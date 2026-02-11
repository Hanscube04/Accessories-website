<?php
require_once 'database.php';
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['is_admin'] != 1) {
    header("Location: login.php");
    exit();
}

// Handle coupon actions
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['add_coupon'])) {
        $code = sanitize($_POST['code']);
        $discount_type = sanitize($_POST['discount_type']);
        $discount_value = sanitize($_POST['discount_value']);
        $min_order = sanitize($_POST['min_order']);
        $max_uses = sanitize($_POST['max_uses']);
        $valid_from = sanitize($_POST['valid_from']);
        $valid_to = sanitize($_POST['valid_to']);
        
        $sql = "INSERT INTO coupons (code, discount_type, discount_value, min_order, max_uses, valid_from, valid_to) 
                VALUES ('$code', '$discount_type', $discount_value, $min_order, $max_uses, '$valid_from', '$valid_to')";
        
        if ($conn->query($sql)) {
            $success = "Coupon added successfully!";
        } else {
            $error = "Error: " . $conn->error;
        }
    }
    
    if (isset($_POST['delete_coupon'])) {
        $coupon_id = sanitize($_POST['coupon_id']);
        $conn->query("DELETE FROM coupons WHERE id = $coupon_id");
        $success = "Coupon deleted!";
    }
}

require_once 'header.php';
?>

<div class="coupon-management">
    <h1>Coupon Management</h1>
    
    <?php 
    if (isset($success)) echo showMessage('success', $success);
    if (isset($error)) echo showMessage('error', $error);
    ?>
    
    <!-- Add Coupon Form -->
    <div style="background: #f8f9fa; padding: 20px; border-radius: 5px; margin-bottom: 30px;">
        <h2>Add New Coupon</h2>
        <form method="POST" action="">
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                <div class="form-group">
                    <label>Coupon Code *</label>
                    <input type="text" name="code" class="form-control" required 
                           placeholder="e.g., SUMMER2024">
                </div>
                
                <div class="form-group">
                    <label>Discount Type *</label>
                    <select name="discount_type" class="form-control" required>
                        <option value="percentage">Percentage</option>
                        <option value="fixed">Fixed Amount</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>Discount Value *</label>
                    <input type="number" name="discount_value" class="form-control" step="0.01" required
                           placeholder="10 for 10% or 5000 for Tsh 5000">
                </div>
                
                <div class="form-group">
                    <label>Minimum Order Amount</label>
                    <input type="number" name="min_order" class="form-control" step="0.01"
                           placeholder="Minimum order to use coupon">
                </div>
                
                <div class="form-group">
                    <label>Maximum Uses</label>
                    <input type="number" name="max_uses" class="form-control"
                           placeholder="Leave empty for unlimited">
                </div>
                
                <div class="form-group">
                    <label>Valid From *</label>
                    <input type="date" name="valid_from" class="form-control" required>
                </div>
                
                <div class="form-group">
                    <label>Valid To *</label>
                    <input type="date" name="valid_to" class="form-control" required>
                </div>
            </div>
            
            <button type="submit" name="add_coupon" class="btn" style="margin-top: 20px;">Add Coupon</button>
        </form>
    </div>
    
    <!-- Coupon List -->
    <h2>Active Coupons</h2>
    <?php
    $coupons_sql = "SELECT * FROM coupons WHERE valid_to >= CURDATE() ORDER BY created_at DESC";
    $coupons_result = $conn->query($coupons_sql);
    ?>
    
    <table>
        <thead>
            <tr>
                <th>Code</th>
                <th>Type</th>
                <th>Value</th>
                <th>Min Order</th>
                <th>Uses Left</th>
                <th>Valid Until</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php while($coupon = $coupons_result->fetch_assoc()): 
                // Calculate uses left
                $uses_sql = "SELECT COUNT(*) as used FROM orders WHERE coupon_code = '{$coupon['code']}'";
                $uses_result = $conn->query($uses_sql);
                $used = $uses_result->fetch_assoc()['used'];
                $uses_left = $coupon['max_uses'] ? $coupon['max_uses'] - $used : 'Unlimited';
            ?>
            <tr>
                <td><strong><?php echo $coupon['code']; ?></strong></td>
                <td><?php echo ucfirst($coupon['discount_type']); ?></td>
                <td>
                    <?php if($coupon['discount_type'] == 'percentage'): ?>
                        <?php echo $coupon['discount_value']; ?>%
                    <?php else: ?>
                        Tsh <?php echo number_format($coupon['discount_value']); ?>
                    <?php endif; ?>
                </td>
                <td>
                    <?php if($coupon['min_order']): ?>
                        Tsh <?php echo number_format($coupon['min_order']); ?>
                    <?php else: ?>
                        None
                    <?php endif; ?>
                </td>
                <td><?php echo $uses_left; ?></td>
                <td><?php echo date('d/m/Y', strtotime($coupon['valid_to'])); ?></td>
                <td>
                    <form method="POST" action="" style="display: inline;">
                        <input type="hidden" name="coupon_id" value="<?php echo $coupon['id']; ?>">
                        <button type="submit" name="delete_coupon" class="btn" 
                                style="background: #e74c3c;"
                                onclick="return confirm('Delete this coupon?')">Delete</button>
                    </form>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
    
    <!-- Expired Coupons -->
    <h2 style="margin-top: 40px;">Expired Coupons</h2>
    <?php
    $expired_sql = "SELECT * FROM coupons WHERE valid_to < CURDATE() ORDER BY valid_to DESC LIMIT 10";
    $expired_result = $conn->query($expired_sql);
    ?>
    
    <table>
        <thead>
            <tr>
                <th>Code</th>
                <th>Type</th>
                <th>Value</th>
                <th>Expired Date</th>
                <th>Total Uses</th>
            </tr>
        </thead>
        <tbody>
            <?php while($coupon = $expired_result->fetch_assoc()): 
                $uses_sql = "SELECT COUNT(*) as used FROM orders WHERE coupon_code = '{$coupon['code']}'";
                $uses_result = $conn->query($uses_sql);
                $used = $uses_result->fetch_assoc()['used'];
            ?>
            <tr style="color: #999;">
                <td><?php echo $coupon['code']; ?></td>
                <td><?php echo ucfirst($coupon['discount_type']); ?></td>
                <td>
                    <?php if($coupon['discount_type'] == 'percentage'): ?>
                        <?php echo $coupon['discount_value']; ?>%
                    <?php else: ?>
                        Tsh <?php echo number_format($coupon['discount_value']); ?>
                    <?php endif; ?>
                </td>
                <td><?php echo date('d/m/Y', strtotime($coupon['valid_to'])); ?></td>
                <td><?php echo $used; ?></td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

<?php require_once 'footer.php'; ?>