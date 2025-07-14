<?php
// Turn on error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<!DOCTYPE html>
<html>
<head>
    <title>Database Setup - RentACar</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 800px; margin: 50px auto; padding: 20px; }
        .success { color: green; background: #dfd; padding: 10px; border-radius: 5px; margin: 10px 0; }
        .error { color: red; background: #fdd; padding: 10px; border-radius: 5px; margin: 10px 0; }
        .info { color: blue; background: #ddf; padding: 10px; border-radius: 5px; margin: 10px 0; }
        .btn { background: #667eea; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; display: inline-block; margin: 10px 5px; }
    </style>
</head>
<body>";

echo "<h1>🚗 RentACar Database Setup</h1>";

// Database credentials
$servername = "localhost";
$username = "uc7ggok7oyoza";
$password = "gqypavorhbbc";
$dbname = "dbidpzzbqibczw";

try {
    // Connect to database
    echo "<div class='info'>📡 Connecting to database...</div>";
    $pdo = new PDO("mysql:host=$servername;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "<div class='success'>✅ Database connection successful!</div>";
    
    // Drop existing tables to start fresh
    echo "<div class='info'>🗑️ Removing old tables (if any)...</div>";
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 0");
    $pdo->exec("DROP TABLE IF EXISTS bookings");
    $pdo->exec("DROP TABLE IF EXISTS cars");
    $pdo->exec("DROP TABLE IF EXISTS locations");
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");
    echo "<div class='success'>✅ Old tables removed</div>";
    
    // Create locations table first
    echo "<div class='info'>📍 Creating locations table...</div>";
    $sql = "CREATE TABLE locations (
        id INT AUTO_INCREMENT PRIMARY KEY,
        city VARCHAR(100) NOT NULL,
        country VARCHAR(100) NOT NULL,
        airport_code VARCHAR(10),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
    $pdo->exec($sql);
    echo "<div class='success'>✅ Locations table created</div>";
    
    // Create cars table
    echo "<div class='info'>🚗 Creating cars table...</div>";
    $sql = "CREATE TABLE cars (
        id INT AUTO_INCREMENT PRIMARY KEY,
        brand VARCHAR(100) NOT NULL,
        model VARCHAR(100) NOT NULL,
        year INT NOT NULL,
        car_type VARCHAR(50) NOT NULL,
        fuel_type VARCHAR(50) NOT NULL,
        transmission VARCHAR(50) NOT NULL,
        seats INT NOT NULL,
        price_per_day DECIMAL(10,2) NOT NULL,
        image_url TEXT NOT NULL,
        features TEXT,
        location VARCHAR(100) NOT NULL,
        available TINYINT(1) DEFAULT 1,
        rating DECIMAL(2,1) DEFAULT 4.5,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
    $pdo->exec($sql);
    echo "<div class='success'>✅ Cars table created</div>";
    
    // Create bookings table
    echo "<div class='info'>📋 Creating bookings table...</div>";
    $sql = "CREATE TABLE bookings (
        id INT AUTO_INCREMENT PRIMARY KEY,
        car_id INT NOT NULL,
        customer_name VARCHAR(200) NOT NULL,
        customer_email VARCHAR(200) NOT NULL,
        customer_phone VARCHAR(50) NOT NULL,
        pickup_location VARCHAR(200) NOT NULL,
        pickup_date DATE NOT NULL,
        return_date DATE NOT NULL,
        total_days INT NOT NULL,
        total_amount DECIMAL(10,2) NOT NULL,
        booking_status VARCHAR(50) DEFAULT 'Pending',
        special_requests TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_car_id (car_id),
        INDEX idx_pickup_date (pickup_date),
        FOREIGN KEY (car_id) REFERENCES cars(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
    $pdo->exec($sql);
    echo "<div class='success'>✅ Bookings table created</div>";
    
    // Insert sample locations
    echo "<div class='info'>📍 Adding sample locations...</div>";
    $locations = [
        ['New York', 'USA', 'JFK'],
        ['Los Angeles', 'USA', 'LAX'],
        ['Miami', 'USA', 'MIA'],
        ['Chicago', 'USA', 'ORD'],
        ['Dallas', 'USA', 'DFW'],
        ['Seattle', 'USA', 'SEA'],
        ['San Francisco', 'USA', 'SFO'],
        ['Phoenix', 'USA', 'PHX'],
        ['Austin', 'USA', 'AUS'],
        ['Denver', 'USA', 'DEN'],
        ['Las Vegas', 'USA', 'LAS'],
        ['Orlando', 'USA', 'MCO']
    ];
    
    $stmt = $pdo->prepare("INSERT INTO locations (city, country, airport_code) VALUES (?, ?, ?)");
    foreach ($locations as $location) {
        $stmt->execute($location);
    }
    echo "<div class='success'>✅ " . count($locations) . " locations added</div>";
    
    // Insert sample cars
    echo "<div class='info'>🚗 Adding sample cars...</div>";
    $cars = [
        ['Toyota', 'Corolla', 2023, 'Compact', 'Petrol', 'Automatic', 5, 45.00, 'https://images.unsplash.com/photo-1621007947382-bb3c3994e3fb?w=400&h=300&fit=crop', 'Air Conditioning, Bluetooth, GPS Navigation, USB Charging', 'New York', 1, 4.8],
        ['Honda', 'Civic', 2023, 'Compact', 'Petrol', 'Manual', 5, 42.00, 'https://images.unsplash.com/photo-1606664515524-ed2f786a0bd6?w=400&h=300&fit=crop', 'Air Conditioning, Bluetooth, Backup Camera', 'Los Angeles', 1, 4.7],
        ['BMW', 'X3', 2023, 'SUV', 'Petrol', 'Automatic', 5, 85.00, 'https://images.unsplash.com/photo-1555215695-3004980ad54e?w=400&h=300&fit=crop', 'Leather Seats, Sunroof, Premium Sound, GPS Navigation', 'Miami', 1, 4.9],
        ['Mercedes-Benz', 'C-Class', 2023, 'Luxury', 'Petrol', 'Automatic', 5, 95.00, 'https://images.unsplash.com/photo-1618843479313-40f8afb4b4d8?w=400&h=300&fit=crop', 'Leather Interior, Premium Sound, GPS, Heated Seats', 'Chicago', 1, 4.8],
        ['Ford', 'Explorer', 2023, 'SUV', 'Petrol', 'Automatic', 7, 75.00, 'https://images.unsplash.com/photo-1544636331-e26879cd4d9b?w=400&h=300&fit=crop', '7 Seater, Air Conditioning, Bluetooth, Large Trunk', 'Dallas', 1, 4.6],
        ['Nissan', 'Altima', 2023, 'Mid-size', 'Petrol', 'Automatic', 5, 50.00, 'https://images.unsplash.com/photo-1605559424843-9e4c228bf1c2?w=400&h=300&fit=crop', 'Air Conditioning, Bluetooth, Backup Camera, USB Ports', 'Seattle', 1, 4.5],
        ['Audi', 'A4', 2023, 'Premium', 'Petrol', 'Automatic', 5, 80.00, 'https://images.unsplash.com/photo-1606664515524-ed2f786a0bd6?w=400&h=300&fit=crop', 'Premium Interior, GPS Navigation, Heated Seats, Sunroof', 'San Francisco', 1, 4.7],
        ['Chevrolet', 'Malibu', 2023, 'Mid-size', 'Petrol', 'Automatic', 5, 48.00, 'https://images.unsplash.com/photo-1552519507-da3b142c6e3d?w=400&h=300&fit=crop', 'Air Conditioning, Bluetooth, Apple CarPlay, Android Auto', 'Phoenix', 1, 4.4],
        ['Tesla', 'Model 3', 2023, 'Premium', 'Electric', 'Automatic', 5, 90.00, 'https://images.unsplash.com/photo-1560958089-b8a1929cea89?w=400&h=300&fit=crop', 'Electric Vehicle, Autopilot, Premium Interior, Supercharging', 'Austin', 1, 4.9],
        ['Hyundai', 'Elantra', 2023, 'Compact', 'Petrol', 'Automatic', 5, 40.00, 'https://images.unsplash.com/photo-1605559424843-9e4c228bf1c2?w=400&h=300&fit=crop', 'Air Conditioning, Bluetooth, Backup Camera, Fuel Efficient', 'Denver', 1, 4.3],
        ['Jeep', 'Grand Cherokee', 2023, 'SUV', 'Petrol', 'Automatic', 5, 78.00, 'https://images.unsplash.com/photo-1544636331-e26879cd4d9b?w=400&h=300&fit=crop', '4WD, Leather Seats, Premium Sound, Towing Capacity', 'Las Vegas', 1, 4.6],
        ['Volkswagen', 'Jetta', 2023, 'Compact', 'Petrol', 'Automatic', 5, 44.00, 'https://images.unsplash.com/photo-1621007947382-bb3c3994e3fb?w=400&h=300&fit=crop', 'Fuel Efficient, Bluetooth, Safety Features, Comfortable', 'Orlando', 1, 4.4]
    ];
    
    $stmt = $pdo->prepare("INSERT INTO cars (brand, model, year, car_type, fuel_type, transmission, seats, price_per_day, image_url, features, location, available, rating) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    
    foreach ($cars as $car) {
        $stmt->execute($car);
    }
    echo "<div class='success'>✅ " . count($cars) . " cars added</div>";
    
    // Verify data
    echo "<div class='info'>🔍 Verifying data...</div>";
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM cars");
    $carCount = $stmt->fetch()['count'];
    
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM locations");
    $locationCount = $stmt->fetch()['count'];
    
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM bookings");
    $bookingCount = $stmt->fetch()['count'];
    
    echo "<div class='success'>✅ Database verification complete:</div>";
    echo "<ul>";
    echo "<li>Cars: $carCount records</li>";
    echo "<li>Locations: $locationCount records</li>";
    echo "<li>Bookings: $bookingCount records</li>";
    echo "</ul>";
    
    echo "<h2 style='color: green;'>🎉 Setup Complete!</h2>";
    echo "<p><strong>Your RentACar database is now ready to use!</strong></p>";
    
    echo "<div style='text-align: center; margin: 30px 0;'>";
    echo "<a href='index.php' class='btn'>🏠 Go to Homepage</a>";
    echo "<a href='cars.php' class='btn'>🚗 View All Cars</a>";
    echo "<a href='test_system.php' class='btn'>🧪 Test System</a>";
    echo "</div>";
    
} catch (PDOException $e) {
    echo "<div class='error'>";
    echo "<h3>❌ Database Error</h3>";
    echo "<p><strong>Error:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p><strong>Please check:</strong></p>";
    echo "<ul>";
    echo "<li>Database name: $dbname</li>";
    echo "<li>Username: $username</li>";
    echo "<li>Password: [hidden]</li>";
    echo "<li>Make sure the database exists in your hosting control panel</li>";
    echo "</ul>";
    echo "</div>";
} catch (Exception $e) {
    echo "<div class='error'>";
    echo "<h3>❌ General Error</h3>";
    echo "<p>" . htmlspecialchars($e->getMessage()) . "</p>";
    echo "</div>";
}

echo "</body></html>";
?>
