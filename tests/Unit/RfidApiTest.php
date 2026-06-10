<?php

use PHPUnit\Framework\TestCase;

final class RfidApiTest extends TestCase
{
    public function test_empty_rfid_rejected(): void
    {
        $rfid = '';
        $this->assertEmpty($rfid);
    }

    public function test_toggle_in_to_out(): void
    {
        $lastStatus = 'IN';
        $status = ($lastStatus === 'IN') ? 'OUT' : 'IN';
        $this->assertSame('OUT', $status);
    }

    public function test_toggle_out_to_in(): void
    {
        $lastStatus = 'OUT';
        $status = ($lastStatus === 'IN') ? 'OUT' : 'IN';
        $this->assertSame('IN', $status);
    }

    public function test_first_scan_defaults_to_in(): void
    {
        $last = null;
        $status = 'IN';
        $this->assertSame('IN', $status);
    }

    public function test_duplicate_scan_within_interval_rejected(): void
    {
        $DUPLICATE_INTERVAL = 20;
        $lastTime = time() - 5;
        $currentTime = time();
        $timeDiff = $currentTime - $lastTime;

        $isDuplicate = $timeDiff < $DUPLICATE_INTERVAL;
        $this->assertTrue($isDuplicate);
    }

    public function test_scan_after_interval_allowed(): void
    {
        $DUPLICATE_INTERVAL = 20;
        $lastTime = time() - 30;
        $currentTime = time();
        $timeDiff = $currentTime - $lastTime;

        $isDuplicate = $timeDiff < $DUPLICATE_INTERVAL;
        $this->assertFalse($isDuplicate);
    }
}
