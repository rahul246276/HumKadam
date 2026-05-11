<?php
/**
 * Common Functions
 * HumKadam Matrimonial Contact Management System
 */

/**
 * Redirect with message
 * @param string $url
 * @param string $message
 * @param string $type (success|error)
 */
function redirect($url, $message, $type = 'success') {
    $separator = $type === 'success' ? '?' : '&error=';
    header("Location: $url$separator" . urlencode($message));
    exit();
}

/**
 * Clean input for database
 * @param mixed $input
 * @return string
 */
function cleanInput($input) {
    return trim(htmlspecialchars($input, ENT_QUOTES, 'UTF-8'));
}

/**
 * Validate email format
 * @param string $email
 * @return boolean
 */
function isValidEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Validate phone number
 * @param string $phone
 * @return boolean
 */
function isValidPhone($phone) {
    // Remove all non-digit characters
    $digits = preg_replace('/[^0-9]/', '', $phone);
    return strlen($digits) >= 10 && strlen($digits) <= 15;
}

/**
 * Format date for display
 * @param string $date
 * @return string
 */
function formatDate($date) {
    return date('M j, Y g:i A', strtotime($date));
}

/**
 * Get status badge class
 * @param string $status
 * @return string
 */
function getStatusClass($status) {
    switch ($status) {
        case 'new':
            return 'badge-new';
        case 'read':
            return 'badge-read';
        case 'responded':
            return 'badge-responded';
        default:
            return 'badge-read';
    }
}

/**
 * Log activity
 * @param string $action
 * @param string $details
 * @param int $admin_id
 */
function logActivity($action, $details = '', $admin_id = null) {
    global $conn;
    
    $stmt = $conn->prepare("INSERT INTO audit_log (admin_id, action, table_name, record_id, old_values, new_values, ip_address, user_agent) VALUES (?, ?, 'contact_submissions', NULL, ?, ?, ?, ?)");
    $stmt->bind_param("isssss", 
        $admin_id ?? $_SESSION['admin_id'] ?? 1,
        $action,
        $details,
        $_SERVER['REMOTE_ADDR'],
        $_SERVER['HTTP_USER_AGENT'] ?? ''
    );
    $stmt->execute();
    $stmt->close();
}

/**
 * Check if admin is logged in
 * @return boolean
 */
function isAdminLoggedIn() {
    return isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
}

/**
 * Get current admin user
 * @return array|null
 */
function getCurrentAdmin() {
    if (!isAdminLoggedIn()) {
        return null;
    }
    
    global $conn;
    $admin_id = $_SESSION['admin_id'] ?? 1;
    
    $stmt = $conn->prepare("SELECT id, username, email, last_login FROM admin_users WHERE id = ? AND is_active = 1");
    $stmt->bind_param("i", $admin_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $admin = $result->fetch_assoc();
    $stmt->close();
    
    return $admin;
}

/**
 * Generate pagination
 * @param int $total
 * @param int $current_page
 * @param int $per_page
 * @return array
 */
function getPagination($total, $current_page, $per_page = 20) {
    $total_pages = ceil($total / $per_page);
    $current_page = max(1, min($current_page, $total_pages));
    
    return [
        'total' => $total,
        'per_page' => $per_page,
        'current_page' => $current_page,
        'total_pages' => $total_pages,
        'offset' => ($current_page - 1) * $per_page,
        'prev_page' => $current_page > 1 ? $current_page - 1 : null,
        'next_page' => $current_page < $total_pages ? $current_page + 1 : null
    ];
}

/**
 * Get pagination HTML
 * @param array $pagination
 * @return string
 */
function getPaginationHTML($pagination, $base_url = '') {
    if ($pagination['total_pages'] <= 1) {
        return '';
    }
    
    $html = '<div class="pagination">';
    
    // Previous button
    if ($pagination['prev_page']) {
        $html .= '<a href="' . $base_url . '?page=' . $pagination['prev_page'] . '" class="page-btn">&laquo; Previous</a>';
    }
    
    // Page numbers
    $start = max(1, $pagination['current_page'] - 2);
    $end = min($pagination['total_pages'], $pagination['current_page'] + 2);
    
    for ($i = $start; $i <= $end; $i++) {
        $active = $i == $pagination['current_page'] ? ' active' : '';
        $html .= '<a href="' . $base_url . '?page=' . $i . '" class="page-btn' . $active . '">' . $i . '</a>';
    }
    
    // Next button
    if ($pagination['next_page']) {
        $html .= '<a href="' . $base_url . '?page=' . $pagination['next_page'] . '" class="page-btn">Next &raquo;</a>';
    }
    
    $html .= '</div>';
    return $html;
}
?>
