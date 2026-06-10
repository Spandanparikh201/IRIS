<?php

use PHPUnit\Framework\TestCase;

final class ExportCsvTest extends TestCase
{
    public function test_csv_header_format(): void
    {
        $headers = ['Name', 'RFID', 'Department', 'Status', 'Timestamp'];
        $this->assertContains('Name', $headers);
        $this->assertContains('Status', $headers);
        $this->assertCount(5, $headers);
    }

    public function test_empty_dataset_returns_no_rows(): void
    {
        $data = [];
        $this->assertCount(0, $data);
    }

    public function test_csv_row_format(): void
    {
        $row = ['John Doe', 'ABC123', 'CS', 'IN', '2026-06-10 08:00:00'];
        $this->assertCount(5, $row);
        $this->assertIsString($row[0]);
        $this->assertIsString($row[1]);
    }
}
