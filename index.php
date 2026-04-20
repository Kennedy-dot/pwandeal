<?php
/**
 * PwanDeal - Home Page
 * Refined for Pwani University Student Community
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/config/database.php';

$page_title = 'Welcome to PwanDeal';
$base_url = '/pwandeal'; 

// 1. DATA AGGREGATION
$user_count = 0;
$active_listings = 0;
$recent_services = [];

if (isset($conn) && $conn) {
    $res_u = $conn->query("SELECT COUNT(*) as total FROM users");
    if ($res_u) $user_count = (int)$res_u->fetch_assoc()['total'];

    $res_l = $conn->query("SELECT COUNT(*) as total FROM listings WHERE status = 'active'");
    if ($res_l) $active_listings = (int)$res_l->fetch_assoc()['total'];

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

$display_users = ($user_count > 10) ? $user_count . "+" : $user_count;

include __DIR__ . '/includes/header.php';
?>

<style>
    :root {
        --primary-gradient: linear-gradient(135deg, #028090 0%, #00BFB2 100%);
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
        background: #028090;
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
                    🎓 Verified Student Network
                </span>
                <h1 class="display-3 fw-bold mb-4">Trade. Save. Grow. <br><span class="text-warning">At Pwani Uni.</span></h1>
                <p class="lead mb-5 opacity-90">The exclusive digital marketplace for PU students. Find hostels, sell electronics, or hire a tutor—all within the safe campus community.</p>
                
                <div class="d-flex flex-wrap justify-content-center justify-content-lg-start gap-3">
                    <?php if (!isset($_SESSION['user_id'])): ?>
                        <a href="<?= $base_url ?>/auth/register.php" class="btn btn-warning btn-lg rounded-pill px-5 fw-bold shadow">Join Now</a>
                    <?php else: ?>
                        <a href="<?= $base_url ?>/listings/create.php" class="btn btn-warning btn-lg rounded-pill px-5 fw-bold shadow">Post a Listing</a>
                    <?php endif; ?>
                    <a href="#how-it-works" class="btn btn-outline-light btn-lg rounded-pill px-4">How it works</a>
                </div>
            </div>
            
            <div class="col-lg-5 d-none d-lg-block">
                <div class="pulse-box p-4 text-center">
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
                                <h2 class="mb-0 fw-bold"><?= $active_listings ?></h2>
                                <small class="text-uppercase opacity-75">Listings</small>
                            </div>
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
                        <input type="text" name="search" class="form-control border-0 shadow-none p-0" placeholder="What do you need today?">
                    </div>
                    <div class="col-md-3 p-1">
                        <button class="btn btn-primary w-100 rounded-pill py-2 fw-bold" type="submit">Search</button>
                    </div>
                </form>
            </div>
            
            <div class="d-flex flex-wrap justify-content-center gap-2 mt-4">
                <a href="<?= $base_url ?>/listings/view.php?category=1" class="btn btn-sm rounded-pill category-link px-3 py-2 fw-medium shadow-sm text-decoration-none">🏠 Hostels</a>
                <a href="<?= $base_url ?>/listings/view.php?category=2" class="btn btn-sm rounded-pill category-link px-3 py-2 fw-medium shadow-sm text-decoration-none">💻 Electronics</a>
                <a href="<?= $base_url ?>/listings/view.php?category=3" class="btn btn-sm rounded-pill category-link px-3 py-2 fw-medium shadow-sm text-decoration-none">📚 Academics</a>
                <a href="<?= $base_url ?>/listings/view.php?category=4" class="btn btn-sm rounded-pill category-link px-3 py-2 fw-medium shadow-sm text-decoration-none">🥘 Food</a>
            </div>
        </div>
    </div>
</div>

<section class="py-5 bg-white mt-5">
    <div class="container">
        <div class="d-flex justify-content-between align-items-end mb-4 px-2">
            <div>
                <h3 class="fw-bold mb-1">New Arrivals</h3>
                <p class="text-muted small mb-0">Fresh deals added by your peers today.</p>
            </div>
            <a href="<?= $base_url ?>/listings/view.php" class="btn btn-link text-primary text-decoration-none fw-bold">View All ➔</a>
        </div>

        <div class="row g-4">
            <?php foreach ($recent_services as $service): ?>
                <div class="col-sm-6 col-lg-3">
                    <div class="card h-100 border-0 shadow-sm rounded-4 card-hover overflow-hidden">
                        <div class="position-relative">
                            <?php 
                                // Dynamic Image Fallback
                                $img_path = !empty($service['image_url']) 
                                    ? $base_url . "/uploads/services/" . $service['image_url'] 
                                    : "https://images.unsplash.com/photo-1557821552-17105176677c?auto=format&fit=crop&q=60&w=600";
                            ?>
                            <img src="<?= $img_path ?>" 
                                 class="card-img-top" 
                                 style="height:180px; object-fit:cover;"
                                 onerror="this.src='https://placehold.co/600x400?text=Listing+Image'">
                            <span class="badge bg-white text-dark shadow-sm rounded-pill position-absolute top-0 end-0 m-2">KSh <?= number_format($service['price']) ?></span>
                        </div>
                        <div class="card-body p-3">
                            <h6 class="fw-bold text-truncate mb-2"><?= htmlspecialchars($service['title']) ?></h6>
                            <div class="d-flex align-items-center mb-3">
                                <i class="bi bi-person-circle text-primary me-2"></i>
                                <span class="small text-muted"><?= htmlspecialchars($service['seller_name'] ?? 'PU Student') ?></span>
                            </div>
                            <a href="<?= $base_url ?>/listings/detail.php?id=<?= $service['listing_id'] ?>" 
                               class="btn btn-sm btn-outline-primary w-100 rounded-pill">View Details</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section id="how-it-works" class="py-5 bg-light rounded-5 mx-2 mb-5">
    <div class="container py-4 text-center">
        <h3 class="fw-bold mb-5">Built for <span class="text-primary">Pwani Students</span></h3>
        <div class="row g-4 justify-content-center">
            <div class="col-md-4 px-lg-5">
                <div class="mb-3 text-primary display-5"><i class="bi bi-shield-check"></i></div>
                <h5 class="fw-bold">Safe Transactions</h5>
                <p class="text-muted small">Meet on campus and pay only after you've inspected the item or service.</p>
            </div>
            <div class="col-md-4 px-lg-5 border-start border-end">
                <div class="mb-3 text-primary display-5"><i class="bi bi-chat-text"></i></div>
                <h5 class="fw-bold">Direct Messaging</h5>
                <p class="text-muted small">Chat with sellers instantly to ask questions or negotiate prices.</p>
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