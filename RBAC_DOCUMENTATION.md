# I.R.I.S - Role-Based Access Control (RBAC) System

## 📋 Overview

The RBAC system defines permissions for different user roles in the I.R.I.S application. Each role has specific permissions that determine what actions they can perform.

## 👥 User Roles

### 1. **Administrator (admin)**
- **Full system access**
- Can manage all aspects of the system
- Can create and manage users
- Can access all reports and features

### 2. **Teacher (teacher)**
- Can manage students
- Can view and generate reports
- Can mark attendance
- Cannot manage users or departments
- Cannot delete students

### 3. **Staff (staff)**
- Can view dashboard
- Can view attendance
- Can mark attendance
- Limited report generation
- Cannot manage users or departments

*(Librarian role has been removed)*

## 🔐 Permission Categories

### Dashboard Permissions
- `view_dashboard` - View dashboard statistics
- `view_all_reports` - View and generate all reports
- `export_data` - Export data to CSV/Excel

### Student Management
- `add_student` - Add new students
- `edit_student` - Edit student information
- `delete_student` - Delete students
- `bulk_upload_students` - Upload students via CSV
- `view_all_students` - View all student records

### Attendance
- `view_attendance` - View attendance records
- `mark_attendance` - Mark attendance
- `edit_attendance` - Edit attendance records
- `delete_attendance` - Delete attendance records

### Reports
- `generate_daily_report` - Generate daily attendance report
- `generate_weekly_report` - Generate weekly attendance report
- `generate_monthly_report` - Generate monthly attendance report
- `generate_department_report` - Generate department-wise report
- `generate_student_report` - Generate student-specific report
- `export_reports` - Export reports to PDF/Excel

### Library
- `view_library` - View library catalog
- `add_book` - Add new books to library
- `issue_book` - Issue books to students
- `return_book` - Process book returns
- `manage_library` - Manage library settings

### User Management
- `add_user` - Create new user accounts
- `edit_user` - Edit user accounts
- `delete_user` - Delete user accounts
- `manage_users` - Manage all users
- `view_all_users` - View all user accounts

### Department Management
- `add_department` - Create new departments
- `edit_department` - Edit department information
- `delete_department` - Delete departments
- `manage_departments` - Manage all departments

### Settings
- `change_password` - Change own password
- `manage_settings` - Manage system settings

### System
- `view_system_logs` - View system logs
- `backup_database` - Backup database
- `restore_database` - Restore database

## 📁 Files

### Core RBAC Files
1. **rbac.php** - Main RBAC configuration file
   - Defines all permissions for each role
   - Contains permission checking functions
   - Role management functions

2. **rbac_helper.php** - Helper functions for permission checking
   - Easy-to-use permission checking functions
   - Session-based user role detection
   - Menu filtering functions

### Usage Examples

#### Basic Permission Check
```php
<?php
include 'rbac_helper.php';

// Check if user can add students
if (checkPermission('add_student')) {
    // User can add students
    echo "Add Student Button";
}
?>
```

#### Require Permission (Redirect if No Access)
```php
<?php
include 'rbac_helper.php';

// Require admin permission
requireAdmin('dashboard.php');

// This code only runs if user is admin
echo "Admin-only content";
?>
```

#### Check User Role
```php
<?php
include 'rbac_helper.php';

if (isAdministrator()) {
    echo "Welcome, Administrator!";
} elseif (isTeacher()) {
    echo "Welcome, Teacher!";
}
?>
```

#### Filter Menu Items
```php
<?php
include 'rbac_helper.php';

$menuItems = [
    ['name' => 'Dashboard', 'url' => 'dashboard.php'],
    ['name' => 'Students', 'url' => 'add_student.php', 'permission' => 'add_student'],
    ['name' => 'Reports', 'url' => 'reports.php', 'permission' => 'view_all_reports'],
    ['name' => 'Users', 'url' => 'manage_users.php', 'permission' => 'manage_users'],
];

$filteredMenu = filterMenu($menuItems);

foreach ($filteredMenu as $item) {
    echo "<a href='{$item['url']}'>{$item['name']}</a>";
}
?>
```

#### Check Page Access
```php
<?php
include 'rbac_helper.php';

if (canAccess('reports')) {
    // User can access reports page
} else {
    // Redirect to dashboard
    header("Location: dashboard.php");
}
?>
```

## 🔧 Adding New Permissions

### Step 1: Add Permission to rbac.php
```php
$rbac_permissions = [
    ROLE_ADMIN => [
        // ... existing permissions ...
        'new_permission' => true,  // Add here
    ],
    ROLE_TEACHER => [
        // ... existing permissions ...
        'new_permission' => false,  // Add here
    ],
    // ... other roles
];
```

### Step 2: Add Permission Description
```php
$descriptions = [
    // ... existing descriptions ...
    'new_permission' => 'Description of the new permission',
];
```

### Step 3: Use in Your Code
```php
<?php
include 'rbac_helper.php';

if (checkPermission('new_permission')) {
    // Code for users with permission
}
?>
```

## 🛡️ Security Features

1. **Session-based Authentication** - All permission checks use session data
2. **Role-based Permissions** - Permissions are defined per role
3. **Centralized Configuration** - All permissions in one file (rbac.php)
4. **Easy Maintenance** - Simple array structure for adding/removing permissions
5. **Flexible Checking** - Multiple ways to check permissions

## 📊 Permission Matrix

| Permission | Admin | Teacher | Staff |
|------------|-------|---------|-------|
| View Dashboard | ✅ | ✅ | ✅ |
| View All Reports | ✅ | ✅ | ❌ |
| Add Student | ✅ | ✅ | ❌ |
| Edit Student | ✅ | ✅ | ❌ |
| Delete Student | ✅ | ❌ | ❌ |
| Mark Attendance | ✅ | ✅ | ✅ |
| Generate Reports | ✅ | ✅ | ❌ |
| Manage Users | ✅ | ❌ | ❌ |
| Manage Departments | ✅ | ❌ | ❌ |
| Change Password | ✅ | ✅ | ✅ |

## 🎯 Best Practices

1. **Always include rbac_helper.php** in pages that need permission checking
2. **Use checkPermission()** for simple permission checks
3. **Use requireAdmin()** for admin-only pages
4. **Use filterMenu()** to show/hide menu items based on permissions
5. **Test permissions** before deploying to production
6. **Document new permissions** in rbac.php comments

## 📝 Notes

- All permissions default to `false` if not defined for a role
- Permission checks are case-sensitive
- Session must be started before using RBAC functions
- RBAC system is independent of database structure
- Permissions can be modified without changing code logic

## 🔮 Future Enhancements

1. **Permission inheritance** - Allow roles to inherit permissions from other roles
2. **Granular permissions** - Add more specific permissions (e.g., edit own students only)
3. **Audit logging** - Log permission changes and access attempts
4. **Permission groups** - Group related permissions for easier management
5. **Dynamic permissions** - Allow admin to modify permissions via UI
