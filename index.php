<?php
require_once 'database.php';
require_once 'header.php';
?>

<h1>Welcome to Machemba Accessories</h1>
<p>Your one-stop shop for quality electronics and accessories</p>

<div class="features" style="display: flex; justify-content: space-around; margin: 30px 0;">
    <div style="text-align: center;">
        <h3>Free Shipping</h3>
        <p>On orders over Tsh 200,000</p>
    </div>
    <div style="text-align: center;">
        <h3>Quality Products</h3>
        <p>Guaranteed quality</p>
    </div>
    <div style="text-align: center;">
        <h3>24/7 Support</h3>
        <p>Always here to help</p>
    </div>
</div>

<h2>Featured Products</h2>
<div class="product-grid">
    <?php
    // Get featured products from database
    $sql = "SELECT * FROM products LIMIT 4";
    $result = $conn->query($sql);
    
    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            echo "<div class='product-card'>";
            echo "<h3>" . $row['name'] . "</h3>";
            echo "<p>" . substr($row['description'], 0, 50) . "...</p>";
            echo "<p class='price'>Tsh " . number_format($row['price']) . "</p>";
            echo "<a href='product_detail.php?id=" . $row['id'] . "' class='btn'>View Details</a>";
            echo "</div>";
        }
    } else {
        echo "<p>No products available yet.</p>";
    }
    ?>
</div>

<div style="text-align: center; margin: 30px 0;">
    <a href="products.php" class="btn">View All Products</a>
</div>

<?php require_once 'footer.php'; ?>