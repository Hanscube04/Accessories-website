<?php
require_once 'database.php';
require_once 'header.php';

// Get search and filter parameters
$search = isset($_GET['search']) ? sanitize($_GET['search']) : '';
$category = isset($_GET['category']) ? sanitize($_GET['category']) : '';

// Build SQL query
$sql = "SELECT * FROM products WHERE 1=1";
if ($search) {
    $sql .= " AND (name LIKE '%$search%' OR description LIKE '%$search%')";
}
if ($category) {
    $sql .= " AND category = '$category'";
}
$sql .= " ORDER BY name";

$result = $conn->query($sql);
?>

<h1>Our Products</h1>

<!-- Search and Filter Form -->
<form method="GET" action="" style="margin-bottom: 20px;">
    <input type="text" name="search" placeholder="Search products..." 
           value="<?php echo $search; ?>" class="form-control" style="width: 30%; display: inline-block;">
    
    <select name="category" class="form-control" style="width: 20%; display: inline-block;">
        <option value="">All Categories</option>
        <option value="laptop" <?php if($category=='laptop') echo 'selected'; ?>>Laptops</option>
        <option value="phone" <?php if($category=='phone') echo 'selected'; ?>>Phones</option>
        <option value="desktop" <?php if($category=='desktop') echo 'selected'; ?>>Desktops</option>
        <option value="accessory" <?php if($category=='accessory') echo 'selected'; ?>>Accessories</option>
    </select>
    
    <button type="submit" class="btn">Filter</button>
    <a href="products.php" class="btn">Clear</a>
</form>

<div class="product-grid">
    <?php
    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            echo "<div class='product-card'>";
            echo "<h3>" . $row['name'] . "</h3>";
            echo "<p>" . substr($row['description'], 0, 100) . "...</p>";
            echo "<p class='price'>Tsh " . number_format($row['price']) . "</p>";
            echo "<p><strong>Stock: " . $row['stock_quantity'] . "</strong></p>";
            
            if(isset($_SESSION['user_id'])) {
                echo "<form method='POST' action='add_to_cart.php' style='margin-top: 10px;'>";
                echo "<input type='hidden' name='product_id' value='" . $row['id'] . "'>";
                echo "<input type='number' name='quantity' value='1' min='1' max='" . $row['stock_quantity'] . "' style='width: 60px; padding: 5px;'>";
                echo "<button type='submit' class='btn' style='margin-left: 10px;'>Add to Cart</button>";
                echo "</form>";
            } else {
                echo "<p><a href='login.php'>Login to purchase</a></p>";
            }
            
            echo "</div>";
        }
    } else {
        echo "<p>No products found.</p>";
    }
    ?>
</div>

<?php require_once 'footer.php'; ?>