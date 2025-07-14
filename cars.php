<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check if tables exist first
try {
    require_once 'db.php';
    
    // Test if cars table exists and has data
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM cars LIMIT 1");
    $result = $stmt->fetch();
    
    if ($result['count'] == 0) {
        // Tables exist but no data
        header("Location: create_tables.php");
        exit;
    }
    
} catch (PDOException $e) {
    // If tables don't exist or other database error
    if (strpos($e->getMessage(), "doesn't exist") !== false || 
        strpos($e->getMessage(), "Table") !== false) {
        // Redirect to setup instead of showing error
        header("Location: create_tables.php");
        exit;
    } else {
        die("Database connection error: " . $e->getMessage());
    }
}

// Get search parameters
$pickup_location = $_GET['pickup_location'] ?? '';
$pickup_date = $_GET['pickup_date'] ?? '';
$return_date = $_GET['return_date'] ?? '';
$car_type = $_GET['car_type'] ?? '';
$fuel_type = $_GET['fuel_type'] ?? '';
$price_range = $_GET['price_range'] ?? '';
$sort_by = $_GET['sort_by'] ?? 'rating';

// Build query
$query = "SELECT * FROM cars WHERE available = 1";
$params = [];

if ($pickup_location) {
    $query .= " AND location = ?";
    $params[] = $pickup_location;
}

if ($car_type) {
    $query .= " AND car_type = ?";
    $params[] = $car_type;
}

if ($fuel_type) {
    $query .= " AND fuel_type = ?";
    $params[] = $fuel_type;
}

if ($price_range) {
    switch($price_range) {
        case 'low':
            $query .= " AND price_per_day <= 50";
            break;
        case 'medium':
            $query .= " AND price_per_day BETWEEN 51 AND 80";
            break;
        case 'high':
            $query .= " AND price_per_day > 80";
            break;
    }
}

// Add sorting
switch($sort_by) {
    case 'price_low':
        $query .= " ORDER BY price_per_day ASC";
        break;
    case 'price_high':
        $query .= " ORDER BY price_per_day DESC";
        break;
    case 'rating':
        $query .= " ORDER BY rating DESC";
        break;
    case 'newest':
        $query .= " ORDER BY year DESC";
        break;
}

try {
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $cars = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "Error fetching cars: " . $e->getMessage();
    $cars = [];
}

