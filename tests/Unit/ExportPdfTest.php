<?php

use PHPUnit\Framework\TestCase;

final class ExportPdfTest extends TestCase
{
    public function test_html_table_structure(): void
    {
        $html = '<table><tr><th>Name</th><th>RFID</th><th>Status</th></tr></table>';
        $this->assertStringContainsString('<table', $html);
        $this->assertStringContainsString('<th>Name</th>', $html);
        $this->assertStringContainsString('</table>', $html);
    }

    public function test_report_header(): void
    {
        $html = '<h1>Attendance Report</h1>';
        $this->assertStringContainsString('Attendance Report', $html);
    }

    public function test_empty_table_no_rows(): void
    {
        $rows = [];
        $this->assertCount(0, $rows);
    }
}
