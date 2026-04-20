<?php
/**
 * PwanDeal - My Reviews (Received)
 */
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit();
}

require_once '../config/database.php';

$user_id = $_SESSION['user_id'];

// Pagination Setup
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

// 1. Fetch Summary Stats & User Info
$user_stmt = $conn->prepare("
    SELECT name, profile_photo, 
           COALESCE(average_rating, 0) as average_rating, 
           COALESCE(total_reviews, 0) as total_reviews 
    FROM users WHERE user_id = ?
");
$user_stmt->bind_param('i', $user_id);
$user_stmt->execute();
$user = $user_stmt->get_result()->fetch_assoc();

$total_reviews = $user['total_reviews'];
$total_pages = ceil($total_reviews / $limit);

// 2. Fetch Paginated Reviews with Join on Reviewer and Listing
$sql = "SELECT r.*, u.name as reviewer_name, u.profile_photo as reviewer_photo, l.title as listing_title
        FROM reviews r
        LEFT JOIN users u ON r.from_user_id = u.user_id
        LEFT JOIN listings l ON r.listing_id = l.listing_id
        WHERE r.to_user_id = ?
        ORDER BY r.created_at DESC
        LIMIT ? OFFSET ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param('iii', $user_id, $limit, $offset);
$stmt->execute();
$reviews = $stmt->get_result();

$page_title = 'My Feedback';
include '../includes/header.php';
?>

<div class="container py-5">
    <div class="row mb-5">
        <div class="col-12">
            <div class="card border-0 shadow-sm rounded-4 bg-white overflow-hidden">
                <div class="card-body p-4 p-lg-5">
                    <div class="row align-items-center">
                        <div class="col-md-auto text-center mb-4 mb-md-0">
                            <div class="rating-circle mx-auto">
                                <h1 class="fw-bold mb-0 text-dark"><?= number_format($user['average_rating'], 1) ?></h1>
                                <div class="text-warning">
                                    <i class="bi bi-star-fill"></i>
                                </div>
                            </div>
                        </div>
                        <div class="col-md ps-md-4">
                            <h2 class="fw-bold text-dark mb-1">Your Campus Reputation</h2>
                            <p class="text-muted mb-4">Feedback helps you get more customers on PwanDeal.</p>
                            
                            <div class="d-flex flex-wrap gap-4">
                                <div class="stat-item">
                                    <span class="text-muted small fw-bold text-uppercase d-block mb-1">Total Reviews</span>
                                    <h4 class="fw-bold mb-0"><?= $total_reviews ?></h4>
                                </div>
                                <div class="vr d-none d-md-block"></div>
                                <div class="stat-item">
                                    <span class="text-muted small fw-bold text-uppercase d-block mb-1">Trust Level</span>
                                    <?php if($user['average_rating'] >= 4.5): ?>
                                        <span class="badge bg-success rounded-pill px-3">Elite Provider</span>
                                    <?php elseif($user['average_rating'] >= 3.0): ?>
                                        <span class="badge bg-primary rounded-pill px-3">Verified Student</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary rounded-pill px-3">New/Improving</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <h5 class="fw-bold mb-4 d-flex align-items-center">
                <i class="bi bi-chat-left-text me-2 text-primary"></i> 
                Recent Student Feedback
            </h5>
            
            <?php if ($reviews->num_rows > 0): ?>
                <?php while ($row = $reviews->fetch_assoc()): ?>
                    <div class="card border-0 shadow-sm rounded-4 mb-4 review-card">
                        <div class="card-body p-4">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <div class="d-flex align-items-center">
                                    <img src="<?= !empty($row['reviewer_photo']) ? '../uploads/profiles/'.$row['reviewer_photo'] : '../assets/img/default-avatar.png' ?>" 
                                         class="rounded-circle me-3 border" style="width: 48px; height: 48px; object-fit: cover;">
                                    <div>
                                        <h6 class="fw-bold mb-0 text-dark"><?= htmlspecialchars($row['reviewer_name'] ?? 'Anonymous Student') ?></h6>
                                        <small class="text-muted"><?= date('M j, Y', strtotime($row['created_at'])) ?></small>
                                    </div>
                                </div>
                                <div class="text-warning">
                                    <?php for($i=1; $i<=5; $i++) echo '<i class="bi bi-star'.($i <= $row['rating'] ? '-fill' : '').'"></i>'; ?>
                                </div>
                            </div>
                            
                            <h6 class="fw-bold text-dark mb-2"><?= htmlspecialchars($row['title']) ?></h6>
                            <p class="text-secondary mb-3" style="font-style: italic;">"<?= htmlspecialchars($row['comment']) ?>"</p>
                            
                            <?php if ($row['listing_title']): ?>
                                <div class="bg-light px-3 py-2 rounded-pill d-inline-block">
                                    <small class="text-muted">
                                        <i class="bi bi-cart-check me-1"></i> Item: <span class="text-dark fw-medium"><?= htmlspecialchars($row['listing_title']) ?></span>
                                    </small>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endwhile; ?>

                <?php if ($total_pages > 1): ?>
                    <nav class="mt-5">
                        <ul class="pagination justify-content-center gap-2">
                            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                <li class="page-item <?= ($i == $page) ? 'active' : '' ?>">
                                    <a class="page-link border-0 shadow-sm rounded-circle d-flex align-items-center justify-content-center" 
                                       style="width: 40px; height: 40px;" 
                                       href="?page=<?= $i ?>"><?= $i ?></a>
                                </li>
                            <?php endfor; ?>
                        </ul>
                    </nav>
                <?php endif; ?>

            <?php else: ?>
                <div class="text-center py-5 bg-white rounded-4 shadow-sm border">
                    <div class="display-1 text-muted mb-3"><i class="bi bi-star"></i></div>
                    <h5 class="text-dark fw-bold">No feedback yet</h5>
                    <p class="text-muted small px-4">Transactions you complete will appear here once rated.</p>
                </div>
            <?php endif; ?>
        </div>

        <div class="col-lg-4">
            <div class="sticky-top" style="top: 100px;">
                <div class="card border-0 rounded-4 shadow-sm mb-4 overflow-hidden">
                    <div class="card-header bg-dark text-white border-0 py-3">
                        <h6 class="fw-bold mb-0"><i class="bi bi-lightning-charge me-2 text-warning"></i>Boost Your Rating</h6>
                    </div>
                    <div class="card-body p-4">
                        <div class="d-flex mb-4">
                            <div class="me-3 text-primary"><i class="bi bi-clock-history fs-4"></i></div>
                            <div>
                                <h6 class="fw-bold mb-1 small">Speed Matters</h6>
                                <p class="text-muted x-small mb-0">Replying within 30 minutes significantly increases trust.</p>
                            </div>
                        </div>
                        <div class="d-flex mb-4">
                            <div class="me-3 text-primary"><i class="bi bi-camera fs-4"></i></div>
                            <div>
                                <h6 class="fw-bold mb-1 small">Honest Photos</h6>
                                <p class="text-muted x-small mb-0">Show flaws clearly to avoid 1-star disappointment.</p>
                            </div>
                        </div>
                        <div class="d-flex">
                            <div class="me-3 text-primary"><i class="bi bi-heart fs-4"></i></div>
                            <div>
                                <h6 class="fw-bold mb-1 small">Be Polite</h6>
                                <p class="text-muted x-small mb-0">Pwani students value respect as much as the price.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <a href="../listings/create.php" class="btn btn-primary w-100 rounded-pill py-3 fw-bold shadow-sm" style="background-color: #028090; border: none;">
                    Create New Listing
                </a>
            </div>
        </div>
    </div>
</div>

<style>
    .rating-circle {
        width: 110px;
        height: 110px;
        background: #fff;
        border: 5px solid #028090;
        border-radius: 50%;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        box-shadow: 0 4px 15px rgba(2, 128, 144, 0.1);
    }
    .review-card { 
        transition: transform 0.2s, box-shadow 0.2s; 
        border: 1px solid rgba(0,0,0,0.05) !important;
    }
    .review-card:hover { 
        transform: translateY(-3px); 
        box-shadow: 0 10px 20px rgba(0,0,0,0.08) !important;
    }
    .page-link { 
        color: #028090; 
        font-weight: bold; 
        transition: 0.3s;
    }
    .page-item.active .page-link { 
        background-color: #028090; 
        border-color: #028090; 
        color: white;
    }
    .x-small { font-size: 0.75rem; }
</style>

<?php include '../includes/footer.php'; ?>