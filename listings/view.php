<?php
/**
 * PwanDeal - Marketplace Browse
 */
session_start();
require_once '../config/database.php';

// 1. Filter & Pagination Parameters
$category_filter = $_GET['category'] ?? '';
$search_query = trim($_GET['search'] ?? '');
$min_price = !empty($_GET['min_price']) ? (float)$_GET['min_price'] : 0;
$max_price = !empty($_GET['max_price']) ? (float)$_GET['max_price'] : 1000000; 

$page = max(1, (int)($_GET['page'] ?? 1));
$limit = 12;
$offset = ($page - 1) * $limit;

// 2. Build Dynamic Query
$where = "WHERE l.status = 'active'";
$params = [];
$types = '';

if ($category_filter) {
    $where .= " AND l.category_id = ?";
    $params[] = $category_filter;
    $types .= 'i';
}
if ($search_query) {
    $where .= " AND (l.title LIKE ? OR l.description LIKE ?)";
    $s = "%$search_query%";
    $params[] = $s; $params[] = $s;
    $types .= 'ss';
}
if ($min_price > 0) {
    $where .= " AND l.price >= ?";
    $params[] = $min_price;
    $types .= 'd';
}
if ($max_price < 1000000) {
    $where .= " AND l.price <= ?";
    $params[] = $max_price;
    $types .= 'd';
}

// 3. Count total for pagination
$count_stmt = $conn->prepare("SELECT COUNT(*) as total FROM listings l $where");
if ($types) $count_stmt->bind_param($types, ...$params);
$count_stmt->execute();
$total_listings = $count_stmt->get_result()->fetch_assoc()['total'];
$total_pages = ceil($total_listings / $limit);

// 4. Fetch actual results (FIXED: profile_photo instead of profile_image)
$sql = "SELECT l.*, c.name as category_name, u.name as provider_name, u.profile_photo 
        FROM listings l 
        JOIN categories c ON l.category_id = c.category_id 
        JOIN users u ON l.user_id = u.user_id 
        $where 
        ORDER BY l.created_at DESC 
        LIMIT ? OFFSET ?";

$p_final = $params; 
$p_final[] = $limit;
$p_final[] = $offset;
$t_final = $types . 'ii';

$stmt = $conn->prepare($sql);
$stmt->bind_param($t_final, ...$p_final);
$stmt->execute();
$listings = $stmt->get_result();

$categories = $conn->query("SELECT * FROM categories ORDER BY name ASC");

$page_title = 'Marketplace';
$base_url = '..';
include '../includes/header.php';
?>

<div class="container py-4">
    <div class="row">
        <div class="col-lg-3">
            <div class="card border-0 shadow-sm rounded-4 mb-4 sticky-top" style="top: 20px;">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="fw-bold mb-0">Filters</h5>
                        <a href="browse.php" class="small text-decoration-none">Clear</a>
                    </div>
                    
                    <form method="GET" id="filterForm">
                        <div class="mb-4">
                            <label class="small fw-bold text-muted mb-2 text-uppercase">Keyword</label>
                            <input type="text" name="search" class="form-control bg-light border-0" 
                                   value="<?= htmlspecialchars($search_query) ?>" placeholder="Search...">
                        </div>
                        
                        <div class="mb-4">
                            <label class="small fw-bold text-muted mb-2 text-uppercase">Category</label>
                            <select name="category" class="form-select bg-light border-0">
                                <option value="">All Categories</option>
                                <?php while($cat = $categories->fetch_assoc()): ?>
                                    <option value="<?= $cat['category_id'] ?>" <?= ($category_filter == $cat['category_id']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($cat['name']) ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>

                        <div class="mb-4">
                            <label class="small fw-bold text-muted mb-2 text-uppercase">Budget (KSh)</label>
                            <div class="row g-2">
                                <div class="col-6">
                                    <input type="number" name="min_price" class="form-control form-control-sm bg-light border-0" 
                                           placeholder="Min" value="<?= $min_price > 0 ? $min_price : '' ?>">
                                </div>
                                <div class="col-6">
                                    <input type="number" name="max_price" class="form-control form-control-sm bg-light border-0" 
                                           placeholder="Max" value="<?= $max_price < 1000000 ? $max_price : '' ?>">
                                </div>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary w-100 rounded-pill shadow-sm py-2 fw-bold" style="background-color: #028090; border: none;">
                            Apply Filters
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-9">
            <h4 class="fw-bold mb-4">
                <?= $search_query ? 'Results for "'.htmlspecialchars($search_query).'"' : 'Discover Services' ?>
                <span class="badge bg-light text-dark ms-2 fw-normal fs-6 border"><?= $total_listings ?></span>
            </h4>

            <div class="row g-4">
                <?php if ($listings->num_rows > 0): ?>
                    <?php while ($row = $listings->fetch_assoc()): ?>
                        <div class="col-md-6 col-xl-4">
                            <div class="card h-100 border-0 shadow-sm rounded-4 overflow-hidden listing-hover">
                                <a href="detail.php?id=<?= $row['listing_id'] ?>" class="text-decoration-none text-dark">
                                    <div class="position-relative">
                                        <img src="../uploads/services/<?= $row['image_url'] ?>" 
                                             class="card-img-top" style="height: 180px; object-fit: cover;" 
                                             onerror="this.src='../assets/img/placeholder.jpg'">
                                        <div class="position-absolute bottom-0 start-0 m-2">
                                            <span class="badge bg-dark bg-opacity-75 rounded-pill small">
                                                <?= htmlspecialchars($row['category_name']) ?>
                                            </span>
                                        </div>
                                    </div>
                                    <div class="card-body p-3">
                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                            <h6 class="fw-bold mb-0 text-truncate" style="max-width: 70%;">
                                                <?= htmlspecialchars($row['title']) ?>
                                            </h6>
                                            <span class="text-primary fw-bold">KSh <?= number_format($row['price']) ?></span>
                                        </div>
                                        
                                        <p class="text-muted small mb-3 text-truncate-2">
                                            <?= htmlspecialchars(substr($row['description'], 0, 80)) ?>...
                                        </p>

                                        <div class="d-flex align-items-center pt-2 border-top">
                                            <img src="<?= !empty($row['profile_photo']) ? '../uploads/profiles/'.$row['profile_photo'] : '../assets/img/default-avatar.png' ?>" 
                                                 class="rounded-circle me-2" width="24" height="24" style="object-fit: cover;">
                                            <small class="text-secondary"><?= htmlspecialchars($row['provider_name']) ?></small>
                                        </div>
                                    </div>
                                </a>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="col-12 text-center py-5">
                        <h4 class="fw-bold">No services found</h4>
                        <a href="browse.php" class="btn btn-outline-primary rounded-pill px-4 mt-3">See All Listings</a>
                    </div>
                <?php endif; ?>
            </div>

            </div>
    </div>
</div>

<style>
    .listing-hover { transition: transform 0.2s ease-in-out; }
    .listing-hover:hover { transform: translateY(-5px); }
    .text-truncate-2 {
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }
</style>

<?php include '../includes/footer.php'; ?>