# HumKadam Contact Form Management System

## Overview
A complete PHP and MySQL system to save and manage contact form submissions with admin panel.

## Features Implemented

### 1. Contact Form (contact.html)
- **Frontend**: HTML5 form with validation
- **Backend Integration**: PHP handler (contact-handler.php)
- **AJAX Submission**: Modern fetch API with loading states
- **Responsive Design**: Mobile-friendly layout
- **Form Fields**: Name, Email, Phone, Service Type, Message
- **Real-time Validation**: Client-side and server-side

### 2. PHP Backend (contact-handler.php)
- **Database Connection**: Secure MySQLi connection
- **Input Sanitization**: FILTER_SANITIZE for security
- **SQL Injection Prevention**: Prepared statements
- **Error Handling**: Comprehensive validation and error responses
- **JSON Responses**: AJAX-compatible response format

### 3. MySQL Database
- **Table**: contact_submissions
- **Fields**: id, name, email, phone, message, service_type, submission_date, status, ip_address
- **Auto-creation**: Table created automatically
- **Data Types**: Optimized for performance

### 4. Admin Panel (admin/)
- **Login System**: Secure authentication (admin/humkadam123)
- **Dashboard**: Statistics overview (total, new, read contacts)
- **Contact Management**: View, mark as read, delete submissions
- **Responsive Interface**: Mobile-friendly admin design
- **Session Management**: Secure logout functionality

## File Structure
```
Hamkadam/
├── contact.html              # Contact form page
├── contact-handler.php         # PHP form processor
├── admin/
│   ├── index.php           # Admin dashboard
│   ├── login.php           # Admin login
│   ├── logout.php          # Logout handler
│   ├── mark-read.php        # Mark contact as read
│   └── delete-contact.php  # Delete contact
└── README-Contact-System.md  # This documentation
```

## Database Schema
```sql
CREATE TABLE contact_submissions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    message TEXT NOT NULL,
    service_type VARCHAR(100),
    submission_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status ENUM('new', 'read', 'responded') DEFAULT 'new',
    ip_address VARCHAR(45)
);
```

## Security Features
- **Input Validation**: Server-side sanitization
- **SQL Injection Prevention**: Prepared statements
- **Session Security**: Secure admin authentication
- **IP Logging**: Track submission sources
- **Error Handling**: Prevent information disclosure

## Usage Instructions

### For Users:
1. Fill out the contact form on the website
2. Submit the form - data is saved to database
3. Admin can view and manage submissions

### For Admins:
1. Navigate to `/admin/` directory
2. Login with credentials:
   - Username: `admin`
   - Password: `humkadam123`
3. View contact submissions and manage status
4. Mark contacts as read or delete as needed

## Technical Requirements
- **PHP**: 7.0+ with MySQLi extension
- **MySQL**: 5.6+ or MariaDB 10.0+
- **Web Server**: Apache/Nginx with PHP support
- **Browser**: Modern browser with JavaScript support

## Security Notes
- Change default admin credentials in production
- Implement HTTPS for secure data transmission
- Regular database backups recommended
- Consider implementing CAPTCHA for spam prevention

## Future Enhancements
- Email notification system for new submissions
- Export functionality for contact data
- Advanced filtering and search capabilities
- Multi-user admin roles and permissions
