<?php
/**
 * PwanDeal - View Profile
 */
session_start();
require_once '../config/database.php';

// 1. Get user_id from URL or fallback to logged-in user
$user_id = isset($_GET['id']) ? (int)$_GET['id'] : ($_SESSION['user_id'] ?? 0);

if ($user_id === 0) {
    header('Location: ../auth/login.php');
    exit();
}

// 2. Fetch User Data
$stmt = $conn->prepare('SELECT * FROM users WHERE user_id = ?');
$stmt->bind_param('i', $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

if (!$user) {
    die("User not found!");
}

// 3. Fetch Recent Reviews
$rev_stmt = $conn->prepare('
    SELECT r.*, u.name as reviewer_name 
    FROM reviews r 
    JOIN users u ON r.from_user_id = u.user_id 
    WHERE r.to_user_id = ? 
    ORDER BY r.created_at DESC LIMIT 5
');
$rev_stmt->bind_param('i', $user_id);
$rev_stmt->execute();
$reviews = $rev_stmt->get_result();

// 4. Fetch Active Listings
$list_stmt = $conn->prepare('SELECT * FROM listings WHERE user_id = ? AND status = "active" ORDER BY created_at DESC');
$list_stmt->bind_param('i', $user_id);
$list_stmt->execute();
$listings = $list_stmt->get_result();

$page_title = $user['name'] . "'s Profile";
$base_url = '..';
include '../includes/header.php';
?>

<div class="container py-5">
    <div class="row g-4">
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm rounded-4 text-center p-4 sticky-top" style="top: 2rem;">
                <div class="mb-3">
                    <?php 
                        $photo_path = !empty($user['profile_photo']) ? '../uploads/profiles/'.$user['profile_photo'] : '../assets/img/default-avatar.png';
                    ?>
                    <img src="<?= $photo_path ?>" class="rounded-circle shadow-sm border border-4 border-white" style="width: 150px; height: 150px; object-fit: cover;">
                </div>
                
                <h3 class="fw-bold mb-1" style="color: #1e2761;"><?= htmlspecialchars($user['name']) ?></h3>
                <p class="text-muted small mb-3">Member since <?= date('M Y', strtotime($user['created_at'])) ?></p>

                <div class="d-flex justify-content-center gap-2 mb-4">
                    <div class="px-3 py-2 bg-light rounded-3">
                        <span class="fw-bold d-block"><?= number_format($user['average_rating'], 1) ?> ★</span>
                        <small class="text-muted italic" style="font-size: 0.7rem;">Rating</small>
                    </div>
                    <div class="px-3 py-2 bg-light rounded-3">
                        <span class="fw-bold d-block"><?= $user['total_reviews'] ?></span>
                        <small class="text-muted italic" style="font-size: 0.7rem;">Reviews</small>
                    </div>
                </div>

                <div class="text-start mb-4 border-top pt-3">
                    <p class="mb-2 small"><strong>🎓 School:</strong> <?= htmlspecialchars($user['school'] ?? 'Not specified') ?></p>
                    <p class="mb-2 small"><strong>📚 Year:</strong> <?= $user['year_of_study'] ? 'Year ' . $user['year_of_study'] : 'Not specified' ?></p>
                    <?php if(!empty($user['phone'])): ?>
                        <p class="mb-0 small text-success"><strong>📱 Phone:</strong> <?= htmlspecialchars($user['phone']) ?></p>
                    <?php endif; ?>
                </div>

                <div class="d-grid gap-2">
                    <?php if (isset($_SESSION['user_id']) && $_SESSION['user_id'] == $user_id): ?>
                        <a href="edit.php" class="btn btn-outline-primary rounded-pill btn-sm">Edit Profile</a>
                    <?php else: ?>
                        <a href="../messages/chat.php?user=<?= $user_id ?>" class="btn btn-primary rounded-pill" style="background-color: #028090; border:none;">Message Provider</a>
                        <a href="../reviews/add.php?to_user=<?= $user_id ?>" class="btn btn-outline-warning rounded-pill btn-sm mt-1">Leave Review</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="card border-0 shadow-sm rounded-4 p-4 mb-4">
                <h5 class="fw-bold mb-3" style="color: #028090;">About Me</h5>
                <p class="text-secondary mb-0">
                    <?= !empty($user['bio']) ? nl2br(htmlspecialchars($user['bio'])) : 'This user hasn\'t added a bio yet.' ?>
                </p>
            </div>

            <h5 class="fw-bold mb-3 d-flex align-items-center">
                <span class="me-2">📦</span> Active Services
            </h5>
            <div class="row g-3 mb-5">
                <?php if ($listings->num_rows > 0): ?>
                    <?php while ($list = $listings->fetch_assoc()): ?>
                        <div class="col-md-6">
                            <div class="card h-100 border-0 shadow-sm rounded-3 overflow-hidden">
                                <div class="card-body">
                                    <h6 class="fw-bold"><?= htmlspecialchars($list['title']) ?></h6>
                                    <p class="small text-muted mb-3"><?= substr(htmlspecialchars($list['description']), 0, 80) ?>...</p>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="fw-bold text-dark">KSh <?= number_format($list['price']) ?></span>
                                        <a href="../listings/detail.php?id=<?= $list['listing_id'] ?>" class="btn btn-sm btn-outline-info rounded-pill">View Details</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="col-12"><p class="text-muted italic p-3 bg-light rounded">No active services currently listed.</p></div>
                <?php endif; ?>
            </div>

            <h5 class="fw-bold mb-3 d-flex align-items-center">
                <span class="me-2">💬</span> Student Feedback
            </h5>
            <div class="reviews-list">
                <?php if ($reviews->num_rows > 0): ?>
                    <?php while ($rev = $reviews->fetch_assoc()): ?>
                        <div class="card border-0 shadow-sm rounded-3 mb-3">
                            <div class="card-body">
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="fw-bold small"><?= htmlspecialchars($rev['reviewer_name']) ?></span>
                                    <span class="text-warning small">
                                        <?php for($i=1; $i<=5; $i++) echo $i <= $rev['rating'] ? '★' : '☆'; ?>
                                    </span>
                                </div>
                                <p class="small text-secondary mb-1">"<?= htmlspecialchars($rev['comment']) ?>"</p>
                                <small class="text-muted" style="font-size: 0.7rem;"><?= date('M d, Y', strtotime($rev['created_at'])) ?></small>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p class="text-muted italic small">No reviews yet.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>