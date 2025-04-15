<?php
session_start();
require_once 'config.php'; // Database configuration

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    // Validate inputs
    if (empty($username) || empty($password)) {
        header("Location: userlogin.html?error=Please fill in all fields");
        exit();
    }

    try {
        // Prepare SQL statement to prevent SQL injection
        $stmt = $conn->prepare("SELECT id, username, password, role FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            // Login successful - set session variables
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['logged_in'] = true;

            // Redirect based on user role
            switch ($user['role']) {
                case 'admin':
                    header("Location: admin_dashboard.php");
                    break;
                case 'agent':
                    header("Location: agent_dashboard.php");
                    break;
                default:
                    header("Location: user_dashboard.php");
            }
            exit();
        } else {
            // Invalid credentials
            header("Location: userlogin.html?error=Invalid username or password");
            exit();
        }
    } catch (PDOException $e) {
        // Log the error (in a real application)
        error_log("Database error: " . $e->getMessage());
        header("Location: userlogin.html?error=Database error. Please try again later.");
        exit();
    }
} else {
    // Not a POST request
    header("Location: userlogin.html");
    exit();
}
?>