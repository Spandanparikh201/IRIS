<?php

use PHPUnit\Framework\TestCase;

final class GetSchemaTest extends TestCase
{
    public function test_schema_returns_tables(): void
    {
        $expectedTables = ['students', 'attendance', 'users', 'departments', 'books', 'book_transactions'];
        $this->assertContains('students', $expectedTables);
        $this->assertContains('attendance', $expectedTables);
        $this->assertCount(6, $expectedTables);
    }
}
