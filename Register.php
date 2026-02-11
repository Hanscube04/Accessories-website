<?php
require_once 'database.php';

$error = '';
$success = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $fullname = sanitize($_POST['fullname']);
    $email = sanitize($_POST['email']);
    $phone = sanitize($_POST['phone']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Validation
    if (empty($fullname) || empty($email) || empty($password)) {
        $error = "All fields are required";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format";
    } elseif ($password !== $confirm_password) {
        $error = "Passwords do not match";
    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters";
    } else {
        // Check if email exists
        $check_email = $conn->query("SELECT id FROM users WHERE email = '$email'");
        if ($check_email->num_rows > 0) {
            $error = "Email already registered";
        } else {
            // Hash password and insert user
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $sql = "INSERT INTO users (full_name, email, phone, password) 
                    VALUES ('$fullname', '$email', '$phone', '$hashed_password')";
            
            if ($conn->query($sql) === TRUE) {
                $success = "Registration successful! You can now login.";
                header("refresh:2;url=login.php");
            } else {
                $error = "Error: " . $conn->error;
            }
        }
    }
}

require_once 'header.php';
?>

<h2>Register Account</h2>

<?php
if ($error) echo showMessage('error', $error);
if ($success) echo showMessage('success', $success);
?>

<form method="POST" action="" onsubmit="return validateForm()">
    <div class="form-group">
        <label>Full Name *</label>
        <input type="text" name="fullname" class="form-control" required>
    </div>
    
    <div class="form-group">
        <label>Email *</label>
        <input type="email" name="email" class="form-control" required>
    </div>
    
    <div class="form-group">
        <label>Phone Number *</label>
        <input type="tel" name="phone" class="form-control" required>
    </div>
    
    <div class="form-group">
        <label>Password *</label>
        <input type="password" name="password" class="form-control" required>
        <small>At least 6 characters</small>
    </div>
    
    <div class="form-group">
        <label>Confirm Password *</label>
        <input type="password" name="confirm_password" class="form-control" required>
    </div>
    
    <button type="submit" class="btn">Register</button>
</form>

<p style="margin-top: 15px;">Already have an account? <a href="login.php">Login here</a></p>

<?php require_once 'footer.php'; ?>