<?php
/**
 * PwanDeal - Refined User Profile View
 */
session_start();
require_once __DIR__ . '/../config/database.php';

// 1. Get user_id from URL or fallback to logged-in user
$view_user_id = isset($_GET['id']) ? (int)$_GET['id'] : ($_SESSION['user_id'] ?? 0);

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
        height: 180px;
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
        border: 1px solid transparent;
    }
    .nav-pills .nav-link.active {
        background-color: #028090;
        color: white;
    }
    .stats-card {
        background: #f8f9fa;
        border-radius: 15px;
        transition: transform 0.2s;
    }
    .stats-card:hover {
        transform: translateY(-3px);
    }
    .listing-card-img {
        height: 160px;
        object-fit: cover;
    }
</style>

<div class="profile-cover w-100"></div>

<div class="container mb-5">
    <div class="row">
        <div class="col-lg-4">
            <div class="text-center profile-img-wrapper">
                <?php 
                    // Added ?v=time() cache-buster here
                    $photo_path = (!empty($user['profile_photo']) && file_exists('../uploads/profiles/'.$user['profile_photo'])) 
                                  ? '../uploads/profiles/'.$user['profile_photo'] . '?v=' . time() 
                                  : '../assets/img/default-avatar.png'; 
                ?>
                <img src="<?= $photo_path ?>" class="rounded-circle profile-avatar shadow-sm mb-3" alt="Profile Photo">
                
                <h3 class="fw-bold mb-0"><?= htmlspecialchars($user['name']) ?></h3>
                <p class="text-muted small"><i class="bi bi-patch-check-fill text-primary"></i> Pwani Student Community</p>
                
                <div class="row g-2 mb-4 px-2">
                    <div class="col-6">
                        <div class="stats-card p-3 shadow-sm border">
                            <h5 class="mb-0 fw-bold"><?= number_format($user['average_rating'] ?? 0, 1) ?> <i class="bi bi-star-fill text-warning small"></i></h5>
                            <small class="text-muted">Rating</small>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="stats-card p-3 shadow-sm border">
                            <h5 class="mb-0 fw-bold"><?= $listings->num_rows ?></h5>
                            <small class="text-muted">Services</small>
                        </div>
                    </div>
                </div>
                
                <!-- ... (Rest of your profile HTML remains the same) -->
                
                <div class="card border-0 shadow-sm rounded-4 text-start p-4 mb-4">
                    <h6 class="fw-bold mb-3">Campus Details</h6>
                    <ul class="list-unstyled small mb-0">
                        <li class="mb-3 d-flex align-items-center">
                            <i class="bi bi-mortarboard text-primary me-2 fs-5"></i> 
                            <span><?= htmlspecialchars($user['school'] ?: 'School not set') ?></span>
                        </li>
                        <li class="mb-3 d-flex align-items-center">
                            <i class="bi bi-calendar3 text-primary me-2 fs-5"></i> 
                            <span><?= $user['year_of_study'] ? 'Year ' . $user['year_of_study'] : 'Year not set' ?></span>
                        </li>
                        <?php if(!empty($user['phone'])): ?>
                            <li class="mb-3 d-flex align-items-center">
                                <i class="bi bi-whatsapp text-success me-2 fs-5"></i> 
                                <span class="fw-semibold"><?= htmlspecialchars($user['phone']) ?></span>
                            </li>
                        <?php endif; ?>
                        <li class="d-flex align-items-center">
                            <i class="bi bi-clock-history text-muted me-2 fs-5"></i> 
                            <span class="text-muted">Joined <?= date('M Y', strtotime($user['created_at'])) ?></span>
                        </li>
                    </ul>
                </div>

                <div class="d-grid gap-2">
                    <?php if (isset($_SESSION['user_id']) && $_SESSION['user_id'] == $view_user_id): ?>
                        <a href="edit.php" class="btn btn-outline-dark rounded-pill fw-bold">
                            <i class="bi bi-pencil-square me-2"></i>Edit My Profile
                        </a>
                    <?php else: ?>
                        <a href="../messages/chat.php?user=<?= $view_user_id ?>" class="btn btn-primary rounded-pill shadow-sm py-2 fw-bold" style="background-color: #028090; border: none;">
                            <i class="bi bi-chat-fill me-2"></i>Message Student
                        </a>
                        <a href="../reviews/add.php?to_user_id=<?= $view_user_id ?>" class="btn btn-link btn-sm text-decoration-none text-muted mt-1">
                            <i class="bi bi-star me-1"></i>Rate this user
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-lg-8 mt-4 mt-lg-0">
            <!-- ... (The rest of your profile remains exactly the same as provided) ... -->
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>