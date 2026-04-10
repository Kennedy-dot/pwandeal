<?php
/**
 * PwanDeal - Edit Profile
 */
session_start();
require_once '../config/database.php';

// 1. Auth Check (Must happen before any HTML output)
if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$error = '';
$success = '';

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
        if (isset($_FILES['profile_photo']) && $_FILES['profile_photo']['error'] !== UPLOAD_ERR_NO_FILE) {
            $file = $_FILES['profile_photo'];
            $allowed = ['image/jpeg','image/jpg','image/png'];
            
            if (!in_array($file['type'], $allowed)) {
                $error = 'Invalid file type. JPG, JPEG, PNG only.';
            } elseif ($file['size'] > 2*1024*1024) {
                $error = 'File too large (max 2MB).';
            } else {
                $upload_dir = '../uploads/profiles/';
                if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);

                $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
                $new_photo = 'user_'.$user_id.'_'.time().'.'.$ext;
                
                if (move_uploaded_file($file['tmp_name'], $upload_dir.$new_photo)) {
                    // Delete old photo if it exists and isn't the default
                    if (!empty($user['profile_photo']) && file_exists($upload_dir.$user['profile_photo'])) {
                        unlink($upload_dir.$user['profile_photo']);
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
                $_SESSION['user_name'] = $name; // Sync session
                // Sync local array for immediate display
                $user['name'] = $name; $user['phone'] = $phone; $user['bio'] = $bio;
                $user['school'] = $school; $user['year_of_study'] = $year_of_study;
                $user['profile_photo'] = $new_photo;
            } else {
                $error = 'Database error. Please try again.';
            }
        }
    }
}

$page_title = 'Edit Profile';
$base_url = '..';
include '../includes/header.php';
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">
            <div class="card border-0 shadow-lg rounded-4 overflow-hidden">
                <div class="card-header text-center text-white py-4" style="background: linear-gradient(135deg, #028090, #1e2761);">
                    <h3 class="fw-bold mb-0">Update Profile</h3>
                    <p class="small opacity-75 mb-0">Customize how others see you on PwanDeal</p>
                </div>
                
                <div class="card-body p-4 p-md-5">
                    <?php if ($error): ?>
                        <div class="alert alert-danger border-0 rounded-3 small">⚠️ <?= htmlspecialchars($error) ?></div>
                    <?php endif; ?>
                    
                    <?php if ($success): ?>
                        <div class="alert alert-success border-0 rounded-3 small">✅ <?= htmlspecialchars($success) ?></div>
                    <?php endif; ?>

                    <form method="POST" enctype="multipart/form-data">
                        <div class="text-center mb-4">
                            <div class="position-relative d-inline-block">
                                <img src="<?= !empty($user['profile_photo']) ? '../uploads/profiles/'.htmlspecialchars($user['profile_photo']) : '../assets/img/default-avatar.png' ?>" 
                                     class="rounded-circle shadow-sm object-fit-cover border border-4 border-white" 
                                     style="width: 130px; height: 130px;" id="preview">
                                <label for="profile_photo" class="position-absolute bottom-0 end-0 bg-primary text-white rounded-circle p-2 shadow-sm" style="cursor: pointer;">
                                    <i class="bi bi-camera-fill"></i>
                                </label>
                            </div>
                            <input type="file" id="profile_photo" name="profile_photo" class="d-none" accept=".jpg,.jpeg,.png" onchange="previewImage(this)">
                            <p class="text-muted small mt-2">Click icon to change photo</p>
                        </div>

                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label fw-bold small text-uppercase">Full Name</label>
                                <input type="text" class="form-control bg-light border-0" name="name" value="<?= htmlspecialchars($user['name']) ?>" required>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-bold small text-uppercase">School/Faculty</label>
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
                                <label class="form-label fw-bold small text-uppercase">Year of Study</label>
                                <select class="form-select bg-light border-0" name="year_of_study">
                                    <option value="">Select Year</option>
                                    <?php for($i=1; $i<=4; $i++): ?>
                                        <option value="<?= $i ?>" <?= $user['year_of_study'] == $i ? 'selected' : '' ?>>Year <?= $i ?></option>
                                    <?php endfor; ?>
                                </select>
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-bold small text-uppercase">Phone Number (M-Pesa Preferred)</label>
                                <input type="tel" class="form-control bg-light border-0" name="phone" value="<?= htmlspecialchars($user['phone'] ?? '') ?>" placeholder="0712345678">
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-bold small text-uppercase">Short Bio</label>
                                <textarea class="form-control bg-light border-0" name="bio" rows="4" maxlength="500" placeholder="E.g. I provide professional hair braiding services near the Main Gate..."><?= htmlspecialchars($user['bio'] ?? '') ?></textarea>
                                <div class="text-end small text-muted"><span id="char-count">0</span>/500</div>
                            </div>
                        </div>

                        <div class="d-grid gap-2 mt-4">
                            <button type="submit" class="btn btn-primary btn-lg rounded-pill fw-bold" style="background-color: #028090; border: none;">Save Profile</button>
                            <a href="view.php" class="btn btn-link text-decoration-none text-muted">Discard Changes</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Real-time character counter
const bioArea = document.querySelector('textarea[name="bio"]');
const charCounter = document.getElementById('char-count');
charCounter.textContent = bioArea.value.length;
bioArea.addEventListener('input', () => charCounter.textContent = bioArea.value.length);

// Image preview before upload
function previewImage(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = (e) => document.getElementById('preview').src = e.target.result;
        reader.readAsDataURL(input.files[0]);
    }
}
</script>

<?php include '../includes/header.php'; ?>