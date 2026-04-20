<?php
/**
 * PwanDeal - Post New Service
 * Refined for Schema Consistency, Secure Transactions, and Polished UI
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit();
}

require_once __DIR__ . '/../config/database.php';

$user_id = $_SESSION['user_id'];
$error = '';
$success = '';
$base_url = '/pwandeal';

// CSRF Token Generation
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Fetch categories for the dropdown
$categories = $conn->query("SELECT * FROM categories ORDER BY name ASC");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF Check
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die('Security validation failed.');
    }

    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $category_id = intval($_POST['category_id'] ?? 0);
    $price = floatval($_POST['price'] ?? 0);

    // Validation
    if (strlen($title) < 5 || strlen($title) > 100) {
        $error = 'Title must be between 5 and 100 characters.';
    } elseif ($price < 0) {
        $error = 'Please set a valid price (can be 0 for free/negotiable).';
    } elseif ($category_id === 0) {
        $error = 'Please select a valid category.';
    } elseif (empty($description)) {
        $error = 'Description is required.';
    } else {
        
        $conn->begin_transaction();

        try {
            // 1. Insert Listing
            $expires_at = date('Y-m-d H:i:s', strtotime('+60 days'));
            // Note: We use the schema that supports listing_images table
            $stmt = $conn->prepare('INSERT INTO listings (user_id, title, description, category_id, price, status, expires_at) VALUES (?, ?, ?, ?, ?, "active", ?)');
            $stmt->bind_param('issids', $user_id, $title, $description, $category_id, $price, $expires_at);
            $stmt->execute();
            $listing_id = $conn->insert_id;

            // 2. Handle Image Upload
            if (isset($_FILES['service_image']) && $_FILES['service_image']['error'] === 0) {
                $allowed = ['jpg', 'jpeg', 'png', 'webp'];
                $ext = strtolower(pathinfo($_FILES['service_image']['name'], PATHINFO_EXTENSION));

                if (in_array($ext, $allowed)) {
                    $upload_dir = __DIR__ . "/../uploads/services/";
                    if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);

                    $image_name = "listing_" . $listing_id . "_" . time() . "." . $ext;
                    if (move_uploaded_file($_FILES['service_image']['tmp_name'], $upload_dir . $image_name)) {
                        // Insert into listing_images table
                        $img_stmt = $conn->prepare("INSERT INTO listing_images (listing_id, image_url, is_primary) VALUES (?, ?, 1)");
                        $img_stmt->bind_param('is', $listing_id, $image_name);
                        $img_stmt->execute();
                    }
                } else {
                    throw new Exception("Invalid image format.");
                }
            }

            // 3. Update User Stats
            $update_stats = $conn->prepare("UPDATE users SET total_listings = total_listings + 1 WHERE user_id = ?");
            $update_stats->bind_param('i', $user_id);
            $update_stats->execute();

            $conn->commit();
            $success = 'Service published successfully!';
            header("refresh:2;url=view.php?id=$listing_id");

        } catch (Exception $e) {
            $conn->rollback();
            $error = $e->getMessage() ?: 'Something went wrong. Please try again.';
        }
    }
}

$page_title = 'Post New Service';
include __DIR__ . '/../includes/header.php';
?>

<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <nav aria-label="breadcrumb" class="mb-4">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= $base_url ?>/index.php">Home</a></li>
                    <li class="breadcrumb-item"><a href="view.php">Listings</a></li>
                    <li class="breadcrumb-item active">Post Service</li>
                </ol>
            </nav>

            <div class="card shadow-sm border-0 rounded-4 overflow-hidden">
                <div class="card-header border-0 p-4 text-center text-white" 
                     style="background: linear-gradient(135deg, #028090 0%, #1e2761 100%);">
                    <h3 class="fw-bold mb-1">📤 Create a Listing</h3>
                    <p class="mb-0 opacity-75 small">Reach more students at Pwani University</p>
                </div>

                <div class="card-body p-4 p-md-5">
                    <?php if ($error): ?>
                        <div class="alert alert-danger border-0 shadow-sm mb-4">
                            <i class="bi bi-x-circle me-2"></i><?= $error ?>
                        </div>
                    <?php endif; ?>

                    <?php if ($success): ?>
                        <div class="alert alert-success border-0 shadow-sm mb-4">
                            <i class="bi bi-check-circle me-2"></i><?= $success ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST" enctype="multipart/form-data" id="postForm" class="needs-validation">
                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">

                        <div class="row g-4">
                            <div class="col-12 text-center">
                                <label class="form-label d-block fw-bold text-muted small text-uppercase">Listing Image</label>
                                <div class="upload-placeholder mx-auto rounded-4 d-flex align-items-center justify-content-center overflow-hidden" 
                                     style="width: 100%; max-width: 500px; height: 250px; border: 2px dashed #dee2e6; background: #f8f9fa; cursor: pointer;"
                                     onclick="document.getElementById('imageUpload').click();">
                                    <img id="preview" src="#" alt="Preview" style="display:none; width:100%; height:100%; object-fit:cover;">
                                    <div id="placeholderText" class="text-center p-3">
                                        <i class="bi bi-cloud-arrow-up fs-1 text-primary"></i>
                                        <p class="text-muted small mt-2">Click to upload your service/product photo</p>
                                        <p class="text-muted" style="font-size: 0.7rem;">PNG, JPG or WEBP (Max 2MB)</p>
                                    </div>
                                </div>
                                <input type="file" name="service_image" id="imageUpload" hidden accept="image/*" onchange="previewImage(this);">
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-bold">Title</label>
                                <input type="text" name="title" class="form-control form-control-lg bg-light" 
                                       placeholder="e.g. Professional Laptop Repair & OS Installation" required 
                                       value="<?= htmlspecialchars($_POST['title'] ?? '') ?>">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-bold">Category</label>
                                <select name="category_id" class="form-select form-select-lg bg-light" required>
                                    <option value="0" disabled selected>Select category</option>
                                    <?php while($cat = $categories->fetch_assoc()): ?>
                                        <option value="<?= $cat['category_id'] ?>" <?= ($_POST['category_id'] ?? '') == $cat['category_id'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($cat['name']) ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-bold">Price</label>
                                <div class="input-group input-group-lg">
                                    <span class="input-group-text bg-light border-end-0">KSh</span>
                                    <input type="number" name="price" class="form-control bg-light border-start-0" 
                                           placeholder="0.00" step="0.01" required 
                                           value="<?= htmlspecialchars($_POST['price'] ?? '') ?>">
                                </div>
                                <small class="text-muted">Enter 0 if the price is negotiable.</small>
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-bold">Detailed Description</label>
                                <textarea name="description" id="description" class="form-control bg-light" rows="6" 
                                          placeholder="Include details like location on campus, availability, or requirements..." 
                                          maxlength="1500" required><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>
                                <div class="text-end mt-1">
                                    <span id="char-count" class="badge bg-secondary opacity-75">0 / 1500</span>
                                </div>
                            </div>

                            <div class="col-12 mt-4">
                                <button type="submit" class="btn btn-primary btn-lg w-100 fw-bold rounded-pill py-3 shadow-sm" id="submitBtn">
                                    Publish Listing
                                </button>
                                <p class="text-center mt-3 small text-muted">
                                    By publishing, you agree to PwanDeal's <a href="../terms.php">Terms of Service</a>.
                                </p>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .form-control:focus, .form-select:focus {
        border-color: #028090;
        box-shadow: 0 0 0 0.25rem rgba(2, 128, 144, 0.1);
    }
    .upload-placeholder:hover {
        border-color: #028090 !important;
        background: #f0f9fa !important;
    }
</style>

<script>
function previewImage(input) {
    const preview = document.getElementById('preview');
    const placeholder = document.getElementById('placeholderText');
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            preview.src = e.target.result;
            preview.style.display = 'block';
            placeholder.style.display = 'none';
        }
        reader.readAsDataURL(input.files[0]);
    }
}

// Character Counter & Double-Submit Prevention
const form = document.getElementById('postForm');
const btn = document.getElementById('submitBtn');
const descArea = document.getElementById('description');
const charDisplay = document.getElementById('char-count');

descArea.addEventListener('input', function() {
    const len = this.value.length;
    charDisplay.textContent = `${len} / 1500`;
    if (len > 1400) {
        charDisplay.className = 'badge bg-danger';
    } else {
        charDisplay.className = 'badge bg-secondary opacity-75';
    }
});

form.addEventListener('submit', () => {
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Publishing...';
    btn.disabled = true;
});
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>