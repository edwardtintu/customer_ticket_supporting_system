<?php
$host = 'localhost';
$dbname = 'support_ticket';
$username = 'root'; // Change as per your MySQL credentials
$password = ''; // Change as per your MySQL credentials

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
?>