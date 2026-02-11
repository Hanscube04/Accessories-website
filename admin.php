<?php
require_once 'database.php';
session_start();

// Check if user is admin (you need to add 'is_admin' field to users table)
if (!isset($_SESSION['user_id']) || $_SESSION['is_admin'] != 1) {
    header("Location: login.php");
    exit();
}

// Handle admin actions
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['add_product'])) {
        // Add new product
        $name = sanitize($_POST['name']);
        $description = sanitize($_POST['description']);
        $price = sanitize($_POST['price']);
        $category = sanitize($_POST['category']);
        $stock = sanitize($_POST['stock_quantity']);
        
        $sql = "INSERT INTO products (name, description, price, category, stock_quantity) 
                VALUES ('$name', '$description', $price, '$category', $stock)";
        $conn->query($sql);
        $success = "Product added successfully!";
    }
    
    if (isset($_POST['update_order_status'])) {
        // Update order status
        $order_id = sanitize($_POST['order_id']);
        $status = sanitize($_POST['status']);
        
        $sql = "UPDATE orders SET status = '$status' WHERE id = $order_id";
        $conn->query($sql);
        $success = "Order status updated!";
    }
}

// Get statistics
$total_users = $conn->query("SELECT COUNT(*) as count FROM users")->fetch_assoc()['count'];
$total_products = $conn->query("SELECT COUNT(*) as count FROM products")->fetch_assoc()['count'];
$total_orders = $conn->query("SELECT COUNT(*) as count FROM orders")->fetch_assoc()['count'];
$total_revenue = $conn->query("SELECT SUM(total_amount) as total FROM orders WHERE status = 'delivered'")->fetch_assoc()['total'];
$total_revenue = $total_revenue ?: 0;

require_once 'header.php';
?>

