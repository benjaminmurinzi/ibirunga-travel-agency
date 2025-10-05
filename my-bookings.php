<?php
require_once 'config/database.php';
requireLogin();

$db = Database::getInstance();
$userId = $_SESSION['user_id'];

// Get user bookings
$bookings = $db->fetchAll(
    "SELECT b.*, t.title as tour_title, t.featured_image, t.location, t.duration 
     FROM bookings b 
     LEFT JOIN tours t ON b.tour_id = t.id 
     WHERE b.user_id = ? OR b.customer_email = ?
     ORDER BY b.created_at DESC",
    [$userId, $_SESSION['email']]
);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Bookings - Iburunga Travel</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <div class="page-header">
        <div class="container">
            <h1>My Bookings</h1>
            <p>View and manage your tour bookings</p>
        </div>
    </div>

    <section class="section">
        <div class="container">
            <div class="user-dashboard">
                <!-- User Info Card -->
                <div class="user-info-card">
                    <div class="user-avatar-large">
                        <?= strtoupper(substr($_SESSION['name'], 0, 1)) ?>
                    </div>
                    <h3><?= $_SESSION['name'] ?></h3>
                    <p><?= $_SESSION['email'] ?></p>
                    
                    <div class="user-stats">
                        <div class="stat-item">
                            <strong><?= count($bookings) ?></strong>
                            <span>Total Bookings</span>
                        </div>
                        <div class="stat-item">
                            <strong><?= count(array_filter($bookings, fn($b) => $b['status'] === 'confirmed')) ?></strong>
                            <span>Confirmed</span>
                        </div>
                        <div class="stat-item">
                            <strong><?= count(array_filter($bookings, fn($b) => $b['status'] === 'completed')) ?></strong>
                            <span>Completed</span>
                        </div>
                    </div>
                    
                    <a href="tours.php" class="btn btn-primary btn-block">
                        <i class="fas fa-plus"></i> Book New Tour
                    </a>
                </div>

                <!-- Bookings List -->
                <div class="bookings-container">
                    <?php if (empty($bookings)): ?>
                        <div class="no-bookings">
                            <i class="fas fa-calendar-times"></i>
                            <h3>No Bookings Yet</h3>
                            <p>Start exploring our amazing tours and make your first booking!</p>
                            <a href="tours.php" class="btn btn-primary">Browse Tours</a>
                        </div>
                    <?php else: ?>
                        <?php foreach ($bookings as $booking): ?>
                            <div class="booking-card">
                                <div class="booking-image">
                                    <img src="uploads/tours/<?= $booking['featured_image'] ?>" 
                                         alt="<?= $booking['tour_title'] ?>"
                                         onerror="this.src='assets/images/placeholder.jpg'">
                                    <span class="booking-status status-<?= $booking['status'] ?>">
                                        <?= ucfirst($booking['status']) ?>
                                    </span>
                                </div>
                                
                                <div class="booking-details">
                                    <div class="booking-header">
                                        <h3><?= $booking['tour_title'] ?></h3>
                                        <span class="booking-ref">#<?= $booking['booking_ref'] ?></span>
                                    </div>
                                    
                                    <div class="booking-info">
                                        <div class="info-item">
                                            <i class="fas fa-calendar"></i>
                                            <span><?= date('F d, Y', strtotime($booking['travel_date'])) ?></span>
                                        </div>
                                        <div class="info-item">
                                            <i class="fas fa-users"></i>
                                            <span><?= $booking['num_people'] ?> People</span>
                                        </div>
                                        <div class="info-item">
                                            <i class="fas fa-map-marker-alt"></i>
                                            <span><?= $booking['location'] ?></span>
                                        </div>
                                        <div class="info-item">
                                            <i class="fas fa-clock"></i>
                                            <span><?= $booking['duration'] ?></span>
                                        </div>
                                    </div>
                                    
                                    <div class="booking-footer">
                                        <div class="booking-price">
                                            <span>Total Amount</span>
                                            <strong><?= formatPrice($booking['total_price']) ?></strong>
                                            <small class="payment-status payment-<?= $booking['payment_status'] ?>">
                                                <?= ucfirst($booking['payment_status']) ?>
                                            </small>
                                        </div>
                                        
                                        <div class="booking-actions">
                                            <button onclick="viewBookingDetails(<?= htmlspecialchars(json_encode($booking)) ?>)" 
                                                    class="btn btn-sm">
                                                <i class="fas fa-eye"></i> View Details
                                            </button>
                                            
                                            <?php if ($booking['status'] === 'pending'): ?>
                                                <button onclick="cancelBooking('<?= $booking['booking_ref'] ?>')" 
                                                        class="btn btn-sm btn-secondary">
                                                    <i class="fas fa-times"></i> Cancel
                                                </button>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    
                                    <div class="booking-meta">
                                        <small>Booked on <?= date('M d, Y', strtotime($booking['created_at'])) ?></small>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>

    <!-- Booking Details Modal -->
    <div id="bookingModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Booking Details</h2>
                <span class="close" onclick="closeBookingModal()">&times;</span>
            </div>
            <div id="bookingModalContent"></div>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>

    <script src="assets/js/main.js"></script>
    <script>
    function viewBookingDetails(booking) {
        const content = `
            <div style="padding: 1.5rem;">
                <div style="text-align: center; margin-bottom: 2rem;">
                    <h3>${booking.tour_title}</h3>
                    <span class="badge badge-${booking.status}">${booking.status.toUpperCase()}</span>
                </div>
                
                <table style="width: 100%; border-collapse: collapse;">
                    <tr>
                        <td style="padding: 0.8rem; border-bottom: 1px solid #ddd;"><strong>Booking Reference:</strong></td>
                        <td style="padding: 0.8rem; border-bottom: 1px solid #ddd;">${booking.booking_ref}</td>
                    </tr>
                    <tr>
                        <td style="padding: 0.8rem; border-bottom: 1px solid #ddd;"><strong>Travel Date:</strong></td>
                        <td style="padding: 0.8rem; border-bottom: 1px solid #ddd;">${new Date(booking.travel_date).toLocaleDateString('en-US', {year: 'numeric', month: 'long', day: 'numeric'})}</td>
                    </tr>
                    <tr>
                        <td style="padding: 0.8rem; border-bottom: 1px solid #ddd;"><strong>Number of People:</strong></td>
                        <td style="padding: 0.8rem; border-bottom: 1px solid #ddd;">${booking.num_people} (${booking.customer_type})</td>
                    </tr>
                    <tr>
                        <td style="padding: 0.8rem; border-bottom: 1px solid #ddd;"><strong>Location:</strong></td>
                        <td style="padding: 0.8rem; border-bottom: 1px solid #ddd;">${booking.location}</td>
                    </tr>
                    <tr>
                        <td style="padding: 0.8rem; border-bottom: 1px solid #ddd;"><strong>Duration:</strong></td>
                        <td style="padding: 0.8rem; border-bottom: 1px solid #ddd;">${booking.duration}</td>
                    </tr>
                    <tr>
                        <td style="padding: 0.8rem; border-bottom: 1px solid #ddd;"><strong>Total Amount:</strong></td>
                        <td style="padding: 0.8rem; border-bottom: 1px solid #ddd;"><strong style="color: var(--primary); font-size: 1.2rem;">${formatPrice(booking.total_price)}</strong></td>
                    </tr>
                    <tr>
                        <td style="padding: 0.8rem; border-bottom: 1px solid #ddd;"><strong>Payment Status:</strong></td>
                        <td style="padding: 0.8rem; border-bottom: 1px solid #ddd;">
                            <span class="badge badge-${booking.payment_status === 'paid' ? 'active' : 'pending'}">${booking.payment_status.toUpperCase()}</span>
                        </td>
                    </tr>
                    ${booking.message ? `
                    <tr>
                        <td colspan="2" style="padding: 0.8rem;">
                            <strong>Your Message:</strong><br>
                            <p style="background: #f5f5f5; padding: 1rem; border-radius: 5px; margin-top: 0.5rem;">${booking.message}</p>
                        </td>
                    </tr>
                    ` : ''}
                </table>
                
                <div style="margin-top: 2rem; padding-top: 1.5rem; border-top: 1px solid #ddd;">
                    <p style="color: #666; font-size: 0.9rem;">
                        <i class="fas fa-info-circle"></i> 
                        For any changes or inquiries, please contact us at 
                        <a href="mailto:info@iburunga.com">info@iburunga.com</a> or 
                        <a href="tel:+250788123456">+250 788 123 456</a>
                    </p>
                </div>
            </div>
        `;
        
        document.getElementById('bookingModalContent').innerHTML = content;
        document.getElementById('bookingModal').style.display = 'flex';
    }
    
    function closeBookingModal() {
        document.getElementById('bookingModal').style.display = 'none';
    }
    
    function formatPrice(price) {
        return new Intl.NumberFormat('en-RW', {
            style: 'currency',
            currency: 'RWF',
            minimumFractionDigits: 0
        }).format(price);
    }
    
    function cancelBooking(ref) {
        if (confirm('Are you sure you want to cancel booking ' + ref + '?')) {
            alert('Please contact us to cancel your booking: info@iburunga.com or +250 788 123 456');
        }
    }
    
    // Close modal when clicking outside
    window.onclick = function(event) {
        const modal = document.getElementById('bookingModal');
        if (event.target === modal) {
            closeBookingModal();
        }
    }
    </script>
    
    <style>
        .user-dashboard {
            display: grid;
            grid-template-columns: 320px 1fr;
            gap: 2rem;
            margin: 3rem 0;
        }
        
        .user-info-card {
            background: white;
            padding: 2rem;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            height: fit-content;
            position: sticky;
            top: 100px;
            text-align: center;
        }
        
        .user-avatar-large {
            width: 100px;
            height: 100px;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 3rem;
            font-weight: bold;
            margin: 0 auto 1rem;
        }
        
        .user-info-card h3 {
            margin-bottom: 0.5rem;
            color: var(--dark);
        }
        
        .user-info-card p {
            color: var(--gray);
            margin-bottom: 1.5rem;
        }
        
        .user-stats {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1rem;
            margin: 1.5rem 0;
            padding: 1.5rem 0;
            border-top: 1px solid var(--light);
            border-bottom: 1px solid var(--light);
        }
        
        .user-stats .stat-item {
            text-align: center;
        }
        
        .user-stats strong {
            display: block;
            font-size: 1.5rem;
            color: var(--primary);
            margin-bottom: 0.3rem;
        }
        
        .user-stats span {
            font-size: 0.85rem;
            color: var(--gray);
        }
        
        .bookings-container {
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
        }
        
        .booking-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            overflow: hidden;
            display: grid;
            grid-template-columns: 250px 1fr;
            transition: all 0.3s;
        }
        
        .booking-card:hover {
            box-shadow: 0 10px 30px rgba(0,0,0,0.15);
            transform: translateY(-2px);
        }
        
        .booking-image {
            position: relative;
            height: 200px;
        }
        
        .booking-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .booking-status {
            position: absolute;
            top: 10px;
            right: 10px;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
            color: white;
        }
        
        .status-pending { background: #F39C12; }
        .status-confirmed { background: #3498DB; }
        .status-completed { background: #27AE60; }
        .status-cancelled { background: #E74C3C; }
        
        .booking-details {
            padding: 1.5rem;
            display: flex;
            flex-direction: column;
        }
        
        .booking-header {
            display: flex;
            justify-content: space-between;
            align-items: start;
            margin-bottom: 1rem;
        }
        
        .booking-header h3 {
            margin: 0;
            color: var(--dark);
        }
        
        .booking-ref {
            background: var(--light);
            padding: 0.3rem 0.8rem;
            border-radius: 15px;
            font-size: 0.85rem;
            font-weight: 600;
            color: var(--gray);
        }
        
        .booking-info {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 1rem;
            margin-bottom: 1.5rem;
        }
        
        .info-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.9rem;
            color: var(--gray);
        }
        
        .info-item i {
            color: var(--primary);
        }
        
        .booking-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-top: 1rem;
            border-top: 1px solid var(--light);
        }
        
        .booking-price {
            display: flex;
            flex-direction: column;
            gap: 0.3rem;
        }
        
        .booking-price span {
            font-size: 0.85rem;
            color: var(--gray);
        }
        
        .booking-price strong {
            font-size: 1.5rem;
            color: var(--primary);
        }
        
        .payment-status {
            display: inline-block;
            padding: 0.2rem 0.6rem;
            border-radius: 10px;
            font-size: 0.75rem;
            font-weight: 600;
        }
        
        .payment-paid {
            background: #D4EDDA;
            color: #155724;
        }
        
        .payment-unpaid {
            background: #FFF3CD;
            color: #856404;
        }
        
        .payment-refunded {
            background: #F8D7DA;
            color: #721C24;
        }
        
        .booking-actions {
            display: flex;
            gap: 0.5rem;
        }
        
        .booking-meta {
            margin-top: 1rem;
            padding-top: 1rem;
            border-top: 1px solid var(--light);
        }
        
        .booking-meta small {
            color: var(--gray);
            font-size: 0.85rem;
        }
        
        .no-bookings {
            text-align: center;
            padding: 4rem 2rem;
            background: white;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
        }
        
        .no-bookings i {
            font-size: 4rem;
            color: var(--gray);
            opacity: 0.5;
            margin-bottom: 1rem;
        }
        
        .no-bookings h3 {
            color: var(--dark);
            margin-bottom: 0.5rem;
        }
        
        .no-bookings p {
            color: var(--gray);
            margin-bottom: 1.5rem;
        }
        
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0,0,0,0.5);
            z-index: 9999;
            align-items: center;
            justify-content: center;
            overflow-y: auto;
            padding: 2rem;
        }
        
        .modal.active,
        .modal[style*="display: flex"] {
            display: flex !important;
        }
        
        @media (max-width: 1024px) {
            .user-dashboard {
                grid-template-columns: 1fr;
            }
            
            .user-info-card {
                position: static;
            }
            
            .booking-card {
                grid-template-columns: 1fr;
            }
            
            .booking-image {
                height: 180px;
            }
            
            .booking-info {
                grid-template-columns: repeat(2, 1fr);
            }
        }
        
        @media (max-width: 768px) {
            .booking-footer {
                flex-direction: column;
                gap: 1rem;
                align-items: stretch;
            }
            
            .booking-actions {
                width: 100%;
            }
            
            .booking-actions .btn {
                flex: 1;
            }
        }
    </style>
</body>
</html>
