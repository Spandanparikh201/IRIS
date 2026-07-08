<?php
/**
 * Role-Based Access Control (RBAC) System for I.R.I.S
 * 
 * This file defines the permission system for different user roles
 */

// Define user roles
define('ROLE_ADMIN', 'admin');
define('ROLE_HOD', 'hod');
define('ROLE_TEACHER', 'teacher');
define('ROLE_STAFF', 'staff');
define('ROLE_LIBRARIAN', 'librarian');

// Define permissions for each role
$rbac_permissions = [
    ROLE_ADMIN => [
        // Dashboard permissions
        'view_dashboard' => true,
        'view_all_reports' => true,
        'export_data' => true,
        
        // Student management
        'add_student' => true,
        'edit_student' => true,
        'delete_student' => true,
        'bulk_upload_students' => true,
        'view_all_students' => true,
        
        // Attendance
        'view_attendance' => true,
        'mark_attendance' => true,
        'edit_attendance' => true,
        'delete_attendance' => true,
        
        // Reports
        'generate_daily_report' => true,
        'generate_weekly_report' => true,
        'generate_monthly_report' => true,
        'generate_department_report' => true,
        'generate_student_report' => true,
        'export_reports' => true,
        
        // Library
        'view_library' => true,
        'add_book' => true,
        'issue_book' => true,
        'return_book' => true,
        'manage_library' => true,
        
        // User management
        'add_user' => true,
        'edit_user' => true,
        'delete_user' => true,
        'manage_users' => true,
        'view_all_users' => true,
        
        // Department management
        'add_department' => true,
        'edit_department' => true,
        'delete_department' => true,
        'manage_departments' => true,
        
        // Settings
        'change_password' => true,
        'manage_settings' => true,
        
        // System
        'view_system_logs' => true,
        'backup_database' => true,
        'restore_database' => true,
    ],
    
    ROLE_TEACHER => [
        // Dashboard permissions
        'view_dashboard' => true,
        'view_all_reports' => false,
        'export_data' => false,
        
        // Student management
        'add_student' => false,
        'edit_student' => false,
        'delete_student' => false,
        'bulk_upload_students' => false,
        'view_all_students' => true,
        'view_department_students' => true,
        
        // Attendance
        'view_attendance' => true,
        'mark_attendance' => true,
        'edit_attendance' => false,
        'delete_attendance' => false,
        'view_department_attendance' => true,
        
        // Reports
        'generate_daily_report' => true,
        'generate_weekly_report' => false,
        'generate_monthly_report' => false,
        'generate_department_report' => true,
        'generate_student_report' => false,
        'export_reports' => false,
        
        // Library
        'view_library' => false,
        'issue_book' => false,
        'return_book' => false,
        'add_book' => false,
        'manage_library' => false,
        
        // User management
        'add_user' => false,
        'edit_user' => false,
        'delete_user' => false,
        'manage_users' => false,
        'view_all_users' => false,
        
        // Department management
        'add_department' => false,
        'edit_department' => false,
        'delete_department' => false,
        'manage_departments' => false,
        
        // Settings
        'change_password' => true,
        'manage_settings' => false,
        
        // System
        'view_system_logs' => false,
        'backup_database' => false,
        'restore_database' => false,
    ],
    
    ROLE_STAFF => [
        // Dashboard permissions
        'view_dashboard' => true,
        'view_all_reports' => false,
        'export_data' => false,
        
        // Student management
        'add_student' => false,
        'edit_student' => false,
        'delete_student' => false,
        'bulk_upload_students' => false,
        'view_all_students' => true,
        'view_department_students' => true,
        
        // Attendance
        'view_attendance' => true,
        'mark_attendance' => true,
        'edit_attendance' => false,
        'delete_attendance' => false,
        'view_department_attendance' => true,
        
        // Reports
        'generate_daily_report' => true,
        'generate_weekly_report' => false,
        'generate_monthly_report' => false,
        'generate_department_report' => false,
        'generate_student_report' => false,
        'export_reports' => false,
        
        // Library
        'view_library' => false,
        'issue_book' => false,
        'return_book' => false,
        'add_book' => false,
        'manage_library' => false,
        
        // User management
        'add_user' => false,
        'edit_user' => false,
        'delete_user' => false,
        'manage_users' => false,
        'view_all_users' => false,
        
        // Department management
        'add_department' => false,
        'edit_department' => false,
        'delete_department' => false,
        'manage_departments' => false,
        
        // Settings
        'change_password' => true,
        'manage_settings' => false,
        
        // System
        'view_system_logs' => false,
        'backup_database' => false,
        'restore_database' => false,
    ],
    
    ROLE_HOD => [
        // Dashboard permissions
        'view_dashboard' => true,
        'view_all_reports' => true,
        'export_data' => true,
        
        // Student management
        'add_student' => true,
        'edit_student' => true,
        'delete_student' => true,
        'bulk_upload_students' => true,
        'view_all_students' => true,
        'view_department_students' => true,
        
        // Attendance
        'view_attendance' => true,
        'mark_attendance' => true,
        'edit_attendance' => true,
        'delete_attendance' => true,
        'view_department_attendance' => true,
        
        // Reports
        'generate_daily_report' => true,
        'generate_weekly_report' => true,
        'generate_monthly_report' => true,
        'generate_department_report' => true,
        'generate_student_report' => true,
        'export_reports' => true,
        
        // Library
        'view_library' => false,
        'issue_book' => false,
        'return_book' => false,
        'add_book' => false,
        'manage_library' => false,
        
        // User management
        'add_user' => false,
        'edit_user' => false,
        'delete_user' => false,
        'manage_users' => false,
        'view_all_users' => false,
        
        // Department management
        'add_department' => false,
        'edit_department' => false,
        'delete_department' => false,
        'manage_departments' => false,
        
        // Settings
        'change_password' => true,
        'manage_settings' => false,
        
        // System
        'view_system_logs' => false,
        'backup_database' => false,
        'restore_database' => false,
    ],
    
    ROLE_LIBRARIAN => [
        // Dashboard permissions
        'view_dashboard' => true,
        'view_all_reports' => false,
        'export_data' => false,
        
        // Student management
        'add_student' => false,
        'edit_student' => false,
        'delete_student' => false,
        'bulk_upload_students' => false,
        'view_all_students' => true,
        
        // Attendance
        'view_attendance' => true,
        'mark_attendance' => false,
        'edit_attendance' => false,
        'delete_attendance' => false,
        
        // Reports
        'generate_daily_report' => false,
        'generate_weekly_report' => false,
        'generate_monthly_report' => false,
        'generate_department_report' => false,
        'generate_student_report' => false,
        'export_reports' => false,
        
        // Library
        'view_library' => true,
        'issue_book' => true,
        'return_book' => true,
        'add_book' => true,
        'manage_library' => true,
        
        // User management
        'add_user' => false,
        'edit_user' => false,
        'delete_user' => false,
        'manage_users' => false,
        'view_all_users' => false,
        
        // Department management
        'add_department' => false,
        'edit_department' => false,
        'delete_department' => false,
        'manage_departments' => false,
        
        // Settings
        'change_password' => true,
        'manage_settings' => false,
        
        // System
        'view_system_logs' => false,
        'backup_database' => false,
        'restore_database' => false,
    ],
];

