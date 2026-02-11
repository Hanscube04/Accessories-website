<?php
// header.php - Common header for all pages
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Machemba Accessories</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Arial, sans-serif;
        }
        
        body {
            background-color: #f4f4f4;
        }
        
        .header {
            background-color: #2c3e50;
            color: white;
            padding: 15px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .logo {
            font-size: 24px;
            font-weight: bold;
        }
        
        .logo a {
            color: white;
            text-decoration: none;
        }
        
        .nav-links {
            display: flex;
            gap: 15px;
        }
        
        .nav-links a {
            color: white;
            text-decoration: none;
            padding: 8px 15px;
            border-radius: 4px;
            transition: background 0.3s;
        }
        
        .nav-links a:hover {
            background-color: #3498db;
        }
        
        .container {
            max-width: 1200px;
            margin: 20px auto;
            padding: 20px;
            background-color: white;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        
        .alert {
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 4px;
        }
        
        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .btn {
            background-color: #3498db;
            color: white;
            padding: 8px 15px;
            border: none;
            border-radius: 4px;
            text-decoration: none;
            display: inline-block;
            cursor: pointer;
        }
        
        .btn:hover {
            background-color: #2980b9;
        }
        
        .form-group {
            margin-bottom: 15px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        
        .form-control {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        
        .product-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }
        
        .product-card {
            border: 1px solid #ddd;
            border-radius: 5px;
            padding: 15px;
            text-align: center;
        }
        
        .product-card img {
            max-width: 100%;
            height: 150px;
            object-fit: cover;
            margin-bottom: 10px;
        }
        
        .price {
            color: #e74c3c;
            font-weight: bold;
            font-size: 18px;
            margin: 10px 0;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        
        table th, table td {
            padding: 10px;
            border: 1px solid #ddd;
            text-align: left;
        }
        
        table th {
            background-color: #f8f9fa;
        }
        
        .footer {
            text-align: center;
            padding: 20px;
            margin-top: 20px;
            background-color: #2c3e50;
            color: white;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="logo">
            <a href="index.php">MACHEMBA ACCESSORIES</a>
        </div>
        <div class="nav-links">
            <a href="index.php">Home</a>
            <a href="products.php">Products</a>
            
            <?php if(isset($_SESSION['user_id'])): ?>
                <a href="cart.php">Cart</a>
                <a href="orders.php">My Orders</a>
                <a href="logout.php">Logout (<?php echo $_SESSION['username']; ?>)</a>
            <?php else: ?>
                <a href="login.php">Login</a>
                <a href="register.php">Register</a>
            <?php endif; ?>
        </div>
    </div>
    
    <div class="container">