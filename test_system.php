<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<!DOCTYPE html>
<html>
<head>
    <title>System Test - RentACar</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 800px; margin: 50px auto; padding: 20px; }
        .success { color: green; background: #dfd; padding: 10px; border-radius: 5px; margin: 10px 0; }
        .error { color: red; background: #fdd; padding: 10px; border-radius: 5px; margin: 10px 0; }
        .info { color: blue; background: #ddf; padding: 10px; border-radius: 5px; margin: 10px 0; }
        .btn { background: #667eea; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; display: inline-block; margin: 10px 5px; }
        table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
    </style>
</head>
<body>";

echo "<h1>🧪 RentACar System Test</h1>";

try {
    require_once 'db.php';
    echo "<div class='success'>✅ Database connection successful</div>";
    
    // Test cars table
    echo "<h3>🚗 Testing Cars Table</h3>";
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM cars");
    $carCount = $stmt->fetch()['count'];
    echo "<div class='success'>✅ Cars table exists with $carCount records</div>";
    
    // Show sample cars
    $stmt = $pdo->query("SELECT brand, model, car_type, price_per_day, location FROM cars LIMIT 5");
    $cars = $stmt->fetchAll();
    
    if ($cars) {
        echo "<table>";
        echo "<tr><th>Brand</th><th>Model</th><th>Type</th><th>Price/Day</th><th>Location</th></tr>";
        foreach ($cars as $car) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($car['brand']) . "</td>";
            echo "<td>" . htmlspecialchars($car['model']) . "</td>";
            echo "<td>" . htmlspecialchars($car['car_type']) . "</td>";
            echo "<td>$" . number_format($car['price_per_day'], 2) . "</td>";
            echo "<td>" . htmlspecialchars($car['location']) . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
    // Test locations table
    echo "<h3>📍 Testing Locations Table</h3>";
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM locations");
    $locationCount = $stmt->fetch()['count'];
    echo "<div class='success'>✅ Locations table exists with $locationCount records</div>";
    
    // Test bookings table
    echo "<h3>📋 Testing Bookings Table</h3>";
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM bookings");
    $bookingCount = $stmt->fetch()['count'];
    echo "<div class='success'>✅ Bookings table exists with $bookingCount records</div>";
    
    // Test search functionality
    echo "<h3>🔍 Testing Search Functionality</h3>";
    $stmt = $pdo->prepare("SELECT * FROM cars WHERE available = 1 AND location = ? LIMIT 3");
    $stmt->execute(['New York']);
    $searchResults = $stmt->fetchAll();
    echo "<div class='success'>✅ Search functionality works - Found " . count($searchResults) . " cars in New York</div>";
    
    echo "<h2 style='color: green;'>🎉 All Tests Passed!</h2>";
    echo "<p>Your RentACar system is working perfectly!</p>";
    
    echo "<div style='text-align: center; margin: 30px 0;'>";
    echo "<a href='index.php' class='btn'>🏠 Go to Homepage</a>";
    echo "<a href='cars.php' class='btn'>🚗 Search Cars</a>";
    echo "</div>";
    
} catch (Exception $e) {
    echo "<div class='error'>";
    echo "<h3>❌ Test Failed</h3>";
    echo "<p>" . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p><a href='create_tables.php' class='btn'>🔧 Run Database Setup</a></p>";
    echo "</div>";
}

echo "</body></html>";
?>
