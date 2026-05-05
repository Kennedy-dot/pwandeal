<?php
/**
 * PwanDeal - Chat Conversation (Enhanced with Delete)
 * Path: messages/chat.php
 */
session_start();
require_once __DIR__ . '/../config/database.php';

// 1. AUTH GUARD
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

// 2. FETCH RECIPIENT DETAILS
$stmt = $conn->prepare('SELECT name, profile_photo, average_rating, is_suspended FROM users WHERE user_id = ?');
$stmt->bind_param('i', $other_user_id);
$stmt->execute();
$other_user = $stmt->get_result()->fetch_assoc();

if (!$other_user) {
    die("User not found!");
}

// Check if user is suspended
if ($other_user['is_suspended']) {
    echo "<div class='container py-5'><div class='alert alert-warning'>This user is no longer available on PwanDeal.</div></div>";
    exit();
}

// 3. MARK INCOMING MESSAGES AS READ
$read_stmt = $conn->prepare('UPDATE messages SET is_read = 1, read_at = NOW() WHERE sender_id = ? AND receiver_id = ? AND is_read = 0');
$read_stmt->bind_param('ii', $other_user_id, $user_id);
$read_stmt->execute();

// 4. HANDLE OUTGOING MESSAGE (Post-Redirect-Get Pattern)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty(trim($_POST['message']))) {
    $message_text = trim($_POST['message']);
    
    // Validate message length
    if (strlen($message_text) > 1000) {
        $_SESSION['message_error'] = 'Message is too long (max 1000 characters)';
    } else {
        $send_stmt = $conn->prepare('INSERT INTO messages (sender_id, receiver_id, message_text) VALUES (?, ?, ?)');
        $send_stmt->bind_param('iis', $user_id, $other_user_id, $message_text);
        
        if ($send_stmt->execute()) {
            header("Location: chat.php?user=$other_user_id");
            exit();
        } else {
            $_SESSION['message_error'] = 'Failed to send message. Try again.';
        }
    }
}

// 5. FETCH CONVERSATION HISTORY (EXCLUDING DELETED)
$history_sql = "SELECT message_id, sender_id, receiver_id, message_text, is_read, is_deleted, created_at, deleted_at
                FROM messages 
                WHERE (sender_id = ? AND receiver_id = ?) 
                OR (sender_id = ? AND receiver_id = ?)
                ORDER BY created_at ASC";
$history_stmt = $conn->prepare($history_sql);
$history_stmt->bind_param('iiii', $user_id, $other_user_id, $other_user_id, $user_id);
$history_stmt->execute();
$messages = $history_stmt->get_result();

$page_title = 'Chat with ' . htmlspecialchars($other_user['name']);
include __DIR__ . '/../includes/header.php';
?>

