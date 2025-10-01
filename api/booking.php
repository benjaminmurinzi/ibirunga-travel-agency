<?php
require_once '../config/database.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

try {
    $db = Database::getInstance();
    
    // Validate input
    $tourId = filter_input(INPUT_POST, 'tour_id', FILTER_VALIDATE_INT);
    $customerName = sanitize($_POST['customer_name'] ?? '');
    $customerEmail = filter_input(INPUT_POST, 'customer_email', FILTER_VALIDATE_EMAIL);
    $customerPhone = sanitize($_POST['customer_phone'] ?? '');
    $customerType = $_POST['customer_type'] ?? '';
    $numPeople = filter_input(INPUT_POST, 'num_people', FILTER_VALIDATE_INT);
    $travelDate = $_POST['travel_date'] ?? '';
    $message = sanitize($_POST['message'] ?? '');
    
    // Validation
    if (!$tourId || !$customerName || !$customerEmail || !$customerPhone || 
        !in_array($customerType, ['local', 'foreign']) || !$numPeople || !$travelDate) {
        throw new Exception('All required fields must be filled');
    }
    
    // Get tour details
    $tour = $db->fetch("SELECT * FROM tours WHERE id = ? AND status = 'active'", [$tourId]);
    if (!$tour) {
        throw new Exception('Tour not found');
    }
    
    // Check max people
    if ($numPeople > $tour['max_people']) {
        throw new Exception('Number of people exceeds maximum allowed');
    }
    
    // Calculate total price
    $price = $customerType === 'local' ? $tour['price_local'] : $tour['price_foreign'];
    $totalPrice = $price * $numPeople;
    
    // Generate booking reference
    $bookingRef = generateBookingRef();
    
    // Insert booking
    $bookingData = [
        'booking_ref' => $bookingRef,
        'tour_id' => $tourId,
        'customer_name' => $customerName,
        'customer_email' => $customerEmail,
        'customer_phone' => $customerPhone,
        'customer_type' => $customerType,
        'num_people' => $numPeople,
        'travel_date' => $travelDate,
        'total_price' => $totalPrice,
        'message' => $message,
        'status' => 'pending',
        'payment_status' => 'unpaid'
    ];
    
    if (isLoggedIn()) {
        $bookingData['user_id'] = $_SESSION['user_id'];
    }
    
    $bookingId = $db->insert('bookings', $bookingData);
    
    // Send email to customer
    sendBookingConfirmationEmail($customerEmail, $customerName, $bookingRef, $tour['title'], $travelDate, $numPeople, $totalPrice, $customerType);
    
    // Send notification to admin
    sendAdminNotificationEmail($bookingRef, $tour['title'], $customerName, $customerEmail, $travelDate, $numPeople, $totalPrice);
    
    // Mark as notified
    $db->update('bookings', ['admin_notified' => 1], 'id = ?', [$bookingId]);
    
    echo json_encode([
        'success' => true,
        'message' => 'Booking created successfully',
        'booking_ref' => $bookingRef
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

function sendBookingConfirmationEmail($email, $name, $ref, $tourTitle, $date, $people, $price, $type) {
    $currency = $type === 'local' ? 'RWF' : 'USD';
    $formattedPrice = $type === 'local' ? number_format($price, 0) . ' ' . $currency : '$' . number_format($price, 2);
    
    $subject = "Booking Confirmation - " . $ref;
    $message = "
    <html>
    <body style='font-family: Arial, sans-serif;'>
        <h2>Booking Confirmation</h2>
        <p>Dear $name,</p>
        <p>Thank you for booking with Iburunga Travel Agency!</p>
        
        <h3>Booking Details:</h3>
        <ul>
            <li><strong>Reference:</strong> $ref</li>
            <li><strong>Tour:</strong> $tourTitle</li>
            <li><strong>Date:</strong> $date</li>
            <li><strong>Number of People:</strong> $people</li>
            <li><strong>Total Price:</strong> $formattedPrice</li>
        </ul>
        
        <p>We will contact you shortly to confirm your booking and provide payment details.</p>
        
        <p>Best regards,<br>Iburunga Travel Team</p>
    </body>
    </html>
    ";
    
    $headers = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
    $headers .= "From: " . SITE_NAME . " <" . ADMIN_EMAIL . ">" . "\r\n";
    
    mail($email, $subject, $message, $headers);
}

function sendAdminNotificationEmail($ref, $tourTitle, $customerName, $customerEmail, $date, $people, $price) {
    $subject = "New Booking Alert - " . $ref;
    $message = "
    <html>
    <body style='font-family: Arial, sans-serif;'>
        <h2>New Booking Received</h2>
        
        <h3>Booking Details:</h3>
        <ul>
            <li><strong>Reference:</strong> $ref</li>
            <li><strong>Tour:</strong> $tourTitle</li>
            <li><strong>Customer:</strong> $customerName</li>
            <li><strong>Email:</strong> $customerEmail</li>
            <li><strong>Date:</strong> $date</li>
            <li><strong>People:</strong> $people</li>
            <li><strong>Total:</strong> $price</li>
        </ul>
        
        <p><a href='" . SITE_URL . "/admin/bookings.php'>View in Admin Dashboard</a></p>
    </body>
    </html>
    ";
    
    $headers = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
    $headers .= "From: " . SITE_NAME . " <" . ADMIN_EMAIL . ">" . "\r\n";
    
    mail(ADMIN_EMAIL, $subject, $message, $headers);
}
?>
