<?php
session_set_cookie_params([
    'httponly' => true,
    'samesite' => 'Strict',
    'secure' => true,
]);

session_start();

include 'db_connect.php';

$username = trim($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';

if ($username === '' || $password === '') {
    echo "<script>alert('Username and password are required'); window.location.href='login.php';</script>";
    exit;
}

$stmt = $conn->prepare("SELECT * FROM users WHERE name = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $user = $result->fetch_assoc();
    $storedHash = $user['password'];
    $valid = false;
    if (strlen($storedHash) > 0 && $storedHash[0] === '$') {
        $valid = password_verify($password, $storedHash);
    } else {
        $valid = ($password === $storedHash);
        if ($valid) {
            $newHash = password_hash($password, PASSWORD_BCRYPT);
            $upgrade = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
            $upgrade->bind_param("si", $newHash, $user['id']);
            $upgrade->execute();
        }
    }
    if ($valid) {
        session_regenerate_id(true);
        $_SESSION['user'] = $user['name'];
        $_SESSION['user_id'] = $user['id'] ?? 1;
        $_SESSION['user_role'] = $user['role'] ?? 'staff';
        $_SESSION['user_dept'] = $user['dept'] ?? 'General';
        
        if (isset($user['first_login']) && $user['first_login'] == 1) {
            $_SESSION['force_password_reset'] = true;
            header("Location: reset_password.php");
            exit(0);
        }
        
        header("Location: dashboard.php");
        exit(0);
    }
}

echo "<script>alert('Invalid credentials'); window.location.href='login.php';</script>";
?>
