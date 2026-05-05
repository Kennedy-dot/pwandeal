<?php
/**
 * PwanDeal - Edit Profile
 */
session_start();
require_once __DIR__ . '/../config/database.php';

// 1. Auth Check
if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$error = '';
$success = '';

// Security: Generate CSRF token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// 2. Fetch current user data
$stmt = $conn->prepare('SELECT * FROM users WHERE user_id = ?');
$stmt->bind_param('i', $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

if (!$user) {
    die('User not found.');
}

// 3. Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF Check
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die('Security token mismatch.');
    }

    $name = trim($_POST['name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $bio = trim($_POST['bio'] ?? '');
    $school = $_POST['school'] ?? '';
    $year_of_study = $_POST['year_of_study'] ?? '';

    // Validation
    if (empty($name)) {
        $error = 'Name is required.';
    } elseif (strlen($bio) > 500) {
        $error = 'Bio must be less than 500 characters.';
    } else {
        // Image Processing
        $new_photo = $user['profile_photo'];
        
        if (isset($_FILES['profile_photo']) && $_FILES['profile_photo']['error'] === UPLOAD_ERR_OK) {
            $file = $_FILES['profile_photo'];
            $allowed = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'];
            
            // Check actual MIME type
            $finfo = new finfo(FILEINFO_MIME_TYPE);
            $file_type = $finfo->file($file['tmp_name']);
            
            if (!in_array($file_type, $allowed)) {
                $error = 'Invalid file type. JPG, PNG, or WebP only.';
            } elseif ($file['size'] > 2*1024*1024) {
                $error = 'File too large (max 2MB).';
            } else {
                $upload_dir = '../uploads/profiles/';
                if (!file_exists($upload_dir)) mkdir($upload_dir, 0755, true);

                $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
                $new_photo = 'user_'.$user_id.'_'.time().'.'.$ext;
                
                if (move_uploaded_file($file['tmp_name'], $upload_dir.$new_photo)) {
                    // Cleanup: Delete old photo if it's not the default
                    if (!empty($user['profile_photo']) && $user['profile_photo'] !== 'default-avatar.png') {
                        $old_path = $upload_dir . $user['profile_photo'];
                        if (file_exists($old_path)) @unlink($old_path);
                    }
                } else {
                    $error = 'Failed to upload photo.';
                }
            }
        }

        if (!$error) {
            $update = $conn->prepare('UPDATE users SET name=?, phone=?, bio=?, school=?, year_of_study=?, profile_photo=? WHERE user_id=?');
            $update->bind_param('ssssssi', $name, $phone, $bio, $school, $year_of_study, $new_photo, $user_id);
            
            if ($update->execute()) {
                $success = 'Profile updated successfully!';
                $_SESSION['user_name'] = $name; 
                // Refresh local data
                $user = array_merge($user, [
                    'name' => $name, 'phone' => $phone, 'bio' => $bio,
                    'school' => $school, 'year_of_study' => $year_of_study, 'profile_photo' => $new_photo
                ]);
            } else {
                $error = 'Update failed. Please try again.';
            }
        }
    }
}

$page_title = 'Edit Profile';
include '../includes/header.php';
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">
            <div class="card border-0 shadow-lg rounded-4 overflow-hidden">
                <div class="card-header text-center text-white py-4" style="background: linear-gradient(135deg, #028090, #1e2761);">
                    <h3 class="fw-bold mb-0">Update Profile</h3>
                    <p class="small opacity-75 mb-0">Customize your PwanDeal student identity</p>
                </div>
                
                <div class="card-body p-4 p-md-5">
                    <?php if ($error): ?>
                        <div class="alert alert-danger border-0 rounded-3 small py-2 animate__animated animate__shakeX">
                             <i class="bi bi-exclamation-circle me-2"></i><?= htmlspecialchars($error) ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($success): ?>
                        <div class="alert alert-success border-0 rounded-3 small py-2 animate__animated animate__fadeIn">
                            <i class="bi bi-check-circle me-2"></i><?= htmlspecialchars($success) ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">

                        <div class="text-center mb-5">
                            <div class="position-relative d-inline-block">
                                <?php 
                                    $photo_path = (!empty($user['profile_photo']) && file_exists('../uploads/profiles/'.$user['profile_photo'])) 
                                                  ? '../uploads/profiles/'.$user['profile_photo'] 
                                                  : '../assets/img/default-avatar.png';
                                ?>
                                <img src="<?= $photo_path ?>" 
                                     class="rounded-circle shadow-sm object-fit-cover border border-4 border-white" 
                                     style="width: 130px; height: 130px;" id="preview">
                                <label for="profile_photo" class="position-absolute bottom-0 end-0 bg-primary text-white rounded-circle d-flex align-items-center justify-content-center shadow-sm" style="width: 38px; height: 38px; cursor: pointer; border: 3px solid #fff;">
                                    <i class="bi bi-camera-fill small"></i>
                                </label>
                            </div>
                            <input type="file" id="profile_photo" name="profile_photo" class="d-none" accept="image/*" onchange="previewImage(this)">
                            <p class="text-muted small mt-2">Recommended: Square image (max 2MB)</p>
                        </div>

                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label fw-bold small text-muted text-uppercase">Full Name</label>
                                <input type="text" class="form-control bg-light border-0 py-2" name="name" value="<?= htmlspecialchars($user['name']) ?>" required>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-bold small text-muted text-uppercase">School/Faculty</label>
                                <select class="form-select bg-light border-0" name="school">
                                    <option value="">Select School</option>
                                    <?php 
                                    $schools = [
                                        "School of Education (SED)",
                                        "School of Pure & Applied Sciences (SPAS)",
                                        "School of Humanities & Social Sciences (SHSS)",
                                        "School of Business & Economics (SBE)",
                                        "School of Health & Human Sciences (SHHS)",
                                        "School of Environmental & Earth Sciences (SEES)",
                                        "School of Ag. Sciences & Agribusiness (SASA)"
                                    ];
                                    foreach($schools as $s): ?>
                                        <option value="<?= $s ?>" <?= $user['school'] === $s ? 'selected' : '' ?>><?= $s ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-bold small text-muted text-uppercase">Year of Study</label>
                                <select class="form-select bg-light border-0" name="year_of_study">
                                    <option value="">Select Year</option>
                                    <?php for($i=1; $i<=4; $i++): ?>
                                        <option value="<?= $i ?>" <?= $user['year_of_study'] == $i ? 'selected' : '' ?>>Year <?= $i ?></option>
                                    <?php endfor; ?>
                                </select>
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-bold small text-muted text-uppercase">WhatsApp / Phone</label>
                                <input type="tel" class="form-control bg-light border-0" name="phone" value="<?= htmlspecialchars($user['phone'] ?? '') ?>" placeholder="0712345678">
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-bold small text-muted text-uppercase">About You (Bio)</label>
                                <textarea class="form-control bg-light border-0" name="bio" id="bio_text" rows="4" maxlength="500" placeholder="Tell other students about what you do..."><?= htmlspecialchars($user['bio'] ?? '') ?></textarea>
                                <div class="text-end mt-1" style="font-size: 0.75rem; color: #aaa;">
                                    <span id="char-count">0</span>/500 characters
                                </div>
                            </div>
                        </div>

                        <div class="d-grid gap-2 mt-4">
                            <button type="submit" class="btn btn-primary btn-lg rounded-pill fw-bold shadow-sm" style="background-color: #028090; border: none;">
                                Save Profile Changes
                            </button>
                            <a href="dashboard.php" class="btn btn-link text-decoration-none text-muted small">Go back to Dashboard</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Image preview
function previewImage(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = (e) => document.getElementById('preview').src = e.target.result;
        reader.readAsDataURL(input.files[0]);
    }
}

// Character counter initialization
const bioArea = document.getElementById('bio_text');
const charCounter = document.getElementById('char-count');

if(bioArea) {
    const updateCount = () => charCounter.textContent = bioArea.value.length;
    updateCount();
    bioArea.addEventListener('input', updateCount);
}
</script>

<?php include '../includes/footer.php'; ?>