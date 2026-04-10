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

// 1. Fetch & Ownership Verification
$stmt = $conn->prepare("SELECT * FROM listings WHERE listing_id = ? AND user_id = ?");
$stmt->bind_param("ii", $listing_id, $user_id);
$stmt->execute();
$listing = $stmt->get_result()->fetch_assoc();

if (!$listing) {
    header('Location: my-listings.php?error=unauthorized');
    exit();
}

// 2. Setup Data for View
$categories = $conn->query("SELECT * FROM categories ORDER BY name ASC");
$errors = [];
$success = false;

// 3. Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $price = (float)$_POST['price'];
    $category_id = (int)$_POST['category_id'];
    $status = $_POST['status']; 
    
    if (empty($title)) $errors[] = "Title is required.";
    if ($price <= 0) $errors[] = "Please enter a valid price.";

    // Image Processing Logic
    $image_name = $listing['image_url']; 
    $old_image_to_delete = null;

    if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'webp'];
        $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        
        if (in_array($ext, $allowed)) {
            $new_name = "service_" . time() . "_" . bin2hex(random_bytes(4)) . "." . $ext;
            $upload_path = "../assets/uploads/services/" . $new_name;
            
            if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_path)) {
                // Set old image for deletion later
                if ($listing['image_url'] !== 'default-service.jpg') {
                    $old_image_to_delete = "../assets/uploads/services/" . $listing['image_url'];
                }
                $image_name = $new_name;
            }
        } else {
            $errors[] = "Invalid image format (JPG, PNG, WebP only).";
        }
    }

    if (empty($errors)) {
        $update_sql = "UPDATE listings SET title = ?, description = ?, price = ?, 
                       category_id = ?, status = ?, image_url = ?, updated_at = NOW() 
                       WHERE listing_id = ? AND user_id = ?";
        
        $update_stmt = $conn->prepare($update_sql);
        $update_stmt->bind_param("ssdissii", $title, $description, $price, $category_id, $status, $image_name, $listing_id, $user_id);
        
        if ($update_stmt->execute()) {
            $success = true;
            // CLEANUP: Database is updated, now safe to remove old file
            if ($old_image_to_delete && file_exists($old_image_to_delete)) {
                @unlink($old_image_to_delete);
            }
            // Sync local variable for display
            $listing['title'] = $title;
            $listing['image_url'] = $image_name;
        } else {
            $errors[] = "Database update failed. Please try again.";
        }
    }
}

$page_title = "Edit " . $listing['title'];
$base_url = "..";
include '../includes/header.php';
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <nav aria-label="breadcrumb" class="mb-4">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="my-listings.php" class="text-decoration-none text-muted">My Services</a></li>
                    <li class="breadcrumb-item active">Edit Listing</li>
                </ol>
            </nav>

            <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                <div class="card-header bg-white p-4 border-0">
                    <h3 class="fw-bold mb-0 text-dark">Update Service Info</h3>
                </div>

                <div class="card-body p-4">
                    <?php if ($success): ?>
                        <div class="alert alert-success border-0 shadow-sm mb-4">
                            ✨ <strong>Success!</strong> Your service has been updated. 
                            <a href="details.php?id=<?= $listing_id ?>" class="alert-link ms-2">View Changes</a>
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
                                <label class="form-label d-block small fw-bold text-uppercase text-muted">Service Image</label>
                                <div class="d-flex align-items-center gap-4 p-3 bg-light rounded-3 border">
                                    <img src="<?= "../assets/uploads/services/" . $listing['image_url'] ?>" 
                                         class="rounded-3 shadow-sm" style="width: 100px; height: 100px; object-fit: cover;">
                                    <div>
                                        <input type="file" name="image" class="form-control form-control-sm mb-2">
                                        <p class="small text-muted mb-0">Recommended size: 800x600px. Leave empty to keep current image.</p>
                                    </div>
                                </div>
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-bold">Title</label>
                                <input type="text" name="title" class="form-control form-control-lg bg-light border-0" 
                                       value="<?= htmlspecialchars($listing['title']) ?>" required>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-bold">Category</label>
                                <select name="category_id" class="form-select bg-light border-0">
                                    <?php while($cat = $categories->fetch_assoc()): ?>
                                        <option value="<?= $cat['category_id'] ?>" <?= ($cat['category_id'] == $listing['category_id']) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($cat['name']) ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-bold">Price (KSh)</label>
                                <input type="number" name="price" class="form-control bg-light border-0" 
                                       value="<?= $listing['price'] ?>" required>
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-bold">Description</label>
                                <textarea name="description" class="form-control bg-light border-0" 
                                          rows="5" placeholder="What exactly are you offering?"><?= htmlspecialchars($listing['description']) ?></textarea>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-bold">Listing Status</label>
                                <select name="status" class="form-select bg-light border-0 text-capitalize">
                                    <?php $statuses = ['active', 'sold', 'archived'];
                                    foreach($statuses as $s): ?>
                                        <option value="<?= $s ?>" <?= ($listing['status'] == $s) ? 'selected' : '' ?>><?= $s ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="col-12 pt-3 border-top">
                                <button type="submit" class="btn btn-primary px-5 fw-bold rounded-pill">
                                    Save Updates
                                </button>
                                <a href="my-listings.php" class="btn btn-link text-muted ms-3">Cancel</a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>