<?php
session_start();
require_once 'config.php'; // Database configuration

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    // Validate inputs
    if (empty($username) || empty($password)) {
        header("Location: agentlogin.html?error=Please fill in all fields");
        exit();
    }

    try {
        // Prepare SQL statement to prevent SQL injection
        $stmt = $conn->prepare("SELECT id, username, password, role FROM users WHERE username = ? AND role = 'agent'");
        $stmt->execute([$username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            // Login successful - set session variables
            $_SESSION['agent_id'] = $user['id'];
            $_SESSION['agent_username'] = $user['username'];
            $_SESSION['agent_role'] = $user['role'];
            $_SESSION['agent_logged_in'] = true;

            // Redirect to agent dashboard
            header("Location: agent_dashboard.php");
            exit();
        } else {
            // Invalid credentials
            header("Location: agentlogin.html?error=Invalid username or password");
            exit();
        }
    } catch (PDOException $e) {
        // Log the error (in a real application)
        error_log("Database error: " . $e->getMessage());
        header("Location: agentlogin.html?error=Database error. Please try again later.");
        exit();
    }
} else {
    // Not a POST request
    header("Location: agentlogin.html");
    exit();
}
?>