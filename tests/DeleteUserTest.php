<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../delete_functions.php';

class DeleteUserTest extends TestCase
{
    public function testValidUserId()
    {
        $this->assertTrue(
            validateUserId(5)
        );
    }

    public function testInvalidUserId()
    {
        $this->assertFalse(
            validateUserId(0)
        );
    }
}