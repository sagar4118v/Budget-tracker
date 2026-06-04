<?php

use PHPUnit\Framework\TestCase;

class EditUserTest extends TestCase
{
    public function testTrimmedName()
    {
        $name = trim(" Mohan ");

        $this->assertEquals(
            "Mohan",
            $name
        );
    }
}