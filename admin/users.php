<?php
/**
 * PwanDeal - Manage Users (Admin)
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/database.php';

// 1. ADMIN ACCESS CONTROL
if (!isset($_SESSION['user_id']) || $_SESSION['user_id'] !== 1) {
    header('Location: /pwandeal/auth/login.php');
    exit();
}

$page_title = 'Manage Users';
$base_url = '/pwandeal';

// 2. HANDLE ACTIONS (Suspend/Unsuspend)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $user_id = (int)($_POST['user_id'] ?? 0);
    
    // Safety check: Prevent the Super Admin (ID 1) from suspending themselves
    if ($user_id > 1) {
        if ($action === 'suspend') {
            $reason = trim($_POST['reason'] ?? 'No reason provided.');
            $stmt = $conn->prepare('UPDATE users SET is_suspended = 1, suspension_reason = ? WHERE user_id = ?');
            $stmt->bind_param('si', $reason, $user_id);
            $stmt->execute();
        } elseif ($action === 'unsuspend') {
            $stmt = $conn->prepare('UPDATE users SET is_suspended = 0, suspension_reason = NULL WHERE user_id = ?');
            $stmt->bind_param('i', $user_id);
            $stmt->execute();
        }
    }
    
    header('Location: /pwandeal/admin/users.php?msg=success');
    exit();
}

// 3. PAGINATION LOGIC
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

$total_result = $conn->query("SELECT COUNT(*) as c FROM users");
$total = $total_result->fetch_assoc()['c'];
$total_pages = ceil($total / $limit);

// 4. FETCH USERS
$sql = "SELECT * FROM users ORDER BY created_at DESC LIMIT ? OFFSET ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $limit, $offset);
$stmt->execute();
$result = $stmt->get_result();

include __DIR__ . '/../includes/header.php';
?>

<div class="container py-5">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
        <div>
            <h2 class="fw-bold mb-1" style="color: #1e2761;">👥 User Management</h2>
            <p class="text-muted small mb-0">Monitor accounts, verify students, and handle suspensions</p>
        </div>
        <span class="badge bg-primary rounded-pill px-4 py-2 fs-6 shadow-sm">Total: <?= number_format($total) ?></span>
    </div>

    <?php if (isset($_GET['msg']) && $_GET['msg'] === 'success'): ?>
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm rounded-3 mb-4" role="alert">
            ✅ Action processed successfully.
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4 py-3 border-0">ID</th>
                            <th class="py-3 border-0">User Details</th>
                            <th class="py-3 border-0">School</th>
                            <th class="py-3 border-0">Status</th>
                            <th class="py-3 border-0">Rating</th>
                            <th class="py-3 border-0">Joined</th>
                            <th class="text-end pe-4 py-3 border-0">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($result->num_rows > 0): ?>
                            <?php while ($u = $result->fetch_assoc()): ?>
                                <tr>
                                    <td class="ps-4 text-muted small">#<?= $u['user_id'] ?></td>
                                    <td>
                                        <div class="fw-bold text-dark"><?= htmlspecialchars($u['name']) ?></div>
                                        <div class="text-muted small"><?= htmlspecialchars($u['email']) ?></div>
                                    </td>
                                    <td><span class="small"><?= htmlspecialchars($u['school'] ?? 'N/A') ?></span></td>
                                    <td>
                                        <?php if ($u['is_suspended']): ?>
                                            <span class="badge rounded-pill bg-danger-subtle text-danger px-3 border border-danger">Suspended</span>
                                        <?php elseif (isset($u['is_verified']) && $u['is_verified']): ?>
                                            <span class="badge rounded-pill bg-success-subtle text-success px-3 border border-success">Verified</span>
                                        <?php else: ?>
                                            <span class="badge rounded-pill bg-light text-dark px-3 border">Standard</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><span class="text-warning">★</span> <?= number_format($u['average_rating'] ?? 0, 1) ?></td>
                                    <td class="small text-muted"><?= date('M d, Y', strtotime($u['created_at'])) ?></td>
                                    <td class="text-end pe-4">
                                        <div class="btn-group">
                                            <a href="/pwandeal/profile/view.php?id=<?= $u['user_id'] ?>" class="btn btn-sm btn-outline-secondary">Profile</a>
                                            
                                            <?php if ($u['user_id'] !== 1): ?>
                                                <?php if ($u['is_suspended']): ?>
                                                    <form method="POST" class="d-inline">
                                                        <input type="hidden" name="action" value="unsuspend">
                                                        <input type="hidden" name="user_id" value="<?= $u['user_id'] ?>">
                                                        <button type="submit" class="btn btn-sm btn-success px-3" onclick="return confirm('Restore user access?');">Unsuspend</button>
                                                    </form>
                                                <?php else: ?>
                                                    <button class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#suspendModal<?= $u['user_id'] ?>">
                                                        Suspend
                                                    </button>
                                                <?php endif; ?>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>

                                <?php if (!$u['is_suspended']): ?>
                                <div class="modal fade" id="suspendModal<?= $u['user_id'] ?>" tabindex="-1">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content border-0 shadow-lg rounded-4">
                                            <div class="modal-header border-0 pb-0">
                                                <h5 class="fw-bold text-danger">Suspend Account</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <form method="POST">
                                                <div class="modal-body">
                                                    <p class="text-muted">You are restricting <strong><?= htmlspecialchars($u['name']) ?></strong> from accessing the platform. They will be unable to log in until unsuspended.</p>
                                                    <input type="hidden" name="action" value="suspend">
                                                    <input type="hidden" name="user_id" value="<?= $u['user_id'] ?>">
                                                    <div class="mb-3">
                                                        <label class="form-label small fw-bold text-uppercase">Reason for Suspension</label>
                                                        <textarea name="reason" class="form-control bg-light" rows="3" placeholder="e.g. Repeated scam reports, Terms of Service violation..." required></textarea>
                                                    </div>
                                                </div>
                                                <div class="modal-footer border-0 pt-0">
                                                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                                                    <button type="submit" class="btn btn-danger rounded-pill px-4 fw-bold shadow-sm">Confirm Suspension</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                                <?php endif; ?>
                                
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="7" class="text-center py-5 text-muted">No users found in the system.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <?php if ($total_pages > 1): ?>
        <nav class="mt-4">
            <ul class="pagination justify-content-center">
                <li class="page-item <?= ($page <= 1) ? 'disabled' : '' ?>">
                    <a class="page-link border-0 shadow-sm mx-1 rounded" href="?page=<?= $page - 1 ?>">Previous</a>
                </li>
                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <li class="page-item <?= ($i == $page) ? 'active' : '' ?>">
                        <a class="page-link border-0 shadow-sm mx-1 rounded" href="?page=<?= $i ?>"><?= $i ?></a>
                    </li>
                <?php endfor; ?>
                <li class="page-item <?= ($page >= $total_pages) ? 'disabled' : '' ?>">
                    <a class="page-link border-0 shadow-sm mx-1 rounded" href="?page=<?= $page + 1 ?>">Next</a>
                </li>
            </ul>
        </nav>
    <?php endif; ?>
    
    <div class="mt-4">
        <a href="/pwandeal/admin/dashboard.php" class="btn btn-link text-decoration-none text-muted p-0">
            <i class="bi bi-arrow-left"></i> Back to Dashboard
        </a>
    </div>
</div>

<style>
    .table-hover tbody tr:hover { background-color: rgba(30, 39, 97, 0.02); }
    .page-item.active .page-link { background-color: #1e2761; color: white; }
    .page-link { color: #1e2761; }
</style>

<?php include __DIR__ . '/../includes/footer.php'; ?>