<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: userlogin.html");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id = $_SESSION['user_id'];
    $transportType = $_POST['transportType'];
    $subject = $_POST['subject'];
    $issueType = $_POST['issueType'];
    $ticketNumber = $_POST['ticketNumber'] ?? '';
    $incidentDate = $_POST['incidentDate'];
    $description = $_POST['description'];
    
    try {
        // Handle file upload if present
        $evidencePath = '';
        if (isset($_FILES['evidence']) && $_FILES['evidence']['error'] == UPLOAD_ERR_OK) {
            $uploadDir = 'uploads/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            
            $fileName = uniqid() . '_' . basename($_FILES['evidence']['name']);
            $targetPath = $uploadDir . $fileName;
            
            if (move_uploaded_file($_FILES['evidence']['tmp_name'], $targetPath)) {
                $evidencePath = $targetPath;
            }
        }
        
        // Insert ticket into database
        $stmt = $conn->prepare("INSERT INTO tickets (user_id, transport_type, subject, issue_type, ticket_number, incident_date, description, evidence_path, status) 
                               VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'Open')");
        $stmt->execute([$user_id, $transportType, $subject, $issueType, $ticketNumber, $incidentDate, $description, $evidencePath]);
        
        header("Location: user_dashboard.php?section=my-tickets&success=Ticket submitted successfully");
        exit();
        
    } catch (PDOException $e) {
        error_log("Database error: " . $e->getMessage());
        header("Location: user_dashboard.php?section=new-ticket&error=Error submitting ticket");
        exit();
    }
} else {
    header("Location: user_dashboard.php");
    exit();
}
?>