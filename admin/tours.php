<?php
require_once '../config/database.php';
requireAdmin();

$db = Database::getInstance();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'create' || $action === 'update') {
        $tourData = [
            'title' => sanitize($_POST['title']),
            'slug' => strtolower(str_replace(' ', '-', sanitize($_POST['title']))),
            'category_id' => $_POST['category_id'],
            'description' => $_POST['description'],
            'itinerary' => $_POST['itinerary'],
            'duration' => sanitize($_POST['duration']),
            'location' => sanitize($_POST['location']),
            'max_people' => $_POST['max_people'],
            'min_age' => $_POST['min_age'],
            'price_local' => $_POST['price_local'],
            'price_foreign' => $_POST['price_foreign'],
            'latitude' => $_POST['latitude'] ?? null,
            'longitude' => $_POST['longitude'] ?? null,
            'status' => $_POST['status'],
            'featured' => isset($_POST['featured']) ? 1 : 0
        ];
        
        // Handle image upload
        if (isset($_FILES['featured_image']) && $_FILES['featured_image']['error'] === 0) {
            $uploadDir = '../uploads/tours/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
            
            $ext = pathinfo($_FILES['featured_image']['name'], PATHINFO_EXTENSION);
            $filename = uniqid() . '.' . $ext;
            
            if (move_uploaded_file($_FILES['featured_image']['tmp_name'], $uploadDir . $filename)) {
                $tourData['featured_image'] = $filename;
            }
        }
        
        try {
            if ($action === 'create') {
                $db->insert('tours', $tourData);
                $_SESSION['success'] = 'Tour created successfully';
            } else {
                $tourId = $_POST['tour_id'];
                $db->update('tours', $tourData, 'id = ?', [$tourId]);
                $_SESSION['success'] = 'Tour updated successfully';
            }
            redirect('/admin/tours.php');
        } catch (Exception $e) {
            $_SESSION['error'] = 'Error: ' . $e->getMessage();
        }
    } elseif ($action === 'delete') {
        $tourId = $_POST['tour_id'];
        $db->delete('tours', 'id = ?', [$tourId]);
        $_SESSION['success'] = 'Tour deleted successfully';
        redirect('/admin/tours.php');
    }
}

// Get all tours
$tours = $db->fetchAll(
    "SELECT t.*, c.name as category_name 
     FROM tours t 
     LEFT JOIN categories c ON t.category_id = c.id 
     ORDER BY t.created_at DESC"
);