<div class="admin-dashboard">
    <h1>Admin Dashboard</h1>
    
    <?php if (isset($success)) echo showMessage('success', $success); ?>
    
    <!-- Admin Navigation -->
    <div class="admin-nav" style="margin: 20px 0; padding: 10px; background: #f8f9fa; border-radius: 5px;">
        <a href="#dashboard" class="btn" style="margin-right: 10px;">Dashboard</a>
        <a href="#products" class="btn" style="margin-right: 10px;">Products</a>
        <a href="#orders" class="btn" style="margin-right: 10px;">Orders</a>
        <a href="#users" class="btn" style="margin-right: 10px;">Users</a>
        <a href="#reviews" class="btn" style="margin-right: 10px;">Reviews</a>
        <a href="#coupons" class="btn" style="margin-right: 10px;">Coupons</a>
    </div>
    
    <!-- Statistics -->
    <div id="dashboard" style="margin: 30px 0;">
        <h2>Statistics</h2>
        <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; margin-top: 20px;">
            <div style="background: #3498db; color: white; padding: 20px; border-radius: 5px; text-align: center;">
                <h3>Total Users</h3>
                <p style="font-size: 24px; font-weight: bold;"><?php echo $total_users; ?></p>
            </div>
            <div style="background: #2ecc71; color: white; padding: 20px; border-radius: 5px; text-align: center;">
                <h3>Total Products</h3>
                <p style="font-size: 24px; font-weight: bold;"><?php echo $total_products; ?></p>
            </div>
            <div style="background: #9b59b6; color: white; padding: 20px; border-radius: 5px; text-align: center;">
                <h3>Total Orders</h3>
                <p style="font-size: 24px; font-weight: bold;"><?php echo $total_orders; ?></p>
            </div>
            <div style="background: #e74c3c; color: white; padding: 20px; border-radius: 5px; text-align: center;">
                <h3>Total Revenue</h3>
                <p style="font-size: 24px; font-weight: bold;">Tsh <?php echo number_format($total_revenue); ?></p>
            </div>
        </div>
    </div>
    
    <!-- Recent Orders -->
    <div id="orders" style="margin: 30px 0;">
        <h2>Recent Orders</h2>
        <?php
        $orders_sql = "SELECT o.*, u.full_name FROM orders o 
                      JOIN users u ON o.user_id = u.id 
                      ORDER BY o.order_date DESC LIMIT 10";
        $orders_result = $conn->query($orders_sql);
        ?>
        
        <table>
            <thead>
                <tr>
                    <th>Order ID</th>
                    <th>Customer</th>
                    <th>Amount</th>
                    <th>Status</th>
                    <th>Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while($order = $orders_result->fetch_assoc()): ?>
                <tr>
                    <td>#<?php echo $order['id']; ?></td>
                    <td><?php echo $order['full_name']; ?></td>
                    <td>Tsh <?php echo number_format($order['total_amount']); ?></td>
                    <td>
                        <form method="POST" style="display: inline;">
                            <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                            <select name="status" onchange="this.form.submit()">
                                <option value="pending" <?php if($order['status']=='pending') echo 'selected'; ?>>Pending</option>
                                <option value="processing" <?php if($order['status']=='processing') echo 'selected'; ?>>Processing</option>
                                <option value="shipped" <?php if($order['status']=='shipped') echo 'selected'; ?>>Shipped</option>
                                <option value="delivered" <?php if($order['status']=='delivered') echo 'selected'; ?>>Delivered</option>
                                <option value="cancelled" <?php if($order['status']=='cancelled') echo 'selected'; ?>>Cancelled</option>
                            </select>
                            <input type="hidden" name="update_order_status" value="1">
                        </form>
                    </td>
                    <td><?php echo date('d/m/Y H:i', strtotime($order['order_date'])); ?></td>
                    <td>
                        <a href="order_details_admin.php?id=<?php echo $order['id']; ?>" class="btn">View</a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
    
    <!-- Add Product Form -->
    <div id="products" style="margin: 30px 0;">
        <h2>Add New Product</h2>
        <form method="POST" action="" style="max-width: 600px;">
            <div class="form-group">
                <label>Product Name *</label>
                <input type="text" name="name" class="form-control" required>
            </div>
            
            <div class="form-group">
                <label>Description *</label>
                <textarea name="description" class="form-control" rows="3" required></textarea>
            </div>
            
            <div class="form-group">
                <label>Price (Tsh) *</label>
                <input type="number" name="price" class="form-control" step="0.01" required>
            </div>
            
            <div class="form-group">
                <label>Category *</label>
                <select name="category" class="form-control" required>
                    <option value="laptop">Laptop</option>
                    <option value="phone">Phone</option>
                    <option value="desktop">Desktop</option>
                    <option value="accessory">Accessory</option>
                </select>
            </div>
            
            <div class="form-group">
                <label>Stock Quantity *</label>
                <input type="number" name="stock_quantity" class="form-control" required>
            </div>
            
            <button type="submit" name="add_product" class="btn">Add Product</button>
        </form>
        
        <!-- Product List -->
        <h3 style="margin-top: 30px;">All Products</h3>
        <?php
        $products_sql = "SELECT * FROM products ORDER BY id DESC";
        $products_result = $conn->query($products_sql);
        ?>
        
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Price</th>
                    <th>Category</th>
                    <th>Stock</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while($product = $products_result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $product['id']; ?></td>
                    <td><?php echo $product['name']; ?></td>
                    <td>Tsh <?php echo number_format($product['price']); ?></td>
                    <td><?php echo ucfirst($product['category']); ?></td>
                    <td><?php echo $product['stock_quantity']; ?></td>
                    <td>
                        <a href="edit_product.php?id=<?php echo $product['id']; ?>" class="btn">Edit</a>
                        <a href="delete_product.php?id=<?php echo $product['id']; ?>" 
                           class="btn" style="background: #e74c3c;"
                           onclick="return confirm('Delete this product?')">Delete</a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
    
    <!-- User Management -->
    <div id="users" style="margin: 30px 0;">
        <h2>User Management</h2>
        <?php
        $users_sql = "SELECT * FROM users ORDER BY created_at DESC";
        $users_result = $conn->query($users_sql);
        ?>
        
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Joined Date</th>
                    <th>Admin</th>
                </tr>
            </thead>
            <tbody>
                <?php while($user = $users_result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $user['id']; ?></td>
                    <td><?php echo $user['full_name']; ?></td>
                    <td><?php echo $user['email']; ?></td>
                    <td><?php echo $user['phone']; ?></td>
                    <td><?php echo date('d/m/Y', strtotime($user['created_at'])); ?></td>
                    <td>
                        <?php if($user['is_admin'] == 1): ?>
                            <span style="color: green;">✓ Admin</span>
                        <?php else: ?>
                            <span style="color: #666;">User</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
    
</div>

<?php require_once 'footer.php'; ?>