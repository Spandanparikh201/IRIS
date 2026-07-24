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
        'bulk_upload_students' => true,
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
        'bulk_upload_students' => true,
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


?>
