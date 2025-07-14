<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'db.php';

// Handle booking status updates
if ($_POST && isset($_POST['action'])) {
    $action = $_POST['action'];
    $booking_id = intval($_POST['booking_id'] ?? 0);
    
    if ($action === 'update_status' && $booking_id) {
        $new_status = $_POST['status'] ?? '';
        $valid_statuses = ['Pending', 'Confirmed', 'Cancelled', 'Completed'];
        
        if (in_array($new_status, $valid_statuses)) {
            try {
                $stmt = $pdo->prepare("UPDATE bookings SET booking_status = ? WHERE id = ?");
                $stmt->execute([$new_status, $booking_id]);
                $message = "Booking status updated successfully!";
            } catch (PDOException $e) {
                $error = "Error updating booking: " . $e->getMessage();
            }
        }
    }
}

// Get all bookings with car details
try {
    $stmt = $pdo->query("
        SELECT b.*, c.brand, c.model, c.image_url 
        FROM bookings b 
        JOIN cars c ON b.car_id = c.id 
        ORDER BY b.created_at DESC
    ");
    $bookings = $stmt->fetchAll();
} catch (PDOException $e) {
    $bookings = [];
    $error = "Error fetching bookings: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bookings Management - RentACar Admin</title>
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

        .admin-title {
            font-size: 2.5rem;
            color: #333;
            margin-bottom: 2rem;
        }

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

        .bookings-section {
            background: white;
            padding: 2rem;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        }

        .bookings-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1rem;
        }

        .bookings-table th,
        .bookings-table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #e1e5e9;
        }

        .bookings-table th {
            background: #f8f9fa;
            font-weight: 600;
            color: #555;
        }

        .bookings-table tr:hover {
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

        .status-pending {
            background: #fff3cd;
            color: #856404;
        }

        .status-confirmed {
            background: #d4edda;
            color: #155724;
        }

        .status-cancelled {
            background: #f8d7da;
            color: #721c24;
        }

        .status-completed {
            background: #d1ecf1;
            color: #0c5460;
        }

        .status-select {
            padding: 4px 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 0.9rem;
        }

        .btn {
            padding: 5px 10px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 0.9rem;
        }

        .btn-primary {
            background: #667eea;
            color: white;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: white;
            padding: 1.5rem;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            text-align: center;
        }

        .stat-number {
            font-size: 2rem;
            font-weight: bold;
            color: #667eea;
        }

        .stat-label {
            color: #666;
            margin-top: 0.5rem;
        }

        @media (max-width: 768px) {
            .nav-links {
                display: none;
            }

            .bookings-table {
                font-size: 0.8rem;
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
                        <li><a href="admin.php">Cars Admin</a></li>
                        <li><a href="bookings_admin.php">Bookings</a></li>
                    </ul>
                </nav>
            </div>
        </div>
    </header>

    <div class="container">
        <div class="admin-container">
            <h1 class="admin-title">
                <i class="fas fa-calendar-check"></i> Bookings Management
            </h1>

            <?php if (isset($message)): ?>
                <div class="message success">
                    <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>

            <?php if (isset($error)): ?>
                <div class="message error">
                    <i class="fas fa-exclamation-triangle"></i> <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <!-- Statistics -->
            <div class="stats-grid">
                <?php
                $total_bookings = count($bookings);
                $pending = count(array_filter($bookings, fn($b) => $b['booking_status'] === 'Pending'));
                $confirmed = count(array_filter($bookings, fn($b) => $b['booking_status'] === 'Confirmed'));
                $completed = count(array_filter($bookings, fn($b) => $b['booking_status'] === 'Completed'));
                $total_revenue = array_sum(array_column($bookings, 'total_amount'));
                ?>
                <div class="stat-card">
                    <div class="stat-number"><?php echo $total_bookings; ?></div>
                    <div class="stat-label">Total Bookings</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo $pending; ?></div>
                    <div class="stat-label">Pending</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo $confirmed; ?></div>
                    <div class="stat-label">Confirmed</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number">$<?php echo number_format($total_revenue, 0); ?></div>
                    <div class="stat-label">Total Revenue</div>
                </div>
            </div>

            <!-- Bookings List -->
            <div class="bookings-section">
                <h2 style="margin-bottom: 1rem;">
                    <i class="fas fa-list"></i> All Bookings (<?php echo count($bookings); ?>)
                </h2>

                <?php if (empty($bookings)): ?>
                    <p>No bookings found.</p>
                <?php else: ?>
                    <div style="overflow-x: auto;">
                        <table class="bookings-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Car</th>
                                    <th>Customer</th>
                                    <th>Contact</th>
                                    <th>Dates</th>
                                    <th>Days</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($bookings as $booking): ?>
                                <tr>
                                    <td>#<?php echo str_pad($booking['id'], 4, '0', STR_PAD_LEFT); ?></td>
                                    <td>
                                        <div style="display: flex; align-items: center; gap: 10px;">
                                            <img src="<?php echo htmlspecialchars($booking['image_url']); ?>" 
                                                 alt="Car" class="car-image-small"
                                                 onerror="this.src='https://via.placeholder.com/60x40/667eea/white?text=Car'">
                                            <div>
                                                <strong><?php echo htmlspecialchars($booking['brand'] . ' ' . $booking['model']); ?></strong>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($booking['customer_name']); ?></strong>
                                    </td>
                                    <td>
                                        <div style="font-size: 0.9rem;">
                                            <div><?php echo htmlspecialchars($booking['customer_email']); ?></div>
                                            <div><?php echo htmlspecialchars($booking['customer_phone']); ?></div>
                                        </div>
                                    </td>
                                    <td>
                                        <div style="font-size: 0.9rem;">
                                            <div><strong>From:</strong> <?php echo date('M j, Y', strtotime($booking['pickup_date'])); ?></div>
                                            <div><strong>To:</strong> <?php echo date('M j, Y', strtotime($booking['return_date'])); ?></div>
                                        </div>
                                    </td>
                                    <td><?php echo $booking['total_days']; ?></td>
                                    <td><strong>$<?php echo number_format($booking['total_amount'], 2); ?></strong></td>
                                    <td>
                                        <span class="status-badge status-<?php echo strtolower($booking['booking_status']); ?>">
                                            <?php echo htmlspecialchars($booking['booking_status']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <form method="POST" style="display: inline;">
                                            <input type="hidden" name="action" value="update_status">
                                            <input type="hidden" name="booking_id" value="<?php echo $booking['id']; ?>">
                                            <select name="status" class="status-select" onchange="this.form.submit()">
                                                <option value="Pending" <?php echo $booking['booking_status'] === 'Pending' ? 'selected' : ''; ?>>Pending</option>
                                                <option value="Confirmed" <?php echo $booking['booking_status'] === 'Confirmed' ? 'selected' : ''; ?>>Confirmed</option>
                                                <option value="Completed" <?php echo $booking['booking_status'] === 'Completed' ? 'selected' : ''; ?>>Completed</option>
                                                <option value="Cancelled" <?php echo $booking['booking_status'] === 'Cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                            </select>
                                        </form>
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
</body>
</html>
