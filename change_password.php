<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: userlogin.html");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id = $_SESSION['user_id'];
    $currentPassword = $_POST['currentPassword'];
    $newPassword = $_POST['newPassword'];
    $confirmPassword = $_POST['confirmPassword'];
    
    // Validate passwords match
    if ($newPassword !== $confirmPassword) {
        header("Location: user_dashboard.php?section=profile&error=New passwords do not match");
        exit();
    }
    
    try {
        // Verify current password
        $stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        $user = $stmt->fetch();
        
        if (!$user || !password_verify($currentPassword, $user['password'])) {
            header("Location: user_dashboard.php?section=profile&error=Current password is incorrect");
            exit();
        }
        
        // Update password
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
        $stmt->execute([$hashedPassword, $user_id]);
        
        header("Location: user_dashboard.php?section=profile&success=Password changed successfully");
        exit();
        
    } catch (PDOException $e) {
        error_log("Database error: " . $e->getMessage());
        header("Location: user_dashboard.php?section=profile&error=Error changing password");
        exit();
    }
} else {
    header("Location: user_dashboard.php");
    exit();
}
?>