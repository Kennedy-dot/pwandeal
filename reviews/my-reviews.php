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
    SELECT name, profile_photo, average_rating, total_reviews 
    FROM users WHERE user_id = ?
");
$user_stmt->bind_param('i', $user_id);
$user_stmt->execute();
$user = $user_stmt->get_result()->fetch_assoc();

// 2. Count total reviews for pagination calculation
$total_reviews = $user['total_reviews'] ?? 0;
$total_pages = ceil($total_reviews / $limit);

// 3. Fetch Paginated Reviews
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

$page_title = 'My Reviews';
$base_url = '..';
include '../includes/header.php';
?>

<div class="container py-5">
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                <div class="card-body p-4 bg-white">
                    <div class="row align-items-center">
                        <div class="col-md-auto text-center mb-3 mb-md-0">
                            <div class="d-inline-flex align-items-center justify-content-center rounded-circle shadow-sm" 
                                 style="width: 100px; height: 100px; background: linear-gradient(135deg, #028090, #00BFB2); color: white;">
                                <div class="text-center">
                                    <h2 class="fw-bold mb-0"><?= number_format($user['average_rating'], 1) ?></h2>
                                    <small class="text-uppercase" style="font-size: 0.6rem;">Rating</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md">
                            <h3 class="fw-bold text-dark mb-1">Your Reputation</h3>
                            <p class="text-muted mb-3">Based on feedback from fellow students</p>
                            <div class="d-flex gap-4">
                                <div>
                                    <span class="text-uppercase small fw-bold text-muted d-block">Received</span>
                                    <span class="fs-5 fw-bold"><?= $total_reviews ?> Reviews</span>
                                </div>
                                <div class="border-start ps-4">
                                    <span class="text-uppercase small fw-bold text-muted d-block">Status</span>
                                    <span class="badge bg-success rounded-pill">Verified Provider</span>
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
            <h5 class="fw-bold mb-4">Latest Feedback</h5>
            
            <?php if ($reviews->num_rows > 0): ?>
                <?php while ($row = $reviews->fetch_assoc()): ?>
                    <div class="card border-0 shadow-sm rounded-4 mb-3">
                        <div class="card-body p-4">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <div class="d-flex align-items-center">
                                    <img src="<?= $row['reviewer_photo'] ? '../assets/uploads/profiles/'.$row['reviewer_photo'] : '../assets/img/default-avatar.png' ?>" 
                                         class="rounded-circle me-3" style="width: 45px; height: 45px; object-fit: cover;">
                                    <div>
                                        <h6 class="fw-bold mb-0"><?= htmlspecialchars($row['reviewer_name'] ?? 'Anonymous User') ?></h6>
                                        <small class="text-muted"><?= date('M j, Y', strtotime($row['created_at'])) ?></small>
                                    </div>
                                </div>
                                <div class="text-warning">
                                    <?php for($i=1; $i<=5; $i++) echo $i <= $row['rating'] ? '★' : '☆'; ?>
                                </div>
                            </div>
                            
                            <h6 class="fw-bold"><?= htmlspecialchars($row['title']) ?></h6>
                            <p class="text-secondary mb-3"><?= htmlspecialchars($row['comment']) ?></p>
                            
                            <?php if ($row['listing_title']): ?>
                                <div class="bg-light p-2 rounded-3 d-inline-block">
                                    <small class="text-muted">
                                        <i class="bi bi-tag-fill me-1"></i> For: <strong><?= htmlspecialchars($row['listing_title']) ?></strong>
                                    </small>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endwhile; ?>

                <?php if ($total_pages > 1): ?>
                    <nav class="mt-4">
                        <ul class="pagination">
                            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                <li class="page-item <?= ($i == $page) ? 'active' : '' ?>">
                                    <a class="page-link border-0 shadow-sm mx-1 rounded-3" href="?page=<?= $i ?>"><?= $i ?></a>
                                </li>
                            <?php endfor; ?>
                        </ul>
                    </nav>
                <?php endif; ?>

            <?php else: ?>
                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-body p-5 text-center">
                        <div class="display-1 text-light mb-3">💬</div>
                        <h4>No reviews yet</h4>
                        <p class="text-muted">Once students complete a deal with you, their reviews will appear here.</p>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <div class="col-lg-4 mt-4 mt-lg-0">
            <div class="card border-0 bg-dark text-white rounded-4 shadow-sm p-3">
                <div class="card-body">
                    <h5 class="fw-bold mb-3">Boost Your Rating 🚀</h5>
                    <ul class="small list-unstyled">
                        <li class="mb-3"><i class="bi bi-check2-circle text-info me-2"></i> Be responsive to messages.</li>
                        <li class="mb-3"><i class="bi bi-check2-circle text-info me-2"></i> Describe your services accurately.</li>
                        <li class="mb-3"><i class="bi bi-check2-circle text-info me-2"></i> Encourage students to leave feedback after a deal.</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .page-link { color: #028090; }
    .page-item.active .page-link { background-color: #028090; color: white; }
</style>

<?php include '../includes/footer.php'; ?>