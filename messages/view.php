
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
// This query groups by user to show unique conversation threads with the latest message text
$sql = "SELECT 
            u.user_id, u.name, u.profile_photo, u.average_rating,
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

// Helper function for nice dates
function formatInboxTime($datetime) {
    $time = strtotime($datetime);
    if (date('Y-m-d', $time) == date('Y-m-d')) {
        return date('H:i', $time);
    }
    return date('M d', $time);
}

$page_title = 'My Messages';
include '../includes/header.php';
?>

<style>
    .bg-unread { background-color: #f0f8ff !important; }
    .transition-all { transition: all 0.2s ease; }
    .conv-item:hover {
        background-color: #f8f9fa !important;
        transform: translateX(5px);
    }
    .text-truncate-custom {
        display: block;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
</style>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-9">
            
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                <div class="card-header bg-primary py-4 border-0">
                    <div class="d-flex justify-content-between align-items-center px-2">
                        <h3 class="mb-0 text-white fw-bold">Inbox</h3>
                        <span class="badge bg-white text-primary rounded-pill px-3">
                            <?= $conversations->num_rows ?> Conversations
                        </span>
                    </div>
                </div>

                <div class="card-body p-0">
                    <?php if ($conversations->num_rows > 0): ?>
                        <div class="list-group list-group-flush">
                            <?php while ($conv = $conversations->fetch_assoc()): 
                                $is_unread = ($conv['unread_count'] > 0);
                            ?>
                                <a href="chat.php?user=<?= $conv['user_id'] ?>" 
                                   class="list-group-item list-group-item-action p-4 border-0 border-bottom conv-item transition-all <?= $is_unread ? 'bg-unread' : '' ?>">
                                    <div class="d-flex align-items-center">
                                        
                                        <div class="position-relative">
                                            <img src="<?= !empty($conv['profile_photo']) ? '../uploads/profiles/'.$conv['profile_photo'] : '../assets/img/default-avatar.png' ?>" 
                                                 class="rounded-circle shadow-sm border border-2 border-white" 
                                                 style="width: 60px; height: 60px; object-fit: cover;">
                                            <?php if ($is_unread): ?>
                                                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger border border-2 border-white">
                                                    <?= $conv['unread_count'] ?>
                                                </span>
                                            <?php endif; ?>
                                        </div>

                                        <div class="ms-4 flex-grow-1 min-width-0">
                                            <div class="d-flex justify-content-between align-items-baseline mb-1">
                                                <h6 class="mb-0 fw-bold <?= $is_unread ? 'text-primary' : 'text-dark' ?> fs-5">
                                                    <?= htmlspecialchars($conv['name']) ?>
                                                </h6>
                                                <small class="text-muted">
                                                    <?= formatInboxTime($conv['last_message_time']) ?>
                                                </small>
                                            </div>
                                            <p class="mb-0 text-muted text-truncate-custom <?= $is_unread ? 'fw-bold text-dark' : '' ?>" style="max-width: 90%;">
                                                <?= $is_unread ? '📩 ' : '' ?>
                                                <?= htmlspecialchars($conv['last_msg'] ?? 'No messages yet') ?>
                                            </p>
                                        </div>

                                        <div class="ms-3 text-light">
                                            <i class="bi bi-chevron-right"></i>
                                        </div>

                                    </div>
                                </a>
                            <?php endwhile; ?>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-5 my-5">
                            <div class="mb-4">
                                <span class="display-1 text-light opacity-50">💬</span>
                            </div>
                            <h4 class="text-secondary fw-bold">No conversations yet</h4>
                            <p class="text-muted px-4">Browse listings and contact a provider to start a deal!</p>
                            <a href="../listings/browse.php" class="btn btn-primary rounded-pill px-4 mt-3">Browse Services</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>