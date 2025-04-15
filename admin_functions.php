<?php
require_once 'config.php';

/**
 * Get dashboard statistics
 */
function getDashboardStatistics() {
    global $pdo;
    
    $stats = [
        'total_customers' => 0,
        'total_agents' => 0,
        'total_tickets' => 0,
        'resolution_rate' => '0%'
    ];
    
    try {
        // Get total customers
        $stmt = $pdo->query("SELECT COUNT(*) FROM users WHERE user_type = 'customer'");
        $stats['total_customers'] = $stmt->fetchColumn();
        
        // Get total agents
        $stmt = $pdo->query("SELECT COUNT(*) FROM users WHERE user_type = 'agent'");
        $stats['total_agents'] = $stmt->fetchColumn();
        
        // Get total tickets
        $stmt = $pdo->query("SELECT COUNT(*) FROM tickets");
        $stats['total_tickets'] = $stmt->fetchColumn();
        
        // Get resolution rate
        $stmt = $pdo->query("SELECT 
            ROUND((SUM(CASE WHEN status = 'resolved' THEN 1 ELSE 0 END) / COUNT(*)) * 100, 0) 
            AS resolution_rate FROM tickets");
        $rate = $stmt->fetchColumn();
        $stats['resolution_rate'] = $rate ? $rate.'%' : '0%';
        
    } catch(PDOException $e) {
        error_log("Error getting dashboard stats: " . $e->getMessage());
    }
    
    return $stats;
}

/**
 * Get all customers
 */
function getAllCustomers($search = '') {
    global $pdo;
    
    $sql = "SELECT * FROM users WHERE user_type = 'customer'";
    
    if (!empty($search)) {
        $sql .= " AND (username LIKE :search OR email LIKE :search)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['search' => "%$search%"]);
    } else {
        $stmt = $pdo->query($sql);
    }
    
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Get all agents
 */
function getAllAgents($search = '') {
    global $pdo;
    
    $sql = "SELECT * FROM users WHERE user_type = 'agent'";
    
    if (!empty($search)) {
        $sql .= " AND (username LIKE :search OR email LIKE :search)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['search' => "%$search%"]);
    } else {
        $stmt = $pdo->query($sql);
    }
    
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Get all tickets with optional status filter
 */
function getAllTickets($status = null, $search = '') {
    global $pdo;
    
    $sql = "SELECT t.*, u1.username AS customer_name, u2.username AS agent_name 
            FROM tickets t
            JOIN users u1 ON t.customer_id = u1.id
            LEFT JOIN users u2 ON t.agent_id = u2.id";
    
    $params = [];
    $where = [];
    
    if ($status && $status !== 'all') {
        $where[] = "t.status = :status";
        $params['status'] = $status;
    }
    
    if (!empty($search)) {
        $where[] = "(t.title LIKE :search OR t.description LIKE :search)";
        $params['search'] = "%$search%";
    }
    
    if (!empty($where)) {
        $sql .= " WHERE " . implode(" AND ", $where);
    }
    
    $sql .= " ORDER BY t.created_at DESC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Add new user (customer or agent)
 */
function addNewUser($username, $email, $password, $userType) {
    global $pdo;
    
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("INSERT INTO users (username, email, password, user_type) 
                          VALUES (?, ?, ?, ?)");
    return $stmt->execute([$username, $email, $hashedPassword, $userType]);
}

/**
 * Delete user
 */
function deleteUser($userId) {
    global $pdo;
    $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
    return $stmt->execute([$userId]);
}

/**
 * Update system settings
 */
function updateSystemSettings($systemName, $supportEmail) {
    // In a real application, you would store these in a settings table
    // This is a simplified example
    return true;
}

/**
 * Change admin password
 */
function changeAdminPassword($adminId, $currentPassword, $newPassword) {
    global $pdo;
    
    // Verify current password
    $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
    $stmt->execute([$adminId]);
    $admin = $stmt->fetch();
    
    if (!$admin || !password_verify($currentPassword, $admin['password'])) {
        return false;
    }
    
    // Update password
    $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
    return $stmt->execute([$hashedPassword, $adminId]);
}

/**
 * Get ticket by ID
 */
function getTicketById($ticketId) {
    global $pdo;
    
    $stmt = $pdo->prepare("SELECT t.*, u1.username AS customer_name, u2.username AS agent_name 
                          FROM tickets t
                          JOIN users u1 ON t.customer_id = u1.id
                          LEFT JOIN users u2 ON t.agent_id = u2.id
                          WHERE t.id = ?");
    $stmt->execute([$ticketId]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

/**
 * Update ticket status
 */
function updateTicketStatus($ticketId, $status, $agentId = null) {
    global $pdo;
    
    $stmt = $pdo->prepare("UPDATE tickets SET status = ?, agent_id = ? WHERE id = ?");
    return $stmt->execute([$status, $agentId, $ticketId]);
}

/**
 * Add reply to ticket
 */
function addTicketReply($ticketId, $userId, $message) {
    global $pdo;
    
    $stmt = $pdo->prepare("INSERT INTO ticket_replies (ticket_id, user_id, message) 
                          VALUES (?, ?, ?)");
    return $stmt->execute([$ticketId, $userId, $message]);
}

/**
 * Get ticket replies
 */
function getTicketReplies($ticketId) {
    global $pdo;
    
    $stmt = $pdo->prepare("SELECT tr.*, u.username, u.user_type 
                          FROM ticket_replies tr
                          JOIN users u ON tr.user_id = u.id
                          WHERE ticket_id = ?
                          ORDER BY created_at ASC");
    $stmt->execute([$ticketId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>