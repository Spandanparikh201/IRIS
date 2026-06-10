<?php

use PHPUnit\Framework\TestCase;
use Playwright\Testing\PlaywrightTestCaseTrait;

final class LoginTest extends TestCase
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

    public function test_valid_login_redirects_to_dashboard(): void
    {
        $this->page->goto('http://localhost/IRIS/login.php');
        $this->page->locator('input[name="username"]')->fill('admin');
        $this->page->locator('input[name="password"]')->fill('password');
        $this->page->locator('button[type="submit"]')->click();
        $this->page->waitForURL('**/dashboard.php');
    }

    public function test_invalid_login_shows_error(): void
    {
        $this->page->goto('http://localhost/IRIS/login.php');
        $this->page->locator('input[name="username"]')->fill('admin');
        $this->page->locator('input[name="password"]')->fill('wrongpassword');
        $this->page->locator('button[type="submit"]')->click();
        $this->assertStringContainsString('login', $this->page->url());
    }
}
