<?php
require_once 'config/database.php';
$db = Database::getInstance();

$slug = $_GET['slug'] ?? '';
if (!$slug) {
    redirect('/tours.php');
}

// Get tour details
$tour = $db->fetch(
    "SELECT t.*, c.name as category_name 
     FROM tours t 
     LEFT JOIN categories c ON t.category_id = c.id 
     WHERE t.slug = ? AND t.status = 'active'",
    [$slug]
);

if (!$tour) {
    redirect('/tours.php');
}

// Update view count
$db->query("UPDATE tours SET views = views + 1 WHERE id = ?", [$tour['id']]);

// Get related tours
$relatedTours = $db->fetchAll(
    "SELECT * FROM tours 
     WHERE category_id = ? AND id != ? AND status = 'active' 
     LIMIT 3",
    [$tour['category_id'], $tour['id']]
);

// Get tour testimonials
$tourTestimonials = $db->fetchAll(
    "SELECT * FROM testimonials 
     WHERE tour_id = ? AND status = 'approved' 
     ORDER BY created_at DESC",
    [$tour['id']]
);

$gallery = $tour['gallery'] ? json_decode($tour['gallery'], true) : [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $tour['title'] ?> - Iburunga Travel</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <!-- Tour Header -->
    <div class="tour-detail-header" style="background-image: url('uploads/tours/<?= $tour['featured_image'] ?>');">
        <div class="overlay"></div>
        <div class="container">
            <div class="breadcrumb">
                <a href="index.php">Home</a> / 
                <a href="tours.php">Tours</a> / 
                <span><?= $tour['title'] ?></span>
            </div>
            <h1><?= $tour['title'] ?></h1>
            <div class="tour-quick-info">
                <span><i class="fas fa-map-marker-alt"></i> <?= $tour['location'] ?></span>
                <span><i class="fas fa-clock"></i> <?= $tour['duration'] ?></span>
                <span><i class="fas fa-users"></i> Max <?= $tour['max_people'] ?> people</span>
                <span><i class="fas fa-eye"></i> <?= $tour['views'] ?> views</span>
            </div>
        </div>
    </div>

    <div class="container">
        <div class="tour-detail-layout">
            <!-- Main Content -->
            <div class="tour-main-content">
                <!-- Description -->
                <section class="detail-section">
                    <h2>About This Tour</h2>
                    <p><?= nl2br($tour['description']) ?></p>
                </section>

                <!-- Itinerary -->
                <?php if ($tour['itinerary']): ?>
                <section class="detail-section">
                    <h2><i class="fas fa-route"></i> Itinerary</h2>
                    <div class="itinerary">
                        <?php 
                        $itineraryItems = explode("\n", $tour['itinerary']);
                        foreach ($itineraryItems as $index => $item): 
                            if (trim($item)):
                        ?>
                        <div class="itinerary-item">
                            <div class="itinerary-marker"><?= $index + 1 ?></div>
                            <div class="itinerary-content">
                                <p><?= $item ?></p>
                            </div>
                        </div>
                        <?php 
                            endif;
                        endforeach; 
                        ?>
                    </div>
                </section>
                <?php endif; ?>

                <!-- Gallery -->
                <?php if (!empty($gallery)): ?>
                <section class="detail-section">
                    <h2><i class="fas fa-images"></i> Gallery</h2>
                    <div class="tour-gallery">
                        <?php foreach ($gallery as $image): ?>
                        <img src="uploads/tours/<?= $image ?>" alt="Tour image" class="gallery-image">
                        <?php endforeach; ?>
                    </div>
                </section>
                <?php endif; ?>

                <!-- Map -->
                <?php if ($tour['latitude'] && $tour['longitude']): ?>
                <section class="detail-section">
                    <h2><i class="fas fa-map"></i> Location</h2>
                    <div id="tourMap" class="tour-map"></div>
                </section>
                <?php endif; ?>

                <!-- Reviews -->
                <?php if (!empty($tourTestimonials)): ?>
                <section class="detail-section">
                    <h2><i class="fas fa-star"></i> Reviews</h2>
                    <div class="reviews-list">
                        <?php foreach ($tourTestimonials as $review): ?>
                        <div class="review-item">
                            <div class="review-header">
                                <strong><?= $review['name'] ?></strong>
                                <div class="review-rating">
                                    <?php for ($i = 0; $i < 5; $i++): ?>
                                        <i class="fas fa-star <?= $i < $review['rating'] ? 'active' : '' ?>"></i>
                                    <?php endfor; ?>
                                </div>
                            </div>
                            <p><?= $review['comment'] ?></p>
                            <small><?= date('M d, Y', strtotime($review['created_at'])) ?></small>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </section>
                <?php endif; ?>

                <!-- Related Tours -->
                <?php if (!empty($relatedTours)): ?>
                <section class="detail-section">
                    <h2>Related Tours</h2>
                    <div class="related-tours">
                        <?php foreach ($relatedTours as $related): ?>
                        <a href="tour-details.php?slug=<?= $related['slug'] ?>" class="related-tour-card">
                            <img src="uploads/tours/<?= $related['featured_image'] ?>" 
                                 alt="<?= $related['title'] ?>"
                                 onerror="this.src='assets/images/placeholder.jpg'">
                            <div class="related-tour-info">
                                <h4><?= $related['title'] ?></h4>
                                <p class="price"><?= formatPrice($related['price_local']) ?></p>
                            </div>
                        </a>
                        <?php endforeach; ?>
                    </div>
                </section>
                <?php endif; ?>
            </div>

            <!-- Booking Sidebar -->
            <aside class="booking-sidebar">
                <div class="booking-card">
                    <div class="price-display">
                        <div class="price-item">
                            <span>Local Price</span>
                            <strong><?= formatPrice($tour['price_local']) ?></strong>
                        </div>
                        <div class="price-item">
                            <span>Foreign Price</span>
                            <strong><?= formatPrice($tour['price_foreign'], 'USD') ?></strong>
                        </div>
                    </div>

                    <form id="bookingForm" class="booking-form">
                        <input type="hidden" name="tour_id" value="<?= $tour['id'] ?>">
                        
                        <div class="form-group">
                            <label>Full Name *</label>
                            <input type="text" name="customer_name" required class="form-control">
                        </div>

                        <div class="form-group">
                            <label>Email *</label>
                            <input type="email" name="customer_email" required class="form-control">
                        </div>

                        <div class="form-group">
                            <label>Phone *</label>
                            <input type="tel" name="customer_phone" required class="form-control">
                        </div>

                        <div class="form-group">
                            <label>Customer Type *</label>
                            <select name="customer_type" id="customerType" required class="form-control">
                                <option value="local">Local (Rwandan)</option>
                                <option value="foreign">Foreign</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>Number of People *</label>
                            <input type="number" name="num_people" id="numPeople" min="1" 
                                   max="<?= $tour['max_people'] ?>" value="1" required class="form-control">
                            <small>Maximum <?= $tour['max_people'] ?> people</small>
                        </div>

                        <div class="form-group">
                            <label>Travel Date *</label>
                            <input type="date" name="travel_date" required class="form-control"
                                   min="<?= date('Y-m-d', strtotime('+1 day')) ?>">
                        </div>

                        <div class="form-group">
                            <label>Additional Message</label>
                            <textarea name="message" rows="3" class="form-control"></textarea>
                        </div>

                        <div class="total-price">
                            <span>Total Price:</span>
                            <strong id="totalPrice"><?= formatPrice($tour['price_local']) ?></strong>
                        </div>

                        <button type="submit" class="btn btn-primary btn-block">
                            <i class="fas fa-calendar-check"></i> Book Now
                        </button>
                    </form>

                    <div class="booking-features">
                        <div class="feature">
                            <i class="fas fa-check-circle"></i>
                            <span>Instant Confirmation</span>
                        </div>
                        <div class="feature">
                            <i class="fas fa-shield-alt"></i>
                            <span>Secure Booking</span>
                        </div>
                        <div class="feature">
                            <i class="fas fa-headset"></i>
                            <span>24/7 Support</span>
                        </div>
                    </div>
                </div>

                <!-- Share Buttons -->
                <div class="share-card">
                    <h3>Share This Tour</h3>
                    <div class="share-buttons">
                        <a href="https://facebook.com/sharer/sharer.php?u=<?= urlencode(SITE_URL . '/tour-details.php?slug=' . $slug) ?>" 
                           target="_blank" class="share-btn facebook">
                            <i class="fab fa-facebook-f"></i>
                        </a>
                        <a href="https://twitter.com/intent/tweet?url=<?= urlencode(SITE_URL . '/tour-details.php?slug=' . $slug) ?>&text=<?= urlencode($tour['title']) ?>" 
                           target="_blank" class="share-btn twitter">
                            <i class="fab fa-twitter"></i>
                        </a>
                        <a href="https://wa.me/?text=<?= urlencode($tour['title'] . ' - ' . SITE_URL . '/tour-details.php?slug=' . $slug) ?>" 
                           target="_blank" class="share-btn whatsapp">
                            <i class="fab fa-whatsapp"></i>
                        </a>
                        <a href="mailto:?subject=<?= urlencode($tour['title']) ?>&body=<?= urlencode(SITE_URL . '/tour-details.php?slug=' . $slug) ?>" 
                           class="share-btn email">
                            <i class="fas fa-envelope"></i>
                        </a>
                    </div>
                </div>
            </aside>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>

    <script src="assets/js/main.js"></script>
    <?php if ($tour['latitude'] && $tour['longitude']): ?>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.css">
    <script>
        const map = L.map('tourMap').setView([<?= $tour['latitude'] ?>, <?= $tour['longitude'] ?>], 13);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(map);
        L.marker([<?= $tour['latitude'] ?>, <?= $tour['longitude'] ?>])
            .addTo(map)
            .bindPopup('<?= $tour['title'] ?>');
    </script>
    <?php endif; ?>
    <script>
    const priceLocal = <?= $tour['price_local'] ?>;
    const priceForeign = <?= $tour['price_foreign'] ?>;
    
    function updateTotalPrice() {
        const type = document.getElementById('customerType').value;
        const numPeople = parseInt(document.getElementById('numPeople').value) || 1;
        const price = type === 'local' ? priceLocal : priceForeign;
        const total = price * numPeople;
        const currency = type === 'local' ? 'RWF' : 'USD';
        
        document.getElementById('totalPrice').textContent = 
            currency === 'RWF' 
                ? total.toLocaleString() + ' RWF'
                : ' + total.toFixed(2);
    }
    
    document.getElementById('customerType').addEventListener('change', updateTotalPrice);
    document.getElementById('numPeople').addEventListener('input', updateTotalPrice);
    
    document.getElementById('bookingForm').addEventListener('submit', async (e) => {
        e.preventDefault();
        const formData = new FormData(e.target);
        
        try {
            const response = await fetch('api/booking.php', {
                method: 'POST',
                body: formData
            });
            const data = await response.json();
            
            if (data.success) {
                alert('Booking successful! Reference: ' + data.booking_ref + '\nWe will contact you shortly.');
                window.location.href = 'booking-confirmation.php?ref=' + data.booking_ref;
            } else {
                alert('Error: ' + data.message);
            }
        } catch (error) {
            alert('An error occurred. Please try again.');
            console.error('Error:', error);
        }
    });
    </script>
</body>
</html>
