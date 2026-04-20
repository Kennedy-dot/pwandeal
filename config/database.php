<?php
/**
 * PwanDeal - Pro Database Configuration
 * Refined for Scalability and Security
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 1. Configuration Constants
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'pwandeal_db');

// 2. Strict Error Reporting
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    // 3. Persistent Connection
    // Prepending 'p:' to the host creates a persistent connection, 
    // which is faster for high-traffic student portals.
    $conn = new mysqli('p:'.DB_HOST, DB_USER, DB_PASS, DB_NAME);

    // 4. Optimization Settings
    $conn->set_charset("utf8mb4");
    $conn->query("SET time_zone = '+03:00'");
    date_default_timezone_set('Africa/Nairobi');

} catch (mysqli_sql_exception $e) {
    error_log("PwanDeal DB Error: " . $e->getMessage());
    // Friendly error for students, actual error for the logs
    die("PwanDeal is under maintenance. Please check back in a few minutes.");
}

/**
 * Enhanced Output Escaping (Shortened for ease of use)
 */
function e($string) {
    return htmlspecialchars($string ?? '', ENT_QUOTES, 'UTF-8');
}

/**
 * The "Swiss Army Knife" Query Helper
 * Handles SELECT, INSERT, UPDATE, and DELETE safely.
 */
function p_query($sql, $params = []) {
    global $conn;
    $stmt = $conn->prepare($sql);
    
    if (!empty($params)) {
        // Dynamically build the types string (e.g., "issi")
        $types = "";
        foreach ($params as $param) {
            if (is_int($param)) $types .= "i";
            elseif (is_double($param)) $types .= "d";
            else $types .= "s";
        }
        $stmt->bind_param($types, ...$params);
    }
    
    $stmt->execute();
    
    // If it's a SELECT query, return the result set
    $result = $stmt->get_result();
    if ($result) return $result;
    
    // For INSERT/UPDATE/DELETE, return the statement object (to check affected_rows)
    return $stmt;
}