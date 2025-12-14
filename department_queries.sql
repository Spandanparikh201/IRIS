-- Essential Department Queries for IRIS System

-- 1. Get all departments with student counts (matches your current data)
SELECT 
    d.dept_code,
    d.dept_name,
    d.dept_head,
    d.contact_email,
    d.building,
    d.floor_number,
    d.total_students,
    d.status,
    COUNT(DISTINCT s.id) as current_students
FROM departments d
LEFT JOIN students s ON d.dept_code = s.department
GROUP BY d.id
ORDER BY d.dept_name;

-- 2. Today's attendance by department
SELECT 
    d.dept_code,
    d.dept_name,
    COUNT(DISTINCT s.id) as total_students,
    COUNT(DISTINCT CASE WHEN a.status = 'IN' AND DATE(a.timestamp) = CURDATE() THEN s.id END) as present_today,
    ROUND(
        (COUNT(DISTINCT CASE WHEN a.status = 'IN' AND DATE(a.timestamp) = CURDATE() THEN s.id END) * 100.0) / 
        NULLIF(COUNT(DISTINCT s.id), 0), 2
    ) as attendance_percentage
FROM departments d
LEFT JOIN students s ON d.dept_code = s.department
LEFT JOIN attendance a ON s.rfid = a.rfid
WHERE d.status = 'active'
GROUP BY d.id
ORDER BY attendance_percentage DESC;

-- 3. Department-wise student details
SELECT 
    d.dept_name,
    s.name as student_name,
    s.roll_number,
    s.email,
    s.rfid,
    COALESCE(latest_attendance.last_seen, 'Never') as last_attendance,
    COALESCE(latest_attendance.status, 'N/A') as last_status
FROM departments d
JOIN students s ON d.dept_code = s.department
LEFT JOIN (
    SELECT 
        a1.rfid,
        a1.timestamp as last_seen,
        a1.status
    FROM attendance a1
    WHERE a1.timestamp = (
        SELECT MAX(a2.timestamp) 
        FROM attendance a2 
        WHERE a2.rfid = a1.rfid
    )
) latest_attendance ON s.rfid = latest_attendance.rfid
WHERE d.status = 'active'
ORDER BY d.dept_name, s.name;

-- 4. Weekly attendance summary by department
SELECT 
    d.dept_name,
    COUNT(DISTINCT s.id) as total_students,
    COUNT(DISTINCT CASE WHEN YEARWEEK(a.timestamp, 1) = YEARWEEK(CURDATE(), 1) THEN s.id END) as attended_this_week,
    COUNT(CASE WHEN YEARWEEK(a.timestamp, 1) = YEARWEEK(CURDATE(), 1) THEN 1 END) as total_records_this_week
FROM departments d
LEFT JOIN students s ON d.dept_code = s.department
LEFT JOIN attendance a ON s.rfid = a.rfid
WHERE d.status = 'active'
GROUP BY d.id
ORDER BY d.dept_name;

-- 5. Department performance metrics
SELECT 
    d.dept_code,
    d.dept_name,
    COUNT(DISTINCT s.id) as total_students,
    
    -- Today's metrics
    COUNT(DISTINCT CASE WHEN DATE(a.timestamp) = CURDATE() THEN s.id END) as unique_students_today,
    COUNT(CASE WHEN DATE(a.timestamp) = CURDATE() THEN 1 END) as total_scans_today,
    
    -- This week's metrics
    COUNT(DISTINCT CASE WHEN YEARWEEK(a.timestamp, 1) = YEARWEEK(CURDATE(), 1) THEN s.id END) as unique_students_week,
    COUNT(CASE WHEN YEARWEEK(a.timestamp, 1) = YEARWEEK(CURDATE(), 1) THEN 1 END) as total_scans_week,
    
    -- This month's metrics
    COUNT(DISTINCT CASE WHEN MONTH(a.timestamp) = MONTH(CURDATE()) AND YEAR(a.timestamp) = YEAR(CURDATE()) THEN s.id END) as unique_students_month,
    COUNT(CASE WHEN MONTH(a.timestamp) = MONTH(CURDATE()) AND YEAR(a.timestamp) = YEAR(CURDATE()) THEN 1 END) as total_scans_month

FROM departments d
LEFT JOIN students s ON d.dept_code = s.department
LEFT JOIN attendance a ON s.rfid = a.rfid
WHERE d.status = 'active'
GROUP BY d.id
ORDER BY d.dept_name;

-- 6. Find students without recent attendance (last 7 days)
SELECT 
    d.dept_name,
    s.name,
    s.roll_number,
    s.email,
    COALESCE(MAX(a.timestamp), 'Never attended') as last_attendance
FROM departments d
JOIN students s ON d.dept_code = s.department
LEFT JOIN attendance a ON s.rfid = a.rfid
WHERE d.status = 'active'
GROUP BY s.id
HAVING MAX(a.timestamp) IS NULL OR MAX(a.timestamp) < DATE_SUB(CURDATE(), INTERVAL 7 DAY)
ORDER BY d.dept_name, s.name;

-- 7. Department head contact list
SELECT 
    d.dept_code,
    d.dept_name,
    d.dept_head,
    d.contact_email,
    d.contact_phone,
    CONCAT(d.building, ' - Floor ', d.floor_number) as location,
    d.total_students
FROM departments d
WHERE d.status = 'active' AND d.dept_head IS NOT NULL
ORDER BY d.dept_name;