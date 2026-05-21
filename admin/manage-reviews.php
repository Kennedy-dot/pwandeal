<?php
/**
 * PwanDeal - Manage Reviews (Admin)
 */
ini_set('display_errors', 1);
error_reporting(E_ALL);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 1. Security: Ensure only Super Admin (ID 1) can access this
if (!isset($_SESSION['user_id']) || $_SESSION['user_id'] !== 1) {
    header('Location: /pwandeal/auth/login.php');
    exit();
}

// 2. CSRF Token Generation
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

require_once __DIR__ . '/../config/database.php';
$page_title = 'Review Management';
include __DIR__ . '/../includes/header.php'; 
?>

<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold m-0">All Platform Reviews</h2>
        <span class="badge bg-secondary px-3 py-2 rounded-pill">Admin Panel</span>
    </div>
    
    <div class="card border-0 shadow-sm rounded-3 overflow-hidden">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light text-uppercase fs-7 fw-semibold text-muted">
                    <tr>
                        <th class="ps-4">Recipient</th>
                        <th>Sender</th>
                        <th>Rating</th>
                        <th>Comment</th>
                        <th>Date</th>
                        <th class="text-end pe-4">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // Using LEFT JOINs to handle deleted users gracefully
                    $sql = "SELECT r.*, 
                                   u1.name as recipient_name, 
                                   u2.name as sender_name 
                            FROM reviews r
                            LEFT JOIN users u1 ON r.to_user_id = u1.user_id
                            LEFT JOIN users u2 ON r.from_user_id = u2.user_id
                            ORDER BY r.created_at DESC";
                    
                    $result = $conn->query($sql);

                    if ($result && $result->num_rows > 0):
                        while ($row = $result->fetch_assoc()): 
                            $comment = $row['comment'] ?? '';
                            $short_comment = mb_strlen($comment) > 50 ? mb_substr($comment, 0, 50) . '...' : $comment;
                    ?>
                        <tr>
                            <td class="ps-4">
                                <span class="fw-medium text-dark"><?= htmlspecialchars($row['recipient_name'] ?? 'Deleted User') ?></span>
                            </td>
                            <td><?= htmlspecialchars($row['sender_name'] ?? 'Anonymous') ?></td>
                            <td>
                                <span class="badge bg-warning text-dark d-inline-flex align-items-center gap-1">
                                    <i class="bi bi-star-fill small"></i> <?= number_format($row['rating'], 1) ?>
                                </span>
                            </td>
                            <td>
                                <span class="text-muted" title="<?= htmlspecialchars($comment) ?>">
                                    <?= htmlspecialchars($short_comment) ?>
                                </span>
                            </td>
                            <td>
                                <span class="text-secondary font-monospace small">
                                    <?= date('M j, Y', strtotime($row['created_at'])) ?>
                                </span>
                            </td>
                            <td class="text-end pe-4">
                                <form action="delete-review.php" method="POST" class="d-inline execution-form">
                                    <input type="hidden" name="review_id" value="<?= $row['review_id'] ?>">
                                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                                    <button type="submit" class="btn btn-sm btn-outline-danger rounded-2 btn-delete" title="Delete Review">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php 
                        endwhile; 
                    else: 
                    ?>
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">
                                <i class="bi bi-chat-square-text display-6 d-block mb-2 text-black-50"></i>
                                No reviews found on the platform.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modern Confirmation Modal -->
<div class="modal fade" id="deleteConfirmModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-0 bg-light">
                <h5 class="modal-title fw-bold text-danger">Confirm Deletion</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body py-4">
                Are you completely sure you want to permanently delete this review? This action cannot be undone.
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button type="button" id="confirmDeleteBtn" class="btn btn-danger px-4">Delete</button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const deleteModal = new bootstrap.Modal(document.getElementById('deleteConfirmModal'));
    let formToSubmit = null;

    document.querySelectorAll('.btn-delete').forEach(button => {
        button.addEventListener('click', function (e) {
            e.preventDefault();
            formToSubmit = this.closest('form');
            deleteModal.show();
        });
    });

    document.getElementById('confirmDeleteBtn').addEventListener('click', function () {
        if (formToSubmit) {
            formToSubmit.submit();
        }
    });
});
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>