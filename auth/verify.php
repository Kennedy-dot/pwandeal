<?php
/**
 * PwanDeal - Email Verification
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Use __DIR__ for reliable pathing
require_once __DIR__ . '/../config/database.php';

$page_title = 'Verify Email';
$base_url = '/pwandeal';

$error = '';
$success = '';

// Pre-fill email from session if available
$session_email = $_SESSION['verify_email'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $code = trim($_POST['code'] ?? '');

    if (empty($email) || empty($code)) {
        $error = 'Please enter both your email and the 6-digit code.';
    } else {
        // Find user with matching email and code
        $stmt = $conn->prepare('SELECT user_id, is_verified FROM users WHERE email = ? AND verification_code = ? LIMIT 1');
        $stmt->bind_param('ss', $email, $code);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            $error = 'The code is incorrect or the email is not registered.';
        } else {
            $user = $result->fetch_assoc();
            
            if ($user['is_verified']) {
                $success = 'Your account is already verified! You can log in anytime.';
            } else {
                // Update status and wipe the code
                $update = $conn->prepare('UPDATE users SET is_verified = 1, verification_code = NULL WHERE user_id = ?');
                $update->bind_param('i', $user['user_id']);
                
                if ($update->execute()) {
                    $success = '✅ Verification successful! Your PwanDeal account is now active.';
                    unset($_SESSION['verify_email']); // Clean up session
                } else {
                    $error = 'Database error. Please try again later.';
                }
            }
        }
    }
}

include __DIR__ . '/../includes/header.php';
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="card shadow-lg border-0 rounded-4">
                <div class="card-header border-0 rounded-top-4 text-center py-4" style="background: linear-gradient(135deg, #028090 0%, #1e2761 100%); color: white;">
                    <h2 class="fw-bold mb-0">🔒 Verify Account</h2>
                    <p class="small mb-0 opacity-75">Check your registration for the 6-digit code</p>
                </div>

                <div class="card-body p-4 p-md-5">
                    <?php if ($error): ?>
                        <div class="alert alert-danger border-0 small mb-4">❌ <?= $error ?></div>
                    <?php endif; ?>
                    
                    <?php if ($success): ?>
                        <div class="text-center">
                            <div class="alert alert-success border-0 mb-4"><?= $success ?></div>
                            <a href="/pwandeal/auth/login.php" class="btn btn-primary w-100 py-2 fw-bold" style="background-color: #028090; border: none;">
                                Go to Login
                            </a>
                        </div>
                    <?php else: ?>
                        <form method="POST">
                            <div class="mb-3">
                                <label class="form-label small fw-bold">Email Address</label>
                                <input type="email" name="email" class="form-control" 
                                       placeholder="name@pwani.ac.ke" 
                                       value="<?= htmlspecialchars($email ?? $session_email) ?>" required>
                            </div>

                            <div class="mb-4">
                                <label class="form-label small fw-bold">6-Digit Code</label>
                                <input type="text" name="code" class="form-control form-control-lg text-center fw-bold letter-spacing-2" 
                                       placeholder="000000" maxlength="6" pattern="\d{6}" required>
                                <div class="form-text text-center mt-2 small">Enter the numeric code provided after registration.</div>
                            </div>

                            <button type="submit" class="btn btn-primary w-100 py-2 fw-bold" style="background-color: #028090; border: none;">
                                Verify Now
                            </button>
                        </form>
                    <?php endif; ?>

                    <div class="text-center mt-4 pt-2">
                        <a href="/pwandeal/auth/register.php" class="small text-muted text-decoration-none">← Back to Registration</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .letter-spacing-2 { letter-spacing: 0.5rem; font-size: 1.5rem; }
    .form-control:focus { border-color: #028090; box-shadow: 0 0 0 0.25rem rgba(2, 128, 144, 0.25); }
</style>

<?php include __DIR__ . '/../includes/footer.php'; ?>