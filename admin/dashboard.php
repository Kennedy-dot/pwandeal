<?php
/**
 * PwanDeal - Admin Dashboard
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/database.php';

// 1. ADMIN ACCESS CONTROL
// Assuming user_id 1 is the primary admin as per your logic
if (!isset($_SESSION['user_id']) || $_SESSION['user_id'] !== 1) {
    header('Location: /pwandeal/auth/login.php');
    exit();
}

$page_title = 'Admin Dashboard';
$base_url = '/pwandeal';

// 2. FETCH STATISTICS
// Users
$total_users = $conn->query("SELECT COUNT(*) as c FROM users")->fetch_assoc()['c'];
$suspended_users = $conn->query("SELECT COUNT(*) as c FROM users WHERE is_suspended=1")->fetch_assoc()['c'];

// Listings & Revenue
$active_listings = $conn->query("SELECT COUNT(*) as c FROM listings WHERE status='active'")->fetch_assoc()['c'];
$revenue_res = $conn->query("SELECT SUM(price) as total FROM listings WHERE status='sold'");
$total_revenue = $revenue_res->fetch_assoc()['total'] ?? 0;

// Interactions
$total_messages = $conn->query("SELECT COUNT(*) as c FROM messages")->fetch_assoc()['c'];
$total_reviews = $conn->query("SELECT COUNT(*) as c FROM reviews")->fetch_assoc()['c'];
$pending_reports = $conn->query("SELECT COUNT(*) as c FROM reports WHERE status='pending'")->fetch_assoc()['c'];

include __DIR__ . '/../includes/header.php';
?>

<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 style="color: #1e2761; font-weight: 700;">🛡️ Admin Control Center</h1>
        <span class="text-muted">System Status: <span class="badge bg-success">Online</span></span>
    </div>
    
    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100 rounded-4">
                <div class="card-body text-center">
                    <div class="display-6 mb-2">👥</div>
                    <h3 class="fw-bold mb-0" style="color: #028090;"><?= number_format($total_users) ?></h3>
                    <small class="text-muted text-uppercase fw-bold">Total Users</small>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100 rounded-4">
                <div class="card-body text-center">
                    <div class="display-6 mb-2">📋</div>
                    <h3 class="fw-bold mb-0" style="color: #27ae60;"><?= number_format($active_listings) ?></h3>
                    <small class="text-muted text-uppercase fw-bold">Active Listings</small>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100 rounded-4">
                <div class="card-body text-center">
                    <div class="display-6 mb-2">💬</div>
                    <h3 class="fw-bold mb-0" style="color: #3498db;"><?= number_format($total_messages) ?></h3>
                    <small class="text-muted text-uppercase fw-bold">Total Messages</small>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100 rounded-4">
                <div class="card-body text-center">
                    <div class="display-6 mb-2">💰</div>
                    <h3 class="fw-bold mb-0" style="color: #1abc9c;">KSh <?= number_format($total_revenue) ?></h3>
                    <small class="text-muted text-uppercase fw-bold">Market Value</small>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row g-4 mb-5">
        <div class="col-md-6">
            <div class="card border-0 bg-danger text-white shadow-sm rounded-4">
                <div class="card-body d-flex align-items-center justify-content-between p-4">
                    <div>
                        <h4 class="mb-0 fw-bold"><?= $pending_reports ?> Pending Reports</h4>
                        <small class="opacity-75">Content flagged by the community requires review</small>
                    </div>
                    <a href="/pwandeal/admin/reports.php" class="btn btn-light btn-sm fw-bold text-danger px-3 rounded-pill">Handle →</a>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card border-0 bg-dark text-white shadow-sm rounded-4">
                <div class="card-body d-flex align-items-center justify-content-between p-4">
                    <div>
                        <h4 class="mb-0 fw-bold"><?= $suspended_users ?> Suspended Accounts</h4>
                        <small class="opacity-75">Users currently restricted from the platform</small>
                    </div>
                    <a href="/pwandeal/admin/users.php" class="btn btn-outline-light btn-sm fw-bold px-3 rounded-pill">Manage Users</a>
                </div>
            </div>
        </div>
    </div>
    
    <h3 class="fw-bold mb-4" style="color: #1e2761;">Management Tools</h3>
    <div class="row g-4">
        <div class="col-md-3">
            <a href="/pwandeal/admin/users.php" class="text-decoration-none">
                <div class="card h-100 border-0 shadow-sm hover-effect py-3 rounded-4">
                    <div class="card-body text-center">
                        <h5 class="fw-bold" style="color: #028090;">Users</h5>
                        <p class="text-muted small mb-0">Moderate accounts & permissions</p>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-md-3">
            <a href="/pwandeal/admin/listings.php" class="text-decoration-none">
                <div class="card h-100 border-0 shadow-sm hover-effect py-3 rounded-4">
                    <div class="card-body text-center">
                        <h5 class="fw-bold" style="color: #27ae60;">Listings</h5>
                        <p class="text-muted small mb-0">Moderate marketplace services</p>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-md-3">
            <a href="/pwandeal/admin/reports.php" class="text-decoration-none">
                <div class="card h-100 border-0 shadow-sm hover-effect py-3 rounded-4">
                    <div class="card-body text-center">
                        <h5 class="fw-bold" style="color: #e74c3c;">Reports</h5>
                        <p class="text-muted small mb-0">Resolve community complaints</p>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-md-3">
            <a href="/pwandeal/reviews/my-reviews.php" class="text-decoration-none">
                <div class="card h-100 border-0 shadow-sm hover-effect py-3 rounded-4">
                    <div class="card-body text-center">
                        <h5 class="fw-bold" style="color: #FFD700;">Reviews</h5>
                        <p class="text-muted small mb-0">Moderate student feedback</p>
                    </div>
                </div>
            </a>
        </div>
    </div>
</div>

<style>
.hover-effect { transition: all 0.3s ease; border: 1px solid transparent !important; }
.hover-effect:hover { 
    transform: translateY(-5px); 
    box-shadow: 0 10px 20px rgba(0,0,0,0.1) !important;
    border-color: #028090 !important;
}
</style>

<?php include __DIR__ . '/../includes/footer.php'; ?>