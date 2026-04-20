<?php
/**
 * PwanDeal - Delete Review
 */
session_start();
require_once '../config/database.php';

// 1. Auth Check
if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$review_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// 2. CSRF & Security Check
// Pro Tip: In a real app, use a CSRF token. For now, we ensure ownership.
$stmt = $conn->prepare("SELECT to_user_id FROM reviews WHERE review_id = ? AND from_user_id = ?");
$stmt->bind_param("ii", $review_id, $user_id);
$stmt->execute();
$review = $stmt->get_result()->fetch_assoc();

if (!$review) {
    // If review doesn't exist or doesn't belong to the user
    header("Location: ../index.php?error=unauthorized");
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
     * Using IFNULL ensures that if the count is 0, the average is 0, not NULL.
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
    
    // Redirect back to the provider's profile with a specific success flag
    header("Location: ../profile/view.php?id=" . $provider_id . "&success=review_deleted");
} catch (Exception $e) {
    $conn->rollback();
    header("Location: ../profile/view.php?id=" . $provider_id . "&error=delete_failed");
}
exit();