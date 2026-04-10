<?php
/**
 * PwanDeal - Delete Review
 */
session_start();
require_once '../config/database.php';

// 1. Auth & Input Validation
if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$review_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// 2. Verify ownership & Fetch Provider ID
// We must get to_user_id BEFORE we delete the row
$stmt = $conn->prepare("SELECT to_user_id FROM reviews WHERE review_id = ? AND from_user_id = ?");
$stmt->bind_param("ii", $review_id, $user_id);
$stmt->execute();
$review = $stmt->get_result()->fetch_assoc();

if (!$review) {
    // Redirect back if unauthorized or missing
    header("Location: ../listings/view.php?error=notfound");
    exit();
}

$provider_id = $review['to_user_id'];

// 3. Atomic Delete & Recalculate
$conn->begin_transaction();

try {
    // Delete the specific review
    $del = $conn->prepare("DELETE FROM reviews WHERE review_id = ?");
    $del->bind_param("i", $review_id);
    $del->execute();

    /** * Recalculate stats
     * IFNULL is crucial here: if it was the user's only review, 
     * AVG() returns NULL, but we want 0.
     */
    $upd = $conn->prepare("
        UPDATE users SET 
            average_rating = IFNULL((SELECT AVG(rating) FROM reviews WHERE to_user_id = ?), 0),
            total_reviews = (SELECT COUNT(*) FROM reviews WHERE to_user_id = ?)
        WHERE user_id = ?
    ");
    $upd->bind_param("iii", $provider_id, $provider_id, $provider_id);
    $upd->execute();

    $conn->commit();
    
    // Redirect back to the provider's profile with a success toast
    header("Location: ../profile/view.php?id=$provider_id&msg=deleted");
} catch (Exception $e) {
    $conn->rollback();
    // Log error in a real production environment: error_log($e->getMessage());
    header("Location: ../profile/view.php?id=$provider_id&error=db_error");
}
exit();