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

// Set headers for CSV download
header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="humkadam_contacts_' . date('Y-m-d') . '.csv');

// Create file pointer
$output = fopen('php://output', 'w');

// Get contact data
$contacts = $conn->query("SELECT * FROM contact_submissions ORDER BY submission_date DESC");

// CSV headers
$headers = ['ID', 'Name', 'Email', 'Phone', 'Service Type', 'Date', 'Status', 'IP Address'];
fputcsv($output, $headers);

// Write data rows
while ($contact = $contacts->fetch_assoc()) {
    $row = [
        $contact['id'],
        $contact['name'],
        $contact['email'],
        $contact['phone'],
        $contact['service_type'],
        date('M j, Y', strtotime($contact['submission_date'])),
        ucfirst($contact['status']),
        $contact['ip_address']
    ];
    fputcsv($output, $row);
}

// Log export action before closing connection
$admin_id = $_SESSION['admin_id'] ?? 1;
$ip = $_SERVER['REMOTE_ADDR'];
$ua = $_SERVER['HTTP_USER_AGENT'] ?? '';
$stmt = $conn->prepare("INSERT INTO audit_log (admin_id, action, table_name, ip_address, user_agent) VALUES (?, 'EXPORT', 'contact_submissions', ?, ?)");
$stmt->bind_param("iss", $admin_id, $ip, $ua);
$stmt->execute();
$stmt->close();

$conn->close();
fclose($output);
?>