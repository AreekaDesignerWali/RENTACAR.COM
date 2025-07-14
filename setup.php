<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>RentACar Database Setup</h2>";

try {
    require_once 'db.php';
    
    // Create cars table
    $sql = "CREATE TABLE IF NOT EXISTS cars (
        id INT AUTO_INCREMENT PRIMARY KEY,
        brand VARCHAR(50) NOT NULL,
        model VARCHAR(50) NOT NULL,
        year INT NOT NULL,
        car_type ENUM('Economy', 'Compact', 'Mid-size', 'Full-size', 'Premium', 'Luxury', 'SUV', 'Van') NOT NULL,
        fuel_type ENUM('Petrol', 'Diesel', 'Electric', 'Hybrid') NOT NULL,
        transmission ENUM('Manual', 'Automatic') NOT NULL,
        seats INT NOT NULL,
        price_per_day DECIMAL(10,2) NOT NULL,
        image_url VARCHAR(255) NOT NULL,
        features TEXT,
        location VARCHAR(100) NOT NULL,
        available BOOLEAN DEFAULT TRUE,
        rating DECIMAL(2,1) DEFAULT 4.5,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    $pdo->exec($sql);
    echo "<p style='color: green;'>✓ Cars table created successfully</p>";
    
    // Create bookings table
    $sql = "CREATE TABLE IF NOT EXISTS bookings (
        id INT AUTO_INCREMENT PRIMARY KEY,
        car_id INT NOT NULL,
        customer_name VARCHAR(100) NOT NULL,
        customer_email VARCHAR(100) NOT NULL,
        customer_phone VARCHAR(20) NOT NULL,
        pickup_location VARCHAR(100) NOT NULL,
        pickup_date DATE NOT NULL,
        return_date DATE NOT NULL,
        total_days INT NOT NULL,
        total_amount DECIMAL(10,2) NOT NULL,
        booking_status ENUM('Pending', 'Confirmed', 'Cancelled', 'Completed') DEFAULT 'Pending',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    $pdo->exec($sql);
    echo "<p style='color: green;'>✓ Bookings table created successfully</p>";
    
    // Create locations table
    $sql = "CREATE TABLE IF NOT EXISTS locations (
        id INT AUTO_INCREMENT PRIMARY KEY,
        city VARCHAR(50) NOT NULL,
        country VARCHAR(50) NOT NULL,
        airport_code VARCHAR(10),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    $pdo->exec($sql);
    echo "<p style='color: green;'>✓ Locations table created successfully</p>";
    
    // Insert sample data
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM cars");
    $stmt->execute();
    $carCount = $stmt->fetchColumn();
    
    if ($carCount == 0) {
        // Insert sample cars
        $cars = [
            ['Toyota', 'Corolla', 2023, 'Compact', 'Petrol', 'Automatic', 5, 45.00, 'https://images.unsplash.com/photo-1621007947382-bb3c3994e3fb?w=400', 'Air Conditioning, Bluetooth, GPS Navigation, USB Charging', 'New York', 4.8],
            ['Honda', 'Civic', 2023, 'Compact', 'Petrol', 'Manual', 5, 42.00, 'https://images.unsplash.com/photo-1606664515524-ed2f786a0bd6?w=400', 'Air Conditioning, Bluetooth, Backup Camera', 'Los Angeles', 4.7],
            ['BMW', 'X3', 2023, 'SUV', 'Petrol', 'Automatic', 5, 85.00, 'https://images.unsplash.com/photo-1555215695-3004980ad54e?w=400', 'Leather Seats, Sunroof, Premium Sound, GPS Navigation', 'Miami', 4.9],
            ['Mercedes-Benz', 'C-Class', 2023, 'Luxury', 'Petrol', 'Automatic', 5, 95.00, 'https://images.unsplash.com/photo-1618843479313-40f8afb4b4d8?w=400', 'Leather Interior, Premium Sound, GPS, Heated Seats', 'Chicago', 4.8],
            ['Ford', 'Explorer', 2023, 'SUV', 'Petrol', 'Automatic', 7, 75.00, 'https://images.unsplash.com/photo-1544636331-e26879cd4d9b?w=400', '7 Seater, Air Conditioning, Bluetooth, Large Trunk', 'Dallas', 4.6]
        ];
        
        $stmt = $pdo->prepare("INSERT INTO cars (brand, model, year, car_type, fuel_type, transmission, seats, price_per_day, image_url, features, location, rating) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        
        foreach ($cars as $car) {
            $stmt->execute($car);
        }
        echo "<p style='color: green;'>✓ Sample cars inserted successfully</p>";
    }
    
    // Insert sample locations
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM locations");
    $stmt->execute();
    $locationCount = $stmt->fetchColumn();
    
    if ($locationCount == 0) {
        $locations = [
            ['New York', 'USA', 'JFK'],
            ['Los Angeles', 'USA', 'LAX'],
            ['Miami', 'USA', 'MIA'],
            ['Chicago', 'USA', 'ORD'],
            ['Dallas', 'USA', 'DFW']
        ];
        
        $stmt = $pdo->prepare("INSERT INTO locations (city, country, airport_code) VALUES (?, ?, ?)");
        
        foreach ($locations as $location) {
            $stmt->execute($location);
        }
        echo "<p style='color: green;'>✓ Sample locations inserted successfully</p>";
    }
    
    echo "<h3 style='color: green;'>Setup Complete!</h3>";
    echo "<p><a href='index.php'>Go to Homepage</a> | <a href='test.php'>Run Test</a></p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
}
?>
