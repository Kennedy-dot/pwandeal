<?php
/**
 * PwanDeal - Reset Password
 * Reset password using token from email
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Redirect if logged in
if (isset($_SESSION['user_id'])) {
    header('Location: /pwandeal/index.php');
    exit();
}

// Use __DIR__ for reliable pathing
require_once __DIR__ . '/../config/database.php';

$page_title = 'Reset Password';
$base_url = '/pwandeal';

$error = '';
$success = '';
$token = trim($_GET['token'] ?? '');
$token_valid = false;
$user_id = 0;

// Validate token
if (empty($token)) {
    $error = 'No reset token provided.';
} else {
    // Check if token exists and is not expired
    $stmt = $conn->prepare('SELECT user_id FROM users WHERE reset_token = ? AND reset_token_expiry > NOW()');
    $stmt->bind_param('s', $token);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        $error = 'Invalid or expired reset token. Please request a new one.';
    } else {
        $user = $result->fetch_assoc();
        $user_id = $user['user_id'];
        $token_valid = true;
    }
}

// Process password reset
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $token_valid) {
    $token = trim($_POST['token'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $password_confirm = trim($_POST['password_confirm'] ?? '');

    // Validate inputs
    if (empty($password)) {
        $error = 'Password is required.';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters.';
    } elseif ($password !== $password_confirm) {
        $error = 'Passwords do not match.';
    } else {
        // Hash password
        $password_hash = password_hash($password, PASSWORD_BCRYPT);

        // Update password and clear reset token
        $stmt = $conn->prepare('UPDATE users SET password = ?, reset_token = NULL, reset_token_expiry = NULL WHERE user_id = ?');
        $stmt->bind_param('si', $password_hash, $user_id);

        if ($stmt->execute()) {
            $success = '✅ Your password has been reset successfully! You can now login with your new password.';
            $token_valid = false; // Hide form after success
        } else {
            $error = 'Error resetting password. Please try again.';
        }
    }
}

include __DIR__ . '/../includes/header.php';
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow-lg border-0 rounded-4">
                <div class="card-header border-0 rounded-top-4" style="background: linear-gradient(135deg, #028090 0%, #1e2761 100%); color: white; padding: 30px; text-align: center;">
                    <h2 class="fw-bold mb-0">🔑 Reset Your Password</h2>
                </div>

                <div class="card-body p-4 p-md-5">
                    
                    <?php if ($error): ?>
                        <div class="alert alert-danger border-0 small shadow-sm mb-4" role="alert">
                            ❌ <?php echo htmlspecialchars($error); ?>
                        </div>
                        <div class="text-center">
                            <a href="/pwandeal/auth/forgot-password.php" class="btn btn-primary rounded-pill px-4" style="background-color: #028090; border: none;">
                                Request New Reset Link
                            </a>
                        </div>
                    <?php elseif ($success): ?>
                        <div class="alert alert-success border-0 shadow-sm mb-4" role="alert">
                            <?php echo htmlspecialchars($success); ?>
                        </div>
                        <div class="text-center">
                            <a href="/pwandeal/auth/login.php" class="btn btn-primary rounded-pill px-4" style="background-color: #028090; border: none;">
                                🔐 Login with New Password
                            </a>
                        </div>
                    <?php elseif ($token_valid): ?>
                        
                        <p class="text-muted text-center mb-4">
                            Enter your new password below.
                        </p>

                        <form method="POST">
                            <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">

                            <div class="mb-3">
                                <label for="password" class="form-label small fw-bold">🔐 New Password</label>
                                <input type="password" 
                                       id="password" 
                                       name="password" 
                                       class="form-control form-control-lg border-2" 
                                       placeholder="At least 6 characters"
                                       required>
                                <div class="form-text small">Minimum 6 characters</div>
                            </div>

                            <div class="mb-4">
                                <label for="password_confirm" class="form-label small fw-bold">🔐 Confirm Password</label>
                                <input type="password" 
                                       id="password_confirm" 
                                       name="password_confirm" 
                                       class="form-control form-control-lg border-2" 
                                       placeholder="Re-enter your password"
                                       required
                                       onkeyup="checkPasswordMatch()">
                                <div class="form-text small" id="password-match-msg"></div>
                            </div>

                            <button type="submit" class="btn btn-primary btn-lg w-100 fw-bold shadow-sm rounded-pill" style="background-color: #028090; border: none;">
                                ✓ Reset Password
                            </button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function checkPasswordMatch() {
    const password = document.getElementById('password').value;
    const confirm = document.getElementById('password_confirm').value;
    const msg = document.getElementById('password-match-msg');

    if (password && confirm) {
        if (password === confirm) {
            msg.innerHTML = '<span class="text-success">✅ Passwords match</span>';
        } else {
            msg.innerHTML = '<span class="text-danger">❌ Passwords do not match</span>';
        }
    } else {
        msg.innerHTML = '';
    }
}
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>