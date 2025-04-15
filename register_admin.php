<?php
require_once 'config.php'; // Database configuration

// Define super admin verification key (should be stored more securely in production)
define('SUPER_ADMIN_KEY', 'secure_admin_key_123'); // Change this to a strong secret key

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Collect and sanitize input data
    $fullName = trim($_POST['fullName']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $confirmPassword = $_POST['confirmPassword'];
    $superAdminKey = $_POST['superAdminKey'];

    // Validate inputs
    $errors = [];

    // Verify super admin key
    if ($superAdminKey !== SUPER_ADMIN_KEY) {
        $errors[] = "Invalid super admin verification key";
    }

    // Check if passwords match
    if ($password !== $confirmPassword) {
        $errors[] = "Passwords do not match";
    }

    // Check password strength (minimum 8 characters)
    if (strlen($password) < 8) {
        $errors[] = "Password must be at least 8 characters long";
    }

    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format";
    }

    // If there are errors, redirect back with error messages
    if (!empty($errors)) {
        header("Location: newadmin.html?error=" . urlencode(implode(", ", $errors)));
        exit();
    }

    try {
        // Check if username or email already exists
        $stmt = $conn->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
        $stmt->execute([$username, $email]);
        
        if ($stmt->rowCount() > 0) {
            header("Location: newadmin.html?error=Username or email already exists");
            exit();
        }

        // Hash the password
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        // Insert new admin into database
        $stmt = $conn->prepare("INSERT INTO users (full_name, email, phone, username, password, role) 
                               VALUES (?, ?, ?, ?, ?, 'admin')");
        $stmt->execute([$fullName, $email, $phone, $username, $hashedPassword]);

        // Registration successful - redirect to login page
        header("Location: adminlogin.html?success=Admin registration successful. Please login.");
        exit();

    } catch (PDOException $e) {
        // Log the error (in a real application)
        error_log("Database error: " . $e->getMessage());
        header("Location: newadmin.html?error=Database error. Please try again later.");
        exit();
    }
} else {
    // Not a POST request
    header("Location: newadmin.html");
    exit();
}
?>