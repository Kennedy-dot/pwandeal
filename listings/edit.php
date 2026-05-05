<?php
/**
 * PwanDeal - Edit Service Listing
 */
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit();
}

require_once __DIR__ . '/../config/database.php';

$user_id = $_SESSION['user_id'];
$listing_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($listing_id === 0) {
    header('Location: my-listings.php');
    exit();
}

// 1. Ownership & Existing Data Check
$stmt = $conn->prepare('SELECT * FROM listings WHERE listing_id = ? AND user_id = ?');
$stmt->bind_param('ii', $listing_id, $user_id);
$stmt->execute();
$listing = $stmt->get_result()->fetch_assoc();

if (!$listing) {
    die("Access denied or listing not found.");
}

// CSRF Token Generation
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Fetch categories for the dropdown
$categories = $conn->query("SELECT * FROM categories ORDER BY name ASC");

$error = '';
$success = '';

// 2. Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF Check
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die('Security token expired. Please refresh.');
    }

    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $category_id = intval($_POST['category_id'] ?? 0);
    $price = floatval($_POST['price'] ?? 0);
    $status = $_POST['status'] ?? 'active';
    $image_path = $listing['image_url']; // Default to current image

    // Validation
    if (strlen($title) < 5) {
        $error = 'Title must be at least 5 characters.';
    } elseif ($category_id == 0) {
        $error = 'Please select a valid category.';
    } else {
        // Handle Optional Image Change
        if (isset($_FILES['service_image']) && $_FILES['service_image']['error'] === 0) {
            $allowed = ['jpg', 'jpeg', 'png', 'webp'];
            $file_info = pathinfo($_FILES['service_image']['name']);
            $ext = strtolower($file_info['extension']);

            if (in_array($ext, $allowed)) {
                $new_name = "service_" . time() . "_" . bin2hex(random_bytes(4)) . "." . $ext;
                $upload_dir = "../assets/uploads/services/";
                
                if (move_uploaded_file($_FILES['service_image']['tmp_name'], $upload_dir . $new_name)) {
                    // Delete old image if it exists and isn't a default
                    if (!empty($listing['image_url']) && $listing['image_url'] !== 'default-service.jpg') {
                        if (file_exists($upload_dir . $listing['image_url'])) {
                            @unlink($upload_dir . $listing['image_url']);
                        }
                    }
                    $image_path = $new_name;
                }
            } else {
                $error = 'Invalid image format (JPG, PNG, WEBP only).';
            }
        }

        if (!$error) {
            $update_stmt = $conn->prepare("
                UPDATE listings 
                SET title = ?, description = ?, category_id = ?, price = ?, image_url = ?, status = ?, updated_at = NOW() 
                WHERE listing_id = ? AND user_id = ?
            ");
            $update_stmt->bind_param('ssidssii', $title, $description, $category_id, $price, $image_path, $status, $listing_id, $user_id);

            if ($update_stmt->execute()) {
                $success = 'Changes saved successfully!';
                // Update local array to show new data in form
                $listing['title'] = $title;
                $listing['description'] = $description;
                $listing['category_id'] = $category_id;
                $listing['price'] = $price;
                $listing['status'] = $status;
                $listing['image_url'] = $image_path;
            } else {
                $error = 'Failed to update database.';
            }
        }
    }
}

$page_title = 'Edit Service';
include '../includes/header.php';
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm border-0 rounded-4 overflow-hidden">
                <div class="card-header border-0 p-4 text-center text-white" 
                     style="background: linear-gradient(135deg, #028090 0%, #1e2761 100%);">
                    <h3 class="fw-bold mb-0">✏️ Update Service</h3>
                </div>

                <div class="card-body p-4 p-md-5">
                    <?php if ($error): ?>
                        <div class="alert alert-danger border-0 mb-4"><?= $error ?></div>
                    <?php endif; ?>

                    <?php if ($success): ?>
                        <div class="alert alert-success border-0 mb-4">✨ <?= $success ?></div>
                    <?php endif; ?>

                    <form method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">

                        <div class="row g-4">
                            <div class="col-12 text-center mb-2">
                                <label class="form-label d-block fw-bold text-muted small uppercase">Service Cover Image</label>
                                <div class="mx-auto bg-light rounded-4 d-flex align-items-center justify-content-center overflow-hidden border shadow-sm" 
                                     style="width: 240px; height: 160px; cursor: pointer;"
                                     onclick="document.getElementById('imageUpload').click();">
                                    <?php 
                                        $display_img = (!empty($listing['image_url']) && $listing['image_url'] !== 'default-service.jpg') 
                                            ? "../assets/uploads/services/" . $listing['image_url'] 
                                            : "../assets/img/service-placeholder.jpg";
                                    ?>
                                    <img id="preview" src="<?= $display_img ?>" style="width:100%; height:100%; object-fit:cover;">
                                </div>
                                <input type="file" name="service_image" id="imageUpload" hidden accept="image/*" onchange="previewImage(this, 'preview');">
                                <small class="text-primary d-block mt-2" style="cursor:pointer;" onclick="document.getElementById('imageUpload').click();">
                                    <i class="bi bi-camera me-1"></i> Change Photo
                                </small>
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-bold">Service Title</label>
                                <input type="text" name="title" class="form-control form-control-lg bg-light border-0" 
                                       required value="<?= htmlspecialchars($listing['title']) ?>">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-bold">Category</label>
                                <select name="category_id" class="form-select form-select-lg bg-light border-0" required>
                                    <?php while($cat = $categories->fetch_assoc()): ?>
                                        <option value="<?= $cat['category_id'] ?>" <?= $listing['category_id'] == $cat['category_id'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($cat['name']) ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-bold">Price (KSh)</label>
                                <input type="number" name="price" class="form-control form-control-lg bg-light border-0" 
                                       required value="<?= $listing['price'] ?>">
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-bold">Listing Status</label>
                                <select name="status" class="form-select bg-light border-0">
                                    <option value="active" <?= $listing['status'] == 'active' ? 'selected' : '' ?>>Active (Visible to all)</option>
                                    <option value="inactive" <?= $listing['status'] == 'inactive' ? 'selected' : '' ?>>Inactive (Private Draft)</option>
                                    <option value="sold" <?= $listing['status'] == 'sold' ? 'selected' : '' ?>>Sold / Completed</option>
                                </select>
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-bold">Detailed Description</label>
                                <textarea name="description" id="description" class="form-control bg-light border-0" rows="5" 
                                          maxlength="1000" required><?= htmlspecialchars($listing['description']) ?></textarea>
                                <div class="text-end small text-muted mt-1">
                                    <span id="char-count"><?= strlen($listing['description']) ?></span>/1000
                                </div>
                            </div>

                            <div class="col-12 mt-4 d-flex gap-2">
                                <button type="submit" class="btn btn-primary btn-lg flex-grow-1 fw-bold shadow-sm py-3 rounded-3">
                                    Save Changes
                                </button>
                                <a href="my-listings.php" class="btn btn-light btn-lg border py-3 px-4 rounded-3">Cancel</a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Instant Image Preview
function previewImage(input, previewId) {
    const preview = document.getElementById(previewId);
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            preview.src = e.target.result;
        }
        reader.readAsDataURL(input.files[0]);
    }
}

// Character Counter
document.getElementById('description').addEventListener('input', function() {
    document.getElementById('char-count').textContent = this.value.length;
});
</script>

<?php include '../includes/footer.php'; ?>