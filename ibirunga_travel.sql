-- Create Database
CREATE DATABASE IF NOT EXISTS iburunga_travel;
USE iburunga_travel;

-- Categories Table
CREATE TABLE categories (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    icon VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tours Table
CREATE TABLE tours (
    id INT PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(200) NOT NULL,
    slug VARCHAR(250) UNIQUE NOT NULL,
    category_id INT,
    description TEXT,
    itinerary TEXT,
    duration VARCHAR(50),
    location VARCHAR(150),
    max_people INT DEFAULT 20,
    min_age INT DEFAULT 0,
    price_local DECIMAL(10,2) NOT NULL,
    price_foreign DECIMAL(10,2) NOT NULL,
    featured_image VARCHAR(255),
    gallery TEXT,
    latitude DECIMAL(10, 8),
    longitude DECIMAL(11, 8),
    status ENUM('active', 'inactive') DEFAULT 'active',
    featured BOOLEAN DEFAULT FALSE,
    views INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
);

-- Users Table
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(150) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    phone VARCHAR(20),
    role ENUM('admin', 'user') DEFAULT 'user',
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Bookings Table
CREATE TABLE bookings (
    id INT PRIMARY KEY AUTO_INCREMENT,
    booking_ref VARCHAR(20) UNIQUE NOT NULL,
    tour_id INT NOT NULL,
    user_id INT,
    customer_name VARCHAR(100) NOT NULL,
    customer_email VARCHAR(150) NOT NULL,
    customer_phone VARCHAR(20) NOT NULL,
    customer_type ENUM('local', 'foreign') NOT NULL,
    num_people INT NOT NULL,
    travel_date DATE NOT NULL,
    total_price DECIMAL(10,2) NOT NULL,
    message TEXT,
    status ENUM('pending', 'confirmed', 'cancelled', 'completed') DEFAULT 'pending',
    payment_status ENUM('unpaid', 'paid', 'refunded') DEFAULT 'unpaid',
    admin_notified BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (tour_id) REFERENCES tours(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

-- Testimonials Table
CREATE TABLE testimonials (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    tour_id INT,
    rating INT CHECK (rating >= 1 AND rating <= 5),
    comment TEXT NOT NULL,
    image VARCHAR(255),
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (tour_id) REFERENCES tours(id) ON DELETE SET NULL
);

-- Newsletter Table
CREATE TABLE newsletter (
    id INT PRIMARY KEY AUTO_INCREMENT,
    email VARCHAR(150) UNIQUE NOT NULL,
    status ENUM('active', 'unsubscribed') DEFAULT 'active',
    subscribed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- FAQs Table
CREATE TABLE faqs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    question VARCHAR(255) NOT NULL,
    answer TEXT NOT NULL,
    category VARCHAR(50),
    order_num INT DEFAULT 0,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Site Settings Table
CREATE TABLE settings (
    id INT PRIMARY KEY AUTO_INCREMENT,
    setting_key VARCHAR(100) UNIQUE NOT NULL,
    setting_value TEXT,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Insert Default Admin User (password: admin123)
INSERT INTO users (name, email, password, role) VALUES 
('Admin', 'admin@iburunga.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');

-- Insert Sample Categories
INSERT INTO categories (name, description, icon) VALUES
('Wildlife Safari', 'Experience Rwanda\'s amazing wildlife', 'fa-paw'),
('Cultural Tours', 'Discover Rwandan culture and heritage', 'fa-landmark'),
('Adventure', 'Thrilling outdoor activities', 'fa-mountain'),
('City Tours', 'Explore Rwanda\'s vibrant cities', 'fa-city'),
('Nature & Hiking', 'Beautiful natural landscapes', 'fa-tree');

-- Insert Sample Tours
INSERT INTO tours (title, slug, category_id, description, itinerary, duration, location, price_local, price_foreign, featured_image, status, featured) VALUES
('Gorilla Trekking Experience', 'gorilla-trekking-experience', 1, 'Encounter mountain gorillas in their natural habitat at Volcanoes National Park. This once-in-a-lifetime experience brings you face to face with these gentle giants.', 
'Day 1: Early morning departure to Volcanoes National Park\nDay 1: Briefing and trek (3-6 hours)\nDay 1: Return and evening relaxation', 
'1 Day', 'Volcanoes National Park', 80000, 1500, 'gorilla.jpg', 'active', TRUE),

('Kigali City Tour', 'kigali-city-tour', 4, 'Discover the heart of Rwanda with a comprehensive tour of Kigali, including the Genocide Memorial, local markets, and craft centers.',
'Morning: Genocide Memorial visit\nAfternoon: City center and markets\nEvening: Craft shops and dinner',
'Half Day', 'Kigali', 15000, 50, 'kigali.jpg', 'active', TRUE),

('Akagera Safari', 'akagera-safari', 1, 'Experience the Big Five in Rwanda\'s only savanna park. Game drives through diverse landscapes with abundant wildlife.',
'Day 1: Morning game drive\nDay 1: Lunch at park lodge\nDay 1: Afternoon boat safari\nDay 2: Early morning game drive',
'2 Days', 'Akagera National Park', 120000, 300, 'akagera.jpg', 'active', FALSE);

-- Insert Sample FAQs
INSERT INTO faqs (question, answer, category, order_num) VALUES
('What is the best time to visit Rwanda?', 'Rwanda can be visited year-round, but the dry seasons (June-September and December-February) are ideal for gorilla trekking and wildlife viewing.', 'General', 1),
('Do I need a visa to visit Rwanda?', 'Most visitors can obtain a visa on arrival or apply online. Check with your embassy for specific requirements.', 'Travel Info', 2),
('How do I book a tour?', 'Simply browse our tours, select your preferred package, fill out the booking form, and we\'ll confirm your reservation within 24 hours.', 'Booking', 3);

-- Insert Site Settings
INSERT INTO settings (setting_key, setting_value) VALUES
('site_name', 'Iburunga Travel Agency'),
('site_email', 'info@iburunga.com'),
('site_phone', '+250 788 123 456'),
('site_address', 'Kigali, Rwanda'),
('admin_email', 'admin@iburunga.com'),
('smtp_host', 'smtp.gmail.com'),
('smtp_port', '587'),
('smtp_username', ''),
('smtp_password', ''),
('currency_local', 'RWF'),
('currency_foreign', 'USD');
