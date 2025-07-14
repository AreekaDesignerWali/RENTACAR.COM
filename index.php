<?php
// Enable error reporting for debugging (remove in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start output buffering to catch any errors
ob_start();

try {
    // Include database connection
    require_once 'db.php';
    
    // Check if tables exist and have data
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM cars");
    $carCount = $stmt->fetch()['count'];
    
    if ($carCount == 0) {
        // Redirect to setup if no cars found
        header("Location: create_tables.php");
        exit;
    }
    
    // Fetch featured cars with error handling
    $stmt = $pdo->prepare("SELECT * FROM cars WHERE available = 1 ORDER BY rating DESC LIMIT 6");
    $stmt->execute();
    $featured_cars = $stmt->fetchAll();
    
    // Fetch locations with error handling
    $stmt = $pdo->prepare("SELECT DISTINCT city FROM locations ORDER BY city");
    $stmt->execute();
    $locations = $stmt->fetchAll();
    
} catch (PDOException $e) {
    // If tables don't exist, redirect to setup
    if (strpos($e->getMessage(), "doesn't exist") !== false) {
        header("Location: create_tables.php");
        exit;
    }
    
    // For other errors, show user-friendly message
    error_log("Database error in index.php: " . $e->getMessage());
    $featured_cars = [];
    $locations = [];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RentACar - Premium Car Rental Service</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            background: #f8f9fa;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }

        /* Header Styles */
        header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 1rem 0;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo {
            font-size: 2rem;
            font-weight: bold;
            text-decoration: none;
            color: white;
        }

        .nav-links {
            display: flex;
            list-style: none;
            gap: 2rem;
        }

        .nav-links a {
            color: white;
            text-decoration: none;
            font-weight: 500;
            transition: opacity 0.3s;
        }

        .nav-links a:hover {
            opacity: 0.8;
        }

        /* Hero Section */
        .hero {
            background: linear-gradient(rgba(0,0,0,0.4), rgba(0,0,0,0.4)), url('https://images.unsplash.com/photo-1449824913935-59a10b8d2000?w=1200') center/cover;
            height: 70vh;
            display: flex;
            align-items: center;
            color: white;
            text-align: center;
        }

        .hero-content h1 {
            font-size: 3.5rem;
            margin-bottom: 1rem;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.5);
        }

        .hero-content p {
            font-size: 1.3rem;
            margin-bottom: 2rem;
            text-shadow: 1px 1px 2px rgba(0,0,0,0.5);
        }

        /* Search Form */
        .search-section {
            background: white;
            padding: 3rem 0;
            margin-top: -100px;
            position: relative;
            z-index: 10;
        }

        .search-form {
            background: white;
            padding: 2rem;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            align-items: end;
        }

        .form-group {
            display: flex;
            flex-direction: column;
        }

        .form-group label {
            font-weight: 600;
            margin-bottom: 0.5rem;
            color: #555;
        }

        .form-group input,
        .form-group select {
            padding: 12px;
            border: 2px solid #e1e5e9;
            border-radius: 8px;
            font-size: 1rem;
            transition: border-color 0.3s;
        }

        .form-group input:focus,
        .form-group select:focus {
            outline: none;
            border-color: #667eea;
        }

        .search-btn {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            padding: 12px 2rem;
            border-radius: 8px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.3s;
        }

        .search-btn:hover {
            transform: translateY(-2px);
        }

        /* Featured Cars Section */
        .featured-section {
            padding: 4rem 0;
        }

        .section-title {
            text-align: center;
            font-size: 2.5rem;
            margin-bottom: 3rem;
            color: #333;
        }

        .cars-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 2rem;
        }

        .car-card {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            transition: transform 0.3s, box-shadow 0.3s;
        }

        .car-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0,0,0,0.15);
        }

        .car-image {
            width: 100%;
            height: 200px;
            object-fit: cover;
        }

        .car-info {
            padding: 1.5rem;
        }

        .car-title {
            font-size: 1.3rem;
            font-weight: bold;
            margin-bottom: 0.5rem;
            color: #333;
        }

        .car-details {
            display: flex;
            justify-content: space-between;
            margin-bottom: 1rem;
            color: #666;
        }

        .car-features {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
            margin-bottom: 1rem;
        }

        .feature-tag {
            background: #f0f2f5;
            padding: 0.3rem 0.8rem;
            border-radius: 20px;
            font-size: 0.8rem;
            color: #555;
        }

        .car-price {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .price {
            font-size: 1.5rem;
            font-weight: bold;
            color: #667eea;
        }

        .rating {
            display: flex;
            align-items: center;
            gap: 0.3rem;
            color: #ffa500;
        }

        .book-btn {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            padding: 0.8rem 1.5rem;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            transition: transform 0.3s;
        }

        .book-btn:hover {
            transform: scale(1.05);
        }

        .no-cars {
            text-align: center;
            padding: 3rem;
            color: #666;
        }

        /* Footer */
        footer {
            background: #333;
            color: white;
            padding: 3rem 0 1rem;
            margin-top: 4rem;
        }

        .footer-content {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 2rem;
            margin-bottom: 2rem;
        }

        .footer-section h3 {
            margin-bottom: 1rem;
            color: #667eea;
        }

        .footer-section ul {
            list-style: none;
        }

        .footer-section ul li {
            margin-bottom: 0.5rem;
        }

        .footer-section ul li a {
            color: #ccc;
            text-decoration: none;
            transition: color 0.3s;
        }

        .footer-section ul li a:hover {
            color: white;
        }

        .footer-bottom {
            text-align: center;
            padding-top: 2rem;
            border-top: 1px solid #555;
            color: #ccc;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .hero-content h1 {
                font-size: 2.5rem;
            }

            .search-form {
                grid-template-columns: 1fr;
            }

            .nav-links {
                display: none;
            }

            .cars-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <header>
        <div class="container">
            <div class="header-content">
                <a href="index.php" class="logo">
                    <i class="fas fa-car"></i> RentACar
                </a>
                <nav>
                    <ul class="nav-links">
                        <li><a href="index.php">Home</a></li>
                        <li><a href="cars.php">Cars</a></li>
                        <li><a href="#about">About</a></li>
                        <li><a href="#contact">Contact</a></li>
                    </ul>
                </nav>
            </div>
        </div>
    </header>

    <section class="hero">
        <div class="container">
            <div class="hero-content">
                <h1>Premium Car Rental Service</h1>
                <p>Discover the perfect car for your journey with our premium fleet</p>
            </div>
        </div>
    </section>

    <section class="search-section">
        <div class="container">
            <form class="search-form" id="searchForm">
                <div class="form-group">
                    <label for="pickup-location">Pickup Location</label>
                    <select id="pickup-location" name="pickup_location" required>
                        <option value="">Select Location</option>
                        <?php if (!empty($locations)): ?>
                            <?php foreach($locations as $location): ?>
                                <option value="<?php echo htmlspecialchars($location['city']); ?>">
                                    <?php echo htmlspecialchars($location['city']); ?>
                                </option>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <option value="New York">New York</option>
                            <option value="Los Angeles">Los Angeles</option>
                            <option value="Chicago">Chicago</option>
                        <?php endif; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="pickup-date">Pickup Date</label>
                    <input type="date" id="pickup-date" name="pickup_date" required>
                </div>
                <div class="form-group">
                    <label for="return-date">Return Date</label>
                    <input type="date" id="return-date" name="return_date" required>
                </div>
                <div class="form-group">
                    <label for="car-type">Car Type</label>
                    <select id="car-type" name="car_type">
                        <option value="">Any Type</option>
                        <option value="Economy">Economy</option>
                        <option value="Compact">Compact</option>
                        <option value="Mid-size">Mid-size</option>
                        <option value="Full-size">Full-size</option>
                        <option value="Premium">Premium</option>
                        <option value="Luxury">Luxury</option>
                        <option value="SUV">SUV</option>
                        <option value="Van">Van</option>
                    </select>
                </div>
                <div class="form-group">
                    <button type="submit" class="search-btn">
                        <i class="fas fa-search"></i> Search Cars
                    </button>
                </div>
            </form>
        </div>
    </section>

    <section class="featured-section">
        <div class="container">
            <h2 class="section-title">Featured Vehicles</h2>
            <div class="cars-grid">
                <?php if (!empty($featured_cars)): ?>
                    <?php foreach($featured_cars as $car): ?>
                    <div class="car-card">
                        <img src="<?php echo htmlspecialchars($car['image_url']); ?>" 
                             alt="<?php echo htmlspecialchars($car['brand'] . ' ' . $car['model']); ?>" 
                             class="car-image"
                             onerror="this.src='https://via.placeholder.com/400x200/667eea/white?text=Car+Image'">
                        <div class="car-info">
                            <h3 class="car-title"><?php echo htmlspecialchars($car['brand'] . ' ' . $car['model']); ?></h3>
                            <div class="car-details">
                                <span><i class="fas fa-users"></i> <?php echo $car['seats']; ?> Seats</span>
                                <span><i class="fas fa-cog"></i> <?php echo htmlspecialchars($car['transmission']); ?></span>
                                <span><i class="fas fa-gas-pump"></i> <?php echo htmlspecialchars($car['fuel_type']); ?></span>
                            </div>
                            <div class="car-features">
                                <?php 
                                $features = explode(', ', $car['features']);
                                foreach(array_slice($features, 0, 3) as $feature): 
                                ?>
                                    <span class="feature-tag"><?php echo htmlspecialchars($feature); ?></span>
                                <?php endforeach; ?>
                            </div>
                            <div class="car-price">
                                <div>
                                    <span class="price">$<?php echo number_format($car['price_per_day'], 2); ?></span>
                                    <small>/day</small>
                                </div>
                                <div class="rating">
                                    <i class="fas fa-star"></i>
                                    <span><?php echo $car['rating']; ?></span>
                                </div>
                            </div>
                            <button class="book-btn" onclick="bookCar(<?php echo $car['id']; ?>)">
                                <i class="fas fa-calendar-check"></i> Book Now
                            </button>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="no-cars">
                        <h3>No cars available at the moment</h3>
                        <p>Please check back later or <a href="test.php">run system test</a></p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <footer>
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <h3>RentACar</h3>
                    <p>Your trusted partner for premium car rental services. Experience luxury and comfort on every journey.</p>
                </div>
                <div class="footer-section">
                    <h3>Quick Links</h3>
                    <ul>
                        <li><a href="index.php">Home</a></li>
                        <li><a href="cars.php">Our Fleet</a></li>
                        <li><a href="#about">About Us</a></li>
                        <li><a href="#contact">Contact</a></li>
                    </ul>
                </div>
                <div class="footer-section">
                    <h3>Services</h3>
                    <ul>
                        <li><a href="#">Airport Pickup</a></li>
                        <li><a href="#">Long Term Rental</a></li>
                        <li><a href="#">Corporate Rental</a></li>
                        <li><a href="#">Luxury Cars</a></li>
                    </ul>
                </div>
                <div class="footer-section">
                    <h3>Contact Info</h3>
                    <ul>
                        <li><i class="fas fa-phone"></i> +1 (555) 123-4567</li>
                        <li><i class="fas fa-envelope"></i> info@rentacar.com</li>
                        <li><i class="fas fa-map-marker-alt"></i> 123 Main St, City</li>
                    </ul>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; 2024 RentACar. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script>
        // Set minimum date to today
        document.addEventListener('DOMContentLoaded', function() {
            const today = new Date().toISOString().split('T')[0];
            document.getElementById('pickup-date').min = today;
            document.getElementById('return-date').min = today;
            
            // Update return date minimum when pickup date changes
            document.getElementById('pickup-date').addEventListener('change', function() {
                document.getElementById('return-date').min = this.value;
            });
        });

        // Search form submission
        document.getElementById('searchForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const params = new URLSearchParams();
            
            for (let [key, value] of formData.entries()) {
                if (value) params.append(key, value);
            }
            
            window.location.href = 'cars.php?' + params.toString();
        });

        // Book car function
        function bookCar(carId) {
            const pickupDate = document.getElementById('pickup-date').value;
            const returnDate = document.getElementById('return-date').value;
            const location = document.getElementById('pickup-location').value;
            
            if (!pickupDate || !returnDate) {
                alert('Please select pickup and return dates first.');
                return;
            }
            
            const params = new URLSearchParams({
                car_id: carId,
                pickup_date: pickupDate,
                return_date: returnDate,
                pickup_location: location || 'New York'
            });
            
            window.location.href = 'booking.php?' + params.toString();
        }
    </script>
</body>
</html>
