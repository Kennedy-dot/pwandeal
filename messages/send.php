<?php
/**
 * PwanDeal - Initial Message Sender
 */
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$to_user_id = isset($_GET['to']) ? (int)$_GET['to'] : 0;
$listing_id = isset($_GET['listing']) ? (int)$_GET['listing'] : null;
$error = '';
$success = false;

// 1. Fetch Provider Details
if ($to_user_id > 0) {
    // Prevent messaging yourself
    if($to_user_id === $user_id) {
        header('Location: ../listings/view.php?id=' . $listing_id);
        exit();
    }

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

// 2. Get Listing Context (to show what they are inquiring about)
$listing = null;
if ($listing_id) {
    $stmt = $conn->prepare('SELECT title, price, listing_id FROM listings WHERE listing_id = ?');
    $stmt->bind_param('i', $listing_id);
    $stmt->execute();
    $listing = $stmt->get_result()->fetch_assoc();
}

// 3. Handle Message Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $message_text = trim($_POST['message'] ?? '');
    
    if (empty($message_text)) {
        $error = 'Please write a message to send.';
    } elseif (strlen($message_text) > 1000) {
        $error = 'Keep your message under 1000 characters.';
    } else {
        // Optional: Add a check here to see if they've already sent this exact message 
        // to avoid spamming the "Send" button.
        
        $stmt = $conn->prepare('INSERT INTO messages (sender_id, receiver_id, listing_id, message_text) VALUES (?, ?, ?, ?)');
        $stmt->bind_param('iiis', $user_id, $to_user_id, $listing_id, $message_text);
        
        if ($stmt->execute()) {
            $success = true;
        } else {
            $error = 'Could not send message. Please try again later.';
        }
    }
}

$page_title = 'Contact ' . htmlspecialchars($provider['name']);
include '../includes/header.php';
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-7 col-lg-6">
            
            <?php if ($success): ?>
                <div class="card border-0 shadow-lg rounded-4 text-center p-5 animate__animated animate__fadeIn">
                    <div class="mb-4">
                        <i class="bi bi-check-circle-fill text-success" style="font-size: 4rem;"></i>
                    </div>
                    <h3 class="fw-bold text-dark">Inquiry Sent!</h3>
                    <p class="text-muted">Taking you to your conversation with <?= htmlspecialchars($provider['name']) ?>...</p>
                    
                    <div class="mt-4">
                        <div class="spinner-border text-primary spinner-border-sm me-2" role="status"></div>
                        <span class="small text-muted">Redirecting...</span>
                    </div>

                    <script>setTimeout(() => { window.location.href = 'chat.php?user=<?= $to_user_id ?>'; }, 2000);</script>
                </div>
            <?php else: ?>

                <div class="card border-0 shadow-lg rounded-4 overflow-hidden">
                    <div class="card-header bg-dark py-4 text-center text-white border-0">
                        <h5 class="mb-0 fw-bold">Message Provider</h5>
                    </div>

                    <div class="card-body p-4 p-md-5">
                        <?php if ($listing): ?>
                            <div class="alert alert-info border-0 rounded-4 mb-4 d-flex align-items-center">
                                <i class="bi bi-info-circle-fill fs-4 me-3"></i>
                                <div>
                                    <small class="d-block text-uppercase fw-bold opacity-75" style="font-size: 0.65rem;">Inquiring about</small>
                                    <span class="fw-bold"><?= htmlspecialchars($listing['title']) ?></span>
                                    <span class="badge bg-white text-info ms-2">KES <?= number_format($listing['price']) ?></span>
                                </div>
                            </div>
                        <?php endif; ?>

                        <?php if ($error): ?>
                            <div class="alert alert-danger rounded-3 border-0 mb-4"><?= $error ?></div>
                        <?php endif; ?>

                        <div class="d-flex align-items-center mb-4">
                            <img src="<?= !empty($provider['profile_photo']) ? '../uploads/profiles/'.$provider['profile_photo'] : '../assets/img/default-avatar.png' ?>" 
                                 class="rounded-circle shadow-sm" style="width: 55px; height: 55px; object-fit: cover;">
                            <div class="ms-3">
                                <h6 class="mb-0 fw-bold"><?= htmlspecialchars($provider['name']) ?></h6>
                                <div class="text-warning small">
                                    <i class="bi bi-star-fill"></i> <?= number_format($provider['average_rating'], 1) ?> Rating
                                </div>
                            </div>
                        </div>

                        <form method="POST">
                            <div class="mb-4">
                                <label class="form-label fw-bold small text-muted">YOUR MESSAGE</label>
                                <textarea name="message" id="message" rows="5" 
                                          class="form-control border-0 bg-light rounded-4 p-3 shadow-none" 
                                          placeholder="Hi <?= explode(' ', $provider['name'])[0] ?>! I'm interested in this. Is it still available?" 
                                          style="resize: none;" required></textarea>
                                <div class="d-flex justify-content-between mt-2">
                                    <small class="text-muted"><span id="char-count">0</span>/1000</small>
                                </div>
                            </div>

                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary btn-lg rounded-pill fw-bold py-3 shadow">
                                    Send Message <i class="bi bi-send-fill ms-2"></i>
                                </button>
                                <a href="javascript:history.back();" class="btn btn-link text-muted text-decoration-none small">Cancel</a>
                            </div>
                        </form>
                    </div>
                </div>
            <?php endif; ?>

        </div>
    </div>
</div>

<script>
    // Character counter
    const textarea = document.getElementById('message');
    const counter = document.getElementById('char-count');
    if(textarea) {
        textarea.addEventListener('input', () => {
            counter.textContent = textarea.value.length;
        });
    }
</script>

<?php include '../includes/footer.php'; ?>