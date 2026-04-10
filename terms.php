<?php
/**
 * PwanDeal - Terms of Service
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/config/database.php';

$page_title = 'Terms of Service';
$base_url = '.'; 

include 'includes/header.php';
?>

<div class="container py-5">
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb small">
            <li class="breadcrumb-item"><a href="index.php" class="text-decoration-none" style="color: #028090;">Home</a></li>
            <li class="breadcrumb-item active" aria-current="page">Terms of Service</li>
        </ol>
    </nav>

    <div class="row">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm p-4 p-md-5 rounded-4">
                <div class="d-flex align-items-center mb-4">
                    <div class="p-3 bg-light rounded-circle me-3">
                        <span class="fs-2">📋</span>
                    </div>
                    <h1 class="fw-bold mb-0" style="color: #1e2761;">Terms of Service</h1>
                </div>
                
                <div class="alert alert-light border-0 rounded-4 p-4 mb-4" style="background-color: #f0f7f8;">
                    <h6 class="fw-bold" style="color: #028090;"><i class="bi bi-info-circle-fill me-2"></i>The PwanDeal Handshake</h6>
                    <p class="small mb-0 text-secondary">PwanDeal is a platform for Pwani University students. By using it, you agree to act honestly, follow university guidelines, and obey Kenyan law. We provide the marketplace; you provide the integrity.</p>
                </div>

                <div class="terms-content">
                    <section class="mb-5">
                        <h4 class="fw-bold" style="color: #028090;">1. Agreement to Terms</h4>
                        <p class="text-secondary">By accessing PwanDeal, you agree to be bound by these terms. This platform is strictly for academic and student-led commercial exchange within the Pwani University community.</p>
                    </section>

                    <section class="mb-5">
                        <h4 class="fw-bold" style="color: #028090;">2. Eligibility</h4>
                        <p class="text-secondary">Registration is restricted to current students and staff with a valid <strong>@pwani.ac.ke</strong> email address. Accounts found to be held by non-university members will be terminated immediately.</p>
                    </section>

                    <section class="mb-5">
                        <h4 class="fw-bold" style="color: #028090;">3. Prohibited Activities</h4>
                        <p class="text-secondary">The following are strictly forbidden and will result in a permanent ban:</p>
                        <ul class="text-secondary">
                            <li class="mb-2"><strong>Illegal Content:</strong> Selling illegal substances, weapons, or counterfeit items.</li>
                            <li class="mb-2"><strong>Academic Dishonesty:</strong> Selling completed exams, assignments, or proxy attendance services.</li>
                            <li class="mb-2"><strong>Harassment:</strong> Using the chat system to bully, spam, or solicit other students.</li>
                            <li class="mb-2"><strong>Fraud:</strong> Misrepresenting services or failing to deliver items after payment.</li>
                        </ul>
                    </section>

                    <section class="mb-5">
                        <h4 class="fw-bold" style="color: #028090;">4. Disclaimer of Liability</h4>
                        <div class="p-3 border-start border-4 border-warning bg-light">
                            <p class="text-secondary mb-0"><em>PwanDeal acts as a neutral venue. We do not vet every user or item. All transactions and physical meetings are conducted at your own risk. Always meet in public, well-lit campus areas.</em></p>
                        </div>
                    </section>

                    <section class="mb-5">
                        <h4 class="fw-bold" style="color: #028090;">5. Governing Law</h4>
                        <p class="text-secondary">These terms are governed by the laws of the Republic of Kenya and Pwani University's internal student code of conduct.</p>
                    </section>
                </div>

                <div class="mt-5 pt-4 border-top d-flex justify-content-between align-items-center flex-wrap gap-3">
                    <div>
                        <h6 class="fw-bold mb-0">Questions?</h6>
                        <a href="mailto:support@pwandeal.pw" class="small text-decoration-none" style="color: #028090;">support@pwandeal.pw</a>
                    </div>
                    <small class="text-muted italic">Last updated: April 10, 2026</small>
                </div>
            </div>
        </div>

        <div class="col-lg-4 mt-4 mt-lg-0">
            <div class="sticky-top" style="top: 2rem;">
                <div class="card border-0 shadow-sm rounded-4 mb-4 overflow-hidden">
                    <div class="card-body p-4">
                        <h5 class="fw-bold mb-3">Quick Links</h5>
                        <div class="d-grid gap-2">
                            <a href="privacy.php" class="btn btn-outline-light text-dark border p-3 rounded-3 text-start">
                                <span class="d-block fw-bold small">Privacy Policy</span>
                                <small class="text-muted">How we handle your data</small>
                            </a>
                            <a href="contact.php" class="btn btn-outline-light text-dark border p-3 rounded-3 text-start">
                                <span class="d-block fw-bold small">Report a User</span>
                                <small class="text-muted">Keep the campus safe</small>
                            </a>
                        </div>
                    </div>
                </div>

                <div class="card border-0 shadow-sm rounded-4 text-white" style="background: #1e2761;">
                    <div class="card-body p-4 text-center">
                        <div class="fs-1 mb-2">🤝</div>
                        <h6 class="fw-bold">Fair Play Policy</h6>
                        <p class="small opacity-75 mb-0">PwanDeal is built on mutual respect. Treat every seller and buyer as a colleague.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>