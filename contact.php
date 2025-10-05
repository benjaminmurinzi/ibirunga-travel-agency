<?php
require_once 'config/database.php';

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize($_POST['name'] ?? '');
    $email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
    $phone = sanitize($_POST['phone'] ?? '');
    $subject = sanitize($_POST['subject'] ?? '');
    $message = sanitize($_POST['message'] ?? '');
    
    if (!$name || !$email || !$subject || !$message) {
        $error = 'Please fill in all required fields';
    } else {
        // Send email to admin
        $emailSubject = "Contact Form: $subject";
        $emailMessage = "
        <html>
        <body style='font-family: Arial, sans-serif;'>
            <h2>New Contact Form Submission</h2>
            <p><strong>From:</strong> $name</p>
            <p><strong>Email:</strong> $email</p>
            <p><strong>Phone:</strong> $phone</p>
            <p><strong>Subject:</strong> $subject</p>
            <hr>
            <p><strong>Message:</strong></p>
            <p>$message</p>
            <hr>
            <p><small>Sent from Iburunga Travel website</small></p>
        </body>
        </html>
        ";
        
        $headers = "MIME-Version: 1.0" . "\r\n";
        $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
        $headers .= "From: $name <$email>" . "\r\n";
        $headers .= "Reply-To: $email" . "\r\n";
        
        if (mail(ADMIN_EMAIL, $emailSubject, $emailMessage, $headers)) {
            // Send confirmation to user
            $confirmSubject = "Thank you for contacting Iburunga Travel";
            $confirmMessage = "
            <html>
            <body style='font-family: Arial, sans-serif;'>
                <h2>Thank You for Contacting Us!</h2>
                <p>Hi $name,</p>
                <p>We have received your message and will get back to you within 24 hours.</p>
                <p><strong>Your Message:</strong></p>
                <p style='background: #f5f5f5; padding: 15px; border-radius: 5px;'>$message</p>
                <p>Best regards,<br>Iburunga Travel Team</p>
            </body>
            </html>
            ";
            
            $confirmHeaders = "MIME-Version: 1.0" . "\r\n";
            $confirmHeaders .= "Content-type:text/html;charset=UTF-8" . "\r\n";
            $confirmHeaders .= "From: " . SITE_NAME . " <" . ADMIN_EMAIL . ">" . "\r\n";
            
            mail($email, $confirmSubject, $confirmMessage, $confirmHeaders);
            
            $success = 'Thank you! Your message has been sent successfully. We will contact you soon.';
            
            // Clear form
            $_POST = [];
        } else {
            $error = 'Failed to send message. Please try again or contact us directly.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us - Iburunga Travel</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <div class="page-header">
        <div class="container">
            <h1>Contact Us</h1>
            <p>We'd love to hear from you!</p>
        </div>
    </div>

    <section class="contact-section">
        <div class="container">
            <div class="contact-grid">
                <!-- Contact Information -->
                <div class="contact-info">
                    <h2>Get in Touch</h2>
                    <p>Have questions about our tours? Want to make a special request? We're here to help!</p>
                    
                    <div class="contact-details">
                        <div class="contact-item">
                            <div class="contact-icon">
                                <i class="fas fa-map-marker-alt"></i>
                            </div>
                            <div class="contact-text">
                                <h4>Address</h4>
                                <p>KN 4 Ave, Kigali<br>Rwanda, East Africa</p>
                            </div>
                        </div>
                        
                        <div class="contact-item">
                            <div class="contact-icon">
                                <i class="fas fa-phone"></i>
                            </div>
                            <div class="contact-text">
                                <h4>Phone</h4>
                                <p>+250 788 123 456<br>+250 788 654 321</p>
                            </div>
                        </div>
                        
                        <div class="contact-item">
                            <div class="contact-icon">
                                <i class="fas fa-envelope"></i>
                            </div>
                            <div class="contact-text">
                                <h4>Email</h4>
                                <p>info@iburunga.com<br>bookings@iburunga.com</p>
                            </div>
                        </div>
                        
                        <div class="contact-item">
                            <div class="contact-icon">
                                <i class="fas fa-clock"></i>
                            </div>
                            <div class="contact-text">
                                <h4>Business Hours</h4>
                                <p>Monday - Friday: 8:00 AM - 6:00 PM<br>
                                   Saturday: 9:00 AM - 4:00 PM<br>
                                   Sunday: Closed</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="social-contact">
                        <h4>Follow Us</h4>
                        <div class="social-links">
                            <a href="#" target="_blank" title="Facebook">
                                <i class="fab fa-facebook-f"></i>
                            </a>
                            <a href="#" target="_blank" title="Twitter">
                                <i class="fab fa-twitter"></i>
                            </a>
                            <a href="#" target="_blank" title="Instagram">
                                <i class="fab fa-instagram"></i>
                            </a>
                            <a href="#" target="_blank" title="LinkedIn">
                                <i class="fab fa-linkedin-in"></i>
                            </a>
                            <a href="#" target="_blank" title="YouTube">
                                <i class="fab fa-youtube"></i>
                            </a>
                        </div>
                    </div>
                </div>
                
                <!-- Contact Form -->
                <div class="contact-form-container">
                    <h2>Send us a Message</h2>
                    
                    <?php if ($success): ?>
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle"></i> <?= $success ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($error): ?>
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-circle"></i> <?= $error ?>
                        </div>
                    <?php endif; ?>
                    
                    <form method="POST" action="" class="contact-form">
                        <div class="form-row">
                            <div class="form-group">
                                <label>Your Name *</label>
                                <input type="text" name="name" required class="form-control" 
                                       placeholder="John Doe" value="<?= htmlspecialchars($_POST['name'] ?? '') ?>">
                            </div>
                            
                            <div class="form-group">
                                <label>Email Address *</label>
                                <input type="email" name="email" required class="form-control" 
                                       placeholder="john@example.com" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label>Phone Number</label>
                                <input type="tel" name="phone" class="form-control" 
                                       placeholder="+250 788 123 456" value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>">
                            </div>
                            
                            <div class="form-group">
                                <label>Subject *</label>
                                <select name="subject" required class="form-control">
                                    <option value="">Select a subject</option>
                                    <option value="General Inquiry">General Inquiry</option>
                                    <option value="Booking Question">Booking Question</option>
                                    <option value="Tour Information">Tour Information</option>
                                    <option value="Custom Tour Request">Custom Tour Request</option>
                                    <option value="Feedback">Feedback</option>
                                    <option value="Complaint">Complaint</option>
                                    <option value="Other">Other</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label>Your Message *</label>
                            <textarea name="message" required class="form-control" rows="6" 
                                      placeholder="Tell us how we can help you..."><?= htmlspecialchars($_POST['message'] ?? '') ?></textarea>
                        </div>
                        
                        <button type="submit" class="btn btn-primary btn-block">
                            <i class="fas fa-paper-plane"></i> Send Message
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </section>

    <!-- Map Section -->
    <section class="map-section">
        <div class="container">
            <h2 class="section-title">Find Us</h2>
        </div>
        <div id="contactMap" style="height: 400px; width: 100%;"></div>
    </section>

    <!-- FAQ Quick Links -->
    <section class="section" style="background: var(--light);">
        <div class="container">
            <h2 class="section-title">Quick Help</h2>
            <div class="quick-help-grid">
                <a href="faqs.php" class="help-card">
                    <i class="fas fa-question-circle"></i>
                    <h4>FAQs</h4>
                    <p>Find answers to common questions</p>
                </a>
                
                <a href="tours.php" class="help-card">
                    <i class="fas fa-map-marked-alt"></i>
                    <h4>Browse Tours</h4>
                    <p>Explore our tour packages</p>
                </a>
                
                <a href="tel:+250788123456" class="help-card">
                    <i class="fas fa-phone-alt"></i>
                    <h4>Call Us</h4>
                    <p>Speak with our team directly</p>
                </a>
                
                <a href="https://wa.me/250788123456" class="help-card" target="_blank">
                    <i class="fab fa-whatsapp"></i>
                    <h4>WhatsApp</h4>
                    <p>Chat with us on WhatsApp</p>
                </a>
            </div>
        </div>
    </section>

    <?php include 'includes/footer.php'; ?>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.js"></script>
    <script src="assets/js/main.js"></script>
    <script>
        // Initialize map
        const map = L.map('contactMap').setView([-1.9403, 29.8739], 13);
        
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap contributors'
        }).addTo(map);
        
        // Add marker
        L.marker([-1.9403, 29.8739])
            .addTo(map)
            .bindPopup('<b>Iburunga Travel Agency</b><br>Kigali, Rwanda')
            .openPopup();
    </script>
    
    <style>
        .contact-section {
            padding: 4rem 0;
        }
        
        .contact-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 3rem;
        }
        
        .contact-info h2,
        .contact-form-container h2 {
            margin-bottom: 1.5rem;
            color: var(--dark);
        }
        
        .contact-details {
            margin: 2rem 0;
        }
        
        .contact-item {
            display: flex;
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .contact-icon {
            width: 50px;
            height: 50px;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            flex-shrink: 0;
        }
        
        .contact-text h4 {
            margin-bottom: 0.5rem;
            color: var(--dark);
        }
        
        .contact-text p {
            color: var(--gray);
            line-height: 1.6;
        }
        
        .social-contact {
            margin-top: 3rem;
        }
        
        .social-contact h4 {
            margin-bottom: 1rem;
        }
        
        .social-contact .social-links {
            display: flex;
            gap: 1rem;
        }
        
        .social-contact .social-links a {
            width: 45px;
            height: 45px;
            background: var(--light);
            color: var(--dark);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s;
        }
        
        .social-contact .social-links a:hover {
            background: var(--primary);
            color: white;
            transform: translateY(-3px);
        }
        
        .contact-form-container {
            background: white;
            padding: 2rem;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        }
        
        .contact-form .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }
        
        .map-section {
            margin: 4rem 0;
        }
        
        .quick-help-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 2rem;
            margin-top: 2rem;
        }
        
        .help-card {
            background: white;
            padding: 2rem;
            border-radius: 15px;
            text-align: center;
            text-decoration: none;
            color: var(--dark);
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            transition: all 0.3s;
        }
        
        .help-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0,0,0,0.15);
        }
        
        .help-card i {
            font-size: 3rem;
            color: var(--primary);
            margin-bottom: 1rem;
        }
        
        .help-card h4 {
            margin-bottom: 0.5rem;
        }
        
        .help-card p {
            color: var(--gray);
            font-size: 0.9rem;
        }
        
        @media (max-width: 768px) {
            .contact-grid {
                grid-template-columns: 1fr;
            }
            
            .contact-form .form-row {
                grid-template-columns: 1fr;
            }
            
            .quick-help-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</body>
</html>
