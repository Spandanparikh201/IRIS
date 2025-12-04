<?php
session_start();
include 'db_connect.php';

$username = $_POST['username'];
$password = $_POST['password'];

$stmt = $conn->prepare("SELECT * FROM users WHERE name = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $user = $result->fetch_assoc();
    if ($password === $user['password']) {
        $_SESSION['user'] = $user['name'];
        $_SESSION['user_id'] = $user['id'] ?? 1;
        $_SESSION['user_role'] = $user['role'] ?? 'staff';
        $_SESSION['user_dept'] = $user['dept'] ?? 'General';
        
        if (isset($user['first_login']) && $user['first_login'] == 1) {
            $_SESSION['force_password_reset'] = true;
            header("Location: reset_password.php");
            exit();
        }
        
        header("Location: dashboard.php");
        exit();
    }
}

echo "<script>alert('Invalid credentials'); window.location.href='login.php';</script>";
?>
