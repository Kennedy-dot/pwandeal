<?php
/**
 * PwanDeal - Service Details View
 */
session_start();
require_once __DIR__ . '/../config/database.php';

$listing_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($listing_id === 0) {
    header('Location: browse.php');
    exit();
}

// 1. FETCH DATA (Joined with listing_images for the primary photo)
$stmt = $conn->prepare("
    SELECT l.*, c.name as category_name, i.image_url,
           u.name as provider_name, u.email, u.profile_photo, u.school, 
           u.average_rating, u.total_reviews, u.total_listings
    FROM listings l 
    JOIN categories c ON l.category_id = c.category_id 
    JOIN users u ON l.user_id = u.user_id 
    LEFT JOIN listing_images i ON l.listing_id = i.listing_id AND i.is_primary = 1
    WHERE l.listing_id = ?
");
$stmt->bind_param('i', $listing_id);
$stmt->execute();
$listing = $stmt->get_result()->fetch_assoc();

if (!$listing) {
    die("<div class='container py-5 text-center'><h1>Listing not found!</h1><a href='browse.php' class='btn btn-primary'>Back to browse</a></div>");
}

// 2. VIEW COUNT LOGIC
if (!isset($_SESSION['viewed_listings'])) { $_SESSION['viewed_listings'] = []; }
if (!in_array($listing_id, $_SESSION['viewed_listings'])) {
    $conn->query("UPDATE listings SET views = views + 1 WHERE listing_id = $listing_id");
    $_SESSION['viewed_listings'][] = $listing_id;
}

// 3. RECENT REVIEWS
$rev_stmt = $conn->prepare("SELECT r.*, u.name as reviewer_name FROM reviews r JOIN users u ON r.from_user_id = u.user_id WHERE r.to_user_id = ? ORDER BY r.created_at DESC LIMIT 3");
$rev_stmt->bind_param('i', $listing['user_id']);
$rev_stmt->execute();
$reviews = $rev_stmt->get_result();

// 4. RELATED LISTINGS (Same category, excluding current)
$related_stmt = $conn->prepare("SELECT l.*, i.image_url FROM listings l LEFT JOIN listing_images i ON l.listing_id = i.listing_id AND i.is_primary = 1 WHERE l.category_id = ? AND l.listing_id != ? AND l.status = 'active' LIMIT 3");
$related_stmt->bind_param('ii', $listing['category_id'], $listing_id);
$related_stmt->execute();
$related_items = $related_stmt->get_result();

$page_title = $listing['title'];
include __DIR__ . '/../includes/header.php';
?>

<div class="container py-4">
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="../index.php">Home</a></li>
            <li class="breadcrumb-item"><a href="browse.php">Marketplace</a></li>
            <li class="breadcrumb-item active text-truncate" style="max-width: 250px;"><?= htmlspecialchars($listing['title']) ?></li>
        </ol>
    </nav>

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
                <div class="position-relative">
                    <?php $main_img = !empty($listing['image_url']) ? "../uploads/services/".$listing['image_url'] : "../assets/img/service-placeholder.jpg"; ?>
                    <img src="<?= $main_img ?>" class="img-fluid w-100" style="height: 450px; object-fit: cover;" alt="Service">
                    <div class="position-absolute bottom-0 start-0 m-3">
                        <span class="badge bg-dark bg-opacity-75 px-3 py-2 rounded-pill"><i class="bi bi-eye me-1"></i> <?= $listing['views'] ?> views</span>
                    </div>
                </div>
                
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <span class="badge bg-primary-subtle text-primary mb-2"><?= htmlspecialchars($listing['category_name']) ?></span>
                            <h1 class="fw-bold h2 mb-1"><?= htmlspecialchars($listing['title']) ?></h1>
                            <p class="text-muted small"><i class="bi bi-calendar3 me-1"></i> Posted on <?= date('M d, Y', strtotime($listing['created_at'])) ?></p>
                        </div>
                        <div class="text-end">
                            <div class="h2 fw-bold text-primary mb-0">KSh <?= number_format($listing['price']) ?></div>
                            <span class="text-muted small">Fixed Price</span>
                        </div>
                    </div>

                    <hr class="my-4 opacity-10">

                    <h5 class="fw-bold mb-3">Service Description</h5>
                    <div class="text-secondary mb-4" style="line-height: 1.8; white-space: pre-line;">
                        <?= htmlspecialchars($listing['description']) ?>
                    </div>

                    <div class="d-flex gap-2">
                        <button class="btn btn-light btn-sm rounded-pill px-3 border" onclick="copyLink(this)">
                            <i class="bi bi-share me-1"></i> Copy Link
                        </button>
                    </div>
                </div>
            </div>

            <?php if ($related_items->num_rows > 0): ?>
            <h5 class="fw-bold mb-3">More in <?= htmlspecialchars($listing['category_name']) ?></h5>
            <div class="row g-3">
                <?php while($item = $related_items->fetch_assoc()): ?>
                <div class="col-md-4">
                    <a href="detail.php?id=<?= $item['listing_id'] ?>" class="text-decoration-none text-dark">
                        <div class="card border-0 shadow-sm rounded-3 h-100">
                            <img src="<?= !empty($item['image_url']) ? '../uploads/services/'.$item['image_url'] : '../assets/img/service-placeholder.jpg' ?>" class="card-img-top rounded-top-3" style="height: 120px; object-fit: cover;">
                            <div class="card-body p-2">
                                <h6 class="small fw-bold mb-1 text-truncate"><?= htmlspecialchars($item['title']) ?></h6>
                                <div class="text-primary small fw-bold">KSh <?= number_format($item['price']) ?></div>
                            </div>
                        </div>
                    </a>
                </div>
                <?php endwhile; ?>
            </div>
            <?php endif; ?>
        </div>

        <div class="col-lg-4">
            <div class="card border-0 shadow-sm rounded-4 sticky-top" style="top: 90px;">
                <div class="card-body p-4">
                    <div class="text-center mb-4">
                        <img src="<?= $listing['profile_photo'] ? '../uploads/profiles/'.$listing['profile_photo'] : '../assets/img/default-avatar.png' ?>" 
                             class="rounded-circle border border-4 border-white shadow-sm mb-3" 
                             style="width: 80px; height: 80px; object-fit: cover;">
                        <h5 class="fw-bold mb-0"><?= htmlspecialchars($listing['provider_name']) ?></h5>
                        <p class="text-muted small mb-3"><?= htmlspecialchars($listing['school'] ?? 'Pwani University Student') ?></p>
                        
                        <div class="d-flex justify-content-center gap-3 py-2 bg-light rounded-3">
                            <div class="text-center">
                                <div class="fw-bold"><?= number_format($listing['average_rating'], 1) ?> <i class="bi bi-star-fill text-warning small"></i></div>
                                <div class="small text-muted" style="font-size: 0.7rem;">Rating</div>
                            </div>
                            <div class="vr text-muted"></div>
                            <div class="text-center">
                                <div class="fw-bold"><?= $listing['total_listings'] ?></div>
                                <div class="small text-muted" style="font-size: 0.7rem;">Services</div>
                            </div>
                        </div>
                    </div>

                    <div class="d-grid gap-2">
                        <?php if (isset($_SESSION['user_id'])): ?>
                            <?php if ($_SESSION['user_id'] != $listing['user_id']): ?>
                                <a href="../messages/chat.php?seller_id=<?= $listing['user_id'] ?>&listing_id=<?= $listing_id ?>" 
                                   class="btn btn-primary py-2 fw-bold rounded-pill">
                                    <i class="bi bi-chat-dots-fill me-2"></i> Chat with Seller
                                </a>
                            <?php else: ?>
                                <a href="edit.php?id=<?= $listing_id ?>" class="btn btn-outline-secondary py-2 fw-bold rounded-pill">
                                    <i class="bi bi-pencil-square me-2"></i> Manage Listing
                                </a>
                            <?php endif; ?>
                        <?php else: ?>
                            <a href="../auth/login.php" class="btn btn-primary py-2 fw-bold rounded-pill">Login to Chat</a>
                        <?php endif; ?>
                    </div>

                    <div class="mt-4 p-3 rounded-3 bg-light border-start border-4 border-warning">
                        <h6 class="fw-bold small text-uppercase mb-2"><i class="bi bi-shield-lock me-1"></i> Stay Safe</h6>
                        <ul class="list-unstyled small mb-0 opacity-75" style="font-size: 0.8rem;">
                            <li class="mb-1">• Meet in public campus spots (e.g. Library, Cafe)</li>
                            <li class="mb-1">• Never pay before seeing the item/service</li>
                            <li>• Check the student's rating & profile</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function copyLink(btn) {
    navigator.clipboard.writeText(window.location.href).then(() => {
        const originalText = btn.innerHTML;
        btn.innerHTML = '<i class="bi bi-check2"></i> Link Copied!';
        btn.classList.replace('btn-light', 'btn-success');
        btn.classList.add('text-white');
        setTimeout(() => {
            btn.innerHTML = originalText;
            btn.classList.replace('btn-success', 'btn-light');
            btn.classList.remove('text-white');
        }, 2000);
    });
}
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>