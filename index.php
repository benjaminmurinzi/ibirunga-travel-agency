<?php
require_once 'config/database.php';
$db = Database::getInstance();

// Get featured tours
$featuredTours = $db->fetchAll(
    "SELECT t.*, c.name as category_name 
     FROM tours t 
     LEFT JOIN categories c ON t.category_id = c.id 
     WHERE t.featured = TRUE AND t.status = 'active' 
     LIMIT 6"
);

// Get categories
$categories = $db->fetchAll("SELECT * FROM categories ORDER BY name");

// Get testimonials
$testimonials = $db->fetchAll(
    "SELECT t.*, tours.title as tour_title 
     FROM testimonials t 
     LEFT JOIN tours ON t.tour_id = tours.id 
     WHERE t.status = 'approved' 
     ORDER BY t.created_at DESC LIMIT 6"
);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iburunga Travel Agency - Discover Rwanda</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <!-- Hero Section -->
    <section class="hero">
        <div class="hero-overlay"></div>
        <div class="hero-content">
            <h1 class="hero-title">Discover the Heart of Africa</h1>
            <p class="hero-subtitle">Experience Rwanda's breathtaking landscapes, wildlife, and culture</p>
            <div class="hero-search">
                <form action="tours.php" method="GET" class="search-form">
                    <input type="text" name="search" placeholder="Search tours..." class="search-input">
                    <select name="category" class="search-select">
                        <option value="">All Categories</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= $cat['id'] ?>"><?= $cat['name'] ?></option>
                        <?php endforeach; ?>
                    </select>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search"></i> Search
                    </button>
                </form>
            </div>
        </div>
    </section>

    <!-- Categories Section -->
    <section class="categories-section">
        <div class="container">
            <h2 class="section-title">Explore by Category</h2>
            <div class="categories-grid">
                <?php foreach ($categories as $category): ?>
                <a href="tours.php?category=<?= $category['id'] ?>" class="category-card">
                    <i class="fas <?= $category['icon'] ?> category-icon"></i>
                    <h3><?= $category['name'] ?></h3>
                    <p><?= $category['description'] ?></p>
                </a>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Featured Tours Section -->
    <section class="tours-section">
        <div class="container">
            <h2 class="section-title">Featured Tours</h2>
            <div class="tours-grid">
                <?php foreach ($featuredTours as $tour): ?>
                <div class="tour-card">
                    <div class="tour-image">
                        <img src="uploads/tours/<?= $tour['featured_image'] ?>" 
                             alt="<?= $tour['title'] ?>"
                             onerror="this.src='assets/images/placeholder.jpg'">
                        <span class="tour-badge"><?= $tour['category_name'] ?></span>
                    </div>
                    <div class="tour-content">
                        <h3><?= $tour['title'] ?></h3>
                        <div class="tour-meta">
                            <span><i class="fas fa-map-marker-alt"></i> <?= $tour['location'] ?></span>
                            <span><i class="fas fa-clock"></i> <?= $tour['duration'] ?></span>
                        </div>
                        <p><?= substr($tour['description'], 0, 120) ?>...</p>
                        <div class="tour-footer">
                            <div class="tour-price">
                                <small>From</small>
                                <strong><?= formatPrice($tour['price_local']) ?></strong>
                            </div>
                            <a href="tour-details.php?slug=<?= $tour['slug'] ?>" class="btn btn-sm">
                                View Details
                            </a>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <div class="text-center mt-4">
                <a href="tours.php" class="btn btn-primary">View All Tours</a>
            </div>
        </div>
    </section>

    <!-- Testimonials Section -->
    <section class="testimonials-section">
        <div class="container">
            <h2 class="section-title">What Our Travelers Say</h2>
            <div class="testimonials-grid">
                <?php foreach ($testimonials as $testimonial): ?>
                <div class="testimonial-card">
                    <div class="testimonial-rating">
                        <?php for ($i = 0; $i < 5; $i++): ?>
                            <i class="fas fa-star <?= $i < $testimonial['rating'] ? 'active' : '' ?>"></i>
                        <?php endfor; ?>
                    </div>
                    <p class="testimonial-text">"<?= $testimonial['comment'] ?>"</p>
                    <div class="testimonial-author">
                        <strong><?= $testimonial['name'] ?></strong>
                        <?php if ($testimonial['tour_title']): ?>
                            <small><?= $testimonial['tour_title'] ?></small>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Newsletter Section -->
    <section class="newsletter-section">
        <div class="container">
            <div class="newsletter-content">
                <h2>Subscribe to Our Newsletter</h2>
                <p>Get the latest tour updates and special offers</p>
                <form id="newsletterForm" class="newsletter-form">
                    <input type="email" name="email" placeholder="Enter your email" required>
                    <button type="submit" class="btn btn-primary">Subscribe</button>
                </form>
                <div id="newsletterMessage"></div>
            </div>
        </div>
    </section>

    <?php include 'includes/footer.php'; ?>

    <script src="assets/js/main.js"></script>
    <script>
    document.getElementById('newsletterForm').addEventListener('submit', async (e) => {
        e.preventDefault();
        const formData = new FormData(e.target);
        
        try {
            const response = await fetch('api/newsletter.php', {
                method: 'POST',
                body: formData
            });
            const data = await response.json();
            
            const msgDiv = document.getElementById('newsletterMessage');
            msgDiv.className = data.success ? 'alert alert-success' : 'alert alert-danger';
            msgDiv.textContent = data.message;
            
            if (data.success) {
                e.target.reset();
            }
        } catch (error) {
            console.error('Error:', error);
        }
    });
    </script>
</body>
</html>
