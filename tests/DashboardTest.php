<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../dashboard_functions.php';

class DashboardTest extends TestCase
{
    public function testBalanceCalculation()
    {
        $balance = calculateBalance(
            5000,
            2000
        );

        $this->assertEquals(
            3000,
            $balance
        );
    }
}