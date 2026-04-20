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

// 2. Ownership Verification & Retrieve Images for Cleanup
// We join listing_images to get all files associated with this listing
$stmt = $conn->prepare("
    SELECT l.listing_id, i.image_url 
    FROM listings l 
    LEFT JOIN listing_images i ON l.listing_id = i.listing_id 
    WHERE l.listing_id = ? AND l.user_id = ?
");
$stmt->bind_param("ii", $listing_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

$images_to_delete = [];
$listing_exists = false;

while ($row = $result->fetch_assoc()) {
    $listing_exists = true;
    if (!empty($row['image_url'])) {
        $images_to_delete[] = $row['image_url'];
    }
}

if (!$listing_exists) {
    header('Location: my-listings.php?error=NotFound');
    exit();
}

// 3. Perform Deletion with Transaction
$conn->begin_transaction();

try {
    // A. Delete from listing_images first (Foreign Key hygiene)
    $del_img = $conn->prepare("DELETE FROM listing_images WHERE listing_id = ?");
    $del_img->bind_param("i", $listing_id);
    $del_img->execute();

    // B. Delete the listing record
    $del_stmt = $conn->prepare("DELETE FROM listings WHERE listing_id = ? AND user_id = ?");
    $del_stmt->bind_param("ii", $listing_id, $user_id);
    $del_stmt->execute();

    // C. Update the user's total_listings count
    $upd_stmt = $conn->prepare("UPDATE users SET total_listings = GREATEST(0, total_listings - 1) WHERE user_id = ?");
    $upd_stmt->bind_param("i", $user_id);
    $upd_stmt->execute();

    // D. Commit DB changes
    $conn->commit();

    // E. File Cleanup
    foreach ($images_to_delete as $filename) {
        $file_path = __DIR__ . "/../uploads/services/" . $filename;
        if (file_exists($file_path)) {
            @unlink($file_path);
        }
    }

    header('Location: my-listings.php?msg=Deleted');
    exit();

} catch (Exception $e) {
    $conn->rollback();
    header('Location: my-listings.php?msg=DeleteError');
    exit();
}