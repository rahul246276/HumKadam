-- HumKadam Contact Management System Database Schema
-- MySQL Database Structure for Contact Form Submissions

-- Create database
CREATE DATABASE IF NOT EXISTS humkadam_contacts;

-- Use the database
USE humkadam_contacts;

-- Create contact submissions table
CREATE TABLE IF NOT EXISTS contact_submissions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL COMMENT 'Full name of the contact person',
    email VARCHAR(255) NOT NULL COMMENT 'Email address of the contact person',
    phone VARCHAR(20) NOT NULL COMMENT 'Phone number with country code',
    message TEXT NOT NULL COMMENT 'Message content from contact form',
    service_type VARCHAR(100) COMMENT 'Type of service requested',
    submission_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'When the contact was submitted',
    status ENUM('new', 'read', 'responded') DEFAULT 'new' COMMENT 'Current status of the contact',
    ip_address VARCHAR(45) COMMENT 'IP address of the submitter',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Record creation timestamp',
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Last update timestamp'
);

-- Add indexes for better performance
CREATE INDEX idx_email ON contact_submissions(email);
CREATE INDEX idx_status ON contact_submissions(status);
CREATE INDEX idx_submission_date ON contact_submissions(submission_date);
CREATE INDEX idx_ip_address ON contact_submissions(ip_address);

-- Insert sample data for testing
INSERT INTO contact_submissions (name, email, phone, message, service_type, ip_address) VALUES 
('Test User', 'test@humkadam.com', '+91 9876543210', 'This is a test message to verify the contact form is working properly.', 'general', '127.0.0.1'),
('Demo Contact', 'demo@humkadam.in', '+91 1122334456', 'Sample inquiry about premium membership services and consultation availability.', 'premium', '192.168.1.1'),
('Sample NRI', 'nri@humkadam.com', '+1 5551234567', 'Interested in NRI matrimony services for overseas clients. Please provide information about your international matchmaking packages.', 'nri', '10.0.0.2');

-- Create admin users table for future expansion
CREATE TABLE IF NOT EXISTS admin_users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE COMMENT 'Admin username',
    password VARCHAR(255) NOT NULL COMMENT 'Hashed password',
    email VARCHAR(255) NOT NULL COMMENT 'Admin email',
    role ENUM('super_admin', 'admin', 'viewer') DEFAULT 'admin' COMMENT 'User role',
    is_active BOOLEAN DEFAULT TRUE COMMENT 'Whether the user account is active',
    last_login TIMESTAMP NULL COMMENT 'Last login timestamp',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Account creation timestamp'
);

-- Insert default admin user (password: humkadam123)
INSERT INTO admin_users (username, password, email, role) VALUES 
('admin', '$2y$10$YvKmQ9wX8qL3pP5vG2W8qL3', 'admin@humkadam.com', 'super_admin');

-- Create audit log table for security
CREATE TABLE IF NOT EXISTS audit_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    admin_id INT COMMENT 'Admin user ID who performed the action',
    action VARCHAR(100) NOT NULL COMMENT 'Action performed',
    table_name VARCHAR(50) COMMENT 'Table that was affected',
    record_id INT COMMENT 'ID of the affected record',
    old_values TEXT COMMENT 'Previous values before update',
    new_values TEXT COMMENT 'New values after update',
    ip_address VARCHAR(45) COMMENT 'IP address of the admin',
    user_agent TEXT COMMENT 'Browser user agent',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'When the action was performed'
);

-- Add indexes for audit log
CREATE INDEX idx_admin_id ON audit_log(admin_id);
CREATE INDEX idx_action ON audit_log(action);
CREATE INDEX idx_created_at ON audit_log(created_at);

-- Create views for common queries
CREATE VIEW contact_summary AS
SELECT 
    COUNT(*) as total_contacts,
    COUNT(CASE WHEN status = 'new' THEN 1 END) as new_contacts,
    COUNT(CASE WHEN status = 'read' THEN 1 END) as read_contacts,
    COUNT(CASE WHEN status = 'responded' THEN 1 END) as responded_contacts,
    DATE(submission_date) as contact_date
FROM contact_submissions;

-- Create stored procedures for common operations
DELIMITER //

-- Procedure to mark contact as read
CREATE PROCEDURE MarkContactAsRead(
    IN contact_id INT,
    IN admin_id INT
)
BEGIN
    UPDATE contact_submissions 
    SET status = 'read', updated_at = CURRENT_TIMESTAMP 
    WHERE id = contact_id;
    
    INSERT INTO audit_log (admin_id, action, table_name, record_id, old_values, new_values, ip_address, user_agent)
    VALUES (admin_id, 'UPDATE_STATUS', 'contact_submissions', contact_id, 'new', 'read', 
            CONNECTION_ID(), USER_AGENT());
END //

-- Procedure to delete contact
CREATE PROCEDURE DeleteContact(
    IN contact_id INT,
    IN admin_id INT
)
BEGIN
    DECLARE old_status VARCHAR(20);
    SELECT status INTO old_status FROM contact_submissions WHERE id = contact_id;
    
    DELETE FROM contact_submissions WHERE id = contact_id;
    
    INSERT INTO audit_log (admin_id, action, table_name, record_id, old_values, new_values, ip_address, user_agent)
    VALUES (admin_id, 'DELETE', 'contact_submissions', contact_id, old_status, 'DELETED', 
            CONNECTION_ID(), USER_AGENT());
END //

DELIMITER ;

-- Add comments for documentation
COMMENT ON TABLE contact_submissions IS 'Stores all contact form submissions from the HumKadam website';
COMMENT ON TABLE admin_users IS 'Stores admin user accounts for accessing the management panel';
COMMENT ON TABLE audit_log IS 'Tracks all admin actions for security and auditing';

-- Set character set for proper Unicode support
ALTER DATABASE humkadam_contacts CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
