<?php
/**
 * RBAC Helper Functions
 * Include this file in any page that needs permission checking
 */

// Include RBAC configuration
include 'rbac.php';

/**
 * Check if current user has permission
 * 
 * @param string $permission The permission to check
 * @param string $redirectUrl URL to redirect to if no permission (optional)
 * @return bool True if user has permission
 */
function safeRedirect($url) {
    $url = str_replace(["\r", "\n"], '', $url);
    header("Location: $url");
    exit();
}

function csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return '<input type="hidden" name="csrf_token" value="' . $_SESSION['csrf_token'] . '">';
}

function verify_csrf() {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== ($_SESSION['csrf_token'] ?? '')) {
        http_response_code(403);
        die("Invalid or expired request. Please go back and try again.");
    }
}

function checkPermission($permission, $redirectUrl = null) {
    if (!isset($_SESSION['user'])) {
        if ($redirectUrl) {
            safeRedirect($redirectUrl);
        }
        return false;
    }
    
    if (!hasPermission($_SESSION, $permission)) {
        if ($redirectUrl) {
            $_SESSION['error'] = 'You do not have permission to access this page.';
            safeRedirect($redirectUrl);
        }
        return false;
    }
    
    return true;
}

/**
 * Require admin access
 * 
 * @param string $redirectUrl URL to redirect to if not admin
 */
function requireAdmin($redirectUrl = 'dashboard.php') {
    checkPermission('manage_users', $redirectUrl);
}

/**
 * Require teacher or admin access
 * 
 * @param string $redirectUrl URL to redirect to if not authorized
 */
function requireTeacherOrAdmin($redirectUrl = 'dashboard.php') {
    if (!isset($_SESSION['user'])) {
        safeRedirect($redirectUrl);
    }
    
    $role = $_SESSION['user_role'] ?? 'staff';
    if ($role !== 'admin' && $role !== 'teacher') {
        if ($redirectUrl) {
            $_SESSION['error'] = 'You do not have permission to access this page.';
            safeRedirect($redirectUrl);
        }
    }
}

/**
 * Get department filter for non-admin users.
 * Returns the user's department (teacher/staff/HOD see only their own),
 * or null for admin (sees all).
 *
 * @return string|null Department code or null for no filter
 */
function getUserDept() {
    if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin') {
        return null;
    }
    return $_SESSION['user_dept'] ?? null;
}
?>
