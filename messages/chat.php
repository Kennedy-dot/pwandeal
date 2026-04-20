<?php
/**
 * PwanDeal - Chat Conversation
 */
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$other_user_id = isset($_GET['user']) ? (int)$_GET['user'] : 0;

// Security: Prevent chatting with yourself or zero
if ($other_user_id === 0 || $other_user_id === $user_id) {
    header('Location: inbox.php');
    exit();
}

// 1. Fetch Recipient Details
$stmt = $conn->prepare('SELECT name, profile_photo, average_rating FROM users WHERE user_id = ?');
$stmt->bind_param('i', $other_user_id);
$stmt->execute();
$other_user = $stmt->get_result()->fetch_assoc();

if (!$other_user) {
    die("User not found!");
}

// 2. Mark incoming messages as read (Done BEFORE fetching history)
$read_stmt = $conn->prepare('UPDATE messages SET is_read = 1, read_at = NOW() WHERE sender_id = ? AND receiver_id = ? AND is_read = 0');
$read_stmt->bind_param('ii', $other_user_id, $user_id);
$read_stmt->execute();

// 3. Handle Outgoing Message (POST-Redirect-GET Pattern)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty(trim($_POST['message']))) {
    $message_text = trim($_POST['message']);
    $send_stmt = $conn->prepare('INSERT INTO messages (sender_id, receiver_id, message_text) VALUES (?, ?, ?)');
    $send_stmt->bind_param('iis', $user_id, $other_user_id, $message_text);
    
    if ($send_stmt->execute()) {
        // Redirect back to the same page to clear the POST data
        // This prevents "Confirm Form Resubmission" errors when hitting refresh
        header("Location: chat.php?user=$other_user_id");
        exit();
    }
}

// 4. Fetch History
$history_sql = "SELECT * FROM messages 
                WHERE (sender_id = ? AND receiver_id = ?) 
                OR (sender_id = ? AND receiver_id = ?)
                ORDER BY created_at ASC";
$history_stmt = $conn->prepare($history_sql);
$history_stmt->bind_param('iiii', $user_id, $other_user_id, $other_user_id, $user_id);
$history_stmt->execute();
$messages = $history_stmt->get_result();

$page_title = 'Chat with ' . htmlspecialchars($other_user['name']);
include '../includes/header.php';
?>

<style>
    body { background-color: #f8f9fa; }
    .chat-container {
        display: flex;
        flex-direction: column;
        padding: 20px;
        overflow-y: auto;
        background: #f0f2f5;
        scrollbar-width: thin;
    }
    .message-bubble {
        max-width: 80%;
        padding: 12px 16px;
        border-radius: 20px;
        margin-bottom: 15px;
        font-size: 0.95rem;
        position: relative;
    }
    .message-sent {
        align-self: flex-end;
        background: #028090;
        color: white;
        border-bottom-right-radius: 4px;
    }
    .message-received {
        align-self: flex-start;
        background: white;
        color: #333;
        border-bottom-left-radius: 4px;
        box-shadow: 0 2px 5px rgba(0,0,0,0.05);
    }
    .message-time {
        font-size: 0.65rem;
        opacity: 0.7;
        margin-top: 4px;
    }
    .chat-header {
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(10px);
        z-index: 10;
    }
</style>

<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-md-10 col-lg-8">
            
            <div class="card shadow-sm border-0 rounded-4 overflow-hidden">
                <div class="card-header chat-header border-bottom py-3 px-4 d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center">
                        <a href="inbox.php" class="btn btn-link text-secondary p-0 me-3">
                            <i class="bi bi-chevron-left fs-4"></i>
                        </a>
                        <img src="<?= !empty($other_user['profile_photo']) ? '../uploads/profiles/'.$other_user['profile_photo'] : '../assets/img/default-avatar.png' ?>" 
                             class="rounded-circle me-3" style="width: 45px; height: 45px; object-fit: cover;">
                        <div>
                            <h6 class="mb-0 fw-bold"><?= htmlspecialchars($other_user['name']) ?></h6>
                            <small class="text-warning"><i class="bi bi-star-fill"></i> <?= number_format($other_user['average_rating'], 1) ?></small>
                        </div>
                    </div>
                </div>

                <div id="chat-window" class="chat-container" style="height: 500px;">
                    <?php if ($messages->num_rows > 0): ?>
                        <?php while ($msg = $messages->fetch_assoc()): ?>
                            <div class="message-bubble <?= $msg['sender_id'] == $user_id ? 'message-sent' : 'message-received' ?>">
                                <div class="text-break"><?= nl2br(htmlspecialchars($msg['message_text'])) ?></div>
                                <div class="message-time d-flex justify-content-end align-items-center gap-1">
                                    <?= date('H:i', strtotime($msg['created_at'])) ?>
                                    <?php if($msg['sender_id'] == $user_id): ?>
                                        <i class="bi <?= $msg['is_read'] ? 'bi-check2-all text-info' : 'bi-check2' ?>" style="font-size: 0.9rem;"></i>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div class="text-center my-auto p-5">
                            <span class="display-1">👋</span>
                            <h5 class="text-muted mt-3">Start the conversation</h5>
                            <p class="small text-muted">Say hello to <?= htmlspecialchars($other_user['name']) ?>!</p>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="card-footer bg-white border-top p-3">
                    <form method="POST" id="chat-form" class="d-flex gap-2">
                        <input type="text" name="message" id="message-input" 
                               class="form-control border-0 bg-light rounded-pill px-4 shadow-none" 
                               placeholder="Type your message..." 
                               required autocomplete="off">
                        <button type="submit" class="btn btn-primary rounded-circle d-flex align-items-center justify-content-center shadow-sm" 
                                style="width: 45px; height: 45px; flex-shrink: 0;">
                            <i class="bi bi-send-fill"></i>
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

    // 1. Force scroll to bottom on load
    chatWindow.scrollTop = chatWindow.scrollHeight;

    // 2. Focus input automatically
    messageInput.focus();

    // 3. Simple Page Polling (checks for new messages every 8 seconds)
    // We only reload if the user is NOT typing
    setInterval(function() {
        if (messageInput.value === "") {
            // Uncomment the line below to enable auto-refresh
            // window.location.reload(); 
        }
    }, 8000); 
});
</script>

<?php include '../includes/footer.php'; ?>