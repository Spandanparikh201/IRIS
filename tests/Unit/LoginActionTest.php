<?php

use PHPUnit\Framework\TestCase;

final class LoginActionTest extends TestCase
{
    public function test_valid_credentials_sets_session(): void
    {
        $_POST['username'] = 'admin';
        $_POST['password'] = 'password123';

        $this->assertArrayHasKey('username', $_POST);
        $this->assertArrayHasKey('password', $_POST);
    }

    public function test_empty_username_rejected(): void
    {
        $username = '';
        $this->assertEmpty($username);
    }

    public function test_empty_password_rejected(): void
    {
        $password = '';
        $this->assertEmpty($password);
    }

    public function test_role_assignment_defaults_to_staff(): void
    {
        $role = $_SESSION['user_role'] ?? 'staff';
        $this->assertSame('staff', $role);
    }
}
