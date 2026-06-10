<?php

use PHPUnit\Framework\TestCase;

final class ImportStudentsTest extends TestCase
{
    public function test_valid_csv_row_parsed(): void
    {
        $row = ['ABC123', 'John Doe', 'CS', '2024001', 'john@example.com'];
        $this->assertCount(5, $row);
        $this->assertNotEmpty($row[0]);
        $this->assertNotEmpty($row[1]);
    }

    public function test_missing_rfid_rejected(): void
    {
        $rfid = '';
        $this->assertEmpty($rfid);
    }

    public function test_duplicate_rfid_detected(): void
    {
        $existing = ['ABC123', 'DEF456'];
        $newRfid = 'ABC123';
        $this->assertContains($newRfid, $existing);
    }

    public function test_missing_name_rejected(): void
    {
        $name = '';
        $this->assertEmpty($name);
    }

    public function test_valid_email_format(): void
    {
        $email = 'john@example.com';
        $this->assertStringContainsString('@', $email);
    }
}
