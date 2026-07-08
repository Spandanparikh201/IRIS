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
function checkPermission($permission, $redirectUrl = null) {
    if (!isset($_SESSION['user'])) {
        if ($redirectUrl) {
            header("Location: $redirectUrl");
            exit();
        }
        return false;
    }
    
    if (!hasPermission($_SESSION, $permission)) {
        if ($redirectUrl) {
            $_SESSION['error'] = 'You do not have permission to access this page.';
            header("Location: $redirectUrl");
            exit();
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
function requireAdmin($redirectUrl = 'admin_dashboard.php') {
    checkPermission('manage_users', $redirectUrl);
}

/**
 * Require teacher or admin access
 * 
 * @param string $redirectUrl URL to redirect to if not authorized
 */
function requireTeacherOrAdmin($redirectUrl = 'teacher_dashboard.php') {
    if (!isset($_SESSION['user'])) {
        header("Location: $redirectUrl");
        exit();
    }
    
    $role = $_SESSION['user_role'] ?? 'staff';
    if ($role !== 'admin' && $role !== 'teacher') {
        if ($redirectUrl) {
            $_SESSION['error'] = 'You do not have permission to access this page.';
            header("Location: $redirectUrl");
            exit();
        }
    }
}

/**
 * Require librarian access
 * 
 * @param string $redirectUrl URL to redirect to if not librarian
 */
function requireLibrarian($redirectUrl = 'librarian_dashboard.php') {
    checkPermission('manage_library', $redirectUrl);
}

/**
 * Get user's role name
 * 
 * @return string User's role name
 */
function getUserRole() {
    return $_SESSION['user_role'] ?? 'staff';
}

/**
 * Get user's department
 * 
 * @return string User's department
 */
function getUserDepartment() {
    return $_SESSION['user_dept'] ?? 'General';
}

/**
 * Check if user can access a specific page
 * 
 * @param string $page The page name (file name without .php)
 * @return bool True if user can access the page
 */
function canAccess($page) {
    if (!isset($_SESSION['user'])) {
        return false;
    }
    
    return canAccessPage($_SESSION, $page);
}

/**
 * Filter menu items based on user permissions
 * 
 * @param array $menuItems Array of menu items
 * @return array Filtered menu items
 */
function filterMenu($menuItems) {
    if (!isset($_SESSION['user'])) {
        return [];
    }
    
    return filterMenuByPermissions($_SESSION, $menuItems);
}

/**
 * Get user's enabled permissions
 * 
 * @return array Array of enabled permission names
 */
function getEnabledPermissions() {
    if (!isset($_SESSION['user'])) {
        return [];
    }
    
    return getUserEnabledPermissions($_SESSION);
}

/**
 * Check if user is admin
 * 
 * @return bool True if user is admin
 */
function isAdministrator() {
    return $_SESSION['user_role'] ?? '' === 'admin';
}

/**
 * Check if user is admin
 * 
 * @return bool True if user is admin
 */
function isAdmin() {
    return $_SESSION['user_role'] ?? '' === 'admin';
}

/**
 * Check if user is HOD
 * 
 * @return bool True if user is HOD
 */
function isHOD() {
    return $_SESSION['user_role'] ?? '' === 'hod';
}

/**
 * Check if user is teacher
 * 
 * @return bool True if user is teacher
 */
function isTeacher() {
    return $_SESSION['user_role'] ?? '' === 'teacher';
}

/**
 * Check if user is staff
 * 
 * @return bool True if user is staff
 */
function isStaff() {
    return $_SESSION['user_role'] ?? '' === 'staff';
}

/**
 * Check if user is librarian
 * 
 * @return bool True if user is librarian
 */
function isLibrarian() {
    return $_SESSION['user_role'] ?? '' === 'librarian';
}
?>
