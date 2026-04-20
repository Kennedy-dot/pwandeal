
<?php
/**
 * PwanDeal - Inbox / Conversations Overview
 */
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// SQL: Grouping messages into distinct conversations with the latest message text
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

// Helper function for nice dates
function formatInboxTime($datetime) {
    $time = strtotime($datetime);
    if (date('Y-m-d', $time) == date('Y-m-d')) {
        return date('H:i', $time);
    }
    return date('M d', $time);
}
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            
            <div class="d-flex align-items-center justify-content-between mb-4">
                <h2 class="fw-bold mb-0">Messages</h2>
                <?php if ($conversations->num_rows > 0): ?>
                    <span class="badge bg-light text-dark border rounded-pill px-3 py-2">
                        <?= $conversations->num_rows ?> Threads
                    </span>
                <?php endif; ?>
            </div>

            <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                <div class="card-body p-0">
                    <?php if ($conversations->num_rows > 0): ?>
                        <div class="list-group list-group-flush">
                            <?php while ($conv = $conversations->fetch_assoc()): 
                                $is_unread = ($conv['unread_count'] > 0);
                            ?>
                                <a href="chat.php?user=<?= $conv['user_id'] ?>" 
                                   class="list-group-item list-group-item-action p-4 border-0 border-bottom transition-all <?= $is_unread ? 'bg-aliceblue' : '' ?>">
                                    <div class="d-flex align-items-center">
                                        
                                        <div class="position-relative">
                                            <img src="<?= !empty($conv['profile_photo']) ? '../uploads/profiles/'.$conv['profile_photo'] : '../assets/img/default-avatar.png' ?>" 
                                                 class="rounded-circle shadow-sm border border-2 border-white" 
                                                 style="width: 55px; height: 55px; object-fit: cover;">
                                            <?php if ($is_unread): ?>
                                                <span class="position-absolute top-0 start-100 translate-middle p-2 bg-primary border border-2 border-white rounded-circle">
                                                    <span class="visually-hidden">New Message</span>
                                                </span>
                                            <?php endif; ?>
                                        </div>

                                        <div class="ms-3 flex-grow-1 min-width-0">
                                            <div class="d-flex justify-content-between align-items-center mb-1">
                                                <h6 class="mb-0 fw-bold <?= $is_unread ? 'text-primary' : 'text-dark' ?>">
                                                    <?= htmlspecialchars($conv['name']) ?>
                                                </h6>
                                                <small class="<?= $is_unread ? 'fw-bold text-primary' : 'text-muted' ?>">
                                                    <?= formatInboxTime($conv['last_message_time']) ?>
                                                </small>
                                            </div>
                                            
                                            <div class="d-flex justify-content-between align-items-center">
                                                <p class="mb-0 text-truncate small <?= $is_unread ? 'fw-bold text-dark' : 'text-muted' ?>" style="max-width: 85%;">
                                                    <?= htmlspecialchars($conv['last_msg'] ?? 'Tap to start chatting') ?>
                                                </p>
                                                <?php if ($is_unread): ?>
                                                    <span class="badge bg-primary rounded-pill small"><?= $conv['unread_count'] ?></span>
                                                <?php endif; ?>
                                            </div>
                                        </div>

                                    </div>
                                </a>
                            <?php endwhile; ?>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-5 my-5">
                            <div class="mb-4">
                                <i class="bi bi-chat-dots text-light display-1"></i>
                            </div>
                            <h4 class="fw-bold">Your inbox is empty</h4>
                            <p class="text-muted mb-4">When you contact providers about services, <br>your conversations will appear here.</p>
                            <a href="../listings/browse.php" class="btn btn-primary rounded-pill px-4">Find Services</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

        </div>
    </div>
</div>

<style>
    .bg-aliceblue { background-color: #f0f8ff !important; }
    .transition-all { transition: all 0.2s ease; }
    .list-group-item-action:hover {
        background-color: #f8f9fa !important;
        transform: translateX(5px);
    }
    .text-truncate {
        display: block;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
</style>

<?php include '../includes/footer.php'; ?>