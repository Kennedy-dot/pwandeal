<?php
session_start();
require_once 'config/database.php';

$token = $_GET['token'] ?? '';

echo "<h1>🔍 Token Debug</h1>";
echo "<hr>";

if (empty($token)) {
    echo "❌ No token provided in URL<br>";
    exit;
}

echo "<h2>Token from URL</h2>";
echo "Token: <code>$token</code><br>";
echo "Length: " . strlen($token) . " characters<br>";

echo "<hr>";
echo "<h2>Check Database</h2>";

// Check if token exists
$stmt = $conn->prepare('SELECT user_id, reset_token, reset_token_expiry FROM users WHERE reset_token = ?');
$stmt->bind_param('s', $token);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "❌ Token NOT FOUND in database<br>";
    
    // Show all tokens in database
    echo "<br>Tokens in database:<br>";
    $all = $conn->query('SELECT user_id, reset_token, reset_token_expiry FROM users WHERE reset_token IS NOT NULL');
    while ($row = $all->fetch_assoc()) {
        echo "User " . $row['user_id'] . ": " . substr($row['reset_token'], 0, 20) . "... | Expiry: " . $row['reset_token_expiry'] . "<br>";
    }
} else {
    $user = $result->fetch_assoc();
    echo "✅ Token FOUND in database!<br>";
    echo "User ID: " . $user['user_id'] . "<br>";
    echo "Token in DB: <code>" . $user['reset_token'] . "</code><br>";
    echo "Expiry: " . $user['reset_token_expiry'] . "<br>";
    echo "Current time: " . date('Y-m-d H:i:s') . "<br>";
    
    echo "<hr>";
    echo "<h2>Check Expiry</h2>";
    
    // Check expiry time
    $expiry_time = strtotime($user['reset_token_expiry']);
    $current_time = time();
    
    echo "Expiry timestamp: $expiry_time<br>";
    echo "Current timestamp: $current_time<br>";
    
    if ($expiry_time > $current_time) {
        echo "✅ Token is NOT EXPIRED<br>";
        echo "Time remaining: " . ($expiry_time - $current_time) . " seconds<br>";
    } else {
        echo "❌ Token IS EXPIRED<br>";
        echo "Expired " . ($current_time - $expiry_time) . " seconds ago<br>";
    }
    
    echo "<hr>";
    echo "<h2>Test Query</h2>";
    
    // Test the exact query from reset-password.php
    $stmt2 = $conn->prepare('SELECT user_id FROM users WHERE reset_token = ? AND reset_token_expiry > NOW()');
    $stmt2->bind_param('s', $token);
    $stmt2->execute();
    $result2 = $stmt2->get_result();
    
    if ($result2->num_rows > 0) {
        echo "✅ Query PASSED (should see reset form)<br>";
    } else {
        echo "❌ Query FAILED (this is the problem!)<br>";
        
        // Try without expiry check
        $stmt3 = $conn->prepare('SELECT user_id FROM users WHERE reset_token = ?');
        $stmt3->bind_param('s', $token);
        $stmt3->execute();
        $result3 = $stmt3->get_result();
        
        if ($result3->num_rows > 0) {
            echo "Token exists but expiry check failed<br>";
        }
    }
}

?>