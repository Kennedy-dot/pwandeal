<?php
/**
 * PwanDeal - Home Page
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 1. Error Reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// 2. Database Connection - Using __DIR__ for absolute pathing
require_once __DIR__ . '/config/database.php';

$page_title = 'Home';
$base_url = '/pwandeal'; 

// 3. Database Logic
$user_count = 0;
$active_listings = 0;
$recent_services = [];

if (isset($conn) && $conn) {
    $res_u = $conn->query("SELECT COUNT(*) as total FROM users");
    if ($res_u) $user_count = (int)$res_u->fetch_assoc()['total'];

    $res_l = $conn->query("SELECT COUNT(*) as total FROM listings WHERE status = 'active'");
    if ($res_l) $active_listings = (int)$res_l->fetch_assoc()['total'];

    $res_recent = $conn->query("
        SELECT l.*, u.name as seller_name 
        FROM listings l 
        JOIN users u ON l.user_id = u.user_id 
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

$display_users = ($user_count > 0) ? number_format($user_count) : "100+";
$display_listings = ($active_listings > 0) ? number_format($active_listings) : "50+";

include __DIR__ . '/includes/header.php';
?>

<div class="container pt-4 pb-5">
    <div class="row align-items-center"> 
        <div class="col-lg-6 mb-5 mb-lg-0 py-5">
            <span class="badge px-3 py-2 rounded-pill mb-3" style="background: rgba(2,128,144,0.1); color: #028090;">
                🚀 Exclusively for Pwani University
            </span>
            <h1 class="display-4 fw-bold lh-1 mb-4" style="color: #1e2761;">
                Unlock Your Campus <span style="color: #028090;">Potential.</span>
            </h1>
            <p class="lead text-muted mb-4 fs-5">
                The secure digital hub for students to trade skills, find academic help, and grow their hustle safely within the PU community.
            </p>
            
            <div class="d-flex flex-wrap gap-3">
                <?php if (!isset($_SESSION['user_id'])): ?>
                    <a href="/pwandeal/auth/register.php" class="btn btn-primary btn-lg rounded-pill shadow-sm" style="background: #028090; border:none;">Get Started</a>
                    <a href="/pwandeal/auth/login.php" class="btn btn-outline-secondary btn-lg rounded-pill">Login</a>
                <?php else: ?>
                    <a href="/pwandeal/listings/view.php" class="btn btn-primary btn-lg rounded-pill shadow-sm" style="background: #028090; border:none;">Explore Marketplace</a>
                    <a href="/pwandeal/listings/create.php" class="btn btn-outline-primary btn-lg rounded-pill">Post a Service</a>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="col-lg-5 offset-lg-1">
            <div class="p-5 rounded-5 shadow-lg position-relative overflow-hidden" 
                 style="background: linear-gradient(135deg, #028090 0%, #1e2761 100%); color: white;">
                <div class="position-absolute bg-white opacity-10 rounded-circle" style="width:200px; height:200px; top:-50px; right:-50px;"></div>
                
                <h3 class="fw-bold mb-4">Marketplace Pulse</h3>
                <div class="d-flex align-items-center mb-4">
                    <div class="display-5 fw-bold me-3"><?php echo $display_users; ?></div>
                    <div class="text-uppercase small opacity-75">Students<br>Registered</div>
                </div>
                <div class="d-flex align-items-center mb-4">
                    <div class="display-5 fw-bold me-3"><?php echo $display_listings; ?></div>
                    <div class="text-uppercase small opacity-75">Live<br>Services</div>
                </div>
                <div class="d-flex align-items-center">
                    <div class="display-5 fw-bold me-3">100%</div>
                    <div class="text-uppercase small opacity-75">PU Email<br>Verified</div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php if (!empty($recent_services)): ?>
<section class="py-5 bg-light">
    <div class="container">
        <div class="d-flex justify-content-between align-items-end mb-4">
            <div>
                <h2 class="fw-bold h1" style="color: #1e2761;">Recently Added</h2>
                <p class="text-muted">Fresh opportunities from your classmates.</p>
            </div>
            <a href="/pwandeal/listings/view.php" class="btn btn-link text-primary fw-bold text-decoration-none">View All ➔</a>
        </div>
        <div class="row g-4">
            <?php foreach ($recent_services as $service): ?>
                <div class="col-md-3">
                    <div class="card h-100 border-0 shadow-sm rounded-4 overflow-hidden">
                        <?php 
                            $img = !empty($service['image_url']) ? $service['image_url'] : 'default.jpg';
                        ?>
                        <img src="/pwandeal/assets/uploads/services/<?php echo htmlspecialchars($img); ?>" 
                             class="card-img-top" style="height:200px; object-fit:cover;"
                             onerror="this.src='/pwandeal/assets/img/service-placeholder.jpg'">
                        
                        <div class="card-body p-3">
                            <h6 class="card-title fw-bold mb-1 text-truncate"><?php echo htmlspecialchars($service['title']); ?></h6>
                            <p class="text-muted small mb-3">By <?php echo htmlspecialchars($service['seller_name']); ?></p>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="fw-bold text-primary">KSh <?php echo number_format($service['price']); ?></span>
                                <a href="/pwandeal/listings/detail.php?id=<?php echo $service['listing_id']; ?>" class="btn btn-sm btn-primary rounded-pill px-3">View</a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<?php include __DIR__ . '/includes/footer.php'; ?>