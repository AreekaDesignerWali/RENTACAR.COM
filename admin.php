<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'db.php';

// Handle form submissions
$message = '';
$error = '';

if ($_POST) {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'add_car') {
        // Add new car
        $brand = trim($_POST['brand'] ?? '');
        $model = trim($_POST['model'] ?? '');
        $year = intval($_POST['year'] ?? 0);
        $car_type = $_POST['car_type'] ?? '';
        $fuel_type = $_POST['fuel_type'] ?? '';
        $transmission = $_POST['transmission'] ?? '';
        $seats = intval($_POST['seats'] ?? 0);
        $price_per_day = floatval($_POST['price_per_day'] ?? 0);
        $image_url = trim($_POST['image_url'] ?? '');
        $features = trim($_POST['features'] ?? '');
        $location = trim($_POST['location'] ?? '');
        $rating = floatval($_POST['rating'] ?? 4.5);
        
        // Validation
        if ($brand && $model && $year && $car_type && $fuel_type && $transmission && $seats && $price_per_day && $image_url && $location) {
            try {
                $stmt = $pdo->prepare("
                    INSERT INTO cars (brand, model, year, car_type, fuel_type, transmission, seats, price_per_day, image_url, features, location, rating) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                ");
                $stmt->execute([$brand, $model, $year, $car_type, $fuel_type, $transmission, $seats, $price_per_day, $image_url, $features, $location, $rating]);
                $message = "Car added successfully!";
            } catch (PDOException $e) {
                $error = "Error adding car: " . $e->getMessage();
            }
        } else {
            $error = "Please fill in all required fields.";
        }
    }
    
    elseif ($action === 'delete_car') {
        $car_id = intval($_POST['car_id'] ?? 0);
        if ($car_id) {
            try {
                $stmt = $pdo->prepare("DELETE FROM cars WHERE id = ?");
                $stmt->execute([$car_id]);
                $message = "Car deleted successfully!";
            } catch (PDOException $e) {
                $error = "Error deleting car: " . $e->getMessage();
            }
        }
    }
    
    elseif ($action === 'toggle_availability') {
        $car_id = intval($_POST['car_id'] ?? 0);
        $available = intval($_POST['available'] ?? 0);
        if ($car_id) {
            try {
                $stmt = $pdo->prepare("UPDATE cars SET available = ? WHERE id = ?");
                $stmt->execute([$available, $car_id]);
                $message = "Car availability updated!";
            } catch (PDOException $e) {
                $error = "Error updating car: " . $e->getMessage();
            }
        }
    }
}

// Get all cars
try {
    $stmt = $pdo->query("SELECT * FROM cars ORDER BY created_at DESC");
    $cars = $stmt->fetchAll();
} catch (PDOException $e) {
    $cars = [];
    $error = "Error fetching cars: " . $e->getMessage();
}

