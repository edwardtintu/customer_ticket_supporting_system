<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['agent_logged_in'])) {
    header("HTTP/1.1 401 Unauthorized");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $ticket_id = $_POST['ticket_id'];
    $new_status = $_POST['status'];
    $agent_id = $_SESSION['agent_id'];

    try {
        // Verify the ticket is assigned to this agent
        $stmt = $conn->prepare("SELECT id FROM tickets WHERE id = ? AND assigned_agent_id = ?");
        $stmt->execute([$ticket_id, $agent_id]);
        
        if ($stmt->rowCount() == 0) {
            echo json_encode(['success' => false, 'message' => 'Ticket not assigned to you']);
            exit();
        }

        // Update ticket status
        $stmt = $conn->prepare("UPDATE tickets SET status = ? WHERE id = ?");
        $stmt->execute([$new_status, $ticket_id]);

        // Log the status change
        $stmt = $conn->prepare("INSERT INTO ticket_activity (ticket_id, agent_id, description) 
                               VALUES (?, ?, ?)");
        $description = "Status changed to " . $new_status;
        $stmt->execute([$ticket_id, $agent_id, $description]);

        echo json_encode(['success' => true]);
        
    } catch (PDOException $e) {
        error_log("Database error: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Database error']);
    }
} else {
    header("HTTP/1.1 400 Bad Request");
    exit();
}
?>