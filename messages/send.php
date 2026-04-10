<?php
/**
 * PwanDeal - Initial Message Sender
 */
session_start();
require_once '../config/database.php';

// 1. Auth Guard
if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$to_user_id = isset($_GET['to']) ? (int)$_GET['to'] : 0;
$listing_id = isset($_GET['listing']) ? (int)$_GET['listing'] : null;
$error = '';
$success = false;

// 2. Fetch Provider Details
if ($to_user_id > 0) {
    $stmt = $conn->prepare('SELECT name, profile_photo, average_rating FROM users WHERE user_id = ?');
    $stmt->bind_param('i', $to_user_id);
    $stmt->execute();
    $provider = $stmt->get_result()->fetch_assoc();
    
    if (!$provider) {
        die("User not found!");
    }
} else {
    header('Location: ../listings/browse.php');
    exit();
}

// 3. Get Listing Context
$listing = null;
if ($listing_id) {
    $stmt = $conn->prepare('SELECT title FROM listings WHERE listing_id = ?');
    $stmt->bind_param('i', $listing_id);
    $stmt->execute();
    $listing = $stmt->get_result()->fetch_assoc();
}

// 4. Handle Post Logic
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $message_text = trim($_POST['message'] ?? '');
    
    if (empty($message_text)) {
        $error = 'Message cannot be empty.';
    } elseif (strlen($message_text) > 1000) {
        $error = 'Message is too long (max 1000 characters).';
    } else {
        $stmt = $conn->prepare('INSERT INTO messages (sender_id, receiver_id, listing_id, message_text) VALUES (?, ?, ?, ?)');
        $stmt->bind_param('iiis', $user_id, $to_user_id, $listing_id, $message_text);
        
        if ($stmt->execute()) {
            $success = true;
        } else {
            $error = 'Failed to send message. Please try again.';
        }
    }
}

$page_title = 'Contact ' . $provider['name'];
$base_url = '..';
include '../includes/header.php';
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-7 col-lg-6">
            
            <?php if ($success): ?>
                <div class="card border-0 shadow-lg rounded-4 text-center p-5">
                    <div class="display-3 text-success mb-3">✅</div>
                    <h3 class="fw-bold">Message Sent!</h3>
                    <p class="text-muted">Connecting you to <?= htmlspecialchars($provider['name']) ?>...</p>
                    <meta http-equiv="refresh" content="2;url=chat.php?user=<?= $to_user_id ?>">
                    <a href="chat.php?user=<?= $to_user_id ?>" class="btn btn-primary btn-lg rounded-pill mt-3 px-5">Open Chat Now</a>
                </div>
            <?php else: ?>

                <div class="card border-0 shadow-lg rounded-4 overflow-hidden">
                    <div class="card-header bg-primary py-4 text-center text-white border-0">
                        <h4 class="mb-0 fw-bold">Inquiry for <?= $listing ? htmlspecialchars($listing['title']) : 'Service' ?></h4>
                    </div>

                    <div class="card-body p-4 p-md-5">
                        <?php if ($error): ?>
                            <div class="alert alert-danger rounded-3 border-0"><?= $error ?></div>
                        <?php endif; ?>

                        <div class="d-flex align-items-center mb-4 p-3 bg-light rounded-4">
                            <img src="<?= !empty($provider['profile_photo']) ? '../uploads/profiles/'.$provider['profile_photo'] : '../assets/img/default-avatar.png' ?>" 
                                 class="rounded-circle border border-2 border-white shadow-sm" style="width: 65px; height: 65px; object-fit: cover;">
                            <div class="ms-3">
                                <h5 class="mb-0 fw-bold text-dark"><?= htmlspecialchars($provider['name']) ?></h5>
                                <small class="text-muted">★ <?= number_format($provider['average_rating'], 1) ?> Provider Rating</small>
                            </div>
                        </div>

                        <form method="POST">
                            <div class="mb-4">
                                <label class="form-label fw-semibold">Your Message</label>
                                <textarea name="message" id="message" rows="6" 
                                          class="form-control rounded-4 p-3" 
                                          placeholder="Hi! I'm interested in this service. Is it still available?"></textarea>
                                <div class="d-flex justify-content-between mt-2">
                                    <small class="text-muted"><span id="char-count">0</span>/1000</small>
                                    <small class="text-info">💡 Keep it polite!</small>
                                </div>
                            </div>

                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary btn-lg rounded-pill fw-bold py-3 shadow-sm">
                                    Send Inquiry
                                </button>
                                <a href="javascript:history.back();" class="btn btn-link text-muted">Go Back</a>
                            </div>
                        </form>
                    </div>
                </div>

            <?php endif; ?>

        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>