<?php
/**
 * PwanDeal - Footer
 * Standard footer for all pages.
 */

// Ensure base_url is set for pathing
if (!isset($base_url)) {
    $base_url = '/pwandeal';
}

// Check session for conditional links
$is_logged_in = isset($_SESSION['user_id']);
$is_admin = (isset($_SESSION['user_id']) && $_SESSION['user_id'] === 1);
?>

</div> <footer class="footer text-white py-5 mt-auto" style="background: #1e2761; border-top: 4px solid #028090;">
    <div class="container">
        <div class="row g-4 mb-4">
            
            <div class="col-lg-4 col-md-6">
                <h5 class="fw-bold mb-3" style="color: #028090;">⭐ PwanDeal</h5>
                <p class="small opacity-75" style="line-height: 1.8;">
                    The official digital marketplace for Pwani University students. Buy, sell, and offer services safely within the Kilifi campus community.
                </p>
                <div class="mt-3">
                    <span class="badge bg-success rounded-pill px-3">Kilifi Campus</span>
                    <span class="badge bg-info rounded-pill px-3">Student Verified</span>
                </div>
            </div>

            <div class="col-lg-4 col-md-6">
                <h5 class="fw-bold mb-3">🔗 Quick Links</h5>
                <ul class="list-unstyled footer-links">
                    <li><a href="<?= $base_url; ?>/index.php">🏠 Home</a></li>
                    <li><a href="<?= $base_url; ?>/listings/view.php">🔍 Browse Services</a></li>
                    
                    <?php if (!$is_logged_in): ?>
                        <li><a href="<?= $base_url; ?>/auth/register.php">📝 Create Account</a></li>
                        <li><a href="<?= $base_url; ?>/auth/login.php">🔐 Student Login</a></li>
                    <?php else: ?>
                        <li><a href="<?= $base_url; ?>/profile/dashboard.php">👤 My Dashboard</a></li>
                        <li><a href="<?= $base_url; ?>/listings/create.php">➕ Post a Listing</a></li>
                        <?php if ($is_admin): ?>
                            <li><a href="<?= $base_url; ?>/admin/dashboard.php" class="text-warning">⚙️ Admin Panel</a></li>
                        <?php endif; ?>
                    <?php endif; ?>
                </ul>
            </div>

            <div class="col-lg-4 col-md-12">
                <h5 class="fw-bold mb-3">📞 Support & Contact</h5>
                <div class="d-flex align-items-center mb-3">
                    <div class="me-3 fs-5 text-info"><i class="bi bi-envelope-fill"></i></div>
                    <a href="mailto:support@pwandeal.pw" class="text-white text-decoration-none small opacity-75">support@pwandeal.pw</a>
                </div>
                <div class="d-flex align-items-center mb-3">
                    <div class="me-3 fs-5 text-info"><i class="bi bi-whatsapp"></i></div>
                    <a href="https://wa.me/254111602678" target="_blank" class="text-white text-decoration-none small opacity-75">+254 111 602 678</a>
                </div>
                <div class="d-flex align-items-start">
                    <div class="me-3 fs-5 text-info"><i class="bi bi-geo-alt-fill"></i></div>
                    <span class="small opacity-75">Pwani University Main Campus,<br>Kilifi, Kenya</span>
                </div>
            </div>
        </div>

        <hr style="border-color: rgba(255,255,255,0.1);">

        <div class="row align-items-center pt-3">
            <div class="col-md-6 text-center text-md-start">
                <p class="small mb-0 opacity-50">
                    &copy; <?= date('Y'); ?> <strong>PwanDeal</strong>. Built with ❤️ for Pwanians.
                </p>
            </div>
            <div class="col-md-6 text-center text-md-end mt-3 mt-md-0">
                <a href="<?= $base_url; ?>/privacy.php" class="footer-sub-link">Privacy Policy</a>
                <span class="mx-2 opacity-25">|</span>
                <a href="<?= $base_url; ?>/terms.php" class="footer-sub-link">Terms of Service</a>
            </div>
        </div>
    </div>
</footer>

<button type="button" id="backToTopBtn" class="btn shadow-lg" title="Go to top">
    <i class="bi bi-arrow-up"></i>
</button>

<style>
    .footer-links li { margin-bottom: 12px; transition: 0.3s; }
    .footer-links a { 
        color: rgba(255,255,255,0.7); 
        text-decoration: none; 
        font-size: 0.95rem;
        transition: 0.2s;
    }
    .footer-links a:hover { color: #028090; padding-left: 8px; }
    
    .footer-sub-link { 
        color: rgba(255,255,255,0.5); 
        text-decoration: none; 
        font-size: 0.85rem; 
    }
    .footer-sub-link:hover { color: white; }

    #backToTopBtn {
        position: fixed;
        bottom: 30px;
        right: 30px;
        background: #028090;
        color: white;
        border-radius: 50%;
        width: 45px;
        height: 45px;
        display: none;
        z-index: 2000;
        border: none;
        transition: all 0.3s ease;
    }
    #backToTopBtn:hover {
        background: #1e2761;
        transform: translateY(-5px);
    }
</style>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
    // Back to Top Logic
    const btt = document.getElementById("backToTopBtn");
    window.onscroll = function() {
        if (document.body.scrollTop > 400 || document.documentElement.scrollTop > 400) {
            btt.style.display = "block";
        } else {
            btt.style.display = "none";
        }
    };
    btt.onclick = function() {
        window.scrollTo({top: 0, behavior: 'smooth'});
    };

    // Tooltip initialization (if used)
    const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]')
    const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl))
</script>

</body>
</html>