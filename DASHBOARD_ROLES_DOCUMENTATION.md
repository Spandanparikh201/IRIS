# Role-Specific Dashboard System

## Overview
This document describes the role-specific dashboard implementation for the I.R.I.S system. Each user role now has a customized dashboard that displays only relevant information and actions based on their permissions.

## Role Structure

### 1. Admin (Administrator)
- **Access Level**: Full system access
- **Department**: None (system-wide)
- **Dashboard**: `admin_dashboard.php`
- **Permissions**: All permissions enabled

**Features:**
- View all students across all departments
- View all attendance records
- View all library data
- Manage all users
- Manage all departments
- Generate all reports
- Full system statistics

### 2. HOD (Head of Department)
- **Access Level**: Department-specific
- **Department**: Assigned to one department only
- **Dashboard**: `hod_dashboard.php`
- **Permissions**: Department-level management

**Features:**
- View only their department's students
- View only their department's attendance
- View only their department's reports
- Add/edit/delete students in their department
- Mark attendance for their department
- Cannot access library or user management

### 3. Teacher/Staff
- **Access Level**: Department-specific
- **Department**: Assigned to one department
- **Dashboard**: `teacher_dashboard.php`
- **Permissions**: Read-only students, attendance management

**Features:**
- View only their department's students (read-only)
- View only their department's attendance
- Mark attendance for their department
- Generate reports for their department
- Cannot add/edit/delete students
- Cannot access library or user management

*(Librarian role has been removed)*
- Manage library catalog
- View book issues and returns
- No access to attendance or student data

## Dashboard Files

### admin_dashboard.php
- Full system overview
- All department statistics
- Quick actions for all features
- Recent activity feed (all departments)
- Navigation: Dashboard, Students, Attendance, Reports, Library, Settings, Manage Users, Departments

### hod_dashboard.php
- Department-specific overview
- Only their department's statistics
- Quick actions for department management
- Recent activity feed (department only)
- Navigation: Dashboard, Students, Attendance, Reports, Settings

### teacher_dashboard.php
- Department-specific overview
- Only their department's statistics
- Quick actions for attendance and reports
- Recent activity feed (attendance only)
- Navigation: Dashboard, Attendance, Reports, Settings

*(librarian_dashboard.php removed - role removed)*

## Database Changes

### Users Table Structure
```sql
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    dept VARCHAR(50) NOT NULL,
    role ENUM('admin', 'hod', 'teacher', 'staff') NOT NULL,
    password VARCHAR(20) NOT NULL,
    first_login BOOLEAN DEFAULT TRUE
);
```

### Migration Script
Run `update_users_for_hod.sql` to:
1. Add 'hod' to the role enum
2. Create sample HOD users for each department
3. Update existing users to correct roles

## Permission Matrix

| Permission | Admin | HOD | Teacher | Staff |
|------------|-------|-----|---------|-------|-----------|
| Permission | Admin | HOD | Teacher | Staff |
|------------|-------|-----|---------|-------|
| view_dashboard | ✅ | ✅ | ✅ | ✅ |
| view_all_reports | ✅ | ✅ | ❌ | ❌ |
| add_student | ✅ | ✅ | ❌ | ❌ |
| edit_student | ✅ | ✅ | ❌ | ❌ |
| delete_student | ✅ | ✅ | ❌ | ❌ |
| view_all_students | ✅ | ✅ | ✅ | ✅ |
| view_department_students | ❌ | ✅ | ✅ | ✅ |
| mark_attendance | ✅ | ✅ | ✅ | ✅ |
| view_attendance | ✅ | ✅ | ✅ | ✅ |
| view_department_attendance | ❌ | ✅ | ✅ | ✅ |
| manage_users | ✅ | ❌ | ❌ | ❌ |
| manage_departments | ✅ | ❌ | ❌ | ❌ |

## Implementation Details

### Role Detection
The system uses session variables to determine user role:
- `$_SESSION['user_role']` - Contains the user's role
- `$_SESSION['user_dept']` - Contains the user's department (if applicable)

### Dashboard Redirection
When a user logs in or visits `dashboard.php`, they are automatically redirected to their role-specific dashboard:
```php
if (isAdministrator()) {
    header("Location: admin_dashboard.php");
} elseif (isHOD()) {
    header("Location: hod_dashboard.php");
} elseif (isTeacher() || isStaff()) {
    header("Location: teacher_dashboard.php");
} // Librarian role removed
```

### Department Filtering
For HOD, Teacher, and Staff roles, all queries are filtered by their department:
```php
$userDepartment = getUserDepartment();
$totalStudents = $conn->query("SELECT COUNT(*) FROM students WHERE department = '" . $conn->real_escape_string($userDepartment) . "'")->fetch_row()[0];
```

## Setup Instructions

1. **Run the migration script:**
   ```bash
   mysql -u root -p studentdb < update_users_for_hod.sql
   ```

2. **Update existing users:**
   - Admin users should have `role = 'admin'`
   - HOD users should have `role = 'hod'` and `dept = 'Department Name'`
   - Teacher/Staff users should have `role = 'teacher'` or `role = 'staff'` and `dept = 'Department Name'`
   - *(Librarian role removed)*

3. **Test the system:**
   - Login as each role type
   - Verify dashboard shows correct data
   - Verify navigation shows correct links
   - Verify department filtering works correctly

## Security Considerations

1. **Department Filtering**: All queries for HOD, Teacher, and Staff are filtered by department to prevent data leakage
2. **Permission Checks**: Each dashboard checks user role before displaying content
3. **Session Validation**: All dashboards validate user session before showing content
4. **SQL Injection Prevention**: Use prepared statements and real_escape_string for all queries

## Future Enhancements

1. **Activity Feed**: Add more detailed activity tracking
2. **Notifications**: Add alert system for overdue books, low attendance, etc.
3. **Charts**: Add more visualizations for department statistics
4. **Export**: Add export functionality for department-specific reports
5. **Mobile Responsive**: Improve mobile experience for all dashboards

## Troubleshooting

### Issue: User sees wrong dashboard
**Solution**: Check `$_SESSION['user_role']` in the database and ensure it matches one of the valid roles

### Issue: Department data not showing
**Solution**: Verify `$_SESSION['user_dept']` is set correctly in the session

### Issue: Permission errors
**Solution**: Check the `rbac.php` file to ensure permissions are defined for the user's role

## Support

For issues or questions, refer to:
- `RBAC_DOCUMENTATION.md` - RBAC system documentation
- `RBAC_IMPLEMENTATION_SUMMARY.md` - Implementation details
- `update_users_for_hod.sql` - Database migration script
