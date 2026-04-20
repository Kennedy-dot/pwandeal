<?php
/**
 * PwanDeal - Privacy Policy
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/config/database.php';

$page_title = 'Privacy Policy';
$base_url = '.'; // Relative to root

include 'includes/header.php';
?>

<div class="container py-5">
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="index.php" class="text-decoration-none" style="color: #028090;">Home</a></li>
            <li class="breadcrumb-item active" aria-current="page">Privacy Policy</li>
        </ol>
    </nav>

    <div class="row">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm p-4 p-md-5 rounded-4">
                <div class="d-flex align-items-center mb-4">
                    <div class="p-3 bg-light rounded-circle me-3">
                        <span class="fs-2">🛡️</span>
                    </div>
                    <h1 class="fw-bold mb-0" style="color: #1e2761;">Privacy Policy</h1>
                </div>

                <section class="mb-5">
                    <h4 class="fw-bold" style="color: #028090;">1. Introduction</h4>
                    <p class="text-secondary leading-relaxed">PwanDeal is built exclusively for the Pwani University community. We value your trust and are committed to protecting your personal data. This policy outlines how we handle your information to provide a safe trading environment.</p>
                </section>

                <section class="mb-5">
                    <h4 class="fw-bold" style="color: #028090;">2. Information We Collect</h4>
                    <p class="text-secondary">To keep the platform secure, we collect the following:</p>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="p-3 border rounded-3 h-100">
                                <h6 class="fw-bold mb-1">Account Data</h6>
                                <small class="text-muted">Name, @pwani.ac.ke email, and your profile photo.</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="p-3 border rounded-3 h-100">
                                <h6 class="fw-bold mb-1">Content Data</h6>
                                <small class="text-muted">Listing details, reviews, and messages sent to other users.</small>
                            </div>
                        </div>
                    </div>
                </section>

                <section class="mb-5">
                    <h4 class="fw-bold" style="color: #028090;">3. How We Use Your Data</h4>
                    <ul class="text-secondary">
                        <li>To verify your student status via the university email system.</li>
                        <li>To facilitate connections between buyers and service providers.</li>
                        <li>To maintain the integrity of our review and rating system.</li>
                        <li>To prevent fraud or misuse of the PwanDeal platform.</li>
                    </ul>
                </section>

                <section class="mb-5">
                    <h4 class="fw-bold" style="color: #028090;">4. Data Sharing & Visibility</h4>
                    <p class="text-secondary">Your phone number is only visible to registered users when you post a listing. We <strong>never</strong> sell your data to third-party advertisers or external organizations.</p>
                </section>

                <section class="border-top pt-4">
                    <h5 class="fw-bold mb-3">Questions?</h5>
                    <div class="d-flex flex-wrap gap-4">
                        <a href="mailto:support@pwandeal.pw" class="text-decoration-none fw-bold" style="color: #028090;">
                            <i class="bi bi-envelope-fill me-1"></i> support@pwandeal.pw
                        </a>
                        <span class="text-muted">|</span>
                        <a href="tel:+254111602678" class="text-decoration-none fw-bold" style="color: #028090;">
                            <i class="bi bi-whatsapp me-1"></i> +254 111 602 678
                        </a>
                    </div>
                </section>

                <div class="mt-5 pt-3 border-top">
                    <small class="text-muted italic">Last updated: April 10, 2026</small>
                </div>
            </div>
        </div>

        <div class="col-lg-4 mt-4 mt-lg-0">
            <div class="sticky-top" style="top: 2rem;">
                <div class="card border-0 shadow-sm p-4 rounded-4 text-white mb-4" style="background: linear-gradient(135deg, #1e2761, #028090);">
                    <h5 class="fw-bold mb-3">Student Safety First</h5>
                    <p class="small opacity-75">By limiting access to verified <strong>@pwani.ac.ke</strong> accounts, we significantly reduce the risk of non-student scammers entering the marketplace.</p>
                </div>

                <div class="card border-0 shadow-sm p-4 rounded-4 bg-white">
                    <h6 class="fw-bold mb-3 text-dark">Quick Navigation</h6>
                    <ul class="list-unstyled small mb-0">
                        <li class="mb-2"><a href="terms.php" class="text-decoration-none text-muted">Terms of Service</a></li>
                        <li class="mb-2"><a href="safety.php" class="text-decoration-none text-muted">Safety Tips</a></li>
                        <li><a href="contact.php" class="text-decoration-none text-muted">Contact Admin</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>