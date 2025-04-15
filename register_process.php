<?php
require_once 'config.php'; // Database configuration

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Collect and sanitize input data
    $fullName = trim($_POST['fullName']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $confirmPassword = $_POST['confirmPassword'];

    // Validate inputs
    $errors = [];

    // Check if passwords match
    if ($password !== $confirmPassword) {
        $errors[] = "Passwords do not match";
    }

    // Check password strength (minimum 8 characters)
    if (strlen($password) < 2) {
        $errors[] = "Password must be at least 2 characters long";
    }

    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format";
    }

    // If there are errors, redirect back with error messages
    if (!empty($errors)) {
        header("Location: newuser.html?error=" . urlencode(implode(", ", $errors)));
        exit();
    }

    try {
        // Check if username or email already exists
        $stmt = $conn->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
        $stmt->execute([$username, $email]);
        
        if ($stmt->rowCount() > 0) {
            header("Location: newuser.html?error=Username or email already exists");
            exit();
        }

        // Hash the password
    

        // Insert new user into database
        $stmt = $conn->prepare("INSERT INTO users (full_name, email, phone, username, password, role) 
                               VALUES (?, ?, ?, ?, ?, 'customer')");
        $stmt->execute([$fullName, $email, $phone, $username, $Password]);

        // Registration successful - redirect to login page
        header("Location: userlogin.html?success=Registration successful. Please login.");
        exit();

    } catch (PDOException $e) {
        // Log the error (in a real application)
        error_log("Database error: " . $e->getMessage());
        header("Location: newuser.html?error=Database error. Please try again later.");
        exit();
    }
} else {
    // Not a POST request
    header("Location: newuser.html");
    exit();
}
?>