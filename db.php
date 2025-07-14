<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database configuration
$servername = "localhost";
$username = "uc7ggok7oyoza";
$password = "gqypavorhbbc";
$dbname = "dbidpzzbqibczw";

try {
    // Create PDO connection
    $pdo = new PDO("mysql:host=$servername;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    
    // Test the connection
    $pdo->query("SELECT 1");
    
} catch(PDOException $e) {
    // Log the error and show user-friendly message
    error_log("Database connection failed: " . $e->getMessage());
    
    // For debugging - remove this in production
    die("
    <div style='font-family: Arial; padding: 20px; background: #fee; border: 1px solid #fcc; border-radius: 5px; margin: 20px;'>
        <h3 style='color: #c33;'>Database Connection Error</h3>
        <p><strong>Error:</strong> " . htmlspecialchars($e->getMessage()) . "</p>
        <p><strong>Please check:</strong></p>
        <ul>
            <li>Database credentials are correct</li>
            <li>Database server is running</li>
            <li>Database exists</li>
            <li>Tables are created</li>
        </ul>
    </div>
    ");
}
?>
