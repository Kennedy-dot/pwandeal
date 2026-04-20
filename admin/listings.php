<?php
/**
 * PwanDeal - Manage Listings (Admin)
 */
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once __DIR__ . '/../config/database.php';

// 1. ADMIN ACCESS CONTROL (Assuming ID 1 is Super Admin)
if (!isset($_SESSION['user_id']) || $_SESSION['user_id'] !== 1) {
    header('Location: ../auth/login.php');
    exit();
}

// 2. CSRF PROTECTION
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// 3. CONFIGURATION & PAGINATION
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;
$allowed_statuses = ['active', 'inactive', 'sold', 'archived'];
$status = in_array($_GET['status'] ?? '', $allowed_statuses) ? $_GET['status'] : 'active';

// 4. HANDLE ACTIONS
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("Security token mismatch.");
    }

    $action = $_POST['action'] ?? '';
    $listing_id = (int)($_POST['listing_id'] ?? 0);
    $update_success = false;

    if ($action === 'delete') {
        // Fetch image paths for cleanup before deleting DB records
        $img_stmt = $conn->prepare("SELECT image_url FROM listing_images WHERE listing_id = ?");
        $img_stmt->bind_param("i", $listing_id);
        $img_stmt->execute();
        $images = $img_stmt->get_result();

        $conn->begin_transaction();
        try {
            // Delete images from DB
            $conn->query("DELETE FROM listing_images WHERE listing_id = $listing_id");
            // Delete listing
            $stmt = $conn->prepare("DELETE FROM listings WHERE listing_id = ?");
            $stmt->bind_param("i", $listing_id);
            $stmt->execute();
            
            $conn->commit();
            $update_success = true;

            // Physical file cleanup
            while ($img = $images->fetch_assoc()) {
                $file = __DIR__ . "/../uploads/services/" . $img['image_url'];
                if (file_exists($file)) @unlink($file);
            }
        } catch (Exception $e) {
            $conn->rollback();
        }
    } else {
        $action_map = ['hide' => 'inactive', 'activate' => 'active', 'archive' => 'archived'];
        if (array_key_exists($action, $action_map)) {
            $new_status = $action_map[$action];
            $stmt = $conn->prepare("UPDATE listings SET status = ? WHERE listing_id = ?");
            $stmt->bind_param("si", $new_status, $listing_id);
            $update_success = $stmt->execute();
        }
    }
    
    header("Location: listings.php?status=$status&msg=" . ($update_success ? 'success' : 'error'));
    exit();
}

// 5. FETCH DATA
$count_stmt = $conn->prepare("SELECT COUNT(*) as c FROM listings WHERE status = ?");
$count_stmt->bind_param("s", $status);
$count_stmt->execute();
$total = $count_stmt->get_result()->fetch_assoc()['c'];
$total_pages = ceil($total / $limit);

$sql = "SELECT l.*, c.name as cat, u.name as provider 
        FROM listings l 
        JOIN categories c ON l.category_id = c.category_id 
        JOIN users u ON l.user_id = u.user_id 
        WHERE l.status = ? 
        ORDER BY l.created_at DESC LIMIT ? OFFSET ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("sii", $status, $limit, $offset);
$stmt->execute();
$result = $stmt->get_result();

$page_title = 'Moderation Hub';
include __DIR__ . '/../includes/header.php';
?>

<div class="container-fluid py-4 px-lg-5">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
        <div>
            <h2 class="fw-bold mb-0 text-dark">Moderation Hub</h2>
            <p class="text-muted small">Managing all student listings across Pwani University</p>
        </div>
        <div class="btn-group bg-white p-1 rounded-3 shadow-sm">
            <?php foreach($allowed_statuses as $s): ?>
                <a href="?status=<?= $s ?>" class="btn btn-sm <?= $status === $s ? 'btn-primary shadow-sm' : 'btn-light text-muted' ?> px-3 text-capitalize"><?= $s ?></a>
            <?php endforeach; ?>
        </div>
    </div>

    <?php if (isset($_GET['msg'])): ?>
        <div class="alert alert-<?= $_GET['msg'] === 'success' ? 'success' : 'danger' ?> border-0 shadow-sm py-2">
            <?= $_GET['msg'] === 'success' ? 'Action completed successfully.' : 'An error occurred during the update.' ?>
        </div>
    <?php endif; ?>

    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light">
                    <tr>
                        <th class="ps-4">Listing</th>
                        <th>Provider</th>
                        <th>Price</th>
                        <th class="text-center">Stats</th>
                        <th class="text-end pe-4">Control</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($l = $result->fetch_assoc()): ?>
                        <tr>
                            <td class="ps-4">
                                <div class="fw-bold text-dark mb-0"><?= htmlspecialchars($l['title']) ?></div>
                                <div class="text-muted" style="font-size: 0.75rem;">ID: #<?= $l['listing_id'] ?> • <?= $l['cat'] ?></div>
                            </td>
                            <td>
                                <div class="small"><?= htmlspecialchars($l['provider']) ?></div>
                                <div class="text-muted" style="font-size: 0.7rem;"><?= date('d M Y', strtotime($l['created_at'])) ?></div>
                            </td>
                            <td class="fw-bold">KSh <?= number_format($l['price']) ?></td>
                            <td class="text-center">
                                <span class="badge bg-light text-dark border fw-normal"><?= $l['views'] ?> views</span>
                            </td>
                            <td class="text-end pe-4">
                                <div class="d-flex justify-content-end gap-1">
                                    <a href="../listings/detail.php?id=<?= $l['listing_id'] ?>" class="btn btn-sm btn-light border" title="View"><i class="bi bi-eye"></i></a>
                                    <form method="POST" class="d-inline">
                                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                                        <input type="hidden" name="listing_id" value="<?= $l['listing_id'] ?>">
                                        
                                        <?php if ($status === 'active'): ?>
                                            <button name="action" value="hide" class="btn btn-sm btn-light border text-warning" title="Deactivate"><i class="bi bi-slash-circle"></i></button>
                                            <button name="action" value="archive" class="btn btn-sm btn-light border text-info" title="Archive"><i class="bi bi-archive"></i></button>
                                        <?php else: ?>
                                            <button name="action" value="activate" class="btn btn-sm btn-light border text-success" title="Restore"><i class="bi bi-arrow-counterclockwise"></i></button>
                                        <?php endif; ?>
                                        
                                        <button name="action" value="delete" class="btn btn-sm btn-light border text-danger" onclick="return confirm('PERMANENTLY DELETE?')" title="Nuke"><i class="bi bi-trash"></i></button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
    
    </div>

<style>
    .table thead th { font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.5px; color: #6c757d; font-weight: 700; border: none; }
    .table tbody td { border-bottom: 1px solid #f8f9fa; }
    .btn-light:hover { background: #fff; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
</style>

<?php include __DIR__ . '/../includes/footer.php'; ?>