/**
 * Check if a user has a specific permission
 * 
 * @param array $user The user array from session
 * @param string $permission The permission to check
 * @return bool True if user has permission, false otherwise
 */
function hasPermission($user, $permission) {
    global $rbac_permissions;
    
    $role = $user['user_role'] ?? 'staff';
    
    if (isset($rbac_permissions[$role])) {
        return $rbac_permissions[$role][$permission] ?? false;
    }
    
    return false;
}

/**
 * Check if user has any of the required permissions
 * 
 * @param array $user The user array from session
 * @param array $permissions Array of permissions to check
 * @return bool True if user has any permission, false otherwise
 */
function hasAnyPermission($user, $permissions) {
    foreach ($permissions as $permission) {
        if (hasPermission($user, $permission)) {
            return true;
        }
    }
    return false;
}

/**
 * Check if user has all required permissions
 * 
 * @param array $user The user array from session
 * @param array $permissions Array of permissions to check
 * @return bool True if user has all permissions, false otherwise
 */
function hasAllPermissions($user, $permissions) {
    foreach ($permissions as $permission) {
        if (!hasPermission($user, $permission)) {
            return false;
        }
    }
    return true;
}

/**
 * Redirect user if they don't have required permission
 * 
 * @param array $user The user array from session
 * @param string $permission The required permission
 * @param string $redirectUrl URL to redirect to if no permission
 */
function requirePermission($user, $permission, $redirectUrl = 'admin_dashboard.php') {
    if (!hasPermission($user, $permission)) {
        $_SESSION['error'] = 'You do not have permission to access this page.';
        header("Location: $redirectUrl");
        exit();
    }
}

/**
 * Get all permissions for a specific role
 * 
 * @param string $role The role name
 * @return array Array of permissions
 */
