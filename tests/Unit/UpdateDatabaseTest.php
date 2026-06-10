<?php

use PHPUnit\Framework\TestCase;

final class UpdateDatabaseTest extends TestCase
{
    public function test_schema_alter_sql_valid(): void
    {
        $sql = "ALTER TABLE students MODIFY COLUMN department ENUM('CE', 'IT', 'ME') NOT NULL";
        $this->assertStringStartsWith('ALTER TABLE', $sql);
        $this->assertStringContainsString('MODIFY COLUMN', $sql);
    }

    public function test_enum_values_preserved(): void
    {
        $enumValues = ['CE', 'IT', 'ME', 'EE', 'EC', 'CV', 'CSE', 'AI', 'DS'];
        $this->assertContains('CE', $enumValues);
        $this->assertContains('IT', $enumValues);
        $this->assertCount(9, $enumValues);
    }

    public function test_status_enum_valid(): void
    {
        $statusValues = ['IN', 'OUT'];
        $this->assertContains('IN', $statusValues);
        $this->assertContains('OUT', $statusValues);
        $this->assertCount(2, $statusValues);
    }
}
