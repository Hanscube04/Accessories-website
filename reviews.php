<?php
require_once 'database.php';
require_once 'header.php';

$product_id = isset($_GET['product_id']) ? sanitize($_GET['product_id']) : 0;

// Get product details
$product_sql = "SELECT * FROM products WHERE id = $product_id";
$product_result = $conn->query($product_sql);
$product = $product_result->fetch_assoc();

if (!$product) {
    echo "<p>Product not found.</p>";
    require_once 'footer.php';
    exit();
}

// Handle review submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit_review'])) {
    if (!isset($_SESSION['user_id'])) {
        $error = "Please login to submit a review";
    } else {
        $user_id = $_SESSION['user_id'];
        $rating = sanitize($_POST['rating']);
        $comment = sanitize($_POST['comment']);
        
        // Check if user has already reviewed this product
        $check_sql = "SELECT id FROM reviews WHERE user_id = $user_id AND product_id = $product_id";
        $check_result = $conn->query($check_sql);
        
        if ($check_result->num_rows > 0) {
            $error = "You have already reviewed this product";
        } else {
            $insert_sql = "INSERT INTO reviews (user_id, product_id, rating, comment) 
                          VALUES ($user_id, $product_id, $rating, '$comment')";
            if ($conn->query($insert_sql)) {
                $success = "Review submitted successfully!";
            } else {
                $error = "Error submitting review: " . $conn->error;
            }
        }
    }
}

// Get reviews for this product
$reviews_sql = "SELECT r.*, u.full_name FROM reviews r 
                JOIN users u ON r.user_id = u.id 
                WHERE r.product_id = $product_id 
                ORDER BY r.created_at DESC";
$reviews_result = $conn->query($reviews_sql);

// Calculate average rating
$avg_rating_sql = "SELECT AVG(rating) as avg_rating FROM reviews WHERE product_id = $product_id";
$avg_result = $conn->query($avg_rating_sql);
$avg_rating = $avg_result->fetch_assoc()['avg_rating'];
$avg_rating = round($avg_rating, 1);
?>

<h1>Reviews for <?php echo $product['name']; ?></h1>

<!-- Product Info -->
<div style="background: #f8f9fa; padding: 20px; border-radius: 5px; margin-bottom: 20px;">
    <h3><?php echo $product['name']; ?></h3>
    <p><?php echo $product['description']; ?></p>
    <p><strong>Price: Tsh <?php echo number_format($product['price']); ?></strong></p>
    
    <?php if ($avg_rating): ?>
    <div style="margin-top: 10px;">
        <h4>Average Rating: <?php echo $avg_rating; ?> / 5</h4>
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
            <span style="color: #333; font-size: 14px; margin-left: 10px;">(<?php echo $reviews_result->num_rows; ?> reviews)</span>
        </div>
    </div>
    <?php endif; ?>
</div>

<!-- Submit Review Form -->
<?php if(isset($_SESSION['user_id'])): ?>
<div style="background: #fff; padding: 20px; border: 1px solid #ddd; border-radius: 5px; margin-bottom: 30px;">
    <h3>Submit Your Review</h3>
    
    <?php 
    if (isset($error)) echo showMessage('error', $error);
    if (isset($success)) echo showMessage('success', $success);
    ?>
    
    <form method="POST" action="">
        <div class="form-group">
            <label>Rating *</label>
            <div style="font-size: 24px; color: #ddd; cursor: pointer;" id="rating-stars">
                <span data-value="1">☆</span>
                <span data-value="2">☆</span>
                <span data-value="3">☆</span>
                <span data-value="4">☆</span>
                <span data-value="5">☆</span>
            </div>
            <input type="hidden" name="rating" id="rating-value" value="5" required>
        </div>
        
        <div class="form-group">
            <label>Your Comment *</label>
            <textarea name="comment" class="form-control" rows="3" required 
                      placeholder="Share your experience with this product..."></textarea>
        </div>
        
        <button type="submit" name="submit_review" class="btn">Submit Review</button>
    </form>
</div>

<script>
// Star rating system
const stars = document.querySelectorAll('#rating-stars span');
const ratingValue = document.getElementById('rating-value');

stars.forEach(star => {
    star.addEventListener('mouseover', function() {
        const value = this.getAttribute('data-value');
        highlightStars(value);
    });
    
    star.addEventListener('click', function() {
        const value = this.getAttribute('data-value');
        ratingValue.value = value;
        highlightStars(value);
    });
});

function highlightStars(value) {
    stars.forEach(star => {
        const starValue = star.getAttribute('data-value');
        if (starValue <= value) {
            star.innerHTML = '★';
            star.style.color = '#f39c12';
        } else {
            star.innerHTML = '☆';
            star.style.color = '#ddd';
        }
    });
}

// Initialize with default rating
highlightStars(ratingValue.value);
</script>
<?php else: ?>
<div style="background: #f8f9fa; padding: 15px; border-radius: 5px; margin-bottom: 20px;">
    <p><a href="login.php">Login</a> to submit a review for this product.</p>
</div>
<?php endif; ?>

<!-- Reviews List -->
<h3>Customer Reviews (<?php echo $reviews_result->num_rows; ?>)</h3>

<?php if ($reviews_result->num_rows > 0): ?>
    <?php while($review = $reviews_result->fetch_assoc()): ?>
    <div style="border-bottom: 1px solid #eee; padding: 15px 0; margin-bottom: 15px;">
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <div>
                <strong><?php echo $review['full_name']; ?></strong>
                <div style="color: #f39c12;">
                    <?php for($i = 1; $i <= 5; $i++): ?>
                        <?php if($i <= $review['rating']): ?>
                            ★
                        <?php else: ?>
                            ☆
                        <?php endif; ?>
                    <?php endfor; ?>
                    <span style="color: #666; font-size: 12px; margin-left: 10px;">
                        <?php echo date('d M Y', strtotime($review['created_at'])); ?>
                    </span>
                </div>
            </div>
        </div>
        <p style="margin-top: 10px;"><?php echo nl2br(htmlspecialchars($review['comment'])); ?></p>
    </div>
    <?php endwhile; ?>
<?php else: ?>
    <p>No reviews yet. Be the first to review this product!</p>
<?php endif; ?>

<!-- Back to Product -->
<div style="margin-top: 30px;">
    <a href="products.php" class="btn">Back to Products</a>
</div>

<?php require_once 'footer.php'; ?>