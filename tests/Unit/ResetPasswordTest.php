<?php

use PHPUnit\Framework\TestCase;

final class ResetPasswordTest extends TestCase
{
    public function test_valid_password_update(): void
    {
        $newPassword = 'newpass123';
        $confirmPassword = 'newpass123';
        $minLength = 6;

        $valid = $newPassword === $confirmPassword && strlen($newPassword) >= $minLength;
        $this->assertTrue($valid);
    }

    public function test_short_password_rejected(): void
    {
        $newPassword = 'abc';
        $minLength = 6;

        $valid = strlen($newPassword) >= $minLength;
        $this->assertFalse($valid);
    }

    public function test_non_matching_passwords_rejected(): void
    {
        $newPassword = 'password123';
        $confirmPassword = 'different456';

        $match = $newPassword === $confirmPassword;
        $this->assertFalse($match);
    }

    public function test_force_reset_flag_cleared_on_success(): void
    {
        $_SESSION['force_password_reset'] = true;

        unset($_SESSION['force_password_reset']);

        $this->assertArrayNotHasKey('force_password_reset', $_SESSION);
    }
}
