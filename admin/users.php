<?php
/**
 * PwanDeal - Manage Users (Admin)
 * Updated with CSRF security, Search, and Enhanced UI
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

// 2. CSRF PROTECTION
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// 3. HANDLE ACTIONS
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("Invalid security token.");
    }

    $action = $_POST['action'] ?? '';
    $user_id = (int)($_POST['user_id'] ?? 0);
    
    if ($user_id > 1) { // Protect Super Admin
        if ($action === 'suspend') {
            $reason = trim($_POST['reason'] ?? 'Violation of community guidelines.');
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

// 4. SEARCH & PAGINATION LOGIC
$search = trim($_GET['search'] ?? '');
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

$search_query = $search ? "WHERE name LIKE ? OR email LIKE ?" : "";
$count_sql = "SELECT COUNT(*) as c FROM users $search_query";
$count_stmt = $conn->prepare($count_sql);

if ($search) {
    $search_param = "%$search%";
    $count_stmt->bind_param("ss", $search_param, $search_param);
}
$count_stmt->execute();
$total = $count_stmt->get_result()->fetch_assoc()['c'];
$total_pages = ceil($total / $limit);

// 5. FETCH USERS
$sql = "SELECT * FROM users $search_query ORDER BY created_at DESC LIMIT ? OFFSET ?";
$stmt = $conn->prepare($sql);

if ($search) {
    $stmt->bind_param("ssii", $search_param, $search_param, $limit, $offset);
} else {
    $stmt->bind_param("ii", $limit, $offset);
}
$stmt->execute();
$result = $stmt->get_result();

$page_title = 'Manage Users';
include __DIR__ . '/../includes/header.php';
?>

<div class="container py-5">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
        <div>
            <h2 class="fw-bold mb-1" style="color: #1e2761;">👥 User Management</h2>
            <p class="text-muted small mb-0">Platform governance and account moderation</p>
        </div>
        <form class="d-flex gap-2" method="GET">
            <div class="input-group input-group-sm">
                <input type="text" name="search" class="form-control rounded-start-pill ps-3" placeholder="Search name or email..." value="<?= htmlspecialchars($search) ?>">
                <button class="btn btn-primary rounded-end-pill px-3" type="submit"><i class="bi bi-search"></i></button>
            </div>
            <?php if($search): ?>
                <a href="/pwandeal/admin/users.php" class="btn btn-sm btn-outline-secondary rounded-pill px-3">Clear</a>
            <?php endif; ?>
        </form>
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
                            <th class="ps-4 py-3 border-0">User</th>
                            <th class="py-3 border-0">Details</th>
                            <th class="py-3 border-0">Status</th>
                            <th class="py-3 border-0">Rating</th>
                            <th class="text-end pe-4 py-3 border-0">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($result->num_rows > 0): ?>
                            <?php while ($u = $result->fetch_assoc()): ?>
                                <tr>
                                    <td class="ps-4">
                                        <div class="fw-bold text-dark"><?= htmlspecialchars($u['name']) ?></div>
                                        <div class="text-muted small"><?= htmlspecialchars($u['email']) ?></div>
                                    </td>
                                    <td>
                                        <div class="small text-muted"><i class="bi bi-mortarboard me-1"></i><?= htmlspecialchars($u['school'] ?? 'No School') ?></div>
                                        <div class="small text-muted"><i class="bi bi-calendar-event me-1"></i>Joined <?= date('M Y', strtotime($u['created_at'])) ?></div>
                                    </td>
                                    <td>
                                        <?php if ($u['is_suspended']): ?>
                                            <span class="badge rounded-pill bg-danger-subtle text-danger px-3 border border-danger">Suspended</span>
                                        <?php elseif (isset($u['is_verified']) && $u['is_verified']): ?>
                                            <span class="badge rounded-pill bg-success-subtle text-success px-3 border border-success">Verified</span>
                                        <?php else: ?>
                                            <span class="badge rounded-pill bg-light text-dark px-3 border">Standard</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="text-warning small"><i class="bi bi-star-fill"></i></span> 
                                        <span class="fw-semibold"><?= number_format($u['average_rating'] ?? 0, 1) ?></span>
                                    </td>
                                    <td class="text-end pe-4">
                                        <div class="btn-group shadow-sm">
                                            <a href="/pwandeal/profile/view.php?id=<?= $u['user_id'] ?>" class="btn btn-sm btn-white border">View</a>
                                            
                                            <?php if ($u['user_id'] !== 1): ?>
                                                <?php if ($u['is_suspended']): ?>
                                                    <form method="POST" class="d-inline">
                                                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                                                        <input type="hidden" name="action" value="unsuspend">
                                                        <input type="hidden" name="user_id" value="<?= $u['user_id'] ?>">
                                                        <button type="submit" class="btn btn-sm btn-success px-3" onclick="return confirm('Restore user access?');">Unsuspend</button>
                                                    </form>
                                                <?php else: ?>
                                                    <button class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#suspendModal<?= $u['user_id'] ?>">Suspend</button>
                                                <?php endif; ?>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>

                                <?php if (!$u['is_suspended'] && $u['user_id'] !== 1): ?>
                                <div class="modal fade" id="suspendModal<?= $u['user_id'] ?>" tabindex="-1">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content border-0 shadow rounded-4">
                                            <div class="modal-header border-0">
                                                <h5 class="fw-bold text-danger mb-0">Restriction Action</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <form method="POST">
                                                <div class="modal-body pt-0">
                                                    <p class="text-muted small">Suspending <strong><?= htmlspecialchars($u['name']) ?></strong> will immediately revoke their access to PwanDeal.</p>
                                                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                                                    <input type="hidden" name="action" value="suspend">
                                                    <input type="hidden" name="user_id" value="<?= $u['user_id'] ?>">
                                                    <div class="mb-3">
                                                        <label class="form-label small fw-bold text-uppercase text-muted">Reason for suspension</label>
                                                        <textarea name="reason" class="form-control bg-light border-0" rows="3" placeholder="Scamming, Harassment, etc." required></textarea>
                                                    </div>
                                                </div>
                                                <div class="modal-footer border-0">
                                                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                                                    <button type="submit" class="btn btn-danger rounded-pill px-4 fw-bold">Confirm</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                                <?php endif; ?>
                                
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="5" class="text-center py-5 text-muted">No users matching your criteria.</td></tr>
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
                    <a class="page-link border-0 shadow-sm mx-1 rounded" href="?page=<?= $page - 1 ?>&search=<?= urlencode($search) ?>">Prev</a>
                </li>
                <li class="page-item active"><span class="page-link border-0 shadow-sm mx-1 rounded"><?= $page ?> of <?= $total_pages ?></span></li>
                <li class="page-item <?= ($page >= $total_pages) ? 'disabled' : '' ?>">
                    <a class="page-link border-0 shadow-sm mx-1 rounded" href="?page=<?= $page + 1 ?>&search=<?= urlencode($search) ?>">Next</a>
                </li>
            </ul>
        </nav>
    <?php endif; ?>
</div>

<style>
    .btn-white { background: #fff; color: #6c757d; }
    .btn-white:hover { background: #f8f9fa; color: #1e2761; }
    .table-hover tbody tr:hover { background-color: rgba(30, 39, 97, 0.02); }
    .page-item.active .page-link { background-color: #1e2761; border-color: #1e2761; }
</style>

<?php include __DIR__ . '/../includes/footer.php'; ?>