<?php
require_once '../config/database.php';
requireAdmin();

$db = Database::getInstance();

// Get statistics
$stats = [
    'total_tours' => $db->fetch("SELECT COUNT(*) as count FROM tours")['count'],
    'active_tours' => $db->fetch("SELECT COUNT(*) as count FROM tours WHERE status = 'active'")['count'],
    'total_bookings' => $db->fetch("SELECT COUNT(*) as count FROM bookings")['count'],
    'pending_bookings' => $db->fetch("SELECT COUNT(*) as count FROM bookings WHERE status = 'pending'")['count'],
    'total_users' => $db->fetch("SELECT COUNT(*) as count FROM users WHERE role = 'user'")['count'],
    'total_revenue' => $db->fetch("SELECT SUM(total_price) as total FROM bookings WHERE payment_status = 'paid'")['total'] ?? 0,
    'newsletter_subscribers' => $db->fetch("SELECT COUNT(*) as count FROM newsletter WHERE status = 'active'")['count'],
    'pending_testimonials' => $db->fetch("SELECT COUNT(*) as count FROM testimonials WHERE status = 'pending'")['count']
];

// Get recent bookings
$recentBookings = $db->fetchAll(
    "SELECT b.*, t.title as tour_title 
     FROM bookings b 
     LEFT JOIN tours t ON b.tour_id = t.id 
     ORDER BY b.created_at DESC 
     LIMIT 10"
);

// Get popular tours
$popularTours = $db->fetchAll(
    "SELECT t.*, COUNT(b.id) as booking_count 
     FROM tours t 
     LEFT JOIN bookings b ON t.id = b.tour_id 
     GROUP BY t.id 
     ORDER BY booking_count DESC, t.views DESC 
     LIMIT 5"
);

