<?php
/**
 * PwanDeal - Chat Conversation
 * Path: messages/chat.php
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

// 4. Handle Outgoing Message (Post-Redirect-Get Pattern)
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

$page_title = 'Chat with ' . htmlspecialchars($other_user['name']);
include '../includes/header.php';
?>

<style>
    .chat-container {
        display: flex;
        flex-direction: column;
        padding: 20px;
        overflow-y: auto;
        background: #f8f9fa;
        scrollbar-width: thin;
    }
    .message-bubble {
        max-width: 75%;
        padding: 10px 15px;
        border-radius: 18px;
        margin-bottom: 10px;
        font-size: 0.95rem;
    }
    .message-sent {
        align-self: flex-end;
        background: #028090;
        color: white;
        border-bottom-right-radius: 2px;
    }
    .message-received {
        align-self: flex-start;
        background: #e9ecef;
        color: #212529;
        border-bottom-left-radius: 2px;
    }
    .message-time {
        font-size: 0.7rem;
        opacity: 0.8;
        margin-top: 3px;
    }
</style>

<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm border-0 rounded-4 overflow-hidden">
                <div class="card-header bg-white py-3 d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center">
                        <a href="inbox.php" class="text-dark me-3"><i class="bi bi-arrow-left fs-5"></i></a>
                        <img src="<?= !empty($other_user['profile_photo']) ? '../uploads/profiles/'.$other_user['profile_photo'] : '../assets/img/default-avatar.png' ?>" 
                             class="rounded-circle me-2" width="40" height="40" style="object-fit: cover;">
                        <div>
                            <h6 class="mb-0 fw-bold"><?= htmlspecialchars($other_user['name']) ?></h6>
                            <small class="text-muted">Rating: <?= number_format($other_user['average_rating'], 1) ?> ★</small>
                        </div>
                    </div>
                    <a href="../profile/view.php?id=<?= $other_user_id ?>" class="btn btn-sm btn-outline-primary rounded-pill">View Profile</a>
                </div>

                <div id="chat-window" class="chat-container" style="height: 450px;">
                    <?php if ($messages->num_rows > 0): ?>
                        <?php while ($msg = $messages->fetch_assoc()): ?>
                            <div class="message-bubble <?= $msg['sender_id'] == $user_id ? 'message-sent' : 'message-received' ?>">
                                <div><?= nl2br(htmlspecialchars($msg['message_text'])) ?></div>
                                <div class="message-time text-end">
                                    <?= date('H:i', strtotime($msg['created_at'])) ?>
                                    <?php if($msg['sender_id'] == $user_id): ?>
                                        <i class="bi <?= $msg['is_read'] ? 'bi-check2-all text-info' : 'bi-check2' ?>"></i>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div class="text-center my-auto">
                            <p class="text-muted">No messages yet. Send a "Hi" to start!</p>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="card-footer bg-white p-3">
                    <form method="POST" class="input-group">
                        <input type="text" name="message" id="message-input" class="form-control rounded-pill-start border-light bg-light px-4" placeholder="Type a message..." required>
                        <button class="btn btn-primary rounded-pill-end px-4" type="submit">
                            <i class="bi bi-send"></i>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Auto-scroll to the bottom of the chat
    const chatWindow = document.getElementById('chat-window');
    chatWindow.scrollTop = chatWindow.scrollHeight;
</script>

<?php include '../includes/footer.php'; ?>