<?php
session_start();
require_once 'config/database.php';

echo "<h1>🔧 Password Reset Test</h1>";
echo "<hr>";

// Check columns exist
echo "<h2>Step 1: Check Database Columns</h2>";
$columns_result = $conn->query("SHOW COLUMNS FROM users");
$columns = [];
while ($row = $columns_result->fetch_assoc()) {
    $columns[] = $row['Field'];
}

echo "Columns in users table:<br>";
if (in_array('reset_token', $columns)) {
    echo "✅ reset_token EXISTS<br>";
} else {
    echo "❌ reset_token MISSING - NEED TO ADD IT<br>";
}

if (in_array('reset_token_expiry', $columns)) {
    echo "✅ reset_token_expiry EXISTS<br>";
} else {
    echo "❌ reset_token_expiry MISSING - NEED TO ADD IT<br>";
}

echo "<hr>";

// Test user
echo "<h2>Step 2: Check Your User</h2>";
$email = 'kennedymusyoka2002@pwani.ac.ke';
$stmt = $conn->prepare('SELECT user_id, email, reset_token, reset_token_expiry FROM users WHERE email = ?');
$stmt->bind_param('s', $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "❌ User not found!<br>";
} else {
    $user = $result->fetch_assoc();
    echo "✅ User found!<br>";
    echo "User ID: " . $user['user_id'] . "<br>";
    echo "Email: " . $user['email'] . "<br>";
    echo "Current reset_token: " . ($user['reset_token'] ?? "NULL") . "<br>";
    echo "Current reset_token_expiry: " . ($user['reset_token_expiry'] ?? "NULL") . "<br>";
    
    echo "<hr>";
    echo "<h2>Step 3: Generate New Token</h2>";
    
    if (isset($user['user_id'])) {
        $new_token = bin2hex(random_bytes(32));
        $expiry = date('Y-m-d H:i:s', strtotime('+1 hour'));
        
        echo "New token generated: <code>$new_token</code><br>";
        echo "Expiry: <code>$expiry</code><br>";
        
        // Store in database
        $stmt = $conn->prepare('UPDATE users SET reset_token = ?, reset_token_expiry = ? WHERE user_id = ?');
        $stmt->bind_param('ssi', $new_token, $expiry, $user['user_id']);
        
        if ($stmt->execute()) {
            echo "✅ Token saved to database!<br>";
            echo "<hr>";
            echo "<h2>Step 4: Test Reset Link</h2>";
            echo "Click this link to reset your password:<br>";
            echo "<a href='auth/reset-password.php?token=" . $new_token . "' style='color: blue; text-decoration: underline;'>";
            echo "http://localhost/pwandeal/auth/reset-password.php?token=" . $new_token . "</a><br>";
        } else {
            echo "❌ Failed to save token to database!<br>";
            echo "Error: " . $stmt->error . "<br>";
        }
    }
}

echo "<hr>";
echo "<h2>Step 5: Debug Token Check</h2>";

if (isset($_GET['debug_token'])) {
    $debug_token = $_GET['debug_token'];
    echo "Checking token: <code>$debug_token</code><br>";
    
    $stmt = $conn->prepare('SELECT user_id FROM users WHERE reset_token = ? AND reset_token_expiry > NOW()');
    $stmt->bind_param('s', $debug_token);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        echo "✅ Token is VALID!<br>";
    } else {
        echo "❌ Token is INVALID or EXPIRED<br>";
        
        // Check if token exists at all
        $stmt = $conn->prepare('SELECT user_id, reset_token_expiry FROM users WHERE reset_token = ?');
        $stmt->bind_param('s', $debug_token);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            echo "Token exists but is expired<br>";
            echo "Expiry time: " . $row['reset_token_expiry'] . "<br>";
            echo "Current time: " . date('Y-m-d H:i:s') . "<br>";
        } else {
            echo "Token doesn't exist in database at all<br>";
        }
    }
}

?>