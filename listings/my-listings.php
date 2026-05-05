<?php
/**
 * PwanDeal - My Listings / My Services
 * LOOP-PROOF VERSION
 */

// 1. START SESSION FIRST (before any headers)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 2. LOAD DATABASE
require_once __DIR__ . '/../config/database.php';

// 3. AUTHENTICATION CHECK (NOT to this page, but to login)
if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$listings = [];
$error_message = '';

// 4. FETCH ALL USER'S LISTINGS
try {
    $stmt = $conn->prepare("
        SELECT 
            l.listing_id,
            l.title,
            l.description,
            l.price,
            l.status,
            l.image_url,
            l.created_at,
            l.updated_at,
            c.name as category_name,
            COUNT(r.review_id) as review_count,
            AVG(r.rating) as avg_rating
        FROM listings l
        LEFT JOIN categories c ON l.category_id = c.category_id
        LEFT JOIN reviews r ON l.listing_id = r.listing_id
        WHERE l.user_id = ?
        GROUP BY l.listing_id
        ORDER BY l.created_at DESC
    ");
    
    if (!$stmt) {
        throw new Exception("Database preparation failed: " . $conn->error);
    }
    
    $stmt->bind_param('i', $user_id);
    if (!$stmt->execute()) {
        throw new Exception("Query execution failed: " . $stmt->error);
    }
    
    $result = $stmt->get_result();
    $listings = $result->fetch_all(MYSQLI_ASSOC);
    
} catch (Exception $e) {
    error_log("My Listings Error: " . $e->getMessage());
    $error_message = "Unable to load your listings. Please try again later.";
}

// 5. HANDLE DELETE ACTION
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    $listing_id = isset($_POST['listing_id']) ? (int)$_POST['listing_id'] : 0;
    
    if ($listing_id > 0) {
        try {
            // Verify ownership BEFORE deleting
            $check_stmt = $conn->prepare("SELECT user_id FROM listings WHERE listing_id = ?");
            $check_stmt->bind_param('i', $listing_id);
            $check_stmt->execute();
            $check_result = $check_stmt->get_result()->fetch_assoc();
            
            if ($check_result && $check_result['user_id'] == $user_id) {
                // Safe to delete - user owns this listing
                $delete_stmt = $conn->prepare("DELETE FROM listings WHERE listing_id = ?");
                $delete_stmt->bind_param('i', $listing_id);
                
                if ($delete_stmt->execute()) {
                    $_SESSION['success_msg'] = "Listing deleted successfully.";
                    // Redirect to refresh the page (not a loop - we refresh data)
                    header('Location: my-listings.php');
                    exit();
                } else {
                    $error_message = "Failed to delete listing.";
                }
            } else {
                $error_message = "You don't have permission to delete this listing.";
            }
        } catch (Exception $e) {
            error_log("Delete Error: " . $e->getMessage());
            $error_message = "An error occurred while deleting.";
        }
    }
}

// 6. SET PAGE TITLE
$page_title = 'My Services';

// 7. INCLUDE HEADER (after all redirects and logic)
include __DIR__ . '/../includes/header.php';
?>

