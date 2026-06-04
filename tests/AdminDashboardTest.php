<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../admin_functions.php';

class AdminDashboardTest extends TestCase
{
    public function testAdminStats()
    {
        $users = [

            ['is_active' => 1],

            ['is_active' => 0]
        ];

        $stats = calculateAdminStats($users);

        $this->assertEquals(
            1,
            $stats['active']
        );

        $this->assertEquals(
            1,
            $stats['blocked']
        );
    }
}