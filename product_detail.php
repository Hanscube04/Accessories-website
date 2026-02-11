<?php
require_once 'database.php';
require_once 'header.php';

$product_id = isset($_GET['id']) ? sanitize($_GET['id']) : 0;

// Get product details
$sql = "SELECT * FROM products WHERE id = $product_id";
$result = $conn->query($sql);

if ($result->num_rows == 0) {
    echo "<p>Product not found.</p>";
    require_once 'footer.php';
    exit();
}

$product = $result->fetch_assoc();

// Get average rating
$rating_sql = "SELECT AVG(rating) as avg_rating, COUNT(*) as count FROM reviews WHERE product_id = $product_id";
$rating_result = $conn->query($rating_sql);
$rating_data = $rating_result->fetch_assoc();
$avg_rating = $rating_data['avg_rating'] ? round($rating_data['avg_rating'], 1) : 0;
$review_count = $rating_data['count'];

// Get recent reviews
$reviews_sql = "SELECT r.*, u.full_name FROM reviews r 
                JOIN users u ON r.user_id = u.id 
                WHERE r.product_id = $product_id 
                ORDER BY r.created_at DESC LIMIT 3";
$reviews_result = $conn->query($reviews_sql);
?>

<h1><?php echo $product['name']; ?></h1>

<div style="display: flex; gap: 30px; margin-top: 20px;">
    <!-- Product Image & Details -->
    <div style="flex: 1;">
        <div style="background: #f8f9fa; padding: 20px; border-radius: 5px; text-align: center;">
            <h3><?php echo $product['name']; ?></h3>
            <p><?php echo $product['description']; ?></p>
            
            <!-- Rating Display -->
            <?php if ($avg_rating > 0): ?>
            <div style="margin: 15px 0;">
                <div style="color: #f39c12; font-size: 20px;">
                    <?php for($i = 1; $i <= 5; $i++): ?>
                        <?php if($i <= floor($avg_rating)): ?>
                            ★
                        <?php elseif($i - 0.5 <= $avg_rating): ?>
                            ☆
                        <?php else: ?>
                            ☆
                        <?php endif; ?>
                    <?php endfor; ?>
                </div>
                <p><?php echo $avg_rating; ?> / 5 (<?php echo $review_count; ?> reviews)</p>
                <a href="reviews.php?product_id=<?php echo $product_id; ?>" class="btn">View All Reviews</a>
            </div>
            <?php else: ?>
            <div style="margin: 15px 0;">
                <p>No reviews yet</p>
                <a href="reviews.php?product_id=<?php echo $product_id; ?>" class="btn">Be the first to review</a>
            </div>
            <?php endif; ?>
            
            <p class="price" style="font-size: 24px; margin: 20px 0;">Tsh <?php echo number_format($product['price']); ?></p>
            <p><strong>Stock Available: <?php echo $product['stock_quantity']; ?></strong></p>
            
            <?php if(isset($_SESSION['user_id'])): ?>
            <form method="POST" action="add_to_cart.php" style="margin-top: 20px;">
                <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                <div style="margin-bottom: 15px;">
                    <label>Quantity:</label>
                    <input type="number" name="quantity" value="1" min="1" max="<?php echo $product['stock_quantity']; ?>" 
                           style="width: 80px; padding: 8px; margin-left: 10px;">
                </div>
                <button type="submit" class="btn" style="font-size: 18px; padding: 10px 30px;">Add to Cart</button>
            </form>
            <?php else: ?>
            <p style="margin-top: 20px;"><a href="login.php">Login</a> to purchase this product</p>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Recent Reviews -->
    <div style="flex: 1;">
        <h3>Recent Reviews</h3>
        
        <?php if ($reviews_result->num_rows > 0): ?>
            <?php while($review = $reviews_result->fetch_assoc()): ?>
            <div style="border: 1px solid #eee; padding: 15px; margin-bottom: 15px; border-radius: 5px;">
                <div style="display: flex; justify-content: space-between;">
                    <strong><?php echo $review['full_name']; ?></strong>
                    <div style="color: #f39c12;">
                        <?php for($i = 1; $i <= 5; $i++): ?>
                            <?php if($i <= $review['rating']): ?>
                                ★
                            <?php else: ?>
                                ☆
                            <?php endif; ?>
                        <?php endfor; ?>
                    </div>
                </div>
                <p style="margin-top: 10px; color: #666;"><?php echo substr($review['comment'], 0, 150); ?>...</p>
                <p style="font-size: 12px; color: #999; margin-top: 10px;">
                    <?php echo date('d M Y', strtotime($review['created_at'])); ?>
                </p>
            </div>
            <?php endwhile; ?>
            
            <div style="text-align: center; margin-top: 20px;">
                <a href="reviews.php?product_id=<?php echo $product_id; ?>" class="btn">See All Reviews</a>
                <?php if(isset($_SESSION['user_id'])): ?>
                <a href="reviews.php?product_id=<?php echo $product_id; ?>" class="btn">Write a Review</a>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <div style="text-align: center; padding: 30px; background: #f8f9fa; border-radius: 5px;">
                <p>No reviews yet for this product.</p>
                <?php if(isset($_SESSION['user_id'])): ?>
                <a href="reviews.php?product_id=<?php echo $product_id; ?>" class="btn">Be the first to review</a>
                <?php else: ?>
                <p><a href="login.php">Login</a> to write a review</p>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Product Specifications -->
<div style="margin-top: 30px;">
    <h3>Product Specifications</h3>
    <table>
        <tr>
            <td style="width: 200px;"><strong>Category:</strong></td>
            <td><?php echo ucfirst($product['category']); ?></td>
        </tr>
        <tr>
            <td><strong>Availability:</strong></td>
            <td>
                <?php if($product['stock_quantity'] > 0): ?>
                <span style="color: green;">In Stock (<?php echo $product['stock_quantity']; ?> units)</span>
                <?php else: ?>
                <span style="color: red;">Out of Stock</span>
                <?php endif; ?>
            </td>
        </tr>
        <tr>
            <td><strong>Warranty:</strong></td>
            <td>1 Year Warranty</td>
        </tr>
    </table>
</div>

<!-- Related Products -->
<div style="margin-top: 40px;">
    <h3>Related Products</h3>
    <?php
    $related_sql = "SELECT * FROM products 
                    WHERE category = '{$product['category']}' 
                    AND id != $product_id 
                    LIMIT 3";
    $related_result = $conn->query($related_sql);
    ?>
    
    <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; margin-top: 20px;">
        <?php while($related = $related_result->fetch_assoc()): ?>
        <div style="border: 1px solid #ddd; padding: 15px; border-radius: 5px; text-align: center;">
            <h4><?php echo $related['name']; ?></h4>
            <p>Tsh <?php echo number_format($related['price']); ?></p>
            <a href="product_detail.php?id=<?php echo $related['id']; ?>" class="btn">View Details</a>
        </div>
        <?php endwhile; ?>
    </div>
</div>

<?php require_once 'footer.php'; ?>