<?php
/**
 * PwanDeal - User Login
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Redirect if already logged in - Using absolute path
if (isset($_SESSION['user_id'])) {
    header('Location: /pwandeal/index.php');
    exit();
}

// Use __DIR__ to ensure the path is correct from the auth folder
require_once __DIR__ . '/../config/database.php';

$page_title = 'Login';
$base_url = '/pwandeal'; 
$error = '';

// Generate CSRF Token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF check
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die('Security breach detected (CSRF).');
    }

    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $error = 'Email and password are required.';
    } else {
        $stmt = $conn->prepare('SELECT * FROM users WHERE email = ? LIMIT 1');
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            $error = 'No account found with that email.';
        } else {
            $user = $result->fetch_assoc();

            // 1. Check Lockout Status
            $lockout_time = 600; // 10 minutes
            if ($user['login_attempts'] >= 5) {
                $time_passed = time() - strtotime($user['last_failed_login']);
                if ($time_passed < $lockout_time) {
                    $wait = ceil(($lockout_time - $time_passed) / 60);
                    $error = "Too many failed attempts. Please wait $wait minute(s).";
                }
            }

            // 2. Check Suspension
            if (!$error && $user['is_suspended']) {
                $error = 'Your account has been suspended. Please contact the administrator.';
            }

            // 3. Verify Password
            if (!$error) {
                if (password_verify($password, $user['password'])) {
                    
                    // Check Verification
                    if (!$user['is_verified']) {
                        $_SESSION['verify_email'] = $user['email'];
                        $error = 'Your email is not verified. <a href="verify.php" class="alert-link">Verify now</a>';
                    } else {
                        // Success Logic
                        session_regenerate_id(true);
                        
                        // Reset attempts
                        $reset = $conn->prepare('UPDATE users SET login_attempts = 0, last_login = NOW() WHERE user_id = ?');
                        $reset->bind_param('i', $user['user_id']);
                        $reset->execute();

                        $_SESSION['user_id'] = $user['user_id'];
                        $_SESSION['user_name'] = $user['name'];
                        $_SESSION['profile_photo'] = $user['profile_photo'];

                        header('Location: /pwandeal/index.php');
                        exit();
                    }
                } else {
                    // Fail Logic - Increment attempts
                    $inc = $conn->prepare('UPDATE users SET login_attempts = login_attempts + 1, last_failed_login = NOW() WHERE user_id = ?');
                    $inc->bind_param('i', $user['user_id']);
                    $inc->execute();
                    
                    $error = 'Incorrect password. Please try again.';
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
                    <h2 class="fw-bold mb-0">🔐 Login</h2>
                    <p class="small mb-0 opacity-75">Access the PwanDeal Marketplace</p>
                </div>

                <div class="card-body p-4 p-md-5">
                    <?php if ($error): ?>
                        <div class="alert alert-danger border-0 small mb-4 shadow-sm">
                            <?= $error ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST">
                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">

                        <div class="mb-3">
                            <label class="form-label small fw-bold">University Email</label>
                            <input type="email" name="email" class="form-control form-control-lg border-2" 
                                   placeholder="name@pwani.ac.ke" 
                                   value="<?= htmlspecialchars($email ?? '') ?>" required>
                        </div>

                        <div class="mb-4">
                            <div class="d-flex justify-content-between">
                                <label class="form-label small fw-bold">Password</label>
                                <a href="/pwandeal/auth/forgot-password.php" class="small text-decoration-none" style="color: #028090;">Forgot?</a>
                            </div>
                            <div class="input-group">
                                <input type="password" name="password" id="passwordField" 
                                       class="form-control form-control-lg border-2 border-end-0" 
                                       placeholder="••••••••" required>
                                <span class="input-group-text bg-white border-2 border-start-0" 
                                      onclick="togglePassword()" style="cursor: pointer;">
                                    👁️
                                </span>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary btn-lg w-100 fw-bold shadow-sm" style="background-color: #028090; border: none;">
                            Sign In
                        </button>
                    </form>

                    <div class="text-center mt-4 pt-2">
                        <p class="small text-muted mb-0">Don't have an account? 
                            <a href="/pwandeal/auth/register.php" class="fw-bold text-decoration-none" style="color: #1e2761;">Register here</a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function togglePassword() {
    const field = document.getElementById('passwordField');
    field.type = field.type === 'password' ? 'text' : 'password';
}
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>