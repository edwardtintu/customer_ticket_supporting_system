<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("HTTP/1.1 401 Unauthorized");
    exit();
}

if (!isset($_GET['id'])) {
    header("HTTP/1.1 400 Bad Request");
    exit();
}

$ticket_id = $_GET['id'];
$user_id = $_SESSION['user_id'];

try {
    // Verify the ticket belongs to the user
    $stmt = $conn->prepare("SELECT id FROM tickets WHERE id = ? AND user_id = ?");
    $stmt->execute([$ticket_id, $user_id]);
    
    if (!$stmt->fetch()) {
        header("HTTP/1.1 404 Not Found");
        exit();
    }
    
    // Get replies
    $stmt = $conn->prepare("SELECT r.*, u.username, u.role as user_role FROM replies r 
                           JOIN users u ON r.user_id = u.id 
                           WHERE r.ticket_id = ? 
                           ORDER BY r.created_at ASC");
    $stmt->execute([$ticket_id]);
    $replies = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    header('Content-Type: application/json');
    echo json_encode($replies);
    
} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    header("HTTP/1.1 500 Internal Server Error");
    exit();
}
?>