$categories = $db->fetchAll("SELECT * FROM categories ORDER BY name");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Tours - Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body>
    <?php include 'includes/sidebar.php'; ?>
    
    <div class="main-content">
        <?php include 'includes/topbar.php'; ?>
        
        <div class="dashboard-container">
            <div class="page-header">
                <h1>Manage Tours</h1>
                <button class="btn btn-primary" onclick="openModal('createTourModal')">
                    <i class="fas fa-plus"></i> Add New Tour
                </button>
            </div>

            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success"><?= $_SESSION['success']; unset($_SESSION['success']); ?></div>
            <?php endif; ?>

            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger"><?= $_SESSION['error']; unset($_SESSION['error']); ?></div>
            <?php endif; ?>

            <!-- Filters -->
            <div class="filters-bar">
                <input type="text" id="searchTours" placeholder="Search tours..." class="form-control">
                <select id="filterCategory" class="form-control">
                    <option value="">All Categories</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= $cat['id'] ?>"><?= $cat['name'] ?></option>
                    <?php endforeach; ?>
                </select>
                <select id="filterStatus" class="form-control">
                    <option value="">All Status</option>
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                </select>
            </div>

            <!-- Tours Table -->
            <div class="table-card">
                <div class="table-responsive">
                    <table class="data-table" id="toursTable">
                        <thead>
                            <tr>
                                <th>Image</th>
                                <th>Title</th>
                                <th>Category</th>
                                <th>Location</th>
                                <th>Duration</th>
                                <th>Price (Local)</th>
                                <th>Price (Foreign)</th>
                                <th>Status</th>
                                <th>Views</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($tours as $tour): ?>
                            <tr data-category="<?= $tour['category_id'] ?>" data-status="<?= $tour['status'] ?>">
                                <td>
                                    <img src="../uploads/tours/<?= $tour['featured_image'] ?>" 
                                         alt="<?= $tour['title'] ?>" 
                                         class="table-image"
                                         onerror="this.src='../assets/images/placeholder.jpg'">
                                </td>
                                <td>
                                    <strong><?= $tour['title'] ?></strong>
                                    <?php if ($tour['featured']): ?>
                                        <span class="badge badge-warning">Featured</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= $tour['category_name'] ?></td>
                                <td><?= $tour['location'] ?></td>
                                <td><?= $tour['duration'] ?></td>
                                <td><?= formatPrice($tour['price_local']) ?></td>
                                <td><?= formatPrice($tour['price_foreign'], 'USD') ?></td>
                                <td>
                                    <span class="badge badge-<?= $tour['status'] ?>">
                                        <?= ucfirst($tour['status']) ?>
                                    </span>
                                </td>
                                <td><?= $tour['views'] ?></td>
                                <td>
                                    <div class="action-buttons">
                                        <button class="btn-icon" onclick='editTour(<?= json_encode($tour) ?>)' title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <a href="../tour-details.php?slug=<?= $tour['slug'] ?>" 
                                           class="btn-icon" target="_blank" title="View">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <button class="btn-icon btn-danger" 
                                                onclick="deleteTour(<?= $tour['id'] ?>, '<?= $tour['title'] ?>')" 
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

    <!-- Create/Edit Tour Modal -->
    <div id="createTourModal" class="modal">
        <div class="modal-content modal-lg">
            <div class="modal-header">
                <h2 id="modalTitle">Add New Tour</h2>
                <span class="close" onclick="closeModal('createTourModal')">&times;</span>
            </div>
            <form id="tourForm" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="action" id="formAction" value="create">
                <input type="hidden" name="tour_id" id="tourId">
                
                <div class="form-row">
                    <div class="form-group col-8">
                        <label>Title *</label>
                        <input type="text" name="title" id="title" required class="form-control">
                    </div>
                    <div class="form-group col-4">
                        <label>Category *</label>
                        <select name="category_id" id="category_id" required class="form-control">
                            <option value="">Select Category</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?= $cat['id'] ?>"><?= $cat['name'] ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label>Description *</label>
                    <textarea name="description" id="description" rows="4" required class="form-control"></textarea>
                </div>

                <div class="form-group">
                    <label>Itinerary</label>
                    <textarea name="itinerary" id="itinerary" rows="4" class="form-control" 
                              placeholder="Day 1: ...\nDay 2: ..."></textarea>
                </div>

                <div class="form-row">
                    <div class="form-group col-4">
                        <label>Duration *</label>
                        <input type="text" name="duration" id="duration" required class="form-control" 
                               placeholder="e.g., 2 Days">
                    </div>
                    <div class="form-group col-4">
                        <label>Location *</label>
                        <input type="text" name="location" id="location" required class="form-control">
                    </div>
                    <div class="form-group col-2">
                        <label>Max People</label>
                        <input type="number" name="max_people" id="max_people" value="20" class="form-control">
                    </div>
                    <div class="form-group col-2">
                        <label>Min Age</label>
                        <input type="number" name="min_age" id="min_age" value="0" class="form-control">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group col-6">
                        <label>Price Local (RWF) *</label>
                        <input type="number" name="price_local" id="price_local" step="0.01" required class="form-control">
                    </div>
                    <div class="form-group col-6">
                        <label>Price Foreign (USD) *</label>
                        <input type="number" name="price_foreign" id="price_foreign" step="0.01" required class="form-control">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group col-6">
                        <label>Latitude</label>
                        <input type="text" name="latitude" id="latitude" class="form-control" 
                               placeholder="e.g., -1.9403">
                    </div>
                    <div class="form-group col-6">
                        <label>Longitude</label>
                        <input type="text" name="longitude" id="longitude" class="form-control" 
                               placeholder="e.g., 29.8739">
                    </div>
                </div>

                <div class="form-group">
                    <label>Featured Image</label>
                    <input type="file" name="featured_image" id="featured_image" accept="image/*" class="form-control">
                    <small>Leave empty to keep current image (when editing)</small>
                </div>

                <div class="form-row">
                    <div class="form-group col-6">
                        <label>Status</label>
                        <select name="status" id="status" class="form-control">
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                    <div class="form-group col-6">
                        <label class="checkbox-label">
                            <input type="checkbox" name="featured" id="featured">
                            Featured Tour
                        </label>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('createTourModal')">
                        Cancel
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Save Tour
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script src="../assets/js/admin.js"></script>
    <script>
    // Search and filter
    document.getElementById('searchTours').addEventListener('input', filterTable);
    document.getElementById('filterCategory').addEventListener('change', filterTable);
    document.getElementById('filterStatus').addEventListener('change', filterTable);

    function filterTable() {
        const search = document.getElementById('searchTours').value.toLowerCase();
        const category = document.getElementById('filterCategory').value;
        const status = document.getElementById('filterStatus').value;
        const rows = document.querySelectorAll('#toursTable tbody tr');

        rows.forEach(row => {
            const text = row.textContent.toLowerCase();
            const rowCategory = row.dataset.category;
            const rowStatus = row.dataset.status;

            const matchSearch = text.includes(search);
            const matchCategory = !category || rowCategory === category;
            const matchStatus = !status || rowStatus === status;

            row.style.display = matchSearch && matchCategory && matchStatus ? '' : 'none';
        });
    }

    function editTour(tour) {
        document.getElementById('modalTitle').textContent = 'Edit Tour';
        document.getElementById('formAction').value = 'update';
        document.getElementById('tourId').value = tour.id;
        document.getElementById('title').value = tour.title;
        document.getElementById('category_id').value = tour.category_id;
        document.getElementById('description').value = tour.description;
        document.getElementById('itinerary').value = tour.itinerary || '';
        document.getElementById('duration').value = tour.duration;
        document.getElementById('location').value = tour.location;
        document.getElementById('max_people').value = tour.max_people;
        document.getElementById('min_age').value = tour.min_age;
        document.getElementById('price_local').value = tour.price_local;
        document.getElementById('price_foreign').value = tour.price_foreign;
        document.getElementById('latitude').value = tour.latitude || '';
        document.getElementById('longitude').value = tour.longitude || '';
        document.getElementById('status').value = tour.status;
        document.getElementById('featured').checked = tour.featured == 1;
        
        openModal('createTourModal');
    }

    function deleteTour(id, title) {
        if (confirm(`Are you sure you want to delete "${title}"?`)) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.innerHTML = `
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="tour_id" value="${id}">
            `;
            document.body.appendChild(form);
            form.submit();
        }
    }
    </script>
</body>
</html>