// Get monthly revenue data for chart
$monthlyRevenue = $db->fetchAll(
    "SELECT DATE_FORMAT(created_at, '%Y-%m') as month, 
            SUM(total_price) as revenue,
            COUNT(*) as bookings
     FROM bookings 
     WHERE payment_status = 'paid' 
     AND created_at >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
     GROUP BY month 
     ORDER BY month"
);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Iburunga Travel</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body>
    <?php include 'includes/sidebar.php'; ?>
    
    <div class="main-content">
        <?php include 'includes/topbar.php'; ?>
        
        <div class="dashboard-container">
            <div class="dashboard-header">
                <h1>Dashboard</h1>
                <p>Welcome back, <?= $_SESSION['name'] ?>!</p>
            </div>

            <!-- Statistics Cards -->
            <div class="stats-grid">
                <div class="stat-card blue">
                    <div class="stat-icon">
                        <i class="fas fa-map-marked-alt"></i>
                    </div>
                    <div class="stat-details">
                        <h3><?= $stats['active_tours'] ?></h3>
                        <p>Active Tours</p>
                        <small><?= $stats['total_tours'] ?> total</small>
                    </div>
                </div>

                <div class="stat-card green">
                    <div class="stat-icon">
                        <i class="fas fa-calendar-check"></i>
                    </div>
                    <div class="stat-details">
                        <h3><?= $stats['total_bookings'] ?></h3>
                        <p>Total Bookings</p>
                        <small><?= $stats['pending_bookings'] ?> pending</small>
                    </div>
                </div>

                <div class="stat-card orange">
                    <div class="stat-icon">
                        <i class="fas fa-dollar-sign"></i>
                    </div>
                    <div class="stat-details">
                        <h3><?= formatPrice($stats['total_revenue']) ?></h3>
                        <p>Total Revenue</p>
                        <small>Paid bookings</small>
                    </div>
                </div>

                <div class="stat-card purple">
                    <div class="stat-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="stat-details">
                        <h3><?= $stats['total_users'] ?></h3>
                        <p>Registered Users</p>
                    </div>
                </div>

                <div class="stat-card teal">
                    <div class="stat-icon">
                        <i class="fas fa-envelope"></i>
                    </div>
                    <div class="stat-details">
                        <h3><?= $stats['newsletter_subscribers'] ?></h3>
                        <p>Newsletter Subscribers</p>
                    </div>
                </div>

                <div class="stat-card red">
                    <div class="stat-icon">
                        <i class="fas fa-star"></i>
                    </div>
                    <div class="stat-details">
                        <h3><?= $stats['pending_testimonials'] ?></h3>
                        <p>Pending Reviews</p>
                    </div>
                </div>
            </div>

            <!-- Charts Row -->
            <div class="charts-row">
                <div class="chart-card">
                    <h3>Monthly Revenue</h3>
                    <canvas id="revenueChart"></canvas>
                </div>

                <div class="chart-card">
                    <h3>Booking Status Distribution</h3>
                    <canvas id="statusChart"></canvas>
                </div>
            </div>

            <!-- Data Tables Row -->
            <div class="tables-row">
                <!-- Recent Bookings -->
                <div class="table-card">
                    <div class="card-header">
                        <h3>Recent Bookings</h3>
                        <a href="bookings.php" class="btn btn-sm">View All</a>
                    </div>
                    <div class="table-responsive">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Ref</th>
                                    <th>Customer</th>
                                    <th>Tour</th>
                                    <th>Date</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recentBookings as $booking): ?>
                                <tr>
                                    <td><strong><?= $booking['booking_ref'] ?></strong></td>
                                    <td><?= $booking['customer_name'] ?></td>
                                    <td><?= $booking['tour_title'] ?></td>
                                    <td><?= date('M d, Y', strtotime($booking['travel_date'])) ?></td>
                                    <td><?= formatPrice($booking['total_price']) ?></td>
                                    <td>
                                        <span class="badge badge-<?= $booking['status'] ?>">
                                            <?= ucfirst($booking['status']) ?>
                                        </span>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Popular Tours -->
                <div class="table-card">
                    <div class="card-header">
                        <h3>Popular Tours</h3>
                        <a href="tours.php" class="btn btn-sm">Manage Tours</a>
                    </div>
                    <div class="popular-tours-list">
                        <?php foreach ($popularTours as $tour): ?>
                        <div class="popular-tour-item">
                            <img src="../uploads/tours/<?= $tour['featured_image'] ?>" 
                                 alt="<?= $tour['title'] ?>"
                                 onerror="this.src='../assets/images/placeholder.jpg'">
                            <div class="tour-info">
                                <h4><?= $tour['title'] ?></h4>
                                <div class="tour-stats">
                                    <span><i class="fas fa-eye"></i> <?= $tour['views'] ?> views</span>
                                    <span><i class="fas fa-calendar"></i> <?= $tour['booking_count'] ?> bookings</span>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.0/chart.umd.min.js"></script>
    <script src="../assets/js/admin.js"></script>
    <script>
    // Revenue Chart
    const revenueCtx = document.getElementById('revenueChart').getContext('2d');
    const revenueData = <?= json_encode($monthlyRevenue) ?>;
    
    new Chart(revenueCtx, {
        type: 'line',
        data: {
            labels: revenueData.map(d => d.month),
            datasets: [{
                label: 'Revenue',
                data: revenueData.map(d => d.revenue),
                borderColor: '#4CAF50',
                backgroundColor: 'rgba(76, 175, 80, 0.1)',
                tension: 0.4,
                fill: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false }
            },
            scales: {
                y: { beginAtZero: true }
            }
        }
    });

    // Status Chart
    fetch('api/booking-stats.php')
        .then(res => res.json())
        .then(data => {
            const statusCtx = document.getElementById('statusChart').getContext('2d');
            new Chart(statusCtx, {
                type: 'doughnut',
                data: {
                    labels: Object.keys(data),
                    datasets: [{
                        data: Object.values(data),
                        backgroundColor: ['#FFC107', '#4CAF50', '#F44336', '#2196F3']
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false
                }
            });
        });
    </script>
</body>
</html>
