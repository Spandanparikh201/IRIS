<?php

use PHPUnit\Framework\TestCase;
use Playwright\Testing\PlaywrightTestCaseTrait;

final class StudentCrudTest extends TestCase
{
    use PlaywrightTestCaseTrait;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpPlaywright();
    }

    protected function tearDown(): void
    {
        $this->tearDownPlaywright();
        parent::tearDown();
    }

    public function test_add_student_form_submits(): void
    {
        $this->login();
        $this->page->goto('http://localhost/IRIS/add_student.php');
        $this->page->locator('input[name="name"]')->fill('Test Student');
        $this->page->locator('input[name="roll_number"]')->fill('TEST001');
        $this->page->locator('input[name="rfid"]')->fill('TESTRFID001');
        $this->page->locator('form')->first()->press('Enter');
    }

    private function login(): void
    {
        $this->page->goto('http://localhost/IRIS/login.php');
        $this->page->locator('input[name="username"]')->fill('admin');
        $this->page->locator('input[name="password"]')->fill('password');
        $this->page->locator('button[type="submit"]')->click();
        $this->page->waitForURL('**/dashboard.php');
    }
}
