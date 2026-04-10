<?php
/**
 * PwanDeal - Forgot Password
 * Request password reset link
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Redirect if logged in
if (isset($_SESSION['user_id'])) {
    header('Location: /pwandeal/index.php');
    exit();
}

require_once __DIR__ . '/../config/database.php';

$page_title = 'Forgot Password';
$base_url = '/pwandeal';

$error = '';
$success = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');

    // Validate email
    if (empty($email)) {
        $error = 'Email is required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Invalid email format.';
    } else {
        
        // Check if user exists
        $stmt = $conn->prepare('SELECT user_id FROM users WHERE email = ?');
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            // Security: Don't reveal if email exists
            $success = 'If this email is registered, you will receive a password reset link. Please check your email.';
        } else {
            
            $user = $result->fetch_assoc();
            $user_id = $user['user_id'];

            // Generate reset token
            $reset_token = bin2hex(random_bytes(32));
            $expiry = date('Y-m-d H:i:s', strtotime('+1 hour'));

            // Store token in database
            $stmt = $conn->prepare('UPDATE users SET reset_token = ?, reset_token_expiry = ?, reset_requested_at = NOW() WHERE user_id = ?');
            $stmt->bind_param('ssi', $reset_token, $expiry, $user_id);
            
            if ($stmt->execute()) {
                $success = '✅ Password reset link generated!<br><br>';
                $success .= '<strong>⚠️ FOR TESTING ONLY:</strong><br>';
                $success .= 'Your reset token: <code style="background: #f0f0f0; padding: 5px; border-radius: 3px; word-break: break-all;">' . htmlspecialchars($reset_token) . '</code><br><br>';
                $success .= 'Use this link to reset your password:<br>';
                $success .= '<a href="/pwandeal/auth/reset-password.php?token=' . htmlspecialchars($reset_token) . '" style="color: #028090; text-decoration: none; font-weight: bold;">Click here to reset password</a>';
            } else {
                $error = 'Error generating reset token. Please try again.';
            }
        }
    }
}

include __DIR__ . '/../includes/header.php';
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="card shadow-lg border-0 rounded-4">
                <div class="card-header border-0 rounded-top-4 text-center py-4" style="background: linear-gradient(135deg, #028090 0%, #1e2761 100%); color: white;">
                    <h2 class="fw-bold mb-0">🔐 Forgot Password?</h2>
                    <p class="small mb-0 opacity-75">We'll help you get back into your account</p>
                </div>

                <div class="card-body p-4 p-md-5">
                    
                    <?php if ($error): ?>
                        <div class="alert alert-danger border-0 small mb-4 shadow-sm">
                            ❌ <?php echo htmlspecialchars($error); ?>
                        </div>
                    <?php endif; ?>

                    <?php if ($success): ?>
                        <div class="alert alert-success border-0 shadow-sm mb-4">
                            <?php echo $success; ?>
                        </div>
                        <div class="text-center mt-4">
                            <a href="/pwandeal/auth/login.php" class="btn btn-outline-primary rounded-pill px-4">Back to Login</a>
                        </div>
                    <?php else: ?>
                        
                        <p class="text-muted text-center mb-4">
                            Enter your email address and we'll send you a link to reset your password.
                        </p>

                        <form method="POST">
                            <div class="mb-4">
                                <label for="email" class="form-label small fw-bold">📧 Email Address</label>
                                <input type="email" 
                                       id="email" 
                                       name="email" 
                                       class="form-control form-control-lg border-2" 
                                       placeholder="your.email@pwani.ac.ke"
                                       value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>"
                                       required>
                                <div class="form-text small">Enter the email associated with your account</div>
                            </div>

                            <button type="submit" class="btn btn-primary btn-lg w-100 fw-bold shadow-sm rounded-pill" style="background-color: #028090; border: none;">
                                🔗 Send Reset Link
                            </button>
                        </form>

                        <div class="text-center mt-4 pt-2">
                            <p class="small text-muted mb-0">
                                Remember your password? 
                                <a href="/pwandeal/auth/login.php" class="fw-bold text-decoration-none" style="color: #028090;">Login here</a>
                            </p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?><?php
/**
 * PwanDeal - Forgot Password
 * Request password reset link
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Redirect if logged in
if (isset($_SESSION['user_id'])) {
    header('Location: /pwandeal/index.php');
    exit();
}

require_once __DIR__ . '/../config/database.php';

$page_title = 'Forgot Password';
$base_url = '/pwandeal';

$error = '';
$success = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');

    // Validate email
    if (empty($email)) {
        $error = 'Email is required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Invalid email format.';
    } else {
        
        // Check if user exists
        $stmt = $conn->prepare('SELECT user_id FROM users WHERE email = ?');
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            // Security: Don't reveal if email exists
            $success = 'If this email is registered, you will receive a password reset link. Please check your email.';
        } else {
            
            $user = $result->fetch_assoc();
            $user_id = $user['user_id'];

            // Generate reset token
            $reset_token = bin2hex(random_bytes(32));
            $expiry = date('Y-m-d H:i:s', strtotime('+1 hour'));

            // Store token in database
            $stmt = $conn->prepare('UPDATE users SET reset_token = ?, reset_token_expiry = ?, reset_requested_at = NOW() WHERE user_id = ?');
            $stmt->bind_param('ssi', $reset_token, $expiry, $user_id);
            
            if ($stmt->execute()) {
                $success = '✅ Password reset link generated!<br><br>';
                $success .= '<strong>⚠️ FOR TESTING ONLY:</strong><br>';
                $success .= 'Your reset token: <code style="background: #f0f0f0; padding: 5px; border-radius: 3px; word-break: break-all;">' . htmlspecialchars($reset_token) . '</code><br><br>';
                $success .= 'Use this link to reset your password:<br>';
                $success .= '<a href="/pwandeal/auth/reset-password.php?token=' . htmlspecialchars($reset_token) . '" style="color: #028090; text-decoration: none; font-weight: bold;">Click here to reset password</a>';
            } else {
                $error = 'Error generating reset token. Please try again.';
            }
        }
    }
}

include __DIR__ . '/../includes/header.php';
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="card shadow-lg border-0 rounded-4">
                <div class="card-header border-0 rounded-top-4 text-center py-4" style="background: linear-gradient(135deg, #028090 0%, #1e2761 100%); color: white;">
                    <h2 class="fw-bold mb-0">🔐 Forgot Password?</h2>
                    <p class="small mb-0 opacity-75">We'll help you get back into your account</p>
                </div>

                <div class="card-body p-4 p-md-5">
                    
                    <?php if ($error): ?>
                        <div class="alert alert-danger border-0 small mb-4 shadow-sm">
                            ❌ <?php echo htmlspecialchars($error); ?>
                        </div>
                    <?php endif; ?>

                    <?php if ($success): ?>
                        <div class="alert alert-success border-0 shadow-sm mb-4">
                            <?php echo $success; ?>
                        </div>
                        <div class="text-center mt-4">
                            <a href="/pwandeal/auth/login.php" class="btn btn-outline-primary rounded-pill px-4">Back to Login</a>
                        </div>
                    <?php else: ?>
                        
                        <p class="text-muted text-center mb-4">
                            Enter your email address and we'll send you a link to reset your password.
                        </p>

                        <form method="POST">
                            <div class="mb-4">
                                <label for="email" class="form-label small fw-bold">📧 Email Address</label>
                                <input type="email" 
                                       id="email" 
                                       name="email" 
                                       class="form-control form-control-lg border-2" 
                                       placeholder="your.email@pwani.ac.ke"
                                       value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>"
                                       required>
                                <div class="form-text small">Enter the email associated with your account</div>
                            </div>

                            <button type="submit" class="btn btn-primary btn-lg w-100 fw-bold shadow-sm rounded-pill" style="background-color: #028090; border: none;">
                                🔗 Send Reset Link
                            </button>
                        </form>

                        <div class="text-center mt-4 pt-2">
                            <p class="small text-muted mb-0">
                                Remember your password? 
                                <a href="/pwandeal/auth/login.php" class="fw-bold text-decoration-none" style="color: #028090;">Login here</a>
                            </p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>