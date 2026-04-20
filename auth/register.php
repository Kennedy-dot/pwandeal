<?php
/**
 * PwanDeal - User Registration
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Use __DIR__ for reliable pathing
require_once __DIR__ . '/../config/database.php';

$page_title = 'Register';
$base_url = '/pwandeal';
$error = '';
$success = '';

// CSRF Token Generation
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF check
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die('Invalid security token.');
    }

    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $school = trim($_POST['school'] ?? '');
    $year = trim($_POST['year'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // Validation
    if (empty($name) || empty($email) || empty($school) || empty($year) || empty($password)) {
        $error = 'All fields are required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL) || !str_ends_with($email, '@pwani.ac.ke')) {
        $error = 'Please use a valid @pwani.ac.ke email address.';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters.';
    } elseif ($password !== $confirm_password) {
        $error = 'Passwords do not match.';
    } else {
        // Check for existing email
        $stmt = $conn->prepare('SELECT user_id FROM users WHERE email = ?');
        $stmt->bind_param('s', $email);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            $error = 'This email is already registered.';
        } else {
            // Success Path
            $verification_code = random_int(100000, 999999);
            $hashed_password = password_hash($password, PASSWORD_BCRYPT);
            
            $stmt = $conn->prepare('INSERT INTO users (name, email, password, school, year, verification_code, is_verified) VALUES (?, ?, ?, ?, ?, ?, 0)');
            $stmt->bind_param('sssssi', $name, $email, $hashed_password, $school, $year, $verification_code);
            
            if ($stmt->execute()) {
                $_SESSION['verify_email'] = $email; 
                $success = 'Account created! Your verification code is: <strong>' . $verification_code . '</strong>';
            } else {
                $error = 'Something went wrong. Please try again.';
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
                    <h2 class="fw-bold mb-0">📝 Join PwanDeal</h2>
                    <p class="small mb-0 opacity-75">Exclusively for Pwani University Students</p>
                </div>

                <div class="card-body p-4 p-md-5">
                    <?php if ($error): ?>
                        <div class="alert alert-danger border-0 small">❌ <?= $error ?></div>
                    <?php endif; ?>
                    
                    <?php if ($success): ?>
                        <div class="text-center py-3">
                            <div class="alert alert-success border-0"><?= $success ?></div>
                            <p class="text-muted small">Normally, this would be sent to your email. Please copy it above to verify.</p>
                            <a href="/pwandeal/auth/verify.php" class="btn btn-primary px-4" style="background-color: #028090; border: none;">Proceed to Verify</a>
                        </div>
                    <?php else: ?>
                        <form method="POST" action="">
                            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">

                            <div class="mb-3">
                                <label class="form-label small fw-bold">Full Name</label>
                                <input type="text" name="name" class="form-control" placeholder="John Doe" value="<?= htmlspecialchars($name ?? '') ?>" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label small fw-bold">University Email</label>
                                <input type="email" name="email" class="form-control" placeholder="name@pwani.ac.ke" value="<?= htmlspecialchars($email ?? '') ?>" required>
                            </div>

                            <div class="row">
                                <div class="col-md-7 mb-3">
                                    <label class="form-label small fw-bold">School/Faculty</label>
                                    <select name="school" class="form-select" required>
                                        <option value="">Select School</option>
                                        <option value="SED">Education (SED)</option>
                                        <option value="SPAS">Pure & Applied Sciences (SPAS)</option>
                                        <option value="SHSS">Humanities (SHSS)</option>
                                        <option value="SASA">Agriculture (SASA)</option>
                                        <option value="SEES">Environmental (SEES)</option>
                                        <option value="SHHS">Health Sciences (SHHS)</option>
                                        <option value="SBE">Business (SBE)</option>
                                    </select>
                                </div>
                                <div class="col-md-5 mb-3">
                                    <label class="form-label small fw-bold">Year</label>
                                    <select name="year" class="form-select" required>
                                        <option value="1">Year 1</option>
                                        <option value="2">Year 2</option>
                                        <option value="3">Year 3</option>
                                        <option value="4">Year 4</option>
                                    </select>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label small fw-bold">Password</label>
                                <input type="password" name="password" id="password" class="form-control" placeholder="Min. 6 characters" required>
                            </div>

                            <div class="mb-4">
                                <label class="form-label small fw-bold">Confirm Password</label>
                                <input type="password" name="confirm_password" id="confirm_password" class="form-control" onkeyup="checkPasswordMatch()" required>
                                <div id="match-msg" class="form-text"></div>
                            </div>

                            <button type="submit" class="btn btn-primary w-100 py-2 fw-bold" style="background-color: #028090; border: none;">
                                Create Account
                            </button>
                        </form>

                        <div class="text-center mt-4">
                            <span class="text-muted small">Already have an account?</span> 
                            <a href="/pwandeal/auth/login.php" class="small fw-bold text-decoration-none" style="color: #028090;">Login</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function checkPasswordMatch() {
    const p = document.getElementById('password').value;
    const c = document.getElementById('confirm_password').value;
    const m = document.getElementById('match-msg');
    if(c.length > 0) {
        m.innerHTML = (p === c) ? '<span class="text-success small">✓ Passwords match</span>' : '<span class="text-danger small">× Passwords do not match</span>';
    } else {
        m.innerHTML = '';
    }
}
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>