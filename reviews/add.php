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
$to_user_id = isset($_GET['to']) ? (int)$_GET['to'] : 0;
$listing_id = isset($_GET['listing']) ? (int)$_GET['listing'] : null;

// Prevent self-reviewing
if ($to_user_id === 0 || $to_user_id === $user_id) {
    header('Location: ../listings/view.php');
    exit();
}

// Fetch Provider Info
$stmt = $conn->prepare('SELECT name, profile_photo, average_rating FROM users WHERE user_id = ?');
$stmt->bind_param('i', $to_user_id);
$stmt->execute();
$provider = $stmt->get_result()->fetch_assoc();

if (!$provider) {
    die("User not found.");
}

$error = '';
$success = '';

// Check if already reviewed for this listing
if ($listing_id) {
    $check = $conn->prepare('SELECT review_id FROM reviews WHERE from_user_id = ? AND to_user_id = ? AND listing_id = ?');
    $check->bind_param('iii', $user_id, $to_user_id, $listing_id);
    $check->execute();
    if ($check->get_result()->num_rows > 0) {
        $error = 'You have already reviewed this service.';
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

            // 2. Update Provider Stats (Atomic recalculation)
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
            $error = 'Something went wrong. Please try again.';
        }
    }
}

$page_title = 'Review ' . $provider['name'];
$base_url = '..';
include '../includes/header.php';
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="card border-0 shadow-lg rounded-4 overflow-hidden">
                <div class="card-body p-4 p-md-5">
                    
                    <?php if ($success): ?>
                        <div class="text-center py-4">
                            <div class="display-1 text-success mb-3">⭐</div>
                            <h3 class="fw-bold">Feedback Sent!</h3>
                            <p class="text-muted">Thanks for helping the PwanDeal community grow.</p>
                            <a href="../listings/view.php" class="btn btn-primary rounded-pill px-4 mt-3">Back to Marketplace</a>
                        </div>
                    <?php else: ?>

                        <h3 class="fw-bold mb-4">How was the service?</h3>
                        
                        <div class="d-flex align-items-center mb-4 p-3 bg-light rounded-3">
                            <img src="<?= $provider['profile_photo'] ? '../assets/uploads/profiles/'.$provider['profile_photo'] : '../assets/img/default-avatar.png' ?>" 
                                 class="rounded-circle me-3" style="width: 50px; height: 50px; object-fit: cover;">
                            <div>
                                <h6 class="fw-bold mb-0"><?= htmlspecialchars($provider['name']) ?></h6>
                                <small class="text-muted">Service Provider</small>
                            </div>
                        </div>

                        <?php if ($error): ?>
                            <div class="alert alert-warning border-0 small mb-4"><?= $error ?></div>
                        <?php endif; ?>

                        <form method="POST">
                            <div class="mb-4">
                                <label class="form-label d-block fw-bold small text-muted text-uppercase">Your Rating</label>
                                <div class="star-rating d-flex flex-row-reverse justify-content-center gap-2">
                                    <?php for($i=5; $i>=1; $i--): ?>
                                        <input type="radio" name="rating" value="<?= $i ?>" id="star<?= $i ?>" class="btn-check" required>
                                        <label for="star<?= $i ?>" class="display-5 star-label">★</label>
                                    <?php endfor; ?>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold">Review Title</label>
                                <input type="text" name="title" class="form-control form-control-lg border-0 bg-light" placeholder="e.g., Excellent work!">
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold">Your Experience</label>
                                <textarea name="comment" id="comment" class="form-control border-0 bg-light" rows="4" maxlength="500" required placeholder="What was great? What could be better?"></textarea>
                                <div class="text-end small text-muted mt-1"><span id="char-count">0</span>/500</div>
                            </div>

                            <button type="submit" class="btn btn-primary btn-lg w-100 rounded-pill fw-bold shadow-sm" style="background-color: #028090; border:none;">
                                Post Public Review
                            </button>
                        </form>
                    <?php endif; ?>

                </div>
            </div>
        </div>
    </div>
</div>

<style>
    /* Premium Star Rating CSS */
    .star-rating {
        border: none;
    }
    .star-label {
        color: #e9ecef;
        transition: all 0.2s ease-in-out;
        cursor: pointer;
    }
    .star-rating input:checked ~ .star-label,
    .star-rating .star-label:hover,
    .star-rating .star-label:hover ~ .star-label {
        color: #ffc107;
        transform: scale(1.1);
    }
</style>

<script>
    const area = document.getElementById('comment');
    const count = document.getElementById('char-count');
    area.addEventListener('input', () => count.textContent = area.value.length);
</script>

<?php include '../includes/footer.php'; ?>