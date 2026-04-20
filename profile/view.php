<?php
/**
 * PwanDeal - Refined User Profile View
 */
session_start();
require_once __DIR__ . '/../config/database.php';

// 1. Get user_id from URL or fallback to logged-in user
$view_user_id = isset($_GET['id']) ? (int)$GET['id'] : ($_SESSION['user_id'] ?? 0);

if ($view_user_id === 0) {
    header('Location: ../auth/login.php');
    exit();
}

// 2. Fetch User Data
$stmt = $conn->prepare('SELECT * FROM users WHERE user_id = ?');
$stmt->bind_param('i', $view_user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

if (!$user) { 
    die("User not found!"); 
}

// 3. Fetch Reviews (Joined with reviewer name)
$rev_stmt = $conn->prepare('
    SELECT r.*, u.name as reviewer_name 
    FROM reviews r 
    JOIN users u ON r.from_user_id = u.user_id 
    WHERE r.to_user_id = ? 
    ORDER BY r.created_at DESC
');
$rev_stmt->bind_param('i', $view_user_id);
$rev_stmt->execute();
$reviews = $rev_stmt->get_result();

// 4. Fetch Active Listings (With Primary Image)
$list_stmt = $conn->prepare('
    SELECT l.*, 
    (SELECT image_url FROM listing_images WHERE listing_id = l.listing_id AND is_primary = 1 LIMIT 1) as main_image
    FROM listings l 
    WHERE l.user_id = ? AND l.status = "active" 
    ORDER BY l.created_at DESC
');
$list_stmt->bind_param('i', $view_user_id);
$list_stmt->execute();
$listings = $list_stmt->get_result();

$page_title = htmlspecialchars($user['name']) . " | Profile";
include __DIR__ . '/../includes/header.php';
?>

<style>
    .profile-cover {
        height: 200px;
        background: linear-gradient(135deg, #028090 0%, #1e2761 100%);
        border-radius: 0 0 30px 30px;
    }
    .profile-img-wrapper {
        margin-top: -75px;
    }
    .profile-avatar {
        width: 150px;
        height: 150px;
        object-fit: cover;
        border: 6px solid #fff;
        background: #fff;
    }
    .nav-pills .nav-link {
        color: #6c757d;
        font-weight: 600;
        padding: 10px 25px;
        border-radius: 50px;
    }
    .nav-pills .nav-link.active {
        background-color: #028090;
        color: white;
    }
    .stats-card {
        background: #f8f9fa;
        border-radius: 20px;
        transition: 0.3s;
    }
</style>

<div class="profile-cover w-100"></div>

<div class="container mb-5">
    <div class="row">
        <div class="col-lg-4">
            <div class="text-center profile-img-wrapper">
                <?php $photo = !empty($user['profile_photo']) ? '../uploads/profiles/'.$user['profile_photo'] : '../assets/img/default-avatar.png'; ?>
                <img src="<?= $photo ?>" class="rounded-circle profile-avatar shadow-sm mb-3">
                
                <h3 class="fw-bold mb-0"><?= htmlspecialchars($user['name']) ?></h3>
                <p class="text-muted small"><i class="bi bi-patch-check-fill text-primary"></i> Verified Pwani Student</p>
                
                <div class="row g-2 mb-4">
                    <div class="col-6">
                        <div class="stats-card p-3 shadow-sm">
                            <h5 class="mb-0 fw-bold"><?= number_format($user['average_rating'] ?? 0, 1) ?> <i class="bi bi-star-fill text-warning small"></i></h5>
                            <small class="text-muted">Rating</small>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="stats-card p-3 shadow-sm">
                            <h5 class="mb-0 fw-bold"><?= $listings->num_rows ?></h5>
                            <small class="text-muted">Listings</small>
                        </div>
                    </div>
                </div>

                <div class="card border-0 shadow-sm rounded-4 text-start p-4 mb-4">
                    <h6 class="fw-bold mb-3">Campus Details</h6>
                    <ul class="list-unstyled small mb-0">
                        <li class="mb-2"><i class="bi bi-mortarboard text-primary me-2"></i> <?= htmlspecialchars($user['school'] ?: 'Not Specified') ?></li>
                        <li class="mb-2"><i class="bi bi-calendar3 text-primary me-2"></i> <?= $user['year_of_study'] ? 'Year ' . $user['year_of_study'] : 'Not Specified' ?></li>
                        <?php if(!empty($user['phone'])): ?>
                            <li class="mb-2 text-success"><i class="bi bi-whatsapp me-2"></i> <?= htmlspecialchars($user['phone']) ?></li>
                        <?php endif; ?>
                        <li><i class="bi bi-clock-history text-primary me-2"></i> Joined <?= date('M Y', strtotime($user['created_at'])) ?></li>
                    </ul>
                </div>

                <div class="d-grid gap-2">
                    <?php if (isset($_SESSION['user_id']) && $_SESSION['user_id'] == $view_user_id): ?>
                        <a href="edit.php" class="btn btn-outline-primary rounded-pill"><i class="bi bi-pencil-square me-2"></i>Edit My Profile</a>
                    <?php else: ?>
                        <a href="../messages/chat.php?user=<?= $view_user_id ?>" class="btn btn-primary rounded-pill shadow-sm py-2" style="background-color: #028090; border: none;"><i class="bi bi-chat-fill me-2"></i>Message Student</a>
                        <a href="../reviews/add.php?to_user=<?= $view_user_id ?>" class="btn btn-link btn-sm text-decoration-none">Leave a Review</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-lg-8 mt-4 mt-lg-0">
            <div class="card border-0 shadow-sm rounded-4 p-4 mb-4">
                <h5 class="fw-bold mb-3" style="color: #028090;">About Me</h5>
                <p class="text-secondary mb-0">
                    <?= !empty($user['bio']) ? nl2br(htmlspecialchars($user['bio'])) : 'This student hasn\'t added a bio yet.' ?>
                </p>
            </div>

            <ul class="nav nav-pills mb-4 gap-2" id="profileTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active shadow-sm" id="listings-tab" data-bs-toggle="pill" data-bs-target="#listings" type="button">Services</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link shadow-sm" id="reviews-tab" data-bs-toggle="pill" data-bs-target="#reviews" type="button">Reviews (<?= $reviews->num_rows ?>)</button>
                </li>
            </ul>

            <div class="tab-content" id="profileTabsContent">
                <div class="tab-pane fade show active" id="listings" role="tabpanel">
                    <div class="row g-3">
                        <?php if ($listings->num_rows > 0): ?>
                            <?php while ($list = $listings->fetch_assoc()): ?>
                                <div class="col-md-6">
                                    <div class="card h-100 border-0 shadow-sm rounded-4 overflow-hidden">
                                        <img src="<?= $list['main_image'] ? '../uploads/services/'.$list['main_image'] : '../assets/img/placeholder.jpg' ?>" class="card-img-top" style="height: 180px; object-fit: cover;">
                                        <div class="card-body p-3">
                                            <h6 class="fw-bold mb-1 text-truncate"><?= htmlspecialchars($list['title']) ?></h6>
                                            <p class="text-primary fw-bold mb-3">KSh <?= number_format($list['price']) ?></p>
                                            <a href="../listings/detail.php?id=<?= $list['listing_id'] ?>" class="btn btn-sm btn-light w-100 rounded-pill">View Listing</a>
                                        </div>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <div class="text-center py-5 w-100">
                                <p class="text-muted">No active services currently listed.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="tab-pane fade" id="reviews" role="tabpanel">
                    <?php if ($reviews->num_rows > 0): ?>
                        <?php while ($rev = $reviews->fetch_assoc()): ?>
                            <div class="card border-0 shadow-sm rounded-4 p-3 mb-3">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="fw-bold small text-dark"><?= htmlspecialchars($rev['reviewer_name']) ?></span>
                                    <div class="text-warning small">
                                        <?php for($i=1; $i<=5; $i++) echo '<i class="bi bi-star'.($i <= $rev['rating'] ? '-fill' : '').'"></i>'; ?>
                                    </div>
                                </div>
                                <p class="text-muted small mb-1">"<?= htmlspecialchars($rev['comment']) ?>"</p>
                                <small class="text-muted" style="font-size: 0.75rem;"><?= date('M d, Y', strtotime($rev['created_at'])) ?></small>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div class="text-center py-5 w-100">
                            <p class="text-muted">No reviews yet.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>