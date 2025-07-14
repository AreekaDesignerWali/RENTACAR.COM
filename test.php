<?php
// Test file to check database connection and tables
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>RentACar System Test</h2>";

// Test 1: PHP Version
echo "<h3>1. PHP Version Check</h3>";
echo "PHP Version: " . phpversion() . "<br>";
if (version_compare(phpversion(), '7.0.0', '>=')) {
    echo "<span style='color: green;'>✓ PHP version is compatible</span><br>";
} else {
    echo "<span style='color: red;'>✗ PHP version too old</span><br>";
}

// Test 2: Database Connection
echo "<h3>2. Database Connection Test</h3>";
try {
    require_once 'db.php';
    echo "<span style='color: green;'>✓ Database connection successful</span><br>";
} catch (Exception $e) {
    echo "<span style='color: red;'>✗ Database connection failed: " . $e->getMessage() . "</span><br>";
    exit;
}

// Test 3: Check if tables exist
echo "<h3>3. Database Tables Check</h3>";
$tables = ['cars', 'bookings', 'locations'];
foreach ($tables as $table) {
    try {
        $stmt = $pdo->query("SELECT COUNT(*) FROM $table");
        $count = $stmt->fetchColumn();
        echo "<span style='color: green;'>✓ Table '$table' exists with $count records</span><br>";
    } catch (Exception $e) {
        echo "<span style='color: red;'>✗ Table '$table' missing or error: " . $e->getMessage() . "</span><br>";
    }
}

echo "<h3>4. File Permissions Check</h3>";
$files = ['index.php', 'cars.php', 'booking.php', 'confirmation.php'];
foreach ($files as $file) {
    if (file_exists($file)) {
        echo "<span style='color: green;'>✓ File '$file' exists</span><br>";
    } else {
        echo "<span style='color: red;'>✗ File '$file' missing</span><br>";
    }
}

echo "<h3>Test Complete!</h3>";
echo "<p>If all tests pass, your system should work. If not, fix the issues above.</p>";
?>
