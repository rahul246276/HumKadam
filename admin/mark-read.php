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

$ajax = isset($_GET['ajax']);

if (isset($_GET['id'])) {
    $contact_id = (int)$_GET['id'];
    // Accept 'read' or 'responded'; default to 'read'
    $allowed = ['read', 'responded'];
    $new_status = in_array($_GET['status'] ?? '', $allowed) ? $_GET['status'] : 'read';

    $stmt = $conn->prepare("UPDATE contact_submissions SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $new_status, $contact_id);

    if ($stmt->execute()) {
        if (!$ajax) header('Location: index.php?message=Status+updated+to+' . $new_status);
    } else {
        if (!$ajax) header('Location: index.php?error=Failed+to+update+contact');
    }
    $stmt->close();
} else {
    if (!$ajax) header('Location: index.php?error=No+contact+ID+provided');
}

$conn->close();
if ($ajax) { http_response_code(200); echo 'ok'; }
exit();