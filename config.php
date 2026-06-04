<?php
declare(strict_types=1);

/**
 * ============================================================
 * Database Configuration
 * ============================================================
 *
 * PURPOSE:
 * Establish a secure PDO connection to MySQL database.
 *
 * FEATURES:
 * - Exception-based error handling
 * - UTF-8 encoding
 * - Centralized connection reuse
 * ============================================================
 */

$host = "localhost";
$db   = "budget_tracker";
$user = "root";
$pass = "";

try {
    $conn = new PDO(
        "mysql:host=$host;dbname=$db;charset=utf8mb4",
        $user,
        $pass
    );

    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

} catch (PDOException $e) {
    die("Database Connection Failed: " . $e->getMessage());
}