// Get locations for dropdown
try {
    $stmt = $pdo->query("SELECT DISTINCT city FROM locations ORDER BY city");
    $locations = $stmt->fetchAll();
} catch (PDOException $e) {
    $locations = [];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Car Management - RentACar Admin</title>
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
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 20px;
        }

        /* Header */
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
        .admin-container {
            padding: 2rem 0;
        }

        .admin-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
        }

        .admin-title {
            font-size: 2.5rem;
            color: #333;
        }

        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            transition: transform 0.3s;
        }

        .btn:hover {
            transform: translateY(-2px);
        }

        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .btn-success {
            background: #28a745;
            color: white;
        }

        .btn-danger {
            background: #dc3545;
            color: white;
        }

        .btn-warning {
            background: #ffc107;
            color: #333;
        }

        .btn-sm {
            padding: 5px 10px;
            font-size: 0.9rem;
        }

        /* Messages */
        .message {
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1rem;
        }

        .message.success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .message.error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        /* Add Car Form */
        .add-car-section {
            background: white;
            padding: 2rem;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            margin-bottom: 2rem;
        }

        .section-title {
            font-size: 1.5rem;
            margin-bottom: 1.5rem;
            color: #333;
            border-bottom: 2px solid #667eea;
            padding-bottom: 0.5rem;
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 1rem;
            margin-bottom: 1rem;
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
        .form-group select,
        .form-group textarea {
            padding: 10px;
            border: 2px solid #e1e5e9;
            border-radius: 8px;
            font-size: 1rem;
            transition: border-color 0.3s;
        }

        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #667eea;
        }

        .form-group textarea {
            resize: vertical;
            min-height: 80px;
        }

        .form-full-width {
            grid-column: 1 / -1;
        }

        /* Cars List */
        .cars-section {
            background: white;
            padding: 2rem;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        }

        .cars-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1rem;
        }

        .cars-table th,
        .cars-table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #e1e5e9;
        }

        .cars-table th {
            background: #f8f9fa;
            font-weight: 600;
            color: #555;
        }

        .cars-table tr:hover {
            background: #f8f9fa;
        }

        .car-image-small {
            width: 60px;
            height: 40px;
            object-fit: cover;
            border-radius: 5px;
        }

        .status-badge {
            padding: 4px 8px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
        }

        .status-available {
            background: #d4edda;
            color: #155724;
        }

        .status-unavailable {
            background: #f8d7da;
            color: #721c24;
        }

        .actions {
            display: flex;
            gap: 0.5rem;
        }

        /* Modal */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
        }

        .modal-content {
            background: white;
            margin: 5% auto;
            padding: 2rem;
            border-radius: 15px;
            width: 90%;
            max-width: 800px;
            max-height: 80vh;
            overflow-y: auto;
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
        }

        .close {
            font-size: 2rem;
            cursor: pointer;
            color: #999;
        }

        .close:hover {
            color: #333;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .nav-links {
                display: none;
            }

            .admin-header {
                flex-direction: column;
                gap: 1rem;
            }

            .form-grid {
                grid-template-columns: 1fr;
            }

            .cars-table {
                font-size: 0.9rem;
            }

            .actions {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <header>
        <div class="container">
            <div class="header-content">
                <a href="index.php" class="logo">
                    <i class="fas fa-car"></i> RentACar Admin
                </a>
                <nav>
                    <ul class="nav-links">
                        <li><a href="index.php">Home</a></li>
                        <li><a href="cars.php">Cars</a></li>
                        <li><a href="admin.php">Admin</a></li>
                        <li><a href="bookings_admin.php">Bookings</a></li>
                    </ul>
                </nav>
            </div>
        </div>
    </header>

    <div class="container">
        <div class="admin-container">
            <div class="admin-header">
                <h1 class="admin-title">
                    <i class="fas fa-cogs"></i> Car Management
                </h1>
                <button class="btn btn-primary" onclick="openAddCarModal()">
                    <i class="fas fa-plus"></i> Add New Car
                </button>
            </div>

            <?php if ($message): ?>
                <div class="message success">
                    <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="message error">
                    <i class="fas fa-exclamation-triangle"></i> <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <!-- Cars List -->
            <div class="cars-section">
                <h2 class="section-title">
                    <i class="fas fa-list"></i> All Cars (<?php echo count($cars); ?>)
                </h2>

                <?php if (empty($cars)): ?>
                    <p>No cars found. <a href="#" onclick="openAddCarModal()">Add your first car</a></p>
                <?php else: ?>
                    <div style="overflow-x: auto;">
                        <table class="cars-table">
                            <thead>
                                <tr>
                                    <th>Image</th>
                                    <th>Car</th>
                                    <th>Type</th>
                                    <th>Year</th>
                                    <th>Price/Day</th>
                                    <th>Location</th>
                                    <th>Rating</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($cars as $car): ?>
                                <tr>
                                    <td>
                                        <img src="<?php echo htmlspecialchars($car['image_url']); ?>" 
                                             alt="Car" class="car-image-small"
                                             onerror="this.src='https://via.placeholder.com/60x40/667eea/white?text=Car'">
                                    </td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($car['brand'] . ' ' . $car['model']); ?></strong>
                                    </td>
                                    <td><?php echo htmlspecialchars($car['car_type']); ?></td>
                                    <td><?php echo $car['year']; ?></td>
                                    <td>$<?php echo number_format($car['price_per_day'], 2); ?></td>
                                    <td><?php echo htmlspecialchars($car['location']); ?></td>
                                    <td>
                                        <i class="fas fa-star" style="color: #ffa500;"></i>
                                        <?php echo $car['rating']; ?>
                                    </td>
                                    <td>
                                        <span class="status-badge <?php echo $car['available'] ? 'status-available' : 'status-unavailable'; ?>">
                                            <?php echo $car['available'] ? 'Available' : 'Unavailable'; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="actions">
                                            <button class="btn btn-warning btn-sm" onclick="editCar(<?php echo $car['id']; ?>)">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <form method="POST" style="display: inline;" onsubmit="return confirm('Toggle availability?')">
                                                <input type="hidden" name="action" value="toggle_availability">
                                                <input type="hidden" name="car_id" value="<?php echo $car['id']; ?>">
                                                <input type="hidden" name="available" value="<?php echo $car['available'] ? 0 : 1; ?>">
                                                <button type="submit" class="btn <?php echo $car['available'] ? 'btn-warning' : 'btn-success'; ?> btn-sm">
                                                    <i class="fas fa-<?php echo $car['available'] ? 'pause' : 'play'; ?>"></i>
                                                </button>
                                            </form>
                                            <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this car?')">
                                                <input type="hidden" name="action" value="delete_car">
                                                <input type="hidden" name="car_id" value="<?php echo $car['id']; ?>">
                                                <button type="submit" class="btn btn-danger btn-sm">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Add Car Modal -->
    <div id="addCarModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2><i class="fas fa-plus"></i> Add New Car</h2>
                <span class="close" onclick="closeAddCarModal()">&times;</span>
            </div>
            
            <form method="POST" id="addCarForm">
                <input type="hidden" name="action" value="add_car">
                
                <div class="form-grid">
                    <div class="form-group">
                        <label for="brand">Brand *</label>
                        <input type="text" id="brand" name="brand" required placeholder="e.g., Toyota">
                    </div>
                    
                    <div class="form-group">
                        <label for="model">Model *</label>
                        <input type="text" id="model" name="model" required placeholder="e.g., Corolla">
                    </div>
                    
                    <div class="form-group">
                        <label for="year">Year *</label>
                        <input type="number" id="year" name="year" required min="2000" max="2025" value="2023">
                    </div>
                    
                    <div class="form-group">
                        <label for="car_type">Car Type *</label>
                        <select id="car_type" name="car_type" required>
                            <option value="">Select Type</option>
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
                        <label for="fuel_type">Fuel Type *</label>
                        <select id="fuel_type" name="fuel_type" required>
                            <option value="">Select Fuel Type</option>
                            <option value="Petrol">Petrol</option>
                            <option value="Diesel">Diesel</option>
                            <option value="Electric">Electric</option>
                            <option value="Hybrid">Hybrid</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="transmission">Transmission *</label>
                        <select id="transmission" name="transmission" required>
                            <option value="">Select Transmission</option>
                            <option value="Manual">Manual</option>
                            <option value="Automatic">Automatic</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="seats">Seats *</label>
                        <input type="number" id="seats" name="seats" required min="2" max="12" value="5">
                    </div>
                    
                    <div class="form-group">
                        <label for="price_per_day">Price per Day ($) *</label>
                        <input type="number" id="price_per_day" name="price_per_day" required min="1" step="0.01" placeholder="45.00">
                    </div>
                    
                    <div class="form-group">
                        <label for="location">Location *</label>
                        <select id="location" name="location" required>
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
                        <label for="rating">Rating (1-5)</label>
                        <input type="number" id="rating" name="rating" min="1" max="5" step="0.1" value="4.5">
                    </div>
                    
                    <div class="form-group form-full-width">
                        <label for="image_url">Image URL *</label>
                        <input type="url" id="image_url" name="image_url" required 
                               placeholder="https://images.unsplash.com/photo-...">
                        <small style="color: #666; margin-top: 5px; display: block;">
                            Use Unsplash or other image hosting services. Example: https://images.unsplash.com/photo-1621007947382-bb3c3994e3fb?w=400
                        </small>
                    </div>
                    
                    <div class="form-group form-full-width">
                        <label for="features">Features</label>
                        <textarea id="features" name="features" 
                                  placeholder="Air Conditioning, Bluetooth, GPS Navigation, USB Charging"></textarea>
                        <small style="color: #666; margin-top: 5px; display: block;">
                            Separate features with commas
                        </small>
                    </div>
                </div>
                
                <div style="text-align: center; margin-top: 2rem;">
                    <button type="button" class="btn" onclick="closeAddCarModal()" style="background: #6c757d; color: white; margin-right: 1rem;">
                        Cancel
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Add Car
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openAddCarModal() {
            document.getElementById('addCarModal').style.display = 'block';
        }

        function closeAddCarModal() {
            document.getElementById('addCarModal').style.display = 'none';
            document.getElementById('addCarForm').reset();
        }

        function editCar(carId) {
            // For now, just show an alert. You can implement edit functionality later
            alert('Edit functionality coming soon! Car ID: ' + carId);
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            const modal = document.getElementById('addCarModal');
            if (event.target == modal) {
                closeAddCarModal();
            }
        }

        // Form validation
        document.getElementById('addCarForm').addEventListener('submit', function(e) {
            const imageUrl = document.getElementById('image_url').value;
            const price = parseFloat(document.getElementById('price_per_day').value);
            
            if (price <= 0) {
                e.preventDefault();
                alert('Price must be greater than 0');
                return;
            }
            
            if (!imageUrl.startsWith('http')) {
                e.preventDefault();
                alert('Please enter a valid image URL starting with http:// or https://');
                return;
            }
        });
    </script>
</body>
</html>
