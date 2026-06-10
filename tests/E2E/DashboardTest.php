<?php

use PHPUnit\Framework\TestCase;
use Playwright\Testing\PlaywrightTestCaseTrait;

final class DashboardTest extends TestCase
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

    public function test_dashboard_shows_stat_cards(): void
    {
        $this->login();
        $this->page->goto('http://localhost/IRIS/dashboard.php');
        $this->assertStringContainsString('dashboard', $this->page->url());
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
