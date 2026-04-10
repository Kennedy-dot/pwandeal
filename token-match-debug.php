<?php
session_start();
require_once 'config/database.php';

$url_token = trim($_GET['token'] ?? '');

echo "<h1>🔍 Token Match Debug</h1>";
echo "<hr>";

// Show URL token
echo "<h2>URL Token</h2>";
echo "<code>" . $url_token . "</code><br>";
echo "Length: " . strlen($url_token) . "<br>";
echo "Hex: " . bin2hex($url_token) . "<br>";

echo "<hr>";

// Show database tokens
echo "<h2>Database Tokens</h2>";
$result = $conn->query("SELECT user_id, reset_token, reset_token_expiry, LENGTH(reset_token) as token_length FROM users WHERE reset_token IS NOT NULL");

if ($result->num_rows === 0) {
    echo "No tokens in database<br>";
} else {
    while ($row = $result->fetch_assoc()) {
        echo "User ID: " . $row['user_id'] . "<br>";
        echo "Token: <code>" . $row['reset_token'] . "</code><br>";
        echo "Length: " . $row['token_length'] . "<br>";
        echo "Expiry: " . $row['reset_token_expiry'] . "<br>";
        echo "Current time: " . date('Y-m-d H:i:s') . "<br>";
        
        echo "<hr>";
        
        // Compare
        if ($url_token === $row['reset_token']) {
            echo "✅ TOKENS MATCH!<br>";
        } else {
            echo "❌ TOKENS DON'T MATCH<br>";
            echo "URL Token: " . $url_token . "<br>";
            echo "DB Token:  " . $row['reset_token'] . "<br>";
        }
        
        echo "<hr>";
    }
}

// Test the exact query
echo "<h2>Test Reset Query</h2>";
echo "Token to test: <code>$url_token</code><br>";

$stmt = $conn->prepare('SELECT user_id FROM users WHERE reset_token = ? AND reset_token_expiry > NOW()');
if (!$stmt) {
    echo "❌ Prepare failed: " . $conn->error . "<br>";
} else {
    $stmt->bind_param('s', $url_token);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        echo "✅ Query PASSED<br>";
        $row = $result->fetch_assoc();
        echo "Found user_id: " . $row['user_id'] . "<br>";
    } else {
        echo "❌ Query FAILED - No results<br>";
        
        // Try without expiry
        $stmt2 = $conn->prepare('SELECT user_id FROM users WHERE reset_token = ?');
        $stmt2->bind_param('s', $url_token);
        $stmt2->execute();
        $result2 = $stmt2->get_result();
        
        if ($result2->num_rows > 0) {
            echo "Token exists but expiry check failed<br>";
            $row2 = $result2->fetch_assoc();
            
            // Check expiry time
            $expiry_result = $conn->query("SELECT reset_token_expiry FROM users WHERE user_id = " . $row2['user_id']);
            $expiry_row = $expiry_result->fetch_assoc();
            echo "Expiry time in DB: " . $expiry_row['reset_token_expiry'] . "<br>";
            echo "Current time: " . date('Y-m-d H:i:s') . "<br>";
        } else {
            echo "Token not found in database at all<br>";
        }
    }
}

?>