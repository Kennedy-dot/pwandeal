<?php
/**
 * PwanDeal - Database Configuration
 */

// 1. Database Credentials
// Consider moving these to an .env file for high-level security later
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'pwandeal_db');

// 2. Error Reporting
// Enable this to catch errors during development. 
// It will throw an exception instead of a silent error.
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    // 3. Create Connection
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

    // 4. Set Charset
    $conn->set_charset("utf8mb4");

    // 5. Set Timezone (Crucial for Kilifi/Kenya time consistency)
    $conn->query("SET time_zone = '+03:00'");
    date_default_timezone_set('Africa/Nairobi');

} catch (mysqli_sql_exception $e) {
    // In production, you'd log this to a file and show a friendly message
    error_log($e->getMessage());
    die("PwanDeal is temporarily unavailable. Please try again shortly.");
}

/**
 * Global Helper for cleaning input 
 * (Prevents basic XSS when echoing data)
 */
function e($string) {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}
?>