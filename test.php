<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>PwanDeal Diagnostic Test</h1>";
echo "<hr>";

// Test 1: PHP Working
echo "<h2>1. PHP Working?</h2>";
echo "✅ PHP is working!<br>";

// Test 2: Session
echo "<h2>2. Sessions Working?</h2>";
session_start();
echo "✅ Sessions OK<br>";

// Test 3: Database Connection
echo "<h2>3. Database Connection?</h2>";
require_once 'config/database.php';
if ($conn && !$conn->connect_error) {
    echo "✅ Database Connected!<br>";
} else {
    echo "❌ Database Error: " . ($conn->connect_error ?? "Unknown") . "<br>";
}

// Test 4: Header Include
echo "<h2>4. Header Inclusion?</h2>";
$page_title = "Test";
$base_url = '';

// Temporarily suppress header output
ob_start();
include 'includes/header.php';
ob_end_clean();
echo "✅ Header included successfully<br>";

// Test 5: Check files exist
echo "<h2>5. Required Files Exist?</h2>";
echo file_exists('config/database.php') ? "✅ config/database.php" : "❌ config/database.php" . "<br>";
echo file_exists('includes/header.php') ? "✅ includes/header.php" : "❌ includes/header.php" . "<br>";
echo file_exists('includes/footer.php') ? "✅ includes/footer.php" : "❌ includes/footer.php" . "<br>";
echo file_exists('auth/login.php') ? "✅ auth/login.php" : "❌ auth/login.php" . "<br>";
echo file_exists('auth/register.php') ? "✅ auth/register.php" : "❌ auth/register.php" . "<br>";

echo "<hr>";
echo "<h2>Result: Check above for any ❌ marks</h2>";
?>