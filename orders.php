<?php
require_once 'database.php';
require_once 'header.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Get user orders
$sql = "SELECT * FROM orders WHERE user_id = $user_id ORDER BY order_date DESC";
$result = $conn->query($sql);
?>

<h1>My Orders</h1>

<?php if ($result->num_rows > 0): ?>
    <table>
        <thead>
            <tr>
                <th>Order ID</th>
                <th>Date</th>
                <th>Total Amount</th>
                <th>Status</th>
                <th>Payment Method</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php while($order = $result->fetch_assoc()): ?>
            <tr>
                <td>#<?php echo $order['id']; ?></td>
                <td><?php echo date('d/m/Y', strtotime($order['order_date'])); ?></td>
                <td>Tsh <?php echo number_format($order['total_amount'] + 10000); ?></td>
                <td>
                    <?php 
                    $status_colors = [
                        'pending' => '#f39c12',
                        'processing' => '#3498db',
                        'shipped' => '#9b59b6',
                        'delivered' => '#27ae60',
                        'cancelled' => '#e74c3c'
                    ];
                    $color = $status_colors[$order['status']] ?? '#95a5a6';
                    ?>
                    <span style="background-color: <?php echo $color; ?>; color: white; padding: 5px 10px; border-radius: 3px;">
                        <?php echo ucfirst($order['status']); ?>
                    </span>
                </td>
                <td><?php echo ucwords(str_replace('_', ' ', $order['payment_method'])); ?></td>
                <td>
                    <a href="order_details.php?id=<?php echo $order['id']; ?>" class="btn">View Details</a>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
<?php else: ?>
    <p>You haven't placed any orders yet. <a href="products.php">Start shopping</a></p>
<?php endif; ?>

<?php require_once 'footer.php'; ?>