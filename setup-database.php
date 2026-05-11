<?php
// Database configuration
$host = 'localhost';
$username = 'root';
$password = '';
$database = 'humkadam_contacts';

// Create connection
$conn = new mysqli($host, $username, $password, '');

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Create database
$createDatabase = "CREATE DATABASE IF NOT EXISTS $database";
if (!$conn->query($createDatabase)) {
    echo "Error creating database: " . $conn->error;
} else {
    echo "Database '$database' created successfully or already exists.<br>";
}

// Select the database
$conn->select_db($database);

// Create table
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

if ($conn->query($createTable)) {
    echo "Table 'contact_submissions' created successfully.<br>";
} else {
    echo "Error creating table: " . $conn->error;
}

// Insert sample data (optional - for testing)
$insertSample = "INSERT INTO contact_submissions (name, email, phone, message, service_type, ip_address) VALUES (?, ?, ?, ?, ?, ?)";
$stmt = $conn->prepare($insertSample);
$sampleName = "Test User";
$sampleEmail = "test@humkadam.com";
$samplePhone = "+91 9876543210";
$sampleMessage = "This is a test message to verify the contact form is working properly.";
$sampleService = "general";
$sampleIP = "127.0.0.1";

$stmt->bind_param("sssss", $sampleName, $sampleEmail, $samplePhone, $sampleMessage, $sampleService, $sampleIP);

if ($stmt->execute()) {
    echo "Sample data inserted successfully.<br>";
} else {
    echo "Error inserting sample data: " . $stmt->error;
}

$stmt->close();
$conn->close();

echo "<br><strong>Setup Complete!</strong><br>";
echo "Database and table created successfully.<br>";
echo "You can now:";
echo "<ul>";
echo "<li>Use the contact form at: <a href='contact.html'>contact.html</a></li>";
echo "<li>Access admin panel at: <a href='admin/login.php'>admin/login.php</a></li>";
echo "<li>Login with: admin / humkadam123</li>";
echo "</ul>";
?>
