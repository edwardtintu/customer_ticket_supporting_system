<?php
session_start();
require_once 'config.php'; // Database configuration

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    // Validate inputs
    if (empty($username) || empty($password)) {
        header("Location: adminlogin.html?error=Please fill in all fields");
        exit();
    }

    try {
        // Prepare SQL statement to prevent SQL injection
        $stmt = $conn->prepare("SELECT id, username, password, role FROM users WHERE username = ? AND role = 'admin'");
        $stmt->execute([$username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            // Login successful - set session variables
            $_SESSION['admin_id'] = $user['id'];
            $_SESSION['admin_username'] = $user['username'];
            $_SESSION['admin_role'] = $user['role'];
            $_SESSION['admin_logged_in'] = true;

            // Redirect to admin dashboard
            header("Location: admin_dashboard.php");
            exit();
        } else {
            // Invalid credentials
            header("Location: adminlogin.html?error=Invalid username or password");
            exit();
        }
    } catch (PDOException $e) {
        // Log the error (in a real application)
        error_log("Database error: " . $e->getMessage());
        header("Location: adminlogin.html?error=Database error. Please try again later.");
        exit();
    }
} else {
    // Not a POST request
    header("Location: adminlogin.html");
    exit();
}
?>