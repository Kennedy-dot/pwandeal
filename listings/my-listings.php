<?php
/**
 * PwanDeal - Edit Existing Service
 */
session_start();
require_once __DIR__ . '/../config/database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$listing_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// 1. Fetch & Ownership Verification (Including Primary Image)
$stmt = $conn->prepare("
    SELECT l.*, i.image_url 
    FROM listings l 
    LEFT JOIN listing_images i ON l.listing_id = i.listing_id AND i.is_primary = 1 
    WHERE l.listing_id = ? AND l.user_id = ?
");
$stmt->bind_param("ii", $listing_id, $user_id);
$stmt->execute();
$listing = $stmt->get_result()->fetch_assoc();

if (!$listing) {
    header('Location: my-listings.php?error=unauthorized');
    exit();
}

$categories = $conn->query("SELECT * FROM categories ORDER BY name ASC");
$errors = [];
$success = false;

// 2. Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $price = (float)$_POST['price'];
    $category_id = (int)$_POST['category_id'];
    $status = $_POST['status']; 
    
    if (empty($title)) $errors[] = "Title is required.";
    if ($price <= 0) $errors[] = "Please enter a valid price.";

    if (empty($errors)) {
        // Start Transaction to ensure both table updates succeed
        $conn->begin_transaction();

        try {
            // Update Basic Info
            $update_sql = "UPDATE listings SET title = ?, description = ?, price = ?, 
                           category_id = ?, status = ?, updated_at = NOW() 
                           WHERE listing_id = ? AND user_id = ?";
            $update_stmt = $conn->prepare($update_sql);
            $update_stmt->bind_param("ssdisii", $title, $description, $price, $category_id, $status, $listing_id, $user_id);
            $update_stmt->execute();

            // Handle Image Upload if provided
            if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
                $allowed = ['jpg', 'jpeg', 'png', 'webp'];
                $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
                
                if (in_array($ext, $allowed)) {
                    $new_name = "service_" . time() . "_" . bin2hex(random_bytes(4)) . "." . $ext;
                    $upload_path = "../uploads/services/" . $new_name;
                    
                    if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_path)) {
                        // 1. Mark existing images as NOT primary
                        $conn->query("UPDATE listing_images SET is_primary = 0 WHERE listing_id = $listing_id");
                        
                        // 2. Insert new primary image
                        $img_stmt = $conn->prepare("INSERT INTO listing_images (listing_id, image_url, is_primary) VALUES (?, ?, 1)");
                        $img_stmt->bind_param("is", $listing_id, $new_name);
                        $img_stmt->execute();

                        // 3. Optional: Delete old physical file (only if you want to save space)
                        if ($listing['image_url'] && file_exists("../uploads/services/" . $listing['image_url'])) {
                            @unlink("../uploads/services/" . $listing['image_url']);
                        }
                        $listing['image_url'] = $new_name; // Update local var for the preview
                    }
                } else {
                    throw new Exception("Invalid image format.");
                }
            }

            $conn->commit();
            $success = true;
            $listing['title'] = $title; // Update local var for UI
        } catch (Exception $e) {
            $conn->rollback();
            $errors[] = $e->getMessage();
        }
    }
}

$page_title = "Edit Service";
include '../includes/header.php';
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h3 class="fw-bold mb-0">Update Service</h3>
                        <a href="my-listings.php" class="btn btn-light btn-sm rounded-pill px-3">Back to List</a>
                    </div>

                    <?php if ($success): ?>
                        <div class="alert alert-success border-0 shadow-sm mb-4">
                            ✨ <strong>Great!</strong> Your service has been updated. 
                            <a href="detail.php?id=<?= $listing_id ?>" class="alert-link ms-2">View listing →</a>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($errors)): ?>
                        <div class="alert alert-danger border-0 mb-4">
                            <ul class="mb-0"><?php foreach($errors as $e) echo "<li>$e</li>"; ?></ul>
                        </div>
                    <?php endif; ?>

                    <form action="" method="POST" enctype="multipart/form-data">
                        <div class="row g-4">
                            <div class="col-12">
                                <label class="form-label small fw-bold text-muted text-uppercase">Cover Image</label>
                                <div class="d-flex align-items-center gap-3 p-3 bg-light rounded-3 border">
                                    <?php $preview = !empty($listing['image_url']) ? "../uploads/services/".$listing['image_url'] : "../assets/img/service-placeholder.jpg"; ?>
                                    <img src="<?= $preview ?>" class="rounded-3 shadow-sm" style="width: 80px; height: 80px; object-fit: cover;">
                                    <div class="flex-grow-1">
                                        <input type="file" name="image" class="form-control form-control-sm">
                                        <div class="form-text small">Uploading a new image replaces the current one.</div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-bold">Title</label>
                                <input type="text" name="title" class="form-control" value="<?= htmlspecialchars($listing['title']) ?>" required>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-bold">Category</label>
                                <select name="category_id" class="form-select">
                                    <?php while($cat = $categories->fetch_assoc()): ?>
                                        <option value="<?= $cat['category_id'] ?>" <?= ($cat['category_id'] == $listing['category_id']) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($cat['name']) ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-bold">Price (KSh)</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-white border-end-0">KSh</span>
                                    <input type="number" name="price" class="form-control border-start-0" value="<?= $listing['price'] ?>" required>
                                </div>
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-bold">Description</label>
                                <textarea name="description" class="form-control" rows="4" required><?= htmlspecialchars($listing['description']) ?></textarea>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-bold">Status</label>
                                <select name="status" class="form-select">
                                    <option value="active" <?= $listing['status'] == 'active' ? 'selected' : '' ?>>Active (Visible)</option>
                                    <option value="sold" <?= $listing['status'] == 'sold' ? 'selected' : '' ?>>Mark as Sold</option>
                                    <option value="archived" <?= $listing['status'] == 'archived' ? 'selected' : '' ?>>Archived (Hidden)</option>
                                </select>
                            </div>

                            <div class="col-12 pt-3">
                                <button type="submit" class="btn btn-primary w-100 py-2 fw-bold rounded-pill">
                                    Save Changes
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>