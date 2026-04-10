<?php
// test_db.php
// Test database connection

require_once 'config/database.php';

echo "<h1 style='color: #028090; text-align: center;'>PwanDeal Database Connection Test</h1>";

// Check connection
if ($conn->connect_error) {
    echo "<div style='color: red; text-align: center; font-size: 18px;'>";
    echo "❌ Connection failed: " . $conn->connect_error;
    echo "</div>";
    exit();
}

echo "<div style='color: green; text-align: center; font-size: 18px; margin: 20px 0;'>";
echo "✅ Connected to database: <strong>" . DB_NAME . "</strong>";
echo "</div>";

// Get table count
$sql = "SELECT COUNT(*) as table_count FROM information_schema.tables WHERE table_schema='" . DB_NAME . "'";
$result = $conn->query($sql);
$row = $result->fetch_assoc();

echo "<div style='text-align: center; font-size: 16px; margin: 20px 0;'>";
echo "✅ Number of tables: <strong>" . $row['table_count'] . "</strong>";
echo "</div>";

// List all tables
echo "<h3 style='text-align: center; color: #1e2761;'>Database Tables</h3>";
echo "<div style='max-width: 600px; margin: 0 auto;'>";

$sql = "SELECT TABLE_NAME FROM information_schema.TABLES WHERE TABLE_SCHEMA='" . DB_NAME . "'";
$result = $conn->query($sql);

echo "<ol style='font-size: 16px;'>";
while ($table = $result->fetch_assoc()) {
    echo "<li>" . $table['TABLE_NAME'] . "</li>";
}
echo "</ol>";

echo "</div>";

echo "<div style='text-align: center; margin-top: 30px;'>";
echo "<a href='index.php' style='padding: 10px 20px; background: #028090; color: white; text-decoration: none; border-radius: 5px; font-size: 16px;'>Go to Home Page</a>";
echo "</div>";

$conn->close();
?>
