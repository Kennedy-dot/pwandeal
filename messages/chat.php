<?php
/**
 * PwanDeal - Chat Conversation
 */
session_start();
require_once '../config/database.php';

// 1. Auth Guard
if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$other_user_id = isset($_GET['user']) ? (int)$_GET['user'] : 0;

// Prevent chatting with oneself or invalid users
if ($other_user_id === 0 || $other_user_id === $user_id) {
    header('Location: inbox.php');
    exit();
}

// 2. Fetch Recipient Details
$stmt = $conn->prepare('SELECT name, profile_photo, average_rating FROM users WHERE user_id = ?');
$stmt->bind_param('i', $other_user_id);
$stmt->execute();
$other_user = $stmt->get_result()->fetch_assoc();

if (!$other_user) {
    die("User not found!");
}

// 3. Mark incoming messages as read
$read_stmt = $conn->prepare('UPDATE messages SET is_read = 1, read_at = NOW() WHERE sender_id = ? AND receiver_id = ? AND is_read = 0');
$read_stmt->bind_param('ii', $other_user_id, $user_id);
$read_stmt->execute();

// 4. Handle Outgoing Message
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty(trim($_POST['message']))) {
    $message_text = trim($_POST['message']);
    $send_stmt = $conn->prepare('INSERT INTO messages (sender_id, receiver_id, message_text) VALUES (?, ?, ?)');
    $send_stmt->bind_param('iis', $user_id, $other_user_id, $message_text);
    
    if ($send_stmt->execute()) {
        header("Location: chat.php?user=$other_user_id");
        exit();
    }
}

// 5. Fetch Conversation History
$history_sql = "SELECT * FROM messages 
                WHERE (sender_id = ? AND receiver_id = ?) 
                OR (sender_id = ? AND receiver_id = ?)
                ORDER BY created_at ASC";
$history_stmt = $conn->prepare($history_sql);
$history_stmt->bind_param('iiii', $user_id, $other_user_id, $other_user_id, $user_id);
$history_stmt->execute();
$messages = $history_stmt->get_result();

$page_title = 'Chat with ' . $other_user['name'];
$base_url = '..';
include '../includes/header.php';
?>

<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-md-10 col-lg-8">
            
            <div class="card shadow-sm border-0 rounded-4 overflow-hidden">
                <div class="card-header bg-white border-bottom py-3 px-4 d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center">
                        <a href="inbox.php" class="text-secondary me-3 d-md-none"><i class="fas fa-arrow-left"></i></a>
                        <img src="<?= !empty($other_user['profile_photo']) ? '../uploads/profiles/'.$other_user['profile_photo'] : '../assets/img/default-avatar.png' ?>" 
                             class="rounded-circle me-3" style="width: 45px; height: 45px; object-fit: cover;">
                        <div>
                            <h6 class="mb-0 fw-bold"><?= htmlspecialchars($other_user['name']) ?></h6>
                            <small class="text-warning">★ <?= number_format($other_user['average_rating'], 1) ?> Provider</small>
                        </div>
                    </div>
                    <a href="../profile/view.php?id=<?= $other_user_id ?>" class="btn btn-sm btn-light rounded-pill px-3">View Profile</a>
                </div>

                <div id="chat-window" class="chat-container bg-light" style="height: 500px;">
                    <?php if ($messages->num_rows > 0): ?>
                        <?php while ($msg = $messages->fetch_assoc()): ?>
                            <div class="message-bubble <?= $msg['sender_id'] == $user_id ? 'message-sent' : 'message-received' ?>">
                                <?= htmlspecialchars($msg['message_text']) ?>
                                <small class="message-time d-block text-end mt-1">
                                    <?= date('H:i', strtotime($msg['created_at'])) ?>
                                    <?php if($msg['sender_id'] == $user_id): ?>
                                        <span class="ms-1 fw-bold"><?= $msg['is_read'] ? '✓✓' : '✓' ?></span>
                                    <?php endif; ?>
                                </small>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div class="text-center my-auto p-5">
                            <div class="display-1 text-muted mb-3">👋</div>
                            <h5 class="text-muted">Say "Hi" to start the deal!</h5>
                            <p class="small text-muted">Negotiate safely within the platform.</p>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="card-footer bg-white border-top-0 p-3">
                    <form method="POST" class="input-group">
                        <input type="text" name="message" id="message-input" 
                               class="form-control border-0 bg-light rounded-pill px-4" 
                               placeholder="Type a message..." 
                               required autocomplete="off">
                        <button type="submit" class="btn btn-primary rounded-circle ms-2 d-flex align-items-center justify-content-center" 
                                style="width: 45px; height: 45px;">
                            <span class="fs-5">➤</span>
                        </button>
                    </form>
                </div>
            </div>

        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const chatWindow = document.getElementById('chat-window');
    const messageInput = document.getElementById('message-input');

    // 1. Scroll to bottom on load
    chatWindow.scrollTop = chatWindow.scrollHeight;

    // 2. Focus input immediately
    messageInput.focus();
});
</script>

<?php include '../includes/footer.php'; ?>