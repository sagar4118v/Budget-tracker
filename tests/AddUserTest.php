<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../user_functions.php';

class AddUserTest extends TestCase
{
    private PDO $conn;

    protected function setUp(): void
    {
        global $conn;

        $this->conn = $conn;
    }

    public function testEmailExistsReturnsFalse()
    {
        $email = "notfound@gmail.com";

        $result = emailExists(
            $this->conn,
            $email
        );

        $this->assertFalse($result);
    }

    public function testCreateUser()
    {
        $randomEmail =
            "test" . rand(1000,9999) . "@gmail.com";

        $result = createUser(
            $this->conn,
            "Test User",
            $randomEmail,
            "9800000000",
            "123456",
            "user"
        );

        $this->assertTrue($result);
    }
}