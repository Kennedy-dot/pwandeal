<?php
/**
 * PwanDeal - Edit Review
 */
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit();
}

require_once '../config/database.php';

$user_id = $_SESSION['user_id'];
$review_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($review_id === 0) {
    header('Location: ../index.php');
    exit();
}

// 1. Get review and verify ownership
$stmt = $conn->prepare('
    SELECT r.*, u.name as provider_name, u.profile_photo as provider_photo 
    FROM reviews r 
    JOIN users u ON r.to_user_id = u.user_id 
    WHERE r.review_id = ? AND r.from_user_id = ?
');
$stmt->bind_param('ii', $review_id, $user_id);
$stmt->execute();
$review = $stmt->get_result()->fetch_assoc();

if (!$review) {
    die("<div class='container py-5 text-center'><h3>Review not found or unauthorized.</h3><a href='../index.php'>Back Home</a></div>");
}

$error = '';
$success = '';

// 2. Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $rating = (int)($_POST['rating'] ?? 0);
    $title = trim($_POST['title'] ?? '');
    $comment = trim($_POST['comment'] ?? '');
    
    if ($rating < 1 || $rating > 5) {
        $error = 'Please select a rating.';
    } elseif (empty($title)) {
        $error = 'Review title is required.';
    } elseif (empty($comment)) {
        $error = 'Review comment is required.';
    } else {
        $conn->begin_transaction();
        try {
            // Update the review
            $update = $conn->prepare('UPDATE reviews SET rating = ?, title = ?, comment = ?, updated_at = CURRENT_TIMESTAMP WHERE review_id = ?');
            $update->bind_param('issi', $rating, $title, $comment, $review_id);
            $update->execute();

            // Recalculate provider stats (Total count + New Average)
            $calc = $conn->prepare('
                UPDATE users 
                SET average_rating = (SELECT AVG(rating) FROM reviews WHERE to_user_id = ?),
                    total_reviews = (SELECT COUNT(*) FROM reviews WHERE to_user_id = ?)
                WHERE user_id = ?
            ');
            $calc->bind_param('iii', $review['to_user_id'], $review['to_user_id'], $review['to_user_id']);
            $calc->execute();

            $conn->commit();
            $success = 'Your feedback has been updated.';
            
            // Update local variables for display
            $review['rating'] = $rating;
            $review['title'] = $title;
            $review['comment'] = $comment;
        } catch (Exception $e) {
            $conn->rollback();
            $error = 'Something went wrong. Please try again.';
        }
    }
}

$page_title = 'Edit Review';
$base_url = '..';
include '../includes/header.php';
?>

<div class="container py-5">
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb small">
            <li class="breadcrumb-item"><a href="../index.php" class="text-decoration-none text-muted">Home</a></li>
            <li class="breadcrumb-item"><a href="my_reviews.php" class="text-decoration-none text-muted">Reviews</a></li>
            <li class="breadcrumb-item active text-dark fw-bold">Edit Feedback</li>
        </ol>
    </nav>

    <div class="row justify-content-center">
        <div class="col-md-7 col-lg-6">
            <div class="card border-0 shadow-lg rounded-4 overflow-hidden">
                <div class="card-header border-0 p-4 text-center text-white" style="background: linear-gradient(135deg, #1e2761 0%, #028090 100%);">
                    <h3 class="fw-bold mb-0">Update Review</h3>
                </div>

                <div class="card-body p-4 p-md-5">
                    
                    <div class="d-flex align-items-center mb-4 p-3 bg-light rounded-4">
                        <img src="<?= !empty($review['provider_photo']) ? '../uploads/profiles/'.$review['provider_photo'] : '../assets/img/default-avatar.png' ?>" 
                             class="rounded-circle me-3 border border-white shadow-sm" style="width: 50px; height: 50px; object-fit: cover;">
                        <div>
                            <small class="text-muted d-block x-small text-uppercase fw-bold">Reviewing Service from</small>
                            <h6 class="fw-bold mb-0"><?= htmlspecialchars($review['provider_name']) ?></h6>
                        </div>
                    </div>

                    <?php if ($success): ?>
                        <div class="alert alert-success border-0 rounded-4 py-3 mb-4 text-center">
                            <i class="bi bi-check-circle-fill me-2"></i> <?= $success ?>
                            <div class="mt-2">
                                <a href="my_reviews.php" class="btn btn-sm btn-success rounded-pill px-3">View All Reviews</a>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if ($error): ?>
                        <div class="alert alert-danger border-0 rounded-4 mb-4">
                            <i class="bi bi-exclamation-triangle-fill me-2"></i> <?= $error ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST">
                        <div class="mb-4 text-center">
                            <label class="form-label d-block fw-bold text-muted small text-uppercase mb-3">Adjust Your Stars</label>
                            <div class="star-rating d-flex flex-row-reverse justify-content-center">
                                <?php for($i=5; $i>=1; $i--): ?>
                                    <input type="radio" name="rating" value="<?= $i ?>" id="star<?= $i ?>" class="btn-check" <?= $review['rating'] == $i ? 'checked' : '' ?> required>
                                    <label for="star<?= $i ?>" class="star-label mx-1">★</label>
                                <?php endfor; ?>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold text-dark small">REVIEW TITLE</label>
                            <input type="text" name="title" class="form-control form-control-lg bg-light border-0 rounded-3 shadow-none" 
                                   value="<?= htmlspecialchars($review['title']) ?>" placeholder="Sum up your experience" required>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-bold text-dark small">YOUR FEEDBACK</label>
                            <textarea name="comment" id="comment" class="form-control bg-light border-0 rounded-3 shadow-none" 
                                      rows="5" maxlength="500" required placeholder="What changed?"><?= htmlspecialchars($review['comment']) ?></textarea>
                            <div class="text-end mt-1">
                                <small class="text-muted"><span id="char-count"><?= strlen($review['comment']) ?></span>/500</small>
                            </div>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary btn-lg rounded-pill fw-bold py-3 shadow-sm" style="background-color: #028090; border: none;">
                                Update Feedback
                            </button>
                            <a href="../profile/view.php?id=<?= $review['to_user_id'] ?>" class="btn btn-link text-decoration-none text-muted small">
                                Cancel and return to profile
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .star-label { 
        font-size: 2.8rem;
        color: #dee2e6; 
        cursor: pointer; 
        transition: transform 0.2s, color 0.2s; 
    }
    .star-rating input:checked ~ .star-label,
    .star-rating .star-label:hover,
    .star-rating .star-label:hover ~ .star-label {
        color: #ffc107;
    }
    .star-label:active { transform: scale(0.9); }
    .x-small { font-size: 0.65rem; }
</style>

<script>
    const textarea = document.getElementById('comment');
    const counter = document.getElementById('char-count');
    if(textarea) {
        textarea.addEventListener('input', () => {
            counter.textContent = textarea.value.length;
        });
    }
</script>

<?php include '../includes/footer.php'; ?>