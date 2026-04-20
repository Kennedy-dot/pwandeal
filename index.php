<?php
/**
 * PwanDeal - Home Page
 * Refined for Pwani University Student Community
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 1. Setup & Error Reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/config/database.php';

$page_title = 'Welcome to PwanDeal';
$base_url = '/pwandeal'; 

// 2. Data Aggregation
$user_count = 0;
$active_listings = 0;
$recent_services = [];

if (isset($conn) && $conn) {
    // Get total student count
    $res_u = $conn->query("SELECT COUNT(*) as total FROM users");
    if ($res_u) $user_count = (int)$res_u->fetch_assoc()['total'];

    // Get active listings count
    $res_l = $conn->query("SELECT COUNT(*) as total FROM listings WHERE status = 'active'");
    if ($res_l) $active_listings = (int)$res_l->fetch_assoc()['total'];

    // Fetch 4 most recent listings with primary image
    $res_recent = $conn->query("
        SELECT l.listing_id, l.title, l.price, u.name as seller_name, i.image_url 
        FROM listings l 
        JOIN users u ON l.user_id = u.user_id 
        LEFT JOIN listing_images i ON l.listing_id = i.listing_id AND i.is_primary = 1
        WHERE l.status = 'active' 
        ORDER BY l.created_at DESC 
        LIMIT 4
    ");
    if ($res_recent) {
        while($row = $res_recent->fetch_assoc()) {
            $recent_services[] = $row;
        }
    }
}

// Formatting display numbers
$display_users = ($user_count > 10) ? $user_count . "+" : $user_count;
$display_listings = ($active_listings > 0) ? $active_listings : "0";

include __DIR__ . '/includes/header.php';
?>

<style>
    :root {
        --primary-gradient: linear-gradient(135deg, #028090 0%, #1e2761 100%);
        --pu-teal: #028090;
    }
    .hero-section {
        background: var(--primary-gradient);
        color: white;
        padding: 80px 0 120px 0;
        border-radius: 0 0 40px 40px;
    }
    .search-container {
        margin-top: -50px;
        z-index: 5;
        position: relative;
    }
    .search-card {
        border: none;
        border-radius: 50px;
        box-shadow: 0 15px 35px rgba(0,0,0,0.1);
        padding: 10px;
    }
    .category-link {
        transition: all 0.3s ease;
        border: 1px solid #e9ecef;
        background: white;
        color: #495057;
    }
    .category-link:hover {
        background: var(--pu-teal);
        color: white !important;
        transform: translateY(-3px);
    }
    .pulse-box {
        background: rgba(255, 255, 255, 0.1);
        backdrop-filter: blur(5px);
        border: 1px solid rgba(255, 255, 255, 0.2);
        border-radius: 20px;
    }
    .card-hover {
        transition: all 0.3s ease;
    }
    .card-hover:hover {
        transform: translateY(-8px);
        box-shadow: 0 10px 25px rgba(0,0,0,0.1) !important;
    }
</style>

<div class="hero-section">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-7 text-center text-lg-start">
                <span class="badge bg-white text-primary px-3 py-2 rounded-pill mb-4 shadow-sm fw-bold">
                    🎓 Verified Pwani Uni Network
                </span>
                <h1 class="display-3 fw-bold mb-4">Trade. Save. Grow. <br><span class="text-warning">Your Campus Hustle.</span></h1>
                <p class="lead mb-5 opacity-90">The exclusive digital marketplace for PU students. Find hostels, sell electronics, or hire a tutor—all within a safe student community.</p>
                
                <div class="d-flex flex-wrap justify-content-center justify-content-lg-start gap-3">
                    <?php if (!isset($_SESSION['user_id'])): ?>
                        <a href="<?= $base_url ?>/auth/register.php" class="btn btn-warning btn-lg rounded-pill px-5 fw-bold shadow">Join Now</a>
                        <a href="<?= $base_url ?>/auth/login.php" class="btn btn-outline-light btn-lg rounded-pill px-4">Login</a>
                    <?php else: ?>
                        <a href="<?= $base_url ?>/listings/create.php" class="btn btn-warning btn-lg rounded-pill px-5 fw-bold shadow">Post a Listing</a>
                        <a href="<?= $base_url ?>/listings/view.php" class="btn btn-outline-light btn-lg rounded-pill px-4">Browse All</a>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="col-lg-5 d-none d-lg-block">
                <div class="pulse-box p-5 text-center">
                    <h4 class="fw-bold mb-4">Marketplace Pulse</h4>
                    <div class="row g-3">
                        <div class="col-6">
                            <div class="bg-white bg-opacity-10 p-3 rounded-4">
                                <h2 class="mb-0 fw-bold"><?= $display_users ?></h2>
                                <small class="text-uppercase opacity-75">Students</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="bg-white bg-opacity-10 p-3 rounded-4">
                                <h2 class="mb-0 fw-bold"><?= $display_listings ?></h2>
                                <small class="text-uppercase opacity-75">Live Deals</small>
                            </div>
                        </div>
                    </div>
                    <div class="mt-4 pt-2">
                        <div class="d-flex align-items-center justify-content-center text-warning">
                            <i class="bi bi-patch-check-fill me-2"></i>
                            <span class="small fw-bold text-uppercase">100% Student Verified</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="container search-container">
    <div class="row justify-content-center">
        <div class="col-lg-9">
            <div class="card search-card">
                <form action="<?= $base_url ?>/listings/view.php" method="GET" class="row g-0 align-items-center">
                    <div class="col-md-9 px-4 py-2 d-flex align-items-center">
                        <i class="bi bi-search text-muted me-3"></i>
                        <input type="text" name="search" class="form-control border-0 shadow-none p-0" placeholder="Search for electronics, hostels, or services...">
                    </div>
                    <div class="col-md-3 p-1">
                        <button class="btn btn-primary w-100 rounded-pill py-2 fw-bold" type="submit" style="background-color: var(--pu-teal); border:none;">Search</button>
                    </div>
                </form>
            </div>
            
            <div class="d-flex flex-wrap justify-content-center gap-2 mt-4">
                <a href="<?= $base_url ?>/listings/view.php?category=Hostels" class="btn btn-sm rounded-pill category-link px-3 py-2 fw-medium shadow-sm text-decoration-none">🏠 Hostels</a>
                <a href="<?= $base_url ?>/listings/view.php?category=Electronics" class="btn btn-sm rounded-pill category-link px-3 py-2 fw-medium shadow-sm text-decoration-none">💻 Electronics</a>
                <a href="<?= $base_url ?>/listings/view.php?category=Academics" class="btn btn-sm rounded-pill category-link px-3 py-2 fw-medium shadow-sm text-decoration-none">📚 Academics</a>
                <a href="<?= $base_url ?>/listings/view.php?category=Food" class="btn btn-sm rounded-pill category-link px-3 py-2 fw-medium shadow-sm text-decoration-none">🥘 Food</a>
            </div>
        </div>
    </div>
</div>

<?php if (!empty($recent_services)): ?>
<section class="py-5 bg-white mt-5">
    <div class="container">
        <div class="d-flex justify-content-between align-items-end mb-4 px-2">
            <div>
                <h3 class="fw-bold mb-1">Recently Added</h3>
                <p class="text-muted small mb-0">Fresh opportunities from your classmates.</p>
            </div>
            <a href="<?= $base_url ?>/listings/view.php" class="btn btn-link text-primary text-decoration-none fw-bold">View All ➔</a>
        </div>

        <div class="row g-4">
            <?php foreach ($recent_services as $service): ?>
                <div class="col-sm-6 col-lg-3">
                    <div class="card h-100 border-0 shadow-sm rounded-4 card-hover overflow-hidden">
                        <div class="position-relative">
                            <?php 
                                $img_path = !empty($service['image_url']) 
                                    ? $base_url . "/assets/uploads/services/" . $service['image_url'] 
                                    : $base_url . "/assets/img/service-placeholder.jpg";
                            ?>
                            <img src="<?= $img_path ?>" 
                                 class="card-img-top" 
                                 style="height:180px; object-fit:cover;"
                                 onerror="this.src='https://placehold.co/600x400?text=Listing+Image'">
                            <span class="badge bg-white text-dark shadow-sm rounded-pill position-absolute top-0 end-0 m-2">KSh <?= number_format($service['price']) ?></span>
                        </div>
                        <div class="card-body p-3">
                            <h6 class="fw-bold text-truncate mb-1"><?= htmlspecialchars($service['title']) ?></h6>
                            <p class="text-muted x-small mb-3">By <?= htmlspecialchars($service['seller_name'] ?? 'PU Student') ?></p>
                            <a href="<?= $base_url ?>/listings/detail.php?id=<?= $service['listing_id'] ?>" 
                               class="btn btn-sm btn-outline-primary w-100 rounded-pill">View Details</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<section id="how-it-works" class="py-5 bg-light rounded-5 mx-2 mb-5">
    <div class="container py-4 text-center">
        <h3 class="fw-bold mb-5">Built for <span style="color: var(--pu-teal);">Pwani Students</span></h3>
        <div class="row g-4 justify-content-center">
            <div class="col-md-4 px-lg-5">
                <div class="mb-3 text-primary display-5"><i class="bi bi-shield-check"></i></div>
                <h5 class="fw-bold">Safe Transactions</h5>
                <p class="text-muted small">The student-only verification ensures you are dealing with your peers on campus.</p>
            </div>
            <div class="col-md-4 px-lg-5 border-start border-end">
                <div class="mb-3 text-primary display-5"><i class="bi bi-chat-text"></i></div>
                <h5 class="fw-bold">Direct Messaging</h5>
                <p class="text-muted small">Chat with sellers instantly to ask questions or negotiate prices without middlemen.</p>
            </div>
            <div class="col-md-4 px-lg-5">
                <div class="mb-3 text-primary display-5"><i class="bi bi-lightning-charge"></i></div>
                <h5 class="fw-bold">Boost Your Hustle</h5>
                <p class="text-muted small">Turn your skills into cash by offering services like hair styling, coding, or printing.</p>
            </div>
        </div>
    </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>