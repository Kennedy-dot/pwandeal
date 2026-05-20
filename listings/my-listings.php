<?php
/**
 * PwanDeal - My Listings / My Services
 * FIXED VERSION: Corrected column mapping
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$listings = [];
$error_message = '';

// 4. FETCH ALL USER'S LISTINGS
try {
    // We join the listing_images table instead of assuming image_url is in listings
    $stmt = $conn->prepare("
        SELECT 
            l.listing_id,
            l.title,
            l.description,
            l.price,
            l.status,
            l.created_at,
            l.updated_at,
            c.name as category_name,
            img.image_url,
            COUNT(r.review_id) as review_count,
            IFNULL(AVG(r.rating), 0) as avg_rating
        FROM listings l
        LEFT JOIN categories c ON l.category_id = c.category_id
        LEFT JOIN reviews r ON l.listing_id = r.listing_id
        LEFT JOIN listing_images img ON l.listing_id = img.listing_id AND img.is_primary = 1
        WHERE l.user_id = ?
        GROUP BY l.listing_id
        ORDER BY l.created_at DESC
    ");
    
    if (!$stmt) {
        throw new Exception("Database prepare error: " . $conn->error);
    }
    
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $listings = $result->fetch_all(MYSQLI_ASSOC);
    
} catch (Exception $e) {
    error_log("My Listings Error: " . $e->getMessage());
    $error_message = "Database Error: " . $e->getMessage();
}

// 5. HANDLE DELETE ACTION
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    $listing_id = isset($_POST['listing_id']) ? (int)$_POST['listing_id'] : 0;
    
    if ($listing_id > 0) {
        $check_stmt = $conn->prepare("SELECT user_id FROM listings WHERE listing_id = ?");
        $check_stmt->bind_param('i', $listing_id);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result()->fetch_assoc();
        
        if ($check_result && $check_result['user_id'] == $user_id) {
            $delete_stmt = $conn->prepare("DELETE FROM listings WHERE listing_id = ?");
            $delete_stmt->bind_param('i', $listing_id);
            if ($delete_stmt->execute()) {
                $_SESSION['success_msg'] = "Listing deleted successfully.";
                header('Location: my-services.php');
                exit();
            }
        }
    }
}

$page_title = 'My Services';
include __DIR__ . '/../includes/header.php';
?>

<div class="container py-5">
    <div class="row mb-5">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="fw-bold mb-2">📦 My Services</h1>
                    <p class="text-muted">Manage your active listings and track your sales</p>
                </div>
                <a href="create.php" class="btn btn-primary btn-lg fw-bold rounded-pill px-5 shadow-sm" style="background-color: #028090; border: none;">
                    <i class="bi bi-plus-lg me-2"></i> Post New Service
                </a>
            </div>
        </div>
    </div>

    <?php if (isset($_SESSION['success_msg'])): ?>
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm mb-4">
            <?= htmlspecialchars($_SESSION['success_msg']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['success_msg']); endif; ?>

    <?php if (!empty($error_message)): ?>
        <div class="alert alert-danger shadow-sm mb-4"><?= htmlspecialchars($error_message) ?></div>
    <?php endif; ?>

    <?php if (empty($listings) && empty($error_message)): ?>
        <div class="text-center py-5">
            <h3 class="text-muted">No services yet</h3>
            <a href="create.php" class="btn btn-primary">Create Your First Service</a>
        </div>
    <?php else: ?>
        <div class="row g-4">
            <?php foreach ($listings as $listing): ?>
                <div class="col-md-6 col-lg-4">
                    <div class="card border-0 shadow-sm rounded-4 h-100">
                        <div class="position-relative" style="height: 200px; background: #eee;">
                            <img src="<?= !empty($listing['image_url']) ? '../uploads/services/' . htmlspecialchars($listing['image_url']) : '../assets/img/service-placeholder.jpg' ?>" 
                                 class="w-100 h-100" style="object-fit: cover;">
                        </div>
                        <div class="card-body">
                            <h6 class="fw-bold"><?= htmlspecialchars($listing['title']) ?></h6>
                            <p class="small text-muted"><?= htmlspecialchars($listing['category_name'] ?? 'Uncategorized') ?></p>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="h5 fw-bold" style="color: #028090;">KSh <?= number_format($listing['price'], 0) ?></span>
                                <span class="small text-muted"><?= $listing['review_count'] ?> reviews</span>
                            </div>
                            <div class="d-grid gap-2 mt-3">
                                <a href="edit.php?id=<?= $listing['listing_id'] ?>" class="btn btn-outline-secondary btn-sm">Edit</a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>