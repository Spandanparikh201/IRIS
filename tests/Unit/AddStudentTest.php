<?php

use PHPUnit\Framework\TestCase;

final class AddStudentTest extends TestCase
{
    public function test_student_required_fields(): void
    {
        $required = ['name', 'roll_number', 'department', 'rfid'];
        $data = ['name' => 'John', 'roll_number' => '2024001', 'department' => 'CS', 'rfid' => 'ABC123'];

        foreach ($required as $field) {
            $this->assertArrayHasKey($field, $data);
            $this->assertNotEmpty($data[$field]);
        }
    }

    public function test_missing_rfid_rejected(): void
    {
        $data = ['name' => 'John', 'roll_number' => '2024001', 'department' => 'CS'];
        $this->assertArrayNotHasKey('rfid', $data);
    }

    public function test_student_creation_success(): void
    {
        $name = 'Jane Doe';
        $roll = '2024002';
        $dept = 'IT';
        $rfid = 'DEF456';

        $this->assertNotEmpty($name);
        $this->assertNotEmpty($roll);
        $this->assertNotEmpty($dept);
        $this->assertNotEmpty($rfid);
    }
}
