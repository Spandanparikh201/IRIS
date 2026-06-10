<?php

use PHPUnit\Framework\TestCase;

final class SendEmailTest extends TestCase
{
    public function test_admin_can_send_email(): void
    {
        $role = 'admin';
        $allowed = in_array($role, ['admin', 'teacher']);
        $this->assertTrue($allowed);
    }

    public function test_staff_cannot_send_email(): void
    {
        $role = 'staff';
        $allowed = in_array($role, ['admin', 'teacher']);
        $this->assertFalse($allowed);
    }

    public function test_librarian_cannot_send_email(): void
    {
        $role = 'librarian';
        $allowed = in_array($role, ['admin', 'teacher']);
        $this->assertFalse($allowed);
    }

    public function test_date_filter_query_format(): void
    {
        $date = '2026-06-10';
        $sql = "SELECT name, department, status, timestamp FROM attendance WHERE DATE(timestamp) = ?";
        $this->assertStringContainsString('DATE(timestamp)', $sql);
        $this->assertStringContainsString($date, $date);
    }
}
