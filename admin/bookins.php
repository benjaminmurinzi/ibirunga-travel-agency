<?php
require_once '../config/database.php';
requireAdmin();

$db = Database::getInstance();

// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    $bookingId = $_POST['booking_id'];
    
    if ($action === 'update_status') {
        $status = $_POST['status'];
        $db->update('bookings', ['status' => $status], 'id = ?', [$bookingId]);
        $_SESSION['success'] = 'Booking status updated successfully!';
    } elseif ($action === 'update_payment') {
        $paymentStatus = $_POST['payment_status'];
        $db->update('bookings', ['payment_status' => $paymentStatus], 'id = ?', [$bookingId]);
        $_SESSION['success'] = 'Payment status updated successfully!';
    } elseif ($action === 'delete') {
        $db->delete('bookings', 'id = ?', [$bookingId]);
        $_SESSION['success'] = 'Booking deleted successfully!';
    }
    redirect('/admin/bookings.php');
}

// Get filter parameters
$status = $_GET['status'] ?? '';
$paymentStatus = $_GET['payment_status'] ?? '';
$search = $_GET['search'] ?? '';

// Build query
$sql = "SELECT b.*, t.title as tour_title, u.name as user_name 
        FROM bookings b 
        LEFT JOIN tours t ON b.tour_id = t.id 
        LEFT JOIN users u ON b.user_id = u.id 
        WHERE 1=1";
$params = [];

if ($status) {
    $sql .= " AND b.status = ?";
    $params[] = $status;
}

if ($paymentStatus) {
    $sql .= " AND b.payment_status = ?";
    $params[] = $paymentStatus;
}

if ($search) {
    $sql .= " AND (b.booking_ref LIKE ? OR b.customer_name LIKE ? OR b.customer_email LIKE ? OR t.title LIKE ?)";
    $searchTerm = "%$search%";
    $params = array_merge($params, [$searchTerm, $searchTerm, $searchTerm, $searchTerm]);
}

$sql .= " ORDER BY b.created_at DESC";

$bookings = $db->fetchAll($sql, $params);

