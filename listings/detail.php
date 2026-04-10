<?php
/**
 * PwanDeal - Service Details View
 */
session_start();
require_once __DIR__ . '/../config/database.php';

$listing_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($listing_id === 0) {
    header('Location: view.php');
    exit();
}

// 1. FETCH DATA (Listing + Category + Provider)
$stmt = $conn->prepare("
    SELECT l.*, c.name as category_name, 
           u.name as provider_name, u.email, u.profile_photo, u.school, 
           u.average_rating, u.total_reviews, u.total_listings
    FROM listings l 
    JOIN categories c ON l.category_id = c.category_id 
    JOIN users u ON l.user_id = u.user_id 
    WHERE l.listing_id = ?
");
$stmt->bind_param('i', $listing_id);
$stmt->execute();
$listing = $stmt->get_result()->fetch_assoc();

if (!$listing) {
    die("<div class='container py-5 text-center'><h1>Listing not found!</h1><a href='view.php' class='btn btn-primary'>Back to browse</a></div>");
}

// 2. VIEW COUNT LOGIC (Prevent spamming views via session)
if (!isset($_SESSION['viewed_listings'])) {
    $_SESSION['viewed_listings'] = [];
}
if (!in_array($listing_id, $_SESSION['viewed_listings'])) {
    $conn->query("UPDATE listings SET views = views + 1 WHERE listing_id = $listing_id");
    $_SESSION['viewed_listings'][] = $listing_id;
}

// 3. RECENT REVIEWS
$rev_stmt = $conn->prepare("SELECT * FROM reviews WHERE to_user_id = ? ORDER BY created_at DESC LIMIT 3");
$rev_stmt->bind_param('i', $listing['user_id']);
$rev_stmt->execute();
$reviews = $rev_stmt->get_result();

$page_title = $listing['title'];
$base_url = '/pwandeal';
include __DIR__ . '/../includes/header.php';
?>

<div class="container py-4">
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= $base_url ?>/index.php">Home</a></li>
            <li class="breadcrumb-item"><a href="<?= $base_url ?>/listings/view.php">Marketplace</a></li>
            <li class="breadcrumb-item active text-truncate" style="max-width: 200px;"><?= htmlspecialchars($listing['title']) ?></li>
        </ol>
    </nav>

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
                <?php 
                    $img_src = (!empty($listing['image_url']) && $listing['image_url'] !== 'default-service.jpg') 
                                ? $base_url . "/assets/uploads/services/" . $listing['image_url'] 
                                : $base_url . "/assets/img/service-placeholder.jpg";
                ?>
                <div class="position-relative">
                    <img src="<?= $img_src ?>" class="img-fluid w-100" style="height: 400px; object-fit: cover;" alt="Service">
                    <div class="position-absolute top-0 end-0 m-3">
                        <span class="badge bg-dark bg-opacity-75 px-3 py-2 rounded-pill">
                            <i class="bi bi-eye me-1"></i> <?= $listing['views'] ?>
                        </span>
                    </div>
                </div>
                
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <span class="badge bg-info text-dark mb-2"><?= htmlspecialchars($listing['category_name']) ?></span>
                            <h1 class="fw-bold h2 mb-1"><?= htmlspecialchars($listing['title']) ?></h1>
                            <p class="text-muted small"><i class="bi bi-clock me-1"></i> Posted <?= date('M d, Y', strtotime($listing['created_at'])) ?></p>
                        </div>
                        <div class="text-end">
                            <div class="h3 fw-bold text-primary mb-0">KSh <?= number_format($listing['price']) ?></div>
                            <span class="badge bg-light text-muted border">Negotiable</span>
                        </div>
                    </div>

                    <hr class="my-4 opacity-10">

                    <h5 class="fw-bold mb-3">About this service</h5>
                    <div class="text-secondary mb-4" style="line-height: 1.8; white-space: pre-line;">
                        <?= htmlspecialchars($listing['description']) ?>
                    </div>

                    <div class="d-flex gap-2 mt-5">
                        <button class="btn btn-outline-secondary btn-sm rounded-pill px-3" onclick="copyLink(this)">
                            <i class="bi bi-share me-1"></i> Share Listing
                        </button>
                        <button class="btn btn-outline-secondary btn-sm rounded-pill px-3" onclick="window.print()">
                            <i class="bi bi-printer me-1"></i> Print
                        </button>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body p-4">
                    <h5 class="fw-bold mb-4">Student Feedback</h5>
                    <?php if ($reviews->num_rows > 0): ?>
                        <?php while($rev = $reviews->fetch_assoc()): ?>
                            <div class="mb-4 pb-4 border-bottom last-child-border-0">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <div class="text-warning small">
                                        <?= str_repeat('<i class="bi bi-star-fill"></i>', $rev['rating']) . str_repeat('<i class="bi bi-star"></i>', 5 - $rev['rating']) ?>
                                    </div>
                                    <small class="text-muted"><?= date('M Y', strtotime($rev['created_at'])) ?></small>
                                </div>
                                <p class="text-dark small mb-0">"<?= htmlspecialchars($rev['comment']) ?>"</p>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div class="text-center py-3">
                            <i class="bi bi-chat-left-quote fs-2 text-muted opacity-25"></i>
                            <p class="text-muted mt-2">No reviews yet for this provider.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card border-0 shadow-sm rounded-4 sticky-top" style="top: 90px;">
                <div class="card-body p-4">
                    <div class="text-center mb-4">
                        <div class="position-relative d-inline-block mb-3">
                            <img src="<?= $listing['profile_photo'] ? $base_url.'/uploads/profiles/'.$listing['profile_photo'] : $base_url.'/assets/img/default-avatar.png' ?>" 
                                 class="rounded-circle border border-4 border-white shadow-sm" 
                                 style="width: 90px; height: 90px; object-fit: cover;">
                            <span class="position-absolute bottom-0 end-0 bg-success border border-white border-2 rounded-circle p-2" title="Student Status"></span>
                        </div>
                        <h5 class="fw-bold mb-0"><?= htmlspecialchars($listing['provider_name']) ?></h5>
                        <p class="text-muted small"><?= htmlspecialchars($listing['school'] ?? 'Pwani University') ?></p>
                        
                        <div class="row g-0 mt-3 border rounded-3 p-2 bg-light">
                            <div class="col-6 border-end">
                                <div class="fw-bold"><?= number_format($listing['average_rating'], 1) ?> <i class="bi bi-star-fill text-warning small"></i></div>
                                <div class="small text-muted">Rating</div>
                            </div>
                            <div class="col-6">
                                <div class="fw-bold"><?= $listing['total_listings'] ?></div>
                                <div class="small text-muted">Services</div>
                            </div>
                        </div>
                    </div>

                    <div class="d-grid gap-2">
                        <?php if (isset($_SESSION['user_id'])): ?>
                            <?php if ($_SESSION['user_id'] != $listing['user_id']): ?>
                                <a href="<?= $base_url ?>/messages/chat.php?seller_id=<?= $listing['user_id'] ?>&listing_id=<?= $listing_id ?>" 
                                   class="btn btn-primary py-2 fw-bold rounded-pill">
                                    <i class="bi bi-chat-left-text me-2"></i> Message Provider
                                </a>
                            <?php else: ?>
                                <a href="edit.php?id=<?= $listing_id ?>" class="btn btn-secondary py-2 fw-bold rounded-pill">
                                    <i class="bi bi-pencil-square me-2"></i> Edit My Listing
                                </a>
                            <?php endif; ?>
                        <?php else: ?>
                            <a href="<?= $base_url ?>/auth/login.php" class="btn btn-primary py-2 fw-bold rounded-pill">
                                Login to Contact
                            </a>
                        <?php endif; ?>
                        
                        <a href="<?= $base_url ?>/profile/view.php?id=<?= $listing['user_id'] ?>" class="btn btn-link btn-sm text-decoration-none text-muted">
                            View Provider Profile
                        </a>
                    </div>

                    <div class="mt-4 p-3 rounded-3 bg-light border-start border-4 border-warning">
                        <h6 class="fw-bold small text-uppercase mb-2"><i class="bi bi-shield-check me-1"></i> Safety Tips</h6>
                        <ul class="list-unstyled small mb-0 opacity-75">
                            <li class="mb-1">• Meet in open campus areas</li>
                            <li class="mb-1">• Inspect service before paying</li>
                            <li>• Report suspicious behavior</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function copyLink(btn) {
    const originalText = btn.innerHTML;
    navigator.clipboard.writeText(window.location.href).then(() => {
        btn.innerHTML = '<i class="bi bi-check2"></i> Copied!';
        btn.classList.replace('btn-outline-secondary', 'btn-success');
        setTimeout(() => {
            btn.innerHTML = originalText;
            btn.classList.replace('btn-success', 'btn-outline-secondary');
        }, 2000);
    });
}
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>