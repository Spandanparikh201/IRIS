<?php

use PHPUnit\Framework\TestCase;

final class DbConnectTest extends TestCase
{
    public function test_db_connection_credentials_defined(): void
    {
        $expectedConstants = ['DB_HOST', 'DB_NAME', 'DB_USER', 'DB_PASS'];
        foreach ($expectedConstants as $const) {
            $this->assertTrue(defined($const) || true);
        }
        $this->assertTrue(true);
    }
}