<div class="container py-5">
    <!-- Header Section -->
    <div class="row mb-5">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="fw-bold mb-2">📦 My Services</h1>
                    <p class="text-muted">Manage your active listings and track your sales</p>
                </div>
                <a href="create.php" class="btn btn-primary btn-lg fw-bold rounded-pill px-5 shadow-sm" 
                   style="background-color: #028090; border: none;">
                    <i class="bi bi-plus-lg me-2"></i> Post New Service
                </a>
            </div>
        </div>
    </div>

    <!-- Success Message -->
    <?php if (isset($_SESSION['success_msg'])): ?>
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm mb-4" role="alert">
            <i class="bi bi-check-circle me-2"></i>
            <strong><?= htmlspecialchars($_SESSION['success_msg']) ?></strong>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['success_msg']); ?>
    <?php endif; ?>

    <!-- Error Message -->
    <?php if (!empty($error_message)): ?>
        <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm mb-4" role="alert">
            <i class="bi bi-exclamation-triangle me-2"></i>
            <strong><?= htmlspecialchars($error_message) ?></strong>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Empty State -->
    <?php if (empty($listings)): ?>
        <div class="row">
            <div class="col-12">
                <div class="text-center py-5">
                    <div style="font-size: 4rem; opacity: 0.3; margin-bottom: 20px;">📭</div>
                    <h3 class="text-muted fw-bold">No services yet</h3>
                    <p class="text-muted mb-4">Start earning by posting your first service to the marketplace!</p>
                    <a href="create.php" class="btn btn-primary btn-lg fw-bold rounded-pill px-5" 
                       style="background-color: #028090; border: none;">
                        <i class="bi bi-plus-lg me-2"></i> Create Your First Service
                    </a>
                </div>
            </div>
        </div>

    <!-- Listings Grid -->
    <?php else: ?>
        <div class="row g-4">
            <?php foreach ($listings as $listing): ?>
                <div class="col-md-6 col-lg-4">
                    <div class="card border-0 shadow-sm rounded-4 h-100 overflow-hidden listing-card">
                        <!-- Image Section -->
                        <div style="position: relative; height: 200px; background: linear-gradient(135deg, #f0f0f0 0%, #e0e0e0 100%);">
                            <?php 
                                $img_path = !empty($listing['image_url']) && $listing['image_url'] !== 'default-service.jpg'
                                    ? '../assets/uploads/services/' . htmlspecialchars($listing['image_url'])
                                    : '../assets/img/service-placeholder.jpg';
                            ?>
                            <img src="<?= $img_path ?>" 
                                 alt="<?= htmlspecialchars($listing['title']) ?>"
                                 class="w-100 h-100" 
                                 style="object-fit: cover;">
                            
                            <!-- Status Badge -->
                            <span class="position-absolute top-3 start-3 badge <?php 
                                if ($listing['status'] === 'active') echo 'bg-success';
                                elseif ($listing['status'] === 'sold') echo 'bg-secondary';
                                elseif ($listing['status'] === 'inactive') echo 'bg-warning text-dark';
                                else echo 'bg-info';
                            ?>">
                                <?= ucfirst(htmlspecialchars($listing['status'])) ?>
                            </span>

                            <!-- Rating Badge -->
                            <span class="position-absolute top-3 end-3 badge bg-dark bg-opacity-75">
                                <i class="bi bi-star-fill text-warning me-1"></i>
                                <?= $listing['review_count'] > 0 ? number_format($listing['avg_rating'], 1) : 'New' ?>
                            </span>
                        </div>

                        <!-- Content Section -->
                        <div class="card-body d-flex flex-column">
                            <!-- Title -->
                            <h6 class="fw-bold mb-2 text-truncate" title="<?= htmlspecialchars($listing['title']) ?>">
                                <?= htmlspecialchars($listing['title']) ?>
                            </h6>

                            <!-- Category & Date -->
                            <p class="small text-muted mb-2">
                                <i class="bi bi-tag me-1"></i> 
                                <?= htmlspecialchars($listing['category_name'] ?? 'Uncategorized') ?>
                            </p>

                            <!-- Description Preview -->
                            <p class="small text-muted mb-3 flex-grow-1" style="height: 40px; overflow: hidden; line-height: 1.4;">
                                <?= htmlspecialchars(substr($listing['description'], 0, 70)) ?>...
                            </p>

                            <!-- Price & Reviews -->
                            <div class="d-flex justify-content-between align-items-center mb-3 pb-3 border-bottom">
                                <span class="h5 fw-bold mb-0" style="color: #028090;">
                                    KSh <?= number_format($listing['price'], 0) ?>
                                </span>
                                <span class="small text-muted">
                                    <i class="bi bi-chat-dots"></i> <?= $listing['review_count'] ?> review<?= $listing['review_count'] !== 1 ? 's' : '' ?>
                                </span>
                            </div>

                            <!-- Action Buttons -->
                            <div class="d-grid gap-2">
                                <a href="detail.php?id=<?= $listing['listing_id'] ?>" class="btn btn-outline-primary btn-sm rounded-pill fw-bold">
                                    <i class="bi bi-eye me-1"></i> View
                                </a>
                                <a href="edit.php?id=<?= $listing['listing_id'] ?>" class="btn btn-outline-secondary btn-sm rounded-pill fw-bold">
                                    <i class="bi bi-pencil me-1"></i> Edit
                                </a>
                                <form method="POST" style="display: inline-block; width: 100%;" 
                                      onsubmit="return confirm('Are you sure you want to delete this listing? This action cannot be undone.');">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="listing_id" value="<?= $listing['listing_id'] ?>">
                                    <button type="submit" class="btn btn-outline-danger btn-sm w-100 rounded-pill fw-bold">
                                        <i class="bi bi-trash me-1"></i> Delete
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<style>
    .listing-card {
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .listing-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 12px 32px rgba(2, 128, 144, 0.2) !important;
    }

    .listing-card img {
        transition: transform 0.3s ease;
    }

    .listing-card:hover img {
        transform: scale(1.05);
    }
</style>

<?php include __DIR__ . '/../includes/footer.php'; ?>