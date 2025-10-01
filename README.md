# Iburunga Travel Agency - Tourism Web Application

A comprehensive tourism booking platform built with PHP, MySQL, HTML, CSS, and JavaScript.

## Features

### User Features
- ✅ Browse tours by category, location, and price
- ✅ Search and filter tours
- ✅ View detailed tour information with itinerary
- ✅ Interactive maps showing tour locations
- ✅ Book tours with local/foreign pricing
- ✅ Newsletter subscription
- ✅ Social media sharing
- ✅ Testimonials and reviews
- ✅ FAQs section
- ✅ Responsive design for all devices

### Admin Features
- ✅ Dashboard with analytics and statistics
- ✅ Manage tours (create, edit, delete)
- ✅ Manage categories
- ✅ View and manage bookings
- ✅ User management
- ✅ Testimonial moderation
- ✅ Newsletter subscriber management
- ✅ Media upload management
- ✅ FAQs management
- ✅ Site settings configuration
- ✅ Revenue analytics with charts

### Booking System
- ✅ Real-time price calculation
- ✅ Automatic booking reference generation
- ✅ Email confirmation to customers
- ✅ Admin notification emails
- ✅ Booking status management

## Installation Guide

### Requirements
- PHP 7.4 or higher
- MySQL 5.7 or higher
- XAMPP/WAMP/LAMP server
- Modern web browser

### Step 1: Setup XAMPP
1. Download and install XAMPP from https://www.apachefriends.org
2. Start Apache and MySQL from XAMPP Control Panel

### Step 2: Database Setup
1. Open phpMyAdmin (http://localhost/phpmyadmin)
2. Create a new database named `iburunga_travel`
3. Import the SQL file: `iburunga_travel.sql`
4. Or execute the SQL commands from the database schema file

### Step 3: Project Installation
1. Extract/Copy project files to `C:\xampp\htdocs\iburunga`
2. Update database credentials in `config/database.php`:
   ```php
   define('DB_HOST', 'localhost');
   define('DB_USER', 'root');
   define('DB_PASS', '');
   define('DB_NAME', 'iburunga_travel');
   ```

### Step 4: Email Configuration (Optional)
Configure SMTP settings in `config/database.php`:
```php
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', 'your-email@gmail.com');
define('SMTP_PASSWORD', 'your-app-password');
```

**For Gmail:**
1. Enable 2-factor authentication
2. Generate an App Password
3. Use the app password in configuration

### Step 5: File Permissions
Ensure the following directories are writable:
```
uploads/
uploads/tours/
uploads/testimonials/
```

### Step 6: Access the Application
- **Frontend:** http://localhost/iburunga
- **Admin Panel:** http://localhost/iburunga/admin
- **Default Admin Credentials:**
  - Email: admin@iburunga.com
  - Password: admin123

## Project Structure

```
iburunga/
├── admin/                  # Admin dashboard
│   ├── includes/          # Admin header/sidebar
│   ├── index.php          # Dashboard
│   ├── tours.php          # Tour management
│   ├── bookings.php       # Booking management
│   └── ...
├── api/                   # API endpoints
│   ├── booking.php        # Booking API
│   ├── newsletter.php     # Newsletter API
│   └── ...
├── assets/
│   ├── css/              # Stylesheets
│   ├── js/               # JavaScript files
│   └── images/           # Static images
├── config/
│   └── database.php      # Database configuration
├── includes/
│   ├── header.php        # Site header
│   └── footer.php        # Site footer
├── uploads/              # Uploaded files
│   └── tours/           # Tour images
├── index.php             # Homepage
├── tours.php             # Tours listing
├── tour-details.php      # Tour details
├── login.php             # User login
└── README.md            # This file
```

## Database Tables

- **users** - User accounts (admin/customers)
- **categories** - Tour categories
- **tours** - Tour packages
- **bookings** - Tour bookings
- **testimonials** - Customer reviews
- **newsletter** - Email subscribers
- **faqs** - Frequently asked questions
- **settings** - Site configuration

## Features Implementation

### 1. Search & Filter
```php
// Example: Search tours by keyword and category
$tours = $db->fetchAll(
    "SELECT * FROM tours 
     WHERE title LIKE ? AND category_id = ?",
    ["%$keyword%", $categoryId]
);
```

### 2. Booking with Email Notification
The booking system:
- Validates input data
- Calculates total price (local/foreign rates)
- Generates unique booking reference
- Sends confirmation email to customer
- Notifies admin via email

### 3. Price Display
- Local prices in RWF (Rwandan Francs)
- Foreign prices in USD
- Dynamic calculation based on number of people

### 4. Admin Dashboard Analytics
- Total tours, bookings, revenue
- Monthly revenue charts (Chart.js)
- Booking status distribution
- Popular tours tracking

### 5. Security Features
- Password hashing (bcrypt)
- SQL injection prevention (prepared statements)
- XSS protection (input sanitization)
- CSRF protection (session tokens)
- Role-based access control

## Email Configuration Alternatives

### Using Gmail SMTP
```php
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', 'your-email@gmail.com');
define('SMTP_PASSWORD', 'your-app-password');
```

### Using Local Mail (Development)
For testing without SMTP, PHP's `mail()` function works with:
- Sendmail (Linux/Mac)
- Fake Sendmail (Windows with XAMPP)

## Customization

### Changing Colors
Edit `assets/css/style.css`:
```css
:root {
    --primary: #2ECC71;  /* Main color */
    --secondary: #3498DB; /* Secondary color */
}
```

### Adding New Tour Categories
1. Go to Admin > Categories
2. Click "Add New Category"
3. Enter name, description, and icon class
4. Save

### Modifying Email Templates
Email templates are in:
- `api/booking.php` - Booking confirmation
- `api/newsletter.php` - Newsletter welcome

## Troubleshooting

### Issue: Cannot access admin panel
**Solution:** Make sure you're logged in with admin credentials

### Issue: Images not displaying
**Solution:** Check file permissions on `uploads/` directory

### Issue: Booking emails not sending
**Solution:** 
- Check SMTP credentials
- Verify PHP mail() is configured
- Check spam folder

### Issue: Database connection error
**Solution:** Verify MySQL is running and credentials are correct

## Browser Compatibility
- Chrome (recommended)
- Firefox
- Safari
- Edge
- Opera

## Mobile Responsive
- Fully responsive design
- Mobile-friendly navigation
- Touch-optimized interfaces

## Future Enhancements
- Payment gateway integration (Stripe/PayPal)
- Multi-language support
- Real-time chat support
- Advanced analytics
- Mobile app (React Native)
- Tour reviews and ratings
- Booking calendar view
- Customer loyalty program

## Support
For issues or questions, contact:
- Email: admin@iburunga.com
- Documentation: [Add your docs link]

## License
This project is for educational/commercial use.

## Credits
- Font Awesome for icons
- Chart.js for analytics
- Leaflet for maps
- PHP & MySQL for backend

---

**Developed for Iburunga Travel Agency**
Version 1.0.0 - 2025