// Get statistics
$stats = [
    'total' => $db->fetch("SELECT COUNT(*) as count FROM bookings")['count'],
    'pending' => $db->fetch("SELECT COUNT(*) as count FROM bookings WHERE status = 'pending'")['count'],
    'confirmed' => $db->fetch("SELECT COUNT(*) as count FROM bookings WHERE status = 'confirmed'")['count'],
    'completed' => $db->fetch("SELECT COUNT(*) as count FROM bookings WHERE status = 'completed'")['count'],
    'cancelled' => $db->fetch("SELECT COUNT(*) as count FROM bookings WHERE status = 'cancelled'")['count'],
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Bookings - Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body>
    <?php include 'includes/sidebar.php'; ?>
    
    <div class="main-content">
        <?php include 'includes/topbar.php'; ?>
        
        <div class="dashboard-container">
            <div class="page-header">
                <h1>Manage Bookings</h1>
            </div>

            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success"><?= $_SESSION['success']; unset($_SESSION['success']); ?></div>
            <?php endif; ?>

            <!-- Booking Stats -->
            <div class="stats-grid" style="grid-template-columns: repeat(5, 1fr);">
                <div class="stat-card blue">
                    <div class="stat-details">
                        <h3><?= $stats['total'] ?></h3>
                        <p>Total Bookings</p>
                    </div>
                </div>
                <div class="stat-card orange">
                    <div class="stat-details">
                        <h3><?= $stats['pending'] ?></h3>
                        <p>Pending</p>
                    </div>
                </div>
                <div class="stat-card blue">
                    <div class="stat-details">
                        <h3><?= $stats['confirmed'] ?></h3>
                        <p>Confirmed</p>
                    </div>
                </div>
                <div class="stat-card green">
                    <div class="stat-details">
                        <h3><?= $stats['completed'] ?></h3>
                        <p>Completed</p>
                    </div>
                </div>
                <div class="stat-card red">
                    <div class="stat-details">
                        <h3><?= $stats['cancelled'] ?></h3>
                        <p>Cancelled</p>
                    </div>
                </div>
            </div>

            <!-- Filters -->
            <div class="filters-bar">
                <form method="GET" style="display: flex; gap: 1rem; flex: 1;">
                    <input type="text" name="search" placeholder="Search bookings..." 
                           value="<?= htmlspecialchars($search) ?>" class="form-control">
                    
                    <select name="status" class="form-control">
                        <option value="">All Status</option>
                        <option value="pending" <?= $status === 'pending' ? 'selected' : '' ?>>Pending</option>
                        <option value="confirmed" <?= $status === 'confirmed' ? 'selected' : '' ?>>Confirmed</option>
                        <option value="cancelled" <?= $status === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                        <option value="completed" <?= $status === 'completed' ? 'selected' : '' ?>>Completed</option>
                    </select>
                    
                    <select name="payment_status" class="form-control">
                        <option value="">All Payments</option>
                        <option value="unpaid" <?= $paymentStatus === 'unpaid' ? 'selected' : '' ?>>Unpaid</option>
                        <option value="paid" <?= $paymentStatus === 'paid' ? 'selected' : '' ?>>Paid</option>
                        <option value="refunded" <?= $paymentStatus === 'refunded' ? 'selected' : '' ?>>Refunded</option>
                    </select>
                    
                    <button type="submit" class="btn btn-primary">Filter</button>
                    <a href="bookings.php" class="btn btn-secondary">Reset</a>
                </form>
                
                <button onclick="exportToCSV()" class="btn btn-secondary">
                    <i class="fas fa-download"></i> Export
                </button>
            </div>

            <!-- Bookings Table -->
            <div class="table-card">
                <div class="table-responsive">
                    <table class="data-table" id="bookingsTable">
                        <thead>
                            <tr>
                                <th>Ref</th>
                                <th>Customer</th>
                                <th>Tour</th>
                                <th>Date</th>
                                <th>People</th>
                                <th>Amount</th>
                                <th>Status</th>
                                <th>Payment</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($bookings as $booking): ?>
                            <tr>
                                <td>
                                    <strong><?= $booking['booking_ref'] ?></strong><br>
                                    <small><?= date('M d, Y', strtotime($booking['created_at'])) ?></small>
                                </td>
                                <td>
                                    <strong><?= $booking['customer_name'] ?></strong><br>
                                    <small><?= $booking['customer_email'] ?></small><br>
                                    <small><?= $booking['customer_phone'] ?></small>
                                </td>
                                <td><?= $booking['tour_title'] ?></td>
                                <td><?= date('M d, Y', strtotime($booking['travel_date'])) ?></td>
                                <td>
                                    <?= $booking['num_people'] ?> 
                                    <small>(<?= ucfirst($booking['customer_type']) ?>)</small>
                                </td>
                                <td><strong><?= formatPrice($booking['total_price']) ?></strong></td>
                                <td>
                                    <select class="badge badge-<?= $booking['status'] ?>" 
                                            onchange="updateStatus(<?= $booking['id'] ?>, this.value, 'status')"
                                            style="border: none; cursor: pointer;">
                                        <option value="pending" <?= $booking['status'] === 'pending' ? 'selected' : '' ?>>Pending</option>
                                        <option value="confirmed" <?= $booking['status'] === 'confirmed' ? 'selected' : '' ?>>Confirmed</option>
                                        <option value="completed" <?= $booking['status'] === 'completed' ? 'selected' : '' ?>>Completed</option>
                                        <option value="cancelled" <?= $booking['status'] === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                                    </select>
                                </td>
                                <td>
                                    <select class="badge badge-<?= $booking['payment_status'] === 'paid' ? 'active' : 'pending' ?>" 
                                            onchange="updateStatus(<?= $booking['id'] ?>, this.value, 'payment')"
                                            style="border: none; cursor: pointer;">
                                        <option value="unpaid" <?= $booking['payment_status'] === 'unpaid' ? 'selected' : '' ?>>Unpaid</option>
                                        <option value="paid" <?= $booking['payment_status'] === 'paid' ? 'selected' : '' ?>>Paid</option>
                                        <option value="refunded" <?= $booking['payment_status'] === 'refunded' ? 'selected' : '' ?>>Refunded</option>
                                    </select>
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <button class="btn-icon" onclick="viewBooking(<?= $booking['id'] ?>)" title="View">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button class="btn-icon btn-danger" 
                                                onclick="deleteBooking(<?= $booking['id'] ?>, '<?= $booking['booking_ref'] ?>')" 
                                                title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- View Booking Modal -->
    <div id="viewBookingModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Booking Details</h2>
                <span class="close" onclick="closeModal('viewBookingModal')">&times;</span>
            </div>
            <div id="bookingDetailsContent"></div>
        </div>
    </div>

    <script src="../assets/js/admin.js"></script>
    <script>
    function updateStatus(bookingId, value, type) {
        if (confirm('Update this booking ' + type + '?')) {
            const form = document.createElement('form');
            form.method = 'POST';
            
            if (type === 'status') {
                form.innerHTML = `
                    <input type="hidden" name="action" value="update_status">
                    <input type="hidden" name="booking_id" value="${bookingId}">
                    <input type="hidden" name="status" value="${value}">
                `;
            } else {
                form.innerHTML = `
                    <input type="hidden" name="action" value="update_payment">
                    <input type="hidden" name="booking_id" value="${bookingId}">
                    <input type="hidden" name="payment_status" value="${value}">
                `;
            }
            
            document.body.appendChild(form);
            form.submit();
        }
    }
    
    function viewBooking(bookingId) {
        fetch(`api/get-booking.php?id=${bookingId}`)
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    const booking = data.booking;
                    document.getElementById('bookingDetailsContent').innerHTML = `
                        <div style="padding: 1.5rem;">
                            <table style="width: 100%; border-collapse: collapse;">
                                <tr><td style="padding: 0.5rem; border-bottom: 1px solid #ddd;"><strong>Reference:</strong></td>
                                    <td style="padding: 0.5rem; border-bottom: 1px solid #ddd;">${booking.booking_ref}</td></tr>
                                <tr><td style="padding: 0.5rem; border-bottom: 1px solid #ddd;"><strong>Customer:</strong></td>
                                    <td style="padding: 0.5rem; border-bottom: 1px solid #ddd;">${booking.customer_name}</td></tr>
                                <tr><td style="padding: 0.5rem; border-bottom: 1px solid #ddd;"><strong>Email:</strong></td>
                                    <td style="padding: 0.5rem; border-bottom: 1px solid #ddd;">${booking.customer_email}</td></tr>
                                <tr><td style="padding: 0.5rem; border-bottom: 1px solid #ddd;"><strong>Phone:</strong></td>
                                    <td style="padding: 0.5rem; border-bottom: 1px solid #ddd;">${booking.customer_phone}</td></tr>
                                <tr><td style="padding: 0.5rem; border-bottom: 1px solid #ddd;"><strong>Tour:</strong></td>
                                    <td style="padding: 0.5rem; border-bottom: 1px solid #ddd;">${booking.tour_title}</td></tr>
                                <tr><td style="padding: 0.5rem; border-bottom: 1px solid #ddd;"><strong>Travel Date:</strong></td>
                                    <td style="padding: 0.5rem; border-bottom: 1px solid #ddd;">${booking.travel_date}</td></tr>
                                <tr><td style="padding: 0.5rem; border-bottom: 1px solid #ddd;"><strong>People:</strong></td>
                                    <td style="padding: 0.5rem; border-bottom: 1px solid #ddd;">${booking.num_people} (${booking.customer_type})</td></tr>
                                <tr><td style="padding: 0.5rem; border-bottom: 1px solid #ddd;"><strong>Total:</strong></td>
                                    <td style="padding: 0.5rem; border-bottom: 1px solid #ddd;"><strong>${booking.total_price}</strong></td></tr>
                                <tr><td style="padding: 0.5rem; border-bottom: 1px solid #ddd;"><strong>Status:</strong></td>
                                    <td style="padding: 0.5rem; border-bottom: 1px solid #ddd;">${booking.status}</td></tr>
                                <tr><td style="padding: 0.5rem; border-bottom: 1px solid #ddd;"><strong>Payment:</strong></td>
                                    <td style="padding: 0.5rem; border-bottom: 1px solid #ddd;">${booking.payment_status}</td></tr>
                                ${booking.message ? `<tr><td colspan="2" style="padding: 0.5rem;"><strong>Message:</strong><br>${booking.message}</td></tr>` : ''}
                            </table>
                        </div>
                    `;
                    openModal('viewBookingModal');
                }
            });
    }
    
    function deleteBooking(id, ref) {
        if (confirm(`Delete booking ${ref}?`)) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.innerHTML = `
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="booking_id" value="${id}">
            `;
            document.body.appendChild(form);
            form.submit();
        }
    }
    
    function exportToCSV() {
        const table = document.getElementById('bookingsTable');
        const rows = table.querySelectorAll('tr');
        let csv = [];
        
        rows.forEach(row => {
            const cols = row.querySelectorAll('td, th');
            const csvRow = [];
            cols.forEach((col, index) => {
                if (index < 8) { // Skip actions column
                    csvRow.push('"' + col.textContent.trim().replace(/"/g, '""') + '"');
                }
            });
            csv.push(csvRow.join(','));
        });
        
        const csvContent = csv.join('\n');
        const blob = new Blob([csvContent], { type: 'text/csv' });
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = 'bookings_' + new Date().toISOString().split('T')[0] + '.csv';
        a.click();
        window.URL.revokeObjectURL(url);
    }
    </script>
</body>
</html>
