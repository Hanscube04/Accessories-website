<?php
require_once 'database.php';
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['is_admin'] != 1) {
    header("Location: login.php");
    exit();
}

$product_id = isset($_GET['id']) ? sanitize($_GET['id']) : 0;

// Get product details
$product_sql = "SELECT * FROM products WHERE id = $product_id";
$product_result = $conn->query($product_sql);
$product = $product_result->fetch_assoc();

if (!$product) {
    header("Location: admin.php");
    exit();
}

// Update product
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = sanitize($_POST['name']);
    $description = sanitize($_POST['description']);
    $price = sanitize($_POST['price']);
    $category = sanitize($_POST['category']);
    $stock = sanitize($_POST['stock_quantity']);
    
    $update_sql = "UPDATE products SET 
                  name = '$name',
                  description = '$description',
                  price = $price,
                  category = '$category',
                  stock_quantity = $stock
                  WHERE id = $product_id";
    
    if ($conn->query($update_sql)) {
        $success = "Product updated successfully!";
        // Refresh product data
        $product_result = $conn->query($product_sql);
        $product = $product_result->fetch_assoc();
    } else {
        $error = "Error updating product: " . $conn->error;
    }
}

require_once 'header.php';
?>

<h1>Edit Product</h1>

<?php
if (isset($success)) echo showMessage('success', $success);
if (isset($error)) echo showMessage('error', $error);
?>

<form method="POST" action="" style="max-width: 600px;">
    <div class="form-group">
        <label>Product Name *</label>
        <input type="text" name="name" class="form-control" value="<?php echo $product['name']; ?>" required>
    </div>
    
    <div class="form-group">
        <label>Description *</label>
        <textarea name="description" class="form-control" rows="3" required><?php echo $product['description']; ?></textarea>
    </div>
    
    <div class="form-group">
        <label>Price (Tsh) *</label>
        <input type="number" name="price" class="form-control" step="0.01" value="<?php echo $product['price']; ?>" required>
    </div>
    
    <div class="form-group">
        <label>Category *</label>
        <select name="category" class="form-control" required>
            <option value="laptop" <?php if($product['category']=='laptop') echo 'selected'; ?>>Laptop</option>
            <option value="phone" <?php if($product['category']=='phone') echo 'selected'; ?>>Phone</option>
            <option value="desktop" <?php if($product['category']=='desktop') echo 'selected'; ?>>Desktop</option>
            <option value="accessory" <?php if($product['category']=='accessory') echo 'selected'; ?>>Accessory</option>
        </select>
    </div>
    
    <div class="form-group">
        <label>Stock Quantity *</label>
        <input type="number" name="stock_quantity" class="form-control" value="<?php echo $product['stock_quantity']; ?>" required>
    </div>
    
    <button type="submit" class="btn">Update Product</button>
    <a href="admin.php" class="btn" style="background: #95a5a6;">Cancel</a>
</form>

<?php require_once 'footer.php'; ?>