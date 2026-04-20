<?php
/**
 * PwanDeal - Leave a Review
 */
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit();
}

require_once '../config/database.php';

$user_id = $_SESSION['user_id'];
// 'to' matches the provider's user_id, 'listing' is optional
$to_user_id = isset($_GET['to']) ? (int)$_GET['to'] : 0;
$listing_id = isset($_GET['listing']) ? (int)$_GET['listing'] : null;

// Prevent self-reviewing or invalid IDs
if ($to_user_id === 0 || $to_user_id === $user_id) {
    header('Location: ../listings/view.php');
    exit();
}

// Fetch Provider Info
$stmt = $conn->prepare('SELECT name, profile_photo FROM users WHERE user_id = ?');
$stmt->bind_param('i', $to_user_id);
$stmt->execute();
$provider = $stmt->get_result()->fetch_assoc();

if (!$provider) {
    die("User not found.");
}

$error = '';
$success = '';

// Check if already reviewed for this specific listing (if listing provided)
if ($listing_id) {
    $check = $conn->prepare('SELECT review_id FROM reviews WHERE from_user_id = ? AND to_user_id = ? AND listing_id = ?');
    $check->bind_param('iii', $user_id, $to_user_id, $listing_id);
    $check->execute();
    if ($check->get_result()->num_rows > 0) {
        $error = 'You have already reviewed this specific service.';
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$error) {
    $rating = (int)($_POST['rating'] ?? 0);
    $title = trim($_POST['title'] ?? '');
    $comment = trim($_POST['comment'] ?? '');
    
    if ($rating < 1 || $rating > 5) {
        $error = 'Please select a star rating.';
    } elseif (empty($comment)) {
        $error = 'Please share some details about your experience.';
    } else {
        $conn->begin_transaction();
        try {
            // 1. Insert Review
            $stmt = $conn->prepare('INSERT INTO reviews (from_user_id, to_user_id, listing_id, rating, title, comment) VALUES (?, ?, ?, ?, ?, ?)');
            $stmt->bind_param('iiiiss', $user_id, $to_user_id, $listing_id, $rating, $title, $comment);
            $stmt->execute();

            // 2. Atomic Recalculation
            // We update the users table with the new aggregate data immediately
            $update = $conn->prepare('
                UPDATE users u 
                SET u.average_rating = (SELECT AVG(rating) FROM reviews WHERE to_user_id = ?),
                    u.total_reviews = (SELECT COUNT(*) FROM reviews WHERE to_user_id = ?)
                WHERE u.user_id = ?
            ');
            $update->bind_param('iii', $to_user_id, $to_user_id, $to_user_id);
            $update->execute();
            
            $conn->commit();
            $success = 'Thank you! Your review has been posted.';
        } catch (Exception $e) {
            $conn->rollback();
            $error = 'Database error. Please try again later.';
        }
    }
}

$page_title = 'Rate ' . htmlspecialchars($provider['name']);
include '../includes/header.php';
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="card border-0 shadow-lg rounded-4 overflow-hidden">
                <div class="card-body p-4 p-md-5">
                    
                    <?php if ($success): ?>
                        <div class="text-center py-4">
                            <div class="mb-3" style="font-size: 4rem;">🎉</div>
                            <h3 class="fw-bold text-dark">Review Posted!</h3>
                            <p class="text-muted">Your feedback helps fellow Pwani students make better choices.</p>
                            <a href="../profile/view.php?id=<?= $to_user_id ?>" class="btn btn-primary rounded-pill px-4 mt-3 w-100 shadow-sm">View Provider Profile</a>
                        </div>
                    <?php else: ?>

                        <div class="text-center mb-4">
                            <h3 class="fw-bold mb-1">Rate Service</h3>
                            <p class="text-muted small">Share your experience with the community</p>
                        </div>
                        
                        <div class="d-flex align-items-center mb-4 p-3 bg-light rounded-4 border">
                            <img src="<?= !empty($provider['profile_photo']) ? '../uploads/profiles/'.$provider['profile_photo'] : '../assets/img/default-avatar.png' ?>" 
                                 class="rounded-circle me-3 shadow-sm" style="width: 55px; height: 55px; object-fit: cover; border: 2px solid #fff;">
                            <div>
                                <h6 class="fw-bold mb-0"><?= htmlspecialchars($provider['name']) ?></h6>
                                <span class="badge bg-white text-primary border rounded-pill x-small">Service Provider</span>
                            </div>
                        </div>

                        <?php if ($error): ?>
                            <div class="alert alert-warning border-0 small rounded-3 mb-4">
                                <i class="bi bi-exclamation-triangle-fill me-2"></i><?= $error ?>
                            </div>
                        <?php endif; ?>

                        <form method="POST">
                            <div class="mb-4 text-center">
                                <label class="form-label d-block fw-bold small text-muted text-uppercase mb-3">Your Rating</label>
                                <div class="star-rating d-flex flex-row-reverse justify-content-center">
                                    <?php for($i=5; $i>=1; $i--): ?>
                                        <input type="radio" name="rating" value="<?= $i ?>" id="star<?= $i ?>" class="btn-check" <?= (isset($_POST['rating']) && $_POST['rating'] == $i) ? 'checked' : '' ?>>
                                        <label for="star<?= $i ?>" class="star-label mx-1">★</label>
                                    <?php endfor; ?>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold small text-muted">TITLE (OPTIONAL)</label>
                                <input type="text" name="title" class="form-control bg-light border-0 py-2" placeholder="Summary of your experience" value="<?= htmlspecialchars($_POST['title'] ?? '') ?>">
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold small text-muted">DETAILED FEEDBACK</label>
                                <textarea name="comment" id="comment" class="form-control bg-light border-0" rows="4" maxlength="500" required placeholder="How was the communication? Was the service as described?"><?= htmlspecialchars($_POST['comment'] ?? '') ?></textarea>
                                <div class="text-end small text-muted mt-1"><span id="char-count">0</span>/500</div>
                            </div>

                            <button type="submit" class="btn btn-primary btn-lg w-100 rounded-pill fw-bold shadow mt-3" style="background: linear-gradient(135deg, #028090, #00a896); border:none;">
                                Submit Review
                            </button>
                            <a href="../profile/view.php?id=<?= $to_user_id ?>" class="btn btn-link w-100 text-muted small mt-2">Cancel</a>
                        </form>
                    <?php endif; ?>

                </div>
            </div>
        </div>
    </div>
</div>

<style>
    /* Premium Star Rating CSS */
    .star-rating { border: none; }
    .star-label {
        font-size: 2.5rem;
        color: #dee2e6;
        transition: all 0.2s ease-in-out;
        cursor: pointer;
    }
    /* Magic: Highlight all stars to the left of the hovered/checked star */
    .star-rating input:checked ~ .star-label,
    .star-rating .star-label:hover,
    .star-rating .star-label:hover ~ .star-label {
        color: #ffc107;
    }
    .star-rating .star-label:active { transform: scale(0.9); }
    
    .x-small { font-size: 0.7rem; }
</style>

<script>
    const area = document.getElementById('comment');
    const count = document.getElementById('char-count');
    if(area) {
        count.textContent = area.value.length;
        area.addEventListener('input', () => count.textContent = area.value.length);
    }
</script>

<?php include '../includes/footer.php'; ?>