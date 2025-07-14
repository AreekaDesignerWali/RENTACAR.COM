<?php
require_once 'db.php';

// Get booking parameters
$car_id = $_GET['car_id'] ?? 0;
$pickup_date = $_GET['pickup_date'] ?? '';
$return_date = $_GET['return_date'] ?? '';
$pickup_location = $_GET['pickup_location'] ?? '';

// Validate parameters
if (!$car_id || !$pickup_date || !$return_date) {
    header('Location: cars.php');
    exit;
}

// Get car details
$stmt = $pdo->prepare("SELECT * FROM cars WHERE id = ? AND available = 1");
$stmt->execute([$car_id]);
$car = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$car) {
    header('Location: cars.php');
    exit;
}

// Calculate rental duration and total cost
$pickup = new DateTime($pickup_date);
$return = new DateTime($return_date);
$interval = $pickup->diff($return);
$total_days = $interval->days;

if ($total_days <= 0) {
    header('Location: cars.php');
    exit;
}

$total_amount = $total_days * $car['price_per_day'];
$tax = $total_amount * 0.1; // 10% tax
$final_total = $total_amount + $tax;

// Handle form submission
if ($_POST) {
    $customer_name = trim($_POST['customer_name'] ?? '');
    $customer_email = trim($_POST['customer_email'] ?? '');
    $customer_phone = trim($_POST['customer_phone'] ?? '');
    $special_requests = trim($_POST['special_requests'] ?? '');
    
    // Server-side validation
    $errors = [];
    
    if (empty($customer_name)) {
        $errors[] = "Name is required";
    }
    
    if (empty($customer_email) || !filter_var($customer_email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Valid email is required";
    }
    
    if (empty($customer_phone)) {
        $errors[] = "Phone number is required";
    } else {
        // Clean phone number (remove all non-digits)
        $clean_phone = preg_replace('/[^0-9]/', '', $customer_phone);
        
        // Check if phone has at least 10 digits
        if (strlen($clean_phone) < 10) {
            $errors[] = "Phone number must have at least 10 digits";
        } elseif (strlen($clean_phone) > 15) {
            $errors[] = "Phone number is too long";
        }
    }
    
    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("
                INSERT INTO bookings (car_id, customer_name, customer_email, customer_phone, 
                                    pickup_location, pickup_date, return_date, total_days, total_amount, special_requests) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            
            $stmt->execute([
                $car_id, $customer_name, $customer_email, $customer_phone,
                $pickup_location, $pickup_date, $return_date, $total_days, $final_total, $special_requests
            ]);
            
            $booking_id = $pdo->lastInsertId();
            header("Location: confirmation.php?booking_id=$booking_id");
            exit;
        } catch (PDOException $e) {
            $errors[] = "Booking failed. Please try again.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Your Car - RentACar</title>
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
        .booking-container {
            display: grid;
            grid-template-columns: 1fr 400px;
            gap: 2rem;
            padding: 2rem 0;
        }

        .booking-form {
            background: white;
            padding: 2rem;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        }

        .form-title {
            font-size: 2rem;
            margin-bottom: 1.5rem;
            color: #333;
            border-bottom: 2px solid #667eea;
            padding-bottom: 0.5rem;
        }

        .form-section {
            margin-bottom: 2rem;
        }

        .section-title {
            font-size: 1.3rem;
            margin-bottom: 1rem;
            color: #555;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .form-group {
            margin-bottom: 1rem;
        }

        .form-group label {
            display: block;
            font-weight: 600;
            margin-bottom: 0.5rem;
            color: #555;
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 12px;
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

        .form-group input.error {
            border-color: #dc3545;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }

        .rental-details {
            background: #f8f9fa;
            padding: 1.5rem;
            border-radius: 10px;
            margin-bottom: 1.5rem;
        }

        .detail-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 0.5rem;
            padding: 0.5rem 0;
        }

        .detail-row:not(:last-child) {
            border-bottom: 1px solid #e1e5e9;
        }

        .detail-label {
            font-weight: 600;
            color: #555;
        }

        .detail-value {
            color: #333;
        }

        .error-message {
            background: #fee;
            color: #c33;
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1rem;
            border: 1px solid #fcc;
        }

        .error-list {
            list-style: none;
            margin: 0;
            padding: 0;
        }

        .error-list li {
            margin-bottom: 0.5rem;
        }

        .error-list li:before {
            content: "• ";
            color: #c33;
            font-weight: bold;
        }

        .phone-help {
            font-size: 0.9rem;
            color: #666;
            margin-top: 0.3rem;
        }

        .phone-examples {
            font-size: 0.8rem;
            color: #888;
            margin-top: 0.2rem;
            font-style: italic;
        }

        .submit-btn {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            padding: 15px 2rem;
            border-radius: 8px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            width: 100%;
            transition: transform 0.3s;
        }

        .submit-btn:hover {
            transform: translateY(-2px);
        }

        .submit-btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }

        /* Booking Summary */
        .booking-summary {
            background: white;
            padding: 2rem;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            height: fit-content;
            position: sticky;
            top: 2rem;
        }

        .summary-title {
            font-size: 1.5rem;
            margin-bottom: 1.5rem;
            color: #333;
            text-align: center;
        }

        .car-summary {
            text-align: center;
            margin-bottom: 2rem;
        }

        .car-image {
            width: 100%;
            height: 200px;
            object-fit: cover;
            border-radius: 10px;
            margin-bottom: 1rem;
        }

        .car-name {
            font-size: 1.3rem;
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
            grid-template-columns: 1fr 1fr;
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

        .price-breakdown {
            border-top: 2px solid #e1e5e9;
            padding-top: 1rem;
        }

        .price-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 0.5rem;
            padding: 0.5rem 0;
        }

        .price-row.total {
            border-top: 1px solid #e1e5e9;
            font-weight: bold;
            font-size: 1.2rem;
            color: #667eea;
        }

        .rating {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.3rem;
            color: #ffa500;
            margin-bottom: 1rem;
        }

        /* Responsive Design */
        @media (max-width: 1024px) {
            .booking-container {
                grid-template-columns: 1fr;
            }

            .booking-summary {
                position: static;
            }
        }

        @media (max-width: 768px) {
            .nav-links {
                display: none;
            }

            .form-row {
                grid-template-columns: 1fr;
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
        <div class="booking-container">
            <!-- Booking Form -->
            <div class="booking-form">
                <h2 class="form-title">
                    <i class="fas fa-calendar-check"></i> Complete Your Booking
                </h2>

                <?php if (!empty($errors)): ?>
                    <div class="error-message">
                        <i class="fas fa-exclamation-triangle"></i> Please fix the following errors:
                        <ul class="error-list">
                            <?php foreach($errors as $error): ?>
                                <li><?php echo htmlspecialchars($error); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <form method="POST" id="bookingForm">
                    <!-- Customer Information -->
                    <div class="form-section">
                        <h3 class="section-title">
                            <i class="fas fa-user"></i> Customer Information
                        </h3>
                        
                        <div class="form-group">
                            <label for="customer_name">Full Name *</label>
                            <input type="text" id="customer_name" name="customer_name" required 
                                   value="<?php echo htmlspecialchars($_POST['customer_name'] ?? ''); ?>"
                                   placeholder="Enter your full name">
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="customer_email">Email Address *</label>
                                <input type="email" id="customer_email" name="customer_email" required 
                                       value="<?php echo htmlspecialchars($_POST['customer_email'] ?? ''); ?>"
                                       placeholder="your.email@example.com">
                            </div>
                            <div class="form-group">
                                <label for="customer_phone">Phone Number *</label>
                                <input type="tel" id="customer_phone" name="customer_phone" required 
                                       value="<?php echo htmlspecialchars($_POST['customer_phone'] ?? ''); ?>"
                                       placeholder="Enter your phone number">
                                <div class="phone-help">
                                    Enter any format: with or without country code
                                </div>
                                <div class="phone-examples">
                                    Examples: (555) 123-4567, +1-555-123-4567, 5551234567
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Rental Details -->
                    <div class="form-section">
                        <h3 class="section-title">
                            <i class="fas fa-calendar-alt"></i> Rental Details
                        </h3>
                        
                        <div class="rental-details">
                            <div class="detail-row">
                                <span class="detail-label">Pickup Location:</span>
                                <span class="detail-value"><?php echo htmlspecialchars($pickup_location); ?></span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Pickup Date:</span>
                                <span class="detail-value"><?php echo date('F j, Y', strtotime($pickup_date)); ?></span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Return Date:</span>
                                <span class="detail-value"><?php echo date('F j, Y', strtotime($return_date)); ?></span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Rental Duration:</span>
                                <span class="detail-value"><?php echo $total_days; ?> day<?php echo $total_days > 1 ? 's' : ''; ?></span>
                            </div>
                        </div>
                    </div>

                    <!-- Additional Information -->
                    <div class="form-section">
                        <h3 class="section-title">
                            <i class="fas fa-info-circle"></i> Additional Information
                        </h3>
                        
                        <div class="form-group">
                            <label for="special_requests">Special Requests (Optional)</label>
                            <textarea id="special_requests" name="special_requests" rows="3" 
                                      placeholder="Any special requirements or requests..."><?php echo htmlspecialchars($_POST['special_requests'] ?? ''); ?></textarea>
                        </div>
                    </div>

                    <button type="submit" class="submit-btn" id="submitBtn">
                        <i class="fas fa-credit-card"></i> Confirm Booking
                    </button>
                </form>
            </div>

            <!-- Booking Summary -->
            <div class="booking-summary">
                <h3 class="summary-title">Booking Summary</h3>
                
                <div class="car-summary">
                    <img src="<?php echo htmlspecialchars($car['image_url']); ?>" 
                         alt="<?php echo htmlspecialchars($car['brand'] . ' ' . $car['model']); ?>" 
                         class="car-image"
                         onerror="this.src='https://via.placeholder.com/400x200/667eea/white?text=Car+Image'">
                    
                    <div class="car-name"><?php echo htmlspecialchars($car['brand'] . ' ' . $car['model']); ?></div>
                    <div class="car-type"><?php echo htmlspecialchars($car['car_type']); ?> • <?php echo $car['year']; ?></div>
                    
                    <div class="rating">
                        <i class="fas fa-star"></i>
                        <span><?php echo $car['rating']; ?></span>
                    </div>

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
                </div>

                <div class="price-breakdown">
                    <div class="price-row">
                        <span>Daily Rate:</span>
                        <span>$<?php echo number_format($car['price_per_day'], 2); ?></span>
                    </div>
                    <div class="price-row">
                        <span>Duration:</span>
                        <span><?php echo $total_days; ?> day<?php echo $total_days > 1 ? 's' : ''; ?></span>
                    </div>
                    <div class="price-row">
                        <span>Subtotal:</span>
                        <span>$<?php echo number_format($total_amount, 2); ?></span>
                    </div>
                    <div class="price-row">
                        <span>Tax (10%):</span>
                        <span>$<?php echo number_format($tax, 2); ?></span>
                    </div>
                    <div class="price-row total">
                        <span>Total Amount:</span>
                        <span>$<?php echo number_format($final_total, 2); ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Improved phone number validation and formatting
        document.addEventListener('DOMContentLoaded', function() {
            const phoneInput = document.getElementById('customer_phone');
            const form = document.getElementById('bookingForm');
            const submitBtn = document.getElementById('submitBtn');
            
            // Phone number formatting (optional - makes it look nicer)
            phoneInput.addEventListener('input', function(e) {
                let value = e.target.value.replace(/\D/g, ''); // Remove all non-digits
                
                // Don't format if it's too long or has country code
                if (value.length <= 10 && !value.startsWith('1')) {
                    // Format as (XXX) XXX-XXXX for US numbers
                    if (value.length >= 6) {
                        value = value.replace(/(\d{3})(\d{3})(\d{4})/, '($1) $2-$3');
                    } else if (value.length >= 3) {
                        value = value.replace(/(\d{3})(\d{0,3})/, '($1) $2');
                    }
                    e.target.value = value;
                }
            });
            
            // Form validation
            form.addEventListener('submit', function(e) {
                const name = document.getElementById('customer_name').value.trim();
                const email = document.getElementById('customer_email').value.trim();
                const phone = document.getElementById('customer_phone').value.trim();
                
                let isValid = true;
                let errorMessage = '';
                
                // Reset previous error styling
                document.querySelectorAll('.error').forEach(el => el.classList.remove('error'));
                
                // Name validation
                if (!name || name.length < 2) {
                    document.getElementById('customer_name').classList.add('error');
                    errorMessage += 'Please enter your full name.\n';
                    isValid = false;
                }
                
                // Email validation
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!email || !emailRegex.test(email)) {
                    document.getElementById('customer_email').classList.add('error');
                    errorMessage += 'Please enter a valid email address.\n';
                    isValid = false;
                }
                
                // Phone validation - much more flexible
                if (!phone) {
                    document.getElementById('customer_phone').classList.add('error');
                    errorMessage += 'Please enter your phone number.\n';
                    isValid = false;
                } else {
                    // Clean phone number (remove all non-digits)
                    const cleanPhone = phone.replace(/\D/g, '');
                    
                    // Check if phone has at least 10 digits (US standard)
                    // Allow up to 15 digits (international standard)
                    if (cleanPhone.length < 10) {
                        document.getElementById('customer_phone').classList.add('error');
                        errorMessage += 'Phone number must have at least 10 digits.\n';
                        isValid = false;
                    } else if (cleanPhone.length > 15) {
                        document.getElementById('customer_phone').classList.add('error');
                        errorMessage += 'Phone number is too long (max 15 digits).\n';
                        isValid = false;
                    }
                }
                
                if (!isValid) {
                    e.preventDefault();
                    alert('Please fix the following errors:\n\n' + errorMessage);
                    return false;
                }
                
                // Disable submit button to prevent double submission
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
                
                return true;
            });
            
            // Real-time validation feedback
            document.getElementById('customer_phone').addEventListener('blur', function() {
                const phone = this.value.trim();
                const cleanPhone = phone.replace(/\D/g, '');
                
                this.classList.remove('error');
                
                if (phone && (cleanPhone.length < 10 || cleanPhone.length > 15)) {
                    this.classList.add('error');
                }
            });
            
            document.getElementById('customer_email').addEventListener('blur', function() {
                const email = this.value.trim();
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                
                this.classList.remove('error');
                
                if (email && !emailRegex.test(email)) {
                    this.classList.add('error');
                }
            });
        });
    </script>
</body>
</html>
