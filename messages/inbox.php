<?php
/**
 * PwanDeal - Inbox / Conversations Overview
 */
session_start();
require_once '../config/database.php';

// 1. Auth Guard
if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// 2. Fetch Conversations
$sql = "SELECT 
            u.user_id, u.name, u.profile_photo,
            MAX(m.created_at) as last_message_time,
            (SELECT message_text FROM messages 
             WHERE (sender_id = ? AND receiver_id = u.user_id) 
                OR (sender_id = u.user_id AND receiver_id = ?) 
             ORDER BY created_at DESC LIMIT 1) as last_msg,
            (SELECT COUNT(*) FROM messages 
             WHERE receiver_id = ? AND sender_id = u.user_id AND is_read = 0) as unread_count
        FROM messages m
        JOIN users u ON (CASE WHEN m.sender_id = ? THEN m.receiver_id = u.user_id ELSE m.sender_id = u.user_id END)
        WHERE m.sender_id = ? OR m.receiver_id = ?
        GROUP BY u.user_id
        ORDER BY last_message_time DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param('iiiiii', $user_id, $user_id, $user_id, $user_id, $user_id, $user_id);
$stmt->execute();
$conversations = $stmt->get_result();

$page_title = 'My Messages';
include '../includes/header.php';

// Date Helper
function formatInboxTime($datetime) {
    $time = strtotime($datetime);
    return (date('Y-m-d', $time) == date('Y-m-d')) ? date('H:i', $time) : date('M d', $time);
}
?>

<style>
    /* Custom styles for a more "app-like" feel */
    .inbox-card { transition: all 0.2s ease; border-bottom: 1px solid #f0f0f0 !important; }
    .inbox-card:hover { background-color: #f8f9fa !important; transform: translateX(4px); }
    .unread-dot { width: 12px; height: 12px; border: 2px solid #fff; }
</style>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="fw-bold mb-0">Messages</h2>
                <span class="badge bg-light text-dark border rounded-pill px-3"><?= $conversations->num_rows ?> Threads</span>
            </div>

            <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                <div class="card-body p-0">
                    <?php if ($conversations->num_rows > 0): ?>
                        <div class="list-group list-group-flush">
                            <?php while ($conv = $conversations->fetch_assoc()): 
                                $is_unread = ($conv['unread_count'] > 0);
                            ?>
                                <a href="chat.php?user=<?= $conv['user_id'] ?>" 
                                   class="list-group-item list-group-item-action p-4 inbox-card border-0 <?= $is_unread ? 'bg-aliceblue' : '' ?>">
                                    <div class="d-flex align-items-center">
                                        
                                        <div class="position-relative">
                                            <img src="<?= !empty($conv['profile_photo']) ? '../uploads/profiles/'.$conv['profile_photo'] : '../assets/img/default-avatar.png' ?>" 
                                                 class="rounded-circle shadow-sm" style="width: 55px; height: 55px; object-fit: cover;">
                                            <?php if ($is_unread): ?>
                                                <span class="position-absolute top-0 start-100 translate-middle p-2 bg-primary unread-dot rounded-circle shadow-sm"></span>
                                            <?php endif; ?>
                                        </div>

                                        <div class="ms-3 flex-grow-1 overflow-hidden">
                                            <div class="d-flex justify-content-between align-items-center mb-1">
                                                <h6 class="mb-0 <?= $is_unread ? 'fw-bold text-dark' : 'text-secondary' ?>"><?= htmlspecialchars($conv['name']) ?></h6>
                                                <small class="<?= $is_unread ? 'text-primary fw-bold' : 'text-muted' ?>">
                                                    <?= formatInboxTime($conv['last_message_time']) ?>
                                                </small>
                                            </div>
                                            <p class="mb-0 text-truncate small <?= $is_unread ? 'text-dark fw-medium' : 'text-muted' ?>">
                                                <?= htmlspecialchars($conv['last_msg'] ?? 'Start a conversation') ?>
                                            </p>
                                        </div>

                                    </div>
                                </a>
                            <?php endwhile; ?>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-5">
                            <div class="mb-3">
                                <i class="bi bi-chat-dots display-1 text-light"></i>
                            </div>
                            <h5 class="text-secondary">Your inbox is empty</h5>
                            <p class="text-muted mb-4">Messages from service providers will appear here.</p>
                            <a href="../listings/browse.php" class="btn btn-primary rounded-pill px-4">Find a Service</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>