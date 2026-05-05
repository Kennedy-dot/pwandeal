<?php
/**
 * PwanDeal - Delete Message API
 * Path: messages/delete-message.php
 * 
 * Handles soft deletion of messages (only sender can delete their own)
 * Returns JSON response
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Set JSON header
header('Content-Type: application/json');

// 1. AUTH GUARD
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit();
}

require_once __DIR__ . '/../config/database.php';

$user_id = $_SESSION['user_id'];
$message_id = isset($_POST['message_id']) ? (int)$_POST['message_id'] : 0;

// 2. VALIDATION
if ($message_id === 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid message ID']);
    exit();
}

// 3. VERIFY OWNERSHIP & GET MESSAGE
$stmt = $conn->prepare('SELECT sender_id, receiver_id, message_text FROM messages WHERE message_id = ?');
$stmt->bind_param('i', $message_id);
$stmt->execute();
$message = $stmt->get_result()->fetch_assoc();

if (!$message) {
    http_response_code(404);
    echo json_encode(['success' => false, 'message' => 'Message not found']);
    exit();
}

// 4. SECURITY: Only sender can delete their own message
if ($message['sender_id'] != $user_id) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'You can only delete your own messages']);
    exit();
}

// 5. SOFT DELETE - Mark as deleted but keep record for moderation
$delete_stmt = $conn->prepare('
    UPDATE messages 
    SET is_deleted = 1, 
        deleted_at = NOW(), 
        deleted_by = ?
    WHERE message_id = ?
');

if (!$delete_stmt) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $conn->error]);
    exit();
}

$delete_stmt->bind_param('ii', $user_id, $message_id);

if ($delete_stmt->execute()) {
    http_response_code(200);
    echo json_encode([
        'success' => true,
        'message' => 'Message deleted successfully',
        'message_id' => $message_id
    ]);
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Failed to delete message']);
}

$delete_stmt->close();
$conn->close();
