<?php
/**
 * PwanDeal - Add Review Form & Handler (Fixed Context Version)
 */
session_start();
require_once '../config/database.php';

// 1. Auth Gate
if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit();
}

$from_user_id = (int)$_SESSION['user_id'];

// 2. Fetch parameters carefully (supports both GET on load, and POST on submit)
$to_user_id = isset($_REQUEST['to_user_id']) ? (int)$_REQUEST['to_user_id'] : 0;

// FIXED: Allow listing_id to be null if a user is rating directly from a profile
$listing_id = isset($_REQUEST['listing_id']) && (int)$_REQUEST['listing_id'] > 0 ? (int)$_REQUEST['listing_id'] : null;

// CRITICAL FIX: If recipient is missing, we cannot continue. 
// If listing_id is missing but to_user_id exists, we allow it as a direct user profile review!
if ($to_user_id === 0) {
    header("Location: ../dashboard/index.php?error=missing_review_context");
    exit();
}

// 3. Handle Form Processing
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $rating = isset($_POST['rating']) ? (int)$_POST['rating'] : 0;
    $title = trim($_POST['title'] ?? '');
    $comment = trim($_POST['comment'] ?? '');

    if ($rating >= 1 && $rating <= 5 && !empty($comment)) {
        $conn->begin_transaction();
        try {
            // A. Insert review record (Using standard parameterized null injection for listing_id)
            $ins = $conn->prepare("
                INSERT INTO reviews (from_user_id, to_user_id, listing_id, rating, title, comment, created_at) 
                VALUES (?, ?, ?, ?, ?, ?, NOW())
            ");
            $ins->bind_param("iiiiss", $from_user_id, $to_user_id, $listing_id, $rating, $title, $comment);
            $ins->execute();

            // B. Recalculate recipient aggregate values
            $upd = $conn->prepare("
                UPDATE users SET 
                    average_rating = IFNULL((SELECT AVG(rating) FROM reviews WHERE to_user_id = ?), 0),
                    total_reviews = (SELECT COUNT(*) FROM reviews WHERE to_user_id = ?)
                WHERE user_id = ?
            ");
            $upd->bind_param("iii", $to_user_id, $to_user_id, $to_user_id);
            $upd->execute();

            $conn->commit();
            header("Location: ../profile/view.php?id=" . $to_user_id . "&success=review_added");
            exit();
        } catch (Exception $e) {
            $conn->rollback();
            error_log("Review insertion broken: " . $e->getMessage());
            $error_msg = "Could not save your rating right now.";
        }
    } else {
        $error_msg = "Please pick a star rating and fill out a short review description.";
    }
}

$page_title = 'Write a Review';
include '../includes/header.php';
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card border-0 shadow-sm rounded-4 p-4 bg-white">
                <h4 class="fw-bold mb-3 text-dark">Leave a Review</h4>
                
                <?php if (isset($error_msg)): ?>
                    <div class="alert alert-danger rounded-3 small py-2"><?= htmlspecialchars($error_msg) ?></div>
                <?php endif; ?>

                <form action="add.php" method="POST">
                    <!-- Keep hidden inputs to preserve context down to submission path -->
                    <input type="hidden" name="to_user_id" value="<?= $to_user_id ?>">
                    <input type="hidden" name="listing_id" value="<?= $listing_id ?>">

                    <div class="mb-3">
                        <label class="form-label small fw-bold text-muted">Your Rating</label>
                        <select name="rating" class="form-select rounded-3" required>
                            <option value="">-- Choose Star Count --</option>
                            <option value="5">5 Stars - Outstanding</option>
                            <option value="4">4 Stars - Great</option>
                            <option value="3">3 Stars - Good / Standard</option>
                            <option value="2">2 Stars - Disappointed</option>
                            <option value="1">1 Star - Horrible experience</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold text-muted">Review Title</label>
                        <input type="text" name="title" class="form-control rounded-3" placeholder="e.g., Quick transaction, highly recommend!">
                    </div>

                    <div class="mb-4">
                        <label class="form-label small fw-bold text-muted">Detailed Comment</label>
                        <textarea name="comment" rows="4" class="form-control rounded-3" placeholder="Share specific details about item condition, delivery speed, or general communication..." required></textarea>
                    </div>

                    <button type="submit" class="btn btn-primary w-100 rounded-pill py-2.5 fw-bold" style="background-color: #028090; border: none;">
                        Submit Feedback
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>