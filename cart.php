<?php
require_once 'database.php';
require_once 'header.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Handle remove item
if (isset($_GET['remove'])) {
    $item_id = sanitize($_GET['remove']);
    $conn->query("DELETE FROM cart WHERE id = $item_id AND user_id = $user_id");
    $_SESSION['message'] = "Item removed from cart";
}

// Handle update quantity
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update'])) {
    foreach ($_POST['quantity'] as $item_id => $quantity) {
        $item_id = sanitize($item_id);
        $quantity = sanitize($quantity);
        
        if ($quantity > 0) {
            $conn->query("UPDATE cart SET quantity = $quantity WHERE id = $item_id AND user_id = $user_id");
        } else {
            $conn->query("DELETE FROM cart WHERE id = $item_id AND user_id = $user_id");
        }
    }
    $_SESSION['message'] = "Cart updated successfully";
}

// Display messages
if (isset($_SESSION['message'])) {
    echo showMessage('success', $_SESSION['message']);
    unset($_SESSION['message']);
}

// Get cart items
$sql = "SELECT c.*, p.name, p.price, p.image_url, p.stock_quantity 
        FROM cart c 
        JOIN products p ON c.product_id = p.id 
        WHERE c.user_id = $user_id";
$result = $conn->query($sql);

$total_amount = 0;
?>

<h1>Your Shopping Cart</h1>

<?php if ($result->num_rows > 0): ?>
    <form method="POST" action="">
        <table>
            <thead>
                <tr>
                    <th>Product</th>
                    <th>Price</th>
                    <th>Quantity</th>
                    <th>Total</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php while($item = $result->fetch_assoc()): 
                    $item_total = $item['price'] * $item['quantity'];
                    $total_amount += $item_total;
                ?>
                <tr>
                    <td><?php echo $item['name']; ?></td>
                    <td>Tsh <?php echo number_format($item['price']); ?></td>
                    <td>
                        <input type="number" name="quantity[<?php echo $item['id']; ?>]" 
                               value="<?php echo $item['quantity']; ?>" 
                               min="1" max="<?php echo $item['stock_quantity']; ?>" 
                               style="width: 60px;">
                    </td>
                    <td>Tsh <?php echo number_format($item_total); ?></td>
                    <td>
                        <a href="cart.php?remove=<?php echo $item['id']; ?>" 
                           class="btn" 
                           onclick="return confirm('Remove this item from cart?')"
                           style="background-color: #e74c3c;">Remove</a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
        
        <div style="margin-top: 20px;">
            <button type="submit" name="update" class="btn">Update Cart</button>
            <a href="products.php" class="btn">Continue Shopping</a>
        </div>
    </form>
    
    <div style="margin-top: 30px; padding: 20px; background-color: #f8f9fa; border-radius: 5px;">
        <h3>Order Summary</h3>
        <p><strong>Subtotal: Tsh <?php echo number_format($total_amount); ?></strong></p>
        <p>Shipping: Tsh 10,000</p>
        <p><strong>Total: Tsh <?php echo number_format($total_amount + 10000); ?></strong></p>
        
        <a href="checkout.php" class="btn" style="margin-top: 10px;">Proceed to Checkout</a>
    </div>
<?php else: ?>
    <p>Your cart is empty. <a href="products.php">Start shopping</a></p>
<?php endif; ?>

<?php require_once 'footer.php'; ?>