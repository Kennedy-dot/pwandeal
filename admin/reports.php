<?php
/**
 * PwanDeal - Manage Reports (Admin)
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

// 3. HANDLE RESOLVE ACTION
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("Invalid security token.");
    }

    if (isset($_POST['report_id'])) {
        $report_id = (int)$_POST['report_id'];
        $stmt = $conn->prepare("UPDATE reports SET status = 'resolved' WHERE report_id = ?");
        $stmt->bind_param('i', $report_id);
        
        $msg = $stmt->execute() ? 'resolved' : 'error';
        header("Location: /pwandeal/admin/reports.php?msg=$msg");
        exit();
    }
}

// 4. FETCH PENDING REPORTS
$sql = "SELECT r.*, 
               u1.name as reporter_name, 
               u2.name as reported_user_name,
               l.title as reported_listing_title
        FROM reports r
        JOIN users u1 ON r.reporter_id = u1.user_id
        LEFT JOIN users u2 ON (r.target_type = 'user' AND r.target_id = u2.user_id)
        LEFT JOIN listings l ON (r.target_type = 'listing' AND r.target_id = l.listing_id)
        WHERE r.status = 'pending'
        ORDER BY r.created_at DESC";

$result = $conn->query($sql);

$page_title = 'Manage Reports';
include __DIR__ . '/../includes/header.php';
?>

<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-1" style="color: #1e2761;">🚩 Community Reports</h2>
            <p class="text-muted small mb-0">Moderation queue for Pwani University community</p>
        </div>
        <a href="/pwandeal/admin/dashboard.php" class="btn btn-outline-secondary rounded-pill px-3 btn-sm">
            <i class="bi bi-arrow-left"></i> Dashboard
        </a>
    </div>

    <?php if (isset($_GET['msg'])): ?>
        <div class="alert alert-<?= $_GET['msg'] === 'resolved' ? 'success' : 'danger' ?> border-0 shadow-sm mb-4" role="alert">
            <?= $_GET['msg'] === 'resolved' ? '✅ Report marked as resolved.' : '❌ Error updating report.' ?>
            <button type="button" class="btn-close float-end" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="card shadow-sm border-0 rounded-4 overflow-hidden">
        <div class="card-body p-0">
            <?php if ($result && $result->num_rows > 0): ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="ps-4 py-3 border-0">Timestamp</th>
                                <th class="py-3 border-0">Reporter</th>
                                <th class="py-3 border-0">Target Type</th>
                                <th class="py-3 border-0">Reason</th>
                                <th class="py-3 border-0 text-end pe-4">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($row = $result->fetch_assoc()): ?>
                                <tr>
                                    <td class="ps-4 small text-muted"><?= date('M d, H:i', strtotime($row['created_at'])) ?></td>
                                    <td><span class="fw-bold text-dark"><?= htmlspecialchars($row['reporter_name']) ?></span></td>
                                    <td>
                                        <span class="badge rounded-pill <?= $row['target_type'] === 'user' ? 'bg-warning text-dark' : 'bg-info text-white' ?> px-3">
                                            <?= ucfirst($row['target_type']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="text-truncate text-muted" style="max-width: 200px;"><?= htmlspecialchars($row['reason']) ?></div>
                                    </td>
                                    <td class="text-end pe-4">
                                        <div class="btn-group">
                                            <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#viewReport<?= $row['report_id'] ?>">Details</button>
                                            <form method="POST" class="d-inline" onsubmit="return confirm('Mark as resolved?');">
                                                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                                                <input type="hidden" name="report_id" value="<?= $row['report_id'] ?>">
                                                <button type="submit" class="btn btn-sm btn-success px-3">Resolve</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>

                                <div class="modal fade" id="viewReport<?= $row['report_id'] ?>" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content border-0 shadow-lg rounded-4">
                                            <div class="modal-header border-0 pb-0">
                                                <h5 class="fw-bold">Report Details</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="row g-3 mb-3">
                                                    <div class="col-6">
                                                        <label class="small text-muted d-block text-uppercase fw-bold">From</label>
                                                        <p class="mb-0 fw-semibold"><?= htmlspecialchars($row['reporter_name']) ?></p>
                                                    </div>
                                                    <div class="col-6">
                                                        <label class="small text-muted d-block text-uppercase fw-bold">Target ID</label>
                                                        <p class="mb-0 fw-semibold text-danger"><?= ucfirst($row['target_type']) ?> #<?= $row['target_id'] ?></p>
                                                    </div>
                                                </div>
                                                <div class="p-3 bg-light rounded-3 mb-3">
                                                    <label class="small text-muted d-block text-uppercase fw-bold mb-1">Reason</label>
                                                    <p class="mb-0 text-dark"><?= htmlspecialchars($row['reason']) ?></p>
                                                </div>
                                                <?php if($row['target_type'] === 'listing' && $row['reported_listing_title']): ?>
                                                    <div class="alert alert-info border-0 py-2 small">Listing: <strong><?= htmlspecialchars($row['reported_listing_title']) ?></strong></div>
                                                <?php elseif($row['target_type'] === 'user' && $row['reported_user_name']): ?>
                                                    <div class="alert alert-warning border-0 py-2 small">User: <strong><?= htmlspecialchars($row['reported_user_name']) ?></strong></div>
                                                <?php endif; ?>
                                            </div>
                                            <div class="modal-footer border-0 pt-0">
                                                <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Close</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="text-center py-5">
                    <i class="bi bi-shield-check display-1 text-muted opacity-25"></i>
                    <h4 class="text-muted fw-bold mt-3">Moderation queue empty</h4>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>