<?php
require_once '../config/database.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

try {
    $db = Database::getInstance();
    
    $email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
    
    if (!$email) {
        throw new Exception('Please provide a valid email address');
    }
    
    // Check if already subscribed
    $existing = $db->fetch("SELECT * FROM newsletter WHERE email = ?", [$email]);
    
    if ($existing) {
        if ($existing['status'] === 'active') {
            throw new Exception('This email is already subscribed to our newsletter');
        } else {
            // Reactivate subscription
            $db->update('newsletter', ['status' => 'active'], 'email = ?', [$email]);
            echo json_encode([
                'success' => true,
                'message' => 'Welcome back! Your subscription has been reactivated.'
            ]);
            exit;
        }
    }
    
    // Insert new subscription
    $db->insert('newsletter', [
        'email' => $email,
        'status' => 'active'
    ]);
    
    // Send welcome email
    $subject = "Welcome to Iburunga Newsletter";
    $message = "
    <html>
    <body style='font-family: Arial, sans-serif;'>
        <h2>Welcome to Iburunga Travel!</h2>
        <p>Thank you for subscribing to our newsletter.</p>
        <p>You'll now receive updates about:</p>
        <ul>
            <li>New tour packages</li>
            <li>Special offers and discounts</li>
            <li>Travel tips and guides</li>
            <li>Rwanda travel news</li>
        </ul>
        <p>Best regards,<br>Iburunga Travel Team</p>
        <hr>
        <p style='font-size: 0.8rem; color: #888;'>
            If you wish to unsubscribe, <a href='" . SITE_URL . "/unsubscribe.php?email=" . urlencode($email) . "'>click here</a>.
        </p>
    </body>
    </html>
    ";
    
    $headers = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
    $headers .= "From: " . SITE_NAME . " <" . ADMIN_EMAIL . ">" . "\r\n";
    
    mail($email, $subject, $message, $headers);
    
    echo json_encode([
        'success' => true,
        'message' => 'Thank you for subscribing! Check your email for confirmation.'
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