function getRolePermissions($role) {
    global $rbac_permissions;
    
    return $rbac_permissions[$role] ?? [];
}

/**
 * Get all roles
 * 
 * @return array Array of role names
 */
function getAllRoles() {
    return ['admin', 'hod', 'teacher', 'staff', 'librarian'];
}

/**
 * Get role display name
 * 
 * @param string $role The role name
 * @return string Display name of the role
 */
function getRoleDisplayName($role) {
    $roleNames = [
        'admin' => 'Administrator',
        'hod' => 'Head of Department',
        'teacher' => 'Teacher',
        'staff' => 'Staff',
        'librarian' => 'Librarian'
    ];
    
    return $roleNames[$role] ?? ucfirst($role);
}



/**
 * Get user's role permissions as array of enabled permissions
 * 
 * @param array $user The user array from session
 * @return array Array of enabled permission names
 */
function getUserEnabledPermissions($user) {
    global $rbac_permissions;
    
    $role = $user['user_role'] ?? 'staff';
    $permissions = [];
    
    if (isset($rbac_permissions[$role])) {
        foreach ($rbac_permissions[$role] as $permission => $enabled) {
            if ($enabled) {
                $permissions[] = $permission;
            }
        }
    }
    
    return $permissions;
}

/**
 * Check if user can access a specific page
 * 
 * @param array $user The user array from session
 * @param string $page The page name (file name without .php)
 * @return bool True if user can access the page
 */
function canAccessPage($user, $page) {
    $pagePermissions = [
        'dashboard' => ['view_dashboard'],
        'add_student' => ['add_student'],
        'attendance' => ['view_attendance'],
        'reports' => ['view_all_reports'],
        'library' => ['view_library'],
        'settings' => ['change_password'],
        'manage_users' => ['manage_users'],
        'manage_departments' => ['manage_departments'],
    ];
    
    if (isset($pagePermissions[$page])) {
        return hasAnyPermission($user, $pagePermissions[$page]);
    }
    
    return false;
}

/**
 * Filter menu items based on user permissions
 * 
 * @param array $user The user array from session
 * @param array $menuItems Array of menu items
 * @return array Filtered menu items
 */
function filterMenuByPermissions($user, $menuItems) {
    $filtered = [];
    
    foreach ($menuItems as $item) {
        if (isset($item['permission'])) {
            if (hasPermission($user, $item['permission'])) {
                $filtered[] = $item;
            }
        } else {
            $filtered[] = $item;
        }
    }
    
    return $filtered;
}

/**
 * Get permission description
 * 
 * @param string $permission The permission name
 * @return string Description of the permission
 */
function getPermissionDescription($permission) {
    $descriptions = [
        'view_dashboard' => 'View dashboard statistics',
        'view_all_reports' => 'View and generate all reports',
        'export_data' => 'Export data to CSV/Excel',
        'add_student' => 'Add new students',
        'edit_student' => 'Edit student information',
        'delete_student' => 'Delete students',
        'bulk_upload_students' => 'Upload students via CSV',
        'view_all_students' => 'View all student records',
        'view_attendance' => 'View attendance records',
        'mark_attendance' => 'Mark attendance',
        'edit_attendance' => 'Edit attendance records',
        'delete_attendance' => 'Delete attendance records',
        'generate_daily_report' => 'Generate daily attendance report',
        'generate_weekly_report' => 'Generate weekly attendance report',
        'generate_monthly_report' => 'Generate monthly attendance report',
        'generate_department_report' => 'Generate department-wise report',
        'generate_student_report' => 'Generate student-specific report',
        'export_reports' => 'Export reports to PDF/Excel',
        'view_library' => 'View library catalog',
        'add_book' => 'Add new books to library',
        'issue_book' => 'Issue books to students',
        'return_book' => 'Process book returns',
        'manage_library' => 'Manage library settings',
        'add_user' => 'Create new user accounts',
        'edit_user' => 'Edit user accounts',
        'delete_user' => 'Delete user accounts',
        'manage_users' => 'Manage all users',
        'view_all_users' => 'View all user accounts',
        'add_department' => 'Create new departments',
        'edit_department' => 'Edit department information',
        'delete_department' => 'Delete departments',
        'manage_departments' => 'Manage all departments',
        'change_password' => 'Change own password',
        'manage_settings' => 'Manage system settings',
        'view_system_logs' => 'View system logs',
        'backup_database' => 'Backup database',
        'restore_database' => 'Restore database',
    ];
    
    return $descriptions[$permission] ?? ucfirst(str_replace('_', ' ', $permission));
}
?>
