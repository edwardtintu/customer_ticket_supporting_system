<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['agent_logged_in'])) {
    header("Location: agentlogin.html");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $agent_id = $_SESSION['agent_id'];
    $fullName = $_POST['fullName'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    
    try {
        $stmt = $conn->prepare("UPDATE users SET full_name = ?, email = ?, phone = ? WHERE id = ?");
        $stmt->execute([$fullName, $email, $phone, $agent_id]);
        
        header("Location: agent_dashboard.php?section=profile&success=Profile updated successfully");
        exit();
        
    } catch (PDOException $e) {
        error_log("Database error: " . $e->getMessage());
        header("Location: agent_dashboard.php?section=profile&error=Error updating profile");
        exit();
    }
} else {
    header("Location: agent_dashboard.php");
    exit();
}
?>