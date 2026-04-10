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

$page_title = 'Manage Reports';
$base_url = '/pwandeal';

// 2. HANDLE RESOLVE ACTION
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['report_id'])) {
    $report_id = (int)$_POST['report_id'];
    
    // Update report status to 'resolved'
    $stmt = $conn->prepare("UPDATE reports SET status = 'resolved' WHERE report_id = ?");
    $stmt->bind_param('i', $report_id);
    
    if ($stmt->execute()) {
        header('Location: /pwandeal/admin/reports.php?msg=resolved');
        exit();
    }
}

// 3. FETCH PENDING REPORTS
$sql = "SELECT r.*, 
               u1.name as reporter_name, 
               u2.name as reported_user_name 
        FROM reports r
        JOIN users u1 ON r.reporter_id = u1.user_id
        LEFT JOIN users u2 ON r.reported_item_id = u2.user_id AND r.report_type = 'user'
        WHERE r.status = 'pending'
        ORDER BY r.created_at DESC";

$result = $conn->query($sql);

include __DIR__ . '/../includes/header.php';
?>

<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-1" style="color: #1e2761;">🚩 Community Reports</h2>
            <p class="text-muted small mb-0">Review and resolve flagged content or users</p>
        </div>
        <a href="/pwandeal/admin/dashboard.php" class="btn btn-outline-secondary rounded-pill px-3 btn-sm">
            <i class="bi bi-arrow-left"></i> Dashboard
        </a>
    </div>

    <?php if (isset($_GET['msg']) && $_GET['msg'] === 'resolved'): ?>
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm mb-4" role="alert">
            ✅ Report marked as resolved successfully.
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="card shadow-sm border-0 rounded-4 overflow-hidden">
        <div class="card-body p-0">
            <?php if ($result && $result->num_rows > 0): ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="ps-4 py-3 border-0">Date</th>
                                <th class="py-3 border-0">Reporter</th>
                                <th class="py-3 border-0">Type</th>
                                <th class="py-3 border-0">Reason</th>
                                <th class="py-3 border-0 text-end pe-4">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($row = $result->fetch_assoc()): ?>
                                <tr>
                                    <td class="ps-4 small text-muted">
                                        <?= date('M d, Y', strtotime($row['created_at'])) ?><br>
                                        <?= date('H:i', strtotime($row['created_at'])) ?>
                                    </td>
                                    <td>
                                        <span class="fw-bold text-dark"><?= htmlspecialchars($row['reporter_name']) ?></span>
                                    </td>
                                    <td>
                                        <span class="badge rounded-pill <?= $row['report_type'] === 'user' ? 'bg-warning text-dark' : 'bg-info text-white' ?> px-3">
                                            <?= ucfirst($row['report_type']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="text-truncate text-muted" style="max-width: 250px;">
                                            <?= htmlspecialchars($row['reason']) ?>
                                        </div>
                                    </td>
                                    <td class="text-end pe-4">
                                        <div class="btn-group">
                                            <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#viewReport<?= $row['report_id'] ?>">Details</button>
                                            <form method="POST" class="d-inline">
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
                                                <h5 class="fw-bold">Report Investigation</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="mb-3">
                                                    <label class="small text-muted d-block text-uppercase fw-bold">From Reporter</label>
                                                    <p class="mb-0 fw-semibold text-dark"><?= htmlspecialchars($row['reporter_name']) ?></p>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="small text-muted d-block text-uppercase fw-bold">Reported Item/User ID</label>
                                                    <p class="mb-0 fw-semibold">
                                                        <?= ucfirst($row['report_type']) ?> #<?= $row['reported_item_id'] ?>
                                                        <?php if($row['reported_user_name']): ?>
                                                            (<?= htmlspecialchars($row['reported_user_name']) ?>)
                                                        <?php endif; ?>
                                                    </p>
                                                </div>
                                                <hr class="opacity-10">
                                                <div class="mb-2">
                                                    <label class="small text-muted d-block text-uppercase fw-bold">Complaint Reason</label>
                                                    <div class="p-3 bg-light rounded-3 text-dark mt-2" style="white-space: pre-wrap;"><?= htmlspecialchars($row['reason']) ?></div>
                                                </div>
                                            </div>
                                            <div class="modal-footer border-0 pt-0">
                                                <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Close</button>
                                                <a href="/pwandeal/admin/users.php" class="btn btn-warning rounded-pill px-4 fw-bold">Investigate User</a>
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
                    <div class="display-1 text-muted opacity-10 mb-3"><i class="bi bi-shield-check"></i></div>
                    <h4 class="text-muted fw-bold">All clear!</h4>
                    <p class="text-muted">No pending reports require your attention right now.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
    .table-hover tbody tr:hover { background-color: rgba(0,0,0,0.01); }
    .badge { font-weight: 600; font-size: 0.75rem; }
</style>

<?php include __DIR__ . '/../includes/footer.php'; ?>