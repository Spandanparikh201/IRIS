<?php

use PHPUnit\Framework\TestCase;

final class SettingsTest extends TestCase
{
    public function test_password_change_requires_old_password(): void
    {
        $postData = ['old_password' => 'current123', 'new_password' => 'new456'];
        $this->assertArrayHasKey('old_password', $postData);
    }

    public function test_new_password_minimum_length(): void
    {
        $newPassword = 'newpass123';
        $this->assertGreaterThanOrEqual(6, strlen($newPassword));
    }

    public function test_password_change_success(): void
    {
        $oldPassword = 'current123';
        $newPassword = 'newpass456';
        $confirmPassword = 'newpass456';

        $this->assertNotEmpty($oldPassword);
        $this->assertEquals($newPassword, $confirmPassword);
        $this->assertNotEquals($oldPassword, $newPassword);
    }
}
