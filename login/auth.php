<?php
declare(strict_types=1);

/**
 * ============================================================
 * Authentication Module
 * ============================================================
 *
 * PURPOSE:
 * Handles session management and access control.
 *
 * RESPONSIBILITIES:
 * - Start session safely
 * - Verify user login state
 * - Protect routes
 * - Handle logout
 * ============================================================
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Check if user is authenticated
 */
function isLoggedIn(): bool {
    return isset($_SESSION['user_id']);
}

/**
 * Protect pages from unauthorized access
 */
function requireLogin(): void {
    if (!isLoggedIn()) {
        header("Location: login.php");
        exit;
    }
}

/**
 * Destroy session and log out user
 */
function logout(): void {
    session_unset();
    session_destroy();
    header("Location: login.php");
    exit;
}