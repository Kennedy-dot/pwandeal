<?php
/**
 * PwanDeal - Manage Listings (Admin)
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

// 2. CONFIGURATION & PAGINATION
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;
$status = $_GET['status'] ?? 'active';

// 3. HANDLE ACTIONS (Delete, Hide, Activate)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $listing_id = (int)($_POST['listing_id'] ?? 0);
    
    if ($action === 'delete') {
        $stmt = $conn->prepare("DELETE FROM listings WHERE listing_id = ?");
        $stmt->bind_param("i", $listing_id);
        $stmt->execute();
    } elseif ($action === 'hide') {
        $stmt = $conn->prepare("UPDATE listings SET status = 'inactive' WHERE listing_id = ?");
        $stmt->bind_param("i", $listing_id);
        $stmt->execute();
    } elseif ($action === 'activate') {
        $stmt = $conn->prepare("UPDATE listings SET status = 'active' WHERE listing_id = ?");
        $stmt->bind_param("i", $listing_id);
        $stmt->execute();
    }
    
    header('Location: /pwandeal/admin/listings.php?status=' . $status . '&msg=updated');
    exit();
}

// 4. FETCH DATA
// Count total for pagination
$count_sql = "SELECT COUNT(*) as c FROM listings WHERE status = ?";
$count_stmt = $conn->prepare($count_sql);
$count_stmt->bind_param("s", $status);
$count_stmt->execute();
$total = $count_stmt->get_result()->fetch_assoc()['c'];
$total_pages = ceil($total / $limit);

// Get listings with Category and User names
$sql = "SELECT l.*, c.name as cat, u.name as provider 
        FROM listings l 
        JOIN categories c ON l.category_id = c.category_id 
        JOIN users u ON l.user_id = u.user_id 
        WHERE l.status = ? 
        ORDER BY l.created_at DESC 
        LIMIT ? OFFSET ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("sii", $status, $limit, $offset);
$stmt->execute();
$result = $stmt->get_result();

$page_title = 'Manage Listings';
$base_url = '/pwandeal';
include __DIR__ . '/../includes/header.php';
?>

<div class="container py-5">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
        <div>
            <h2 class="fw-bold mb-1" style="color: #1e2761;">📋 Manage Listings</h2>
            <p class="text-muted small mb-0">Overview of all platform services and items</p>
        </div>
        <div class="btn-group shadow-sm rounded-pill overflow-hidden">
            <a href="?status=active" class="btn <?= $status === 'active' ? 'btn-primary' : 'btn-outline-primary' ?> px-4">Active</a>
            <a href="?status=inactive" class="btn <?= $status === 'inactive' ? 'btn-warning' : 'btn-outline-warning' ?> px-4">Inactive</a>
            <a href="?status=sold" class="btn <?= $status === 'sold' ? 'btn-success' : 'btn-outline-success' ?> px-4">Sold</a>
        </div>
    </div>

    <div class="card shadow-sm border-0 rounded-4 overflow-hidden">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4 py-3 border-0">Service / Item</th>
                            <th class="py-3 border-0">Provider</th>
                            <th class="py-3 border-0">Category</th>
                            <th class="py-3 border-0">Price</th>
                            <th class="py-3 border-0 text-center">Stats</th>
                            <th class="py-3 border-0 text-end pe-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($result->num_rows > 0): ?>
                            <?php while ($l = $result->fetch_assoc()): ?>
                                <tr>
                                    <td class="ps-4 py-3">
                                        <div class="fw-bold text-dark"><?= htmlspecialchars(substr($l['title'], 0, 40)) ?><?= strlen($l['title']) > 40 ? '...' : '' ?></div>
                                        <small class="text-muted">ID: #<?= $l['listing_id'] ?></small>
                                    </td>
                                    <td>
                                        <div class="small fw-semibold"><?= htmlspecialchars($l['provider']) ?></div>
                                    </td>
                                    <td>
                                        <span class="badge rounded-pill bg-light text-dark border px-3"><?= htmlspecialchars($l['cat']) ?></span>
                                    </td>
                                    <td class="fw-bold text-primary">KSh <?= number_format($l['price']) ?></td>
                                    <td class="text-center">
                                        <span class="small text-muted"><i class="bi bi-eye"></i> <?= number_format($l['views']) ?></span>
                                    </td>
                                    <td class="text-end pe-4">
                                        <div class="btn-group">
                                            <a href="/pwandeal/listings/detail.php?id=<?= $l['listing_id'] ?>" class="btn btn-sm btn-outline-secondary">View</a>
                                            
                                            <?php if ($l['status'] === 'active'): ?>
                                                <form method="POST" class="d-inline" onsubmit="return confirm('Hide this listing from public view?');">
                                                    <input type="hidden" name="action" value="hide">
                                                    <input type="hidden" name="listing_id" value="<?= $l['listing_id'] ?>">
                                                    <button class="btn btn-sm btn-outline-warning">Hide</button>
                                                </form>
                                            <?php else: ?>
                                                <form method="POST" class="d-inline">
                                                    <input type="hidden" name="action" value="activate">
                                                    <input type="hidden" name="listing_id" value="<?= $l['listing_id'] ?>">
                                                    <button class="btn btn-sm btn-outline-success">Restore</button>
                                                </form>
                                            <?php endif; ?>

                                            <form method="POST" class="d-inline" onsubmit="return confirm('Permanently delete this listing? This cannot be undone.');">
                                                <input type="hidden" name="action" value="delete">
                                                <input type="hidden" name="listing_id" value="<?= $l['listing_id'] ?>">
                                                <button class="btn btn-sm btn-outline-danger">Delete</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center py-5">
                                    <div class="py-4">
                                        <i class="bi bi-box-seam display-4 text-muted opacity-25"></i>
                                        <p class="text-muted mt-2">No <?= $status ?> listings found.</p>
                                    </div>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <?php if ($total_pages > 1): ?>
        <nav class="mt-4">
            <ul class="pagination justify-content-center">
                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                        <a class="page-link shadow-sm border-0 mx-1 rounded" href="?status=<?= $status ?>&page=<?= $i ?>"><?= $i ?></a>
                    </li>
                <?php endfor; ?>
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
    .table-hover tbody tr:hover { background-color: rgba(2, 128, 144, 0.03); }
    .page-link { color: #028090; }
    .page-item.active .page-link { background-color: #028090; }
</style>

<?php include __DIR__ . '/../includes/footer.php'; ?>