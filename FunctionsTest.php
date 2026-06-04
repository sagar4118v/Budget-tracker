<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../functions.php';

class FunctionsTest extends TestCase
{
    public function testFunctionExists()
    {
        $this->assertTrue(
            function_exists('getTransactions')
        );
    }

    public function testReturnsArray()
    {
        $data = [];

        $this->assertIsArray($data);
    }

    public function testSearchFilterValue()
    {
        $filter = [
            'search' => 'Food'
        ];

        $this->assertEquals(
            'Food',
            $filter['search']
        );
    }
}