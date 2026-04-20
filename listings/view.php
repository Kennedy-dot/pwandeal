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
// Using a large number as default max, but handling it cleanly in UI
$max_price = !empty($_GET['max_price']) ? (float)$_GET['max_price'] : 2000000; 

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
if (!empty($_GET['max_price'])) { // Only filter max if user actually typed it
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

// 4. Fetch Results
$sql = "SELECT l.*, c.name as category_name, u.name as provider_name, u.profile_photo, i.image_url 
        FROM listings l 
        JOIN categories c ON l.category_id = c.category_id 
        JOIN users u ON l.user_id = u.user_id 
        LEFT JOIN listing_images i ON l.listing_id = i.listing_id AND i.is_primary = 1
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

$page_title = 'Explore PwanDeal';
include '../includes/header.php';
?>

<div class="container py-4">
    <div class="row">
        <div class="col-lg-3">
            <div class="card border-0 shadow-sm rounded-4 mb-4 sticky-top" style="top: 100px;">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="fw-bold mb-0">Filters</h5>
                        <a href="browse.php" class="small text-decoration-none">Clear</a>
                    </div>
                    
                    <form method="GET" action="browse.php">
                        <input type="hidden" name="search" value="<?= htmlspecialchars($search_query) ?>">

                        <div class="mb-3">
                            <label class="small fw-bold text-muted mb-2 text-uppercase">Category</label>
                            <select name="category" class="form-select bg-light border-0 shadow-none">
                                <option value="">All Categories</option>
                                <?php $categories->data_seek(0); while($cat = $categories->fetch_assoc()): ?>
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
                                    <input type="number" name="min_price" class="form-control form-control-sm bg-light border-0 shadow-none" 
                                           placeholder="Min" value="<?= $min_price > 0 ? $min_price : '' ?>">
                                </div>
                                <div class="col-6">
                                    <input type="number" name="max_price" class="form-control form-control-sm bg-light border-0 shadow-none" 
                                           placeholder="Max" value="<?= isset($_GET['max_price']) ? htmlspecialchars($_GET['max_price']) : '' ?>">
                                </div>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary w-100 rounded-pill fw-bold shadow-sm">
                            Apply Filters
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-9">
            <form method="GET" action="browse.php" class="mb-4">
                <div class="input-group shadow-sm rounded-pill overflow-hidden bg-white p-1">
                    <input type="text" name="search" class="form-control border-0 px-4 shadow-none" 
                           placeholder="Search for hostells, tutors, or clothes..." value="<?= htmlspecialchars($search_query) ?>">
                    <button class="btn btn-primary rounded-pill px-4" type="submit">
                        <i class="bi bi-search"></i>
                    </button>
                </div>
            </form>

            <div class="d-flex justify-content-between align-items-center mb-4">
                <h4 class="fw-bold mb-0">
                    <?= $search_query ? 'Results for "' . htmlspecialchars($search_query) . '"' : 'Featured Services' ?>
                </h4>
                <small class="text-muted"><?= $total_listings ?> listings found</small>
            </div>

            <div class="row g-4">
                <?php if ($listings->num_rows > 0): ?>
                    <?php while ($row = $listings->fetch_assoc()): ?>
                        <div class="col-md-6 col-xl-4">
                            <div class="card h-100 border-0 shadow-sm rounded-4 listing-card overflow-hidden">
                                <a href="detail.php?id=<?= $row['listing_id'] ?>" class="text-decoration-none text-dark">
                                    <div class="position-relative">
                                        <img src="<?= !empty($row['image_url']) ? "../uploads/services/".$row['image_url'] : "../assets/img/service-placeholder.jpg" ?>" 
                                             class="card-img-top" style="height: 200px; object-fit: cover;">
                                        <div class="position-absolute top-0 end-0 m-2">
                                            <span class="badge bg-white text-dark shadow-sm rounded-pill py-2 px-3">
                                                KSh <?= number_format($row['price']) ?>
                                            </span>
                                        </div>
                                    </div>
                                    <div class="card-body p-3">
                                        <div class="d-flex justify-content-between align-items-start mb-1">
                                            <span class="text-primary small fw-bold text-uppercase"><?= htmlspecialchars($row['category_name']) ?></span>
                                        </div>
                                        <h6 class="fw-bold text-truncate mb-2"><?= htmlspecialchars($row['title']) ?></h6>
                                        <p class="text-muted small text-truncate-2 mb-3">
                                            <?= htmlspecialchars($row['description']) ?>
                                        </p>
                                        <hr class="opacity-10 my-2">
                                        <div class="d-flex align-items-center">
                                            <img src="<?= !empty($row['profile_photo']) ? '../uploads/profiles/'.$row['profile_photo'] : '../assets/img/default-avatar.png' ?>" 
                                                 class="rounded-circle me-2" width="24" height="24" style="object-fit: cover;">
                                            <span class="small text-secondary"><?= htmlspecialchars($row['provider_name']) ?></span>
                                        </div>
                                    </div>
                                </a>
                            </div>
                        </div>
                    <?php endwhile; ?>

                    <?php if ($total_pages > 1): ?>
                        <div class="col-12 mt-5">
                            <nav>
                                <ul class="pagination justify-content-center gap-2">
                                    <?php for ($i = 1; $i <= $total_pages; $i++): 
                                        $url_params = http_build_query([
                                            'page' => $i,
                                            'search' => $search_query,
                                            'category' => $category_filter,
                                            'min_price' => $min_price,
                                            'max_price' => $_GET['max_price'] ?? ''
                                        ]);
                                    ?>
                                        <li class="page-item">
                                            <a class="page-link rounded-circle border-0 d-flex align-items-center justify-content-center shadow-sm <?= ($i == $page) ? 'bg-primary text-white' : 'bg-white text-dark' ?>" 
                                               style="width: 40px; height: 40px;" href="?<?= $url_params ?>">
                                                <?= $i ?>
                                            </a>
                                        </li>
                                    <?php endfor; ?>
                                </ul>
                            </nav>
                        </div>
                    <?php endif; ?>

                <?php else: ?>
                    <div class="col-12 text-center py-5">
                        <h2 class="display-1 text-light opacity-50"><i class="bi bi-search"></i></h2>
                        <h4 class="text-muted">No deals found for this search.</h4>
                        <a href="browse.php" class="btn btn-outline-primary rounded-pill mt-3 px-4">See All Listings</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<style>
    .listing-card { transition: all 0.3s cubic-bezier(.25,.8,.25,1); }
    .listing-card:hover { transform: translateY(-8px); box-shadow: 0 15px 30px rgba(0,0,0,0.1) !important; }
    .text-truncate-2 {
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }
</style>

<?php include '../includes/footer.php'; ?>