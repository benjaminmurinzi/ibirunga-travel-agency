<?php
require_once 'config/database.php';
$db = Database::getInstance();

// Get filters
$search = $_GET['search'] ?? '';
$categoryId = $_GET['category'] ?? '';
$priceRange = $_GET['price'] ?? '';
$duration = $_GET['duration'] ?? '';

// Build query
$sql = "SELECT t.*, c.name as category_name 
        FROM tours t 
        LEFT JOIN categories c ON t.category_id = c.id 
        WHERE t.status = 'active'";
$params = [];

if ($search) {
    $sql .= " AND (t.title LIKE ? OR t.description LIKE ? OR t.location LIKE ?)";
    $searchTerm = "%$search%";
    $params = array_merge($params, [$searchTerm, $searchTerm, $searchTerm]);
}

if ($categoryId) {
    $sql .= " AND t.category_id = ?";
    $params[] = $categoryId;
}

$sql .= " ORDER BY t.featured DESC, t.created_at DESC";

$tours = $db->fetchAll($sql, $params);
$categories = $db->fetchAll("SELECT * FROM categories");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tours - Iburunga Travel</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <div class="page-header">
        <div class="container">
            <h1>Explore Our Tours</h1>
            <p>Discover amazing experiences across Rwanda</p>
        </div>
    </div>

    <div class="container">
        <div class="tours-layout">
            <!-- Filters Sidebar -->
            <aside class="filters-sidebar">
                <div class="filter-card">
                    <h3>Filters</h3>
                    <form action="tours.php" method="GET" id="filterForm">
                        <!-- Search -->
                        <div class="filter-group">
                            <label>Search</label>
                            <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" 
                                   placeholder="Search tours..." class="form-control">
                        </div>

                        <!-- Category -->
                        <div class="filter-group">
                            <label>Category</label>
                            <select name="category" class="form-control">
                                <option value="">All Categories</option>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?= $cat['id'] ?>" 
                                            <?= $categoryId == $cat['id'] ? 'selected' : '' ?>>
                                        <?= $cat['name'] ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Price Range -->
                        <div class="filter-group">
                            <label>Price Range (Local)</label>
                            <select name="price" class="form-control">
                                <option value="">Any Price</option>
                                <option value="0-50000">Under 50,000 RWF</option>
                                <option value="50000-100000">50,000 - 100,000 RWF</option>
                                <option value="100000-200000">100,000 - 200,000 RWF</option>
                                <option value="200000+">Above 200,000 RWF</option>
                            </select>
                        </div>

                        <button type="submit" class="btn btn-primary btn-block">Apply Filters</button>
                        <a href="tours.php" class="btn btn-secondary btn-block">Reset</a>
                    </form>
                </div>
            </aside>

            <!-- Tours Grid -->
            <div class="tours-content">
                <div class="tours-header">
                    <p><?= count($tours) ?> tours found</p>
                    <select id="sortSelect" class="form-control" style="width: auto;">
                        <option value="featured">Featured First</option>
                        <option value="price_low">Price: Low to High</option>
                        <option value="price_high">Price: High to Low</option>
                        <option value="newest">Newest First</option>
                    </select>
                </div>

                <?php if (empty($tours)): ?>
                    <div class="no-results">
                        <i class="fas fa-search fa-3x"></i>
                        <h3>No tours found</h3>
                        <p>Try adjusting your filters or search terms</p>
                    </div>
                <?php else: ?>
                    <div class="tours-grid">
                        <?php foreach ($tours as $tour): ?>
                        <div class="tour-card" data-price="<?= $tour['price_local'] ?>">
                            <div class="tour-image">
                                <img src="uploads/tours/<?= $tour['featured_image'] ?>" 
                                     alt="<?= $tour['title'] ?>"
                                     onerror="this.src='assets/images/placeholder.jpg'">
                                <?php if ($tour['featured']): ?>
                                    <span class="tour-badge featured">Featured</span>
                                <?php endif; ?>
                                <span class="tour-badge"><?= $tour['category_name'] ?></span>
                            </div>
                            <div class="tour-content">
                                <h3><?= $tour['title'] ?></h3>
                                <div class="tour-meta">
                                    <span><i class="fas fa-map-marker-alt"></i> <?= $tour['location'] ?></span>
                                    <span><i class="fas fa-clock"></i> <?= $tour['duration'] ?></span>
                                    <span><i class="fas fa-users"></i> Max <?= $tour['max_people'] ?></span>
                                </div>
                                <p><?= substr($tour['description'], 0, 120) ?>...</p>
                                <div class="tour-footer">
                                    <div class="tour-price">
                                        <div>
                                            <small>Local</small>
                                            <strong><?= formatPrice($tour['price_local']) ?></strong>
                                        </div>
                                        <div>
                                            <small>Foreign</small>
                                            <strong><?= formatPrice($tour['price_foreign'], 'USD') ?></strong>
                                        </div>
                                    </div>
                                    <a href="tour-details.php?slug=<?= $tour['slug'] ?>" class="btn btn-sm">
                                        View Details <i class="fas fa-arrow-right"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>

    <script src="assets/js/main.js"></script>
    <script>
    // Sorting functionality
    document.getElementById('sortSelect').addEventListener('change', function() {
        const value = this.value;
        const grid = document.querySelector('.tours-grid');
        const cards = Array.from(grid.querySelectorAll('.tour-card'));
        
        cards.sort((a, b) => {
            switch(value) {
                case 'price_low':
                    return parseFloat(a.dataset.price) - parseFloat(b.dataset.price);
                case 'price_high':
                    return parseFloat(b.dataset.price) - parseFloat(a.dataset.price);
                default:
                    return 0;
            }
        });
        
        cards.forEach(card => grid.appendChild(card));
    });
    </script>
</body>
</html>
