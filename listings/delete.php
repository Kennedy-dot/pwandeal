<?php
/**
 * PwanDeal - Delete Service Listing
 */
session_start();
require_once __DIR__ . '/../config/database.php';

// 1. Authentication & ID Validation
if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$listing_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($listing_id === 0) {
    header('Location: my-listings.php');
    exit();
}

// 2. Ownership Verification & Data Retrieval
$stmt = $conn->prepare("SELECT image_url FROM listings WHERE listing_id = ? AND user_id = ?");
$stmt->bind_param("ii", $listing_id, $user_id);
$stmt->execute();
$listing = $stmt->get_result()->fetch_assoc();

if (!$listing) {
    // Fail silently or redirect to prevent ID probing
    header('Location: my-listings.php?error=NotFound');
    exit();
}

// 3. Perform Deletion with Transaction
$conn->begin_transaction();

try {
    // A. Delete the record
    $del_stmt = $conn->prepare("DELETE FROM listings WHERE listing_id = ? AND user_id = ?");
    $del_stmt->bind_param("ii", $listing_id, $user_id);
    $del_stmt->execute();

    // B. Update the user's total_listings count
    // Using simple subtraction is faster than a subquery COUNT on large tables
    $upd_stmt = $conn->prepare("UPDATE users SET total_listings = GREATEST(0, total_listings - 1) WHERE user_id = ?");
    $upd_stmt->bind_param("i", $user_id);
    $upd_stmt->execute();

    // C. Commit DB changes before touching files
    $conn->commit();

    // D. Cleanup the image file
    // Only delete if it's not the default and not used by another listing (optional safety)
    if (!empty($listing['image_url']) && $listing['image_url'] !== 'default-service.jpg') {
        $file_path = __DIR__ . "/../assets/uploads/services/" . $listing['image_url'];
        
        if (file_exists($file_path)) {
            // Check if any other listing uses this image before unlinking
            $check_img = $conn->prepare("SELECT listing_id FROM listings WHERE image_url = ? LIMIT 1");
            $check_img->bind_param("s", $listing['image_url']);
            $check_img->execute();
            if ($check_img->get_result()->num_rows === 0) {
                @unlink($file_path);
            }
        }
    }

    header('Location: my-listings.php?msg=Deleted');
    exit();

} catch (Exception $e) {
    $conn->rollback();
    // Log error internally in a real production app: error_log($e->getMessage());
    header('Location: my-listings.php?msg=DeleteError');
    exit();
}
