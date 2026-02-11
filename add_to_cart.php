<?php
require_once 'database.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_id = $_SESSION['user_id'];
    $product_id = sanitize($_POST['product_id']);
    $quantity = sanitize($_POST['quantity']);
    
    // Check if product exists in cart
    $check_sql = "SELECT * FROM cart WHERE user_id = $user_id AND product_id = $product_id";
    $check_result = $conn->query($check_sql);
    
    if ($check_result->num_rows > 0) {
        // Update quantity
        $update_sql = "UPDATE cart SET quantity = quantity + $quantity WHERE user_id = $user_id AND product_id = $product_id";
    } else {
        // Insert new item
        $update_sql = "INSERT INTO cart (user_id, product_id, quantity) VALUES ($user_id, $product_id, $quantity)";
    }
    
    if ($conn->query($update_sql) === TRUE) {
        $_SESSION['message'] = "Product added to cart successfully!";
    } else {
        $_SESSION['error'] = "Error: " . $conn->error;
    }
    
    header("Location: cart.php");
    exit();
}

header("Location: products.php");
exit();
?>