<?php
/**
 * PwanDeal - Home Page (Enhanced)
 * Refined for Pwani University Student Community
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 1. Setup & Error Reporting
error_reporting(E_ALL);
ini_set('display_errors', 0); // Hide errors in production

require_once __DIR__ . '/config/database.php';

$page_title = 'PwanDeal - Student Marketplace';
$base_url = '/pwandeal'; 

// 2. Data Aggregation
$user_count = 0;
$active_listings = 0;
$recent_services = [];
$total_transactions = 0;

if (isset($conn) && $conn) {
    // Get total student count
    $res_u = $conn->query("SELECT COUNT(*) as total FROM users WHERE is_suspended = 0");
    if ($res_u) $user_count = (int)$res_u->fetch_assoc()['total'];

    // Get active listings count
    $res_l = $conn->query("SELECT COUNT(*) as total FROM listings WHERE status = 'active'");
    if ($res_l) $active_listings = (int)$res_l->fetch_assoc()['total'];

    // Get total completed transactions (approximate from reviews)
    $res_t = $conn->query("SELECT COUNT(*) as total FROM reviews");
    if ($res_t) $total_transactions = (int)$res_t->fetch_assoc()['total'];

    // Fetch 4 most recent listings with primary image
    $res_recent = $conn->query("
        SELECT l.listing_id, l.title, l.price, u.name as seller_name, i.image_url,
               ROUND(AVG(r.rating), 1) as avg_rating, COUNT(r.review_id) as review_count
        FROM listings l 
        JOIN users u ON l.user_id = u.user_id 
        LEFT JOIN listing_images i ON l.listing_id = i.listing_id AND i.is_primary = 1
        LEFT JOIN reviews r ON l.listing_id = r.listing_id
        WHERE l.status = 'active' 
        GROUP BY l.listing_id
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
$display_transactions = ($total_transactions > 0) ? $total_transactions . "+" : "0";

include __DIR__ . '/includes/header.php';
?>

<style>
    :root {
        --primary-gradient: linear-gradient(135deg, #028090 0%, #1e2761 100%);
        --secondary-gradient: linear-gradient(135deg, #1e2761 0%, #028090 100%);
        --pu-teal: #028090;
        --pu-navy: #1e2761;
        --accent-yellow: #f4d03f;
    }

    body { scroll-behavior: smooth; }

    /* Hero Section */
    .hero-section {
        background: var(--primary-gradient);
        color: white;
        padding: 100px 0 80px 0;
        position: relative;
        overflow: hidden;
    }

    .hero-section::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -10%;
        width: 500px;
        height: 500px;
        background: rgba(255, 255, 255, 0.05);
        border-radius: 50%;
        z-index: 0;
    }

    .hero-content {
        position: relative;
        z-index: 2;
    }

    .search-container {
        margin-top: -60px;
        z-index: 10;
        position: relative;
    }

    .search-card {
        border: none;
        border-radius: 50px;
        box-shadow: 0 20px 40px rgba(0,0,0,0.15);
        padding: 10px;
        background: white;
        transition: all 0.3s ease;
    }

    .search-card:hover {
        box-shadow: 0 25px 50px rgba(0,0,0,0.2);
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
        border-color: var(--pu-teal);
    }

    .pulse-box {
        background: rgba(255, 255, 255, 0.1);
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.2);
        border-radius: 25px;
    }

    .stat-box {
        text-align: center;
        padding: 20px;
    }

    .stat-number {
        font-size: 2rem;
        font-weight: 800;
        color: var(--accent-yellow);
    }

    .stat-label {
        font-size: 0.85rem;
        text-transform: uppercase;
        letter-spacing: 1px;
        opacity: 0.8;
    }

    .card-hover {
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .card-hover:hover {
        transform: translateY(-8px);
        box-shadow: 0 15px 40px rgba(2, 128, 144, 0.15) !important;
    }

    /* Trust Section */
    .trust-section {
        background: linear-gradient(135deg, rgba(2, 128, 144, 0.05) 0%, rgba(30, 39, 97, 0.05) 100%);
        border-radius: 30px;
        padding: 50px 30px;
    }

    /* Feature Cards */
    .feature-card {
        background: white;
        border: none;
        border-radius: 20px;
        padding: 40px 30px;
        text-align: center;
        height: 100%;
        transition: all 0.3s ease;
    }

    .feature-card:hover {
        box-shadow: 0 10px 30px rgba(2, 128, 144, 0.1);
        transform: translateY(-5px);
    }

    .feature-icon {
        font-size: 3rem;
        margin-bottom: 20px;
        display: inline-block;
    }

    /* CTA Buttons */
    .btn-primary-cta {
        background: var(--pu-teal);
        border: none;
        padding: 12px 30px;
        font-weight: 600;
        border-radius: 50px;
        transition: all 0.3s ease;
    }

    .btn-primary-cta:hover {
        background: var(--pu-navy);
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(2, 128, 144, 0.3);
    }

    /* Section Spacing */
    .section-spacing {
        padding: 60px 0;
    }

    .section-title {
        color: var(--pu-navy);
        font-weight: 800;
        margin-bottom: 40px;
        font-size: 2.5rem;
    }

    .subtitle {
        color: #666;
        font-size: 1.1rem;
        margin-bottom: 30px;
    }

    /* FAQ Section */
    .accordion-button:not(.collapsed) {
        background-color: var(--pu-teal);
        color: white;
    }

    .accordion-button:focus {
        border-color: var(--pu-teal);
        box-shadow: 0 0 0 0.25rem rgba(2, 128, 144, 0.25);
    }

    /* Testimonial */
    .testimonial-card {
        background: white;
        border-left: 4px solid var(--pu-teal);
        border-radius: 15px;
        padding: 30px;
        box-shadow: 0 5px 15px rgba(0,0,0,0.08);
    }

    .testimonial-stars {
        color: var(--accent-yellow);
        font-size: 1.3rem;
    }

    /* Badge Styles */
    .trust-badge {
        display: inline-block;
        background: linear-gradient(135deg, var(--pu-teal) 0%, var(--pu-navy) 100%);
        color: white;
        padding: 8px 16px;
        border-radius: 50px;
        font-size: 0.85rem;
        font-weight: 600;
        margin-right: 10px;
        margin-bottom: 10px;
    }

    /* Mobile Menu Fix */
    @media (max-width: 991px) {
        .hero-section {
            padding: 80px 0 60px 0;
        }

        .section-title {
            font-size: 2rem;
        }

        .pulse-box {
            margin-top: 30px;
        }

        .search-container {
            margin-top: -40px;
        }
    }
</style>

<!-- ==================== HERO SECTION ==================== -->
<section class="hero-section">
    <div class="container hero-content">
        <div class="row align-items-center">
            <div class="col-lg-6 text-center text-lg-start mb-5 mb-lg-0">
                <div class="mb-4">
                    <span class="trust-badge">
                        <i class="bi bi-patch-check-fill me-2"></i>Verified Pwani Uni Network
                    </span>
                </div>
                
                <h1 class="display-3 fw-bold mb-4">
                    Trade Smart.<br>
                    <span style="background: linear-gradient(120deg, #f4d03f, #ffd700); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">Save Big.</span><br>
                    Grow Faster.
                </h1>
                
                <p class="lead mb-5 opacity-90">
                    The exclusive peer-to-peer marketplace for Pwani University students. Buy and sell textbooks, electronics, services, and more—all within a verified student community.
                </p>
                
                <div class="d-flex flex-wrap gap-3 justify-content-center justify-content-lg-start">
                    <?php if (!isset($_SESSION['user_id'])): ?>
                        <a href="<?= $base_url ?>/auth/register.php" class="btn btn-warning btn-lg rounded-pill px-5 fw-bold shadow-lg">
                            <i class="bi bi-rocket-takeoff me-2"></i>Get Started Free
                        </a>
                        <a href="<?= $base_url ?>/listings/view.php" class="btn btn-outline-light btn-lg rounded-pill px-4 fw-bold">
                            <i class="bi bi-eye me-2"></i>Browse Listings
                        </a>
                    <?php else: ?>
                        <a href="<?= $base_url ?>/listings/create.php" class="btn btn-warning btn-lg rounded-pill px-5 fw-bold shadow-lg">
                            <i class="bi bi-plus-circle me-2"></i>Post a Listing
                        </a>
                        <a href="<?= $base_url ?>/listings/view.php" class="btn btn-outline-light btn-lg rounded-pill px-4 fw-bold">
                            <i class="bi bi-shop me-2"></i>Browse Marketplace
                        </a>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="col-lg-6 d-none d-lg-block">
                <div class="pulse-box p-5">
                    <h5 class="fw-bold text-white mb-4">📊 Marketplace Pulse</h5>
                    
                    <div class="row g-3 mb-4">
                        <div class="col-6">
                            <div class="bg-white bg-opacity-10 p-4 rounded-4 stat-box">
                                <div class="stat-number"><?= $display_users ?></div>
                                <div class="stat-label">Active Students</div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="bg-white bg-opacity-10 p-4 rounded-4 stat-box">
                                <div class="stat-number"><?= $display_listings ?></div>
                                <div class="stat-label">Live Listings</div>
                            </div>
                        </div>
                    </div>

                    <div class="row g-3">
                        <div class="col-6">
                            <div class="bg-white bg-opacity-10 p-4 rounded-4 stat-box">
                                <div class="stat-number"><?= $display_transactions ?></div>
                                <div class="stat-label">Transactions</div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="bg-white bg-opacity-10 p-4 rounded-4 stat-box">
                                <div class="stat-number">100%</div>
                                <div class="stat-label">Verified</div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4 pt-3 border-top border-white border-opacity-10 text-center">
                        <div class="d-flex align-items-center justify-content-center text-warning">
                            <i class="bi bi-shield-check-fill me-2"></i>
                            <span class="small fw-bold">Student-Only Safe Zone</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ==================== SEARCH SECTION ==================== -->
<div class="container search-container mb-5">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="card search-card">
                <form action="<?= $base_url ?>/listings/view.php" method="GET" class="row g-0 align-items-center">
                    <div class="col-md-9 px-4 py-2 d-flex align-items-center">
                        <i class="bi bi-search text-muted me-3 fs-5"></i>
                        <input type="text" name="search" class="form-control border-0 shadow-none p-0 fs-6" 
                               placeholder="Search listings, categories, or seller names...">
                    </div>
                    <div class="col-md-3 p-2">
                        <button class="btn btn-primary-cta w-100 rounded-pill py-2 fw-bold text-white" type="submit">
                            <i class="bi bi-search me-2"></i>Search
                        </button>
                    </div>
                </form>
            </div>
            
            <div class="d-flex flex-wrap justify-content-center gap-2 mt-4">
                <a href="<?= $base_url ?>/listings/view.php?category=Academic" class="btn btn-sm rounded-pill category-link px-3 py-2 fw-bold shadow-sm text-decoration-none">
                    📚 Academic
                </a>
                <a href="<?= $base_url ?>/listings/view.php?category=Electronics" class="btn btn-sm rounded-pill category-link px-3 py-2 fw-bold shadow-sm text-decoration-none">
                    💻 Electronics
                </a>
                <a href="<?= $base_url ?>/listings/view.php?category=Services" class="btn btn-sm rounded-pill category-link px-3 py-2 fw-bold shadow-sm text-decoration-none">
                    🔧 Services
                </a>
                <a href="<?= $base_url ?>/listings/view.php?category=Products" class="btn btn-sm rounded-pill category-link px-3 py-2 fw-bold shadow-sm text-decoration-none">
                    📦 Products
                </a>
                <a href="<?= $base_url ?>/listings/view.php" class="btn btn-sm rounded-pill category-link px-3 py-2 fw-bold shadow-sm text-decoration-none">
                    ➕ Browse All
                </a>
            </div>
        </div>
    </div>
</div>

<!-- ==================== RECENT LISTINGS ==================== -->
<?php if (!empty($recent_services)): ?>
<section class="section-spacing bg-light">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-5">
            <div>
                <h2 class="section-title mb-1">🔥 Recently Added</h2>
                <p class="subtitle mb-0">Fresh opportunities from your classmates</p>
            </div>
            <a href="<?= $base_url ?>/listings/view.php" class="btn btn-link text-primary text-decoration-none fw-bold">
                View All Listings <i class="bi bi-arrow-right ms-2"></i>
            </a>
        </div>

        <div class="row g-4">
            <?php foreach ($recent_services as $service): ?>
                <div class="col-sm-6 col-lg-3">
                    <div class="card h-100 border-0 shadow-sm rounded-4 card-hover overflow-hidden">
                        <!-- Image -->
                        <div class="position-relative" style="height: 200px; overflow: hidden; background: #f0f0f0;">
                            <?php 
                                $img_path = !empty($service['image_url']) && file_exists(__DIR__ . "/assets/uploads/services/" . $service['image_url'])
                                    ? $base_url . "/assets/uploads/services/" . htmlspecialchars($service['image_url'])
                                    : $base_url . "/assets/img/service-placeholder.jpg";
                            ?>
                            <img src="<?= $img_path ?>" 
                                 class="card-img-top w-100 h-100" 
                                 style="object-fit:cover;"
                                 alt="<?= htmlspecialchars($service['title']) ?>">
                            
                            <!-- Price Badge -->
                            <span class="badge bg-dark text-warning shadow rounded-pill position-absolute bottom-0 start-0 m-3">
                                <i class="bi bi-cash-coin me-1"></i>KSh <?= number_format($service['price']) ?>
                            </span>
                        </div>

                        <!-- Content -->
                        <div class="card-body p-4">
                            <h6 class="fw-bold text-truncate mb-2" title="<?= htmlspecialchars($service['title']) ?>">
                                <?= htmlspecialchars($service['title']) ?>
                            </h6>
                            
                            <p class="text-muted small mb-3">
                                <i class="bi bi-person-circle me-1"></i><?= htmlspecialchars($service['seller_name'] ?? 'PU Student') ?>
                            </p>

                            <!-- Rating -->
                            <?php if ($service['review_count'] > 0): ?>
                                <div class="small mb-3">
                                    <span class="text-warning">
                                        <i class="bi bi-star-fill"></i> <?= $service['avg_rating'] ?>
                                    </span>
                                    <span class="text-muted">(<?= $service['review_count'] ?> review<?= $service['review_count'] !== 1 ? 's' : '' ?>)</span>
                                </div>
                            <?php else: ?>
                                <div class="small text-muted mb-3">
                                    <i class="bi bi-star"></i> New Listing
                                </div>
                            <?php endif; ?>

                            <!-- CTA -->
                            <a href="<?= $base_url ?>/listings/detail.php?id=<?= $service['listing_id'] ?>" 
                               class="btn btn-sm btn-outline-primary w-100 rounded-pill fw-bold">
                                View Details
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- ==================== FEATURES SECTION ==================== -->
<section class="section-spacing">
    <div class="container">
        <h2 class="section-title text-center mb-5">Why Choose PwanDeal?</h2>

        <div class="row g-4">
            <div class="col-md-6 col-lg-3">
                <div class="feature-card">
                    <div class="feature-icon">🛡️</div>
                    <h5 class="fw-bold mb-3">100% Verified</h5>
                    <p class="text-muted">Only @pwani.ac.ke accounts. No fake profiles, no outside scammers. Safe student community only.</p>
                </div>
            </div>

            <div class="col-md-6 col-lg-3">
                <div class="feature-card">
                    <div class="feature-icon">💬</div>
                    <h5 class="fw-bold mb-3">Direct Chat</h5>
                    <p class="text-muted">Negotiate prices, ask questions, and make deals directly with sellers. No middlemen involved.</p>
                </div>
            </div>

            <div class="col-md-6 col-lg-3">
                <div class="feature-card">
                    <div class="feature-icon">⭐</div>
                    <h5 class="fw-bold mb-3">Ratings & Reviews</h5>
                    <p class="text-muted">Real feedback from real students. Build your reputation and trust through quality transactions.</p>
                </div>
            </div>

            <div class="col-md-6 col-lg-3">
                <div class="feature-card">
                    <div class="feature-icon">⚡</div>
                    <h5 class="fw-bold mb-3">Lightning Fast</h5>
                    <p class="text-muted">Post listings in 2 minutes. Find what you need in seconds. Meet your classmates on campus.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ==================== TRUST & SAFETY SECTION ==================== -->
<section class="section-spacing">
    <div class="container">
        <div class="trust-section">
            <h2 class="section-title text-center text-dark mb-5">Safety is Our Priority</h2>

            <div class="row g-5">
                <div class="col-lg-4">
                    <div class="d-flex">
                        <div class="me-4 flex-shrink-0">
                            <div style="width: 50px; height: 50px; background: rgba(2, 128, 144, 0.2); border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.5rem;">
                                🔐
                            </div>
                        </div>
                        <div>
                            <h6 class="fw-bold mb-2">Secure Messaging</h6>
                            <p class="small text-muted mb-0">All conversations stay within PwanDeal, creating a digital record for your protection.</p>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="d-flex">
                        <div class="me-4 flex-shrink-0">
                            <div style="width: 50px; height: 50px; background: rgba(2, 128, 144, 0.2); border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.5rem;">
                                📋
                            </div>
                        </div>
                        <div>
                            <h6 class="fw-bold mb-2">Dispute Resolution</h6>
                            <p class="small text-muted mb-0">If something goes wrong, our moderation team investigates and helps resolve issues fairly.</p>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="d-flex">
                        <div class="me-4 flex-shrink-0">
                            <div style="width: 50px; height: 50px; background: rgba(2, 128, 144, 0.2); border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.5rem;">
                                👥
                            </div>
                        </div>
                        <div>
                            <h6 class="fw-bold mb-2">Community Standards</h6>
                            <p class="small text-muted mb-0">Scammers get suspended. We enforce strict rules to keep the marketplace trustworthy.</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="text-center mt-5 pt-4 border-top border-dark border-opacity-10">
                <a href="<?= $base_url ?>/safety.php" class="btn btn-primary-cta text-white me-3">
                    <i class="bi bi-shield-check me-2"></i>Safety Tips
                </a>
                <a href="<?= $base_url ?>/privacy.php" class="btn btn-outline-dark">
                    <i class="bi bi-lock me-2"></i>Privacy Policy
                </a>
            </div>
        </div>
    </div>
</section>

<!-- ==================== HOW IT WORKS ==================== -->
<section class="section-spacing bg-light">
    <div class="container">
        <h2 class="section-title text-center mb-5">Getting Started is Easy</h2>

        <div class="row g-4 align-items-stretch">
            <div class="col-md-6 col-lg-3">
                <div class="feature-card">
                    <div class="mb-3" style="font-size: 2.5rem; color: var(--pu-teal);">1️⃣</div>
                    <h5 class="fw-bold mb-3">Register</h5>
                    <p class="text-muted small">Sign up with your @pwani.ac.ke email. Takes less than 2 minutes.</p>
                </div>
            </div>

            <div class="col-md-6 col-lg-3">
                <div class="feature-card">
                    <div class="mb-3" style="font-size: 2.5rem;">2️⃣</div>
                    <h5 class="fw-bold mb-3">Browse or Post</h5>
                    <p class="text-muted small">Search listings or create your own. Set your prices and watch offers come in.</p>
                </div>
            </div>

            <div class="col-md-6 col-lg-3">
                <div class="feature-card">
                    <div class="mb-3" style="font-size: 2.5rem;">3️⃣</div>
                    <h5 class="fw-bold mb-3">Chat & Negotiate</h5>
                    <p class="text-muted small">Message sellers directly. Ask questions, negotiate prices, and finalize details.</p>
                </div>
            </div>

            <div class="col-md-6 col-lg-3">
                <div class="feature-card">
                    <div class="mb-3" style="font-size: 2.5rem;">4️⃣</div>
                    <h5 class="fw-bold mb-3">Meet & Complete</h5>
                    <p class="text-muted small">Meet on campus, inspect items, pay via M-Pesa, and leave a review. Done!</p>
                </div>
            </div>
        </div>

        <div class="text-center mt-5 pt-4">
            <a href="<?= $base_url ?>/auth/register.php" class="btn btn-primary-cta text-white btn-lg rounded-pill px-5">
                Start Trading Now <i class="bi bi-arrow-right ms-2"></i>
            </a>
        </div>
    </div>
</section>

<!-- ==================== FAQ SECTION ==================== -->
<section class="section-spacing">
    <div class="container">
        <h2 class="section-title text-center mb-5">Frequently Asked Questions</h2>

        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="accordion" id="faqAccordion">
                    <div class="accordion-item border-0 mb-3 shadow-sm rounded-3 overflow-hidden">
                        <h2 class="accordion-header">
                            <button class="accordion-button fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#faq1">
                                How do I know PwanDeal is safe?
                            </button>
                        </h2>
                        <div id="faq1" class="accordion-collapse collapse show" data-bs-parent="#faqAccordion">
                            <div class="accordion-body bg-light">
                                PwanDeal is exclusive to verified Pwani University students (@pwani.ac.ke email). We have a moderation team that reviews reports 24/7, and scammers get suspended permanently. Plus, all conversations are documented for your protection.
                            </div>
                        </div>
                    </div>

                    <div class="accordion-item border-0 mb-3 shadow-sm rounded-3 overflow-hidden">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#faq2">
                                Is there a fee to use PwanDeal?
                            </button>
                        </h2>
                        <div id="faq2" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body bg-light">
                                Nope! Signing up and posting listings is completely free. We don't charge commission on sales. PwanDeal is built by students, for students.
                            </div>
                        </div>
                    </div>

                    <div class="accordion-item border-0 mb-3 shadow-sm rounded-3 overflow-hidden">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#faq3">
                                What if I get scammed or have a problem?
                            </button>
                        </h2>
                        <div id="faq3" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body bg-light">
                                Report the issue immediately through our contact form or WhatsApp. Our team investigates all reports and takes action within 24 hours. We have a full dispute resolution system.
                            </div>
                        </div>
                    </div>

                    <div class="accordion-item border-0 mb-3 shadow-sm rounded-3 overflow-hidden">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#faq4">
                                Can I sell services, not just products?
                            </button>
                        </h2>
                        <div id="faq4" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body bg-light">
                                Absolutely! PwanDeal is perfect for services. Tutoring, printing, hair styling, coding, design—if you have a skill, post it. Students can hire you directly and leave reviews.
                            </div>
                        </div>
                    </div>

                    <div class="accordion-item border-0 shadow-sm rounded-3 overflow-hidden">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#faq5">
                                What payment methods are accepted?
                            </button>
                        </h2>
                        <div id="faq5" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body bg-light">
                                We recommend M-Pesa for the safest, most traceable transactions. It creates a digital record that protects both you and the seller. Cash is also acceptable for in-person transactions.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ==================== CTA SECTION ==================== -->
<section class="section-spacing" style="background: var(--primary-gradient); color: white;">
    <div class="container text-center">
        <h2 class="display-5 fw-bold mb-4">Ready to Join the Marketplace?</h2>
        <p class="lead mb-5 opacity-90">Start buying, selling, and earning today. No fees, no middlemen—just students connecting with students.</p>
        
        <div class="d-flex flex-wrap gap-3 justify-content-center">
            <?php if (!isset($_SESSION['user_id'])): ?>
                <a href="<?= $base_url ?>/auth/register.php" class="btn btn-warning btn-lg rounded-pill px-5 fw-bold shadow-lg">
                    Create Account <i class="bi bi-arrow-right ms-2"></i>
                </a>
                <a href="<?= $base_url ?>/listings/view.php" class="btn btn-outline-light btn-lg rounded-pill px-5 fw-bold">
                    Browse First
                </a>
            <?php else: ?>
                <a href="<?= $base_url ?>/listings/create.php" class="btn btn-warning btn-lg rounded-pill px-5 fw-bold shadow-lg">
                    Post Your First Deal <i class="bi bi-arrow-right ms-2"></i>
                </a>
                <a href="<?= $base_url ?>/messages/inbox.php" class="btn btn-outline-light btn-lg rounded-pill px-5 fw-bold">
                    Check Messages
                </a>
            <?php endif; ?>
        </div>

        <div class="mt-5 pt-4 border-top border-white border-opacity-10">
            <p class="small opacity-75 mb-0">
                <i class="bi bi-shield-check me-2"></i>100% Student Verified • Safe • Free to Use
            </p>
        </div>
    </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>