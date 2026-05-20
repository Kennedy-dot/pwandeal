<?php
/**
 * PwanDeal - Delete Review
 * 
 * NOTE: This action should be requested via a POST form to prevent
 * accidental deletion via web crawlers or pre-fetching browsers.
 */
session_start();
require_once '../config/database.php';

// 1. Auth & Input Validation
if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Enforce POST method for destructive actions
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../dashboard/index.php?error=invalid_method");
    exit();
}

$review_id = isset($_POST['id']) ? (int)$_POST['id'] : 0;

// 2. Verify ownership & Fetch Provider ID
// We must get to_user_id BEFORE we delete the row to know whose stats to update
$stmt = $conn->prepare("SELECT to_user_id FROM reviews WHERE review_id = ? AND from_user_id = ?");
$stmt->bind_param("ii", $review_id, $user_id);
$stmt->execute();
$review = $stmt->get_result()->fetch_assoc();

if (!$review) {
    // If review doesn't exist or doesn't belong to the user, redirect with error
    header("Location: ../dashboard/index.php?error=unauthorized");
    exit();
}

$provider_id = (int)$review['to_user_id'];

// 3. Atomic Delete & Recalculate
$conn->begin_transaction();

try {
    // A. Delete the specific review
    $del = $conn->prepare("DELETE FROM reviews WHERE review_id = ?");
    $del->bind_param("i", $review_id);
    $del->execute();

    /** 
     * B. Recalculate provider stats
     * IFNULL is crucial: if it was the user's only review, 
     * AVG() returns NULL, but we want it to show 0 on their profile.
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
    
    // Redirect back to the provider's profile with a success flag
    header("Location: ../profile/view.php?id=" . $provider_id . "&success=review_deleted");
} catch (Exception $e) {
    $conn->rollback();
    // Log error for system monitoring
    error_log("Review deletion failed: " . $e->getMessage());
    header("Location: ../profile/view.php?id=" . $provider_id . "&error=delete_failed");
}
exit();