// Get locations for filter
try {
    $stmt = $pdo->query("SELECT DISTINCT city FROM locations ORDER BY city");
    $locations = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $locations = [];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Available Cars - RentACar</title>
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

        /* Main Content */
        .main-content {
            display: grid;
            grid-template-columns: 300px 1fr;
            gap: 2rem;
            padding: 2rem 0;
        }

        /* Filters Sidebar */
        .filters-sidebar {
            background: white;
            padding: 2rem;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            height: fit-content;
            position: sticky;
            top: 2rem;
        }

        .filters-title {
            font-size: 1.5rem;
            margin-bottom: 1.5rem;
            color: #333;
            border-bottom: 2px solid #667eea;
            padding-bottom: 0.5rem;
        }

        .filter-group {
            margin-bottom: 1.5rem;
        }

        .filter-group label {
            display: block;
            font-weight: 600;
            margin-bottom: 0.5rem;
            color: #555;
        }

        .filter-group select,
        .filter-group input {
            width: 100%;
            padding: 10px;
            border: 2px solid #e1e5e9;
            border-radius: 8px;
            font-size: 1rem;
            transition: border-color 0.3s;
        }

        .filter-group select:focus,
        .filter-group input:focus {
            outline: none;
            border-color: #667eea;
        }

        .apply-filters {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            padding: 12px;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            width: 100%;
            transition: transform 0.3s;
        }

        .apply-filters:hover {
            transform: translateY(-2px);
        }

        /* Cars Section */
        .cars-section {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        }

        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .results-count {
            font-size: 1.2rem;
            color: #666;
        }

        .sort-controls {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .sort-controls select {
            padding: 8px 12px;
            border: 2px solid #e1e5e9;
            border-radius: 8px;
            font-size: 1rem;
        }

        /* Car Cards */
        .cars-grid {
            display: grid;
            gap: 2rem;
        }

        .car-card {
            display: grid;
            grid-template-columns: 300px 1fr auto;
            gap: 1.5rem;
            background: white;
            border: 1px solid #e1e5e9;
            border-radius: 15px;
            overflow: hidden;
            transition: transform 0.3s, box-shadow 0.3s;
        }

        .car-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 30px rgba(0,0,0,0.15);
        }

        .car-image {
            width: 100%;
            height: 200px;
            object-fit: cover;
        }

        .car-info {
            padding: 1.5rem 0;
        }

        .car-title {
            font-size: 1.4rem;
            font-weight: bold;
            margin-bottom: 0.5rem;
            color: #333;
        }

        .car-type {
            color: #667eea;
            font-weight: 600;
            margin-bottom: 1rem;
        }

        .car-specs {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 0.5rem;
            margin-bottom: 1rem;
        }

        .spec-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: #666;
            font-size: 0.9rem;
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

        .car-pricing {
            padding: 1.5rem;
            text-align: center;
            border-left: 1px solid #e1e5e9;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            min-width: 200px;
        }

        .price-section {
            margin-bottom: 1rem;
        }

        .price {
            font-size: 2rem;
            font-weight: bold;
            color: #667eea;
            display: block;
        }

        .price-period {
            color: #666;
            font-size: 0.9rem;
        }

        .rating {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.3rem;
            color: #ffa500;
            margin-bottom: 1rem;
        }

        .book-btn {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            padding: 12px 1.5rem;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.3s;
            width: 100%;
        }

        .book-btn:hover {
            transform: scale(1.05);
        }

        .no-results {
            text-align: center;
            padding: 3rem;
            color: #666;
        }

        .no-results i {
            font-size: 4rem;
            margin-bottom: 1rem;
            color: #ccc;
        }

        /* Responsive Design */
        @media (max-width: 1024px) {
            .main-content {
                grid-template-columns: 1fr;
            }

            .filters-sidebar {
                position: static;
            }

            .car-card {
                grid-template-columns: 1fr;
            }

            .car-pricing {
                border-left: none;
                border-top: 1px solid #e1e5e9;
            }
        }

        @media (max-width: 768px) {
            .nav-links {
                display: none;
            }

            .section-header {
                flex-direction: column;
                align-items: stretch;
            }

            .car-specs {
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

    <div class="container">
        <div class="main-content">
            <!-- Filters Sidebar -->
            <aside class="filters-sidebar">
                <h3 class="filters-title">
                    <i class="fas fa-filter"></i> Filters
                </h3>
                <form id="filtersForm">
                    <div class="filter-group">
                        <label for="filter-location">Location</label>
                        <select id="filter-location" name="pickup_location">
                            <option value="">All Locations</option>
                            <?php if (!empty($locations)): ?>
                                <?php foreach($locations as $location): ?>
                                    <option value="<?php echo htmlspecialchars($location['city']); ?>" 
                                            <?php echo $pickup_location === $location['city'] ? 'selected' : ''; ?>>
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

                    <div class="filter-group">
                        <label for="filter-pickup-date">Pickup Date</label>
                        <input type="date" id="filter-pickup-date" name="pickup_date" 
                               value="<?php echo htmlspecialchars($pickup_date); ?>">
                    </div>

                    <div class="filter-group">
                        <label for="filter-return-date">Return Date</label>
                        <input type="date" id="filter-return-date" name="return_date" 
                               value="<?php echo htmlspecialchars($return_date); ?>">
                    </div>

                    <div class="filter-group">
                        <label for="filter-car-type">Car Type</label>
                        <select id="filter-car-type" name="car_type">
                            <option value="">All Types</option>
                            <option value="Economy" <?php echo $car_type === 'Economy' ? 'selected' : ''; ?>>Economy</option>
                            <option value="Compact" <?php echo $car_type === 'Compact' ? 'selected' : ''; ?>>Compact</option>
                            <option value="Mid-size" <?php echo $car_type === 'Mid-size' ? 'selected' : ''; ?>>Mid-size</option>
                            <option value="Full-size" <?php echo $car_type === 'Full-size' ? 'selected' : ''; ?>>Full-size</option>
                            <option value="Premium" <?php echo $car_type === 'Premium' ? 'selected' : ''; ?>>Premium</option>
                            <option value="Luxury" <?php echo $car_type === 'Luxury' ? 'selected' : ''; ?>>Luxury</option>
                            <option value="SUV" <?php echo $car_type === 'SUV' ? 'selected' : ''; ?>>SUV</option>
                            <option value="Van" <?php echo $car_type === 'Van' ? 'selected' : ''; ?>>Van</option>
                        </select>
                    </div>

                    <div class="filter-group">
                        <label for="filter-fuel-type">Fuel Type</label>
                        <select id="filter-fuel-type" name="fuel_type">
                            <option value="">All Fuel Types</option>
                            <option value="Petrol" <?php echo $fuel_type === 'Petrol' ? 'selected' : ''; ?>>Petrol</option>
                            <option value="Diesel" <?php echo $fuel_type === 'Diesel' ? 'selected' : ''; ?>>Diesel</option>
                            <option value="Electric" <?php echo $fuel_type === 'Electric' ? 'selected' : ''; ?>>Electric</option>
                            <option value="Hybrid" <?php echo $fuel_type === 'Hybrid' ? 'selected' : ''; ?>>Hybrid</option>
                        </select>
                    </div>

                    <div class="filter-group">
                        <label for="filter-price-range">Price Range</label>
                        <select id="filter-price-range" name="price_range">
                            <option value="">All Prices</option>
                            <option value="low" <?php echo $price_range === 'low' ? 'selected' : ''; ?>>Under $50/day</option>
                            <option value="medium" <?php echo $price_range === 'medium' ? 'selected' : ''; ?>>$51-$80/day</option>
                            <option value="high" <?php echo $price_range === 'high' ? 'selected' : ''; ?>>Over $80/day</option>
                        </select>
                    </div>

                    <button type="submit" class="apply-filters">
                        <i class="fas fa-search"></i> Apply Filters
                    </button>
                </form>
            </aside>

            <!-- Cars Section -->
            <main class="cars-section">
                <div class="section-header">
                    <div class="results-count">
                        <i class="fas fa-car"></i> 
                        <?php echo count($cars); ?> cars available
                    </div>
                    <div class="sort-controls">
                        <label for="sort-by">Sort by:</label>
                        <select id="sort-by" name="sort_by" onchange="applySorting()">
                            <option value="rating" <?php echo $sort_by === 'rating' ? 'selected' : ''; ?>>Best Rated</option>
                            <option value="price_low" <?php echo $sort_by === 'price_low' ? 'selected' : ''; ?>>Price: Low to High</option>
                            <option value="price_high" <?php echo $sort_by === 'price_high' ? 'selected' : ''; ?>>Price: High to Low</option>
                            <option value="newest" <?php echo $sort_by === 'newest' ? 'selected' : ''; ?>>Newest First</option>
                        </select>
                    </div>
                </div>

                <div class="cars-grid">
                    <?php if (empty($cars)): ?>
                        <div class="no-results">
                            <i class="fas fa-car"></i>
                            <h3>No cars found</h3>
                            <p>Try adjusting your search criteria or filters.</p>
                            <p><a href="create_tables.php">Setup Database</a> if this is your first time.</p>
                        </div>
                    <?php else: ?>
                        <?php foreach($cars as $car): ?>
                        <div class="car-card">
                            <img src="<?php echo htmlspecialchars($car['image_url']); ?>" 
                                 alt="<?php echo htmlspecialchars($car['brand'] . ' ' . $car['model']); ?>" 
                                 class="car-image"
                                 onerror="this.src='https://via.placeholder.com/300x200/667eea/white?text=Car+Image'">
                            
                            <div class="car-info">
                                <h3 class="car-title"><?php echo htmlspecialchars($car['brand'] . ' ' . $car['model']); ?></h3>
                                <div class="car-type"><?php echo htmlspecialchars($car['car_type']); ?> • <?php echo $car['year']; ?></div>
                                
                                <div class="car-specs">
                                    <div class="spec-item">
                                        <i class="fas fa-users"></i>
                                        <span><?php echo $car['seats']; ?> Seats</span>
                                    </div>
                                    <div class="spec-item">
                                        <i class="fas fa-cog"></i>
                                        <span><?php echo htmlspecialchars($car['transmission']); ?></span>
                                    </div>
                                    <div class="spec-item">
                                        <i class="fas fa-gas-pump"></i>
                                        <span><?php echo htmlspecialchars($car['fuel_type']); ?></span>
                                    </div>
                                    <div class="spec-item">
                                        <i class="fas fa-map-marker-alt"></i>
                                        <span><?php echo htmlspecialchars($car['location']); ?></span>
                                    </div>
                                </div>

                                <div class="car-features">
                                    <?php 
                                    $features = explode(', ', $car['features']);
                                    foreach(array_slice($features, 0, 4) as $feature): 
                                    ?>
                                        <span class="feature-tag"><?php echo htmlspecialchars($feature); ?></span>
                                    <?php endforeach; ?>
                                </div>
                            </div>

                            <div class="car-pricing">
                                <div class="price-section">
                                    <span class="price">$<?php echo number_format($car['price_per_day'], 2); ?></span>
                                    <div class="price-period">per day</div>
                                </div>
                                
                                <div class="rating">
                                    <i class="fas fa-star"></i>
                                    <span><?php echo $car['rating']; ?></span>
                                </div>

                                <button class="book-btn" onclick="bookCar(<?php echo $car['id']; ?>)">
                                    <i class="fas fa-calendar-check"></i> Book Now
                                </button>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </main>
        </div>
    </div>

    <script>
        // Set minimum date to today
        document.addEventListener('DOMContentLoaded', function() {
            const today = new Date().toISOString().split('T')[0];
            document.getElementById('filter-pickup-date').min = today;
            document.getElementById('filter-return-date').min = today;
            
            // Update return date minimum when pickup date changes
            document.getElementById('filter-pickup-date').addEventListener('change', function() {
                document.getElementById('filter-return-date').min = this.value;
            });
        });

        // Apply filters
        document.getElementById('filtersForm').addEventListener('submit', function(e) {
            e.preventDefault();
            applyFilters();
        });

        function applyFilters() {
            const formData = new FormData(document.getElementById('filtersForm'));
            const params = new URLSearchParams();
            
            for (let [key, value] of formData.entries()) {
                if (value) params.append(key, value);
            }
            
            // Add current sort
            const sortBy = document.getElementById('sort-by').value;
            if (sortBy) params.append('sort_by', sortBy);
            
            window.location.href = 'cars.php?' + params.toString();
        }

        // Apply sorting
        function applySorting() {
            const currentUrl = new URL(window.location);
            const sortBy = document.getElementById('sort-by').value;
            
            if (sortBy) {
                currentUrl.searchParams.set('sort_by', sortBy);
            } else {
                currentUrl.searchParams.delete('sort_by');
            }
            
            window.location.href = currentUrl.toString();
        }

        // Book car function
        function bookCar(carId) {
            const pickupDate = document.getElementById('filter-pickup-date').value;
            const returnDate = document.getElementById('filter-return-date').value;
            const location = document.getElementById('filter-location').value;
            
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
