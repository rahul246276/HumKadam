<?php
require_once __DIR__ . '/config/database.php';

// Create connection
$conn = getDBConnection();

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Create table if not exists
$createTable = "CREATE TABLE IF NOT EXISTS contact_submissions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    message TEXT NOT NULL,
    service_type VARCHAR(100),
    submission_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status ENUM('new', 'read', 'responded') DEFAULT 'new',
    ip_address VARCHAR(45)
)";

if (!$conn->query($createTable)) {
    echo "Error creating table: " . $conn->error;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Set content type header
    header('Content-Type: application/json');
    
    // Sanitize and validate inputs
    $name = htmlspecialchars(trim($_POST['name'] ?? ''));
    $email = filter_var(trim($_POST['email'] ?? ''), FILTER_SANITIZE_EMAIL);
    $phone = htmlspecialchars(trim($_POST['phone'] ?? ''));
    $message = htmlspecialchars(trim($_POST['message'] ?? ''));
    $service_type = htmlspecialchars(trim($_POST['service_type'] ?? ''));
    $ip_address = $_SERVER['REMOTE_ADDR'];
    
    // Validate required fields
    $errors = [];
    if (empty($name)) $errors[] = "Name is required";
    if (empty($email)) $errors[] = "Email is required";
    if (empty($phone)) $errors[] = "Phone is required";
    if (empty($message)) $errors[] = "Message is required";
    
    // Additional validation
    if (strlen($name) < 2) $errors[] = "Name must be at least 2 characters";
    if (strlen($phone) < 10) $errors[] = "Phone number must be at least 10 digits";
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format";
    }
    
    
    if (empty($errors)) {
        // Prepare and bind statement
        $stmt = $conn->prepare("INSERT INTO contact_submissions (name, email, phone, message, service_type, ip_address) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssss", $name, $email, $phone, $message, $service_type, $ip_address);
        
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Contact form submitted successfully!']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error submitting form: ' . $stmt->error]);
        }
        $stmt->close();
    } else {
        echo json_encode(['success' => false, 'message' => 'Please fix the errors: ' . implode(', ', $errors)]);
    }
}

$conn->close();
?>