<style>
    .chat-container {
        display: flex;
        flex-direction: column;
        padding: 20px;
        overflow-y: auto;
        background: #f8f9fa;
        scrollbar-width: thin;
        height: 500px;
    }

    .message-wrapper {
        display: flex;
        margin-bottom: 15px;
        position: relative;
        align-items: flex-end;
        gap: 8px;
    }

    .message-wrapper.sent {
        justify-content: flex-end;
    }

    .message-wrapper.received {
        justify-content: flex-start;
    }

    .message-bubble {
        max-width: 70%;
        padding: 12px 16px;
        border-radius: 18px;
        font-size: 0.95rem;
        word-wrap: break-word;
        animation: slideIn 0.3s ease;
    }

    @keyframes slideIn {
        from {
            opacity: 0;
            transform: translateY(10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .message-sent {
        background: #028090;
        color: white;
        border-bottom-right-radius: 4px;
    }

    .message-received {
        background: #e9ecef;
        color: #212529;
        border-bottom-left-radius: 4px;
    }

    .message-deleted {
        background: #f0f0f0;
        color: #999;
        font-style: italic;
        opacity: 0.7;
    }

    .message-time {
        font-size: 0.75rem;
        opacity: 0.7;
        margin-top: 4px;
        display: flex;
        align-items: center;
        gap: 4px;
    }

    .message-actions {
        position: absolute;
        right: -40px;
        top: 0;
        display: none;
        flex-direction: column;
        gap: 2px;
    }

    .message-wrapper.sent:hover .message-actions {
        display: flex;
    }

    .msg-delete-btn {
        background: #dc3545;
        color: white;
        border: none;
        width: 32px;
        height: 32px;
        border-radius: 50%;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.8rem;
        transition: all 0.2s;
        padding: 0;
    }

    .msg-delete-btn:hover {
        background: #c82333;
        transform: scale(1.1);
    }

    .msg-copy-btn {
        background: #6c757d;
        color: white;
        border: none;
        width: 32px;
        height: 32px;
        border-radius: 50%;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.8rem;
        transition: all 0.2s;
        padding: 0;
    }

    .msg-copy-btn:hover {
        background: #5a6268;
        transform: scale(1.1);
    }

    .empty-chat {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        height: 100%;
        color: #999;
    }

    .toast-notification {
        position: fixed;
        bottom: 20px;
        right: 20px;
        background: #28a745;
        color: white;
        padding: 12px 20px;
        border-radius: 8px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.2);
        animation: slideUp 0.3s ease;
        z-index: 1000;
    }

    @keyframes slideUp {
        from {
            transform: translateY(20px);
            opacity: 0;
        }
        to {
            transform: translateY(0);
            opacity: 1;
        }
    }
</style>

<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm border-0 rounded-4 overflow-hidden">
                
                <!-- Header -->
                <div class="card-header bg-white py-3 d-flex align-items-center justify-content-between border-bottom">
                    <div class="d-flex align-items-center">
                        <a href="inbox.php" class="text-dark me-3" title="Back to inbox">
                            <i class="bi bi-arrow-left fs-5"></i>
                        </a>
                        <img src="<?= !empty($other_user['profile_photo']) ? '../uploads/profiles/'.$other_user['profile_photo'] : '../assets/img/default-avatar.png' ?>" 
                             class="rounded-circle me-2" width="40" height="40" style="object-fit: cover;">
                        <div>
                            <h6 class="mb-0 fw-bold"><?= htmlspecialchars($other_user['name']) ?></h6>
                            <small class="text-muted">
                                <i class="bi bi-star-fill text-warning"></i> 
                                <?= number_format($other_user['average_rating'], 1) ?>
                            </small>
                        </div>
                    </div>
                    <a href="../profile/view.php?id=<?= $other_user_id ?>" class="btn btn-sm btn-outline-primary rounded-pill">
                        View Profile
                    </a>
                </div>

                <!-- Chat Messages -->
                <div id="chat-window" class="chat-container">
                    <?php if ($messages->num_rows > 0): ?>
                        <?php while ($msg = $messages->fetch_assoc()): ?>
                            <div class="message-wrapper <?= $msg['sender_id'] == $user_id ? 'sent' : 'received' ?>">
                                <?php if ($msg['is_deleted']): ?>
                                    <!-- DELETED MESSAGE -->
                                    <div class="message-bubble message-deleted">
                                        <i class="bi bi-trash-fill"></i> Message deleted
                                    </div>
                                <?php else: ?>
                                    <!-- NORMAL MESSAGE -->
                                    <div class="message-bubble <?= $msg['sender_id'] == $user_id ? 'message-sent' : 'message-received' ?>">
                                        <div><?= nl2br(htmlspecialchars($msg['message_text'])) ?></div>
                                        <div class="message-time">
                                            <?= date('H:i', strtotime($msg['created_at'])) ?>
                                            <?php if ($msg['sender_id'] == $user_id): ?>
                                                <i class="bi <?= $msg['is_read'] ? 'bi-check2-all text-info' : 'bi-check2' ?>"></i>
                                            <?php endif; ?>
                                        </div>
                                    </div>

                                    <!-- MESSAGE ACTIONS (Visible on hover for sent messages) -->
                                    <?php if ($msg['sender_id'] == $user_id): ?>
                                        <div class="message-actions">
                                            <button class="msg-copy-btn" onclick="copyMessage('<?= addslashes(htmlspecialchars($msg['message_text'])) ?>')" title="Copy">
                                                <i class="bi bi-clipboard"></i>
                                            </button>
                                            <button class="msg-delete-btn" onclick="deleteMessage(<?= $msg['message_id'] ?>)" title="Delete">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </div>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div class="empty-chat">
                            <p class="mb-2">👋 No messages yet</p>
                            <small>Send a "Hi" to start the conversation!</small>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Message Input -->
                <div class="card-footer bg-white p-3 border-top">
                    <?php if (isset($_SESSION['message_error'])): ?>
                        <div class="alert alert-danger small mb-2">
                            <?= htmlspecialchars($_SESSION['message_error']) ?>
                        </div>
                        <?php unset($_SESSION['message_error']); ?>
                    <?php endif; ?>

                    <form method="POST" class="d-flex gap-2">
                        <textarea name="message" id="message-input" class="form-control rounded-4 border-light bg-light px-4" 
                                  placeholder="Type a message..." style="resize: none; height: 45px; max-height: 120px;" 
                                  required></textarea>
                        <button class="btn btn-primary rounded-circle px-3" type="submit" title="Send">
                            <i class="bi bi-send-fill"></i>
                        </button>
                    </form>
                    <small class="text-muted d-block mt-2">
                        <span id="char-count">0</span>/1000 characters
                    </small>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // ==================== AUTO-SCROLL ====================
    const chatWindow = document.getElementById('chat-window');
    chatWindow.scrollTop = chatWindow.scrollHeight;

    // ==================== CHARACTER COUNTER ====================
    const textarea = document.getElementById('message-input');
    const charCount = document.getElementById('char-count');
    
    textarea.addEventListener('input', function() {
        charCount.textContent = this.value.length;
        // Auto-expand textarea
        this.style.height = 'auto';
        this.style.height = Math.min(this.scrollHeight, 120) + 'px';
    });

    // ==================== COPY MESSAGE ====================
    function copyMessage(text) {
        navigator.clipboard.writeText(text).then(() => {
            showToast('Message copied!');
        }).catch(() => {
            alert('Failed to copy message');
        });
    }

    // ==================== DELETE MESSAGE ====================
    function deleteMessage(messageId) {
        if (!confirm('Delete this message? This cannot be undone.')) {
            return;
        }

        const formData = new FormData();
        formData.append('message_id', messageId);

        fetch('../messages/delete-message.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showToast('Message deleted');
                // Reload the chat to show deleted message
                setTimeout(() => {
                    location.reload();
                }, 1500);
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Failed to delete message');
        });
    }

    // ==================== TOAST NOTIFICATION ====================
    function showToast(message) {
        const toast = document.createElement('div');
        toast.className = 'toast-notification';
        toast.textContent = message;
        document.body.appendChild(toast);

        setTimeout(() => {
            toast.style.animation = 'slideUp 0.3s ease reverse';
            setTimeout(() => toast.remove(), 300);
        }, 2000);
    }

    // ==================== AUTO-REFRESH (Optional) ====================
    // Uncomment to auto-refresh chat every 3 seconds
    // setInterval(() => {
    //     chatWindow.scrollTop = chatWindow.scrollHeight;
    //     fetch('?user=<?= $other_user_id ?>')
    //         .then(r => r.text())
    //         .then(html => {
    //             const parser = new DOMParser();
    //             const newDoc = parser.parseFromString(html, 'text/html');
    //             const newChat = newDoc.getElementById('chat-window');
    //             if (newChat) {
    //                 chatWindow.innerHTML = newChat.innerHTML;
    //                 chatWindow.scrollTop = chatWindow.scrollHeight;
    //             }
    //         });
    // }, 3000);
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
