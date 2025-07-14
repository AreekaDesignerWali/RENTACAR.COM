<?php
require_once 'db.php';

$booking_id = $_GET['booking_id'] ?? 0;

if (!$booking_id) {
    header('Location: index.php');
    exit;
}

// Get booking details
$stmt = $pdo->prepare("
    SELECT b.*, c.brand, c.model, c.image_url, c.car_type, c.year, c.seats, c.transmission, c.fuel_type
    FROM bookings b 
    JOIN cars c ON b.car_id = c.id 
    WHERE b.id = ?
");
$stmt->execute([$booking_id]);
$booking = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$booking) {
    header('Location: index.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Confirmed - RentACar</title>
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
            max-width: 800px;
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

        /* Confirmation Content */
        .confirmation-container {
            padding: 3rem 0;
        }

        .success-header {
            text-align: center;
            margin-bottom: 3rem;
        }

        .success-icon {
            font-size: 4rem;
            color: #28a745;
            margin-bottom: 1rem;
        }

        .success-title {
            font-size: 2.5rem;
            color: #333;
            margin-bottom: 0.5rem;
        }

        .success-subtitle {
            font-size: 1.2rem;
            color: #666;
        }

        .booking-details {
            background: white;
            padding: 2rem;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            margin-bottom: 2rem;
        }

        .details-title {
            font-size: 1.5rem;
            margin-bottom: 1.5rem;
            color: #333;
            border-bottom: 2px solid #667eea;
            padding-bottom: 0.5rem;
        }

        .booking-info {
            display: grid;
            grid-template-columns: 200px 1fr;
            gap: 2rem;
            margin-bottom: 2rem;
        }

        .car-image {
            width: 100%;
            height: 150px;
            object-fit: cover;
            border-radius: 10px;
        }

        .booking-summary {
            display: grid;
            gap: 1rem;
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            padding: 0.5rem 0;
            border-bottom: 1px solid #e1e5e9;
        }

        .info-row:last-child {
            border-bottom: none;
        }

        .info-label {
            font-weight: 600;
            color: #555;
        }

        .info-value {
            color: #333;
        }

        .booking-id {
            background: #667eea;
            color: white;
            padding: 1rem;
            border-radius: 10px;
            text-align: center;
            margin-bottom: 2rem;
        }

        .booking-id-label {
            font-size: 0.9rem;
            opacity: 0.9;
            margin-bottom: 0.5rem;
        }

        .booking-id-value {
            font-size: 1.5rem;
            font-weight: bold;
            letter-spacing: 2px;
        }

        .next-steps {
            background: #e8f4fd;
            padding: 2rem;
            border-radius: 15px;
            border-left: 4px solid #667eea;
            margin-bottom: 2rem;
        }

        .next-steps-title {
            font-size: 1.3rem;
            margin-bottom: 1rem;
            color: #333;
        }

        .steps-list {
            list-style: none;
            counter-reset: step-counter;
        }

        .steps-list li {
            counter-increment: step-counter;
            margin-bottom: 1rem;
            padding-left: 2rem;
            position: relative;
        }

        .steps-list li::before {
            content: counter(step-counter);
            position: absolute;
            left: 0;
            top: 0;
            background: #667eea;
            color: white;
            width: 1.5rem;
            height: 1.5rem;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.8rem;
            font-weight: bold;
        }

        .action-buttons {
            display: flex;
            gap: 1rem;
            justify-content: center;
            flex-wrap: wrap;
        }

        .btn {
            padding: 12px 2rem;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
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

        .btn-secondary {
            background: white;
            color: #667eea;
            border: 2px solid #667eea;
        }

        .contact-info {
            background: #f8f9fa;
            padding: 1.5rem;
            border-radius: 10px;
            text-align: center;
            margin-top: 2rem;
        }

        .contact-title {
            font-size: 1.2rem;
            margin-bottom: 1rem;
            color: #333;
        }

        .contact-details {
            display: flex;
            justify-content: center;
            gap: 2rem;
            flex-wrap: wrap;
        }

        .contact-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: #666;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .nav-links {
                display: none;
            }

            .booking-info {
                grid-template-columns: 1fr;
            }

            .contact-details {
                flex-direction: column;
                gap: 1rem;
            }

            .action-buttons {
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
        <div class="confirmation-container">
            <!-- Success Header -->
            <div class="success-header">
                <div class="success-icon">
                    <i class="fas fa-check-circle"></i>
                </div>
                <h1 class="success-title">Booking Confirmed!</h1>
                <p class="success-subtitle">Your car rental has been successfully booked</p>
            </div>

            <!-- Booking ID -->
            <div class="booking-id">
                <div class="booking-id-label">Your Booking Reference</div>
                <div class="booking-id-value">RC<?php echo str_pad($booking['id'], 6, '0', STR_PAD_LEFT); ?></div>
            </div>

            <!-- Booking Details -->
            <div class="booking-details">
                <h2 class="details-title">
                    <i class="fas fa-info-circle"></i> Booking Details
                </h2>

                <div class="booking-info">
                    <img src="<?php echo htmlspecialchars($booking['image_url']); ?>" 
                         alt="<?php echo htmlspecialchars($booking['brand'] . ' ' . $booking['model']); ?>" 
                         class="car-image">
                    
                    <div class="booking-summary">
                        <div class="info-row">
                            <span class="info-label">Vehicle:</span>
                            <span class="info-value"><?php echo htmlspecialchars($booking['brand'] . ' ' . $booking['model']); ?></span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Type:</span>
                            <span class="info-value"><?php echo htmlspecialchars($booking['car_type']); ?> (<?php echo $booking['year']; ?>)</span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Customer:</span>
                            <span class="info-value"><?php echo htmlspecialchars($booking['customer_name']); ?></span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Email:</span>
                            <span class="info-value"><?php echo htmlspecialchars($booking['customer_email']); ?></span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Phone:</span>
                            <span class="info-value"><?php echo htmlspecialchars($booking['customer_phone']); ?></span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Pickup Location:</span>
                            <span class="info-value"><?php echo htmlspecialchars($booking['pickup_location']); ?></span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Pickup Date:</span>
                            <span class="info-value"><?php echo date('F j, Y', strtotime($booking['pickup_date'])); ?></span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Return Date:</span>
                            <span class="info-value"><?php echo date('F j, Y', strtotime($booking['return_date'])); ?></span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Duration:</span>
                            <span class="info-value"><?php echo $booking['total_days']; ?> day<?php echo $booking['total_days'] > 1 ? 's' : ''; ?></span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Total Amount:</span>
                            <span class="info-value"><strong>$<?php echo number_format($booking['total_amount'], 2); ?></strong></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Next Steps -->
            <div class="next-steps">
                <h3 class="next-steps-title">
                    <i class="fas fa-list-check"></i> What's Next?
                </h3>
                <ol class="steps-list">
                    <li>You will receive a confirmation email shortly with all booking details</li>
                    <li>Bring a valid driver's license and credit card for pickup</li>
                    <li>Arrive at the pickup location 15 minutes before your scheduled time</li>
                    <li>Complete the vehicle inspection with our staff</li>
                    <li>Enjoy your rental and drive safely!</li>
                </ol>
            </div>

            <!-- Action Buttons -->
            <div class="action-buttons">
                <a href="index.php" class="btn btn-primary">
                    <i class="fas fa-home"></i> Back to Home
                </a>
                <a href="cars.php" class="btn btn-secondary">
                    <i class="fas fa-car"></i> Book Another Car
                </a>
                <button onclick="window.print()" class="btn btn-secondary">
                    <i class="fas fa-print"></i> Print Confirmation
                </button>
            </div>

            <!-- Contact Information -->
            <div class="contact-info">
                <h3 class="contact-title">Need Help? Contact Us</h3>
                <div class="contact-details">
                    <div class="contact-item">
                        <i class="fas fa-phone"></i>
                        <span>+1 (555) 123-4567</span>
                    </div>
                    <div class="contact-item">
                        <i class="fas fa-envelope"></i>
                        <span>support@rentacar.com</span>
                    </div>
                    <div class="contact-item">
                        <i class="fas fa-clock"></i>
                        <span>24/7 Customer Support</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Auto-print functionality (optional)
        // window.onload = function() {
        //     setTimeout(function() {
        //         if (confirm('Would you like to print this confirmation?')) {
        //             window.print();
        //         }
        //     }, 2000);
        // };

        // Copy booking reference to clipboard
        document.querySelector('.booking-id-value').addEventListener('click', function() {
            const text = this.textContent;
            navigator.clipboard.writeText(text).then(function() {
                alert('Booking reference copied to clipboard!');
            });
        });
    </script>
</body>
</html>
