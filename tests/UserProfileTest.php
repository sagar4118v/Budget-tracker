<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../profile_functions.php';

class UserProfileTest extends TestCase
{
    public function testSavingCalculation()
    {
        $saving = calculateSaving(
            10000,
            4000
        );

        $this->assertEquals(
            6000,
            $saving
        );
    }
}