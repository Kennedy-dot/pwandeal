<?php
/**
 * PwanDeal - Optimized Database Configuration
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 1. Database Credentials
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'pwandeal_db');

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    $conn->set_charset("utf8mb4");

    // Sync DB and PHP time with EAT (East Africa Time)
    $conn->query("SET time_zone = '+03:00'");
    date_default_timezone_set('Africa/Nairobi');

} catch (mysqli_sql_exception $e) {
    error_log("Connection Error: " . $e->getMessage());
    // Friendly message for the students
    die("PwanDeal is currently resting. We'll be back in a moment!");
}

/**
 * Enhanced Helper: Clean output for XSS protection
 */
function e($string) {
    return htmlspecialchars($string ?? '', ENT_QUOTES, 'UTF-8');
}

/**
 * Quick Query Helper: Simplifies prepared statements
 * Usage: $user = p_query("SELECT * FROM users WHERE id = ?", "i", [$id])->fetch_assoc();
 */
function p_query($sql, $types = null, $params = []) {
    global $conn;
    $stmt = $conn->prepare($sql);
    if ($types && $params) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    return $stmt->get_result();
}