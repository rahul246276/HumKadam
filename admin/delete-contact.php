<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit();
}

// Database connection
require_once __DIR__ . '/../config/database.php';
$conn = getDBConnection();

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get contact ID and delete
$ajax = isset($_GET['ajax']);

if (isset($_GET['id'])) {
    $contact_id = (int)$_GET['id'];
    
    $stmt = $conn->prepare("DELETE FROM contact_submissions WHERE id = ?");
    $stmt->bind_param("i", $contact_id);
    
    if ($stmt->execute()) {
        if (!$ajax) header('Location: index.php?message=Contact+deleted+successfully');
    } else {
        if (!$ajax) header('Location: index.php?error=Failed+to+delete+contact');
    }
    $stmt->close();
} else {
    if (!$ajax) header('Location: index.php?error=No+contact+ID+provided');
}

$conn->close();
if ($ajax) { http_response_code(200); echo 'ok'; }
exit();